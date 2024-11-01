<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.trendmd.com
 * @since      2.0
 *
 * @package    Trendmd
 * @subpackage Trendmd/includes
 */

/**
 * Fired during plugin uninstall.
 *
 * This class defines all code necessary to run during the plugin's uninstall.
 *
 * @since      2.4.2
 * @package    Trendmd
 * @subpackage Trendmd/includes
 * @author     TrendMD Team
 */
class Trendmd_Uninstaller {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    2.4.2
	 */
	public static function uninstall() {
		delete_option('trendmd_journal_id');
		delete_option('trendmd_custom_widget_location');
		delete_option('trendmd_categories_ignored');
		delete_option('trendmd_settings_saved');
        Trendmd_Admin::deactivate_db();
	}

}
