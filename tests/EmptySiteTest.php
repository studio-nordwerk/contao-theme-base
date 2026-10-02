<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Nordwerk\ThemeBaseBundle\Backend\EmptySite;
use PHPUnit\Framework\TestCase;

final class EmptySiteTest extends TestCase
{
    public function testFreshInstallationOffersTheDemo(): void
    {
        $site = new EmptySite($this->connection(false, false));

        $this->assertTrue($site->needsDemo('seminar'));
        $this->assertStringContainsString('href="contao?do=seminar_setup&amp;step=demo"', $site->message('seminar', 'Demo <b>', 'contao?do=seminar_setup&step=demo'));
        $this->assertStringContainsString('Demo &lt;b&gt;', $site->message('seminar', 'Demo <b>', '#'));
    }

    public function testExistingRootPageOrImportedDemoStaysQuiet(): void
    {
        $this->assertFalse((new EmptySite($this->connection(1, false)))->needsDemo('seminar'));
        $this->assertFalse((new EmptySite($this->connection(false, 3)))->needsDemo('seminar'));
        $this->assertSame('', (new EmptySite($this->connection(1, false)))->message('seminar', 'x', '#'));
    }

    public function testMissingTablesBeforeTheMigrationStayQuiet(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchOne')
            ->willThrowException($this->createStub(TableNotFoundException::class))
        ;

        $this->assertFalse((new EmptySite($connection))->needsDemo('seminar'));
    }

    private function connection(false|int $root, false|int $theme): Connection
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchOne')
            ->willReturnCallback(static fn (string $sql): false|int => str_contains($sql, 'tl_page') ? $root : $theme)
        ;

        return $connection;
    }
}
