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
class Sesevent_Widget_ProfileRsvpController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
		if(!Engine_Api::_()->core()->hasSubject('sesevent_event'))
			 return $this->setNoRender();
    if ($viewer->getIdentity()) {		
    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
    if (!$subject->authorization()->isAllowed($viewer, 'view')) {
      return $this->setNoRender();
    }
		$this->view->attntextColor = $this->_getparam('attntextColor','#FFFFFF');
		$this->view->attnbagcolor = $this->_getparam('attnbagcolor','#51DB2E');
		$this->view->mbattntextColor = $this->_getparam('mbattntextColor','#FFFFFF');
		$this->view->mbattnbagcolor = $this->_getparam('mbattnbagcolor','#EAB752');
		$this->view->nattntextColor = $this->_getparam('nattntextColor','#FFFFFF');
		$this->view->nattnbagcolor = $this->_getparam('nattnbagcolor','#F22E48');
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			$eventHasTicket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $subject->getIdentity()));
			if(count($eventHasTicket))
				return $this->setNoRender();
		}else{
			//check event expire	
			if(strtotime($subject->endtime) <= time())
				$this->view->noRsvp =  true;	
		}
    // Must be a member
    if (!$subject->membership()->isMember($viewer, true)) {
      return $this->setNoRender();
    }
    // Build form
    $row = $subject->membership()->getRow($viewer);
    $this->view->viewer_id = $viewer->getIdentity();
    if (!$row) {
      return $this->setNoRender();
    }
    $this->view->rsvp = $row->rsvp;
		}else
			$this->view->nonlogedin = true;
  }
}