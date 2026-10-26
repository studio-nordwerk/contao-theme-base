<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Demo;

use Doctrine\DBAL\Connection;

/**
 * Records of an import log whose tables still exist. A demo may list tables of
 * bundles that were removed later (testimonials, FAQ).
 */
final class DemoRecords
{
    /**
     * @param array<string, list<int>> $records
     *
     * @return array<string, list<int>>
     */
    public static function existing(Connection $connection, array $records): array
    {
        $schema = $connection->createSchemaManager();

        return array_filter($records, static fn (array $ids, string $table): bool => [] !== $ids && $schema->tablesExist([$table]), ARRAY_FILTER_USE_BOTH);
    }
}
