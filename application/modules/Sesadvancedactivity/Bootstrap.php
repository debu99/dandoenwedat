<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  public function __construct($application)
  {
    parent::__construct($application);
    $settings = Engine_Api::_()->getApi('settings', 'core');// GitHub Issue #119
    if ($settings->getSetting('sesadvancedactivity.pluginactivated'))
      $this->initViewHelperPath();
    if(strpos(str_replace('/','',$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']),str_replace('/','',$_SERVER['SERVER_NAME'].'admin'))=== FALSE){
        $headScript = new Zend_View_Helper_HeadScript();
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') 
            . 'application/modules/Sesbasic/externals/scripts/jquery.tooltip.js');
         $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') 
            . 'application/modules/Sesbasic/externals/scripts/tooltip.js');
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') 
            . 'application/modules/Sesadvancedactivity/externals/scripts/core.js');
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') 
            . 'application/modules/Sesbasic/externals/scripts/hashtag/autosize.min.js');
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') 
            . 'application/modules/Sesbasic/externals/scripts/hashtag/hashtags.js');
       $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $view->headLink()->appendStylesheet(Zend_Registry::get('StaticBaseUrl') 
            . 'application/modules/Sesadvancedactivity/externals/styles/styles.css');
    }
  }

  protected function _initFrontController() {
  
    $headScript = new Zend_View_Helper_HeadScript();
    //Advanced Notification work based on admin settings
    $advancednotification = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.advancednotification', 0);
    if ($advancednotification) {
      $headScript->appendFile(Zend_Registry::get('StaticBaseUrl'). 'application/modules/Sesadvancedactivity/externals/scripts/updates_notifications.js');
    }
    include APPLICATION_PATH . '/application/modules/Sesadvancedactivity/controllers/Checklicense.php';
  }
}
