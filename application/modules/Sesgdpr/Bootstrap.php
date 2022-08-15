<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Bootstrap extends Engine_Application_Bootstrap_Abstract {

  protected function _initFrontController() {
    $this->initActionHelperPath();
    include APPLICATION_PATH . '/application/modules/Sesgdpr/controllers/Checklicense.php';
    Zend_Controller_Action_HelperBroker::addHelper(new Sesgdpr_Controller_Action_Helper_Gdpr());
  }
  
}