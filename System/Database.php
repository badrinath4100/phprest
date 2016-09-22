<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

use Config\Config;

class Database {
    
    /**
     * Class instance
     * @var type 
     */
    public static $class_instance = null;
    
    /**
     * Private constructor
     */
    private function __construct() {
        //Private constructor;
    }
    
    /**
     * Get Instance 
     */
    public static function getInstance() {
        if(self::$class_instance == null){
            self::createInstance();
        }
        return self::$class_instance;
    }
    
    /**
     * cREATE INSTANCE
     * @throws \Exception
     */
    private static function createInstance() {
        try {
            $conn = new \PDO(Config::DB_CONNECTION_STRING, Config::DB_USER, Config::DB_PASS);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            self::$class_instance = $conn;
        } catch (\PDOException $e) {
            throw new \Exception($e);
        }
        self::$class_instance = $conn;
    }

}
