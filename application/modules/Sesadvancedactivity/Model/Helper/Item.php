<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Item.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Model_Helper_Item extends Sesadvancedactivity_Model_Helper_Abstract
{
  /**
   * Generates text representing an item
   * 
   * @param mixed $item The item or item guid
   * @param string $text (OPTIONAL)
   * @param string $href (OPTIONAL)
   * @return string
   */
  public function direct($item, $text = null, $href = null,$separator = ' &rarr; ')
  {
    $item = $this->_getItem($item, false);

    // Check to make sure we have an item
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }

    if( !isset($text) )
    {
      $text = $item->getTitle();
    }

    // translate text
    $translate = Zend_Registry::get('Zend_Translate');
    if( !($item instanceof User_Model_User) && $translate instanceof Zend_Translate ) {
      $text = $translate->translate($text);
      // if the value is pluralized, only use the singular
      if (is_array($text))
        $text = $text[0];
    }

    if( !isset($href) )
    {
      $href = $item->getHref();
    }
    
    return '<a '
      . 'class="feed_item_username ses_tooltip" '
      . ( $href ? 'href="'.$href.'"' : '' )
      . ($href ? 'data-src="'.$item->getGuid().'"' : '')
      . '>'
      . $text
      . '</a>';
  }
}
