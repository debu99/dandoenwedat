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
class Sesevent_Widget_ManageEventsController extends Engine_Content_Widget_Abstract {
  public function indexAction() {  
    //Prepare
    if (isset($_POST['params']))
    $params = json_decode($_POST['params'], true);
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
    parse_str($_POST['searchParams'], $searchArray);
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $defaultOptionsArray = $this->_getParam('search_type',array('all','joinedEvents','hostedEvents','save','like','favourite','featured','sponsored','verified','lists','hosts'));
    if (!$is_ajax && is_array($defaultOptionsArray)) {
				$this->view->tab_option = $this->_getParam('tabOption','vertical');
      $defaultOptions = $arrayOptions = array();
      foreach ($defaultOptionsArray as $key => $defaultValue) {
        if ($this->_getParam($defaultValue . '_order'))
          $order = $this->_getParam($defaultValue . '_order') ;
        else
          $order = (777 + $key);
        if ($this->_getParam($defaultValue . '_label'))
          $valueLabel = $this->_getParam($defaultValue . '_label'). '||' . $defaultValue;
        else {
					if ($defaultValue == 'all')
            $valueLabel = 'Events'. '||' . $defaultValue;
					else if ($defaultValue == 'joinedEvents')
            $valueLabel = 'Joined Events Only'. '||' . $defaultValue;
					else if ($defaultValue == 'hostedEvents')
            $valueLabel = 'Hosted Events Only'. '||' . $defaultValue;
				 else if ($defaultValue == 'save')
            $valueLabel = 'Saved Events'. '||' . $defaultValue;
					else if ($defaultValue == 'like')
            $valueLabel = 'Liked Events'. '||' . $defaultValue;
          else if ($defaultValue == 'favourite')
            $valueLabel = 'Favourite Events'. '||' . $defaultValue;
						
					else if ($defaultValue == 'featured')
            $valueLabel = 'Featured Events'. '||' . $defaultValue;
					else if ($defaultValue == 'sponsored')
            $valueLabel = 'Sponsored  Events'. '||' . $defaultValue;
					else if ($defaultValue == 'verified')
            $valueLabel = 'verified  Events'. '||' . $defaultValue;
					else if ($defaultValue == 'lists')
            $valueLabel = 'My Lists'. '||' . $defaultValue;   
				else if ($defaultValue == 'hosts')
            $valueLabel = 'My Hosts'. '||' . $defaultValue;         
        }
        $arrayOptions[$order] = $valueLabel;
      }
      ksort($arrayOptions);
      $counter = 0;
      foreach ($arrayOptions as $key => $valueOption) {
        $key = explode('||', $valueOption);
        if ($counter == 0)
          $this->view->defaultOpenTab = $defaultOpenTab = $key[1];
        $defaultOptions[$key[1]] = $key[0];
        $counter++;
      }
      $this->view->defaultOptions = $defaultOptions;
    }
     if (isset($_GET['openTab']) || $is_ajax) {
      $this->view->defaultOpenTab = $defaultOpenTab = ($this->_getParam('openTab',false) ? $this->_getParam('openTab') : (isset($params['manageorder']) ? $params['manageorder'] : ''));
    }
    $text =  isset($searchArray['search_text']) ? $searchArray['search_text'] : (!empty($params['search_text']) ? $params['search_text'] : (isset($_GET['search_text']) && ($_GET['search_text'] != '') ? $_GET['search_text'] : ''));
    $limit_data = isset($params['limit_data']) ? $params['limit_data'] : $this->_getParam('limit_data', '10');
    $this->view->list_title_truncation = $list_title_truncation = isset($params['list_title_truncation']) ? $params['list_title_truncation'] : $this->_getParam('list_title_truncation', '100');
    $this->view->grid_title_truncation= $grid_title_truncation = isset($params['grid_title_truncation']) ? $params['grid_title_truncation'] : $this->_getParam('grid_title_truncation', '100');
		$this->view->masonry_title_truncation = $masonry_title_truncation = isset($params['masonry_title_truncation']) ? $params['masonry_title_truncation'] : $this->_getParam('masonry_title_truncation', '100');
		$this->view->pinboard_title_truncation = $pinboard_title_truncation = isset($params['pinboard_title_truncation']) ? $params['pinboard_title_truncation'] : $this->_getParam('pinboard_title_truncation', '100');
    $this->view->list_description_truncation = $list_description_truncation = isset($params['list_description_truncation']) ? $params['list_description_truncation'] : $this->_getParam('list_description_truncation', '100');
		$this->view->grid_description_truncation = $grid_description_truncation = isset($params['grid_description_truncation']) ? $params['grid_description_truncation'] : $this->_getParam('grid_description_truncation', '100');
		$this->view->pinboard_description_truncation = $pinboard_description_truncation = isset($params['pinboard_description_truncation']) ? $params['pinboard_description_truncation'] : $this->_getParam('pinboard_description_truncation', '100');
    $value['category_id'] =  isset($searchArray['category_id']) ? $searchArray['category_id'] : (isset($_GET['category_id']) ? $_GET['category_id'] : (isset($params['category_id']) ? $params['category_id'] : ''));
		$value['subcat_id'] = isset($searchArray['subcat_id']) ? $searchArray['subcat_id'] :  (isset($_GET['subcat_id']) ? $_GET['subcat_id'] : (isset($params['subcat_id']) ? $params['subcat_id'] : ''));
    $value['subsubcat_id'] = isset($searchArray['subsubcat_id']) ? $searchArray['subsubcat_id'] : (isset($_GET['subsubcat_id']) ? $_GET['subsubcat_id'] : (isset($params['subsubcat_id']) ? $params['subsubcat_id'] : ''));
		$value['location'] = isset($searchArray['location']) ? $searchArray['location'] :  (isset($_GET['location']) ? $_GET['location'] : (isset($params['location']) ?  $params['location'] : ''));
		$this->view->advgrid_title_truncation= $advgrid_title_truncation = isset($params['advgrid_title_truncation']) ? $params['advgrid_title_truncation'] : $this->_getParam('advgrid_title_truncation', '100');
		$this->view->advgrid_height = $advgrid_height = isset($params['advgrid_height']) ? $params['advgrid_height'] : $this->_getParam('advgrid_height', '222');
    $this->view->advgrid_width = $advgrid_width = isset($params['advgrid_width']) ? $params['advgrid_width'] : $this->_getParam('advgrid_width', '322');
		
		$this->view->width_hosts = $width_hosts = isset($params['width_hosts']) ? $params['width_hosts'] : $this->_getParam('width_hosts', '322');
		$this->view->height_hosts = $height_hosts = isset($params['height_hosts']) ? $params['height_hosts'] : $this->_getParam('height_hosts', '322');
    $value['show'] = isset($searchArray['show']) ? $searchArray['show'] :  (isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ?  $params['show'] : ''));
    $value['miles'] = isset($searchArray['miles']) ? $searchArray['miles'] :  (isset($_GET['miles']) ? $_GET['miles'] : (isset($params['miles']) ?  $params['miles'] : ''));
    $value['view'] =  isset($searchArray['view']) ? $searchArray['view'] : (isset($_GET['view']) ? $_GET['view'] : (isset($params['view']) ? $params['view'] : ''));
    //search data
    $orderKey = str_replace(array('SP',''), array(' ',' '), $defaultOpenTab);
    $defaultOrder = Engine_Api::_()->sesevent()->getColumnName($orderKey);
    $value['manageorder'] =  $defaultOrder;
    $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'rating', 'by', 'title', 'featuredLabel', 'sponsoredLabel', 'category', 'description','favouriteButton', 'likeButton', 'socialSharing', 'view'));
    $this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
    $this->view->loadOptionData = $loadOptionData = isset($params['pagging']) ? $params['pagging'] : $this->_getParam('pagging', 'auto_load');
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('sesevent', null, 'create');
    foreach ($show_criterias as $show_criteria)
    $this->view->{$show_criteria . 'Active'} = $show_criteria;
    if (!$is_ajax) {
      $this->view->optionsEnable = $optionsEnable = $this->_getParam('enableTabs', array('list', 'grid', 'pinboard', 'masonry', 'map'));
			$view_type = $this->_getParam('openViewType', 'list');
			if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1) && $view_type == 'map'){
				$view_type = 'list';	
			}
      if (count($optionsEnable) > 1) {
        $this->view->bothViewEnable = true;
      }
    }
    $this->view->view_type = $view_type = (isset($_POST['type']) ? $_POST['type'] : (isset($params['view_type']) ? $params['view_type'] : $view_type));
    $this->view->height = $defaultHeight = isset($params['height']) ? $params['height'] : $this->_getParam('height', '200');
    
    $this->view->socialshare_enable_plusicon = $socialshare_enable_plusicon = isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $socialshare_icon_limit = isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);
    
		$this->view->width_lists = $defaultwidth_lists = isset($params['width_lists']) ? $params['width_lists'] : $this->_getParam('width_lists', '300');
		$this->view->width = $defaultWidth = isset($params['width']) ? $params['width'] : $this->_getParam('width', '200');
    $this->view->photo_height = $defaultPhotoHeight = isset($params['photo_height']) ? $params['photo_height'] : $this->_getParam('photo_height', '200');
    $this->view->photo_width = $defaultPhotoWidth = isset($params['photo_width']) ? $params['photo_width'] : $this->_getParam('photo_width', '200');
    $this->view->info_height = $defaultInfoHeight = isset($params['info_height']) ? $params['info_height'] : $this->_getParam('info_height', '200');
    $this->view->pinboard_width = $defaultPinboardWidth = isset($params['pinboard_width']) ? $params['pinboard_width'] : $this->_getParam('pinboard_width', '200px');
    $this->view->masonry_height = $defaultMasonryHeight = isset($params['masonry_height']) ? $params['masonry_height'] : $this->_getParam('masonry_height', '200px');
    $params = array('pagging' => $loadOptionData, 'limit_data' => $limit_data, 'list_title_truncation' => $list_title_truncation, 'grid_title_truncation' => $grid_title_truncation,'masonry_title_truncation' => $masonry_title_truncation,'pinboard_title_truncation' => $pinboard_title_truncation ,'list_description_truncation' => $list_description_truncation,'grid_description_truncation' => $grid_description_truncation,'pinboard_description_truncation' => $pinboard_description_truncation,'show_criterias' => $show_criterias,'view_type' => $view_type, 'height' => $defaultHeight,'photo_height' => $defaultPhotoHeight,'photo_width' => $defaultPhotoWidth,'info_height' => $defaultInfoHeight,'pinboard_width' => $defaultPinboardWidth,'masonry_height' => $defaultMasonryHeight,'category_id' => $value['category_id'],'order' => $defaultOpenTab, 'subcat_id' => $value['subcat_id'], 'subsubcat_id' => $value['subsubcat_id'], 'location'=>$value['location'],'width'=>$defaultWidth,'advgrid_title_truncation'=>$advgrid_title_truncation,'advgrid_height'=>$advgrid_height,'advgrid_width'=>$advgrid_width,'width_lists'=>$defaultwidth_lists,'width_hosts'=>$width_hosts,'height_hosts'=>$height_hosts, 'socialshare_enable_plusicon' => $socialshare_enable_plusicon, 'socialshare_icon_limit' => $socialshare_icon_limit);
    $this->view->widgetName = 'manage-events';
    $this->view->page = $page;
    $this->view->params = array_merge($params, $value);
    if($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    }
		$value['widgetManage'] = true;
    // Get paginator
		$this->view->manageorder = $value['manageorder'];
		if($value['manageorder'] == 'lists'){
			 $this->view->first_content  = isset($_POST['first_content']) ? true : false;
			$this->view->viewer_id = $viewer->getIdentity();
			$this->view->information = $show_criterias;
			$this->view->paginator = $paginator = Engine_Api::_()->getDbTable('lists', 'sesevent')
            ->getListPaginator(array_merge($value,array('action'=>'manage','user'=>$viewer->getIdentity())));
		}else if($value['manageorder'] == 'hosts'){
			$this->view->first_content  = isset($_POST['first_content']) ? true : false;
			$this->view->paginator = $paginator = Engine_Api::_()->getDbTable('hosts', 'sesevent')
            ->getAllHosts(array_merge($value,array('owner_id'=>$viewer->getIdentity())));
		}else{
		if(isset($_POST['searchCtr']))
			$value['searchCtr'] = $_POST['searchCtr'];
		else
			$value['searchCtr'] = 'upcomming';
      $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')
            ->getEventPaginator($value);
		}
    $paginator->setItemCountPerPage($limit_data);
    $paginator->setCurrentPageNumber($page);
  }
}