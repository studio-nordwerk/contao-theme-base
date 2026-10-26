<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Demo;

/**
 * A demo provider that can list the records its import log (tl_theme.nwDemoState)
 * refers to. The importer versions them and invalidates their cache tags; the
 * backend shows them as the records a replacing import would overwrite.
 */
interface DemoInventory
{
    /**
     * @param array<string, mixed> $state
     *
     * @return array<string, list<int>> record IDs by table
     */
    public function records(array $state): array;
}
