<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Notification.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesmembershipswitch_Plugin_Task_Notification extends Core_Plugin_Task_Abstract
{
  public function execute()
  {
    Engine_Api::_()->sesmembershipswitch()->sendNotification();  
  }
}


