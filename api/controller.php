<?php

namespace api;
/* class */
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
    public function model($config = null, $class_name = null){
	if($this->model === null)
		$this->set_model($config, $class_name);
	return $this->model;
    }
    /**
     * Sets the model for this controllers package
     */
    public function set_model($config = null, $class_name = null){
        if($class_name === null)
            $class_name = $this->class_name;
	$model = $this->folder."\models\\$class_name";
	if($config === null)
	    $this->model = $this->api->reusable()->no_config($model);
	else
	    $this->model = $this->api->reusable()->with_just_config($model, $config);
    }
    
    public function data_stdClass(){
        if($this->data === null)
            $this->data = new \stdClass;
        return $this;
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