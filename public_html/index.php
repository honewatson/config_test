<?php


include '../api/deploy.php';

include "../app/config.php";

$base_path::$base_path = dirname(__file__).'/../';

$autoload::get_instance()->autoload($base_path);
class testInclude {
	public function getController()  {
		$this->db = "Fake Db Object";
		$this->widgets = array();
		$this->ini = "inifile";
		$this->params = $_GET;
		$this->controller = include "../app/test/config/get_index.php";

		print_r($this->controller);

	}
}

$c = new testInclude;

$c->getController();
