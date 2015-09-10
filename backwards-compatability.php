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
