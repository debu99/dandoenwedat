<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Bootstrap extends Engine_Application_Bootstrap_Abstract {

  public function __construct($application) {
    parent::__construct($application);
    $baseURL = Zend_Registry::get('StaticBaseUrl');
    $this->initViewHelperPath();
    $headScript = new Zend_View_Helper_HeadScript();
    if (strpos(str_replace('/', '', $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']), str_replace('/', '', $_SERVER['SERVER_NAME'] . 'admin')) === FALSE) {
      $headScript = new Zend_View_Helper_HeadScript();
      $headScript->appendFile($baseURL . 'application/modules/Sescredit/externals/scripts/core.js');
    }
  }

}
