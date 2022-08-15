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
class Sesevent_Widget_LocationDetectController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1))
      $this->setNoRender();
    //get cookie data for auto location
		$this->view->cookiedata = $cookiedata = Engine_Api::_()->sesbasic()->getUserLocationBasedCookieData();
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)) {
				
		} else {
			$this->setNoRender();
    }
  }
}
