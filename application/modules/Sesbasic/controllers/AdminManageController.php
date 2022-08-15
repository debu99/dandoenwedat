<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminManageController.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_AdminManageController extends Core_Controller_Action_Admin {

  public function uploadImageAction() {
  
  
    $ses_public_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sesadminWysiwygPhotos';

    if (!is_dir($ses_public_path) && mkdir($ses_public_path, 0777, true))
      @chmod($ses_public_path, 0777);

    // Prepare
    if (empty($_FILES['userfile'])) {
      $this->view->error = 'File failed to upload. Check your server settings (such as php.ini max_upload_filesize).';
      return;
    }

    $info = $_FILES['userfile'];
    $targetFile = realpath($ses_public_path) . '/' . $info['name'];

    if( !move_uploaded_file($info['tmp_name'], $targetFile) ) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Unable to move file to upload directory.");
      return;
    }

    $this->view->status = 1;
    
    $this->view->photo_url = ( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . Zend_Registry::get('Zend_View')->baseUrl().'/public/sesadminWysiwygPhotos/' . $info['name'];
  }

}