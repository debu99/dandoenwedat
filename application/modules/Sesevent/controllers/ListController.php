<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ListController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_ListController extends Core_Controller_Action_Standard {

  public function init() {

    //Get viewer info
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    //Get subject
    if (null !== ($list_id = $this->_getParam('list_id')) && null !== ($list = Engine_Api::_()->getItem('sesevent_list', $list_id)) && $list instanceof Sesevent_Model_List && !Engine_Api::_()->core()->hasSubject()) {
      Engine_Api::_()->core()->setSubject($list);
    }
  }

  public function browseAction() {
    $this->_helper->content->setEnabled();
  }

  //View Action
  public function viewAction() {

    //Set layout
    if ($this->_getParam('popout')) {
      $this->view->popout = true;
      $this->_helper->layout->setLayout('default-simple');
    }
		
    //Check subject
    if (!$this->_helper->requireSubject()->isValid())
      return;

    //Get viewer/subject
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    $this->view->list = $list = Engine_Api::_()->core()->getSubject('sesevent_list');
		if(!$viewer->isSelf($list->getOwner())){
			if($list->is_private){
				return $this->_forward('requireauth', 'error', 'core');
			}
		}
    //Increment view count
    if (!$viewer->isSelf($list->getOwner())) {
      $list->view_count++;
      $list->save();
    }
		 /* Insert data for recently viewed widget */
    if ($viewer->getIdentity() != 0 && isset($chanel->chanel_id)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesevent_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $list->list_id . '", "sesevent_list","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }
    //Render
    $this->_helper->content->setEnabled();
  }

  //Delete list songs Action
  public function deleteListeventAction() {

    //Get event/list
    $event = Engine_Api::_()->getItem('sesevent_listevent', $this->_getParam('listevent_id'));

    $list = $event->getParent();

    //Check song/list
    if (!$event || !$list) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid list');
      return;
    }

    //Get file
    $file = Engine_Api::_()->getItem('storage_file', $event->file_id);
    if (!$file) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid list');
      return;
    }

    $db = $event->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      Engine_Api::_()->getDbtable('listevents', 'sesevent')->delete(array('listevent_id =?' => $this->_getParam('listevent_id')));
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      $this->view->success = false;
      $this->view->error = $this->view->translate('Unknown database error');
      throw $e;
    }

    $this->view->success = true;
  }

  //Edit Action
  public function editAction() {
    //Only members can upload event
    if (!$this->_helper->requireUser()->isValid())
      return;

    //Get list
    $this->view->list = $list = Engine_Api::_()->getItem('sesevent_list', $this->_getParam('list_id'));
		$viewer = Engine_Api::_()->user()->getViewer();
		if($viewer->getIdentity() != $list->owner_id && $viewer->level_id != 1 ){
				 return $this->_forward('notfound', 'error', 'core');
		}
    //Make form
    $this->view->form = $form = new Sesevent_Form_EditList();

    $form->populate($list->toarray());

    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    $values = $form->getValues();

    unset($values['file']);

    $db = Engine_Api::_()->getDbTable('lists', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $list->title = $values['title'];
      $list->description = $values['description'];
			$list->is_private = $values['is_private'];
      $list->save();

      //Photo upload for list
      if (!empty($values['mainphoto'])) {
        $previousPhoto = $list->photo_id;
        if ($previousPhoto) {
          $listPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $listPhoto->delete();
        }
        $list->setPhoto($form->mainphoto, 'mainPhoto');
      }

      if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
        $storage = Engine_Api::_()->getItem('storage_file', $list->photo_id);
        $list->photo_id = 0;
        $list->save();
        if ($storage)
          $storage->delete();
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }
    return $this->_helper->redirector->gotoRoute(array('action'=>'edit', 'list_id'=>$list->getIdentity(),'slug'=>$list->getSlug()),'sesevent_list_view',true);
  }

  //Delete List Action
  public function deleteAction() {

    $list = Engine_Api::_()->getItem('sesevent_list', $this->getRequest()->getParam('list_id'));
		$viewer = Engine_Api::_()->user()->getViewer();
		if($viewer->getIdentity() != $list->owner_id && $viewer->level_id != 1 ){
				 return $this->_forward('notfound', 'error', 'core');
		}
    //In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    $this->view->form = $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete List?');
    $form->setDescription('Are you sure that you want to delete this list? It will not be recoverable after being deleted. ');
    $form->submit->setLabel('Delete');


    if (!$list) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("List doesn't exists or not authorized to delete");
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $list->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      //Delete all list events which is related to this list
      Engine_Api::_()->getDbtable('listevents', 'sesevent')->delete(array('list_id =?' => $this->_getParam('list_id')));
      $list->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected list has been deleted.');
    return $this->_forward('success', 'utility', 'core', array('parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sesevent_general', true), 'messages' => Array($this->view->message)));
  }

  public function addAction() {

    //Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;

    if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'addlist_event')->isValid())
      return;

    //Set song
    $event = Engine_Api::_()->getItem('sesevent_event', $this->_getParam('event_id'));
    $event_id = $event->event_id;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //Get form
    $this->view->form = $form = new Sesevent_Form_Append();
    if ($form->list_id) {
      $alreadyExistsResults = Engine_Api::_()->getDbtable('listevents', 'sesevent')->getListEvents(array('column_name' => 'list_id', 'file_id' => $event_id));

      $allListIds = array();
      foreach ($alreadyExistsResults as $alreadyExistsResult) {
        $allListIds[] = $alreadyExistsResult['list_id'];
      }

      //Populate form
      $listTable = Engine_Api::_()->getDbtable('lists', 'sesevent');
      $select = $listTable->select()
              ->from($listTable, array('list_id', 'title'));

      if ($allListIds) {
        $select->where($listTable->info('name') . '.list_id NOT IN(?)', $allListIds);
      }

      $select->where('owner_id = ?', $viewer->getIdentity());
      $lists = $listTable->fetchAll($select);
      if ($lists)
        $lists = $lists->toArray();
      foreach ($lists as $list)
        $form->list_id->addMultiOption($list['list_id'], html_entity_decode($list['title']));
    }

    //Check method/data
    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    //Get values
    $values = $form->getValues();
    if (empty($values['list_id']) && empty($values['title']))
      return $form->addError('Please enter a title or select a list.');

    //Process
    $listEventTable = Engine_Api::_()->getDbtable('lists', 'sesevent');
    $db = $listEventTable->getAdapter();
    $db->beginTransaction();
    try {
      //Existing list
      if (!empty($values['list_id'])) {

        $list = Engine_Api::_()->getItem('sesevent_list', $values['list_id']);

        //Already exists in list
        $alreadyExists = Engine_Api::_()->getDbtable('listevents', 'sesevent')->checkEventsAlready(array('column_name' => 'listevent_id', 'list_id' => $list->getIdentity(), 'listevent_id' => $event_id));

        if ($alreadyExists)
          return$form->addError($this->view->translate("This list already has this event."));
      }
      //New list
      else {
        $list = $listEventTable->createRow();
        $list->title = trim($values['title']);
        $list->description = $values['description'];
        $list->owner_id = $viewer->getIdentity();
        $list->save();
      }
      $list->event_count++;
      $list->save();
      //Add song
      $list->addEvent($event->photo_id, $event_id);
      $listID = $list->getIdentity();

      //Photo upload for list
      if (!empty($values['mainphoto'])) {
        $previousPhoto = $list->photo_id;
        if ($previousPhoto) {
          $listPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $listPhoto->delete();
        }
        $list->setPhoto($form->mainphoto, 'mainPhoto');
      }
      if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
        $storage = Engine_Api::_()->getItem('storage_file', $list->photo_id);
        $list->photo_id = 0;
        $list->save();
        if ($storage)
          $storage->delete();
      }
      $this->view->list = $list;

      //Activity Feed work
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $event, "sesevent_list_create", '', array('list' => array($list->getType(), $list->getIdentity()),
      ));
      if ($action) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $event);
      }

      $db->commit();
      //Response
      $this->view->success = true;
      $this->view->message = $this->view->translate('Event has been successfully added to your list.');
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => 300,
                  'messages' => array('Event has been successfully added to your list.')
      ));
    } catch (Sesevent_Model_Exception $e) { print_r($e);die;
      $this->view->success = false;
      $this->view->error = $this->view->translate($e->getMessage());
      $form->addError($e->getMessage());
      $db->rollback();
    } catch (Exception $e) { print_r($e);die;
      $this->view->success = false;
      $db->rollback();
    }
  }

}
