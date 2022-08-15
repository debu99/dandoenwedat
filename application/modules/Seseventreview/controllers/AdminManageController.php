<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminManageController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_AdminManageController extends Core_Controller_Action_Admin {

  public function reviewSettingsAction() {	
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_reviewsettings');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventreview_admin_main', array(), 'seseventreview_admin_main_reviewsettings');

    $this->view->form = $form = new Seseventreview_Form_Admin_Manage_ReviewSettings();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array());
    }
  }

  public function manageReviewsAction() {
  
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_reviewsettings');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventreview_admin_main', array(), 'seseventreview_admin_main_managereview');

    $module_name = 'sesevent'; //$this->_getParam('module_name', null);

    $this->view->formFilter = $formFilter = new Seseventreview_Form_Admin_Manage_Filter();

    //Process form
    $values = array();
    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }

    foreach ($_GET as $key => $value) {
      if ('' === $value) {
        unset($_GET[$key]);
      } else
        $values[$key] = $value;
    }

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $video = Engine_Api::_()->getItem('eventreview', $value)->delete();
        }
      }
    }

    $table = Engine_Api::_()->getDbtable('eventreviews', 'seseventreview');
    $tableName = $table->info('name');
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
		$tableEventName = Engine_Api::_()->getItemTable('sesevent_event')->info('name');
    $select = $table->select()
            ->from($tableName)
            ->setIntegrityCheck(false)
            ->joinLeft($tableUserName, "$tableUserName.user_id = $tableName.owner_id", 'username')
						->joinLeft($tableEventName, "$tableEventName.event_id = $tableName.content_id",null);
		$select->where($tableEventName.'.event_id != ?','');
    $select->order('review_id DESC');

    // Set up select info
    if (!empty($_GET['title']))
      $select->where($tableName.'.title LIKE ?', '%' . $values['title'] . '%');
    if (!empty($_GET['event_title']))
      $select->where($tableEventName.'.title LIKE ?', '%' . $values['event_title'] . '%');
    if (isset($_GET['featured']) && $_GET['featured'] != '')
      $select->where('featured = ?', $values['featured']);

    if (isset($_GET['new']) && $_GET['new'] != '')
      $select->where('new = ?', $values['new']);

    if (isset($_GET['sponsored']) && $_GET['sponsored'] != '')
      $select->where('sponsored = ?', $values['sponsored']);

    if (!empty($values['creation_date']))
      $select->where('date(' . $tableName . '.creation_date) = ?', $values['creation_date']);

    if (!empty($_GET['owner_name']))
      $select->where($tableUserName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');

    if (isset($_GET['offtheday']) && $_GET['offtheday'] != '')
      $select->where($tableName . '.offtheday =?', $values['offtheday']);

    $page = $this->_getParam('page', 1);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(50);
    $paginator->setCurrentPageNumber($page);
  }

 
  public function viewAction() {
    $this->view->item = Engine_Api::_()->getItem('eventreview', $this->_getParam('id', null));
  }

  //Delete entry
  public function deleteReviewAction() {

    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete Review?');
    $form->setDescription('Are you sure that you want to delete this review? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $review = Engine_Api::_()->getItem('eventreview', $this->_getParam('id'));
        $review->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => 10,
                  'parentRefresh' => 10,
                  'messages' => array('You have successfully delete entry.')
      ));
    }
  }

  public function levelSettingsAction() {
  
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_reviewsettings');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventreview_admin_main', array(), 'seseventreview_admin_main_levelsettings');

    //Get level id
    if (null !== ($id = $this->_getParam('level_id', $this->_getParam('id'))))
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    else
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();

    if (!$level instanceof Authorization_Model_Level)
      throw new Engine_Exception('missing level');

    $id = $level->level_id;

    //Make form
    $this->view->form = $form = new Seseventreview_Form_Admin_Manage_Level(array(
        'public' => ( in_array($level->type, array('public')) ),
        'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
    ));
    $form->level_id->setValue($id);

    $content_type = 'eventreview';
    $module_name = $this->_getParam('module_name', null);

    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed($content_type, $id, array_keys($form->getValues())));

    //Check post
    if (!$this->getRequest()->isPost())
      return;

    //Check validitiy
    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    //Process
    $values = $form->getValues();

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();
    try {
      //Set permissions
      $permissionsTable->setAllowed($content_type, $id, $values);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
  }
}