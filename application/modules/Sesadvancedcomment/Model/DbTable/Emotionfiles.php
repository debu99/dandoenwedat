<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Emotionfiles.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedcomment_Model_DbTable_Emotionfiles extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedcomment_Model_Files';
  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getFiles($params));
  }
  public function getFiles($params = array()){
     $select = ($this->select());
     if(!empty($params['limit'])){
       $select->limit($params['limit']);
     }
		 $select->where('gallery_id =?',$params['gallery_id']);
    if(!empty($params['fetchAll'])){
      return $this->fetchAll($select);  
    }
    return $select;
  }
}