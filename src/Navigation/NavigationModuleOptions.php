<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Navigation;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * The navigation fields of the header and footer modules offer the core
 * navigation modules of the same theme.
 */
final readonly class NavigationModuleOptions
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array<int, string>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.nwNavModule.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.nwFooterNavModule.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.nwFooterLegalModule.options')]
    public function __invoke(DataContainer $dc): array
    {
        // "Edit multiple" has no current record; all modules there belong to the open theme.
        $theme = (int) ($dc->getCurrentRecord()['pid'] ?? $dc->currentPid);
        $options = [];

        foreach ($this->connection->fetchAllKeyValue('SELECT id, name FROM tl_module WHERE pid = ? AND type IN (?, ?) ORDER BY name', [$theme, ...NavigationRenderer::TYPES]) as $id => $name) {
            $options[(int) $id] = (string) $name;
        }

        return $options;
    }
}
