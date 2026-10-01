<?php

declare(strict_types=1);

use Nordwerk\ThemeBaseBundle\Backend\DesignModule;

$GLOBALS['BE_MOD']['design']['nw_theme_design'] = ['callback' => DesignModule::class];
