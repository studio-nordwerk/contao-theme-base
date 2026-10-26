<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Nordwerk\ThemeBaseBundle\Design\ImageSize;
use PHPUnit\Framework\TestCase;

final class ImageSizeTest extends TestCase
{
    public function testAnEmptyFieldUsesTheRegisteredSize(): void
    {
        $this->assertSame('_nw_logo', ImageSize::orDefault('', '_nw_logo'));
        $this->assertSame('_nw_logo', ImageSize::orDefault(null, '_nw_logo'));
        $this->assertSame('_nw_logo', ImageSize::orDefault(serialize(['', '', '']), '_nw_logo'));
        $this->assertSame('_nw_logo', ImageSize::orDefault(serialize(['0', '0', '']), '_nw_logo'));
    }

    public function testAChosenSizeWins(): void
    {
        $this->assertSame(['', '', '_mini_shop_card'], ImageSize::orDefault(serialize(['', '', '_mini_shop_card']), '_nw_logo'));
        $this->assertSame(['800', '600', 'crop'], ImageSize::orDefault(serialize(['800', '600', 'crop']), '_nw_logo'));
        $this->assertSame(['', '', '7'], ImageSize::orDefault(serialize(['', '', '7']), '_nw_logo'));
    }
}
