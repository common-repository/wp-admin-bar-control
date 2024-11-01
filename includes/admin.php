<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly
if(!function_exists('wp_get_current_user'))
  include(ABSPATH . "wp-includes/pluggable.php");

class WPABC_Settings {
	/** PLugin settings.
	 * @var array */
	private $wpabc = array();

	/* Hook in tabs */
	public function __construct() {
    global $wpabc_default;
    if (!function_exists('prr')){ function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; } }
    // delete_option('wpabc');
    // $wpabc_old = array(
    //    'hideBar' => 'no'
    //   ,'hideBarWPAdmin' => ''
    //   ,'remove' => array()
    //   ,'barColor' => '#c0b8ed'
    //   ,'style' => 'inline'
    //   ,'hidePlugins' => array(
    //     'wpabc' => 'wpabc'
    //   )
    //   ,'custom' => ''
    // );
    //update_option( 'wpabc', $wpabc_old );
    // $yabp_old = array(
    //    'hideBar' => 'no'
    //   ,'hideBarWPAdmin' => ''
    //   ,'remove' => array()
    //   ,'barColor' => '#c0b8ed'
    //   ,'style' => 'inline'
    //   ,'hidePlugins' => array(
    //     'wpabc' => 'wpabc'
    //   )
    //   ,'custom' => ''
    // );
    // update_option( 'yabp', $yabp_old );

    $wpabc_default = array(
       'type' => 'global'
      ,'hideBar' => 0
      ,'hideBarWPAdmin' => 0
      ,'remove' => array()
      ,'barColor' => '#23282d'
      ,'barColorHover' => '#32373c'
      ,'textColor' => '#eee'
      ,'iconsColor' => '#a0a5aa'
      ,'style' => 'groupwsub'
      ,'hidePlugins' => array()
      ,'hideRoles' => array()
      ,'hideRolesForce' =>  0
      ,'custom' => array()
      ,'customForce' => 0
      ,'custom_pos' => 0
      ,'hide' => 0
      ,'css' => ''
      ,'ver' => WPABC_VER
    );
    if( !get_option('wpabc') || !isset($_POST['from']) && isset($_POST['reset']) ) update_option( 'wpabc', $wpabc_default );

    /* After update */
    $wpabc = get_option('wpabc');
    // prr($wpabc);

    if( !isset($wpabc['ver']) || isset($wpabc['ver']) && $wpabc['ver'] != WPABC_VER ){
      $yabp = get_option('yabp');
      foreach( $wpabc_default as $k => $ya ){
        $yabp['hideBar'] = 0;
        if( !array_key_exists($k,$yabp) )
          $yabp[$k] = $ya;
      }
      update_option( 'wpabc', $yabp );
    }

		add_action('admin_menu', array($this,'settings_init'));
    add_action('init', array($this,'settings_save'));

    if( current_user_can('manage_options') && $wpabc['type'] == 'peruser' ){
      add_action( 'show_user_profile', array($this,'add_custom_userprofile_fields') );
      add_action( 'edit_user_profile', array($this,'add_custom_userprofile_fields') );
      add_action( 'personal_options_update', array($this,'save_custom_userprofile_fields') );
      add_action( 'edit_user_profile_update', array($this,'save_custom_userprofile_fields') );
    }

