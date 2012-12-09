<?php

namespace api\unittests\mocks;

class dolphin {
    public $fins = 'fins';
    public $tail = 'tail';
    public $mouth = 'mouth';
    
    public function __construct($fins = 'fins', $tail = 'tail', $mouth = 'mouth'){
        $this->fins = $fins;
        $this->tail = $tail;
        $this->mouth = $mouth;
    }

    public function set($fins = 'fins', $tail = 'tail', $mouth = 'mouth'){
        $this->fins = $fins;
        $this->tail = $tail;
        $this->mouth = $mouth;
    }
}