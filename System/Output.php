<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

class Output {
    
    private static $statuscode = 200;
    private static $response_headers = [];


    /**
     * Constructor
     */
    private function __construct() {
        //Private constructor;
    }
    
    /**
     * 
     * @param string $key
     * @param string $val
     */
    public static function setResponseHeader($key,$val){
        self::$response_headers[$key] = $val;
    }
    
    /**
     * Send final response
     * @param int $status
     * @param array $data
     */
    public static function response($status,array $data){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, Content-Type, AuthToken, ApiKey, AccessToken");
        header("Content-Type: application/json");
        
        foreach (self::$response_headers as $key => $value) {
            header("$key: $value");
        }
        http_response_code($status);
        $data['process_time'] = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        
        echo json_encode($data);
        exit;
    }
    
    /**
     * Creates response object
     * @param boolean $error
     * @param int $code
     * @param Mixed $data
     * @return Array
     */
    public static function response_object($error,$code,$data){
        return [
            'error' => $error,
            'response_code' => $code,
            'data' => $data
        ];
    }
    
}