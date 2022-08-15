<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeelingactivity_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  public function __construct($application) {
    parent::__construct($application);
    define('SESFEELINGACTIVITYENABLED', 1);
  }
	
  protected function _initFrontController() {
    include APPLICATION_PATH . '/application/modules/Sesfeelingactivity/controllers/Checklicense.php';
  }
}