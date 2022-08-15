<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Admin_Offer_Create extends Engine_Form {

  public function init() {

    $this->setTitle('Create New Offer')->setMethod('post')->setDescription('Here, you can create new offer to sell credit points on your website.');
    $price = Engine_Api::_()->sescredit()->getCurrencySymbol();
    $this->addElement('Text', 'point_value', array(
        'label' => "Value in $price",
        'description' => 'Enter the price (value) in $ which users have to pay to purchase the credits from your site.',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Text', 'point', array(
        'label' => 'Credit Points',
        'description' => 'Enter the number of credit points which users will purchase from your site.',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Text', 'limit_offer', array(
        'label' => 'Offer Usage limit',
        'description' => 'Enter total quantity of this offer which users on your website can avail.',
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Text', 'user_avail', array(
        'label' => 'Usage Limit Per User',
        'description' => 'Enter the number of times this offer can be used by individual user on your website. ',
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Radio', 'offer_time', array(
        'label' => 'Offer Duration',
		'description' => 'Do you want to set an expiry duration for this offer on your website? If you choose Yes, then you can also choose a custom start date for this offer.',
         'multiOptions' => array(
              1 => 'Yes, set expiry after a time interval.',
              0 => 'No, do not set any expiry duration.'
          ),
        'value' => 1,
    ));
    if (isset($_GET['starttime']) && isset($_GET['endtime']))
      $dateRange = $_GET['starttime'] . '-' . $_GET['endtime'];
    else
      $dateRange = '';
    $this->addElement('Text', 'show_date_field', array(
        'label' => 'Date Range',
		'description' => 'Choose a date range during which this offer will be available on your website.',
        'value' => $dateRange,
    ));
    $this->addElement('Checkbox', 'enable', array(
        'label' => 'Yes, enable this offer now.',
        'description' => 'Enable This offer'
    ));
    // Add submit button
    $this->addElement('Button', 'save', array(
        'label' => 'Create',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index')),
        'onClick' => 'javascript:parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('save', 'cancel'), 'buttons');
  }

}
