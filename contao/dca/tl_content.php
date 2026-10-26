<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Hyperlink: main button, secondary button or text link, without typing a CSS class.
$GLOBALS['TL_DCA']['tl_content']['fields']['nwLinkStyle'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['nwLinkStyle'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['', 'primary', 'secondary'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['nwLinkStyleOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

PaletteManipulator::create()
    ->addField('nwLinkStyle', 'linkTitle', PaletteManipulator::POSITION_AFTER)
    ->applyToPalette('hyperlink', 'tl_content')
;
