<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_settings');

    $this->view->form = $form = new Sesgdpr_Form_Admin_Global();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      include_once APPLICATION_PATH . "/application/modules/Sesgdpr/controllers/License.php";
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgdpr.pluginactivated')) {
        foreach ($values as $key => $value) {
          if(Engine_Api::_()->getApi('settings', 'core')->hasSetting($key))
            Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        if(!empty($error))
            $this->_helper->redirector->gotoRoute(array());
      }
    }
  }

  function cookieAction() {
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_cookir');
     $this->view->form = $form = new Sesgdpr_Form_Admin_Cookie();
      if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        $values = $form->getValues();
          foreach ($values as $key => $value) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
          }
          $form->addNotice('Your changes have been saved.');
          $this->_helper->redirector->gotoRoute(array());
      }
  }

  function dpoAction(){

     if ($this->getRequest()->isPost()) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $values = $this->getRequest()->getPost();
        foreach ($values as $key => $value) {
          if ($key == 'delete_' . $value) {
            $form = Engine_Api::_()->getItem('sesgdpr_content', $value)->delete();
          }
        }
      }

     $settings = Engine_Api::_()->getApi('settings', 'core');
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_request');
     $this->view->formFilter = $formFilter = new Sesgdpr_Form_Admin_Filter();
     $table = Engine_Api::_()->getDbTable('contents','sesgdpr');
     $select = $table->select()->where('type =?','gdpr')->order('creation_date DESC');
     $formFilter->populate($this->_getAllParams());

     if(isset($_POST['dpoemails']))
      $settings->setSetting('sesgdpr.dpoemails', $_POST['dpoemails']);

     $this->view->formEmail = $formEmail = new Sesgdpr_Form_Admin_Dpoemails();
     $formEmail->populate(array('dpoemails'=>$settings->getSetting('sesgdpr.dpoemails')));

     if(!empty($_GET['name']))
        $select->where('name LIKE ("%'.$_GET['name'].'%")') ;
     if(!empty($_GET['email']))
        $select->where('email LIKE ("%'.$_GET['email'].'%")');
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

  }

  function viewAction(){
    $id = $this->_getParam('id');
    $this->view->item = $item = Engine_Api::_()->getItem('sesgdpr_content',$id);
  }
  function noteAction(){
    $id = $this->_getParam('id');
    $this->view->item = $item = Engine_Api::_()->getItem('sesgdpr_content',$id);
    $this->view->form = $form = new Sesgdpr_Form_Admin_Note();
    $form->populate(array('note'=>$item->note));
    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    $values = $form->getValues();
    $item->note = $values['note'];
    $item->save();

    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('Note saved successfully.')
    ));
  }
  function serviceApprovedAction(){
    $id = $this->_getParam('id');
    $service = Engine_Api::_()->getItem('sesgdpr_service', $id);
    $service->enabled = !$service->enabled;
    $service->save();
    header("Location:".$_SERVER['HTTP_REFERER']);
    exit();
  }
  public function deleteServiceAction() {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesgdpr_Form_Admin_Delete();
    $form->setTitle('Delete This Service?');
    $form->setDescription('Are you sure that you want to delete this Service? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $deleteform = Engine_Api::_()->getItem('sesgdpr_service', $id)->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Service Deleted Successfully.')
      ));
    }
  }
  public function deleteAction() {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesgdpr_Form_Admin_Delete();
    $form->setTitle('Delete This Entry?');
    $form->setDescription('Are you sure that you want to delete this Entry? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $deleteform = Engine_Api::_()->getItem('sesgdpr_content', $id)->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Entry Deleted Successfully.')
      ));
    }
  }
  function replyAction(){
    $id = $this->_getParam('id');
    $this->view->item = $item = Engine_Api::_()->getItem('sesgdpr_content',$id);

		 $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesgdpr_Form_Admin_Mail();
    if($item->note)
      $form->removeElement('note');
    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    $values = $form->getValues();
    unset($values['body_text']);

    $core_mail = Engine_Api::_()->getApi('mail', 'core');
    $core_mail->sendSystem($item->email, 'sesgdpr_admin_reply', array(
        'subject' => $values['subject'],
        'body' => $values['body'],
    ));
    if(!empty($values['note']))
      $item->note = $values['body'];
    $item->replied = 1;
    $item->save();

    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('You have successfully reply to member.')
    ));

  }

  function privacyAction(){
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_privacyse');

     if ($this->getRequest()->isPost()) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $values = $this->getRequest()->getPost();
        foreach ($values as $key => $value) {
          if ($key == 'delete_' . $value) {
            if(Engine_Api::_()->getItem('sesgdpr_service', $value))
              Engine_Api::_()->getItem('sesgdpr_service', $value)->delete();
          }
        }
      }

     $table = Engine_Api::_()->getDbTable('services','sesgdpr');
     $select = $table->select();

    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

  }
  function addServiceAction(){
    $id = $this->_getParam('id');
    $this->view->item = $service = Engine_Api::_()->getItem('sesgdpr_service',$id);

		 $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesgdpr_Form_Admin_Service();

    if (!$this->getRequest()->isPost()){
      if($id)
        $form->populate($service->toArray());
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    $values = $form->getValues();

    $db = Engine_Api::_()->getItemTable('sesgdpr_service')->getAdapter();
    $db->beginTransaction();
    try {
       $table = Engine_Api::_()->getItemTable('sesgdpr_service');
       if(!$id)
       $service = $table->createRow();
       $service->setFromArray($form->getValues());
       $service->save();
       $db->commit();
    }catch(Exception $e){
      throw $e;
    }

    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('Service successfully saved.')
    ));
  }
  function subjectAccessAction(){
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_accessrequest');

     if ($this->getRequest()->isPost()) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $values = $this->getRequest()->getPost();
        foreach ($values as $key => $value) {
          if ($key == 'delete_' . $value) {
            $form = Engine_Api::_()->getItem('sesgdpr_content', $value)->delete();
          }
        }
      }

     $this->view->formFilter = $formFilter = new Sesgdpr_Form_Admin_Filter();
     $table = Engine_Api::_()->getDbTable('contents','sesgdpr');
     $select = $table->select()->where('type =?','service-access')->order('creation_date DESC');
     $formFilter->populate($this->_getAllParams());


     if(!empty($_GET['name']))
        $select->where('name LIKE ("%'.$_GET['name'].'%")') ;
     if(!empty($_GET['email']))
        $select->where('email LIKE ("%'.$_GET['email'].'%")');
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

  }

  function unsubscribeAction(){
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_unsubscribe');


     if ($this->getRequest()->isPost()) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $values = $this->getRequest()->getPost();
        foreach ($values as $key => $value) {
          if ($key == 'delete_' . $value) {
            $form = Engine_Api::_()->getItem('sesgdpr_content', $value)->delete();
          }
        }
      }

     $this->view->formFilter = $formFilter = new Sesgdpr_Form_Admin_Filter();
     $table = Engine_Api::_()->getDbTable('contents','sesgdpr');
     $select = $table->select()->where('type =?','unsubscribe')->order('creation_date DESC');
     $formFilter->populate($this->_getAllParams());


     if(!empty($_GET['name']))
        $select->where('name LIKE ("%'.$_GET['name'].'%")') ;
     if(!empty($_GET['email']))
        $select->where('email LIKE ("%'.$_GET['email'].'%")');
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  function forgotMeAction(){
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_forgotme');

     if ($this->getRequest()->isPost()) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $values = $this->getRequest()->getPost();
        foreach ($values as $key => $value) {
          if ($key == 'delete_' . $value) {
            $form = Engine_Api::_()->getItem('sesgdpr_content', $value)->delete();
          }
        }
      }

     $this->view->formFilter = $formFilter = new Sesgdpr_Form_Admin_Filter();
     $table = Engine_Api::_()->getDbTable('contents','sesgdpr');
     $select = $table->select()->where('type =?','forgot')->order('creation_date DESC');
     $formFilter->populate($this->_getAllParams());


     if(!empty($_GET['name']))
        $select->where('name LIKE ("%'.$_GET['name'].'%")') ;
     if(!empty($_GET['email']))
        $select->where('email LIKE ("%'.$_GET['email'].'%")');
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  function auditLogAction(){
     $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_audit');

     $this->view->formFilter = $formFilter = new Sesgdpr_Form_Admin_Audit();


     $table = Engine_Api::_()->getDbTable('audits','sesgdpr');
     $select = $table->select()->where('email =?',!empty($_GET['email']) ? $_GET['email'] : "as");
     $formFilter->populate($this->_getAllParams());

    if(!empty($_GET['email']))
        $select->where('email LIKE ("%'.$_GET['email'].'%")');
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));


  }

    public function usesettingschangeAction() {

        $db = Engine_Db_Table::getDefaultAdapter();
        $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_modules')
            ->where('name = ?', 'sesgdpr')
            ->where('version <= ?', '4.10.3p6');
        $is_enabled = $select->query()->fetchObject();
        if (!empty($is_enabled)) {
            $settings = Engine_Api::_()->getDbTable('settings', 'user');
            $settingsTable = Engine_Api::_()->getDbTable('settings','core');
            $select = $settingsTable->select()->where('name LIKE "%.user.consent"');
            $consent = $settingsTable->fetchAll($select);
            $coreSettings = Engine_Api::_()->getApi('settings', 'core');
            foreach($consent as $value){
                $user_id = str_replace('.user.consent','',$value['name']);
                $user = Engine_Api::_()->getItem('user',$user_id);
                if($user){
                    $settings->setSetting($user,'user_consent',1);
                    $consentTime = $coreSettings->getSetting($user_id.'.user.consent.time');
                    if($consentTime){
                        $coreSettings->removeSetting($user_id.'.user.consent.time');
                        $settings->setSetting($user,'user_consent_time',$consentTime);
                    }
                    $consentPopup = $coreSettings->getSetting($user_id.'.gdpr.popup.consent');
                    if($consentPopup){
                        $coreSettings->removeSetting($user_id.'.gdpr.popup.consent');
                        $settings->setSetting($user,'gdpr_popup_consent',1);
                    }
                }
                $value->delete();
            }
        }

		$referralurl = $this->_getParam('referralurl', false);
		if($referralurl == 'install') {
			$this->_redirect('install/manage');
		} elseif($referralurl == 'query') {
			$this->_redirect('install/manage/complete');
		}
	}

    public function supportAction() {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesgdpr_admin_main', array(), 'sesgdpr_admin_main_support');
    }
}
