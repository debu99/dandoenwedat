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
class Sesevent_Widget_ProfileInfoController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    // Get subject and check auth
    $subject = $event = Engine_Api::_()->core()->getSubject('sesevent_event');
    if (!$subject) {
      return $this->setNoRender();
    }
		$this->view->guestInfo = true;
		 if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			$this->view->ticket = $ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $subject->getIdentity()));
			if(count($ticket)) {
				$this->view->guestInfo = false;
			}
	 }
	 $this->view->eventTags = $subject->tags()->getTagMaps();
		$this->view->criteria = $this->_getParam('criteria',array('location','date','like','comment','favourite','view','rating','guestinfo'));
    $this->view->subject = $subject;
		if(in_array('guestinfo',$this->view->criteria)){
			$membershipTable = Engine_Api::_()->getDbtable('membership', 'sesevent');
			$membershipTableName = $membershipTable->info('name');
	
			$selectAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS attending');
			$this->view->attending = $selectAttenting->where('active =?',1)->where('rsvp =?',2)->where('resource_id =?',$event->getIdentity())->query()->fetchColumn();
			
			$selectNotAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS notattending');
			$this->view->notattending = $selectNotAttenting->where('active =?',1)->where('resource_id =?',$event->getIdentity())->where('rsvp =?',0)->query()->fetchColumn();
			
			$selectMaybeAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS maybeattending');
			$this->view->maybeattending = $selectMaybeAttenting->where('active =?',1)->where('resource_id =?',$event->getIdentity())->where('rsvp =?',1)->query()->fetchColumn();
			
			$selectNewAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS newattending');
			$this->view->newattending = $selectNewAttenting->where('active =?',0)->where('resource_id =?',$event->getIdentity())->query()->fetchColumn();	
		}
  }
}
