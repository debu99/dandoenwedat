<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_IndexController extends Core_Controller_Action_Standard {

  public function searchAction() {

    $text = $this->_getParam('text', null);
    $table = Engine_Api::_()->getDbtable('search', 'core');
    $select = $table->select()->where('title LIKE ? OR description LIKE ? OR keywords LIKE ? OR hidden LIKE ?', '%' . $text . '%')->order('id DESC');
    $select->limit('10');

    $results = Zend_Paginator::factory($select);
    foreach ($results as $result) {
      $itemType = $result->type;
      if (Engine_Api::_()->hasItemType($itemType)) {
        $item = Engine_Api::_()->getItem($itemType, $result->id);
        $item_type = ucfirst($item->getShortType());
        $photo_icon_photo = $this->view->itemPhoto($item, 'thumb.icon');
        $data[] = array(
            'id' => $result->id,
            'label' => strip_tags($item->getTitle()),
            'photo' => $photo_icon_photo,
            'url' => $item->getHref(),
            'resource_type' => $item_type,
        );
      }
    }
    return $this->_helper->json($data);
  }

  public function inboxAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('messages_conversation')->getInboxPaginator($viewer);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    Engine_Api::_()->getApi('message', 'sespwa')->setUnreadMessage($viewer);
  }

  public function friendshipRequestsAction() {

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->friendRequests = $newFriendRequests = Engine_Api::_()->getDbtable('notifications', 'sespwa')->getFriendrequestPaginator($viewer);
    $newFriendRequests->setCurrentPageNumber($this->_getParam('page'));
    Engine_Api::_()->getApi('message', 'sespwa')->setUnreadFriendRequest($viewer);

    //People You May Know work
    $userIDS = $viewer->membership()->getMembershipsOfIds();
    $userMembershipTable = Engine_Api::_()->getDbtable('membership', 'user');
    $userMembershipTableName = $userMembershipTable->info('name');
    $select_membership = $userMembershipTable->select()
            ->where('resource_id = ?', $viewer->getIdentity());
    $member_results = $userMembershipTable->fetchAll($select_membership);
    foreach($member_results as $member_result) {
      $membershipIDS[] = $member_result->user_id;
    }
    if($membershipIDS) {
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
    $this->view->messageCount = Engine_Api::_()->getApi('message', 'sespwa')->getMessagesUnreadCount(Engine_Api::_()->user()->getViewer());
  }

  public function markallmessageAction() {

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array('read' => 1,  'inbox_read' => 1), array('`user_id` = ?' => $viewer_id));

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
}
