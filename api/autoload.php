<?php

namespace api;
/* class */
class autoload {
    
    /**
     *
     * @var api\base_path::$base_path
     */
    protected $_base_path;
    
    
    /**
     * handles autoloading
     *
     * @param string $class_name
     * @return void
     */
    public function autoloader($class_name)
    {

        $path = str_replace('\\', '/', $class_name) . '.php';

        include $this->_base_path.$path;

        
    }



    /**
     * initializes autoloader
     *
     * @return void
     */
    public function autoload($base_path = 'base_path')
    {
        $this->_base_path = $base_path::$base_path;

        spl_autoload_register(array($this, 'autoloader'));
        
        return $this;
    }

    protected function __construct(){}
    
    /**
      *   @return api\autoload
      */
    public static function get_instance(){
        return new static;
    }

}