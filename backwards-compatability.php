<?php
//Prevent Unauthorized Access
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

/**
 * Function to redirect the page based upon _links_to_ attribute
 *
 * @param void
 * @return void
 */
add_action( 'template_redirect', 'idxplatinum_redirect_links_to_pages');
function idxplatinum_redirect_links_to_pages() {
    if ( !is_single() && !is_page() )
        return;

    global $wp_query;

    $link = get_post_meta( $wp_query->post->ID, '_links_to', true );
    if ( !$link )
        return;

    $redirect_type = get_post_meta( $wp_query->post->ID, '_links_to_type', true );
    $redirect_type = ( $redirect_type = '302' ) ? '302' : '301';
    wp_redirect( $link, $redirect_type );

    exit;
}

       
/**        
 * FUnction to check if a link is system link or not       
 * @param link name $link_name     
 */        
function check_system_link($link_name) {       
    if(strpos($link_name, 'idx_platinum_system') !== false) {      
        return true;       
    } else {       
        return false;      
    }      
}      
       
       
function check_saved_link($link_name)      
{      
    if(strpos($link_name, 'idx_platinum_saved') !== false) {       
        return true;       
    } else {       
        return false;      
    }      
}      
       

/**        
 * FUnction to get current saved links     
 */        
function get_my_saved_links() {        
    global $wpdb;      
    return $wpdb->get_col("SELECT uid from ".$wpdb->prefix."posts_idx where link_type = 1");       
}      
       
    
       
/**        
 * Function to get meta data of created pages uisng IDX settings page      
 *     
 * @params void        
 * @return String Page/Post URL        
 */        
function idxplatinum_get_page_links_to_meta () {       
    global $wpdb, $page_links_to_cache, $blog_id;      
       
    if ( !isset( $page_links_to_cache[$blog_id] ) )        
        $links_to = idxplatinum_get_post_meta_by_key( '_links_to' );       
    else       
        return $page_links_to_cache[$blog_id];     
       
    if ( !$links_to ) {        
        $page_links_to_cache[$blog_id] = false;        
        return false;      
    }      
       
    foreach ( (array) $links_to as $link )     
        $page_links_to_cache[$blog_id][$link->post_id] = $link->meta_value;        
       
    return $page_links_to_cache[$blog_id];     
}      
       
/**        
 * Function to override permalink tab in post/page section of Wordpress        
 *     
 * @params string $link        
 * @params object post details     
 * @return string Page/Post URL        
 */        
add_filter('page_link', 'idxplatinum_filter_links_to_pages', 20, 2);       
add_filter('post_link', 'idxplatinum_filter_links_to_pages', 20, 2);       
function idxplatinum_filter_links_to_pages ($link, $post) {        
    $page_links_to_cache = idxplatinum_get_page_links_to_meta();       
       
    // Really strange, but page_link gives us an ID and post_link gives us a post object       
    $id = isset( $post->ID ) ? $post->ID : $post;      
    if ( isset($page_links_to_cache[$id]) )        
        $link = esc_url( $page_links_to_cache[$id] );      
       
    return $link;      
}      

/**        
 * Function to highlight the page links        
 *     
 * @param array $pages     
 * @return array $pages        
 */        
add_filter('wp_list_pages', 'idxplatinum_page_links_to_highlight_tabs', 9);        
function idxplatinum_page_links_to_highlight_tabs( $pages ) {      
    // remove wrapper page     
    $page_links_to_cache = idxplatinum_get_page_links_to_meta();       
    $page_links_to_target_cache = idxplatinum_get_page_links_to_targets();     
    if ( !$page_links_to_cache && !$page_links_to_target_cache )       
        return $pages;     
       
    $this_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];       
    $targets = array();        
       
    foreach ( (array) $page_links_to_cache as $id => $page ) {     
        if ( isset( $page_links_to_target_cache[$id] ) )       
            $targets[$page] = $page_links_to_target_cache[$id];        
        if ( str_replace( 'http://www.', 'http://', $this_url ) == str_replace( 'http://www.', 'http://', $page ) || ( is_home() && str_replace( 'http://www.', 'http://', trailingslashit( get_bloginfo( 'url' ) ) ) == str_replace( 'http://www.', 'http://', trailingslashit( $page ) ) ) ) {       
            $highlight = true;     
            $current_page = esc_url( $page );      
        }      
    }      
       
    if ( count( $targets ) ) {     
        foreach ( $targets as  $p => $t ) {        
            $p = esc_url( $p );        
            $t = esc_attr( $t );       
            $pages = str_replace( '<a href="' . $p . '" ', '<a href="' . $p . '" target="' . $t . '" ', $pages );      
        }      
    }      
       
    global $highlight;     
       
    if ( $highlight ) {        
        $pages = preg_replace( '| class="([^"]+)current_page_item"|', ' class="$1"', $pages ); // Kill default highlighting        
        $pages = preg_replace( '|<li class="([^"]+)"><a href="' . $current_page . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages );      
    }      
       
    return $pages;     
}      
       
