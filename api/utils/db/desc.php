<?php

namespace api\utils\db;
use api;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class desc {
            public $Field;
            public $Type;
            public $Null;
            public $Key = '';
            public $Default;
            public $Extra;
            
            public function prepare_column($these_fields){

                $column['field'] = $this->Field;
                $type = explode('(',$this->Type);
                $column['size'] = str_replace(")","", $type[1]);
                $column['type'] = $type[0];
                if(strpos($this->Type, 'unsigned')) {
                    $column['extra'][] = 'unsigned';
                    $column['size'] = str_replace(' unsigned', '', $column['size']);
                }
                
                if($this->Null == 'NO'){
                    $column['require'] = true;
                }
                else {
                    $column['require'] = false;
                }
                //print_r($this);
                if($this->Key == 'PRI') {
                    $column['primary'] = true;
                    $column['extra'][] = 'first';
                }
                else {$column['primary'] = false;}
                //print_r($this->Extra); exit;
                if( strpos($this->Extra, 'uto_increment') ){
                    $column['autoinc'] = true;
                }
                //print_r($this);
               // print_r($column);
                //exit;
                return $column;
            }
}

