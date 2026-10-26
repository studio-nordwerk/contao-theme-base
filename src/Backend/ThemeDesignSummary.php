<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\System;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Design\DesignRepository;
use Nordwerk\ThemeBaseBundle\Design\DesignSettings;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * The current design values of the themes of a package, with a link to the theme form.
 */
final class ThemeDesignSummary
{
    public static function generate(string $themeKey): string
    {
        System::loadLanguageFile('tl_theme');
        $container = System::getContainer();
        /** @var Connection $connection */
        $connection = $container->get('database_connection');
        /** @var DesignRepository $repository */
        $repository = $container->get(DesignRepository::class);
        /** @var Security $security */
        $security = $container->get('security.helper');
        $lang = $GLOBALS['TL_LANG']['tl_theme'];
        $summary = $GLOBALS['TL_LANG']['nw_theme']['summary'];
        $label = static fn (string $field): string => (string) ($lang[$field][0] ?? $field);
        $themes = [];

        foreach ($connection->fetchAllAssociative('SELECT id, name FROM tl_theme WHERE nwThemeKey = ? ORDER BY id', [$themeKey]) as $theme) {
            $settings = $repository->load((int) $theme['id']);
            $theme['values'] = [
                $label('nwLogo') => null === $settings->logo ? $summary['wordmark'] : $summary['logo'],
                $label('nwAccent') => $settings->accent,
                $label('nwSurface') => $settings->surface,
                $label('nwFontPair') => (string) ($lang['nwFontPairOptions'][$settings->fontPair] ?? implode(' / ', array_unique(DesignSettings::FONT_PAIRS[$settings->fontPair]))),
                $label('nwHeaderStyle') => (string) ($lang['nwHeaderStyleOptions'][$settings->headerStyle] ?? $settings->headerStyle),
                $label('nwAnnouncement') => '' === $settings->announcement ? $summary['hidden'] : $settings->announcement,
                $label('nwRadius') => (string) ($lang['nwRadiusOptions'][$settings->radius] ?? $settings->radius),
                $label('nwButtonShape') => (string) ($lang['nwButtonShapeOptions'][$settings->buttonShape] ?? $settings->buttonShape),
            ];

            foreach (['paper' => 'nwPaper', 'text' => 'nwText', 'tone1' => 'nwTone1', 'tone2' => 'nwTone2'] as $field => $column) {
                $theme['values'][$label($column)] = $settings->effectiveSurfaces()[$field].('' === $settings->{$field} ? ' ('.$summary['default'].')' : '');
            }
            // The theme form needs the module "Themes" and the right to edit the theme.
            $theme['editUrl'] = $security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'themes') ? html_entity_decode(Backend::addToUrl('do=themes&act=edit&id='.(int) $theme['id'], true, ['theme', 'imported']), ENT_QUOTES, 'UTF-8') : null;
            $themes[] = $theme;
        }
        $template = new BackendTemplate('be_nw_theme_summary');
        $template->setData(['themes' => $themes, 'lang' => $summary]);

        return $template->parse();
    }
}
