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



