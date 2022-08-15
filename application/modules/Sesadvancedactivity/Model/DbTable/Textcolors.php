<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id Textcolors.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_DbTable_Textcolors extends Engine_Db_Table {

  protected $_rowClass = 'Sesadvancedactivity_Model_Textcolor';
  
  public function getAllTextColors() {
    
    $tableName = $this->info('name');
    $select = $this->select()
                  ->from($tableName)
                  ->where('active =?', 1);
    return $this->fetchAll($select);

  }
}