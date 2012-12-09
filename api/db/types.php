<?php

namespace api\db;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class types {
    
    public function string($value){
        try {
           $value = (string)$value; 
        }
        catch (Exception $e){
            echo $e->getMessage();
            return false;
        }
            
    }

    public function int($value){
        try {
           $value = (int)$value; 
        }
        catch (Exception $e){
            echo $e->getMessage();
            return false;
        }
            
    }

}
