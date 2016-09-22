<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

use Config\Config;

class Input {

    protected static $filtered_input = [];

    /**
     * Constructor
     */
    private function __construct() {
        //constructor;
    }

    /**
     * Get URL Pattern for current request
     * @return string Returns Pattern
     */
    public static function pattern() {
        //split query
        $request_url = explode("?", $_SERVER['REQUEST_URI'])[0];
        if(Config::BASE_URL !== "/"){
            return '/' . str_replace(Config::BASE_URL, '', $request_url);
        }
        return $request_url;
    }

    /**
     * Get Request Method
     * @param type $upper
     * @return type
     */
    public static function method($upper = FALSE) {
        return ($upper) ? strtoupper($_SERVER['REQUEST_METHOD']) : strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Check if request is on HTTPS
     * @return type
     */
    public static function isHTTPS() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
    }

    /**
     * Get User agent
     * @return string
     */
    public static function user_agent() {
        $user_agent = self::xss_clean($_SERVER ['HTTP_USER_AGENT']);
        return $user_agent != "" ? $user_agent : "Unnown user agent";
    }

    /**
     * Get IP address
     * @return string
     */
    public static function ip_address() {
        // check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if (validate_ip($ip))
                        return $ip;
                }
            } else {
                if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        }
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }
        // return unreliable ip since all else failed
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * XSS Clean
     * @param string $data
     * @return string
     */
    public static function xss_clean($data) {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }

    /**
     * Get raw json data
     * @return type
     */
    private static function getRawParams() {
        $string = file_get_contents('php://input');
        return (array) json_decode($string);
    }

    /**
     * Get query params
     * @param string $index
     */
    public static function get($index = FALSE) {
        if ($index === false) {
            return $_GET;
        }
        return array_key_exists($index, $_GET) ? $_GET[$index] : false;
    }

    /**
     * Get Post Data
     * @param string $index
     * @return Mixed
     */
    public static function post($index = false) {
        $raw_data = self::getRawParams();
        if ($index === false) {
            return $raw_data;
        }
        return array_key_exists($index, $raw_data) ? $raw_data[$index] : false;
    }

    /**
     * Get Put Data
     * @return Mixed
     */
    public static function put($index = false) {
        $raw_data = self::getRawParams();
        if ($index === false) {
            return $raw_data;
        }
        return array_key_exists($index, $raw_data) ? $raw_data[$index] : false;
    }

    /**
     * Get parameters
     * @param string $index
     * @return Mixed
     */
    public static function delete($index = false) {
        $raw_data = self::getRawParams();
        if ($index === false) {
            return $raw_data;
        }
        return array_key_exists($index, $raw_data) ? $raw_data[$index] : false;
    }

    /**
     * get Filtered params
     * @param string $index
     * @return Mixed
     */
    public static function param($index = false) {
        $filtered_data = self::$filtered_input;
        if ($index === false) {
            return $filtered_data;
        }
        return array_key_exists($index, $filtered_data) ? $filtered_data[$index] : false;
    }

    /**
     * Set Filtered data
     * @param array $data
     */
    public static function setFiltered(array $data) {
        self::$filtered_input = $data;
    }

    /**
     * Get files 
     * @param string $index
     */
    public static function file($index = false) {
        if ($index === false) {
            return $_FILES;
        }
        return array_key_exists($index, $_FILES) ? $_FILES[$index] : false;
    }
    
    
    /**
     * Get Request Headers
     * @param string $index
     * @return type
     */
    public static function header($index = false) {
        $headers = self::getAllHeaders();
        if ($index === false) {
            return $headers;
        }
        return array_key_exists(strtolower($index), $headers) ? $headers[strtolower($index)] : false;
    }
    
    /**
     * Fetch all request headers
     * @return type
     */
    private static function getAllHeaders() {
        if (!function_exists('apache_request_headers')) {
            function apache_request_headers() {
                foreach ($_SERVER as $key => $value) {
                    if (substr($key, 0, 5) == "HTTP_") {
                        $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                        $out[$key] = $value;
                    } else {
                        $out[$key] = $value;
                    }
                }
                return $out;
            }
        }        
        $headers = [];
        foreach(apache_request_headers() as $key => $val){
            $headers[strtolower($key)] = $val;
        }
        return $headers;
    }

}
