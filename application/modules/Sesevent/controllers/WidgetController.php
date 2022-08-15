<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: WidgetController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_WidgetController extends Core_Controller_Action_Standard {

  public function profileInfoAction() {
    // Don't render this if not authorized
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid())
      return $this->_helper->viewRenderer->setNoRender(true);
  }

  public function profileJoinAction(){
    $test = "la";
  }
  public function profileLeaveAction(){
    $test = "la";
  }

  public function profileRsvpAction() {

    $this->view->form = new Sesevent_Form_Rsvp();
    $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$event->membership()->isMember($viewer, true)) {
      return;
    }
    $row = $event->membership()->getRow($viewer);
    $this->view->viewer_id = $viewer->getIdentity();
    if ($row) {
      $this->view->rsvp = $row->rsvp;
    } else {
      return $this->_helper->viewRenderer->setNoRender(true);
    }
    if ($this->getRequest()->isPost()) {
      $option_id = $this->getRequest()->getParam('option_id');
      $row->rsvp = $option_id;
      $row->save();
      
      //Send mail to event owner when some change rsvp
      if($row->rsvp == '0') {
	      $rsvp_change = 'Not Attending';
      } elseif($row->rsvp == '1') {
	      $rsvp_change = 'Maybe Attending';
      } elseif($row->rsvp == '2') {
	      $rsvp_change = 'Attending';
      } 
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($event->getOwner(), 'sesevent_rsvp_change', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'viewer_name' => $viewer->getTitle(), 'host' => $_SERVER['HTTP_HOST'], 'rsvp_changetext' => $rsvp_change));
    }
  }

  public function requestSeseventAction() {
    $this->view->notification = $notification = $this->_getParam('notification');
  }

}
