<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesmembershipswitch_Plugin_Core {
  public function onPaymentSubscriptionUpdateAfter($event){
    $payload = $event->getPayload();
    if($payload->expiration_date != $_SESSION['sesmembership_expiration']){
        $user = Engine_Api::_()->getItem('user',$payload->user_id);
        Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->getMemberships(array('user_id'=>$user->getIdentity(),'is_sesmembershipswitch'=>0,'is_sesmembershipswitch_notification'=>0));

    }
  }
  public function onPaymentSubscriptionUpdateBefore($event){
    $payload = $event->getPayload();
    $_SESSION['sesmembership_expiration'] = $payload->expiration_date;
  }
  public function onUserLoginAfter($payload){
   /* $payload = $payload->getPayload();
    $userid = $payload->getIdentity();
    $isActiveSubscription = Engine_Api::_()->getDbTable('subscriptions','payment')->check($payload);
    if(!$isActiveSubscription){
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $url = $view->absoluteUrl($view->url(array('module' => 'payment','controller'=>'settings', "action"=>"index"), 'default', true));
      header('Location: '.$url);
      exit();
    }*/
  }
}
