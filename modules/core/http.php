<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core;

class Http
{
    static public function POST($url, $post_data = '', $headers = null)
    {
        global $CONFIG;
        
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,$url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, $CONFIG['user_agent']);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_data);
        if(is_array($headers))
        {
            curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        }
        $data = curl_exec($curl_handle);
        curl_close($curl_handle);
        
        return $data;
    }
    
    static public function GET($url, $headers = null)
    {
        global $CONFIG;
        
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,$url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, $CONFIG['user_agent']);
        if(is_array($headers))
        {
            curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        }
        $data = curl_exec($curl_handle);
        curl_close($curl_handle);
        
        return $data;
    }
    
    static public function JGET($url, $headers = null)
    {
        return json_decode(static::GET($url, $headers));
    }
    
    static public function JPOST($url, $post_data = '', $headers = null)
    {
        if(is_array($post_data))
        {
            $post_data = json_encode($post_data);
        }
        return json_decode(static::POST($url, $post_data, $headers));
    }
}