/**        
 * Function to get page _link _to_ targets     
 *     
 * @param void     
 * @return string page meta value      
 */        
function idxplatinum_get_page_links_to_targets () {        
    global $wpdb, $page_links_to_target_cache, $blog_id;       
       
    if ( !isset( $page_links_to_target_cache[$blog_id] ) )     
        $links_to = idxplatinum_get_post_meta_by_key( '_links_to_target' );        
    else       
        return $page_links_to_target_cache[$blog_id];      
       
    if ( !$links_to ) {        
        $page_links_to_target_cache[$blog_id] = false;     
        return false;      
    }      
       
    foreach ( (array) $links_to as $link )     
        $page_links_to_target_cache[$blog_id][$link->post_id] = $link->meta_value;     
       
    return $page_links_to_target_cache[$blog_id];      
}      
       
/**        
 * Functiom to get post meta by key        
 *     
 * @param string $key      
 * @return string meta value       
 */        
function idxplatinum_get_post_meta_by_key( $key ) {        
    global $wpdb;      
    return $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key ) );        
}      
       
/**        
 * Function to delete saved IDX page IDs from option table     
 *     
 * @param integer page_id      
 * @return void        
 *     
 */        
add_action('before_delete_post', 'idxplatinum_update_pages');      
function idxplatinum_update_pages($post_ID) {      
    global $wpdb;      
       
    $wpdb->query("DELETE from ".$wpdb->prefix."posts_idx where post_id = $post_ID");       
    delete_post_meta( $post_ID, '_links_to' );     
    delete_post_meta( $post_ID, '_links_to_target' );      
    delete_post_meta( $post_ID, '_links_to_type' );        
}      
       
/**        
 * Function to delete meta table if post/page is deleted by user       
 *     
 * @param integer $post_ID     
 * @return integer $post_ID        
 */        
add_action('save_post', 'idxplatinum_plt_save_meta_box');      
function idxplatinum_plt_save_meta_box( $post_ID ) {       
    if ( wp_verify_nonce( isset($_REQUEST['_idx_pl2_nonce']), 'idxplatinum_plt' ) ) {      
        if ( isset( $_POST['idx_links_to'] ) && strlen( $_POST['idx_links_to'] ) > 0 && $_POST['idx_links_to'] !== 'http://' ) {       
            $link = stripslashes( $_POST['idx_links_to'] );        
       
            if ( 0 === strpos( $link, 'www.' ) )       
                $link = 'http://' . $link; // Starts with www., so add http://     
       
            update_post_meta( $post_ID, '_links_to', $link );      
       
            if ( isset( $_POST['idx_links_to_new_window'] ) )      
                update_post_meta( $post_ID, '_links_to_target', '_blank' );        
            else       
                delete_post_meta( $post_ID, '_links_to_target' );      
       
            if ( isset( $_POST['idx_links_to_302'] ) )     
                update_post_meta( $post_ID, '_links_to_type', '302' );     
            else       
                delete_post_meta( $post_ID, '_links_to_type' );        
        } else {       
            delete_post_meta( $post_ID, '_links_to' );     
            delete_post_meta( $post_ID, '_links_to_target' );      
            delete_post_meta( $post_ID, '_links_to_type' );        
        }      
    }      
    return $post_ID;       
}      