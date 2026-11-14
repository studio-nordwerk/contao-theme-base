<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Contao\DataContainer;
use Nordwerk\ThemeBaseBundle\Design\DesignSettings;
use Nordwerk\ThemeBaseBundle\Design\DesignVariables;
use Nordwerk\ThemeBaseBundle\Design\Palette;
use Nordwerk\ThemeBaseBundle\Design\Style;
use Nordwerk\ThemeBaseBundle\Design\StyleCallbacks;
use Nordwerk\ThemeBaseBundle\Design\StyleCatalog;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackages;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The mapping of a catalog style onto the theme: CSS variables and the fields of the
 * theme form. Expectations follow the catalog's rules (profiles.ts), not the code.
 */
final class StyleTest extends TestCase
{
    private const MINIMAL = [
        'id' => 'test',
        'label' => 'Test',
        'colors' => ['surface' => '#ffffff', 'surfaceRaised' => '#ffffff', 'ink' => '#000000', 'inkSoft' => '#555555', 'line' => '#dddddd', 'brand' => '#225588', 'brandInk' => '#ffffff'],
        'fonts' => ['display' => '"Fraunces", Georgia, serif', 'body' => '"Inter", system-ui, sans-serif'],
        'radius' => ['button' => '999px', 'control' => '0.25rem', 'card' => '1rem'],
    ];

    /**
     * @return iterable<string, array{Style}>
     */
    public static function bundledStyles(): iterable
    {
        foreach ((new StyleCatalog())->all() as $id => $style) {
            yield $id => [$style];
        }
    }

    /**
     * @return iterable<string, array{Palette}>
     */
    public static function bundledPalettes(): iterable
    {
        // The curated palettes editors pick, and the styles' own for whole styles.
        foreach ((new StyleCatalog())->allPalettes() as $id => $palette) {
            yield $id => [$palette];
        }
    }

    /**
     * Picking a palette must never leave the form in a state it refuses to save,
     * whatever typeset and shape go with it: the contrast rule hangs on the color
     * axis alone.
     */
    #[DataProvider('bundledPalettes')]
    public function testEveryBundledPaletteCanBeSavedInTheThemeForm(Palette $palette): void
    {
        $fields = $palette->themeFields();
        $settings = new DesignSettings(accent: $fields['nwAccent'], surface: $fields['nwSurface'], paper: $fields['nwPaper'], text: $fields['nwText'], tone1: $fields['nwTone1'], tone2: $fields['nwTone2'], themeKey: 'nordwerk-booking-v1', palette: $palette->id);

        $settings->validateSurfaceContrast();
        $this->assertGreaterThanOrEqual(4.5, DesignSettings::contrastRatio($settings->accent, $settings->accentText()));
    }

    /**
     * Every style is made of axes the catalog holds, and splitting it changed
     * nothing: the axes together give the profile's values.
     */
    #[DataProvider('bundledStyles')]
    public function testEveryStyleIsTheSumOfItsAxes(Style $style): void
    {
        $catalog = new StyleCatalog();

        $this->assertSame($style->palette->tokens(), $catalog->palette($style->palette->id)?->tokens());
        $this->assertSame($style->typeset->tokens(), $catalog->typeset($style->typeset->id)?->tokens());
        $this->assertSame($style->shape->tokens(), $catalog->shape($style->shape->id)?->tokens());
        $this->assertSame([], array_intersect_key($style->palette->tokens(), $style->typeset->tokens()), 'palette and typeset share no variable');
        $this->assertSame([], array_intersect_key($style->palette->tokens(), $style->shape->tokens()), 'palette and shape share no variable');
        $this->assertSame([], array_intersect_key($style->typeset->tokens(), $style->shape->tokens()), 'typeset and shape share no variable');
    }

    public function testOptionalRolesFallBackLikeTheCatalog(): void
    {
        $tokens = Style::fromArray(self::MINIMAL)->tokens();

        $this->assertSame('#225588', $tokens['--nw-accent'], 'action falls back to brand');
        $this->assertSame('#ffffff', $tokens['--nw-accent-contrast'], 'actionInk falls back to brandInk');
        $this->assertSame('"Fraunces", Georgia, serif', $tokens['--nw-font-accent'], 'accent font falls back to display');
        $this->assertSame('"Inter", system-ui, sans-serif', $tokens['--nw-font-meta'], 'meta font falls back to body');
        $this->assertSame('1rem', $tokens['--nw-radius-panel'], 'panel radius falls back to card');
        $this->assertSame('1rem', $tokens['--nw-radius-media'], 'image radius falls back to card');
        $this->assertSame('#a63a2b', $tokens['--nw-error']);

        // Missing type, space and shadow keep the values of the theme package.
        foreach (['--nw-text-display', '--nw-text-title', '--nw-text-lead', '--nw-leading-heading', '--nw-tracking-heading', '--nw-content-width', '--nw-gutter', '--theme-section-space', '--nw-shadow-panel'] as $variable) {
            $this->assertArrayNotHasKey($variable, $tokens);
        }
    }

