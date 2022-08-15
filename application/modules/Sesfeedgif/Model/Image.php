<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Image.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedgif_Model_Image extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;
  
  /**
   * Gets a proxy object for the tags handler
   *
   * @return Engine_ProxyObject
   **/
  public function tags()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }
}