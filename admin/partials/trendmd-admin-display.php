<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.trendmd.com
 * @since      2.0
 *
 * @package    Trendmd
 * @subpackage Trendmd/admin/partials
 */

if(!Trendmd_Admin::is_set_journal_id() && Trendmd_Admin::is_remote_fopen()) {
  update_option('trendmd_journal_id', Trendmd_Admin::trendmd_get_journal_id());
?>
    <style type="text/css">
      .form-table, .submit {
        display:none!important;
      }
    </style>
  <?php
}
?>

<div class="wrap">
  <form method="POST" action="options.php" onsubmit="return validateTrendMDWebsiteId()">
  <?php @settings_fields('trendmd-group'); ?>
  <?php do_settings_sections('trendmd'); ?>
  <?php submit_button('Save changes'); ?>
  </form>
</div>
<script type="text/javascript">
  function validateTrendMDWebsiteId() {
    var data = document.getElementById('trendmd_journal_id').value;
    if (data == parseInt(data, 10) && data > 0) {
      return true;
    } else {
      alert('TrendMD website ID must be a number');
      document.getElementById('trendmd_journal_id').focus();
      return false;
    }
  }
</script>
<?php
if(get_option('trendmd_settings_saved')) { ?>
  <script type="text/javascript">
    (function( $ ) {
    'use strict';
      <?php global $wpdb; $offset = Trendmd_Admin::offset() ?>
      <?php $site_url = parse_url(get_bloginfo("url"), PHP_URL_HOST); ?>
      <?php $support_link = Trendmd::TRENDMD_URL . "/contact-us?message=" . urlencode('Need help with Wordpress installation on website ') . $site_url; ?>
      var trendmd_offset = <?php echo $offset; ?>;
      var indexed = trendmd_offset;
      var trendmdIndexArticles =  function (trendmd_chunk) {
      $('.trendmd-progress-container').show();
      var data = {
        action: 'my_action' , trendmd_offset: trendmd_offset
      };
      $.ajax({type: "POST", url: ajaxurl, data: data})
        .done(function( msg ) {
          trendmd_offset = msg;
          $('.trendmd-progress').css("width", parseInt(trendmd_offset) * parseInt(trendmd_chunk));
          if(parseInt(trendmd_offset) > 0 ) {
            indexed = parseInt(trendmd_offset);
            $('.articles-indexed').html(trendmd_offset);
            trendmdIndexArticles(trendmd_chunk);
          }else{
            $('.trendmd-progress-container').hide();
            $('.trendmd-message').html('<h3 class="push--bottom">Widget installed</h3><p class="push--bottom eta">TrendMD recommendations will appear on <?php echo $site_url; ?> within 10 minutes. Contact support if you have problems with the widget displaying</p><a class="button--primary" target="_blank" href=<?php echo $support_link; ?>>Contact support</a>');
          }
        });
      }
      <?php
        $count_posts = wp_count_posts();
        $published_posts = $count_posts->publish;
        $chunk = round(400 / $published_posts);
        echo 'trendmdIndexArticles(' . $chunk . ');';
      ?>
    })( jQuery );
  </script>
<?php } ?>
