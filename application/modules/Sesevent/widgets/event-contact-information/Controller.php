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
class Sesevent_Widget_EventContactInformationController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
    if (!$subject) {
      return $this->setNoRender();
    }
		$this->view->info = $this->_getParam('show_criteria',array('name','email','phone','facebook','linkedin','twitter','website'));
		if(!$subject->event_contact_name && !$subject->event_contact_email && !$subject->event_contact_phone && !$subject->event_contact_website && !$subject->event_contact_facebook && !$subject->event_contact_twitter && !$subject->event_contact_linkedin)
			 return $this->setNoRender();
    $this->view->subject = $subject;
  }
}