    public function testExplicitRolesWin(): void
    {
        $tokens = Style::fromArray([
            ...self::MINIMAL,
            'colors' => [...self::MINIMAL['colors'], 'action' => '#cc3300', 'actionInk' => '#000000'],
            'fonts' => [...self::MINIMAL['fonts'], 'accent' => '"Lora", serif', 'meta' => '"Archivo", sans-serif'],
            'radius' => [...self::MINIMAL['radius'], 'panel' => '2rem', 'image' => '0'],
            'type' => ['display' => 'clamp(2rem, 5vw, 4rem)', 'title' => '2rem', 'lead' => '1.2rem', 'leadingDisplay' => '0.98', 'trackingDisplay' => '-0.025em'],
            'space' => ['section' => '5rem', 'content' => '84rem', 'gutter' => 'clamp(1rem, 4vw, 3rem)'],
            'states' => ['error' => '#990000', 'errorSoft' => '#ffeeee'],
        ])->tokens();

        $this->assertSame('#cc3300', $tokens['--nw-accent']);
        $this->assertSame('#000000', $tokens['--nw-accent-contrast']);
        $this->assertSame('#225588', $tokens['--nw-focus'], 'brand stays the focus color');
        $this->assertSame('"Lora", serif', $tokens['--nw-font-accent']);
        $this->assertSame('"Archivo", sans-serif', $tokens['--nw-font-meta']);
        $this->assertSame('2rem', $tokens['--nw-radius-panel']);
        $this->assertSame('0', $tokens['--nw-radius-media']);
        $this->assertSame('clamp(2rem, 5vw, 4rem)', $tokens['--nw-text-display']);
        $this->assertSame('clamp(2rem, 5vw, 4rem)', $tokens['--nw-text-hero']);
        $this->assertSame('2rem', $tokens['--nw-text-section']);
        $this->assertSame('0.98', $tokens['--nw-leading-heading']);
        $this->assertSame('84rem', $tokens['--nw-content-width']);
        $this->assertSame('clamp(1rem, 4vw, 3rem)', $tokens['--nw-gutter']);
        $this->assertSame('5rem', $tokens['--theme-section-space']);
        $this->assertSame('#990000', $tokens['--nw-error']);
        $this->assertSame('#ffeeee', $tokens['--nw-error-soft']);
    }

    public function testShadowNullMeansAFlatStyle(): void
    {
        $this->assertSame('none', Style::fromArray([...self::MINIMAL, 'shadow' => null])->tokens()['--nw-shadow-panel']);
        $this->assertSame('0 1px 2px rgb(0 0 0 / .1)', Style::fromArray([...self::MINIMAL, 'shadow' => ['card' => '0 1px 2px rgb(0 0 0 / .1)']])->tokens()['--nw-shadow-panel']);
    }

    public function testDerivedColorsMoveTowardsTheInk(): void
    {
        $tokens = Style::fromArray(self::MINIMAL)->tokens();

        // 8 % of black on white: 255 × 0.92 = 234.6 → eb.
        $this->assertSame('#ebebeb', $tokens['--theme-tile']);
        // Hairline #dddddd, 35 % towards black: 221 × 0.65 = 143.65 → 90.
        $this->assertSame('#909090', $tokens['--nw-field-border']);
        // Error tint: 14 % of #a63a2b on white: 255 - 89 × 0.14 = 242.5 → f3.
        $this->assertSame('#f3e3e1', $tokens['--nw-error-soft']);
    }

