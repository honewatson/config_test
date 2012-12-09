<?php

namespace api;
/* class */
class loader {
    
    protected static $parse_ini_files = array();
    /**
     *
     * @var api\base_path::$base_path
     */
    protected static $base_path = null;
    
    /**
     *
     * @return api\loader::$base_path
     */
    public function get_base_path(){
        return static::$base_path;
    }
    
    /**
     *
     * @param api\base_path $base_path 
     */
    public function set_base_path($base_path = 'api\base_path'){
        if(static::$base_path === null || $base_path !== static::$base_path ) {    
            static::$base_path = $base_path::$base_path;
        }
    }
    /**
     * For loading classes.
     * $class_name should be in full namespace format eg api\db\model
     * @param string $class_name 
     */
    public function load($class_name)
    {
        if(!class_exists($class_name)) {
            include static::$base_path.str_replace('\\', '/', $class_name) . '.php';       
        }
        

    }
    /**
     * For loading files
     * 
     * @param string $file_name without '.php' extension eg api/db/model will be converted and include to $base_path.api/db/model.php
     */
    public function load_file($file_name){

            include static::$base_path.str_replace('\\', '/', $file_name) . '.php';	
    }
    
    
    /**
     * For loading array configs.
     * @param string $path_name @see api\loader::load_file();
     * @return array
     */
    public function load_array($path_name){

            $path = str_replace('\\', '/', $path_name) . '.php';
            return include static::$base_path.$path;            
     
    }
    /**
     *
     * @param string $path_name
     * @return array
     */
    public function load_ini_file($path_name){
            
            $path_name = str_replace('\\', '/', $path_name) . '.ini';

            if(!isset(static::$parse_ini_files[$path_name]))
                  static::$parse_ini_files[$path_name] = parse_ini_file(static::$base_path.$path_name, true);
            return static::$parse_ini_files[$path_name];
                    
    }

}
