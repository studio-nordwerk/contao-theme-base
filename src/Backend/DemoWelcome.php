<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Contao\BackendTemplate;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\System;

/**
 * First step on a fresh site: start with the demo website or without it.
 */
final class DemoWelcome
{
    /**
     * @param string       $action   setup URL the two forms post to
     * @param list<string> $contents what the demo creates, shown as a list
     */
    public static function generate(string $action, string $product, array $contents): string
    {
        /** @var ContaoCsrfTokenManager $tokens */
        $tokens = System::getContainer()->get('contao.csrf.token_manager');
        $template = new BackendTemplate('be_nw_demo_welcome');
        $template->setData([
            'action' => $action,
            'product' => $product,
            'contents' => $contents,
            'requestToken' => $tokens->getDefaultTokenValue(),
            'lang' => $GLOBALS['TL_LANG']['nw_theme']['welcome'],
        ]);

        return $template->parse();
    }
}
