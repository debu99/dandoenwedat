<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php  2017-09-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_ThemeController extends Core_Controller_Action_Standard {

  public function pulldownAction() {

    $page = $this->_getParam('page');
    $viewer = Engine_Api::_()->user()->getViewer();
    
    Engine_Api::_()->getApi('message', 'sesbasic')->setUnreadNotification($viewer);
    
    $this->view->notifications = $notifications = Engine_Api::_()->getDbtable('notifications', 'sesbasic')->getNotificationsPaginator($viewer);
    $notifications->setCurrentPageNumber($page);

    if ($notifications->getCurrentItemCount() <= 0 || $page > $notifications->getCurrentPageNumber()) {
      $this->_helper->viewRenderer->setNoRender(true);
      return;
    }
    // Force rendering now
    $this->_helper->viewRenderer->postDispatch();
    $this->_helper->viewRenderer->setNoRender(true);
  }

  public function markreadmentionAction() {

    $action_id = $this->_getParam('actionid',0);
    if($action_id){
      $item = Engine_Api::_()->getItem('activity_notification',$action_id);
      if($item){
        $item->read = 1;
        $item->save();
      }
      echo 1;die;
    }
    echo 0;die;
  }

  public function friendshipRequestsAction() {

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

    $this->view->friendRequests = $newFriendRequests = Engine_Api::_()->getDbtable('notifications', 'sesbasic')->getFriendrequestPaginator($viewer);
    $newFriendRequests->setCurrentPageNumber($this->_getParam('page'));

    Engine_Api::_()->getApi('message', 'sesbasic')->setUnreadFriendRequest($viewer);

    //People You May Know work
    $userIDS = $viewer->membership()->getMembershipsOfIds();
    $userMembershipTable = Engine_Api::_()->getDbtable('membership', 'user');
    $userMembershipTableName = $userMembershipTable->info('name');
    $select_membership = $userMembershipTable->select()
            ->where('resource_id = ?', $viewer->getIdentity());
    $member_results = $userMembershipTable->fetchAll($select_membership);
    $membershipIDS = array();
    foreach($member_results as $member_result) {
      $membershipIDS[] = $member_result->user_id;
    }
    if(count($membershipIDS)) {
    $userTable = Engine_Api::_()->getDbtable('users', 'user');
    $userTableName = $userTable->info('name');
    $select = $userTable->select()
            ->where('user_id <> ?', $viewer->getIdentity())
            ->where('user_id NOT IN (?)', $membershipIDS)
            ->order('user_id DESC');
    $this->view->peopleyoumayknow = $peopleyoumayknow = Zend_Paginator::factory($select);
    $peopleyoumayknow->setCurrentPageNumber($this->_getParam('page'));
    } else {
      $this->view->peopleyoumayknow = 0;
    }
    //People You may know work
  }

  public function newUpdatesAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->notificationCount = Engine_Api::_()->getDbtable('notifications', 'sesbasic')->hasNotifications($viewer);
  }

  public function newFriendRequestsAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->requestCount = Engine_Api::_()->getDbtable('notifications', 'sesbasic')->hasNotifications($viewer, 'friend');
  }

  public function newMessagesAction() {
    $this->view->messageCount = Engine_Api::_()->getApi('message', 'sesbasic')->getMessagesUnreadCount(Engine_Api::_()->user()->getViewer());
  }

  public function markallmessageAction() {

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array('inbox_read' => 1), array('`user_id` = ?' => $viewer_id));

  }

  public function deleteMessageAction() {

    $message_id = $this->getRequest()->getParam('id');
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      $recipients = Engine_Api::_()->getItem('messages_conversation', $message_id)->getRecipientsInfo();
      foreach ($recipients as $r) {
        if ($viewer_id == $r->user_id) {
          $this->view->deleted_conversation_ids[] = $r->conversation_id;
          $r->inbox_deleted = true;
          $r->outbox_deleted = true;
          $r->save();
        }
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }
  }

  public function inboxAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('messages_conversation')->getInboxPaginator($viewer);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    Engine_Api::_()->getApi('message', 'sesbasic')->setUnreadMessage($viewer);
  }
}
