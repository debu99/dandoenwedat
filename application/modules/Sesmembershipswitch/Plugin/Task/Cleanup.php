<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Cleanup.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesmembershipswitch_Plugin_Task_Cleanup extends Core_Plugin_Task_Abstract
{
  public function execute()
  {
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    // Get subscriptions that have expired or have finished their trial period
    // (trial is not yet implemented)
    $customSelect = $subscriptionsTable->info('name').'.package_id NOT IN (SELECT current_plan_id FROM engine4_sesmembershipswitch_plans)';

      $switchTableName  = Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->info('name');



      $select = $subscriptionsTable->select()
          ->from($subscriptionsTable->info('name'),'*')
      ->where('expiration_date <= ?', new Zend_Db_Expr('NOW()'))
      ->where('status = ?', 'active')
      ->where($customSelect)
      //->where('status IN(?)', array('active', 'trial'))
      ->order('subscription_id ASC')
      ->limit(10);
      $select->setIntegrityCheck(false);
      $select->joinLeft($switchTableName,$switchTableName.'.user_id ='.$subscriptionsTable->info('name').'.user_id',null);
      $select->where($switchTableName.'.is_sesmembershipswitch IS NULL || '.$switchTableName.'.is_sesmembershipswitch = 0 ');
    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
      $package = $subscription->getPackage();
      // Check if the package has an expiration date
      $expiration = $package->getExpirationDate();
      if( !$expiration || !$package->hasDuration()) {
        continue;
      }
      // It's expired
      // @todo send an email
      $subscription->onExpiration();
      if ($subscription->didStatusChange()) {
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($subscription->getUser(), 'payment_subscription_expired', array(
            'subscription_title' => $package->title,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
            Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        ));
      }
        $user = Engine_Api::_()->getItem('user',$subscription->user_id);
        Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->getMemberships(array('user_id'=>$user->getIdentity(),'is_sesmembershipswitch'=>1,'is_sesmembershipswitch_notification'=>1));
    }

    
    // Get subscriptions that are old and are pending payment
    $select = $subscriptionsTable->select()
      ->where('status IN(?)', array('initial', 'pending'))
      ->where('expiration_date <= ?', new Zend_Db_Expr('DATE_SUB(NOW(), INTERVAL 2 DAY)'))
      ->order('subscription_id ASC')
      ->limit(10)
      ;

    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
      $subscription->onCancel();
        $user = Engine_Api::_()->getItem('user',$subscription->user_id);
        Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->getMemberships(array('user_id'=>$user->getIdentity(),'is_sesmembershipswitch'=>1,'is_sesmembershipswitch_notification'=>1));

        if ($subscription->didStatusChange()) {
        $package = $subscription->getPackage();
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($subscription->getUser(), 'payment_subscription_cancelled', array(
            'subscription_title' => $package->title,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
            Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        ));
      }
    }
  }
}


