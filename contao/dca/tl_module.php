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
$GLOBALS['TL_DCA']['tl_module']['fields']['nwFooterPages'] = [
    'label' => ['Weitere Seiten', 'Zusätzliche Links in der Seitenspalte, z. B. Kontakt oder FAQ. Reihenfolge per Ziehen.'],
    'inputType' => 'pageTree',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'isSortable' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwDemoNote'] = [
    'label' => ['Demo-Hinweis anzeigen', 'Hinweis, dass Betreiberangaben und Rechtstexte noch Platzhalter sind. Vor dem Start ausschalten.'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

// Header: navigation start point and an optional button, used by the header
// modules of the shop and seminar themes.
$GLOBALS['TL_DCA']['tl_module']['fields']['nwNavigationRoot'] = [
    'label' => ['Startpunkt der Navigation', 'Die Unterseiten dieser Seite bilden das Menü. Leer lassen für den Startpunkt der aktuellen Website. Einzelne Seiten blenden Sie über „Im Menü verstecken“ in der Seite aus.'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwButtonLabel'] = [
    'label' => ['Button-Text', 'Hervorgehobener Button im Kopfbereich, z. B. „Termine“. Leer lassen für die Vorgabe des Themes.'],
    'inputType' => 'text',
    'eval' => ['maxlength' => 40, 'tl_class' => 'w50'],
    'sql' => "varchar(40) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwButtonPage'] = [
    'label' => ['Button-Ziel', 'Seite, zu der der Button führt. Leer lassen für die Vorgabe des Themes.'],
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_module']['fields']['nwHideButton'] = [
    'label' => ['Button ausblenden', 'Keinen hervorgehobenen Button im Kopfbereich zeigen.'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];
