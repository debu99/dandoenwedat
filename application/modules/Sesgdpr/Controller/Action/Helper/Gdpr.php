<?php
class Sesgdpr_Controller_Action_Helper_Gdpr extends Zend_Controller_Action_Helper_Abstract {
  public function preDispatch() {
    if(!empty($_GET['restApi']))
        return;
    $user = Engine_Api::_()->user()->getViewer();
    $front = Zend_Controller_Front::getInstance();
    $module = $front->getRequest()->getModuleName();
    $action = $front->getRequest()->getActionName();
    $controller = $front->getRequest()->getControllerName();
    $consent = false;
    if($user->getIdentity()){ 
      $consent = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($user,'user_consent');
      $consentTime = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($user,'user_consent_time');  
    }else{
      if(!empty($_COOKIE['user_consent'])){
        $consent = true;
        $consentTime = $_COOKIE['user_consent_date'];
      }
    }
    $bypassCookieKeys = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesconsent_bypass_cookie','en4_maint_code,user_consent,user_consent_date,PHPSESSID,en4_locale,en4_language,user_popup_consent,sesatozthemechoose,ses_login_users');
    $cookieArray = array();
    if($bypassCookieKeys){
      $cookieArray = explode(',',$bypassCookieKeys);  
    }
    //remove all cookie if consent not given
    if(!$consent && strpos($_SERVER['REQUEST_URI'],'sesgdpr/index/consent') == false){
      $cookies = $_COOKIE;  
      foreach($cookies as $key=>$cookie){
        if(in_array($key,$cookieArray))
          continue;
        unset($_COOKIE[$key]);  
        setcookie($key, "", null, "/");
      }
    }
  }
}
