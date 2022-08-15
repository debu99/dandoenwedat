<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: testing.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Form_sesevent_testing extends Engine_Form
{
    public function init(){
         $this->setMethod('post');
         
          $this->addElement('File', 'image', array(
        'label' => 'Upload Icon',
        'description' => 'Upload an icon. (The Recommended dimensions of the icon: 40px * 40px.]'
    ));
           $this->addElement('Button', 'submit', array(
        'label' => 'upload',
        'type' => 'submit',
        'ignore' => true,
        
    ));
           
           
    }
 
}
