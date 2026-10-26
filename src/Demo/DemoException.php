<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Demo;

/**
 * A demo import that must not run. The backend and the console show the reason
 * from $GLOBALS['TL_LANG']['ERR']['nw_demo_<reason>'] (sprintf parameters); the
 * message is the English fallback.
 */
final class DemoException extends \RuntimeException
{
    /**
     * @param list<string> $parameters
     */
    public function __construct(
        public readonly string $reason,
        string $message,
        public readonly array $parameters = [],
    ) {
        parent::__construct($message);
    }
}
