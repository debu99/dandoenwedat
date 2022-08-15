<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ParentType.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Admin_Settings_ParentType extends Engine_Form {

  public function init() {
    $this->setMethod('post');
    $this->addElement('select', 'plugin', array(
        'label' => 'Plugin',
		'description' => 'Choose the module in which the activities of selected module will be displayed.',
        'multiOptions' => array(''),
        'description' => '',
        'value' => '',
        'onchange' => 'fetchLevelSettings(this);',
    ));
    $this->addElement('Text', 'title', array(
        'label' => 'Plugin Title for Users',
        'description' => 'Enter the title of this plugin which will be displayed to the users in various widgets of this plugin.',
        'value' => '',
    ));
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
      $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'link' => true,
          'href' => '',
          'prependText' => ' or ',
          'onClick' => 'javascript:parent.Smoothbox.close();',
          'decorators' => array(
              'ViewHelper'
          )
      ));
  }

}
