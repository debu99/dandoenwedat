<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Menus.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Plugin_Menus {
  public function enableonthisday() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity_enableonthisday', 1) || !$viewer->getIdentity()){
			return false;	
		}    
    return true;
  }
}