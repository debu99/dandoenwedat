<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: EventController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_EventController extends Core_Controller_Action_Standard {

  public function init() {
    $id = $this->_getParam('event_id', $this->_getParam('id', null));
    if ($id) {
      $event = Engine_Api::_()->getItem('sesevent_event', $id);
      if ($event) {
        Engine_Api::_()->core()->setSubject($event);
      }
    }
  }
	public function messageAction(){
    $id = $this->_getParam('event_id',null);
    $type = $this->_getParam('item_type', null);
    if (!$id || !$type)
      return;
    // Make form
    $this->view->form = $form = new Sesevent_Form_Compose();
    // Get params
    $viewer = Engine_Api::_()->user()->getViewer();
   
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      // Try attachment getting stuff
      $attachment = null;
      $attachment = Engine_Api::_()->getItem($type, $id);
			if($attachment->host_type == 'site'){
					$viewer = Engine_Api::_()->user()->getViewer();
					$values = $form->getValues();
				 
					$host = Engine_Api::_()->getItem('user', $attachment->host);
					// Create conversation
					$conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
									$viewer, $attachment, $values['title'], $values['body'], $attachment
					);
		
					
					Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
									$host, $viewer, $conversation, 'message_new'
					);
					
					// Increment messages counter
					Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');
			}
      // Commit
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    if ($this->getRequest()->getParam('format') == 'smoothbox') {
      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.')),
                  'smoothboxClose' => true,
      ));
    }
  	
	}
	public function calendarAction(){
	 if(empty($_GET['to']) || empty($_GET['from']) || empty($_GET['view'])){
	 		echo json_encode(array('success'=>1,'result'=>array()));die;
	 }
	 $param['to'] =  date('Y-m-d h:i:s',($_GET['to']/1000));
	 $param['from'] =  date('Y-m-d h:i:s',($_GET['from']/1000));
	 $param['viewCal'] = $_GET['view'];
	 $param['fetchAll'] = true;
	 $this->view->events = $events = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventSelect($param);
	 $counter = 0;
	 $eventDataArray = array();
	 foreach($events as $event){
		 $eventDataArray[$counter]['id'] = $event['event_id'];
		 $eventDataArray[$counter]['url'] = $this->view->url(array('id' => $event['custom_url']), 'sesevent_profile', true);
		 $eventDataArray[$counter]['title'] = $event['title'];
		 $eventDataArray[$counter]['start'] = strtotime($event['starttime'])*1000;
		 if($param['viewCal'] == 'day')
		 	$eventDataArray[$counter]['end'] = strtotime(date('Y-m-d',strtotime($event['starttime'])).' '.date('H:i:s',strtotime($event['endtime'])))*1000;
		 else
		 	$eventDataArray[$counter]['end'] = strtotime($event['endtime'])*1000;
		 //check event expiry
		 if(time() < strtotime($event['endtime']))
		 	$class = 'event-success';
		 else
		 	$class = 'event-important';
		 //$eventDataArray[$counter]['class'] = $class;
		 $counter++;
	 }
	 echo json_encode(array('success' => 1,'result'=>$eventDataArray));die;
	}
  public function inviteAction() {
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject('sesevent_event')->isValid())
      return;
    // @todo auth
    // Prepare data
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $this->view->friends = $friends = $viewer->membership()->getMembers();

    // Prepare form
    $this->view->form = $form = new Sesevent_Form_Invite();

    $count = 0;
    foreach ($friends as $friend) {
      if ($event->membership()->isMember($friend, null))
        continue;
      $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
      $count++;
    }
    $this->view->count = $count;
    // Not posting
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $table = $event->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $usersIds = $form->getValue('users');

      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      foreach ($friends as $friend) {
        if (!in_array($friend->getIdentity(), $usersIds)) {
          continue;
        }

        $event->membership()->addMember($friend)
                ->setResourceApproved($friend);

        $notifyApi->addNotification($friend, $viewer, $event, 'sesevent_invite');
      }


      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Members invited')),
                'layout' => 'default-simple',
                'parentRefresh' => true,
    ));
  }

  public function styleAction() {
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'style')->isValid())
      return;

    $user = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->core()->getSubject('sesevent_event');

    // Make form
    $this->view->form = $form = new Sesevent_Form_Style();

    // Get current row
    $table = Engine_Api::_()->getDbtable('styles', 'core');
    $select = $table->select()
            ->where('type = ?', 'sesevent')
            ->where('id = ?', $event->getIdentity())
            ->limit(1);

    $row = $table->fetchRow($select);

    // Check post
    if (!$this->getRequest()->isPost()) {
      $form->populate(array(
          'style' => ( null === $row ? '' : $row->style )
      ));
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Cool! Process
    $style = $form->getValue('style');

    // Save
    if (null == $row) {
      $row = $table->createRow();
      $row->type = 'sesevent';
      $row->id = $event->getIdentity();
    }

    $row->style = $style;
    $row->save();

    $this->view->draft = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.');
    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => false,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'))
    ));
  }

  public function deleteAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->getItem('sesevent_event', $this->getRequest()->getParam('event_id'));
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid())
      return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Make form
    $this->view->form = $form = new Sesevent_Form_Delete();

    if (!$event) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Event doesn't exists or not authorized to delete");
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $event->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $event->is_delete = '1';
			$event->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected event has been deleted.');
    return $this->_forward('success', 'utility', 'core', array(
                'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sesevent_general', true),
                'messages' => Array($this->view->message)
    ));
  }

}
