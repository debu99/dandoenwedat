<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_IndexController extends Core_Controller_Action_Standard {

  public function manageAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;
    // Render
    $this->_helper->content->setEnabled();
  }

  public function transactionAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;

    // Render
    $this->_helper->content->setEnabled();
  }

  public function earnCreditAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;

    // Render
    $this->_helper->content->setEnabled();
  }

  public function helpAction() {
    // Render
    $this->_helper->content->setEnabled();
  }

  public function badgesAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;

    // Render
    $this->_helper->content->setEnabled();
  }

  public function leaderboardAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;

    // Render
    $this->_helper->content->setEnabled();
  }

  public function inviteAction() {

    //Take Reference From SE Invite module
    $settings = Engine_Api::_()->getApi('settings', 'core');

    // Check if admins only
    if ($settings->getSetting('user.signup.inviteonly') == 1) {
      if (!$this->_helper->requireAdmin()->isValid()) {
        return;
      }
    }

    // Check for users only
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $enableSignupReferral = $settings->getSetting('sescredit.affiliateforsingup', 1);
    if (!$enableSignupReferral) {
      return;
    }

    // Make form
    $this->view->form = $form = new Sescredit_Form_Invite();

    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $values = $form->getValues();
    $viewer = Engine_Api::_()->user()->getViewer();
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $db = $inviteTable->getAdapter();
    $db->beginTransaction();
    try {
      $emailsSent = Engine_Api::_()->getDbtable('invites', 'sescredit')->sendInvites($viewer, $values['recipients'], @$values['message'], $values['friendship']);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      if (APPLICATION_ENV == 'development') {
        throw $e;
      }
    }
    //$this->view->alreadyMembers = $alreadyMembers;
    $this->view->emails_sent = $emailsSent;

    return $this->render('sent');
  }

  public function signupAction() {
    // Psh, you're already signed up
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if ($viewer && $viewerId) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    $affiliateCode = $this->_getParam('affiliate');
    if (!empty($affiliateCode)) {
      $affiliateTable = Engine_Api::_()->getDbTable('affiliates', 'sescredit');
      $userId = $affiliateTable->select()
              ->from($affiliateTable->info('name'), 'user_id')
              ->where('affiliate =?', $affiliateCode)
              ->query()
              ->fetchColumn();
      if ($userId) {
        $session = new Zend_Session_Namespace('sescredit_affiliate_signup');
        $session->user_id = $userId;
      }
    }
    // Get invite params
    $session = new Zend_Session_Namespace('invite');
    $session->invite_code = $this->_getParam('code');
    $session->invite_email = $this->_getParam('email');

    // Check code now if set
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if ($settings->getSetting('user.signup.inviteonly') > 0) {
      // Tsk tsk no code
      if (empty($session->invite_code)) {
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }

      // Check code
      $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
      $inviteSelect = $inviteTable->select()
              ->where('code = ?', $session->invite_code);

      // Check email
      if ($settings->getSetting('user.signup.checkemail')) {
        // Tsk tsk no email
        if (empty($session->invite_email)) {
          return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }
        $inviteSelect
                ->where('recipient = ?', $session->invite_email);
      }

      $inviteRow = $inviteTable->fetchRow($inviteSelect);

      // No invite or already signed up
      if (!$inviteRow || $inviteRow->new_user_id) {
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }
    }

    return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
  }

  public function showDetailAction() {
    if (!$this->_helper->requireUser()->isValid())
      return;
    $this->view->creditDetail = Engine_Api::_()->getItem('sescredit_credit', $this->_getParam('id'));
  }

  public function showMemberLevelAction() {
    if (!$this->_helper->requireUser()->isValid())
      return;
    $this->view->levelInfo = Engine_Api::_()->getDbTable('levelpoints', 'sescredit')->getMemberLevel();
    if (!$this->getRequest()->isPost())
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $upgradeUserTable = Engine_Api::_()->getDbTable('upgradeusers', 'sescredit');
    $db = $upgradeUserTable->getAdapter();
    $db->beginTransaction();
    try {
      $upgradeUser = $upgradeUserTable->createRow();
      $upgradeUser->owner_id = $viewer->getIdentity();
      $upgradeUser->level_id = $_POST['level'];
      $upgradeUser->save();

      //Start Mail Send Work
      $usersTable = Engine_Api::_()->getDbtable('users', 'user');
      $usersSelect = $usersTable->select()
              ->where('level_id = ?', 1)
              ->where('enabled >= ?', 1);
      $superAdmins = $usersTable->fetchAll($usersSelect);
      foreach ($superAdmins as $superAdmin) {
        $adminEmails[$superAdmin->displayname] = $superAdmin->email;
      }
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($adminEmails, 'sescredit_send_upgrade_request', array('new_member_level' => Engine_Api::_()->getItem('authorization_level', $_POST['level'])->title, 'owner_title' => $viewer->getTitle()));
      //End Mail SendWork
      $db->commit();
      echo json_encode(array('status' => 'true'));
      die;
      // Redirect
    } catch (Exception $e) {
      $db->rollBack();
    }
  }
  function applyCreditAction(){
    $credit_value = $this->_getParam('credit_value',0);
    $item_amount = $this->_getParam('item_amount',0);
    $moduleName = $this->_getParam('moduleName','');
    $id = $this->_getParam('id','');
    $item_amount = str_replace(',','',$item_amount);
    $item_id = $this->_getParam('item_id',0);
    $creditCode =  'credit'.'-'.$moduleName.'-'.$id.'-'.$item_id;
    $sessionCredit = new Zend_Session_Namespace($creditCode);
    $session = new Zend_Session_Namespace('sescredit_redeem_purchase');
    $status = 0;
    $purchaseValueOfPoints = 0;
    $purchaseValue = 0;
    if(!empty($credit_value)){
      if($item_amount > 0){
          $response = Engine_Api::_()->sescredit()->validateCreditPurchase($moduleName,$item_amount,$credit_value);
          if($response['status']){
              $sessionCredit->value = $credit_value;
              //get purchase value of redeem points
              $creditvalue = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.creditvalue',0); 
              if($creditvalue){
                  $purchaseValueOfPoints = (1/$creditvalue) * $credit_value;
                  $purchaseValue = $sessionCredit->purchaseValue = $purchaseValueOfPoints;
                  $status = 1;
              }
          }
      }
    } 
    $sessionCredit->item_amount = $item_amount;
    $sessionCredit->credit_value = $credit_value;
    $sessionCredit->total_amount =  round(($item_amount-$purchaseValueOfPoints),2);
    echo json_encode(array('status'=>$status,'message'=>$session->error,'purchaseValue'=>Engine_Api::_()->sesbasic()->getCurrencyPrice(round($purchaseValue,2)),'value'=>$sessionCredit->value,'item_amount'=>Engine_Api::_()->sesbasic()->getCurrencyPrice(round($item_amount,2)),'total_amount'=>Engine_Api::_()->sesbasic()->getCurrencyPrice(round(($item_amount-$purchaseValueOfPoints),2))));die;
  }
}
