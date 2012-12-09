<?php

namespace api\unittests\mocks;

class farm {
    public $pig = 'pig';
    public $horse = 'horse';
    public $cow = 'meow';
    
    public function __construct($pig = 'pig', $horse = 'horse', $cow = 'meow'){
        $this->pig = $pig;
        $this->horse = $horse;
        $this->cow = $cow;
    }

    public function set($pig = 'pig', $horse = 'horse', $cow = 'meow'){
        $this->pig = $pig;
        $this->horse = $horse;
        $this->cow = $cow;
    }
}