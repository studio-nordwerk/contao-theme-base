<?php

declare(strict_types=1);

use Nordwerk\ThemeBaseBundle\Design\DesignSettings;

$GLOBALS['TL_DCA']['tl_theme']['palettes']['default'] .= ';{nw_design_legend},nwLogo,nwLogoSize,nwAccent,nwSurface,nwPaper,nwText,nwTone1,nwTone2,nwRadius,nwButtonShape,nwFontPair,nwHeaderStyle,nwAnnouncement';
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwLogo'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwLogo'], 'inputType' => 'fileTree', 'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => implode(',', DesignSettings::LOGO_EXTENSIONS), 'tl_class' => 'clr'], 'sql' => 'binary(16) NULL'];
// Empty uses the registered size _nw_logo (180 × 56 px box, 1x and 2x).
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwLogoSize'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwLogoSize'], 'inputType' => 'imageSize', 'reference' => &$GLOBALS['TL_LANG']['MSC'], 'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50 clr'], 'sql' => "varchar(255) NOT NULL default ''"];

foreach (['nwAccent' => '#382f3d', 'nwSurface' => '#fffaf7', 'nwPaper' => '', 'nwText' => '', 'nwTone1' => '', 'nwTone2' => ''] as $field => $default) {
    $GLOBALS['TL_DCA']['tl_theme']['fields'][$field] = ['label' => &$GLOBALS['TL_LANG']['tl_theme'][$field], 'default' => $default, 'inputType' => 'text', 'eval' => ['maxlength' => 6, 'colorpicker' => true, 'isHexColor' => true, 'mandatory' => '' !== $default, 'tl_class' => in_array($field, ['nwPaper', 'nwTone1'], true) ? 'w50 clr' : 'w50'], 'sql' => "varchar(7) NOT NULL default '$default'"];
}
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwRadius'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwRadius'], 'inputType' => 'select', 'options' => DesignSettings::RADII, 'reference' => &$GLOBALS['TL_LANG']['tl_theme']['nwRadiusOptions'], 'eval' => ['tl_class' => 'w50 clr'], 'sql' => "varchar(16) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwButtonShape'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwButtonShape'], 'inputType' => 'select', 'options' => DesignSettings::BUTTON_SHAPES, 'reference' => &$GLOBALS['TL_LANG']['tl_theme']['nwButtonShapeOptions'], 'eval' => ['tl_class' => 'w50'], 'sql' => "varchar(16) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwFontPair'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwFontPair'], 'default' => 'lora-source', 'inputType' => 'select', 'options' => array_keys(DesignSettings::FONT_PAIRS), 'reference' => &$GLOBALS['TL_LANG']['tl_theme']['nwFontPairOptions'], 'eval' => ['tl_class' => 'w50 clr'], 'sql' => "varchar(32) NOT NULL default 'lora-source'"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwHeaderStyle'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwHeaderStyle'], 'default' => 'clear', 'inputType' => 'select', 'options' => DesignSettings::HEADER_STYLES, 'reference' => &$GLOBALS['TL_LANG']['tl_theme']['nwHeaderStyleOptions'], 'eval' => ['tl_class' => 'w50'], 'sql' => "varchar(16) NOT NULL default 'clear'"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwAnnouncement'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwAnnouncement'], 'inputType' => 'text', 'eval' => ['maxlength' => 120, 'tl_class' => 'clr'], 'sql' => "varchar(120) NOT NULL default ''"];
// Key of the theme package (nordwerk-shop-v1, seminar) and the import log of its
// demo. Both are shown in "Theme einrichten", not in this form.
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwThemeKey'] = ['sql' => "varchar(64) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwDemoState'] = ['sql' => 'text NULL'];
