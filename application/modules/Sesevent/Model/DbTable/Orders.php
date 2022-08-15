<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Orders.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Orders extends Engine_Db_Table {
  protected $_rowClass = "Sesevent_Model_Order";
	public function getOrder($params = array()){
		$select = $this->select()->where('owner_id =?',$params['owner_id'])->where('is_delete =?',0);
		return $this->fetchAll($select);
	}
	public function getOrderStatus($order_id = ''){
		return $this->select()
								->from($this->info('name'), new Zend_Db_Expr('COUNT(*)'))
								->where('state =?', 'complete')
								->where('order_id =?',$order_id)
								->query()
								->fetchColumn();

	}
	public function checkRegistrationNumber($code = ''){
		if(!$code)
			$code = Engine_Api::_()->sesevent()->generateTicketCode(8);;
		return $this->select()
								->from($this->info('name'), new Zend_Db_Expr('COUNT(*)'))
								->where('ragistration_number =?', $code)
								->query()
								->fetchColumn();

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
				if ($params['view_type'] == 'current')
						$select->where("$eventTableName.endtime >= '$now'");
			  else
						$select->where("$eventTableName.endtime < ?", $now);
        $select->order('creation_date DESC');
			}
			$paginator = Zend_Paginator::factory($select);
			if (!empty($params['page']))
					$paginator->setCurrentPageNumber($params['page']);
			if (!empty($params['limit']))
					$paginator->setItemCountPerPage($params['limit']);
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
                ->from($this->info('name'), array('total_amount'=>new Zend_Db_Expr("sum(total_amount)"),'total_entertainment_tax' => new Zend_Db_Expr("sum(total_entertainment_tax)") ,'total_service_tax' => new Zend_Db_Expr("sum(total_service_tax)"),'totalAmountSale' => new Zend_Db_Expr("(sum(total_service_tax) + sum(total_entertainment_tax) + sum(total_amount))")))
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
	public function getEventStats($params = array()) {
	 $select = $this->select()
		->from($this->info('name'), array('totalOrder'=> new Zend_Db_Expr("COUNT(order_id)"),"commission_amount" => new Zend_Db_Expr("SUM(commission_amount)"), 'total_entertainment_tax' => new Zend_Db_Expr("sum(total_entertainment_tax)"),'total_service_tax' => new Zend_Db_Expr("sum(total_service_tax)"),'totalTaxAmount' => new Zend_Db_Expr("(sum(total_service_tax) + sum(total_entertainment_tax))"),'totalAmountSale' => new Zend_Db_Expr("(sum(total_service_tax) + sum(total_entertainment_tax) + sum(total_amount))"),'total_tickets' => new Zend_Db_Expr("SUM(total_tickets)")))
		->where('event_id =?',$params['event_id'])
		->where("state = 'complete'");
		return $select->query()->fetch();
	}
	public function manageOrders($params = array()){
		$orderTableName = $this->info('name');
		$select = $this->select()
		->from($this->info('name'),array('*',"(total_service_tax + total_entertainment_tax + total_amount) AS totalAmountSale"))
		->where('event_id =?',$params['event_id'])
		->where("state = 'complete'");
		$userTableName = Engine_Api::_()->getItemTable('user')->info('name');
		$select ->setIntegrityCheck(false)->joinLeft($userTableName, "$orderTableName.owner_id = $userTableName.user_id", array());
		if (!empty($params['order_id']))
				$select->where($orderTableName . '.order_id =?', $params['order_id']);
		if (!empty($params['registration_number']))
				$select->where($orderTableName . '.ragistration_number =?', $params['registration_number']);
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
		if (!empty($params['email']))
				$select->where($orderTableName . '.email  LIKE ?', '%' . $params['email'] . '%');
		if (!empty($params['buyer_name']))
				$select->where($userTableName . '.displayname  LIKE ?', '%' . $params['buyer_name'] . '%');
		if(!empty($params['date_to']) && !empty($params['date_from']))
			$select->where("DATE($orderTableName.creation_date) BETWEEN '".$params['date_to']."' AND '".$params['date_from']."'");
		else{
			if (!empty($params['date_to']))
					$select->where("DATE($orderTableName.creation_date) >=?", $params['date_to']);
			if (!empty($params['date_from']))
					$select->where("DATE($orderTableName.creation_date) <=?", $params['date_from']);
		}
		$select->order('order_id DESC');
		return $select;
	}
	public function getReportData($params = array()){
		$orderTableName = $this->info('name');
		$ticketTableName = Engine_Api::_()->getDbtable('tickets', 'sesevent')->info('name');
		$orderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
		$orderTicketTableName = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->info('name');
		$select = $orderTicket->select()->from($orderTicketTableName,array("title", "ticket_id"))->setIntegrityCheck(false);
		$select->joinLeft($orderTableName, "($orderTableName.order_id  = $orderTicketTableName.order_id)", array('total_entertainment_tax' => new Zend_Db_Expr("sum(($orderTicketTableName.price*".$orderTicketTableName.".entertainment_tax * $orderTicketTableName.quantity)/100)") ,'total_service_tax' => new Zend_Db_Expr("sum(($orderTicketTableName.price*".$orderTicketTableName.".service_tax* $orderTicketTableName.quantity)/100)") ,'totalTaxAmount' =>new Zend_Db_Expr("(sum(($orderTicketTableName.price*".$orderTicketTableName.".service_tax* $orderTicketTableName.quantity)/100) + sum(($orderTicketTableName.price*".$orderTicketTableName.".entertainment_tax* $orderTicketTableName.quantity)/100))"),'totalAmountSale' => new Zend_Db_Expr("sum(((($orderTicketTableName.price*".$orderTicketTableName.".service_tax)/100 )* $orderTicketTableName.quantity) + ((($orderTicketTableName.price*".$orderTicketTableName.".entertainment_tax)/100 )* $orderTicketTableName.quantity) + $orderTicketTableName.price* $orderTicketTableName.quantity)"),'total_tickets' => new Zend_Db_Expr("SUM($orderTicketTableName.quantity)"),"$orderTicketTableName.creation_date"));
		$select->joinLeft($ticketTableName, "($ticketTableName.ticket_id  = $orderTicketTableName.ticket_id)", null);
		if(!empty($params['eventTicketId']))
			$select->where($orderTicketTableName.'.ticket_id =?',$params['eventTicketId']);
		if(isset($params['event_id']))
			$select->where($orderTableName.'.event_id =?',$params['event_id']);
		$select->where($orderTicketTableName.'.state =?','complete');
		if(isset($params['type'])){
			if($params['type'] == 'month'){
				$select->where("DATE_FORMAT(" . $orderTicketTableName . " .creation_date, '%Y-%m') <= ?", $params['enddate'])
							 ->where("DATE_FORMAT(" . $orderTicketTableName . " .creation_date, '%Y-%m') >= ?", $params['startdate'])
							 ->group("$orderTicketTableName.ticket_id")
							 ->group("YEAR($orderTicketTableName.creation_date)")
							 ->group("MONTH($orderTicketTableName.creation_date)");
			}else{
				$select->where("DATE_FORMAT(" . $orderTicketTableName . " .creation_date, '%Y-%m-%d') <= ?", $params['enddate'])
							 ->where("DATE_FORMAT(" . $orderTicketTableName . " .creation_date, '%Y-%m-%d') >= ?", $params['startdate'])
							 ->group("$orderTicketTableName.ticket_id")
							 ->group("YEAR($orderTicketTableName.creation_date)")
							 ->group("MONTH($orderTicketTableName.creation_date)")
							 ->group("DAY($orderTicketTableName.creation_date)");
			}
		}
		return $orderTicket->fetchAll($select);
	}
}
