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
class Sesevent_Widget_ListViewPageController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    // Default option for tabbed widget
    if (isset($_POST['params']))
      $params = $_POST['params'];
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
    if (!$is_ajax)
      $list_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('list_id');
    else
      $list_id = $params['list_id'];
    $this->view->lists = $list = Engine_Api::_()->getItem('sesevent_list', $list_id);
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    //Get viewer/subject
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();
    $this->view->informationList = $this->_getParam('informationList', array('editButton', 'deleteButton','viewCountList', 'postedby', 'shareList', 'watchLater', 'favouriteButtonList', 'descriptionList','favouriteCountList','featuredLabelList', 'sponsoredLabelList','likeButtonList','socialSharingList','likeCountList','reportList'));
    $this->view->loadOptionData = $loadOptionData = isset($params['pagging']) ? $params['pagging'] : $this->_getParam('pagging', 'auto_load');
   $this->view->height_list = $defaultHeightList = isset($params['height_list']) ? $params['height_list'] : $this->_getParam('height_list','160');
    $this->view->width_list = $defaultWidthList = isset($params['width_list']) ? $params['width_list'] : $this->_getParam('width_list','140');
		$this->view->height_grid = $defaultHeightGrid = isset($params['height_grid']) ? $params['height_grid'] : $this->_getParam('height_grid','160');
		
		
		$this->view->socialshare_enable_plusicon = $socialshare_enable_plusicon = isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
		$this->view->socialshare_icon_limit = $socialshare_icon_limit = isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);
		
    $this->view->width_grid = $defaultWidthGrid = isset($params['width_grid']) ? $params['width_grid'] : $this->_getParam('width_grid','140');
		$this->view->width_pinboard = $defaultWidthPinboard = isset($params['width_pinboard']) ? $params['width_pinboard'] : $this->_getParam('width_pinboard','300');
    $this->view->limit_data = $limit_data = isset($params['limit_data']) ? $params['limit_data'] : $this->_getParam('limit_data', '10');
    $this->view->limit = ($page - 1) * $limit_data;
    $this->view->title_truncation_list = $title_truncation_list = isset($params['title_truncation_list']) ? $params['title_truncation_list'] : $this->_getParam('title_truncation_list', '100');
    $this->view->title_truncation_grid = $title_truncation_grid = isset($params['title_truncation_grid']) ? $params['title_truncation_grid'] : $this->_getParam('title_truncation_grid', '100');
		$this->view->title_truncation_pinboard = $title_truncation_pinboard = isset($params['title_truncation_pinboard']) ? $params['title_truncation_pinboard'] : $this->_getParam('title_truncation_pinboard', '100');
    $this->view->description_truncation_list = $description_truncation_list = isset($params['description_truncation_list']) ? $params['description_truncation_list'] : $this->_getParam('description_truncation_list', '100');
		$this->view->description_truncation_grid = $description_truncation_grid = isset($params['description_truncation_grid']) ? $params['description_truncation_grid'] : $this->_getParam('description_truncation_grid', '100');
		$this->view->description_truncation_pinboard = $description_truncation_pinboard = isset($params['description_truncation_pinboard']) ? $params['description_truncation_pinboard'] : $this->_getParam('description_truncation_pinboard', '100');
    $text = (!empty($params['text']) ? $params['text'] : (isset($_GET['text']) && ($_GET['text'] != '') ? $_GET['text'] : ''));
    $category = (!empty($params['category']) ? $params['category'] : (isset($_GET['category']) && intval($_GET['category']) ? $_GET['category'] : ''));
     $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'rating', 'by', 'title', 'featuredLabel', 'sponsoredLabel', 'watchLater', 'category', 'description', 'duration', 'hotLabel', 'favouriteButton', 'listAdd', 'likeButton', 'socialSharing', 'view'));
    foreach ($show_criterias as $show_criteria)
      $this->view->{$show_criteria . 'Active'} = $show_criteria;
    $this->view->view_type = $view_type = (isset($_POST['type']) ? $_POST['type'] : (isset($params['view_type']) ? $params['view_type'] : $this->_getParam('view_type', 'list')));
		$this->view->viewTypeStyle = $viewTypeStyle = (isset($_POST['viewTypeStyle']) ? $_POST['viewTypeStyle'] : (isset($params['viewTypeStyle']) ? $params['viewTypeStyle'] : $this->_getParam('viewTypeStyle','fixed')));
    if (!$is_ajax) {
			$this->view->bothViewEnable = false;
      $this->view->optionsEnable = $optionsEnable = $this->_getParam('enableTabs', array('list', 'grid', 'pinboard'));
      $view_type = $this->_getParam('openViewType', 'list');
      if (count($optionsEnable) > 1) {
        $this->view->bothViewEnable = true;
      }
    }
		$this->view->listId = $list_id;
    $params = array('height_list' => $defaultHeightList, 'width_list' => $defaultWidthList,'height_grid' => $defaultHeightGrid, 'width_grid' => $defaultWidthGrid,'width_pinboard' => $defaultWidthPinboard, 'limit_data' => $limit_data, 'pagging' => $loadOptionData, 'show_criterias' => $show_criterias, 'view_type' => $view_type, 'description_truncation_list' => $description_truncation_list, 'title_truncation_list' => $title_truncation_list, 'title_truncation_grid' => $title_truncation_grid,'title_truncation_pinboard'=>$title_truncation_pinboard,'description_truncation_grid'=>$description_truncation_grid,'description_truncation_pinboard'=>$description_truncation_pinboard,'list_id' => $list_id,'viewTypeStyle' => $viewTypeStyle, 'socialshare_enable_plusicon' => $socialshare_enable_plusicon, 'socialshare_icon_limit' => $socialshare_icon_limit);
    $this->view->loadMoreLink = $this->_getParam('openTab') != NULL ? true : false;
    $this->view->loadJs = true;
    // get event associated to list
    $this->view->paginator = $paginator = $list->getEvents(array('list_id' => $list_id, 'order' => true), true);
    $this->view->widgetName = 'list-view-page';
    $this->view->getEventItem = true;
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($limit_data);
    $this->view->page = $page;
    $this->view->params = $params;
    $paginator->setCurrentPageNumber($page);
    if ($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    } else {
			$getmodule = Engine_Api::_()->getDbTable('modules', 'core')->getModule('core');
			if (!empty($getmodule->version) && version_compare($getmodule->version, '4.8.8') >= 0){
				$this->view->doctype('XHTML1_RDFA');
				$this->view->docActive = true;
			}
      // Do not render if nothing to show
      if ($paginator->getTotalItemCount() <= 0) {
        
      }
    }
  }

}
