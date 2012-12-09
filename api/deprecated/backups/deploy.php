<?php

namespace api;

class base_path {
	public static $base_path = null;
}

class template_path {
	public static $template_path = null;

}

class faux_singleton {
    protected function __construct(){}
    public static function get_instance(){
        return new static;
    }
}

class controller {
    public $params;
    public $folder;
    public $api;
    public $class_name;
    public $model = null;
    public $data = null;
    public $view = null;
    public $rendered_view = null;

    
    public function __construct($config){
        $this->params = $config->params;
        $this->folder = $config->folder;
	$this->api = $config->api;
	$this->class_name = $config->class_name;
    }
    
    public function model(){
	if($this->model === null)
		$this->set_model();
	return $this->model;
    }
    
    public function set_model(){
	$this->model = $this->folder."\models\\$this->class_name";
    }
    
    public function before_run(){}
    
    public function view($template = 'index'){
	if($this->view === null)
		$this->set_view($template);
	return $this->view;
    }
    
    public function set_view($template = 'index', $view = 'api\view' ){
	$loader = $this->api->loader();
	if($view !== 'api\view')
		$loader->load($view);
	$this->view = new $view($loader, $template, $this->data);
	return $this;
    }
    
    public function render($template = 'index'){
	$template = $this->folder."\\templates\\$template";
	$this->rendered_view = $this->view()->render($template);
	return $this->rendered_view;
    }
    
    public function send_response( $response = 'api\response_200', $view = null ) {
	if($view === null)
		$view = $this->rendered_view;
	$response = new $response;
	$response->send($view);
    }
    
}

class response {
	
	public function send_headers(){}
	
	public function send($view){
		$this->send_headers();
		if($view !== null)
			echo $view;
	}	
}

class response_200 extends response {}

class view {
	public $data;
	public $loader;
	public $template;
	public function __construct($loader, $data = null){
		$this->data = $data;
		$this->loader = $loader;
	}
	
	public function render($template){
		ob_start();
		$this->loader->load_file($template);
		return ob_get_clean();
	}
	
}

class app {
	public $autoload;
	public $request;
	public $api;
	public $main_router;
	public $routes;
	public $main;
	
	public function start($base_path, $autoload, $request, $api, $main_router){
		$autoload::get_instance()->autoload($base_path);
		$this->request = $request;
		$this->api = $api = $api::get_instance()->set_api()->set_aggregates();
		$this->main_router = $api->reusable()->no_config($main_router);
		$this->routes = $this->main_router->route();
	}
	
	public function run(){
		//print_r($this->routes); exit;
		$this->routes['config']->api = $this->api;
		//print_r($this->routes); exit;
		$this->main = $this->api->reusable()->with_just_config($this->routes['class'], $this->routes['config']);
		$this->main->before_run();
		$this->main->main();
	}
}

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
            include static::$base_path.str_replace('\\', '/', $class_name) . '.php';          
        }

    }
    
    public function load_file($file_name){
            include static::$base_path.str_replace('\\', '/', $file_name) . '.php';	
    }
    
    
    
    public function load_array($path_name){

            $path = str_replace('\\', '/', $path_name) . '.php';
            return include static::$base_path.$path;            
     
    }
}


class factory extends faux_singleton {
    
    protected static $loader = null;
    
    public function get_loader(){
        return static::$loader;
    }
    
    public function set_loader( $loader = 'api\loader' ) {
        if(static::$loader === null || $loader !== static::$loader ) {    
            static::$loader = $loader::get_instance();

        }        
    }

    public function no_config($class_name){
        static::$loader->load($class_name);
        return new $class_name;
    }
    
    public function with_config($class_name, $config){
        static::$loader->load($class_name);
        return new $class_name($config);
    }

    public function with_config_lambda($class_name, $config, $lambda){
        static::$loader->load($class_name);
        return $lambda($class_name, $config);
    }
    
    public function with_config_array($class_name, $config ){
        return static::with_config($class_name, (array)$config );
    }
    
