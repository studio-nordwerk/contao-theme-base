<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * Checks for the values of style presets and their axes. They go raw into a style
 * element and their ids into CSS selectors and URLs, so both are restricted.
 */
final class StyleValues
{
    /**
     * Enough for colors, lengths, clamp(), font stacks and shadows; no ";", braces or
     * "<", so a value cannot leave its declaration or the style element.
     */
    private const VALUE = '/^[\w\s#.,%()"\'+\-*\/]+$/u';

    public static function id(string $id): string
    {
        if (!preg_match('/^[a-z0-9-]+$/D', $id)) {
            throw new \InvalidArgumentException(\sprintf('Style id "%s" may only hold a-z, 0-9 and "-".', $id));
        }

        return $id;
    }

    /**
     * @param array<string, string> ...$groups
     */
    public static function safe(string $owner, array ...$groups): void
    {
        foreach ($groups as $group) {
            foreach ($group as $value) {
                if (!preg_match(self::VALUE, $value)) {
                    throw new \InvalidArgumentException(\sprintf('Style "%s" has a value the theme does not accept: %s', $owner, $value));
                }
            }
        }
    }

    /**
     * @param array<string, string> $group
     * @param list<string>          $keys
     */
    public static function require(string $owner, string $what, array $group, array $keys): void
    {
        foreach ($keys as $key) {
            if (!isset($group[$key])) {
                throw new \InvalidArgumentException(\sprintf('Style "%s" lacks the %s "%s".', $owner, $what, $key));
            }
        }
    }

    /**
     * Strings of an object of data/styles.json; other values are dropped.
     *
     * @return array<string, string>
     */
    public static function strings(mixed $group): array
    {
        $values = [];

        foreach (\is_array($group) ? $group : [] as $key => $value) {
            if (\is_string($key) && (\is_string($value) || \is_int($value) || \is_float($value))) {
                $values[$key] = (string) $value;
            }
        }

        return $values;
    }
}
