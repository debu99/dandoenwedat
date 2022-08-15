<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminPackageController.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_AdminPackageController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_plan');
    $table = Engine_Api::_()->getDbtable('packages', 'payment');
    $select = $table->select()->where('enabled = ?', 1);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  public function addFeaturesAction()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_plan');
    if( null === ($packageIdentity = $this->_getParam('package_id')) ||
        !($package = Engine_Api::_()->getDbtable('packages', 'payment')->find($packageIdentity)->current()) ) {
      throw new Engine_Exception('No package found');
    }
    $this->view->form = $form = new Ememsub_Form_Admin_Feature_Add();
    $this->view->package = $package;
    // Check method/data
    if(!$this->getRequest()->isPost()){
      return;
    }
    if(!$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    $existColumnArray = array('column_name', 'column_title', 'column_width', 'column_row_color', 'column_row_text_color', 'icon_position', 'currency', 'show_currency', 'currency_value', 'currency_duration', 'column_description', 'footer_text', 'footer_text_color', 'footer_bg_color', 'column_text_color', 'text_url', 'column_color', 'show_highlight', 'submit', 'MAX_FILE_SIZE');
    $db = Engine_Db_Table::getDefaultAdapter();
    foreach ($_POST as $key => $value) {
      $columnName = $key;
      if (in_array($columnName, $existColumnArray))
        continue;
      $explodeKey = end(explode('_', $key));
      $column = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$columnName'")->fetch();
      if (empty($column)) {
        if ($explodeKey == 'text')
          $db->query("ALTER TABLE `engine4_ememsub_features` ADD $columnName VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        else
          $db->query("ALTER TABLE `engine4_ememsub_features` ADD $columnName TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
      }
      $db->commit();
    } 
    foreach ($_FILES as $columnName => $value) {
      if (in_array($columnName, $existColumnArray))
        continue;
      $explodedColumnName = explode('_', $columnName);
      if (!isset($explodedColumnName[2]))
        continue;
      $column = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$columnName'")->fetch();
      if (empty($column)) {
        $db->query("ALTER TABLE `engine4_ememsub_features` ADD $columnName INT(11) NULL");
      }
      $db->commit();
    }
    
    
    $featureTable = Engine_Api::_()->getDbtable('features', 'ememsub');
    $db = $featureTable->getAdapter();
    $db->beginTransaction();
    unset($_POST['submit']);
    try {
      $values = $form->getValues();
      $row = $featureTable->createRow();
      $row->setFromArray($values);
      $row->save();
      $rowCount = $settings->getSetting('ememsub.table.row',4);
      $row->package_id = $package->package_id;
      $tabs_count = array();
      for ($i = 1; $i <= $rowCount; $i++) {
        $columnCountArray[] = $i;
      }
      //Upload accordions icon
      foreach ($columnCountArray as $coulmnCount) {
        $tableColumnName = 'row' . $coulmnCount . '_file_id';
        if (isset($_FILES[$tableColumnName]) && !empty($_FILES[$tableColumnName]['name'])) {
          $Icon = $row->setPhoto($form->$tableColumnName, $row->feature_id);
          if (!empty($Icon->file_id))
            $row->$tableColumnName = $Icon->file_id;
        }
      }
      $row->save();
      $db->commit();
    } catch( Exception $e ) { 
      $db->rollBack();
      throw $e;
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }
  public function editFeaturesAction()
  {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_plan');
    if( null === ($featureIdentity = $this->_getParam('feature_id')) ||
        !($feature = Engine_Api::_()->getDbtable('features', 'ememsub')->find($featureIdentity)->current()) ) {
      throw new Engine_Exception('No package found');
    }
    $this->view->feature_id = $this->_getParam('feature_id');
    $this->view->form = $form = new Ememsub_Form_Admin_Feature_Edit();
    $this->view->feature  = $feature;
    $form->populate($feature->toArray());
    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if(!$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    unset($_POST['submit']);
    try {
        $values = $form->getValues();
        $existColumnArray = array('column_name', 'column_title', 'column_width', 'column_row_color', 'column_row_text_color', 'icon_position', 'currency', 'show_currency', 'currency_value', 'currency_duration', 'column_description', 'footer_text', 'footer_text_color', 'footer_bg_color', 'column_text_color', 'text_url', 'column_color', 'show_highlight', 'submit', 'MAX_FILE_SIZE');
        foreach ($_POST as $key => $value) {
          $columnName = $key;
          if (in_array($columnName, $existColumnArray))
            continue;
          $explodeKey = end(explode('_', $key));
          $column = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$columnName'")->fetch();
          if (empty($column)) {
            if ($explodeKey == 'text')
              $db->query("ALTER TABLE `engine4_ememsub_features` ADD $columnName VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
            else
              $db->query("ALTER TABLE `engine4_ememsub_features` ADD $columnName TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
          }
        } 
        foreach ($_FILES as $columnName => $value) {
          if (in_array($columnName, $existColumnArray))
            continue;
          $explodedColumnName = explode('_', $columnName);
          if (!isset($explodedColumnName[2]))
            continue;
          $column = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$columnName'")->fetch();
          if (empty($column)) {
            $db->query("ALTER TABLE `engine4_ememsub_features` ADD $columnName INT(11) NULL");
          }
        }
        $db->commit();
        $rowCount = $settings->getSetting('ememsub.table.row',4);
        $tabs_count = array();
        for ($i = 1; $i <= $rowCount; $i++) {
          $columnCountArray[] = $i;
        }
        foreach ($columnCountArray as $coulmnCount) {
          $tableColumnName = 'row' . $coulmnCount . '_file_id';
          if (empty($values[$tableColumnName]))
            unset($values[$tableColumnName]);
        }
        $feature->setFromArray($values);
        $feature->save();
        foreach ($columnCountArray as $coulmnCount) {
          $tableColumnName = 'row' . $coulmnCount . '_file_id';
          if (isset($_FILES[$tableColumnName]) && !empty($_FILES[$tableColumnName]['name'])) {
            $Icon = $feature->setPhoto($form->$tableColumnName, $row->pricingtable_id);
            $previousColumnIcon = $feature->$tableColumnName;

            if (!empty($Icon->file_id)) {
              if ($previousColumnIcon) {
                $columnIcon = Engine_Api::_()->getItem('storage_file', $previousColumnIcon);
                if ($columnIcon)
                  $columnIcon->delete();
              }
              $feature->$tableColumnName = $Icon->file_id;
              $feature->save();
            }
          }
          if (isset($values['remove_row' . $coulmnCount . '_icon']) && !empty($values['remove_row' . $coulmnCount . '_icon'])) {
            $columnIcon = Engine_Api::_()->getItem('storage_file', $feature->$tableColumnName);
            $feature->$tableColumnName = 0;
            $feature->save();
            $columnIcon->delete();
          }
        }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }
  public function uploadPhotoAction() {
    if( null === ($featureIdentity = $this->_getParam('feature_id')) ||
        !($feature = Engine_Api::_()->getDbtable('features', 'ememsub')->find($featureIdentity)->current()) ) {
      throw new Engine_Exception('No Feature found');
    }
    $viewer = Engine_Api::_()->user()->getViewer();
     
    if (isset($_FILES['Filedata']))
      $data = $_FILES['Filedata'];
    else if (isset($_FILES['webcam']))
      $data = $_FILES['webcam'];
      
    $Icon = $feature->setPhoto($data, '', 'profile');
    $previousPhoto = $feature->photo_id;
    if (!empty($Icon->file_id)) {
      $feature->photo_id = $Icon->file_id;
      $feature->save();
    }
    if($previousPhoto != 0) {
      $im = Engine_Api::_()->getItem('storage_file', $previousPhoto);
      if(!empty($im))
        $im->delete();
    }
    echo json_encode(array('file' => $feature->getPhotoUrl()));die;
  }
  public function removePhotoAction() {
    if( null === ($featureIdentity = $this->_getParam('feature_id')) ||
        !($feature = Engine_Api::_()->getDbtable('features', 'ememsub')->find($featureIdentity)->current()) ) {
      throw new Engine_Exception('No Feature found');
    }
    $feature->photo_id = 0;
    $feature->save();
    echo json_encode(array());die;
  }
}
