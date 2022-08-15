<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminManageReactionsController.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesadvancedcomment_AdminManageReactionsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedcomment_admin_main_cmtsettings');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedcomment_admin_main', array(), 'sesadvancedcomment_admin_main_managereactions');

    $page = $this->_getParam('page',1);
    $this->view->paginator = Engine_Api::_()->getDbTable('reactions','sesadvancedcomment')->getPaginator();
    $this->view->paginator->setItemCountPerPage(100);
    $this->view->paginator->setCurrentPageNumber($page);
  }

  public function addReactionAction() {

    $id = $this->_getParam('id',false);
    $this->view->form = $form = new Sesadvancedcomment_Form_Admin_Reaction_AddReaction();
    if($id) {
      $item = Engine_Api::_()->getItem('sesadvancedcomment_reaction',$id);
      $form->populate($item->toArray());
      $form->setTitle('Edit This Reaction');
      $form->setDescription('Here, you can edit this reactions which will show to users when they mouse over on Like button.');
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
    //If we're here, we're done
    $this->view->status = true;
    try {
      $reactionsTable = Engine_Api::_()->getDbtable('reactions', 'sesadvancedcomment');
      $values = $form->getValues();
      unset($values['file']);
      if(empty($id))
       $item = $reactionsTable->createRow();
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
    }catch(Exception $e){
      $db->rollBack();
      throw $e;
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('Reaction Added Successfully.')
    ));
  }

  public function statusAction() {
    $reaction_id = $this->_getParam('id');
    if (!empty($reaction_id)) {
      $reaction = Engine_Api::_()->getItem('sesadvancedcomment_reaction', $reaction_id);
      $reaction->enabled = !$reaction->enabled;
      $reaction->save();
    }
		if(isset($_SERVER['HTTP_REFERER']))
			$url = $_SERVER['HTTP_REFERER'];
		else
			$url = 'admin/sesadvancedcomment/manage-reactions';
    $this->_redirect($url);
  }

  public function deleteReactionAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete This Reaction');
    $form->setDescription('Are you sure that you want to delete this reaction? It will not be recoverable after being deleted and also all entry also deleted which user on any content and feed.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->query("DELETE FROM `engine4_sesadvancedactivity_activitylikes` WHERE `engine4_sesadvancedactivity_activitylikes`.`type` = '".$id."';");
      $db->query("DELETE FROM `engine4_sesadvancedactivity_corelikes` WHERE `engine4_sesadvancedactivity_corelikes`.`type` = '".$id."';");

      $file = Engine_Api::_()->getItem('sesadvancedcomment_reaction', $id);
      $file->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Reaction Delete Successfully.')
      ));
    }
  }
}
