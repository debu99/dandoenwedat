<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Widget_CookieBannerController extends Engine_Content_Widget_Abstract {
  
  public function indexAction() {
     $user = Engine_Api::_()->user()->getViewer();
     if($user->getIdentity()){ 
      $consent = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($user,'user_consent');
      $consentTime = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($user,'user_consent_time');  
    }else{
      if(!empty($_COOKIE['user_consent'])){
        $consent = true;
        $consentTime = $_COOKIE['user_consent_date'];
      }
    }
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $isPopup = $settings->getSetting('gdpr.popup',0);
    if(@$consent){
      return $this->setNoRender();  
    }

    $request = Zend_Controller_Front::getInstance()->getRequest();
		$params = $request->getParams();
    if($params['controller'] == "help" && $params['action'] == "privacy" && $params['module'] == "core")
      return $this->setNoRender();  

    $this->view->gdpr_bannertext = $settings->getSetting('gdpr_bannertext', 'We use cookies to personalise site content, social media features and to analyse our traffic. We also share information about your use of this site with our advertising and social media partners.');
    $this->view->gdpr_bannerstyle = $settings->getSetting('gdpr_bannerstyle', 'top_center');
    $this->view->gdpr_banneroption = $settings->getSetting('gdpr_banneroption', array('changeSettings','readMore','accept'));
    $this->view->gdpr_bannerbackgroundcolor = $settings->getSetting('gdpr_bannerbackgroundcolor', 'fff');
    $this->view->gdpr_bannertextcolor = $settings->getSetting('gdpr_bannertextcolor', '555');
    $this->view->gdpr_bannerlinkcolor = $settings->getSetting('gdpr_bannerlinkcolor', '3960bb');
    $this->view->gdpr_privacyurl = $settings->getSetting('gdpr_privacyurl', 'help/privacy');
	}
}
