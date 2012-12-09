<?php

namespace api\db;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class select extends db {
    
        // Values to be bound to the query
        protected $_values = array();

        // Columns to select in the result
        protected $_result_columns = array('*');

        // Are we using the default result column or have these been manually changed?
        protected $_using_default_result_columns = true;

        // Join sources
        protected $_join_sources = array();

        // Should the query include a DISTINCT keyword?
        protected $_distinct = false;

        // Is this a raw query?
        protected $_is_raw_query = false;

        // The raw query
        protected $_raw_query = '';

        // The raw query parameters
        protected $_raw_parameters = array();

        // Array of WHERE clauses
        protected $_where_conditions = array();

        // LIMIT
        protected $_limit = null;

        // OFFSET
        protected $_offset = null;

        // ORDER BY
        protected $_order_by = array();

        // GROUP BY
        protected $_group_by = array();

        // The data for a hydrated instance of the class
        protected $_data = array();

        // Is this a new object (has create() been called)?
        protected $_is_new = false;

        // Name of the column to use as the primary key for
        // this instance only. Overrides the config settings.
        protected $_instance_id_column = null;    

        protected $_table_alias = null;

        //public $_last_query = NULL;

        public function __construct($table_name = '', $data=array()) {
            //echo $table_name; exit;
            $this->_table_name = $table_name;
            $this->_data = $data;
        }


        /**
         * Specify the ID column to use for this instance or array of instances only.
         * This overrides the id_column and id_column_overrides settings.
         *
         * This is mostly useful for libraries built on top of Idiorm, and will
         * not normally be used in manually built queries. If you don't know why
         * you would want to use this, you should probably just ignore it.
         */
        public function use_id_column($id_column) {
            $this->_instance_id_column = $id_column;
            return $this;
        }


        /**
         * Tell the ORM that you wish to execute a COUNT query.
         * Will return an integer representing the number of
         * rows returned.
         */
        public function count() {
            $this->select_expr('COUNT(*)', 'count');
            $result = $this->fetch_one();
            return ($result !== false && isset($result->count)) ? (int) $result->count : 0;
        }


        /**
         * Perform a raw query. The query should contain placeholders,
         * in either named or question mark style, and the parameters
         * should be an array of values which will be bound to the
         * placeholders in the query. If this method is called, all
         * other query building methods will be ignored.
         */
        public function raw_query($query, $parameters = array()) {
            $this->_is_raw_query = true;
            $this->_raw_query = $query;
            $this->_raw_parameters = $parameters;
            return $this;
        }

        /**
         * Add an alias for the main table to be used in SELECT queries
         */
        public function table_alias($alias) {
            $this->_table_alias = $alias;
            return $this;
        }

        /**
         * Internal method to add an unquoted expression to the set
         * of columns returned by the SELECT query. The second optional
         * argument is the alias to return the expression as.
         */
        protected function _add_result_column($expr, $alias=null) {
            if (!is_null($alias)) {
                $expr .= " AS " . $this->_quote_identifier($alias);
            }

            if ($this->_using_default_result_columns) {
                $this->_result_columns = array($expr);
                $this->_using_default_result_columns = false;
            } else {
                $this->_result_columns[] = $expr;
            }
            return $this;
        }

        /**
         * Add a column to the list of columns returned by the SELECT
         * query. This defaults to '*'. The second optional argument is
         * the alias to return the column as.
         */
        public function select($column, $alias=null) {
            $column = $this->_quote_identifier($column);
            return $this->_add_result_column($column, $alias);
        }

        /**
         * Add an unquoted expression to the list of columns returned
         * by the SELECT query. The second optional argument is
         * the alias to return the column as.
         */
        public function select_expr($expr, $alias=null) {
            return $this->_add_result_column($expr, $alias);
        }

        /**
         * Add a DISTINCT keyword before the list of columns in the SELECT query
         */
        public function distinct() {
            $this->_distinct = true;
            return $this;
        }

