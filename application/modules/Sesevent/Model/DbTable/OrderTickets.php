<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: OrderTickets.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_OrderTickets extends Engine_Db_Table {
	protected $_rowClass = "Sesevent_Model_Orderticket";
	public function getOrderTicket($params = array()){}
	public function updateTicketOrderState($params = array()){
		if(empty($params['state']) || empty($params['order_id']))
			return;
		$this->update(
				array('state' => $params['state']),
				array('order_id =?' => $params['order_id'])
     );
		 return true;
	}
	public function getOrderTicketDetails($params = array()){
		 $orderTableName = $this->info('name');
     $select = $this->select();    
        if(!empty($params['columns'])) {
            $select->from($orderTableName, $params['columns']);
        }
        else {
            $select->from($orderTableName);
        }
        if(!empty($params['order_id'])) {
            $select->where('order_id =?', $params['order_id']);
        }
        return $this->fetchAll($select);
	}
	
	public function getTicketId($params = array()) {
		return $this->select()
                    ->from($this->info('name'), array('ticket_id'))
                    ->where('order_id =?', $params['order_id'])
                    ->query()
                    ->fetchColumn();
	}
	
}
