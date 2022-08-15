<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Notification.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_Notification extends Core_Model_Item_Abstract//extends Engine_Db_Table_Row
{
  protected $_searchTriggers = false;

  protected $_user;
  
  protected $_object;
  
  protected $_subject;
  protected $_type = 'activity_notification';
  public function getParent($type = null)
  {
    // @todo not sure if this is correct
    return $this->getObject();
  }

  public function getOwner($type = null)
  {
    // @todo not sure if this is correct
    return $this->getSubject();
  }

  public function getContent()
  {
    $model = Engine_Api::_()->getApi('core', 'sesadvancedactivity');
    $params = array_merge(
      $this->toArray(),
      (array) $this->params,
      array(
        'user' => $this->getUser(),
        'object' => $this->getObject(),
        'subject' => $this->getSubject(),
      )
    );
    $content = $model->assemble($this->getTypeInfo()->body, $params);
    return $content;
  }

  public function getUser()
  {
    if( null === $this->_user ) {
      $this->_user = Engine_Api::_()->getItem('user', $this->user_id);
    }

    return $this->_user;
  }
  
  public function getSubject()
  {
    if( null === $this->_subject )
    {
      $this->_subject = Engine_Api::_()->getItem($this->subject_type, $this->subject_id);
    }

    return $this->_subject;
  }

  public function getObject()
  {
    if( null === $this->_object )
    {
      $this->_object = Engine_Api::_()->getItem($this->object_type, $this->object_id);
    }

    return $this->_object;
  }

  public function getTypeInfo()
  {
    $info = Engine_Api::_()->getDbtable('notificationTypes', 'sesadvancedactivity')->getNotificationType($this->type);
    if( !$info )
    {
      throw new Sesadvancedactivity_Model_Exception('Notification Type is missing ' . $this->type);
    }
    return $info;
  }
  
  public function __toString()
  {
    return $this->getContent();
  }
}