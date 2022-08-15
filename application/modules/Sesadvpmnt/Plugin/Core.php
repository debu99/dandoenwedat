<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvpmnt_Plugin_Core extends Zend_Controller_Plugin_Abstract
{

 public function routeShutdown(Zend_Controller_Request_Abstract $request) {
    $module = $request->getModuleName();
    $controller = $request->getControllerName(); 
    $action = $request->getActionName();
    if(isset($_GET['gateway_id'])) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $_GET['gateway_id']);
    }   
    $gatewayEnabled = false;
    if(!empty($gateway))
      $gatewayEnabled = ($gateway->plugin == 'Sesadvpmnt_Plugin_Gateway_Stripe') ? 1 : 0;
    if($module == "payment" && $controller == "subscription" && $action == "process" && $gatewayEnabled){
        $request->setModuleName('sesadvpmnt');
        $request->setControllerName('payment');
        $request->setActionName('index');
        $request->setParam('type','user');
    } /*elseif($module == "sespagepackage" && $controller == "payment" && $action == "process" && $gateway->plugin == 'Sesadvpmnt_Plugin_Gateway_Stripe'){
        $request->setModuleName('sesadvpmnt');
        $request->setControllerName('payment');
        $request->setActionName('index');
        $request->setParam('type','pagepackage');
    }*/
  }
  public function onUserCreateBefore($event)
  {
    $payload = $event->getPayload();
    if( !($payload instanceof User_Model_User) ) {
      return;
    }
    // Check if the user should be enabled?
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    if( !$subscriptionsTable->check($payload) ) {
      $payload->enabled = false;
      // We don't want to save here
    }
  }

  public function onUserUpdateBefore($event)
  {
    $payload = $event->getPayload();

    if( !($payload instanceof User_Model_User) ) {
      return;
    }

    // Actually, let's ignore if they've logged in before
    if( !empty($payload->lastlogin_date) ) {
      return;
    }

    // Check if the user should be enabled?
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    if( !$subscriptionsTable->check($payload) ) {
      $payload->enabled = false;
      // We don't want to save here
    }
  }

  public function onAuthorizationLevelDeleteBefore($event)
  {
    $payload = $event->getPayload();

    if( $payload instanceof Authorization_Model_Level ) {
      $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
      $packagesTable->update(array(
        'level_id' => 0,
      ), array(
        'level_id = ?' => $payload->getIdentity(),
      ));
    }
  }
}
