<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Paymentrequest.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Sponsorship_Paymentrequest extends Engine_Form {
 public function init() {
    //get current logged in user
    $user = Engine_Api::_()->user()->getViewer();
    $this->setTitle('Payment Request')
            ->setAttrib('id','sesevent_ppayment_request')
            ->setMethod("POST");
		
		$this->addElement('Text', 'total_amount', array(
          'label' => 'Total Amount',
					'readonly'=>'readonly',
    ));
		$this->addElement('Text', 'total_commission_amount', array(
          'label' => 'Total Commission Amount',
					'readonly'=>'readonly',
    ));
		$this->addElement('Text', 'remaining_amount', array(
          'label' => 'Total Remaining Amount',
					'readonly'=>'readonly',
    ));
		$this->addElement('Text', 'requested_amount', array(
          'label' => 'Requested Amount',
					'allowEmpty' => false,
					'required' => true,
					'validators' => array(
								array('GreaterThan', true, array(0)),
						)
    ));
		$this->addElement('Textarea', 'user_message', array(
          'label' => 'Message',
    ));
		$this->addElement('Button', 'submit', array(
        'label' => 'Send',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
				'onclick'=>'parent.Smoothbox.close();',
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
 }
}