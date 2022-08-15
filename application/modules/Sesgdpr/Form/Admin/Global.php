<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Global.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Form_Admin_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sesgdpr_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sesgdpr.licensekey'),
    ));
    $this->getElement('sesgdpr_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    if ($settings->getSetting('sesgdpr.pluginactivated')) {

      $this->addElement('MultiCheckbox', 'gdpr_content', array(
        'label' => 'Consents',
        'description' => 'Choose from below which all consents do you want to enable on your website? The selected consents will be visible to the users of your website in “Privacy Center” widget. You can monitor the requests for each consent in their respective sections of this plugin.',
        'multiOptions'=>array('cookie'=>'Cookies & Privacy Policy Consent','dataProtection'=>'Contact Data Protection Officer','privacySettings'=>'Privacy Settings','requestArchive'=>'Request Archive','unsubscribe'=>'Unsubscribe','forgotMe'=>'Forget Me'),
        'value' => $settings->getSetting('gdpr_content', array('cookie','dataProtection','privacySettings','requestArchive','unsubscribe','forgotMe')),
      ));

      $this->addElement('Radio', 'gdpr_popup', array(
          'label' => 'Display Privacy Policy in Popup',
          'description' => 'Do you want to display the Privacy Policy of your website in popup until user has given consent?',
          'multiOptions' => array('1'=>'Yes','0'=>'No'),
           'value' => $settings->getSetting('gdpr_popup', '0'),
      ));

      $this->addElement('Radio', 'gdpr_madatory_popup', array(
          'label' => 'Make Privacy Policy Consent Mandatory',
          'description' => 'Do you want to make it mandatory for users to Agree to the Privacy policies before using your website? If you choose Yes, then until the users Agree the privacy policy, they will not be able to view and use the website as the popup will not close.',
          'multiOptions' => array('1'=>'Yes','0'=>'No'),
           'value' => $settings->getSetting('gdpr_madatory_popup', '0'),
      ));

      $this->addElement('Textarea', 'sesconsent_bypass_cookie', array(
          'label' => 'Necessary Allowed Cookies',
          'description' => 'This box contains cookies which will be saved as necessary allowed cookies into the browsers of your users even if they have not provided any consent for the same. You can remove and add new cookies as per your requirements in this box.',
          'value' => $settings->getSetting('sesconsent_bypass_cookie', 'en4_maint_code,user_consent,user_consent_date,PHPSESSID,en4_locale,en4_language,user_popup_consent'),
      ));

      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));
    } else {

      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Activate This Plugin',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }

}
