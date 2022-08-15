<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sponsorshipdetails.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Sponsorshipdetails extends Engine_Db_Table {
  protected $_name = 'sesevent_sponsorshipdetails';
  protected $_rowClass = "Sesevent_Model_Sponsorshipdetail";
	public function getSponsorDetails($params){
		$tableName = $this->info('name');	
		$tableSponsorshipMember = Engine_Api::_()->getDbtable('sponsorshipmembers', 'sesevent')->info('name');
		$select = $this->select()->from($tableName);
		if(isset($params['sponsorship_id']))
			$select->where($tableName.'.sponsorship_id =?',$params['sponsorship_id']);
    $select->where($tableName.'.event_id =?', $params['event_id'])
					 ->setIntegrityCheck(false)
					 ->joinLeft($tableSponsorshipMember, $tableSponsorshipMember . '.sponsorshipmemeber_id= '.$tableName.'.sponsorshipmemeber_id ',array(''))
					 ->where($tableSponsorshipMember.'.status =?','complete');
	 	return Zend_Paginator::factory($select);
	}
}
