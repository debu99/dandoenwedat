<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Speakers.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Speakers extends Engine_Db_Table {

  protected $_rowClass = "Sesevent_Model_Speaker";

  public function getSpeakerMemers($params = array()) {

    $select = $this->select()->from($this->info('name'));

    if (!empty($params)) {

      if (isset($params['type']) && $params['type'] == 'widget')
        $select = $select->where('enabled = ?', 1);

      if (isset($params['widgettype']) && $params['widgettype'] == 'widget')
        $select = $select->where('enabled = ?', 1);

      if (isset($params['allSpeaker']))
        $select = $select->where('user_id IN(?)', $params['allSpeaker']);

      if (!empty($params['designation_id']))
        $select->where($this->info('name') . '.designation_id = ?', $params['designation_id']);

      if (!empty($params['type']))
        $select->where($this->info('name') . '.type = ?', $params['type']);

      if (!empty($params['popularity'])) {
        if ($params['popularity'] == 'featured')
          $select->where($this->info('name') . '.featured = ?', 1);
        elseif ($params['popularity'] == 'sponsored')
          $select->where($this->info('name') . '.sponsored = ?', 1);
      }

      if (isset($params['limit']))
        $select = $select->limit($params['limit']);
    }


    if (isset($params['widgettype']) && $params['widgettype'] == 'widget')
      return $this->fetchAll($select);
    else
      return $paginator = Zend_Paginator::factory($select);
  }

  public function getOfTheDayResults($params = array()) {

    $select = $this->select()
            ->from($this->info('name'), array('*'))
            ->where('offtheday =?', 1)
            ->where('enabled = ?', 1)
            ->where('type =?', $params['type'])
            ->where('starttime <= DATE(NOW())')
            ->where('endtime >= DATE(NOW())')
            ->order('RAND()');
    return $this->fetchRow($select);
  }

  public function getUserId($params = array()) {

    return $this->select()
                    ->from($this->info('name'), array('speaker_id'))
                    ->where('user_id =?', $params['user_id'])
                    ->query()
                    ->fetchColumn();
  }

  public function getSpeakerId($params = array()) {

    return $this->select()
                    ->from($this->info('name'), array('speaker_id'))
                    ->where('user_id =?', $params['user_id'])
                    ->where('enabled = ?', 1)
                    ->query()
                    ->fetchColumn();
  }

}
