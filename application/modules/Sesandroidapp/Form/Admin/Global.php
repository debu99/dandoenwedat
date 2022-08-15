<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Global.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesandroidapp_Form_Admin_Global extends Engine_Form {
  public function init() {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
            ->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sesandroidapp_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sesandroidapp.licensekey'),
    ));
    $this->getElement('sesandroidapp_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

		if ($settings->getSetting('sesandroidapp.pluginactivated')) {

      $this->addElement('Text', 'sesandroidapp_server_key', array(
          'label' => 'Android API Key',
          'description' => 'Android API Key will be used to send Push Notifications from your server. So, an API key will be required to enable this service. Here, enter the key. If you are not sure what to enter  , then please contact our support team from here.',
          'value'=>$settings->getSetting('sesandroidapp_server_key'),
      ));
      $this->getElement('sesandroidapp_server_key')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

      $this->addElement('Radio', 'sesandroidapp_disable_welcome', array(
          'label' => 'Disable welcome screen',
          'description' => 'Do you want to disable welcome screen',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
          'value'=>$settings->getSetting('sesandroidapp_disable_welcome', '0'),
      ));
      $this->addElement('Radio', 'sesandroidapp_guest_enable', array(
        'label' => 'Enable "Skip Login"',
        'description' => 'Do you want to allow "Guests" or "Non-Logged In" users to browse your app without login to your site? (If No, then only "Logged In" members will be able to use your app. If Yes, users will see "Skip Login" link to browse and use your app without having to login into your app.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => $settings->getSetting('sesandroidapp.guest.enable', 1),
      ));

      $this->addElement('Radio', 'sesandroidapp_show_titleheader', array(
        'label' => 'Display Site Title or Search in Header',
        'description' => 'Do you want to display the Site Title or the Global Search in header of your app?',
        'multiOptions' => array(
            1 => 'Show Site Title',
            2 => 'Show Global Search',
        ),
        'value' => $settings->getSetting('sesandroidapp_show_titleheader', 2),
      ));

      $this->addElement('Text', 'sesandroidapp_sitetitle', array(
        'label' => 'Site Title in Header',
        'description' => 'Enter the title of the site which you want to show in the header of your app.',
        'value' => $settings->getSetting('sesandroidapp_sitetitle', ''),
      ));

      $this->addElement('Radio', 'sesandroidapp_display_loggedinuserphoto', array(
        'label' => 'Display Logged-in Member’s Photo',
        'description' => 'Do you want to display current logged-in member’s photo in the Top Right corner of your app header after global search or site title? (If you choose Yes, then a small photo will show in circle and clicking on this photo will send users to their member profile page.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => $settings->getSetting('sesandroidapp_display_loggedinuserphoto', 1),
      ));

      $this->addElement('Radio', 'sesandroidapp_headerfixed', array(
        'label' => 'Fix Header on Home Page',
        'description' => 'Do you want to fix the header on the home page of your app? Currently, activity feed is shown by default on the home page, so if you choose Yes, then the header will shown when users scroll down the page. But, if you choose No, then the header will disappear on scrolling down and will reappear on scrolling up.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => $settings->getSetting('sesandroidapp_headerfixed', 0),
      ));

     /* $this->addElement('Radio', 'sesandroidapp_isNavigationTransparent', array(
        'label' => 'Enable transparency in App Navigation Bar',
        'description' => 'Do you want to enable transparency in app navigation bar.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => $settings->getSetting('sesandroidapp_isNavigationTransparent', 1),
      ));*/

      /* Admin - Setting is not working. #756 */
      $this->addElement('Radio', 'sesandroidapp_memberImageShapeIsRound', array(
        'label' => 'Member Avatar Shape',
        'description' => 'Choose from below the shape of the member avatar in Activity Feeds page.',
        'multiOptions' => array(
            1 => 'Circle',
            0 => 'Square',
        ),
        'value' => $settings->getSetting('sesandroidapp_memberImageShapeIsRound', 0),
      ));


       /*$this->addElement('Radio', 'sesandroidapp_enable_tabbedmenu', array(
        'label' => 'Display Dashboard Viewer on Home Page',
        'description' => 'Do you want to display the Dashboard View icon on the home page of your app? If you choose Yes, then the icon will appear in the left side of the header of your app in Home page.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => $settings->getSetting('sesandroidapp_enable_tabbedmenu', 1),
      ));*/

      $this->addElement('Text', 'sesandroidapp_limitForphone', array(
        'label' => 'Content Load Count in Phone',
        'description' => 'Enter the count for the content to be loaded by default in phone. If you enter 10, then 10 feeds will load in activity feeds at once, 10 Photos will load on Browse Photos page, and so on for all modules and pages.',
        'value' => $settings->getSetting('sesandroidapp_limitForphone', '10'),
      ));

      $this->addElement('Text', 'sesandroidapp_limitForTablet', array(
        'label' => 'Content Load Count in Tablet',
        'description' => 'Enter the count for the content to be loaded by default in tablet. If you enter 10, then 10 feeds will load in activity feeds at once, 10 Photos will load on Browse Photos page, and so on for all modules and pages.',
        'value' => $settings->getSetting('sesandroidapp_limitForTablet', '15'),
      ));

      $this->addElement('Radio', 'sesandroidapp_showtabbartitle', array(
        'label' => 'Display Tab Titles under Tab Bar',
        'description' => 'Do you want to display the titles of the tabs: “Activity”, “Requests”, “Notifications” & “Messages” in the Tab bar which comes at the bottom of the app?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => $settings->getSetting('sesandroidapp_showtabbartitle', 1),
      ));
			if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmusic')){
				$this->addElement('Radio', 'sesandroidapp_showyoutube_video_musicapp', array(
					'label' => 'Youtube Video Link',
					'description' => 'Do you want to display the Youtube video in Song of the Music plugin : This video will be show only App?',
					'multiOptions' => array(
							1 => 'Yes',
							0 => 'No',
					),
					'value' => $settings->getSetting('sesandroidapp_showyoutube_video', 1),
				));
			}

       $this->addElement('Text', 'sesandroidapp_shareontext', array(
        'label' => 'Text for “Share On” in Activity Feeds Share.',
        'description' => 'Enter the text to be shown when users share activity feeds on your website for “Share On”. We recommend you to enter the site title or any short name of your site, so that text does not go too long.',
        'value' => $settings->getSetting('sesandroidapp_shareontext', 'SocialEngine'),
      ));

      $this->addElement('Text', 'sesandroidapp_feedtruncationlimit', array(
        'label' => 'Activity Feed Character Limit',
        'description' => 'Enter the character limit after which users will see "more" option in the feeds. After clicking on "more" they will redirect to the Activity Feed View Page.',
        'value' => $settings->getSetting('sesandroidapp_feedtruncationlimit', '200'),
      ));



      /*$this->addElement('Text', 'sesandroidapp_appurl', array(
        'label' => 'App URL for Rating',
        'description' => 'Enter the URL of your app at itunes store where users will be able to give their rating. The Rate Us option will be shown in the Dashboard of your app.',
        'value' => $settings->getSetting('sesandroidapp_appurl', ''),
      ));*/

      /*$this->addElement('Text', 'sesandroidapp_googleapikey', array(
        'label' => 'Google Place API Key',
        'description' => 'Enter the Google Place API key for entering location, check-in and displaying map in your app.',
        'value' => $settings->getSetting('sesandroidapp_googleapikey', ''),
      ));
      */

      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));

    } else {
      //Add submit button
      $this->addElement('Button', 'submit', array(
        'label' => 'Activate Your Plugin',
        'type' => 'submit',
        'ignore' => true
      ));
    }
  }
}
