<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: CategoryController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_CategoryController extends Core_Controller_Action_Standard {

  public function browseAction() {
    $this->_helper->content->setEnabled();
  }

  //function to get images as per given album id.
  public function eventDataAction() {

    $event_id = $this->_getParam('event_id', false);
    if ($event_id) {
      //default params
      if (isset($_POST['params']))
        $params = json_decode($_POST['params'], true);
      $this->view->title_truncation = $title_truncation = isset($params['title_truncation']) ? $params['title_truncation'] : $this->_getParam('title_truncation', '100');
      $this->view->description_truncation = $description_truncation = isset($params['description_truncation']) ? $params['description_truncation'] : $this->_getParam('description_truncation', '150');
      $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('by', 'view', 'title', 'follow', 'followButton', 'featuredLabel', 'sponsoredLabel', 'description', 'albumPhoto', 'albumPhotos', 'photoThumbnail', 'albumCount'));
      foreach ($show_criterias as $show_criteria)
        $this->view->{$show_criteria . 'Active'} = $show_criteria;
      $resultArray = array();
      $albumDatas = $resultArray['event_data'] =  Engine_Api::_()->getDbTable('events', 'sesevent')->getEventSelect(array('event_id'=> $event_id,'limit_data'=> 1, 'fetchAll' => 1)); 
      $this->view->resultArray = $resultArray;
    } else {
      $this->_forward('requireauth', 'error', 'core');
    }
  }

  public function indexAction() {

    $category_id = $this->_getParam('category_id', 0);

    if ($category_id)
      $category_id = Engine_Api::_()->getDbtable('categories', 'sesevent')->getCategoryId($category_id);
    else
      return;

    $category = Engine_Api::_()->getItem('sesevent_category', $category_id);
    if ($category)
      Engine_Api::_()->core()->setSubject($category);
    else
      $this->_forward('requireauth', 'error', 'core');
    
    // item is a type of object chanel
    // if this is sending a message id, the user is being directed from a coversation
    // check if member is part of the conversation
    $message_id = $this->getRequest()->getParam('message');
    $message_view = false;
    if ($message_id) {
      $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
      if ($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) {
        $message_view = true;
      }
    }
    $this->view->message_view = $message_view;
    // Render
    $this->_helper->content->setEnabled();
  }

}
