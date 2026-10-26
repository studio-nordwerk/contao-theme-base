<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Theme;

use Nordwerk\ThemeBaseBundle\Demo\DemoProvider;

/**
 * A theme package (shop, seminar) built on the theme base. The base loads its
 * stylesheets for every layout of a theme with this key, offers its demo in the
 * backend module "Theme einrichten" and links to the setup of its bundle.
 * Services implementing it are tagged automatically (see ThemeBaseBundle).
 */
interface ThemePackage
{
    public const TAG = 'nordwerk.theme_package';

    /**
     * tl_theme.nwThemeKey of the themes this package creates.
     */
    public function key(): string;

    /**
     * Stylesheets loaded after base.css, as public paths such as
     * "bundles/shoptheme/shop.css".
     *
     * @return list<string>
     */
    public function stylesheets(): array;

    /**
     * Body classes. Classic layouts store them in "cssClass", the Twig layout
     * "page/layout/nw_theme" adds them itself.
     */
    public function cssClass(): string;

    public function demo(): DemoProvider;

    /**
     * Backend module of the bundle the theme is made for, e.g. "mini_shop_setup".
     */
    public function setupModule(): string;
}
