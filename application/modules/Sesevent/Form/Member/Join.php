<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Join.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Member_Join extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Join Event')
            ->setDescription('Would you like to join this event?')
            ->setMethod('POST')
            ->setAction($_SERVER['REQUEST_URI']);

    $this->addElement('Radio', 'rsvp', array(
        'required' => true,
        'allowEmpty' => false,
        'multiOptions' => array(
            2 => 'Attending',
            // 1 => 'Maybe Attending',
            0 => 'Not Attending',
        //3 => 'Awaiting Reply',
        ),
        'value' => 2,
    ));

    //$this->addElement('Hash', 'token');

    $this->addElement('Button', 'submit', array(
        'label' => 'Join Event',
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
