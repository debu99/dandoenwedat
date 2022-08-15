<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Notifications.php 2016-11-22 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Model_DbTable_Notifications extends Engine_Db_Table {

  protected $_rowClass = 'Activity_Model_Notification';
  protected $_serializedColumns = array('params');

  /**
   * Get notification paginator
   *
   * @param User_Model_User $user
   * @return Zend_Paginator
   */
  public function getUserLastNotification(User_Model_User $user,$friend = ""){
     $table = Engine_Api::_()->getDbtable('notifications', 'activity');
    $tablename = $table->info('name');
    $select = $table->select()->from($table->info('name'), 'notification_id')
            ->where($tablename.'.user_id = ?', $user->getIdentity());
    if ($friend == 'friend') {
      $select->where($tablename.'.type = ?', 'friend_request');
      $select->where('mitigated 	 = ?', 0);
    } else {
      $select->where($tablename.'.type != ?', 'message_new');
      $select->where($tablename.'.type != ?', 'friend_request');
    }
     $select->limit(1)->order('notification_id DESC');
     return $table->fetchRow($select);

  }
  public function getNotificationsPaginator(User_Model_User $user) {
    $enabledNotificationTypes = array();
    foreach (Engine_Api::_()->getDbtable('NotificationTypes', 'activity')->getNotificationTypes() as $type) {
      $enabledNotificationTypes[] = $type->type;
    }

    $select = Engine_Api::_()->getDbtable('notifications', 'activity')->select()
            ->where('user_id = ?', $user->getIdentity())
            ->where('type IN(?)', $enabledNotificationTypes)
            ->where('type != ?', 'message_new')
            ->where('type != ?', 'friend_request')
            ->order('date DESC');

    return Zend_Paginator::factory($select);
  }

  public function getFriendrequestPaginator(User_Model_User $user) {
    $enabledNotificationTypes = array();
    foreach (Engine_Api::_()->getDbtable('NotificationTypes', 'activity')->getNotificationTypes() as $type) {
      $enabledNotificationTypes[] = $type->type;
    }
    $select = Engine_Api::_()->getDbtable('notifications', 'activity')->select()
            ->where('user_id = ?', $user->getIdentity())
            ->where('type IN(?)', $enabledNotificationTypes)
            ->where('type = ?', 'friend_request')
            ->where('mitigated = ?', 0)
            ->order('date DESC');
    return Zend_Paginator::factory($select);
  }

  public function hasNotifications(User_Model_User $user, $friend = null) {
    $table = Engine_Api::_()->getDbtable('notifications', 'activity');
    $tablename = $table->info('name');
    $select = $table->select()->from($table->info('name'), 'COUNT(notification_id) AS notification_count')
            ->where($tablename.'.user_id = ?', $user->getIdentity())
            ->where('`read` =?',0);
    $tableread = Engine_Api::_()->getDbTable('notificationreads','sesbasic');
    $select->setIntegrityCheck(false);
    $tablereadname = $tableread->info('name');
    if ($friend == 'friend') {
      $select->joinLeft($tablereadname,$tablereadname.".`user_id` =".$tablename.".`user_id` AND ".$tablereadname.".`type` = 'friendreq_read'",null);
      $select->where('CASE WHEN '.$tablereadname.'.notificationread_id IS NOT NULL THEN '.$tablename.'.notification_id > '.$tablereadname.'.item_id ELSE TRUE END');
      $select->where($tablename.'.type = ?', 'friend_request');
      $select->where('mitigated 	 = ?', 0);
    } else {
      $select->joinLeft($tablereadname,$tablereadname.".`user_id` =".$tablename.".`user_id` AND ".$tablereadname.".`type` = 'notification_read'",null);
      $select->where('CASE WHEN '.$tablereadname.'.notificationread_id IS NOT NULL THEN '.$tablename.'.notification_id > '.$tablereadname.'.item_id ELSE TRUE END');
      $select->where($tablename.'.type != ?', 'message_new');
      $select->where($tablename.'.type != ?', 'friend_request');
    }

    $data = $table->getAdapter()->fetchRow($select);
    return (int) @$data['notification_count'];
  }

}
