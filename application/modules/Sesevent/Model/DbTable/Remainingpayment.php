<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Remainingpayment.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Remainingpayments extends Engine_Db_Table {
  protected $_name = 'sesevent_remainingpayments';
	public function getEventRemainingAmount($params = array()){
		$tabeleName = $this->info('name');
	 $select = $this->select()->from($tabeleName);
	 if(isset($params['event_id']))
	 	$select->where('event_id =?',$params['event_id']);	 
	 return $this->FetchRow($select);
	}
}
