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
class Sesevent_Widget_ProfileMembersController extends Engine_Content_Widget_Abstract {
  protected $_childCount;
  public function indexAction() {
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject('sesevent_event')) {
      return $this->setNoRender();
    }
    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
    if (!$subject->authorization()->isAllowed($viewer, 'view')) {
      return $this->setNoRender();
    }
		$this->view->viewtype = $this->_getParam('viewtype','loadmore');
		// Prepare data
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
		
    $this->view->is_ajax =$is_ajax = $this->_getParam('is_ajax',false);
		if($is_ajax){
			 $this->getElement()->removeDecorator('Container');
			  $this->getElement()->removeDecorator('Title');
		}
		if(!$is_ajax){
			$this->view->canEdit = $subject->authorization()->isAllowed($viewer, 'edit')	;
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
		
    if(!$is_ajax && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			$eventHasTicket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $subject->getIdentity()));
			if(count($eventHasTicket))
				return $this->setNoRender();		  
	  }
    // Get params
    $this->view->page = $page = $this->_getParam('page', 1);		
		$params['event_id'] = $event->event_id;
		$params['type'] = $this->_getParam('type','');
		$params['searchVal'] = $this->_getParam('searchVal',null);
		$this->view->paginator = $paginator = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership($params);
    
		// Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('limit_data', 10));
    $paginator->setCurrentPageNumber( $page);
				
    // Add count to title if configured
    if (!$is_ajax && $this->_getParam('titleCount', false) && $paginator->getTotalItemCount() > 0) {
      $this->_childCount = $paginator->getTotalItemCount();
    }
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}
