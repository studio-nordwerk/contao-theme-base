<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['MOD']['nw_theme_setup'] = ['Theme einrichten', 'Demo-Seiten und Gestaltung der Nordwerk-Themes'];

$GLOBALS['TL_LANG']['nw_theme']['setup'] = [
    'navigation' => 'Theme einrichten',
    'noPackage' => 'Es ist kein Nordwerk-Theme installiert.',
    'view' => 'Seite ansehen',
    'demoTitle' => 'Demo-Seiten anlegen',
    'inventory' => 'Ein ersetzender Import überschreibt diese Datensätze der Demo (laut Importprotokoll im Theme):',
    'overwrite' => 'Ich bestätige: Bereits importierte Demo-Inhalte dürfen durch die Vorlage ersetzt werden.',
    'overwriteHelp' => 'Ohne Auswahl bleibt eine vorhandene Demo unverändert. Eigene zusätzliche Seiten und Elemente bleiben erhalten. Jeder ersetzte Datensatz bekommt eine neue Version, frühere Stände finden Sie unter „Versionen“.',
    'adminOnly' => 'Demo-Seiten anlegen und ersetzen dürfen nur Administratoren: Der Import legt Seiten, Artikel, Produkte bzw. Termine und Einstellungen an und ersetzt sie, unabhängig von Pagemounts und erlaubten Feldern. Übersicht und Gestaltung stehen Ihnen mit diesem Modul zur Verfügung.',
    'import' => 'Demo-Seiten anlegen',
    'created' => 'Die Demo-Seiten wurden angelegt.',
    'preserved' => 'Die Demo ist bereits vorhanden. Ihre Inhalte wurden erhalten.',
    'importedWelcome' => 'Die Beispielseite ist angelegt. Tragen Sie jetzt Ihre eigenen Angaben ein, danach ersetzen Sie Texte und Bilder in Contao.',
];
$GLOBALS['TL_LANG']['nw_theme']['welcome'] = [
    'title' => 'Willkommen! Wie möchten Sie starten?',
    'intro' => 'Ihre Website hat noch keine Seiten. Der Assistent richtet %s Schritt für Schritt ein. Vorher können Sie eine fertige Beispielseite anlegen lassen.',
    'with' => 'Mit Beispielseite (empfohlen)',
    'withText' => 'Sie sehen sofort, wie alles zusammenspielt, und ersetzen danach Texte, Bilder und Angaben durch Ihre eigenen. Angelegt werden:',
    'fictional' => 'Alle Inhalte gehören einer fiktiven Firma und bleiben in Contao bearbeitbar oder löschbar.',
    'withButton' => 'Mit Beispielseite starten',
    'without' => 'Ohne Beispielseite',
    'withoutText' => 'Sie legen Ihre Seiten selbst an. Die Beispielseite können Sie später jederzeit unter „Layout → Theme einrichten“ nachholen.',
    'withoutButton' => 'Ohne Beispielseite starten',
];
$GLOBALS['TL_LANG']['nw_theme']['summary'] = [
    'title' => 'Gestaltung',
    'where' => 'Logo, Farben und lokal ausgelieferte Schriften pflegen Sie unter Layout → Themes → Theme bearbeiten, im Bereich Gestaltung.',
    'none' => 'Bitte legen Sie zuerst Demo-Seiten oder ein Theme für dieses Theme-Paket an.',
    'edit' => 'Gestaltung im Theme bearbeiten',
    'wordmark' => 'Theme-Name als Wortmarke',
    'logo' => 'Bild aus der Dateiverwaltung',
    'hidden' => 'Ausgeblendet',
    'default' => 'Vorgabe des Themes',
];
$GLOBALS['TL_LANG']['nw_theme']['empty'] = [
    'intro' => 'Ihre Website hat noch keine Seiten.',
    'after' => 'Sie können alles danach anpassen oder löschen.',
    'link' => 'Demo-Seiten anlegen',
];
$GLOBALS['TL_LANG']['nw_theme']['tables'] = [
    'tl_theme' => 'Theme',
    'tl_layout' => 'Seitenlayouts',
    'tl_module' => 'Module',
    'tl_page' => 'Seiten',
    'tl_article' => 'Artikel',
    'tl_content' => 'Inhaltselemente',
    'tl_calendar' => 'Kalender',
    'tl_calendar_events' => 'Events',
    'tl_faq_category' => 'FAQ-Kategorien',
    'tl_faq' => 'Fragen',
    'tl_mini_shop_product' => 'Produkte',
    'tl_mini_shop_category' => 'Produktkategorien',
    'tl_nw_testimonial_archive' => 'Archive für Kundenstimmen',
    'tl_nw_testimonial' => 'Kundenstimmen',
];

$GLOBALS['TL_LANG']['ERR']['nw_design_color'] = 'Bitte geben Sie eine Hex-Farbe mit drei oder sechs Stellen ein, z. B. 382f3d.';
$GLOBALS['TL_LANG']['ERR']['nw_design_shape'] = 'Bitte wählen Sie eine der vorgegebenen Ecken- und Buttonformen.';
$GLOBALS['TL_LANG']['ERR']['nw_design_font_pair'] = 'Bitte wählen Sie eines der mitgelieferten Schriftpaare.';
$GLOBALS['TL_LANG']['ERR']['nw_design_header_style'] = 'Bitte wählen Sie eine der Kopfzeilen.';
$GLOBALS['TL_LANG']['ERR']['nw_design_announcement'] = 'Die Hinweisleiste hat Platz für höchstens 120 Zeichen.';
$GLOBALS['TL_LANG']['ERR']['nw_design_contrast'] = 'Textfarbe auf %s erreicht nur %s:1 Kontrast. Erforderlich sind mindestens 4,5:1. Bitte ändern Sie die Textfarbe oder den Hintergrund; leere Felder verwenden die Vorgabe des Theme-Pakets.';
$GLOBALS['TL_LANG']['ERR']['nw_design_logo_file'] = 'Bitte wählen Sie ein Bild aus der Dateiverwaltung (JPG, PNG, WebP oder SVG).';
$GLOBALS['TL_LANG']['ERR']['nw_design_logo_public'] = 'Bitte geben Sie das Logo in der Dateiverwaltung öffentlich frei.';
$GLOBALS['TL_LANG']['ERR']['nw_demo_locked'] = 'Ein Demo-Import läuft bereits. Bitte versuchen Sie es später erneut.';
$GLOBALS['TL_LANG']['ERR']['nw_demo_image_directory'] = 'Das Demo-Bilderverzeichnis kann nicht angelegt werden.';
$GLOBALS['TL_LANG']['ERR']['nw_demo_image_copy'] = 'Ein Demo-Bild kann nicht kopiert werden.';
