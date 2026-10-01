<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Demo;

interface DemoProvider
{
    public function key(): string;

    public function title(): string;

    /** @param array<string, mixed> $state
     * @return array<string, mixed>
     */
    public function apply(int $themeId, array $state): array;

    public function assertCanImport(bool $overwrite): void;
}
