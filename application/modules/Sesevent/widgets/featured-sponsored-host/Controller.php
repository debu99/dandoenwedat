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
class Sesevent_Widget_FeaturedSponsoredHostController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    $value['limit'] = $this->_getParam('limit_data', 5);
    $value['criteria'] = $this->_getParam('criteria', 5);
    $value['popularity'] = $this->_getParam('info', 'recently_created');
    
    $this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);
    
		$this->view->height = $this->_getParam('height', '180');
    $this->view->width = $this->_getParam('width', '180');
    $this->view->title_truncation_list = $this->_getParam('title_truncation_list', '45');
    $this->view->title_truncation_grid = $this->_getParam('title_truncation_grid', '45');
    $this->view->view_type = $this->_getParam('viewType', 'list');
    $this->view->contentInsideOutside = $this->_getParam('contentInsideOutside', 'in');
    $this->view->mouseOver = $this->_getParam('mouseOver', '1');
    
    $show_criterias = isset($value['show_criterias']) ? $value['show_criterias'] : $this->_getParam('show_criteria', array('like', 'title', 'socialSharing', 'view', 'featuredLabel', 'sponsoredLabel', 'likeButton'));
    foreach ($show_criterias as $show_criteria)
      $this->view->{$show_criteria . 'Active'} = $show_criteria;

    $this->view->paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getHostsPaginator($value);
    if ($this->view->paginator->getTotalItemCount() <= 0)
      return $this->setNoRender();
  }

}
