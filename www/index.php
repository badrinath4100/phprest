<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require '../vendor/autoload.php';
$app = new System\Rest();

$app->service("get:/test", [], [], "Application/Demo:test");

$app->run();

