<?php

namespace api\utils\db;
use api;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class crud {
        protected $_get_id_column_name = NULL;
        protected $_table_name = NULL;
        protected static $_db = NULL;
        
    
        public function db(){
            if(self::$_db === NULL){
                    self::$_db = db_foundation::get_instance();
            }
            return self::$_db;
        }
        
        public function get_values(){
            $this->_updateable_fields = array();
            foreach($this->_fields as $field){
                if($this->$field && $field != $this->_get_id_column_name) {
                    $params[] = $this->$field;
                    $this->_updateable_fields[] = $field;
                }

            }
            $params[] = $this->{$this->_get_id_column_name};
            return $params;
        }
        
        public function update(){
            
            $values = $this->get_values();
            $query = $this->_build_update($this->_updateable_fields);
           
            if( $this instanceof customers_wishlist_attributes && $this->customers_id == 2788) {
                echo  "<h3>$query</h3>";
                print_r($values);         
            }
           
            try {
                $this->db()->execute($query, $values);
            }
            catch(Exception $e){
                
                
                echo "<h3>".$e->getMessage()."</h3>";

            }
            
        }
        protected function _build_update($fields) {
            $query = array();
            $query[] = "UPDATE {$this->db()->_quote_identifier($this->_table_name)} SET";

            $field_list = array();
            foreach ($fields as $value) {
                $field_list[] = "{$this->db()->_quote_identifier($value)} = ?";
            }
            $query[] = join(", ", $field_list);
            $query[] = "WHERE";
            $query[] = $this->db()->_quote_identifier($this->_get_id_column_name);
            $query[] = "= ?";
            return join(" ", $query);
        }

    
}
