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
class Sesevent_Widget_RecentlyViewedItemController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {  
		
		$userId = Engine_Api::_()->user()->getViewer()->getIdentity();
		
		$params['limit'] = $this->_getParam('limit_data',10);
		$this->view->type = $type = $params['criteria'] = $this->_getParam('criteria','by_me');
		if(($type == 'by_me' || $type == 'by_myfriend') && $userId == 0){
				return $this->setNoRender();
		}
		
		$this->view->socialshare_enable_plusicon = $socialshare_enable_plusicon =isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
		$this->view->socialshare_icon_limit = $socialshare_icon_limit =isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);
		
		$this->view->height = $defaultHeight =isset($params['height']) ? $params['height'] : $this->_getParam('height', '180');
		$this->view->width = $defaultWidth= isset($params['width']) ? $params['width'] :$this->_getParam('width', '180');
		$this->view->title_truncation_grid = $title_truncation_grid = isset($params['title_truncation_grid']) ? $params['title_truncation_grid'] :$this->_getParam('grid_title_truncation', '45');
		$this->view->title_truncation_list = $title_truncation_list = isset($params['title_truncation_list']) ? $params['title_truncation_list'] :$this->_getParam('list_title_truncation', '45');
		
		$show_criterias = isset($value['show_criterias']) ? $value['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'by', 'title', 'socialSharing', 'view', 'featuredLabel', 'sponsoredLabel', 'verifiedLabel', 'likeButton','buyActive'));
    foreach ($show_criterias as $show_criteria)
      $this->view->{$show_criteria . 'Active'} = $show_criteria;
		$this->view->order = $order = isset($params['order']) ? $params['order'] : $this->_getParam('order', '');  
		$this->view->view_type = $this->_getParam('view_type', 'list');
		$this->view->gridInsideOutside = $this->_getParam('gridInsideOutside', 'in');
		$this->view->mouseOver = $this->_getParam('mouseOver', 'over');
		$params['type'] = 'sesevent_event';
    $params['order'] = $order;
		$this->view->results = $paginator = Engine_Api::_()->getDbtable('recentlyviewitems', 'sesevent')->getitem($params);
		$this->view->getitem = true;
		if(count($paginator) == 0)
				return $this->setNoRender();
	}
}
