<?php

namespace api\utils\db;
use api;

class create_selects {
    /**
     *
     * @var api\utils\db\index
     */
    public $desc;
    /**
     *
     * @var api\utils\db\desc
     */
    public $indexes;
    /**
     *
     * @var string api\utils\db\select_statement_builder
     */
    public $select_statement_builder;
    
    public $keys = null;
    
    /**
     *
     * @param api\utils\db\desc $desc
     * @param api\utils\db\index $indexes 
     */
    public function __construct($desc, $indexes, $select_statement_builder = 'api\utils\db\select_statement_builder'){
        $this->desc = $desc;
        $this->indexes = $indexes;
        $this->select_statement_builder = $select_statement_builder;
    }
    
    public function create(){
       $api_small_links = $this->select_factory('api_small_links');
       print_r($this);
    }
    
    /**
     *
     * @param string $table_name
     * @return api\utils\db\select_statement_builder 
     */
    public function select_factory($table_name){
        $select_statement_builder = $this->select_statement_builder;
        return new $select_statement_builder($table_name);
    }
    
    public function get_indexes(){
        if($this->keys === null)
            $this->set_keys();
        return $this->keys;
    }
    
    public function set_keys(){
        foreach($this->desc as $field_desc => $field_values)
            $this->add_keys($field_values);
        ksort($this->keys);            
    }
    
    public function add_keys($field_values){
         if($field_values->Key)
            $this->keys[$this->order_key($field_values->Key)][] = $field_values->Field;       
    }
    
    public function order_key($key){
        return $this->$key();
    }
    
    public function MUL(){
        return 'c_index';
    }
    
    public function PRI(){
        return 'a_primary';
    }   
    
    public function UNI(){
        return 'b_unique';
    }   
}
