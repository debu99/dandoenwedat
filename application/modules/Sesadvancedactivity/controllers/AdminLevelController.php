<?php

class Sesadvancedactivity_AdminLevelController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_main_level');

    // Get level id
    if (null !== ($id = $this->_getParam('id'))) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if (!$level instanceof Authorization_Model_Level) {
      throw new Engine_Exception('missing level');
    }

    $id = $level->level_id;

    // Make form
    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Level(array(
      'public' => (in_array($level->type, array('public'))),
      'moderator' => (in_array($level->type, array('admin', 'moderator'))),
    ));
    $form->level_id->setValue($id);
    $level_id_public = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    if ($level_id_public) {
      $form->level_id->removeMultiOption($level_id_public);
    }

    // Populate values
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed('sesadvactivity', $id, array_keys($form->getValues())));

    // Check post
    if (!$this->getRequest()->isPost()) {
      return;
    }

    // Check validitiy
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process

    $values = $form->getValues();

    // Form elements with NonBoolean values
    $nonBooleanSettings = $form->nonBooleanFields();

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();

    try {
      // Set permissions
      $permissionsTable->setAllowed('sesadvactivity', $id, $values, '', $nonBooleanSettings);

      // Commit
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
  }
}
