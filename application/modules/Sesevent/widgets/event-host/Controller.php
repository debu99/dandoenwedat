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
class Sesevent_Widget_EventHostController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id =  $viewer->getIdentity();
    if (!Engine_Api::_()->core()->hasSubject()) {
      return $this->setNoRender();
    }

    // Get subject and check auth
	  $this->view->subject =  $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
	  $host = Engine_Api::_()->getItem('sesevent_host', $subject->host);
    $this->view->type = 'sesevent_host';
    $this->view->id = $subject->host;
    $this->view->isFollow = Engine_Api::_()->getDbTable('follows', 'sesevent')->isFollow(array('resource_type' => $this->view->type, 'resource_id' => $this->view->id));
    $this->view->allowFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.followeventowner', 1);
    
    $select = Engine_Api::_()->getDbtable('follows', 'sesevent')->getFollowSelect($host);
    $results = $select->query()->fetchAll();
    $this->view->followCount = count($results);

    if (!$subject->authorization()->isAllowed($viewer, 'view')) {
      return $this->setNoRender();
    }
// 		if($subject->host_type == 'site'){
// 			$user = Engine_Api::_()->getItem('user', $subject->host);
// 			$params['href'] = $user->getHref();
// 			$params['image'] = $this->view->itemPhoto($user, 'thumb.icon');
// 			$params['title'] = $user->getTitle();
// 			$params['id'] = $user->getIdentity();
// 		}else{
			$user = Engine_Api::_()->getItem('sesevent_host', $subject->host);
			$params['href'] = $user->getHref();
			$params['image'] = '<img class="thumb_icon item_photo_user" alt="" src="'.$user->getPhotoUrl().'">';
			$params['title'] = $user->getTitle();
			$params['id'] = $user->getIdentity();
			$params['description'] = $user->host_description;
		//}
		//total event hosted by host
		$totalEventHosted = Engine_Api::_()->getDbtable('events', 'sesevent')->countEvents(array('host'=>$subject->host_type,'host_id'=>$subject->host,'fetchAll'=>true));
		$this->view->totalEventOfHost = count($totalEventHosted);
		$this->view->host = $params;

  }
}
