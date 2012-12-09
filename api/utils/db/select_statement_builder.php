<?php

namespace api\utils\db;
use api;

class select_statement_builder extends api\db\select {

        protected function _add_simple_where($column_name, $separator, $value) {
            $quoted_column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$quoted_column_name} {$separator} :$column_name", $value);
        }
    
}