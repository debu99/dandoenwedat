<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Reactions.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedcomment_Model_DbTable_Reactions extends Engine_Db_Table {

  protected $_rowClass = 'Sesadvancedcomment_Model_Reaction';

  public function getPaginator($params = array()) {

    return Zend_Paginator::factory($this->getReactions($params));
  }

  public function getReactions($params = array()){

    $select = ($this->select());

    if(@$params['userside']) {
      $select = $select->where('enabled =?', 1);
    }

    if(!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }
}
