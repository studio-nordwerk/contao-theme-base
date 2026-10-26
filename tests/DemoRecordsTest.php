<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Nordwerk\ThemeBaseBundle\Demo\DemoRecords;
use PHPUnit\Framework\TestCase;

final class DemoRecordsTest extends TestCase
{
    public function testTablesOfRemovedBundlesAreSkipped(): void
    {
        $schema = $this->createStub(AbstractSchemaManager::class);
        $schema
            ->method('tablesExist')
            ->willReturnCallback(static fn (array $tables): bool => 'tl_nw_testimonial' !== $tables[0])
        ;
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('createSchemaManager')
            ->willReturn($schema)
        ;

        $this->assertSame(
            ['tl_page' => [1, 2], 'tl_theme' => [3]],
            DemoRecords::existing($connection, ['tl_page' => [1, 2], 'tl_nw_testimonial' => [4], 'tl_faq' => [], 'tl_theme' => [3]]),
        );
    }
}
