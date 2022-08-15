<?php

class Sesbasic_Model_DbTable_Saves extends Engine_Db_Table {

  protected $_rowClass = 'Sesbasic_Model_Save';
  protected $_custom = false;

  public function __construct($config = array()) {

    if (get_class($this) !== 'Sesbasic_Model_DbTable_Saves') {
      $this->_custom = true;
    }

    parent::__construct($config);
  }

  public function getSaveTable() {
    return $this;
  }

  public function addSave(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster) {

    $row = $this->getSave($resource, $poster);
    if (null !== $row) {
      throw new Core_Model_Exception('Already saved');
    }

    $table = $this->getSaveTable();
    $row = $table->createRow();

    if (isset($row->resource_type))
      $row->resource_type = $resource->getType();

    $row->resource_id = $resource->getIdentity();
    $row->poster_type = $poster->getType();
    $row->poster_id = $poster->getIdentity();
    $row->save();

    if (isset($resource->save_count)) {
      $resource->save_count++;
      $resource->save();
    }

    return $row;
  }

  public function removeSave(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster) {
    $row = $this->getSave($resource, $poster);
    if (null === $row) {
      throw new Core_Model_Exception('No save to remove');
    }

    $row->delete();

    if (isset($resource->save_count)) {
      $resource->save_count--;
      $resource->save();
    }

    return $this;
  }

  public function isSave(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster) {
    return ( null !== $this->getSave($resource, $poster) );
  }

  public function getSave(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster) {
    $table = $this->getSaveTable();
    $select = $this->getSaveSelect($resource)
            ->where('poster_type = ?', $poster->getType())
            ->where('poster_id = ?', $poster->getIdentity())
            ->limit(1);

    return $table->fetchRow($select);
  }

  public function getSaveSelect(Core_Model_Item_Abstract $resource) {
    $select = $this->getSaveTable()->select();

    if (!$this->_custom) {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select
            ->where('resource_id = ?', $resource->getIdentity())
            ->order('save_id ASC');

    return $select;
  }

  public function getSavePaginator(Core_Model_Item_Abstract $resource) {
    $paginator = Zend_Paginator::factory($this->getSaveSelect($resource));
    $paginator->setItemCountPerPage(3);
    $paginator->count();
    $pages = $paginator->getPageRange();
    $paginator->setCurrentPageNumber($pages);
    return $paginator;
  }

  public function getSaveCount(Core_Model_Item_Abstract $resource) {
    if (isset($resource->save_count)) {
      return $resource->save_count;
    }

    $select = new Zend_Db_Select($this->getSaveTable()->getAdapter());
    $select->from($this->getSaveTable()->info('name'), new Zend_Db_Expr('COUNT(1) as count'));

    if (!$this->_custom) {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select->where('resource_id = ?', $resource->getIdentity());

    $data = $select->query()->fetchAll();
    return (int) $data[0]['count'];
  }

  public function getAllSaves(Core_Model_Item_Abstract $resource) {
    return $this->getSaveTable()->fetchAll($this->getSaveSelect($resource));
  }

  public function getAllSavesUsers(Core_Model_Item_Abstract $resource) {

    $table = $this->getSaveTable();
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), array('poster_type', 'poster_id'));

    if (!$this->_custom) {
      $select->where('resource_type = ?', $resource->getType());
    }

    $select->where('resource_id = ?', $resource->getIdentity());

    $users = array();
    foreach ($select->query()->fetchAll() as $data) {
      if ($data['poster_type'] == 'user') {
        $users[] = $data['poster_id'];
      }
    }
    $users = array_values(array_unique($users));

    return Engine_Api::_()->getItemMulti('user', $users);
  }

}
