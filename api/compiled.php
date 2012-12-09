<?php
namespace api;

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
                
		$this->routes = $this->router_main->route();
                
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
            
		$this->routes['config']->api = $this->api;
                
		$this->main = $this->api->reusable()->with_just_config($this->routes['class'], $this->routes['config']);
                
		$this->main->before_run();
                
		$this->main->main();
                
                $this->main->after_run();
                
	}
}
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
class base_path {
    /**
     *
     * @var string
     */
	public static $base_path = null;
}
class controller {
    /**
     * Routed Params api\router_main::route_page();
     * The params will vary based on those specified in get_routes.php
     * eg api/app/page/blog/get_routes.php
     * $params->page_id
     * $params->page_name
     * @var stdClass
     */
    public $params;
    /**
     * Base path to page folder
     * eg api\app\page\blog
     * Used for loading model, sql, form etc
     * @var string
     */
    public $folder;
    /**
     *
     * @var api\api 
     */
    public $api;
    /**
     * Main contoller class name
     * eg 'get_page'
     * eg $this->folder."\controllers|models|models\sql\".$this->class_name = 'api\app\page\blog\controllers\get_page'
     * @var string
     */
    public $class_name;
    /**
     *
     * @var api\db\model
     */
    public $model = null;
    /**
     * Results from $this->model()->main();
     * @var mixed
     */
    public $data = null;
    /**
     *
     * @var api\view 
     */
    public $view = null;
    /**
     *
     * @var string
     */
    public $rendered_view = null;

    
    public function __construct($config){
        $this->params = $config->params;
        $this->folder = $config->folder;
	$this->api = $config->api;
	$this->class_name = $config->class_name;

    }
    /**
     *
     * @return api\db\model
     */
    public function model(){
	if($this->model === null)
		$this->set_model();
	return $this->model;
    }
    /**
     * Sets the model for this controllers package
     */
    public function set_model(){
	$this->model = $this->folder."\m\\$this->class_name";
    }
    /**
     * This method runs before controller::main().
     */
    public function before_run(){}
    
    /**
     * This method runs after controller::main().
     */
    public function after_run(){}
    
    /**
     * 
     * @param string $template
     * @return api\view
     */
    public function view($template = 'index'){
	if($this->view === null)
		$this->set_view($template);
	return $this->view;
    }
    
    /**
     * 
     * @param string $template
     * @param string $view
     * @return controller 
     */
    public function set_view($template = 'index', $view = 'api\view' ){
	if($view !== 'api\view')
		$this->api->loader()->load($view);
	$this->view = new $view($this->api->factory(), $this->data);
	return $this;
    }
    
    /**
     * Retrieves the rendered view from api\view
     * @param string $template
     * @return string 
     */
    public function render($template = 'index'){
	$template = $this->folder."\\templates\\$template";
	$this->rendered_view = $this->view()->render($template);
	return $this->rendered_view;
    }
    /**
     * Dispatches the response to the client (browser usually)
     * @param api\response $response
     * @param string $rendered_view
     */
    public function send_response( $response = 'api\response_200', $rendered_view = null ) {
	if($rendered_view === null)
		$rendered_view = $this->rendered_view;
	$response = new $response;
	$response->send($rendered_view);
    }
    
    
    
}
class core_app_routes {
    /**
     *
     * @var api\app\config\page_routes
     */
    public $page;
    /**
     *
     * @var api\app\config\app_routes 
     */
    public $app;
    
    public function __construct($page_routes = "app\config\page_routes", $app_routes = "app\config\app_routes") {
        $this->page = new $page_routes;
        $this->app = new $app_routes;
    }
}


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

class folder_params {
    /**
     *
     * @var string
     */
    public $folder;
    /**
     *
     * @var stdClass
     */
    public $params;
    /**
     *
     * @var string
     */
    public $class_name;
    public function __construct($folder, $params, $class_name){
        $this->folder = $folder;
        $this->params = $params;
	$this->class_name = $class_name;
    }
}
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
                  static::$parse_ini_files[$path_name] = parse_ini_file(include static::$base_path.$path_name, true);
            return static::$parse_ini_files[$path_name];
                    
    }

}

class request {
	/**
         *
         * @var bool|null
         */
	public static $_reset = null;
        /**
         * Basically $_SERVER
         * @var array 
         */
	public static $_server = null;
        /**
         * Basically $_GET
         * @var array 
         */
	public static $_get = null;
        /**
         * Basically $_POST
         * @var array 
         */
        public static $_post = null;
        /**
         * Basically $_COOKIE
         * @var array 
         */
        public static $_cookie = null;
        /**
         * Basically $_FILES
         * @var array 
         */
        public static $_files = null;
        /**
         * Basically $_ENV
         * @var array 
         */
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
class reusable_keys extends reusable {
    /**
     * Object storage
     * @var array
     */    
    protected static $instances = array();
    
