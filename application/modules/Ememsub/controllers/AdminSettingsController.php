<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminSettingsController.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_AdminSettingsController extends Core_Controller_Action_Admin {
  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_setting');
    $this->view->form = $form = new Ememsub_Form_Admin_Global();
    $db = Engine_Db_Table::getDefaultAdapter();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $values = $form->getValues();
        $lastRows = $settings->getSetting('ememsub.table.row',4);
        include_once APPLICATION_PATH . "/application/modules/Ememsub/controllers/License.php";
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('ememsub.pluginactivated')) {
          foreach ($values as $key => $value) {
              if($settings->hasSetting($key, $value))
                  $settings->removeSetting($key);
              if(is_null($value)) {
                $value = 0;
              }
            $settings->setSetting($key, $value);
        if($lastRows == $settings->getSetting('ememsub.table.row',4))
          continue;
        try { 
          $tabs_count = array();
          $rowCount = $settings->getSetting('ememsub.table.row',4);
          for ($i = 1; $i <= $rowCount; $i++) {
            $tabs_count[] = $i;
          }
          $localeObject = Zend_Registry::get('Locale');
          $languages = Zend_Locale::getTranslationList('language', $localeObject);
          $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
          $translate = Zend_Registry::get('Zend_Translate');
          $languageList = $translate->getList();
          foreach ($tabs_count as $tab) {
              $id = "row$tab";
              $text = $id . '_text';
              $description = $id . '_description';
              $columnText = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$text'")->fetch();
              if (empty($columnText)) {
                $db->query("ALTER TABLE `engine4_ememsub_features` ADD $text VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
              }
              $columnText = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$text'")->fetch();
              if (empty($columnText)) {
                $db->query("ALTER TABLE `engine4_ememsub_features` ADD $text VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
              }
              $columnDescription = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$description'")->fetch();
              if (empty($columnDescription)) {
                $db->query("ALTER TABLE `engine4_ememsub_features` ADD $description TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
              }
          }
          foreach ($tabs_count as $tab) {
            $iconId = 'row' . $tab . '_file_id';
            $previewId = 'row' . $tab . '_icon_preview';
            $columnIconId = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$iconId'")->fetch();
            if (empty($columnIconId)) {
              $db->query("ALTER TABLE `engine4_ememsub_features` ADD $iconId INT(11) NULL");
            }
          }
          $db->commit();
        } catch( Exception $e ) { 
          $db->rollBack();
          throw $e;
        }
       }
	   
        $form->addNotice('Your changes have been saved.');
        if($error)
          $this->_helper->redirector->gotoRoute(array());
      }
    }
  }
  public function supportAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_support');
  }
}
