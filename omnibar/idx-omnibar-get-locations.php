<?php
//Prevent Unauthorized Access
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

new Idx_Omnibar_Get_Locations;

class Idx_Omnibar_Get_Locations {

  public function __construct(){
    $api_key = get_option('idx_broker_apikey');
    if (! empty($api_key) ) {
        $this->initiate_get_locations();
    }

  }

  //Find Results URL
  public function get_base_url($array){
    foreach ((array)$array as $item){
      if(preg_match("/results/i", $item->url)){
        return($item->url);
      }
    }
  }

  /*
  * Custom Advanced Fields added via admin
  */
  //used in get_additional_fields function
  public function get_idxIDs($array){
      $idxIDs = array();
      foreach($array as $field){
          $idxID = $field['idxID'];
          if(! in_array($idxID, $idxIDs)){
              array_push($idxIDs, $idxID);
          }
      }
      return $idxIDs;
  }
  //used in get_additional_fields function
  public function fields_in_idxID($idxIDMatch, $fields){
      $output = '';
      $first_run_for_idxID = TRUE;
      for($i = 0; $i<count($fields); $i++){
          $field = $fields[$i];
          $idxID = $field['idxID'];
          $name = $field['value'];
          $mlsPtID = $field['mlsPtID'];
          $prefix = ', {"'.$name.'" : ';
          if($first_run_for_idxID){
              $prefix = '{"'.$name.'" : ';
          }
          if($idxIDMatch === $idxID){
              $first_run_for_idxID = FALSE;
              $field_values = json_encode(idx_api("searchfieldvalues/$idxID?mlsPtID=$mlsPtID&name=$name", idx_api_get_apiversion(), 'mls', array(), 86400));
              $output .= "$prefix $field_values }";
          }

      }
      return $output;
  }
  //used to retrieve all fields and create JSON objects by each idxID for each field
  public function get_additional_fields(){
      $fields = get_option('idx-omnibar-custom-fields');
      if(empty($fields)){
          return;
      }
      $idxIDs = $this->get_idxIDs($fields);
      $output = '';
      foreach($idxIDs as $idxID){
          $fields_in_idxID = $this->fields_in_idxID($idxID, $fields);
          $output .= ", {\"$idxID\" : [ $fields_in_idxID ]}";
      }
      return $output;
  }
  //for display on the front end.
  public function create_custom_fields_key(){
      $custom_fields_key = array();
      $fields = get_option('idx-omnibar-custom-fields');
      if(empty($fields)){
        return;
      }
      foreach($fields as $field){
          $name = $field['value'];
          $mlsPtID = $field['mlsPtID'];
          $displayName = $field['name'];
          $custom_fields_key[$name] = $displayName;
      }
      return 'var customFieldsKey = ' . json_encode($custom_fields_key) . '; ';
  }


  public function get_cczs(){
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
      $cities = '"cities" : '.json_encode(idx_api("cities/$omnibar_city", idx_api_get_apiversion(), 'clients', array(), 10));
      $counties = ', "counties" : '.json_encode(idx_api("counties/$omnibar_county", idx_api_get_apiversion(), 'clients', array(), 10));
      $zipcodes = ', "zipcodes" : '.json_encode(idx_api("zipcodes/$omnibar_zipcode", idx_api_get_apiversion(), 'clients', array(), 10));
    return $cities . $counties . $zipcodes;
  }

  public function initiate_get_locations(){
    $cczs = $this->get_cczs();
    //location lists together
    $locations = 'idxOmnibar( [{"core" : {'.$cczs.'} }'. $this->get_additional_fields().']);';

    $output = $this->create_custom_fields_key() . $locations;
    //get base Url for client's results page for use on omnibar.js front end
    $system_links_call = idx_api_get_systemlinks();

    //test to confirm an API call worked properly before updating JSON file etc.
    if($system_links_call){
      file_put_contents(dirname(dirname(__FILE__)) . '/js/locationlist.json', $output);

      //update database with new results url
      update_option('idx-results-url', $this->get_base_url($system_links_call));
      //Update db for admin page to display latest available ccz lists
        $city_lists = idx_api("citieslistname", idx_api_get_apiversion(), 'clients', array(), 10);
        $county_lists = idx_api("counties", idx_api_get_apiversion(), 'clients', array(), 10);
        $zipcode_lists = idx_api("zipcodes", idx_api_get_apiversion(), 'clients', array(), 10);
        update_option('idx-omnibar-city-lists', $city_lists);
        update_option('idx-omnibar-county-lists', $county_lists);
        update_option('idx-omnibar-zipcode-lists', $zipcode_lists);
    }
  }

}

