<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Nordwerk\ThemeBaseBundle\Migration\LayoutStylesheetMigration;
use PHPUnit\Framework\TestCase;

final class LayoutStylesheetMigrationTest extends TestCase
{
    public function testThemeStylesheetsLeaveTheHeadOwnTagsStay(): void
    {
        // Demo layout of 0.5.5 plus a tag of the editor.
        $head = '<link rel="icon" href="/bundles/shoptheme/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/bundles/themebase/base.css"><link rel="stylesheet" href="/bundles/shoptheme/shop.css"><link rel="stylesheet" href="/files/own.css">';

        $this->assertSame(
            '<link rel="icon" href="/bundles/shoptheme/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/files/own.css">',
            LayoutStylesheetMigration::removeLinks($head, ['bundles/themebase/base.css', 'bundles/shoptheme/shop.css']),
        );
    }

    public function testRelativePathsOfOlderDemosAndVersionsAreRemovedToo(): void
    {
        $head = "<meta name=\"color-scheme\" content=\"light\">\n<link rel=\"stylesheet\" href=\"bundles/themebase/base.css\">\n<link rel=\"stylesheet\" href=\"/bundles/nordwerkwithdrawal/withdrawal-base.css?v=1\" />";

        $this->assertSame('<meta name="color-scheme" content="light">', LayoutStylesheetMigration::removeLinks($head, ['bundles/themebase/base.css', 'bundles/nordwerkwithdrawal/withdrawal-base.css']));
    }

    public function testOtherFilesOfTheSameBundleStay(): void
    {
        $head = '<link rel="stylesheet" href="/bundles/themebase/base-print.css">';

        $this->assertSame($head, LayoutStylesheetMigration::removeLinks($head, ['bundles/themebase/base.css']));
    }
}
