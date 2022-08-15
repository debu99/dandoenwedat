<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: General.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesmembershipswitch_Form_Admin_Settings_General extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Global Settings')
        ->setDescription('These settings affect all members in your community.');
            
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sesmembershipswitch_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sesmembershipswitch.licensekey'),
    ));
    $this->getElement('sesmembershipswitch_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    if ($settings->getSetting('sesmembershipswitch.pluginactivated')) {

      $this->addElement('Radio', 'sesmembershipswitch_enable', array(
            'label' => 'Enable Switching of Membership Plans',
            'description' => 'Do you want to enable switching of membership plans on your website from this plugin? If you choose No, then SocialEngine\'s default functionality will work. ',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $settings->getSetting('sesmembershipswitch.enable', 1),
        ));

      $this->addElement('Radio', 'sesmembershipswitch_changelevelmail_enable', array(
            'label' => 'Send Email',
            'description' => 'Do you want to send emails to users when their membership plans (in Free Plan) or member levels (in Paid Plan) are changed?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $settings->getSetting('sesmembershipswitch.changelevelmail.enable', 1),
        ));

       $this->addElement('Radio', 'sesmembershipswitch_changelevelnotification_enable', array(
          'label' => 'Send Notification',
          'description' => 'Do you want to send notifications to users when their membership plans (in Free Plan) or member levels (in Paid Plan) are changed?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sesmembershipswitch.changelevelnotification.enable', 1),
      ));

      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Settings',
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
