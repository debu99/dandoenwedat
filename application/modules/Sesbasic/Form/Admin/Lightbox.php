<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Lightbox.php 2015-10-11 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Form_Admin_Lightbox extends Engine_Form {
  public function init() {
		
    $settings = Engine_Api::_()->getApi('settings', 'core');
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $this
            ->setTitle('Lightbox Viewer Settings')
						->setDescription(' .. ');
		
   $this->addElement('Radio', 'sesbasic_enable_lightbox', array(
        'label' => 'Open Videos in Lightbox',
        'description' => 'Do you want to open videos in Lightbox Viewer? [You can choose the type of the lightbox viewer to be opened for members depending on their member levels from the <a target="_blank" href="' . $view->baseUrl() . "/admin/sesbasic/lightbox/index#imageviewer-wrapper" . '">Member Level Settings</a>.]',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesbasic.enable.lightbox', 1),
    ));
    
    $this->addElement('Radio', 'sesbasic_show_information', array(
      'label' => 'Show Information in Advanced Lightbox',
      'description' => 'Do you want to show the information when advanced lightbox is opened? (If you select Yes, then the information window available in the right side of the advanced lightbox will be shown by default. If you select No, then the information will show when users will click on the Info icon (i) available in the top right corner of the lightbox.)',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.show.information', 1),
    ));

    $this->sesbasic_enable_lightbox->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    //New File System Code
    $banner_options = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
    foreach( $files as $file ) {
      $banner_options[$file->storage_path] = $file->name;
    }

    $fileLink = $view->baseUrl() . '/admin/files/';
    if (count($banner_options) > 0) {
      $this->addElement('Select', 'sesbasic_private_photo', array(
          'label' => 'Private Photo for Private Videos',
          'description' => 'Choose below the photo to be shown for a private video when the video is shown in video lightbox. When a user upload a video and restrict its visibility to friend or network, then also that video is showed in Activity Feed and certain widgets and browse pages. Now, the chosen photo from below will be shown for such private videos to unauthorized users. [Note: You can add a new photo from the <a target="_blank" href="' . $fileLink . '">File & Media Manager</a> section from here: File & Media Manager.]',
          'multiOptions' => $banner_options,
          'value' => $settings->getSetting('sesbasic.private.photo'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for private. Photo to be chosen for private photo should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the private photo. Please upload the Photo to be chosen for private photo from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manage</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesbasic_private_photo', array(
          'label' => 'Photo instead of Private Photo',
          'description' => $description,
      ));
    }
    $this->sesbasic_private_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Text', 'sesbasic_title_truncate', array(
        'label' => 'Title Truncation Limit of Videos',
        'description' => 'Enter the title truncation limit of the video when shown lightbox viewer.',
        'value' => $settings->getSetting('sesbasic.title.truncate', 45),
    ));
    $this->addElement('Text', 'sesbasic_description_truncate', array(
        'label' => 'Description Truncation Limit of Videos',
        'description' => 'Enter the description truncation limit of the videos when shown in lightbox.',
        'value' => $settings->getSetting('sesbasic.description.truncate', 45),
    ));
    $this->addElement('Dummy', 'dummy', array(
        'content' => 'Choose from below the options to be available in the lightbox viewer for videos.',
    ));

    $this->addElement('Radio', 'sesbasic_add_delete', array(
        'label' => 'Delete',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesbasic.add.delete', 1),
    ));

    $this->addElement('Radio', 'sesbasic_add_share', array(
        'label' => 'Share',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesbasic.add.share', 1),
    ));

    $this->addElement('Radio', 'sesbasic_add_report', array(
        'label' => 'Report',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesbasic.add.report', 1),
    ));
    
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$link = '<a href="https://www.socialenginesolutions.com/social-engine/advanced-share-plugin/" target="_blank">Advanced Sharing Plugin – Inside and Outside Site Sharing</a>';
		$description = sprintf('Do you want to allow your users to share videos from the video lightbox viewer on your website to other social networking websites? [Note: Only Facebook, Twitter & Pinterest options are available in Lightbox Viewer.] (If you want to enable social sharing on multiple other social networking websites (Facebook, Twitter, Whatsapp, Skype, and many more …), then you should have our "%s" plugin installed and activated on your website.)',$link);

    $this->addElement('Radio', 'sesbasic_enablesessocialshare', array(
      'label' => 'Enable Social Sharing',
      'description' => $description,
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'onchange' => "enablesessocialshare(this.value)",
      'value' => $settings->getSetting('sesbasic.enablesessocialshare', 1),
    ));
    $this->getElement('sesbasic_enablesessocialshare')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    
    $this->addElement('Text', "sesbasic_iconlimit", array(
        'label' => "Count For Social Sites To Show",
        'description' => 'Enter the number of social networking sites to be shown while sharing the videos. (If you enable More Icon, then other social site icons will display on clicking the more icon. (Works only if you have advanced social sharing.)',
        'value' => $settings->getSetting('sesbasic.iconlimit', 3),
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    
    $this->addElement('Select', "sesbasic_enableplusicon", array(
      'label' => "Show More Icon",
      'description' => 'Do you want to enable More icon to view all social networking sites’ share icons? (Works only if you have advanced social sharing.)',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => $settings->getSetting('sesbasic.enableplusicon', 1),
    ));

    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}
