<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminHostController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AdminHostController extends Core_Controller_Action_Admin {
  public function indexAction() {
    $db = Engine_Db_Table::getDefaultAdapter();
		$this->view->formFilter = $form = new Sesevent_Form_Admin_FilterHost();
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_managehost');
		$values = array();
		 if (!empty($_GET['host_name']))
      $values['host_name'] = $_GET['host_name'];
		if (!empty($_GET['host_type']))
      $values['host_type'] = $_GET['host_type'];
		if (!empty($_GET['offtheday']))
      $values['offtheday'] = $_GET['offtheday'];
		if (!empty($_GET['sponsored']))
      $values['sponsored'] = $_GET['sponsored'];
		if (!empty($_GET['featured']))
      $values['featured'] = $_GET['featured'];
		if (!empty($_GET['verified']))
      $values['verified'] = $_GET['verified'];
		$getValues = $this->_getAllParams();
		$form->populate($getValues);
    $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getAllHosts($values);
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  //Delete host member
  public function deleteAction() {
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        Engine_Api::_()->getDbtable('hosts', 'sesevent')->delete(array('host_id =?' => $this->_getParam('host_id')));
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully delete host entry.'))
      ));
    }
    $this->renderScript('admin-host/delete.tpl');
  }
  //Delete multiple host
  public function multiDeleteAction() {
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $sesevent_host = Engine_Api::_()->getItem('sesevent_host', (int) $value);
          if (!empty($sesevent_host))
            $sesevent_host->delete();
        }
      }
    }
    $this->_redirect('admin/sesevent/host');
  }
  //Enable Action
  public function verifiedAction() {
    $id = $this->_getParam('host_id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesevent_host', $id);
      $item->verified = !$item->verified;
      $item->save();
    }
    $this->_redirect('admin/sesevent/host');
  }

  //Featured Action
  public function featuredAction() {

    $id = $this->_getParam('host_id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesevent_host', $id);
      $item->featured = !$item->featured;
      $item->save();
    }
    $this->_redirect('admin/sesevent/host');
  }

  //Sponsored Action
  public function sponsoredAction() {

    $id = $this->_getParam('host_id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesevent_host', $id);
      $item->sponsored = !$item->sponsored;
      $item->save();
    }
    $this->_redirect('admin/sesevent/host');
  }
}