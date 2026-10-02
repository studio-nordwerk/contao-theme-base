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

    public function __construct(
        public string $accent = '#382f3d',
        public string $surface = '#fffaf7',
        public string $fontPair = 'lora-source',
        public string|null $logo = null,
        public string $headerStyle = 'clear',
        public string $announcement = '',
    ) {
        foreach ([$accent, $surface] as $color) {
            if (!preg_match('/^#[0-9a-fA-F]{6}$/D', $color)) {
                throw new \InvalidArgumentException('Bitte geben Sie Farben im Format #RRGGBB ein.');
            }
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
        return self::contrastRatio($this->surface, '#382f3d') >= 4.5 ? '#382f3d' : (self::contrastRatio($this->surface, '#000000') >= 4.5 ? '#000000' : '#ffffff');
    }

    /**
     * Text color for light surfaces comes from the theme's own ink (CSS). Only a surface too
     * dark for dark ink needs an override.
     */
    public function surfaceTextOverride(): string|null
    {
        return self::contrastRatio($this->surface, '#382f3d') >= 4.5 ? null : $this->surfaceText();
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