    public function with_just_config($class_name, $config, $with_config = 'with_config', $key = null){
        if($key === null)    
            $key = $this->get_key($config);
        if(!isset(static::$instances[$key]))
            static::$instances[$key] = static::$factory->$with_config($class_name, $config);
        return static::$instances[$key];
    }
    
    public function get_key($config){
        return implode("", array_values((array)$config));
    }
    
    public function with_config_lambda($class_name, $config, $lambda, $key = null){
        if($key === null)    
            $key = $this->get_key($config);
        if(!isset(static::$instances[$key]))
            static::$instances[$key] = static::$factory->with_config_lambda($class_name, $config, $lambda);
        return static::$instances[$key];
    }
    
}


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

	
	public function __construct($app_sub_folder = '', $core_app_routes = 'api\core_app_routes', $factory = 'api\factory', $request = 'api\request', $router_page = 'api\router_page', $app_folder = 'app\page\\') {
            
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
                    
			return $this->route_page(  $this->core_app_routes->page->index, array('index') );
                
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
	public function route_app($app_folder, $route_bits ){
		
		$route_bits = array_shift($route_bits);
                
		$this->app_folder = "app\apps\\$app_folder\\";
                
		$this->app_class_routes = $this->loader->load_file($this->app_folder."/app_routes.php");
		
		if(!sizeof($route_bits))
			return $this->route_index( $this->app_class_routes );
		else
			return $this->route_page_or_app( $route_bits, $this->app_class_routes);
		
		
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


class router_page {
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
        return "$this->page_folder\c\\$this->class_name";
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
class template_path {
        /**
         * Path to main template folder
         * @var string
         */
	public static $template_path = null;
}
class view {
        /**
         * <p>The resulting data from the model passed by the controller to view.</p>
         * @var object 
         */
	public $data;
        /**
         *
         * @var api\factory
         */
	public $factory;

	public function __construct($factory, $data = null){
                $this->factory = $factory;
		$this->data = $data;
	}
	/**
         * <p>Renders the template</p>
         * @param string $template path to template file
         * @return string 
         */
	public function render($template, $base_path = 'api\base_path'){
                ob_start();
                include $base_path::$base_path.str_replace('\\', '/', $template) . '.php';   
		return ob_get_clean();
	}
	
        public function widget($widget_name){
                $this->factory->no_config("app\widget\\$widget_name")->main();
        }

        public function page_section($page_section){
                $this->factory->no_config("app\page_section\\$page_section")->main();
        }
}
class response {
	/**
         * Method for sending headers
         */
	public function send_headers(){}
	/**
         * Prints your rendered view
         * @param api\view::render() $view 
         */
	public function send($view){
		$this->send_headers();
		if($view !== null)
			echo $view;
	}	
}

class response_200 extends response {}
class db {
    /**
     *
     * @var \PDO
     */
    protected static $_db = null;
    
    protected static $statements = array();
    
    protected static $prefix = null;
    
    protected static $replace_prefix = null;
    
    protected static $quote_character = null;
    
    public function setup_db($mode = 'production', $quote_character = "`", $db_config = 'app\config\db_config') {
        
            if (static::$_db === null ) {
                
                $db_config = new $db_config;
                
                $db_string = $db_config->production;

                if(isset($db_string['prefix']))
                    static::$prefix = $db_string['prefix'];
                
                if(isset($db_string[3]))
                    $db = new \PDO($db_string[0], $db_string[1], $db_string[2], $db_string[3]);
                else
                    $db = new \PDO($db_string[0], $db_string[1], $db_string[2], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                
                if(isset($db_string[4]))
                    $db->setAttribute(\PDO::ATTR_ERRMODE, $db_string[4]);
                else
                    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                static::$_db = $db;
                static::$quote_character = $quote_character; 
                
            }   
            return $this;
    }

    
    public function set_prefix($prefix){
            static::$_prefix = $prefix;
            return $this;
    }
    
    public function set_db($db){
            static::$_db = $db;
            return $this;
    }
    
    public function set_quote_character($quote_character = "`"){
            static::$quote_character = $quote_character; 
            return $this;
    }

    public function use_db($db){
            static::$_db->query("USE $db;");
            return $this;
    }
    /**
     *
     * @return \PDO
     */
    public function get_db() {
            return static::$_db;
    }
}