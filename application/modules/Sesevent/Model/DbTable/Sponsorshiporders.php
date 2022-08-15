<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sponsorshiporders.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Sponsorshiporders extends Engine_Db_Table {
  protected $_rowClass = "Sesevent_Model_Sponsorshiporder";
	public function updateOrderState($params = array()){
		if(empty($params['state']) || empty($params['order_id']))
			return;
		$this->update(
				array('state' => $params['state']),
				array('sponsorshiporder_id =?' => $params['order_id'])
     );
		 return true;
	}
	public function getOrder($params = array()){
		$select = $this->select()->where('owner_id =?',$params['owner_id'])->where('is_delete =?',0);
		return $this->fetchAll($select);
	}
	public function getOrders($params = array()){
		$orderTableName = $this->info('name');	
		$eventTableName = Engine_Api::_()->getItemTable('sesevent_event')->info('name');
		$select = $this->select()->from($orderTableName)->where($orderTableName.'.is_delete =?',0)->where($eventTableName.'.is_delete =?',0)->setIntegrityCheck(false)
		 						->joinLeft($eventTableName, "$orderTableName.event_id = $eventTableName.event_id", array());
		$select->where('state =?','complete');
		 if(isset($params['viewer_id']))
		 	$select->where('owner_id =?',$params['viewer_id']);
		 if(isset($params['event_id']))
		 	$select->where($orderTableName.'.event_id =?',$params['event_id']);
		 if(isset($params['groupBy']))	
		 	$select->group($params['groupBy']);
			if (isset($params['view_type'])) {
				$now = date("Y-m-d H:i:s");
				if ($params['view_type'] == 'current') {
						$select->where("$eventTableName.endtime >= '$now'");
				} else {
						$select->where("$eventTableName.endtime < ?", $now);
				}
			}
			
			$paginator = Zend_Paginator::factory($select);
			if (!empty($params['page'])) {
					$paginator->setCurrentPageNumber($params['page']);
			}
			if (!empty($params['limit'])) {
					$paginator->setItemCountPerPage($params['limit']);
			}
			return $paginator;
	}
	public function getTotalTicketSoldCount($params = array()){
		$orderTableName =  $this->info('name');
	  return $this->select()
					  ->from($orderTableName, new Zend_Db_Expr('SUM(total_tickets)'))
					  ->where('event_id =?', $params['event_id'])
						->where('state =?',$params['state'])
					  ->limit(1)
					  ->query()
					  ->fetchColumn();
	}
	public function getSaleStats($params = array()){
    $select = $this->select()
                ->from($this->info('name'), array("sum(total_amount) total_amount"))
                ->where("event_id =?", $params['event_id'])
                ->where("state = 'complete'");
		if ($params['stats'] == 'month')
          $select->where("YEAR(creation_date) = YEAR(NOW()) AND MONTH(creation_date) = MONTH(NOW())");
    if ($params['stats'] == 'week')
          $select->where("YEARWEEK(creation_date) = YEARWEEK(CURRENT_DATE)");
		if ($params['stats'] == 'today')
          $select->where("DATE(creation_date) = DATE(NOW())");
    return $select->query()->fetchColumn();
	}
	public function getSponsorshipStats($params = array()) {
	 $select = $this->select()
		->from($this->info('name'), array("COUNT(sponsorshiporder_id) as totalOrder", "SUM(commission_amount) as commission_amount","sum(total_amount) totalAmountSale"))
		->where('event_id =?',$params['event_id'])
		->where("state = 'complete'");
		return $select->query()->fetch();
	}
	public function manageSponsorshipOrders($params = array()){
		$orderTableName = $this->info('name');
		$select = $this->select()
		->from($this->info('name'),array('*',"(total_service_tax + total_entertainment_tax + total_amount) AS totalAmountSale"))
		->where('event_id =?',$params['event_id'])
		->where("state = 'complete'");
		$userTableName = Engine_Api::_()->getItemTable('user')->info('name');
		$select ->setIntegrityCheck(false)->joinLeft($userTableName, "$orderTableName.owner_id = $userTableName.user_id", array());
		if (!empty($params['order_id']))
				$select->where($orderTableName . '.sponsorshiporder_id =?', $params['order_id']);		
		if (!empty($params['order_max']))
				$select->having("totalAmountSale <=?", $params['order_max']);
		if (!empty($params['order_min']))
				$select->having("totalAmountSale >=?", $params['order_min']);
		if (!empty($params['commision_min']))
				$select->where("$orderTableName.commission_amount >=?", $params['commision_min']);
		if (!empty($params['commision_max']))
				$select->where("$orderTableName.commission_amount <=?", $params['commision_max']);
		if (!empty($params['gateway']))
				$select->where($orderTableName . '.gateway_type = ? ', $params['gateway']);
		if (!empty($params['buyer_name']))
				$select->where($userTableName . '.displayname  LIKE ?', '%' . $params['buyer_name'] . '%');
		if (!empty($params['date_to']))
				$select->where("DATE_FORMAT($orderTableName.creation_date, '%Y-%m-%d') >=?", $params['date_to']);
		if (!empty($params['date_from']))
				$select->where("DATE_FORMAT($orderTableName.creation_date, '%Y-%m-%d') <=?", $params['date_from']);			
				
		return $select;
	}
	public function getReportData($params = array()){
		$orderTableName = $this->info('name');
		$sponsorshipTableName = Engine_Api::_()->getDbtable('sponsorships', 'sesevent')->info('name');
		$select = $this->select()->from($orderTableName,array("SUM(commission_amount) as commission_amount", "sum(total_entertainment_tax) total_entertainment_tax,sum(total_service_tax) total_service_tax,(sum(total_service_tax) + sum(total_entertainment_tax)) totalTaxAmount","sum(total_service_tax) total_service_tax,(sum(total_service_tax) + sum(total_entertainment_tax) + sum(total_amount)) totalAmountSale", "COUNT(sponsorshiporder_id) as total_sponsorship","creation_date"))->setIntegrityCheck(false);
		$select->joinLeft($sponsorshipTableName, "($orderTableName.sponsorship_id  = $sponsorshipTableName.sponsorship_id)", array("title", "sponsorship_id"));
		
		if(!empty($params['eventSponsorshipId']))
			$select->where($sponsorshipTableName.'.sponsorship_id =?',$params['eventSponsorshipId']);
		
		$select->where($orderTableName.'.state =?','complete');
		if(isset($params['type'])){
			if($params['type'] == 'month'){
				$select->where("DATE_FORMAT(" . $orderTableName . " .creation_date, '%Y-%m') <= ?", $params['enddate'])
							 ->where("DATE_FORMAT(" . $orderTableName . " .creation_date, '%Y-%m') >= ?", $params['startdate'])
							 ->group("$sponsorshipTableName.sponsorship_id, YEAR($orderTableName.creation_date), MONTH($orderTableName.creation_date)");
			}else{
				$select->where("DATE_FORMAT(" . $orderTableName . " .creation_date, '%Y-%m-%d') <= ?", $params['enddate'])
							 ->where("DATE_FORMAT(" . $orderTableName . " .creation_date, '%Y-%m-%d') >= ?", $params['startdate'])
							 ->group("$sponsorshipTableName.sponsorship_id, YEAR($orderTableName.creation_date), MONTH($orderTableName.creation_date) , DAY($orderTableName.creation_date)");
			}
		}
		return $this->fetchAll($select);
	}
}
