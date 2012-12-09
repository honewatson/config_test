<?php

namespace api;
/* class */
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