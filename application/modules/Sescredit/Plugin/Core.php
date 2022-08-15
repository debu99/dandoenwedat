<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Plugin_Core extends Zend_Controller_Plugin_Abstract {

  public function onActivityActionCreateAfter($event) {
    $itemObject = $event->getPayload();
    $activityOwner = $itemObject->getOwner();
    Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => $itemObject->type, 'owner_id' => $activityOwner->getIdentity(), 'action_id' => $itemObject->action_id, 'object_id' => $itemObject->object_id, 'point_type' => 'credit', 'level_id' => $activityOwner->level_id));
  }

  public function onItemDeleteBefore($event) {
    $itemObject = $event->getPayload();
    if ($itemObject->getType() == 'activity_action') {
      $activityOwner = $itemObject->getOwner();
      Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => $itemObject->type, 'owner_id' => $activityOwner->getIdentity(), 'action_id' => $itemObject->action_id, 'object_id' => $itemObject->object_id, 'point_type' => 'deduction', 'level_id' => $activityOwner->level_id));
    } else {
      if (isset($itemObject->owner_id))
        $ownerId = $itemObject->owner_id;
      elseif (isset($itemObject->user_id))
        $ownerId = $itemObject->user_id;
      else
        return;
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      $select = $actionTable->select()
              ->from($actionTable->info('name'), array('*'))
          ->where('object_type =?', $itemObject->getType())
              ->where('object_id =?', $itemObject->getIdentity());
      $actions = $actionTable->fetchAll($select);
      foreach ($actions as $action) {
        $activityOwner = Engine_Api::_()->getItem('user', $ownerId);
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => $action->type, 'owner_id' => $activityOwner->getIdentity(), 'action_id' => $action->action_id, 'object_id' => $action->object_id, 'point_type' => 'deduction', 'level_id' => $activityOwner->level_id));
      }
    }
  }

  public function onUserCreateAfter($event) {
    $payload = $event->getPayload();
    if ($payload instanceof User_Model_User) {
      $user = $payload;
      $session = new Zend_Session_Namespace('sescredit_affiliate_signup');
      if (isset($session->user_id) && !empty($session->user_id)) {
        $userId = $session->user_id;
        $userObject = Engine_Api::_()->getItem('user', $session->user_id);
        $creditValue = Engine_Api::_()->authorization()->getPermission($userObject->level_id, 'sescredit', 'credit_referral');
        if (!empty($creditValue)) {
          Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'sescredit_affiliate', 'owner_id' => $session->user_id, 'action_id' => 0, 'object_id' => 0, 'point_type' => 'affiliate', 'level_id' => 0, 'point' => $creditValue));
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($userObject->email, 'sescredit_received_referral_point', array('point' => $creditValue));
        }
        unset($session->user_id);
      }
    }
  }

  public function onUserLoginAfter($event) {
    $user = $event->getPayload();
    $typeInfo = Engine_Api::_()->getDbTable('actions', 'activity')->getActionType('login');
    if (!$typeInfo->enabled) {
      Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'login', 'owner_id' => $user->getIdentity(), 'action_id' => 0, 'object_id' => $user->getIdentity(), 'point_type' => 'credit', 'level_id' => $user->level_id));
    }
  }

  public function onUserDeleteBefore($event) {
    $payload = $event->getPayload();
    if ($payload instanceof User_Model_User) {
      $userId = $payload->getIdentity();
      Engine_Api::_()->getDbTable('affiliates', 'sescredit')->delete(array('user_id =?' => $userId));
      Engine_Api::_()->getDbTable('credits', 'sescredit')->delete(array('owner_id =?' => $userId));
      Engine_Api::_()->getDbTable('details', 'sescredit')->delete(array('owner_id =?' => $userId));
      Engine_Api::_()->getDbTable('orderdetails', 'sescredit')->delete(array('owner_id =?' => $userId));
      Engine_Api::_()->getDbTable('rewardpoints', 'sescredit')->delete(array('user_id =?' => $userId));
      Engine_Api::_()->getDbTable('transactions', 'sescredit')->delete(array('owner_id =?' => $userId));
      Engine_Api::_()->getDbTable('upgradeusers', 'sescredit')->delete(array('owner_id =?' => $userId));
      Engine_Api::_()->getDbTable('userbadges', 'sescredit')->delete(array('user_id =?' => $userId));
    }
  }

}
