<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Elivestreaming_IndexController extends Core_Controller_Action_Standard
{
  public function cancelAction()
  {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: POST, GET');
      header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
    $elivehost_id = $this->_getParam('elivehost_id');
    $canShareInStory = $this->_getParam('canShareInStory');
    $canPost = $this->_getParam('canPost');
    $message = "";
    if (empty($elivehost_id))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'elivehost_id is missing', 'result' => array()));
    if (empty($canShareInStory))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'canShareInStory is missing', 'result' => array()));
    if (empty($canPost))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'canPost is missing', 'result' => array()));
    if ($canShareInStory) {
      $elivehostItem = Engine_Api::_()->getItem('elivehost', $elivehost_id);
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesstories')) {
        if (!empty($elivehostItem['story_id'])) {
          //delete dummy story when user goes live. this is image type story.
          $story = Engine_Api::_()->getItem('sesstories_story', $elivehostItem->story_id);
            if($story) {
                $message = "Dummy story delete successfully id is =>" . $story->getIdentity();
                $story->delete();
            }
        } else {
          $message = "Story not available";
        }
      } else
        $message = "story_plugin_disable";
    }
    if ($elivehost_id && ($canPost == 'false' || $canPost == false)) {
      $elivehostItem = Engine_Api::_()->getItem('elivehost', $elivehost_id);
      $actionItem = Engine_Api::_()->getItem('activity_action', $elivehostItem->action_id);
      if (!empty($actionItem)) {
        $actionItem->delete();
        $message = "Activity feed delete successfully";
      } else {
        $message = "Activity feed not available";
      }
      if (Engine_Api::_()->elivestreaming()->deleteAllNotifications($elivehostItem['elivehost_id']))
        $message .= " and notifications";
    }
    if ($elivehost_id && ($canPost == 'false' || $canPost == false) && ($canShareInStory == 'false' || $canShareInStory == false)) {
      $elivehostItem = Engine_Api::_()->getItem('elivehost', $elivehost_id);
      if ($elivehostItem) {
        $elivehostItem->delete();
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesstories')) {
          $message = $this->view->translate("elive_cancel_all");
        } else
          $message = $this->view->translate("elive_cancel_all_no_story");
      } else {
        $message = "live host not available.";
      }
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message)));
  }
    public function commentsAction(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET');
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
        $host_id = $this->_getParam('host_id');
        $activity_id = $this->_getParam('activity_id');
        $elivehostItem = Engine_Api::_()->getItem('elivehost', $host_id);
        if(!$host_id || !$activity_id || !$elivehostItem){
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => "parameter_missing"));
        }
        $comments = array();
        $user = Engine_Api::_()->getItem('user',$elivehostItem->user_id);
        $comments['host']['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($user->getPhotoUrl());
        $comments['host']['name'] = $user->getTitle();
        $comments['host']['title'] = "is live now.";
        $comments['host']['href'] = $user->getHref();
        $comments['host']['creation_date'] = $elivehostItem->datetime;
        //reactions
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
            $reactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getPaginator();
            $counter = 0;
            foreach ($reactions as $reac) {
                if (!$reac->enabled)
                    continue;
                $comments['reactions'][$counter]['reaction_id'] = $reac['reaction_id'];
                $comments['reactions'][$counter]['title'] = $this->view->translate($reac['title']);
                $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
                $comments['reactions'][$counter]['image'] = $icon['main'];
                $counter++;
            }
        }
        //loggedin user details
        $viewer = Engine_Api::_()->user()->getViewer();
        if($viewer->getIdentity()) {
            $comments['user']['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($viewer->getPhotoUrl());
            $comments['user']['name'] = $viewer->getTitle();
            $comments['user']['href'] = $viewer->getHref();
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $comments));
    }
  public function statusAction()
  {
    $elivehost_id = $this->_getParam('elivehost_id');
    if (!empty($this->_getParam('action_id'))) {
      $elivehost = Engine_Api::_()->getDbtable('elivehosts', 'elivestreaming')->getHostId(array('action_id' => $this->_getParam('action_id')));
      $elivehost_id = $elivehost->elivehost_id;
    }
    $elivehostItem = Engine_Api::_()->getItem('elivehost', $elivehost_id);
    if (!empty($elivehostItem)) {
      $status = array('message' => $elivehostItem->name . " is live now.");
      if ($elivehostItem->status == "processing")
        $status['message'] = $this->view->translate("elive_process");
      if ($elivehostItem->status == "completed")
        $status['message'] = $this->view->translate("elive_completed");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array_merge($elivehostItem->toArray(), $status)));
    } else
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('elive_delete'), 'status' => 'deleted')));
  }

  public function getPermissionAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $privacy = $this->_getParam('privacy', 'everyone');
    $network = explode("_", $privacy);
    $isCause = false;
    $friendsIds = $viewer->membership()->getMembersIds();
    $totalLiveVideo = Engine_Api::_()->getDbTable('elivehosts', 'elivestreaming')->countLiveVideo($viewer->getIdentity());
    $allowLiveVideo = Engine_Api::_()->authorization()->getPermission($viewer, 'elivehost', 'max');
    $permission = json_decode(Engine_Api::_()->authorization()->getPermission($viewer, 'elivehost', 'share'));
    $result = array('canPost' => false, 'canShareInStory' => false);
    $result['canSave'] = filter_var(Engine_Api::_()->authorization()->getPermission($viewer, 'elivehost', 'save'), FILTER_VALIDATE_BOOLEAN);
    $result['maxStreamDurations'] = (int) Engine_Api::_()->authorization()->getPermission($viewer, 'elivehost', 'duration');
    if (in_array("sesadvancedactivity", $permission))
      $result['canPost'] = true;
    if (in_array("sesstories", $permission))
      $result['canShareInStory'] = true;
    if (!$this->_helper->requireUser()->isValid()) {
      $result['cause'] = 'permission_error';
      $result['message'] = 'permission_error';
      $isCause = true;
    } else if (!Engine_Api::_()->sesapi()->isModuleEnable('sesvideo')) {
      $result['cause'] = 'sesvideo';
      $result['message'] = $this->view->translate('advance_video_plugin_disable');
      $isCause = true;
    } else if (!Engine_Api::_()->authorization()->getPermission($viewer, 'elivehost', 'create')) {
      $result['cause'] = 'perform_live';
      $result['message'] = $this->view->translate('elive_can_create');
      $isCause = true;
    } else if ($totalLiveVideo >= $allowLiveVideo && $allowLiveVideo != 0) {
      $result['cause'] = 'perform_limit';
      $result['message'] = $this->view->translate('elive_max_create');
      $isCause = true;
    } else if ($privacy == "friends") {
      if (empty($friendsIds)) {
        $result['cause'] = 'friend_list';
        $result['message'] = $this->view->translate('elive_no_friends');
        $isCause = true;
      }
    } else if ($network[0] == "network") {
      if (!Engine_Api::_()->elivestreaming()->getNetworkMembers($network[2])) {
        $result['cause'] = 'network_list';
        $result['message'] = $this->view->translate('elive_empty_network');
        $isCause = true;
      }
    }
    if ($isCause)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $result['message'], 'result' => $result));
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
  }

  public function changeStatusAction()
  {
    $elivehost_id = $this->_getParam('elivehost_id');
    if (!empty($elivehost_id)) {
      $elivehostItem = Engine_Api::_()->getItem('elivehost', $elivehost_id);
      if (!empty($elivehostItem)) {
        $elivehostItem->status = 'processing';
        $elivehostItem->save();
        if ($elivehostItem['action_id']) {
          $actionItem = Engine_Api::_()->getItem('activity_action', $elivehostItem->action_id);
          $actionItem->params = array('processing' => 1);
          $actionItem->save();
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => "host data change successfully.")));
      } else {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Elive host id not found."), 'result' => array()));
      }
    } else {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Elive host id is missing."), 'result' => array()));
    }
  }
}
