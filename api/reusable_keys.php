<?php

namespace api;
/* class */
class reusable_keys extends reusable {
    /**
     * Object storage
     * @var array
     */    
    protected static $instances = array();
    
    public function with_just_config($class_name, $config, $with_config = 'with_config', $key = null){
        if($key === null)    
            $key = $this->get_key($config);
        if(!isset(static::$instances[$key]))
            static::$instances[$key] = static::$factory->$with_config($class_name, $config);
        return static::$instances[$key];
    }
    
    public function get_key($config){
        return implode("", array_values((array)$config));
    }
    
    public function with_config_lambda($class_name, $config, $lambda, $key = null){
        if($key === null)    
            $key = $this->get_key($config);
        if(!isset(static::$instances[$key]))
            static::$instances[$key] = static::$factory->with_config_lambda($class_name, $config, $lambda);
        return static::$instances[$key];
    }
    
}

