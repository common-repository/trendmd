<?php

/**
 *
 * @link              http://www.trendmd.com
 * @since             2.0
 * @package           Trendmd
 *
 * @wordpress-plugin
 * Plugin Name:       TrendMD
 * Plugin URI:        http://www.trendmd.com
 * Description:       This plugin will add the TrendMD recommendations widget to your WordPress website. The TrendMD recommendations widget is used by scholarly publishers to increase their readership and revenue.
 * Version:           2.4.8
 * Author:            TrendMD Team
 * Author URI:        http://www.trendmd.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       trendmd
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-trendmd-activator.php
 */
function activate_trendmd() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-trendmd-activator.php';
    Trendmd_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-trendmd-deactivator.php
 */
function deactivate_trendmd() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-trendmd-deactivator.php';
    Trendmd_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_trendmd' );
register_deactivation_hook( __FILE__, 'deactivate_trendmd' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-trendmd.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0
 */

global $plugin;
$plugin = plugin_basename(__FILE__);

function run_trendmd() {

    $plugin = new Trendmd();
    $plugin->run();

}
run_trendmd();
