<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Global.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
        ->setTitle('Global Settings')
        ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sespwa_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sespwa.licensekey'),
    ));
    $this->getElement('sespwa_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    if ($settings->getSetting('sespwa.pluginactivated')) {

        $this->addElement('Radio', 'sespwa_enablepwamode', array(
            'label' => 'Enable PWA Mode',
            'description' => 'Do you want to enable PWA mode for your website? If you want to test the layout and setup of your website using this plugin, then you can append "?pwa=1" at the end of your website domain. to exit the PWA mode append "?pwa=0" at the end of your website domain.',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $settings->getSetting('sespwa.enablepwamode', 1),
        ));

        $this->addElement('Text', "sespwa_logositetext", array(
            'label' => 'Site Title for PWA',
			'description' => 'Enter the Site Title for the Progressive Web App of your website. You can enter this title different from the title on your full website.',
            'allowEmpty' => false,
            'required' => true,
            'value' => $settings->getSetting('sespwa.logositetext', $settings->getSetting('core.general.site.title', '')),
        ));

        $this->addElement('Radio', 'sespwa_showleftright', array(
            'label' => 'Show Left / Right Column',
            'description' => 'Do you want to show left / right columns when users view your website in PWA mode? If you want to give a complete app like look to your Progressive web app, then it is recommended to hide the left/right columns of your website in PWA mode.',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => $settings->getSetting('sespwa.showleftright', 0),
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
