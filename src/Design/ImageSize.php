<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

use Contao\StringUtil;

/**
 * Value of an "imageSize" field, or a registered size when the field is empty.
 */
final class ImageSize
{
    /**
     * @return array<mixed>|string
     */
    public static function orDefault(mixed $value, string $default): array|string
    {
        $size = StringUtil::deserialize($value, true);

        return [] !== array_filter($size, static fn (mixed $part): bool => '' !== trim((string) $part) && '0' !== (string) $part) ? $size : $default;
    }
}
