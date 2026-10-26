<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Nordwerk\ThemeBaseBundle\Migration\LayoutStylesheetMigration;
use PHPUnit\Framework\TestCase;

final class LayoutStylesheetMigrationTest extends TestCase
{
    private const FILES = ['bundles/themebase/base.css', 'bundles/shoptheme/shop.css', 'bundles/nordwerkwithdrawal/withdrawal-base.css'];

    public function testThemeStylesheetsLeaveTheHeadOwnTagsStay(): void
    {
        // Demo layout of 0.5.5 plus a tag of the editor.
        $head = '<link rel="icon" href="/bundles/shoptheme/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/bundles/themebase/base.css"><link rel="stylesheet" href="/bundles/shoptheme/shop.css"><link rel="stylesheet" href="/files/own.css">';

        $this->assertSame(
            '<link rel="icon" href="/bundles/shoptheme/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/files/own.css">',
            LayoutStylesheetMigration::removeLinks($head, self::FILES),
        );
    }

    public function testOnlyTheRemovedLinesChangeInAFormattedHead(): void
    {
        $head = "<meta name=\"color-scheme\" content=\"light\">\n\n  <link rel=\"stylesheet\" href=\"bundles/themebase/base.css\">\n<!-- own -->\n<link rel=\"stylesheet\" href=\"/bundles/nordwerkwithdrawal/withdrawal-base.css?v=1\" />\n";

        $this->assertSame("<meta name=\"color-scheme\" content=\"light\">\n\n<!-- own -->\n", LayoutStylesheetMigration::removeLinks($head, self::FILES));
    }

    public function testAttributeOrderQuotesMediaAndSubdirectoriesAreRecognized(): void
    {
        $head = implode("\n", [
            '<link href="/bundles/themebase/base.css" rel="stylesheet">',
            "<link rel='stylesheet' href='/bundles/shoptheme/shop.css' media='all'>",
            '<link media="screen" REL="stylesheet" href="/shop/bundles/themebase/base.css">',
            '<meta name="keep">',
        ]);

        $this->assertSame('<meta name="keep">', LayoutStylesheetMigration::removeLinks($head, self::FILES));
    }

    public function testOtherFilesAndOtherRelationsStay(): void
    {
        $head = '<link rel="stylesheet" href="/bundles/themebase/base-print.css"><link rel="preload" href="/bundles/themebase/base.css" as="style"><link rel="stylesheet" href="/mybundles/themebase/base.css.map">';

        $this->assertSame($head, LayoutStylesheetMigration::removeLinks($head, self::FILES));
    }
}
