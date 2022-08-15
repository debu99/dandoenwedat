<?php

class Serenity_Plugin_Core extends Zend_Controller_Plugin_Abstract {

	public function onRenderLayoutDefault(){

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    $action = $request->getActionName();
    $changelanding = Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.changelanding', 0);
    
    if(!empty($changelanding) && $module == 'core' && $controller == 'index' && $action == 'index') {
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $script = '
        en4.core.runonce.add(function() {
          $(document).getElement("body").addClass("serenity_landingpage");
        });';
      $view->headScript()->appendScript($script);
		}
	}
}