    add_action('init', array($this,'data'));
	}

	/* Init our settings */
	public function settings_init() {
		//add_settings_section( 'wpue-permalink', __( 'WP URL Extension Settings', 'wpue' ), array( $this, 'settings' ), 'permalink' );
    if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'yummi' && !function_exists('yummi_register_settings') || isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpabc' && !function_exists('yummi_register_settings') ){ /* Filter pages */
        $url = plugin_dir_url( __FILE__ );
        //register_setting( 'wpabc_admin_menu', 'wpabc', 'wpabc_validate_options' );
        wp_enqueue_style( 'yummi-hint', $url . '/css/hint.min.css' );
    }
    if( empty( $GLOBALS['admin_page_hooks']['yummi']) )
      add_menu_page( 'yummi', 'Yummi '.__('Plugins'), 'manage_options', 'yummi', array( $this, 'yummi_plugins_wpabc'), WPABC_URL.'/includes/img/dashicons-yummi.png' );

    /*add_submenu_page( parent_slug, page_title, menu_title, rights(user can manage_options), menu_slug, function ); */
    add_submenu_page('yummi', __('Admin Bar Control', 'wpabc'), __('Admin Bar Control', 'wpabc'), 'manage_options', 'wpabc', array( $this, 'settings' ));
	}

  public function yummi_plugins_wpabc(){ if(!function_exists('yummi_plugins')) include_once( WPABC_PATH . '/includes/yummi-plugins.php' ); }

  private function isPluginActive($plug){
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
    //prr($active_plugins);
    foreach($active_plugins as $plugin){
      if(strpos($plugin, $plug))
        return true;
    }
    return false;
  }

	public function settings() {
    global $wp_version;
    $wpabc = get_option('wpabc');
		//prr($wpabc);
		// _e( 'If you <code>like</code>.', 'wpue' );

    $isOldWP = floatval($wp_version) < 2.5;

    $beforeRow      = $isOldWP ? "<p>" : '<tr valign="top"><th scope="row">';
    $beforeRowSlim  = $isOldWP ? "<p>" : '<tr valign="top" class="customlinks"><th scope="row">';
    $betweenRow     = $isOldWP ? "" : '<td>';
    $afterRow       = $isOldWP ? "</p>" : '</td></tr>';
    $afterRowSlim   = $isOldWP ? "</p>" : '</td></tr>';
    //prr($_POST);

    // if ( false !== $_REQUEST['updated'] ) echo '<div class="updated fade"><p><strong>'.__( 'Options saved' ).'</strong></p></div>'; // If the form has just been submitted, this shows the notification ?>

    <div class="wrap">
      <div style='float:right;margin-top:13px;'> ❤ <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url" target="_blank"><?php _e('Donate', 'wpabc')?></a> &ensp; <span style="font-size:1.3em">&starf;</span> <a href="https://wordpress.org/support/plugin/wp-admin-bar-control/reviews/#new-post" target="_blank"><?php _e('Rate')?></a> &ensp; ❖ <a href="http://ae.yummi.club" target="_blank"><?php _e('Me', 'wpabc')?></a></div>
      <?php echo "<h1>" . __('Admin Bar Control', 'wpabc') .' '. __( 'Settings' ) . "</h1>"; ?>

      <form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
        <?php
        if(function_exists('wp_nonce_field'))
          wp_nonce_field('update-options-wpabc');

          if (get_bloginfo('version') >= 3.5){
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_style('wp-color-picker');
          } ?>

        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="wpabc" />
        <input type="hidden" name="ver" value="<?php echo WPABC_VER ?>" />
        <p class="submit">
          <input type="submit" name="submit" class="button-primary wpabcsave" value="<?php _e('Save Changes') ?>" />
          <input type="submit" name="reset" class="button wpabcreset" value="<?php _e('Reset') ?>" />
        </p>
        <span id="log"></span>

        <label class="hint--top" data-hint="<?php _e('Global settings are default for each User!', 'wpabc')?>"><?php _e('Global', 'wpabc') ?> <input type="radio" name="type" value="global" <?php checked( $wpabc['type'], 'global' ); ?>></label>
        <label><input type="radio" name="type" value="peruser" <?php checked( $wpabc['type'], 'peruser' ); ?>> <?php _e('Per Users', 'wpabc') ?></label><br/><br/>

        <?php if(!$isOldWP)
          echo '<table class="form-table">'; ?>

        <?php echo $beforeRow ?>
          <label for="hideBar-no"><?php _e('Hide Admin Bar for', 'wpabc'); if( $wpabc['type'] == 'peruser') echo ' <label class="hint--top" data-hint="'. __('Force! Use global user Roles if users Hide Admin Bar for: Current user is empty', 'wpabc').'"><input type="checkbox" name="hideRolesForce" value="1" '. checked( $wpabc['hideRolesForce'], 1, 0 ).' /></label>'; ?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="checkbox" name="hideBarWPAdmin" value="1" <?php checked( $wpabc['hideBarWPAdmin'], 1 ); ?>> <?php _e('Admin page only', 'wpabc') ?></label><br>
          <b><?php _e('Roles', 'wpabc') ?>:</b><br/>
          <?php
            $all_roles = get_editable_roles(); //error_log( print_r( $all_plugins, true ) );
            foreach ($all_roles as $url => $role) {
              $role_str = preg_replace('/\s+/', '_', strtolower($role['name']));
              $hide = empty($wpabc['hideRoles'][$role_str]) ? '' : $wpabc['hideRoles'][$role_str];
              echo '&nbsp;<label><input type="checkbox" name="hideRoles['.$role_str.']" value="'.$role_str.'" '.checked( $hide, $role_str, false).' "/> '.$role['name'].'</label><br>';
            }
          ?>
          <?php /*_e('Current user roles', 'wpabc') ?>:<br/>
          <?php $current_user = wp_get_current_user();
            echo '<b>'.$current_user->data->user_nicename.'</b><br/>';
            foreach ($current_user->roles as $k => $role) {
              echo $role.'<br/>';
            }
          */ ?>
        <?php echo $afterRow ?>

        <?php /*echo $beforeRow ?>
          <label for="hideBar-no"><?php _e('Hide Admin Bar for', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <input type="checkbox" name="hideBarWPAdmin" id="hideBarWPAdmin" value="1" <?php checked( $wpabc['hideBarWPAdmin'], 1 ); ?>> <label for="hideBarWPAdmin"><?php _e('Admin page', 'wpabc') ?></label><br>
          <input type="radio" name="hideBar" id="hideBar-all" value="all" <?php checked( $wpabc['hideBar'], 'all' ); ?>> <label for="hideBar-all"><?php _e('Admins and Users', 'wpabc') ?></label><br>
          <input type="radio" name="hideBar" id="hideBar-user" value="user" <?php checked( $wpabc['hideBar'], 'user' ); ?>> <label for="hideBar-user"><?php _e('Users only', 'wpabc') ?></label><br>
          <input type="radio" name="hideBar" id="hideBar-no" value="no" <?php checked( $wpabc['hideBar'], 'no' ); ?>> <label for="hideBar-no"><?php _e('No one', 'wpabc') ?></label><br>
        <?php echo $afterRow*/ ?>

        <?php echo $beforeRow ?>
          <label><?php _e('Remove from Bar', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="checkbox" name="remove[wplogo]" value="hide" <?php if(!empty($wpabc['remove']['wplogo'])) checked( $wpabc['remove']['wplogo'], 'hide' ); ?>><span class="dashicons dashicons-wordpress" style="line-height:10px;"></span></label><br>
          <?php if( empty($wpabc['remove']['wplogo']) ){?>
            &emsp;<label><input type="checkbox" name="remove[about]" value="hide" <?php if(!empty($wpabc['remove']['about'])) checked( $wpabc['remove']['about'], 'hide' ); ?>> <?php _e('About WordPress') ?></label><br>
            &emsp;<label><input type="checkbox" name="remove[wporg]" value="hide" <?php if(!empty($wpabc['remove']['wporg'])) checked( $wpabc['remove']['wporg'], 'hide' ); ?>> Wordpress.org</label><br>
            &emsp;<label><input type="checkbox" name="remove[documentation]" value="hide" <?php if(!empty($wpabc['remove']['documentation'])) checked( $wpabc['remove']['documentation'], 'hide' ); ?>> <?php _e('Documentation') ?></label><br>
            &emsp;<label><input type="checkbox" name="remove[supportforums]" value="hide" <?php if(!empty($wpabc['remove']['supportforums'])) checked( $wpabc['remove']['supportforums'], 'hide' ); ?>> <?php _e('Support Forums') ?></label><br>
            &emsp;<label><input type="checkbox" name="remove[feedback]" value="hide" <?php if(!empty($wpabc['remove']['feedback'])) checked( $wpabc['remove']['feedback'], 'hide' ); ?>> <?php _e('Feedback') ?></label><br>
          <?php } ?>
          <label ><input type="checkbox" name="remove[sitename]" value="hide" <?php if(!empty($wpabc['remove']['sitename'])) checked( $wpabc['remove']['sitename'], 'hide' ); ?>><span class="dashicons dashicons-admin-home"></span> <?php echo get_bloginfo('name') ?></label><br>
          <?php if( empty($wpabc['remove']['sitename']) ){ ?>
            &emsp;<label><input type="checkbox" name="remove[viewsite]" value="hide" <?php if(!empty($wpabc['remove']['viewsite'])) checked( $wpabc['remove']['viewsite'], 'hide' ); ?>> <?php _e('View Site') ?></label><br>
          <?php } ?>
          <label><input type="checkbox" name="remove[updates]" value="hide" <?php if(!empty($wpabc['remove']['updates'])) checked( $wpabc['remove']['updates'], 'hide' ); ?>><span class="dashicons dashicons-update"> </span> <?php _e('Update') ?></label><br>
          <label><input type="checkbox" name="remove[comments]" value="hide" <?php if(!empty($wpabc['remove']['comments'])) checked( $wpabc['remove']['comments'], 'hide' ); ?>><span class="dashicons dashicons-admin-comments"></span><?php _e('Comments') ?></label><br>
          <label><input type="checkbox" name="remove[newcontent]" value="hide" <?php if(!empty($wpabc['remove']['newcontent'])) checked( $wpabc['remove']['newcontent'], 'hide' ); ?>><span class="dashicons dashicons-plus"></span> <?php _e('Add') ?></label><br>
          <label><input type="checkbox" name="remove[myaccount]" value="hide" <?php if(!empty($wpabc['remove']['myaccount'])) checked( $wpabc['remove']['myaccount'], 'hide' ); ?>> <?php _e('My Account', 'wpabc') ?></label><br>
          <?php if( WPABC_Settings::isPluginActive('-total-cache') ){ ?><label><input type="checkbox" name="remove[w3tc]" value="hide" <?php if(!empty($wpabc['remove']['w3tc'])) checked( $wpabc['remove']['w3tc'], 'hide' ); ?>> <?php _e('W3 Total Cache', 'wpabc') ?></label><br><?php } ?>
          <?php if( WPABC_Settings::isPluginActive('wp-seo') ){ ?><label><input type="checkbox" name="remove[yoast]" value="hide" <?php if(!empty($wpabc['remove']['yoast'])) checked( $wpabc['remove']['yoast'], 'hide' ); ?>> <?php _e('Yoast', 'wpabc') ?></label><br><?php } ?>
        <?php echo $afterRow ?>

        <?php echo $beforeRow ?>
          <label><?php _e('Auto hide', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="checkbox" name="hide" id="hide" value="1" <?php if(!empty($wpabc['hide'])) checked( $wpabc['hide'], 1 ); ?>> <?php _e('Auto hide', 'wpabc') ?></label><br>
        <?php echo $afterRow ?>

        <?php echo $beforeRowSlim ?>
          <label for="barColor"><?php _e('Bar Background color', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="barColor" id="barColor" value="<?php echo $wpabc['barColor']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var barColor = {
                defaultColor: '#23282d',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#barColorStyle').remove();
                  $('<style id="barColorStyle" type="text/css">#wpadminbar{background:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#23282d';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#barColor').wpColorPicker(barColor);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php echo $beforeRowSlim ?>
          <label for="barColorHover"><?php _e('Bar Dropdown/Hover color', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="barColorHover" id="barColorHover" value="<?php echo $wpabc['barColorHover']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var barColorHover = {
                defaultColor: '#32373c',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#barColorHoverStyle').remove();
                  $('<style id="barColorHoverStyle" type="text/css">#wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus,#wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input{background:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#32373c';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#barColorHover').wpColorPicker(barColorHover);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php echo $beforeRowSlim ?>
          <label for="textColor"><?php _e('Bar Text color', 'wpabc'); ?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="textColor" id="textColor" value="<?php echo $wpabc['textColor']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var textColor = {
                defaultColor: '#eee',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#textColorStyle').remove();
                  $('<style id="textColorStyle" type="text/css">#wpadminbar .ab-empty-item,#wpadminbar a.ab-item,#wpadminbar>#wp-toolbar span.ab-label,#wpadminbar>#wp-toolbar span.noticon{color:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#eee';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#textColor').wpColorPicker(textColor);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php echo $beforeRowSlim ?>
          <label for="iconsColor"><?php _e('Bar Icons color', 'wpabc'); ?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="iconsColor" id="iconsColor" value="<?php echo $wpabc['iconsColor']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var iconsColor = {
                defaultColor: '#a0a5aa',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#iconsColorStyle').remove();
                  $('<style id="iconsColorStyle" type="text/css">#wpadminbar #adminbarsearch:before,#wpadminbar .ab-icon:before,#wpadminbar .ab-item:before,#wpadminbar .ab-icon{color:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#a0a5aa';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#iconsColor').wpColorPicker(iconsColor);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php
        function endKey($array){
          end($array);
          return key($array);
        }

        $i = is_array($wpabc['custom']) ? endKey($wpabc['custom']) : 0;

        if( is_array($wpabc['custom']) ){
          foreach ($wpabc['custom'] as $key => $value):?>
            <?php echo $beforeRowSlim ?>
              <label for="custom[<?php echo $key?>]"><?php echo __('Custom Link', 'wpabc').' '.$key?></label>
            <?php echo $betweenRow ?>
              <input name="custom[<?php echo $key?>][title]" type="text" value="<?php echo $value['title'] ?>" placeholder="<?php _e('Title', 'wpabc')?>"/>
              <input name="custom[<?php echo $key?>][icon]" type="text" value="<?php echo $value['icon'] ?>" placeholder="<?php _e('Icon (like: fas fa-star)', 'wpabc')?>"/>
              <label><input name="custom[<?php echo $key?>][blink]" type="checkbox" value="1" <?php if( isset($value['blink']) ) checked( $value['blink'], 1 ); ?>/> <?php _e('blink', 'wpabc')?></label>
              <input name="custom[<?php echo $key?>][link]" type="text" value="<?php echo $value['link'] ?>" placeholder="<?php _e('Link', 'wpabc')?>"/>
              <span class="del">✖</span>
            <?php echo $afterRowSlim ?>
          <?php endforeach;
        }else{
          $i = 0;
        }?>


        <?php echo $beforeRowSlim ?>
          <?php _e('Add Custom Link', 'wpabc'); if( $wpabc['type'] == 'peruser') echo ' <label class="hint--top" data-hint="'. __('Force! Show global Custom Link if users Custom Links is empty', 'wpabc').'"><input type="checkbox" name="customForce" value="1" '. checked( $wpabc['customForce'], 1, 0 ).' /></label>'; ?>:<br/><a href="https://fontawesome.com/icons?d=gallery"><small><?php _e('FontAwesome icons', 'wpabc') ?></small></a>
        <?php echo $betweenRow ?>
          <div id="custom"></div>
          <label id="add">✚ <?php _e('Add Custom Link', 'wpabc')?></label>
          <script type="text/javascript">
            var i=<?php echo $i ? $i : 0 ?>;
            jQuery('#add').on('click', function(){
              i++; //<?php // echo $beforeRow ?><?php // echo $betweenRow ?><label for="custom['+i+']"><?php // _e('Custom Link', 'wpabc')?> '+i+'</label>
              jQuery('<div><div><input name="custom['+i+'][title]" type="text" value="" placeholder="<?php _e("Title", "wpabc")?>"/><input name="custom['+i+'][icon]" type="text" value="" placeholder="<?php _e("Icon (like: fas fa-star)", "wpabc")?>"/><label><input name="custom['+i+'][blink]" type="checkbox" value="1"/> <?php _e("blink", "wpabc")?></label><input name="custom['+i+'][link]" type="text" value="" placeholder="<?php _e("Link", "wpabc")?>"/> <span class="del">✖</span></div></div>').appendTo( "#custom" );
              del();
            });
            function del(){
              jQuery('.del').on('click', function(){
                jQuery(this).parent().parent().remove();
              });
            }
            del();
          </script>
        <?php echo $afterRowSlim ?>


        <?php echo $beforeRow ?>
          <label id="custom_pos"><?php _e('Custom Link Position', 'wpabc') ?>:<br/><small>from 0 to 110</small></label>
        <?php echo $betweenRow ?>
          <input id="custom_pos" name="custom_pos" type="number" value="<?php echo $wpabc['custom_pos'] ?>" min="0" max="110" step="10" placeholder="0 <?php _e("to", "wpabc")?> 110"/>
          <?php
          // wp_admin_bar_wp_menu - 10
          // wp_admin_bar_my_sites_menu - 20
          // wp_admin_bar_site_menu - 30
          // wp_admin_bar_updates_menu - 40
          // wp_admin_bar_comments_menu - 60
          // wp_admin_bar_new_content_menu - 70
          // wp_admin_bar_edit_menu - 80
          // Plugins - 100
          ?>
        <?php echo $afterRow ?>

        <?php echo $beforeRow ?>
          <label for="style-group"><?php _e('Plugins group style', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="radio" name="style" value="group" <?php checked( $wpabc['style'], 'group' ); ?>> <?php _e('Group', 'wpabc') ?></label><br>
          <label><input type="radio" name="style" value="groupwsub" <?php checked( $wpabc['style'], 'groupwsub' ); ?>> <?php _e('Group with SubGroups', 'wpabc') ?></label><br>
          <label><input type="radio" name="style" value="inline" <?php checked( $wpabc['style'], 'inline' ); ?>> <?php _e('InLine', 'wpabc') ?></label><br>
          <label><input type="radio" name="style" value="hide" <?php checked( $wpabc['style'], 'hide' ); ?>> <?php _e('Hide', 'wpabc') ?></label><br>
        <?php echo $afterRow ?>

        <?php echo $beforeRow ?>
          <label for="hidePlugins">
            <?php _e('What plugins Hide', 'wpabc')?>:<br/>
            ✔ - <?php _e('Active Plugin', 'wpabc')?><br/>
            <span style="opacity:.3">✖</span> - <?php _e('Not Active Plugin', 'wpabc')?><br/><br/>
            <input type="button" class="button" value="<?php _e('Check All','wpabc')?>" onclick="jQuery('.hidePlugin').attr('checked', true);"/>
            <input type="button" class="button" value="<?php _e('UnCheck All','wpabc')?>" onclick="jQuery('.hidePlugin').attr('checked', false);"/><br/><br/>
            <input type="button" class="button" value="<?php _e('Inverse Check','wpabc')?>" onclick="jQuery('input.hidePlugin').each(function(){ jQuery(this).is(':checked') ? jQuery(this).removeAttr('checked') : jQuery(this).attr('checked','checked'); });"/>
          </label>

        <?php echo $betweenRow ?>
           <?php
           $all_plugins = get_plugins(); //error_log( print_r( $all_plugins, true ) );

           foreach ($all_plugins as $url => $plugin) {
             // ✔ ✓ ☐ ☑ ☒ ◉ ○ ✖
             //prr($wpabc['hidePlugins'][$plugin['TextDomain']]);
             $hide = empty($wpabc['hidePlugins'][$plugin['TextDomain']]) ? '' : $wpabc['hidePlugins'][$plugin['TextDomain']];
            echo is_plugin_active($url) ? '✔' : '<span style="opacity:.3">✖</span>';
            echo ' <input type="checkbox" id="'.$plugin['TextDomain'].'" class="hidePlugin noactive" name="hidePlugins['.$plugin['TextDomain'].']" value="'.$plugin['TextDomain'].'" '.checked( $hide, $plugin['TextDomain'], false).' "/> <label for="'.$plugin['TextDomain'].'">'.$plugin['Name'].'</label><br>';
           }
           ?>
        <?php echo $afterRow ?>

        <?php echo $beforeRow ?>
          <?php _e('Custom Css', 'wpabc')?></label>
        <?php echo $betweenRow ?>
           <textarea id="css" name="css" rows="5" cols="30"><?php echo stripslashes($wpabc['css']); ?></textarea>
        <?php echo $afterRow ?>

        <?php if(!$isOldWP)
            echo "</table>"; ?>

        <p class="submit">
          <input type="submit" name="submit" class="button-primary wpabcsave" value="<?php _e('Save Changes') ?>" />
          <input type="submit" name="reset" class="button wpabcreset" value="<?php _e('Reset') ?>" />
        </p>
      </form>
    </div>
    <?php }

	/* Save the settings */
	public function settings_save() {
		if(!is_admin())	return;
    //prr($_POST);
    global $wpabc_default;

    if( !isset($_POST['from']) && isset($_POST['submit']) && isset($_POST['action']) && $_POST['action'] == 'update' ){
      $wpabc									  = (array) get_option( 'wpabc', array() );
      $wpabc['type']           = isset($_POST['type'])           ? wp_filter_nohtml_kses( $_POST['type'] )           : $wpabc_default['type'];
      $wpabc['hideBar']        = isset($_POST['hideBar'])        ? wp_filter_nohtml_kses( $_POST['hideBar'] )        : $wpabc_default['hideBar'];
      $wpabc['hideBarWPAdmin'] = isset($_POST['hideBarWPAdmin']) ? wp_filter_nohtml_kses( $_POST['hideBarWPAdmin'] ) : $wpabc_default['hideBarWPAdmin'];
      $wpabc['remove']         = isset($_POST['remove'])         ? (array) $_POST['remove']                          : $wpabc_default['remove'];
      $wpabc['barColor']       = isset($_POST['barColor'])       ? wp_filter_nohtml_kses( $_POST['barColor'] )       : $wpabc_default['barColor'];
      $wpabc['barColorHover']  = isset($_POST['barColorHover'])  ? wp_filter_nohtml_kses( $_POST['barColorHover'] )  : $wpabc_default['barColorHover'];
      $wpabc['textColor']      = isset($_POST['textColor'])      ? wp_filter_nohtml_kses( $_POST['textColor'] )      : $wpabc_default['textColor'];
      $wpabc['iconsColor']     = isset($_POST['iconsColor'])     ? wp_filter_nohtml_kses( $_POST['iconsColor'] )     : $wpabc_default['iconsColor'];
      $wpabc['style']          = isset($_POST['style'])          ? wp_filter_post_kses( $_POST['style'] )            : $wpabc_default['style'];
      $wpabc['hidePlugins']    = isset($_POST['hidePlugins'])    ? (array) $_POST['hidePlugins']                     : $wpabc_default['hidePlugins'];
      $wpabc['hideRoles']      = isset($_POST['hideRoles'])      ? (array) $_POST['hideRoles']                       : $wpabc_default['hideRoles'];
      $wpabc['hideRolesForce'] = isset($_POST['hideRolesForce']) ? wp_filter_nohtml_kses( $_POST['hideRolesForce'] ) : $wpabc_default['hideRolesForce'];
      $wpabc['custom']         = isset($_POST['custom'])         ? (array) $_POST['custom']                          : $wpabc_default['custom'];
      $wpabc['customForce']    = isset($_POST['customForce'])    ? wp_filter_nohtml_kses( $_POST['customForce'] )    : $wpabc_default['customForce'];
      $wpabc['custom_pos']     = isset($_POST['custom_pos'])     ? wp_filter_post_kses( $_POST['custom_pos'] )       : $wpabc_default['custom_pos'];
      $wpabc['hide']           = isset($_POST['hide'])           ? wp_filter_post_kses( $_POST['hide'] )             : $wpabc_default['hide'];
      $wpabc['css']            = isset($_POST['css'])            ? wp_filter_post_kses( $_POST['css'] )              : $wpabc_default['css'];
      // if(isset($_POST['remove'])){
      // 	foreach ($_POST['remove'] as $k => $remove) {
      // 		$wpabc['remove'][$k] = wp_filter_nohtml_kses($remove);
      // 	}
      // }
      update_option("wpabc", $wpabc);
    }
  }

  /* Type == peruser */
  public function add_custom_userprofile_fields($user){
    global $wp_version;

    // delete_user_meta( $user->ID, 'wpabc');
    $wpabc_meta = get_the_author_meta( 'wpabc', $user->ID );
    if( empty($wpabc_meta) ){
      global $wpabc_default;
      update_user_meta( $user->ID, 'wpabc', $wpabc_default);
      $wpabc_meta = get_the_author_meta( 'wpabc', $user->ID );
    }
    // prr($wpabc_meta);

    $isOldWP = floatval($wp_version) < 2.5;

    $beforeRow      = $isOldWP ? "<p>" : '<tr valign="top"><th scope="row">';
    $beforeRowSlim  = $isOldWP ? "<p>" : '<tr valign="top" class="customlinks"><th scope="row">';
    $betweenRow     = $isOldWP ? "" : '<td>';
    $afterRow       = $isOldWP ? "</p>" : '</td></tr>';
    $afterRowSlim   = $isOldWP ? "</p>" : '</td></tr>';
    //prr($_POST);

    // if ( false !== $_REQUEST['updated'] ) echo '<div class="updated fade"><p><strong>'.__( 'Options saved' ).'</strong></p></div>'; // If the form has just been submitted, this shows the notification ?>

      <?php
      echo "<h2>" . __('Admin Bar Control', 'wpabc') .' '. __( 'Settings' ) . "</h2>"; ?>
        <?php
        // if(function_exists('wp_nonce_field'))
        //   wp_nonce_field('update-options-wpabc');

          if (get_bloginfo('version') >= 3.5){
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_style('wp-color-picker');
          } ?>

        <span id="log"></span>
        <input type="hidden" name="action" value="update_user" />
        <?php if(!$isOldWP)
          echo '<table class="form-table">'; ?>

        <?php echo $beforeRow ?>
          <label for="hideBar-no"><?php _e('Hide Admin Bar for', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="checkbox" name="hideBar" value="1" <?php checked( $wpabc_meta['hideBar'], 1 ); ?>> <?php _e('Current user', 'wpabc') ?></label><br>
          <label><input type="checkbox" name="hideBarWPAdmin" value="1" <?php checked( $wpabc_meta['hideBarWPAdmin'], 1 ); ?>> <?php _e('Admin page only', 'wpabc') ?></label><br>
        <?php echo $afterRow ?>


        <?php echo $beforeRow ?>
          <label><?php _e('Remove from Bar', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="checkbox" name="remove[wplogo]" value="hide" <?php if(!empty($wpabc_meta['remove']['wplogo'])) checked( $wpabc_meta['remove']['wplogo'], 'hide' ); ?>><span class="dashicons dashicons-wordpress" style="line-height:10px;"></span></label><br>
          <?php if( empty($wpabc_meta['remove']['wplogo']) ){?>
            &emsp;<label><input type="checkbox" name="remove[about]" value="hide" <?php if(!empty($wpabc_meta['remove']['about'])) checked( $wpabc_meta['remove']['about'], 'hide' ); ?>> <?php _e('About WordPress') ?></label><br>
            &emsp;<label><input type="checkbox" name="remove[wporg]" value="hide" <?php if(!empty($wpabc_meta['remove']['wporg'])) checked( $wpabc_meta['remove']['wporg'], 'hide' ); ?>> Wordpress.org</label><br>
            &emsp;<label><input type="checkbox" name="remove[documentation]" value="hide" <?php if(!empty($wpabc_meta['remove']['documentation'])) checked( $wpabc_meta['remove']['documentation'], 'hide' ); ?>> <?php _e('Documentation') ?></label><br>
            &emsp;<label><input type="checkbox" name="remove[supportforums]" value="hide" <?php if(!empty($wpabc_meta['remove']['supportforums'])) checked( $wpabc_meta['remove']['supportforums'], 'hide' ); ?>> <?php _e('Support Forums') ?></label><br>
            &emsp;<label><input type="checkbox" name="remove[feedback]" value="hide" <?php if(!empty($wpabc_meta['remove']['feedback'])) checked( $wpabc_meta['remove']['feedback'], 'hide' ); ?>> <?php _e('Feedback') ?></label><br>
          <?php } ?>
          <label ><input type="checkbox" name="remove[sitename]" value="hide" <?php if(!empty($wpabc_meta['remove']['sitename'])) checked( $wpabc_meta['remove']['sitename'], 'hide' ); ?>><span class="dashicons dashicons-admin-home"></span> <?php echo get_bloginfo('name') ?></label><br>
          <?php if( empty($wpabc_meta['remove']['sitename']) ){?>
            &emsp;<label><input type="checkbox" name="remove[viewsite]" value="hide" <?php if(!empty($wpabc_meta['remove']['viewsite'])) checked( $wpabc_meta['remove']['viewsite'], 'hide' ); ?>> <?php _e('View Site') ?></label><br>
          <?php } ?>
          <label><input type="checkbox" name="remove[updates]" value="hide" <?php if(!empty($wpabc_meta['remove']['updates'])) checked( $wpabc_meta['remove']['updates'], 'hide' ); ?>><span class="dashicons dashicons-update"></span> <?php _e('Update') ?></label><br>
          <label><input type="checkbox" name="remove[comments]" value="hide" <?php if(!empty($wpabc_meta['remove']['comments'])) checked( $wpabc_meta['remove']['comments'], 'hide' ); ?>><span class="dashicons dashicons-admin-comments"></span> <?php _e('Comments') ?></label><br>
          <label><input type="checkbox" name="remove[newcontent]" value="hide" <?php if(!empty($wpabc_meta['remove']['newcontent'])) checked( $wpabc_meta['remove']['newcontent'], 'hide' ); ?>><span class="dashicons dashicons-plus"></span> <?php _e('Add') ?></label><br>
          <label><input type="checkbox" name="remove[myaccount]" value="hide" <?php if(!empty($wpabc_meta['remove']['myaccount'])) checked( $wpabc_meta['remove']['myaccount'], 'hide' ); ?>> <?php _e('My Account', 'wpabc') ?></label><br>
          <?php if( WPABC_Settings::isPluginActive('-total-cache') ){ ?><label><input type="checkbox" name="remove[w3tc]" value="hide" <?php if(!empty($wpabc_meta['remove']['w3tc'])) checked( $wpabc_meta['remove']['w3tc'], 'hide' ); ?>> <?php _e('W3 Total Cache', 'wpabc') ?></label><br><?php } ?>
          <?php if( WPABC_Settings::isPluginActive('wp-seo') ){ ?><label><input type="checkbox" name="remove[yoast]" value="hide" <?php if(!empty($wpabc_meta['remove']['yoast'])) checked( $wpabc_meta['remove']['yoast'], 'hide' ); ?>> <?php _e('Yoast', 'wpabc') ?></label><br><?php } ?>
        <?php echo $afterRow ?>

        <?php echo $beforeRow ?>
          <label><?php _e('Auto hide', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="checkbox" name="hide" id="hide" value="1" <?php if(!empty($wpabc_meta['hide'])) checked( $wpabc_meta['hide'], 1 ); ?>> <?php _e('Auto hide', 'wpabc') ?></label><br>
        <?php echo $afterRow ?>

        <?php echo $beforeRowSlim ?>
          <label for="barColor"><?php _e('Bar Background color', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="barColor" id="barColor" value="<?php echo $wpabc_meta['barColor']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var barColor = {
                defaultColor: '#23282d',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#barColorStyle').remove();
                  $('<style id="barColorStyle" type="text/css">#wpadminbar{background:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#23282d';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#barColor').wpColorPicker(barColor);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php echo $beforeRowSlim ?>
          <label for="barColorHover"><?php _e('Bar Dropdown/Hover color', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="barColorHover" id="barColorHover" value="<?php echo $wpabc_meta['barColorHover']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var barColorHover = {
                defaultColor: '#32373c',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#barColorHoverStyle').remove();
                  $('<style id="barColorHoverStyle" type="text/css">#wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus,#wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input{background:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#32373c';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#barColorHover').wpColorPicker(barColorHover);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php echo $beforeRowSlim ?>
          <label for="textColor"><?php _e('Bar Text color', 'wpabc'); ?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="textColor" id="textColor" value="<?php echo $wpabc_meta['textColor']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var textColor = {
                defaultColor: '#eee',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#textColorStyle').remove();
                  $('<style id="textColorStyle" type="text/css">#wpadminbar .ab-empty-item,#wpadminbar a.ab-item,#wpadminbar>#wp-toolbar span.ab-label,#wpadminbar>#wp-toolbar span.noticon{color:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#eee';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#textColor').wpColorPicker(textColor);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php echo $beforeRowSlim ?>
          <label for="iconsColor"><?php _e('Bar Icons color', 'wpabc'); ?>:</label>
        <?php echo $betweenRow ?>
          <input type="text" name="iconsColor" id="iconsColor" value="<?php echo $wpabc_meta['iconsColor']; ?>" />
          <script type="text/javascript">
            jQuery(document).ready(function($) {
              var iconsColor = {
                defaultColor: '#a0a5aa',
                change: function(event, ui){
                  var element = event.target;
                  var color = ui.color.toString();
                  $('#iconsColorStyle').remove();
                  $('<style id="iconsColorStyle" type="text/css">#wpadminbar #adminbarsearch:before,#wpadminbar .ab-icon:before,#wpadminbar .ab-item:before,#wpadminbar .ab-icon{color:'+color+'!important}</style>').appendTo($('head'));
                  //setTimeout(function(){jQuery( element ).trigger('change');},1);
                },
                clear: function(){
                  var element = jQuery(event.target).siblings('.wp-color-picker')[0];
                  var color = '#a0a5aa';
                  //jQuery( event.target ).trigger('change'); // enable widget "Save" button
                },
                hide: true,
                palettes: true
              };
              $('#iconsColor').wpColorPicker(iconsColor);
            });</script>
        <?php echo $afterRowSlim ?>

        <?php
        function endKey($array){
          end($array);
          return key($array);
        }

        if(!empty($wpabc_meta['custom'])){
          $i = is_array($wpabc_meta['custom']) ? endKey($wpabc_meta['custom']) : 0;

          if( is_array($wpabc_meta['custom']) ){
            foreach ($wpabc_meta['custom'] as $key => $value):?>
              <?php echo $beforeRowSlim ?>
                <label for="custom[<?php echo $key?>]"><?php echo __('Custom Link', 'wpabc').' '.$key?></label>
              <?php echo $betweenRow ?>
                <input id="custom[<?php echo $key?>]['title']" name="custom[<?php echo $key?>][title]" type="text" value="<?php echo $value['title'] ?>" placeholder="<?php _e('Title', 'wpabc')?>"/>
                <input id="custom[<?php echo $key?>]['icon']" name="custom[<?php echo $key?>][icon]" type="text" value="<?php echo $value['icon'] ?>" placeholder="<?php _e('Icon (like: fas fa-star)', 'wpabc')?>"/>
                <label><input id="custom[<?php echo $key?>]['blink']" name="custom[<?php echo $key?>][blink]" type="checkbox" value="1" <?php if( isset($value['blink']) ) checked( $value['blink'], 1 ); ?>/> <?php _e('blink', 'wpabc')?></label>
                <input id="custom[<?php echo $key?>]['link']" name="custom[<?php echo $key?>][link]" type="text" value="<?php echo $value['link'] ?>" placeholder="<?php _e('Link', 'wpabc')?>"/>
                <span class="del">✖</span>
              <?php echo $afterRowSlim ?>
            <?php endforeach;
          }
        }else{
          $i = 0;
        }?>

        <?php echo $beforeRowSlim ?>
          <?php _e('Add Custom Link', 'wpabc') ?>:<br/><a href="https://fontawesome.com/icons?d=gallery"><small><?php _e('FontAwesome icons', 'wpabc') ?></small></a>
        <?php echo $betweenRow ?>
          <div id="custom"></div>
          <?php echo '<label id="add">✚ '.__('Add Custom Link', 'wpabc').'</label>' ?>
          <script type="text/javascript">
            var i=<?php echo $i ? $i : 0 ?>;
            jQuery('#add').on('click', function(){
              i++; //<?php // echo $beforeRow ?><?php // echo $betweenRow ?><label for="custom['+i+']"><?php // _e('Custom Link', 'wpabc')?> '+i+'</label>
              jQuery('<div><div><input name="custom['+i+'][title]" type="text" value="" placeholder="<?php _e("Title", "wpabc")?>"/><input name="custom['+i+'][icon]" type="text" value="" placeholder="<?php _e("Icon (like: fas fa-star)", "wpabc")?>"/><label><input name="custom['+i+'][blink]" type="checkbox" value="1"/> <?php _e("blink", "wpabc")?></label><input name="custom['+i+'][link]" type="text" value="" placeholder="<?php _e("Link", "wpabc")?>"/> <span class="del">✖</span></div></div>').appendTo( "#custom" );
              del();
            });
            function del(){
              jQuery('.del').on('click', function(){
                jQuery(this).parent().parent().remove();
              });
            }
            del();
          </script>
        <?php echo $afterRowSlim ?>


        <?php echo $beforeRow ?>
          <label id="custom_pos"><?php _e('Custom Link Position', 'wpabc') ?>:<br/><small>from 0 to 110</small></label>
        <?php echo $betweenRow ?>
          <input id="custom_pos" name="custom_pos" type="number" value="<?php echo $wpabc_meta['custom_pos'] ?>" min="0" max="110" step="10" placeholder="0 <?php _e("to", "wpabc")?> 110"/>
          <?php
          // wp_admin_bar_wp_menu - 10
          // wp_admin_bar_my_sites_menu - 20
          // wp_admin_bar_site_menu - 30
          // wp_admin_bar_updates_menu - 40
          // wp_admin_bar_comments_menu - 60
          // wp_admin_bar_new_content_menu - 70
          // wp_admin_bar_edit_menu - 80
          // Plugins - 100
          ?>
        <?php echo $afterRow ?>

        <?php /* echo $beforeRow ?>
          <label for="style-group"><?php _e('Plugins group style', 'wpabc')?>:</label>
        <?php echo $betweenRow ?>
          <label><input type="radio" name="style" value="group" <?php if(!empty($wpabc_meta['style'])) checked( $wpabc_meta['style'], 'group' ); ?>> <?php _e('Group', 'wpabc') ?></label><br>
          <label><input type="radio" name="style" value="groupwsub" <?php if(!empty($wpabc_meta['style'])) checked( $wpabc_meta['style'], 'groupwsub' ); ?>> <?php _e('Group with SubGroups', 'wpabc') ?></label><br>
          <label><input type="radio" name="style" value="inline" <?php if(!empty($wpabc_meta['style'])) checked( $wpabc_meta['style'], 'inline' ); ?>> <?php _e('InLine', 'wpabc') ?></label><br>
          <label><input type="radio" name="style" value="hide" <?php if(!empty($wpabc_meta['style'])) checked( $wpabc_meta['style'], 'hide' ); ?>> <?php _e('Hide', 'wpabc') ?></label><br>
        <?php echo $afterRow */ ?>

        <?php /* echo $beforeRow ?>
          <label for="hidePlugins">
            <?php _e('What plugins Hide', 'wpabc')?>:<br/>
            ✔ - <?php _e('Active Plugin', 'wpabc')?><br/>
            <span style="opacity:.3">✖</span> - <?php _e('Not Active Plugin', 'wpabc')?><br/><br/>
            <input type="button" class="button" value="<?php _e('Check All','wpabc')?>" onclick="jQuery('.hidePlugin').attr('checked', true);"/>
            <input type="button" class="button" value="<?php _e('UnCheck All','wpabc')?>" onclick="jQuery('.hidePlugin').attr('checked', false);"/><br/><br/>
            <input type="button" class="button" value="<?php _e('Inverse Check','wpabc')?>" onclick="jQuery('input.hidePlugin').each(function(){ jQuery(this).is(':checked') ? jQuery(this).removeAttr('checked') : jQuery(this).attr('checked','checked'); });"/>
          </label>

        <?php echo $betweenRow ?>
           <?php
           if ( ! function_exists( 'get_plugins' ) )
             require_once ABSPATH . 'wp-admin/includes/plugin.php';

           $all_plugins = get_plugins(); //error_log( print_r( $all_plugins, true ) );

           foreach ($all_plugins as $url => $plugin) {
             // ✔ ✓ ☐ ☑ ☒ ◉ ○ ✖
             //prr($wpabc_meta['hidePlugins'][$plugin['TextDomain']]);
             $hide = empty($wpabc_meta['hidePlugins'][$plugin['TextDomain']]) ? '' : $wpabc_meta['hidePlugins'][$plugin['TextDomain']];
            echo is_plugin_active($url) ? '✔' : '<span style="opacity:.3">✖</span>';
            echo ' <input type="checkbox" id="'.$plugin['TextDomain'].'" class="hidePlugin noactive" name="hidePlugins['.$plugin['TextDomain'].']" value="'.$plugin['TextDomain'].'" '.checked( $hide, $plugin['TextDomain'], false).' "/> <label for="'.$plugin['TextDomain'].'">'.$plugin['Name'].'</label><br>';
           }
           ?>
        <?php echo $afterRow */ ?>

        <?php echo $beforeRow ?>
          <?php _e('Custom Css', 'wpabc')?></label>
        <?php echo $betweenRow ?>
           <textarea id="css" name="css" rows="5" cols="30"><?php echo stripslashes($wpabc_meta['css']); ?></textarea>
        <?php echo $afterRow ?>

        <?php if(!$isOldWP)
            echo "</table>"; ?>

    <!-- <table class="form-table">
      <tr><th><label for="company_name"><?php _e('Company Name', 'wpabc'); ?></label></th><td>
      <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr( get_the_author_meta( 'company_name', $user->ID ) ); ?>" class="regular-text" /><br />
      <span class="description"><?php _e('Please enter your company name.', 'wpabc'); ?></span>
      </td>
      </tr>
      <tr><th>
      <label for="user_phone"><?php _e('Phone No.', 'wpabc'); ?>
      </label></th>
      <td>
      <input type="text" name="user_phone" id="user_phone" value="<?php echo esc_attr( get_the_author_meta( 'user_phone', $user->ID ) ); ?>" class="regular-text" /><br />
      <span class="description"><?php _e('Please enter your phone number.', 'wpabc'); ?></span>
      </td></tr>
    </table> -->
  <?php }
  public function save_custom_userprofile_fields( $user_id ) {
    global $wpabc_default;
    if ( !current_user_can( 'edit_user', $user_id ) )
      return FALSE;

    //$wpabc['type']           = wp_filter_nohtml_kses( $_POST['type'] );
    $wpabc['hideBar']        = wp_filter_nohtml_kses( $_POST['hideBar'] );
    $wpabc['hideBarWPAdmin'] = wp_filter_nohtml_kses( $_POST['hideBarWPAdmin'] );
    // if(isset($_POST['remove'])){
    // 	foreach ($_POST['remove'] as $k => $remove) {
    // 		$wpabc['remove'][$k] = wp_filter_nohtml_kses($remove);
    // 	}
    // }
    $wpabc['remove']         = (array) $_POST['remove'];
    $wpabc['barColor']       = wp_filter_nohtml_kses( $_POST['barColor'] );
    $wpabc['barColorHover']  = wp_filter_nohtml_kses( $_POST['barColorHover'] );
    $wpabc['textColor']      = wp_filter_nohtml_kses( $_POST['textColor'] );
    $wpabc['iconsColor']     = wp_filter_nohtml_kses( $_POST['iconsColor'] );
    $wpabc['style']          = wp_filter_post_kses( $_POST['style'] );
    $wpabc['hidePlugins']    = (array) $_POST['hidePlugins'];
    $wpabc['hideRoles']      = (array) $_POST['hideRoles'];
    //$wpabc['hideRolesForce'] = wp_filter_nohtml_kses( $_POST['hideRolesForce'] );
    $wpabc['custom']         = (array) $_POST['custom'];
    //$wpabc['customForce']    = wp_filter_nohtml_kses( $_POST['customForce'] );
    $wpabc['custom_pos']     = wp_filter_post_kses( $_POST['custom_pos'] );
    $wpabc['hide']           = wp_filter_post_kses( $_POST['hide'] );
    $wpabc['css']            = wp_filter_post_kses( $_POST['css'] );
    update_user_meta( $user_id, 'wpabc', $wpabc );
  }

  public function data(){
    $wpabc = get_option('wpabc');
    //prr($wpabc);
    $current_user = wp_get_current_user();
    if( $wpabc['type'] == 'peruser' )
      $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );

    // prr($current_user->data->user_login);
    // prr($wpabc['hideRoles']);
    // prr($current_user->roles);
    // prr($wpabc_meta);

    if( is_admin() && current_user_can( 'manage_options' ) && $wpabc['style'] != 'hide' )
      add_action( 'admin_bar_menu', 'all_plugins', 101 );

    //if( $wpabc['hideBar'] == 'user' && !current_user_can( 'manage_options' ) || $wpabc['hideBar'] == 'all' )
    if(  !empty($wpabc['hideBar']) && $wpabc['type'] == 'global'
      || !empty($wpabc['hideBar']) && !isset($wpabc_meta['hideBar'])
      || !empty($wpabc['hideBar']) &&  isset($wpabc_meta['hideBar']) &&  empty($wpabc_meta['hideBar'])
      || !empty($wpabc['hideBar']) &&  isset($wpabc_meta['hideBar']) && !empty($wpabc_meta['hideBar'])
      ||  empty($wpabc['hideBar']) &&  isset($wpabc_meta['hideBar']) && !empty($wpabc_meta['hideBar'])
      ) show_admin_bar( false );

    if(  !empty($wpabc['hideBarWPAdmin']) && $wpabc['type'] == 'global'
      || !empty($wpabc['hideBarWPAdmin']) && !isset($wpabc_meta['hideBarWPAdmin'])
      || !empty($wpabc['hideBarWPAdmin']) &&  isset($wpabc_meta['hideBarWPAdmin']) &&  empty($wpabc_meta['hideBarWPAdmin'])
      || !empty($wpabc['hideBarWPAdmin']) &&  isset($wpabc_meta['hideBarWPAdmin']) && !empty($wpabc_meta['hideBarWPAdmin'])
      ||  empty($wpabc['hideBarWPAdmin']) &&  isset($wpabc_meta['hideBarWPAdmin']) && !empty($wpabc_meta['hideBarWPAdmin'])
      ) add_action( 'admin_enqueue_scripts', 'hide_wp_admin_bar' );

    if(  !empty($wpabc['remove']) && $wpabc['type'] == 'global'
      || !empty($wpabc['remove']) && !isset($wpabc_meta['remove'])
      || !empty($wpabc['remove']) &&  isset($wpabc_meta['remove']) &&  empty($wpabc_meta['remove'])
      || !empty($wpabc['remove']) &&  isset($wpabc_meta['remove']) && !empty($wpabc_meta['remove'])
      ||  empty($wpabc['remove']) &&  isset($wpabc_meta['remove']) && !empty($wpabc_meta['remove'])
      ) add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );

    if( empty($wpabc['hideRoles']) ) $wpabc['hideRoles'] = array();
    if(  !empty(array_intersect($current_user->roles,$wpabc['hideRoles'])) && $wpabc['type'] == 'global'
      || !empty(array_intersect($current_user->roles,$wpabc['hideRoles'])) && !isset($wpabc_meta['hideBar'])
      || !empty(array_intersect($current_user->roles,$wpabc['hideRoles'])) &&  isset($wpabc_meta['hideBar']) &&  empty($wpabc_meta['hideBar']) && $wpabc['hideRolesForce']
      || !empty(array_intersect($current_user->roles,$wpabc['hideRoles'])) &&  isset($wpabc_meta['hideBar']) && !empty($wpabc_meta['hideBar'])
      ||  empty(array_intersect($current_user->roles,$wpabc['hideRoles'])) &&  isset($wpabc_meta['hideBar']) && !empty($wpabc_meta['hideBar'])
      ){
        show_admin_bar( false );
        add_action( 'admin_enqueue_scripts', 'hide_wp_admin_bar' );
      }

    if(  //!empty($wpabc['hide']) && $wpabc['type'] == 'global'
         !empty($wpabc['hide']) && !isset($wpabc_meta['hide'])
      || !empty($wpabc['hide']) &&  isset($wpabc_meta['hide']) &&  empty($wpabc_meta['hide'])
      || !empty($wpabc['hide']) &&  isset($wpabc_meta['hide']) && !empty($wpabc_meta['hide'])
      ||  empty($wpabc['hide']) &&  isset($wpabc_meta['hide']) && !empty($wpabc_meta['hide'])
      ){
        $hide = '#wpadminbar{top:-30px!important;border-bottom:3px solid #0085BA}#wpadminbar:hover{top:0!important}html.wp-toolbar{padding-top:0!important}';
      }else{
        $hide = '';
      }

    if(  //!empty($wpabc['barColor']) && $wpabc['type'] == 'global'
         !empty($wpabc['barColor']) && $wpabc['barColor'] != '#23282d' && !isset($wpabc_meta['barColor'])
      || !empty($wpabc['barColor']) && $wpabc['barColor'] != '#23282d' &&  isset($wpabc_meta['barColor']) &&  empty($wpabc_meta['barColor'])
      || !empty($wpabc['barColor']) && isset($wpabc_meta['barColor']) && !empty($wpabc_meta['barColor']) && $wpabc_meta['barColor'] != '#23282d'
      ||  empty($wpabc['barColor']) && isset($wpabc_meta['barColor']) && !empty($wpabc_meta['barColor']) && $wpabc_meta['barColor'] != '#23282d'
      ){
        add_action( 'wp_enqueue_scripts', 'bar_color' );
        add_action( 'admin_enqueue_scripts', 'bar_color' );
      }

    if(  //!empty($wpabc['barColorHover']) && $wpabc['type'] == 'global'
         !empty($wpabc['barColorHover']) && $wpabc['barColorHover'] != '#32373c' && !isset($wpabc_meta['barColorHover'])
      || !empty($wpabc['barColorHover']) && $wpabc['barColorHover'] != '#32373c' &&  isset($wpabc_meta['barColorHover']) &&  empty($wpabc_meta['barColorHover'])
      || !empty($wpabc['barColorHover']) && isset($wpabc_meta['barColorHover']) && !empty($wpabc_meta['barColorHover']) && $wpabc_meta['barColorHover'] != '#32373c'
      ||  empty($wpabc['barColorHover']) && isset($wpabc_meta['barColorHover']) && !empty($wpabc_meta['barColorHover']) && $wpabc_meta['barColorHover'] != '#32373c'
      ){
        add_action( 'wp_enqueue_scripts', 'bar_color_hover' );
        add_action( 'admin_enqueue_scripts', 'bar_color_hover' );
      }

    if(  //!empty($wpabc['textColor']) && $wpabc['type'] == 'global'
         !empty($wpabc['textColor']) && $wpabc['textColor'] != '#eee' && !isset($wpabc_meta['textColor'])
      || !empty($wpabc['textColor']) && $wpabc['textColor'] != '#eee' &&  isset($wpabc_meta['textColor']) &&  empty($wpabc_meta['textColor'])
      || !empty($wpabc['textColor']) && isset($wpabc_meta['textColor']) && !empty($wpabc_meta['textColor']) && $wpabc_meta['textColor'] != '#eee'
      ||  empty($wpabc['textColor']) && isset($wpabc_meta['textColor']) && !empty($wpabc_meta['textColor']) && $wpabc_meta['textColor'] != '#eee'
      ){
        add_action( 'wp_enqueue_scripts', 'text_color' );
        add_action( 'admin_enqueue_scripts', 'text_color' );
      }

    if(  //!empty($wpabc['iconsColor']) && $wpabc['type'] == 'global'
         !empty($wpabc['iconsColor']) && $wpabc['iconsColor'] != '#a0a5aa' && !isset($wpabc_meta['iconsColor'])
      || !empty($wpabc['iconsColor']) && $wpabc['iconsColor'] != '#a0a5aa' &&  isset($wpabc_meta['iconsColor']) &&  empty($wpabc_meta['iconsColor'])
      || !empty($wpabc['iconsColor']) && isset($wpabc_meta['iconsColor']) && !empty($wpabc_meta['iconsColor']) && $wpabc_meta['iconsColor'] != '#a0a5aa'
      ||  empty($wpabc['iconsColor']) && isset($wpabc_meta['iconsColor']) && !empty($wpabc_meta['iconsColor']) && $wpabc_meta['iconsColor'] != '#a0a5aa'
      ){
        add_action( 'wp_enqueue_scripts', 'icons_color' );
        add_action( 'admin_enqueue_scripts', 'icons_color' );
      }

    if(  !empty($wpabc['custom']) && $wpabc['type'] == 'global'
      || !empty($wpabc['custom']) && !isset($wpabc_meta['custom'])
      || !empty($wpabc['custom']) &&  isset($wpabc_meta['custom']) &&  empty($wpabc_meta['custom']) && $wpabc['customForce']
      || !empty($wpabc['custom']) &&  isset($wpabc_meta['custom']) && !empty($wpabc_meta['custom'])
      ||  empty($wpabc['custom']) &&  isset($wpabc_meta['custom']) && !empty($wpabc_meta['custom'])
      ) add_action('admin_bar_menu', 'add_mycms_admin_bar_link', $wpabc['custom_pos']);

    if( !empty($wpabc['css']) && empty($wpabc_meta['css']) )
      $css = $wpabc['css'];
    elseif( empty($wpabc['css']) && !empty($wpabc_meta['css']) || !empty($wpabc['css']) && !empty($wpabc_meta['css']) )
      $css = $wpabc_meta['css'];
    else
      $css = '';

    wp_enqueue_style('wpabc', WPABC_URL. 'includes/css/style.css');
    $css = "#wpadminbar .fa,#wpadminbar .far,#wpadminbar .fas{font-weight:900!important;font-family:'Font Awesome 5 Free'!important;}.CustomLinks th,.CustomLinks td{padding:0!important}.del{cursor:pointer}#wp-admin-bar-plugins.group .ab-submenu,#wp-admin-bar-active .ab-submenu,#wp-admin-bar-deactive .ab-submenu{overflow:auto;max-height:90vh}#wpadminbar .blink{animation:blink 1s infinite linear;display:inline-block}{$hide}{$css}@keyframes blink{100%{transform:rotatey(360deg)}";
    wp_add_inline_style( 'wpabc', $css );
  }

}

/* Data */
function na_action_link( $plugin, $action = 'activate' ) {
  if ( strpos( $plugin, '/' ) )
    $plugin = str_replace( '\/', '%2F', $plugin );

  $url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
  $_REQUEST['plugin'] = $plugin;
  $url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
  return $url;
}

function all_plugins( $wp_admin_bar ){
  $wpabc = get_option('wpabc');
  $current_user = wp_get_current_user();
  $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );
  $all_plugins = get_plugins(); //error_log( print_r( $all_plugins, true ) );

  empty($wpabc['hidePlugins']) ? $wpabc['hidePlugins'] = [] : $wpabc['hidePlugins'];

  // if(  !isset($wpabc['remove']) &&  isset($wpabc_meta['remove'])
  //   ||  isset($wpabc['remove']) &&  isset($wpabc_meta['remove'])
  //   ||  isset($wpabc['remove']) && !isset($wpabc_meta['remove'])
  //   ) $wp_admin_bar->remove_menu('wp-logo');

  if( $wpabc['style'] == 'group' || $wpabc['style'] == 'groupwsub' || !isset($wpabc['style']) ){
    $args = array(
       'id'   	=> 'plugins'
      ,'title'	=> '<span class="ab-icon">◉</span> '.__('Plugins')
      ,'parent'	=> null
      ,'href'		=> null
      ,'meta'		=> array(
           'title'    => __('Activate/Deactivate plugins','wpabc')
          ,'class'    => $wpabc['style']
         //,'onclick'  => ''
         // ,'target'   => '_self'
         // ,'html'     => ''
         // ,'rel'      => 'friend'
         // ,'tabindex' => PHP_INT_MAX
        )

    );

    $wp_admin_bar->add_node( $args );

    if($wpabc['style'] == 'groupwsub'){
      $active = array(
         'id'   	=> 'active'
        ,'title'	=> '<span class="active">◉</span> '.__('Active','wpabc') //<span class="dashicons dashicons-visibility"></span>
        ,'parent'	=> 'plugins'
        ,'href'		=> null
        ,'meta'		=> array(
             'title' => __('Activate/Deactivate plugins','wpabc')
            ,'class' => 'active-plugins-group'
          )
      );
      $wp_admin_bar->add_node( $active );

      $deactive = array(
         'id'   	=> 'deactive'
        ,'title'	=> '<span class="deactive">○</span> '.__('Deactive','wpabc') //<span class="dashicons dashicons-hidden"></span>
        ,'parent'	=> 'plugins'
        ,'href'		=> null
        ,'meta'		=> array(
            'title'    => __('Activate/Deactivate plugins','wpabc')
            ,'class' => 'deactive-plugins-group'
          )
      );
      $wp_admin_bar->add_node( $deactive );
    }
  }

  $styleOff = ($wpabc['style'] == 'groupwsub' || !isset($wpabc['style']) ) ? 'active'   : null;
  $styleOn  = ($wpabc['style'] == 'groupwsub' || !isset($wpabc['style']) ) ? 'deactive' : null;

  ($wpabc['style'] == 'group' || !isset($wpabc['style']) ) ? $styleOff = 'plugins' : null;
  ($wpabc['style'] == 'group' || !isset($wpabc['style']) ) ? $styleOn  = 'plugins' : null;

  foreach ($all_plugins as $url => $plugin) {
    if( !in_array($plugin['TextDomain'], $wpabc['hidePlugins']) ) {
      //prr($plugin['TextDomain']);
      $off = array(
         'id'		  => $plugin['TextDomain']
        ,'parent'	=> $styleOff
        ,'title'	=> '<span class="active">◉</span> '.$plugin['Name'].' <b></b>' //<span class="dashicons dashicons-visibility"></span>
        ,'href'		=> na_action_link( $url, 'deactivate' )
        ,'meta'		=> array(
           'title'    => 'Deactivate '.$plugin['Name'].' plugin'
          ,'onclick'  => 'event.preventDefault();doIt(this, "'.na_action_link( $url, 'activate' ).'", "'.na_action_link( $url, 'deactivate' ).'");'
          )
      );

      $on = array(
         'id'   	=> $plugin['TextDomain']
        ,'parent'	=> $styleOn
        ,'title'	=> '<span class="deactive">○</span> '.$plugin['Name'].' <b></b>' //<span class="dashicons dashicons-hidden"></span>
        ,'href'		=> na_action_link( $url, 'activate' )
        ,'meta'		=> array(
            'title'    => 'Activate '.$plugin['Name'].' plugin'
           ,'onclick'  => 'event.preventDefault();doIt(this, "'.na_action_link( $url, 'activate' ).'", "'.na_action_link( $url, 'deactivate' ).'");'
          )
      );

      if( is_plugin_active($url) )
        $wp_admin_bar->add_node( $off );
      else
        $wp_admin_bar->add_node( $on );
    }
  }

  // [advanced-custom-fields-pro/acf.php] => Array(
  //     [Name] => Advanced Custom Fields PRO
  //     [PluginURI] => https://www.advancedcustomfields.com/
  //     [Version] => 5.5.1
  //     [Description] => Customise WordPress with powerful, professional and intuitive fields
  //     [Author] => Elliot Condon
  //     [AuthorURI] => http://www.elliotcondon.com/
  //     [TextDomain] => acf
  //     [DomainPath] => /lang
  //     [Network] =>
  //     [Title] => Advanced Custom Fields PRO
  //     [AuthorName] => Elliot Condon
  // ) ?>

  <style type="text/css">
    #wp-admin-bar-span { font:400 20px/1 dashicons; margin-top:5px; }
    ul.active-plugins-group { display: block; }
    ul.deactive-plugins-group {
        position: absolute !important;
        top: 0;
        left: 100%;
        -webkit-box-shadow: 0 3px 5px rgba(0,0,0,.2);
        box-shadow: 3px 3px 5px rgba(0,0,0,.2);
        border-left: 1px solid rgba(0,0,0,.2);
        background: #32373c !important;
    }
    /*#wp-admin-bar-plugins .ab-sub-wrapper { display: block !important; }*/
  </style>

  <script>
    function doIt(that, wpnonceActivate, wpnonceDeactivate){
      var that = jQuery(that),
          url = that.attr('href'),
          log = jQuery(that).children('b'),
          child = jQuery(that).children('span');
      child.addClass('blink');
      wpnonceActivate   = wpnonceActivate.split('=');
      wpnonceDeactivate = wpnonceDeactivate.split('=');
      wpnonceActivate   = wpnonceActivate[wpnonceActivate.length - 1];
      wpnonceDeactivate = wpnonceDeactivate[wpnonceDeactivate.length - 1];
      //console.log( 'wpnonceActivate:' + wpnonceActivate + ' / wpnonceDeactivate:' + wpnonceDeactivate );

      jQuery.get( url, function() {
        //console.log( 'Activate/Deactivate plugin success' );
        if( child.hasClass('active') ){ //child.hasClass('dashicons-visibility')
          url = url.replace('=deactivate&','=activate&');
          url = url.replace(wpnonceDeactivate,wpnonceActivate);
          child.removeClass("blink active").addClass("deactive").text('○'); //child.removeClass("dashicons-visibility").addClass("dashicons-hidden")
        }else{
          url = url.replace('=activate&','=deactivate&');
          url = url.replace(wpnonceActivate,wpnonceDeactivate);
          child.removeClass("blink deactive").addClass("active").text('◉'); //child.removeClass("dashicons-hidden").addClass("dashicons-visibility")
        }
        that.attr('href', url);
        } )
        .done(function(){
          //log.css('color','green').text('Done');
          //console.log( 'done' );
        })
        .fail(function(){
          log.css('color','red').text('<?php _e('Get Error') ?>');
          var logClean = function(){
            log.removeAttr('style').text('');
          };
          setTimeout(logClean, 3000);
          //console.log( 'error' );
        })
        .always(function(){
          // console.log( 'finished' );
        });
      // jQuery.post(url, { data: valueToPass }, function(data){} );
      // return false; // prevent default browser refresh on '#' link
      };
    </script>
<?php }

function buildTreeNodes(array $elements, $options = ['parent_id_column_name' => 'parent','children_key_name' => 'children','id_column_name' => 'id'], $parentId = 0){
    $branch = array();
    foreach ($elements as $element) {
        if ($element[$options['parent_id_column_name']] == $parentId) {
            $children = buildTree($elements, $options, $element[$options['id_column_name']]);
            if( $children )
                $element[$options['children_key_name']] = $children;
            else
                $element[$options['children_key_name']] = []; // added this line for empty children array
            $branch[] = $element;
        }
    }
    return $branch;
}

function remove_admin_bar_links(){
  global $wp_admin_bar, $wp_version;

  $wpabc = get_option('wpabc');
  if($wpabc['type'] == 'peruser'){
    $current_user = wp_get_current_user();
    $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );
  }

  // $isOldWP = floatval($wp_version) < 2.5;
  //
  // $beforeRow      = $isOldWP ? "<p>" : '<tr valign="top"><th scope="row">';
  // $beforeRowSlim  = $isOldWP ? "<p>" : '<tr valign="top" class="customlinks"><th scope="row">';
  // $betweenRow     = $isOldWP ? "" : '<td>';
  // $afterRow       = $isOldWP ? "</p>" : '</td></tr>';
  // $afterRowSlim   = $isOldWP ? "</p>" : '</td></tr>';
  //
  // $all_toolbar_nodes = $wp_admin_bar->get_nodes();
  // //prr($all_toolbar_nodes);
  // $new_all_toolbar_nodes = array();
  // if(!$isOldWP)echo '<table class="form-table">';
  // echo $beforeRow.'<label>'. __('Remove from Bar', 'wpabc').':</label>'.$betweenRow;
  // //prr($all_toolbar_nodes);
  // $all_toolbar_nodes_tree = buildTreeNodes(json_decode(json_encode($all_toolbar_nodes), True));
  // prr($all_toolbar_nodes_tree);
  // foreach ($all_toolbar_nodes_tree as $key => $node) {
  //     if( !empty($node['children']) ){
  //       foreach ($node['children'] as $k => $child) {
  //         $checked = !empty($wpabc['remove'][$k]) ? checked( $wpabc['remove'][$k], 'hide' ) : null;
  //         $title = !empty($child['title']) ? $child['title'] : $child['id'];
  //         echo '&nbsp;&nbsp;&nbsp;<label><input type="checkbox" name="remove['.$k.']" value="hide" '.$checked.'> '.$title.'</label><br/>';
  //       }
  //     }else{
  //       $checked = !empty($wpabc['remove'][$key]) ? checked( $wpabc['remove'][$key], 'hide' ) : null;
  //       $title = !empty($node['title']) ? $node['title'] : $node['id'];
  //       echo '<label><input type="checkbox" name="remove['.$key.']" value="hide" '.$checked.'> '.$title.'</label><br/>';
  //     }
  //
  // }
  // echo $afterRow;
  // if(!$isOldWP) echo "</table>";

  if(  !isset($wpabc['remove']['wplogo']) &&  isset($wpabc_meta['remove']['wplogo'])
    ||  isset($wpabc['remove']['wplogo']) &&  isset($wpabc_meta['remove']['wplogo'])
    ||  isset($wpabc['remove']['wplogo']) && !isset($wpabc_meta['remove']['wplogo'])
    ) $wp_admin_bar->remove_menu('wp-logo');

    if(  !isset($wpabc['remove']['about']) &&  isset($wpabc_meta['remove']['about'])
      ||  isset($wpabc['remove']['about']) &&  isset($wpabc_meta['remove']['about'])
      ||  isset($wpabc['remove']['about']) && !isset($wpabc_meta['remove']['about'])
      ) $wp_admin_bar->remove_menu('about');

    if(  !isset($wpabc['remove']['wporg']) &&  isset($wpabc_meta['remove']['wporg'])
      ||  isset($wpabc['remove']['wporg']) &&  isset($wpabc_meta['remove']['wporg'])
      ||  isset($wpabc['remove']['wporg']) && !isset($wpabc_meta['remove']['wporg'])
      ) $wp_admin_bar->remove_menu('wporg');

    if(  !isset($wpabc['remove']['documentation']) &&  isset($wpabc_meta['remove']['documentation'])
      ||  isset($wpabc['remove']['documentation']) &&  isset($wpabc_meta['remove']['documentation'])
      ||  isset($wpabc['remove']['documentation']) && !isset($wpabc_meta['remove']['documentation'])
      ) $wp_admin_bar->remove_menu('documentation');

    if(  !isset($wpabc['remove']['supportforums']) &&  isset($wpabc_meta['remove']['supportforums'])
      ||  isset($wpabc['remove']['supportforums']) &&  isset($wpabc_meta['remove']['supportforums'])
      ||  isset($wpabc['remove']['supportforums']) && !isset($wpabc_meta['remove']['supportforums'])
      ) $wp_admin_bar->remove_menu('support-forums');

    if(  !isset($wpabc['remove']['feedback']) &&  isset($wpabc_meta['remove']['feedback'])
      ||  isset($wpabc['remove']['feedback']) &&  isset($wpabc_meta['remove']['feedback'])
      ||  isset($wpabc['remove']['feedback']) && !isset($wpabc_meta['remove']['feedback'])
      ) $wp_admin_bar->remove_menu('feedback');

  if(  !isset($wpabc['remove']['sitename']) &&  isset($wpabc_meta['remove']['sitename'])
    ||  isset($wpabc['remove']['sitename']) &&  isset($wpabc_meta['remove']['sitename'])
    ||  isset($wpabc['remove']['sitename']) && !isset($wpabc_meta['remove']['sitename'])
    ) $wp_admin_bar->remove_menu('site-name');

    if(  !isset($wpabc['remove']['viewsite']) &&  isset($wpabc_meta['remove']['viewsite'])
      ||  isset($wpabc['remove']['viewsite']) &&  isset($wpabc_meta['remove']['viewsite'])
      ||  isset($wpabc['remove']['viewsite']) && !isset($wpabc_meta['remove']['viewsite'])
      ) $wp_admin_bar->remove_menu('view-site');

  if(  !isset($wpabc['remove']['updates']) &&  isset($wpabc_meta['remove']['updates'])
    ||  isset($wpabc['remove']['updates']) &&  isset($wpabc_meta['remove']['updates'])
    ||  isset($wpabc['remove']['updates']) && !isset($wpabc_meta['remove']['updates'])
    ) $wp_admin_bar->remove_menu('updates');

  if(  !isset($wpabc['remove']['comments']) &&  isset($wpabc_meta['remove']['comments'])
    ||  isset($wpabc['remove']['comments']) &&  isset($wpabc_meta['remove']['comments'])
    ||  isset($wpabc['remove']['comments']) && !isset($wpabc_meta['remove']['comments'])
    ) $wp_admin_bar->remove_menu('comments');

  if(  !isset($wpabc['remove']['newcontent']) &&  isset($wpabc_meta['remove']['newcontent'])
    ||  isset($wpabc['remove']['newcontent']) &&  isset($wpabc_meta['remove']['newcontent'])
    ||  isset($wpabc['remove']['newcontent']) && !isset($wpabc_meta['remove']['newcontent'])
    ) $wp_admin_bar->remove_menu('new-content');

  if(  !isset($wpabc['remove']['w3tc']) &&  isset($wpabc_meta['remove']['w3tc'])
    ||  isset($wpabc['remove']['w3tc']) &&  isset($wpabc_meta['remove']['w3tc'])
    ||  isset($wpabc['remove']['w3tc']) && !isset($wpabc_meta['remove']['w3tc'])
    ) $wp_admin_bar->remove_menu('w3tc');

  if(  !isset($wpabc['remove']['yoast']) &&  isset($wpabc_meta['remove']['yoast'])
    ||  isset($wpabc['remove']['yoast']) &&  isset($wpabc_meta['remove']['yoast'])
    ||  isset($wpabc['remove']['yoast']) && !isset($wpabc_meta['remove']['yoast'])
    ) $wp_admin_bar->remove_menu('wpseo-menu');

  if(  !isset($wpabc['remove']['myaccount']) &&  isset($wpabc_meta['remove']['myaccount'])
    ||  isset($wpabc['remove']['myaccount']) &&  isset($wpabc_meta['remove']['myaccount'])
    ||  isset($wpabc['remove']['myaccount']) && !isset($wpabc_meta['remove']['myaccount'])
    ) $wp_admin_bar->remove_menu('my-account');
}
// /Удаление значков WP и ссылок в админбаре

function hide_wp_admin_bar(){
  wp_enqueue_style('bar_color', WPABC_URL. '/includes/css/style.css');
  $css = "html { padding-top: 0!important; } #wpadminbar {display: none;height: 0 !important;}";
  wp_add_inline_style( 'bar_color', $css );
}

function bar_color(){
  $wpabc = get_option('wpabc');
  if($wpabc['type'] == 'peruser'){
    $current_user = wp_get_current_user();
    $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );
  }

  if( !empty($wpabc['barColor']) && empty($wpabc_meta['barColor']) )
    $color = $wpabc['barColor'];
  elseif( empty($wpabc['barColor']) && !empty($wpabc_meta['barColor']) || !empty($wpabc['barColor']) && !empty($wpabc_meta['barColor']) )
    $color = $wpabc_meta['barColor'];

  wp_enqueue_style('bar_color', WPABC_URL. '/includes/css/style.css');
  $css = "#wpadminbar {background: {$color}}";
  wp_add_inline_style( 'bar_color', $css );
}

function bar_color_hover(){
  $wpabc = get_option('wpabc');
  if($wpabc['type'] == 'peruser'){
    $current_user = wp_get_current_user();
    $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );
  }

  if( !empty($wpabc['barColorHover']) && empty($wpabc_meta['barColorHover']) )
    $color = $wpabc['barColorHover'];
  elseif( empty($wpabc['barColorHover']) && !empty($wpabc_meta['barColorHover']) || !empty($wpabc['barColorHover']) && !empty($wpabc_meta['barColorHover']) )
    $color = $wpabc_meta['barColorHover'];

  wp_enqueue_style('bar_color_hover', WPABC_URL. '/includes/css/style.css');
  $css = "#wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus,#wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input {background: {$color}}";
  wp_add_inline_style( 'bar_color_hover', $css );
}

function text_color(){
  $wpabc = get_option('wpabc');
  if($wpabc['type'] == 'peruser'){
    $current_user = wp_get_current_user();
    $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );
  }

  if( !empty($wpabc['textColor']) && empty($wpabc_meta['textColor']) )
    $color = $wpabc['textColor'];
  elseif( empty($wpabc['textColor']) && !empty($wpabc_meta['textColor']) || !empty($wpabc['textColor']) && !empty($wpabc_meta['textColor']) )
    $color = $wpabc_meta['textColor'];

  wp_enqueue_style('text_color', WPABC_URL. '/includes/css/style.css');
  $css = "#wpadminbar .ab-empty-item, #wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon {color: {$color}}";
  wp_add_inline_style( 'text_color', $css );
}

function icons_color(){
  $wpabc = get_option('wpabc');
  if($wpabc['type'] == 'peruser'){
    $current_user = wp_get_current_user();
    $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );
  }

  if( !empty($wpabc['iconsColor']) && empty($wpabc_meta['iconsColor']) )
    $color = $wpabc['iconsColor'];
  elseif( empty($wpabc['iconsColor']) && !empty($wpabc_meta['iconsColor']) || !empty($wpabc['iconsColor']) && !empty($wpabc_meta['iconsColor']) )
    $color = $wpabc_meta['iconsColor'];

  wp_enqueue_style('icons_color', WPABC_URL. '/includes/css/style.css');
  $css = "#wpadminbar #adminbarsearch:before,#wpadminbar .ab-icon:before,#wpadminbar .ab-item:before,#wpadminbar .ab-icon,#wpadminbar .fa,#wpadminbar .far,#wpadminbar .fas{color: {$color}}";
  wp_add_inline_style( 'icons_color', $css );
}

// Добавление своих пунктов админ-панель
function add_mycms_admin_bar_link(){
  global $wp_admin_bar;

  $wpabc = get_option('wpabc');

  if($wpabc['type'] == 'peruser'){
    $current_user = wp_get_current_user();
    $wpabc_meta = get_the_author_meta( 'wpabc', $current_user->data->ID );
  }

  // if ( !is_super_admin() || !is_admin_bar_showing() )
  //   return;

    $wpabc_custom = array();

  if( !empty($wpabc['custom']) && empty($wpabc_meta['custom']) )
    $wpabc_custom = $wpabc['custom'];
  elseif( empty($wpabc['custom']) && !empty($wpabc_meta['custom']) || !empty($wpabc['custom']) && !empty($wpabc_meta['custom']) )
    $wpabc_custom = $wpabc_meta['custom'];

  //wp_enqueue_style( 'yummi-FontAwesome', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css' );
  echo '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" crossorigin="anonymous"><style type="text/css"></style>';

  foreach( $wpabc_custom as $key => $value ){
    $blink[$key] = isset($value['blink']) ? 'blink' : null;
    $icon[$key] = !empty($value['icon']) ? '<span class="'.$blink[$key].' '.$value['icon'].'"></span> ' : null;
    $wp_admin_bar->add_menu( array(
       'id'    => 'custom_link_'.$key
      ,'title' => $icon[$key].$value['title']
      ,'href'  => $value['link']
      ,'parent'	=> null // Уникальный идентификатор родительского меню
      ,'meta'		=> array(
        // 'title'    => 'Activate/Deactivate plugins'
        //,'onclick'  => ''
        // ,'target'   => '_self'
        // ,'html'     => ''
        // ,'class'    => 'imsanity'
        // ,'rel'      => 'friend'
        // ,'tabindex' => PHP_INT_MAX
        )
    ));
  }
}
