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
    
    public $name = null;
    
    public $ordered_selects = array();
    
    public $selects = null;
    
    public $sql_location = 'app/global/sql/';
    
    /**
     *
     * @param api\utils\db\desc $desc
     * @param api\utils\db\index $indexes 
     */
    public function __construct($name, $desc, $indexes, $select_statement_builder = 'api\utils\db\select_statement_builder'){
        $this->name = $name;
        $this->desc = $desc;
        $this->indexes = $indexes;
        $this->select_statement_builder = $select_statement_builder;
    }
    
    
    
    public function create($base_path = "api\base_path"){
       
       $selects = $this->selects();
       $sql_path = $base_path::$base_path.$this->sql_location."{$this->name}.ini";
       $content = "[queries]\n";
       foreach($selects as $select_name => $sql)
           $content .= "$select_name = '$sql'\n";
       $content .= "\n[params]\n";
       foreach($selects as $select_name => $sql)
           $content .= $this->get_params_from_sql_string($select_name, $sql);
           

       file_put_contents($sql_path, $content);
       
           
    }
    public function get_params_from_sql_string($select_name, $sql){
           $sql_bits = explode(" ", $sql);
           $content = "\n;Params for $select_name\n";
           foreach($sql_bits as $sql_bit){
               if(strpos($sql_bit, ":") !== false)
                       $content .= "{$select_name}[] = ".str_replace(":", "", $sql_bit)."\n";
           }        
           return $content;
    }
    public function selects(){
        if($this->selects === null) {
           $this->selects = array();
           $indexes = $this->indexes();
           foreach($indexes as $index_type => $indexes_of_type_x)
               $selects[$index_type] = $this->create_selects($indexes_of_type_x);
           $this->ordered_selects = $selects;           
        }
        return $this->selects;
    }
    public function create_selects($indexes_of_type_x){
        foreach($indexes_of_type_x as $index)
            $indexes[] = $this->get_selects_for_indexes($index);
        return $indexes;
    }
    
    public function get_selects_for_indexes($index) {
        foreach($index as $index_name => $columns)  {
            $select = $this->get_selects_for_index($columns);  
            $this->selects = array_merge($this->selects, $select);
            return $select;
        }

    }
    
    public function get_selects_for_index($columns){
        
        $select = $this->select_factory();
        foreach($columns as $column) {
                $select->where($column, ":$column");
                //if($this->is_unique($column))
                   // $select->limit(1);
                $column_names[] = $column;
        }
        $keyname = implode(".", $column_names);

        return array($keyname => $select->get_sql());  
    }

    public function is_unique($column){
        if(isset(   $this->desc[$column]->Key) 
                    && ( $this->desc[$column]->Key == 'PRI' 
                            || $this->desc[$column]->Key == 'UNI' ) )
                                return true;
    }
   
    
    
    /**
     *
     * @param string $table_name
     * @return api\utils\db\select_statement_builder 
     */
    public function select_factory(){
        $select_statement_builder = $this->select_statement_builder;
        return new $select_statement_builder($this->name);
    }
    
    public function indexes(){
        if($this->keys === null)
            $this->set_keys();
        return $this->keys;
    }
    
    public function set_keys(){
        foreach($this->indexes as $field_desc => $field_values)
            $this->add_keys($field_desc, $field_values);
        ksort($this->keys);      
        
    }
    
    public function add_keys($field_desc, $field_values){
            $this->keys[$this->order_key($field_values)][] = array($field_desc => $field_values->Column_name);       
    }
    
    /**
     *
     * @param api\utils\db\index $index
     * @return type 
     */
    public function order_key($index){
        if($index->is_primary())
            return 'a_primary';
        if($index->is_unique())
            return 'b_unique';
        else return 'c_index';
        
    }
    

}
