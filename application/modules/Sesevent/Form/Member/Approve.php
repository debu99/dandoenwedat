<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Approve.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Member_Approve extends Engine_Form {

  public function init() {
    
    $this
            ->setTitle('Approve Invite Request')
            ->setDescription('Would you like to approve this invite request?')
            ->setMethod('POST')
            ->setAction($_SERVER['REQUEST_URI']);

    //$this->addElement('Hash', 'token');

    $this->addElement('Button', 'submit', array(
        'label' => 'Approve Request',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
        'type' => 'submit'
    ));

    $this->addElement('Cancel', 'cancel', array(
        'prependText' => ' or ',
        'label' => 'cancel',
        'link' => true,
        'href' => '',
        'onclick' => 'parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel'
            ), 'buttons');
  }

}
