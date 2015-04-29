<?php
//make API calls for location lists and store them in locationlist.json file
function idx_api_call($url){
  if(!get_option('idx_broker_apikey'))
    return 'No API Key. Please enter the API Key under the IDX Broker Plugin Settings.';

  $headers = array(
    'Content-Type' => 'application/x-www-form-urlencoded',
    'accesskey' => get_option('idx_broker_apikey'),
    'outputtype' => 'json'
  );

  $response = wp_remote_get($url, array( 'timeout' => 120, 'sslverify' => false, 'headers' => $headers ));
  $response = (array)$response;

  extract(apiResponse($response)); // get code and error message if any, assigned to vars $code and $error
  if ($error !== false) {
    if ($code == 401)
      //delete_transient('idx_systemlinks_cache');
    return new WP_Error("idx_api_error", __("Error {$code}: $error"));
  }
  else {
    $response_data = ($code == 200 && isset($response['body'])) ? json_decode($response['body']) : array();
    //set_transient('idx_systemlinks_cache', $system_links, 7200);
    return json_encode($response_data);
  }
}
function update_json_file($url, $data){
  return file_put_contents($url, $data);
}
function get_base_url($array){
  foreach ($array as $item){
    if(preg_match("/results/i", $item->url)){
      return($item->url);
    }
  }
};

//grab responses and add JSON object container for easier parsing later
$cities = '"cities" : '.idx_api_call('https://api.idxbroker.com/clients/cities/combinedActiveMLS');
$counties = ', "counties" : '.idx_api_call('https://api.idxbroker.com/clients/counties/combinedActiveMLS');
$zipcodes = ', "zipcodes" : '.idx_api_call('https://api.idxbroker.com/clients/zipcodes/combinedActiveMLS');
//location lists together
$locations = 'idxParseLocations({'.$cities.$counties.$zipcodes.'})';

//get base Url for client's results page for use on omnibar.js front end
$systemLinks = json_decode(idx_api_call('https://api.idxbroker.com/clients/systemlinks?rf[]=url'));

//update_json_file(WP_PLUGIN_DIR.'/idx-omnibar/locationlist.json', $locations);

  //update idxUrl
  $wpdb->update(
  $wpdb->prefix."idx_omnibar",
  	array(
  		'idx_url' => get_base_url($systemLinks)
  	), array(
      'id' => 1
    )
  );
