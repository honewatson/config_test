<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace api\db;

class table extends db {

    
    public $params = array();
    public $queue = array();
    public $queries = array();
    public $query = null;
    public $statement = null;
    /**
     *
     * @return \PDOStatement 
     */  
    public function get_statement_prepared_from_query(){
        $key = get_called_class().$this->query;
        if(!isset(static::$prepared[$key]))
                static::$prepared[$key] = $this->get_statement_prepared($key, $this->queries[$this->query]);
        return static::$prepared[$key];     

    }
    /**
     *
     * @return \PDOStatement 
     */    
    public function get_statement_prepared_auto(){
        $key = $this->get_key();
        if(!isset(static::$prepared[$key]))
                static::$prepared[$key] = $this->get_statement( $this->set_return_query($key) );
        return static::$prepared[$key];        
    }
    
    public function fetch_all(){
        return $this->prepare_and_bind()->fetchAll();
    }
    
    public function fetch_one(){
        $all = $this->fetch_all();
        if(sizeof($all))
            return $all[0];
    }
    /**
     *
     * @return \PDOStatement
     */
    public function prepare_and_bind(){
        $statement = $this->get_statement_prepared_from_query();
        $params = $this->params[$this->query];
        foreach($params as $param)
            $statement->bindParam(":$param", $this->$param);
        $statement->execute();
        return $statement;
    }
    
    public function set_query($query_name){
        $this->query = $this->queries[$query_name];
        return $this;
    }

    
    public function set_return_query($key = null){
        if($key === null)
            $key = $this->get_key();
        $this->query = $this->queries[$key];
        return $this->query;
    }
    
    public function get_key(){
        $keys = array();
        foreach($this->params as $property) {
            if($this->$property !== null)
                $keys[] = $property;
        }
        sort($keys);
        $this->queue = $keys;
        return implode("",$keys);
    }
}
