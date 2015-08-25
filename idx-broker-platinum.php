<?php
/*
Plugin Name: IDX Broker
Plugin URI: http://www.idxbroker.com
Description: Over 600 IDX/MLS feeds serviced. The #1 IDX/MLS solution just got even better!
Version: 1.2.2
Author: IDX Broker
Contributors: IDX, LLC
Author URI: http://www.idxbroker.com/
License: GPLv2 or later
*/

// Report all errors during development. Remember to hash out when sending to production.

error_reporting(E_ALL);

//Prevent script timeout when API response is slow
set_time_limit(0);

// The function below adds a settings link to the plugin page.
$plugin = plugin_basename(__FILE__);
$api_error = false;


define('SHORTCODE_SYSTEM_LINK', 'idx-platinum-system-link');
define('SHORTCODE_SAVED_LINK', 'idx-platinum-saved-link');
define('SHORTCODE_WIDGET', 'idx-platinum-widget');
define('IDX__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IDX_WP_PLUGIN_VERSION', '1.2.2');
define('IDX_API_DEFAULT_VERSION', '1.2.0');
define('IDX_API_URL', 'https://api.idxbroker.com/');

//Adds a comment declaring the version of the WordPress.
add_action('wp_head', 'display_wpversion');
function display_wpversion() {
    echo "\n\n<!-- Wordpress Version ";
    echo bloginfo('version');
    echo " -->";
}



/**  Register Map Libraries in case the user adds a map Widget to their site **/
add_action( 'wp_enqueue_scripts', 'wp_api_script' );
function wp_api_script() {
    wp_register_script( 'custom-scriptBing', '//ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0', __FILE__ ) ;
    wp_register_script( 'custom-scriptLeaf', '//idxdyncdn.idxbroker.com/graphical/javascript/leaflet.js', __FILE__ );
    wp_register_script( 'custom-scriptMQ', '//www.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh', __FILE__ );

    wp_enqueue_script( 'custom-scriptBing' );
    wp_enqueue_script( 'custom-scriptLeaf' );
    wp_enqueue_script( 'custom-scriptMQ' );
} // end wp_api_script fn


/**
 * Registers leaflet css
 * @return [type] [description]
 */
add_action('wp_enqueue_scripts', 'idx_register_styles'); // calls the above function
function idx_register_styles () {
    wp_register_style('cssLeaf', '//idxdyncdn.idxbroker.com/graphical/css/leaflet.css');
    wp_enqueue_style('cssLeaf');
}


/** Function that is executed when plugin is activated. **/
register_activation_hook( __FILE__, 'idx_activate');
function idx_activate() {
    global $wpdb;
    if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."posts_idx'") != $wpdb->prefix.'posts_idx') {
        $sql = "CREATE TABLE " . $wpdb->prefix."posts_idx" . " (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `post_id` int(11) NOT NULL,
                `uid` varchar(255) NOT NULL,
                `link_type` int(11) NOT NULL COMMENT '0 for system link and 1 for saved link',
                PRIMARY KEY (`id`)
                )";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    } // end if

    if(! get_option('idx-results-url')){
        add_option('idx-results-url');
    }
} // end idx_activate fn


register_uninstall_hook(__FILE__, 'idx_uninstall');
function idx_uninstall() {
    $page_id = get_option('idx_broker_dynamic_wrapper_page_id');
    if($page_id) {
        wp_delete_post($page_id, true);
        wp_trash_post($page_id);
    }
    idx_clean_transients();
}


//Adds a comment declaring the version of the IDX Broker plugin if it is activated.
add_action('wp_head', 'idx_broker_activated');
function idx_broker_activated() {
    echo "\n<!-- IDX Broker WordPress Plugin ". IDX_WP_PLUGIN_VERSION . " Activated -->\n\n";

    echo "\n<!-- IDX Broker WordPress Plugin Wrapper Meta-->\n\n";
    global $post;
    if ($post && $post->post_type === 'wrappers' || $post->ID == get_option('idx_broker_dynamic_wrapper_page_id')) {
        echo "<meta name='idx-robot'>\n";
        echo "<meta name='robots' content='noindex,nofollow'>\n";
    }
}


/**
* This function runs on plugin activation.  It sets up all options that will need to be
* saved that we know of on install, including cid, pass, domain, and main nav links from
* the idx broker system.
*
* @params void
* @return void
*/
add_action('admin_menu', 'idx_broker_platinum_options_init' );

function idx_broker_platinum_options_init() {
    global $api_error;
    //register our settings
    register_setting( 'idx-platinum-settings-group', "idx_broker_apikey" );
    register_setting( 'idx-platinum-settings-group', "idx_broker_dynamic_wrapper_page_name" );
    register_setting( 'idx-platinum-settings-group', "idx_broker_dynamic_wrapper_page_id" );
    register_setting( 'idx-platinum-settings-group', "idx_broker_admin_page_tab" );

    /*
     *  Since we have custom links that can be added and deleted inside
     *  the IDX Broker admin, we need to grab them and set up the options
     *  to control them here.  First let's grab them, if the API is not blank.
     */

    if (get_option('idx_broker_apikey') != '') {
        $systemlinks = idx_api_get_systemlinks();
        if( is_wp_error($systemlinks) ) {
            $api_error = $systemlinks->get_error_message();
            $systemlinks = '';
        }

        $savedlinks = idx_api_get_savedlinks();

        if( is_wp_error($savedlinks) ) {
            $api_error = $savedlinks->get_error_message();
            $savedlinks = '';
        }

        if(isset($_COOKIE["api_refresh"]) && $_COOKIE["api_refresh"] == 1) {
            if (! empty($systemlinks)) {
                update_system_page_links($systemlinks);
            }
            if (! empty($savedlinks)) {
                update_saved_page_links($savedlinks);
            }
        }
    }
}


