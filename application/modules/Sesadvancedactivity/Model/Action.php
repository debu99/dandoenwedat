<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Action.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_Action extends Activity_Model_Action
{
  protected $_searchTriggers = false;
  protected $_type = 'activity_action';
  public function getReply($comment_id, $page = 'zero'){
    if( null !== $this->_comments ) {
      return $this->_comments;
    }

    $activityCommentTable = Engine_Api::_()->getDbTable('comments', 'activity');
    $activityCommentTableName = $activityCommentTable->info('name');

    $coreCommentTable = Engine_Api::_()->getDbTable('comments', 'core');
    $coreCommentTableName = $coreCommentTable->info('name');

    $comments = $this->comments();

    $table = $comments->getReceiver();

    $select = $comments->getCommentSelect();

    if($table->info('name') == 'engine4_core_comments') {
        $select->from($coreCommentTableName, '*');
    } else if($table->info('name') == 'engine4_activity_comments') {
        $select->from($activityCommentTableName, '*');
    }

    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {

        if($table->info('name') == 'engine4_core_comments') {
            $corecommentsTableName = Engine_Api::_()->getDbtable('corecomments', 'sesadvancedactivity')->info('name');
            $select->setIntegrityCheck(false)
                ->joinLeft($corecommentsTableName, $corecommentsTableName.'.core_comment_id ='.$coreCommentTableName.'.comment_id', array('*'))
                ->where($corecommentsTableName.'.parent_id =?',$comment_id);

        } else if($table->info('name') == 'engine4_activity_comments') {
            $activitycommentsTableName = Engine_Api::_()->getDbtable('activitycomments', 'sesadvancedactivity')->info('name');
            $select->setIntegrityCheck(false)
                ->joinLeft($activitycommentsTableName, $activitycommentsTableName.'.activity_comment_id ='.$activityCommentTableName.'.comment_id', array('*'))
                ->where($activitycommentsTableName.'.parent_id =?',$comment_id);
        }
    }

    $reverseOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', false);

    if($page == 'zero'){
       $commentCount = count($select->query()->fetchAll());
       $page = ceil($commentCount/5);
    }

    $select->reset('order');
    if($reverseOrder)
    $select->order('comment_id DESC');
    else
      $select->order('comment_id ASC');
    $comments = Zend_Paginator::factory($select);
    $comments->setCurrentPageNumber($page);
    $comments->setItemCountPerPage(5);
    return $comments;
  }
  public function canEdit()
  {
    $editable = (int) $this->getTypeInfo()->editable;
    if( !$editable ) {
      return false;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return false;
    }

    $maxEditTime = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'activity', 'edit_time');
    $isEditable = true;
    if( $maxEditTime ) {
      $relDate = new Zend_Date($this->getTimeValue());
      $relDate->add((int) $maxEditTime, Zend_Date::MINUTE);
      $isEditable = $relDate->sub(Zend_Date::now())->toValue() > 0;
    }

    if( !$isEditable ) {
      return false;
    }

    $activityModerate = Engine_Api::_()->getDbtable('permissions', 'authorization')
      ->getAllowed('user', $viewer->level_id, 'activity');
    if( $activityModerate ) {
      return true;
    }

    return 'user' == $this->subject_type && $viewer->getIdentity() == $this->subject_id;
  }
  public function getComments($commentViewAll = false,$page = '',$type = 'newest')
  {

    if( null !== $this->_comments ) {
      return $this->_comments;
    }

    $activityCommentTable = Engine_Api::_()->getDbTable('comments', 'activity');
    $activityCommentTableName = $activityCommentTable->info('name');

    $coreCommentTable = Engine_Api::_()->getDbTable('comments', 'core');
    $coreCommentTableName = $coreCommentTable->info('name');

    $comments = $this->comments();

    $table = $comments->getReceiver();

   // $comment_count = $comments->getCommentCount();

    //if( $comment_count <= 0 ) {
      //return;
    //}

    $reverseOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', false);

    // Always just get the last three comments
    $select = $comments->getCommentSelect();

    if($table->info('name') == 'engine4_core_comments') {
        $select->from($coreCommentTableName, '*');
    } else if($table->info('name') == 'engine4_activity_comments') {
        $select->from($activityCommentTableName, '*');
    }

    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {

        if($table->info('name') == 'engine4_core_comments') {
            $corecommentsTableName = Engine_Api::_()->getDbtable('corecomments', 'sesadvancedactivity')->info('name');
            $select->setIntegrityCheck(false)
                ->joinLeft($corecommentsTableName, $corecommentsTableName.'.core_comment_id ='.$coreCommentTableName.'.comment_id', array('*'))
                ->where($corecommentsTableName.'.parent_id =?',0);

        } else if($table->info('name') == 'engine4_activity_comments') {
            $activitycommentsTableName = Engine_Api::_()->getDbtable('activitycomments', 'sesadvancedactivity')->info('name');
            $select->setIntegrityCheck(false)
                ->joinLeft($activitycommentsTableName, $activitycommentsTableName.'.activity_comment_id ='.$activityCommentTableName.'.comment_id', array('*'))
                ->where($activitycommentsTableName.'.parent_id =?',0);
        }
    }

    /*if($page == ''){
      if( $comment_count <= 5 ) {
        $select->limit(5);
      } else if( !$commentViewAll ) {
        if ($reverseOrder)
          $select->limit(5);
        else
          $select->limit(5, $comment_count - 5);
      }
    }
    if(!strlen($page) && $page != 'zero')
      return $this->_comments = $table->fetchAll($select);*/
    if($page == 'zero')
      $page = 1;
     $select->reset('order');
    if($type){
      switch($type){
        case "newest":
          $select->order('comment_id DESC');
        break;
        case "oldest":
          $select->order('comment_id ASC');
          $select->order('comment_id DESC');
        break;
        case "liked":
          $select->order('like_count DESC');
          $select->order('comment_id DESC');
        break;
        case "replied":
          if($table->info('name') == 'engine4_core_comments') {
            $select->order($corecommentsTableName.'.reply_count DESC');
          } else if($table->info('name') == 'engine4_activity_comments') {
            $select->order($activitycommentsTableName.'.reply_count DESC');
          }
          $select->order('comment_id DESC');
        break;
      }
    }

    if(!$type){
     if(!$reverseOrder)
       $select->order('comment_id ASC');
     else
       $select->order('comment_id DESC');
    }

    if($commentViewAll)
     return $table->fetchAll($select);

    $comments = Zend_Paginator::factory($select);
    $comments->setCurrentPageNumber($page);
    $comments->setItemCountPerPage(5);
    return $comments;
  }

  public function isPinPost($params = array()){
      if(!empty($params['resource_type']) && !empty($params['resource_id']) && !empty($params['action_id'])){
        $table = Engine_Api::_()->getDbTable('pinposts','sesadvancedactivity');
        $select = $table->select()->where('resource_id	 =?',$params['resource_id'])->where('resource_type =?',$params['resource_type'])
                  ->where('action_id =?',$params['action_id']);
        return $table->fetchRow($select);
      }
      return false;
  }
  public function getCommentsLikes($comments, $viewer)
  {
    if( empty($comments) ) {
      return array();
    }

    $firstComment = $comments[0];
    if( !is_object($firstComment) ||
        !method_exists($firstComment, 'likes') ) {
      return array();
    }

    $likes = $firstComment->likes();
    $table = $likes->getReceiver();

    $ids = array();

    foreach( $comments as $c ) {
      $ids[] = $c->comment_id;
    }

    $select = $table
      ->select()
      ->from($table, 'resource_id')
      ->where('resource_id IN (?)', $ids)
      ->where('poster_type = ?', $viewer->getType())
      ->where('poster_id = ?', $viewer->getIdentity());

    if ($table instanceof Core_Model_DbTable_Likes) {
        $select->where('resource_type = ?', $firstComment->getType());
    }

    $isLiked = array();

    $rs = $table->fetchAll($select);

    foreach( $rs as $r ) {
      $isLiked[$r->resource_id] = true;
    }

    return $isLiked;
  }

  public function comments($isGroup = false)
  {
    $commentable = $this->getCommentable();
    switch( $commentable ) {
      // Comments linked to action item
      default: case 0: case 1:
        if($isGroup)
          return  $this;
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'activity'));
        break;

      // Comments linked to subject
      case 2:
      if($isGroup)
          return  $this->getSubject();

        return $this->getSubject()->comments();
        break;

      // Comments linked to object
      case 3:
      if($isGroup)
          return  $this->getObject();
        return $this->getObject()->comments();
        break;

      // Comments linked to the first attachment
      case 4:
        $attachments = $this->getAttachments();
        if( !isset($attachments[0]) ) {

          // We could just link them to the action item instead
          throw new Activity_Model_Exception('No attachment to link comments to');
        }
        if($isGroup)
          return  $attachments[0]->item;
        return $attachments[0]->item->comments();
        break;
    }

    throw new Activity_Model_Exception('Comment handler undefined');
  }

  public function likes($isGroup = false)
  {
    $commentable = $this->getCommentable();
    switch( $commentable ) {
      // Comments linked to action item
      default: case 0: case 1:
        if($isGroup)
          return  $this;
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'activity'));
        break;

      // Comments linked to subject
      case 2:
        if($isGroup)
          return $this->getSubject();
        return $this->getSubject()->likes();
        break;

      // Comments linked to object
      case 3:
      if($isGroup)
          return $this->getObject();
        return $this->getObject()->likes();
        break;

      // Comments linked to the first attachment
      case 4:
        $attachments = $this->getAttachments();
        if( !isset($attachments[0]) )
        {
          // We could just link them to the action item instead
          throw new Activity_Model_Exception('No attachment to link comments to');
        }
        if($isGroup)
          return  $attachments[0]->item;
        return $attachments[0]->item->likes();;
        break;
    }

    throw new Activity_Model_Exception('Likes handler undefined');
  }
  public function getBuySellItem(){
    $action_id = $this->action_id;
    $table  = Engine_Api::_()->getDbTable('buysells','sesadvancedactivity');
    $select = $table->select()->where('action_id = ?', (int) $action_id);
    return $table->fetchRow($select);
  }
  public function intializeAttachmentcount(){
    $this->_attachments = null;
  }

  //Make conpitablity Code with lesser version of SE
  public function getCommentable() {
    $coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version;
    if(version_compare($coreVersion, '4.8.5') < 0){
      return $this->getTypeInfo()->commentable;
    } else {
      $commentable = (int) $this->getTypeInfo()->commentable;
      if ($commentable !== 4) {
        return $commentable;
      }
      $attachment = $this->getFirstAttachment();
      if (!($attachment && $attachment->item instanceof Core_Model_Item_Abstract) || !method_exists($attachment->item, 'comments') || !method_exists($attachment->item, 'likes')) {
        $commentable = 1;
      }

      return $commentable;
    }
  }
    public function getAttachments()
    {
        if (null !== $this->_attachments) {
            // return $this->_attachments;
        }

        if ($this->attachment_count <= 0) {
            // return null;
        }

        $table = Engine_Api::_()->getDbtable('attachments', 'activity');
        $select = $table->select()
            ->where('action_id = ?', $this->action_id);
        $_attachments = array();
        foreach ($table->fetchAll($select) as $row) {
            $item = Engine_Api::_()->getItem($row->type, $row->id);
            if ($item instanceof Core_Model_Item_Abstract) {
                $val = new stdClass();
                $val->meta = $row;
                $val->item = $item;
                $_attachments[] = $val;
            }
        }

        return $_attachments;
    }
  public function getCommentableItem() {
    $coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version;
    if(version_compare($coreVersion, '4.8.5') < 0){
      return $this->getTypeInfo()->commentable;
    } else {
      $commentable = $this->getCommentable();

      // Comments linked to the first attachment
      if ($commentable === 4) {
          return $this->getFirstAttachment()->item;
      }

      return $this->getObject();
    }
  }
}
