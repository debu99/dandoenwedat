<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Plugin_Core extends Zend_Controller_Plugin_Abstract {
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
      $module = $request->getModuleName();
      $controller = $request->getControllerName();
      $action = $request->getActionName();
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      if($module == "user" && $action == "index" && $controller == "admin-signup"){
        $view->headStyle()->appendStyle('#step_1, #step_4, #step_3{display:none;}');
      }
      if($module == "user" && $action == "order" && $controller == "admin-signup"){
          $request->setModuleName('sessociallogin');
          $request->setControllerName('admin-settings');
          $request->setActionName('order');
      }
    }
    public function onUserLogoutAfter($event, $mode = null) {
        if (!empty($_SESSION['sessociallogin_instagram']['inphoto_url'])) {
            unset($_SESSION['sessociallogin_instagram']['in_id']);
            unset($_SESSION['sessociallogin_instagram']['inphoto_url']);
            unset($_SESSION['sessociallogin_instagram']['in_name']);
            unset($_SESSION['sessociallogin_instagram']['in_username']);
            unset($_SESSION['instagram_code']);
            unset($_SESSION['instagram_uid']);
            unset($_SESSION['instagram_token']);
        }
        if (!empty($_SESSION['instagram_signup']))
            unset($_SESSION['instagram_signup']);

        if (!empty($_SESSION['pinterest_signup']))
            unset($_SESSION['pinterest_signup']);

        if (!empty($_SESSION['yahoo_signup']))
            unset($_SESSION['yahoo_signup']);

        if (!empty($_SESSION['hotmail_signup']))
            unset($_SESSION['hotmail_signup']);

        if (!empty($_SESSION['linkedin_signup'])) {
            unset($_SESSION['linkedin_signup']);
            unset($_SESSION['signup_fields']);
        }
        if (!empty($_SESSION['google_signup'])) {
            unset($_SESSION['google_signup']);
            unset($_SESSION['signup_fields']);
            unset($_SESSION['access_token']);
            unset($_SESSION['refresh_token']);
            unset($_SESSION['google_uid']);
        }
    }

    public function onUserCreateAfter($event) {

      $settings = Engine_Api::_()->getApi('settings', 'core');
      if($_SESSION['facebook_signup']) {
        $facebooksignup = $settings->getSetting('sessociallogin.facebooksignup');
        $facebook_signup = $facebooksignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $facebook_signup), array('name = ?' => 'sessociallogin.facebooksignup'));
      } else if($_SESSION['twitter_signup']) {
        $twittersignup = $settings->getSetting('sessociallogin.twittersignup');
        $twitter_signup = $twittersignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $twitter_signup), array('name = ?' => 'sessociallogin.twittersignup'));
      } else if($_SESSION['google_signup']) {
        $googlesignup = $settings->getSetting('sessociallogin.googlesignup');
        $google_signup = $googlesignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $google_signup), array('name = ?' => 'sessociallogin.googlesignup'));
      } else if($_SESSION['linkedin_signup']) {
        $linkedinsignup = $settings->getSetting('sessociallogin.linkedinsignup');
        $linkedin_signup = $linkedinsignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $linkedin_signup), array('name = ?' => 'sessociallogin.linkedinsignup'));
      } else if($_SESSION['hotmail_signup']) {
        $hotmailsignup = $settings->getSetting('sessociallogin.hotmailsignup');
        $hotmail_signup = $hotmailsignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $hotmail_signup), array('name = ?' => 'sessociallogin.hotmailsignup'));
      } else if($_SESSION['instagram_signup']) {
        $instagramsignup = $settings->getSetting('sessociallogin.instagramsignup');
        $instagram_signup = $instagramsignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $instagram_signup), array('name = ?' => 'sessociallogin.instagramsignup'));
      } else if($_SESSION['pinterest_signup']) {
        $pinterestsignup = $settings->getSetting('sessociallogin.pinterestsignup');
        $pinterest_signup = $pinterestsignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $pinterest_signup), array('name = ?' => 'sessociallogin.pinterestsignup'));
      } else if($_SESSION['yahoo_signup']) {
        $yahoosignup = $settings->getSetting('sessociallogin.yahoosignup');
        $yahoo_signup = $yahoosignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $yahoo_signup), array('name = ?' => 'sessociallogin.yahoosignup'));
      } else if($_SESSION['flickr_signup']) {
        $flickrsignup = $settings->getSetting('sessociallogin.flickrsignup');
        $flickr_signup = $flickrsignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $flickr_signup), array('name = ?' => 'sessociallogin.flickrsignup'));
      } else if($_SESSION['vk_signup']) {
        $vksignup = $settings->getSetting('sessociallogin.vksignup');
        $vk_signup = $vksignup + 1;
        Engine_Api::_()->getDbTable('settings', 'core')->update(array('value' => $vk_signup), array('name = ?' => 'sessociallogin.vksignup'));
      }
    }
    
    public function onUserDeleteAfter($event) {
      $payload = $event->getPayload();
      $user_id = $payload['identity'];
      $socialLogins = array('instagram','linkedin','yahoo','pinterest','google','hotmail','flickr','vk');
      foreach($socialLogins as $socialLogin) {
        $socialloginTable = Engine_Api::_()->getDbtable($socialLogin, 'sessociallogin');
        $socialloginTableSelect = $socialloginTable->select()->where('user_id = ?', $user_id);
        $data = $socialloginTable->fetchRow($socialloginTableSelect);
        if($data)
        $data->delete();
      }
  }
  
    public function onRenderLayoutDefault($event) {

        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $viewer = Engine_Api::_()->user()->getViewer();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $moduleName = $request->getModuleName();
        $actionName = $request->getActionName();
        $controllerName = $request->getControllerName();
        $script = '';
        if ($controllerName == 'signup' && $actionName == 'index' && !count($_POST) && !empty($_SESSION['signup_fields']['email'])) {
            $email = $_SESSION['signup_fields']['email'];
            $script .= "window.addEvent('domready', function() {
        var elum = sesJqueryObject('.form-elements').find('input[type=email]');
        elum.each(function(){
        if(sesJqueryObject(this).closest('form').attr('action').indexOf('signup') >= 0) {
            sesJqueryObject(this).val('" . $email . "')
}
        });})";
        }

        $view->headScript()->appendScript($script);
    }

}
