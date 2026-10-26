<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\System;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackages;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * On a site without pages the backend start page points to the demo import of
 * each installed theme package, for users who may open "Theme einrichten".
 */
#[AsHook('getSystemMessages')]
final readonly class EmptySiteMessage
{
    public function __construct(
        private EmptySite $site,
        private ThemePackages $packages,
        private Security $security,
    ) {
    }

    public function __invoke(): string
    {
        if (!$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, ThemeSetupModule::MODULE)) {
            return '';
        }
        System::loadLanguageFile('default');
        $messages = '';

        foreach ($this->packages->all() as $key => $package) {
            $offer = (string) ($GLOBALS['TL_LANG']['nw_theme']['packages'][$key]['offer'] ?? '');
            $messages .= $this->site->message($key, $offer, 'contao?do='.ThemeSetupModule::MODULE.'&theme='.rawurlencode($key));
        }

        return $messages;
    }
}
