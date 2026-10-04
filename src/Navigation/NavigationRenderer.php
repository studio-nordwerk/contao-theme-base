<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Navigation;

use Contao\Controller;
use Contao\ModuleModel;

/**
 * Renders a core navigation module ("Navigationsmenü" or "Individuelle
 * Navigation") inside the theme's header and footer. Contao decides which pages
 * appear; the theme only supplies the markup around the list.
 */
final readonly class NavigationRenderer
{
    public const TYPES = ['navigation', 'customnav'];

    public function render(int $moduleId): string
    {
        $module = $moduleId > 0 ? ModuleModel::findById($moduleId) : null;
        if (null === $module || !\in_array($module->type, self::TYPES, true)) {
            return '';
        }

        // The header prints the menu up to three times (desktop, sheet, noscript) and
        // brings its own <nav>, so the module contributes the list only.
        $module = $module->cloneDetached();
        $module->customTpl = 'mod_nw_navigation';
        $module->navigationTpl = $module->navigationTpl ?: 'nav_nw';

        return trim(Controller::getFrontendModule($module));
    }
}
