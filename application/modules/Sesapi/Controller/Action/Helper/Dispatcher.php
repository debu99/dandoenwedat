<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Dispatcher.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
class Sesapi_Controller_Action_Helper_Dispatcher extends Zend_Controller_Action_Helper_Abstract {

  public function preDispatch()
  {
    
    if(!empty($_SESSION['sesapi'])){
      $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      unset($_SESSION['sesapi']); 
      header("Location:".$actual_link.'&restApi=Sesapi');
      exit();
    }
    if(!empty($_GET['sesapi_platform'])){
      $settingEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesapi.headerfooter.enable', '1');
    }else
      $settingEnable = 0;
    if(($settingEnable || !empty($_SESSION['removeSiteHeaderFooter'])) && strpos($_SERVER['REQUEST_URI'],'admin') === FALSE ){
      $_SESSION['removeSiteHeaderFooter'] = true;
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl . 'application/modules/Sesapi/externals/styles/style.css');
    }
    //set language
    $locale = null;
    $language = (!empty($_REQUEST['language'])) ? $_REQUEST['language'] : "";
    if($language){
      if( !empty($language) ) {
        try {
          $language = Zend_Locale::findLocale($language);
        } catch( Exception $e ) {
          $language = null;
        }
      }
  
      if(  $language && !$locale ) $locale = $language;
      if( !$language &&  $locale ) $language = $locale;
      
      if( $language && $locale ) {
        // Set as cookie
        //remove language cookie to set again
        if(isset($_COOKIE['en4_language']))
          setcookie('en4_language', $language, time() - (86400*365), '/');
        setcookie('en4_language', $language, time() + (86400*365), '/');
        //remove locale cookie to set again
        if(isset($_COOKIE['en4_locale']))
          setcookie('en4_locale', $language, time() + (86400*365), '/');
        setcookie('en4_locale',   $locale,   time() + (86400*365), '/');
      }
    }
    if(!empty($_REQUEST['auth_token'])){
      $user_id = Engine_Api::_()->getApi('oauth','sesapi')->validateToken($_REQUEST['auth_token']);
      if($user_id){ 
        $user = Engine_Api::_()->getItem('user',$user_id);
        if($user->getIdentity()){
          Zend_Auth::getInstance()->getStorage()->write($user_id);
          Engine_Api::_()->user()->setViewer();  
          if(!empty($locale) && !empty($language)){
            $setLocale = new Zend_Locale($locale);
            $user->locale = $locale;
            $user->language = $language;
            $user->save();
          }
        }else{
          Engine_Api::_()->user()->getAuth()->clearIdentity();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'account_deleted','result'=>array()));  
        }
      }
    }
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    if(!empty($_SESSION['subscriptionStepsEnable']) && strpos($_SERVER['REQUEST_URI'],'sesapi/subscription') === false){
       $_SERVER['REQUEST_URI'] = $view->url(array('module'=>'sesapi','controller'=>'subscription','action'=>"finish",'state'=>"failure"),'default',true);
       unset($_SESSION['subscriptionStepsEnable']);
    }
  }

}