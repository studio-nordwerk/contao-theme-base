<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * The CSS variables a theme writes into the <head>: the tokens of the chosen
 * style first, the fields of the theme form after them, so a value changed by
 * hand wins. Every value is safe to output raw inside a style element: colors are
 * normalized hex, radii and font pairs come from fixed lists, and Style accepts
 * no ";", braces or "<".
 */
final class DesignVariables
{
    /**
     * @return array<string, string> variable => value; a later key overrides an earlier one
     */
    public static function of(DesignSettings $design, bool $stylable): array
    {
        return array_merge(...array_values(self::groups($design, $stylable)));
    }

    /**
     * The variables by axis: each axis's tokens, then the form fields that belong to
     * it, and the style's spacing. The groups share no variable, so the visitor
     * preview can lay one rule per axis (and one per style for the spacing) over the
     * theme's own.
     *
     * @param bool $stylable false: the package does not follow all tokens, only the form fields count
     *
     * @return array{palette: array<string, string>, typeset: array<string, string>, shape: array<string, string>, space: array<string, string>}
     */
    public static function groups(DesignSettings $design, bool $stylable): array
    {
        $palette = $stylable ? $design->paletteProfile()?->tokens() ?? [] : [];
        $palette['--nw-accent'] = $design->accent;
        $palette['--nw-accent-contrast'] = $design->accentText();
        $palette['--nw-surface'] = $design->surface;
        if (null !== $override = $design->surfaceTextOverride()) {
            $palette['--theme-surface-ink'] = $override;
        }

        $typeset = $stylable ? $design->typesetProfile()?->tokens() ?? [] : [];
        if (null !== $fonts = $design->fontFamilies()) {
            $typeset['--theme-heading-font'] = "'".$fonts[0]."'";
            $typeset['--theme-body-font'] = "'".$fonts[1]."'";
        }

        return [
            'palette' => [...$palette, ...$design->colorVariables()],
            'typeset' => $typeset,
            'shape' => [...($stylable ? $design->shapeProfile()?->tokens() ?? [] : []), ...$design->shapeVariables()],
            // Spacing comes with the whole style only, whatever shape is picked.
            'space' => $stylable ? $design->spaceTokens() : [],
        ];
    }
}
