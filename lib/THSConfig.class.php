<?php

/**
 * Description of THSConfig
 *
 * @author orlando
 */
class THSConfig
{
    protected static $options = array();
    
    public static function load($ini)
    {
        $config = parse_ini_file($ini);
        
        foreach ($config as $option => $value){
            self::$options[$option] = $value;
        }
    }
    
    public static function get($option)
    {
        return self::$options[$option];
    }
    
    public static function set($option, $value)
    {
        self::$options[$option] = $value;
    }
}
