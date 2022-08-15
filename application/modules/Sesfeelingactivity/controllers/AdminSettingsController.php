<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeelingactivity_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesfeelingactivity_admin_main_flngsettings');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesfeelingactivity_admin_main', array(), 'sesfeelingactivity_admin_main_settings');

    $this->view->form = $form = new Sesfeelingactivity_Form_Admin_Settings_Global();

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeelingactivity.pluginactivated')) {
        foreach ($values as $key => $value) {
          if($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        if($error)
          $this->_helper->redirector->gotoRoute(array());
      }
    }
  }


  public function uploadFeelingsMainIconsActivity() {

    $paginator = Engine_Api::_()->getDbTable('feelings','sesfeelingactivity')->getFeelings(array('fetchAll' => 1, 'admin' => 1));
    foreach($paginator as $emoji) {

      $feelings = explode(' ',strtolower($emoji->title));

      $foldername = '';
      if($feelings[0]) {
        $foldername .= $feelings[0];
      }

      if($feelings[1]) {
        $foldername .= '_'.$feelings[1];
      }

      //Main Feeling icon work
      $mainFeelingIcon = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesfeelingactivity' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "feeling_activity" . DIRECTORY_SEPARATOR . 'feeling_activity_tittle_icons' . DIRECTORY_SEPARATOR . $foldername.'.png';

      if (file_exists($mainFeelingIcon)) {

        $file_ext = pathinfo($mainFeelingIcon);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile($mainFeelingIcon, array(
          'parent_id' => $emoji->getIdentity(),
          'parent_type' => $emoji->getType(),
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        ));

        // Remove temporary file
        @unlink($file['tmp_name']);
        $emoji->file_id = $storageObject->file_id;
        $emoji->save();
      }
      //Main Feeling icon work
    }
  }

  public function uploadFeelingsActivity() {

    $emojiiconsTable = Engine_Api::_()->getDbtable('feelingicons', 'sesfeelingactivity');

    $paginator = Engine_Api::_()->getDbTable('feelings','sesfeelingactivity')->getFeelings(array('fetchAll' => 1, 'admin' => 1));
    foreach($paginator as $emoji) {

      $feelings = explode(' ',strtolower($emoji->title));
      $foldername = '';

      if($feelings[0]) {
        $foldername .= $feelings[0];
      }

      if($feelings[1]) {
        $foldername .= '_'.$feelings[1];
      }

      $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesfeelingactivity' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "feeling_activity" . DIRECTORY_SEPARATOR . $foldername . DIRECTORY_SEPARATOR;

      // Get all existing log files
      $logFiles = array();
      $file_display = array('jpg', 'jpeg', 'png', 'gif');
      if (file_exists($PathFile)) {

        $dir_contents = scandir( $PathFile );

        foreach ( $dir_contents as $file ) {

          $file_type = strtolower( end( explode('.', $file ) ) );
          if ( ($file !== '.') && ($file !== '..') && (in_array( $file_type, $file_display)) ) {

            $images = explode('.', $file);
            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            // If we're here, we're done

            try {

              $values['title'] = str_replace('_', ' ', $images[0]);
              $values['type'] = 1;
              $values['feeling_id'] = $emoji->feeling_id;

              $getEmojiIconExist = Engine_Api::_()->getDbTable('feelingicons', 'sesfeelingactivity')->getFeelingIconExist(array('title' => str_replace('_', ' ', $images[0])));

              if(empty($getEmojiIconExist)) {

                $item = $emojiiconsTable->createRow();

                $item->setFromArray($values);
                $item->save();

                if(!empty($file)) {
                  $file_ext = pathinfo($file);
                  $file_ext = $file_ext['extension'];
                  $storage = Engine_Api::_()->getItemTable('storage_file');

                  $pngFile = $PathFile . $file;

                  $storageObject = $storage->createFile($pngFile, array(
                    'parent_id' => $item->getIdentity(),
                    'parent_type' => $item->getType(),
                    'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                  ));

                  // Remove temporary file
                  @unlink($file['tmp_name']);
                  $item->feeling_icon = $storageObject->file_id;
                  $item->save();
                }
                $db->commit();
              }
            } catch(Exception $e) {
              $db->rollBack();
              throw $e;
            }
          }
        }
      }
    }
  }
}
