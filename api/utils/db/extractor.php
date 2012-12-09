<?php

namespace api\utils\db;
use api;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class extractor {
    
    public $results;
    public $db_name;
    public $tables;
    
    public function __construct($results, $db_name){
        $this->results = $results;
        $this->db_name = $db_name;
        $this->tables = array_keys($results);
    }
    
    public function desc($name){
        if(isset($this->results[$name]))
            return $this->results[$name]['desc'];
        else
            return array();
    }
    
    public function indexes($name) {
        if(isset($this->results[$name]))
            return $this->results[$name]['indexes'];
        else
            return array();        
    }
    
    public function check_indexes($name, $index_object) {
        $indexes = $this->indexes($name);
        if(isset($indexes[$index_object->Key_name])){
            $this_table_index = $indexes[$index_object->Key_name];
            if($this_table_index->Non_unique != $index_object->Non_unique)
                return $this_table_index;
            if($this_table_index->Column_name != $index_object->Column_name)
                return $this_table_index;
        }
        else {
            return false;
        }
    }
    
    public function check_field($name, $column) {
        $columns = $this->desc($name);
        $table = $columns[$column->Field];
        if($table->Type != $column->Type){
            //echo ' class="different"';
            return $table;
        }
        else
            return false;

    }
    
    public function field_not_found($name, $column) {
        $columns = $this->desc($name);
        //echo "<h3>$column->Field</h3>";

        if( !isset($columns[$column->Field]) ) {
            //echo "<h3>$name $column->Field</h3>";
            return true;
        }
    }
    
    public function index_not_found($name, $index_object) {


        $indexes = $this->indexes($name);
        if(!isset($indexes[$index_object->Key_name])){
            return true;
        }
    }
    
    public function create_selects_for_db(){
        foreach($this->results as $table => $table_values)
            $this->create_selects($table);
    }
    
    public function create_selects($name, $create_selects = "api\utils\db\create_selects") {
        if(isset($this->results[$name]))
                $create_selects = new $create_selects($name, $this->results[$name]['desc'], $this->results[$name]['indexes']);
                return $create_selects->create();
    }
}