<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: NotificationController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Elivestreaming_NotificationController extends Core_Controller_Action_Standard
{
  public function indexAction()
  { }
  public function sendAction()
  {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: POST, GET');
      header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    if (!isset($viewer))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('you are not authorize to access this page'), 'result' => array()));

    if (!Engine_Api::_()->sesapi()->isModuleEnable('sesvideo'))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Advance Video Plugin is not Enabled yet!'), 'result' => array()));

    if (!Engine_Api::_()->authorization()->getPermission($viewer, 'elivehost', 'create'))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You don\'t have permission to perform live video.'), 'result' => array()));

    $totalLiveVideo = Engine_Api::_()->getDbTable('elivehosts', 'elivestreaming')->countLiveVideo($viewer->getIdentity());
    $allowLiveVideo = Engine_Api::_()->authorization()->getPermission($viewer, 'elivehost', 'max');
    if ($totalLiveVideo >= $allowLiveVideo && $allowLiveVideo != 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have already uploaded the maximum number of entries allowed.'), 'result' => array()));
    }
    $privacy = $this->_getParam('privacy', 'everyone');
    $friendsIds = $viewer->membership()->getMembersIds();
    
    if ($privacy == "friends") {
      if (empty($friendsIds))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Your friend list is empty yet!'), 'result' => array()));
    }

    $network = explode("_", $privacy);
    if ($network[0] == "network") {
      $networkMember = Engine_Api::_()->elivestreaming()->getNetworkMembers($network[2]);
      if (!$networkMember) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('No members in this network yet!'), 'result' => array()));
      }
    }

    $params = array('started' => 1);
    $postData['privacy'] = $privacy;
    $livestreamingTable = Engine_Api::_()->getDbTable('elivehosts', 'elivestreaming');
    $db = $livestreamingTable->getAdapter();
    $db->beginTransaction();
    try {
      $viewerId = $viewer->getIdentity();
      $elivestreamingHost = $livestreamingTable->createRow();
      $values['user_id'] = $viewerId;
      $values['name'] = $viewer->displayname;
      $values['status'] = 'started';
      $elivestreamingHost->setFromArray($values);
      $elivestreamingHost->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
    }

    //set authorization on service created by professional
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    if (empty($values['auth_view'])) {
      $values['auth_view'] = 'everyone';
    }
    if (empty($values['auth_comment'])) {
      $values['auth_comment'] = 'everyone';
    }
    $viewMax = array_search($values['auth_view'], $roles);
    $commentMax = array_search($values['auth_comment'], $roles);
    foreach ($roles as $i => $role) {
      $auth->setAllowed($elivestreamingHost, $role, 'view', ($i <= $viewMax));
      $auth->setAllowed($elivestreamingHost, $role, 'comment', ($i <= $commentMax));
    }
    //end authorization
    //activity feed
    // $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
    // $action = $activityApi->addActivity($viewer, $elivestreamingHost, 'elivestreaming_golive', null, );
    $activityApi = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity');
    $action = $activityApi->addActivity($viewer, $elivestreamingHost, 'elivestreaming_golive', null, $params, $postData);
    if ($action)
      $activityApi->attachActivity($action, $elivestreamingHost);
    $elivestreamingHost->action_id = $action->getIdentity();
    $elivestreamingHost->save();
    //end activity feed

    // if ($privacy != 'everyone') {
    if ($privacy == "friends" || $privacy == "everyone") {
      if (!empty($friendsIds)) {
        $userTable = Engine_Api::_()->getDbTable('users', 'user');
        $select = $userTable
          ->select()
          ->from($userTable->info('name'), array('user_id', 'displayname'))
          ->where('user_id IN (?)', $friendsIds);
        $users = $userTable->fetchAll($select);
      }
    }
    if ($network[0] == "network") {
      $users = $networkMember;
    }
    if (!empty($users)) {
      foreach ($users as $receivers) {
        $receiver = Engine_Api::_()->getItem('user', $receivers->user_id);
        $notificationreceiverTable = Engine_Api::_()->getDbTable('notificationreceivers', 'elivestreaming');
        $db = $notificationreceiverTable->getAdapter();
        $db->beginTransaction();
        try {
          $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($receiver, $viewer, $elivestreamingHost, 'elivestreaming_golive', array('activity_action_id' => $action->getIdentity(), 'elivehosts' => $elivestreamingHost->getIdentity(), 'host_id' => $viewerId));
          $elivestreamingnotification = $notificationreceiverTable->createRow();
          $notificationValues['elivehost_id'] = $elivestreamingHost->getIdentity();
          $notificationValues['notification_id'] = $notification->getIdentity();
          $elivestreamingnotification->setFromArray($notificationValues);
          $elivestreamingnotification->save();
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
        }
      }
    }
    // }

    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesstories')) {
      //Current User Privacy
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_network', 'registered');
      foreach ($roles as $role) {
        if ($auth->isAllowed($viewer, $role, 'story_view')) {
          $auth_view = $role;
        } else {
          $auth_view = 'owner_member';
        }
        if ($auth->isAllowed($viewer, $role, 'story_comment')) {
          $auth_comment = $role;
        } else {
          $auth_comment = 'owner_member';
        }
      }
      Engine_Api::_()->sesstories()->isExist($viewer->getIdentity(), $auth_view);
      // Process
      $table = Engine_Api::_()->getDbtable('stories', 'sesstories');
      $values['owner_id'] = $viewer->getIdentity();
      $values['type'] = '0';
      try {
        $item = $table->createRow();
        $item->setFromArray($values);
        $item->title = $this->view->translate("elive_dummy_story");
        $item->view_privacy = $auth_view;
        $item->status = 1;
        $item->file_id = $viewer->photo_id;
        $item->save();
        // Auth
        $viewMax = array_search($auth_view, $roles);
        $commentMax = array_search($auth_comment, $roles);

        foreach ($roles as $i => $role) {
          $auth->setAllowed($item, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($item, $role, 'comment', ($i <= $commentMax));
        }
        $elivestreamingHost->story_id = $item->getIdentity();
        $elivestreamingHost->save();
      } catch (Exception $e) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Error while create live story'), 'result' => array('message' => $e)));
      }
    }
    $result = array('elivehost_id' => $elivestreamingHost->getIdentity(), 'activity' => $action->toArray());
    if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesstories'))
      $result['message'] = $message = $this->view->translate("story_plugin_disable");

    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
  }
}
