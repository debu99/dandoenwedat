<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Tickets.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Tickets extends Engine_Db_Table {

  protected $_rowClass = "Sesevent_Model_Ticket";

  public function getTicket($params = array()) {
		$tablename = $this->info('name');
    $select = $this->select()->from($tablename)->where($tablename .'.is_delete = ?', 0);
    if (isset($params['checkEndDateTime'])) {
      $select->where($tablename.'.endtime > ?', $params['checkEndDateTime']);
      $select->where($tablename.'.starttime <=?', $params['checkEndDateTime']);
			$eventTableName = Engine_Api::_()->getDbtable('events', 'sesevent')->info('name');
			$select->where($eventTableName.'.endtime >= ?',$params['checkEndDateTime'])->setIntegrityCheck(false)
						 ->joinLeft($eventTableName, $tablename . '.event_id= '.$eventTableName.'.event_id', null);
			
    }
		
    if (isset($params['event_id']))
      $select->where($tablename.'.event_id =?', $params['event_id']);

    if (isset($params['name']) && $params['name']) {
      $name = $params['name'];
      $select->where($tablename.".description LIKE '%$name%' or ".$tablename.".name LIKE '%$name%'");
    }

    if (isset($params['type']) && $params['type'] != '')
      $select->where('type =?', $params['type']);
		if(isset($params['lowestPrice'])){
			$select->order('price');
			$select->limit(1);	
		}
    $select->order('ticket_id DESC');
     return $this->fetchAll($select);
  }

}
