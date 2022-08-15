<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Emotiongalleries.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedcomment_Model_DbTable_Emotiongalleries extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedcomment_Model_Gallery';
  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getGallery($params));
  }
  public function getGallery($params = array()){
    $select = ($this->select());
    if(!empty($params['type']) && $params['type'] && $params['type'] == 'user') {
      $select->where('enabled =?', 1);
    }
    if(!empty($params['fetchAll'])){
      return $this->fetchAll($select);  
    }
    return $select;
    
  }
}