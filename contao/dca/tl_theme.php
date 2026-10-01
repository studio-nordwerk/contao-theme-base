<?php

declare(strict_types=1);

use Nordwerk\ThemeBaseBundle\Design\DesignSettings;

$GLOBALS['TL_DCA']['tl_theme']['palettes']['default'] .= ';{nw_design_legend},nwLogo,nwAccent,nwSurface,nwFontPair,nwHeaderStyle,nwAnnouncement';
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwLogo'] = ['label' => ['Logo', 'Eine Datei aus der Contao-Dateiverwaltung.'], 'inputType' => 'fileTree', 'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => 'jpg,jpeg,png,webp', 'tl_class' => 'clr'], 'sql' => 'binary(16) NULL'];

foreach (['nwAccent' => '#382f3d', 'nwSurface' => '#fffaf7'] as $field => $default) {
    $GLOBALS['TL_DCA']['tl_theme']['fields'][$field] = ['label' => &$GLOBALS['TL_LANG']['tl_theme'][$field], 'default' => $default, 'inputType' => 'text', 'eval' => ['maxlength' => 7, 'tl_class' => 'w50'], 'sql' => "varchar(7) NOT NULL default '$default'"];
}
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwFontPair'] = ['label' => ['Schriftpaar', 'Alle Schriften werden lokal ausgeliefert.'], 'default' => 'lora-source', 'inputType' => 'select', 'options' => ['lora-source' => 'Lora / Source Sans 3', 'newsreader-source' => 'Newsreader / Source Sans 3', 'nunito' => 'Nunito Sans'], 'eval' => ['tl_class' => 'w50'], 'sql' => "varchar(32) NOT NULL default 'lora-source'"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwHeaderStyle'] = ['label' => ['Kopfzeile', 'Anordnung von Wortmarke, Menü und Symbolen.'], 'default' => 'clear', 'inputType' => 'select', 'options' => DesignSettings::HEADER_STYLES, 'eval' => ['tl_class' => 'w50'], 'sql' => "varchar(16) NOT NULL default 'clear'"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwAnnouncement'] = ['label' => ['Hinweisleiste', 'Optionale Zeile über der Kopfzeile, z. B. „Kostenloser Versand ab 30 €“. Höchstens 120 Zeichen.'], 'inputType' => 'text', 'eval' => ['maxlength' => 120, 'tl_class' => 'w50'], 'sql' => "varchar(120) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwThemeKey'] = ['sql' => "varchar(64) NOT NULL default ''"];
$GLOBALS['TL_DCA']['tl_theme']['fields']['nwDemoState'] = ['sql' => 'text NULL'];
