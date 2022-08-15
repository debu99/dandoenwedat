<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Comments.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_DbTable_Activitycomments extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Activitycomment';

  public function rowExists($comment_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $select = $this->select()
                    ->where('activity_comment_id = ?', $comment_id)
                    ->limit(1);
    $results = $this->fetchRow($select);
    return $results;
  }

  public function removeExists($like_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $db->query('DELETE FROM `engine4_sesadvancedactivity_activitylikes` WHERE `engine4_sesadvancedactivity_activitylikes`.`activity_like_id` = "'.$like_id.'";');
  }

  public function isRowExists($id, $file_id = 0) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $comment_id = $this->select()
            ->from($this->info('name'), 'activity_comment_id')
            ->where('activity_comment_id =?', $id)
            ->query()
            ->fetchColumn();

    //return $comment_id;
    if(empty($comment_id)) {
        $row = $this->createRow();
        $row->activity_comment_id = $id;
        $row->file_id = $file_id;
        $row->save();
        return $row;
    } else {

        //$db->update('engine4_sesadvancedactivity_activitycomments', array('type' => $type), array('activity_comment_id =?' => $id));
    }
  }

  public function isCommentExists($id, $file_id = 0) {

    $db = Engine_Db_Table::getDefaultAdapter();

    return $this->select()
            ->from($this->info('name'), 'activitycomment_id')
            ->where('activity_comment_id =?', $id)
            ->query()
            ->fetchColumn();

  }
}
