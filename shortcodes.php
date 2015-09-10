<?php
//Prevent Unauthorized Access
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

//Register the idx button

/**
 * registers the buttons for use
 * @param array $buttons
 */
function register_idx_buttons($buttons) {
    // inserts a separator between existing buttons and our new one
    array_push($buttons, "|", "idx_button");
    return $buttons;
}

/**
 * filters the tinyMCE buttons and adds our custom buttons
 */
add_action('init', 'idx_buttons');
function idx_buttons() {
    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
        return;
    // Add only in Rich Editor mode
    if ( get_user_option('rich_editing') == 'true' ) {
        // filter the tinyMCE buttons and add our own
        add_filter("mce_external_plugins", "add_idx_tinymce_plugin");
        add_filter('mce_buttons', 'register_idx_buttons');
    } // end if rich editing true
} // end idx_buttons fn

/**
 * add the button to the tinyMCE bar
 * @param array $plugin_array
 */
function add_idx_tinymce_plugin($plugin_array) {
    $plugin_array['idx_button'] = plugins_url('js/idx-buttons.js', __FILE__);
    return $plugin_array;
}

/**
 * Function to show a idx link with shortcode of type:
 * [idx-platinum-link title="title here"]
 *
 * @param array $atts
 * @return html code for showing the link/ bool false
 */
add_shortcode('idx-platinum-link', 'show_link');
function show_link($atts) {
    extract( shortcode_atts( array(
            'title' => NULL
    ), $atts ) );

    if(!is_null($title)) {
        $page = get_page_by_title($title);
        $permalink = get_permalink($page->ID);
        return '<a href="'.get_permalink($page->ID).'">'.$page->post_title.'</a>';
    } else {
        return false;
    }
}

/**
 * FUnction to show a idx system link with shortcode of type:
 * [idx-platinum-system-link title="title here"]
 *
 * @param array $atts
 * @return string|boolean
 */
add_shortcode('idx-platinum-system-link', 'show_system_link');
function show_system_link($atts) {
    extract( shortcode_atts( array(
            'id' => NULL,
            'title' => NULL,
    ), $atts ) );

    if(!is_null($id)) {
        $link = idx_get_link_by_uid($id, 0);
        if(is_object($link)) {
            if(!is_null($title)) {
                $link->name = $title;
            }
            return '<a href="'.$link->url.'">'.$link->name.'</a>';
        }
    } else {
        return false;
    }
}

/**
 * FUnction to show a idx ssaved link with shortcode of type:
 * [idx-platinum-saved-link title="title here"]
 *
 * @param array $atts
 * @return string|boolean
 */
add_shortcode('idx-platinum-saved-link', 'show_saved_link');
function show_saved_link($atts) {
    extract( shortcode_atts( array(
            'id' => NULL,
            'title' => NULL
    ), $atts ) );

    if(!is_null($id)) {
        $link = idx_get_link_by_uid($id, 1);
        if(is_object($link)) {
            if(!is_null($title)) {
                $link->name = $title;
            }
            return '<a href="'.$link->url.'">'.$link->name.'</a>';
        }
    } else {
        return false;
    }
}

/**
 * Function to get the widget code by title
 *
 * @param string $title
 * @return html code for showing the widget
 */
function idx_get_link_by_uid($uid, $type = 0) {
    if($type == 0) {
        // if the cache has expired, send an API request to update them. Cache expires after 2 hours.
        if (! get_transient('idx_systemlinks_cache') )
            idx_api_get_systemlinks();
        $idx_links = get_transient('idx_systemlinks_cache');
    } elseif ($type == 1) {
        if (get_transient('idx_savedlinks_cache')) {
            delete_transient('idx_savedlinks_cache');
        }
        $idx_links = idx_api_get_savedlinks();
    }

    $selected_link = '';

    if($idx_links) {
        foreach($idx_links as $link) {
            if(strcmp($link->uid, $uid) == 0) {
                $selected_link = $link;
            }
        }
    }
    return $selected_link;
}

/**
 * Function to show a idx link with shortcode of type:
 * [idx-platinum-link title="widget title here"]
 *
 * @param array $atts
 * @return html code for showing the widget/ bool false
 */
add_shortcode('idx-platinum-widget', 'show_widget');
function show_widget($atts) {
    extract( shortcode_atts( array(
            'id' => NULL
    ), $atts ) );

    if(!is_null($id)) {
        return get_widget_by_uid($id);
    } else {
        return false;
    }
}

