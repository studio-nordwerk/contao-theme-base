<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Validation of the design fields in the theme form. Saving the form invalidates
 * the cache tag contao.db.tl_theme.<id>, which every page of the theme carries
 * (see ThemeAssets), so cached pages show new colors right away.
 */
final class DesignCallbacks
{
    public function __construct(
        private RequestStack $requestStack,
        private DesignRepository $repository,
    ) {
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

        try {
            DesignSettings::fromThemeRow($row)->validateSurfaceContrast();

            return '' === trim($value) ? '' : DesignSettings::normalizeColor($value);
        } catch (DesignException $exception) {
            throw new \InvalidArgumentException(self::message($exception), 0, $exception);
        }
    }

    #[AsCallback(table: 'tl_theme', target: 'fields.nwLogo.save')]
    public function validateLogo(string|null $value): string|null
    {
        try {
            $this->repository->validateLogo($value);
        } catch (DesignException $exception) {
            throw new \InvalidArgumentException(self::message($exception), 0, $exception);
        }

        return $value;
    }

    /**
     * The reason in the backend language, the English message without a translation.
     */
    public static function message(DesignException $exception): string
    {
        $text = $GLOBALS['TL_LANG']['ERR']['nw_design_'.$exception->reason] ?? null;
        if (!\is_string($text)) {
            return $exception->getMessage();
        }
        if ('contrast' !== $exception->reason) {
            return $text;
        }
        $field = (string) ($exception->parameters['field'] ?? '');
        $label = $GLOBALS['TL_LANG']['tl_theme'][$field][0] ?? $field;
        $separator = $GLOBALS['TL_LANG']['MSC']['decimalSeparator'] ?? '.';

        return \sprintf($text, $label, number_format((float) ($exception->parameters['ratio'] ?? 0), 2, \is_string($separator) ? $separator : '.', ''));
    }
}
