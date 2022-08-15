<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: PayPal.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_PayPal extends Engine_Form
{
  public function init()
  {
    parent::init();
    $this->setTitle('Payment Gateway Details');
    $description = $this->getTranslator()->translate('Below, enter your PayPal Account details which will be used for receiving the payments for your tickets ordered on this website.');
    $this->setDescription($description)->setAttrib('id', 'sesevent_ajax_form_submit');
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    // Elements
    $this->addElement('Text', 'username', array(
      'label' => 'API Username',
      'filters' => array(
       new Zend_Filter_StringTrim(),
      ),
    ));
    $this->addElement('Text', 'password', array(
      'label' => 'API Password',
      'filters' => array(
       new Zend_Filter_StringTrim(),
      ),
    ));
    $this->addElement('Text', 'signature', array(
      'label' => 'API Signature',
      'filters' => array(
       new Zend_Filter_StringTrim(),
      ),
    ));
		 // Element: enabled
    $this->addElement('Radio', 'enabled', array(
      'label' => 'Enabled?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'order' => 10000,
    ));
		// Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'decorators' => array('ViewHelper'),
      'order' => 10001,
      'ignore' => true,
    ));
  }
}