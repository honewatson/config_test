<?php

namespace api\db;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class insert extends db {
    
    
    
    public function insert(){
        
        $this->check_slugs();
        
        if ( $this->primary_key() )
            return $this->main_build_insert('duplicates_with_primary_key');
        else
            return $this->main_build_insert('duplicates_without_primary_key');
    }
    
    public function primary_key(){
        
    }
    
    public function duplicates_with_primary_key(){

    }

        
    public function duplicates_without_primary_key(){
    }
    
    public function build_insert_and_params($duplicates_method){
        $duplicates = $this->$duplicates_method();
        $values = array_merge($this->defaults, $this->values);
        $params = array();
        foreach($values as $field => $value){
            $params[":$field"] = $value;
            if($value === $this->defaults[$field]) {
                if(isset($duplicates[$field]))
                    unset($duplicates[$field]);                
            }
        }
        
        $keys = array_keys($values);
        $insert = $this->build_insert($keys, $duplicates);
        
        
    }
    
    public function build_insert($keys, $duplicates){
        $insert = "INSERT INTO $this->table_name (`".implode("`,`", $keys)."`)\n";
        $insert .= "VALUES( :".implode(", :", $keys)." )\n";
        $insert .= "ON DUPLICATE KEY UPDATE\n";
        $insert .= implode(", ", $duplicates);     
        return $insert;
    }
}