<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Hyperlink: main button, secondary button or text link, without typing a CSS class.
$GLOBALS['TL_DCA']['tl_content']['fields']['nwLinkStyle'] = [
    'label' => ['Darstellung', 'Hauptbutton für die wichtigste Handlung, Nebenbutton für Nebenwege, sonst Textlink.'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['' => 'Textlink', 'primary' => 'Hauptbutton', 'secondary' => 'Nebenbutton'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

PaletteManipulator::create()
    ->addField('nwLinkStyle', 'linkTitle', PaletteManipulator::POSITION_AFTER)
    ->applyToPalette('hyperlink', 'tl_content')
;
