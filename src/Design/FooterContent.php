<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;

/**
 * Footer contents from the module settings: free text, column headings, extra
 * pages and the demo note. Empty headings keep the theme's defaults.
 */
final readonly class FooterContent
{
    public function __construct(private ContentUrlGenerator $urls)
    {
    }

    /**
     * @return array{text: string, navHeadline: string, legalHeadline: string, pages: list<array{title: string, url: string}>, demoNote: bool}
     */
    public function build(ModuleModel $module): array
    {
        $pages = [];
        $ids = array_map('intval', StringUtil::deserialize($module->nwFooterPages ?? null, true));

        foreach ($ids as $id) {
            $page = PageModel::findById($id);
            if (null === $page || !$page->published || $page->protected) {
                continue;
            }

            try {
                $pages[] = ['title' => (string) $page->title, 'url' => $this->urls->generate($page)];
            } catch (\Throwable) {
                continue;
            }
        }

        return [
            'text' => trim((string) ($module->nwFooterText ?? '')),
            'navHeadline' => trim((string) ($module->nwFooterNavHeadline ?? '')),
            'legalHeadline' => trim((string) ($module->nwFooterLegalHeadline ?? '')),
            'pages' => $pages,
            'demoNote' => '1' === (string) ($module->nwDemoNote ?? ''),
        ];
    }
}
