<?php

declare(strict_types=1);

// Footer contents an editor maintains in the module instead of the template.
// Used by the footer modules of the shop and seminar themes (palettes there).
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterText'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwFooterText'],
    'inputType' => 'textarea',
    'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterNavHeadline'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwFooterNavHeadline'],
    'inputType' => 'text',
    'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterLegalHeadline'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwFooterLegalHeadline'],
    'inputType' => 'text',
    'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterNavModule'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwFooterNavModule'],
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50 clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterLegalModule'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwFooterLegalModule'],
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwWithdrawalPage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwWithdrawalPage'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwDemoNote'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwDemoNote'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

// Header: the menu is a core navigation module, plus an optional button. Used by
// the header modules of the shop and seminar themes.
$GLOBALS['TL_DCA']['tl_module']['fields']['nwNavModule'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwNavModule'],
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50 clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwButtonLabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwButtonLabel'],
    'inputType' => 'text',
    'eval' => ['maxlength' => 40, 'tl_class' => 'w50'],
    'sql' => "varchar(40) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwButtonPage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwButtonPage'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwHideButton'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['nwHideButton'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

// Before 0.5 header and footer built their links themselves. The columns stay so
// the migrations can read them. nwCoreNavigation is the migration state of a
// module: '' = before 0.5, '1' = core navigation modules (0.5.0), '2' = button
// and withdrawal page picked in the module (0.5.1). New modules start at '2'.
$GLOBALS['TL_DCA']['tl_module']['fields']['nwNavigationRoot'] = ['sql' => "int(10) unsigned NOT NULL default '0'"];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterPages'] = ['sql' => 'blob NULL'];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwCoreNavigation'] = ['default' => '2', 'sql' => "char(1) NOT NULL default ''"];
