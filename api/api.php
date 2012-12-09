<?php

namespace api;

/* class */
class api {
    
    /**
     *
     * @var api\loader|mixed
     */
    protected static $loader = null;
    
    /**
     *
     * @var api\factory|mixed
     */
    protected static $factory = null;

    
    /**
     *
     * @var api\reusable|mixed
     */
    protected static $reusable = null;
    
    /**
     *
     * @var api\reusable_keys|mixed
     */
    protected static $reusable_keys = null;

    /**
     *
     * @param api\loader|mixed $loader
     * @param api\factory|mixed $factory
     * @param api\reusable|mixed $reusable
     * @param api\reusable_keys $reusable_keys
     * @return api 
     */
    
    public function set_api(    
                                $base_path = 'api\base_path',
                                $loader = 'api\loader',
                                $factory = 'api\factory',
                                $reusable = 'api\reusable',
                                $reusable_keys = 'api\reusable_keys'
                            ) {
        
        static::$loader = new $loader;
        
        static::$factory = new $factory;
        
        static::$reusable = new $reusable;
        
        static::$reusable_keys = new $reusable_keys;
        
        $this->set_aggregates($base_path, static::$loader, static::$factory);
        
        return $this;
    }
    
    
    /**
     *
     * @param api\base_path $base_path
     * @param api\loader $loader
     * @param api\factory $factory
     * @param api\factory_instance $factory_instance
     * @return api 
     */
    public function set_aggregates($base_path = 'api\base_path', $loader, $factory) {
        $this->loader()->set_base_path($base_path);
        $this->factory()->set_loader($loader);
        $this->reusable()->set_factory($factory);
        return $this;
    }
    
    /**
     *
     * @return api\loader
     */
    public function loader(){

        return static::$loader;
    }

    /**
     *
     * @return api\factory
     */
    public function factory(){
        return static::$factory;
    }
    
    /**
     *
     * @return api\factory_instance
     */
    public function factory_instance(){
        return static::$factory_instance;    
    }
    /**
     *
     * @return api\reusable;
     */
    public function reusable(){
        return static::$reusable;    
    }
    /**
     *
     * @return api\reusable_instance;
     */
    public function reusable_instance(){
        return static::$reusable_instance;    
    }

}

