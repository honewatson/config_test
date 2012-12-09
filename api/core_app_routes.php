<?php
namespace api;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/* class */
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