    public function testPickingAStyleFillsTheColorsAndHandsShapesAndFontsToTheStyle(): void
    {
        $this->assertSame(
            [
                'nwAccent' => '#225588',
                'nwSurface' => '#ffffff',
                'nwPaper' => '#ffffff',
                'nwText' => '#000000',
                'nwTone1' => '#ebebeb',
                'nwTone2' => '#ffffff',
                'nwFontPair' => '',
                'nwRadius' => '',
                'nwButtonShape' => '',
                'nwPalette' => '',
                'nwTypeset' => '',
                'nwShape' => '',
            ],
            Style::fromArray(self::MINIMAL)->themeFields(),
        );
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function unsafeValues(): iterable
    {
        yield 'semicolon' => [[...self::MINIMAL, 'fonts' => [...self::MINIMAL['fonts'], 'body' => 'Inter; } body { display: none']]];
        yield 'end of style element' => [[...self::MINIMAL, 'type' => ['display' => '1rem</style><script>']]];
        yield 'not a color' => [[...self::MINIMAL, 'colors' => [...self::MINIMAL['colors'], 'ink' => 'red']]];
        yield 'id that leaves the selector' => [[...self::MINIMAL, 'id' => 'a"] body {']];
        yield 'missing color' => [[...self::MINIMAL, 'colors' => ['surface' => '#ffffff']]];
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('unsafeValues')]
    public function testValuesOutsideTheContractAreRejected(array $data): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Style::fromArray($data);
    }

    public function testTheStyleGivesTheButtonTextOnlyForItsOwnAccent(): void
    {
        $style = 'nacht-gold';
        $gold = (new StyleCatalog())->get($style);
        $this->assertNotNull($gold);

        $this->assertSame($gold->actionInk(), (new DesignSettings(accent: $gold->action(), style: $style))->accentText());
        $this->assertSame('#ffffff', (new DesignSettings(accent: '#1c1b19', style: $style))->accentText());
    }

    public function testATextColorChangedByHandDerivesTheSecondaryText(): void
    {
        $gold = (new StyleCatalog())->get('nacht-gold');
        $this->assertNotNull($gold);
        $ink = $gold->surfaces()['text'];

        $this->assertArrayNotHasKey('--theme-muted', (new DesignSettings(text: $ink, style: 'nacht-gold'))->cssVariables());
        $this->assertArrayHasKey('--theme-muted', (new DesignSettings(text: '#ffffff', style: 'nacht-gold'))->cssVariables());
    }

    public function testEmptyFieldsFallBackToTheStyleWhenValidating(): void
    {
        $gold = (new StyleCatalog())->get('nacht-gold');
        $this->assertNotNull($gold);

        $this->assertSame($gold->surfaces(), (new DesignSettings(themeKey: 'nordwerk-booking-v1', style: 'nacht-gold'))->effectiveSurfaces());
        $this->assertSame(DesignSettings::SURFACE_DEFAULTS['nordwerk-booking-v1'], (new DesignSettings(themeKey: 'nordwerk-booking-v1', style: 'unknown'))->effectiveSurfaces());
    }

    public function testFontStylesheetsOnlyForBundledFamilies(): void
    {
        $catalog = new StyleCatalog();
        $style = Style::fromArray([...self::MINIMAL, 'fonts' => ['display' => '"Helvetica Neue", Arial', 'body' => '"Inter", sans-serif', 'accent' => '"Inter", sans-serif']]);

        $this->assertSame(['bundles/themebase/fonts/inter.css'], $catalog->fontStylesheets($style));
    }

    /**
     * The visitor preview lays one rule per axis over the theme's own; any variable
     * the theme form sets for that axis and the option leaves out would show through.
     */
    #[DataProvider('bundledStyles')]
    public function testAPreviewedAxisCoversEveryVariableOfTheThemeForm(Style $style): void
    {
        $row = ['nwThemeKey' => 'nordwerk-booking-v1', 'nwAccent' => '#1c1b19', 'nwSurface' => '#f7f5f0', 'nwPaper' => '#ffffff', 'nwText' => '#222222', 'nwTone1' => '#eeeeee', 'nwTone2' => '#dddddd', 'nwFontPair' => 'fraunces-helvetica', 'nwRadius' => '22px', 'nwButtonShape' => '999px'];
        $theme = DesignVariables::groups(DesignSettings::fromThemeRow($row), true);
        $picked = [
            'palette' => DesignVariables::groups(DesignSettings::fromThemeRow([...$row, ...$style->palette->themeFields(), 'nwPalette' => $style->palette->id]), true)['palette'],
            'typeset' => DesignVariables::groups(DesignSettings::fromThemeRow([...$row, 'nwTypeset' => $style->typeset->id, 'nwFontPair' => '']), true)['typeset'],
            'shape' => DesignVariables::groups(DesignSettings::fromThemeRow([...$row, 'nwShape' => $style->shape->id, 'nwRadius' => '', 'nwButtonShape' => '']), true)['shape'],
        ];

        foreach ($picked as $axis => $variables) {
            $this->assertSame([], array_values(array_diff(array_keys($theme[$axis]), array_keys($variables))), $axis);
        }
        // The three rules and the style's spacing together are the style picked in the form.
        $this->assertEqualsCanonicalizing(DesignVariables::of(DesignSettings::fromThemeRow([...$row, ...$style->themeFields(), 'nwStyle' => $style->id]), true), [...$picked['palette'], ...$picked['typeset'], ...$picked['shape'], ...$style->shape->spaceTokens()]);
    }

