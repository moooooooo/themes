<?php

global $gBitSystem, $gUpgradeFrom, $gUpgradeTo;

$upgrades = array(

'BONNIE' => array(
	'BWR1' => array(
// STEP 1
array( 'DATADICT' => array(
	array( 'RENAMECOLUMN' => array(
		'tiki_theme_control_categs' => array( '`categId`' => '`category_id` I4' ),
		'tiki_theme_control_objects' => array( '`objId`' => '`obj_id` I4' ),
	),
),
)),

	)
),

	'BWR1' => array(
		'BWR2' => array(
// de-tikify tables
array( 'DATADICT' => array(
	array( 'DROPTABLE' => array(
		'tiki_theme_control_categs',
		'tiki_theme_control_objects',
	)),
	array( 'RENAMETABLE' => array(
		'tiki_user_modules'    => 'themes_custom_modules',
	)),

	array( 'RENAMECOLUMN' => array(
		'tiki_layouts' => array(
			'`rows`' => '`module_rows` I4 NOTNULL',
			'`position`' => '`layout_position` C(1) NOTNULL',
		),
		'tiki_layouts_modules' => array(
			'`rows`' => '`module_rows` I4'
		),
	)),

	// create new theme_layouts table
	array( 'CREATE' => array (
		'themes_layouts' => "
			module_id I4 PRIMARY,
			title C(255),
			layout C(160) NOTNULL DEFAULT 'kernel',
			layout_area C(1) NOTNULL,
			module_rows I4,
			module_rsrc C(250) NOTNULL,
			params C(255),
			cache_time I8,
			groups C(255),
			pos I4 NOTNULL DEFAULT '1'
		",
	)),
	array( 'CREATESEQUENCE' => array(
		'themes_layouts_module_id_seq',
	)),
)),

// merge data from old tables into new themes_layouts
array( 'QUERY' =>
	array(
		'MYSQL' => array(
			// This is still not properly working in MYSQL. someone with the patience to fix it should. XOXO spiderr
			"INSERT INTO `".BIT_DB_PREFIX."themes_layouts` ( module_id, title, layout, layout_area, module_rows, module_rsrc, params, cache_time, groups, pos )
				SELECT themes_layouts_module_id_seq.nextval, tlm.title, tl.layout, tl.layout_position, tlm.module_rows, tmm.module_rsrc, tlm.params, tlm.cache_time, tlm.groups, tl.ord
				FROM `".BIT_DB_PREFIX."tiki_layouts_modules` tlm, `".BIT_DB_PREFIX."tiki_layouts` tl, `".BIT_DB_PREFIX."tiki_module_map` tmm
				WHERE tlm.module_id=tl.module_id AND tmm.module_id=tlm.module_id",
			),
		'PGSQL' => array(
			"INSERT INTO `".BIT_DB_PREFIX."themes_layouts` ( module_id, title, layout, layout_area, module_rows, module_rsrc, params, cache_time, groups, pos )
				SELECT NEXTVAL('themes_layouts_module_id_seq'), tlm.title, tl.layout, tl.layout_position, tlm.module_rows, tmm.module_rsrc, tlm.params, tlm.cache_time, tlm.groups, tl.ord
				FROM `".BIT_DB_PREFIX."tiki_layouts_modules` tlm, `".BIT_DB_PREFIX."tiki_layouts` tl, `".BIT_DB_PREFIX."tiki_module_map` tmm
				WHERE tlm.module_id=tl.module_id AND tmm.module_id=tlm.module_id",
			),
		//'SQL92' => ???
	),
),

// we're done - remove old tables
/*
array( 'DATADICT' => array(
	array( 'DROPTABLE' => array(
		'tiki_layouts',
		'tiki_layouts_modules',
		'tiki_module_map',
	)),
)),
 */

// themes_custom_modules
array( 'DATADICT' => array(
	// rename original column
	array( 'RENAMECOLUMN' => array(
		'themes_custom_modules'  => array(
			'`name`'  => "`temp_name` C(255) NOTNULL",            // set NOTNULL PRIMARY
			'`title`' => "`temp_title` C(255)",                   // set error_message NOTNULL DEFAULT ''
		),
	)),
)),
// create new columns and copy all the data accross
// the way this was done before didn't work for some reason - yettyn
array( 'QUERY' =>
	array( 'SQL92' => array(
		"ALTER TABLE `".BIT_DB_PREFIX."themes_custom_modules` ADD COLUMN  `name` VARCHAR(40) FIRST",
		"ALTER TABLE `".BIT_DB_PREFIX."themes_custom_modules` ADD COLUMN  `title` VARCHAR(200) AFTER `name`",
		"UPDATE `".BIT_DB_PREFIX."themes_custom_modules` SET `name` = `temp_name`",
		"UPDATE `".BIT_DB_PREFIX."themes_custom_modules` SET `title` = `temp_title`",
		"ALTER TABLE `".BIT_DB_PREFIX."themes_custom_modules` DROP COLUMN  `temp_name`",
		"ALTER TABLE `".BIT_DB_PREFIX."themes_custom_modules` ADD PRIMARY KEY (`name`)",
	)),
),
// drop original column
array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'themes_custom_modules' => array( '`temp_title`' ),
	)),
)),

array( 'QUERY' =>
	array( 'SQL92' => array(
		"INSERT INTO `".BIT_DB_PREFIX."kernel_config` ( `config_name`, `package`, `config_value` ) VALUES ( 'site_style_layout', '".THEMES_PKG_NAME."', 'gala_13' )"
		)
	),
)),

));

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( THEMES_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}


?>
