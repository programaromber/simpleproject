<?php

class AuthAdapter implements Zend_Auth_Adapter_Interface
    {
        /**
         * Sets username and password for authentication
         *
         * @return void
         */
        public function __construct($username, $password)
        {
            // ...
        }
     
        /**
         * Performs an authentication attempt
         *
         * @throws Zend_Auth_Adapter_Exception If authentication cannot
         *                                     be performed
         * @return Zend_Auth_Result
         */
        public function authenticate()
        {
            // ...
        }
    }

