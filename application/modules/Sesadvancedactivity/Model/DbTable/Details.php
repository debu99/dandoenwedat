<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Details.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Details extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Detail';
  
  public function isRowExists($action_id) {

    $detail_id = $this->select()
            ->from($this->info('name'), 'detail_id')
            ->where('action_id =?', $action_id)
            ->query()
            ->fetchColumn();
    return $detail_id;
  
  }
}