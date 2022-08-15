<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Bootstrap.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Bootstrap extends Engine_Application_Bootstrap_Abstract {
	
	public function __construct($application) {
		parent::__construct($application);
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new Ememsub_Plugin_Core);
	}

  protected function _initFrontController() {
    include APPLICATION_PATH . '/application/modules/Ememsub/controllers/Checklicense.php';
  }
}