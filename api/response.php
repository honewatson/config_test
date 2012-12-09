<?php

namespace api;
/* class */
class response {
	/**
         * Method for sending headers
         */
	public function send_headers(){}
	/**
         * Prints your rendered view
         * @param api\view::render() $view 
         */
	public function send($view){
		$this->send_headers();
		if($view !== null)
			echo $view;
	}	
}

class response_200 extends response {}