<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Filter.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
class Sesgdpr_Form_Admin_Filter extends Engine_Form {

  public function init() {

    $this->clearDecorators()
            ->addDecorator('FormElements')
            ->addDecorator('Form')
            ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
            ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));
    $this->setAttribs(array('id' => 'filter_form', 'class' => 'global_form_box'))->setMethod('GET');
            
      
      $this->addElement('Text', 'name', array(
					'label' => 'Name',
          'description' => '',
           'value' => '',
			));
      $this->addElement('Text', 'email', array(
					'label' => 'Email',
          'description' => '',
           'value' => '',
			));
			// Add submit button
			$this->addElement('Button', 'submit', array(
					'label' => 'Search',
					'type' => 'submit',
					'ignore' => true
			));
    $params = array();
    foreach (array_keys($this->getValues()) as $key) {
      $params[$key] = null;
    }
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble($params));
  }

}
