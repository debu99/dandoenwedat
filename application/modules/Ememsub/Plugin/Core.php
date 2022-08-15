<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Core.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Plugin_Core extends Zend_Controller_Plugin_Abstract
{
  public function routeShutdown(Zend_Controller_Request_Abstract $request) {
    $module = $request->getModuleName();
    $controller = $request->getControllerName(); 
    $action = $request->getActionName();  
    if($module == "payment" && $controller == "settings"){
      $request->setModuleName('ememsub');
    }
  }
  public function onRenderLayoutDefault($event) {
       
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
