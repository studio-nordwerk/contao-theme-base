<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Theme;

/**
 * A theme package whose stylesheets follow all form and type tokens of
 * theme-base, so the style presets (StyleCatalog) work with it. Only these themes
 * show the field "Stil" in the theme form, and only for them the base writes the
 * style's tokens.
 */
interface StylableThemePackage extends ThemePackage
{
}
