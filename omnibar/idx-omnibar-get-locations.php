<?php
//Find Results URL
function get_base_url($array){
  foreach ((array)$array as $item){
    if(preg_match("/results/i", $item->url)){
      return($item->url);
    }
  }
}

//Get correct CCZ List set in admin
$omnibar_city = get_option('idx-omnibar-current-city-list');
$omnibar_county = get_option('idx-omnibar-current-county-list');
$omnibar_zipcode = get_option('idx-omnibar-current-zipcode-list');
//If none is set yet, use cobinedActiveMLS
if(! isset($omnibar_city)){
    $omnibar_city = 'combinedActiveMLS';
    update_option('idx-omnibar-current-city-list', 'combinedActiveMLS');
}
if(! isset($omnibar_county)){
    $omnibar_county = 'combinedActiveMLS';
    update_option('idx-omnibar-current-county-list', 'combinedActiveMLS');
}
if(! isset($omnibar_zipcode)){
    $omnibar_zipcode = 'combinedActiveMLS';
    update_option('idx-omnibar-current-zipcode-list', 'combinedActiveMLS');
}
  //grab responses for CCZs and add JSON object container for front end JavaScript
  $cities = '"cities" : '.json_encode(idx_api("cities/$omnibar_city", IDX_API_DEFAULT_VERSION, 'clients', array(), 10));
  $counties = ', "counties" : '.json_encode(idx_api("counties/$omnibar_county", IDX_API_DEFAULT_VERSION, 'clients', array(), 10));
  $zipcodes = ', "zipcodes" : '.json_encode(idx_api("zipcodes/$omnibar_zipcode", IDX_API_DEFAULT_VERSION, 'clients', array(), 10));
  //location lists together
  $locations = 'idxOmnibar({'.$cities.$counties.$zipcodes.'})';

  //get base Url for client's results page for use on omnibar.js front end
  $systemLinksCall = idx_api_get_systemlinks();


  //test to confirm API call worked properly before updating JSON file etc.
  if($systemLinksCall){
    file_put_contents(dirname(dirname(__FILE__)) . '/js/locationlist.json', $locations);
    
    //update database with new results url
    update_option('idx-results-url', get_base_url($systemLinksCall));
    //Update db for admin page to display latest available ccz lists
      $city_lists = idx_api("citieslistname", IDX_API_DEFAULT_VERSION, 'clients', array(), 10);
      $county_lists = idx_api("counties", IDX_API_DEFAULT_VERSION, 'clients', array(), 10);
      $zipcode_lists = idx_api("zipcodes", IDX_API_DEFAULT_VERSION, 'clients', array(), 10);
      update_option('idx-omnibar-city-lists', $city_lists);
      update_option('idx-omnibar-county-lists', $county_lists);
      update_option('idx-omnibar-zipcode-lists', $zipcode_lists);

    //If invalid API key, display error
  } else {
    echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
  }
  
