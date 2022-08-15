<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Switchmemberships.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesmembershipswitch_Model_DbTable_Switchmemberships extends Engine_Db_Table {
  public function getMemberships($params = array()) {
    if(empty($params['user_id'])){
        $user_id = $userId = Engine_Api::_()->user()->getViewer()->getIdentity();
    }else{
        $user_id = $params['user_id'];
    }
    $select = $this->select()->where('user_id =?',$user_id);
    $row =  $this->fetchRow($select);
    if($row){
        $row = $this->createRow();
        $row->user_id = $user_id;
        if(!empty($params['is_sesmembershipswitch'])){
            $row->is_sesmembershipswitch = $params['is_sesmembershipswitch'];
        }
        if(!empty($params['is_sesmembershipswitch_notification'])){
            $row->is_sesmembershipswitch_notification = $params['is_sesmembershipswitch_notification'];
        }
        $row->save();
    }else{
        $row->setFromArray($params);
        $row->save();
    }
    return $row;
  }
}