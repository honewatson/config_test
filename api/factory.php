<?php

namespace api;
/* class */
class factory {
    
    /**
     *
     * @var api\loader
     */
    protected static $loader = null;
    /**
     *
     * @return api\loader
     */
    public function get_loader(){
        return static::$loader;
    }
    /**
     *
     * @param class $loader usually api\loader
     */
    public function set_loader( $loader ) {
        if(static::$loader === null || $loader !== static::$loader )    
            static::$loader = $loader;
    }

    /**
     * Returns an instance of the $class_name.
     * This method is suitable when there is no variables required in the constructor of the class.
     * @param string $class_name the full namespace eg api\db\model
     * @return $class_name new instance of $class_name 
     */
    public function no_config($class_name){
        static::$loader->load($class_name);
        return new $class_name;
    }
    /**
     * Returns an instance of the $class_name.
     * This method is suitable when there is one variable in the constructor of the class.
     * @param string $class_name the full namespace eg api\db\model
     * @param mixed $config a config which is dependent upon the class.  The config will be a string, array, or class.
     * @return $class_name new instance of $class_name
     */
    public function with_config($class_name, $config){
        static::$loader->load($class_name);
        return new $class_name($config);
    }
    /**
     * Returns an instance of the $class_name.
     * This method is suitable when a call back $lambda, a factory function, is required to set up the class.
     * @param string $class_name the full namespace eg api\db\model
     * @param mixed $config a config which is dependent upon the class.  The config will be a string, array, or class.
     * @param closure|string $lambda is a function that will be used as a primitive factory for the class
     * @return $class_name new instance of $class_name 
     */
    public function with_config_lambda($class_name, $config, $lambda){
        static::$loader->load($class_name);
        return $lambda($class_name, $config);
    }
    
    /**
     * Returns an instance of the $class_name.
     * This method is suitable when there is one variable in the constructor of the class and the variable is an array.
     * @param string $class_name the full namespace eg api\db\model
     * @param array|mixed $config The config could be a  array, string, or class.  It will try and force config to array.
     * @return $class_name new instance of $class_name 
     */
    public function with_config_array($class_name, $config ){
        return static::with_config($class_name, (array)$config );
    }
    /**
     * Returns an instance of the $class_name.
     * This method is suitable when there is one variable in the constructor of the class and the variable is an object.
     * @param string $class_name the full namespace eg api\db\model
     * @param object|mixed $config The config could be a  array, string, or class.  It will try and force config to object.
     * @return $class_name new instance of $class_name 
     */
    public function with_config_object($class_name, $config ){
        return static::with_config($class_name, (object)$config );
    }

    /**
     * Returns an instance of the $class_name.
     * The args for this type of class instantiation are set with ::set($args) method
     * call_user_func_array is relatively slow.
     * @param string $class_name the full namespace eg api\db\model
     * @param mixed $args
     * @return $class_name new instance of $class_name
     */
    public function with_config_call($class_name, $args) {
        static::$loader->load($class_name);
        $class = new $class_name;
        call_user_func_array(array($class, "set"), $args);
        return $class;
    }
    /**
     * Returns an instance of the $class_name.
     * This method uses the ReflectionClass to instantiate a class.  
     * This method is suitable when there is variable arguments in the __construct method of the class.  
     * You can put the __construct args into an array in the specific order of the $class_name __construct args. This method will instantiate the class.
     * 
     * @param string $class_name the full namespace eg api\db\model
     * @param array $args
     * @return $class_name new instance of $class_name
     */
    public function with_config_reflect($class_name, $args) {
        static::$loader->load($class_name);
        $reflection = new \ReflectionClass($class_name);
        return $reflection->newInstanceArgs($args);
    }


}
