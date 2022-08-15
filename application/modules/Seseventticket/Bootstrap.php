<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventticket
 * @package    Seseventticket
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php 2016-03-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventticket_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
   public function __construct($application) {
    parent::__construct($application);
    $this->initViewHelperPath();
    $headScript = new Zend_View_Helper_HeadScript();  
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
              . 'application/modules/Seseventticket/externals/scripts/core.js');
   }
  protected function _initFrontController() {
	  include APPLICATION_PATH . '/application/modules/Seseventticket/controllers/Checklicense.php';
  }
}