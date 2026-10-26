<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Demo;

use Contao\Automator;
use Contao\CoreBundle\Cache\CacheTagManager;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class DemoImporter
{
    public function __construct(
        private Connection $connection,
        private CacheTagManager|null $cacheTags = null,
        private Security|null $security = null,
    ) {
    }

    /**
     * @return array{theme: int, changed: bool}
     */
    public function import(DemoProvider $provider, bool $overwrite = false): array
    {
        if (1 !== (int) $this->connection->fetchOne("SELECT GET_LOCK('nw-theme-demo', 0)")) {
            throw new DemoException('locked', 'A demo import is already running. Please try again later.');
        }

        try {
            $row = $this->connection->fetchAssociative('SELECT id, nwDemoState FROM tl_theme WHERE nwThemeKey = ?', [$provider->key()]);
            if (false !== $row && !$overwrite) {
                return ['theme' => (int) $row['id'], 'changed' => false];
            }
            $provider->assertCanImport($overwrite);
            $state = [];
            $result = $this->connection->transactional(
                function () use ($provider, $row, &$state): array {
                    if (false === $row) {
                        $this->connection->insert('tl_theme', ['tstamp' => time(), 'name' => $provider->title(), 'author' => 'Studio Nordwerk', 'nwThemeKey' => $provider->key()]);
                        $theme = (int) $this->connection->lastInsertId();
                        $state = [];
                    } else {
                        $theme = (int) $row['id'];
                        $state = json_decode((string) $row['nwDemoState'], true, 512, JSON_THROW_ON_ERROR);
                    }
                    $state = $provider->apply($theme, \is_array($state) ? $state : []);
                    $this->connection->update('tl_theme', ['nwDemoState' => json_encode($state, JSON_THROW_ON_ERROR), 'tstamp' => time()], ['id' => $theme]);

                    return ['theme' => $theme, 'changed' => true];
                },
            );
            $records = $provider instanceof DemoInventory ? $provider->records($state) : [];
            $records['tl_theme'][] = $result['theme'];
            $records = DemoRecords::existing($this->connection, $records);
            $this->version($records);
            (new Automator())->generateSymlinks();
            // Only pages that show a replaced record or the theme are outdated.
            $this->cacheTags?->invalidateTags(array_merge(...array_map(static fn (string $table, array $ids): array => array_map(static fn (int $id): string => 'contao.db.'.$table.'.'.$id, $ids), array_keys($records), array_values($records))));

            return $result;
        } finally {
            $this->connection->executeQuery("SELECT RELEASE_LOCK('nw-theme-demo')");
        }
    }

    /**
     * A version per imported record, like saving it in the backend: with the user in
     * the backend, without one on the console. After a replacing import the editor
     * finds the earlier state under "Versionen".
     *
     * @param array<string, list<int>> $records
     */
    private function version(array $records): void
    {
        $anonymous = null === $this->security?->getUser();

        foreach ($records as $table => $ids) {
            foreach (array_unique($ids) as $id) {
                $versions = new Versions($table, $id);
                $versions->setEditUrl($this->editUrl($table, $id));
                $versions->create($anonymous);
            }
        }
    }

    /**
     * Edit link of a record in the backend module that manages its table.
     */
    private function editUrl(string $table, int $id): string
    {
        foreach ($GLOBALS['BE_MOD'] ?? [] as $modules) {
            foreach ($modules as $module => $config) {
                $tables = $config['tables'] ?? [];
                if (\is_array($tables) && \in_array($table, $tables, true)) {
                    return 'contao?do='.$module.($tables[0] === $table ? '' : '&table='.$table).'&act=edit&id='.$id;
                }
            }
        }

        return '';
    }
}
