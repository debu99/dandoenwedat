<?php

class Dummyusers_Form_Admin_Global extends Engine_Form {
    public function init(){
       
        $this->setMethod('POST');
    
        $this->addElement('Radio', "is_enabled", array(
            'required' => true,
            'multiOptions' => array(
                '0' => "disabled",
                '1' => "enabled"
              ),
            'value' => '0'
        ));
        
    
        $this->addElement('Button', 'dummy_user', array(
            'label' => 'submit',
            'type' => 'submit',
            'ignore' => true,
        ));
        }   
}