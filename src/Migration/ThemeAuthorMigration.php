<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * "Autor" is mandatory in the Contao theme form. Themes imported before 0.3 have
 * none, so the form could not be saved until somebody filled it in.
 */
final class ThemeAuthorMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schema = $this->connection->createSchemaManager();
        if (!$schema->tablesExist(['tl_theme']) || !isset(array_change_key_case($schema->listTableColumns('tl_theme'))['nwthemekey'])) {
            return false;
        }

        return false !== $this->connection->fetchOne("SELECT id FROM tl_theme WHERE nwThemeKey != '' AND author = '' LIMIT 1");
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_theme SET author = 'Studio Nordwerk' WHERE nwThemeKey != '' AND author = ''");

        return $this->createResult(true);
    }
}
