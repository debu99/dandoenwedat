<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvpmnt_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  public function __construct($application) {
    parent::__construct($application);
    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new Sesadvpmnt_Plugin_Core);

  }
  protected function _initFrontController() {
    include APPLICATION_PATH . '/application/modules/Sesadvpmnt/controllers/Checklicense.php';
  }
}
