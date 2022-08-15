<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Activitylikes.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_DbTable_Activitylikes extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Activitylike';

  public function rowExists($like_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $select = $this->select()
                    ->where('activity_like_id = ?', $like_id)
                    ->limit(1);
    $results = $this->fetchRow($select);
    return $results;
  }

  public function removeExists($like_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $db->query('DELETE FROM `engine4_sesadvancedactivity_activitylikes` WHERE `engine4_sesadvancedactivity_activitylikes`.`activity_like_id` = "'.$like_id.'";');

//     $table =  Engine_Api::_()->getDbTable('likes', 'activity');
//
//     $viewer = Engine_Api::_()->user()->getViewer();
//     $select = $table->select()
//                     ->where('resource_id = ?', $action->getIdentity())
//                     ->where('poster_type = ?', $viewer->getType())
//                     ->where('poster_id = ?', $viewer->getIdentity())
//                     ->limit(1);
//     $results = $table->fetchRow($select);
//     if($results) {
//         return $results->getIdentity();
//         //$db->query('DELETE FROM `engine4_sesadvancedactivity_activitylikes` WHERE `engine4_sesadvancedactivity_activitylikes`.`like_id` = "'.$results->getIdentity().'";');
//     }
  }

  public function isRowExists($id, $type, $action) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $like_id = $this->select()
            ->from($this->info('name'), 'activitylike_id')
            ->where('activity_like_id =?', $id)
            ->query()
            ->fetchColumn();
    if(empty($like_id)) {
        $row = $this->createRow();
        $row->activity_like_id = $id;
        $row->type = $type;
        $row->save();
    } else {

        $db->update('engine4_sesadvancedactivity_activitylikes', array('type' => $type), array('activity_like_id =?' => $id));
    }
  }
}
