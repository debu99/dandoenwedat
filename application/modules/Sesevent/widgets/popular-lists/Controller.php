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
class Sesevent_Widget_PopularListsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $coreApi = Engine_Api::_()->core();
    $this->view->width = $this->_getParam('width', 100);
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->view->showType = $this->_getParam('showType', 'gridview');
    $this->view->viewType = $this->_getParam('viewType', 'horizontal');
    $this->view->height = $this->_getParam('height', '200');
    $this->view->titletruncation = $this->_getParam('titletruncation', '16');

    $this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);
    
    $showOptionsType = $this->_getParam('showOptionsType', 'all');

    if ($showOptionsType == 'other') {
      $list = $coreApi->getSubject('sesevent_list');
      if (!$list)
        return $this->setNoRender();
    }

    $this->view->information = $this->_getParam('information', array('viewCount', 'title', 'postedby', 'share','eventcount', 'favouriteButton', 'favouriteCount','featuredLabel','sponsoredLabel','likeButton', 'socialSharing','likeCount','viewCount','showEventsList'));

    $params = array();
    if ($showOptionsType == 'recommanded') {
      $params['widgteName'] = 'Recommanded List';
    } elseif ($showOptionsType == 'other') {
      $list_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('list_id');
      if ($list_id)
        $list = Engine_Api::_()->getItem('sesevent_list', $list_id);
      $params['owner_id'] = $list->owner_id;
      $params['widgteName'] = 'Other List';
      $params['list_id'] = $list->list_id;
    }
    $params['popularity'] = $this->_getParam('popularity', 'creation_date');
    $params['limit'] = $this->_getParam('limit', 3);
    $this->view->results = Engine_Api::_()->getDbtable('lists', 'sesevent')->getListPaginator($params);
    if (count($this->view->results) <= 0)
      return $this->setNoRender();
  }

}
