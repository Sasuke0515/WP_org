#!/usr/bin/php
<?php
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

namespace WordPress_org\Main_2022\ExportToPatterns;

require __DIR__ . '/includes/utils.php';

/**
 * CLI script for exporting post content from the working site to local pattern/template files.
 *
 * To be run via wp-cli. Expects a manifest file as the only argument.
 * ex: wp eval-file env/export-content/index.php env/page-manifest.json
 */

// This script should only be called in a CLI environment.
if ( 'cli' != php_sapi_name() ) {
	die();
}
$theme_dir = dirname( __DIR__, 2 ) . '/wp-content/themes/wporg-main-2022'; // Docker env.

if ( ! is_dir( $theme_dir ) ) {
	$theme_dir = dirname( __DIR__, 2 ) . '/source/wp-content/themes/wporg-main-2022'; // Local env.
}

$rest_url = 'http://wordpress.org/wp-json/wp/v2/pages?context=wporg_export&slug=%s';
$pattern_path = $theme_dir . '/patterns/%s';
$template_path = $theme_dir . '/templates/%s';

if ( ! isset( $args[0] ) || ! file_exists( $args[0] ) ) {
	die( "No manifest provided.\n" );
}

$manifest_data = file_get_contents( $args[0] );
$manifest_items = json_decode( $manifest_data );
if ( ! $manifest_data || ! $manifest_items ) {
	die( "Unable to read manifest from $args[0]\n" );
}

foreach ( $manifest_items as $item ) {
	if ( $item->slug ) {
		$pattern = $item->pattern ?? $item->slug . '.php';
		$template = $item->template ?? $item->slug . '.html';

		generate_pattern( sprintf( $rest_url, $item->slug ), sprintf( $pattern_path, $pattern ) );
		generate_template( $item->slug, sprintf( $template_path, $template ) );
	}
}
