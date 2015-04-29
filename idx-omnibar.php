<?php

/**
* Plugin Name: IDX Omnibar Search Widget
* Plugin URI: TOBEDETERMINED
* Description: This plugin allows you to run a search with a single input for Cities, Counties, Zipcodes, Address, and MLS Number. Do NOT contact IDX Broker for support on this plugin as this plugin was not created by them.
* Version: 0.0.1
* Author: Daniel S.
* Author URI: http://danielshepard.x10.mx
* License: GPL-2.0+
*/

if ( ! defined('WPINC')) {
  die;
}
//When plugin is activated, create table and initial row
register_activation_hook( __FILE__, 'idx_omnibar_activate' );
function idx_omnibar_activate() {
	global $wpdb;
  if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."idx_omnibar'") != $wpdb->prefix.'idx_omnibar') {
    $sql = "CREATE TABLE " . $wpdb->prefix."idx_omnibar" . " (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `idx_url` text,
        PRIMARY KEY (`id`)
        )";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    $wpdb->insert(
    $wpdb->prefix."idx_omnibar",
    	array(
    		'idx_url' => ''
    	)
    );
    include_once 'idx-omnibar-get-locations.php';
  } // end if

}
//initialize widget
include 'idx-omnibar-widget.php';
//initialize plugin options
if(is_admin()){
  $apiKey = get_option('idx_broker_apikey');
  //if api key exists from the idx broker plugin, proceed. Otherwise, show an error.
  if($apiKey){
    //include 'idx-omnibar-get-locations.php';
  } else {
    function error_notice(){
      $message = 'Please install IDX Broker\'s Plugin and enter a valid API Key into the IDX Broker plugin settings.';
      echo"<div class=\"error\"><p>$message</p></div>";
    }
    add_action('admin_notices', 'error_notice');
  }
  include 'idx-omnibar-settings.php';

  //Add settings link to plugins page
  function idx_omnibar_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=idx-omnibar-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
  }
  function idx_omnibar_settings_link_setup(){
    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_$plugin", 'idx_omnibar_settings_link' );
  }
  add_action ('after_setup_theme', 'idx_omnibar_settings_link_setup');
}
