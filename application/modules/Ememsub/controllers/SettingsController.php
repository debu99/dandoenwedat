<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: SettingsController.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
class Ememsub_SettingsController extends Core_Controller_Action_User
{
  public function init()
  {
    // Can specifiy custom id
    $id = $this->_getParam('id', null);
    $subject = null;
    if( null === $id ) {
      $subject = Engine_Api::_()->user()->getViewer();
      Engine_Api::_()->core()->setSubject($subject);
    } else {
      $subject = Engine_Api::_()->getItem('user', $id);
      Engine_Api::_()->core()->setSubject($subject);
    }

    // Set up require's
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
    $this->_helper->requireAuth()->setAuthParams(
      $subject,
      null,
      'edit'
    );

    // Set up navigation
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('user_settings', ( $id ? array('params' => array('id'=>$id)) : array()));
  }
  
  public function indexAction()
  {
      $this->_helper->content->setEnabled();
    $user = Engine_Api::_()->core()->getSubject('user');

    // Check if they are an admin or moderator (don't require subscriptions from them)
    $level = Engine_Api::_()->getItem('authorization_level', $user->level_id);
    if( in_array($level->type, array('admin', 'moderator')) ) {
      $this->view->isAdmin = true;
      return;
    }
    
    // Get packages
    $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $this->view->packages = $packages = $packagesTable->fetchAll(array('enabled = ?' => 1, 'after_signup = ?' => 1));

    // Get current subscription and package
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $this->view->currentSubscription = $currentSubscription = $subscriptionsTable->fetchRow(array(
      'user_id = ?' => $user->getIdentity(),
      'active = ?' => true,
    ));

    // Get current package
    if( $currentSubscription ) {
      $this->view->currentPackage = $currentPackage = $packagesTable->fetchRow(array(
        'package_id = ?' => $currentSubscription->package_id,
      ));
    }

    // Get current gateway?
  }

  public function confirmAction()
  {
    // Process
    $user = Engine_Api::_()->core()->getSubject('user');
    

    // Get packages
    $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $this->view->package = $package = $packagesTable->fetchRow(array(
      'enabled = ?' => 1,
      'package_id = ?' => (int) $this->_getParam('package_id'),
    ));

    // Check if it exists
    if( !$package ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    // Get current subscription and package
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $currentSubscription = $subscriptionsTable->fetchRow(array(
      'user_id = ?' => $user->getIdentity(),
      'active = ?' => true,
    ));

    // Get current package
    $currentPackage = null;
    if( $currentSubscription ) {
      $currentPackage = $packagesTable->fetchRow(array(
        'package_id = ?' => $currentSubscription->package_id,
      ));
    }
    
    // Check if they are the same
    if( $package->package_id == $currentPackage->package_id ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }


    // Check method
    if( !$this->getRequest()->isPost() ) {
      return;
    }




    // Cancel any other existing subscriptions
    Engine_Api::_()->getDbtable('subscriptions', 'payment')
      ->cancelAll($user, 'User cancelled the subscription.', $currentSubscription);
    

    // Insert the new temporary subscription
    $db = $subscriptionsTable->getAdapter();
    $db->beginTransaction();

    try {
      $subscription = $subscriptionsTable->createRow();
      $subscription->setFromArray(array(
        'package_id' => $package->package_id,
        'user_id' => $user->getIdentity(),
        'status' => 'initial',
        'active' => false, // Will set to active on payment success
        'creation_date' => new Zend_Db_Expr('NOW()'),
      ));
      $subscription->save();

      // If the package is free, let's set it active now and cancel the other
      if( $package->isFree() ) {
        $subscription->setActive(true);
        $subscription->onPaymentSuccess();
        if( $currentSubscription ) {
          $currentSubscription->cancel();
        }
      }

      $subscription_id = $subscription->subscription_id;

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    // Check if the subscription is ok
    if( $package->isFree() && $subscriptionsTable->check($user) ) {
      $user = Engine_Api::_()->getItem('user', $user->user_id);
      // If user's member level changed then redirect to edit profile page.
      if (Engine_Api::_()->getDbtable('values', 'authorization')->changeUsersProfileType($user)) {
        Engine_Api::_()->getDbtable('values', 'authorization')->resetProfileValues($user);
        $this->_helper->redirector->gotoRoute(array('action' => 'profile', 'controller' => 'edit'), 'user_extended');
      }

      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    // Prepare subscription session
    $session = new Zend_Session_Namespace('Payment_Subscription');
    $session->is_change = true;
    $session->user_id = $user->getIdentity();
    $session->subscription_id = $subscription_id;

    // Redirect to subscription handler
    return $this->_helper->redirector->gotoRoute(array('controller' => 'subscription',
      'action' => 'gateway'));
  }
}
