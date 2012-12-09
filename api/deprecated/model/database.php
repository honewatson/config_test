<?php

namespace api\model;

class database {
    public function test($base_path = "api\base_path"){
        echo $base_path::$base_path;
        echo "<h3>Test</h3>";
    }
}


