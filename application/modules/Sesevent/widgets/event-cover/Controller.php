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
class Sesevent_Widget_EventCoverController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$params = $request->getParams();
		$this->view->actionA = $params['action'];
		// Don't render this if not authorized
		$viewer = Engine_Api::_()->user()->getViewer();
		if (!Engine_Api::_()->core()->hasSubject('sesevent_event')) {
		return $this->setNoRender();
		}
		if(in_array("minimalisticCover",  $this->_getParam('showCriterias'))) 
			$this->view->show_criterias = array('minimalisticCover');
		else 
			$this->view->show_criterias = $this->_getParam('showCriterias',array('minimalisticCover','mainPhoto','hostedby','startEndDate','location','getDirection','addtocalender','bookNow','title','createdby','createdon'));
		$this->view->show_calander = $this->_getParam('showCalander',array('google','yahoo','msn','outlook','ical'));
		$this->view->tab = $this->_getParam('optionInsideOutside',1);
		$this->view->height = $this->_getParam('height','500');
		$this->view->fullwidth = $this->_getParam('fullwidth','1');
		if($this->view->fullwidth){
			$this->view->padding = $this->_getParam('padding','');
		}else
			$this->view->padding = '';

    	$this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    	$this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);
        
    
		$this->view->photo = $this->_getParam('photo','mPhoto');
    	$subject = $this->view->subject = Engine_Api::_()->core()->getSubject('sesevent_event');
        $this->view->isAttending = $subject->membership()->getRow($viewer)->rsvp === 2;
		$this->view->can_edit = $editOverview = $subject->authorization()->isAllowed($viewer, 'edit');		
		$user = Engine_Api::_()->getItem('sesevent_host', $subject->host);
		$params['href'] = $user->getHref();
		$params['image'] = '<img class="thumb_icon item_photo_user" alt="" src="'.$user->getPhotoUrl().'">';
		$params['title'] = $user->getTitle();
		$params['id'] = $user->getIdentity();
		$params['description'] = $user->host_description;	
                
		$this->view->eventTags = $subject->tags()->getTagMaps();
		$this->view->host = $params;
		$currentTime = time();
		if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			$this->view->noTicketAvailable = true;
		}else{
			//don't render widget if event ends
			if(strtotime($subject->endtime) < strtotime($currentTime)){
				$this->view->noTicketAvailable = true;
			}else{
				$params['event_id'] = $subject->event_id;
				$params['checkEndDateTime'] = date('Y-m-d H:i:s');
				$this->view->ticket = $ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($params);
				//check validation event ticket 
				if(!count($ticket))
					$this->view->noTicketAvailable = true;
			}			
		}
  }
}