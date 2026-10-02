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
