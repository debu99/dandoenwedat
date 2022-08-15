<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Targetpost.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Targetpost extends Engine_Db_Table
{
  protected $_name = 'sesadvancedactivity_targetpost';
  protected $_rowClass = 'Sesadvancedactivity_Model_Targetpost';
  public function getTargetPost($action_id = ''){
    if(!$action_id)
      return array();
    $select = $this->select()->where('action_id =?',$action_id);
    return $this->fetchRow($select);  
  }
}