<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminManageController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AdminManageController extends Core_Controller_Action_Admin {
  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_manage');
    $this->view->formFilter = $formFilter = new Sesevent_Form_Admin_Filter();
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $event = Engine_Api::_()->getItem('sesevent_event', $value);
          $event->delete();
        }
      }
    }
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $values = array_merge(array(
        'order' => isset($_GET['order']) ? $_GET['order'] :'',
    'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
            ), $values);
    $this->view->assign($values);
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
    $eventTable = Engine_Api::_()->getDbTable('events', 'sesevent');
    $eventsTableName = $eventTable->info('name');
    $select = $eventTable->select()
            ->setIntegrityCheck(false)
            ->from($eventsTableName)
						->where('is_delete =?',0)
            ->joinLeft($tableUserName, "$eventsTableName.user_id = $tableUserName.user_id", 'username')
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'event_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));
    if (!empty($_GET['name']))
      $select->where($eventsTableName . '.title LIKE ?', '%' . $_GET['name'] . '%');

    if (!empty($_GET['owner_name']))
      $select->where($tableUserName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');

    if (!empty($_GET['category_id']))
      $select->where($eventsTableName . '.category_id =?', $_GET['category_id']);

    if (!empty($_GET['subcat_id']))
      $select->where($eventsTableName . '.subcat_id =?', $_GET['subcat_id']);

    if (!empty($_GET['subsubcat_id']))
      $select->where($eventsTableName . '.subsubcat_id =?', $_GET['subsubcat_id']);

    if (isset($_GET['featured']) && $_GET['featured'] != '')
      $select->where($eventsTableName . '.featured = ?', $_GET['featured']);

    if (isset($_GET['sponsored']) && $_GET['sponsored'] != '')
      $select->where($eventsTableName . '.sponsored = ?', $_GET['sponsored']);

    if (isset($_GET['verified']) && $_GET['verified'] != '')
      $select->where($eventsTableName . '.verified = ?', $_GET['verified']);
		if (isset($_GET['is_approved']) && $_GET['is_approved'] != '')
      $select->where($eventsTableName . '.is_approved = ?', $_GET['is_approved']);
		if (isset($_GET['offtheday']) && $_GET['offtheday'] != '')
      $select->where($eventsTableName . '.offtheday = ?', $_GET['offtheday']);
    if (isset($_GET['rating']) && $_GET['rating'] != '') {
      if ($_GET['rating'] == 1):
        $select->where($eventsTableName . '.rating <> ?', 0);
      elseif ($_GET['rating'] == 0 && $_GET['rating'] != ''):
        $select->where($eventsTableName . '.rating = ?', $_GET['rating']);
      endif;
    }

    if (!empty($_GET['creation_date']))
      $select->where($eventsTableName . '.creation_date LIKE ?', $_GET['creation_date'] . '%');

    if (isset($_GET['subcat_id']))
      $formFilter->subcat_id->setValue($_GET['subcat_id']);

    if (isset($_GET['subsubcat_id']))
      $formFilter->subsubcat_id->setValue($_GET['subsubcat_id']);
		$urlParams = array();
		foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey=>$urlParamsVal){
			if($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
				continue;
			$urlParams['query'][$urlParamsKey] = $urlParamsVal;
		}
		$this->view->urlParams = $urlParams;
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(50);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
	public function slidesAction(){
		 if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $slide = Engine_Api::_()->getItem('sesevent_slidephoto', $value);
          $slide->delete();
        }
      }
    }
		 $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_manageslidephotos');
		 $this->view->slides = Engine_Api::_()->getDbTable('slidephotos', 'sesevent')->getSlides(array('active' => false));
	}
	 public function orderAction() {
    if (!$this->getRequest()->isPost())
      return;

    $table = Engine_Api::_()->getDbtable('slidephotos', 'sesevent');
    $slides = $table->fetchAll($table->select());
    foreach ($slides as $slide) {
      $order = $this->getRequest()->getParam('columns_' . $slide->slidephoto_id);
      if ($order) {
        $slide->order = $order;
        $slide->save();
      }
    }
    echo true;die;
  }
	public function addSlideAction(){
		 $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_manageslidephotos');
		 $this->view->form = $form = new Sesevent_Form_Admin_Slide_Create();
		 //If not post or form not valid, return
    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    //Process
    $table = Engine_Api::_()->getDbtable('slidephotos', 'sesevent');
    $values = $form->getValues();

    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $slide = $table->createRow();
      $slide->setFromArray($values);
      $slide->save();
			$slide->order = $slide->getIdentity();
			if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != '')
				$slide->setPhoto($form->file);
			$slide->save();
      $db->commit();
      return $this->_helper->redirector->gotoRoute(array('module' => 'sesevent', 'controller' => 'manage', 'action' => 'slides'), 'admin_default', true);
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
	}
	public function editSlideAction(){
		 $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_manageslidephotos');
		 $this->view->form = $form = new Sesevent_Form_Admin_Slide_Edit();
		 $id = $this->_getParam('id',false);
		 if(!$id)
		 	return;
		 $slide = Engine_Api::_()->getItem('sesevent_slidephoto', $id);
		 $form->populate($slide->toArray());
		 //If not post or form not valid, return
    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    //Process
    $table = Engine_Api::_()->getDbtable('slidephotos', 'sesevent');
    $values = $form->getValues();

    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $slide->setFromArray($values);
      $slide->save();
			$slide->order = $slide->getIdentity();
			if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != '')
				$slide->setPhoto($form->file);
			$slide->save();
      $db->commit();
      return $this->_helper->redirector->gotoRoute(array('module' => 'sesevent', 'controller' => 'manage', 'action' => 'slides'), 'admin_default', true);
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
	}
	public function deleteSlideAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->sesevent_id = $id = $this->_getParam('id');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete Slide?');
    $form->setDescription('Are you sure that you want to delete this slide entry? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    //Check post
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $event = Engine_Api::_()->getItem('sesevent_slidephoto', $id);
        $event->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('You have successfully delete slide.')
      ));
    }
  }
  public function deleteAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->sesevent_id = $id = $this->_getParam('id');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete Event?');
    $form->setDescription('Are you sure that you want to delete this event? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    //Check post
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $event = Engine_Api::_()->getItem('sesevent_event', $id);
        $event->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('You have successfully delete event.')
      ));
    }
  }
	
	//Approved Action
    public function approvedAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $event_id = $this->_getParam('id');
        if (!empty($event_id)) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
            $event->is_approved = !$event->is_approved;
            $event->save();

            if ($event->is_approved) {
                $mailType = 'sesevent_event_adminapproved';
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $getActivity = $activityApi->getActionsByObject($event);
                if (!count($getActivity)) {
                    $action = $activityApi->addActivity($viewer, $event, 'sesevent_create');
                    if ($action) {
                        $activityApi->attachActivity($action, $event);
                    }
                }

                $userTable = Engine_Api::_()->getItemTable('user');
                $users = $userTable->fetchAll();
                //email to user
                foreach ($users as $user) {
                    if ($user->getIdentity() != $event->getOwner()->getIdentity()) {
                        if ($event->is_webinar) {
                            if ($event->starttime <= date('Y-m-d h:m:s', time() + 3 * 24 * 60 * 60)) {
                                //notify and email to user register its for last minute event
                                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                    $user,
                                    $viewer,
                                    $event,
                                    'sesevent_last_minute_online_event',
                                    array(
                                        'queue' => true,
                                        'object_date' => $event->getTime('starttime', 'j M'),
                                        'object_time' => $event->getTime('starttime', 'H:i')
                                    )
                                );
                            } else {
                                //notify and email to user register its for online event
                                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                    $user,
                                    $viewer,
                                    $event,
                                    'sesevent_new_online_event',
                                    array(
                                        'queue' => true,
                                        'object_date' => $event->getTime('starttime', 'j M'),
                                        'object_time' => $event->getTime('starttime', 'H:i')
                                    )
                                );
                            }
                        } elseif (isset($event->region_id)) {
                            //notify and email to user register its for new event in their region
                            if ($user->checkInRegion($event->region_id)) {
                                if ($event->starttime <= date('Y-m-d h:m:s', time() + 3 * 24 * 60 * 60)) {
                                    //notify and email to user register its for new event in their region
                                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                        $user,
                                        $viewer,
                                        $event,
                                        'sesevent_last_minute_event',
                                        array(
                                            'queue' => true,
                                            'queue' => true,
                                            'object_date' => $event->getTime('starttime', 'j M'),
                                            'object_time' => $event->getTime('starttime', 'H:i')
                                        )
                                    );
                                } else {
                                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                        $user,
                                        $viewer,
                                        $event,
                                        'sesevent_new_event',
                                        array(
                                            'queue' => true,
                                            'object_date' => $event->getTime('starttime', 'j M'),
                                            'object_time' => $event->getTime('starttime', 'H:i')

                                        )
                                    );
                                }
                            }
                        }
                    }
                }
            } else {
                $mailType = 'sesevent_event_admindisapproved';
            }
            //Event approved mail send to event owner
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($event->getOwner(), $mailType, array('event_title' => $event->title, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        }
        $this->_redirect('admin/sesevent/manage');
    }
	
	//Active slide Action
  public function slideactiveAction() {
		$viewer = Engine_Api::_()->user()->getViewer();
    $slide_id = $this->_getParam('id');
    if (!empty($slide_id)) {
      $slide = Engine_Api::_()->getItem('sesevent_slidephoto', $slide_id);
      $slide->active = !$slide->active;
      $slide->save();
    }
    $this->_redirect('admin/sesevent/manage/slides');
  }
	
  //Featured Action
  public function featuredAction() {
    $event_id = $this->_getParam('id');
    if (!empty($event_id)) {
      $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
      $event->featured = !$event->featured;
      $event->save();
    }
		if(isset($_SERVER['HTTP_REFERER']))
			$url = $_SERVER['HTTP_REFERER'];
		else
			$url = 'admin/sesevent/manage';
    $this->_redirect($url);
  }

  //Sponsored Action
  public function sponsoredAction() {
    $event_id = $this->_getParam('id');
    if (!empty($event_id)) {
      $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
      $event->sponsored = !$event->sponsored;
      $event->save();
    }
		if(isset($_SERVER['HTTP_REFERER']))
			$url = $_SERVER['HTTP_REFERER'];
		else
			$url = 'admin/sesevent/manage';
    $this->_redirect($url);
  }

  //Verify Action
  public function verifyAction() {
    $event_id = $this->_getParam('id');
    if (!empty($event_id)) {
      $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
      $event->verified = !$event->verified;
      $event->save();
    }
		if(isset($_SERVER['HTTP_REFERER']))
			$url = $_SERVER['HTTP_REFERER'];
		else
			$url = 'admin/sesevent/manage';
    $this->_redirect($url);
  }

  public function ofthedayAction() {
    $db = Engine_Db_Table::getDefaultAdapter();
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $type = $this->_getParam('type');
    $param = $this->_getParam('param');

    $this->view->form = $form = new Sesevent_Form_Admin_Oftheday();
    if ($type == 'sesevent_event') {
      $item = Engine_Api::_()->getItem('sesevent_event', $id);
      $form->setTitle("Event of the Day");
      $form->setDescription('Here, choose the start date and end date for this event to be displayed as "Event of the Day".');
      if (!$param)
        $form->remove->setLabel("Remove as Event of the Day");
      $table = 'engine4_sesevent_events';
      $item_id = 'event_id';
    } elseif ($type == 'sesevent_list') {
      $item = Engine_Api::_()->getItem('sesevent_list', $id);
      $form->setTitle("List of the Day");
      if (!$param)
        $form->remove->setLabel("Remove as List of the Day");
      $form->setDescription('Here, choose the start date and end date for this list to be displayed as "List of the Day".');
      $table = 'engine4_sesevent_lists';
      $item_id = 'list_id';
    } elseif ($type == 'sesevent_host') {
      $item = Engine_Api::_()->getItem('sesevent_host', $id);
      $form->setTitle("Host of the Day");
      if (!$param)
        $form->remove->setLabel("Remove as Host of the Day");
      $form->setDescription('Here, choose the start date and end date for this host to be displayed as "Host of the Day".');
      $table = 'engine4_sesevent_hosts';
      $item_id = 'host_id';
    }

    if (!empty($id))
      $form->populate($item->toArray());

    if ($this->getRequest()->isPost()) {
      if (!$form->isValid($this->getRequest()->getPost())) {
        return;
      }
      $values = $form->getValues();

      $start = strtotime($values['startdate']);
      $end = strtotime($values['enddate']);

      $values['startdate'] = date('Y-m-d', $start);
      $values['enddate'] = date('Y-m-d', $end);

      $db->update($table, array('startdate' => $values['startdate'], 'enddate' => $values['enddate']), array("$item_id = ?" => $id));
      if (@$values['remove']) {
        $db->update($table, array('offtheday' => 0), array("$item_id = ?" => $id));
      } else {
        $db->update($table, array('offtheday' => 1), array("$item_id = ?" => $id));
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('')
      ));
    }
  }
  
  //view item function
  public function viewListAction() {
    $this->view->type = $type = $this->_getParam('type', 1);
    $id = $this->_getParam('id', 1);
    $item = Engine_Api::_()->getItem($type, $id);
    $this->view->item = $item;
  }
  
  public function viewAction() {
    $this->view->item = Engine_Api::_()->getItem('sesevent_event', $this->_getParam('id', null));
  }
  
  public function featureSponsoredAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->event_id = $id = $this->_getParam('id');
    $this->view->status = $status = $this->_getParam('status');
    $this->view->category = $category = $this->_getParam('category');
    $this->view->params = $params = $this->_getParam('param');
    if ($status == 1)
      $statusChange = ' ' . $category;
    else
      $statusChange = 'un' . $category;

    //$this->view->statusChange = $statusChange;
    // Check post
    //if( $this->getRequest()->isPost())
    // {
    if ($params == 'lists')
      $col = 'list_id';
      
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getDbtable($params, 'sesevent')->update(array(
          'is_' . $category => $status,
              ), array(
          "$col = ?" => $id,
      ));

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    header('location:' . $_SERVER['HTTP_REFERER']);
  }
  
  public function listAction() {
  
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_managelists');
    $this->view->formFilter = $formFilter = new Sesevent_Form_Admin_Manage_Filterlist();
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          Engine_Api::_()->getItem('sesevent_list', $value)->delete();
          $db = Engine_Db_Table::getDefaultAdapter();
          $db->query("DELETE FROM engine4_sesevent_listevents WHERE list_id = " . $value);
        }
      }
    }
    // Process form
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
    $table = Engine_Api::_()->getDbtable('lists', 'sesevent');
    $tableName = $table->info('name');
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
    $select = $table->select()
            ->from($tableName)
            ->setIntegrityCheck(false)
            ->joinLeft($tableUserName, "$tableUserName.user_id = $tableName.owner_id", 'username');
    $select->order('list_id DESC');
    // Set up select info

    if (!empty($_GET['title']))
      $select->where('title LIKE ?', '%' . $values['title'] . '%');

    if (isset($_GET['is_featured']) && $_GET['is_featured'] != '')
      $select->where('is_featured = ?', $values['is_featured']);

    if (isset($_GET['is_sponsored']) && $_GET['is_sponsored'] != '')
      $select->where('is_sponsored = ?', $values['is_sponsored']);

    if (!empty($values['creation_date']))
      $select->where('date(' . $tableName . '.creation_date) = ?', $values['creation_date']);

    if (!empty($_GET['owner_name']))
      $select->where($tableUserName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');

    if (isset($_GET['offtheday']) && $_GET['offtheday'] != '')
      $select->where($tableName . '.offtheday =?', $values['offtheday']);


    $page = $this->_getParam('page', 1);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber($page);
    
  }

  //delete list
  public function deleteListAction() {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete List?');
    $form->setDescription('Are you sure that you want to delete this list? It will not be recoverable after being deleted. ');
    $form->submit->setLabel('Delete');

    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      Engine_Api::_()->getItem('sesevent_list', $id)->delete();
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->query("DELETE FROM engine4_sesevent_listevents WHERE list_id = " . $id);
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('List Delete Successfully.')
      ));
    }
    // Output
    $this->renderScript('admin-manage/delete-list.tpl');
  }
}
