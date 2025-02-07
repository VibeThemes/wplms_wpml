<?php
/*
Plugin Name: WPML WPLMS integration
Plugin URI: https://wplms.io/
Description: Integrating WPML  multilingual plugin with WPLMS
Author: Vibethemes
Author URI: https://vibethemes.com/
Text Domain: wplms-wpml
Domain Path: /languages/
Version: 1.4
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

add_filter('wplms_plugin_instructor_courses','wplms_wpml_courses');
add_filter('wplms_get_instructor_quizzes','wplms_wpml_courses');
add_filter('wplms_mycourses','wplms_wpml_courses');
add_filter('bp_course_wplms_filters','wplms_wpml_courses');

function wplms_wpml_courses($args){
  global $sitepress;
 
  $current_language = $sitepress->get_current_language();
  $sitepress->switch_lang($_COOKIE['wp-wpml_current_language'],false);
  return $args;
}

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





add_action('vibebp_record_bp_setup_nav',function($nav){
  if(!empty($nav)){
    global $sitepress;
    $current_language = $sitepress->get_current_language();
    $sitepress->switch_lang($_COOKIE['wp-wpml_current_language'], false );
//        $sitepress->switch_lang($_COOKIE['_icl_current_language'], false );
    update_option('vibebp_reload_nav_'.$current_language,$nav);
    $sitepress->switch_lang( $current_language, false );
  }
},1);


add_filter('vibebp_setup_nav',function($menu){

  $current_langauge = $_COOKIE['wp-wpml_current_language'];
  //    $current_langauge = $_COOKIE['_icl_current_language'];
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


add_filter('vibebp_vars',function($data){
  $single_page =vibebp_get_setting('bp_single_page');
  if(function_exists('icl_object_id') && !empty($single_page)){
    $single_page = icl_object_id($single_page, 'page', true);
    $data['profile_link'] = get_permalink($single_page );
  }
  return $data;
});
      

add_filter('vibebp_bp_xprofile_field',function($f){
  global $field;
  $field = $f;
  $cookie_name = 'wp-wpml_current_language'; //_icl_current_language
  if(!empty($_COOKIE[$cookie_name]))
  $field['name'] = apply_filters( 'wpml_translate_single_string', $field['name'], 'Buddypress Multilingual','profile field '.$field['id'].' name', $_COOKIE[$cookie_name]);
  return $field;
});
  

add_action('vibebp_before_notification_loop',function(){
  if(!empty($_COOKIE['wp-wpml_current_language'])){
    do_action( 'wpml_switch_language',$_COOKIE['wp-wpml_current_language']);
  }
});


add_filter('vibebp_loggedin_menu_nav',function($menu){
  $init = VibeBP_API_Init::init();
    $menu = apply_filters('vibebp_loggedin_menu','loggedin',$init->user);
  $menuLocations = get_nav_menu_locations(); 
$mid = $menuLocations[$menu];
  if(isset($_COOKIE['wp-wpml_current_language'])){
    $mid = apply_filters('wpml_object_id',$menuLocations[$menu],'nav_menu',false,$_COOKIE['wp-wpml_current_language']);
  }
  
  
  
  
  //print_r($_COOKIE['wp-wpml_current_language'].' = '.$mid);

  $menu = wp_get_nav_menu_items($mid);

    return $menu;
});



add_filter('vibebp_profile_menu_nav',function($menu){
  $init = VibeBP_API_Init::init();
    $menu = apply_filters('vibebp_profile_menu','profile',$init->user);
  $menuLocations = get_nav_menu_locations(); 
  $mid = $menuLocations[$menu];
  if(isset($_COOKIE['wp-wpml_current_language'])){
    $mid = apply_filters('wpml_object_id',$menuLocations[$menu],'nav_menu',false,$_COOKIE['wp-wpml_current_language']);
  
}
  
  
  
  //print_r($_COOKIE['wp-wpml_current_language'].' = '.$mid);

  $menu = wp_get_nav_menu_items($mid);

    return $menu;
});

add_action( 'wpml_enqueued_browser_redirect_language', function($enqueued, $params){
     if ( ! $enqueued ) {
      ?><script>sessionStorage.removeItem('loggedinmenu');</script>
      <?php
     }
}, 10, 2 );


add_filter('vibebp_member_directory_filters',function($args){
  $d_l = apply_filters('wpml_default_language', NULL );
  $c_l=$_COOKIE['wp-wpml_current_language'];
        
  if(!empty($args) && !empty($c_l) && $d_l != $c_l ){
    foreach($args as $i=>$arg){
      $name = 'profile field '.$arg['field_id'].' name';
      $args[$i]['name']=apply_filters('wpml_translate_single_string',$arg['name'],'Buddypress Multilingual',$name,$c_l);
    }
  }
  return $args;
});


add_filter('vibebp_xprofile_field_options',function($return){
  $d_l = apply_filters('wpml_default_language', NULL );
  $c_l=$_COOKIE['wp-wpml_current_language'];
  if(!empty($return['values']) && !empty($c_l) && $d_l != $c_l ){
    foreach($return['values'] as $i=>$v){
      
      if(!empty($v['values'])){
        foreach($v['values'] as $k=>$val){

          $name= "profile field ".$v['id']." - option '".strtolower($val->name)."' name";
      $return['values'][$i]['values'][$k]->name=apply_filters('wpml_translate_single_string',$val->name,'Buddypress Multilingual',$name,$c_l);
        }
      }
      
    }
  }
  return $return;
});
