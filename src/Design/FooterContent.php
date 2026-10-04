<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\ModuleModel;
use Nordwerk\ThemeBaseBundle\Navigation\NavigationRenderer;

/**
 * Footer contents from the module settings: free text, column headings, the two
 * navigation modules and the demo note. Empty headings keep the theme's defaults.
 */
final readonly class FooterContent
{
    public function __construct(private NavigationRenderer $navigation)
    {
    }

    /**
     * @return array{text: string, navHeadline: string, legalHeadline: string, nav: string, legal: string, demoNote: bool}
     */
    public function build(ModuleModel $module): array
    {
        return [
            'text' => trim((string) ($module->nwFooterText ?? '')),
            'navHeadline' => trim((string) ($module->nwFooterNavHeadline ?? '')),
            'legalHeadline' => trim((string) ($module->nwFooterLegalHeadline ?? '')),
            'nav' => $this->navigation->render((int) ($module->nwFooterNavModule ?? 0)),
            'legal' => $this->navigation->render((int) ($module->nwFooterLegalModule ?? 0)),
            'demoNote' => '1' === (string) ($module->nwDemoNote ?? ''),
        ];
    }
}
