<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       http://www.trendmd.com
 * @since      2.0
 *
 * @package    Trendmd
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'admin/class-trendmd-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-trendmd-uninstaller.php';
Trendmd_Uninstaller::uninstall();
