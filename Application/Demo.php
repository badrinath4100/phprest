<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application;

use System\Input;
use System\Output;
use System\Database;

class Demo {

    /**
     * test method which
     * @return array
     */
    public function test() {

        $response = "Hello world";

        return Output::response_object(false, 200, response);
    }

}
