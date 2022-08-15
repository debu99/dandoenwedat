<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Listevents.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Listevents extends Engine_Db_Table {

  protected $_name = 'sesevent_listevents';
  protected $_rowClass = 'Sesevent_Model_Listevent';

  public function getListEvents($params = array()) {
    return $this->select()
                    ->from($this->info('name'), $params['column_name'])
                    ->where('file_id = ?', $params['file_id'])
                    ->query()
                    ->fetchAll();
  }

  public function listEventsCount($params = array()) {

    $row = $this->select()
            ->from($this->info('name'))
            ->where('list_id = ?', $params['list_id'])
            ->query()
            ->fetchAll();
    $total = count($row);
    return $total;
  }

  public function checkEventsAlready($params = array()) {

    return $this->select()
                    ->from($this->info('name'), $params['column_name'])
                    ->where('list_id = ?', $params['list_id'])
                    //->where('file_id = ?', $params['file_id'])
                    ->where('listevent_id = ?', $params['listevent_id'])
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
  }

}
