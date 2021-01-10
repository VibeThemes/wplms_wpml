<?php
/*
Plugin Name: WPML WPLMS integration
Plugin URI: https://wplms.io/
Description: Integrating WPML  multilingual plugin with WPLMS
Author: Vibethemes
Author URI: https://vibethemes.com/
Text Domain: wplms-wpml
Domain Path: /languages/
Version: 1.0
*/



add_filter('wplms_site_link','wpml_wplms_site_link',10,2);
function wpml_wplms_site_link($link,$point){
	if(function_exists('icl_get_home_url')){
		$link = icl_get_home_url();
	}
	return $link;
}
// WPLMS REGISTRATION PAGE CODE

add_filter('wplms_buddypress_registration_link','wplms_wpml_detect_wpml_on_site');
function wplms_wpml_detect_wpml_on_site($registration_link){
  if(function_exists('icl_object_id') && function_exists('vibe_get_bp_page_id')){
        $pageid = vibe_get_bp_page_id('register');
        $pageid = icl_object_id($pageid, 'page', true);
        $registration_link = get_permalink($pageid);
   }
    return $registration_link;
}


add_shortcode('wpml_language_switcher',function(){
	do_action('wpml_add_language_selector');
});


add_filter('wplms_course_directory_course_filters',function($args){

    global $sitepress;
 
    $current_language = $sitepress->get_current_language();

 
    $sitepress->switch_lang($_COOKIE['wp-wpml_current_language'],false);
    if(!empty($args)){
      foreach($args as $key=>$arg){
        $args[$key]['label'] = __($arg['label'],'wplms');
      }
    }

    $sitepress->switch_lang($current_language);
    return $args;
});

add_filter('vibebp_loggedin_menu_nav',function($menu){
  
  $init = VibeBP_API_Init::init();
  $menu = apply_filters('vibebp_loggedin_menu','loggedin',$init->user);

  $menuLocations = get_nav_menu_locations(); 

  $menuID = $menuLocations[$menu]; 
  global $sitepress;
  $current_language = $sitepress->get_current_language();
  $sitepress->switch_lang($_COOKIE['wp-wpml_current_language'], false );
  add_filter('wp_setup_nav_menu_item',array($init,'remove_buddypress_invalid_url'));
  $menu = wp_get_nav_menu_items($menuID);
  $sitepress->switch_lang( $current_language, false );

  return $menu;
});

add_filter('vibebp_profile_menu_nav',function($menu){

  $init = VibeBP_API_Init::init();
  $menu = apply_filters('vibebp_profile_menu','profile',$init->user);
  $menuLocations = get_nav_menu_locations(); 
  $menuID = $menuLocations[$menu]; 
  global $sitepress;
  $current_language = $sitepress->get_current_language();
  $sitepress->switch_lang($_COOKIE['wp-wpml_current_language'], false );
  add_filter('wp_setup_nav_menu_item',array($init,'remove_buddypress_invalid_url'));
  $menu = wp_get_nav_menu_items($menuID);
  $sitepress->switch_lang( $current_language, false );

  return $menu;
});

add_action('vibebp_record_bp_setup_nav',function($nav){
  if(!empty($nav)){
    global $sitepress;
    $current_language = $sitepress->get_current_language();
    $sitepress->switch_lang($_COOKIE['wp-wpml_current_language'], false );
    update_option('vibebp_reload_nav_'.$current_language,$nav);
    $sitepress->switch_lang( $current_language, false );
  }
},1);


add_filter('vibebp_setup_nav',function($menu){

  $current_langauge = $_COOKIE['wp-wpml_current_language'];
  $option = get_option('vibebp_reload_nav_'.$current_langauge);
  if(!empty($option)){
    $menu = $option;
  }
  return $menu;
});


add_action( 'init', 'wplms_wpml_update' );
function wplms_wpml_update() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'update.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => true,
        'repo_uri'  => 'https://wplms.io',  //required
        'repo_slug' => 'wplms-wpml',  //required
    );

    /* Load Updater Class */
    new WPLMS_Wpml_Auto_Update( $config );
}
      