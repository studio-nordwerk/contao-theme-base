<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

final class DesignCallbacks
{
    #[AsCallback(table: 'tl_theme', target: 'fields.nwAccent.save')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwSurface.save')]
    public function validateColor(string $value): string
    {
        new DesignSettings(accent: $value);

        return $value;
    }
}
