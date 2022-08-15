<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Setting.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Admin_Badge_Setting extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Manage Badge Settings')
            ->setDescription('Here, you can choose to enable/disable badges based on the credits earned by the members on your website.');
    $this->addElement('Radio', 'sescredit_enablebadge', array(
        'label' => 'Enable Credit Badges',
        'description' => 'Do you want to enable badges based on the credit points earned by members of your website?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sescredit.enablebadge', 1)
    ));
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}