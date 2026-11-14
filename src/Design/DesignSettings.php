<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

final readonly class DesignSettings
{
    public const FONT_PAIRS = ['lora-source' => ['Lora', 'Source Sans 3'], 'newsreader-source' => ['Newsreader', 'Source Sans 3'], 'nunito' => ['Nunito Sans', 'Nunito Sans'], 'fraunces-helvetica' => ['Fraunces', 'Helvetica Neue']];

    /**
     * Header layouts: clear (brand left, menu centered), centered (menu left, brand
     * centered) and floating (a rounded bar below the top edge). Labels of all
     * options are in $GLOBALS['TL_LANG']['tl_theme'].
     */
    public const HEADER_STYLES = ['clear', 'centered', 'floating'];

    /**
     * Logo formats. SVG keeps its own size; Image Studio scales the others.
     */
    public const LOGO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

    /**
     * Package defaults must match base.css, shop.css and seminar-theme.css.
     */
    public const SURFACE_DEFAULTS = [
        '' => ['paper' => '#f6f2f3', 'text' => '#382f3d', 'tone1' => '#e9e4e6', 'tone2' => '#e4dce3'],
        'nordwerk-shop-v1' => ['paper' => '#f6f2f3', 'text' => '#382f3d', 'tone1' => '#e7dbe9', 'tone2' => '#dce8db'],
        'seminar' => ['paper' => '#fcfdfb', 'text' => '#263b35', 'tone1' => '#e4eee9', 'tone2' => '#e4efe9'],
        'nordwerk-farm-shop-v1' => ['paper' => '#f6f2e7', 'text' => '#3a342b', 'tone1' => '#e6f2d2', 'tone2' => '#f1e6cf'],
        'nordwerk-booking-v1' => ['paper' => '#f7f5f0', 'text' => '#1c1b19', 'tone1' => '#ece8df', 'tone2' => '#e6eae2'],
    ];

    /**
     * Empty keeps the package default.
     */
    public const RADII = ['', '0', '6px', '14px', '22px'];

    public const BUTTON_SHAPES = ['', '4px', '10px', '999px'];

    public string $accent;
    public string $surface;
    public string $paper;
    public string $text;
    public string $tone1;
    public string $tone2;

    public function __construct(
        string $accent = '#382f3d',
        string $surface = '#fffaf7',
        public string $fontPair = 'lora-source',
        public string|null $logo = null,
        public string $headerStyle = 'clear',
        public string $announcement = '',
        string $paper = '',
        string $text = '',
        public string $radius = '',
        public string $buttonShape = '',
        string $tone1 = '',
        string $tone2 = '',
        public string $themeKey = '',
        public string $logoSize = '',
        public string $style = '',
        public string $palette = '',
        public string $typeset = '',
        public string $shape = '',
    ) {
        $this->accent = self::normalizeColor($accent);
        $this->surface = self::normalizeColor($surface);
        $optional = static fn (string $color): string => '' === trim($color) ? '' : self::normalizeColor($color);
        $this->paper = $optional($paper);
        $this->text = $optional($text);
        $this->tone1 = $optional($tone1);
        $this->tone2 = $optional($tone2);
        if (!\in_array($radius, self::RADII, true) || !\in_array($buttonShape, self::BUTTON_SHAPES, true)) {
            throw new DesignException('shape', 'Please choose one of the given corner and button shapes.');
        }
        // Empty: the fonts of the style or, without a style, of the theme package.
        if ('' !== $fontPair && !isset(self::FONT_PAIRS[$fontPair])) {
            throw new DesignException('font_pair', 'Please choose one of the bundled font pairs.');
        }
        if (!\in_array($headerStyle, self::HEADER_STYLES, true)) {
            throw new DesignException('header_style', 'Please choose one of the header layouts.');
        }
        if (mb_strlen($announcement) > 120) {
            throw new DesignException('announcement', 'The announcement bar holds at most 120 characters.');
        }
    }

    public static function normalizeColor(string $color): string
    {
        $color = trim($color);
        if (!preg_match('/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/D', $color)) {
            throw new DesignException('color', 'Please enter a hex color with three or six digits, e.g. 382f3d.');
        }
        $color = ltrim($color, '#');
        if (3 === \strlen($color)) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        }

        return '#'.strtolower($color);
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromThemeRow(array $row): self
    {
        $headerStyle = (string) ($row['nwHeaderStyle'] ?? 'clear');

        return new self(
            accent: (string) ($row['nwAccent'] ?? '#382f3d'),
            surface: (string) ($row['nwSurface'] ?? '#fffaf7'),
            fontPair: (string) ($row['nwFontPair'] ?? 'lora-source'),
            logo: \is_string($row['nwLogo'] ?? null) ? $row['nwLogo'] : null,
            headerStyle: \in_array($headerStyle, self::HEADER_STYLES, true) ? $headerStyle : 'clear',
            announcement: mb_substr((string) ($row['nwAnnouncement'] ?? ''), 0, 120),
            paper: (string) ($row['nwPaper'] ?? ''),
            text: (string) ($row['nwText'] ?? ''),
            radius: (string) ($row['nwRadius'] ?? ''),
            buttonShape: (string) ($row['nwButtonShape'] ?? ''),
            tone1: (string) ($row['nwTone1'] ?? ''),
            tone2: (string) ($row['nwTone2'] ?? ''),
            themeKey: (string) ($row['nwThemeKey'] ?? ''),
            logoSize: (string) ($row['nwLogoSize'] ?? ''),
            style: (string) ($row['nwStyle'] ?? ''),
            palette: (string) ($row['nwPalette'] ?? ''),
            typeset: (string) ($row['nwTypeset'] ?? ''),
            shape: (string) ($row['nwShape'] ?? ''),
        );
    }

    /**
     * @return array{paper: string, text: string, tone1: string, tone2: string}
     */
    public function effectiveSurfaces(): array
    {
        // With a palette, empty fields fall back to its surfaces (see Palette::tokens()).
        $colors = $this->paletteProfile()?->surfaces() ?? self::SURFACE_DEFAULTS[$this->themeKey] ?? self::SURFACE_DEFAULTS[''];

        foreach ($colors as $field => $default) {
            if ('' !== $this->{$field}) {
                $colors[$field] = $this->{$field};
            }
        }

        return $colors;
    }

    public function validateSurfaceContrast(): void
    {
        $colors = $this->effectiveSurfaces();

        foreach (['paper' => 'nwPaper', 'tone1' => 'nwTone1', 'tone2' => 'nwTone2'] as $field => $column) {
            $ratio = self::contrastRatio($colors['text'], $colors[$field]);
            if ($ratio < 4.5) {
                throw new DesignException('contrast', \sprintf('The text color reaches only %s:1 contrast on %s; at least 4.5:1 is required. Empty fields use the default of the theme package.', number_format($ratio, 2), $column), ['field' => $column, 'ratio' => $ratio]);
            }
        }
    }

    /**
     * Only explicit overrides belong in the generated CSS.
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return [...$this->colorVariables(), ...$this->shapeVariables()];
    }

    /**
     * Color fields of the form that were set by hand or by a palette.
     *
     * @return array<string, string>
     */
    public function colorVariables(): array
    {
        $variables = [];

        foreach (['paper' => '--theme-paper', 'text' => '--theme-ink', 'tone1' => '--theme-tile', 'tone2' => '--theme-callout'] as $field => $variable) {
            if ('' !== $this->{$field}) {
                $variables[$variable] = $this->{$field};
            }
        }
        // The palette brings its own secondary text color for its own ink.
        if ('' !== $this->text && $this->text !== $this->paletteProfile()?->surfaces()['text']) {
            $variables['--theme-muted'] = 'color-mix(in srgb, var(--theme-ink) 75%, var(--theme-paper))';
        }

        return $variables;
    }

    /**
     * Corners and buttons chosen by hand.
     *
     * @return array<string, string>
     */
    public function shapeVariables(): array
    {
        $variables = [];
        if ('' !== $this->radius) {
            $variables['--theme-radius'] = $this->radius;
            // Shop cards otherwise keep their package's fixed radius.
            $variables['--nw-radius-card'] = 'var(--theme-radius)';
        }
        if ('' !== $this->buttonShape) {
            $variables['--nw-radius-button'] = $this->buttonShape;
        }

        return $variables;
    }

    public function contrast(): float
    {
        return self::contrastRatio($this->accent, '#ffffff');
    }

    public function accentText(): string
    {
        // The palette's button text for the palette's accent, if it reads well enough.
        $palette = $this->paletteProfile();
        if (null !== $palette && $palette->action() === $this->accent && self::contrastRatio($this->accent, $palette->actionInk()) >= 4.5) {
            return $palette->actionInk();
        }

        return $this->contrast() >= 4.5 ? '#ffffff' : '#000000';
    }

    /**
     * The chosen style preset, null without one or for an id the catalog no longer knows.
     */
    public function styleProfile(): Style|null
    {
        return StyleCatalog::bundled()->get($this->style);
    }

    /**
     * The palette chosen in the form, else the style's. An id the catalog no longer
     * knows counts as not chosen.
     */
    public function paletteProfile(): Palette|null
    {
        return $this->axis('palette', $this->palette);
    }

    public function typesetProfile(): Typeset|null
    {
        return $this->axis('typeset', $this->typeset);
    }

    public function shapeProfile(): Shape|null
    {
        return $this->axis('shape', $this->shape);
    }

    /**
     * Spacing and content width of the chosen style; no axis changes them.
     *
     * @return array<string, string>
     */
    public function spaceTokens(): array
    {
        return $this->styleProfile()?->shape->spaceTokens() ?? [];
    }

    /**
     * Font families of the font pair, null for "default of the style or theme".
     *
     * @return array{0: string, 1: string}|null
     */
    public function fontFamilies(): array|null
    {
        return self::FONT_PAIRS[$this->fontPair] ?? null;
    }

    public function surfaceText(): string
    {
        $ink = $this->effectiveSurfaces()['text'];

        return self::contrastRatio($this->surface, $ink) >= 4.5 ? $ink : (self::contrastRatio($this->surface, '#000000') >= 4.5 ? '#000000' : '#ffffff');
    }

    /**
     * Text color for light surfaces comes from the theme's own ink (CSS). Only a
     * surface without enough contrast to the effective ink needs an override.
     */
    public function surfaceTextOverride(): string|null
    {
        return self::contrastRatio($this->surface, $this->effectiveSurfaces()['text']) >= 4.5 ? null : $this->surfaceText();
    }

    public static function contrastRatio(string $first, string $second): float
    {
        $a = self::luminance($first);
        $b = self::luminance($second);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

    private static function luminance(string $color): float
    {
        $channels = [];

        foreach ([1, 3, 5] as $offset) {
            $channel = hexdec(substr($color, $offset, 2)) / 255;
            $channels[] = $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }

        return $channels[0] * 0.2126 + $channels[1] * 0.7152 + $channels[2] * 0.0722;
    }

    /**
     * @param 'palette'|'typeset'|'shape' $axis
     *
     * @return ($axis is 'palette' ? Palette|null : ($axis is 'typeset' ? Typeset|null : Shape|null))
     */
    private function axis(string $axis, string $id): Palette|Shape|Typeset|null
    {
        $style = $this->styleProfile();

        return StyleCatalog::bundled()->forStyle($axis, $id, $style) ?? $style?->{$axis};
    }
}
