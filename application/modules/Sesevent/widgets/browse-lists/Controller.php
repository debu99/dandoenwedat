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
class Sesevent_Widget_BrowseListsController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    if (isset($_POST['params']))
      $params = json_decode($_POST['params'], true);
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();
    $this->view->viewmore = $this->_getParam('viewmore', 0);
    $this->view->listCount = $this->_getParam('listCount', 1);
    
    $this->view->paginationType = $paginationType = isset($params['paginationType']) ? $params['paginationType'] : $this->_getParam('Type', 200);
		
    $this->view->width = $width = isset($params['width']) ? $params['width'] : $this->_getParam('width', 200);
    $this->view->height = $height = isset($params['height']) ? $params['height'] : $this->_getParam('height', 200);
    
    $this->view->socialshare_enable_plusicon = $socialshare_enable_plusicon = isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $socialshare_icon_limit = isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);
    
    $this->view->titletruncation = $title_truncation = isset($params['titletruncation']) ? $params['titletruncation'] : $this->_getParam('titletruncation', '16');

		 $this->view->description_truncation = $description_truncation = isset($params['description_truncation']) ? $params['description_truncation'] : $this->_getParam('description_truncation', 60);
    $this->view->information = $information = isset($params['information']) ? $params['information'] : $this->_getParam('information', array('viewCount', 'title', 'postedby', 'share','eventcount', 'favouriteButton', 'favouriteCount','featuredLabel','sponsoredLabel','likeButton', 'socialSharing','likeCount','viewCount','showEventsList'));
    if ($this->view->viewmore)
      $this->getElement()->removeDecorator('Container');
    $alphabet = isset($_GET['alphabet']) ? $_GET['alphabet'] : (isset($params['alphabet']) ? $params['alphabet'] : '');
    $itemCount = isset($params['itemCount']) ? $params['itemCount'] : $this->_getParam('itemCount', 10);
    $popularity = isset($_GET['popularity']) ? $_GET['popularity'] : (isset($params['popularity']) ? $params['popularity'] : $this->_getParam('popularity', 'creation_date'));
    $title = isset($_GET['title_name']) ? $_GET['title_name'] : (isset($params['title_name']) ? $params['title_name'] : '');
    $show = isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ? $params['show'] : 1);
    $users = array();
    if (isset($_GET['show']) && $_GET['show'] == 2 && $viewer->getIdentity()) {
      $users = $viewer->membership()->getMembershipsOfIds();
    }
    $action = isset($_GET['action']) ? $_GET['action'] : (isset($params['action']) ? $params['action'] : 'browse');
    $page = isset($_GET['page']) ? $_GET['page'] : $this->_getParam('page', 1);
    $this->view->all_params = $values = array('paginationType' => $paginationType, 'width' => $width, 'height' => $height, 'information' => $information, 'alphabet' => $alphabet, 'itemCount' => $itemCount, 'popularity' => $popularity, 'show' => $show, 'users' => $users, 'title' => $title, 'action' => $action, 'page' => $page,'description_truncation'=>$description_truncation, 'titletruncation' => $title_truncation, 'socialshare_enable_plusicon' => $socialshare_enable_plusicon, 'socialshare_icon_limit' => $socialshare_icon_limit);
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('lists', 'sesevent')->getListPaginator($values);
    $paginator->setItemCountPerPage($itemCount);
    $paginator->setCurrentPageNumber($page);
    $this->view->count = $paginator->getTotalItemCount();
  }
}