    public function with_config_object($class_name, $config ){
        return static::with_config($class_name, (object)$config );
    }

    public function with_config_call($class_name, $args) {
        static::$loader->load($class_name);
        $class = new $class_name;
        call_user_func_array(array($class, "set"), $args);
        return $class;
    }
    
    public function with_config_reflect($class_name, $args) {
        static::$loader->load($class_name);
        $reflection = new \ReflectionClass($class_name);
        return $reflection->newInstanceArgs($args);
    }

}

class factory_instance extends factory {
    
    public function no_config($class_name){
        static::$loader->load($class_name);
        return $class_name::get_instance();
    }
    
    public function with_config($class_name, $config){
        static::$loader->load($class_name);
        return $class_name::get_instance($config);
    }
}



class reusable extends faux_singleton {
	
    protected static $instances = array();
    
    protected static $factory = null;
    
    public function get_factory(){
        return static::$factory;
    }
    
    public function set_instance($instance){
        static::$instances[$class_name] = $instance;
    }
    
    public function set_factory( $factory = 'api\factory' ) {
        if(static::$factory === null || $factory !== static::$factory ) {    
            static::$factory = $factory::get_instance();
        }        
    }
    
    public function with_just_config($class_name, $config, $with_config = 'with_config'){
        if(!isset(static::$instances[$class_name]))
            static::$instances[$class_name] = static::$factory->$with_config($class_name, $config);
        return static::$instances[$class_name];
    }
    
    public function no_config($class_name){
        if(!isset(static::$instances[$class_name]))
            static::$instances[$class_name] = static::$factory->no_config($class_name);
        return static::$instances[$class_name];
    }

    public function with_config_lambda($class_name, $config, $lambda){
        if(!isset(static::$instances[$class_name]))
            static::$instances[$class_name] = static::$factory->with_config_lambda($class_name, $config, $lambda);
        return static::$instances[$class_name];
    }	

    public function with_config_array($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_array');

    }

    public function with_config_object($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_object');
    }

    public function with_config_reflect($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_reflect');
    }

    public function with_config_call($class_name, $config){
        return static::with_just_config($class_name, $config, 'with_config_call');
    }	
}

class reusable_keys extends reusable {
    
    public function with_just_config($class_name, $config, $with_config = 'with_config', $key = null){
        if($key === null)    
            $key = $this->get_key($config);
        if(!isset(static::$instances[$key]))
            static::$instances[$key] = static::$factory->$with_config($class_name, $config);
        return static::$instances[$key];
    }
    
    public function get_key($config){
        return implode("", (array)$config);
    }
    
    public function with_config_lambda($class_name, $config, $lambda, $key = null){
        if($key === null)    
            $key = $this->get_key($config);
        if(!isset(static::$instances[$key]))
            static::$instances[$key] = static::$factory->with_config_lambda($class_name, $config, $lambda);
        return static::$instances[$key];
    }
    
}

class reusable_instance extends reusable {
    
    protected static $factory = null;
    
    public function set_factory( $factory = 'api\factory_instance' ) {
        if(static::$factory === null || $factory !== static::$factory ) {    
            static::$factory = $factory::get_instance();
        }        
    }    
}

class reusable_instance_keys extends reusable_keys {
    
    protected static $factory = null;
    
    public function set_factory( $factory = 'api\factory_instance' ) {
        if(static::$factory === null || $factory !== static::$factory ) {    
            static::$factory = $factory::get_instance();
        }        
    }    
}




class api extends faux_singleton {
    protected static $loader = null;
    protected static $factory = null;
    protected static $factory_instance = null;
    protected static $reusable = null;
    protected static $reusable_instance = null;

    
    
    public function set_api(    $loader = 'api\loader',
                                $factory = 'api\factory',
                                $factory_instance = 'api\factory_instance',
                                $reusable = 'api\reusable',
                                $reusable_instance = 'api\reusable_instance' ) {
        
        static::$loader = $loader::get_instance();
        static::$factory = $factory::get_instance();
        static::$factory_instance = $factory_instance::get_instance();
        static::$reusable = $reusable::get_instance();
        static::$reusable_instance = $reusable_instance::get_instance();
        return $this;
    }
    
