<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Edit.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Photo_Edit extends Engine_Form {

  public function init() {

    $this->setTitle('Edit Event Photo');

    $this->addElement('Text', 'title', array(
        'label' => 'Title',
        'filters' => array(
            new Engine_Filter_Censor(),
        ),
    ));

    $this->addElement('Textarea', 'description', array(
        'label' => 'Description',
    ));

    $this->addElement('Button', 'submit', array(
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
        'label' => 'Save Changes',
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
