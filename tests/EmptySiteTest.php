<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Nordwerk\ThemeBaseBundle\Backend\EmptySite;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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

    public function testDecliningTheDemoIsRememberedPerThemeInTheBackendSession(): void
    {
        $bag = new AttributeBag();
        $bag->setName('contao_backend');
        $session = new Session(new MockArraySessionStorage());
        $session->registerBag($bag);
        $request = new Request();
        $request->setSession($session);
        $requests = new RequestStack();
        $requests->push($request);
        $site = new EmptySite($this->connection(false, false), $requests);

        $this->assertTrue($site->offerDemo('seminar'));
        $site->declineDemo('seminar');
        $this->assertFalse($site->offerDemo('seminar'));
        $this->assertTrue($site->offerDemo('nordwerk-shop-v1'));
        // The start page notice stays until the site has pages.
        $this->assertTrue($site->needsDemo('seminar'));
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
