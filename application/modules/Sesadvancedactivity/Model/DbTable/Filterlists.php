<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Filterlists.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Filterlists extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Filterlist';
  public function getLists($notArray = ''){
    $select = $this->select()->where('active =?',1)->order('order ASC');
    if($notArray)
      $select->where('filtertype NOT IN(?)',implode(',',$notArray));
    return $this->fetchAll($select);  
  }
  
}