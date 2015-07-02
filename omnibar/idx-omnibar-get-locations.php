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
    if ($code == 401){
        echo "<div class='error'><p>$code: $error</p></div>";
        return false;
    }
    echo "<div class='error'><p>$code: $error</p></div>";
    //print_r($error, $code);
  }
  else {
    $response_data = ($code == 200 && isset($response['body'])) ? json_decode($response['body']) : array();
    //set_transient('idx_systemlinks_cache', $system_links, 7200);
    return json_encode($response_data);
  }
}
function get_base_url($array){
  foreach ((array)$array as $item){
    if(preg_match("/results/i", $item->url)){
      return($item->url);
    }
  }
}

  //grab responses and add JSON object container for easier parsing later
  $cities = '"cities" : '.idx_api_call('https://api.idxbroker.com/clients/cities/combinedActiveMLS');
  $counties = ', "counties" : '.idx_api_call('https://api.idxbroker.com/clients/counties/combinedActiveMLS');
  $zipcodes = ', "zipcodes" : '.idx_api_call('https://api.idxbroker.com/clients/zipcodes/combinedActiveMLS');
  //location lists together
  $locations = 'idxOmnibar({'.$cities.$counties.$zipcodes.'})';

  //get base Url for client's results page for use on omnibar.js front end
  $systemLinksCall = idx_api_call('https://api.idxbroker.com/clients/systemlinks?rf[]=url');


  //test to confirm API call worked properly before updating JSON file etc.
  if($systemLinksCall){
    $systemLinks = json_decode($systemLinksCall);
    file_put_contents(dirname(dirname(__FILE__)) . '/js/locationlist.json', $locations);
    //update database with new results url
    update_option('idx-results-url', get_base_url($systemLinks));
    //If invalid API key, display error
  } else {
    echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
  }
  