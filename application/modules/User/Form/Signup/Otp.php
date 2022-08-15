<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Signup_Otp extends Engine_Form {

  public function init() {

    $this->setTitle('Two Step Authentication');

    $this->setAttrib('enctype', 'multipart/form-data')
        ->setAttrib('id', 'SignupForm')
        ->setAttrib('class', 'sesinterest_name');

    $this->addElement('Text', "code", array(
        'label' => 'Enter Verification Code',
        'description' => '',
        'allowEmpty' => false,
        'required' => true,
    ));

    $this->addElement('Hash', 'token');

    $this->addElement('Hidden', 'nextStep', array(
      'order' => 3
    ));

    // Element: done
    $this->addElement('Button', 'done', array(
      'label' => 'Save',
      'type' => 'submit',
      'onclick' => 'javascript:finishForm();',
      'decorators' => array(
      'ViewHelper',
      ),
    ));
  }
}
