<?php

namespace api\utils\db;
use api;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class index {
            public $Table; // => address_book
            public $Non_unique; // => 1
            public $Key_name; // => idx_address_book_customers_id
            public $Seq_in_index; // => 1
            public $Column_name; // => customers_id
            public $Collation; // => A
            public $Cardinality; // => 2445
            public $Sub_part; // => 
            public $Packed; // => 
            public $Null; // => 
            public $Index_type; // => BTREE
            public $Comment; // =>
            
            public function is_primary(){
                 if($this->Key_name === "PRIMARY")
                     return true;
                 else return false;
            }
            
            public function is_unique(){
                if(!$this->Non_unique)
                    return true;
                else return false;
            }
            
            public function is_single_column(){
                if(sizeof($this->Column_name) === 1)
                        return true;
            }
            
            public function single_column(){
                if($this->is_single_column())
                    return $this->Column_name[0];
                else return false;
            }
}
