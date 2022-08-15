<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Plugin_Core {

  public function onUserLoginAfter($event){
    if(!empty($_GET['restApi']))
        return;
    $user = $event->getPayload();
    if(!empty($_COOKIE['user_popup_consent'])){
        Engine_Api::_()->getDbTable('settings', 'user')->setSetting($user,'gdpr_popup_consent',1);
    }
    if(!empty($_COOKIE['user_consent'])){
      Engine_Api::_()->getDbTable('settings', 'user')->setSetting($user,'user_consent',1);
      Engine_Api::_()->getDbTable('settings', 'user')->setSetting($user,'user_consent_time', $_COOKIE['user_consent_date']);
    }
  }
  public function onUserSignupAfter($event){
    $user = $event->getPayload();
    if(!empty($_COOKIE['user_consent'])){
      Engine_Api::_()->getDbTable('settings', 'user')->setSetting($user,'user_consent',1);
      Engine_Api::_()->getDbTable('settings', 'user')->setSetting($user,'user_consent_time', $_COOKIE['user_consent_date']);
    }
    if(!empty($_COOKIE['user_popup_consent'])){
        Engine_Api::_()->getDbTable('settings', 'user')->setSetting($user,'gdpr_popup_consent',1);
    }
  }
  public function onRenderLayoutDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event);
  }
	public function onRenderLayoutMobileDefault($event) {
    return $this->onRenderLayoutDefault($event);
  }
	public function onRenderLayoutMobileDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event);
  }
    public function onUserLogoutBefore($event){
        $viewer = Engine_Api::_()->user()->getViewer();
        $userConsent = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($viewer,'user_consent');
        $userConsentTime = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($viewer,'user_consent_time');
        $gdprPopupConsent = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($viewer,'gdpr_popup_consent');
        if($userConsentTime)
            setcookie('user_consent_date',$userConsentTime, time() + (86400 * 30), "/");
        if($userConsent)
            setcookie('user_consent', $userConsent, time() + (86400 * 30), "/");
        if($gdprPopupConsent)
            setcookie('user_popup_consent', $gdprPopupConsent, time() + (86400 * 30), "/");
    }
	public function onRenderLayoutDefault($event) {
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$moduleName = $request->getModuleName();
		$actionName = $request->getActionName();
		$controllerName = $request->getControllerName();
    $headScript = new Zend_View_Helper_HeadScript();
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
                      . 'application/modules/Sesgdpr/externals/scripts/core.js');
	}
}
