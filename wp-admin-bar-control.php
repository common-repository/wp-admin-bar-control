<?php
/*
  Plugin Name: WP Admin Bar Control
  Description: Style Admin Bar. Add Plugins list to your Admin Bar. Activate and Deactivate plugins without page reload and moving to plugins page.
	Version: 0.9.7
  Author: Alex Egorov
	Author URI: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url
	Plugin URI: https://wordpress.org/plugins/wp-admin-bar-control/
  GitHub Plugin URI:
  License: GPLv2 or later (license.txt)
  Text Domain: wpabc
  Domain Path: /languages
*/
global $wpabc, $wpdb;

$wpabc = get_option('wpabc');
//error_reporting(E_ALL);
define('WPABC_URL', plugins_url( '/', __FILE__ ) );
define('WPABC_PATH', plugin_dir_path(__FILE__) );
define('WPABC_PREF', $wpdb->base_prefix.'n_' );
define('WPABC_VER', '0.1' );

// Async load
if (!function_exists('async_scripts')){
  function async_scripts($url) {
      if ( strpos( $url, '#async') === false )
          return $url;
      else if ( is_admin() )
          return str_replace( '#async', '', $url );
      else
      return str_replace( '#async', '', $url )."' async='async";
  }
  add_filter( 'clean_url', 'async_scripts', 11, 1 );
}

require_once( plugin_dir_path( __FILE__ ) . '/includes/admin.php' );
if( is_user_logged_in() ){
  $instance = new WPABC_Settings;
}

  add_action('admin_enqueue_scripts', 'yabp_scripts');
  function yabp_scripts(){
    global $wpabc;

    if( $wpabc['style'] == 'yummi' ){
      wp_enqueue_style( 'yummi', WPABC_URL . '/includes/css/admin_style.min.css' );
      wp_enqueue_style( 'yummi-hint', WPABC_URL . '/includes/css/hint.min.css' );
    }
  }

  // add_action('admin_footer','yabp_header');
  // function yabp_header(){
  //   global $wpabc;
  //
  //   if( is_array($wpabc['mcss']) ){
  //     $mcss = '';
  //     for ($i=0; $i < count($wpabc['mcss']); $i++) {
  //       $mcss .= $wpabc['mcss'];
  //     }
  //   }
  //   echo '<style>'.$mcss.$wpabc['css'].'</style>'; // <script type="text/javascript">alert("yep!");</script>
  // }

  /* Красивая функция вывода масивов */
  if (!function_exists('prr')){ function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; } }

/* Multiplugin functions */
register_activation_hook(__FILE__, 'yabp_activation');
function yabp_activation(){}
register_deactivation_hook( __FILE__, 'yabp_deactivation' );
function yabp_deactivation(){}
register_uninstall_hook( __FILE__, 'yabp_uninstall' );
function yabp_uninstall(){}

add_filter('plugin_action_links', 'yabp_plugin_action_links', 10, 2);
function yabp_plugin_action_links( $links,$file ){
    static $this_plugin;
    if (!$this_plugin)
        $this_plugin = plugin_basename(__FILE__);

    if ($file == $this_plugin) { // check to make sure we are on the correct plugin
			//$settings_link = '<a href="https://yummi.club/" target="_blank">' . __('Demo', 'wpabc') . '</a> | ';
			$settings_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url" target="_blank">❤ ' . __('Donate', 'wpabc') . '</a> | <a href="admin.php?page=wpabc">' . __('Settings') . '</a>'; // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page

      array_unshift($links, $settings_link); // add the link to the list
    }
    return $links;
}
/* /Multiplugin functions */
