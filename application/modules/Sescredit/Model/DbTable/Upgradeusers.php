<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Upgradeusers.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Upgradeusers extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Upgradeuser";

  public function isSentUpgradeRequest($userId = '') {
    $select =  $this->select()
                    ->from($this->info('name'), array('status','owner_id'))
                    ->where('owner_id =?', $userId);
    return $this->fetchRow($select);
  }

  public function getLevelName($levelId = '') {
    $levelTable = Engine_Api::_()->getItemTable('authorization_level');
    return $levelTable->select()
                    ->from($levelTable->info('name'), array('title'))
                    ->where('level_id =?', $levelId)
                    ->query()
                    ->fetChColumn();
    ;
  }

}
