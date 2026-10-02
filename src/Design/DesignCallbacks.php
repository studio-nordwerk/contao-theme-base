<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\Automator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;

final class DesignCallbacks
{
    public function __construct(private RequestStack $requestStack, private DesignRepository $repository)
    {
    }

    #[AsCallback(table: 'tl_theme', target: 'fields.nwAccent.load')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwSurface.load')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwPaper.load')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwText.load')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwTone1.load')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwTone2.load')]
    public function loadColor(string|null $value): string
    {
        return ltrim($value ?? '', '#');
    }

    #[AsCallback(table: 'tl_theme', target: 'fields.nwAccent.save')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwSurface.save')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwPaper.save')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwText.save')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwTone1.save')]
    #[AsCallback(table: 'tl_theme', target: 'fields.nwTone2.save')]
    public function validateColor(string $value, DataContainer $dc): string
    {
        $row = $dc->getCurrentRecord() ?? [];
        $request = $this->requestStack->getCurrentRequest();
        // Validate the complete submission, not a mixture of new and stored colors.
        foreach (['nwAccent', 'nwSurface', 'nwPaper', 'nwText', 'nwTone1', 'nwTone2'] as $field) {
            if ($request?->request->has($field)) {
                $row[$field] = $request->request->getString($field);
            }
        }
        $row[$dc->field] = $value;
        $settings = DesignSettings::fromThemeRow($row);
        $settings->validateSurfaceContrast();

        return '' === trim($value) ? '' : DesignSettings::normalizeColor($value);
    }

    #[AsCallback(table: 'tl_theme', target: 'fields.nwLogo.save')]
    public function validateLogo(string|null $value): string|null
    {
        $this->repository->validateLogo($value);

        return $value;
    }

    #[AsCallback(table: 'tl_theme', target: 'config.onsubmit')]
    public function purgePageCache(): void
    {
        (new Automator())->purgePageCache();
    }
}