/**
 * Function to get the widget code by title
 *
 * @param string $title
 * @return html code for showing the widget
 */
function get_widget_by_uid($uid) {
    $idx_widgets = idx_api_get_widgetsrc();
    $idx_widget_code = null;

    if($idx_widgets) {
        foreach($idx_widgets as $widget) {
            if(strcmp($widget->uid, $uid) == 0) {
                $idx_widget_link = $widget->url;
                $idx_widget_code =  '<script src="'.$idx_widget_link.'"></script>';
                return $idx_widget_code;
            }
        }
    } else {
        return $idx_widget_code;
    }
}

/**
 * Function to print the system/saved link shortcodes.
 *
 * @param int $link_type 0 for system link and 1 for saved link
 */
function show_link_short_codes($link_type = 0) {
    $available_shortcodes = '';

    if($link_type === 0) {
        $short_code = SHORTCODE_SYSTEM_LINK;
        $idx_links = idx_api_get_systemlinks();
    } elseif($link_type == 1) {
        $short_code = SHORTCODE_SAVED_LINK;
        $idx_links = idx_api_get_savedlinks();
    } else {
        return false;
    }

    if(count($idx_links) > 0 && is_array($idx_links)) {
        foreach ($idx_links as $idx_link) {
            if ($link_type === 0) {
                $available_shortcodes .= get_system_link_html($idx_link);
            }
            if($link_type == 1) {
                $available_shortcodes .= get_saved_link_html($idx_link);
            }
        }
    } else {
        $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
    }
    echo $available_shortcodes;
}

/**
 * Function to return the HTM for displaying each system link
 * @param object $idx_link
 * @return string
 */
function get_system_link_html($idx_link) {
    $available_shortcodes = "";

    if ($idx_link->systemresults != 1) {
        $link_short_code = '['.SHORTCODE_SYSTEM_LINK.' id ="'.$idx_link->uid.'" title ="'.$idx_link->name.'"]';
        $available_shortcodes .= '<div class="each_shortcode_row">';
        $available_shortcodes .= '<input type="hidden" id=\''.$idx_link->uid.'\' value=\''.$link_short_code.'\'>';
        $available_shortcodes .= '<span>'.$idx_link->name.'&nbsp;<a name="'.$idx_link->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$idx_link->uid.'\')" class="shortcode_link">insert</a>
        &nbsp;<a href="?uid='.urlencode($idx_link->uid).'&current_title='.urlencode($idx_link->name).'&short_code='.urlencode($link_short_code).'">change title</a>
        </span>';
        $available_shortcodes .= '</div>';
    }
    return $available_shortcodes;
}

/**
 * Function to return the HTM for displaying each saved link
 * @param object $idx_link
 * @return string
 */
function get_saved_link_html($idx_link) {
    $available_shortcodes = "";
    $link_short_code = '['.SHORTCODE_SAVED_LINK.' id ="'.$idx_link->uid.'" title ="'.$idx_link->linkTitle.'"]';
    $available_shortcodes .= '<div class="each_shortcode_row">';
    $available_shortcodes .= '<input type="hidden" id=\''.$idx_link->uid.'\' value=\''.$link_short_code.'\'>';
    $available_shortcodes .= '<span>'.$idx_link->linkTitle.'&nbsp;<a name="'.$idx_link->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$idx_link->uid.'\')" class="shortcode_link">insert</a>
    &nbsp;<a href="?uid='.urlencode($idx_link->uid).'&current_title='.urlencode($idx_link->linkTitle).'&short_code='.urlencode($link_short_code).'">change title</a>
    </span>';

    $available_shortcodes .= '</div>';

    return $available_shortcodes;
}

/**
 * Function to print the shortcodes of all the widgets
 */
function show_widget_shortcodes() {
    $idx_widgets = get_transient('idx_widgetsrc_cache');
    $available_shortcodes = '';

    if($idx_widgets) {
        foreach($idx_widgets as $widget) {
            $widget_shortcode = '['.SHORTCODE_WIDGET.' id ="'.$widget->uid.'"]';
            $available_shortcodes .= '<div class="each_shortcode_row">';
            $available_shortcodes .= '<input type="hidden" id=\''.$widget->uid.'\' value=\''.$widget_shortcode.'\'>';
            $available_shortcodes .= '<span>'.$widget->name.'&nbsp;<a name="'.$widget->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$widget->uid.'\')">insert</a></span>';
            $available_shortcodes .= '</div>';
        }
    } else {
        $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
    }
    echo $available_shortcodes;
}