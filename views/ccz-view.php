<?php

//Shows which ccz list is currently being used by the omnibar
$omnibar_cities = get_option('idx-omnibar-city-lists');
$omnibar_counties = get_option('idx-omnibar-county-lists');
$omnibar_zipcodes = get_option('idx-omnibar-zipcode-lists');
function is_saved($id, $saved_id){
    if($id === $saved_id){
        return 'selected';
    } else {
        return '';
    }
}
function in_saved_array($name, $array, $idxID){
    if(empty($array)){
        return FALSE;
    }
    foreach($array as $field){
        if(in_array($name, $field) && in_array($idxID, $field)){
            return $name;
        }
    }
}

if(! empty($omnibar_cities)){
   echo "<div id=\"omnibar-ccz\"><h3>Omnibar Search Widget Settings <a href=\"http://support.idxbroker.com/customer/portal/articles/2081878-widget---wordpress-omnibar-search\" target=\"_blank\"><img src=\"".plugins_url('../images/helpIcon.png', __FILE__)."\" alt=\"help\"></a></h3><h4>City, County, and Postal Code Lists</h4><div class=\"city-list\"><label>City List:</label><select name=\"city-list\">";

        foreach ($omnibar_cities as $lists => $list) {
            foreach($list as $list_option => $list_option_value){
                if($list_option === 'id'){
                    $id = $list_option_value;
                } 
                if($list_option === 'name') {
                    $name = $list_option_value;
                }
            }
            //create options for each list and select currently saved option in select by default
            echo "<option value=\"$id\"".is_saved($id, get_option('idx-omnibar-current-city-list')).">$name</option>";
        }
    echo "</select></div><div class=\"county-list\"><label>County List:</label><select name=\"county-list\">";
        foreach ($omnibar_counties as $lists => $list) {
            //create options for each list and select currently saved option in select by default
            echo "<option value=\"$list\"".is_saved($list, get_option('idx-omnibar-current-county-list')).">$list</option>";
        }
    echo "</select></div><div class=\"zipcode-list\"><label>Postal Code List:</label><select name=\"zipcode-list\">";
        foreach ($omnibar_zipcodes as $lists => $list) {
            //create options for each list and select currently saved option in select by default
            echo "<option value=\"$list\"".is_saved($list, get_option('idx-omnibar-current-zipcode-list')).">$list</option>";
        }
    echo "</select></div></div>";

//Advanced Fields:
    $all_mls_fields = idx_omnibar_advanced_fields();
//echo them as one select
    echo "<h4>Custom Fields</h4>";
    echo "<select class=\"omnibar-additional-custom-field select2\" name=\"omnibar-additional-custom-field\" multiple=\"multiple\">";
    foreach($all_mls_fields[0] as $mls){
        $mls_name = $mls['mls_name'];
        $idxID = $mls['idxID'];
        echo "<optgroup label=\"$mls_name\" class=\"$idxID\">";
        $fields = json_decode($mls['field_names']);
        //make sure field names only appear once per MLS
        $unique_values = array();

        foreach($fields as $field){
            $name = $field->displayName;
            $value = $field->name;
            $mlsPtID = $field->mlsPtID;
            if(! in_array($value, $unique_values, TRUE) && $name !== ''){
                array_push($unique_values, $value);
                echo "<option value=\"$value\"".is_saved($value, in_saved_array($value, get_option('idx-omnibar-custom-fields'), $idxID))." data-mlsPtID=\"$mlsPtID\">$name</option>";
            }
            
        }
        echo "</optgroup>";
    }
    echo "</select>";

    //Default property type for each MLS
    echo "<h4>Default Property Type for Custom Field Searches</h4><div class=\"idx-property-types\">";
    foreach($all_mls_fields[1] as $mls){
        $mls_name = $mls['mls_name'];
        $idxID = $mls['idxID'];
        $property_types = json_decode($mls['property_types']);
        echo "<div><label for=\"$idxID\">$mls_name:</label>";
        echo "<select class=\"omnibar-mlsPtID\" name=\"$idxID\">";
            foreach($property_types as $property_type){
                $mlsPtID = $property_type->mlsPtID;
                $mlsPropertyType = $property_type->mlsPropertyType;
                echo "<option value=\"$mlsPtID\"".is_saved($mlsPtID, in_saved_array($mlsPtID, get_option('idx-default-property-types'), $idxID)).">$mlsPropertyType</option>";
            }
        echo "</select></div>";

    }
    echo "</div>";
}
