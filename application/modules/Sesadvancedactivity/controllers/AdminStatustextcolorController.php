<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminStatustextcolorController.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_AdminStatustextcolorController extends Core_Controller_Action_Admin {

  public function indexAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_main_statustextcolor');
    
    $this->view->formFilter = $formFilter = new Sesadvancedactivity_Form_Admin_FilterStatusColor();
    $values = $formFilter->getValues();
    if(!empty($_GET))
      $formFilter->populate($_GET);
    if ($this->getRequest()->isPost() && $formFilter->isValid($this->getRequest()->getPost())) {
      $values = $_POST;
        foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $textcolor = Engine_Api::_()->getItem('sesadvancedactivity_textcolor', $value);
          $textcolor->delete();
        }
      }
      $this->_helper->redirector->gotoRoute(array());
    }
    
    $table = Engine_Api::_()->getDbtable('textcolors', 'sesadvancedactivity');
    $select = $table->select()->order('textcolor_id DESC');
    
    $page = $this->_getParam('page', 1);
		if( !empty($_GET['string']) ) 
      $select->where('string LIKE ?', '%' . $_GET['string'] . '%');
      
    if (isset($_GET['active']) && $_GET['active'] != '')
      $select->where('active =?', $_GET['active']);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber( $page );
  }
  
  public function createAction() {
  
    $id = $this->_getParam('id',false);
  
    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Createstring();
    if($id){
      $item = Engine_Api::_()->getItem('sesadvancedactivity_textcolor',$id);
      $form->populate($item->toArray());
      $form->setTitle('Edit this String');
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
    
    $values = $form->getValues();
    
    $select = Engine_Api::_()->getDbtable('textcolors', 'sesadvancedactivity')->select()->where('string =?',$values['string']);
    if(!empty($item)) 
      $select->where('textcolor_id !=?', $item->getIdentity());
    $stringExists = Engine_Api::_()->getDbtable('textcolors', 'sesadvancedactivity')->fetchRow($select);
    if($stringExists)
      return $form->AddError('You have already added this string.');
      
    $db = Engine_Api::_()->getDbtable('textcolors', 'sesadvancedactivity')->getAdapter();
    $db->beginTransaction();
    
    // If we're here, we're done
    $this->view->status = true;
    
    try {
      $textcolorsTable = Engine_Api::_()->getDbtable('textcolors', 'sesadvancedactivity');
      
      if(empty($id))
       $item = $textcolorsTable->createRow();
       
      $item->setFromArray($form->getValues());
      $item->save();
      $db->commit();
    } catch(Exception $e) {
      $db->rollBack();
      throw $e;  
    }
    
    $this->_forward('success', 'utility', 'core', array(
                    'smoothboxClose' => 10,
                    'parentRefresh'=> 10,
                    'messages' => array('String added Successfully.')
    ));
  }

  public function enabledAction() {
  
    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesadvancedactivity_textcolor', $id);
      $item->active = !$item->active;
      $item->save();
    }
    
    $this->_redirect('admin/sesadvancedactivity/statustextcolor');
  }
  
  public function deleteAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete This String?');
    $form->setDescription('Are you sure that you want to delete this string? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    
    //Check post
    if ($this->getRequest()->isPost()) {
      $item = Engine_Api::_()->getItem('sesadvancedactivity_textcolor', $id)->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('String Deleted Successfully.')
      ));
    }
  }
}