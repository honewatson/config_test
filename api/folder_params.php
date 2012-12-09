<?php

namespace api;
/* class */
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