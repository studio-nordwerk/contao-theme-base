<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Nordwerk\ThemeBaseBundle\ThemeBaseBundle;

final class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [BundleConfig::create(ThemeBaseBundle::class)->setLoadAfter([ContaoCoreBundle::class])];
    }

    /**
     * Registers the logo size. It comes first, so a size of the same name in the
     * project's own configuration wins.
     *
     * @param list<array<string, mixed>> $extensionConfigs
     *
     * @return list<array<string, mixed>>
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container): array
    {
        if ('contao' !== $extensionName) {
            return $extensionConfigs;
        }

        array_unshift($extensionConfigs, [
            'image' => [
                'sizes' => [
                    // Logo in the header: at most 180 × 56 px (base.css), sharp on dense screens.
                    'nw_logo' => ['width' => 180, 'height' => 56, 'resize_mode' => 'box', 'densities' => '1x, 2x'],
                ],
            ],
        ]);

        return $extensionConfigs;
    }
}
