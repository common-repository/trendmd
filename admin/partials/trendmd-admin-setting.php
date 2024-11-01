<?php

/**
 * Provide a admin area view for the plugin
 *
 *
 * @link       http://www.trendmd.com
 * @since      2.0
 *
 * @package    Trendmd
 * @subpackage Trendmd/admin/partials
 */

if (!class_exists('TrendMD_Settings')) {
  class TrendMD_Settings {
    /**
     * Construct the plugin object
     */
    public function __construct() {
      // register actions
      add_action('admin_init', array(&$this, 'admin_init'));
      add_action('admin_menu', array(&$this, 'add_menu'));
    } // END public function __construct

    /**
     * hook into WP's admin_init action hook
     */
    public function admin_init() {
      // register your plugin's settings
      register_setting('trendmd-group', 'trendmd_journal_id');
      register_setting('trendmd-group', 'trendmd_custom_widget_location');
      register_setting('trendmd-group', 'trendmd_categories_ignored');
      register_setting('trendmd-group', 'trendmd_settings_saved');

      $journal_id_label = '';
      if(isset($_GET['page']) && $_GET['page'] == 'trendmd' && (!Trendmd_Admin::is_set_journal_id() && !Trendmd_Admin::is_remote_fopen())) {
        $journal_id_label = 'TrendMD website ID (digits only):';
      }
      // add your settings section
      add_settings_section(
        'trendmd-section',
        '',
        array(&$this, 'settings_section_trendmd'),
        'trendmd'
      );

      // add your setting's fields
      if(isset($_GET['page']) && $_GET['page'] == 'trendmd' && (Trendmd_Admin::is_set_journal_id() && !Trendmd_Admin::is_remote_fopen())) {
        add_settings_field(
            'trendmd-journal_show_id',
            'TrendMD website ID:',
            array(&$this, 'settings_field_input_text_show_journal_id'),
            'trendmd',
            'trendmd-section',
            array(
                'field' => 'trendmd_journal_id'
            )
        );
      }

      add_settings_field(
          'trendmd-journal_id',
          $journal_id_label,
          array(&$this, 'settings_field_input_text_journal_id'),
          'trendmd',
          'trendmd-section',
          array(
              'field' => 'trendmd_journal_id'
          )
      );
      if(isset($_GET['page']) && $_GET['page'] == 'trendmd' && (Trendmd_Admin::is_set_journal_id() || Trendmd_Admin::is_remote_fopen())) {
        add_settings_field(
            'trendmd-custom_widget_location',
            'Do not auto-embed widget code, I want to place the widget in a custom location',
            array(&$this, 'settings_field_input_checkbox'),
            'trendmd',
            'trendmd-section',
            array(
                'field' => 'trendmd_custom_widget_location'
            )
        );

        if (get_option('trendmd_settings_saved') == 1) {
          $future = 'future';
        } else {
          $future = '';
        }

        add_settings_field(
            'trendmd-categories_ignored',
            'Do not index ' . $future . ' posts from the following categories:',
            array(&$this, 'settings_categories'),
            'trendmd',
            'trendmd-section',
            array(
                'field' => 'trendmd_categories_ignored'
            )
        );

        add_settings_field(
          'trendmd-settings_saved',
          '',
          array(&$this, 'settings_field_input_text2'),
          'trendmd',
          'trendmd-section',
          array(
              'field' => 'trendmd_settings_saved',
              'value' => '1'
          )
        );
      }
        // Possibly do additional admin_init tasks
    } // END public static function activate

    public function settings_section_trendmd()
    {

      if(Trendmd_Admin::is_remote_fopen() && !Trendmd_Admin::is_set_journal_id()) {
        update_option('trendmd_journal_id', Trendmd_Admin::trendmd_get_journal_id());
      }
      // Think of this as help text for the section.
      if (Trendmd_Admin::is_set_journal_id()) {
        $count_posts = wp_count_posts();
        $published_posts = $count_posts->publish;
        $chunk = round(400 / $published_posts);

        if (get_option('trendmd_settings_saved')) {
          echo '<div class="box-notice">
                <div class="trendmd-message">
                  <h3 class="push--bottom">
                    TrendMD is indexing
                    <span class="articles-indexed">1</span>
                    of ' . number_format($published_posts) . ' articles from ' . parse_url(get_bloginfo("url"), PHP_URL_HOST) .
              '</h3>
                </div>
                <div class="trendmd-progress-container">
                  <div class="trendmd-progress"></div>
                </div>
              </div>';
        } else {
          echo '<style type="text/css">
                  .form-table, .submit {
                    display:block!important;
                  }
              </style>
              <div class="box-notice">
                <div class="trendmd-message">
                  <h3 class="push--bottom">Indexing required</h3>
                  <p class="push--bottom eta">
                    Save your configuration settings to start indexing. Remember, you can select individual categories that you do not want to be indexed by TrendMD.
                  </p>
                </div>
              </div>';
        }

      } else {
        $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $href = Trendmd::TRENDMD_URL . '/journals/?redirect_to=' . urlencode($url) . '&new=1&journal[url]=' . get_bloginfo('url') . '&journal[short_name]=' . get_bloginfo('name') . '&journal[open_access]=1&journal[peer_reviewed]=1';
        $support_link = Trendmd::TRENDMD_URL . "/contact-us?message=" . urlencode('Need help with Wordpress installation on website ') . parse_url(get_bloginfo("url"), PHP_URL_HOST);

        if(!Trendmd_Admin::is_remote_fopen()) {
          echo '<div class="box-info">
              <h3 class="push--bottom">Almost done!</h3>
              <p class="eta">Contact support  to register ' . parse_url(get_bloginfo("url"), PHP_URL_HOST) . ' (if needed) and obtain the TrendMD website ID.  Input the TrendMD website ID below and save changes. Please make sure to enter the correct ID, as this is a one-time operation.</p>
              <a class="button--primary" href=' . $support_link. ' target="_blank">
                Contact support
              </a>
              </div>';

        } else {
          echo '<div class="box-info">
              <h3 class="push--bottom">Almost done!</h3>
              <p class="eta">Contact support  to register ' . parse_url(get_bloginfo("url"), PHP_URL_HOST) . ' with TrendMD</p>
              <a class="button--primary" href=' . $support_link. '>
                Contact support
              </a></div>';
        }


      }
    }

    /**
    * This function provides text inputs for settings fields
    */
    public function settings_field_input_text($args) {
      // Get the field name from the $args array
      $field = $args['field'];
      // Get the value of this setting
      // $value = get_option($field);
      $value = get_option($field);
      // echo a proper input type="text"
      echo sprintf('<input type="hidden" name="%s" id="%s" value="%s" />', $field, $field, $value);
    } // END public function settings_field_input_text($args)

    /**
     * This function provides text inputs for settings fields
     */
    public function settings_field_input_text_journal_id($args) {
      if(!Trendmd_Admin::is_remote_fopen() && !Trendmd_Admin::is_set_journal_id()) {
        $type = 'text';
        $extra_attributes = ' step="1" min="0" ';
      } else {
        $type = 'hidden';
        $extra_attributes = '';
      }

      // Get the field name from the $args array
      $field = $args['field'];
      // Get the value of this setting
      // $value = get_option($field);
      $value = get_option($field);
      if($value <= 0) $value = '';
      // echo a proper input type="text"
      echo sprintf('<input type="' . $type . '" '. $extra_attributes .' name="%s" id="%s" value="%s" maxlength="10" />', $field, $field, $value);

    } // END public function settings_field_input_text($args)

    /**
     * This function provides text inputs for settings fields
     */
    public function settings_field_input_text_show_journal_id($args) {

      if(!Trendmd_Admin::is_remote_fopen() && Trendmd_Admin::is_set_journal_id()) {
        // Get the field name from the $args array
        $field = $args['field'];
        // Get the value of this setting
        // $value = get_option($field);
        $value = get_option($field);
        if($value <= 0) $value = '';
        // echo a proper input type="text"
        echo sprintf('<input type="text" disabled value="%s" />', $value);
      }

    } // END public function settings_field_input_text($args)

    /**
     * This function provides text inputs for settings fields
     */
    public function settings_field_input_text2($args) {
      // Get the field name from the $args array
      $field = $args['field'];
      $value = $args['value'];
      // Get the value of this setting
      // $value = get_option($field);

      // echo a proper input type="text"
      echo sprintf('<input type="hidden" name="%s" id="%s" value="%s" />', $field, $field, $value);
    } // END public function settings_field_input_text($args)

    /**
     * This function provides text inputs for settings fields
     */
    public function settings_field_input_checkbox($args) {
      $html = '<input type="checkbox" id="trendmd_custom_widget_location" name="trendmd_custom_widget_location" value="1"' . checked(1, get_option('trendmd_custom_widget_location'), false) . '/>';
      echo $html;
    } // END public function settings_field_input_text($args)

    public function settings_categories($args) {
      ob_start();
      wp_category_checklist( 0, 0, get_option('trendmd_categories_ignored'),  0, 0, 0);
      $select_cats = ob_get_contents();
      ob_end_clean();
      $select_cats = str_replace("post_category[]", "trendmd_categories_ignored[]", $select_cats);
      echo $select_cats;
    }

    /**
     * add a menu
     */
    public function add_menu() {
      // Add a page to manage this plugin's settings
      add_options_page(
        'TrendMD settings',
        'TrendMD',
        'manage_options',
        'trendmd',
        array(&$this, 'plugin_settings_page')
      );
    } // END public function add_menu()

    /**
     * Menu Callback
     */
    public function plugin_settings_page() {
      if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
      }
      // Render the settings template
      include(sprintf("%s/trendmd-admin-display.php", dirname(__FILE__)));
    } // END public function plugin_settings_page()
  } // END class TrendMD_Settings
} // END if(!class_exists('TrendMD_Settings'))

?>
