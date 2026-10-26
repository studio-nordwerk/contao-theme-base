<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Theme;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * The installed theme packages, by theme key.
 */
final class ThemePackages
{
    /**
     * @var array<string, ThemePackage>
     */
    private array $packages = [];

    /**
     * @param iterable<ThemePackage> $packages
     */
    public function __construct(#[AutowireIterator(ThemePackage::TAG)] iterable $packages = [])
    {
        foreach ($packages as $package) {
            $this->packages[$package->key()] = $package;
        }
    }

    public function get(string $key): ThemePackage|null
    {
        return $this->packages[$key] ?? null;
    }

    /**
     * @return array<string, ThemePackage>
     */
    public function all(): array
    {
        return $this->packages;
    }
}
