<?php

namespace api;
/* class */
class factory_instance extends factory {
    
    /**
     * @see api\factory::no_config()
     * @param string $class_name
     * @return class $class_name returns instance of $class_name
     */
    public function no_config($class_name){
        static::$loader->load($class_name);
        return $class_name::get_instance();
    }
    
    /**
     * @see api\factory::with_config()
     * @param string $class_name
     * @param mixed $config 
     * @return class $class_name returns instance of $class_name 
     */
    public function with_config($class_name, $config){
        static::$loader->load($class_name);
        return $class_name::get_instance($config);
    }
    
}