/**
 * This adds the options pages to the WP admin.
 *
 * @params void
 * @return Admin Menu
 */
add_action('admin_menu', 'idx_broker_platinum_menu', 2);
function idx_broker_platinum_menu() {
    add_menu_page('IDX Broker Plugin Options', 'IDX Broker', 'administrator', 'idx-broker', 'idx_broker_platinum_admin_page', 'dashicons-admin-home', 55.572);
    add_submenu_page('idx-broker', 'IDX Broker Plugin Options', 'Initial Settings', 'administrator', 'idx-broker', 'idx_broker_platinum_admin_page');
    add_submenu_page('idx-broker', 'IDX Broker Plugin Options', 'Omnibar Settings', 'administrator', 'idx-omnibar-settings', 'idx_omnibar_settings_interface');
}


function idx_admin_scripts(){
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js', 'jquery');
    wp_enqueue_script('idxjs', plugins_url('js/idxbroker.js', __FILE__), 'jquery');
    wp_enqueue_style('idxcss', plugins_url('css/idxbroker.css', __FILE__));
}

//register wrappers and idx_page custom post types
add_action( 'init', 'idx_register_custom_post_types' );
function idx_register_custom_post_types(){
    $args = array(
            'label'             => 'IDX Pages',
            'labels'            => array( 'singular_name' => 'IDX Page' ),
            'public'            => true,
            'show_ui'           => false,
            'show_in_nav_menus' => true,
            'rewrite'           => false
        );

    register_post_type('idx_page', $args);
    $args = array(
          'public'              => true,
          'labels'              => array( 'singular_name' => 'Wrapper' ),
          'label'               => 'Wrappers',
          'description'         => 'Custom Posts Created To Match IDX Pages to the Website',
          'exclude_from_search' => true,
          'show_in_menu'        => 'idx-broker',
          'show_in_nav_menus'   => false,
          'capability_type'     => 'page'
    );
    register_post_type( 'wrappers', $args );
    
}

/**
 * This is tiggered and is run by idx_broker_menu, it's the actual IDX Broker Admin page and display.
 *
 * @params void
 * @return void
*/
function idx_broker_platinum_admin_page() {
    include(IDX__PLUGIN_DIR . '/views/admin.php');
}

/**
 * Function to delete existing cache. So API response in cache will be deleted
 *
 * @param void
 * @return void
 *
 */
add_action('wp_ajax_idx_refresh_api', 'idx_refreshapi' );

function idx_refreshapi()
{
    idx_clean_transients();
    update_option('idx_broker_apikey', $_REQUEST['idx_broker_apikey']);
    setcookie("api_refresh", 1, time() + 20);
    update_tab();
    die();
}

/**
 * Clean IDX cached data
 *
 * @param void
 * @return void
 */
function idx_clean_transients()
{
    // clean old key before 1.1.6
    if (get_transient('idx_savedlink_cache')) {
        delete_transient('idx_savedlink_cache');
    }
    if (get_transient('idx_widget_cache')) {
        delete_transient('idx_widget_cache');
    }

    if (get_transient('idx_savedlinks_cache')) {
        delete_transient('idx_savedlinks_cache');
    }

    if (get_transient('idx_widgetsrc_cache')) {
        delete_transient('idx_widgetsrc_cache');
    }
    if (get_transient('idx_systemlinks_cache')) {
        delete_transient('idx_systemlinks_cache');
    }
    if (get_transient('idx_apiversion_cache')) {
        delete_transient('idx_apiversion_cache');
    }
}



/**
 * Function to display warning message in permalink page
 *
 * @param void
 * @return void
 *
 */
function idxplatinum_notice() {
    global $current_screen;
    echo '<div id="message" class="error"><p><strong>Note that your IDX Broker page links are not governed by WordPress Permalinks. To apply changes to your IDX Broker URLs, you must login to your IDX Broker Control Panel.</strong></p></div>';
}

/**
 * Function to generate permalink warning message
 *
 * @param void
 * @return void
 */
add_action('init', 'permalink_update_warning');
function permalink_update_warning () {
    if(isset($_POST['permalink_structure']) || isset($_POST['category_base'])) {
        add_action('admin_notices', 'idxplatinum_notice');
    }
}

/**
* Add Omnibar Search Widget:
*/
idx_load_plugin_files();
function idx_load_plugin_files(){
    require_once('idx-broker-platinum-api.php');
    require_once('idx-broker-widgets.php');
    require_once('backwards-compatability.php');
    require_once('idx-pages.php');
    require_once('wrappers.php');
    require_once('shortcodes.php');
    require_once('omnibar/idx-omnibar-widget.php');
    require_once('omnibar/idx-set-ccz-lists.php');
    require_once('views/ccz-view.php');
}
