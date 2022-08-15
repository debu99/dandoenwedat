<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Background.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesandroidapp_Form_Admin_Background extends Engine_Form {
  public function init() {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
            ->setTitle('App Background Images Settings')
            ->setDescription("");
            
    //New File System Code
    $videoFile = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('mp4')));
    foreach( $files as $file ) {
      $videoFile[$file->storage_path] = $file->name;
    }

    $this->addElement('Select', 'sesandroidapp_image_video', array(
        'label' => 'Video/Image',
        'description' => 'Choose from below section what you want to show?',
        'multiOptions' => array('1'=>'Video (forgot password + login)','2'=>'Image'),
        'value'=>$settings->getSetting('sesandroidapp_image_video', '2'),
    ));
    $this->getElement('sesandroidapp_image_video')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    //New File System Code
    $videoFile = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('mp4')));
    foreach( $files as $file ) {
      $videoFile[$file->storage_path] = $file->name;
    }
    
    if(count($videoFile) > 1) {
      $this->addElement('Select', 'sesandroidapp_video_slide', array(
        'label' => 'Video',
        'description' => '',
        'multiOptions' => $videoFile,
        'value'=>$settings->getSetting('sesandroidapp_video_slide', ''),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no video in the File & Media Manager for the login screen. Please upload the Video to be chosen for login screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesandroidapp_video_slide', array(
        'label' => 'Video',
        'description' => $description,
      ));
    }
    $this->getElement('sesandroidapp_video_slide')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    //New File System Code
    $default_photos_main = array();
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
    foreach( $files as $file ) {
      $default_photos_main[$file->storage_path] = $file->name;
    }
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    if (count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesandroidapp/externals/images/login.jpeg'=>''),$default_photos_main);
      $this->addElement('Select', 'sesandroidapp_login_background_image', array(
        'label' => 'Login',
        'description' => 'Choose login screen background image for your app. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
        'multiOptions' => $default_photos,
        'value' => $settings->getSetting('sesandroidapp.login.background.image'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo in the File & Media Manager for the login screen. Please upload the Photo to be chosen for login screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesandroidapp_login_background_image', array(
          'label' => 'Login',
          'description' => $description,
      ));
    }
    $this->sesandroidapp_login_background_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
		
    
    if (count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesandroidapp/externals/images/forgot.jpeg'=>''),$default_photos_main);
      $this->addElement('Select', 'sesandroidapp_forgot_background_image', array(
          'label' => 'Forgot Password',
          'description' => 'Choose forgot password background image for your app. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesandroidapp_forgot_background_image'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo in the File & Media Manager for the forgot password screen. Please upload the Photo to be chosen for forgot password screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesandroidapp_forgot_background_image', array(
          'label' => 'Forgot Password',
          'description' => $description,
      ));
    }
    $this->sesandroidapp_forgot_background_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    
    if (count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesandroidapp/externals/images/rateus.jpg'=>''),$default_photos_main);
      $this->addElement('Select', 'sesandroidapp_rateus_background_image', array(
          'label' => 'Rate Us',
          'description' => 'Choose rate us background image for your app. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesandroidapp_rateus_background_image'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo in the File & Media Manager for the rate us screen. Please upload the Photo to be chosen for rate us screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesandroidapp_rateus_background_image', array(
          'label' => 'Rate Us',
          'description' => $description,
      ));
    }
    $this->sesandroidapp_rateus_background_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
