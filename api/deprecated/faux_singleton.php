<?php

namespace api;
/* class */
class faux_singleton {
    protected function __construct(){}
    
    /**
      *   @return get_class()
      */
    public static function get_instance(){
        return new static;
    }
}
