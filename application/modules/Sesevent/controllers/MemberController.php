<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: MemberController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_MemberController extends Core_Controller_Action_Standard {

  public function init() {
    if (0 !== ($event_id = (int) $this->_getParam('event_id')) &&
            null !== ($event = Engine_Api::_()->getItem('sesevent_event', $event_id))) {
      Engine_Api::_()->core()->setSubject($event);
    }

    $this->_helper->requireUser();
    $this->_helper->requireSubject('sesevent_event');
  }

  public function waitinglistAction(){
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject()->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
      return;

    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->core()->getSubject();

    $db = $event->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $event->membership()
      ->addMember($viewer)
      ->setUserApproved($viewer);

      $row = $event->membership()
      ->getRow($viewer);

      $row->rsvp = 5; //waitinglist
      $row->save();

      $db->commit();
    }catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Joined list')),
      'layout' => 'default-simple',
      'parentRefresh' => true,  
    ));
  }

    public function joinWidgetAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $event = Engine_Api::_()->core()->getSubject();

        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            return;
        if (!$this->_helper->requireSubject()->isValid())
            return;
        if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
            return;
        if ($event->getAttendingCount() >= $event->max_participants)
            return;


        $db = $event->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            if (!$event->membership()->isMember($viewer)) {
                $event->membership()
                    ->addMember($viewer)
                    ->setUserApproved($viewer);
            }
            $event->increaseGenderCount($viewer);

            $row = $event->membership()
                ->getRow($viewer);

            $row->rsvp = 2; //attending
            $row->save();

            $owner = Engine_Api::_()->user()->getUser($event->user_id);
            $members = $event->membership()->getMembership(array("event_id" => $event->getIdentity()));
            if ($event->getAttendingCount() == $event->min_participants && $event->is_send_reach_min == 0) {
                //notification and email for organizer when event reach minimum participants
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                    $owner,
                    $viewer,
                    $event,
                    'sesevent_organizer_reach_minimum_partis',
                    array(
                        'queue' => true,
                        'object_date' => $event->getTime('starttime', 'j M'),
                        'object_time' => $event->getTime('starttime', 'H:i')
                    )
                );
                //notification and email for joined when event reach minimum participants
                foreach ($members as $member) {
                    if ($member->user_id != $event->user_id) {
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                            Engine_Api::_()->user()->getUser($member->user_id),
                            $viewer,
                            $event,
                            'sesevent_joined_reach_minimum_partis',
                            array(
                                'queue' => true,
                                'object_date' => $event->getTime('starttime', 'j M'),
                                'object_time' => $event->getTime('starttime', 'H:i')
                            )
                        );
                    }
                }
                $event->is_send_reach_min = 1;
                $event->save();
            }
            if ($event->getAttendingCount() == $event->max_participants  && $event->is_send_reach_max == 0) {
                //notification and email for organizer when event reach maximum participants
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                    $owner,
                    $viewer,
                    $event,
                    'sesevent_organizer_reach_maximum_partis',
                    array(
                        'queue' => true,
                        'object_date' => $event->getTime('starttime', 'j M'),
                        'object_time' => $event->getTime('starttime', 'H:i')
                    )
                );
                //notification and email for joined when event reach maximum participants
                foreach ($members as $member) {
                    if ($member->user_id != $event->user_id) {
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                            Engine_Api::_()->user()->getUser($member->user_id),
                            $viewer,
                            $event,
                            'sesevent_joined_reach_maximum_partis',
                            array(
                                'queue' => true,
                                'object_date' => $event->getTime('starttime', 'j M'),
                                'object_time' => $event->getTime('starttime', 'H:i')
                            )
                        );
                    }
                }
                $event->is_send_reach_max = 1;
                $event->save();
            }

            //send email for favorite
            if (floatval($event->getAttendingCount() / $event->max_participants) >= 0.8) {
                $favTable = Engine_Api::_()->getDbtable('favourites', 'sesevent');
                $favSelect = $favTable->select()
                    ->where("resource_type = 'sesevent_event'")
                    ->where('resource_id = ?', $event->getIdentity());
                $favEvents = $favTable->fetchAll($favSelect);

                foreach ($favEvents as $user) {
                    if ($user->user_id != $owner->getIdentity()) {
                        $fav_user = Engine_Api::_()->user()->getUser($user->user_id);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                            $fav_user,
                            $fav_user,
                            $event,
                            'sesevent_fav_almost_full',
                            array(
                                'queue' => true,
                                'object_date' => $event->getTime('starttime', 'j M'),
                                'object_time' => $event->getTime('starttime', 'H:i')
                            )
                        );
                    }
                }
                $event->is_send_to_favorite = 1;
                $event->save();
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
  public function joinAction() {
    // Check resource approval
  $viewer = Engine_Api::_()->user()->getViewer();
   $event = Engine_Api::_()->core()->getSubject();

    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject()->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
      return;

    if($event->getAttendingCount() >= $event->max_participants || $event->eventIsFull($viewer)) 
      return;

    if ($subject->membership()->isResourceApprovalRequired()) {
      $row =$event->membership()->getReceiver()
              ->select()
              ->where('resource_id = ?',$event->getIdentity())
              ->where('user_id = ?', $viewer->getIdentity())
              ->query()
              ->fetch(Zend_Db::FETCH_ASSOC, 0);
      ;
      if (empty($row)) {
        // has not yet requested an invite
        return $this->_helper->redirector->gotoRoute(array('action' => 'request', 'format' => 'smoothbox'));
      } elseif ($row['user_approved'] && !$row['resource_approved']) {
        // has requested an invite; show cancel invite page
        return $this->_helper->redirector->gotoRoute(array('action' => 'cancel', 'format' => 'smoothbox'));
      }
    }

    $this->view->form = $form = new Sesevent_Form_Member_Join();
    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $event = Engine_Api::_()->core()->getSubject();
      $db =$event->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        $membership_status =$event->membership()->getRow($viewer)->active;
       
        
       $event->membership()
                ->addMember($viewer)
                ->setUserApproved($viewer);

        $row =$event->membership()
                ->getRow($viewer);

        $event->increaseGenderCount($viewer);
        $row->rsvp = $form->getValue('rsvp');
        $row->save();

        // Add activity if membership status was not valid from before
        if (!$membership_status) {
          $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
          $action = $activityApi->addActivity($viewer,$event, 'sesevent_join');
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event joined')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
  }

  public function requestAction() {
    // Check resource approval
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject()->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
      return;

    // Make form
    $this->view->form = $form = new Sesevent_Form_Member_Request();

    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        $subject->membership()->addMember($viewer)->setUserApproved($viewer);

        // Add notification
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'sesevent_approve');

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your invite request has been sent.')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
  }

  public function cancelAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject()->isValid())
      return;

    // Make form
    $this->view->form = $form = new Sesevent_Form_Member_Cancel();

    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $user_id = $this->_getParam('user_id');
      $viewer = Engine_Api::_()->user()->getViewer();
      $subject = Engine_Api::_()->core()->getSubject();
      if (!$subject->authorization()->isAllowed($viewer, 'invite') &&
              $user_id != $viewer->getIdentity() &&
              $user_id) {
        return;
      }

      if ($user_id) {
        $user = Engine_Api::_()->getItem('user', $user_id);
        if (!$user) {
          return;
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
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your invite request has been cancelled.')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
  }

  public function leaveAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject()->isValid())
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->core()->getSubject();

    if ($event->isOwner($viewer))
      return;

    // Make form
    $this->view->form = $form = new Sesevent_Form_Member_Leave();
    
    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $this->notifyWaitingListIfSpothasBecomeAvailable();

      $db = $event->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        $event->membership()->removeMember($viewer);
        $event->decreaseGenderCount($viewer);

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event left')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
  }

  public function leaveWaitingListAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject()->isValid())
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->core()->getSubject();

    if ($event->isOwner($viewer))
      return;

    // // Make form
    $this->view->form = $form = new Sesevent_Form_Member_LeaveWaitingList();
    
    // // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $db = $event->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        $event->membership()->removeMember($viewer);
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event left')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    } 
  }
  
  public function notifyWaitingListIfSpothasBecomeAvailable(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->core()->getSubject();

    $eventWasFull = $event->getAttendingCount() >= $event->max_participants;
    $memberWasholdingASpot = $event->membership()->getRow($viewer)->rsvp === 2; // 2 is attending
    
    if($eventWasFull && $memberWasholdingASpot) {
      $event_url = $_SERVER['HTTP_HOST']."/event/".$event->custom_url;
      $usersOnWaitingList = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id'=>$event->getIdentity(),'type'=>'onwaitinglist'));
      foreach($usersOnWaitingList as $userWaiting){ 
          $user = Engine_Api::_()->getItem('user', $userWaiting->user_id);
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'join_leave_spot_availability', array('event_url'=>$event_url,'event_title'=>$event->title));
      }
    }
  }

  public function acceptAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject('sesevent_event')->isValid())
      return;

    // Make form
    $this->view->form = $form = new Sesevent_Form_Member_Join();

    // Process form
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Method');
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Data');
      return;
    }

    // Process form
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $membership_status = $subject->membership()->getRow($viewer)->active;

      $subject->membership()->setUserApproved($viewer);

      $row = $subject->membership()
              ->getRow($viewer);

      $row->rsvp = $form->getValue('rsvp');
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
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->error = false;

    $message = Zend_Registry::get('Zend_Translate')->_('You have accepted the invite to the event %s');
    $message = sprintf($message, $subject->__toString());
    $this->view->message = $message;

    if ($this->_helper->contextSwitch->getCurrentContext() == "smoothbox") {
      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event invite accepted')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
  }

  public function rejectAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject('sesevent_event')->isValid())
      return;

    $this->view->form = $form = new Sesevent_Form_Member_Reject();

    // Process form
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Method');
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Data');
      return;
    }

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
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->error = false;
    $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the event %s');
    $message = sprintf($message, $subject->__toString());
    $this->view->message = $message;

    if ($this->_helper->contextSwitch->getCurrentContext() == "smoothbox") {
      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event invite rejected')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
  }

  public function removeAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject()->isValid())
      return;

    // Get user
    if (0 === ($user_id = (int) $this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
      return $this->_helper->requireSubject->forward();
    }

    $event = Engine_Api::_()->core()->getSubject();

    if (!$event->membership()->isMember($user)) {
      throw new Sesevent_Model_Exception('Cannot remove a non-member');
    }

    // Make form
    $this->view->form = $form = new Sesevent_Form_Member_Remove();

    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
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
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event member removed.')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
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

    // Prepare friends
    $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
    $friendsIds = $friendsTable->select()
            ->from($friendsTable, 'user_id')
            ->where('resource_id = ?', $viewer->getIdentity())
            ->where('active = ?', true)
            ->limit(6000)
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);
    if (!empty($friendsIds)) {
      $friends = Engine_Api::_()->getItemTable('user')->find($friendsIds);
    } else {
      $friends = array();
    }
    $this->view->friends = $friends;

    // Prepare form
    $this->view->form = $form = new Sesevent_Form_Invite();

    $count = 0;
    foreach ($friends as $friend) {
      if ($event->membership()->isMember($friend, null)) {
        continue;
      }
      $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
      $count++;
    }
    $this->view->count = $count;
		if($count == 1)
			$form->removeElement('all');
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

        $event->membership()->addMember($friend)->setResourceApproved($friend);

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

  public function approveAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject('sesevent_event')->isValid())
      return;

    // Get user
    if (0 === ($user_id = (int) $this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
      return $this->_helper->requireSubject->forward();
    }

    // Make form
    $this->view->form = $form = new Sesevent_Form_Member_Approve();

    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $subject = Engine_Api::_()->core()->getSubject();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        $subject->membership()->setResourceApproved($user);

        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'sesevent_accepted');

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Event request approved')),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
      ));
    }
  }

}
