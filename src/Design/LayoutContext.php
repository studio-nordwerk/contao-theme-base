<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\ModuleModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Navigation\NavigationRenderer;

final readonly class LayoutContext
{
    public function __construct(
        private Connection $connection,
        private DesignRepository $design,
        private ContentUrlGenerator $urls,
        private NavigationRenderer $navigation,
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

        return ['navigation' => $this->navigation->render((int) ($module->nwNavModule ?? 0)), 'button' => $this->button($module, $links), 'brand' => (string) ($theme['name'] ?? 'Ihr Shop'), 'design' => $settings, 'logoPath' => $this->design->logoPath($settings), 'headingFont' => DesignSettings::FONT_PAIRS[$settings->fontPair][0], 'bodyFont' => DesignSettings::FONT_PAIRS[$settings->fontPair][1], 'links' => $links];
    }

    /**
     * Header button from the module (label, target page, hide switch). Without a
     * label or target the theme default applies: the demo's "events" page as
     * "Termine", if the theme has one.
     *
     * @param array<string, string> $links
     *
     * @return array{label: string, url: string}|null
     */
    private function button(ModuleModel $module, array $links): array|null
    {
        if ($module->nwHideButton ?? false) {
            return null;
        }
        $page = PageModel::findPublishedById((int) ($module->nwButtonPage ?? 0));
        $url = null !== $page ? $this->urls->generate($page) : ($links['events'] ?? null);
        $label = trim((string) ($module->nwButtonLabel ?? '')) ?: (null !== $page ? (string) $page->title : 'Termine');

        return null !== $url ? ['label' => $label, 'url' => $url] : null;
    }
}
