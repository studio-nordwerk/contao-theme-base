<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * Before 0.5 header and footer built their links themselves (nwNavigationRoot,
 * nwFooterPages, demo pages). They now render core navigation modules. This
 * creates those modules once with the links the site showed before and renames
 * the untouched module names to "Kopfzeile" and "Fußzeile".
 */
final class CoreNavigationMigration extends AbstractMigration
{
    private const HEADERS = ['nw_shop_header', 'nw_seminar_header'];

    private const FOOTERS = ['nw_shop_footer', 'nw_seminar_footer'];

    // Demo page keys of the shop and the seminar theme.
    private const LISTED = ['shop', 'events'];

    private const LEGAL = ['impressum', 'datenschutz', 'agb', 'widerrufsbelehrung', 'imprint', 'privacy', 'terms', 'withdrawalNotice'];

    private const NAMES = ['Shop-Kopf' => 'Shop: Kopfzeile', 'Shop-Fuß' => 'Shop: Fußzeile', 'Seminar: Kopf' => 'Seminar: Kopfzeile', 'Seminar: Fuß' => 'Seminar: Fußzeile'];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schema = $this->connection->createSchemaManager();
        if (!$schema->tablesExist(['tl_module', 'tl_theme', 'tl_page'])) {
            return false;
        }
        $columns = array_change_key_case($schema->listTableColumns('tl_module'));
        if (!isset($columns['nwcorenavigation'], $columns['nwnavmodule'], $columns['nwfooternavmodule'], $columns['nwfooterlegalmodule'], $columns['nwnavigationroot'], $columns['nwfooterpages'])) {
            return false;
        }

        return [] !== $this->modules();
    }

    public function run(): MigrationResult
    {
        $this->connection->transactional(
            function (): void {
                foreach ($this->modules() as $module) {
                    $id = (int) $module['id'];
                    $theme = (int) $module['pid'];
                    $set = ['nwCoreNavigation' => '1'];

                    if (isset(self::NAMES[$module['name']])) {
                        $set['name'] = self::NAMES[$module['name']];
                    }
                    if (\in_array($module['type'], self::HEADERS, true)) {
                        if (0 === (int) $module['nwNavModule']) {
                            $root = (int) $module['nwNavigationRoot'];
                            $set['nwNavModule'] = $this->create($theme, ['name' => 'Hauptnavigation', 'type' => 'navigation', 'showLevel' => 1, 'hardLimit' => 1, 'defineRoot' => $root > 0 ? 1 : 0, 'rootPage' => $root]);
                        }
                    } else {
                        $demo = $this->demo($theme);
                        if (0 === (int) $module['nwFooterNavModule'] && [] !== $pages = $this->footerPages($theme, $demo, StringUtil::deserialize($module['nwFooterPages'], true))) {
                            $set['nwFooterNavModule'] = $this->create($theme, ['name' => 'Fußzeile: Seiten', 'type' => 'customnav', 'pages' => serialize($pages)]);
                        }
                        if (0 === (int) $module['nwFooterLegalModule'] && [] !== $pages = $this->existing(array_map(static fn (string $key): int => (int) ($demo['pages'][$key] ?? 0), self::LEGAL))) {
                            $set['nwFooterLegalModule'] = $this->create($theme, ['name' => 'Fußzeile: Rechtliches', 'type' => 'customnav', 'pages' => serialize($pages)]);
                        }
                    }
                    $this->connection->update('tl_module', $set, ['id' => $id]);
                }
            },
        );

        return $this->createResult(true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function modules(): array
    {
        $types = [...self::HEADERS, ...self::FOOTERS];

        return $this->connection->fetchAllAssociative("SELECT id, pid, name, type, nwNavModule, nwNavigationRoot, nwFooterNavModule, nwFooterLegalModule, nwFooterPages FROM tl_module WHERE nwCoreNavigation = '' AND type IN (".implode(', ', array_fill(0, \count($types), '?')).')', $types);
    }

    /**
     * @return array<string, mixed>
     */
    private function demo(int $theme): array
    {
        $state = json_decode((string) ($this->connection->fetchOne('SELECT nwDemoState FROM tl_theme WHERE id = ?', [$theme]) ?: '{}'), true);

        return \is_array($state) ? $state : [];
    }

    /**
     * The links of the old page column: the shop or events page, the visible main
     * pages and the extra pages picked in the module.
     *
     * @param array<string, mixed> $demo
     * @param array<mixed>         $extra
     *
     * @return list<int>
     */
    private function footerPages(int $theme, array $demo, array $extra): array
    {
        $pages = array_map(static fn (string $key): int => (int) ($demo['pages'][$key] ?? 0), self::LISTED);
        // The website that uses a layout of this theme, else the demo website.
        $root = (int) $this->connection->fetchOne("SELECT p.id FROM tl_page p JOIN tl_layout l ON l.id = p.layout WHERE p.type = 'root' AND l.pid = ? ORDER BY p.fallback DESC, p.sorting LIMIT 1", [$theme]) ?: (int) ($demo['root'] ?? 0);
        $main = $this->connection->fetchFirstColumn("SELECT id FROM tl_page WHERE pid = ? AND type = 'regular' AND published = 1 AND hide = 0 AND protected = 0 ORDER BY sorting", [$root]);

        return $this->existing([...$pages, ...$main, ...$extra]);
    }

    /**
     * @param array<mixed> $ids
     *
     * @return list<int>
     */
    private function existing(array $ids): array
    {
        $pages = [];

        foreach (array_unique(array_filter(array_map('intval', $ids))) as $id) {
            // Unpublished pages stay picked: the navigation module hides them until they go live.
            if ($this->connection->fetchOne('SELECT id FROM tl_page WHERE id = ?', [$id])) {
                $pages[] = $id;
            }
        }

        return $pages;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function create(int $theme, array $row): int
    {
        $this->connection->insert('tl_module', ['pid' => $theme, 'tstamp' => time(), ...$row]);

        return (int) $this->connection->lastInsertId();
    }
}
