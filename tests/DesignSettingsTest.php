<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Nordwerk\ThemeBaseBundle\Design\DesignSettings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DesignSettingsTest extends TestCase
{
    public function testKnownContrastRatios(): void
    {
        $this->assertEqualsWithDelta(21, DesignSettings::contrastRatio('#000000', '#ffffff'), 0.0001);
        $this->assertEqualsWithDelta(1, DesignSettings::contrastRatio('#abcdef', '#abcdef'), 0.0001);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function colors(): iterable
    {
        foreach (['#382f3d', '#ffffff', '#000000', '#777777', '#ff0000', '#00ff00', '#0000ff', '#888888'] as $color) {
            yield $color => [$color];
        }
    }

    #[DataProvider('colors')]
    public function testComputedTextMeetsContrast(string $color): void
    {
        $settings = new DesignSettings($color, $color);
        $this->assertGreaterThanOrEqual(4.5, DesignSettings::contrastRatio($color, $settings->accentText()));
        $this->assertGreaterThanOrEqual(4.5, DesignSettings::contrastRatio($color, $settings->surfaceText()));
    }

    public function testLightSurfacesKeepTheThemeInk(): void
    {
        $this->assertNull((new DesignSettings('#29584d', '#eef5f1'))->surfaceTextOverride());
        $this->assertSame('#ffffff', (new DesignSettings('#29584d', '#202020'))->surfaceTextOverride());
    }

    public function testStylesCannotContainInjectedMarkup(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DesignSettings('</style><script>alert(1)</script>');
    }

    public function testOnlyBundledFontPairsAreAccepted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DesignSettings(fontPair: 'remote-font');
    }
}
