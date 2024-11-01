<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.trendmd.com
 * @since      2.0
 *
 * @package    Trendmd
 * @subpackage Trendmd/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Trendmd
 * @subpackage Trendmd/admin
 * @author     TrendMD Team
 */
class Trendmd_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    2.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    2.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/trendmd-admin-setting.php';
        $Trendmd_Settings = new TrendMD_Settings();


    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    2.0
     */
    public function enqueue_styles()
    {

        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in Trendmd_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Trendmd_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/trendmd-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    2.0
     */
    public function enqueue_scripts()
    {

        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in Trendmd_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Trendmd_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/trendmd-admin.js', array('jquery'), $this->version, false);

    }

    public static function trendmd_get_journal_id()
    {
        $site_address = get_bloginfo('url');
        $trendMD_endpoint = Trendmd::TRENDMD_URL . '/journals/search?term=' . $site_address;
        $r = json_decode(wp_remote_fopen($trendMD_endpoint));
        $journal_id = 0;
        if (count($r->results) > 0) {
            $journal_id = (int)$r->results[0]->id;
        }

        return (int)$journal_id;
    }

    public static function is_remote_fopen()
    {
        $site_address = get_bloginfo('url');
        $trendMD_endpoint = Trendmd::TRENDMD_URL . '/journals/search?term=' . $site_address;
        $r = json_decode(wp_remote_fopen($trendMD_endpoint));
        if(is_array($r->results)) {
            return true;
        }
        return false;
    }

    public static function get_journal_id()
    {
        return get_option('trendmd_journal_id', array());
    }

    public static function is_set_journal_id()
    {
        $j_id = self::get_journal_id();
        return (is_numeric($j_id) && $j_id > 0);
    }

    public static function category_is_indexed()
    {
        if (in_category(get_option('trendmd_categories_ignored'))) return false;
        return true;
    }

    public static function show_widget()
    {
        return (self::is_set_journal_id() && is_single() && !is_preview() && self::category_is_indexed());
    }

    public static function custom_widget_location()
    {
        return get_option('trendmd_custom_widget_location', array());
    }

    public static function offset()
    {
        global $wpdb;
        $ids = 'SELECT id FROM ' . $wpdb->prefix . 'posts WHERE post_type="post" AND post_status="publish"';
        return $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'trendmd_indexed_articles WHERE id IN (' . $ids . ');');
    }

    static function prepare_string($string)
    {
        return trim(wp_json_encode(html_entity_decode(strip_tags($string), ENT_NOQUOTES, 'UTF-8'), JSON_HEX_APOS), '"\\');
    }

    public static function index_posts()
    {
        $count_posts = wp_count_posts();
        $published_posts = $count_posts->publish;
        $offset = (int)$_POST['trendmd_offset'];

        if ($offset >= $published_posts) {
            echo 'done';
            update_option('trendmd_fetch_articles_at', date('Y-m-d- H:i:s'));
        } else {
            $args = array(
                'posts_per_page' => 1,
                'offset' => $offset,
                'category_name' => '',
                'orderby' => 'post_date',
                'order' => 'DESC',
                'post_type' => 'post',
                'post_status' => 'publish',
                'suppress_filters' => true,
                'category__not_in' => get_option('trendmd_categories_ignored')
            );
            $posts_array = get_posts($args);
            foreach ($posts_array as $post) {
                self::submit_post($post);
                echo ++$offset;
            }
        }
        //Don't forget to always exit in the ajax function.
        exit();
    }

    static function submit_post($post)
    {
        if (!is_object($post)) return;
        global $wpdb;

        if(self::is_set_journal_id() && !in_category(get_option('trendmd_categories_ignored'), $post)) {

            $d = array(
                'method' => 'POST',
                'body' => array(
                    'abstract' => self::prepare_string($post->post_content),
                    'authors' => get_userdata($post->post_author)->display_name,
                    'publication_month' => date('m', strtotime($post->post_date)),
                    'publication_year' => date('Y', strtotime($post->post_date)),
                    'title' => $post->post_title,
                    'force_update' => 1,
                    'url' => get_permalink($post->ID)));
            $trendmd_id = null;

            $querystr = "SELECT trendmd_id FROM " . $wpdb->prefix . "trendmd_indexed_articles  WHERE id = $post->ID  ORDER BY id DESC limit 1";
            $queryr = $wpdb->get_results($querystr, OBJECT);
            if (count($queryr) > 0) $trendmd_id = $queryr[0]->trendmd_id;

            if (!$trendmd_id) {
                $r = wp_remote_post(Trendmd::TRENDMD_URL . '/journals/' . self::get_journal_id() . '/articles', $d);
                $trendmd_id = time().' '.rand();
                $wpdb->query('REPLACE INTO ' . $wpdb->prefix . 'trendmd_indexed_articles(id, trendmd_id) values(' . $post->ID . ', "' . $trendmd_id . '");');
            }
        }
    }

    public static function save_post_callback($post_id, $post)
    {
        if ($post->post_type != 'post' || $post->post_status != 'publish') {
            return;
        }
        self::submit_post($post);
    }

    function plugin_settings_link($links)
    {
        if (function_exists("admin_url")) {
            $settings_link = '<a href="' . admin_url('options-general.php?page=trendmd') . '">' . __('Settings') . '</a>';
            array_unshift($links, $settings_link); // before other links
        }
        return $links;
    }

    function plugin_admin_menu()
    {
        add_menu_page('TrendMD', 'TrendMD', 'manage_options', 'options-general.php?page=trendmd', '', plugin_dir_url( __FILE__ ). 'images/logo.svg', 6);
    }

    function plugin_admin_notice()
    {
        $trendmd_indexed = self::get_journal_id();
        if ($_SERVER["PHP_SELF"] != '/wp-admin/options-general.php' && $trendmd_indexed == 0 && function_exists("admin_url"))
            echo '<div class="notice notice-success"><p><strong>' . sprintf(__('Your TrendMD plugin is now active! Please go to the <a href="%s">plugin settings page</a> to enable indexing.', 'trendmd'), admin_url('options-general.php?page=trendmd')) . '</strong></p></div>';
    }

    public static function init_db()
    {
        global $wpdb;
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'trendmd_indexed_articles' . '(id mediumint(9) PRIMARY KEY, trendmd_id VARCHAR( 255 ) NOT NULL);';
        $wpdb->query($sql);
    }


    public static function deactivate_db()
    {
        global $wpdb;
        $sql = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'trendmd_indexed_articles;';
        $wpdb->query($sql);
    }

}
