<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Bootstrap extends Engine_Application_Bootstrap_Abstract {
  public function __construct($application) { 
    parent::__construct($application); 
    $baseURL = Zend_Registry::get('StaticBaseUrl');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $this->initViewHelperPath();
  }
  protected function _initRouter() {
  
    $router = Zend_Controller_Front::getInstance()->getRouter();
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) {
      $integrateothermodulesTable = Engine_Api::_()->getDbTable('integrateothermodules', 'sesevent');
      $select = $integrateothermodulesTable->select();
      $results = $integrateothermodulesTable->fetchAll($select);
      if(count($results) > 0) {
        foreach ($results as $result) {
          $router->addRoute('sesevent_browseevent_' . $result->getIdentity(), new Zend_Controller_Router_Route($result->content_url . '/browse-events', array('module' => 'sesevent', 'controller' => 'index', 'action' => 'browse-events', 'resource_type' => $result->content_type ,'integrateothermodule_id' => $result->integrateothermodule_id)));
        }
        return $router;
      }
    }
  }
  protected function _initFrontController() {
		$this->initActionHelperPath();
	  include APPLICATION_PATH . '/application/modules/Sesevent/controllers/Checklicense.php';
  }
}
