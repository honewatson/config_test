<?php
namespace api\db;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/* class */
class db {


        // ----------------------- //
        // --- CLASS CONSTANTS --- //
        // ----------------------- //

        // Where condition array keys
    const WHERE_FRAGMENT = 0;
    const WHERE_VALUES = 1;
    /**
     *
     * @var \PDO
     */
    protected static $_db = null;
    
    protected static $prepared = array();
    
    protected static $prefix = null;
    
    protected static $replace_prefix = null;
    
    protected static $quote_character = null;
    
    protected static $id_column_overrides = array();
    
    protected static $id_column = 'id';
    
    public function setup_db($mode = 'production', $quote_character = "`", $db_config = 'app\config\db_config') {
        
            if (static::$_db === null ) {
                
                $db_config = new $db_config;
                
                $db_string = $db_config->production;

                if(isset($db_string['prefix']))
                    static::$prefix = $db_string['prefix'];
                
                if(isset($db_string[3]))
                    $db = new \PDO($db_string[0], $db_string[1], $db_string[2], $db_string[3]);
                else
                    $db = new \PDO($db_string[0], $db_string[1], $db_string[2], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                //PDO::MYSQL_ATTR_MAX_BUFFER_SIZE
                if(isset($db_string[4]))
                    $db->setAttribute(\PDO::ATTR_ERRMODE, $db_string[4]);
                else
                    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                
                static::$_db = $db;
                
                static::$quote_character = $quote_character; 
                
            }   
            return $this;
    }

    public function set_id_column_overrides($id_column_overrides){
        static::$id_column_overrides = $id_column_overrides;
    }
 
    public function add_id_column_overrides($table_name, $id_column_overrides){
        static::$id_column_overrides[$table_name] = $id_column_overrides;
    }


    public function set_id_column($id_column){
        static::$id_column = $id_column;
    }
    
    public function set_prefix($prefix){
            static::$_prefix = $prefix;
            return $this;
    }
    
    public function set_db($db){
            static::$_db = $db;
            return $this;
    }
    
    public function set_quote_character($quote_character = "`"){
            static::$quote_character = $quote_character; 
            return $this;
    }

    public function use_db($db){
            static::$_db->exec("USE $db;");
            return $this;
    }
    /**
     *
     * @return \PDO
     */
    public function get_db() {
            return static::$_db;
    }
    
    /**
    * Quote a string that is used as an identifier
    * (table names, column names etc). This method can
    * also deal with dot-separated identifiers eg table.column
    */
    protected function _quote_identifier($identifier) {
        $parts = explode('.', $identifier);
        $parts = array_map(array($this, '_quote_identifier_part'), $parts);
        return join('.', $parts);
    }

    /**
    * This method performs the actual quoting of a single
    * part of an identifier, using the identifier quote
    * character specified in the config (or autodetected).
    */
    protected function _quote_identifier_part($part) {
        if ($part === '*') {
            return $part;
        }
        $quote_character = static::$quote_character;
        return $quote_character . $part . $quote_character;
    }
    /**
     * 
     *
     * @param string $sql
     * @return \PDOStatement 
     */
    public function use_already_prepared($key, $sql){
       
        if(!isset(static::$prepared[$key]))
                static::$prepared[$key] = static::$_db->prepare($sql);
        return static::$prepared[$key];   
        
    }
    

    /**
     * Turns out MD5 is a lot faster than programatically generating a key based on the param keys
     * @param string $sql
     * @return  \PDOStatement
     */
    public function use_already_prepared_md5($sql){
         $key = md5($sql);
         return $this->use_already_prepared($key, $sql);
    }


    /**
     *
     * @param string $sql
     * @return \PDOStatement 
     */
    public function get_statement($sql){
        return static::$_db->prepare($sql);
    }

    public function str_replace_query($query){
        if(static::$replace_prefix === null)
            return $query;
        else
            return str_replace(static::$replace_prefix, static::$prefix, $query );
    }
}