<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Nordwerk\ThemeBaseBundle\Demo\DemoImporter;
use Nordwerk\ThemeBaseBundle\Demo\DemoProvider;
use PHPUnit\Framework\TestCase;

final class DemoImporterTest extends TestCase
{
    public function testExistingDemoIsReturnedWithoutChangingAnyContent(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->method('fetchOne')
            ->willReturn(1)
        ;

        $connection
            ->method('fetchAssociative')
            ->willReturn(['id' => 42, 'nwDemoState' => '{}'])
        ;

        $connection
            ->expects($this->never())
            ->method('transactional')
        ;

        $connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with($this->stringContains('RELEASE_LOCK'))
            ->willReturn($this->createStub(Result::class))
        ;
        $provider = $this->createMock(DemoProvider::class);
        $provider
            ->method('key')
            ->willReturn('test')
        ;

        $provider
            ->expects($this->never())
            ->method('apply')
        ;
        $this->assertSame(['theme' => 42, 'changed' => false], (new DemoImporter($connection))->import($provider));
    }

    public function testConcurrentImportDoesNotTouchContent(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->method('fetchOne')
            ->willReturn(0)
        ;

        $connection
            ->expects($this->never())
            ->method('transactional')
        ;
        $this->expectException(\RuntimeException::class);
        (new DemoImporter($connection))->import($this->createStub(DemoProvider::class));
    }

    public function testLockIsReleasedWhenPreflightRejectsImport(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->method('fetchOne')
            ->willReturn(1)
        ;

        $connection
            ->method('fetchAssociative')
            ->willReturn(false)
        ;

        $connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with($this->stringContains('RELEASE_LOCK'))
            ->willReturn($this->createStub(Result::class))
        ;
        $provider = $this->createMock(DemoProvider::class);
        $provider
            ->method('key')
            ->willReturn('test')
        ;

        $provider
            ->method('assertCanImport')
            ->willThrowException(new \RuntimeException('Collision'))
        ;
        $this->expectExceptionMessage('Collision');
        (new DemoImporter($connection))->import($provider);
    }
}
