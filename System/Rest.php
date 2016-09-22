<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

use System\Output;
use Config\Config;
use System\Validation;

class Rest {

    private $routes = [];

    /**
     * Constructor
     */
    public function __construct() {
        //Define Base App Path
        define('APP_PATH', "../");
        //Set Exception Handler
        $this->setExceptionHandler();
        //set Error Handler
        $this->setErrorHandler();

        //Chech if SSL Inforced
        if (Config::INFORCE_SSL && !Input::isHTTPS()) {
            $output = Output::response_object(true, 0, 'Allowed over HTTPS');
            Output::response(405, $output);
        }
    }

    /**
     * Set Custom error Handler
     */
    private function setErrorHandler() {
        set_error_handler(function($errtype, $errstr, $errfile, $errline) {
            $error = ['error' => [
                    'err_type' => $errtype,
                    'error_string' => $errstr,
                    'file' => $errfile,
                    'line' => $errline
            ]];
            $output = Output::response_object(true, 0, $error);
            Output::response(500, $output);
        }, E_ALL);
    }

    /**
     * Set Custom exception handler
     */
    private function setExceptionHandler() {
        set_exception_handler(function($ex) {
            if (Config::DEBUG_MODE) {
                $exception = ['exception' => [
                        'message' => $ex->getMessage(),
                        'code' => $ex->getCode(),
                        'file' => $ex->getFile(),
                        'line' => $ex->getLine(),
                        'trace' => $ex->getTraceAsString()
                ]];
                $output = Output::response_object(true, 0, $exception);
                Output::response(500, $output);
            } else {
                $output = Output::response_object(true, 0, 'Server error');
                Output::response(500, $output);
            }
        });
    }

    /**
     * Map available routes and return mapped route
     * @return string Mapped route
     */
    private function match() {
        $pattern = Input::pattern();
        $method = Input::method();
        $match = strtolower($method) . ":" . $pattern;
        //check if method exists
        if (array_key_exists($match, $this->routes)) {
            return $this->routes[$match];
        }
        return false;
    }

    /**
     * Add new service
     * @param string $pattern
     * @param string $action
     */
    public function service($pattern, array $auth, array $validation, $action) {
        $method = explode(":", $pattern);
        $allowed_methods = ['get', 'post', 'put', 'delete'];

        if (!in_array($method[0], $allowed_methods)) {
            throw new \Exception("HTTP Verb [$method[0]] not allowed", 500);
        }

        $this->routes[strtolower($pattern)] = [
            'auth' => $auth,
            'validation' => $validation,
            'action' => $action
        ];
    }

    /**
     * Process request
     * @throws \Exception
     */
    public function run() {
        //check if method exist
        $matched = $this->match();
        if ($matched === false) {
            $output = Output::response_object(true, 404, 'Method not found');
            Output::response(200, $output);
        }
        $this->execute($matched);
    }

    /**
     * Execute matched service
     * @param array $route
     */
    private function execute(array $route) {
        $this->validateAuth($route['auth']);

        $validators = [];
        $filters = [];
        foreach ($route['validation'] as $key => $val) {
            $value = explode(":", $val);
            $validators[$key] = $value[0];
            if (isset($value[1])) {
                $filters[$key] = $value[1];
            }
        }

        $this->validateInput($validators, $filters);
        
        $class_method = explode(":", $route['action']);
        $class_name = str_replace("/", "\\", $class_method[0]);
        $method_name = $class_method[1];
        
        if(!class_exists($class_name)){
            throw new \Exception("Class [$class_method[0]] not found", 500);
        }
        
        if(!method_exists($class_name, $method_name)){
            throw new \Exception("Method [$method_name] not found", 500);
        }
        
        $instance = new $class_name;
        $response_data = $instance->$method_name();
        
        Output::response(200, $response_data);
    }

    /**
     * Validate Auth
     * @param array $auth
     */
    private function validateAuth(array $auth) {

        foreach ($auth as $method) {
            $authClass = str_replace("/", "\\", $method);
            if (!class_exists($authClass)) {
                $output = Output::response_object(true, 0, "Auth class [$method] not found");
                Output::response(500, $output);
            }
            if (!method_exists($authClass, 'validate')) {
                $output = Output::response_object(true, 0, "Auth Class [$method] should implement AuthInterface");
                Output::response(500, $output);
            }

            $authInstance = new $authClass;
            $valid = $authInstance->validate();

            if (!is_array($valid) || !isset($valid['error']) || !is_bool($valid['error'])) {
                $output = Output::response_object(true, 0, "Invalid response from auth class [$method]");
                Output::response(500, $output);
            }

            if ($valid['error']) {
                Output::response(200, $valid);
            }
        }
    }

    /**
     * Validate & Filter Input 
     * @param array $validators
     * @param array $filters
     */
    private function validateInput(array $validators, array $filters) {
        $method = Input::method();
        $validation = new Validation();

        $data = $validation->sanitize(Input::$method());
        $validation->validation_rules($validators);
        $validation->filter_rules($filters);
        $validated_data = $validation->run($data);

        if ($validated_data === false) {
            $errors = $validation->get_errors_array();
            $output = Output::response_object(true, 0, $errors);
            Output::response(200, $output);
        }

        Input::setFiltered($validated_data);
    }

}
