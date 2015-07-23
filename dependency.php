<?php

add_action('init', 'cfx_check_version', 5);
add_action('admin_init', 'cfx_check_dependency', 5);

function cfx_check_version(){
  if ( version_compare( phpversion(), '5.3', '<' ) ) {
    cfx_deactivate_plugin();
    add_action('admin_notices', 'cfx_notice_require_php');
    return false;
  }
  return true;
}

function cfx_check_dependency(){
  if(! file_exists( __DIR__ . '/../' . CFX_CALDERA_COREPATH) ){
    cfx_deactivate_plugin();
    add_action('admin_notices', 'cfx_notice_require_caldera_installed');
    return false;
  } 
  if ( function_exists('is_plugin_active') and !is_plugin_active(CFX_CALDERA_COREPATH) ){
    cfx_deactivate_plugin();
    add_action('admin_notices', 'cfx_notice_require_caldera_activate');
    return false;
  }
  return true;
}

function cfx_deactivate_plugin(){
  $plugins = get_option( 'active_plugins' );
  if (($k = array_search( CFX_PLUGIN_COREPATH, $plugins)) !== false){
    unset( $plugins[$k] );
    update_option( 'active_plugins', $plugins );
  }
}

function cfx_caldera_loaded(){
  return class_exists('Caldera_Forms');
}

function cfx_notice_require_php(){
  echo '<div class="error notice -is-dismissible"><p>' . CFX_PLUGIN_NAME . ' requires PHP 5.3 or higher!' . '</p></div>';
}

function cfx_notice_require_caldera_installed(){
  echo '<div class="error notice -is-dismissible"><p>' . CFX_PLUGIN_NAME . ' requires <b><a href="//wordpress.org/plugins/caldera-forms/" target="_blank">Caldera Forms</a></b> installed' . '</p></div>';
}

function cfx_notice_require_caldera_activate(){
  echo '<div class="error notice -is-dismissible"><p>' . CFX_PLUGIN_NAME . ' requires <b><a href="//wordpress.org/plugins/caldera-forms/" target="_blank">Caldera Forms</a></b> activated' . '</p></div>';
}