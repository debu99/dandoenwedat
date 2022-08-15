<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesalbum
 * @package    Sesalbum
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Lightbox.php 2015-06-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Form_Admin_PhotoLightbox extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->setTitle('Lightbox Viewer Settings');
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null; 
    $this->addElement('Radio', 'sesalbum_enable_lightbox', array(
        'label' => 'Open Photos in Lightbox',
        'description' => 'Do you want to open photos in Lightbox Viewer? [You can choose the type of the lightbox viewer to be opened for members depending on their member levels from the <a target="_blank" href="'.$view->baseUrl() . "/admin/sesalbum/level".'">Member Level Settings</a>.]',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesalbum.enable.lightbox', 1),
    ));
    $this->sesalbum_enable_lightbox->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $this->addElement('Hidden', 'sesalbum_enable_lightboxForGroup', array(
        'order' => 10001,
        'value'=>1
    ));
    $this->addElement('Hidden', 'sesalbum_enable_lightboxForEvent', array(
      'order' => 10002,
      'value'=>1
     ));
     
    $this->addElement('Radio', 'sesalbum_show_information', array(
      'label' => 'Show Information in Advanced Lightbox',
      'description' => 'Do you want to show the information when advanced lightbox is opened? (If you select Yes, then the information window available in the right side of the advanced lightbox will be shown by default. If you select No, then the information will show when users will click on the Info icon (i) available in the top right corner of the lightbox.)',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.show.information', 1),
    ));

    //New File System Code
    $banner_options = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
    foreach( $files as $file ) {
      $banner_options[$file->storage_path] = $file->name;
    }
    $fileLink = $view->baseUrl() . '/admin/files/';
    if (count($banner_options) > 0) {
    $this->addElement('Select', 'sesalbum_private_photo', array(
        'label' => 'Photo instead of Private Photo',
        'description' => 'Choose below the photo to be shown for a private photo when the photo is shown in photo lightbox. When a user upload a photo and restrict its visibility to friend or network, then also the photo is showed in Activity Feed and certain widgets and browse pages. Below chosen photo will be shown for such private pages to users who does not have access.  [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="'.$fileLink.'">File & Media Manager</a>.]',
        'multiOptions' => $banner_options,
        'value' => $settings->getSetting('sesalbum.private.photo'),
    ));			
    }else{
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for private. Photo to be chosen for private photo should be first uploaded from the "Layout" >> "<a target="_blank" href="'.$fileLink.'">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the private photo. Please upload the Photo to be chosen for private photo from the "Layout" >> "<a target="_blank" href="'.$fileLink.'">File & Media Manage</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesalbum_private_photo', array(
          'label' => 'Photo instead of Private Photo',
          'description' => $description,
      ));
    }
    $this->sesalbum_private_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    $this->addElement('Text', 'sesalbum_title_truncate', array(
        'label' => 'Album Title Truncate Limit',
        'description' => 'Enter the title truncation limit of the albums when shown lightbox viewer.',
        'value' => $settings->getSetting('sesalbum.title.truncate', 45),
    ));
    $this->addElement('Dummy', 'dummy', array(
      'content' => 'Choose from below the options to be available in the lightbox viewer for photos.',
    ));
    $this->addElement('Radio', 'sesalbum_add_tags', array(
        'label' => 'Tags',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesalbum.add.tags', 1),
    ));
    $this->addElement('Radio', 'sesalbum_add_delete', array(
        'label' => 'Delete',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesalbum.add.delete', 1),
    ));
    $this->addElement('Radio', 'sesalbum_add_share', array(
        'label' => 'Share',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesalbum.add.share', 1),
    ));
    $this->addElement('Radio', 'sesalbum_add_report', array(
        'label' => 'Report',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesalbum.add.report', 1),
    ));
    $this->addElement('Radio', 'sesalbum_add_profilepic', array(
        'label' => 'Make Profile Photo',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesalbum.add.profilepic', 1),
    ));
    $this->addElement('Radio', 'sesalbum_add_download', array(
        'label' => 'Download',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesalbum.add.download', 1),
    ));



      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessocialshare')) {

          $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
          $link = '<a href="https://www.socialenginesolutions.com/social-engine/advanced-share-plugin/" target="_blank">Advanced Sharing Plugin – Inside and Outside Site Sharing</a>';
          $description = sprintf('Do you want to allow your users to share photos from the photo lightbox viewer on your website to other social networking websites? [Note: Only Facebook, Twitter & Pinterest options are available in Lightbox Viewer.] (If you want to enable social sharing on multiple other social networking websites (Facebook, Twitter, Whatsapp, Skype, and many more …), then you should have our "%s" plugin installed and activated on your website.)', $link);


          $this->addElement('Radio', 'sesalbum_enablesessocialshare', array(
              'label' => 'Enable Social Sharing',
              'description' => $description,
              'multiOptions' => array(
                  1 => 'Yes',
                  0 => 'No'
              ),
              'onchange' => "enablesessocialshare(this.value)",
              'value' => $settings->getSetting('sesalbum.enablesessocialshare', 1),
          ));
          $this->getElement('sesalbum_enablesessocialshare')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

          $this->addElement('Text', "sesalbum_iconlimit", array(
              'label' => "Count For Social Sites To Show",
              'description' => 'Enter the number of social networking sites to be shown while sharing the photos. (If you enable More Icon, then other social site icons will display on clicking the more icon.',
              'value' => $settings->getSetting('sesalbum.iconlimit', 3),
              'validators' => array(
                  array('Int', true),
                  array('GreaterThan', true, array(0)),
              )
          ));

          $this->addElement('Select', "sesalbum_enableplusicon", array(
              'label' => "Show More Icon",
              'description' => 'Do you want to enable More icon to view all social networking sites’ share icons?',
              'multiOptions' => array(
                  '1' => 'Yes',
                  '0' => 'No',
              ),
              'value' => $settings->getSetting('sesalbum.enableplusicon', 1),
          ));
      }
    
    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}