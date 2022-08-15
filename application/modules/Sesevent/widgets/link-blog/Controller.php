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
class Sesevent_Widget_LinkBlogController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (!Engine_Api::_()->core()->hasSubject()) {
      return $this->setNoRender();
    }
    
    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
   
    if(empty($viewer_id) || ($viewer_id != $subject->user_id)) {
      return $this->setNoRender();
    }
    $this->view->event = $subject;
  }

}
