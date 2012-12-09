<?php

namespace api;
/* class */
class reusable_instance_keys extends reusable_keys {
    /**
     *
     * @var api\factory_instance 
     */
    protected static $factory = null;
    
    public function set_factory( $factory = 'api\factory_instance' ) {
        if(static::$factory === null || $factory !== static::$factory ) {    
            static::$factory = $factory::get_instance();
        }        
    }    
    /**
      *   @return api\reusable_instance_keys
      */
    public static function get_instance(){
        return new static;
    }
}

