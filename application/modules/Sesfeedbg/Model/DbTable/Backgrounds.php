<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedbg
 * @package    Sesfeedbg
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Backgrounds.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedbg_Model_DbTable_Backgrounds extends Engine_Db_Table {

  protected $_rowClass = 'Sesfeedbg_Model_Background';
  public function getPaginator($params = array()) {
    return Zend_Paginator::factory($this->getBackgrounds($params));
  }
  
  public function getBackgrounds($params = array()) {

    $select = $this->select();
    
    if(!empty($params['admin'])) {
      $select->where('enabled =?', 1)
        ->where('starttime <= DATE(NOW())')
        ->where("endtime >= 'DATE(NOW())' OR endtime IS NULL");
    }
    
    if(isset($params['featured']) && !empty($params['featured'])) {
      $select->where('featured =?', 1);
    }

    if(isset($params['featuredbgIds']) && !empty($params['featuredbgIds'])) {
      $select->where('background_id NOT IN (?)', $params['featuredbgIds']);
    }
    
    if(isset($params['feedbgorder']) && !empty($params['feedbgorder']) && $params['feedbgorder'] == 'random') {
      $select->order('Rand()');
    } else {
      $select->order('order ASC');
    }
      
    if(isset($params['sesfeedbg_limit_show']) && !empty($params['sesfeedbg_limit_show']))
      $select->limit($params['sesfeedbg_limit_show']);

    if(!empty($params['fetchAll']))
      return $this->fetchAll($select);
    return $select;
  }
}