<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: NotificationSettings.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_NotificationSettings extends Engine_Db_Table
{
	protected $_name = 'activity_notificationsettings';
  /**
   * Gets all enabled notification types for a user
   *
   * @param User_Model_User $user
   * @return array An array of enabled types
   */
  public function getEnabledNotifications(User_Model_User $user)
  {
    $types = Engine_Api::_()->getDbtable('notificationTypes', 'sesadvancedactivity')->getNotificationTypes();

    $select = $this->select()
      ->where('user_id = ?', $user->getIdentity());
    $rowset = $this->fetchAll($select);

    $enabledTypes = array();
    foreach( $types as $type )
    {
      $row = $rowset->getRowMatching('type', $type->type);
      if( null === $row || $row->email == true )
      {
        $enabledTypes[] = $type->type;
      }
    }

    return $enabledTypes;
  }

  /**
   * Set enabled notification types for a user
   *
   * @param User_Model_User $user
   * @param array $types
   * @return Sesadvancedactivity_Api_Notifications
   */
  public function setEnabledNotifications(User_Model_User $user, array $enabledTypes)
  {
    $types = Engine_Api::_()->getDbtable('notificationTypes', 'sesadvancedactivity')->getNotificationTypes();

    $select = $this->select()
      ->where('user_id = ?', $user->getIdentity());
    $rowset = $this->fetchAll($select);

    foreach( $types as $type )
    {
      $row = $rowset->getRowMatching('type', $type->type);
      $value = in_array($type->type, $enabledTypes);
      if( $value && null !== $row )
      {
        $row->delete();
      }
      else if( !$value && null === $row )
      {
        $row = $this->createRow();
        $row->user_id = $user->getIdentity();
        $row->type = $type->type;
        $row->email = (bool) $value;
        $row->save();
      }
    }

    return $this;
  }

  /**
   * Check if a notification is enabled
   *
   * @param User_Model_User $user User to check for
   * @param string $type Notification type
   * @return bool Enabled
   */
  public function checkEnabledNotification(User_Model_User $user, $type)
  {
    $select = $this->select()
      ->where('user_id = ?', $user->getIdentity())
      ->where('type = ?', $type)
      ->limit(1);

    $row = $this->fetchRow($select);

    if( null === $row )
    {
      return true;
    }

    return (bool) $row->email;
  }
}