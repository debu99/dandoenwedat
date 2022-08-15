<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Integrateothermodules.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Integrateothermodules extends Engine_Db_Table {

  protected $_rowClass = 'Sesevent_Model_Integrateothermodule';

  public function getResults($params = array()) {
  
    if (isset($params['column_name']))
      $columnName = $params['column_name'];
    else
      $columnName = '*';
    $select = $this->select()
            ->from($this->info('name'), $columnName);
            
    if (isset($params['integrateothermodule_id']))
      $select = $select->where('integrateothermodule_id = ?', $params['integrateothermodule_id']);
      
    if (isset($params['content_type']))
      $select = $select->where('content_type = ?', $params['content_type']);

    if (isset($params['module_name']))
      $select = $select->where('module_name = ?', $params['module_name']);
      
    if (isset($params['content_id']))
      $select = $select->where('content_id = ?', $params['content_id']);

    if (isset($params['enabled']))
      $select = $select->where('enabled = ?', $params['enabled']);
      
    if (isset($params['type']))
      $select = $select->where('type = ?', $params['type']);
      
    return $select->query()->fetchAll();
  }
}
