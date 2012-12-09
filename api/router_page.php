<?php

namespace api;
/* class */
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
        else
            $this->class_name = $this->request_type."_index";
            
    }
    

    public function route(){
        if($this->route_bits_size = sizeof($this->route_bits))
            return $this->parse_params();
        else
            return $this->return_config( $this->page_folder, $this->get_class_name_namespace());
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