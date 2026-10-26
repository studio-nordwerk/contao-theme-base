<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Nordwerk\ThemeBaseBundle\Theme\ThemeAssets;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackage;
use PHPUnit\Framework\TestCase;

final class ThemeAssetsTest extends TestCase
{
    public function testEveryLayoutOfTheThemeGetsTheBodyClassesOnce(): void
    {
        $package = $this->createStub(ThemePackage::class);
        $package
            ->method('cssClass')
            ->willReturn('nw-shop-theme')
        ;

        $this->assertSame('nw-theme nw-shop-theme', ThemeAssets::withClasses('', $package));
        $this->assertSame('landing nw-theme nw-shop-theme', ThemeAssets::withClasses(' landing ', $package));
        $this->assertSame('nw-theme nw-shop-theme dark', ThemeAssets::withClasses('nw-theme nw-shop-theme dark', $package));
    }
}
