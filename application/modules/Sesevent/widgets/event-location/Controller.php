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
class Sesevent_Widget_EventLocationController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1))
			return $this->setNoRender();
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
      parse_str($_POST['searchParams'], $searchArray);
    if (!$is_ajax)
      $value['locationWidget'] = true;
		$limit = $this->_getParam('limit_data',200);
    $value['category_id'] = isset($searchArray['category_id']) ? $searchArray['category_id'] : (isset($_GET['category_id']) ? $_GET['category_id'] : (isset($params['category_id']) ? $params['category_id'] : ''));
    $value['sort'] = isset($searchArray['order']) ? $searchArray['order'] : (isset($_GET['order']) ? $_GET['order'] : (isset($params['order']) ? $params['order'] : $this->_getParam('sort', 'mostSPliked')));
    $value['subcat_id'] = isset($searchArray['subcat_id']) ? $searchArray['subcat_id'] : (isset($_GET['subcat_id']) ? $_GET['subcat_id'] : (isset($params['subcat_id']) ? $params['subcat_id'] : ''));
    $value['subsubcat_id'] = isset($searchArray['subsubcat_id']) ? $searchArray['subsubcat_id'] : (isset($_GET['subsubcat_id']) ? $_GET['subsubcat_id'] : (isset($params['subsubcat_id']) ? $params['subsubcat_id'] : ''));
    $value['search'] = 1;
     $this->view->location = $value['location'] = isset($searchArray['location']) ? $searchArray['location'] : (isset($_GET['location']) ? $_GET['location'] : (isset($params['location']) ? $params['location'] : $this->_getParam('location')));
     
    $this->view->socialshare_enable_plusicon = $value['socialshare_enable_plusicon'] = isset($searchArray['socialshare_enable_plusicon']) ? $searchArray['socialshare_enable_plusicon'] : (isset($_GET['socialshare_enable_plusicon']) ? $_GET['socialshare_enable_plusicon'] : (isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1)));
    
    $this->view->socialshare_icon_limit = $value['socialshare_icon_limit'] = isset($searchArray['socialshare_icon_limit']) ? $searchArray['socialshare_icon_limit'] : (isset($_GET['socialshare_icon_limit']) ? $_GET['socialshare_icon_limit'] : (isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2)));
    
     
    $this->view->lat = $value['lat'] = isset($searchArray['lat']) ? $searchArray['lat'] : (isset($_GET['lat']) ? $_GET['lat'] : (isset($params['lat']) ? $params['lat'] : $this->_getParam('lat', '26.9110600')));
    $value['show'] = isset($searchArray['show']) ? $searchArray['show'] : (isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ? $params['show'] : ''));
    $this->view->lng = $value['lng'] = isset($searchArray['lng']) ? $searchArray['lng'] : (isset($_GET['lng']) ? $_GET['lng'] : (isset($params['lng']) ? $params['lng'] : $this->_getParam('lng', '75.7373560')));
    $value['miles'] = isset($searchArray['miles']) ? $searchArray['miles'] : (isset($_GET['miles']) ? $_GET['miles'] : (isset($params['miles']) ? $params['miles'] : $this->_getParam('miles', '1000')));
    $value['text'] = $text = isset($searchArray['search']) ? $searchArray['search'] : (!empty($params['search']) ? $params['search'] : (isset($_GET['search']) && ($_GET['search'] != '') ? $_GET['search'] : ''));
		$this->view->show_criterias =  $show_criterias = isset($_POST['show_criterias']) ? json_decode($_POST['show_criterias'],true) : $this->_getParam('show_criteria', array('like', 'comment','by','title','favouriteButton','likeButton','socialSharing','view','location'));
	  
		foreach ($show_criterias as $show_criteria)
   	 $this->view->{$show_criteria . 'Active'} = $show_criteria;
    $defaultOrder = $value['sort'];
    if(!empty($defaultOrder)) {
      $orderKey = str_replace(array('SP',''), array(' ',' '), $defaultOrder);
      $defaultOrder = Engine_Api::_()->sesevent()->getColumnName($orderKey);
    }else
    	$defaultOrder = 'like_count DESC';
    $value['order'] = $defaultOrder;
		$value['view'] =  isset($searchArray['view']) ? $searchArray['view'] : (isset($_GET['view']) ? $_GET['view'] : (isset($params['view']) ? $params['view'] : ''));
    $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventPaginator($value, true);
    $paginator->setItemCountPerPage($limit);
    $paginator->setCurrentPageNumber(1);
    $this->view->widgetName = 'event-location';
  }
}