<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedbg
 * @package    Sesfeedbg
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedbg_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesfeedbg_admin_main_febgsettings');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesfeedbg_admin_main', array(), 'sesfeedbg_admin_main_settings');

    $this->view->form = $form = new Sesfeedbg_Form_Admin_Settings_Global();

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeedbg.pluginactivated')) {
        foreach ($values as $key => $value) {
          if($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
//         if($error)
//           $this->_helper->redirector->gotoRoute(array());
      }
    }
  }


  public function uploadBackgrounds() {

    $backgroundTable = Engine_Api::_()->getDbtable('backgrounds', 'sesfeedbg');
    $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesfeedbg' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "backgrounds" . DIRECTORY_SEPARATOR;

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
            $item = $backgroundTable->createRow();
            $values['enabled'] = 1;
            $values['starttime'] = date('Y-m-d');
            $values['enableenddate'] = 1;

            $item->setFromArray($values);
            $item->save();
            $item->order = $item->background_id;
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
              $item->file_id = $storageObject->file_id;
              $item->save();
            }
            $db->commit();
          } catch(Exception $e) {
            $db->rollBack();
            throw $e;
          }
        }
      }
    }
  }
}
