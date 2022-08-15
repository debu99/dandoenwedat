<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sponsorships.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Sponsorships extends Core_Model_DbTable_Membership {
 protected $_rowClass = "Sesevent_Model_Sponsorship";
 public function getSponsorship($params = array()) {
		$tableName = $this->info('name');
    $select = $this->select()->from($tableName)->where($tableName.'.is_delete =?','0');   
    if (isset($params['event_id']))
      $select->where($tableName.'.event_id =?', $params['event_id']);
    if (isset($params['title']) && $params['title'] != '') {
      $title = $params['title'];
      $select->where($tableName.".description LIKE '%$title%' or ".$tableName.".title LIKE '%$title%'");
    }
		if(isset($params['sponsorship_id']) && isset($params['sponsorship'])){
				 $select->where($tableName.'.sponsorship_id =?', $params['sponsorship_id']);
		}
		if(isset($params['user_id'])){
			$sponsorshipMemberTableName = Engine_Api::_()->getDbtable('sponsorshipmembers', 'sesevent')->info('name');
			$orderTableName = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->info('name');
			$select ->setIntegrityCheck(false)
				->joinLeft($sponsorshipMemberTableName, '('.$sponsorshipMemberTableName . '.sponsorship_id= '.$tableName.'.sponsorship_id AND '.$sponsorshipMemberTableName.'.owner_id =  '.$params['user_id'].' AND '.$sponsorshipMemberTableName.'.status = "complete"  AND '.$sponsorshipMemberTableName.'.event_id = "'.$params['event_id'].'")', array('sponsorshipmemeber_id'))
				->where($sponsorshipMemberTableName.'.sponsorshipmemeber_id IS NULL')	
				->joinLeft($orderTableName, $orderTableName . '.sponsorship_id= '.$tableName.'.sponsorship_id AND '.$orderTableName.'.state =  "complete" ', array('COUNT(sponsorshiporder_id) as total_orders'))
				->group($tableName.'.sponsorship_id')
				->having("CASE WHEN total = 0 THEN 1 ELSE total >=  total_orders END");
		}		
	
    $select->order($tableName.'.sponsorship_id DESC');
    return $this->fetchAll($select);
  }
}
