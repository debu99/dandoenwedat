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
class Sesevent_Widget_BuyTicketController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
		$viewer = Engine_Api::_()->user()->getViewer();
		$this->view->viewer = $viewer;
		$this->view->type = $this->_getParam('type','button');
		//check sevent_event subject,if not then no need to render widget.
		if(Engine_Api::_()->core()->hasSubject('sesevent_event'))
			$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		else
			return $this->setNoRender();	 
		//event end time as per timezone
		//$currentTime = Engine_Api::_()->sesevent()->getCurrenctDateTimeAccordingToTimezone($event->timezone);
		
		if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
				return $this->setNoRender();	 
		}
		
		$currentTime = time();
		//don't render widget if event ends
		if(strtotime($event->endtime) < ($currentTime))
			return $this->setNoRender();
		$params['event_id'] = $event->event_id;
		$params['checkEndDateTime'] = date('Y-m-d H:i:s');
		$this->view->ticket = $ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($params);
		//check validation event ticket 
		if(!$viewer->userIsInAgeRange($event) || !count($ticket) && $this->view->type == 'button')
			return $this->setNoRender();
		else if(!count($ticket))
			$this->view->noTicketAvailable = true;
		}
}