    public function set_aggregates($base_path = 'api\base_path', $loader = 'api\loader', $factory = 'api\factory', $factory_instance = 'api\factory_instance') {
        $this->loader()->set_base_path($base_path);
        $this->factory()->set_loader($loader);
        $this->reusable()->set_factory($factory);
        $this->reusable_instance()->set_factory($factory_instance);
        return $this;
    }
    
    public function loader(){
        return static::$loader;
    }

    public function factory(){
        return static::$factory;
    }
    
    public function factory_instance(){
        return static::$factory_instance;    
    }

    public function reusable(){
        return static::$reusable;    
    }

    public function reusable_instance(){
        return static::$reusable_instance;    
    }
}

class main_router {
	
        public $config = null;
	public $core_app_routes = null;
	public $app_sub_folder = '';
	public $get = null;
        public $factory = null;
        public $page_router = null;
        public $app_folder = null;
        public $request = null;
        public $q = null;
        public $route_bits = null;
        public $class_name = null;
        public $sub_route_bits = null;
        
        
	
	public $app_class_routes = null;
	public $page_folder = null;
        public $page_router_class = null;

	
	public function __construct($app_sub_folder = '', $core_app_routes = 'api\app\config\core_app_routes', $factory = 'api\factory', $request = 'api\request', $page_router = 'api\page_router', $app_folder = 'api\app\page\\') {
            $this->factory = $factory::get_instance();
	    $this->core_app_routes = $this->factory->no_config($core_app_routes);
            $this->app_sub_folder = $app_sub_folder;
	    $this->get = $request::$_get;
            $this->request = $request;
           
            $this->page_router = $page_router;
            $this->app_folder = $app_folder;
            
	}
	
	public function route( ) {

		if(!$q = $this->get_q())
			return $this->route_page(  $this->core_app_routes->page->index, array('index') );
		else
			return $this->route_page_or_app( $this->get_route_bits($q),  $this->core_app_routes );
	}
	
	public function get_q(){
		if(!isset($this->get['q']))
			return null;
		else {
                        $this->q = str_replace($this->app_sub_folder, '', $this->get['q']);
			return $this->q;
		}
	}
	
        public function get_route_bits($q){
            $this->route_bits = explode('/', trim($q, '/'));
            return $this->route_bits;
        }
        
	public function route_page_or_app( $route_bits , $routes) {
		
		$base_route_bit = $route_bits[0];

		if( isset( $routes->page->$base_route_bit )  )
		
			return $this->route_page( $routes->page->$base_route_bit, $route_bits );
			
		else if ( isset($routes->app->$base_route_bit) )
		
			return $this->route_app( $routes->app->$base_route_bit, $route_bits );
		
		else return $this->route_page( $routes->page->single, $route_bits, 'main');
		
	}


	public function route_app($app_folder, $route_bits ){
		
		$route_bits = array_shift($route_bits);
		$this->app_folder = "api\app\apps\\$app_folder\\";
                
		$this->app_class_routes = $this->loader->load_file($this->app_folder."/app_routes.php");
		
		if(!sizeof($route_bits))
			return $this->route_index( $this->app_class_routes );
		else
			return $this->route_page_or_app( $route_bits, $this->app_class_routes);
		
		
	}

        
	public function route_page($page_folder, $route_bits, $class_name = null){
            $this->class_name = $class_name;
            $this->page_folder = $this->app_folder.$page_folder;
            if($page_folder !== 'single')
                array_shift($route_bits);
            $this->sub_route_bits = $route_bits;
            
            $page_router_class =    $this->factory->with_config($this->page_router,
                                                                    array('page_folder'=>$this->page_folder,
                                                                    'sub_route_bits'=>$this->sub_route_bits,
                                                                    'factory'=>$this->factory,
                                                                    'request'=>$this->request,
                                                                    'class_name'=>$this->class_name));
            $this->page_router_class = $page_router_class;
            //print_r($this->page_router_class); exit;
            return $page_router_class->route();    			
	}
	
    
}

