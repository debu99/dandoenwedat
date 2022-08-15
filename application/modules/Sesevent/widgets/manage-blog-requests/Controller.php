<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_ManageBlogRequestsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $value = array();
    $this->view->event_id = $event_id = $value['event_id'] = $this->_getParam('event_id', false);
    if (!$event_id)
      return $this->setNoRender();
    $this->view->is_search_ajax = $is_search_ajax = isset($_POST['is_search_ajax']) ? $_POST['is_search_ajax'] : false;
   
    $this->view->event = $event = Engine_Api::_()->getItem("sesevent_event", $event_id);

    $mapBlogTable = Engine_Api::_()->getDbtable('mapevents', 'sesblog');
    $select = $mapBlogTable->select()->where('event_id =?', $event_id);
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(10);
  }

}
