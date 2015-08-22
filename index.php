<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

// Enable output buffering during init
header('X-Powered-By: MontagneMVC');

ob_start();
ini_set('display_errors', true);

require_once 'modules/core/core.php';

$path = explode('/', substr(filter_input(INPUT_SERVER, 'REQUEST_URI'), 1));

$action = array_pop($path);

try
{
    if(is_null($path[0]) || strlen($path[0]) <= 0)
    {
        $path[0] = 'home';
    }
    $default = $CONFIG['default_page'];
    $controller = $default::route($path, $action, new stdClass());
}
catch (Exception $e)
{
    if (!is_a($e, 'Core\\Exception\\HttpException'))
    {
        $e = new Core\Exception\HttpException(500, null, $e);
    }

    $error_page = $CONFIG['error_page'];
    $controller = new $error_page();
    $controller->errorAction($e);
    
    trigger_error($e->getMessage());
}

// If we have gotten this far, everything is cool and we can clean the buffer
ob_clean();

$controller->render();
