<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

final readonly class DesignSettings
{
    public const FONT_PAIRS = ['lora-source' => ['Lora', 'Source Sans 3'], 'newsreader-source' => ['Newsreader', 'Source Sans 3'], 'nunito' => ['Nunito Sans', 'Nunito Sans']];

    /**
     * Header layouts: clear (brand left, menu centered), centered (menu left, brand
     * centered) and floating (a rounded bar below the top edge).
     */
    public const HEADER_STYLES = ['clear' => 'Klar: Wortmarke links, Menü mittig', 'centered' => 'Zentriert: Menü links, Wortmarke mittig', 'floating' => 'Schwebend: runde Leiste'];

    /** Package defaults must match base.css, shop.css and seminar-theme.css. */
    public const SURFACE_DEFAULTS = [
        '' => ['paper' => '#f6f2f3', 'text' => '#382f3d', 'tone1' => '#e9e4e6', 'tone2' => '#e4dce3'],
        'nordwerk-shop-v1' => ['paper' => '#f6f2f3', 'text' => '#382f3d', 'tone1' => '#e7dbe9', 'tone2' => '#dce8db'],
        'seminar' => ['paper' => '#fcfdfb', 'text' => '#263b35', 'tone1' => '#e4eee9', 'tone2' => '#e4efe9'],
    ];

    public const RADII = ['' => 'Vorgabe des Themes', '0' => 'Eckig (0)', '6px' => 'Leicht (6 px)', '14px' => 'Weich (14 px)', '22px' => 'Rund (22 px)'];
    public const BUTTON_SHAPES = ['' => 'Vorgabe des Themes', '4px' => 'Eckig (4 px)', '10px' => 'Abgerundet (10 px)', '999px' => 'Pille'];

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
    ) {
        $this->accent = self::normalizeColor($accent);
        $this->surface = self::normalizeColor($surface);
        foreach (['paper' => $paper, 'text' => $text, 'tone1' => $tone1, 'tone2' => $tone2] as $field => $color) {
            $this->{$field} = '' === trim($color) ? '' : self::normalizeColor($color);
        }
        if (!isset(self::RADII[$radius]) || !isset(self::BUTTON_SHAPES[$buttonShape])) {
            throw new \InvalidArgumentException('Bitte wählen Sie eine der vorgegebenen Ecken- und Buttonformen.');
        }
        if (!isset(self::FONT_PAIRS[$fontPair])) {
            throw new \InvalidArgumentException('Bitte wählen Sie eines der mitgelieferten Schriftpaare.');
        }
        if (!isset(self::HEADER_STYLES[$headerStyle])) {
            throw new \InvalidArgumentException('Bitte wählen Sie eine der Kopfzeilen.');
        }
        if (mb_strlen($announcement) > 120) {
            throw new \InvalidArgumentException('Die Hinweisleiste hat Platz für höchstens 120 Zeichen.');
        }
    }

    public static function normalizeColor(string $color): string
    {
        $color = trim($color);
        if (!preg_match('/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/D', $color)) {
            throw new \InvalidArgumentException('Bitte geben Sie eine Hex-Farbe mit drei oder sechs Stellen ein, z. B. 382f3d.');
        }
        $color = ltrim($color, '#');
        if (3 === strlen($color)) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        }

        return '#'.strtolower($color);
    }

    /** @param array<string, mixed> $row */
    public static function fromThemeRow(array $row): self
    {
        $headerStyle = (string) ($row['nwHeaderStyle'] ?? 'clear');

        return new self(
            accent: (string) ($row['nwAccent'] ?? '#382f3d'),
            surface: (string) ($row['nwSurface'] ?? '#fffaf7'),
            fontPair: (string) ($row['nwFontPair'] ?? 'lora-source'),
            logo: \is_string($row['nwLogo'] ?? null) ? $row['nwLogo'] : null,
            headerStyle: isset(self::HEADER_STYLES[$headerStyle]) ? $headerStyle : 'clear',
            announcement: mb_substr((string) ($row['nwAnnouncement'] ?? ''), 0, 120),
            paper: (string) ($row['nwPaper'] ?? ''),
            text: (string) ($row['nwText'] ?? ''),
            radius: (string) ($row['nwRadius'] ?? ''),
            buttonShape: (string) ($row['nwButtonShape'] ?? ''),
            tone1: (string) ($row['nwTone1'] ?? ''),
            tone2: (string) ($row['nwTone2'] ?? ''),
            themeKey: (string) ($row['nwThemeKey'] ?? ''),
        );
    }

    /** @return array{paper: string, text: string, tone1: string, tone2: string} */
    public function effectiveSurfaces(): array
    {
        $colors = self::SURFACE_DEFAULTS[$this->themeKey] ?? self::SURFACE_DEFAULTS[''];
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
        foreach (['paper' => 'Seitenhintergrund', 'tone1' => 'Fläche 1', 'tone2' => 'Fläche 2'] as $field => $label) {
            $ratio = self::contrastRatio($colors['text'], $colors[$field]);
            if ($ratio < 4.5) {
                throw new \InvalidArgumentException(sprintf('Textfarbe auf %s erreicht nur %s:1 Kontrast. Erforderlich sind mindestens 4,5:1. Bitte ändern Sie die Textfarbe oder den Hintergrund; leere Felder verwenden die Vorgabe des Theme-Pakets.', $label, number_format($ratio, 2, ',', '')));
            }
        }
    }

    /** Only explicit overrides belong in the generated CSS. @return array<string, string> */
    public function cssVariables(): array
    {
        $variables = [];
        foreach (['paper' => '--theme-paper', 'text' => '--theme-ink', 'radius' => '--theme-radius', 'buttonShape' => '--nw-radius-button', 'tone1' => '--theme-tile', 'tone2' => '--theme-callout'] as $field => $variable) {
            if ('' !== $this->{$field}) {
                $variables[$variable] = $this->{$field};
            }
        }
        if ('' !== $this->text) {
            $variables['--theme-muted'] = 'color-mix(in srgb, var(--theme-ink) 75%, var(--theme-paper))';
        }
        if ('' !== $this->radius) {
            // Shop cards otherwise keep their package's fixed radius.
            $variables['--nw-radius-card'] = 'var(--theme-radius)';
        }

        return $variables;
    }

    public function contrast(): float
    {
        return self::contrastRatio($this->accent, '#ffffff');
    }

    public function accentText(): string
    {
        return $this->contrast() >= 4.5 ? '#ffffff' : '#000000';
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
}
