<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

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

    public function validateLogo(string|null $logo): void
    {
        if (null === $logo || '' === $logo) {
            return;
        }
        $extensions = DesignSettings::LOGO_EXTENSIONS;
        if (!$this->connection->fetchOne("SELECT uuid FROM tl_files WHERE uuid = ? AND type = 'file' AND extension IN (".implode(', ', array_fill(0, \count($extensions), '?')).')', [$logo, ...$extensions])) {
            throw new DesignException('logo_file', 'Please choose an image from the file manager.');
        }
        if (null === $this->logoPath(new DesignSettings(logo: $logo))) {
            throw new DesignException('logo_public', 'Please make the logo public in the file manager.');
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
