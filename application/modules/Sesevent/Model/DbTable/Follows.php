<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Follows.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Follows extends Engine_Db_Table {

  protected $_rowClass = 'Sesevent_Model_Follow';

  public function isFollow($params = array()) {

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    return $this->select()
                    ->where('resource_type = ?', $params['resource_type'])
                    ->where('resource_id = ?', $params['resource_id'])
                    ->where('poster_id = ?', $viewer_id)
                    ->query()
                    ->fetchColumn();
  }

  public function addFollow(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster) {

    $row = $this->getFollow($resource, $poster);
    if (null !== $row)
      throw new Core_Model_Exception('Already liked');

    $row = $this->createRow();
    if (isset($row->resource_type))
      $row->resource_type = $resource->getType();

    $row->resource_id = $resource->getIdentity();
    $row->poster_id = $poster->getIdentity();
    $row->save();
    return $row;
  }

  public function getFollow(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $poster) {

    $select = $this->getFollowSelect($resource)
            ->where('poster_id = ?', $poster->getIdentity())
            ->limit(1);
    return $this->fetchRow($select);
  }

  public function getFollowSelect(Core_Model_Item_Abstract $resource) {

    return $this->select()
                    ->where('resource_type = ?', $resource->getType())
                    ->where('resource_id = ?', $resource->getIdentity())
                    ->order('follow_id ASC');
  }

  public function getFollowCount($params = array()) {

    $select = $this->select()
            ->from($this->info('name'), new Zend_Db_Expr('COUNT(1) as count'))
            ->where('resource_type = ?', $params['type'])
            ->where('resource_id = ?', $params['host_id']);
    $data = $select->query()->fetchAll();
    return (int) $data[0]['count'];
  }

}
