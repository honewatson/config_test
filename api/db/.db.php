<?php
namespace api\db;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

    class db_foundation {

        // ----------------------- //
        // --- CLASS CONSTANTS --- //
        // ----------------------- //

        // Where condition array keys
        const WHERE_FRAGMENT = 0;
        const WHERE_VALUES = 1;

        // ------------------------ //
        // --- CLASS PROPERTIES --- //
        // ------------------------ //

        // Class configuration
        protected static $_config = array(
            'connection_string' => 'sqlite::memory:',
            'id_column' => 'id',
            'id_column_overrides' => array(),
            'error_mode' => PDO::ERRMODE_EXCEPTION,
            'username' => NULL,
            'password' => NULL,
            'driver_options' => NULL,
            'identifier_quote_character' => NULL, // if this is null, will be autodetected
            'logging' => false,
            'caching' => false,
        );

        // Database connection, instance of the PDO class
        protected static $_db = NULL;
     
        protected static $_statements = array();
        
        protected static $_prefix = '';
        
        // Last query run, only populated if logging is enabled
        protected $_last_query;

        
        protected $_data = array();

        // Fields that have been modified during the
        // lifetime of the object
        protected $_dirty_fields = array();

        // --------------------------- //
        // --- INSTANCE PROPERTIES --- //
        // --------------------------- //

        // The name of the table the current ORM instance is associated with
        protected $_table_name;

        // Alias for the table to be used in SELECT queries
        protected $_table_alias = null;

	public $sql_query = null;
	public $values = null;
	public $to_replace_prefix = true;
	public $merge = false;
	public $fetch_mode = null;

        public $base_prefix = 'prefix_';
        public $queries = null;
        public $get_query = null;
        public $dbquery = null;
        public $latest_db_results = null;
        public $ini_query_class = 'stdClass';

        // ---------------------- //
        // --- STATIC METHODS --- //
        // ---------------------- //

        
        public function set_prefix($prefix){
            static::$_prefix = $prefix;
        }

        /**
         * Despite its slightly odd name, this is actually the factory
         * method used to acquire instances of the class. It is named
         * this way for the sake of a readable interface, ie
         * ORM::for_table('table_name')->find_one()-> etc. As such,
         * this will normally be the first method called in a chain.
         */
        public function for_table($table_name, $data = array()) {

            $this->_table_name = $table_name;
            $this->_data = $data;
            return $this;
            
        }

        
        public function data($data){
            $this->_data = $data;
            return $this;
        }
        
        /**
         * Set up the database connection used by the class.
         */
        public function set_db() {
            if (static::$_db === NULL ) {
                $db_string = static::$_conf->db->home;
                if(isset(static::$_conf->db->home['prefix']))
                    static::set_prefix(static::$_conf->db->home['prefix']);
                if(isset($db_string[3]))
                    $db = new PDO($db_string[0], $db_string[1], $db_string[2], $db_string[3]);
                else
                    $db = new PDO($db_string[0], $db_string[1], $db_string[2], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                if(isset($db_string[4]))
                    $db->setAttribute(PDO::ATTR_ERRMODE, $db_string[4]);
                else
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                static::set_db($db);
            }
        }
        
        
        public function use_db($db){
            static::$_db->query("USE $db;");
        }

        /**
         * Set the PDO object used by Idiorm to communicate with the database.
         * This is public in case the ORM should use a ready-instantiated
         * PDO object as its database connection.
         */
        public static function set_db($db) {
            static::$_db = $db;
            static::_setup_identifier_quote_character();
        }

        /**
         * Detect and initialise the character used to quote identifiers
         * (table names, column names etc). If this has been specified
         * manually using ORM::configure('identifier_quote_character', 'some-char'),
         * this will do nothing.
         */
        public static function _setup_identifier_quote_character() {
            
            if (is_null(static::$_config['identifier_quote_character'])) {
                static::$_config['identifier_quote_character'] = static::_detect_identifier_quote_character();
            }
        }

        /**
         * Return the correct character used to quote identifiers (table
         * names, column names etc) by looking at the driver being used by PDO.
         */
        protected static function _detect_identifier_quote_character() {
            switch(static::$_db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
                case 'pgsql':
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                case 'sybase':
                    return '"';
                case 'mysql':
                case 'sqlite':
                case 'sqlite2':
                default:
                    return '`';
            }
        }

        /**
         * Returns the PDO instance used by the the ORM to communicate with
         * the database. This can be called if any low-level DB access is
         * required outside the class.
         */
        public function get_db() {
            return static::$_db;
        }




        // --------------------- //
        // --- MAGIC METHODS --- //
        // --------------------- //
        public function __get($key) {
            return $this->get($key);
        }

        public function __set($key, $value) {
            $this->set($key, $value);
        }

        /**
         * Set a property to a particular value on this object.
         * Flags that property as 'dirty' so it will be saved to the
         * database when save() is called.
         */
        public function set($key, $value) {
            $this->_data[$key] = $value;
            $this->_dirty_fields[$key] = $value;
        }

        public function __isset($key) {
            return isset($this->_data[$key]);
        }
    }


