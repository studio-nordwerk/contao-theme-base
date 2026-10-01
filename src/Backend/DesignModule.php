<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Contao\BackendModule;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Controller;
use Contao\Input;
use Contao\System;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Design\DesignRepository;
use Nordwerk\ThemeBaseBundle\Design\DesignSettings;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @property BackendTemplate $Template
 */
class DesignModule extends BackendModule
{
    protected $strTemplate = 'be_nw_theme_design';

    protected function compile(): void
    {
        $user = BackendUser::getInstance();
        if (!$user instanceof BackendUser || !$user->isAdmin) {
            throw new AccessDeniedException('Die Gestaltung ist Administratoren vorbehalten.');
        }
        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');
        /** @var DesignRepository $repository */
        $repository = System::getContainer()->get(DesignRepository::class);
        $themes = $connection->fetchAllAssociative('SELECT id, name FROM tl_theme ORDER BY id');
        $theme = (int) (Input::get('theme') ?: ($themes[0]['id'] ?? 0));
        /** @var RequestStack $requestStack */
        $requestStack = System::getContainer()->get('request_stack');
        $request = $requestStack->getCurrentRequest();
        $error = '';
        $notice = '1' === Input::get('saved') ? 'Gestaltung gespeichert.' : '';
        $actionUrl = ('mini_shop_setup' === Input::get('do') ? 'contao?do=mini_shop_setup&step=design&theme=' : 'contao?do=nw_theme_design&theme=').$theme;
        if ($request?->isMethod('POST') && 'save-design' === $request->request->getString('themeAction')) {
            try {
                $logo = $request->request->getString('logo');
                $binary = preg_match('/^[a-f0-9]{32}$/D', $logo) ? hex2bin($logo) : null;
                $settings = new DesignSettings($request->request->getString('accent'), $request->request->getString('surface'), $request->request->getString('fontPair'), $binary ?: null);
                $repository->save($theme, $settings);
                Controller::redirect($actionUrl.'&saved=1');
            } catch (\InvalidArgumentException $exception) {
                $request->attributes->set('_contao_widget_error', true);
                $error = $exception->getMessage();
            }
        }
        $settings = $repository->load($theme);
        $files = $connection->fetchAllAssociative("SELECT uuid, path FROM tl_files WHERE type = 'file' AND extension IN ('jpg', 'jpeg', 'png', 'webp') ORDER BY path");
        $this->Template->setData(['themes' => $themes, 'themeId' => $theme, 'settings' => $settings, 'files' => $files, 'notice' => $notice, 'error' => $error, 'fontPairs' => DesignSettings::FONT_PAIRS, 'previewUrl' => '/', 'actionUrl' => $actionUrl]);
    }
}
