<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Events.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Events extends Engine_Db_Table {

  protected $_rowClass = 'Sesadvancedactivity_Model_Event';
  
  public function getEvent($viewer) {
  
    $oldTimeZone = date_default_timezone_get();
    date_default_timezone_set($viewer->timezone);
    
    $tableName = Engine_Api::_()->getDbTable('eventmessages','sesadvancedactivity')->info('name');
    
    $eventTableName = $this->info('name');
    
    $select = $this->select()
                  ->from($this->info('name'), array('*', 'countevent' => new Zend_Db_Expr('COUNT(eventmessage_id)')))
                  ->setIntegrityCheck(false)
                  ->joinLeft($tableName,$tableName.'.event_id='.$this->info('name').'.event_id  AND '.$tableName.".user_id =".$viewer->getIdentity(), null)
                  ->where("CASE WHEN recurring = 1 THEN ".$eventTableName.".date LIKE '%".date("m-d")."%' ELSE ".$eventTableName.".date = '".date("Y-m-d")."' END ")
                  ->where("CASE WHEN visibility = 4 THEN ".$eventTableName.".starttime <= DATE(NOW()) AND " .$eventTableName.".endtime >= DATE(NOW()) ELSE TRUE END ")
                  
                  ->where("CASE WHEN ".$tableName.".eventmessage_id IS NOT NULL THEN ".$tableName.".userclose = 0 ELSE TRUE END ")
                  ->where("active =?",1)

                  ->group($this->info('name') . '.event_id');

    $results = $this->fetchRow($select);

    date_default_timezone_set($oldTimeZone);
    if(!$results)
      return;

    if($results->visibility == 2 || $results->visibility == 3) {
      if($results->countevent >= ($results->visibility - 1)) {
        return '';
      }
    }
    
    if($results) {
      $values['user_id'] = $viewer->getIdentity();
      $values['creation_date'] = date('Y-m-d H:i:s');
      $values['event_id'] = $results->getIdentity();
      Engine_Api::_()->getDbtable('eventmessages', 'sesadvancedactivity')->insert($values);
    }
    return $results;
  }
}