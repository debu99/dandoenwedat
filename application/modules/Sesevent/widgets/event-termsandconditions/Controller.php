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
class Sesevent_Widget_EventTermsandconditionsController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject()) {
      return $this->setNoRender();
    }
		
		$subject = $this->view->subject = Engine_Api::_()->core()->getSubject();
    $this->view->edit = $edit = $subject->authorization()->isAllowed($viewer, 'edit');
    if ((!$subject->is_custom_term_condition || is_null($subject->is_custom_term_condition))) {
      return $this->setNoRender();
    }
  }
}