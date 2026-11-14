<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Theme;

use Contao\CoreBundle\Cache\CacheTagManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Event\LayoutEvent;
use Contao\CoreBundle\String\HtmlAttributes;
use Contao\LayoutModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Design\DesignSettings;
use Nordwerk\ThemeBaseBundle\Design\DesignVariables;
use Nordwerk\ThemeBaseBundle\Design\Palette;
use Nordwerk\ThemeBaseBundle\Design\Shape;
use Nordwerk\ThemeBaseBundle\Design\Style;
use Nordwerk\ThemeBaseBundle\Design\StyleCatalog;
use Nordwerk\ThemeBaseBundle\Design\Typeset;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Twig\Environment;

/**
 * Loads the theme stylesheets and the design variables of the theme form for
 * every page whose layout belongs to a Nordwerk theme, whatever modules the
 * layout shows. The stylesheets go to $GLOBALS['TL_CSS'], so they come before the
 * "external style sheets" of the layout and carry a version from the file date.
 * The variables follow in the <head> as a small style element.
 */
final class ThemeAssets
{
    public const BASE_STYLESHEET = 'bundles/themebase/base.css';

    public const PREVIEW_STYLESHEET = 'bundles/themebase/style-preview.css';

    public const PREVIEW_SCRIPT = 'bundles/themebase/style-preview.js';

    public function __construct(
        private readonly Connection $connection,
        private readonly ThemePackages $packages,
        private readonly Environment $twig,
        private readonly CacheTagManager $cacheTags,
        private readonly StyleCatalog $styles,
        #[Autowire('%contao.web_dir%')]
        private readonly string $webDir,
    ) {
    }

    /**
     * Classic layouts ("Standard-Layout"). The body classes the stylesheets and the
     * design variables rely on are added if the layout lacks them; the record itself
     * is not changed.
     */
    #[AsHook('generatePage')]
    public function onGeneratePage(PageModel $page, LayoutModel $layout): void
    {
        $package = $this->addToPage($layout);
        if (null !== $package) {
            $layout->cssClass = self::withClasses((string) $layout->cssClass, $package);
        }
    }

    /**
     * Twig layouts, whatever template they use.
     */
    #[AsEventListener]
    public function onLayout(LayoutEvent $event): void
    {
        $package = $this->addToPage($event->getLayout());
        if (null === $package) {
            return;
        }
        $template = $event->getTemplate();
        $attributes = new HtmlAttributes($template->has('body_attributes') ? $template->get('body_attributes') : null);
        $template->set('body_attributes', $attributes->addClass(explode(' ', self::withClasses('', $package))));
        $template->set('nw_theme', ['key' => $package->key(), 'cssClass' => $package->cssClass()]);
    }

