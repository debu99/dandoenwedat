<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Buysell.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_Buysell extends Core_Model_Item_Abstract
{
   protected $_searchTriggers = false;
  public function getMediaType(){
    return 'post';
  }
  public function getHref(){
    $action = Engine_Api::_()->getItem('activity_action',$this->action_id);
    if(!$action)
      return 'javascript:;';
    return  $action->getHref();
  }
}
