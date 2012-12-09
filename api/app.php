<?php

namespace api;
/* class */
class app {
        /**
         *
         * @var api\request
         */
	public $request;
        /**
         *
         * @var api\api
         */
	public $api;
        /**
         *
         * @var api\router_main
         */
	public $router_main;
        /**
         *
         * @var array array('main_controller', 'main_controller_config')
         */
	public $routes;
        /**
         * Main app controller
         * @var api\controller
         */
	public $main;
	
        /**
         * Injects class dependencies
         *
         * @param api\base_path|mixed $base_path  
         * @param api\autoload|mixed $autoload
         * @param api\request|mixed $request
         * @param api\api|mixed $api
         * @param string $router_main 'api\router_main' 
         */
	public function start($base_path, $autoload, $request, $api, $router_main, $db = null){
            
                /* @var $autoload api\autoload */
		$autoload::get_instance()->autoload($base_path);
                
		$this->request = $request;
                
                $api = new $api;
                
		$this->api = $api = $api->set_api();
                
                if($db !== null)
                    $this->set_db($db);
                
		$this->router_main = $api->reusable()->no_config($router_main);
                

                
	}
        
        public function set_db($db){
                /* @var $db api\db\db */
                $db = $this->api->reusable()->no_config($db);
                $db->setup_db();
                
        }
	
        /**
         *  This method runs the main application app of the framework
         */
	public function run(){
            
		$this->routes = $this->router_main->route();     
                
		$this->routes['config']->api = $this->api;
                
		$this->main = $this->api->reusable()->with_just_config($this->routes['class'], $this->routes['config']);
                
		$this->main->before_run();
                
		$this->main->main();
                
                $this->main->after_run();
                
	}
}