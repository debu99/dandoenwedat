<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Orderdetail.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_Orderdetail extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;
  protected $_modifiedTriggers = false;
  protected $_statusChanged;

  public function getTransaction() {
    return Engine_Api::_()->getItem('sescredit_transaction', $this->transaction_id);
  }

  public function onPaymentSuccess() {
    $this->_statusChanged = false;
    $transaction = $this->getTransaction();
    if ($transaction) {
      if (in_array($transaction->state, array('initial', 'trial', 'pending', 'active'))) {
        if ($transaction->state != 'active') {
          $transaction->state = 'active';
          $this->_statusChanged = true;
        }
        if (!$this->purchase_type) {
          $point = $this->point;
        } else {
          $point = Engine_Api::_()->getItem('sescredit_offer', $this->offer_id)->point;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $userId = $viewer->getIdentity();
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'sescredit_purchase', 'owner_id' => $userId, 'action_id' => 0, 'object_id' => $userId, 'point_type' => 'purchase', 'point' => $point));
        $transaction->save();
        $usersTable = Engine_Api::_()->getDbtable('users', 'user');
        $usersSelect = $usersTable->select()
                ->where('level_id = ?', 1)
                ->where('enabled >= ?', 1);
        $superAdmins = $usersTable->fetchAll($usersSelect);
        foreach ($superAdmins as $superAdmin) {
          $adminEmails[$superAdmin->displayname] = $superAdmin->email;
        }
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($adminEmails, 'sescredit_purchase_point', array('owner_title' => $viewer->displayname, 'point' => $point));
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($viewer, 'sescredit_purchased_point_success', array('point' => $point));
      }
    } 
    return $transaction;
  }

  public function onPaymentPending() {
    $this->_statusChanged = false;
    $transaction = $this->getTransaction();
    if ($transaction && ( in_array($transaction->state, array('initial', 'trial', 'pending', 'active')))) {
      //update all items in the transaction
      $this->changeApprovedStatus(0);
      // Change status
      if ($transaction->state != 'pending') {
        $transaction->state = 'pending';
        $this->_statusChanged = true;
        $transaction->save();
      }
    }
    return $this;
  }

  public function onPaymentFailure() {
    $this->_statusChanged = false;
    $transaction = $this->getTransaction();

    if ($transaction && in_array($transaction->state, array('initial', 'trial', 'pending', 'active', 'overdue'))) {
      //update all items in the transaction
      // Change status
      if ($transaction->state != 'overdue') {
        $transaction->state = 'overdue';
        $this->_statusChanged = true;
        $transaction->save();
      }
    }
    return $this;
  }

  public function onCancel() {
    $this->_statusChanged = false;
    $transaction = $this->getTransaction();
    if ($transaction && ( in_array($transaction->state, array('initial', 'trial', 'pending', 'active', 'overdue', 'cancelled', 'okay')) )) {
      //update all items in the transaction
      $this->changeApprovedStatus(0);
      // Change status
      if ($transaction->state != 'cancelled') {
        $transaction->state = 'cancelled';
        $this->_statusChanged = true;
        $transaction->save();
      }
    }
    return $this;
  }

}
