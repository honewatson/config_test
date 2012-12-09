<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace api\form;

class form {
    
    protected static $types = null;
    protected static $loader = null;

    public $data_for_db = null;
    public $ini_file = null;
    public $form = null;
    
    
    public function __construct($parse_ini_file = null, $loader = 'api\loader'){

        if(static::$types === null)
            $this->set_types();
        if(static::$loader === null)
            static::$loader = new $loader;
        if($parse_ini_file !== null)
            $this->load_ini_file($parse_ini_file, $loader)->extend();
        
    }
    
    public function load_ini_file($parse_ini_file, $loader = 'api\loader') {
        $ini_file = static::$loader->load_ini_file($parse_ini_file);
        
        $this->form = array_shift($ini_file);
       
        $this->ini_file = $ini_file;

        return $this;
    }
    
    public function render_fields($base_fields_for_render = 'base_fields_for_render'){
        $fields = new base_fields_for_render($this->ini_file);
        ob_start();
        $fields->render();
        return ob_get_clean();
    }
    
    public function render_form(){
        return '<form method="'.$this->form['method'].'" action="'.$this->form['action'].'" id="'.$this->form['name'].'">'.$this->render_fields().'</form>';
       
    }
    
    public function extend(){
        if(isset($this->form['extends'])) {
            $extended = static::$loader->load_ini_file("api/form/ini/".$this->form['extends']);

            $ini_file_parsed = array();
            foreach($this->ini_file as $field => $value){

                $extends = array_shift($value);
                $value[':field']= $field;
                $ini_file_parsed[$field] = array_merge($extended[$extends], $value );
            }
            $this->ini_file = $ini_file_parsed;
        }           
    }
     
    public function set_types($types = 'api\db\types'){
        static::$types = new $types;
    }
    
    public function set_values($values){
        foreach($values as $field => $value){
            if(isset($this->ini_file[$field]))
                $this->ini_file[$field][':value'] = $value;
        }
    }
    
    public function data_for_db(){
        
        $data = array();
        
        foreach($this->fields as $field) {
            
            $type = $this->$field->type;
            
            $this->$field->validate()->prepare_for_db();
            
            $data[$this->$field->field_for_db] = static::$types->$type( $this->$field->value );
            
        }
            
        $this->data_for_db = $data;
        
        return $this->data_for_db;
    }
    
}


/*
 * 
 * class get {
 *   public function already_prepared(){}
 *   
 * }
 */