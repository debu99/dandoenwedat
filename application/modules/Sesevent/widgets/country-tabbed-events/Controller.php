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
class Sesevent_Widget_CountryTabbedEventsController extends Engine_Content_Widget_Abstract {
  public function indexAction() {  
     // Prepare
    if (isset($_POST['params']))
    	$params = json_decode($_POST['params'], true);
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
    	parse_str($_POST['searchParams'], $searchArray);
		$value['order'] =  isset($searchArray['criteria']) ? $searchArray['criteria'] : (!empty($params['criteria']) ? $params['criteria'] : (isset($_GET['criteria']) && ($_GET['criteria'] != '') ? $_GET['criteria'] : ($this->_getParam('criteria',false) ? $this->_getParam('criteria') : '')));
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
		$value['country'] =  isset($searchArray['country']) ? $searchArray['country'] : (!empty($params['country']) ? $params['country'] : (isset($_GET['country']) && ($_GET['country'] != '') ? $_GET['country'] : $this->_getParam('country',false)));
		$viewer = Engine_Api::_()->user()->getViewer();
		if(!$value['country'] && ($value['order'] == 1 && $viewer->getIdentity() == 0))
		 	$this->setNoRender();
    if (!$is_ajax && is_array($value['country'])) {
			$this->view->tab_option = $this->_getParam('tabOption','advance');
			$valueCountry = $value['country'];
			unset($value['country']);
			$counter = 0;
			foreach($valueCountry as $valCon){
				if($counter == 0)
					$defaultOpenTab = $valCon;
				$value['country'][$valCon]=$valCon;
				$counter++;
			}
      $this->view->defaultOptions =$value['country'];
			$value['defaultOpenTab'] = $this->view->defaultOpenTab;
    }
		$this->view->show_item_count = $show_item_count = isset($params['show_item_count']) ? $params['show_item_count'] :  $this->_getParam('show_item_count',0);
		$this->view->show_limited_data = $show_limited_data = isset($params['show_limited_data']) ? $params['show_limited_data'] :  $this->_getParam('show_limited_data',0);
    if (isset($_GET['openTab']) || $is_ajax)
      $this->view->defaultOpenTab = $defaultOpenTab = $value['defaultOpenTab'] = ($this->_getParam('openTab',false) ? $this->_getParam('openTab') : (isset($params['defaultOpenTab']) ? $params['defaultOpenTab'] : ''));
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
    $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'rating', 'by', 'title', 'featuredLabel', 'sponsoredLabel', 'category', 'description','favouriteButton', 'likeButton', 'socialSharing', 'view'));
    
    $this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
    $this->view->loadOptionData = $loadOptionData = isset($params['pagging']) ? $params['pagging'] : $this->_getParam('pagging', 'auto_load');
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('sesevent', null, 'create');
    foreach ($show_criterias as $show_criteria)
    $this->view->{$show_criteria . 'Active'} = $show_criteria;
    if (!$is_ajax) {
			$this->view->optionsEnable = $optionsEnable = $this->_getParam('enableTabs', array('list', 'grid', 'pinboard', 'masonry', 'map'));
			if(!count($optionsEnable))
				$this->setNoRender();
			$view_type = $this->_getParam('openViewType', 'list');
			if(!in_array($view_type,$optionsEnable)){
				$view_type = $optionsEnable[0];	
			}
			if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1) && $view_type == 'map'){
				$view_type = $optionsEnable[0];
			}
      if (count($optionsEnable) > 1) {
        $this->view->bothViewEnable = true;
      }
    }
    $this->view->view_type = $view_type = (isset($_POST['type']) ? $_POST['type'] : (isset($params['view_type']) ? $params['view_type'] : $view_type));
    $this->view->height = $defaultHeight = isset($params['height']) ? $params['height'] : $this->_getParam('height', '200px');
    
    $this->view->socialshare_enable_plusicon = $socialshare_enable_plusicon = isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $socialshare_icon_limit = isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);
    
		$this->view->width = $defaultWidth = isset($params['width']) ? $params['width'] : $this->_getParam('width', '200px');
    $this->view->photo_height = $defaultPhotoHeight = isset($params['photo_height']) ? $params['photo_height'] : $this->_getParam('photo_height', '200px');
    $this->view->photo_width = $defaultPhotoWidth = isset($params['photo_width']) ? $params['photo_width'] : $this->_getParam('photo_width', '200px');
    $this->view->info_height = $defaultInfoHeight = isset($params['info_height']) ? $params['info_height'] : $this->_getParam('info_height', '200px');
    $this->view->pinboard_width = $defaultPinboardWidth = isset($params['pinboard_width']) ? $params['pinboard_width'] : $this->_getParam('pinboard_width', '200px');
    $this->view->masonry_height = $defaultMasonryHeight = isset($params['masonry_height']) ? $params['masonry_height'] : $this->_getParam('masonry_height', '200px');
    $params = array('pagging' => $loadOptionData, 'limit_data' => $limit_data, 'list_title_truncation' => $list_title_truncation, 'grid_title_truncation' => $grid_title_truncation,'masonry_title_truncation' => $masonry_title_truncation,'pinboard_title_truncation' => $pinboard_title_truncation ,'list_description_truncation' => $list_description_truncation,'grid_description_truncation' => $grid_description_truncation,'pinboard_description_truncation' => $pinboard_description_truncation,'show_criterias' => $show_criterias,'view_type' => $view_type, 'height' => $defaultHeight,'photo_height' => $defaultPhotoHeight,'photo_width' => $defaultPhotoWidth,'info_height' => $defaultInfoHeight,'pinboard_width' => $defaultPinboardWidth,'masonry_height' => $defaultMasonryHeight,'category_id' => $value['category_id'],'defaultOpenTab' => $value['defaultOpenTab'], 'subcat_id' => $value['subcat_id'], 'subsubcat_id' => $value['subsubcat_id'],'width'=>$defaultWidth,'criteria'=>$value['order'],'country'=>$value['country'],'show_limited_data'=>$show_limited_data,'advgrid_title_truncation'=>$advgrid_title_truncation,'advgrid_height'=>$advgrid_height,'advgrid_width'=>$advgrid_width,'show_item_count'=>$show_item_count, 'socialshare_enable_plusicon' => $socialshare_enable_plusicon, 'socialshare_icon_limit' => $socialshare_icon_limit);
    $this->view->widgetName = 'country-tabbed-events';
    $this->view->page = $page;
    $this->view->params = array_merge($params, $value);
    if ($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    } 
		unset($value['info']);
		unset($value['country']);
		$value['country'] =$this->view->defaultOpenTab = $defaultOpenTab;
		unset($value['defaultOpenTab']);
    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')
            ->getEventPaginator(array_merge($value,array('search'=>1)));
    $paginator->setItemCountPerPage($limit_data);
    $paginator->setCurrentPageNumber($page);
  }
}