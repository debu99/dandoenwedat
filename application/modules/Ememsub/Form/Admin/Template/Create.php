<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Create.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Admin_Template_Create extends Engine_Form {

  public function init() { 
		$this->setTitle('Add New Template');
				
    $this->addElement('Text', 'title', array(
        'label' => 'Template Name',
        'description' => 'Enter the name of this template. This name is for your indication only and will not be shown at the user side.',
        'allowEmpty' => false,
        'required' => true,
    ));
    $this->addElement('Text', 'body_container_clr', array(
      'label' => 'Table Container Background Color',
      'class' => 'SEScolor',
    ));
    $this->addElement('Text', 'header_bgclr', array(
      'label' => 'Table Header Background Color',
      'class' => 'SEScolor',
    ));
    $this->addElement('Text', 'header_txtclr', array(
      'label' => 'Table Header Text Color',
      'class' => 'SEScolor',
    ));
    $this->addElement('Radio', 'overlap', array(
      'label' => 'Overlap Table over Header',
      'description'=>'Do you want to overlap the subscription table content on the header of it?',
      'multiOptions'=>array('1'=>'YES','0'=>'NO'),
      'value'=> 1
    ));
    //Add submit button
    $this->addElement('Button', 'save', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
     $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => '',
        'onClick' => 'javascript:parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('save', 'cancel'), 'buttons');
  }
}
