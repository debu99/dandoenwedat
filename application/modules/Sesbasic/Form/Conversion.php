<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Conversion.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Form_Conversion extends Engine_Form {

	 public function init() {

    $defaultCurrency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency','USD');

		$this->setTitle('Convert to '.$defaultCurrency)
					->setAttrib('id', 'sesbasic_currency_converter')
					->setDescription('Below choose your currency from the drop and enter the price, then click on Convert button to get the price in '.$defaultCurrency.'.')
					->setMethod("POST")
					->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

		//Event Currency
		$this->addElement('Select', 'currency', array(
		 		'label'=>'Choose currency',
        'multiOptions' => array($defaultCurrency => $defaultCurrency),
    ));
		// Event Contact Phone
    $this->addElement('Text', 'main_price', array(
        'label' => 'Enter the price to be converted.',
    ));
		// Event Contact Facebook
    $this->addElement('Text', 'converter_price', array(
        'label' => 'Converted price',
    ));
		$this->addElement('Button', 'submit', array(
        'label' => 'Convert',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
				'onclick'=>'javascript:parent.Smoothbox.close()',
        'prependText' => ' or ',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
		$this->addElement('Dummy','loading-img', array(
        'content' => '<div class="sesbasic_loading_cont_overlay" id="sesbasic_loading_cont_overlay_con" style="display:none;"></div>',
        'decorators' => array(
            'ViewHelper',
        ),
   ));
	 }
}
