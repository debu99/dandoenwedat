<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Levelpoints.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Levelpoints extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Levelpoint";

  public function getMemberLevel() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $levelId = $viewer->level_id;
    $viewerId = $viewer->getIdentity();
    $creditDetailsTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $levelPointTableName = $this->info('name');
    $levelsTable = Engine_Api::_()->getDbTable('levels', 'authorization');
    $levelsTableName = $levelsTable->info('name');
    $select = $levelsTable->select()
            ->setIntegrityCheck(false)
            ->from($levelsTable->info('name'), array('title', 'type', 'level_id'))
            ->join($levelPointTableName, $levelPointTableName . '.level_id = ' . $levelsTableName . '.level_id', array('point'))
            ->where('point !=?', 0)
            ->where('point <= (SELECT total_credit from engine4_sescredit_details where owner_id = '.$viewerId.")")
            ->where($levelsTableName .'.level_id !=?', $levelId)
            ->where('type != ?', 'public');
    return $levelsTable->fetchAll($select);
  }

}
