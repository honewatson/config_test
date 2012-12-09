<?php

namespace api\form;

class base_fields_for_render {
    
    public $default = array(":class" => "normal", ":value" => "", ":label" => "", ":description" => "" );
    public $form_fields;
    
    public function __construct($form_fields, $span = "clearfix") {
        $this->form_fields = $form_fields;
        $this->default[":span"] = $span;
    }
    
    public function render(){
        foreach($this->form_fields as $field => $values){
            $method = $values['extends'];
            echo $this->$method($values);
        }  
    }
    
    public function hidden($options) {
        $string = '<input type="hidden" name=":id" id=":id" value=":value" />';
        return $this->get_field($options, $string);     
    }
    
    public function text($options) {
        $string = '<div class=":span"><label for=":field">:label</label><div class="input"><input class=":class" type="text" name=":field" id=":field" value=":value" />:description</div></div>';
        return $this->get_field($options, $string);     
    }  
    
    public function textarea($options) {
        $string = '<div class=":span"><label for=":field">:label</label><div class="input"><textarea class=":class" name=":field" id=":field">:value</textarea>:description</div></div>';
        return $this->get_field($options, $string);     
    }    

    public function submit($options){
        $string = '<div class="actions"><input type="submit" value=":value" class=":class" /></div>';
        return $this->get_field($options, $string);
    }
        
    public function get_field($options, $string){
        $options = array_merge($this->default, $options);
        unset($options["extends"]);
        if($options[':description'])
            $options[':description'] = $this->description($options[':description']);
        return str_replace(array_keys($options), array_values($options), $string);
    }
    
    public function description($description){
        return '<span class="help-block">'.$description.'</span>';
    }    
    

    
    public function get_field_custom($options, $string){
        $options = array_merge($options, $this->default);
        unset($options["extend"]);
        return str_replace(array_values($options), array_keys($options), $string);
    }
}