<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: SendPoint.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_SendPoint extends Engine_Form {

  public function init() {
    $this->setAttrib('id', 'sescredit_send_point_friend')
            ->setMethod('post');
    $this->addElement('Text', 'friend_name_search', array(
        'label' => 'Type Friend Name',
    ));
    $this->addElement('Text', 'send_credit_value', array(
        'label' => 'Point',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Hidden', 'friend_user_id', array());
    $this->addElement('Textarea', 'friend_message', array(
        'label' => 'Message',
        'rows' => '4'
    ));
    // Element: submit
    $this->addElement('Button', 'submit', array(
        'label' => 'Send Point',
        'type' => 'submit',
    ));
  }

}
