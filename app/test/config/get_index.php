<?php
namespace app\test;
/**
 * @var $this \testInclude
 */
$modelInstance = new models\index($this->db, $this->ini);

$this->sections = array();
$this->widget_builder = "some widget builder object which gets configs and injects classes";
$this->section_builder = "some class builder object which gets configs and injects classes";
$this->view = 'view object for render method';
$controller =  new controllers\get($this->params,
	  $modelInstance ,
	  $this->widgets, // widget config for app/page
	  $this->sections, // section config for app/page
	  $this->widget_builder,
      $this->section_builder,
	  $this->view
);

return $controller;