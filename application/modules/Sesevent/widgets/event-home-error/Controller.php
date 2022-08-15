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
class Sesevent_Widget_EventHomeErrorController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();

    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('sesevent_event', $viewer, 'create');

    $this->view->paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->countEvents(array());
    if (($this->view->paginator->getTotalItemCount()) > 0)
      return $this->setNoRender();
  }

}