    /**
     * Themes saved with a style before the axes existed have empty axis fields; they
     * must look exactly as with the style's axes set.
     */
    public function testAStyleSavedWithoutAxesLooksAsBefore(): void
    {
        $gold = (new StyleCatalog())->get('nacht-gold');
        $this->assertNotNull($gold);
        $fields = $gold->themeFields();
        $before = new DesignSettings(accent: $fields['nwAccent'], surface: $fields['nwSurface'], fontPair: '', paper: $fields['nwPaper'], text: $fields['nwText'], tone1: $fields['nwTone1'], tone2: $fields['nwTone2'], style: 'nacht-gold');
        $now = new DesignSettings(accent: $fields['nwAccent'], surface: $fields['nwSurface'], fontPair: '', paper: $fields['nwPaper'], text: $fields['nwText'], tone1: $fields['nwTone1'], tone2: $fields['nwTone2'], style: 'nacht-gold', palette: 'nacht-gold', typeset: $gold->typeset->id, shape: $gold->shape->id);

        $this->assertSame(DesignVariables::of($before, true), DesignVariables::of($now, true));
    }

    public function testAnAxisChosenOnItsOwnWinsOverTheStyle(): void
    {
        $catalog = new StyleCatalog();
        $gold = $catalog->get('nacht-gold');
        $salbei = $catalog->get('salbei-ruhig');
        $square = $catalog->get('sand-editorial');
        $this->assertNotNull($gold);
        $this->assertNotNull($salbei);
        $this->assertNotNull($square);
        $variables = DesignVariables::of(new DesignSettings(accent: $gold->action(), fontPair: '', style: 'nacht-gold', typeset: $salbei->typeset->id, shape: $square->shape->id), true);

        $this->assertSame($gold->palette->tokens()['--theme-paper'], $variables['--theme-paper']);
        $this->assertSame($salbei->typeset->fonts['display'], $variables['--theme-heading-font']);
        $this->assertSame($square->shape->radius['card'], $variables['--nw-radius-card']);
    }

    public function testAFormValueChangedByHandWinsOverTheAxes(): void
    {
        $gold = (new StyleCatalog())->get('nacht-gold');
        $this->assertNotNull($gold);
        $variables = DesignVariables::of(new DesignSettings(accent: '#2f5d50', fontPair: 'lora-source', radius: '0', buttonShape: '999px', style: 'nacht-gold'), true);

        $this->assertSame('#2f5d50', $variables['--nw-accent']);
        $this->assertSame('999px', $variables['--nw-radius-button']);
        $this->assertSame('0', $variables['--theme-radius']);
        $this->assertSame("'Lora'", $variables['--theme-heading-font']);
    }

    /**
     * Picking in the form: a style sets its axes, an axis overwrites only the fields
     * it governs, "Kein Stil" clears the axes and keeps the colors.
     */
    public function testPickingInTheFormOverwritesOnlyTheFieldsOfThePick(): void
    {
        $catalog = new StyleCatalog();
        $callbacks = new StyleCallbacks($catalog, new ThemePackages());
        $dc = $this->createStub(DataContainer::class);
        $gold = $catalog->get('nacht-gold');
        $this->assertNotNull($gold);

        $this->assertSame(['nwStyle' => 'nacht-gold', ...$gold->themeFields()], $callbacks->applyStyle(['nwStyle' => 'nacht-gold'], $dc));
        $this->assertSame(['nwStyle' => '', 'nwPalette' => '', 'nwTypeset' => '', 'nwShape' => ''], $callbacks->applyStyle(['nwStyle' => ''], $dc));
        $this->assertSame(['nwPalette' => 'nacht', 'nwAccent' => '#2f5d50', ...$gold->palette->themeFields()], $callbacks->applyStyle(['nwPalette' => 'nacht', 'nwAccent' => '#2f5d50'], $dc));
        $this->assertSame(['nwTypeset' => 'rund', 'nwFontPair' => ''], $callbacks->applyStyle(['nwTypeset' => 'rund', 'nwFontPair' => 'lora-source'], $dc));
        $this->assertSame(['nwShape' => 'eckig', 'nwRadius' => '', 'nwButtonShape' => ''], $callbacks->applyStyle(['nwShape' => 'eckig'], $dc));
        $this->assertSame(['nwAccent' => '#2f5d50'], $callbacks->applyStyle(['nwAccent' => '#2f5d50'], $dc), 'hand values alone stay as they are');
    }

