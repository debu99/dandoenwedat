<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    if( !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.defaultcurrency')) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('sesbasic.defaultcurrency','USD');
    }

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_main_settings');

    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Settings_General();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
     $error = include_once APPLICATION_PATH . "/application/modules/Sesadvancedactivity/controllers/License.php";
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.pluginactivated')) {
        $db = Engine_Db_Table::getDefaultAdapter();;
        foreach ($values as $key => $value) {
          if($key == 'sesadvancedactivity_composeroptions' &&  Engine_Api::_()->getApi('settings', 'core')->hasSetting($key) ){
            Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
					}
					if($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        if($error != '1')
        $this->_helper->redirector->gotoRoute(array());
      }
    }
  }


  public function feedsharingAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_main_feedsharing');

    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Settings_FeedSharing();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function welcometabAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_main_welcomesettings');

    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Settings_WelcomeTab();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();

      foreach ($values as $key => $value) {
//         Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
        if($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }



  public function createAction(){
    $id = $this->_getParam('id',false);

    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Settings_Create();
      if($id){
        $item = Engine_Api::_()->getItem('sesadvancedactivity_filterlist',$id);
        $form->populate($item->toArray());
        $form->setTitle('Edit This Filter');
        $form->submit->setLabel('Edit');
        if(!$item->is_delete){
          $form->removeElement('filtertype');
          $form->removeElement('module');
        }
      }
    // Check if post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
    $db = Engine_Api::_()->getDbtable('filterlists', 'sesadvancedactivity')->getAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {
      $filterTable = Engine_Api::_()->getDbtable('filterlists', 'sesadvancedactivity');
      if(empty($id))
       $item = $filterTable->createRow();
      $item->setFromArray($form->getValues());
      $item->save();
      if(!$id){
        $item->order = $item->getIdentity();
        $item->save();
      }
      if(!empty($form->getValues()['removeIcon'])){
        $StorageFile_id = $form->getValues()['file_id'];
         Engine_Api::_()->getDbtable('files', 'storage')->delete(array(
          'file_id = ?' =>$StorageFile_id,
        ));
         $ChoosePhoto= $_FILES['file']['name'];
         $ChoosePhoto="";
        $item->file_id = 0;
        $item->save();
      }
      else{
        $ChoosePhoto= $_FILES['file']['name'];
      }
      if(!empty($ChoosePhoto)) {
        $file_ext = pathinfo($_FILES['file']['name']);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile($form->file, array(
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
    }catch(Exception $e){
      $db->rollBack();
      throw $e;
    }
    $this->_forward('success', 'utility', 'core', array(
                    'smoothboxClose' => 10,
                    'parentRefresh'=> 10,
                    'messages' => array('Filter Type Created Successfully.')
    ));
  }
  public function filterAction(){
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_filter');
     $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesadvancedactivity_admin_filter', array(), 'sesadvancedactivity_admin_main_filtermainsettings');
     $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Settings_Filtersettings();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
        foreach ($values as $key => $value) {

          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        $this->_helper->redirector->gotoRoute(array());
    }
  }

  public function filterContentAction(){
     if(!empty($_POST['order'])){
       $counter = 1;
        foreach($_POST['order'] as $order){
          $item = Engine_Api::_()->getItem('sesadvancedactivity_filterlist',$order);
          if(!$item)
            continue;
          $item->order = $counter;
          $item->save();
          $counter++;
        }
     }
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_filter');
     $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesadvancedactivity_admin_filter', array(), 'sesadvancedactivity_admin_main_filtercontentsettings');

     $this->view->paginator = Engine_Api::_()->getDbTable('filterlists','sesadvancedactivity')->fetchAll(Engine_Api::_()->getDbTable('filterlists','sesadvancedactivity')->select()->order('order ASC'));
  }

  public function enabledAction() {
    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesadvancedactivity_filterlist', $id);
      $item->active = !$item->active;
      $item->save();
    }

    $this->_redirect('admin/sesadvancedactivity/settings/filter-content');
  }
  public function notificationAction(){
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_main_feednotification');
    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Settings_Notification();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
        foreach ($values as $key => $value) {
          if($key == 'sesadvancedactivity_composeroptions' &&  Engine_Api::_()->getApi('settings', 'core')->hasSetting($key) ){
            Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
					}
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        $this->_helper->redirector->gotoRoute(array());
    }
  }
  public function deleteAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete Filter?');
    $form->setDescription('Are you sure that you want to delete this filter? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $item = Engine_Api::_()->getItem('sesadvancedactivity_filterlist', $id)->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Filter Delete Successfully.')
      ));
    }
  }



  public function uploadBackgrounds() {

    $backgroundTable = Engine_Api::_()->getDbtable('backgrounds', 'sesfeedbg');
    $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesfeedbg' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "backgrounds" . DIRECTORY_SEPARATOR;

    $file_display = array('jpg', 'jpeg', 'png', 'gif');
    if (file_exists($PathFile)) {
      $dir_contents = scandir( $PathFile );
      foreach ( $dir_contents as $file ) {
        $explode = explode('.', @$file );
        $class = end( $explode );
        $file_type = strtolower($class);
        if ( ($file !== '.') && ($file !== '..') && (in_array( $file_type, $file_display)) ) {
          $images = explode('.', $file);
          //$db = Engine_Db_Table::getDefaultAdapter();
          //$db->beginTransaction();
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
            //$db->commit();
          } catch(Exception $e) {
            //$db->rollBack();
            //throw $e;
          }
        }
      }
    }
  }


  public function uploadFeelingsMainIconsActivity() {

    $paginator = Engine_Api::_()->getDbTable('feelings','sesfeelingactivity')->getFeelings(array('fetchAll' => 1, 'admin' => 1));
    foreach($paginator as $emoji) {

      $feelings = explode(' ',strtolower($emoji->title));

      $foldername = '';
      if(@$feelings[0]) {
        $foldername .= @$feelings[0];
      }

      if(@$feelings[1]) {
        $foldername .= '_'.@$feelings[1];
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

      if(@$feelings[0]) {
        $foldername .= @$feelings[0];
      }

      if(@$feelings[1]) {
        $foldername .= '_'.@$feelings[1];
      }

      $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesfeelingactivity' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "feeling_activity" . DIRECTORY_SEPARATOR . $foldername . DIRECTORY_SEPARATOR;

      // Get all existing log files
      $logFiles = array();
      $file_display = array('jpg', 'jpeg', 'png', 'gif');
      if (file_exists($PathFile)) {

        $dir_contents = scandir( $PathFile );

        foreach ( $dir_contents as $file ) {

          $fileex = explode('.', $file );
          $fileend = end( $fileex );
          $file_type = strtolower( $fileend );
          if ( ($file !== '.') && ($file !== '..') && (in_array( $file_type, $file_display)) ) {

            $images = explode('.', $file);
            //$db = Engine_Db_Table::getDefaultAdapter();
            //$db->beginTransaction();
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
                //$db->commit();
              }
            } catch(Exception $e) {
              //$db->rollBack();
              //throw $e;
            }
          }
        }
      }
    }
  }

  public function writeEnabledModulesFile()
  {
    $db = Engine_Db_Table::getDefaultAdapter();
    // get enabled modules list
    $enabledModuleNames = $db->select()
      ->from('engine4_core_modules', 'name')
      ->where('enabled = ?', 1)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);
    $enabled_modules_file = APPLICATION_PATH . '/application/settings/enabled_module_directories.php';
    if( (is_file($enabled_modules_file) && is_writable($enabled_modules_file)) ||
        (is_dir(dirname($enabled_modules_file)) && is_writable(dirname($enabled_modules_file))) ) {

      foreach( $enabledModuleNames as $module ) {
        $modulesInflected[] = Engine_Api::inflect($module);
      }

      $file_contents = "<?php defined('_ENGINE') or die('Access Denied'); return ";
      $file_contents .= var_export($modulesInflected, true);
      $file_contents .= "; ?>";
      file_put_contents($enabled_modules_file, $file_contents);
      @chmod($enabled_modules_file, 0777);
    }
  }
}
