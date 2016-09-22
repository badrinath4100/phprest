<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

class Session {
    //session data
    private static $session_data = [];
    
    /**
     * Constructor
     */
    private function __construct() {
        // Constructor;
    }
    
    /**
     * Set session data
     * @param string $key
     * @param Mixed $value
     */
    public static function set($key,$value){
        self::$session_data[$key] = $value;
    }
    
    /**
     * Get Session data
     * @param string $key
     * @return Array
     */
    public static function get($key = false){
        if($key === false){
            return self::$session_data;
        }
        return array_key_exists($key, self::$session_data) ? self::$session_data[$key] : false;
    }
    
}