<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Global.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sescredit_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Global Settings')->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sescredit_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sescredit.licensekey'),
    ));
    $this->getElement('sescredit_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    if ($settings->getSetting('sescredit.pluginactivated')) {
      $this->addElement('Text', 'sescredit_manifest', array(
          'label' => 'Plural Text for "credits" in URL',
          'description' => 'Enter the text which you want to show in place of "credits" in the URLs of this plugin.',
          'allowEmpty' => false,
          'required' => true,
          'value' => $settings->getSetting('sescredit.manifest', 'credits'),
      ));
      $this->addElement('Text', 'sescredit_text_singular', array(
          'label' => 'Singular Text for "Credit"',
          'description' => 'Enter the text which you want to show in place of "Credit" at various places in this plugin.',
          'allowEmpty' => false,
          'required' => true,
          'value' => $settings->getSetting('sescredit.text.singular', 'credit'),
      ));
      $this->addElement('Text', 'sescredit_text_plural', array(
          'label' => 'Plural Text for "Credits"',
          'description' => 'Enter the text which you want to show in place of "Credits" at various places in this plugin like search form, navigation menu, etc.',
          'allowEmpty' => false,
          'required' => true,
          'value' => $settings->getSetting('sescredit.text.plural', 'credits'),
      ));
      $price = Engine_Api::_()->sescredit()->getCurrencyPrice('1', '', '', true);
      $this->addElement('Text', 'sescredit_creditvalue', array(
          'label' => 'Credit Points Value in USD',
          'description' => "Enter the value of credit points in USD for $price? (For example. $price = 1000 points)",
          'allowEmpty' => false,
          'required' => true,
          'value' => $settings->getSetting('sescredit.creditvalue', '1000'),
      ));
      $this->addElement('Radio', 'sescredit_badge_type', array(
          'label' => 'Badge Type',
          'description' => 'Choose from below options when your users will get badge for their earned credit points.',
          'multiOptions' => array(
              1 => 'Total Point',
              0 => 'Current Point'
          ),
          'value' => $settings->getSetting('sescredit.badge.type', 1)
      ));
      $this->addElement('Radio', 'sescredit_endtime', array(
          'label' => 'Credits Points Expiry Duration',
          'description' => 'Do you want to set an expiry duration on the credit points earned by members on your website? If Yes, then all the credit points will be set to 0 once the expiry duration is reached. If you do not want to set any expiry date, then by default all credit points will be reset after 16 years.',
          'multiOptions' => array(
              1 => 'No, do not set any expiry duration.',
              0 => 'Yes, set expiry after a time interval.'
          ),
          'value' => $settings->getSetting('sescredit_endtime', 1)
      ));
      $this->addElement('dummy', 'credit_result_datetimes', array(
          'decorators' => array(array('ViewScript', array(
                      'viewScript' => 'application/modules/Sescredit/views/scripts/_customdates.tpl',
                      'class' => 'form element',
                  )))
      ));
      $this->addElement('Radio', 'sescredit_affiliateforsingup', array(
          'label' => 'Enable Signup Invitation Referrals',
          'description' => 'Do you want to enable referrals when members send invites for joining your website. If you choose Yes, then members of your website will be able to send affiliate links. You can set the referral points credits from Member Level Settings of this plugin.',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sescredit.affiliateforsingup', 1)
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
          'label' => 'Activate your plugin',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }

}
