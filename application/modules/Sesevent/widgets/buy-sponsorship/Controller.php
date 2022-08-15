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
class Sesevent_Widget_BuySponsorshipController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
		//check sevent_event subject,if not then no need to render widget.
		$viewer = Engine_Api::_()->user()->getViewer();
	 if(Engine_Api::_()->core()->hasSubject('sesevent_event') && $viewer->getIdentity())
	 	 $this->view->event = $event = Engine_Api::_()->core()->getSubject();
	 else
	 	 return $this->setNoRender();	 
	 $currentTime = time();
	 	if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->setNoRender();	 
	  }
		//don't render widget if event ends
		if(strtotime($event->endtime) < $currentTime)
			return $this->setNoRender();
		$params['event_id'] = $event->event_id;
		$params['user_id'] = $viewer->getIdentity();
		$this->view->sponsorship = $sponsorship = Engine_Api::_()->getDbtable('sponsorships', 'sesevent')->getSponsorship($params);
		//check validation event ticket 
		if(!count($sponsorship))
			return $this->setNoRender();
	}
}
