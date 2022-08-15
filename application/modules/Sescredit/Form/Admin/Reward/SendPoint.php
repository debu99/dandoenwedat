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

class Sescredit_Form_Admin_Reward_SendPoint extends Engine_Form {

  public function init() {

    $this->setTitle('Send Credit Points')->setMethod('post')->setDescription('Use the form below to send credit points to members on your website.');
    $this->addElement('Radio', 'member_type', array(
        'label' => "Choose Members",
		'description' => "Choose the members to whom you want to send credits.",
        'required' => true,
        'multiOptions' => array("0" => 'All Members', "1" => "Specific Member", "2" => "Specific Member Levels"),
       'value' => 0,
    ));
    $this->addElement('Text', 'sescredit_specific_member', array(
        'label' => 'Member Name',
		'description' => 'Enter member name in the auto-suggest below.',
        'placeholder'=> 'Start typing the name of member.'
    ));
    $this->addElement('Hidden', 'sescredit_user_id', array());
    $levelOptions = array();
    $levelValues = array();
    foreach (Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level) {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      if ($level_id == $level->level_id || ($level->level_id == 1))
        continue;
      $levelOptions[$level->level_id] = $level->getTitle();
      $levelValues[] = $level->level_id;
    }
    $this->addElement('select', 'member_level', array(
        'label' => 'Member Level',
		'description' => 'Choose the member level, members belonging to which will receive the credit points.',
        'multiOptions' => $levelOptions,
        'value' => $levelValues,
    ));
    $this->addElement('Text', 'point', array(
        'label' => 'Credit Points Count',
        'description' => 'Enter number of Credit Points to send.',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Textarea', 'send_reason', array(
        'label' => 'Reason',
        'description' => 'Enter the reason for sending the points.',
        'allowEmpty' => false,
        'required' => true,
    ));
    $this->addElement('Radio', 'send_email', array(
        'label' => 'Send Email?',
		'description' => 'Do you want to send email to receivers?',
        'multiOptions' => array('1' => 'Yes','0' => 'No'),
        'value' => 1,
    ));
    $this->addElement('Textarea', 'email_message', array(
        'description' => 'Enter the message body which will be send to the members in their emails. You can edit the other content of email from “Mail Templates” section.',
        'label' => 'Email Message',
    ));
    // Add submit button
    $this->addElement('Button', 'save', array(
        'label' => 'Send Point',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'send-points')),
        'onClick' => 'javascript:parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('save', 'cancel'), 'buttons');
  }

}
