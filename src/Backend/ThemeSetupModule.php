<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Contao\BackendModule;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\PageModel;
use Contao\System;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Demo\DemoException;
use Nordwerk\ThemeBaseBundle\Demo\DemoImporter;
use Nordwerk\ThemeBaseBundle\Demo\DemoInventory;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackage;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackages;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Layout → Theme einrichten: demo website and design summary of the installed
 * theme packages. The module has its own right in the user groups ("Erlaubte
 * Module"); the setup assistants of Mini-Shop and Seminar stay untouched and keep
 * their own rights.
 *
 * @property BackendTemplate $Template
 */
final class ThemeSetupModule extends BackendModule
{
    public const MODULE = 'nw_theme_setup';

    protected $strTemplate = 'be_nw_theme_setup';

    protected function compile(): void
    {
        System::loadLanguageFile('default');
        $container = System::getContainer();
        /** @var ThemePackages $registry */
        $registry = $container->get(ThemePackages::class);
        $packages = $registry->all();
        $lang = $GLOBALS['TL_LANG']['nw_theme'];
        if ([] === $packages) {
            $this->Template->setData(['lang' => $lang['setup'], 'packages' => [], 'package' => null]);

            return;
        }
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        $request = $requestStack->getCurrentRequest() ?? new Request();
        $package = $packages[$request->query->getString('theme')] ?? reset($packages);
        $key = $package->key();
        $texts = $this->texts($package);
        $self = 'contao?do='.self::MODULE.'&theme='.rawurlencode($key);
        $imported = $request->query->getString('imported');
        /** @var EmptySite $site */
        $site = $container->get(EmptySite::class);
        /** @var Security $security */
        $security = $container->get('security.helper');
        $setupUrl = $security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, $package->setupModule()) ? 'contao?do='.$package->setupModule() : null;
        // The import creates and replaces pages, articles, products and settings past
        // pagemounts and field rights, so only administrators may run it. Overview and
        // design summary follow the module right.
        $mayImport = $security->isGranted('ROLE_ADMIN');
        $error = '';

        $action = $request->isMethod('POST') ? $request->request->getString('themeAction') : '';
        if (\in_array($action, ['decline-demo', 'import-demo'], true) && !$mayImport) {
            throw new AccessDeniedException('Only administrators may import the theme demo.');
        }
        if ('decline-demo' === $action) {
            $site->declineDemo($key);
            Controller::redirect($setupUrl ?? $self);
        }
        if ('import-demo' === $action) {
            $welcome = 'welcome' === $request->request->getString('origin') && $site->offerDemo($key);
            /** @var DemoImporter $importer */
            $importer = $container->get(DemoImporter::class);

            try {
                $result = $importer->import($package->demo(), '1' === $request->request->getString('overwrite'));
            } catch (DemoException $exception) {
                $error = self::message($exception);
            } catch (\RuntimeException $exception) {
                $error = $exception->getMessage();
            }
            if (isset($result)) {
                Controller::redirect($self.'&imported='.match (true) {
                    $welcome && $result['changed'] => 'welcome',
                    $result['changed'] => 'created',
                    default => 'preserved',
                });
            }
            $request->attributes->set('_contao_widget_error', true);
        }

        $showWelcome = $mayImport && $site->offerDemo($key);
        /** @var ContaoCsrfTokenManager $tokens */
        $tokens = $container->get('contao.csrf.token_manager');
        $this->Template->setData([
            'lang' => $lang['setup'],
            'packages' => array_map(fn (ThemePackage $item): string => $this->texts($item)['title'], $packages),
            'package' => $key,
            'title' => $texts['title'],
            'notice' => match ($imported) {
                'welcome' => $lang['setup']['importedWelcome'],
                'created' => $texts['imported'],
                'preserved' => $lang['setup']['preserved'],
                default => '',
            },
            // After the welcome step the editor continues with the settings of the bundle.
            'continue' => 'welcome' === $imported && null !== $setupUrl,
            'error' => $error,
            'welcome' => $showWelcome ? DemoWelcome::generate($self, $texts['product'], $texts['contents']) : '',
            'action' => $self,
            'mayImport' => $mayImport,
            'viewUrl' => $this->websiteUrl($key),
            'requestToken' => $tokens->getDefaultTokenValue(),
            'demo' => $texts['demo'],
            'inventory' => $showWelcome ? [] : $this->inventory($package),
            'design' => $showWelcome ? '' : ThemeDesignSummary::generate($key),
            'setupUrl' => $setupUrl,
            'setupLabel' => $texts['setup'],
        ]);
    }

    /**
     * Reason of a refused import in the backend language.
     */
    public static function message(DemoException $exception): string
    {
        System::loadLanguageFile('default');
        $text = $GLOBALS['TL_LANG']['ERR']['nw_demo_'.$exception->reason] ?? null;

        return \is_string($text) ? vsprintf($text, $exception->parameters) : $exception->getMessage();
    }

    /**
     * Start page of the first website that uses a layout of the theme, as an absolute
     * URL (domain and subdirectory of that website), or null.
     */
    private function websiteUrl(string $themeKey): string|null
    {
        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');
        $root = $connection->fetchOne("SELECT p.id FROM tl_page p JOIN tl_layout l ON l.id = p.layout JOIN tl_theme t ON t.id = l.pid WHERE p.type = 'root' AND p.published = 1 AND t.nwThemeKey = ? ORDER BY p.fallback DESC, p.sorting LIMIT 1", [$themeKey]);
        $start = false !== $root ? PageModel::findFirstPublishedByPid((int) $root) : null;
        if (null === $start) {
            return null;
        }

        try {
            /** @var ContentUrlGenerator $urls */
            $urls = System::getContainer()->get('contao.routing.content_url_generator');

            return $urls->generate($start, [], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (ExceptionInterface) {
            return null;
        }
    }

    /**
     * Texts of a theme package from $GLOBALS['TL_LANG']['nw_theme']['packages'].
     *
     * @return array{title: string, product: string, contents: list<string>, demo: string, imported: string, setup: string}
     */
    private function texts(ThemePackage $package): array
    {
        $texts = $GLOBALS['TL_LANG']['nw_theme']['packages'][$package->key()] ?? [];

        return [
            'title' => (string) ($texts['title'] ?? $package->demo()->title()),
            'product' => (string) ($texts['product'] ?? $package->demo()->title()),
            'contents' => array_values(array_map('strval', (array) ($texts['contents'] ?? []))),
            'demo' => (string) ($texts['demo'] ?? ''),
            'imported' => (string) ($texts['imported'] ?? $GLOBALS['TL_LANG']['nw_theme']['setup']['created']),
            'setup' => (string) ($texts['setup'] ?? $package->setupModule()),
        ];
    }

    /**
     * Records of an imported demo by kind, which a replacing import overwrites.
     *
     * @return array<string, int>
     */
    private function inventory(ThemePackage $package): array
    {
        $provider = $package->demo();
        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');
        $json = $connection->fetchOne('SELECT nwDemoState FROM tl_theme WHERE nwThemeKey = ?', [$package->key()]);
        $state = \is_string($json) ? json_decode($json, true) : null;
        if (!$provider instanceof DemoInventory || !\is_array($state)) {
            return [];
        }
        $labels = $GLOBALS['TL_LANG']['nw_theme']['tables'] ?? [];
        $inventory = [];

        foreach ($provider->records($state) as $table => $ids) {
            $count = (int) $connection->fetchOne('SELECT COUNT(*) FROM '.$connection->quoteIdentifier($table).' WHERE id IN (?)', [array_values(array_unique($ids))], [ArrayParameterType::INTEGER]);
            if ($count > 0) {
                $inventory[(string) ($labels[$table] ?? $table)] = $count;
            }
        }

        return $inventory;
    }
}
