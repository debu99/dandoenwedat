<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: MemberController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesevent_MemberController extends Sesapi_Controller_Action_Standard {
    public function init() {
        if (0 !== ($event_id = (int) $this->_getParam('event_id')) &&
            null !== ($event = Engine_Api::_()->getItem('event', $event_id))) {
                Engine_Api::_()->core()->setSubject($event);
        }
    }
    public function joinAction() {
        // Check resource approval
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        if ($subject->membership()->isResourceApprovalRequired()) {
            $row = $subject->membership()->getReceiver()
                  ->select()
                  ->where('resource_id = ?', $subject->getIdentity())
                  ->where('user_id = ?', $viewer->getIdentity())
                  ->query()
                  ->fetch(Zend_Db::FETCH_ASSOC, 0);
            ;
            if (empty($row)) {
              // has not yet requested an invite
                $message=$this->request();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$message)));
            } elseif ($row['user_approved'] && !$row['resource_approved']) {
              // has requested an invite; show cancel invite page
                $message=$this->cancel();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$message)));
            }
        }
            $viewer = Engine_Api::_()->user()->getViewer();
            $subject = Engine_Api::_()->core()->getSubject();
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();
        try {
            $membership_status = $subject->membership()->getRow($viewer)->active;
            $subject->membership()
                    ->addMember($viewer)
                    ->setUserApproved($viewer)
            ;
            $row = $subject->membership()->getRow($viewer);
            $row->rsvp = $this->_getParam('rsvp',2);
            $row->save();
            // Add activity if membership status was not valid from before
            if (!$membership_status) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $subject, 'sesevent_join');
            }
            $db->commit();
            $message = 'Event joined';
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$message)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }

    }
    public function request() {
        // Check resource approval
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('parameter_missing'), 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $subject->membership()->addMember($viewer)->setUserApproved($viewer);
                // Add notification
                $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
                $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'sesevent_approve');
                $db->commit();
                $message = $this->view->translate('Your invite request has been sent.');
            } catch (Exception $e) {
                $db->rollBack();
               
                $message = $e->getMessage();
            }
        return $message;
    }
    public function cancel() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    //    // Make form
    //    $this->view->form = $form = new Sesevent_Form_Member_Cancel();
    // Process form
    if ($this->getRequest()->isPost()) {
        $user_id = $this->_getParam('user_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        if (!$subject->authorization()->isAllowed($viewer, 'invite') &&
                $user_id != $viewer->getIdentity() &&
                $user_id) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
        }
        if ($user_id) {
          $user = Engine_Api::_()->getItem('user', $user_id);
          if (!$user) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
          }
        } else {
          $user = $viewer;
        }
        $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $subject->membership()->removeMember($user);
            // Remove the notification?
            $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
                    $subject->getOwner(), $subject, 'sesevent_approve');
            if ($notification) {
              $notification->delete();
            }
            $db->commit();
            $message = $this->view->translate('Your invite request has been cancelled.');
        } catch (Exception $e) {
          $db->rollBack();
            $message = $e->getMessage();
        }
    }
    return $message;
  }
    public function leaveAction() {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('data not found.'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        if ($subject->isOwner($viewer))
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('owner can not leave.'), 'result' => array()));
        // Process form
        if ($this->getRequest()->isPost()) {
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $subject->membership()->removeMember($viewer);
                $db->commit();
                 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'', 'result' => array('message'=>$this->view->translate('Succussfully Event left.'))));
            } catch (Exception $e) {
                $db->rollBack();
                 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
            }
        }
    }
    public function acceptAction(){
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('sesevent_event')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
        // Process form
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();

        try {
          $membership_status = $subject->membership()->getRow($viewer)->active;
          $subject->membership()->setUserApproved($viewer);
          $row = $subject->membership()->getRow($viewer);
          $row->rsvp = $this->_getParam('rsvp',2);
          $row->save();
          // Set the request as handled
          $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
                  $viewer, $subject, 'sesevent_invite');
          if ($notification) {
                $notification->mitigated = true;
                $notification->save();
          }
          // Add activity
          if (!$membership_status) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $subject, 'sesevent_join');
            }
            $db->commit();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('message'=>$this->view->translate('You have accepted the invite to the event'))));

        } catch (Exception $e) {
          $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
    }
    public function rejectAction() {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('sesevent_event')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_misssing', 'result' => array()));
        // Process form
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $subject->membership()->removeMember($viewer);
            // Set the request as handled
            $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
                  $viewer, $subject, 'sesevent_invite');
            if ($notification) {
                $notification->mitigated = true;
                $notification->save();
            }
            $db->commit();
            $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the event');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$message)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
    }
    public function removeAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));

    // Get user
    if (0 === ($user_id = (int) $this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('member does not exist.'), 'result' => array()));
    }
    $event = Engine_Api::_()->core()->getSubject();
    if (!$event->membership()->isMember($user)) {
      throw new Sesevent_Model_Exception('Cannot remove a non-member');
    }
        $db = $event->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Remove membership
            $event->membership()->removeMember($user);
            // Remove the notification?
            $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationByObjectAndType(
                    $event->getOwner(), $event, 'sesevent_approve');
            if ($notification) {
              $notification->delete();
            }
            $db->commit();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$this->view->translate('Event member removed.'))));

      } catch (Exception $e) {
        $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
    }
    public function inviteAction() {
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('sesevent_event')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'user_not_autheticate', 'result' => array()));
        // @todo auth
        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();
        $event = Engine_Api::_()->core()->getSubject();

        // Prepare friends
        $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
        $friendsIds = $friendsTable->select()
                ->from($friendsTable, 'user_id')
                ->where('resource_id = ?', $viewer->getIdentity())
                ->where('active = ?', true)
                ->limit(100)
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);
        if (!empty($friendsIds)) {
          $friends = Engine_Api::_()->getItemTable('user')->find($friendsIds);
        } else {
          $friends = array();
        }
        // Prepare form
        $form = new Sesevent_Form_Invite();
        $count = 0;
        foreach ($friends as $friend) {
            if ($event->membership()->isMember($friend, null)) {
                continue;
            }
            $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
            $count++;
        }
        if($count == 1)
            $form->removeElement('all');
        // Not posting
        
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if(!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
            if(count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('invalid_request'), 'result' => array()));
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
            $event->membership()->addMember($friend)->setResourceApproved($friend);
            $notifyApi->addNotification($friend, $viewer, $event, 'sesevent_invite');
          }
            $db->commit();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$this->view->translate('Members invited'))));
        } catch (Exception $e) {
          $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$e->getMessage(), 'result' => array()));
        }
    }
    public function approveAction() {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('sesevent_event')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'parameter_missing', 'result' => array()));

        // Get user
        if (0 === ($user_id = (int) $this->_getParam('user_id')) ||
                null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('user does not exist.'), 'result' => array()));
        }
        if ($this->getRequest()->isPost() ) {
            $viewer = Engine_Api::_()->user()->getViewer();
            $subject = Engine_Api::_()->core()->getSubject();
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $subject->membership()->setResourceApproved($user);
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'sesevent_accepted');
                $db->commit();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$this->view->translate('Event request approved'))));
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
            }

        }
    }
} // class end 
