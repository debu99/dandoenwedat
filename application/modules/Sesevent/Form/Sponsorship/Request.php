<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Request.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Sponsorship_Request extends Engine_Form {
 public function init() {
    //get current logged in user
    $user = Engine_Api::_()->user()->getViewer();
    $this->setTitle('Sponsorship Request')
            ->setAttrib('id','sesevent_ppayment_request')
            ->setMethod("POST");
		$this->addElement('Textarea', 'description', array(
          'label' => 'Message',
					'allowEmpty' => false,
					'required' => true,
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