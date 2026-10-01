<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Demo;

use Contao\Automator;
use Doctrine\DBAL\Connection;

final readonly class DemoImporter
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array{theme: int, changed: bool}
     */
    public function import(DemoProvider $provider, bool $overwrite = false): array
    {
        if (1 !== (int) $this->connection->fetchOne("SELECT GET_LOCK('nw-theme-demo', 0)")) {
            throw new \RuntimeException('Ein Demo-Import läuft bereits. Bitte versuchen Sie es später erneut.');
        }

        try {
            $row = $this->connection->fetchAssociative('SELECT id, nwDemoState FROM tl_theme WHERE nwThemeKey = ?', [$provider->key()]);
            if (false !== $row && !$overwrite) {
                return ['theme' => (int) $row['id'], 'changed' => false];
            }
            $provider->assertCanImport($overwrite);
            $result = $this->connection->transactional(
                function () use ($provider, $row): array {
                    if (false === $row) {
                        $this->connection->insert('tl_theme', ['tstamp' => time(), 'name' => $provider->title(), 'nwThemeKey' => $provider->key()]);
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
            (new Automator())->generateSymlinks();
            (new Automator())->purgePageCache();

            return $result;
        } finally {
            $this->connection->executeQuery("SELECT RELEASE_LOCK('nw-theme-demo')");
        }
    }
}
