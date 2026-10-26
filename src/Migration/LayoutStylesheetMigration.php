<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Nordwerk\ThemeBaseBundle\Theme\ThemeAssets;
use Nordwerk\ThemeBaseBundle\Theme\ThemePackages;

/**
 * Up to 0.5 the demo layouts linked the theme stylesheets in the field
 * "Zusätzliche <head>-Tags". The theme base now loads them for every layout of
 * the theme (see ThemeAssets), and the withdrawal element brings its own styles.
 * This removes exactly these <link> tags from the layouts of Nordwerk themes; all
 * other head tags, such as the favicon or own stylesheets, stay.
 */
final class LayoutStylesheetMigration extends AbstractMigration
{
    // The withdrawal element loads it itself while its base styles are switched on.
    private const WITHDRAWAL = 'bundles/nordwerkwithdrawal/withdrawal-base.css';

    public function __construct(
        private readonly Connection $connection,
        private readonly ThemePackages $packages,
        private readonly ThemeAssets $assets,
    ) {
    }

    public function shouldRun(): bool
    {
        $schema = $this->connection->createSchemaManager();
        if (!$schema->tablesExist(['tl_theme', 'tl_layout']) || !isset(array_change_key_case($schema->listTableColumns('tl_theme'))['nwthemekey'])) {
            return false;
        }

        return [] !== $this->layouts();
    }

    public function run(): MigrationResult
    {
        foreach ($this->layouts() as $id => $head) {
            $this->connection->update('tl_layout', ['head' => $head], ['id' => $id]);
        }

        return $this->createResult(true);
    }

    /**
     * @return array<int, string> new head of every layout that links a theme stylesheet
     */
    private function layouts(): array
    {
        $layouts = [];

        foreach ($this->connection->fetchAllAssociative("SELECT l.id, l.head, t.nwThemeKey FROM tl_layout l JOIN tl_theme t ON t.id = l.pid WHERE t.nwThemeKey != '' AND l.head IS NOT NULL AND l.head != ''") as $layout) {
            $package = $this->packages->get((string) $layout['nwThemeKey']);
            if (null === $package) {
                continue;
            }
            $head = self::removeLinks((string) $layout['head'], [...$this->assets->stylesheets($package), self::WITHDRAWAL]);
            if ($head !== $layout['head']) {
                $layouts[(int) $layout['id']] = $head;
            }
        }

        return $layouts;
    }

    /**
     * Removes <link rel="stylesheet"> tags to the given public files: any attribute
     * order and quotes, extra attributes such as media, a leading slash or path
     * prefix (installation in a subdirectory) and a version parameter. Only the
     * removed tags and the line breaks right after them leave the head.
     *
     * @param list<string> $stylesheets
     */
    public static function removeLinks(string $head, array $stylesheets): string
    {
        $files = implode('|', array_map(static fn (string $file): string => preg_quote($file, '~'), $stylesheets));

        return (string) preg_replace_callback(
            '~[ \t]*<link\b[^>]*>(?:[ \t]*\r?\n)?~i',
            static function (array $match) use ($files): string {
                $tag = $match[0];
                $isStylesheet = preg_match('~\srel\s*=\s*(["\'])\s*stylesheet\s*\1~i', $tag);
                $isThemeFile = preg_match('~\shref\s*=\s*(["\'])(?:[^"\']*/)?(?:'.$files.')(?:\?[^"\']*)?\1~i', $tag);

                return $isStylesheet && $isThemeFile ? '' : $tag;
            },
            $head,
        );
    }
}
