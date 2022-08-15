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
class Sesmembershipswitch_Api_Core extends Core_Api_Abstract {
  public function switchUser(){
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if(!$settings->getSetting('sesmembershipswitch.enable', 1))
      return;
    $planTableName = Engine_Api::_()->getDbtable('plans', 'sesmembershipswitch')->info('name');
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $userTableName  = Engine_Api::_()->getItemTable('user')->info('name');

      $switchTableName  = Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->info('name');

     // Get subscriptions that have expired or have finished their trial period
    // (trial is not yet implemented)
    $select = $subscriptionsTable->select()
      ->from($subscriptionsTable->info('name'))
      //->where('DATE_FORMAT(DATE_ADD(expiration_date,INTERVAL `switch` DAY),"%Y-%m-%d") = CURDATE()')
      ->where("CASE WHEN expiration_date IS NOT NULL THEN DATE_FORMAT(DATE_ADD(expiration_date,INTERVAL `switch` DAY),'%Y-%m-%d') = CURDATE() ELSE DATE_FORMAT(DATE_ADD(".$subscriptionsTable->info('name').".creation_date,INTERVAL `switch` DAY),'%Y-%m-%d') = CURDATE()  END ")
      ->where($subscriptionsTable->info('name').'.status = ?', 'active')
      ->order($subscriptionsTable->info('name').'.subscription_id ASC')
      ->limit(50);
    $select->setIntegrityCheck(false);

      $select->joinLeft($switchTableName,$switchTableName.'.user_id ='.$subscriptionsTable->info('name').'.user_id',null);
      $select->where($switchTableName.'.is_sesmembershipswitch IS NULL || '.$switchTableName.'.is_sesmembershipswitch = 0 ');
      $select->joinLeft($userTableName,$userTableName.'.user_id ='.$subscriptionsTable->info('name').'.user_id',null);
    $select->joinLeft($planTableName,$planTableName.'.current_plan_id ='.$subscriptionsTable->info('name').'.package_id','*');    
    $select->joinLeft('engine4_payment_packages','engine4_payment_packages'.'.package_id =engine4_payment_subscriptions.package_id',null);
    $select->where($planTableName.'.change_plan_id != '.$userTableName.'.level_id');
    $select->where($userTableName.'.user_id IS NOT NULL');
    $select->where($planTableName.'.plan_id IS NOT NULL');
    //$select->where($planTableName.'.switch > ?',0);
    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
      $currentPackageId = $subscription->package_id;
      $user = Engine_Api::_()->getItem('user',$subscription->user_id);
      $currentplan = Engine_Api::_()->getItem('payment_package',$subscription->current_plan_id);
      if($user){
        $changedplan = Engine_Api::_()->getItem('payment_package',$subscription->change_plan_id);
        if($changedplan){
          //change plan id
          $user->level_id = $changedplan->level_id;
          $user->enabled = true; // This will get set correctly in the update hook
          $user->save();
          if($subscription && !$changedplan->getExpirationDate()) {
            $subscription->package_id = $changedplan->package_id;
            $subscription->status = 'active';
            $subscription->active = 1;
            $subscription->save();
          }
          if ($settings->getSetting('sesmembershipswitch.changelevelmail.enable', 1) && $currentPackageId != $changedplan->package_id) {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sesmembershipswitch_level_change', array(
                'subscription_title' => $changedplan->title,
                'current_plan'=>$currentplan->getTitle(),
                'changed_plan'=>$changedplan->getTitle(),
                'subscription_description' => $changedplan->description,
                'subscription_terms' => $changedplan->getPackageDescription(),
                'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
          }
          if ($settings->getSetting('sesmembershipswitch.changelevelnotification.enable', 1)  && $currentPackageId != $changedplan->package_id) {
            $params['currentplan'] = '<a href="' . $view->absoluteUrl($view->url(array('module' => 'payment','controller'=>'settings', "action"=>"index"), 'default', true)) . '">' . $currentplan->getTitle() . '</a>';
            $params['changedplan'] = '<a href="' . $view->absoluteUrl($view->url(array('module' => 'payment','controller'=>'settings', "action"=>"index"), 'default', true)) . '">' . $changedplan->getTitle() . '</a>';
            $table = Engine_Api::_()->getDbTable('users','user');
            $select = $table->select()->where('level_id =?',1)->limit(1);
            $admin = $table->fetchRow($select);
            if($admin)
              Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $admin, $user, 'sesmembershipswitch_changenotifi',$params);
          }
        }
      }
      Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->getMemberships(array('user_id'=>$user->getIdentity(),'is_sesmembershipswitch'=>1));
    }    
  }
  function sendNotification(){
     $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if(!$settings->getSetting('sesmembershipswitch.enable', 1))
      return;
    $planTableName = Engine_Api::_()->getDbtable('plans', 'sesmembershipswitch')->info('name');
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $userTableName  = Engine_Api::_()->getItemTable('user')->info('name');
      $switchTableName =  Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->info('name');
     // Get subscriptions that have expired or have finished their trial period
    // (trial is not yet implemented)
    $select = $subscriptionsTable->select()
      ->from($subscriptionsTable->info('name'))
      ->where("CASE 
        WHEN expiration_date IS NOT NULL AND type = 'minutes' THEN DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(expiration_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` MINUTE),'%Y-%m-%d') = CURDATE() 
        WHEN expiration_date IS NOT NULL AND type = 'hours' THEN DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(expiration_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` HOUR),'%Y-%m-%d') = CURDATE() 
        WHEN expiration_date IS NOT NULL AND type = 'days' THEN DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(expiration_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` DAY),'%Y-%m-%d') = CURDATE() 
        WHEN expiration_date IS NOT NULL AND type = 'weeks' THEN DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(expiration_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` WEEK),'%Y-%m-%d') = CURDATE() 
        WHEN expiration_date IS NULL AND `type` = 'minutes' THEN DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(".$subscriptionsTable->info('name').".creation_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` MINUTE),'%Y-%m-%d') = CURDATE()
        WHEN expiration_date IS NULL AND `type` = 'hours' THEN DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(".$subscriptionsTable->info('name').".creation_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` HOUR),'%Y-%m-%d') = CURDATE()
        WHEN expiration_date IS NULL AND `type` = 'days' THEN DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(".$subscriptionsTable->info('name').".creation_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` DAY),'%Y-%m-%d') = CURDATE()
        ELSE DATE_FORMAT(DATE_SUB(DATE_FORMAT(DATE_ADD(".$subscriptionsTable->info('name').".creation_date,INTERVAL `switch` DAY),'%Y-%m-%d'),INTERVAL `number` WEEK),'%Y-%m-%d') = CURDATE()  END ")
      ->where($subscriptionsTable->info('name').'.status = ?', 'active')
      ->order($subscriptionsTable->info('name').'.subscription_id ASC')
      ->limit(50);
    $select->setIntegrityCheck(false);

      $select->joinLeft($switchTableName,$switchTableName.'.user_id ='.$subscriptionsTable->info('name').'.user_id',null);
      $select->where($switchTableName.'.is_sesmembershipswitch_notification IS NULL || '.$switchTableName.'.is_sesmembershipswitch_notification = 0 ');

    $select->joinLeft($userTableName,$userTableName.'.user_id ='.$subscriptionsTable->info('name').'.user_id',null);
    $select->joinLeft($planTableName,$planTableName.'.current_plan_id ='.$subscriptionsTable->info('name').'.package_id','*');
    $select->joinLeft('engine4_payment_packages','engine4_payment_packages'.'.package_id =engine4_payment_subscriptions.package_id',null);
    $select->where($planTableName.'.change_plan_id != '.$userTableName.'.level_id');
    $select->where($userTableName.'.user_id IS NOT NULL');
    $select->where($planTableName.'.plan_id IS NOT NULL');
    //$select->where($planTableName.'.switch > ?',0);
    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
        $user = Engine_Api::_()->getItem('user',$subscription->user_id);
        $package = $subscription->getPackage();
        //{item:$subject} your subscription plan {var:$planname} expiring soon after {var:$days}
        $params['planname'] = '<a href="' . $view->absoluteUrl($view->url(array('module' => 'payment','controller'=>'settings', "action"=>"index"), 'default', true)) . '">' . $package->getTitle() . '</a>';
        $params['days'] = $subscription->number.' '.ucfirst($subscription->type); 
        $table = Engine_Api::_()->getDbTable('users','user');
        $select = $table->select()->where('level_id =?',1)->limit(1);
        $admin = $table->fetchRow($select);
        if($admin)
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $admin, $user, 'sesmembershipswitch_notification',$params);
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sesmembershipswitch_level_notify', array(
            'plan_name'=>$params['planname'],
            'period'=>$params['days'],
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
            Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        ));
        Engine_Api::_()->getDbTable('switchmemberships','sesmembershipswitch')->getMemberships(array('user_id'=>$user->getIdentity(),'is_sesmembershipswitch_notification'=>1));

    }
    
  }
}