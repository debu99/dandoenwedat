<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Lists.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Lists extends Engine_Db_Table {

  protected $_rowClass = 'Sesevent_Model_List';
  public function getOfTheDayResults() {
    $select = $this->select()
            ->from($this->info('name'), array('*'))
            ->where('offtheday =?', 1)
            ->where('startdate <= DATE(NOW())')
            ->where('enddate >= DATE(NOW())')
            ->order('RAND()');
    return Zend_Paginator::factory($select);
  }
  public function getListPaginator($params = array()) {
    $paginator = Zend_Paginator::factory($this->getListSelect($params));
    if (!empty($params['page']))
      $paginator->setCurrentPageNumber($params['page']);
    if (!empty($params['limit']))
      $paginator->setItemCountPerPage($params['limit']);
    return $paginator;
  }
  public function getListSelect($params = array(),$paginator = true) {
    $listTableName = $this->info('name');
    $listEventsTableName = Engine_Api::_()->getDbTable('listevents', 'sesevent')->info('name');
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $select = $this->select()
            ->from($listTableName)
            ->joinLeft($listEventsTableName, "$listTableName.list_id = $listEventsTableName.list_id", '');
    if (isset($params['action']) && ($params['action'] != 'manage' || $params['action'] != 'browse')) {
      $select->where("$listEventsTableName.listevent_id IS NOT NULL");
    }
    if ($viewer_id) {
      $select->where("($listTableName.is_private = '0' ||  ($listTableName.is_private = 1 && $listTableName.owner_id = $viewer_id))");
    } else
      $select->where("$listTableName.is_private = '0' ");
    if (!empty($params['user']))
      $select->where("owner_id =?", $params['user']);
    if (!empty($params['is_featured']))
      $select = $select->where($listTableName . '.is_featured =?', 1);
    if (!empty($params['is_sponsored']))
      $select = $select->where($listTableName . '.is_sponsored =?', 1);
    //USER SEARCH
    if (!empty($params['show']) && $params['show'] == 2) {
      $select->where($listTableName . '.owner_id IN(?)', $viewer_id);
    }
    if (!empty($params['alphabet']) && $params['alphabet'] != 'all')
      $select->where($listTableName . ".title LIKE ?", $params['alphabet'] . '%');

    if (isset($params['popularity']) && $params['popularity'] == 'is_featured') {
      $select->where($listTableName . ".is_featured = ?", 1);
    }
    if (isset($params['popularity']) && $params['popularity'] == 'is_sponsored') {
      $select->where($listTableName . ".is_sponsored = ?", 1);
    }
		 if (!empty($params['popularCol']))
      $select = $select->order($params['popularCol'] . ' DESC');
    //String Search
    if (!empty($params['title']) && !empty($params['title'])) {
      $select->where("$listTableName.title LIKE ?", "%{$params['title']}%")
              ->orWhere("$listTableName.description LIKE ?", "%{$params['title']}%");
    }
    if (isset($params['widgteName']) && $params['widgteName'] == "Recommanded List") {
      $select->where($listTableName . ".owner_id <> ?", $viewer_id);
    }
    if (isset($params['widgteName']) && $params['widgteName'] == "Other List") {
      $select->where($listTableName . ".list_id <> ?", $params['list_id'])
              ->where($listTableName . ".owner_id = ?", $params['owner_id']);
    }
    $select->group("$listTableName.list_id");
    if (isset($params['popularity'])) {
      switch ($params['popularity']) {
        case "featured" :
          $select->where($listTableName . '.is_featured = 1')
                  ->order($listTableName . '.list_id DESC');
          break;
				case "sponsored" :
          $select->where($listTableName . '.is_sponsored = 1')
                  ->order($listTableName . '.list_id DESC');
        break;
        case "view_count":
          $select->order($listTableName . '.view_count DESC');
          break;
				case "like_count":
          $select->order($listTableName . '.like_count DESC');
          break;
        case "favourite_count":
          $select->order($listTableName . '.favourite_count DESC');
          break;
        case "event_count":
          $select->order($listTableName . '.event_count DESC');
          break;
        case "creation_date":
          $select->order($listTableName . '.creation_date DESC');
          break;
        case "modified_date":
          $select->order($listTableName . '.modified_date DESC');
          break;
      }
    }
		if (isset($params['limit_data']))
      $select = $select->limit($params['limit_data']);
			
    if (!$paginator)
      return $this->fetchAll($select);      
    return $select;
  }
  public function getListsCount($params = array()) {
    return $this->select()
                    ->from($this->info('name'), $params['column_name'])
                    ->where('owner_id = ?', $params['viewer_id'])
                    ->query()
                    ->fetchAll();
  }
}