<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Userbadges.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Userbadges extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Userbadge";

  public function assignBadge($params = array()) {
    $isBadgeEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.enablebadge', 1);
    if (!$isBadgeEnable)
      return;
    $creditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $totalCredit = $creditDetailTable->select()
            ->from($creditDetailTable->info('name'), 'total_credit')
            ->where('owner_id =?', $params['user_id'])
            ->query()
            ->fetchColumn();
    $select = $this->select()
            ->from($this->info('name'), array('*'))
            ->where('user_id =?', $params['user_id'])
            ->where('active =?', 1);
    $isBadgeAssigned = $this->fetchRow($select);
    if ($totalCredit <= 0) {
      if (!$isBadgeAssigned)
        return;
      else {
        $this->update(array('active' => 0), array('userbadge_id =?' => $isBadgeAssigned->userbadge_id));
        return;
      }
    }
    $this->update(array('active' => 0), array('active =?' => 1, 'user_id =?' => $params['user_id']));
    $badgeTable = Engine_Api::_()->getDbTable('badges', 'sescredit');
    $badgeIds = $badgeTable->select()->from($badgeTable->info('name'), 'badge_id')->where('credit_value between 0 and ' . $totalCredit)->order('credit_value DESC')->where('enabled =?',1)->limit(1)->query()->fetchColumn();
    if ($badgeIds) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sescredit_userbadges (badge_id, user_id, active, creation_date) VALUES ("' . $badgeIds . '","' . $params['user_id'] . '",1, "' . date('Y-m-d H:i:s') . '")	ON DUPLICATE KEY UPDATE	active = 1');
    }
  }

  public function getBadge($params = array()) {
    $userBadgeTableName = $this->info('name');
    $badgeTable = Engine_Api::_()->getDbTable('badges', 'sescredit');
    $badgeTableName = $badgeTable->info('name');
    $select = $badgeTable->select()
            ->setIntegrityCheck(false)
            ->from($badgeTableName, array('badge_id', 'title', 'description', 'photo_id'))
            ->join($userBadgeTableName, $userBadgeTableName . '.badge_id =' . $badgeTableName . '.badge_id', 'user_id')
            ->where($userBadgeTableName . '.user_id =?', $params['user_id'])
            ->where($userBadgeTableName.'.active =?',1);
    return $this->fetchRow($select);
  }

}
