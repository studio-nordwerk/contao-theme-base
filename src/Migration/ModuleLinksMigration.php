<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * Before 0.5.1 the header button and the shop's withdrawal link fell back to
 * pages of the demo import. Both are module fields now. This picks the pages the
 * site linked before, once per module.
 */
final class ModuleLinksMigration extends AbstractMigration
{
    private const HEADERS = ['nw_shop_header', 'nw_seminar_header'];

    private const FOOTERS = ['nw_shop_footer'];

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
        if (!isset($columns['nwcorenavigation'], $columns['nwbuttonpage'], $columns['nwwithdrawalpage'])) {
            return false;
        }

        return false !== $this->connection->fetchOne("SELECT id FROM tl_module WHERE nwCoreNavigation = '1' LIMIT 1");
    }

    public function run(): MigrationResult
    {
        $this->connection->transactional(
            function (): void {
                foreach ($this->connection->fetchAllAssociative("SELECT id, pid, type, nwButtonPage, nwWithdrawalPage FROM tl_module WHERE nwCoreNavigation = '1'") as $module) {
                    $state = json_decode((string) ($this->connection->fetchOne('SELECT nwDemoState FROM tl_theme WHERE id = ?', [(int) $module['pid']]) ?: '{}'), true);
                    $pages = \is_array($state) && \is_array($state['pages'] ?? null) ? $state['pages'] : [];
                    $set = ['nwCoreNavigation' => '2'];

                    if (\in_array($module['type'], self::HEADERS, true) && 0 === (int) $module['nwButtonPage'] && $this->exists($pages['events'] ?? 0)) {
                        $set['nwButtonPage'] = (int) $pages['events'];
                    }
                    if (\in_array($module['type'], self::FOOTERS, true) && 0 === (int) $module['nwWithdrawalPage'] && $this->exists($pages['widerruf'] ?? 0)) {
                        $set['nwWithdrawalPage'] = (int) $pages['widerruf'];
                    }
                    $this->connection->update('tl_module', $set, ['id' => (int) $module['id']]);
                }
            },
        );

        return $this->createResult(true);
    }

    private function exists(mixed $page): bool
    {
        return (int) $page > 0 && false !== $this->connection->fetchOne('SELECT id FROM tl_page WHERE id = ?', [(int) $page]);
    }
}
