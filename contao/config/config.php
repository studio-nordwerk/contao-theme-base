<?php

declare(strict_types=1);

use Nordwerk\ThemeBaseBundle\Backend\ThemeSetupModule;

// Own module with its own right in the user groups, next to "Themes".
$GLOBALS['BE_MOD']['design'][ThemeSetupModule::MODULE] = ['callback' => ThemeSetupModule::class];
