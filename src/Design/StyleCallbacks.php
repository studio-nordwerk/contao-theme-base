<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Nordwerk\ThemeBaseBundle\Theme\StylableThemePackage;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackages;

/**
 * The fields "Stil", "Farbwelt", "Schrift" and "Form" of the theme form. A style
 * sets all three axes and fills the colors in; each axis can then be changed on
 * its own, and a value changed by hand in the fields below (colors, font pair,
 * corners, buttons) wins over the axes. Only themes of a StylableThemePackage
 * show the fields.
 */
final readonly class StyleCallbacks
{
    private const FIELDS = ',nwStyle,nwPalette,nwTypeset,nwShape,nwStylePreview';

    public function __construct(
        private StyleCatalog $styles,
        private ThemePackages $packages,
    ) {
    }

    #[AsCallback(table: 'tl_theme', target: 'config.onload')]
    public function adjustPalette(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id) {
            return;
        }
        $record = $dc->getCurrentRecord() ?? [];
        if (!$this->packages->get((string) ($record['nwThemeKey'] ?? '')) instanceof StylableThemePackage) {
            $GLOBALS['TL_DCA']['tl_theme']['palettes']['default'] = str_replace(self::FIELDS, '', (string) $GLOBALS['TL_DCA']['tl_theme']['palettes']['default']);

            return;
        }
        $settings = DesignSettings::fromThemeRow(array_map(static fn (mixed $value): string => \is_scalar($value) ? (string) $value : '', $record));
        $lang = &$GLOBALS['TL_LANG']['tl_theme'];
        $fields = &$GLOBALS['TL_DCA']['tl_theme']['fields'];

        // Empty axis fields follow the style, if there is one.
        foreach (['nwPalette', 'nwTypeset', 'nwShape'] as $field) {
            $fields[$field]['eval']['blankOptionLabel'] = null !== $settings->styleProfile() ? ($lang['axisFromStyle'] ?? '-') : ($lang['axisNone'] ?? '-');
        }
        // The fields below the axes: empty means the typeset's or the shape's value.
        if (null !== $settings->typesetProfile()) {
            $fields['nwFontPair']['eval']['blankOptionLabel'] = $lang['typesetDefault'] ?? '-';
        }
        if (null !== $settings->shapeProfile()) {
            foreach (['nwRadiusOptions', 'nwButtonShapeOptions'] as $options) {
                $lang[$options][''] = $lang['shapeDefault'] ?? $lang[$options][''] ?? '';
            }
        }
    }

    /**
     * @return array<string, string>
     */
    #[AsCallback(table: 'tl_theme', target: 'fields.nwStyle.options')]
    public function options(): array
    {
        return $this->styles->options();
    }

    /**
     * The curated palettes; a value saved earlier that is not one of them stays
     * listed, so saving the form does not drop it.
     *
     * @return array<string, string>
     */
    #[AsCallback(table: 'tl_theme', target: 'fields.nwPalette.options')]
    public function paletteOptions(DataContainer|null $dc = null): array
    {
        return $this->withSaved($this->styles->paletteOptions(), $dc, 'nwPalette', $this->styles->palette(...));
    }

    /**
     * @return array<string, string>
     */
    #[AsCallback(table: 'tl_theme', target: 'fields.nwTypeset.options')]
    public function typesetOptions(DataContainer|null $dc = null): array
    {
        return $this->withSaved($this->styles->typesetOptions(), $dc, 'nwTypeset', $this->styles->typeset(...));
    }

    /**
     * @return array<string, string>
     */
    #[AsCallback(table: 'tl_theme', target: 'fields.nwShape.options')]
    public function shapeOptions(DataContainer|null $dc = null): array
    {
        return $this->withSaved($this->styles->shapeOptions(), $dc, 'nwShape', $this->styles->shape(...));
    }

    /**
     * Picking a style or an axis overwrites the fields it governs, also when they
     * were changed in the same submission: a style sets the axes and fills the colors
     * in, a palette fills the colors in, a typeset clears the font pair, a shape
     * clears corners and buttons. "Kein Stil" clears the axes too; the colors stay.
     *
     * Contao only submits changed fields, so a key means "this field was changed".
     *
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    #[AsCallback(table: 'tl_theme', target: 'config.onbeforesubmit')]
    public function applyStyle(array $values, DataContainer $dc): array
    {
        if (\array_key_exists('nwStyle', $values)) {
            $style = $this->styles->get((string) $values['nwStyle']);
            if (null !== $style) {
                return [...$values, ...$style->themeFields()];
            }

            return '' === (string) $values['nwStyle'] ? [...$values, 'nwPalette' => '', 'nwTypeset' => '', 'nwShape' => ''] : $values;
        }
        if (\array_key_exists('nwPalette', $values) && null !== $palette = $this->styles->palette((string) $values['nwPalette'])) {
            $values = [...$values, ...$palette->themeFields()];
        }
        if (\array_key_exists('nwTypeset', $values)) {
            $values['nwFontPair'] = '';
        }
        if (\array_key_exists('nwShape', $values)) {
            $values = [...$values, 'nwRadius' => '', 'nwButtonShape' => ''];
        }

        return $values;
    }

    /**
     * @param array<string, string>                          $options
     * @param \Closure(string): (Palette|Shape|Typeset|null) $resolve
     *
     * @return array<string, string>
     */
    private function withSaved(array $options, DataContainer|null $dc, string $field, \Closure $resolve): array
    {
        $saved = (string) (($dc?->getCurrentRecord() ?? [])[$field] ?? '');
        if ('' === $saved || isset($options[$saved]) || null === $axis = $resolve($saved)) {
            return $options;
        }

        return [...$options, $saved => $axis->label.' ('.($GLOBALS['TL_LANG']['tl_theme']['axisSaved'] ?? 'saved').')'];
    }
}
