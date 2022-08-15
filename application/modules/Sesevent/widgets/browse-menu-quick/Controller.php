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
class Sesevent_Widget_BrowseMenuQuickController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    // Get quick navigation
		 if(!Engine_Api::_()->user()->getViewer()->getIdentity())
			return $this->setNoRender();
		$this->view->popup = $this->_getParam('popup',1);
    $this->view->quickNavigation = $quickNavigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesevent_quick');
  }

}