    /**
     * CSS classes plus "nw-theme" and the classes of the package, without duplicates.
     */
    public static function withClasses(string $classes, ThemePackage $package): string
    {
        $list = preg_split('/\s+/', trim($classes.' nw-theme '.$package->cssClass()), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', array_values(array_unique($list)));
    }

    /**
     * @return list<string> the stylesheets of a theme package in loading order
     */
    public function stylesheets(ThemePackage $package): array
    {
        return array_values(array_unique([self::BASE_STYLESHEET, ...$package->stylesheets()]));
    }

    public function addToPage(LayoutModel|null $layout): ThemePackage|null
    {
        if (null === $layout || (int) $layout->pid < 1) {
            return null;
        }
        $theme = $this->connection->fetchAssociative('SELECT * FROM tl_theme WHERE id = ?', [(int) $layout->pid]);
        $package = false !== $theme ? $this->packages->get((string) ($theme['nwThemeKey'] ?? '')) : null;
        if (false === $theme || null === $package) {
            return null;
        }

        foreach ($this->stylesheets($package) as $stylesheet) {
            $this->addStylesheet($stylesheet);
        }

        try {
            $design = DesignSettings::fromThemeRow($theme);
        } catch (\InvalidArgumentException) {
            // A record changed outside the theme form keeps the package defaults.
            $design = new DesignSettings(themeKey: (string) $theme['nwThemeKey']);
        }
        // Styles and their axes only apply to packages built on all tokens
        // (StylableThemePackage).
        $stylable = $package instanceof StylableThemePackage;
        $typeset = $stylable ? $design->typesetProfile() : null;

        foreach (null !== $typeset ? $this->styles->fontStylesheets($typeset) : [] as $stylesheet) {
            $this->addStylesheet($stylesheet);
        }
        $GLOBALS['TL_HEAD']['nw_theme_design'] = $this->twig->render('@Contao/component/_nw_design_variables.html.twig', [
            'variables' => DesignVariables::of($design, $stylable),
        ]);
        if ($stylable && '1' === (string) ($theme['nwStylePreview'] ?? '')) {
            $this->addStylePreview($theme, $design);
        }

        // Saving the theme form invalidates this tag (Contao does so for every record).
        $this->cacheTags->tagWith('contao.db.tl_theme.'.(int) $theme['id']);

        return $package;
    }

    /**
     * The style preview for visitors of a demo: every palette, typeset and shape as a
     * rule for <html data-nw-palette|typeset|shape>, each computed as if an editor
     * had picked it in the theme form, plus the switcher at the end of the page. The
     * rules of the three axes share no variable, so they combine freely. The switcher
     * offers the featured styles and the curated axes; the styles' own axes are there
     * for whole styles and earlier links. The font files of all typesets are
     * declared; browsers only download those in use.
     *
     * @param array<string, mixed> $theme
     */
    private function addStylePreview(array $theme, DesignSettings $design): void
    {
        $rules = ['palette' => [], 'typeset' => [], 'shape' => []];
        $picks = [
            'palette' => fn (string $id): array => [...$this->styles->palette($id)?->themeFields() ?? [], 'nwPalette' => $id],
            'typeset' => static fn (string $id): array => ['nwTypeset' => $id, 'nwFontPair' => ''],
            'shape' => static fn (string $id): array => ['nwShape' => $id, 'nwRadius' => '', 'nwButtonShape' => ''],
        ];
        $all = ['palette' => $this->styles->allPalettes(), 'typeset' => $this->styles->allTypesets(), 'shape' => $this->styles->allShapes()];

        foreach ($all as $axis => $options) {
            foreach (array_keys($options) as $id) {
                try {
                    // Without the saved style: an axis looks the same over every style.
                    $picked = DesignSettings::fromThemeRow([...$theme, 'nwStyle' => '', ...$picks[$axis]($id)]);
                } catch (\InvalidArgumentException) {
                    continue;
                }
                $rules[$axis][$id] = DesignVariables::groups($picked, true)[$axis];
            }
        }

        foreach ($all['typeset'] as $typeset) {
            foreach ($this->styles->fontStylesheets($typeset) as $stylesheet) {
                $this->addStylesheet($stylesheet);
            }
        }
        $surfaces = $design->effectiveSurfaces();
        $swatches = ['' => ['surface' => $surfaces['paper'], 'ink' => $surfaces['text'], 'action' => $design->accent]];

        foreach ($all['palette'] as $id => $palette) {
            $swatches[$id] = ['surface' => $palette->surfaces()['paper'], 'ink' => $palette->surfaces()['text'], 'action' => $palette->action()];
        }
        // A whole style shows its curated axes where they match exactly, else its own.
        $axes = [];

        // Per style: its spacing, and its own values where it shows a curated axis made from
        // its block that differs for mixing (Studio still keeps its scale under "Plakativ").
        $spaces = [];
        $own = [];

        foreach ($this->styles->all() as $id => $style) {
            $ids = [$style->curated['palette'] ?? $style->palette->id, $style->curated['typeset'] ?? $style->typeset->id, $style->curated['shape'] ?? $style->shape->id];
            if (!isset($rules['palette'][$ids[0]], $rules['typeset'][$ids[1]], $rules['shape'][$ids[2]])) {
                continue;
            }
            $axes[$id] = $ids;
            $whole = DesignSettings::fromThemeRow([...$theme, ...$style->themeFields(), 'nwStyle' => $id]);
            $groups = DesignVariables::groups($whole, true);
            if ([] !== $groups['space']) {
                $spaces[$id] = $groups['space'];
            }

            foreach (['palette', 'typeset', 'shape'] as $index => $axis) {
                if ($groups[$axis] !== $rules[$axis][$ids[$index]]) {
                    $own[] = ['style' => $id, 'axis' => $axis, 'id' => $ids[$index], 'variables' => $groups[$axis]];
                }
            }
        }
        $this->addStylesheet(self::PREVIEW_STYLESHEET);
        $GLOBALS['TL_HEAD']['nw_style_preview'] = $this->twig->render('@Contao/component/_nw_style_preview_head.html.twig', [
            'rules' => $rules,
            'spaces' => $spaces,
            'own' => $own,
            'swatches' => $swatches,
            'fonts' => array_map(static fn (Typeset $typeset): string => $typeset->fonts['display'], $this->styles->typesets()),
            'radii' => array_map(static fn (Shape $shape): string => $shape->radius['card'], $this->styles->shapes()),
            'axes' => $axes,
            // A style's own axis with exactly the values of a curated one shows as that one.
            'same' => array_map(fn (string $axis): array => array_filter(array_map(fn (Palette|Shape|Typeset $value): string => $this->styles->curatedId($axis, $value), array_diff_key($all[$axis], $this->styles->{$axis.'s'}()))), ['palette' => 'palette', 'typeset' => 'typeset', 'shape' => 'shape']),
            'names' => [
                'style' => array_map(static fn (Style $style): string => $style->label, array_intersect_key($this->styles->all(), $axes)),
                'palette' => array_map(static fn (Palette $palette): string => $palette->label, $all['palette']),
                'typeset' => array_map(static fn (Typeset $typeset): string => $typeset->label, $all['typeset']),
                'shape' => array_map(static fn (Shape $shape): string => $shape->label, $all['shape']),
            ],
        ]);
        $mtime = @filemtime($this->webDir.'/'.self::PREVIEW_SCRIPT);
        $GLOBALS['TL_BODY']['nw_style_preview'] = $this->twig->render('@Contao/component/_nw_style_preview.html.twig', [
            'featured' => array_intersect_key($this->styles->featured(), $axes),
            'axes' => $axes,
            'palettes' => array_intersect_key($this->styles->palettes(), $rules['palette']),
            'typesets' => array_intersect_key($this->styles->typesets(), $rules['typeset']),
            'shapes' => array_intersect_key($this->styles->shapes(), $rules['shape']),
            'script' => self::PREVIEW_SCRIPT.(false !== $mtime ? '?v='.$mtime : ''),
        ]);
    }

    /**
     * "static" puts the file into Contao's combiner after the CSS framework; the number is
     * the version for the ?v= parameter. A file already on the page is skipped.
     */
    private function addStylesheet(string $stylesheet): void
    {
        $mtime = @filemtime($this->webDir.'/'.$stylesheet);
        $entry = $stylesheet.'|static'.(false !== $mtime ? '|'.$mtime : '');
        if (!\in_array($entry, $GLOBALS['TL_CSS'] ?? [], true)) {
            $GLOBALS['TL_CSS'][] = $entry;
        }
    }
}
