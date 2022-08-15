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
class Sesevent_Widget_OfTheDayListController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
  
    $this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);

    $this->view->height = $this->_getParam('height', '200');
    $this->view->titletruncation = $this->_getParam('titletruncation', '16');
		$setting = Engine_Api::_()->getApi('settings', 'core');
    $this->view->information = $this->_getParam('information', array('viewCount', 'title', 'postedby', 'share','eventcount', 'favouriteButton', 'favouriteCount','featuredLabel','sponsoredLabel','likeButton', 'socialSharing','likeCount','viewCount','showEventsList'));
    $paginator = Engine_Api::_()->getDbTable('lists', 'sesevent')->getOfTheDayResults();
    $this->view->paginator = $paginator;
		$paginator->setItemCountPerPage(1);
    $paginator->setCurrentPageNumber(1);
    if (!($paginator->getTotalItemCount())) {
      return $this->setNoRender();
    }
  }
}