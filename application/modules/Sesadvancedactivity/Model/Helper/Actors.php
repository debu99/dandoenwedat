<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Actors.php 2017-01-07 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_Helper_Actors extends Sesadvancedactivity_Model_Helper_Abstract
{
  public function direct($subject, $object = false, $separator = ' &rarr; ')
  {
    
    $pageSubject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;

    $subject = $this->_getItem($subject, false);
    $object = $this->_getItem($object, false);
    
    // Check to make sure we have an item
    if( !($subject instanceof Core_Model_Item_Abstract) || !($object instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }
    
    $attribs = array('class' => 'feed_item_username ses_tooltip');
    if($subject->getGuid() == $object->getGuid()){
      return $subject->toString(array_merge($attribs,array('data-src'=>$subject->getGuid())));
    }else if( null === $pageSubject ) {
      return $subject->toString(array_merge($attribs,array('data-src'=>$subject->getGuid()))) . $separator . $object->toString(array_merge($attribs,array('data-src'=>$object->getGuid())));
    } else if( $pageSubject->isSelf($subject) ) {
      return $subject->toString(array_merge($attribs,array('data-src'=>$subject->getGuid()))) . $separator . $object->toString(array_merge($attribs,array('data-src'=>$object->getGuid())));
    } else if( $pageSubject->isSelf($object) ) {
      return $subject->toString(array_merge($attribs,array('data-src'=>$subject->getGuid())));
    } else {
      return $subject->toString(array_merge($attribs,array('data-src'=>$subject->getGuid()))) . $separator . $object->toString(array_merge($attribs,array('data-src'=>$object->getGuid())));
    }
  }
}
