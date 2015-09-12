<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

declare(ticks=1);
function ticker()
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    var_dump($bt);
}
register_tick_function('ticker');

global $CONFIG;

date_default_timezone_set('UTC');

function parse_ini_file_advanced($path)
{
    $p_ini = parse_ini_file($path, true);
    $CONFIG = array();
    foreach ($p_ini as $namespace => $properties)
    {
        $namespace_split = explode(':', $namespace);
        if (count($namespace_split) > 1)
        {
            list($name, $extends) = $namespace_split;
        }
        else
        {
            $name = $namespace;
            $extends = '';
        }
        $name = trim($name);
        $extends = trim($extends);
        // create namespace if necessary
        if (!isset($CONFIG[$name]))
        {
            $CONFIG[$name] = array();
        }
        // inherit base namespace
        if (isset($p_ini[$extends]))
        {
            foreach ($p_ini[$extends] as $prop => $val)
            {
                $CONFIG[$name][$prop] = $val;
            }
        }
        elseif (isset($CONFIG[$extends]))
        {
            foreach ($CONFIG[$extends] as $prop => $val)
            {
                $CONFIG[$name][$prop] = $val;
            }
        }
        // overwrite / set current namespace values
        foreach ($properties as $prop => $val)
            $CONFIG[$name][$prop] = $val;
    }
    return $CONFIG;
}
if (!file_exists('./config.ini'))
{
    trigger_error("No configuration could be found", E_USER_ERROR);
}

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'cli';
$loaded_config = parse_ini_file_advanced('./config.ini', true);

if (!array_key_exists($host, $loaded_config))
{
    trigger_error("No configuration exists for host '$host'", E_USER_ERROR);
}
else
{
    $CONFIG = $loaded_config[$host];
}

function autoload_static($class_name)
{
    global $autoload_cache;
    global $CONFIG;

    if (is_null($autoload_cache))
    {
        if (file_exists($CONFIG['autoload_cache']))
        {
            $autoload_cache = (array) json_decode(file_get_contents($CONFIG['autoload_cache']));
        }
        else
        {
            $autoload_cache = array();
        }
    }

    if (array_key_exists($class_name, $autoload_cache))
    {
        require_once($autoload_cache[$class_name]);
    }
}
spl_autoload_register('autoload_static');

/**
 * Autogenerates missing Exception\* classes to make try-catch easier.
 * 
 * @param type $class_name
 */
function autoload_autogen_exception($class_name)
{
    $chunked = explode('\\', $class_name);
    if(count($chunked) <= 1)
    {
        return;
    }
    if(array_shift($chunked) == 'Exception') //Needs to be in the Exception namespace.
    {
        // Yes, an eval follows. One is aware of the risks of using an eval, but $class_name is not user-defined.
        // If somehow user input gets to $class_name, we have bigger problems than an eval.
        // 
        // The following code generates a dumb subclass of \\Exception in the Exception namespace so that it can be
        // thrown, and then caught in a saner, more logical manner without having to have stupid amounts of source 
        // files dedicated to just exceptions.
        eval('namespace Exception { class ' . implode('\\', $chunked) . ' extends \\Exception { } }');
    }
}
spl_autoload_register('autoload_autogen_exception');

if(isset($CONFIG['ini']) && is_array($CONFIG['ini']))
{
    foreach ($CONFIG['ini'] as $key => $value)
    {
        ini_set($key, $value);
    }
}

function error_handler ($errno , $errstr, $errfile, $errline, $errcontext)
{
    die('ERROR: "' . $errstr . '" IN ' . $errfile . ' ln. ' . $errline);
    if(error_reporting() == 0)
    {
        return;
    }
    
    $type = 'UNKNOWN'; //'UNKNOWN','NOTICE','WARNING','ERROR','EXCEPTION','PARSE','STRICT','RECOVERABLE_ERROR','DEPRECATED'
    $level = 'PHP'; //'USER','PHP','CORE','COMPILE'
    $recoverable = false;
    
    switch($errno)
    {
        case E_COMPILE_ERROR:
        {
            $type = 'ERROR';
            $level = 'COMPILE';
        }
        break;
        case E_COMPILE_WARNING:
        {
            $type = 'WARNING';
            $level = 'COMPILE';
            $recoverable = true;
        }
        break;
        case E_CORE_ERROR:
        {
            $type = 'WARNING';
            $level = 'CORE';
        }
        break;
        case E_CORE_WARNING:
        {
            $type = 'WARNING';
            $level = 'CORE';
            $recoverable = true;
        }
        break;
        case E_ERROR:
        {
            $type = 'ERROR';
            $level = 'PHP';
        }
        break;
        case E_NOTICE:
        {
            $type = 'NOTICE';
            $level = 'PHP';
            $recoverable = true;
        }
        break;
        case E_WARNING:
        {
            $type = 'WARNING';
            $level = 'PHP';
            $recoverable = true;
        }
        break;
        case E_ERROR:
        {
            $type = 'ERROR';
            $level = 'PHP';
        }
        break;
        case E_PARSE:
        {
            $type = 'PARSE';
            $level = 'PHP';
        }
        break;
        case E_RECOVERABLE_ERROR:
        {
            $type = 'ERROR';
            $level = 'PHP';
            $recoverable = true;
        }
        break;
        case E_DEPRECATED:
        {
            $type = 'DEPRECATED';
            $level = 'PHP';
            $recoverable = true;
        }
        break;
        case E_STRICT:
        {
            $type = 'STRICT';
            $level = 'PHP';
        }
        break;
        case E_USER_DEPRECATED:
        {
            $type = 'DEPRECATED';
            $level = 'USER';
            $recoverable = true;
        }
        break;
        case E_USER_ERROR:
        {
            $type = 'ERROR';
            $level = 'USER';
        }
        break;
        case E_USER_NOTICE:
        {
            $type = 'NOTICE';
            $level = 'USER';
            $recoverable = true;
        }
        break;
        case E_USER_WARNING:
        {
            $type = 'WARNING';
            $level = 'USER';
            $recoverable = true;
        }
        break;
    }
    try
    {
        $error = new Core\Model\Error();
        $error->hash = sha1($errno.$errstr.$errfile.$errline);
        $error->timestamp = (new DateTime())->format('Y-m-d h:i:s');
        $error->type = $type;
        $error->level = $level;
        $error->message = $errstr;
        $error->line = $errline;
        $error->file = $errfile;
        $error->variables = @var_export($errcontext, true);
        $error->stack = @var_export(debug_backtrace(), true);
        $error->save();
    }
    catch(PDOException $e)
    {
        // Exception logging the error. Nothing we can do, so just continue on.
    }
    
    if(!$recoverable)
    {
        exit();
    }
}

set_error_handler('error_handler', E_ALL);
register_shutdown_function('fatal_handler');

function fatal_handler()
{
    $error = error_get_last();

    if( $error !== NULL)
    {
        $errno   = $error['type'];
        $errfile = $error['file'];
        $errline = $error['line'];
        $errstr  = $error['message'];

        error_handler($errno, $errstr, $errfile, $errline, []);
    }
}