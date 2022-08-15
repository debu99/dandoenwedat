<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminLightboxController.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_AdminLightboxController extends Core_Controller_Action_Admin {
	public function indexAction(){
    // Make navigation
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_manage', array(), 'sesbasic_admin_memberlevel');
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_manage');
    // Get level id
    if (null !== ($id = $this->_getParam('id'))) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }
    if (!$level instanceof Authorization_Model_Level) {
      throw new Engine_Exception('missing level');
    }
    $level_id = $id = $level->level_id;
    // Make form
    $this->view->form = $form = new Sesbasic_Form_Admin_Settings_Level(array(
        'public' => ( in_array($level->type, array('public')) ),
        'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
    ));		
    $form->level_id->setValue($id);
    // Populate values
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed('sesbasic_video', $id, array_keys($form->getValues())));
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
    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();
    try {
      // Set permissions
      $permissionsTable->setAllowed('sesbasic_video', $id, $values);
      // Commit
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
	}
	
	public function videoAction(){
   // Make navigation
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_manage', array(), 'sesbasic_admin_videolightbox');
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_manage');

    $this->view->form = $form = new Sesbasic_Form_Admin_Lightbox();
		
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $values = $form->getValues();

      if (!$values['sesbasic_private_photo'])
        unset($values['sesbasic_private_photo']);

      if (isset($values['dummy']) || $values['dummy'] == '')
        unset($values['dummy']);

      foreach ($values as $key => $value)
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);

      $form->addNotice('Your changes have been saved.');

      $this->_helper->redirector->gotoRoute(array());
    }
  
	}
}