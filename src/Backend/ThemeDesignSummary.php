<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\System;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Design\DesignRepository;
use Nordwerk\ThemeBaseBundle\Design\DesignSettings;

final class ThemeDesignSummary
{
    public static function generate(string $themeKey): string
    {
        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');
        /** @var DesignRepository $repository */
        $repository = System::getContainer()->get(DesignRepository::class);
        $themes = [];
        foreach ($connection->fetchAllAssociative('SELECT id, name FROM tl_theme WHERE nwThemeKey = ? ORDER BY id', [$themeKey]) as $theme) {
            $settings = $repository->load((int) $theme['id']);
            $theme['values'] = [
                'Logo' => null === $settings->logo ? 'Theme-Name als Wortmarke' : 'Bild aus der Dateiverwaltung',
                'Akzentfarbe' => $settings->accent,
                'Flächenfarbe' => $settings->surface,
                'Schriftpaar' => implode(' / ', array_unique(DesignSettings::FONT_PAIRS[$settings->fontPair])),
                'Kopfzeile' => DesignSettings::HEADER_STYLES[$settings->headerStyle],
                'Hinweisleiste' => '' === $settings->announcement ? 'Ausgeblendet' : $settings->announcement,
                'Ecken' => DesignSettings::RADII[$settings->radius],
                'Buttons' => DesignSettings::BUTTON_SHAPES[$settings->buttonShape],
            ];
            foreach (['paper' => 'Seitenhintergrund', 'text' => 'Textfarbe', 'tone1' => 'Fläche 1', 'tone2' => 'Fläche 2'] as $field => $label) {
                $theme['values'][$label] = $settings->effectiveSurfaces()[$field].('' === $settings->{$field} ? ' (Vorgabe des Themes)' : '');
            }
            $theme['editUrl'] = html_entity_decode(Backend::addToUrl('do=themes&act=edit&id='.(int) $theme['id'], true, ['step', 'theme', 'imported', 'saved']), ENT_QUOTES, 'UTF-8');
            $themes[] = $theme;
        }
        $template = new BackendTemplate('be_nw_theme_summary');
        $template->themes = $themes;

        return $template->parse();
    }
}
