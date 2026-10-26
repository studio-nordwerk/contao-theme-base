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

    public function __construct(
        private readonly Connection $connection,
        private readonly ThemePackages $packages,
        private readonly Environment $twig,
        private readonly CacheTagManager $cacheTags,
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
            // "static" puts the file into Contao's combiner after the CSS framework; the
            // number is the version for the ?v= parameter.
            $mtime = @filemtime($this->webDir.'/'.$stylesheet);
            $GLOBALS['TL_CSS'][] = $stylesheet.'|static'.(false !== $mtime ? '|'.$mtime : '');
        }

        try {
            $design = DesignSettings::fromThemeRow($theme);
        } catch (\InvalidArgumentException) {
            // A record changed outside the theme form keeps the package defaults.
            $design = new DesignSettings(themeKey: (string) $theme['nwThemeKey']);
        }
        $GLOBALS['TL_HEAD']['nw_theme_design'] = $this->twig->render('@Contao/component/_nw_design_variables.html.twig', [
            'design' => $design,
            'headingFont' => DesignSettings::FONT_PAIRS[$design->fontPair][0],
            'bodyFont' => DesignSettings::FONT_PAIRS[$design->fontPair][1],
        ]);

        // Saving the theme form invalidates this tag (Contao does so for every record).
        $this->cacheTags->tagWith('contao.db.tl_theme.'.(int) $theme['id']);

        return $package;
    }
}
