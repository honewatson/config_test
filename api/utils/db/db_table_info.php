<?php

namespace api\utils\db;
use api;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class db_table_info {
    
    protected $db_names = array();
    protected $db = null;
    
    public function __construct($select = 'api\db\select') {
        $this->db  = new $select;
    }
    
    
    public function for_db($db_name, $extractor = 'api\utils\db\extractor') {
        //echo $db_name;
        if(!isset($this->db_names[$db_name]))
                $this->set_db_names($db_name, $extractor);
        return $this->db_names[$db_name];
    }
    
    public function set_db_names($db_name, $extractor = 'api\utils\db\extractor') {
            $this->db->use_db($db_name);
            
            $tables = $this->db->raw_query("SHOW TABLES", array())->fetch_all_num();
           
            foreach($tables as $table) {
                $results[$table[0]] = $this->get_table_info($table);
            }

            $result_class = new $extractor($results, $db_name);
            
            $this->db_names[$db_name] = $result_class;       
    }
    
    public function get_table_info($table, $desc = 'api\utils\db\desc', $index = 'api\utils\db\index'){
        
                $new_desc_results = array();
                $new_index_results = array();
                
                $desc_results = $this->db->raw_query( "DESC {$table[0]};", array() )->fetch_all_class($desc);
                //print_r($desc_results); exit;
                foreach($desc_results as $ob){
                    $new_desc_results[$ob->Field] = $ob;
                }
                
                $results['desc'] = $new_desc_results;
                
                $index_results = $this->db->raw_query( "SHOW INDEXES IN  {$table[0]};", array() )->fetch_all_class($index);
                
                foreach($index_results as $this_index) {
                    if(!isset($new_index_results[$this_index->Key_name])) {
                        $this_index->Column_name = array($this_index->Column_name);
                        $new_index_results[$this_index->Key_name] = $this_index;
                    
                    }
                    else {
                        $i = $new_index_results[$this_index->Key_name];
                        $i->Column_name[] = $this_index->Column_name;
                        //print_r($i->Column_name); 
                        $new_index_results[$this_index->Key_name] = $i;
                    }
                }
                $results['indexes'] = $new_index_results;
                return $results;    
                
    }
    
}
