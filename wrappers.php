<?php
//Prevent Unauthorized Access
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

add_filter('default_content', 'idx_wrapper_content', 10, 2);
function idx_wrapper_content($content, $post){
    if($post->post_type === 'wrappers'){
        $content = idx_does_theme_include_idx_tags();
        return $content;
    }
}
//check if theme includes idxstart and stop tags
function idx_does_theme_include_idx_tags(){
    // default page content
    $post_content = '<div id="idxStart" style="display: none;"></div><div id="idxStop" style="display: none;"></div><style>.entry-title{display:none;}.entry-meta{display: none;}</style>';

    // get theme to check start/stop tag
    $isThemeIncludeIdxTag = false;
    $template_root = get_theme_root().'/'.get_stylesheet();

    $files = scandir( $template_root );

    foreach ($files as $file)
    {
        $path = $template_root . '/' . $file;
        if (is_file($path) && preg_match('/.*\.php/',$file))
        {
            $content = file_get_contents($template_root . '/' . $file);
            if (preg_match('/<div[^>\n]+?id=[\'"]idxstart[\'"].*?(\/>|><\/div>)/i', $content))
            {
                if(preg_match('/<div[^>\n]+?id=[\'"]idxstop[\'"].*?(\/>|><\/div>)/i',$content))
                {
                    $isThemeIncludeIdxTag = true;
                    break;
                }
            }
        }
    }
    if ($isThemeIncludeIdxTag)
        $post_content = '';

    return $post_content;
}

add_action( 'wp_ajax_create_dynamic_page', 'idx_ajax_create_dynamic_page' );

function idx_ajax_create_dynamic_page()
{

    $post_content = idx_does_theme_include_idx_tags();
    $post_title = htmlspecialchars($_POST['post_title']) ? htmlspecialchars($_POST['post_title']) : 'Properties';
    $new_post = array(
        'post_title' => $post_title,
        'post_name' => $post_title,
        'post_content' => $post_content,
        'post_type' => 'wrappers',
        'post_status' => 'publish'
    );
    if ($_POST['wrapper_page_id'])
    {
        $new_post['ID'] = $_POST['wrapper_page_id'];
    }
    $wrapper_page_id = wp_insert_post($new_post);
    update_option('idx_broker_dynamic_wrapper_page_name', $post_title);
    update_option('idx_broker_dynamic_wrapper_page_id', $wrapper_page_id);
    //Set Global Wrapper
    $wrapper_page_url = get_permalink($wrapper_page_id);
    idx_api("dynamicwrapperurl", idx_api_get_apiversion(), 'clients', array('body' => array('dynamicURL' => $wrapper_page_url)), 10, 'post');
    die(json_encode(array("wrapper_page_id"=>$wrapper_page_id, "wrapper_page_name" => $post_title)));
}

add_action( 'wp_ajax_delete_dynamic_page', 'idx_ajax_delete_dynamic_page' );

function idx_ajax_delete_dynamic_page() {
    if ($_POST['wrapper_page_id'])
    {
        wp_delete_post($_POST['wrapper_page_id'], true);
        wp_trash_post($_POST['wrapper_page_id']);
    }
    die();
}