<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Membership.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Membership extends Core_Model_DbTable_Membership {

  protected $_type = 'sesevent_event';

  // Configuration

  /**
   * Does membership require approval of the resource?
   *
   * @param Core_Model_Item_Abstract $resource
   * @return bool
   */
  public function isResourceApprovalRequired(Core_Model_Item_Abstract $resource) {
    return $resource->approval;
  }
	
	public function getMembership($params){
		$select = $this->select()->from($this->info('name'), array('*'))->where('resource_id =?',$params['event_id']);
    if (isset($params['type']) && $params['type'] == 'onwaitinglist') {
      $select->where('active =?',1)->where('rsvp =?',5);  
    } else if (isset($params['type']) && $params['type'] == 'attending') {
      $select->where('active =?',1)->where('rsvp =?',2);
    }else if (isset($params['type']) && $params['type'] == 'maybeattending') {
      $select->where('active =?',1)->where('rsvp =?',1);
    }else if (isset($params['type']) && $params['type'] == 'notattending') {
      $select->where('active =?',1)->where('rsvp =?',0);
    }else if (isset($params['type']) && $params['type'] == 'new') {
      $select->where('active =?',0);
    }
		if(isset($params['searchVal']) && $params['searchVal']){
			$user = Engine_Api::_()->getItemTable('user');	
			$userTableName = $user->info('name');
			$select->setIntegrityCheck(false) ->joinLeft($userTableName, $userTableName . '.user_id='.$this->info('name').'.user_id', null);
			$select->where('displayname LIKE "%'.$params['searchVal'].'%"');
		}
    return Zend_Paginator::factory($select);
  }
  

}
