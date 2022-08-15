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
class Sesevent_Widget_BreadcrumbController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $coreApi = Engine_Api::_()->core();
    if (!$coreApi->hasSubject('sesevent_event') && !$coreApi->hasSubject('sesevent_album')  && !$coreApi->hasSubject('sesevent_photo') && !$coreApi->hasSubject('sesevent_list') && !$coreApi->hasSubject('sesevent_host'))
      return $this->setNoRender();

    $this->view->subject = $coreApi->getSubject();
    
  }

}
