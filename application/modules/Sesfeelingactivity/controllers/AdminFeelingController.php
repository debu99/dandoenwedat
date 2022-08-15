<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminFeelingController.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeelingactivity_AdminFeelingController extends Core_Controller_Action_Admin {

  public function indexAction() {

    if(count($_POST)) {
      $feelingiconsTable = Engine_Api::_()->getDbtable('feelingicons', 'sesfeelingactivity');
      foreach($_POST as $key => $feeling_id) {
        $feeling = Engine_Api::_()->getItem('sesfeelingactivity_feeling', $feeling_id);
        $feelingIconsSelect = $feelingiconsTable->select()->where('feeling_id =?',$feeling_id);
        foreach($feelingiconsTable->fetchAll($feelingIconsSelect) as $feelingicon){
          $feelingicon->delete();
        }
        $feeling->delete();
        $this->_helper->redirector->gotoRoute(array());
      }
		}

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesfeelingactivity_admin_main_flngsettings');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesfeelingactivity_admin_main', array(), 'sesfeelingactivity_admin_main_feelingactivity');

    $this->view->paginator = Engine_Api::_()->getDbTable('feelings','sesfeelingactivity')->getPaginator(array('admin' => 1));
    $this->view->paginator->setItemCountPerPage(100);
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page',1));
  }


  public function enabledAction() {

    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesfeelingactivity_feeling', $id);
      $item->enabled = !$item->enabled;
      $item->save();
    }
    $this->_redirect('admin/sesfeelingactivity/feeling');
  }


  public function orderManageFeelingiconsAction() {

    if (!$this->getRequest()->isPost())
      return;

    $feelingiconsTable = Engine_Api::_()->getDbtable('feelingicons', 'sesfeelingactivity');
    $feelingicons = $feelingiconsTable->fetchAll($feelingiconsTable->select());
    foreach ($feelingicons as $feelingicon) {

      $order = $this->getRequest()->getParam('managefeelingicons_' . $feelingicon->feelingicon_id);

      if (!$order)
        $order = 999;
      $feelingicon->order = $order;
      $feelingicon->save();
    }
    return;
  }

  public function orderManageFeelingAction() {

    if (!$this->getRequest()->isPost())
      return;

    $feelingsTable = Engine_Api::_()->getDbtable('feelings', 'sesfeelingactivity');
    $feelingicons = $feelingsTable->fetchAll($feelingsTable->select());
    foreach ($feelingicons as $feelingicon) {

      $order = $this->getRequest()->getParam('managefeelings_' . $feelingicon->feeling_id);

      if (!$order)
        $order = 999;
      $feelingicon->order = $order;
      $feelingicon->save();
    }
    return;
  }

  public function createFeelingcategoryAction() {

    $id = $this->_getParam('id',false);

    $this->view->form = $form = new Sesfeelingactivity_Form_Admin_Feeling_Feelingcategorycreate();
    if($id){
      $item = Engine_Api::_()->getItem('sesfeelingactivity_feeling',$id);
      $form->populate($item->toArray());
      $form->setTitle('Edit This Category');
      $form->setDescription('Here, you can edit the Feeling/Activity category which will be displayed when users will click on the Feeling/Activity option.');
      $form->submit->setLabel('Edit');
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

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {

      $catgeoryTable = Engine_Api::_()->getDbtable('feelings', 'sesfeelingactivity');
      $values = $form->getValues();

      unset($values['file']);

      if(empty($id))
        $item = $catgeoryTable->createRow();

      $item->setFromArray($values);
      $item->save();

      if(!empty($_FILES['file']['name'])) {
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
    } catch(Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('Feeling Category Created Successfully.')
    ));
  }

  public function deleteFeelingcategoryAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();

    $form->setTitle('Delete This Category');
    $form->setDescription('Are you sure that you want to delete this category? It will not be recoverable after being deleted.');

    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $feelingiconsTable = Engine_Api::_()->getDbtable('feelingicons', 'sesfeelingactivity');
      $feelingiconSelect = $feelingiconsTable->select()->where('feeling_id =?',$id);
      $db = Engine_Db_Table::getDefaultAdapter();
      foreach($feelingiconsTable->fetchAll($feelingiconSelect) as $files) {
        //Delete Feeling post from activity post table
        $db->query('DELETE FROM `engine4_sesadvancedactivity_feelingposts` WHERE `engine4_sesadvancedactivity_feelingposts`.`feelingicon_id` = "'.$files->feelingicon_id.'"');
        $files->delete();
      }
      $emojicategory = Engine_Api::_()->getItem('sesfeelingactivity_feeling', $id);
      $emojicategory->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Feeling Category Deleteed Successfully.')
      ));
    }
  }

  public function feelingiconsAction() {

    $db = Engine_Db_Table::getDefaultAdapter();
    if(count($_POST) > 0) {
      foreach($_POST as $key => $file_id) {
        $file = Engine_Api::_()->getItem('sesfeelingactivity_feelingicon', $file_id[0]);
        $db->query('DELETE FROM `engine4_sesfeelingactivity_feelingicons` WHERE `engine4_sesfeelingactivity_feelingicons`.`feelingicon_id` = "'.$file_id[0].'"');
      }
      $this->_helper->redirector->gotoRoute(array());
		}

		$this->view->type =  $type = $this->_getParam('type',false);
    $this->view->feeling_id =  $feeling_id = $this->_getParam('feeling_id',false);
    $this->view->feeling = Engine_Api::_()->getItem('sesfeelingactivity_feeling', $feeling_id);
    if(!$feeling_id)
      return  $this->_forward('notfound', 'error', 'core');

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesfeelingactivity_admin_main', array(), 'sesfeelingactivity_admin_main_feelingactivity');

    $page = $this->_getParam('page',1);

    $this->view->paginator = Engine_Api::_()->getDbTable('feelingicons','sesfeelingactivity')->getPaginator(array('feeling_id' => $feeling_id));

    $this->view->paginator->setItemCountPerPage(100);
    $this->view->paginator->setCurrentPageNumber($page);
  }

  public function addFeelingiconAction() {

    $this->_helper->layout->setLayout('admin-simple');

    $id = $this->_getParam('id',false);

    $this->view->feeling_id = $feeling_id = $this->_getParam('feeling_id',0);
    $this->view->type = $type = $this->_getParam('type',0);

    $this->view->form = $form = new Sesfeelingactivity_Form_Admin_Feeling_FeelingIcon();
    if($id) {
      $item = Engine_Api::_()->getItem('sesfeelingactivity_feelingicon',$id);
      $form->populate($item->toArray());
      if($type == 1)
        $form->setTitle('Edit This Feeling/Activity List Item');
      else
        $form->setTitle('Edit Modules for Feeling/Activity');
      $form->submit->setLabel('Save Changes');
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

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {
      $catgeoryTable = Engine_Api::_()->getDbtable('feelingicons', 'sesfeelingactivity');

      $values = $form->getValues();

      if(empty($id))
       $item = $catgeoryTable->createRow();

      $values['feeling_id'] = $feeling_id;
      $values['type'] = $type;
      if($type == 2 && @$values['resource_type']) {
        $values['resource_type'] = $values['resource_type'];
      }

      $item->setFromArray($values);
      $item->save();

      if(!empty($_FILES['file']['name']) && $type == 1) {
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
        $item->feeling_icon = $storageObject->file_id;
        $item->save();
      }

      $db->commit();
    } catch(Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('Emoji Icon Created Successfully.')
    ));
  }

  public function deleteFeelingiconAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete This Feeling Icon');
    $form->setDescription('Are you sure that you want to delete this feeling icon? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      //Delete Feeling post from activity post table
      $db->query('DELETE FROM `engine4_sesadvancedactivity_feelingposts` WHERE `engine4_sesadvancedactivity_feelingposts`.`feelingicon_id` = "'.$id.'"');

      $file = Engine_Api::_()->getItem('sesfeelingactivity_feelingicon', $id);
      $file->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Feeling Icon Deleted Successfully.')
      ));
    }
  }
}
