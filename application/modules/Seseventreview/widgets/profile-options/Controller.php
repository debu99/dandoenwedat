<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Widget_ProfileOptionsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1))
		return $this->setNoRender();
    $this->view->viewType = $this->_getParam('viewType', 'vertical');
    $coreMenuApi = Engine_Api::_()->getApi('menus', 'core');

    if (!Engine_Api::_()->core()->hasSubject('eventreview'))
      return $this->setNoRender();

    $review = Engine_Api::_()->core()->getSubject('eventreview');
    $this->view->content_item = $event = Engine_Api::_()->getItem($review->content_type, $review->content_id);
		$currentTime = time();
		//don't render widget if event ends
		if(strtotime($event->starttime) > ($currentTime))
			return $this->setNoRender();
    $this->view->navigation = $coreMenuApi->getNavigation('sesevent_reviewprofile');
  }

}
