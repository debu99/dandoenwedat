<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: General.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Form_Admin_Settings_General extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sessociallogin_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sessociallogin.licensekey'),
    ));
    $this->getElement('sessociallogin_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    if ($settings->getSetting('sessociallogin.pluginactivated')) {

      $sesSite = '<a href="https://www.socialenginesolutions.com/socialengine-category/themes/" target="_blank">https://www.socialenginesolutions.com/socialengine-category/themes/</a>';
      $descriptionLicense = sprintf('Choose the button designs to be shown in login / signup pop-ups which displays in themes from SocialEngineSolutions. (You can explore the themes which we have from here: %s.', $sesSite);
      $this->addElement('Radio', "sessociallogin_button_designs", array(
        'label' => 'Button Design in Login / Signup Popups',
        'description' => $descriptionLicense,
        'allowEmpty' => true,
        'required' => false,
        'multiOptions' => array(1 => 'Buttons with Icons Only', '0' => 'Buttons with Icons and Text Both'),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.button.designs', 0),
      ));
      $this->getElement('sessociallogin_button_designs')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));
    } else {
      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Activate your plugin',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }

}
