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
class Sesevent_Form_Member_Request extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Request Event Membership')
            ->setDescription('Would you like to request membership in this Event?')
            ->setMethod('POST')
            ->setAction($_SERVER['REQUEST_URI']);

    $this->addElement('Button', 'submit', array(
        'label' => 'Send Request',
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
