<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\Automator;
use Contao\File;
use Contao\FilesModel;
use Doctrine\DBAL\Connection;

final readonly class DesignRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function load(int $theme): DesignSettings
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM tl_theme WHERE id = ?', [$theme]);
        if (false === $row) {
            return new DesignSettings();
        }

        return DesignSettings::fromThemeRow($row);
    }

    public function save(int $theme, DesignSettings $settings): void
    {
        if (!$this->connection->fetchOne('SELECT id FROM tl_theme WHERE id = ?', [$theme])) {
            throw new \InvalidArgumentException('Bitte legen Sie zuerst ein Theme oder Demo-Seiten an.');
        }
        $this->validateLogo($settings->logo);
        $this->connection->update('tl_theme', ['nwAccent' => $settings->accent, 'nwSurface' => $settings->surface, 'nwFontPair' => $settings->fontPair, 'nwLogo' => $settings->logo, 'nwHeaderStyle' => $settings->headerStyle, 'nwAnnouncement' => $settings->announcement], ['id' => $theme]);
        (new Automator())->purgePageCache();
    }

    public function validateLogo(string|null $logo): void
    {
        if (null === $logo || '' === $logo) {
            return;
        }
        if (!$this->connection->fetchOne("SELECT uuid FROM tl_files WHERE uuid = ? AND type = 'file' AND extension IN ('jpg', 'jpeg', 'png', 'webp')", [$logo])) {
            throw new \InvalidArgumentException('Bitte wählen Sie ein Bild aus der Dateiverwaltung.');
        }
        if (null === $this->logoPath(new DesignSettings(logo: $logo))) {
            throw new \InvalidArgumentException('Bitte geben Sie das Logo in der Dateiverwaltung öffentlich frei.');
        }
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
