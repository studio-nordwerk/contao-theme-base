<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * Shape axis of a style: radii, button shape and shadow. A style's own shape also
 * holds its spacing, which belongs to the whole style: a curated shape has none,
 * and picking a shape keeps the spacing there is (spaceTokens()). Missing spacing
 * or shadow keeps the values of the theme package.
 */
final readonly class Shape
{
    /**
     * @param array<string, string>      $radius
     * @param array<string, string>      $space
     * @param array<string, string>|null $shadow null: no shadow key; empty: flat
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $description,
        public array $radius,
        public array $space = [],
        public array|null $shadow = null,
    ) {
        StyleValues::id($id);
        StyleValues::require($id, 'radius', $radius, ['button', 'control', 'card']);
        StyleValues::safe($id, $radius, $space, $shadow ?? []);
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // A shape without shadows says so with "shadow": null.
        return new self((string) ($data['id'] ?? ''), (string) ($data['label'] ?? ''), (string) ($data['description'] ?? ''), StyleValues::strings($data['radius'] ?? null), StyleValues::strings($data['space'] ?? null), \array_key_exists('shadow', $data) ? StyleValues::strings($data['shadow']) : null);
    }

    /**
     * Panel and image radius fall back to the card radius, like the catalog.
     *
     * @return array<string, string>
     */
    public function tokens(): array
    {
        $tokens = [
            '--nw-radius-button' => $this->radius['button'],
            '--nw-radius-control' => $this->radius['control'],
            '--nw-radius-card' => $this->radius['card'],
            '--theme-radius' => $this->radius['card'],
            '--nw-radius-panel' => $this->radius['panel'] ?? $this->radius['card'],
            '--nw-radius-media' => $this->radius['image'] ?? $this->radius['card'],
        ];
        if (null !== $this->shadow) {
            $tokens['--nw-shadow-panel'] = $this->shadow['card'] ?? 'none';
        }

        return $tokens;
    }

    /**
     * Section spacing, content width and gutter of the style this shape belongs to.
     *
     * @return array<string, string>
     */
    public function spaceTokens(): array
    {
        $tokens = [];

        foreach (['section' => '--theme-section-space', 'content' => '--nw-content-width', 'gutter' => '--nw-gutter'] as $key => $variable) {
            if (isset($this->space[$key])) {
                $tokens[$variable] = $this->space[$key];
            }
        }

        return $tokens;
    }
}
