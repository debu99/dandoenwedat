<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Bootstrap extends Engine_Application_Bootstrap_Abstract {

    public function _bootstrap($resource = null) {

        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Sespwa_Plugin_Core);
    }

    public function __construct($application) {
        parent::__construct($application);
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $view->headLink()->appendStylesheet("application/modules/Sespwa/externals/styles/styles.css");
    }

    protected function _initFrontController() {
        include APPLICATION_PATH . '/application/modules/Sespwa/controllers/Checklicense.php';
    }
}
