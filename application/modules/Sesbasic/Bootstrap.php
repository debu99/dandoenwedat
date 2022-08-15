<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Bootstrap extends Engine_Application_Bootstrap_Abstract {
  protected function _initFrontController() {
    Zend_Controller_Front::getInstance()->registerPlugin(new Sesbasic_Plugin_Core);
  }
  public function __construct($application) {
    parent::__construct($application);
    $this->initViewHelperPath();
    $baseUrl = Zend_Registry::get('StaticBaseUrl');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $headScript = new Zend_View_Helper_HeadScript();
    if (strpos(str_replace('/', '', $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']), str_replace('/', '', $_SERVER['SERVER_NAME'] . 'admin')) === FALSE) {
			$headScript->appendFile($baseUrl . 'application/modules/Sesbasic/externals/scripts/core.js');
			$headScript->prependFile(Zend_Registry::get('StaticBaseUrl')
									 .'application/modules/Sesbasic/externals/scripts/sesJquery.js');
			$headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
									 .'application/modules/Sesbasic/externals/scripts/sessmoothbox/sessmoothbox.js');
			$headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
									 .'application/modules/Sesbasic/externals/scripts/tooltip/jquery.tooltipster.js');
			$view->headLink()->appendStylesheet("application/modules/Sesbasic/externals/styles/tooltip/tooltipster.css");

      //Load google map if any of the given ses plugin install
      $pluginNames = array('sesalbum', 'sesvideo', 'sesevent', 'sesblog', 'sesgroupalbum', 'sesmember',"sesadvancedactivity"); //pass comma seprated string of modules
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.mapApiKey', '') && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) {
        $headScript->appendFile('https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.mapApiKey', ''), '');
      }
      if (Engine_Api::_()->sesbasic()->isModuleEnable($pluginNames)) {
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
								 .'application/modules/Sesbasic/externals/scripts/jquery.flex-images.js');
      }
    }
	  if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmultiplecurrency')){
        $session = new Zend_Session_Namespace('ses_multiple_currency');
      $session->multipleCurrencyPluginActivated = 0;
    }
    //Tweet plugin work
    $script = '';
    $script .=
    "
    var sestweet_text = '';
    ";
    $view->headScript()->appendScript($script);

      $this->initViewHelperPath();
      $layout = Zend_Layout::getMvcInstance();
      $layout->getView()
          ->addFilterPath(APPLICATION_PATH . "/application/modules/Sesbasic/View/Filter", 'Sesbasic_View_Filter_')
          ->addFilter('Shortcode');


  }
}
