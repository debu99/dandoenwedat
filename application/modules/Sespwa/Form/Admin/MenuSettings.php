<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: MenuSettings.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Form_Admin_MenuSettings extends Engine_Form {

  public function init() {

        $settings = Engine_Api::_()->getApi('settings', 'core');

        $this->setTitle('Manage Header Settings')
                ->setDescription('Here, you can configure the settings for the Header, Main and Mini navigation menus of your website. Below, you can choose to place the Main Navigation menu vertically or horizontally.');

        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

        //New File System Code
        $banner_options = array('' => '');
        $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
        foreach( $files as $file ) {
          $banner_options[$file->storage_path] = $file->name;
        }
        $fileLink = $view->baseUrl() . '/admin/files/';
        $this->addElement('Select', 'sespwa_logo', array(
            'label' => 'Logo in Header',
            'description' => 'Choose from below the logo image for the header of your website. [Note: You can add a new photo from the "File & Media Manager" section from here: <a href="' . $fileLink . '" target="_blank">File & Media Manager</a>.]',
            'multiOptions' => $banner_options,
            'escape' => false,
            'value' => $settings->getSetting('sespwa.logo', ''),
        ));
        $this->sespwa_logo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));


        $this->addElement('MultiCheckbox', 'sespwa_header_loggedin_options', array(
            'label' => 'Header Options for Logged In Members',
            'description' => 'Choose from below the options to be available in header to the logged in members on your website.',
            'multiOptions' => array(
                'search' => 'Search',
                'miniMenu' => 'Mini Menu',
                'mainMenu' =>'Main Menu',
                'logo' =>'Logo',
            ),
            'value' => $settings->getSetting('sespwa.header.loggedin.options',array('search','miniMenu','mainMenu','logo')),
        ));

        $this->addElement('MultiCheckbox', 'sespwa_header_nonloggedin_options', array(
            'label' => 'Header Options for Non-Logged In Members',
            'description' => 'Choose from below the options to be available in header to the non-logged in members on your website.',
            'multiOptions' => array(
                'search' => 'Search Bar',
                'miniMenu' => 'Mini Menu Items',
                'mainMenu' =>'Main Menu Items',
                'logo' =>'Website Logo',
            ),
            'value' => $settings->getSetting('sespwa.header.nonloggedin.options', array('search','miniMenu','mainMenu','logo')),
        ));

        $this->addElement('Select', 'sespwa_menuinformation_img', array(
            'label' => 'Background Image for User in Main Menu',
            'description' => 'Choose from below the background image for the user section in Main Menu. [Note: You can add a new photo from the "File & Media Manager" section from here: <a href="' . $fileLink . '" target="_blank">File & Media Manager</a>.]',
            'multiOptions' => $banner_options,
            'escape' => false,
            'value' => $settings->getSetting('sespwa.menuinformation.img', ''),
        ));
        $this->sespwa_menuinformation_img->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

        $this->addElement('Select', 'sespwa_menu_img', array(
            'label' => 'Background Image for Menu Items in Main Menu',
            'description' => 'Choose from below the background image for the menu section in Main Menu. [Note: You can add a new photo from the "File & Media Manager" section from here: <a href="' . $fileLink . '" target="_blank">File & Media Manager</a>.]',
            'multiOptions' => $banner_options,
            'escape' => false,
            'value' => $settings->getSetting('sespwa.menu.img', ''),
        ));
        $this->sespwa_menu_img->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));


        $this->addElement('Select', 'sespwa_accountsetting', array(
            'label' => 'Show Account Settings',
            'description' => 'Show Account Settings',
            'multiOptions' => array(
                '1' => 'Yes',
                '0' => 'No',
            ),
            'value' =>  $settings->getSetting('sespwa.accountsetting', 1),
        ));

        $this->addElement('Select', 'sespwa_footer', array(
            'label' => 'Show Footer',
            'description' => 'Do you want to show footer?',
            'multiOptions' => array(
                '1' => 'Yes',
                '0' => 'No',
            ),
            'value' =>  $settings->getSetting('sespwa.footer', 1),
        ));

        $this->addElement('Select', 'sespwa_fotrsocialshare', array(
            'label' => 'Show Social Share in Footer',
            'description' => 'Show Social Share in Footer',
            'multiOptions' => array(
                '1' => 'Yes',
                '0' => 'No',
            ),
            'value' =>  $settings->getSetting('sespwa.fotrsocialshare', 1),
        ));

        // Add submit button
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
  }

}
