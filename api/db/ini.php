<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace api\db;
use api;

class ini extends db {

    public $fetch_class = 'stdClass';
    public $fetch_mode = \PDO::FETCH_ASSOC;
    public $previous_fetch_mode = null;
    public $params = array();
    public $queue = array();
    public $queries = array();
    public $query = null;
    public $statement = null;
    public $last_sql = null;
    protected $original_queries = NULL;
    /**
     *
     * @param string $sql_file_path relative path to file eg app\global\sql\table_name
     * @param api\loader $loader 
     */
    public function __construct($sql_file_path, $params = array(), $loader = 'api\loader') {
        $loader = new $loader;
        $this->queries = $loader->load_ini_file($sql_file_path);
	$this->params = $params;
    }
    
    public function fetch_all($statement_method='reuse_prepared', $fetch_mode = \PDO::FETCH_ASSOC, $fetch_class = null){
        if($fetch_class === null)
            return $this->execute($statement_method)->fetchAll($fetch_mode);
        else return $this->execute($statement_method)->fetchAll($fetch_mode, $fetch_class);
    }
    
    public function fetch_one($statement_method='reuse_prepared', $fetch_mode = \PDO::FETCH_ASSOC, $fetch_class = null){
        if($row = $this->fetch_all($statement_method, $fetch_mode, $fetch_class))
            return $row[0];
    }

    public function fetch_all_obj($statement_method='reuse_prepared'){
        return $this->fetch_all($statement_method, \PDO::FETCH_OBJ);
    }

    public function fetch_one_obj($statement_method='reuse_prepared'){
        return $this->fetch_one($statement_method, \PDO::FETCH_OBJ);
    }
 
    public function fetch_all_class($class_name, $statement_method='reuse_prepared'){
        return $this->fetch_all($statement_method, \PDO::FETCH_CLASS, $class_name);
    }

    public function fetch_one_class($class_name, $statement_method='reuse_prepared'){
        return $this->fetch_one($statement_method, \PDO::FETCH_CLASS, $class_name);
    }

    public function set_values($values){
        //print_r($values); exit;
        foreach($values as $param => $value)
            $this->$param = $value;
  
    }
	
    /**
     *
     * @return \PDOStatement 
     */  
    public function reuse_prepared(){
        $key = get_called_class().$this->query;
        $sql = $this->queries[$this->query]['sql'];
        return $this->use_already_prepared($key, $sql); 
    }
    
    /**
     * @param string $statement 'reuse_prepared'|'get_statement' 
     * 'get_statement' method creates a fresh new prepared statement whereas 'reuse_prepared' potentially reuses an already statement
     * @return \PDOStatement
     */
    public function execute($statement_method='reuse_prepared'){
        if(!isset($this->queries[$this->query])) {
            echo "Query named $this->query does not exist";
            exit;
        }
                
        $statement = $this->$statement_method();
        $query = $this->queries[$this->query];
	unset($query['sql']);
	if(sizeof($query)) {
            foreach($query as $param => $type)
                $statement->bindParam(":$param", $this->params[$param]);  	    
	}
        
        $statement->execute();

	$this->last_sql = $statement->queryString;
	
        return $statement;
    }
    
    

    public function original_queries(){
            if($this->original_queries === NULL)
                    $this->original_queries = (object)$this->queries;
            return $this->original_queries;
    }

    public function show_ini_set_query(){
            return $this->queries[$this->query];
    }
    
    public function replace_sql($query_name, $find, $replace){
	$this->query = $query_name;
	$this->queries[$query_name] = str_replace($find, $replace, $this->original_queries()->{$query_name});
        return $this;
    }
}
