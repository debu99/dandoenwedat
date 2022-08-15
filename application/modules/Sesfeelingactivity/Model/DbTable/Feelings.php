<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Feelings.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeelingactivity_Model_DbTable_Feelings extends Engine_Db_Table {

  protected $_rowClass = 'Sesfeelingactivity_Model_Feeling';

  public function getPaginator($params = array()) {

    return Zend_Paginator::factory($this->getFeelings($params));
  }

  public function getFeelings($params = array()) {

    $select = $this->select()->order('order ASC');

    if(empty($params['admin'])) {

      $viewer = Engine_Api::_()->user()->getViewer();

      $enableFeelingsCategories = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesfelngactvity', $viewer, 'felingscategorie');

      if($enableFeelingsCategories)
        $select->where('feeling_id IN (?)', $enableFeelingsCategories);

      $select->where('enabled =?', 1);
    }
    if(!empty($params['notin']))
      $select->where('feeling_id !=?',1);
    if(!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }
}
