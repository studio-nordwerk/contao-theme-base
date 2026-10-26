<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Design;

/**
 * An invalid design value. The theme form shows it in the backend language
 * ($GLOBALS['TL_LANG']['ERR']['nw_design_<reason>']); the message is the
 * English fallback.
 */
final class DesignException extends \InvalidArgumentException
{
    /**
     * @param array<string, string|float> $parameters
     */
    public function __construct(
        public readonly string $reason,
        string $message,
        public readonly array $parameters = [],
    ) {
        parent::__construct($message);
    }
}
