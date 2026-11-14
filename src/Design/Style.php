<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * A style preset from the Nordwerk catalog: one palette, one typeset and one
 * shape (data/styles.json). Picking it in the theme form sets all three axes; an
 * editor can change each one afterwards.
 *
 * Roles the theme has no counterpart for stay unused: the dark tone layer,
 * colors.brand apart from focus and dots (the theme has one accent for buttons),
 * the measure and the meta type size.
 */
final readonly class Style
{
    public function __construct(
        public string $id,
        public string $label,
        public string $note,
        public Palette $palette,
        public Typeset $typeset,
        public Shape $shape,
        /**
         * Curated options with exactly the values of this style's axes: axis => id.
         *
         * @var array<'palette'|'shape'|'typeset', string>
         */
        public array $curated = [],
    ) {
        StyleValues::id($id);
    }

    /**
     * A profile in the catalog's flat form (colors, fonts, radius, type, space,
     * shadow); its axes take the style's id.
     *
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $id = (string) ($data['id'] ?? '');
        $label = (string) ($data['label'] ?? '');
        $space = StyleValues::strings($data['space'] ?? null);

        return new self(
            $id,
            $label,
            (string) ($data['note'] ?? ''),
            new Palette($id, $label, '', StyleValues::strings($data['colors'] ?? null), StyleValues::strings($data['states'] ?? null)),
            new Typeset($id, $label, '', StyleValues::strings($data['fonts'] ?? null), StyleValues::strings($data['type'] ?? null), $space['lead'] ?? ''),
            new Shape($id, $label, '', StyleValues::strings($data['radius'] ?? null), array_intersect_key($space, ['section' => true, 'content' => true, 'gutter' => true]), \array_key_exists('shadow', $data) ? StyleValues::strings($data['shadow']) : null),
        );
    }

    /**
     * Who the style is made for: the note up to its colon ("Gesundheit,
     * Therapie, Betreuung").
     */
    public function audience(): string
    {
        $colon = strpos($this->note, ':');

        return trim(false === $colon ? '' : substr($this->note, 0, $colon));
    }

    public function action(): string
    {
        return $this->palette->action();
    }

    public function actionInk(): string
    {
        return $this->palette->actionInk();
    }

    /**
     * @return array{paper: string, text: string, tone1: string, tone2: string}
     */
    public function surfaces(): array
    {
        return $this->palette->surfaces();
    }

    /**
     * Values the theme form takes over when the style is picked: the palette's
     * colors; font pair, corners and buttons back to empty so the typeset and the
     * shape apply; the three axes on "as the style" unless a curated option has
     * exactly the style's values. Every field stays editable.
     *
     * @return array<string, string> tl_theme column => value
     */
    public function themeFields(): array
    {
        return [
            ...$this->palette->themeFields(),
            'nwFontPair' => '',
            'nwRadius' => '',
            'nwButtonShape' => '',
            // Empty means "as the style"; a curated option with the same values is shown instead.
            'nwPalette' => $this->curated['palette'] ?? '',
            'nwTypeset' => $this->curated['typeset'] ?? '',
            'nwShape' => $this->curated['shape'] ?? '',
        ];
    }

    /**
     * The style as CSS variables of theme-base and the theme packages.
     *
     * @return array<string, string>
     */
    public function tokens(): array
    {
        return [...$this->palette->tokens(), ...$this->typeset->tokens(), ...$this->shape->tokens(), ...$this->shape->spaceTokens()];
    }

    /**
     * @return list<string>
     */
    public function families(): array
    {
        return $this->typeset->families();
    }

    /**
     * Like color-mix(in srgb, $a, $b $share).
     */
    public static function mix(string $a, string $b, float $share): string
    {
        $a = DesignSettings::normalizeColor($a);
        $b = DesignSettings::normalizeColor($b);
        $mixed = '#';

        foreach ([1, 3, 5] as $offset) {
            $x = hexdec(substr($a, $offset, 2));
            $y = hexdec(substr($b, $offset, 2));
            $mixed .= \sprintf('%02x', (int) round($x + ($y - $x) * $share));
        }

        return $mixed;
    }
}
