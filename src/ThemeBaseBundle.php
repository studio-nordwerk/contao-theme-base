<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle;

use Nordwerk\ThemeBaseBundle\Theme\ThemePackage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class ThemeBaseBundle extends AbstractBundle
{
    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        $containerConfigurator->import('../config/services.yaml');
        $containerBuilder->registerForAutoconfiguration(ThemePackage::class)->addTag(ThemePackage::TAG);
    }
}
