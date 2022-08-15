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
class Seseventreview_Widget_ProfileReviewController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1))
		return $this->setNoRender();
    $this->view->stats = isset($params['stats']) ? $params['stats'] : $this->_getParam('stats', array('featured', 'sponsored', 'new', 'likeCount', 'commentCount', 'viewCount', 'title', 'postedBy', 'pros', 'cons', 'description', 'creationDate', 'recommended','parameter','rating','customfields')); 
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject('eventreview'))
      return $this->setNoRender();
    //Get subject and check auth
    $this->view->review = $review = Engine_Api::_()->core()->getSubject();
		$this->view->item = $event = Engine_Api::_()->getItem('sesevent_event', $review->content_id);
		$currentTime = time();
		//don't render widget if event ends
		if(strtotime($event->starttime) > ($currentTime))
			return $this->setNoRender();
  }
}
