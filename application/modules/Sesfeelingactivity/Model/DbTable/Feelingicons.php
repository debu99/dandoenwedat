<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Feelingicons.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeelingactivity_Model_DbTable_Feelingicons extends Engine_Db_Table {

  protected $_rowClass = 'Sesfeelingactivity_Model_Feelingicon';
  
  public function getPaginator($params = array()) {
  
    return Zend_Paginator::factory($this->getFeelingicons($params));
  }
  
  public function getFeelingicons($params = array()) {
  
    $select = $this->select()->order('order ASC');
    
    if(!empty($params['limit'])){
      $select->limit($params['limit']);
    }
    if(!empty($params['search']))
       $select->where('title LIKE ("%'.$params['search'].'%")');

    $select->where('feeling_id =?',$params['feeling_id']);
    if(!empty($params['fetchAll'])){
      return $this->fetchAll($select);  
    }
    
    return $select;
  }
  
  public function getFeelingIconExist($params = array()) {
  
    return $this->select()
          ->from($this->info('name'), array('feelingicon_id'))
          ->where('title =?', $params['title'])
          ->query()
          ->fetchColumn();
  }
}