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
class Sesevent_Widget_BrowseMenuController extends Engine_Content_Widget_Abstract {
  public function indexAction(){
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('sesevent_main', array());
    
    $this->view->max = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.taboptions', 6);
    if (count($this->view->navigation) == 1) {
      $this->view->navigation = null;
    }
  }
}