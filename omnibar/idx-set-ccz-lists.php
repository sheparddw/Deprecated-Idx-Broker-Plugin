<?php


/** Update Saved CCZ Lists for Omnibar when Admin form is saved
 * @param void
*/
function idx_update_omnibar_current_ccz(){
    $city_list = $_POST['city-list'];
    $county_list = $_POST['county-list'];
    $zipcode_list = $_POST['zipcode-list'];
    update_option('idx-omnibar-current-city-list', $city_list);
    update_option('idx-omnibar-current-county-list', $county_list);
    update_option('idx-omnibar-current-zipcode-list', $zipcode_list);
}
add_action('wp_ajax_idx_update_omnibar_current_ccz', 'idx_update_omnibar_current_ccz');


function idx_update_omnibar_custom_fields(){
	update_option('idx-omnibar-custom-fields', $_POST['fields']);
	update_option('idx-default-property-types', $_POST['mlsPtIDs']);
	update_option('idx-omnibar-placeholder', $_POST['placeholder']);
	include 'idx-omnibar-get-locations.php';
	return wp_die();
}
add_action('wp_ajax_idx_update_omnibar_custom_fields', 'idx_update_omnibar_custom_fields');


//custom fields:
function idx_omnibar_advanced_fields(){
	class IDX_Omnibar_Custom_Fields{
		function __construct($idxID, $mls_name, $field_names, $property_types){
			$this->idxID = $idxID;
			$this->field_names = $field_names;
			$this->mls_name = $mls_name;
			$this->property_types = $property_types;
		}
		function return_fields(){
			return array(
				'idxID'=>$this->idxID,
				'mls_name' => $this->mls_name,
				'field_names' => $this->field_names
			);
		}
		function return_mlsPtIDs(){
			return array(
				'idxID'=>$this->idxID,
				'mls_name'=>$this->mls_name,
				'property_types'=>$this->property_types
				);
		}
		public $idxID;
		public $mls_name;
		public $field_names;
		public $property_types;
	}

	//Grab all advanced field names for all MLS

	//grab all idxIDs for account
	$mls_list = idx_api('approvedmls', idx_api_get_apiversion(), 'mls', array(), 86400);
	$all_mls_fields = array();
	$all_mlsPtIDs = array();

	//grab all field names for each idxID
	foreach($mls_list as $mls){
		$idxID = $mls->id;
		$mls_name = $mls->name;
		$fields = json_encode(idx_api("searchfields/$idxID", idx_api_get_apiversion(), 'mls', array(), 86400));
		$property_types = json_encode(idx_api("propertytypes/$idxID", idx_api_get_apiversion(), 'mls', array(), 86400));
		$mls_object = new IDX_Omnibar_Custom_Fields($idxID, $mls_name, $fields, $property_types);
		$mls_fields_object = $mls_object->return_fields();
		$mls_property_types_object = $mls_object->return_mlsPtIDs();
		//push all fieldnames for each MLS to array
		array_push($all_mls_fields, $mls_fields_object);
		array_push($all_mlsPtIDs, $mls_property_types_object);
	}
	return array(array_unique($all_mls_fields, SORT_REGULAR), $all_mlsPtIDs);
}
