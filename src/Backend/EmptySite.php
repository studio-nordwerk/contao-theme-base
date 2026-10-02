<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Backend;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Detects a fresh installation, so the backend can offer the theme demo first.
 */
final readonly class EmptySite
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * True as long as the site has no root page and this theme's demo was never imported.
     */
    public function needsDemo(string $themeKey): bool
    {
        try {
            if (false !== $this->connection->fetchOne("SELECT id FROM tl_page WHERE type = 'root' LIMIT 1")) {
                return false;
            }

            return false === $this->connection->fetchOne('SELECT id FROM tl_theme WHERE nwThemeKey = ?', [$themeKey]);
        } catch (Exception) {
            // Before the database migration the tables or columns may be missing.
            return false;
        }
    }

    /**
     * Backend start page notice with a link to the demo import, or an empty string.
     */
    public function message(string $themeKey, string $offer, string $url): string
    {
        if (!$this->needsDemo($themeKey)) {
            return '';
        }

        return \sprintf(
            '<p class="tl_info" data-nw-empty-site>Ihre Website hat noch keine Seiten. %s Sie können alles danach anpassen oder löschen. <a href="%s">Demo-Seiten anlegen</a></p>',
            htmlspecialchars($offer, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }
}
