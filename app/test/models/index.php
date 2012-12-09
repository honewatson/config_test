<?php

namespace app\test\models;
use api;

class index {
	public function __construct($db, $ini){
		$this->db = $db;
		$this->ini = $ini;
	}
}