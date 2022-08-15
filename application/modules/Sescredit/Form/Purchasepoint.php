<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Purchasepoint.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Purchasepoint extends Engine_Form {

  public function init() {
    $this->setAttrib('id', 'sescredit_purchase_point')
            ->setMethod('post');
    $this->addElement('Radio', 'sescredit_purchase_type', array(
        'label' => 'Purchase Point',
        'description' => 'Select the type of point purchase',
        'multiOptions' => array(
            1 => 'By Using Site Offers',
            0 => 'Direct'
        ),
        'value' => 0,
    ));

    $this->addElement('Text', 'sescredit_number_point', array(
        'label' => 'Point',
        'allowEmpty' => false,
        'required' => true,
        'autocomplete' => 'off',
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        ),
        'onkeypress' => "return isNumberKey(event)",
    ));
    $this->sescredit_number_point->addValidator('alnum', true);

    $this->addElement('Dummy', 'sescredit_number_point_value', array(
        'description' => Engine_Api::_()->sescredit()->getCurrencyPrice(0, '', '', true),
    ));

    $this->addElement('Hidden', 'gateway_id', array(
    ));

    $this->addElement('Radio', 'sescredit_site_offers', array(
        'label' => 'Available Offers',
        'description' => '',
        'multiOptions' => array(),
        'value' => ''
    ));

    // Element: submit
    $this->addElement('Button', 'submit', array(
        'label' => 'Get Payment Method',
        'onclick' => 'showPaymentOption()',
    ));
    $this->addElement('Button', 'gatewayButton', array(
        'label' => 'Get Payment Method',
        'type' => 'submit'
    ));
  }

}
