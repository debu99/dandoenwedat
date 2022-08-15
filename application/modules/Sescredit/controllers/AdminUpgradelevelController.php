<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminUpgradelevelController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_AdminUpgradelevelController extends Core_Controller_Action_Admin {

  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_upgrequest');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main_upgrequest', array(), 'sescredit_admin_main_pointsetting');
    $levelPointTable = Engine_Api::_()->getDbTable('levelpoints', 'sescredit');
    $levelPointTableName = $levelPointTable->info('name');
    $levelsTable = Engine_Api::_()->getDbTable('levels', 'authorization');
    $levelsTableName = $levelsTable->info('name');
    $select = $levelsTable->select()
            ->setIntegrityCheck(false)
            ->from($levelsTable->info('name'), array('title', 'type', 'level_id'))
            ->joinLeft($levelPointTableName, $levelPointTableName . '.level_id = ' . $levelsTableName . '.level_id', array('point'))
            ->where('type != ?', 'public');
    $this->view->levels = $levelsTable->fetchAll($select);
    if (!$this->getRequest()->isPost())
      return;
    foreach ($_POST['level'] as $key => $value) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->query('INSERT INTO engine4_sescredit_levelpoints (level_id, point) VALUES ("' . $key . '", "' . $value.'") ON DUPLICATE KEY UPDATE point = "' . $value . '"');
    }
    return $this->_helper->redirector->gotoRoute(array());
  }

  public function manageRequestAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_upgrequest');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main_upgrequest', array(), 'sescredit_admin_main_managerequest');
    $this->view->formFilter = $formFilter = new Sescredit_Form_Admin_Member_Filter();
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $upgradeUserTable = Engine_Api::_()->getDbTable('upgradeusers', 'sescredit');
    $upgradeUserTableName = $upgradeUserTable->info('name');
    $select = $userTable->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('displayname', 'user_id'))
            ->join($upgradeUserTableName, $upgradeUserTableName . '.owner_id = ' . $userTableName . '.user_id', array('*'))
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'user_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));
    if (!empty($_GET['owner_name']))
      $select->where($userTableName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');
    if (isset($_GET['status']) && $_GET['status'] != '')
      $select->where($upgradeUserTableName . '.status = ?', $_GET['status']);
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $values = array_merge(array(
        'order' => isset($_GET['order']) ? $_GET['order'] : '',
        'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
            ), $values);
    $this->view->assign($values);
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
      if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
      $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function approveAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Accept this Request ?');
    $form->setDescription('Are you sure that you want to accept this ugrade request entry? It will not be recoverable after being accepted.');
    $form->submit->setLabel('Accept');
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $upgradeUser = Engine_Api::_()->getItem('sescredit_upgradeuser', $this->_getParam('id'));
        $ownerId = $upgradeUser->owner_id;
        $upgradeUser->status = 1;
        $upgradeUser->save();

        $levelPointTable = Engine_Api::_()->getDbTable('levelpoints', 'sescredit');
        $point = $levelPointTable->select()
                ->from($levelPointTable->info('name'), 'point')
                ->where('level_id =?', $upgradeUser->level_id)
                ->query()
                ->fetchColumn();

        $creditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
        $userTotlPoint = $creditDetailTable->select()
                ->from($creditDetailTable->info('name'), 'total_credit')
                ->where('owner_id =?', $ownerId)
                ->query()
                ->fetchColumn();
        if ($userTotlPoint < $point) {
          $form->addError("user request you are going to approve does not have sufficient point for reedem.");
          return;
        }
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'sescredit_upgrade_member', 'owner_id' => $ownerId, 'action_id' => 0, 'object_id' => 0, 'point_type' => 'upgrade_level', 'point' => $point));
        $user = Engine_Api::_()->getItem('user', $ownerId);
        $user->level_id = $upgradeUser->level_id;
        $user->save();
        $db->commit();
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $viewer, 'sescredit_approve_upgrade_request');
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sescredit_approve_upgrade_request', array('owner_title' => $user->getTitle(), 'new_member_level' => Engine_Api::_()->getItem('authorization_level', $upgradeUser->level_id)->title));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('You have been accepted member upgrade request.')
      ));
    }
  }

  public function rejectAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Reject this Request ?');
    $form->setDescription('Are you sure that you want to reject this ugrade request entry? It will not be recoverable after being rejeted.');
    $form->submit->setLabel('Reject');
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $upgradeUser = Engine_Api::_()->getItem('sescredit_upgradeuser', $this->_getParam('id'));
        $upgradeUser->status = 2;
        $upgradeUser->save();
        $db->commit();
        $user = Engine_Api::_()->getItem('user', $upgradeUser->owner_id);
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $viewer, 'sescredit_reject_upgrade_request');
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sescredit_reject_upgrade_request', array('owner_title' => $user->getTitle(), 'new_member_level' => Engine_Api::_()->getItem('authorization_level', $upgradeUser->level_id)->title));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('You have been rejected member upgrade request.')
      ));
    }
  }

}
