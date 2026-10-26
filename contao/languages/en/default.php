<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['MOD']['nw_theme_setup'] = ['Theme setup', 'Demo pages and design of the Nordwerk themes'];

$GLOBALS['TL_LANG']['nw_theme']['setup'] = [
    'navigation' => 'Theme setup',
    'noPackage' => 'No Nordwerk theme is installed.',
    'view' => 'View website',
    'demoTitle' => 'Create demo pages',
    'inventory' => 'A replacing import overwrites these records of the demo (according to the import log in the theme):',
    'overwrite' => 'I confirm: demo contents imported before may be replaced by the template.',
    'overwriteHelp' => 'Without this option an existing demo stays as it is. Additional pages and elements of your own are kept. Every replaced record gets a new version; earlier states are listed under "Versions".',
    'adminOnly' => 'Only administrators may create and replace the demo pages: the import creates and replaces pages, articles, products or events and settings regardless of pagemounts and allowed fields. The overview and the design are available to you in this module.',
    'import' => 'Create demo pages',
    'created' => 'The demo pages have been created.',
    'preserved' => 'The demo already exists. Your contents have been kept.',
    'importedWelcome' => 'The example website has been created. Now enter your own details, then replace texts and pictures in Contao.',
];
$GLOBALS['TL_LANG']['nw_theme']['welcome'] = [
    'title' => 'Welcome! How would you like to start?',
    'intro' => 'Your website has no pages yet. The assistant sets up %s step by step. First, you can have a ready-made example website created.',
    'with' => 'With an example website (recommended)',
    'withText' => 'You see right away how everything works together and then replace texts, pictures and details with your own. This creates:',
    'fictional' => 'All contents belong to a fictitious company and remain editable or deletable in Contao.',
    'withButton' => 'Start with an example website',
    'without' => 'Without an example website',
    'withoutText' => 'You create your pages yourself. You can add the example website at any time later under "Layout → Theme setup".',
    'withoutButton' => 'Start without an example website',
];
$GLOBALS['TL_LANG']['nw_theme']['summary'] = [
    'title' => 'Design',
    'where' => 'Logo, colors and the locally served fonts are maintained under Layout → Themes → Edit theme, in the section Design.',
    'none' => 'Please create the demo pages or a theme for this theme package first.',
    'edit' => 'Edit the design in the theme',
    'wordmark' => 'Theme name as wordmark',
    'logo' => 'Image from the file manager',
    'hidden' => 'Hidden',
    'default' => 'theme default',
];
$GLOBALS['TL_LANG']['nw_theme']['empty'] = [
    'intro' => 'Your website has no pages yet.',
    'after' => 'You can change or delete everything afterwards.',
    'link' => 'Create demo pages',
];
$GLOBALS['TL_LANG']['nw_theme']['tables'] = [
    'tl_theme' => 'Theme',
    'tl_layout' => 'Page layouts',
    'tl_module' => 'Modules',
    'tl_page' => 'Pages',
    'tl_article' => 'Articles',
    'tl_content' => 'Content elements',
    'tl_calendar' => 'Calendars',
    'tl_calendar_events' => 'Events',
    'tl_faq_category' => 'FAQ categories',
    'tl_faq' => 'Questions',
    'tl_mini_shop_product' => 'Products',
    'tl_mini_shop_category' => 'Product categories',
    'tl_nw_testimonial_archive' => 'Testimonial archives',
    'tl_nw_testimonial' => 'Testimonials',
];

$GLOBALS['TL_LANG']['ERR']['nw_design_color'] = 'Please enter a hex color with three or six digits, e.g. 382f3d.';
$GLOBALS['TL_LANG']['ERR']['nw_design_shape'] = 'Please choose one of the given corner and button shapes.';
$GLOBALS['TL_LANG']['ERR']['nw_design_font_pair'] = 'Please choose one of the bundled font pairs.';
$GLOBALS['TL_LANG']['ERR']['nw_design_header_style'] = 'Please choose one of the header layouts.';
$GLOBALS['TL_LANG']['ERR']['nw_design_announcement'] = 'The announcement bar holds at most 120 characters.';
$GLOBALS['TL_LANG']['ERR']['nw_design_contrast'] = 'The text color reaches only %2$s:1 contrast on %1$s. At least 4.5:1 is required. Please change the text color or the background; empty fields use the default of the theme package.';
$GLOBALS['TL_LANG']['ERR']['nw_design_logo_file'] = 'Please choose an image from the file manager (JPG, PNG, WebP or SVG).';
$GLOBALS['TL_LANG']['ERR']['nw_design_logo_public'] = 'Please make the logo public in the file manager.';
$GLOBALS['TL_LANG']['ERR']['nw_demo_locked'] = 'A demo import is already running. Please try again later.';
$GLOBALS['TL_LANG']['ERR']['nw_demo_image_directory'] = 'The demo image folder cannot be created.';
$GLOBALS['TL_LANG']['ERR']['nw_demo_image_copy'] = 'A demo image cannot be copied.';
