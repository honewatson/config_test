<?php

namespace unittests;
include "./test_include.php";

class faux_base_path {
    public static $base_path = '/blah/blah/blah';
    
}



class loader_test extends \PHPUnit_Framework_TestCase {
    
    public function test_instantiate( $loader = 'api\loader' ){
        $loader = $loader::get_instance();
        $this->assertInstanceOf('api\loader', $loader);
        return $loader;        
    }

    /**
    * @depends test_instantiate
    */      
    public function test_set_base_path( $loader ){
        $this->assertNull($loader->set_base_path('unittests\faux_base_path'));
        return $loader;
    }
    
    /**
    * @depends test_set_base_path
    */      
    public function test_get_base_path( $loader ){

        $this->assertEquals($loader->get_base_path(), '/blah/blah/blah');
        return $loader;
    }    

    /**
    * @depends test_instantiate
    */      
    public function test_load( $loader ){
        faux_base_path::$base_path = dirname(__file__).'/../';
        $loader->set_base_path('unittests\faux_base_path');
        $loader->load('unittests\mocks\farm');
        $farm = new mocks\farm;
        $this->assertInstanceOf('unittests\mocks\farm' , $farm);
        
        //new farm;
        return $loader;
    }
    
    
}




/*
 
 class loader extends faux_singleton {
    
    protected static $base_path = null;
    
    public function get_base_path(){
        return static::$base_path;
    }
    
    public function set_base_path($base_path = 'api\base_path'){
        if(static::$base_path === null || $base_path !== static::$base_path ) {    
            static::$base_path = $base_path::$base_path;
        }
    }
    
    public function load($class_name)
    {
        if(!class_exists($class_name)) {
            $path = str_replace('\\', '/', $class_name) . '.php';
            include static::$base_path.$path;            
        }

    }
}

 
*/