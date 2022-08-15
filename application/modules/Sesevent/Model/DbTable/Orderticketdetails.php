<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Orderticketdetails.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Orderticketdetails extends Engine_Db_Table {

  protected $_rowClass = 'Sesevent_Model_Orderticketdetail';
	public function orderTicketDetails($params = array()){
		$tableName = Engine_Api::_()->getDbtable('orderticketdetails', 'sesevent');
		$tableInfoName = $tableName->info('name');
		$select = $tableName->select()->from($tableInfoName)->where('order_id =?',$params['order_id'])->order('ticket_id ASC');
		return $tableName->fetchAll($select);
	}
	public function checkRegistrationNumber($code = ''){
		if(!$code)
			$code = Engine_Api::_()->sesevent()->generateTicketCode(8);
		return $this->select()
								->from($this->info('name'), new Zend_Db_Expr('COUNT(*)'))
								->where('registration_number =?', $code)
								->query()
								->fetchColumn();
															
	}
	public function searchTickets($params = array()){
		$orderTableName = $this->info('name');
		$select = $this->select()
		->from($this->info('name'),array('*'))
		->where('event_id =?',$params['event_id'])
		->where("state = 'complete'");
		$orderName = Engine_Api::_()->getItemTable('sesevent_order')->info('name');
		$select->setIntegrityCheck(false)->joinLeft($orderName, "$orderTableName.order_id = $orderName.order_id", array());
		if (!empty($params['order_id']))
				$select->where($orderTableName . '.order_id =?', $params['order_id']);	
		if (!empty($params['registration_number']))
				$select->where($orderTableName . '.registration_number =?', $params['registration_number']);	
		if (!empty($params['buyer_name']))
				$select->where($orderTableName . '.first_name  LIKE "%' . $params['buyer_name'] . '%" || '.$orderTableName . '.last_name  LIKE "%' . $params['buyer_name'] . '%" || '. 'CONCAT('.$orderTableName.'.first_name," ", '.$orderTableName.'.last_name)  LIKE "%' . $params['buyer_name'] . '%"');
		
		if (!empty($params['email']))
				$select->where($orderTableName . '.email  LIKE "%' . $params['email'] . '%"');
		if (!empty($params['mobile']))
				$select->where($orderTableName . '.mobile  LIKE "%' . $params['mobile'] . '%"');
		if (!empty($params['creation_date']))
				$select->where($orderTableName . '.creation_date  LIKE "%' . $params['creation_date'] . '%"');
		$select->order($orderName.'.creation_date DESC');			
		return $select;
	}
}
