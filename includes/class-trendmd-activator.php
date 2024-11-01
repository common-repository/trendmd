<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.trendmd.com
 * @since      2.0
 *
 * @package    Trendmd
 * @subpackage Trendmd/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0
 * @package    Trendmd
 * @subpackage Trendmd/includes
 * @author     TrendMD Team
 */
class Trendmd_Activator {

	public static function activate() {
      if(!Trendmd_Admin::is_set_journal_id()) {
        update_option('trendmd_journal_id', Trendmd_Admin::trendmd_get_journal_id());
      }
      Trendmd_Admin::init_db();
	}

}
