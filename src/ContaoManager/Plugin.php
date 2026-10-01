<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Nordwerk\ThemeBaseBundle\ThemeBaseBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [BundleConfig::create(ThemeBaseBundle::class)->setLoadAfter([ContaoCoreBundle::class])];
    }
}
