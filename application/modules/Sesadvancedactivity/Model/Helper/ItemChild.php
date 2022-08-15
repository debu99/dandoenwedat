<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ItemChild.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_Helper_ItemChild extends Sesadvancedactivity_Model_Helper_Item
{
  public function direct($item, $type = null, $child_id = null)
  {
    $item = $this->_getItem($item, false);   
    
    // Check to make sure we have an item
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }
    
    $child_type = $item->getType().'_'.$type;
    
    try{
      $item = Engine_Api::_()->getItem($child_type, $child_id);
    }
    catch (Exception $e) {
      // With no alarms and no surprises
      // No alarms and no surprises
      // No alarms and no surprises
      // Silent, silent
    }
    
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }    
    
    return parent::direct($item, $type);
  }
}