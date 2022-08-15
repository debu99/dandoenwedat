<?php
class Sesevent_Widget_ProfileEventsController extends Engine_Content_Widget_Abstract
{
  protected $_childCount;
  public function indexAction()
  {
		$this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
		if(empty($_POST['is_ajax'])){
			//Don't render this if not authorized
			$viewer = Engine_Api::_()->user()->getViewer();
			if( !Engine_Api::_()->core()->hasSubject() ) {
				return $this->setNoRender();
			}
			//Get subject and check auth
			$subject = Engine_Api::_()->core()->getSubject();
			if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
			  return $this->setNoRender();
			}
		}
		if (isset($_POST['params']))
   	 $params = json_decode($_POST['params'], true);
		
		$defaultOptionsArrayD = $this->_getParam('search_type',array('events','hosted','spoked','sponsored'));
		foreach($defaultOptionsArrayD as $val){
			if($val == 'spoked'){
				if(!(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventspeaker') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventspeaker.pluginactivated')))
					continue;
			}else if($val == 'sponsored' && !(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')))
				continue;
			$defaultOptionsArray[] = $val;
		}
		if(!$is_ajax){
			if(count($defaultOptionsArray) == 0)
					return $this->setNoRender();
			$defaultOptions = $arrayOptions = array();
			foreach($defaultOptionsArray as $key=>$defaultValue){
				if( $this->_getParam($defaultValue.'_order'))
					$order = $this->_getParam($defaultValue.'_order').'||'.$defaultValue;
				else
					$order = (435+$key).'||'.$defaultValue;
				if( $this->_getParam($defaultValue.'_label'))
						$valueLabel = $this->_getParam($defaultValue.'_label');
				else{
					if($defaultValue == 'events')
						$valueLabel ='Events';
					else if($defaultValue == 'hosted')
						$valueLabel = 'Hosted Events';
					else if($defaultValue == 'spoked')
						$valueLabel = 'Spoked In';
					else if($defaultValue == 'sponsored')
						$valueLabel = 'Events Sponsored';
				}
				$arrayOptions[$order] = $valueLabel;
			}
			ksort($arrayOptions);
			$counter = 0;
			foreach($arrayOptions as $key => $valueOption){
				$key = explode('||',$key);
			if($counter == 0)
				$this->view->defaultOpenTab = $defaultOpenTab = $key[1];
				$defaultOptions[$key[1]]=$valueOption;
				$counter++;
			}				
			$this->view->defaultOptions = $defaultOptions;
			$defaultOptions =isset($params['defaultOptions']) ? $params['defaultOptions'] : $defaultOptions;
		}
		
		if(isset($_GET['openTab']) || $is_ajax){
		 $this->view->defaultOpenTab = $defaultOpenTab = ($this->_getParam('openTab',false) ? $this->_getParam('openTab') : (isset($params['defaultOpenTab']) ? $params['defaultOpenTab'] : ''));
		}
		$type = '';		
		if(empty($_POST['is_ajax'])){
			if($subject->user_id != $viewer->getIdentity()){
				$userObject = Engine_Api::_()->getItem('user', $subject->user_id);
				$profile = 'other';
				$userId = $subject->user_id;
			}else{
				$userObject = Engine_Api::_()->getItem('user', $viewer->getIdentity());
				$profile = 'own';
				$userId = $viewer->getIdentity();
			}
		}else
			$userId = $_POST['identityObject'];
		$this->view->identityObject = $value['user_id'] = $userId ;
		
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$this->view->show_item_count = $show_item_count = isset($params['show_item_count']) ? $params['show_item_count'] :  $this->_getParam('show_item_count',0);
    $limit_data = isset($params['limit_data']) ? $params['limit_data'] : $this->_getParam('limit_data', '10');
    $this->view->list_title_truncation = $list_title_truncation = isset($params['list_title_truncation']) ? $params['list_title_truncation'] : $this->_getParam('list_title_truncation', '100');
    $this->view->grid_title_truncation= $grid_title_truncation = isset($params['grid_title_truncation']) ? $params['grid_title_truncation'] : $this->_getParam('grid_title_truncation', '100');
		$this->view->masonry_title_truncation = $masonry_title_truncation = isset($params['masonry_title_truncation']) ? $params['masonry_title_truncation'] : $this->_getParam('masonry_title_truncation', '100');
		$this->view->pinboard_title_truncation = $pinboard_title_truncation = isset($params['pinboard_title_truncation']) ? $params['pinboard_title_truncation'] : $this->_getParam('pinboard_title_truncation', '100');
    $this->view->list_description_truncation = $list_description_truncation = isset($params['list_description_truncation']) ? $params['list_description_truncation'] : $this->_getParam('list_description_truncation', '100');
		$this->view->grid_description_truncation = $grid_description_truncation = isset($params['grid_description_truncation']) ? $params['grid_description_truncation'] : $this->_getParam('grid_description_truncation', '100');
		$this->view->pinboard_description_truncation = $pinboard_description_truncation = isset($params['pinboard_description_truncation']) ? $params['pinboard_description_truncation'] : $this->_getParam('pinboard_description_truncation', '100');
		$this->view->advgrid_title_truncation= $advgrid_title_truncation = isset($params['advgrid_title_truncation']) ? $params['advgrid_title_truncation'] : $this->_getParam('advgrid_title_truncation', '100');
		$this->view->advgrid_height = $advgrid_height = isset($params['advgrid_height']) ? $params['advgrid_height'] : $this->_getParam('advgrid_height', '222');
    $this->view->advgrid_width = $advgrid_width = isset($params['advgrid_width']) ? $params['advgrid_width'] : $this->_getParam('advgrid_width', '322');
		 $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'rating', 'by', 'title', 'featuredLabel', 'sponsoredLabel', 'category', 'description','favouriteButton', 'likeButton', 'socialSharing', 'view'));
		$this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
    $this->view->loadOptionData = $loadOptionData = isset($params['pagging']) ? $params['pagging'] : $this->_getParam('pagging', 'auto_load');
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('sesevent_event', null, 'create');
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
    $params = array('pagging' => $loadOptionData, 'limit_data' => $limit_data, 'list_title_truncation' => $list_title_truncation, 'grid_title_truncation' => $grid_title_truncation,'masonry_title_truncation' => $masonry_title_truncation,'pinboard_title_truncation' => $pinboard_title_truncation ,'list_description_truncation' => $list_description_truncation,'grid_description_truncation' => $grid_description_truncation,'pinboard_description_truncation' => $pinboard_description_truncation,'show_criterias' => $show_criterias,'view_type' => $view_type, 'height' => $defaultHeight,'photo_height' => $defaultPhotoHeight,'photo_width' => $defaultPhotoWidth,'info_height' => $defaultInfoHeight,'pinboard_width' => $defaultPinboardWidth,'masonry_height' => $defaultMasonryHeight,'width'=>$defaultWidth,'defaultOpenTab'=>$defaultOpenTab,'advgrid_title_truncation'=>$advgrid_title_truncation,'advgrid_height'=>$advgrid_height,'advgrid_width'=>$advgrid_width,'show_item_count'=>$show_item_count, 'socialshare_enable_plusicon' => $socialshare_enable_plusicon, 'socialshare_icon_limit' => $socialshare_icon_limit);
    $this->view->widgetName = 'profile-events';
    $this->view->page = $page;
    $this->view->params = array_merge($params, $value);
		
		if($defaultOpenTab == 'events'){
			$paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventPaginator($value);
		}else if($defaultOpenTab == 'spoked'){
				$value['spoked_id'] = $value['user_id'];
				unset($value['user_id']);
				$paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventPaginator($value);
		}else if($defaultOpenTab == 'hosted'){
			$value['hosted_id'] = $value['user_id'];
			unset($value['user_id']);
			$paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventPaginator($value);
		}else{
			//sponsored	
			$value['sponsorship_owner_id'] = $value['user_id'];
			unset($value['user_id']);
			$paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventPaginator($value);
		}
		if(empty($_POST['is_ajax'])){
			// owner type
			if($profile == 'own'){
				$this->view->profile = 'own';	
			}else{
				$name = explode(' ',$userObject->displayname);
				if(isset($name[0]))
					$name = ucfirst($name[0]);
				else
					$name = ucfirst($name[1]);
				$this->view->profile = $name;	
			}
		}
		$this->view->itemOrigTitle = isset($defaultOptions[$defaultOpenTab]) ? $defaultOptions[$defaultOpenTab] : 'items';
    $this->view->paginator = $paginator ;
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($limit_data);
		$this->view->page = $page ;
    $paginator->setCurrentPageNumber($page);
		if($is_ajax)
			$this->getElement()->removeDecorator('Container');

    // Add count to title if configured
    if( $this->_getParam('titleCount', false) && $paginator->getTotalItemCount() > 0 ) {
      $this->_childCount = $paginator->getTotalItemCount();
    }
  }
  public function getChildCount()
  {
    return $this->_childCount;
  }
}