<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\System;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackages;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * On a site without pages the backend start page points administrators to the
 * demo import of each installed theme package (only they may run it).
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
        if (!$this->security->isGranted('ROLE_ADMIN')) {
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
