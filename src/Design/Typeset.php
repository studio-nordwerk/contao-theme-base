<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * Type axis of a style: font families with the type scale that belongs to them
 * (sizes, leading, tracking) and the measure. A typeset without a scale keeps the
 * sizes of the theme package.
 */
final readonly class Typeset
{
    /**
     * @param array<string, string> $fonts
     * @param array<string, string> $type
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $description,
        public array $fonts,
        public array $type = [],
        public string $measure = '',
    ) {
        StyleValues::id($id);
        StyleValues::require($id, 'font', $fonts, ['display', 'body']);
        StyleValues::safe($id, $fonts, $type, '' === $measure ? [] : ['measure' => $measure]);
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self((string) ($data['id'] ?? ''), (string) ($data['label'] ?? ''), (string) ($data['description'] ?? ''), StyleValues::strings($data['fonts'] ?? null), StyleValues::strings($data['type'] ?? null), \is_string($data['measure'] ?? null) ? $data['measure'] : '');
    }

    /**
     * Optional roles fall back the way the catalog does (accent font → display,
     * meta font → body); a missing type value keeps the theme's own. The measure
     * has no counterpart in the themes yet.
     *
     * @return array<string, string>
     */
    public function tokens(): array
    {
        $tokens = [
            '--theme-heading-font' => $this->fonts['display'],
            // The studio theme sets headlines in its own variable.
            '--nw-studio-sans' => $this->fonts['display'],
            '--theme-body-font' => $this->fonts['body'],
            '--nw-font-accent' => $this->fonts['accent'] ?? $this->fonts['display'],
            '--nw-font-meta' => $this->fonts['meta'] ?? $this->fonts['body'],
        ];

        foreach (['display' => ['--nw-text-display', '--nw-text-hero', '--nw-text-hero-narrow'], 'title' => ['--nw-text-title', '--nw-text-section'], 'lead' => ['--nw-text-lead'], 'leadingDisplay' => ['--nw-leading-heading'], 'trackingDisplay' => ['--nw-tracking-heading']] as $key => $variables) {
            foreach (isset($this->type[$key]) ? $variables : [] as $variable) {
                $tokens[$variable] = $this->type[$key];
            }
        }

        return $tokens;
    }

    /**
     * Font families named in quotes in the stacks, without duplicates. Families without
     * bundled files (system fonts such as Helvetica Neue) fall back along the stack.
     *
     * @return list<string>
     */
    public function families(): array
    {
        $families = [];

        foreach (['display', 'body', 'accent', 'meta'] as $role) {
            preg_match_all('/"([^"]+)"/', $this->fonts[$role] ?? '', $matches);
            array_push($families, ...$matches[1]);
        }

        return array_values(array_unique($families));
    }
}
