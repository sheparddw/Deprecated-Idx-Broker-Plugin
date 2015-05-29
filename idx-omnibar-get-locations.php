<?php
//make API calls for location lists and store them in locationlist.json file
if(! function_exists('apiResponse')){
  function apiResponse($response)
  {
      if (!$response || !is_array($response) || !isset($response['response'])) {
          return array("code" => "Generic", "error" => "Unable to complete API call.");
      }
      if (!function_exists('curl_init')) {
          return array("code" => "PHP", "error" => "The cURL extension for PHP is not enabled on your server.<br />Please contact your developer and/or hosting provider.");
      }
      $responseCode = $response['response']['code'];
      $errMessage = false;
      if (is_numeric($responseCode)) {
          switch ($responseCode) {
          case 401:   $errMessage = 'Access key is invalid or has been revoked, please ensure there are no spaces in your key.<br />If the problem persists, please reset your API key in the IDX Broker Platinum Dashboard or call 800-421-9668.'; break;
          case 403:
          case 403.4: $errMessage = 'API call generated from WordPress is not using SSL (HTTPS) to communicate.<br />Please contact your developer and/or hosting provider.'; break;
          case 405:
          case 409:   $errMessage = 'Invalid request sent to IDX Broker API, please re-install the IDX Broker Platinum plugin'; break;
          case 406:   $errMessage = 'Access key is missing. To obtain an access key, please visit your IDX Broker Platinum Dashboard'; break;
          case 412:   $errMessage = 'Your account has exceeded the hourly access limit for your API key.<br />You may either wait and try again later, reset your API key in the IDX Broker Platinum Dashboard, or call 800-421-9668.'; break;
          case 500:   $errMessage = 'General system error when attempting to communicate with the IDX Broker API, please try again in a few moments or contact 800-421-9668 if the problem persists.'; break;
          case 503:   $errMessage = 'IDX Broker API is currently undergoing maintenance. Please try again in a few moments or call 800-421-9668 if the problem persists.'; break;
          }
      }
      return array("code" => $responseCode, "error" => $errMessage);
  }
}
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
        return false;
    }
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
  $locations = 'idxOmnibar({'.$cities.$counties.$zipcodes.'})';

  //get base Url for client's results page for use on omnibar.js front end
  $systemLinksCall = idx_api_call('https://api.idxbroker.com/clients/systemlinks?rf[]=url');

  //test to confirm API call worked properly before updating JSON file etc.
  if($systemLinksCall){
    $systemLinks = json_decode($systemLinksCall);

    update_json_file(WP_PLUGIN_DIR.'/idx-omnibar/assets/js/locationlist.json', $locations);

    //update idxUrl
    $wpdb->update(
    $wpdb->prefix."idx_omnibar",
      array(
        'idx_url' => get_base_url($systemLinks)
      ), array(
        'id' => 1
      )
    );
    //If invalid API key, display error
  } else {
    echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
  }
  