        /**
         * Internal method to add a JOIN source to the query.
         *
         * The join_operator should be one of INNER, LEFT OUTER, CROSS etc - this
         * will be prepended to JOIN.
         *
         * The table should be the name of the table to join to.
         *
         * The constraint may be either a string or an array with three elements. If it
         * is a string, it will be compiled into the query as-is, with no escaping. The
         * recommended way to supply the constraint is as an array with three elements:
         *
         * first_column, operator, second_column
         *
         * Example: array('user.id', '=', 'profile.user_id')
         *
         * will compile to
         *
         * ON `user`.`id` = `profile`.`user_id`
         *
         * The final (optional) argument specifies an alias for the joined table.
         */
        protected function _add_join_source($join_operator, $table, $constraint, $table_alias=null) {

            $join_operator = trim("{$join_operator} JOIN");

            $table = $this->_quote_identifier($table);

            // Add table alias if present
            if (!is_null($table_alias)) {
                $table_alias = $this->_quote_identifier($table_alias);
                $table .= " {$table_alias}";
            }

            // Build the constraint
            if (is_array($constraint)) {
                list($first_column, $operator, $second_column) = $constraint;
                $first_column = $this->_quote_identifier($first_column);
                $second_column = $this->_quote_identifier($second_column);
                $constraint = "{$first_column} {$operator} {$second_column}";
            }

            $this->_join_sources[] = "{$join_operator} {$table} ON {$constraint}";
            return $this;
        }

        /**
         * Add a simple JOIN source to the query
         */
        public function join($table, $constraint, $table_alias=null) {
            return $this->_add_join_source("", $table, $constraint, $table_alias);
        }

        /**
         * Add an INNER JOIN souce to the query
         */
        public function inner_join($table, $constraint, $table_alias=null) {
            return $this->_add_join_source("INNER", $table, $constraint, $table_alias);
        }

        /**
         * Add a LEFT OUTER JOIN souce to the query
         */
        public function left_outer_join($table, $constraint, $table_alias=null) {
            return $this->_add_join_source("LEFT OUTER", $table, $constraint, $table_alias);
        }

        /**
         * Add an RIGHT OUTER JOIN souce to the query
         */
        public function right_outer_join($table, $constraint, $table_alias=null) {
            return $this->_add_join_source("RIGHT OUTER", $table, $constraint, $table_alias);
        }

        /**
         * Add an FULL OUTER JOIN souce to the query
         */
        public function full_outer_join($table, $constraint, $table_alias=null) {
            return $this->_add_join_source("FULL OUTER", $table, $constraint, $table_alias);
        }

        /**
         * Internal method to add a WHERE condition to the query
         */
        protected function _add_where($fragment, $values=array()) {
            if (!is_array($values)) {
                $values = array($values);
            }
            $this->_where_conditions[] = array(
                static::WHERE_FRAGMENT => $fragment,
                static::WHERE_VALUES => $values,
            );
            return $this;
        }

