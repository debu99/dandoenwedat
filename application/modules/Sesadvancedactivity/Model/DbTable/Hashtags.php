<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Hashtags.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Hashtags extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Hashtag';
  
  public function getAllHashtags($action_id) {
    
    $select = $this->select()
                  ->from($this->info('name'))
                  ->where('action_id =?', $action_id);
    return $this->fetchAll($select);
  
  }
}