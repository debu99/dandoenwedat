<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: EditCurrency.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Settings_EditCurrency extends Engine_Form {
	
  public function init() {
   
    $this->setTitle('Edit Currency Rate')
            ->setAttrib('name', 'currencyrate_edit');

    $this->addElement('Text', 'currency_rate', array(
        'label' => 'Currency Rate',
        'allowEmpty' => false,
        'required' => true,
    ));
			$this->addElement('Text', 'currency_symbol', array(
        'label' => 'currency symbol',
				'id' =>'currency_symbol',
    ));
    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Add',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onClick'=> 'javascript:parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
		
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }

}
