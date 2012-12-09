<?php

namespace api;

/**
 * This request class replaces the standard php superglobals.
 * This makes it easier for setting up mocks for testing.
 */
/* class */
class request {
	/**
         *
         * @var bool|null
         */
	public static $_reset = null;
        /**
         * Basically $_SERVER
         * @var array 
         */
	public static $_server = null;
        /**
         * Basically $_GET
         * @var array 
         */
	public static $_get = null;
        /**
         * Basically $_POST
         * @var array 
         */
        public static $_post = null;
        /**
         * Basically $_COOKIE
         * @var array 
         */
        public static $_cookie = null;
        /**
         * Basically $_FILES
         * @var array 
         */
        public static $_files = null;
        /**
         * Basically $_ENV
         * @var array 
         */
        public static $_env = null;
	
	public function __construct($_reset = null, $_server, $_get, $_post, $_cookie, $_files, $_env) {
		if(static::$_reset === null || $_reset === true){

			static::$_server = $_server;
			static::$_get = $_get;
			static::$_post = $_post;
			static::$_cookie = $_cookie;
			static::$_files = $_files;
			static::$_env = $_env;			
			static::$_reset = true;
		}
	}
	
	
}