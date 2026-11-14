<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * Color axis of a style: the palette with its states (error, success). Picking it
 * fills the color fields of the theme form; the contrast rule hangs on this axis.
 */
final readonly class Palette
{
    private const COLORS = ['surface', 'surfaceRaised', 'ink', 'inkSoft', 'line', 'brand', 'brandInk'];

    /**
     * @param array<string, string> $colors
     * @param array<string, string> $states
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $description,
        public array $colors,
        public array $states = [],
    ) {
        StyleValues::id($id);
        StyleValues::require($id, 'color', $colors, self::COLORS);

        foreach ([...array_values($colors), ...array_values($states)] as $color) {
            DesignSettings::normalizeColor($color);
        }
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self((string) ($data['id'] ?? ''), (string) ($data['label'] ?? ''), (string) ($data['description'] ?? ''), StyleValues::strings($data['colors'] ?? null), StyleValues::strings($data['states'] ?? null));
    }

    public function action(): string
    {
        return DesignSettings::normalizeColor($this->colors['action'] ?? $this->colors['brand']);
    }

    public function actionInk(): string
    {
        return DesignSettings::normalizeColor($this->colors['actionInk'] ?? $this->colors['brandInk']);
    }

    /**
     * Tiles (free days, chosen time, image placeholders) sit on the raised panel, so
     * they are the raised surface moved 8 % towards the ink.
     */
    public function tile(): string
    {
        return Style::mix($this->color('surfaceRaised'), $this->color('ink'), 0.08);
    }

    /**
     * The surfaces the theme form validates, as DesignSettings::effectiveSurfaces().
     *
     * @return array{paper: string, text: string, tone1: string, tone2: string}
     */
    public function surfaces(): array
    {
        return ['paper' => $this->color('surface'), 'text' => $this->color('ink'), 'tone1' => $this->tile(), 'tone2' => $this->color('surfaceRaised')];
    }

    /**
     * Color fields of the theme form this palette fills in.
     *
     * @return array<string, string> tl_theme column => value
     */
    public function themeFields(): array
    {
        $surfaces = $this->surfaces();

        return [
            'nwAccent' => $this->action(),
            'nwSurface' => $this->color('surface'),
            'nwPaper' => $surfaces['paper'],
            'nwText' => $surfaces['text'],
            'nwTone1' => $surfaces['tone1'],
            'nwTone2' => $surfaces['tone2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function tokens(): array
    {
        $ink = $this->color('ink');
        $error = DesignSettings::normalizeColor($this->states['error'] ?? '#a63a2b');

        return [
            '--theme-paper' => $this->color('surface'),
            '--nw-page' => $this->color('surface'),
            '--nw-surface' => $this->color('surface'),
            '--theme-ink' => $ink,
            '--theme-surface-ink' => $ink,
            '--theme-muted' => $this->color('inkSoft'),
            '--nw-muted' => $this->color('inkSoft'),
            '--nw-border' => $this->color('line'),
            // Hairlines are too faint for input borders; move them a third towards the ink.
            '--nw-field-border' => Style::mix($this->color('line'), $ink, 0.35),
            '--nw-accent' => $this->action(),
            '--nw-accent-contrast' => $this->actionInk(),
            '--nw-focus' => $this->color('brand'),
            '--nw-studio-signal' => $this->color('brand'),
            '--nw-studio-card' => $this->color('surfaceRaised'),
            '--theme-tile' => $this->tile(),
            '--theme-callout' => $this->color('surfaceRaised'),
            '--theme-success' => DesignSettings::normalizeColor($this->states['success'] ?? '#2e6b3f'),
            '--nw-error' => $error,
            '--nw-error-soft' => isset($this->states['errorSoft']) ? DesignSettings::normalizeColor($this->states['errorSoft']) : Style::mix($this->color('surface'), $error, 0.14),
        ];
    }

    private function color(string $role): string
    {
        return DesignSettings::normalizeColor($this->colors[$role]);
    }
}
