<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Recentlyviewitems.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Recentlyviewitems extends Engine_Db_Table
{
	protected $_name = 'sesevent_recentlyviewitems';
  protected $_rowClass = 'Sesevent_Model_Recentlyviewitem';	
	public function getitem($params = array()){	
			$itemTable = Engine_Api::_()->getItemTable('sesevent_event');
			$itemTableName = $itemTable->info('name');
			$fieldName = 'event_id';
		$subquery = $this->select()->from($this->info('name'),array('*','MAX(creation_date) as maxcreadate'))->group($this->info('name').".resource_id")->where($this->info('name').'.resource_type =?', $params['type']);
        if($params['criteria'] == 'by_me'){
			$subquery->where($this->info('name').'.owner_id =?',Engine_Api::_()->user()->getViewer()->getIdentity());
		}else if($params['criteria'] == 'by_myfriend'){
		/*friends array*/
			$friendIds = Engine_Api::_()->user()->getViewer()->membership()->getMembershipsOfIds();
			if(count($friendIds) == 0)
				return array();
			$subquery->where($this->info('name').".owner_id IN ('".implode(',',$friendIds)."')");
		}
		$select = $this->select()
                                ->from(array('engine4_sesevent_recentlyviewitems' => $subquery))
                                ->where('resource_type = ?' ,$params['type'])
                                ->setIntegrityCheck(false)
                                ->order('maxcreadate DESC')
                                ->where($itemTableName.'.event_id != ?','')
                                ->group($this->info('name').'.resource_id');	
    if(!empty($params['order'])){
        $currentTime = date('Y-m-d H:i:s');
        $select->where("(endtime >= '".$currentTime."') || (endtime > '".$currentTime."' && starttime > '".$currentTime."')");
      }                            
    
		$select->joinLeft($itemTableName, $itemTableName . ".$fieldName =  ".$this->info('name') . '.resource_id',array('event_id'));
		$select->where($itemTableName.'.'.$fieldName.' != ?','');
		$select->where($itemTableName.'.event_id != ?','');	
                $select->where($itemTableName.'.is_delete != ?','1');
                $select->where($itemTableName.'.search != ?','0');
		$membershipTableName = Engine_Api::_()->getDbtable('membership', 'sesevent')->info('name');
		$select->joinLeft($membershipTableName, $membershipTableName . '.resource_id = '.$itemTableName.' .event_id AND '.$membershipTableName.'.active = 1  AND '.$membershipTableName.'.resource_approved = 1  AND '.$membershipTableName.'.user_approved = 1  AND '.$membershipTableName.'.rsvp = 2','COUNT('.$membershipTableName.'.resource_id) as joinedmember')->group($membershipTableName.'.resource_id');
		if(isset( $params['limit'])){
			$select->limit( $params['limit'])	;
		}
		return $this->fetchAll($select);
	}
}