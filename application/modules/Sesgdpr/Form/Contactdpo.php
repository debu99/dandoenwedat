<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Contactdpo.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Form_Contactdpo extends Engine_Form
{
  public function init()
  {

    // Init form
    $this->setTitle('');
    $this->setDescription("");
    $this->setAttrib('id', 'sesgdpr_contactdpo');
		$this->setAttrib('class', 'sesgdpr_contactdpo');
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    
    $name = Zend_Registry::get('Zend_Translate')->_('First Name');
    // Init password
    $this->addElement('Text', 'first_name', array(
      'label' => $name,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    $description = Zend_Registry::get('Zend_Translate')->_('Last Name');
    // Init password
    $this->addElement('Text', 'last_name', array(
      'label' => $description,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    )); 

		$email = Zend_Registry::get('Zend_Translate')->_('Email');
    // Init password
    $this->addElement('Text', 'email', array(
      'label' => $email,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));   
    $message = Zend_Registry::get('Zend_Translate')->_('Message');
    $this->addElement('Textarea', 'message', array(
      'label' => $message,
      'required' => true,
      'allowEmpty' => false,
			'rows' => '3',
      'filters' => array(
        'StringTrim',
      ),
    )); 
    $this->addElement('Hidden', 'type', array()); 
		

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Submit',
      'type' => 'submit',
      'ignore' => true,
    ));
    // Set default action
  }
}
