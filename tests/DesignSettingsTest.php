<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Nordwerk\ThemeBaseBundle\Design\DesignException;
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

    public function testSurfaceContrastUsesTheEffectiveTextColor(): void
    {
        $settings = new DesignSettings(surface: '#ffffff', paper: '#000000', text: '#ffffff', tone1: '#000000', tone2: '#000000');
        $settings->validateSurfaceContrast();
        $this->assertSame('#000000', $settings->surfaceTextOverride());
        $this->assertGreaterThanOrEqual(4.5, DesignSettings::contrastRatio($settings->surface, $settings->surfaceText()));
    }

    public function testColorpickerValuesAreNormalized(): void
    {
        $settings = new DesignSettings(accent: '382F3D', surface: 'fff', paper: '#FFF', text: '000', tone1: 'abcdef', tone2: '#123456');
        $this->assertSame('#382f3d', $settings->accent);
        $this->assertSame('#ffffff', $settings->surface);
        $this->assertSame('#ffffff', $settings->paper);
        $this->assertSame('#000000', $settings->text);
        $this->assertSame('#abcdef', $settings->tone1);
        $this->assertSame('#123456', $settings->tone2);
    }

    /**
     * @return iterable<string, array{string, array<string, string>}>
     */
    public static function packageDefaults(): iterable
    {
        foreach (DesignSettings::SURFACE_DEFAULTS as $key => $colors) {
            yield ($key ?: 'base') => [$key, $colors];
        }
    }

    /**
     * @param array<string, string> $colors
     */
    #[DataProvider('packageDefaults')]
    public function testEmptyColorsUseReadablePackageDefaults(string $key, array $colors): void
    {
        $settings = new DesignSettings(themeKey: $key);
        $this->assertSame($colors, $settings->effectiveSurfaces());
        $settings->validateSurfaceContrast();
        $this->assertSame([], $settings->cssVariables());
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function unreadableSurfaces(): iterable
    {
        foreach (DesignSettings::SURFACE_DEFAULTS as $key => $colors) {
            foreach (['paper' => 'nwPaper', 'tone1' => 'nwTone1', 'tone2' => 'nwTone2'] as $field => $column) {
                yield ($key ?: 'base').'/'.$field => [$key, $field, $colors['text'], $column];
            }
        }
    }

    #[DataProvider('unreadableSurfaces')]
    public function testBackgroundOverridesAreCheckedAgainstDefaultText(string $key, string $field, string $color, string $column): void
    {
        $settings = new DesignSettings(...['themeKey' => $key, $field => $color]);

        try {
            $settings->validateSurfaceContrast();
            $this->fail('The unreadable surface must be rejected.');
        } catch (DesignException $exception) {
            $this->assertSame('contrast', $exception->reason);
            $this->assertSame($column, $exception->parameters['field']);
            $this->assertEqualsWithDelta(1.0, $exception->parameters['ratio'], 0.0001);
        }
    }

    public function testTextOverrideIsCheckedAgainstPackageBackgrounds(): void
    {
        $settings = new DesignSettings(text: '#ffffff', themeKey: 'seminar');
        $this->expectException(DesignException::class);
        $this->expectExceptionMessage('at least 4.5:1');
        $settings->validateSurfaceContrast();
    }

    public function testContrastJustAboveTheThresholdIsAccepted(): void
    {
        $settings = new DesignSettings(paper: '#ffffff', text: '#767676', tone1: '#ffffff', tone2: '#ffffff');
        $settings->validateSurfaceContrast();
        $this->assertGreaterThanOrEqual(4.5, DesignSettings::contrastRatio($settings->text, $settings->paper));
    }

    public function testContrastJustBelowTheThresholdIsRejected(): void
    {
        $settings = new DesignSettings(paper: '#ffffff', text: '#777777', tone1: '#ffffff', tone2: '#ffffff');
        $this->expectException(\InvalidArgumentException::class);
        $settings->validateSurfaceContrast();
    }

    public function testOnlyBundledShapesAreAccepted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DesignSettings(radius: '1px; color: red');
    }
}