    /**
     * The curated axes of the catalog (axes.json): each takes its values from one
     * profile and keeps its own id and name.
     */
    public function testCuratedAxesTakeTheValuesOfTheirProfile(): void
    {
        $catalog = new StyleCatalog();

        $this->assertCount(11, $catalog->palettes());
        $this->assertCount(6, $catalog->typesets());
        $this->assertCount(4, $catalog->shapes());
        $this->assertSame($catalog->get('nacht-gold')?->palette->tokens(), $catalog->palette('nacht')?->tokens());
        $this->assertSame('Nacht', $catalog->palette('nacht')?->label);
        $this->assertSame($catalog->get('salbei-ruhig')?->typeset->tokens(), $catalog->typeset('rund')?->tokens());
        $this->assertSame($catalog->get('premium-kanzlei')?->shape->tokens(), $catalog->shape('fein')?->tokens());
    }

    /**
     * Picking a style puts each axis on the curated option with exactly its values,
     * or on "as the style" when there is none; the style keeps its own exact values
     * either way.
     */
    public function testAStyleShowsACuratedAxisOnlyForAnExactMatch(): void
    {
        $catalog = new StyleCatalog();
        $salbei = $catalog->get('salbei-ruhig');
        $gastro = $catalog->get('gastro-abend');
        $this->assertNotNull($salbei);
        $this->assertNotNull($gastro);

        $this->assertSame(['nwPalette' => 'salbei', 'nwTypeset' => 'rund', 'nwShape' => 'weich'], array_intersect_key($salbei->themeFields(), array_flip(['nwPalette', 'nwTypeset', 'nwShape'])));
        // Gastro Abend has no curated palette or typeset; its shape equals "Fein" (from
        // Premium Kanzlei).
        $this->assertSame(['nwPalette' => '', 'nwTypeset' => '', 'nwShape' => 'fein'], array_intersect_key($gastro->themeFields(), array_flip(['nwPalette', 'nwTypeset', 'nwShape'])));
        $fields = $gastro->themeFields();
        $picked = DesignVariables::of(DesignSettings::fromThemeRow([...$fields, 'nwStyle' => 'gastro-abend']), true);
        $this->assertSame($gastro->palette->tokens()['--nw-error-soft'], $picked['--nw-error-soft']);
        $this->assertSame($gastro->typeset->fonts['display'], $picked['--theme-heading-font']);
    }

    public function testACuratedAxisWinsOverTheStyle(): void
    {
        $catalog = new StyleCatalog();
        $variables = DesignVariables::of(new DesignSettings(accent: '#c5a46a', fontPair: '', style: 'nacht-gold', typeset: 'rund', shape: 'eckig'), true);

        $this->assertSame($catalog->typeset('rund')?->fonts['display'], $variables['--theme-heading-font']);
        $this->assertSame($catalog->shape('eckig')?->radius['card'], $variables['--nw-radius-card']);
        $this->assertSame($catalog->palette('nacht')?->tokens()['--theme-paper'], $variables['--theme-paper']);
    }

    /**
     * Values saved and links shared before the curated axes used style and block ids;
     * they keep resolving to the same values.
     */
    public function testEarlierIdsKeepResolving(): void
    {
        $catalog = new StyleCatalog();

        $this->assertSame($catalog->palette('nacht')?->tokens(), $catalog->palette('nacht-gold')?->tokens());
        $this->assertSame($catalog->get('premium-kanzlei')?->shape->tokens(), $catalog->shape('premium-kanzlei')?->tokens());
        $this->assertSame($catalog->get('premium-kanzlei')?->shape->tokens(), $catalog->shape('gastro-abend')?->tokens());
        $this->assertNull($catalog->palette('unknown'));
        $before = DesignVariables::of(new DesignSettings(accent: '#c5a46a', fontPair: '', style: 'nacht-gold', typeset: 'salbei-ruhig', shape: 'sand-editorial'), true);
        $now = DesignVariables::of(new DesignSettings(accent: '#c5a46a', fontPair: '', style: 'nacht-gold', typeset: 'rund', shape: 'eckig'), true);
        $this->assertSame($before, $now);
    }

