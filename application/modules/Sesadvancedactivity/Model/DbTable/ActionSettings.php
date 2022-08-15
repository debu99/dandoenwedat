<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ActionSettings.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_DbTable_ActionSettings extends Engine_Db_Table
{
	protected $_name = 'activity_actionsettings';
  /**
   * Gets all enabled action types for a user
   *
   * @param User_Model_User $user
   * @return array An array of enabled types
   */
  public function getEnabledActions(User_Model_User $user)
  {
    $types = Engine_Api::_()->getDbtable('actionTypes', 'sesadvancedactivity')->getEnabledActionTypeNames();
    $canDisable = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.publish', true);

    $disabledTypes = array();
    if( $canDisable ) {
      $disabledTypes = $this->select()
        ->from($this->info('name'), 'type')
        ->where('user_id = ?', $user->getIdentity())
        ->where('publish = ?', 0)
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN);
    }

    $enabledTypes = array_diff($types, $disabledTypes);
    return $enabledTypes;
  }

  /**
   * Set enabled action types for a user
   *
   * @param User_Model_User $user
   * @param array $types
   * @return Sesadvancedactivity_Api_Actions
   */
  public function setEnabledActions(User_Model_User $user, array $enabledTypes)
  {
    $types = Engine_Api::_()->getDbtable('actionTypes', 'sesadvancedactivity')->getEnabledActionTypeNames();
    $canDisable = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.publish', true);

    if( !$canDisable ) {
      return $this;
    }

    $disabledTypes = array_diff($types, $enabledTypes);

    $previousDisabledTypes = $this->select()
      ->from($this->info('name'), 'type')
      ->where('user_id = ?', $user->getIdentity())
      ->where('publish = ?', 0)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);
      
    $previousEnabledTypes = array_diff($types, $previousDisabledTypes);
    $toDisable = array_diff($disabledTypes, $previousDisabledTypes);
    $toEnable = array_diff($enabledTypes, $previousEnabledTypes);

    if( !empty($toEnable) ) {
      $this->delete(array(
        'user_id = ?' => $user->getIdentity(),
        'type IN(?)' => $toEnable,
        'publish = ?' => 0,
      ));
    }

    if( !empty($toDisable) ) {
      foreach( $toDisable as $toDisableType ) {
        $this->insert(array(
          'user_id' => $user->getIdentity(),
          'type' => $toDisableType,
          'publish' => 0,
        ));
      }
    }
    
    return $this;
  }

  /**
   * Check if a action is enabled
   *
   * @param User_Model_User $user User to check for
   * @param string $type Action type
   * @return bool Enabled
   */
  public function checkEnabledAction(User_Model_User $user, $type)
  {
    $canDisable = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.publish', true);
    if( !$canDisable ) {
      return true;
    }
    
    $val = $this->select()
      ->from($this->info('name'), 'publish')
      ->where('user_id = ?', $user->getIdentity())
      ->where('type = ?', $type)
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;
    return ( false === $val || $val );
  }
}