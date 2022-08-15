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
class Sesevent_Widget_tagEventsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->tagCloudData = Engine_Api::_()->sesevent()->tagCloudItemCore('fetchAll', array('type' => 'sesevent_event'));
    if (count($this->view->tagCloudData) <= 0)
      return $this->setNoRender();
  }

}
