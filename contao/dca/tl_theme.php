<?php

declare(strict_types=1);

use Nordwerk\ThemeBaseBundle\Design\DesignSettings;

$GLOBALS['TL_DCA']['tl_theme']['palettes']['default'] .= ';{nw_design_legend},nwLogo,nwAccent,nwSurface,nwPaper,nwText,nwTone1,nwTone2,nwRadius,nwButtonShape,nwFontPair,nwHeaderStyle,nwAnnouncement';
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwLogo'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwLogo'], 'inputType' => 'fileTree', 'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => 'jpg,jpeg,png,webp', 'tl_class' => 'clr'], 'sql' => 'binary(16) NULL'];

foreach (['nwAccent' => '#382f3d', 'nwSurface' => '#fffaf7', 'nwPaper' => '', 'nwText' => '', 'nwTone1' => '', 'nwTone2' => ''] as $field => $default) {
    $GLOBALS['TL_DCA']['tl_theme']['fields'][$field] = ['label' => &$GLOBALS['TL_LANG']['tl_theme'][$field], 'default' => $default, 'inputType' => 'text', 'eval' => ['maxlength' => 6, 'colorpicker' => true, 'isHexColor' => true, 'mandatory' => '' !== $default, 'tl_class' => in_array($field, ['nwPaper', 'nwTone1'], true) ? 'w50 clr' : 'w50'], 'sql' => "varchar(7) NOT NULL default '$default'"];
}
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwRadius'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwRadius'], 'inputType' => 'select', 'options' => DesignSettings::RADII, 'eval' => ['tl_class' => 'w50 clr'], 'sql' => "varchar(16) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwButtonShape'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwButtonShape'], 'inputType' => 'select', 'options' => DesignSettings::BUTTON_SHAPES, 'eval' => ['tl_class' => 'w50'], 'sql' => "varchar(16) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwFontPair'] = ['label' => ['Schriftpaar', 'Alle Schriften werden lokal ausgeliefert.'], 'default' => 'lora-source', 'inputType' => 'select', 'options' => ['lora-source' => 'Lora / Source Sans 3', 'newsreader-source' => 'Newsreader / Source Sans 3', 'nunito' => 'Nunito Sans'], 'eval' => ['tl_class' => 'w50 clr'], 'sql' => "varchar(32) NOT NULL default 'lora-source'"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwHeaderStyle'] = ['label' => ['Kopfzeile', 'Anordnung von Wortmarke, Menü und Symbolen.'], 'default' => 'clear', 'inputType' => 'select', 'options' => DesignSettings::HEADER_STYLES, 'eval' => ['tl_class' => 'w50'], 'sql' => "varchar(16) NOT NULL default 'clear'"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwAnnouncement'] = ['label' => &$GLOBALS['TL_LANG']['tl_theme']['nwAnnouncement'], 'inputType' => 'text', 'eval' => ['maxlength' => 120, 'tl_class' => 'clr'], 'sql' => "varchar(120) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwThemeKey'] = ['sql' => "varchar(64) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwDemoState'] = ['sql' => 'text NULL'];
