<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Corecomments.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_DbTable_Corecomments extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Corecomment';
  //protected $_name = 'activity_comments';
//   public function getResourceType()
//   {
//     return 'activity_action';
//   }


  public function rowExists($comment_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $select = $this->select()
                    ->where('core_comment_id = ?', $comment_id)
                    ->limit(1);
    $results = $this->fetchRow($select);
    return $results;
  }

  public function removeExists($comment_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $db->query('DELETE FROM `engine4_sesadvancedactivity_corecomments` WHERE `engine4_sesadvancedactivity_corecomments`.`core_comment_id` = "'.$comment_id.'";');
  }

  public function isRowExists($id, $file_id = 0) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $comment_id = $this->select()
            ->from($this->info('name'), 'core_comment_id')
            ->where('core_comment_id =?', $id)
            ->query()
            ->fetchColumn();

    //return $comment_id;
    if(empty($comment_id)) {
        $row = $this->createRow();
        $row->core_comment_id = $id;
        if($file_id)
        $row->file_id = $file_id;
        $row->save();
        return $row;
    } else {

        //$db->update('engine4_sesadvancedactivity_corecomments', array('type' => $type), array('core_comment_id =?' => $id));
    }
  }

  public function isCommentExists($id, $file_id = 0) {

    $db = Engine_Db_Table::getDefaultAdapter();

    return $this->select()
            ->from($this->info('name'), 'corecomment_id')
            ->where('core_comment_id =?', $id)
            ->query()
            ->fetchColumn();

  }
}
