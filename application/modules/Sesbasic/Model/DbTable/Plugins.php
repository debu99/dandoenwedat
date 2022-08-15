<?php

class Sesbasic_Model_DbTable_Plugins extends Engine_Db_Table {

  protected $_rowClass = 'Sesbasic_Model_Plugin';
  
  public function getResults($params = array()) {
  
    if (isset($params['column_name']))
      $columnName = $params['column_name'];
    else
      $columnName = '*';
    $select = $this->select()
            ->from($this->info('name'), $columnName);
    return $select->query()->fetchAll();
  }
}