<?php

namespace api;
/* class */
class router_main {
	/**
         *
         * @var api\core_app_routes 
         */
	public $core_app_routes = null;
        /**
         *
         * @var string
         */
	public $app_sub_folder = '';
        /**
         *
         * @var api\request::$_get
         */
	public $get = null;
        /**
         *
         * @var api\factory
         */
        public $factory = null;
        /**
         *
         * @var api\router_page
         */
        public $router_page = null;
        /**
         *
         * @var string
         */
        public $app_folder = null;
        /**
         *
         * @var api\request
         */
        public $request = null;
        /**
         *
         * @var string
         */
        public $q = null;
        /**
         *
         * @var array usually $_GET['q'] exploded by '/'
         */
        public $route_bits = null;
        /**
         *
         * @var string name of the sub controller class
         */
        public $class_name = null;
        /**
         *
         * @var array the route bits used by the sub router
         */
        public $sub_route_bits = null;
	/**
         *
         * @var api\core_app_routes
         */
	public $app_class_routes = null;
        /**
         *
         * @var string
         */
	public $page_folder = null;
        /**
         *
         * @var api\router_page
         */
        public $router_page_class = null;
	
	/**
	 * 
	 * @var api\loader
	 */
	public $loader = null;

	
	public function __construct($app_sub_folder = '', $core_app_routes = 'api\core_app_routes',  $loader = 'api\loader', $factory = 'api\factory', $request = 'api\request', $router_page = 'api\router_page', $app_folder = 'app\page\\') {
            
	    $this->loader = new $loader;
            $this->factory = new $factory;
	    $this->core_app_routes = $this->factory->no_config($core_app_routes);
            $this->app_sub_folder = $app_sub_folder;
	    $this->get = $request::$_get;
            $this->request = $request;
            $this->router_page = $router_page;
            $this->app_folder = $app_folder;
            
	}
	/**
         * factory method which takes $_SERVER['REQUEST_URI'] ($_GET['q']) and returns sub controller and params from $_SERVER['REQUEST_URI'] bits
         * @return api\controller
         */
	public function route( ) {

		if(!$q = $this->get_q())
                    
			return $this->route_index(  $this->core_app_routes );
                
		else
                    
			return $this->route_page_or_app( $this->get_route_bits($q),  $this->core_app_routes );
	}
	
        /**
         *
         * @return string returns $_GET['q']
         */
	public function get_q(){
            
		if(!isset($this->get['q']))
                        
			return null;
                
		else {
                        $this->q = str_replace($this->app_sub_folder, '', $this->get['q']);
                        
			return $this->q;
		}
	}
	
        /**
         * Basically converts $_SERVER['REQUEST_URI'] to an array split by '/'
         * @param string $q ($_GET['q']) @see api\router_main::get_q()
         * @return array 
         */
        public function get_route_bits($q){
            
            $this->route_bits = explode('/', trim($q, '/'));
            
            return $this->route_bits;
            
        }
        
        /**
         *
         * Makes an assessment if the first part of the request_uri is part of the main app routes or if sub app routes are required
         * @param array $route_bits
         * @param api/core_app_routes $routes
         * @return api\controller 
         */
	public function route_page_or_app( $route_bits , $routes) {
		
		$base_route_bit = $route_bits[0];

		if( isset( $routes->page->$base_route_bit )  )
		
			return $this->route_page( $routes->page->$base_route_bit, $route_bits );
			
		else if ( isset($routes->app->$base_route_bit) )
		
			return $this->route_app( $routes->app->$base_route_bit, $route_bits );
		
		else return $this->route_page( $routes->page->single, $route_bits, 'main');
		
	}

        /**
         *
         * @param string $app_folder
         * @param array $route_bits
         * @return return api\controller  
         */
	public function route_app($app_folder, $route_bits, $core_app_routes ='api\core_app_routes' ){
		
		array_shift($route_bits);

		$this->app_folder = "app\apps\\$app_folder\page\\";
                
		$page_routes = "\app\apps\\$app_folder\\config\page_routes";
		
		$app_routes = "\app\apps\\$app_folder\\config\app_routes";
		
		$this->loader->load_file("/app/apps/$app_folder/config");
		
		$this->app_class_routes = new $core_app_routes($page_routes, $app_routes);
	
		$sizeOf = sizeof($route_bits);
		
		if(!$sizeOf)
		    return $this->route_index( $this->app_class_routes );
		else
			return $this->route_page_or_app( $route_bits, $this->app_class_routes);
		
		
	}
	
	public function route_index( $app_class_routes ) {
	    
	    return $this->route_page(  $app_class_routes->page->index, array('index'), 'index' );

	}

        /**
         *
         * @param string $page_folder
         * @param array $route_bits
         * @param string $class_name
         * @return api\controller 
         */
	public function route_page($page_folder, $route_bits, $class_name = null){
            

	    $this->class_name = $class_name;
            
            $this->page_folder = $this->app_folder.$page_folder;
            
	    
            if($page_folder !== 'single')
                array_shift($route_bits);
            
            $this->sub_route_bits = $route_bits;

            $router_page_class = $this->factory->with_config(
                                                    $this->router_page,
                                                    array(
                                                        'page_folder'=>$this->page_folder,
                                                        'sub_route_bits'=>$this->sub_route_bits,
                                                        'factory'=>$this->factory,
                                                        'request'=>$this->request,
                                                        'class_name'=>$this->class_name
                                                    )
                                               );
	    
            $this->router_page_class = $router_page_class;
            
            return $router_page_class->route();    			
	}
	
    
}