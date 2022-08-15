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
class Sesevent_Widget_EventGuestInformationController extends Engine_Content_Widget_Abstract {
	public function indexAction() {
		// Get subject and check auth
		$subject = Engine_Api::_()->core()->getSubject('sesevent_event');
		if (!$subject) {
		return $this->setNoRender();
		}

		$this->view->guestCount = $this->_getParam('guestCount','1');
		$this->view->height = $this->_getParam('height','45');
		$this->view->width = $this->_getParam('width','40');
		$this->view->subject = $subject;

		$viewer = Engine_Api::_()->user()->getViewer();
		
		$isLoggedIn = $viewer->getIdentity() === 0 ? false: true;
		$this->view->isLoggedIn = $isLoggedIn;
		
		$this->view->isMember = $subject->membership()->isMember($viewer);

		$attending = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id'=>$subject->getIdentity(),'type'=>'attending'));
		$attending->setItemCountPerPage($this->_getParam('guestCount','1'));
		$this->view->attending = $attending;
		
		$this->view->notattending = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id'=>$subject->getIdentity(),'type'=>'notattending'));
		$this->view->maybeattending = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id'=>$subject->getIdentity(),'type'=>'maybeattending'));
		$onwaitinglist = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id'=>$subject->getIdentity(),'type'=>'onwaitinglist'));
		$onwaitinglist->setItemCountPerPage($this->_getParam('guestCount','1'));
		$this->view->onwaitinglist = $onwaitinglist;
	
	}
}