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

function idx_update_omnibar_custom_fields(){
	die('test');
}
add_action('wp_ajax_idx_update_omnibar_current_ccz', 'idx_update_omnibar_current_ccz');
add_action('wp_ajax_idx_update_omnibar_custom_fields', 'idx_update_omnibar_custom_fields');







//custom fields:

/*
API Calls {
	grab approved mls: https://api.idxbroker.com/mls/approvedmls
		[{"id":"a001","name":"Regional Multiple Listing Service"}]
	grab advanced fields via api: https://api.idxbroker.com/mls/searchfields/a550
		[{"name":"acres","displayName":"Acres","mlsPtID":"1","parentPtID":"1"}]
	grab advanced field values to write to locationlist.json file: https://api.idxbroker.com/mls/searchfieldvalues/a550?mlsPtID=1&name=subdivision
		["Antelope Creek Valle","Shady Cove\/Trail","Eagle Point","Southwest Medford"]

	account type: https://api.idxbroker.com/clients/accounttype
	
}
*/
function idx_omnibar_advanced_fields(){
	class IDX_Omnibar_Custom_Fields{
		function __construct($idxID, $mls_name, $field_names){
			$this->idxID = $idxID;
			$this->field_names = $field_names;
			$this->mls_name = $mls_name;
		}
		function return_array(){
			return array(
				'idxID'=>$this->idxID,
				'mls_name' => $this->mls_name,
				'field_names' => $this->field_names
			);
		}
		public $idxID;
		public $mls_name;
		public $field_names;
	}

	//Grab all advanced field names for all MLS

	//grab all idxIDs for account
	$mls_list = idx_api('approvedmls', idx_api_get_apiversion(), 'mls');
	$all_mls_fields = array();


	//grab all field names for each idxID
	foreach($mls_list as $mls){
		

		$idxID = $mls->id;
		$mls_name = $mls->name;
		$fields = json_encode(idx_api("searchfields/$idxID", idx_api_get_apiversion(), 'mls'));
		$mls_fields_object = new IDX_Omnibar_Custom_Fields($idxID, $mls_name, $fields);
		$mls_fields_object = $mls_fields_object->return_array();
		//push all fieldnames for each MLS to array
		array_push($all_mls_fields, $mls_fields_object);
		
	}

	return array_unique($all_mls_fields, SORT_REGULAR);


}


