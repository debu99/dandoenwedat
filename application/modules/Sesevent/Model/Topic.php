<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Topic.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Topic extends Core_Model_Item_Abstract {

  protected $_parent_type = 'sesevent_event';
  protected $_owner_type = 'user';
  protected $_children_types = array('sesevent_post');

  public function isSearchable() {
    $event = $this->getParentEvent();
    if (!($event instanceof Core_Model_Item_Abstract)) {
      return false;
    }
    return $event->isSearchable();
  }

  public function getHref($params = array()) {
    $params = array_merge(array(
        'route' => 'sesevent_extended',
        'controller' => 'topic',
        'action' => 'view',
        'event_id' => $this->event_id,
        'topic_id' => $this->getIdentity(),
            ), $params);
    $route = @$params['route'];
    unset($params['route']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
  }

  public function getDescription() {
    $firstPost = $this->getFirstPost();
    $content = '';
    if (null !== $firstPost) {
      $content = $firstPost->body;
      // strip HTML and BBcode
      $content = strip_tags($content);
      $content = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $content);
      $content = ( Engine_String::strlen($content) > 255 ? Engine_String::substr($content, 0, 255) . '...' : $content );
    }
    return $content;
  }

  public function getParentEvent() {
    return Engine_Api::_()->getItem('sesevent_event', $this->event_id);
  }
  //write function for feed
 public function getParent() {
    return Engine_Api::_()->getItem('sesevent_event', $this->event_id);
  }
  public function getFirstPost() {
    $table = Engine_Api::_()->getDbtable('posts', 'sesevent');
    $select = $table->select()
            ->where('topic_id = ?', $this->getIdentity())
            ->order('post_id ASC')
            ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLastPost() {
    $table = Engine_Api::_()->getItemTable('sesevent_post');
    $select = $table->select()
            ->where('topic_id = ?', $this->getIdentity())
            ->order('post_id DESC')
            ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLastPoster() {
    return Engine_Api::_()->getItem('user', $this->lastposter_id);
  }

  public function getAuthorizationItem() {
    return $this->getParent('sesevent_event');
  }

  // Internal hooks

  protected function _insert() {
    if (!$this->event_id) {
      throw new Exception('Cannot create topic without event_id');
    }

    /*
      $this->getParentEvent()->setFromArray(array(

      ))->save();
     */

    parent::_insert();
  }

}
