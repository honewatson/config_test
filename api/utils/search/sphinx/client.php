<?php

namespace api\utils\search\sphinx;

use api;

class client {

    protected $_search_client = NULL;
    protected $_offset = NULL;
    protected $_limit = NULL;
    
    public function __construct($client_factory = "api\utils\search\sphinx\client_factory"){
	$this->_search_client = $client_factory::build();
    }

    public function prepare_query($query=NULL){
        if ($query === NULL)
            $query = $_GET['query'];
        if(is_array($query))
                $query = implode(' ', $query); 
          
        $query = " ".preg_replace("/ +/", " ",preg_replace('/[^A-Za-z0-9 ]/', '', strtolower(str_replace(array("\n", "\t", "\r"), array(" ", " ", " "), strip_tags($query))))). " ";
         
        $n = array(' the ', ' of ', ' and ', ' a ', ' to ', ' in ', ' is ', ' that ', ' it ', ' for ', ' was ', ' on ', ' are ', ' as ', ' with ', ' his ', ' they ', ' at ', ' be ', ' this ', ' from ', ' i ', ' have ', ' or ', ' by ', ' had ', ' not ', ' but ', ' what ', ' all ', ' were ', ' when ', ' we ', ' there ', ' can ', ' an ', ' which ', ' their ', ' said ', ' if ', ' do ', ' will ', ' each ', ' them ', ' then ', ' many ', ' some ', ' so ', ' these ', ' would ', ' other ', ' into ', ' has ', ' more ', ' two ', ' like ', ' see ', ' could ', ' no ', ' than ', ' been ', ' its ', ' now ', ' my ', ' over ', ' did ', ' down ', ' only ', ' way ', ' use ', ' may ', ' long ', ' very ', ' after ', ' called ', ' just ', ' where ', ' know ', ' theyre ', ' its ', ' you ', ' your ', ' how ', ' any ', ' own '); 
          
        $query = preg_replace("/ +/", " ",str_replace($n, " ", $query));
        return $query;

    }
    
    public function search($query=NULL){

        $query = $this->prepare_query($query);

        $result = $this->_search_client->query($query);

        return $result;

    }
    
    
    public function set_offset($_offset){
        $this->_offset = $_offset;
    }
    
    public function offset(){
        if($this->_offset === NULL) {
            if(isset($_GET['offset']))
                $this->_offset = $_GET['offset'];
            else
                $this->_offset = 0;
        }
        return $this->_offset;
    }


    public function set_limit($_limit){
        $this->_limit = $_limit;
    }
    
    public function limit(){
        if($this->_limit === NULL) {
            if(isset($_GET['limit']))
                $this->_limit = $_GET['limit'];
            else
                $this->_limit = 10;
        }
        return $this->_limit;
    }


}