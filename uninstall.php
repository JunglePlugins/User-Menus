<?php
/**
 * Plugin Uninstall.
 *
 * Removes all plugin data when uninstalled.
 *
 * @package UserMenus
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Define option prefix.
$option_prefix = 'user_menus_';

// Delete all plugin options.
global $wpdb;

// Delete plugin options.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$option_prefix . '%'
	)
);

// Delete menu item meta.
$wpdb->query(
	"DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_user_menus_options'"
);

// Clear any cached data.
wp_cache_flush();
