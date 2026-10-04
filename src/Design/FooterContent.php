<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\ModuleModel;
use Contao\PageModel;
use Nordwerk\ThemeBaseBundle\Navigation\NavigationRenderer;

/**
 * Footer contents from the module settings: free text, column headings, the two
 * navigation modules, the withdrawal page and the demo note. Empty headings keep
 * the theme's defaults.
 */
final readonly class FooterContent
{
    public function __construct(
        private NavigationRenderer $navigation,
        private ContentUrlGenerator $urls,
    ) {
    }

    /**
     * @return array{text: string, navHeadline: string, legalHeadline: string, nav: string, legal: string, withdrawal: string|null, demoNote: bool}
     */
    public function build(ModuleModel $module): array
    {
        $withdrawal = PageModel::findPublishedById((int) ($module->nwWithdrawalPage ?? 0));

        return [
            'text' => trim((string) ($module->nwFooterText ?? '')),
            'navHeadline' => trim((string) ($module->nwFooterNavHeadline ?? '')),
            'legalHeadline' => trim((string) ($module->nwFooterLegalHeadline ?? '')),
            'nav' => $this->navigation->render((int) ($module->nwFooterNavModule ?? 0)),
            'legal' => $this->navigation->render((int) ($module->nwFooterLegalModule ?? 0)),
            'withdrawal' => null !== $withdrawal ? $this->urls->generate($withdrawal) : null,
            'demoNote' => '1' === (string) ($module->nwDemoNote ?? ''),
        ];
    }
}
