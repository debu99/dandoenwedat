<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Attachments.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Attachments extends Engine_Db_Table {

  protected $_name = 'activity_attachments';
  
  public function getAllEvents($event_id) {
  
    $attachmentsTableName = $this->info('name');
    $select = $this->select()
                  ->where('type =?', 'sesadvancedactivity_event')
                  ->where('id =?', $event_id);
    return $this->fetchAll($select);
  
  }
}