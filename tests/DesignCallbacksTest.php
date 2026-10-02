<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Design\DesignCallbacks;
use Nordwerk\ThemeBaseBundle\Design\DesignRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class DesignCallbacksTest extends TestCase
{
    public function testSaveUsesAllSubmittedColorsTogether(): void
    {
        $callbacks = $this->callbacks(['nwText' => 'ffffff', 'nwPaper' => '000000', 'nwTone1' => '000000', 'nwTone2' => '000000']);
        $this->assertSame('#ffffff', $callbacks->validateColor('ffffff', $this->dataContainer('nwText')));
    }

    public function testSaveRejectsUnreadablePackageFallback(): void
    {
        $callbacks = $this->callbacks(['nwText' => 'ffffff', 'nwPaper' => '000000', 'nwTone1' => '', 'nwTone2' => '000000']);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Textfarbe auf Fläche 1');
        $callbacks->validateColor('ffffff', $this->dataContainer('nwText'));
    }

    public function testSavePreservesEmptyOverrides(): void
    {
        $this->assertSame('', $this->callbacks([])->validateColor('', $this->dataContainer('nwPaper')));
    }

    public function testLoadRemovesHashForTheColorpicker(): void
    {
        $this->assertSame('382f3d', $this->callbacks([])->loadColor('#382f3d'));
        $this->assertSame('', $this->callbacks([])->loadColor(null));
    }

    /**
     * @param array<string, mixed> $values
     */
    private function callbacks(array $values): DesignCallbacks
    {
        $stack = new RequestStack();
        $stack->push(Request::create('/contao?do=themes&act=edit&id=1', 'POST', $values));

        return new DesignCallbacks($stack, new DesignRepository($this->createMock(Connection::class)));
    }

    private function dataContainer(string $field): DataContainer
    {
        $dc = $this->createMock(DataContainer::class);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['nwThemeKey' => 'seminar'])
        ;

        $dc
            ->method('__get')
            ->with('field')
            ->willReturn($field)
        ;

        return $dc;
    }
}
