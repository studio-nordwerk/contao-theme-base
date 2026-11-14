<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * The style presets bundled with theme-base (data/styles.json, copied from the Nordwerk
 * catalog by scripts/styles/sync-styles.mjs), plus the self-hosted fonts.
 *
 * Two kinds of axes: every style keeps its own exact palette, typeset and shape
 * (ids of the first style using them), so a whole style looks exactly like its
 * profile; editors pick from the short curated lists of the catalog (axes.json,
 * own ids such as "nacht"). Ids of both kinds resolve, and so does a style id, as
 * links and saved values of the first version used them.
 *
 * A curated option stands for one block (its "from"). Together with the style
 * that block belongs to it is that block exactly (forStyle()): a typeset may
 * scale its profile's sizes down for mixing, and a shape leaves the spacing out,
 * yet a whole style keeps all its values.
 */
final class StyleCatalog
{
    public const DATA = __DIR__.'/../../data/styles.json';

    public const FONT_DIRECTORY = 'bundles/themebase/fonts';

    /**
     * Whole styles the visitor preview offers first: far apart in color, type and shape.
     */
    public const FEATURED = ['salbei-ruhig', 'nacht-gold', 'sand-editorial', 'koralle-freundlich', 'werkbank-blau', 'tinte-klar', 'studio-still', 'beere-mutig'];

    private static self|null $bundled = null;

    /**
     * @var array<string, Style>
     */
    private array $styles = [];

    /**
     * @var array<string, Palette>
     */
    private array $palettes = [];

    /**
     * @var array<string, Typeset>
     */
    private array $typesets = [];

    /**
     * @var array<string, Shape>
     */
    private array $shapes = [];

    /**
     * @var array{palette: array<string, Palette>, typeset: array<string, Typeset>, shape: array<string, Shape>}
     */
    private array $curated = ['palette' => [], 'typeset' => [], 'shape' => []];

    /**
     * @var array{palette: array<string, string>, typeset: array<string, string>, shape: array<string, string>} curated id => block id
     */
    private array $from = ['palette' => [], 'typeset' => [], 'shape' => []];

    /**
     * @var array<string, string> family => file name of its stylesheet, without ".css"
     */
    private array $fonts = [];

    public function __construct(string $file = self::DATA)
    {
        $data = json_decode((string) file_get_contents($file), true, 32, JSON_THROW_ON_ERROR);
        if (!\is_array($data)) {
            throw new \InvalidArgumentException('The style data must be an object.');
        }
        $list = static fn (string $key): array => array_filter(\is_array($data[$key] ?? null) ? $data[$key] : [], \is_array(...));

        foreach ($list('palettes') as $entry) {
            $palette = Palette::fromArray($entry);
            $this->palettes[$palette->id] = $palette;
        }

        foreach ($list('typesets') as $entry) {
            $typeset = Typeset::fromArray($entry);
            $this->typesets[$typeset->id] = $typeset;
        }

        foreach ($list('shapes') as $entry) {
            $shape = Shape::fromArray($entry);
            $this->shapes[$shape->id] = $shape;
        }

        foreach ($list('styles') as $entry) {
            $id = (string) ($entry['id'] ?? '');
            $palette = $this->palettes[(string) ($entry['palette'] ?? '')] ?? null;
            $typeset = $this->typesets[(string) ($entry['typeset'] ?? '')] ?? null;
            $shape = $this->shapes[(string) ($entry['shape'] ?? '')] ?? null;
            if (null === $palette || null === $typeset || null === $shape) {
                throw new \InvalidArgumentException(\sprintf('Style "%s" names an axis the data does not hold.', $id));
            }
            $this->styles[$id] = new Style($id, (string) ($entry['label'] ?? ''), (string) ($entry['note'] ?? ''), $palette, $typeset, $shape);
        }

        $curated = \is_array($data['curated'] ?? null) ? $data['curated'] : [];
        $entries = static fn (string $key): array => array_filter(\is_array($curated[$key] ?? null) ? $curated[$key] : [], \is_array(...));

        // A curated option is the block of one profile under its own id, name and description.
        foreach ($entries('palettes') as $entry) {
            $from = $this->palettes[(string) ($entry['from'] ?? '')] ?? throw new \InvalidArgumentException('A curated palette names an unknown block.');
            $palette = new Palette((string) ($entry['id'] ?? ''), (string) ($entry['label'] ?? ''), (string) ($entry['description'] ?? ''), $from->colors, $from->states);
            $this->curated['palette'][$palette->id] = $palette;
            $this->from['palette'][$palette->id] = $from->id;
        }

        foreach ($entries('typesets') as $entry) {
            $from = $this->typesets[(string) ($entry['from'] ?? '')] ?? throw new \InvalidArgumentException('A curated typeset names an unknown block.');
            // "type" replaces single sizes of the profile's scale.
            $typeset = new Typeset((string) ($entry['id'] ?? ''), (string) ($entry['label'] ?? ''), (string) ($entry['description'] ?? ''), $from->fonts, [...$from->type, ...StyleValues::strings($entry['type'] ?? null)], $from->measure);
            $this->curated['typeset'][$typeset->id] = $typeset;
            $this->from['typeset'][$typeset->id] = $from->id;
        }

        foreach ($entries('shapes') as $entry) {
            $from = $this->shapes[(string) ($entry['from'] ?? '')] ?? throw new \InvalidArgumentException('A curated shape names an unknown block.');
            // Without spacing: picking a shape keeps the spacing of the theme or style.
            $shape = new Shape((string) ($entry['id'] ?? ''), (string) ($entry['label'] ?? ''), (string) ($entry['description'] ?? ''), $from->radius, [], $from->shadow);
            $this->curated['shape'][$shape->id] = $shape;
            $this->from['shape'][$shape->id] = $from->id;
        }

        foreach ($this->styles as $id => $style) {
            $this->styles[$id] = new Style($id, $style->label, $style->note, $style->palette, $style->typeset, $style->shape, array_filter(['palette' => $this->styleCuratedId('palette', $style->palette), 'typeset' => $this->styleCuratedId('typeset', $style->typeset), 'shape' => $this->styleCuratedId('shape', $style->shape)]));
        }

        foreach (\is_array($data['fonts'] ?? null) ? $data['fonts'] : [] as $family => $slug) {
            if (\is_string($family) && \is_string($slug) && preg_match('/^[a-z0-9-]+$/', $slug)) {
                $this->fonts[$family] = $slug;
            }
        }
    }

