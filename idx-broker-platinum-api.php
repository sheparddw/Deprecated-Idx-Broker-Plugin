<?php
//Prevent Unauthorized Access
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );
/**
 *
 * Using our web services function, lets get the system links built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 *
 */
function idx_api_get_systemlinks()
{
    if (!get_option('idx_broker_apikey')) {
        return array();
    }
    return idx_api('systemlinks', idx_api_get_apiversion());
}

/**
 *
 * Using our web services function, lets get saved links built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 *
 */
function idx_api_get_savedlinks()
{
    if (!get_option('idx_broker_apikey')) {
        return array();
    }
    return idx_api('savedlinks', idx_api_get_apiversion());
}

/**
 *
 * Using our web services function, lets get the widget details built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 *
 */
function idx_api_get_widgetsrc()
{
    if (!get_option('idx_broker_apikey')) {
        return array();
    }
    return idx_api('widgetsrc', idx_api_get_apiversion());
}

/**
 * Get api version
 */
function idx_api_get_apiversion()
{
    if (!get_option('idx_broker_apikey')) {
        return IDX_API_DEFAULT_VERSION;
    }

    $data = idx_api('apiversion', IDX_API_DEFAULT_VERSION, 'clients', array(), 86400);
    if (is_array($data) && !empty($data)) {
        return $data['version'];
    } else {
       return IDX_API_DEFAULT_VERSION;
    }
}

/**
 * apiResponse handles the various replies we get from the IDX Broker API and returns appropriate error messages.
 * @param  [array] $response [response header from API call]
 * @return [array]           [keys: 'code' => response code, 'error' => false (default), or error message if one is found]
 */
function apiResponse($response)
{
    if (!$response || !is_array($response) || !isset($response['response'])) {
        return array("code" => "Generic", "error" => "Unable to complete API call.");
    }
    if (!function_exists('curl_init')) {
        return array("code" => "PHP", "error" => "The cURL extension for PHP is not enabled on your server.<br />Please contact your developer and/or hosting provider.");
    }
    $responseCode = $response['response']['code'];
    $errMessage = false;
    if (is_numeric($responseCode)) {
        switch ($responseCode) {
        case 401:   $errMessage = 'Access key is invalid or has been revoked, please ensure there are no spaces in your key.<br />If the problem persists, please reset your API key in the IDX Broker Platinum Dashboard or call 800-421-9668.'; break;
        case 403:
        case 403.4: $errMessage = 'API call generated from WordPress is not using SSL (HTTPS) to communicate.<br />Please contact your developer and/or hosting provider.'; break;
        case 405:
        case 409:   $errMessage = 'Invalid request sent to IDX Broker API, please re-install the IDX Broker Platinum plugin'; break;
        case 406:   $errMessage = 'Access key is missing. To obtain an access key, please visit your IDX Broker Platinum Dashboard'; break;
        case 412:   $errMessage = 'Your account has exceeded the hourly access limit for your API key.<br />You may either wait and try again later, reset your API key in the IDX Broker Platinum Dashboard, or call 800-421-9668.'; break;
        case 500:   $errMessage = 'General system error when attempting to communicate with the IDX Broker API, please try again in a few moments or contact 800-421-9668 if the problem persists.'; break;
        case 503:   $errMessage = 'IDX Broker API is currently undergoing maintenance. Please try again in a few moments or call 800-421-9668 if the problem persists.'; break;
        }
    }
    return array("code" => $responseCode, "error" => $errMessage);
}

/**
 * IDX API Request
 */
function idx_api($method, $apiversion = IDX_API_DEFAULT_VERSION, $level = 'clients', $params = array(), $expiration = 7200, $request_type = 'get')
{
    $cacheKey = 'idx_' .$method. '_cache';
    if (($data = get_transient($cacheKey))) {
        return $data;
    }

    $headers = array(
        'Content-Type' => 'application/x-www-form-urlencoded',
        'accesskey' => get_option('idx_broker_apikey'),
        'outputtype' => 'json',
        'apiversion' => $apiversion,
        'pluginversion' => IDX_WP_PLUGIN_VERSION
    );

    $params = array_merge(array('timeout' => 120, 'sslverify' => false, 'headers' => $headers), $params);

    $url = IDX_API_URL . '/' . $level. '/'. $method;

    if($request_type === 'get'){
        $response = wp_remote_get($url, $params);
    } else {
        $response = wp_safe_remote_post($url, $params);
    }
    $response = (array)$response;

    extract(apiResponse($response)); // get code and error message if any, assigned to vars $code and $error
    if (isset($error) && $error !== false) {
        if ($code == 401) {
            delete_transient($cacheKey);
        }
        return new WP_Error("idx_api_error", __("Error {$code}: $error"));
    } else {
        $data = (array) json_decode((string)$response['body']);
        set_transient($cacheKey, $data, $expiration);
        return $data;
    }
}


function system_results_url() {

        $links = idx_api_get_systemlinks();

        if ( !$links ) {
            return false;
        }

        foreach ($links as $link) {
            if ( $link->systemresults ) {
                $results_url = $link->url;
            }
        }

        // What if or can they have more than one system results page?
        if ( isset($results_url) ) {
            return $results_url;
        }

        return false;
    }

    /**
     * Returns the url of the link
     *
     * @param string $name name of the link to return the url of
     * @return bool|string
     */
    function system_link_url($name) {

        $links = idx_api_get_systemlinks();

        if ( !$links ) {
            return false;
        }

        foreach ($links as $link) {
            if ( $name == $link->name ) {
                return $link->url;
            }
        }

        return false;
    }

    /**
     * Returns the url of the first system link found with
     * a category of "details"
     *
     * @return bool|string link url if found else false
     */
    function details_url() {

        $links = idx_api_get_systemlinks();

        if ( !$links ) {
            return false;
        }

        foreach ($links as $link) {
            if ( 'details' == $link->category ) {
                return $link->url;
            }
        }

        return false;
    }

    /**
     * Returns an array of system link urls
     *
     * @return array
     */
    function all_system_link_urls() {

        $links = idx_api_get_systemlinks();

        if ( !$links ) {
            return array();
        }

        $system_link_urls = array();

        foreach ($links as $link) {
            $system_link_urls[] = $link->url;
        }

        return $system_link_urls;
    }

    /**
     * Returns an array of system link names
     *
     * @return array
     */
    function all_system_link_names() {

        $links = idx_api_get_systemlinks();

        if ( !$links ) {
            return array();
        }

        $system_link_names = array();

        foreach ($links as $link) {
            $system_link_names[] = $link->name;
        }

        return $system_link_names;
    }

    function all_saved_link_urls(){

         $links = idx_api_get_savedlinks();

        if ( !$links ) {
            return array();
        }

        $system_link_urls = array();

        foreach ($links as $link) {
            $system_link_urls[] = $link->url;
        }

        return $system_link_urls;
    }

     function all_saved_link_names() {

        $links = idx_api_get_savedlinks();

        if ( !$links ) {
            return array();
        }

        $system_link_names = array();

        foreach ($links as $link) {
            $system_link_names[] = $link->name;
        }

        return $system_link_names;
    }