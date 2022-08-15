<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Bootstrap.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
class Sesadvancedcomment_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  public function __construct($application) {
    parent::__construct($application);
    $this->initViewHelperPath();
    $baseUrl = Zend_Registry::get('StaticBaseUrl');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $view->headTranslate(array('Write a comment...'));
    $headScript = new Zend_View_Helper_HeadScript();
     if(strpos(str_replace('/','',$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']),str_replace('/','',$_SERVER['SERVER_NAME'].'admin'))=== FALSE){
        $albumenable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum');
        $session = new Zend_Session_Namespace('sesadvcomment');
        $session->albumenable = $albumenable;
        $videoenable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesvideo');
        $session->videoenable = $videoenable;
        $session->sesadvcommentActive = true;
        $headScript->appendFile($baseUrl . 'application/modules/Sesadvancedcomment/externals/scripts/core.js');
        $headScript->appendFile($baseUrl. 'application/modules/Sesbasic/externals/scripts/member/membership.js');
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') 
            . 'application/modules/Sesbasic/externals/scripts/jquery.tooltip.js');
        $headScript->appendFile($baseUrl. 'application/modules/Sesbasic/externals/scripts/tooltip.js');
        $headScript->appendFile($baseUrl. 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
        $headScript->appendFile($baseUrl. 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js');
        $headScript->appendFile($baseUrl. 'application/modules/Sesadvancedcomment/externals/scripts/owl.carousel.min.js');
        $headScript->appendFile($baseUrl
            . 'application/modules/Sesbasic/externals/scripts/hashtag/autosize.min.js');
        
        $headScript->appendFile($baseUrl
            . 'application/modules/Sesbasic/externals/scripts/mention/underscore-min.js');
       $headScript->appendFile($baseUrl
            . 'application/modules/Sesbasic/externals/scripts/mention/jquery.mentionsInput.js');
            
        
        $headScript->appendFile($baseUrl
            . 'application/modules/Sesbasic/externals/scripts/hashtag/hashtags.js');
        $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl
                  . 'application/modules/Sesadvancedcomment/externals/styles/styles.css');
        
        $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl
                  . 'application/modules/Sesbasic/externals/styles/mention/jquery.mentionsInput.css');
        
        $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl
                  . 'application/modules/Sesbasic/externals/styles/emoji.css');
        $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl
                  . 'application/modules/Sesbasic/externals/styles/customscrollbar.css');  
     }
    
  }

  protected function _initFrontController() {
    include APPLICATION_PATH . '/application/modules/Sesadvancedcomment/controllers/Checklicense.php';
  }
}