    /**
     * The bundled catalog for value objects without a container (DesignSettings).
     */
    public static function bundled(): self
    {
        return self::$bundled ??= new self();
    }

    public function get(string $id): Style|null
    {
        return '' === $id ? null : $this->styles[$id] ?? null;
    }

    /**
     * A curated palette, a style's own palette by its block id, or the palette of a
     * style by the style's id.
     */
    public function palette(string $id): Palette|null
    {
        return '' === $id ? null : $this->curated['palette'][$id] ?? $this->palettes[$id] ?? $this->get($id)?->palette;
    }

    public function typeset(string $id): Typeset|null
    {
        return '' === $id ? null : $this->curated['typeset'][$id] ?? $this->typesets[$id] ?? $this->get($id)?->typeset;
    }

    public function shape(string $id): Shape|null
    {
        return '' === $id ? null : $this->curated['shape'][$id] ?? $this->shapes[$id] ?? $this->get($id)?->shape;
    }

    /**
     * The axis a theme shows for an id chosen in the form or the preview: the style's
     * own block where the id is the curated option made from it, else as palette().
     *
     * @param 'palette'|'typeset'|'shape' $axis
     */
    public function forStyle(string $axis, string $id, Style|null $style): Palette|Shape|Typeset|null
    {
        if (null !== $style && '' !== $id && ($this->from[$axis][$id] ?? null) === $style->{$axis}->id) {
            return $style->{$axis};
        }

        return $this->{$axis}($id);
    }

    /**
     * The curated option with exactly the values of a style's own axis, if there is one.
     *
     * @param 'palette'|'typeset'|'shape' $axis
     */
    public function curatedId(string $axis, Palette|Shape|Typeset $value): string
    {
        foreach ($this->curated[$axis] as $id => $option) {
            if ($option->tokens() === $value->tokens()) {
                return $id;
            }
        }

        return '';
    }

    /**
     * The curated option a style shows for its own axis: one with the same values,
     * else the one made from its block (shown as that option, rendered as the block).
     *
     * @param 'palette'|'typeset'|'shape' $axis
     */
    private function styleCuratedId(string $axis, Palette|Shape|Typeset $value): string
    {
        return $this->curatedId($axis, $value) ?: (string) array_search($value->id, $this->from[$axis], true);
    }

    /**
     * @return array<string, Style> by id, sorted by label
     */
    public function all(): array
    {
        return $this->styles;
    }

    /**
     * The curated palettes editors pick from, in the catalog's order.
     *
     * @return array<string, Palette>
     */
    public function palettes(): array
    {
        return $this->curated['palette'];
    }

    /**
     * @return array<string, Typeset>
     */
    public function typesets(): array
    {
        return $this->curated['typeset'];
    }

    /**
     * @return array<string, Shape>
     */
    public function shapes(): array
    {
        return $this->curated['shape'];
    }

    /**
     * Every palette a page can show: the curated ones and the styles' own.
     *
     * @return array<string, Palette>
     */
    public function allPalettes(): array
    {
        return [...$this->curated['palette'], ...$this->palettes];
    }

    /**
     * @return array<string, Typeset>
     */
    public function allTypesets(): array
    {
        return [...$this->curated['typeset'], ...$this->typesets];
    }

    /**
     * @return array<string, Shape>
     */
    public function allShapes(): array
    {
        return [...$this->curated['shape'], ...$this->shapes];
    }

    /**
     * @return array<string, Style> the styles of FEATURED the catalog holds
     */
    public function featured(): array
    {
        return array_filter(array_map($this->get(...), array_combine(self::FEATURED, self::FEATURED)));
    }

    /**
     * Options for the theme form: id => label.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        return array_map(static fn (Style $style): string => $style->label, $this->styles);
    }

    /**
     * @return array<string, string>
     */
    public function paletteOptions(): array
    {
        return array_map(static fn (Palette $palette): string => $palette->label.' – '.$palette->description, $this->curated['palette']);
    }

    /**
     * @return array<string, string>
     */
    public function typesetOptions(): array
    {
        return array_map(static fn (Typeset $typeset): string => $typeset->label.' – '.$typeset->description, $this->curated['typeset']);
    }

    /**
     * @return array<string, string>
     */
    public function shapeOptions(): array
    {
        return array_map(static fn (Shape $shape): string => $shape->label.' – '.$shape->description, $this->curated['shape']);
    }

    /**
     * Public paths of the font stylesheets a typeset (or a style's typeset) needs;
     * one per family, only for families with bundled files.
     *
     * @return list<string>
     */
    public function fontStylesheets(Style|Typeset $typeset): array
    {
        $stylesheets = [];

        foreach ($typeset->families() as $family) {
            if (isset($this->fonts[$family])) {
                $stylesheets[] = self::FONT_DIRECTORY.'/'.$this->fonts[$family].'.css';
            }
        }

        return $stylesheets;
    }
}