        /**
         * Helper method to compile a simple COLUMN SEPARATOR VALUE
         * style WHERE condition into a string and value ready to
         * be passed to the _add_where method. Avoids duplication
         * of the call to _quote_identifier
         */
        protected function _add_simple_where($column_name, $separator, $value) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} {$separator} ?", $value);
        }

        /**
         * Return a string containing the given number of question marks,
         * separated by commas. Eg "?, ?, ?"
         */
        protected function _create_placeholders($number_of_placeholders) {
            return join(", ", array_fill(0, $number_of_placeholders, "?"));
        }

        /**
         * Add a WHERE column = value clause to your query. Each time
         * this is called in the chain, an additional WHERE will be
         * added, and these will be ANDed together when the final query
         * is built.
         */
        public function where($column_name, $value) {
            return $this->where_equal($column_name, $value);
        }

        /**
         * More explicitly named version of for the where() method.
         * Can be used if preferred.
         */
        public function where_equal($column_name, $value) {
            return $this->_add_simple_where($column_name, '=', $value);
        }

        /**
         * Add a WHERE column != value clause to your query.
         */
        public function where_not_equal($column_name, $value) {
            return $this->_add_simple_where($column_name, '!=', $value);
        }

        /**
         * Special method to query the table by its primary key
         */
        public function where_id_is($id) {
            return $this->where($this->_get_id_column_name(), $id);
        }

        /**
         * Add a WHERE ... LIKE clause to your query.
         */
        public function where_like($column_name, $value) {
            return $this->_add_simple_where($column_name, 'LIKE', $value);
        }

        /**
         * Add where WHERE ... NOT LIKE clause to your query.
         */
        public function where_not_like($column_name, $value) {
            return $this->_add_simple_where($column_name, 'NOT LIKE', $value);
        }

        /**
         * Add a WHERE ... > clause to your query
         */
        public function where_gt($column_name, $value) {
            return $this->_add_simple_where($column_name, '>', $value);
        }

        /**
         * Add a WHERE ... < clause to your query
         */
        public function where_lt($column_name, $value) {
            return $this->_add_simple_where($column_name, '<', $value);
        }

        /**
         * Add a WHERE ... >= clause to your query
         */
        public function where_gte($column_name, $value) {
            return $this->_add_simple_where($column_name, '>=', $value);
        }

        /**
         * Add a WHERE ... <= clause to your query
         */
        public function where_lte($column_name, $value) {
            return $this->_add_simple_where($column_name, '<=', $value);
        }

        /**
         * Add a WHERE ... IN clause to your query
         */
        public function where_in($column_name, $values) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders(count($values));
            return $this->_add_where("{$column_name} IN ({$placeholders})", $values);
        }

        /**
         * Add a WHERE ... NOT IN clause to your query
         */
        public function where_not_in($column_name, $values) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders(count($values));
            return $this->_add_where("{$column_name} NOT IN ({$placeholders})", $values);
        }

        /**
         * Add a WHERE column IS NULL clause to your query
         */
        public function where_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NULL");
        }

        /**
         * Add a WHERE column IS NOT NULL clause to your query
         */
        public function where_not_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NOT NULL");
        }

        /**
         * Add a raw WHERE clause to the query. The clause should
         * contain question mark placeholders, which will be bound
         * to the parameters supplied in the second argument.
         */
        public function where_raw($clause, $parameters=array()) {
            return $this->_add_where($clause, $parameters);
        }

        /**
         * Add a LIMIT to the query
         */
        public function limit($limit) {
            $this->_limit = $limit;
            return $this;
        }

        /**
         * Add an OFFSET to the query
         */
        public function offset($offset) {
            $this->_offset = $offset;
            return $this;
        }

        /**
         * Add an ORDER BY clause to the query
         */
        protected function _add_order_by($column_name, $ordering) {
            $column_name = $this->_quote_identifier($column_name);
            $this->_order_by[] = "{$column_name} {$ordering}";
            return $this;
        }

        /**
         * Add an ORDER BY column DESC clause
         */
        public function order_by_desc($column_name) {
            return $this->_add_order_by($column_name, 'DESC');
        }

        /**
         * Add an ORDER BY column ASC clause
         */
        public function order_by_asc($column_name) {
            return $this->_add_order_by($column_name, 'ASC');
        }

        /**
         * Add a column to the list of columns to GROUP BY
         */
        public function group_by($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            $this->_group_by[] = $column_name;
            return $this;
        }

        /**
         * Build a SELECT statement based on the clauses that have
         * been passed to this instance by chaining method calls.
         */
        protected function _build_select() {
            // If the query is raw, just set the $this->_values to be
            // the raw query parameters and return the raw query
            if ($this->_is_raw_query) {
                $this->_values = $this->_raw_parameters;
                return $this->_raw_query;
            }

            // Build and return the full SELECT statement by concatenating
            // the results of calling each separate builder method.
            return $this->_join_if_not_empty(" ", array(
                $this->_build_select_start(),
                $this->_build_join(),
                $this->_build_where(),
                $this->_build_group_by(),
                $this->_build_order_by(),
                $this->_build_limit(),
                $this->_build_offset(),
            ));
        }
        public function get_sql(){
            return $this->_build_select();
        }
        /**
         * Build the start of the SELECT statement
         */
        protected function _build_select_start() {
            $result_columns = join(', ', $this->_result_columns);

            if ($this->_distinct) {
                $result_columns = 'DISTINCT ' . $result_columns;
            }

            $fragment = "SELECT {$result_columns} FROM " . $this->_quote_identifier($this->_table_name);

            if (!is_null($this->_table_alias)) {
                $fragment .= " " . $this->_quote_identifier($this->_table_alias);
            }
            return $fragment;
        }

        /**
         * Build the JOIN sources
         */
        protected function _build_join() {
            if (count($this->_join_sources) === 0) {
                return '';
            }

            return join(" ", $this->_join_sources);
        }

        /**
         * Build the WHERE clause(s)
         */
        protected function _build_where() {
            // If there are no WHERE clauses, return empty string
            if (count($this->_where_conditions) === 0) {
                return '';
            }

            $where_conditions = array();
            foreach ($this->_where_conditions as $condition) {
                $where_conditions[] = $condition[static::WHERE_FRAGMENT];
                $this->_values = array_merge($this->_values, $condition[static::WHERE_VALUES]);
            }

            return "WHERE " . join(" AND ", $where_conditions);
        }

        /**
         * Build GROUP BY
         */
        protected function _build_group_by() {
            if (count($this->_group_by) === 0) {
                return '';
            }
            return "GROUP BY " . join(", ", $this->_group_by);
        }

        /**
         * Build ORDER BY
         */
        protected function _build_order_by() {
            if (count($this->_order_by) === 0) {
                return '';
            }
            return "ORDER BY " . join(", ", $this->_order_by);
        }

        /**
         * Build LIMIT
         */
        protected function _build_limit() {
            if (!is_null($this->_limit)) {
                return "LIMIT " . $this->_limit;
            }
            return '';
        }

        /**
         * Build OFFSET
         */
        protected function _build_offset() {
            if (!is_null($this->_offset)) {
                return "OFFSET " . $this->_offset;
            }
            return '';
        }

        /**
         * Wrapper around PHP's join function which
         * only adds the pieces if they are not empty.
         */
        protected function _join_if_not_empty($glue, $pieces) {
            $filtered_pieces = array();
            foreach ($pieces as $piece) {
                if (is_string($piece)) {
                    $piece = trim($piece);
                }
                if (!empty($piece)) {
                    $filtered_pieces[] = $piece;
                }
            }
            return join($glue, $filtered_pieces);
        }


        /**
         * Return the raw data wrapped by this ORM
         * instance as an associative array. Column
         * names may optionally be supplied as arguments,
         * if so, only those keys will be returned.
         */
        public function as_array() {
            if (func_num_args() === 0) {
                return $this->_data;
            }
            $args = func_get_args();
            return array_intersect_key($this->_data, array_flip($args));
        }

        /**
         * Return the value of a property of this object (database row)
         * or null if not present.
         */
        public function get($key) {
            return isset($this->_data[$key]) ? $this->_data[$key] : null;
        }

        /**
         * Return the name of the column in the database table which contains
         * the primary key ID of the row.
         */
        protected function _get_id_column_name() {
            if (!is_null($this->_instance_id_column)) {
                return $this->_instance_id_column;
            }
            if (isset(static::$id_column_overrides[$this->_table_name])) {
                return static::$id_column_overrides[$this->_table_name];
            } else {
                return static::$id_column;
            }
        }

        /**
         * Get the primary key ID of this object.
         */
        public function id() {
            return $this->get($this->_get_id_column_name());
        }

        /**
         * Set a property to a particular value on this object.
         * Flags that property as 'dirty' so it will be saved to the
         * database when save() is called.
         */
        public function set($key, $value) {
            $this->_data[$key] = $value;
        }

        
        public function fetch_all_assoc() {
            $statement = $this->build_prepare_execute();
            $statement->setFetchMode(\PDO::FETCH_ASSOC);
            return $this->fetch_all($statement);            
        }

        public function fetch_all_object() {
            $statement = $this->build_prepare_execute();
            $statement->setFetchMode(\PDO::FETCH_OBJ);
            return $this->fetch_all($statement);            
        }

        public function fetch_all_num() {
            $statement = $this->build_prepare_execute();
            $statement->setFetchMode(\PDO::FETCH_NUM);
            return $this->fetch_all($statement);            
        }
        public function fetch_all_class($class) {
            $statement = $this->build_prepare_execute();
            $statement->setFetchMode(\PDO::FETCH_CLASS,$class);
            return $this->fetch_all($statement);            
        }

        public function fetch_all($statement) {
            $rows = array();
            while ($row = $statement->fetch()) {
                $rows[] = $row;
            }
            return $rows;               
        }
        
        public function build_prepare_execute(){
            $query = $this->_build_select();
            //echo $query; print_r($this);
            
            $this->_last_query_sql = $query = $this->replace_prefix($query);
            $statement = $this->use_already_prepared_md5($query);
            $statement->execute($this->_values);
            return $statement;
        }
    
        public function replace_prefix($query) {            
            return  str_replace('#__', static::$prefix, $query);
        }
        
        public function fetch_one_assoc() {
            $this->limit(1);
            $statement = $this->build_prepare_execute();
            $statement->setFetchMode(\PDO::FETCH_ASSOC);
            if($row = $statement->fetch())
                return $row;
        }

        public function fetch_one_obj() {
            $this->limit(1);
            $statement = $this->build_prepare_execute();
            //print_r($statement); exit;
            $statement->setFetchMode(\PDO::FETCH_OBJ);
            //print_r($statement); exit;
            if($row = $statement->fetch())
                return $row;
        }
        
        public function fetch_one_class($class) {
            $this->limit(1);
            $statement = $this->build_prepare_execute();
            //print_r($statement); exit;
            $statement->setFetchMode(\PDO::FETCH_CLASS,$class);
            //print_r($statement); exit;
            if($row = $statement->fetch())
                return $row;
        }

}
