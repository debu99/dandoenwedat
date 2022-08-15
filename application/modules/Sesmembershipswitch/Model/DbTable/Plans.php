<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Plans.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesmembershipswitch_Model_DbTable_Plans extends Engine_Db_Table {

  protected $_rowClass = 'Sesmembershipswitch_Model_Plan';

  public function getPlans($params = array()) {
    $select = $this->select();
    
    if(!empty($params['current_plan_id']))
      $select->where('current_plan_id =?',$params['current_plan_id']);  
    
    if(!empty($params['plan_type']))
      $select->where('plan_type =?',$params['plan_type']);  
    
    if(!empty($params['change_plan_id']))
      $select->where('change_plan_id =?',$params['change_plan_id']);  
      
    if(!empty($params['plan_id']))
      $select->where('plan_id =?',$params['plan_id']);  
                    
    $select->limit('1');
    return $this->fetchRow($select);
  }

}