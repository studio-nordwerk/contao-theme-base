<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\ModuleModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class LayoutContext
{
    public function __construct(
        private Connection $connection,
        private DesignRepository $design,
        private ContentUrlGenerator $urls,
        private RequestStack $requests = new RequestStack(),
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(ModuleModel $module): array
    {
        $theme = $this->connection->fetchAssociative('SELECT name, nwDemoState FROM tl_theme WHERE id = ?', [(int) $module->pid]);
        $manifest = json_decode((string) ($theme['nwDemoState'] ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
        $pages = \is_array($manifest) ? ($manifest['pages'] ?? []) : [];
        $settings = $this->design->load((int) $module->pid);
        $links = [];

        foreach (\is_array($pages) ? $pages : [] as $name => $id) {
            $page = PageModel::findById((int) $id);
            if (null !== $page && $page->published) {
                $links[$name] = $this->urls->generate($page);
            }
        }

        $navigation = [];
        // The current page and its parents mark the active menu item.
        $current = $this->requests->getMainRequest()?->attributes->get('pageModel');
        $trail = $current instanceof PageModel ? array_map('intval', [...((array) $current->trail), (int) $current->id]) : [];

        foreach (PageModel::findPublishedByPid((int) ($manifest['root'] ?? 0)) ?? [] as $page) {
            if (!$page->hide && !$page->protected && 'regular' === $page->type) {
                $navigation[] = ['title' => (string) $page->title, 'url' => $this->urls->generate($page), 'active' => \in_array((int) $page->id, $trail, true)];
            }
        }

        return ['navigation' => $navigation, 'brand' => (string) ($theme['name'] ?? 'Ihr Shop'), 'design' => $settings, 'logoPath' => $this->design->logoPath($settings), 'headingFont' => DesignSettings::FONT_PAIRS[$settings->fontPair][0], 'bodyFont' => DesignSettings::FONT_PAIRS[$settings->fontPair][1], 'links' => $links];
    }
}
