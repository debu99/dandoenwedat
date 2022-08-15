<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Widget_BrowseMenuController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    // Get navigation
    $this->view->navigation = $navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('sescredit_main', array());
    if (count($this->view->navigation) == 1) {
      $this->view->navigation = null;
    }
  }

}
