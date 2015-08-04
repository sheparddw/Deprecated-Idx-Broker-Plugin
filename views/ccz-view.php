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

if(! empty($omnibar_cities)){
   echo "<div id=\"omnibar-ccz\"><h3>Omnibar Search Widget Location Lists</h3><div class=\"city-list\"><label>City List:</label><select name=\"city-list\">";

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
    echo "</select></div><div class=\"zipcode-list\"><label>Zipcode List:</label><select name=\"zipcode-list\">";
        foreach ($omnibar_zipcodes as $lists => $list) {
            //create options for each list and select currently saved option in select by default
            echo "<option value=\"$list\"".is_saved($list, get_option('idx-omnibar-current-zipcode-list')).">$list</option>";
        }
    echo "</select></div></div>";
}