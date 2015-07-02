<?php

/*
* IDX Omnibar Search Widget
*
*/

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
      $message = 'Please enter a valid API Key.';
      echo"<div class=\"error\"><p>$message</p></div>";
    }
    add_action('admin_notices', 'error_notice');
  }
  
}
