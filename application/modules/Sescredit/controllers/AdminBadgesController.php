<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminBadgesController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_AdminBadgesController extends Core_Controller_Action_Admin {

  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_badges');
    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main_badges', array(), 'sescredit_admin_main_badgegeneralsetting');
    $this->view->form = $form = new Sescredit_Form_Admin_Badge_Setting();
    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
      foreach ($values as $key => $value) {
        if ($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function manageAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_badges');
    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main_badges', array(), 'sescredit_admin_main_managebadges');
    $this->view->formFilter = $formFilter = new Sescredit_Form_Admin_Badge_Filter();
    $table = Engine_Api::_()->getDbtable('badges', 'sescredit');
    $select = $table->select();
    // Process form
    $values = array();
    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }
    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }
    $values = array_merge(array('order_direction' => 'DESC'), $values);
    $this->view->assign($values);
    if (!empty($values['title']))
      $select->where('title LIKE ?', '%' . $values['title'] . '%');
    if (isset($values['enabled']) && $values['enabled'] != -1)
      $select->where('enabled = ?', $values['enabled']);
    $valuesCopy = array_filter($values);
    if (isset($values['enabled']) && $values['enabled'] == 0) {
      $valuesCopy['enabled'] = 0;
    }
    $select->order('badge_id ASC');
    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $page = $this->_getParam('page', 1);
    $this->view->paginator = $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(200);
    $this->view->formValues = $valuesCopy;
  }

  public function addAction() {
    //Set Layout
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->badge_id = $id = $this->_getParam('id');
    $this->view->form = $form = new Sescredit_Form_Admin_Badge_Add();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $badgeTable = Engine_Api::_()->getDbtable('badges', 'sescredit');
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $values = $form->getValues();
        $badge = $badgeTable->createRow();
        $badge->setFromArray($values);
        $badge->save();
        if (isset($_FILES['photo_id']) && $values['photo_id']) {
          $badgeId = Engine_Api::_()->sescredit()->setPhoto($form->photo_id, array('badge_id' => $badge->badge_id));
          if (!empty($badgeId))
            $badge->photo_id = $badgeId;
          $badge->save();
        }
        $db->commit();
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => 10,
            'parentRefresh' => 10,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully add badge.'))
        ));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function editAction() {
    //Set Layout
    $this->_helper->layout->setLayout('admin-simple');
    $badge = Engine_Api::_()->getItem('sescredit_badge', $this->_getParam('id'));
    $this->view->form = $form = new Sescredit_Form_Admin_Badge_Edit();
    $form->getElement('photo_id')->setRequired(false)->setAllowEmpty(true);
    $form->setTitle('Edit This Badge');
    $form->setDescription("Here, you can edit badge information.");
    $form->populate($badge->toArray());
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      if (empty($values['photo_id']))
        unset($values['photo_id']);
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $badge->setFromArray($values);
        $badge->save();
        if (isset($_FILES['photo_id']) && !empty($_FILES['photo_id']['name'])) {
          $previousBadge = $badge->photo_id;
          $newBadge = Engine_Api::_()->sescredit()->setPhoto($form->photo_id, array('badge_id' => $badge->badge_id));
          if (!empty($newBadge)) {
            if ($previousBadge) {
              $badgePhoto = Engine_Api::_()->getItem('storage_file', $previousBadge);
              if ($badgePhoto)
                $badgePhoto->delete();
            }
            $badge->photo_id = $newBadge;
            $badge->save();
          }
        }
        $db->commit();
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => 10,
            'parentRefresh' => 10,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully edited badge.'))
        ));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function deleteAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete This Badge?');
    $form->setDescription('Are you sure that you want to delete this badge entry? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $badge = Engine_Api::_()->getItem('sescredit_badge', $this->_getParam('id'));
        $badgePhoto = Engine_Api::_()->getItem('storage_file', $badge->photo_id);
        if ($badgePhoto)
          $badgePhoto->delete();
        $badge->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('You have been deleted row successfully.')
      ));
    }
  }

  public function enableAction() {
    $badge_id = $this->_getParam('id');
    if (!empty($badge_id)) {
      $badge = Engine_Api::_()->getItem('sescredit_badge', $badge_id);
      $badge->enabled = !$badge->enabled;
      $badge->save();
    }
    if (isset($_SERVER['HTTP_REFERER']))
      $url = $_SERVER['HTTP_REFERER'];
    else
      $url = 'admin/sescredit/badges/manage';
    $this->_redirect($url);
  }

}
