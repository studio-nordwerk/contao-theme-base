<?php

declare(strict_types=1);

// Footer contents an editor maintains in the module instead of the template.
// Used by the footer modules of the shop and seminar themes (palettes there).
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterText'] = [
    'label' => ['Freitext', 'Steht unter Name und Kontakt, z. B. Öffnungszeiten oder ein Satz über den Betrieb.'],
    'inputType' => 'textarea',
    'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterNavHeadline'] = [
    'label' => ['Überschrift der Seitenspalte', 'Leer lassen für die Vorgabe des Themes (z. B. „Shop“ oder „Seminare“).'],
    'inputType' => 'text',
    'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterLegalHeadline'] = [
    'label' => ['Überschrift der Rechtsspalte', 'Leer lassen für „Rechtliches“.'],
    'inputType' => 'text',
    'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterNavModule'] = [
    'label' => ['Navigation der Seitenspalte', 'Navigationsmodul mit den Links der Seitenspalte, z. B. eine „Individuelle Navigation“ mit Ihren wichtigsten Seiten.'],
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50 clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterLegalModule'] = [
    'label' => ['Navigation der Rechtsspalte', 'Navigationsmodul mit den rechtlichen Seiten, z. B. eine „Individuelle Navigation“ mit Impressum und Datenschutz.'],
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwWithdrawalPage'] = [
    'label' => ['Seite „Vertrag widerrufen“', 'Die Rechtsspalte verlinkt diese Seite immer mit dem Wortlaut „Vertrag widerrufen“. Ohne Seite entfällt der Link.'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwDemoNote'] = [
    'label' => ['Demo-Hinweis anzeigen', 'Hinweis, dass Betreiberangaben und Rechtstexte noch Platzhalter sind. Vor dem Start ausschalten.'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

// Header: the menu is a core navigation module, plus an optional button. Used by
// the header modules of the shop and seminar themes.
$GLOBALS['TL_DCA']['tl_module']['fields']['nwNavModule'] = [
    'label' => ['Hauptnavigation', 'Navigationsmodul für das Menü der Kopfzeile, in der Regel ein „Navigationsmenü“. Seiten, Reihenfolge und „Im Menü verstecken“ pflegen Sie in der Seitenstruktur.'],
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50 clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwButtonLabel'] = [
    'label' => ['Button-Text', 'Hervorgehobener Button in der Kopfzeile, z. B. „Termine“. Leer lassen für den Namen der Zielseite.'],
    'inputType' => 'text',
    'eval' => ['maxlength' => 40, 'tl_class' => 'w50'],
    'sql' => "varchar(40) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwButtonPage'] = [
    'label' => ['Button-Ziel', 'Seite, zu der der Button führt. Ohne Ziel erscheint kein Button.'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwHideButton'] = [
    'label' => ['Button ausblenden', 'Keinen hervorgehobenen Button in der Kopfzeile zeigen.'],
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