class folder_params {
    public $folder;
    public $params;
    public $class_name;
    public function __construct($folder, $params, $class_name){
        $this->folder = $folder;
        $this->params = $params;
	$this->class_name = $class_name;
    }
}

class page_router {
    public $page_folder;
    public $route_bits;
    public $factory;
    public $request;
    public $class_name;
    public $request_type = null;
    public $route_bits_size = null;
    public $routes = null;
    public $routes_location = null;
    public $params = null;
    
    
    public function __construct($config){
        //rint_r($config); exit;
        
        $this->page_folder = $config['page_folder'];
        $this->route_bits = $config['sub_route_bits'];
        $this->factory = $config['factory'];
        $this->request = $config['request'];
        $this->set_request_type();

        if($config['class_name'] !== null)
            $this->class_name = $this->request_type."_{$config['class_name']}";
    }
    

    public function route(){
        if($this->route_bits_size = sizeof($this->route_bits))
            return $this->parse_params();
        else
            return $this->return_config($this->get_class_name_namespace(), $this->page_folder);
    }
    
    public function return_config($page_folder, $class_name, $params = array(), $config_class = 'api\folder_params'){
        return array('class' => $class_name ,  "config" => new $config_class( $page_folder, $params, $this->class_name) );
    }
    
    public function parse_params() {
        
            $this->set_route_location();
            $this->set_routes();
            $this->set_class_name();
            $this->set_params();
        
            return $this->return_config(
                            $this->page_folder,
                            $this->get_class_name_namespace(),
                            $this->params
                        );        
    }

    
    public function get_params(){
        if($this->params === null)
            $this->set_params();
        return $this->params;
    }
    
    public function set_params(){
        $routes = $this->routes;
        $route_bits = $this->route_bits;
        if(isset($routes[$this->class_name])) {
            $params = $routes[$this->class_name];
            if(sizeof($params) < $this->route_bits_size) {
                
                $this->params = array("404" => "Array of Request URI has more parameters than is set in routes config for this page.");
            }
            else {
                $count = 0;
                $parsed_params = new \stdClass;
                
                foreach($route_bits as $value) {
                    $parsed_params->{$params[$count]} = $value;
                    $count++;
                }
                $this->params = $parsed_params;
            }            
        }
        else {
            $this->params = array("404" => "$this->class_name is not in $this->page_folder get_routes");
        }
        
       
    }
    
    public function get_class_name_namespace(){
        return "$this->page_folder\controllers\\$this->class_name";
    }
    
    public function set_class_name(){
            $route_bits = $this->route_bits;
            $routes = $this->routes;
            $class_name_or_main =  $this->request_type."_".array_shift($route_bits);
            if(isset($routes[$class_name_or_main]))
                $this->class_name = $class_name_or_main;

            else
                $this->class_name = $this->request_type."_main";
          
    }
    
    public function set_routes(){
        $this->routes = $this->factory->get_loader()->load_array($this->routes_location);
    }
    
    
    public function set_route_location(){
        $request_type = $this->request_type;
        $this->routes_location = "$this->page_folder\\{$request_type}_routes";
    }    
    
    public function set_request_type(){
        $request = $this->request;
        if(sizeof($request::$_post)) 
            $this->request_type = 'post';
        else
            $this->request_type = 'get';          
    }
}


class autoload extends faux_singleton {
    
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

}

class request {
	
	public static $_reset = null;
	public static $_server = null;
	public static $_get = null;
	public static $_post = null;
	public static $_cookie = null;
	public static $_files = null;
	public static $_env = null;
	
	public function __construct($_reset = null, $_server, $_get, $_post, $_cookie, $_files, $_env) {
		if(static::$_reset === null || $_reset === true){

			static::$_server = $_server;
			static::$_get = $_get;
			static::$_post = $_post;
			static::$_cookie = $_cookie;
			static::$_files = $_files;
			static::$_env = $_env;			
			static::$_reset = true;
		}
	}
	
	
}