<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Remainingsponsorshippayments.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Remainingsponsorshippayments extends Engine_Db_Table {
	public function getEventSponsorshipRemainingAmount($params = array()){
		$tabeleName = $this->info('name');
	 $select = $this->select()->from($tabeleName);
	 if(isset($params['event_id']))
	 	$select->where('event_id =?',$params['event_id']);	 
	 return $this->FetchRow($select);
	}
}
