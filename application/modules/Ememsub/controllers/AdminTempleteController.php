<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminTempleteController.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_AdminTempleteController extends Core_Controller_Action_Admin
{
  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_temp');
    $table = Engine_Api::_()->getDbtable('templates', 'ememsub');
    $select = $table->select();
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  public function templatesAction() {
    $this->view->template_id = $this->_getParam('template_id');
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_temp');
    $table = Engine_Api::_()->getDbtable('packages', 'payment');
    $select = $table->select()->where('enabled = ?', 1);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  public function addTemplateAction() {
    $this->view->form = $form = new Ememsub_Form_Admin_Template_Create();
    //If not post or form not valid, return
    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    //Process
     $values = $form->getValues();
    $templateTable = Engine_Api::_()->getDbtable('templates', 'ememsub');
    $db = $templateTable->getAdapter();
    $db->beginTransaction();
    try {
        $template = $templateTable->createRow();
        $template->setFromArray($values);
        $template->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('')
    ));
  }
  public function editTemplateAction() {
    $template_id = $this->_getParam('template_id');
    $this->view->form = $form = new Ememsub_Form_Admin_Template_Edit();
    $template = Engine_Api::_()->getItem('ememsub_template',$template_id);
    $form->populate($template->toArray());
    //If not post or form not valid, return
    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    //Process
    $values = $form->getValues();
    
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
        $template->setFromArray($values);
        $template->save();
        $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('')
    ));
  }
  public function createAction() {
    $template_id = $this->_getParam('template_id');
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('ememsub_admin_main', array(), 'ememsub_admin_main_temp');
    if( null === ($packageIdentity = $this->_getParam('package_id')) ||
        !($package = Engine_Api::_()->getDbtable('packages', 'payment')->find($packageIdentity)->current())) {
      throw new Engine_Exception('No package found');
    }
    $this->view->template_id = $template_id;
    $styleTable = Engine_Api::_()->getDbtable('styles', 'ememsub');
    $styleRow = $styleTable->getStyleId($package->package_id,$template_id);
    if(count($styleRow)) {
      $this->view->form = $form = new Ememsub_Form_Admin_Style_Edit();
       $style = Engine_Api::_()->getItem('ememsub_style', $styleRow->style_id);
      
      $form->populate($style->toArray());
    }else {
      $this->view->form = $form = new Ememsub_Form_Admin_Style_Create();
      $form->populate(
        array(
          'column_name'=>$package->getTitle(),
          'column_description'=>$package->description,
          'column_title'=>$package->getTitle()
        )
      );
    }
  
    //If not post or form not valid, return
    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    //Process
  
    $values = $form->getValues();
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      if(count($styleRow)){
        $style = Engine_Api::_()->getItem('ememsub_style', $styleRow->style_id);
        $style->setFromArray($values);
        $style->package_id = $package->package_id;
        $style->template_id = $template_id;
        $style->save();
      }else{
        $style = $styleTable->createRow();
        $style->setFromArray($values);
        $style->enabled = 1;
        $style->package_id = $package->package_id;
        $style->template_id = $template_id;
        $style->save();
      }
      $db->commit();
      return $this->_helper->redirector->gotoRoute(array('module' => 'ememsub', 'controller' => 'templete', 'action' => 'templates','template_id'=>$template_id), 'admin_default', true);
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  public function setDefaultAction()
  {
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    // Get level
    if( !($id = $this->_getParam('template_id')) ||
        !($template = Engine_Api::_()->getItem('ememsub_template', $id)) ) {
      return;
    }
    $this->view->template = $template;
    $table = Engine_Api::_()->getItemTable('ememsub_template');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      // Remove default
      $table->update(array('active' => 0), array('template_id != ?' => $id));
      // set the current item to default
      $template->active = 1;
      $template->save();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
  public function deleteAction()
  {
    $this->view->form = $form = new Ememsub_Form_Admin_Template_Delete();
     if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !($id = $this->_getParam('template_id')) ||
        !($template = Engine_Api::_()->getItem('ememsub_template', $id)) ) {
      return;
    }
    $table = Engine_Api::_()->getItemTable('ememsub_template');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      // Remove default
      $template->delete();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Template has been Deleted.')
    ));
  }
  public function previewAction(){
    if( !($id = $this->_getParam('template_id')) ||
        !($template = Engine_Api::_()->getItem('ememsub_template', $id)) ) {
      return;
    }
    $this->view->template = $template;
  }
}
