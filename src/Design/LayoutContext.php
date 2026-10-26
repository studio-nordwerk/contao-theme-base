<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\ModuleModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Navigation\NavigationRenderer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LayoutContext
{
    public function __construct(
        private Connection $connection,
        private DesignRepository $design,
        private ContentUrlGenerator $urls,
        private NavigationRenderer $navigation,
        private RequestStack $requests,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(ModuleModel $module): array
    {
        $settings = $this->design->load((int) $module->pid);
        $logoPath = $this->design->logoPath($settings);

        return [
            'navigation' => $this->navigation->render((int) ($module->nwNavModule ?? 0)),
            'button' => $this->button($module),
            'start' => $this->start(),
            'brand' => (string) ($this->connection->fetchOne('SELECT name FROM tl_theme WHERE id = ?', [(int) $module->pid]) ?: $this->translator->trans('nw_theme.brand_fallback')),
            'design' => $settings,
            // Only a public file can be shown; the theme form checks this when saving.
            'logo' => null !== $logoPath ? $settings->logo : null,
            'logoSize' => ImageSize::orDefault($settings->logoSize, '_nw_logo'),
            'logoPath' => $logoPath,
            'headingFont' => DesignSettings::FONT_PAIRS[$settings->fontPair][0],
            'bodyFont' => DesignSettings::FONT_PAIRS[$settings->fontPair][1],
        ];
    }

    /**
     * The logo links to the start page of the website the visitor is on: the first
     * published page below its root.
     */
    private function start(): string
    {
        $current = $this->requests->getMainRequest()?->attributes->get('pageModel');
        $start = $current instanceof PageModel ? PageModel::findFirstPublishedByPid((int) $current->rootId) : null;

        return null !== $start ? $this->urls->generate($start) : '/';
    }

    /**
     * Header button from the module: target page, optional label, hide switch.
     * Without a target there is no button.
     *
     * @return array{label: string, url: string}|null
     */
    private function button(ModuleModel $module): array|null
    {
        $page = $module->nwHideButton ?? false ? null : PageModel::findPublishedById((int) ($module->nwButtonPage ?? 0));
        if (null === $page) {
            return null;
        }

        return ['label' => trim((string) ($module->nwButtonLabel ?? '')) ?: (string) $page->title, 'url' => $this->urls->generate($page)];
    }
}
