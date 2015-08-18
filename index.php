<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

// Enable output buffering during init
ob_start();
ini_set('display_errors', true);

require_once 'modules/core/core.php';

$path = explode('/', substr(filter_input(INPUT_SERVER, 'REQUEST_URI'), 1));

$action = array_pop($path);

$controller_class = 'Page\\';

if (isset($CONFIG['page_prefix']))
{
    $controller_class = $CONFIG['page_prefix'] . '\\' . $controller_class;
}

$exists;
do
{
    $exists = class_exists($controller_class . str_replace(' ', '\\', ucwords(implode(' ', $path))), true);
    if(!$exists)
    {
        if(strlen($action) > 0)
        {
            $action = array_pop($path) . '/' . $action;
        }
        else
        {
            $action = array_pop($path);
        }
    }
}
while(!$exists && count($path) > 0);
unset($exists);

$action = explode('?', $action)[0];

$controller_class .= str_replace(' ', '\\', ucwords(implode(' ', $path)));

if (count($path) === 0)
{
   $controller_class .= 'Home';
}
if ($action == '')
{
    $action = 'index';
}
$action = ltrim($action, '/');

$controller;

try
{
    if (!class_exists($controller_class, true))
    {
        throw new Core\Exception\HttpException(404);
    }
    $controller = new $controller_class();
    
    if (is_callable(array($controller_class, $action . 'Action')))
    {
        $formatted = $action . 'Action';
        $controller->$formatted($action);
    }
    else
    {
        $controller->defaultAction($action);
    }
}
catch (Exception $e)
{
    if (!is_a($e, 'Core\\Exception\\HttpException'))
    {
        $e = new Core\Exception\HttpException(500, null, $e);
    }

    $error_page = $CONFIG['page_prefix'] . '\\Page\\Error';
    $controller = new $error_page();
    $controller->errorAction($e);
    
    trigger_error($e->getMessage());
}

// If we have gotten this far, everything is cool and we can clean the buffer
ob_clean();

$controller->render();
