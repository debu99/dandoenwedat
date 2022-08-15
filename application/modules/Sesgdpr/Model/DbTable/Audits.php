<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Audits.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Model_DbTable_Audits extends Engine_Db_Table {
  protected $_rowClass = "Sesgdpr_Model_Audit";
  function getServices($params = array()){
    $select = $this->select();
    if(!empty($params['email']))
      $select->where('email =?',$params['email']);
    return $this->fetchAll($select);  
  }
}