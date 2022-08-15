<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: TicketController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_TicketController extends Core_Controller_Action_Standard {
  public function init() {
    // @todo this may not work with some of the content stuff in here, double-check
    $subject = null;
    if (!Engine_Api::_()->core()->hasSubject() &&
            ($id = $this->_getParam('event_id'))) {
		$event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
    if ($event_id) {
      $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
      if ($event)
        Engine_Api::_()->core()->setSubject($event);
      else 
        return $this->_forward('requireauth', 'error', 'core');
    }else
      return $this->_forward('requireauth', 'error', 'core'); 					
		}
  }
  public function buyAction() {
		if (!$this->_helper->requireUser->isValid())
      return;
		$viewer = Engine_Api::_()->user()->getViewer();
		if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
		//remove incomplete order from the logged in user
		Engine_Api::_()->sesevent()->removeIncompleteTicketOrder($viewer->getIdentity());
    // Render
    $this->_helper->content->setEnabled();
  }
	public function myTicketsAction(){
		if (!$this->_helper->requireUser->isValid())
      return;
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			// Render
	    $this->_helper->content->setEnabled();
    }
	}
}
