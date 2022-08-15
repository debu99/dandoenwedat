<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Hides.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_DbTable_Hides extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Hide';
  public function getHides($params = array()){
    $select = $this->select()->where('action_id =?',$params['action_id'])->where('user_id =?',$params['user_id'])->limit(1);
    return $this->fetchRow($select);  
  }
  
}