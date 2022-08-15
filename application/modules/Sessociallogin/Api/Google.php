<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Google.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sessociallogin_Api_Google extends Core_Api_Abstract {
 protected $_google;
 protected $_photos;
 function getData($mediaData,$mediatype){
   $table = Engine_Api::_()->getDbtable('google', 'sessociallogin');
   $this->_google = $table->getApi();
   $result = array();
   return $result;
 }
}
