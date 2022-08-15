<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Contents.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Model_DbTable_Contents extends Engine_Db_Table {

  protected $_rowClass = "Sesgdpr_Model_Content";

  function getContents($params = array()){
    $select = $this->select();
    if(!empty($params['type']))
      $select->where('type =?',$params['type']);
    
    return $this->fetchAll($select);  
  }

}