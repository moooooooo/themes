<?php 
require_once( '../../bit_setup_inc.php' );
require_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

// Handle Update
$processForm = set_tab();

if( $processForm ) {
	$pref_simple_values = array(
		"site_slide_style",
		"site_biticon_display_style",
	);

	foreach ($pref_simple_values as $svitem) {
		simple_set_value ($svitem, THEMES_PKG_NAME);
	}

	$pref_toggles = array(
		"site_disable_jstabs",
		"site_disable_fat",
	);

	foreach ($pref_toggles as $toggle) {
		simple_set_toggle ($toggle, THEMES_PKG_NAME);
	}

	if( isset( $_REQUEST['fRemoveTheme'] ) ) {
		$gBitThemes->expunge_dir( THEMES_PKG_PATH.'styles/'.$_REQUEST['fRemoveTheme'] );
	}
}

// apply the icon theme
if( !empty( $_REQUEST["site_icon_style"] ) ) {
	$gBitSystem->storeConfig( 'site_icon_style', $_REQUEST["site_icon_style"], THEMES_PKG_NAME );
}

// apply the style layout
if( !empty( $_REQUEST["site_style_layout"] ) ) {
	$gBitSystem->storeConfig( 'site_style_layout', ( ( $_REQUEST["site_style_layout"] != 'remove' ) ? $_REQUEST["site_style_layout"] : NULL ), THEMES_PKG_NAME );
}

// apply the site style
if( !empty( $_REQUEST["site_style"] ) ) {
	$gBitSystem->storeConfig( 'style', $_REQUEST["site_style"], THEMES_PKG_NAME );
	$gBitSystem->storeConfig( 'style_variation', !empty( $_REQUEST["style_variation"] ) ? $_REQUEST["style_variation"] : '', THEMES_PKG_NAME );
	$gPreviewStyle = $_REQUEST["site_style"];
	$gBitSystem->mStyle = $_REQUEST["site_style"];
}

// Get list of available styles
$styles = $gBitThemes->getStyles( NULL, TRUE );
$gBitSmarty->assign_by_ref( "styles", $styles );

$subDirs = array( 'style_info', 'alternate' );
$stylesList = $gBitThemes->getStylesList( NULL, NULL, $subDirs );
$gBitSmarty->assign_by_ref( "stylesList", $stylesList );

$subDirs = array( 'style_info' );
$iconStyles = $gBitThemes->getStylesList( THEMES_PKG_PATH."icon_styles/", NULL, $subDirs );
$gBitSmarty->assign_by_ref( "iconStyles", $iconStyles );

$styleLayouts = $gBitThemes->getStyleLayouts();
$gBitSmarty->assign_by_ref( "styleLayouts", $styleLayouts );

// pick some icons for the preview.
$sampleIcons = array(
	'applications-internet',
	'applications-multimedia',
	'applications-office',
	'dialog-cancel',
	'dialog-error',
	'dialog-information',
	'dialog-ok',
	'dialog-warning',
	'emblem-default',
	'emblem-downloads',
	'emblem-favorite',
	'emblem-important',
	'emblem-photos',
	'emblem-readonly',
	'emblem-shared',
	'emblem-unreadable',
	'go-jump',
	'go-home',
	'go-down',
	'go-next',
	'go-previous',
	'go-up',
	'help-browser',
	'folder-new',
	'folder-open',
	'folder',
);
$gBitSmarty->assign( "sampleIcons", $sampleIcons );

// set the options biticon takes
$biticon_display_options = array(
	'icon' => tra( 'icon' ),
	'text' => tra( 'text' ),
	'icon_text' => tra( 'icon and text' )
);
$gBitSmarty->assign( "biticon_display_options", $biticon_display_options );

// crude method of loading css styling but we can fix this later
$gBitSmarty->assign( "loadLayoutGalaCss", TRUE );

$gBitSystem->display( 'bitpackage:themes/admin_themes_manager.tpl', 'Themes Manager' );
?>