    public function testTheFormOffersTheCuratedAxesAndKeepsAnEarlierChoice(): void
    {
        $callbacks = new StyleCallbacks(new StyleCatalog(), new ThemePackages());
        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['nwTypeset' => 'salbei-ruhig'])
        ;

        $this->assertCount(11, $callbacks->paletteOptions($dc));
        $this->assertSame(['neutral', 'rund', 'technisch', 'klassisch', 'editorial', 'plakativ', 'salbei-ruhig'], array_keys($callbacks->typesetOptions($dc)));
        $this->assertSame(['eckig', 'fein', 'weich', 'rund'], array_keys($callbacks->shapeOptions($dc)));
    }

    /**
     * Spacing and content width belong to the whole style (axes.json): picking a
     * shape keeps those of the style, or the theme's own without a style.
     */
    public function testPickingAShapeKeepsTheSpacing(): void
    {
        $catalog = new StyleCatalog();
        $space = ['--nw-content-width', '--theme-section-space', '--nw-gutter'];
        $salbei = $catalog->get('salbei-ruhig')?->shape->spaceTokens();
        $this->assertSame('72rem', $salbei['--nw-content-width'] ?? null, 'Salbei ruhig brings its spacing');

        foreach ($catalog->shapes() as $shape => $option) {
            $this->assertSame([], $option->spaceTokens(), $shape);
            $alone = DesignVariables::of(new DesignSettings(accent: '#1c1b19', fontPair: '', shape: $shape), true);
            $this->assertSame([], array_intersect_key($alone, array_flip($space)), $shape.' without a style');
            $withStyle = DesignVariables::of(new DesignSettings(accent: '#1c1b19', fontPair: '', style: 'salbei-ruhig', shape: $shape), true);
            $this->assertSame($salbei, array_intersect_key($withStyle, array_flip($space)), $shape.' over Salbei ruhig');
            $this->assertSame($option->radius['card'], $withStyle['--nw-radius-card'], $shape.' still sets its radius');
        }
    }

    /**
     * "Plakativ" takes the fonts of Studio still with a smaller scale for mixing; the
     * whole style Studio still keeps its large one, also when saved with "Plakativ".
     */
    public function testPlakativScalesDownButStudioStillKeepsItsScale(): void
    {
        $catalog = new StyleCatalog();
        $still = $catalog->get('studio-still');
        $this->assertNotNull($still);
        $plakativ = $catalog->typeset('plakativ')?->tokens();

        $this->assertSame('clamp(2.6rem, 5.8vw, 4.8rem)', $plakativ['--nw-text-display'] ?? null);
        $this->assertSame('clamp(1.9rem, 3.8vw, 2.9rem)', $plakativ['--nw-text-title'] ?? null);
        $this->assertSame($still->typeset->fonts, $catalog->typeset('plakativ')?->fonts);
        $this->assertSame('clamp(3rem, 8.4vw, 8rem)', $still->tokens()['--nw-text-display']);

        // The form shows "Plakativ" for Studio still; saved like that, it renders the style.
        $this->assertSame('plakativ', $still->themeFields()['nwTypeset']);
        $saved = DesignVariables::of(DesignSettings::fromThemeRow([...$still->themeFields(), 'nwStyle' => 'studio-still']), true);
        $this->assertSame('clamp(3rem, 8.4vw, 8rem)', $saved['--nw-text-display']);
        $this->assertSame($still->typeset->tokens(), array_intersect_key($saved, $still->typeset->tokens()));

        // Over another style, or without one, "Plakativ" has its own sizes.
        $mixed = DesignVariables::of(new DesignSettings(accent: '#1c1b19', fontPair: '', style: 'salbei-ruhig', typeset: 'plakativ'), true);
        $this->assertSame('clamp(2.6rem, 5.8vw, 4.8rem)', $mixed['--nw-text-display']);
        $alone = DesignVariables::of(new DesignSettings(accent: '#1c1b19', fontPair: '', typeset: 'plakativ'), true);
        $this->assertSame('clamp(2.6rem, 5.8vw, 4.8rem)', $alone['--nw-text-display']);
    }
}
