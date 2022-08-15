<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Services.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Model_DbTable_Services extends Engine_Db_Table {

  protected $_rowClass = "Sesgdpr_Model_Service";

  function getServices($params = array()){
    $select = $this->select();
    if(!empty($params['enabled']))
      $select->where('enabled =?',$params['enabled']);
    return $this->fetchAll($select);  
  }

}