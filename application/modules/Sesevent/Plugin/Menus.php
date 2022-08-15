<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Menus.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Plugin_Menus {
  public function canViewMultipleCurrency(){
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmultiplecurrency'))
      return true;
    else
      return false;
  }
  public function canCreateEvents() {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer || !$viewer->getIdentity()) {
      return false;
    }
    // Must be able to create events
    if (!Engine_Api::_()->authorization()->isAllowed('sesevent_event', $viewer, 'create')) {
      return false;
    }
    return true;
  }
	public function locationEnable(){
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)){
				return false;
		}
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1))
      return false;
		return true;
	}
  public function canViewEvents($event) {
    $viewer = Engine_Api::_()->user()->getViewer();
    // Must be able to view events
    if (!Engine_Api::_()->authorization()->isAllowed('sesevent_event', $viewer, 'view')) {
      return false;
    }
    return true;
  }
	public function canViewEventsTicket($event) {	
    $viewer = Engine_Api::_()->user()->getViewer();
    // Must be able to view events
    if (!$viewer->getIdentity()) {
      return false;
    }
    return true;
  }
  public function onMenuInitialize_SeseventMainManage() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer->getIdentity()) {
      return false;
    }
    return true;
    /*
      return array(
      'label' => 'My Events',
      'route' => 'event_general',
      'params' => array(
      'action' => 'manage',
      )
      );
     *
     */
  }

  public function onMenuInitialize_SeseventMainCreate() {
    $viewer = Engine_Api::_()->user()->getViewer();

    if (!$viewer->getIdentity()) {
      return false;
    }

    if (!Engine_Api::_()->authorization()->isAllowed('sesevent_event', null, 'create')) {
      return false;
    }
    return true;
	/*return array(
	'label' => 'Create New Events',
	'route' => 'sesevent_general',
	'icon' => 'application/modules/Sesevent/externals/images/create.png',
	'params' => array(
	'action' => 'create',
	)
	);
*/    
  }
  public function onMenuInitialize_SeseventProfileDashboard() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if ($subject->getType() !== 'sesevent_event') {
      throw new Sesevent_Model_Exception('Whoops, not a event!');
    }
    if (!$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit')) {
      return false;
    }
    if (!$subject->authorization()->isAllowed($viewer, 'edit')) {
      return false;
    }
    return array(
        'label' => 'Dashboard',
				'class' => 'sesbasic_icon_edit',
        'route' => 'sesevent_dashboard',
        'params' => array(
            'controller' => 'dashboard',
            'action' => 'edit',
            'event_id' => $subject->custom_url
        )
    );
  }
  public function onMenuInitialize_SeseventProfileStyle() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if ($subject->getType() !== 'sesevent_event') {
      throw new Sesevent_Model_Exception('Whoops, not a event!');
    }
    if (!$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit')) {
      return false;
    }
    if (!$subject->authorization()->isAllowed($viewer, 'style')) {
      return false;
    }
    return array(
        'label' => 'Edit Event Style',
        'class' => 'smoothbox',
        'route' => 'sesevent_specific',
        'params' => array(
            'action' => 'style',
            'event_id' => $subject->getIdentity(),
            'format' => 'smoothbox',
        )
    );
  }
  public function onMenuInitialize_SeseventProfileMember() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if ($subject->getType() !== 'sesevent_event') {
      throw new Sesevent_Model_Exception('Whoops, not a event!');
    }
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			$eventHasTicket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $subject->getIdentity()));
			if(count($eventHasTicket))
				return false;
	  }else{
			//check event expire	
			if(strtotime($subject->endtime) <= time())
				return false;		
		}
    if (!$viewer->getIdentity()) {
      return false;
    }
    $row = $subject->membership()->getRow($viewer);
    // Not yet associated at all
    if (null === $row) {
      if ($subject->membership()->isResourceApprovalRequired()) {
        return array(
            'label' => 'Request Invite',
            'class' => 'smoothbox sesevent_profile_invite_request',
            'route' => 'sesevent_extended',
            'params' => array(
                'controller' => 'member',
                'action' => 'request',
                'event_id' => $subject->getIdentity(),
            ),
        );
      } else {
        return;
        // return array(
        //     'label' => 'Join Event',
        //     'class' => 'smoothbox sesevent_profile_join',
        //     'route' => 'sesevent_extended',
        //     'params' => array(
        //         'controller' => 'member',
        //         'action' => 'join',
        //         'event_id' => $subject->getIdentity()
        //     ),
        // );
      }
    }
    // Full member
    // @todo consider owner
    else if ($row->active) {
      if (!$subject->isOwner($viewer)) {
        return array(
            'label' => 'Leave Event',
            'class' => 'smoothbox sesevent_profile_leave',
            'route' => 'sesevent_extended',
            'params' => array(
                'controller' => 'member',
                'action' => 'leave',
                'event_id' => $subject->getIdentity()
            ),
        );
      } else {
        return false;
        /*
          return array(
          'label' => 'Delete Event',
          'icon' => 'application/modules/Event/externals/images/delete.png',
          'class' => 'smoothbox',
          'route' => 'event_specific',
          'params' => array(
          'action' => 'delete',
          'event_id' => $subject->getIdentity()
          ),
          );
         */
      }
    } else if (!$row->resource_approved && $row->user_approved) {
      return array(
          'label' => 'Cancel Invite Request',
          'class' => 'smoothbox sesevent_cancel_invite_request',
          'route' => 'sesevent_extended',
          'params' => array(
              'controller' => 'member',
              'action' => 'cancel',
              'event_id' => $subject->getIdentity()
          ),
      );
    } else if (!$row->user_approved && $row->resource_approved) {
      return array(
          array(
              'label' => 'Accept Event Invite',
              'class' => 'smoothbox sesevent_accept_invite_request',
              'route' => 'sesevent_extended',
              'params' => array(
                  'controller' => 'member',
                  'action' => 'accept',
                  'event_id' => $subject->getIdentity()
              ),
          ), array(
              'label' => 'Ignore Event Invite',
              'class' => 'smoothbox sesevent_ignore_invite_request',
              'route' => 'sesevent_extended',
              'params' => array(
                  'controller' => 'member',
                  'action' => 'reject',
                  'event_id' => $subject->getIdentity()
              ),
          )
      );
    } else {
      throw new Sesevent_Model_Exception('An error has occurred.');
    }
    return false;
  }
  public function onMenuInitialize_SeseventProfileReport() {
    return false;
  }
  public function onMenuInitialize_SeseventProfileInvite() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if ($subject->getType() !== 'sesevent_event') {
      throw new Sesevent_Model_Exception('This event does not exist.');
    }
    if (!$subject->authorization()->isAllowed($viewer, 'invite')) {
      return false;
    }
    return array(
        'label' => 'Invite Guests',
        'class' => 'smoothbox',
        'route' => 'sesevent_extended',
        'params' => array(
            'module' => 'sesevent',
            'controller' => 'member',
            'action' => 'invite',
            'event_id' => $subject->getIdentity(),
            'format' => 'smoothbox',
        ),
    );
  }
  public function onMenuInitialize_SeseventProfileShare() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if ($subject->getType() !== 'sesevent_event') {
      throw new Sesevent_Model_Exception('This event does not exist.');
    }
    if (!$viewer->getIdentity()) {
      return false;
    }
    return array(
        'label' => 'Share This Event',
        'class' => 'smoothbox',
        'route' => 'default',
        'params' => array(
            'module' => 'activity',
            'controller' => 'index',
            'action' => 'share',
            'type' => $subject->getType(),
            'id' => $subject->getIdentity(),
            'format' => 'smoothbox',
        ),
    );
  }
  public function onMenuInitialize_SeseventProfileMessage() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if ($subject->getType() !== 'sesevent_event') {
      throw new Sesevent_Model_Exception('This event does not exist.');
    }
    if (!$viewer->getIdentity() || !$subject->isOwner($viewer)) {
      return false;
    }
    return array(
        'label' => 'Message Members',
        'route' => 'messages_general',
        'params' => array(
            'action' => 'compose',
            'to' => $subject->getIdentity(),
            'multi' => 'sesevent_event'
        )
    );
  }
  public function onMenuInitialize_SeseventProfileDelete() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if ($subject->getType() !== 'sesevent_event') {
      throw new Sesevent_Model_Exception('This event does not exist.');
    } else if (!$subject->authorization()->isAllowed($viewer, 'delete')) {
      return false;
    }
    return array(
        'label' => 'Delete Event',
        'class' => 'smoothbox',
        'route' => 'sesevent_specific',
        'params' => array(
            'action' => 'delete',
            'event_id' => $subject->getIdentity(),
        ),
    );
  }

    public function onMenuInitialize_SeseventProfileCopy()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        if ($subject->getType() !== 'sesevent_event') {
            throw new Sesevent_Model_Exception('This event does not exist.');
        }
        if (!$viewer->getIdentity()) {
            return false;
        }
        if ($viewer->getIdentity() != $subject->user_id){
            return false;
        }
        return array(
            'label' => 'Copy This Event',
            'route' => 'default',
            'params' => array(
                'module' => 'sesevent',
                'action' => 'create',
                'event_id' => $subject->getIdentity(),
            ),
        );
    }
}
