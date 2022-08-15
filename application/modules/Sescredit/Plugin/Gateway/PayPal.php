<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: PayPal.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Plugin_Gateway_PayPal extends Engine_Payment_Plugin_Abstract {

  protected $_gatewayInfo;
  protected $_gateway;

  // General

  /**
   * Constructor
   */
  public function __construct(Zend_Db_Table_Row_Abstract $gatewayInfo) {
    $this->_gatewayInfo = $gatewayInfo;
    // @todo
    // https://www.sandbox.paypal.com/us/cgi-bin/webscr?cmd=_profile-recurring-payments&encrypted_profile_id=
  }

  /**
   * Get the service API
   *
   * @return Engine_Service_PayPal
   */
  public function getService() {
    return $this->getGateway()->getService();
  }

  /**
   * Get the gateway object
   *
   * @return Engine_Payment_Gateway
   */
  public function getGateway() {
    if (null === $this->_gateway) {
      $class = 'Engine_Payment_Gateway_PayPal';
      Engine_Loader::loadClass($class);
      $gateway = new $class(array(
          'config' => (array) $this->_gatewayInfo->config,
          'testMode' => $this->_gatewayInfo->test_mode,
          'currency' => Engine_Api::_()->sescredit()->getCurrentCurrency(),
      ));
      if (!($gateway instanceof Engine_Payment_Gateway)) {
        throw new Engine_Exception('Plugin class not instance of Engine_Payment_Gateway');
      }
      $this->_gateway = $gateway;
    }

    return $this->_gateway;
  }

  // Actions

  /**
   * Create a transaction object from specified parameters
   *
   * @return Engine_Payment_Transaction
   */
  public function createTransaction(array $params) {
    $transaction = new Engine_Payment_Transaction($params);
    $transaction->process($this->getGateway());
    return $transaction;
  }

  /**
   * Create an ipn object from specified parameters
   *
   * @return Engine_Payment_Ipn
   */
  public function createIpn(array $params) {
    $ipn = new Engine_Payment_Ipn($params);
    $ipn->process($this->getGateway());
    return $ipn;
  }

  // SEv4 Specific

  /**
   * Create a transaction for a subscription
   *
   * @param User_Model_User $user
   * @param Zend_Db_Table_Row_Abstract $subscription
   * @param Zend_Db_Table_Row_Abstract $package
   * @param array $params
   * @return Engine_Payment_Gateway_Transaction
   */
  public function createSubscriptionTransaction(User_Model_User $user, Zend_Db_Table_Row_Abstract $subscription, Payment_Model_Package $package, array $params = array()) {

  }

  public function createPageTransaction(User_Model_User $user, array $params = array()) {

    $currentCurrency = Engine_Api::_()->sescredit()->getCurrentCurrency();
    $defaultCurrency = Engine_Api::_()->sescredit()->defaultCurrency();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $currencyValue = 1;
    if ($currentCurrency != $defaultCurrency) {
      $currencyValue = $settings->getSetting('sesmultiplecurrency.' . $currentCurrency);
    }
    $orderId = $params['vendor_order_id'];
    $orderTable = Engine_Api::_()->getDbTable('orders', 'payment');
    $sourceId = $orderTable->select()->from($orderTable->info('name'), 'source_id')->where('order_id =?', $orderId)->query()->fetchColumn();
    $orderDetailTable = Engine_Api::_()->getDbTable('orderdetails', 'sescredit');
    $select = $orderDetailTable->select()->from($orderDetailTable->info('name'), array('*'))->where('orderdetail_id =?', $sourceId);
    $orderDetail = $orderDetailTable->fetchRow($select);
    if ($orderDetail->purchase_type == 1) {
      $price = Engine_Api::_()->getItem('sescredit_offer', $orderDetail->offer_id)->point_value;
    } else {
      $price = $orderDetail->point / Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.creditvalue', '100');
    }
    $price = @round(($price * $currencyValue), 2);
    // This is a one-time fee
    $params['driverSpecificParams']['PayPal'] = array(
        'AMT' => $price,
        'INVNUM' => $params['vendor_order_id'],
        'ITEMAMT' => $price,
        'ITEMS' => array(
            array(
                'AMT' => $price,
            ),
        ),
    );
    // Should fix some issues with GiroPay
    if (!empty($params['return_url'])) {
      $params['driverSpecificParams']['PayPal']['GIROPAYSUCCESSURL'] = $params['return_url']
              . ( false === strpos($params['return_url'], '?') ? '?' : '&' ) . 'giropay=1';
      $params['driverSpecificParams']['PayPal']['BANKTXNPENDINGURL'] = $params['return_url']
              . ( false === strpos($params['return_url'], '?') ? '?' : '&' ) . 'giropay=1';
    }
    if (!empty($params['cancel_url'])) {
      $params['driverSpecificParams']['PayPal']['GIROPAYCANCELURL'] = $params['cancel_url']
              . ( false === strpos($params['return_url'], '?') ? '?' : '&' ) . 'giropay=1';
    }
    // This is a recurring subscription
    // Create transaction
    $transaction = $this->createTransaction($params);
    return $transaction;
  }

  /**
   * Process return of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param array $params
   */
  public function onSubscriptionTransactionReturn(
  Payment_Model_Order $order, array $params = array()) {

  }

  public function onPageTransactionReturn(
  Payment_Model_Order $order, array $params = array()) {
    // Check that gateways match
    if ($order->gateway_id != $this->_gatewayInfo->gateway_id) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
    // Get related info
    $user = $order->getUser();
    $item = $order->getSource();

    $transaction = $item->getTransaction();
    // Check subscription state
    if ($transaction && ($transaction->state == 'trial')) {
      return 'active';
    } else if ($transaction && $transaction->state == 'pending') {
      return 'pending';
    }

    // Check for cancel state - the user cancelled the transaction
    if ($params['state'] == 'cancel') {
      // Cancel order and item
      $order->onCancel();
      $item->onPaymentFailure();
      // Error
      throw new Payment_Model_Exception('Your payment has been cancelled and ' .
      'not been charged. If this is not correct, please try again later.');
    }

    // Check params
    if (empty($params['token'])) {
      // Cancel order and subscription?
      $order->onFailure();
      $item->onPaymentFailure();
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
      throw new Payment_Model_Exception('There was an error processing your ' .
      'transaction. Please try again later.');
    }

    // Get details
    try {
      $data = $this->getService()->detailExpressCheckout($params['token']);
    } catch (Exception $e) {
      // Cancel order and subscription?
      $order->onFailure();
      $item->onPaymentFailure();
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
      throw new Payment_Model_Exception('There was an error processing your ' .
      'transaction. Please try again later.');
    }

    // Let's log it
    $this->getGateway()->getLog()->log('ExpressCheckoutDetail: '
            . print_r($data, true), Zend_Log::INFO);

    // Do payment
    try {
      $rdata = $this->getService()->doExpressCheckoutPayment($params['token'], $params['PayerID'], array(
          'PAYMENTACTION' => 'Sale',
          'AMT' => $data['AMT'],
          'CURRENCYCODE' => $this->getGateway()->getCurrency(),
      ));
    } catch (Exception $e) {
      // Log the error
      $this->getGateway()->getLog()->log('DoExpressCheckoutPaymentError: '
              . $e->__toString(), Zend_Log::ERR);

      // Cancel order and subscription?
      $order->onFailure();
      $item->onPaymentFailure();
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
      throw new Payment_Model_Exception('There was an error processing your ' .
      'transaction. Please try again later.');
    }

    // Let's log it
    $this->getGateway()->getLog()->log('DoExpressCheckoutPayment: '
            . print_r($rdata, true), Zend_Log::INFO);

    // Get payment state
    $paymentStatus = null;
    $orderStatus = null;

    switch (strtolower($rdata['PAYMENTINFO'][0]['PAYMENTSTATUS'])) {
      case 'created':
      case 'pending':
        $paymentStatus = 'pending';
        $orderStatus = 'complete';
        break;

      case 'completed':
      case 'processed':
      case 'canceled_reversal': // Probably doesn't apply
        $paymentStatus = 'active';
        $orderStatus = 'complete';
        break;

      case 'denied':
      case 'failed':
      case 'voided': // Probably doesn't apply
      case 'reversed': // Probably doesn't apply
      case 'refunded': // Probably doesn't apply
      case 'expired':  // Probably doesn't apply
      default: // No idea what's going on here
        $paymentStatus = 'failed';
        $orderStatus = 'failed'; // This should probably be 'failed'
        break;
    }
    // Update order with profile info and complete status?
    $order->state = $orderStatus;
    $order->gateway_transaction_id = $rdata['PAYMENTINFO'][0]['TRANSACTIONID'];
    $order->save();

    $session = new Zend_Session_Namespace('Payment_Sescredit');
    $currency = $session->currency;

    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'sescredit');
    $transactionsTable->insert(array(
        'owner_id' => $order->user_id,
        'gateway_id' => $this->_gatewayInfo->gateway_id,
        'gateway_transaction_id' => $rdata['PAYMENTINFO'][0]['TRANSACTIONID'],
        'creation_date' => new Zend_Db_Expr('NOW()'),
        'modified_date' => new Zend_Db_Expr('NOW()'),
        'order_id' => $order->order_id,
        'state' => 'initial',
        'gateway_type' => 'Paypal',
        'currency_symbol' => $currency,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
    ));
    $transaction_id = $transactionsTable->getAdapter()->lastInsertId();
    $item->transaction_id = $transaction_id;
    $item->save();
    $transaction = Engine_Api::_()->getItem('sescredit_transaction', $transaction_id);
    // Get benefit setting
    $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'sescredit')
            ->getBenefitStatus($user);
    // Check payment status
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')){
      $transaction->ordercoupon_id = Engine_Api::_()->ecoupon()->setAppliedCouponDetails($params['couponSessionCode']);
    }
    if ($paymentStatus == 'okay' || $paymentStatus == 'active' ||
            ($paymentStatus == 'pending' && $giveBenefit)) {
      //Update subscription info
      $transaction->gateway_id = $this->_gatewayInfo->gateway_id;
      $transaction->save();
      // Payment success
      $transaction = $item->onPaymentSuccess();
      return 'active';
    } else if ($paymentStatus == 'pending') {
      // Update subscription info
      $transaction->gateway_id = $this->_gatewayInfo->gateway_id;
      $transaction->save();
      // Payment pending
      $item->onPaymentPending();
      return 'pending';
    } else if ($paymentStatus == 'failed') {
      // Cancel order and subscription?
      $order->onFailure();
      $item->onPaymentFailure();
      //Send to user for refunded
      if (strtolower($rdata['PAYMENTINFO'][0]['PAYMENTSTATUS']) == 'refunded') {

      }
      // Payment failed
      throw new Payment_Model_Exception('Your payment could not be ' .
      'completed. Please ensure there are sufficient available funds ' .
      'in your account.');
    } else {
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
      throw new Payment_Model_Exception('There was an error processing your ' .
      'transaction. Please try again later.');
    }
  }

  /**
   * Process ipn of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  public function onSubscriptionTransactionIpn(
  Payment_Model_Order $order, Engine_Payment_Ipn $ipn) {

  }

  public function onPageTransactionIpn(
  Payment_Model_Order $order, Engine_Payment_Ipn $ipn) {

    // Check that gateways match
    if ($order->gateway_id != $this->_gatewayInfo->gateway_id) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
    // Get related info
    $user = $order->getUser();
    $item = $order->getSource();
    $package = $item->getPackage();
    $transaction = $item->getTransaction();
    // Get IPN data
    $rawData = $ipn->getRawData();
    // Let's log it
    // Get tx table
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'sescredit');

    // Chargeback --------------------------------------------------------------
    if (!empty($rawData['case_type']) && $rawData['case_type'] == 'chargeback') {
      $item->onPaymentFailure(); // or should we use pending?
    }

    // Transaction Type --------------------------------------------------------
    else if (!empty($rawData['txn_type'])) {
      switch ($rawData['txn_type']) {

        // @todo see if the following types need to be processed:
        // — adjustment express_checkout new_case

        case 'express_checkout':
          // Only allowed for one-time
          if ($package->isOneTime()) {
            switch ($rawData['payment_status']) {

              case 'Created': // Not sure about this one
              case 'Pending':
                // @todo this might be redundant
                // Get benefit setting
                $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'sescredit')->getBenefitStatus($user);
                if ($giveBenefit) {
                  $item->onPaymentSuccess();
                } else {
                  $item->onPaymentPending();
                }
                break;
              case 'Completed':
              case 'Processed':
              case 'Canceled_Reversal': // Not sure about this one
                $item->onPaymentSuccess();
                break;
              case 'Denied':
              case 'Failed':
              case 'Voided':
              case 'Reversed':
                $item->onPaymentFailure();
                break;
              case 'Refunded':
                $item->onRefund();
                break;
              case 'Expired': // Not sure about this one
                $item->onExpiration();
                break;
              default:
                throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
                        'payment status %1$s', $rawData['payment_status']));
                break;
            }
          }
          break;

        // Recurring payment was received
        case 'recurring_payment':
          if (!$package->isOneTime()) {
            $item->onPaymentSuccess();
          }
          break;

        // Profile was created
        case 'recurring_payment_profile_created':
          if ((!empty($rawData['initial_payment_status']) &&
                  $rawData['initial_payment_status'] == 'Completed') ||
                  (!empty($rawData['profile_status']) &&
                  $rawData['profile_status'] == 'Active')) {
            //$subscription->active = true;
            $item->onPaymentSuccess();
          } else {
            throw new Engine_Payment_Plugin_Exception(sprintf('Unknown or missing ' .
                    'initial_payment_status %1$s', @$rawData['initial_payment_status']));
          }
          break;
        // Profile was cancelled
        case 'recurring_payment_profile_cancel':
          $item->onCancel();
          break;
        // Recurring payment expired
        case 'recurring_payment_expired':
          if (!$package->isOneTime()) {
            $item->onExpiration();
          }
          break;

        // Recurring payment failed
        case 'recurring_payment_skipped':
        case 'recurring_payment_suspended_due_to_max_failed_payment':
        case 'recurring_payment_outstanding_payment_failed':
        case 'recurring_payment_outstanding_payment':
          $item->onPaymentFailure();
          break;
        case 'recurring_payment_suspended':
          $item->onPaymentFailure();
          break;
        // What is this?
        default:
          throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
                  'type %1$s', $rawData['txn_type']));
          break;
      }
    }

    // Payment Status ----------------------------------------------------------
    else if (!empty($rawData['payment_status'])) {
      switch ($rawData['payment_status']) {

        case 'Created': // Not sure about this one
        case 'Pending':
          // Get benefit setting
          $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'sescredit')->getBenefitStatus($user);
          if ($giveBenefit) {
            $item->onPaymentSuccess();
          } else {
            $item->onPaymentPending();
          }
          break;
        case 'Completed':
        case 'Processed':
        case 'Canceled_Reversal': // Not sure about this one
          $item->onPaymentSuccess();
          break;
        case 'Denied':
        case 'Failed':
        case 'Voided':
        case 'Reversed':
          $item->onPaymentFailure();
          break;
        case 'Refunded':
          $item->onRefund();
          break;

        case 'Expired': // Not sure about this one
          $item->onExpiration();
          break;

        default:
          throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
                  'payment status %1$s', $rawData['payment_status']));
          break;
      }
    }

    // Unknown -----------------------------------------------------------------
    else {
      throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
              'data structure'));
    }

    return $this;
  }

  /**
   * Cancel a subscription (i.e. disable the recurring payment profile)
   *
   * @params $transactionId
   * @return Engine_Payment_Plugin_Abstract
   */
  public function cancelSubscription($transactionId, $note = null) {

  }

  public function cancelPage($transactionId, $note = null) {
    $profileId = null;
    if ($transactionId instanceof Sescredit_Model_Transaction) {
      $profileId = $transactionId->gateway_profile_id;
    } else if (is_string($transactionId)) {
      $profileId = $transactionId;
    } else {
      // Should we throw?
      return $this;
    }

    try {
      $r = $this->getService()->cancelRecurringPaymentsProfile($profileId, $note);
    } catch (Exception $e) {
      // throw?
    }

    return $this;
  }

  /**
   * Generate href to a page detailing the order
   *
   * @param string $transactionId
   * @return string
   */
  public function getOrderDetailLink($orderId) {
    // @todo make sure this is correct
    // I don't think this works
    if ($this->getGateway()->getTestMode()) {
      // Note: it doesn't work in test mode
      return 'https://www.sandbox.paypal.com/vst/?id=' . $orderId;
    } else {
      return 'https://www.paypal.com/vst/?id=' . $orderId;
    }
  }

  /**
   * Generate href to a page detailing the transaction
   *
   * @param string $transactionId
   * @return string
   */
  public function getTransactionDetailLink($transactionId) {
    // @todo make sure this is correct
    if ($this->getGateway()->getTestMode()) {
      // Note: it doesn't work in test mode
      return 'https://www.sandbox.paypal.com/vst/?id=' . $transactionId;
    } else {
      return 'https://www.paypal.com/vst/?id=' . $transactionId;
    }
  }

  /**
   * Get raw data about an order or recurring payment profile
   *
   * @param string $orderId
   * @return array
   */
  public function getOrderDetails($orderId) {
    // We don't know if this is a recurring payment profile or a transaction id,
    // so try both
    try {
      return $this->getService()->detailRecurringPaymentsProfile($orderId);
    } catch (Exception $e) {
      echo $e;
    }

    try {
      return $this->getTransactionDetails($orderId);
    } catch (Exception $e) {
      echo $e;
    }

    return false;
  }

  /**
   * Get raw data about a transaction
   *
   * @param $transactionId
   * @return array
   */
  public function getTransactionDetails($transactionId) {
    return $this->getService()->detailTransaction($transactionId);
  }

  // IPN

  /**
   * Process an IPN
   *
   * @param Engine_Payment_Ipn $ipn
   * @return Engine_Payment_Plugin_Abstract
   */
  public function onIpn(Engine_Payment_Ipn $ipn) {
    $rawData = $ipn->getRawData();

    $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'sescredit');


    // Find transactions -------------------------------------------------------
    $transactionId = null;
    $parentTransactionId = null;
    $transaction = null;
    $parentTransaction = null;

    // Fetch by txn_id
    if (!empty($rawData['txn_id'])) {
      $transaction = $transactionsTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_transaction_id = ?' => $rawData['txn_id'],
      ));

      if (!$transaction && !empty($rawData['recurring_payment_id'])) {
        $transaction = $transactionsTable->fetchRow(array(
            'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
            'gateway_profile_id = ?' => $rawData['recurring_payment_id'],
        ));
      }
      $parentTransaction = $transactionsTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_parent_transaction_id = ?' => $rawData['txn_id'],
      ));
    }
    // Fetch by parent_txn_id
    if (!empty($rawData['parent_txn_id'])) {
      if (!$transaction) {
        $parentTransaction = $transactionsTable->fetchRow(array(
            'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
            'gateway_parent_transaction_id = ?' => $rawData['parent_txn_id'],
        ));
      }
      if (!$parentTransaction) {
        $parentTransaction = $transactionsTable->fetchRow(array(
            'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
            'gateway_transaction_id = ?' => $rawData['parent_txn_id'],
        ));
      }
    }
    // Fetch by transaction->gateway_parent_transaction_id
    if ($transaction && !$parentTransaction &&
            !empty($transaction->gateway_parent_transaction_id)) {
      $parentTransaction = $transactionsTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_parent_transaction_id = ?' => $transaction->gateway_parent_transaction_id,
      ));
    }
    // Fetch by parentTransaction->gateway_transaction_id
    if ($parentTransaction && !$transaction &&
            !empty($parentTransaction->gateway_transaction_id)) {
      $transaction = $transactionsTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_parent_transaction_id = ?' => $parentTransaction->gateway_transaction_id,
      ));
    }
    // Get transaction id
    if ($transaction) {
      $transactionId = $transaction->gateway_transaction_id;
    } else if (!empty($rawData['txn_id'])) {
      $transactionId = $rawData['txn_id'];
    }
    // Get parent transaction id
    if ($parentTransaction) {
      $parentTransactionId = $parentTransaction->gateway_transaction_id;
    } else if ($transaction && !empty($transaction->gateway_parent_transaction_id)) {
      $parentTransactionId = $transaction->gateway_parent_transaction_id;
    } else if (!empty($rawData['parent_txn_id'])) {
      $parentTransactionId = $rawData['parent_txn_id'];
    }



    // Fetch order -------------------------------------------------------------
    $order = null;

    // Transaction IPN - get order by invoice
    if (!$order && !empty($rawData['invoice'])) {
      $order = $ordersTable->find($rawData['invoice'])->current();
    }

    // Subscription IPN - get order by recurring_payment_id
    if (!$order && !empty($rawData['recurring_payment_id'])) {
      // Get attached order
      $order = $ordersTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_order_id = ?' => $rawData['recurring_payment_id'],
      ));
    }

    // Subscription IPN - get order by rp_invoice_id
    //if( !$order && !empty($rawData['rp_invoice_id']) ) {
    //
    //}
    // Transaction IPN - get order by parent_txn_id
    if (!$order && $parentTransactionId) {
      $order = $ordersTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_transaction_id = ?' => $parentTransactionId,
      ));
    }

    // Transaction IPN - get order by txn_id
    if (!$order && $transactionId) {
      $order = $ordersTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_transaction_id = ?' => $transactionId,
      ));
    }

    // Transaction IPN - get order through transaction
    if (!$order && !empty($transaction->order_id)) {
      $order = $ordersTable->find($parentTransaction->order_id)->current();
    }

    // Transaction IPN - get order through parent transaction
    if (!$order && !empty($parentTransaction->order_id)) {
      $order = $ordersTable->find($parentTransaction->order_id)->current();
    }



    // Process generic IPN data ------------------------------------------------
    // Build transaction info
    if (!empty($rawData['txn_id'])) {
      $transactionData = array(
          'gateway_id' => $this->_gatewayInfo->gateway_id,
      );
      // Get timestamp
      if (!empty($rawData['payment_date'])) {
        $transactionData['creation_date'] = date('Y-m-d H:i:s', strtotime($rawData['payment_date']));
      } else {
        $transactionData['creation_date'] = new Zend_Db_Expr('NOW()');
      }
      // Get amount
      if (!empty($rawData['mc_gross'])) {
        $transactionData['total_amount'] = $rawData['mc_gross'];
      }
      // Get currency
      if (!empty($rawData['mc_currency'])) {
        $transactionData['currency_symbol'] = $rawData['mc_currency'];
      }
      // Get order/user
      if ($order) {
        $transactionData['owner_id'] = $order->user_id;
        $transactionData['order_id'] = $order->order_id;
      }
      // Get transactions
      if ($transactionId) {
        $transactionData['gateway_transaction_id'] = $transactionId;
      }
      if ($parentTransactionId) {
        $transactionData['gateway_parent_transaction_id'] = $parentTransactionId;
      }
      // Get payment_status
      switch ($rawData['payment_status']) {
        case 'Canceled_Reversal': // @todo make sure this works

        case 'Completed':
        case 'Created':
        case 'Processed':
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'active';
          break;

        case 'Denied':
        case 'Expired':
        case 'Failed':
        case 'Voided':
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'failed';
          break;

        case 'Pending':
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'pending';
          break;

        case 'Refunded':
          $transactionData['type'] = 'refund';
          $transactionData['state'] = 'refunded';
          break;
        case 'Reversed':
          $transactionData['type'] = 'reversal';
          $transactionData['state'] = 'reversed';
          break;

        default:
          $transactionData = 'unknown';
          break;
      }

      // Insert new transaction
      if (!$transaction) {
        $transactionsTable->insert($transactionData);
      }
      // Update transaction
      else {
        unset($transactionData['timestamp']);
        //$transaction->setFromArray($transactionData);
        $transaction->save();
      }

      // Update parent transaction on refund?
      if ($parentTransaction && in_array($transactionData['type'], array('refund', 'reversal'))) {
        $parentTransaction->state = $transactionData['state'];
        $parentTransaction->save();
      }
    }


    // Process specific IPN data -----------------------------------------------
    if ($order) {
      $ipnProcessed = false;
      // Subscription IPN
      if ($order->source_type == 'sescredit') {
        $this->onPageTransactionIpn($order, $ipn);
        $ipnProcessed = true;
      }
      // Unknown IPN - could not be processed
      if (!$ipnProcessed) {
        throw new Engine_Payment_Plugin_Exception('Unknown order type for IPN');
      }
    }
    // Missing order
    else {
      throw new Engine_Payment_Plugin_Exception('Unknown or unsupported IPN ' .
      'type, or missing transaction or order ID');
    }

    return $this;
  }

  // Forms

  /**
   * Get the admin form for editing the gateway info
   *
   * @return Engine_Form
   */
  public function getAdminGatewayForm() {
    return new Payment_Form_Admin_Gateway_PayPal();
  }

  public function processAdminGatewayForm(array $values) {
    return $values;
  }

}
