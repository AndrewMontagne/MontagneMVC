#!/usr/bin/php
<?php

/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

$files = array();
$classmap = array();

exec('grep -ER "^(\s+)?(abstract )?class (\S+)" .',  $files);

foreach($files as $file)
{
	$file = strtok($file, ':');
    $tokens = token_get_all(file_get_contents($file));

    $current_token = null;
    $current_string = '';
    $namespace = '';

    foreach ($tokens as $token_data) 
    {
    	if(!is_array($token_data))
    	{
    		continue;
    	}

    	list($token, $value) = $token_data;

    	switch($token)
    	{
    		case T_NAMESPACE:
    			$current_token = T_NAMESPACE;
    			break;
    		case T_CLASS:
    		case T_INTERFACE:
    		case T_TRAIT:
    			$current_token = T_CLASS;
    			break;
    		case T_STRING:
    			$current_string .= $value;
    			break;
    		case T_NS_SEPARATOR:
    			$current_string .= '\\';
    			break;
    		case T_WHITESPACE:
    			if($current_string == '')
    			{
    				continue;
    			}
    			switch ($current_token) 
    			{
    				case T_NAMESPACE:
    					$namespace = $current_string;
    					break;
    				case T_CLASS:
    					$classmap[$namespace . '\\' . $current_string] = $file;
    					break;
    			}
    			$current_token = null;
    			$current_string = '';
    			break;
    	}
    }
}

file_put_contents('autoload.json', json_encode($classmap, JSON_PRETTY_PRINT));