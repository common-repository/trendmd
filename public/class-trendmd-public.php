<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.trendmd.com
 * @since      2.0
 *
 * @package    Trendmd
 * @subpackage Trendmd/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Trendmd
 * @subpackage Trendmd/public
 * @author     TrendMD Team
 */
class Trendmd_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public static function trendmd_add_js() {
    if (Trendmd_Admin::show_widget()) {
      wp_enqueue_script(
        'newscript',
        '//trendmd.s3.amazonaws.com/trendmd.min.js'
      );
    }
  }

  public static function trendmd_add_html($content) {
    $content .= '<!--TrendMD v2.4.8-->';
    if (Trendmd_Admin::show_widget() && !Trendmd_Admin::custom_widget_location()) {
      $content .= "<div id='trendmd-suggestions'></div>";
    }
    return $content;
  }

  public static function trendmd_add_widget_js() {
    if (Trendmd_Admin::show_widget()) {
			$content = "<script type='text/javascript'>
          					TrendMD.register({journal_id: '" . Trendmd_Admin::get_journal_id() . "', element: '#trendmd-suggestions', authors: '" .
				Trendmd_Admin::prepare_string(get_the_author()) . "', url: window.location.href, title: '" .
				Trendmd_Admin::prepare_string(get_the_title()) . "', abstract: '" .
				Trendmd_Admin::prepare_string(get_the_content()) . "', publication_year: '" . (int)get_the_date('Y') . "', publication_month: '" . (int)get_the_date('m') . "' });
          				</script>";

      echo $content;
    }
  }

}
