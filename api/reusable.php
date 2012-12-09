<?php

namespace api;
/* class */
class reusable {

    /**
     * Object storage
     * @var array
     */
    protected static $instances = array();
    
    /**
     *
     * @var api\factory
     */
    protected static $factory = null;
    
    /**
     *
     * @return api\factory
     */
    public function get_factory(){
        return static::$factory;
    }
    
    /**
     *
     * @param class $instance 
     */
    public function set_instance($instance){
        static::$instances[$class_name] = $instance;
    }
    
    /**
     *
     * @param api\factory $factory 
     */
    public function set_factory( $factory ) {
        if(static::$factory === null || $factory !== static::$factory ) {    
            static::$factory = $factory;
        }        
    }
    /**
     * <h2>Returns an instance of the $class_name.</h2>
     * <p>If the $class_name has already been instantiated it will return a stored version from static:$instances;</p>
     * <p>The key is the $class_name string.</p>
     * <p>This method is suitable when there is one variable in the constructor of the class.</p>
     * @param string $class_name the full namespace eg api\db\model
     * @param mixed $config a config which is dependent upon the class.  The config will be a string, array, or class.
     * @return $class_name new instance of $class_name
     */
    public function with_just_config($class_name, $config, $with_config = 'with_config'){
        if(!isset(static::$instances[$class_name]))
            static::$instances[$class_name] = static::$factory->$with_config($class_name, $config);
        return static::$instances[$class_name];
    }
    /**
     * Returns an instance of the $class_name.  
     * If the $class_name has already been instantiated it will return a stored version from static:$instances;
     * The key is the $class_name string.
     * This method is suitable when there is no variables required in the constructor of the class.
     * @param string $class_name the full namespace eg api\db\model
     * @return $class_name new instance of $class_name 
     */
    public function no_config($class_name){
        if(!isset(static::$instances[$class_name]))
            static::$instances[$class_name] = static::$factory->no_config($class_name);
        return static::$instances[$class_name];
    }
    /**
     * Returns an instance of the $class_name.  
     * If the $class_name has already been instantiated it will return a stored version from static:$instances;
     * The key is the $class_name string.
     * This method is suitable when a call back $lambda, a factory function, is required to set up the class.
     * @param string $class_name the full namespace eg api\db\model
     * @param mixed $config a config which is dependent upon the class.  The config will be a string, array, or class.
     * @param closure|string $lambda is a function that will be used as a primitive factory for the class
     * @return $class_name new instance of $class_name 
     */
    public function with_config_lambda($class_name, $config, $lambda){
        if(!isset(static::$instances[$class_name]))
            static::$instances[$class_name] = static::$factory->with_config_lambda($class_name, $config, $lambda);
        return static::$instances[$class_name];
    }	
    /**
     * Returns an instance of the $class_name.  
     * If the $class_name has already been instantiated it will return a stored version from static:$instances;
     * The key is the $class_name string.
     * This method is suitable when there is one variable in the constructor of the class and the variable is an array.
     * @param string $class_name the full namespace eg api\db\model
     * @param array|mixed $config The config could be a  array, string, or class.  It will try and force config to array.
     * @return $class_name new instance of $class_name 
     */
    public function with_config_array($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_array');

    }
    /**
     * Returns an instance of the $class_name.  
     * If the $class_name has already been instantiated it will return a stored version from static:$instances;
     * The key is the $class_name string.
     * This method is suitable when there is one variable in the constructor of the class and the variable is an object.
     * @param string $class_name the full namespace eg api\db\model
     * @param object|mixed $config The config could be a  array, string, or class.  It will try and force config to object.
     * @return $class_name new instance of $class_name 
     */
    public function with_config_object($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_object');
    }
    /**
     * Returns an instance of the $class_name.  
     * If the $class_name has already been instantiated it will return a stored version from static:$instances;
     * The key is the $class_name string.
     * This method uses the ReflectionClass to instantiate a class.  
     * This method is suitable when there is multiple arguments in the __construct method of the class.  
     * You can put the __construct args into an array in the specific order of the $class_name __construct args. This method will instantiate the class.
     * 
     * @param string $class_name the full namespace eg api\db\model
     * @param array $args
     * @return $class_name new instance of $class_name
     */
    public function with_config_reflect($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_reflect');
    }
    /**
     * Returns an instance of the $class_name.  
     * If the $class_name has already been instantiated it will return a stored version from static:$instances;
     * The key is the $class_name string.
     * The args for this type of class instantiation are set with ::set($args) method
     * This method is suitable when there is multiple arguments that neede to be injected into the class using a set method. 
     * call_user_func_array is relatively slow.
     * @param string $class_name the full namespace eg api\db\model
     * @param mixed $args
     * @return $class_name new instance of $class_name
     */
    public function with_config_call($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_call');
    }	


}