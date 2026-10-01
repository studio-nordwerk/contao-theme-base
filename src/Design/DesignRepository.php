<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\Automator;
use Contao\File;
use Contao\FilesModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class DesignRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function load(int $theme): DesignSettings
    {
        try {
            $row = $this->connection->fetchAssociative('SELECT nwAccent, nwSurface, nwFontPair, nwLogo, nwHeaderStyle, nwAnnouncement FROM tl_theme WHERE id = ?', [$theme]);
        } catch (Exception) {
            // Before the database update the new columns are missing: keep the page working.
            $row = $this->connection->fetchAssociative('SELECT nwAccent, nwSurface, nwFontPair, nwLogo FROM tl_theme WHERE id = ?', [$theme]);
        }
        if (false === $row) {
            return new DesignSettings();
        }

        $headerStyle = (string) ($row['nwHeaderStyle'] ?? 'clear');

        return new DesignSettings((string) $row['nwAccent'], (string) $row['nwSurface'], (string) $row['nwFontPair'], \is_string($row['nwLogo']) ? $row['nwLogo'] : null, isset(DesignSettings::HEADER_STYLES[$headerStyle]) ? $headerStyle : 'clear', mb_substr((string) ($row['nwAnnouncement'] ?? ''), 0, 120));
    }

    public function save(int $theme, DesignSettings $settings): void
    {
        if (!$this->connection->fetchOne('SELECT id FROM tl_theme WHERE id = ?', [$theme])) {
            throw new \InvalidArgumentException('Bitte legen Sie zuerst ein Theme oder Demo-Seiten an.');
        }
        if (null !== $settings->logo && !$this->connection->fetchOne("SELECT uuid FROM tl_files WHERE uuid = ? AND type = 'file' AND extension IN ('jpg', 'jpeg', 'png', 'webp')", [$settings->logo])) {
            throw new \InvalidArgumentException('Bitte wählen Sie ein Bild aus der Dateiverwaltung.');
        }
        if (null !== $settings->logo && null === $this->logoPath($settings)) {
            throw new \InvalidArgumentException('Bitte geben Sie das Logo in der Dateiverwaltung öffentlich frei.');
        }
        $this->connection->update('tl_theme', ['nwAccent' => $settings->accent, 'nwSurface' => $settings->surface, 'nwFontPair' => $settings->fontPair, 'nwLogo' => $settings->logo, 'nwHeaderStyle' => $settings->headerStyle, 'nwAnnouncement' => $settings->announcement], ['id' => $theme]);
        (new Automator())->purgePageCache();
    }

    public function logoPath(DesignSettings $settings): string|null
    {
        if (null === $settings->logo) {
            return null;
        }
        $file = FilesModel::findByUuid($settings->logo);

        return null !== $file && (new File((string) $file->path))->isUnprotected() ? (string) $file->path : null;
    }
}
