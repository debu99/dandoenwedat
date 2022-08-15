<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Epaytm
 * @package    Epaytm
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Paytm.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
include_once APPLICATION_PATH . "/application/modules/Epaytm/Api/PaytmKit/lib/encdec_paytm.php";
class Sescredit_Plugin_Gateway_Paytm extends Engine_Payment_Plugin_Abstract
{
  protected $_gatewayInfo;
  protected $_gateway;
  public function __construct(Zend_Db_Table_Row_Abstract $gatewayInfo)
  {
    $this->_gatewayInfo = $gatewayInfo;
  }
  public function getService()
  {
    return $this->getGateway()->getService();
  }
  public function getGateway()
  {
    if( null === $this->_gateway ) {
        $class = 'Epaytm_Gateways_Paytm';
        Engine_Loader::loadClass($class);
        $gateway = new $class(array(
        'config' => (array) $this->_gatewayInfo->config,
        'testMode' => $this->_gatewayInfo->test_mode,
        'currency' => Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD'),
      ));
      if( !($gateway instanceof Engine_Payment_Gateway) ) {
        throw new Engine_Exception('Plugin class not instance of Engine_Payment_Gateway');
      }
      $this->_gateway = $gateway;
    }
    return $this->_gateway;
  }
  public function createTransaction(array $params)
  {
    $transaction = new Engine_Payment_Transaction($params);
    $transaction->process($this->getGateway());
    return $transaction;
  }
  public function createIpn(array $params)
  {
    $ipn = new Engine_Payment_Ipn($params);
    $ipn->process($this->getGateway());
    return $ipn;
  }
  public function createSubscriptionTransaction(User_Model_User $user, Zend_Db_Table_Row_Abstract $subscription, Payment_Model_Package $package, array $params = array()) {
  }
  public function createPageTransaction(User_Model_User $user, array $params = array()) {
    $paytmParams  = array(
      /* Find your MID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
      "MID" => $this->_gatewayInfo->config['paytm_marchant_id'],
      /* Find your WEBSITE in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
      "WEBSITE" => $this->_gatewayInfo->config['paytm_website'],
      /* Find your INDUSTRY_TYPE_ID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
      "INDUSTRY_TYPE_ID" => $this->_gatewayInfo->config['paytm_industry_type'],
      /* WEB for website and WAP for Mobile-websites or App */
      "CHANNEL_ID" => $this->_gatewayInfo->config['paytm_channel_id'],
      /* Enter your unique order id */
      "ORDER_ID" => $params['vendor_order_id'],
      /* unique id that belongs to your customer */
      "CUST_ID" => $user->getIdentity(),
      /* customer's mobile number */
      /**
      * Amount in INR that is payble by customer
      * this should be numeric with optionally having two decimal points
      */
      "TXN_AMOUNT" =>  $params['amount'],
      /* on completion of transaction, we will send you the response on this URL */
      "CALLBACK_URL" => $params['return_url'],
    );
    return $paytmParams;
    // Create transaction
  }
  public function onSubscriptionReturn(
      Payment_Model_Order $order,$transaction)
  {}
 public function onPageTransactionReturn(
  Payment_Model_Order $order, array $params = array()) {
    // Check that gateways match
    if ($order->gateway_id != $this->_gatewayInfo->gateway_id) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
    // Get related info
    $user = $order->getUser();
    $item = $order->getSource();
    // Check for cancel state - the user cancelled the transaction
    if ($params['state'] == 'cancel') {
      // Cancel order and item
      $order->onCancel();
      $item->onPaymentFailure();
      // Error
      throw new Payment_Model_Exception('Your payment has been cancelled and ' .
      'not been charged. If this is not correct, please try again later.');
    }
    // Get payment state
    $paymentStatus = null;
    $orderStatus = null;

    switch($params["STATUS"]) {
          case 'created':
          case 'pending':
              $paymentStatus = 'pending';
              $orderStatus = 'complete';
          break;
          case "TXN_SUCCESS":
              $paymentStatus = 'okay';
              $orderStatus = 'complete';
          break;
          case 'denied':
          case "TXN_FAILURE": 
            $paymentStatus = 'okay';
            $orderStatus = 'failed'; 
          break;
          case 'voided':
          case 'reversed':
          case 'refunded':
          case 'expired':
          default:
              $paymentStatus = $params["STATUS"];
              $orderStatus = $params["STATUS"]; // This should probably be 'failed'
          break;
    }
    // Update order with profile info and complete status?
    $order->state = $orderStatus;
    $order->gateway_transaction_id = isset($params['TXNID']) ? $params['TXNID'] : '';
    $order->save();
    $session = new Zend_Session_Namespace('Payment_Sescredit');
    $currency = $session->currency;
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'sescredit');
    $transactionsTable->insert(array(
        'owner_id' => $order->user_id,
        'gateway_id' => $this->_gatewayInfo->gateway_id,
        'gateway_transaction_id' => isset($params['TXNID']) ? $params['TXNID'] : '',
        'creation_date' => new Zend_Db_Expr('NOW()'),
        'modified_date' => new Zend_Db_Expr('NOW()'),
        'order_id' => $order->order_id,
        'state' => 'initial',
        'gateway_type' => 'Paytm',
        'total_amount' => $params['TXNAMOUNT'],
        'currency_symbol' => $params['CURRENCY'],
        'ip_address' => $_SERVER['REMOTE_ADDR'],
    ));
    $transaction_id = $transactionsTable->getAdapter()->lastInsertId();
    $item->transaction_id = $transaction_id;
    $item->save();
    $transaction = Engine_Api::_()->getItem('sescredit_transaction', $transaction_id);
    // Get benefit setting
    $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'sescredit')
            ->getBenefitStatus($user);
    //For Coupon
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')){
      $transaction->ordercoupon_id = Engine_Api::_()->ecoupon()->setAppliedCouponDetails($params['couponSessionCode']);
    }
    // Check payment status
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
  public function onSubscriptionTransactionIpn(
      Payment_Model_Order $order,
      Engine_Payment_Ipn $ipn)
  {
  }
  public function onSubscriptionTransactionReturn(Payment_Model_Order $order,array $params = array()){}
  public function cancelSubscription($transactionId, $note = null)
  {}

  /**
   * Generate href to a page detailing the order
   *
   * @param string $transactionId
   * @return string
   */
  public function getOrderDetailLink($orderId)
  {
    if( $this->getGateway()->getTestMode() ) {
      // Note: it doesn't work in test mode
      return 'https://dashboard.paytm.com/next/transactions';
    } else {
      return 'https://dashboard.paytm.com/next/transactions';
    }
  }

  public function getTransactionDetailLink($transactionId)
  {
    if( $this->getGateway()->getTestMode() ) {
      // Note: it doesn't work in test mode
      return 'https://dashboard.paytm.com/next/transactions';
    } else {
      return 'https://dashboard.paytm.com/next/transactions';
    }
  }

  public function getOrderDetails($orderId)
  {
    try {
      return $this->getService()->detailRecurringPaymentsProfile($orderId);
    } catch( Exception $e ) {
      echo $e;
    }

    try {
      return $this->getTransactionDetails($orderId);
    } catch( Exception $e ) {
      echo $e;
    }

    return false;
  }

  public function getTransactionDetails($transactionId)
  {
    return $this->getService()->detailTransaction($transactionId);
  }
  public function createOrderTransactionReturn($order,$transaction) {  
    $user = $order->getUser();
    return 'active';
  }
  function getSupportedCurrencies(){ 
      return array('INR'=>'INR');
 }
  public function getAdminGatewayForm(){
    return new Epaytm_Form_Admin_Settings_Paytm();
  }

  public function processAdminGatewayForm(array $values){
    return $values;
  }
  public function getGatewayUrl(){
  }
  function getSupportedBillingCycles(){ 
    return array(0=>'Day',2=>'Month',3=>'Year');
  }

  // IPN

  /**
   * Process an IPN
   *
   * @param Engine_Payment_Ipn $ipn
   * @return Engine_Payment_Plugin_Abstract
   */
   public function onIpn(Engine_Payment_Ipn $ipn)
  {
  }
  public function cancelResourcePackage($transactionId, $note = null) {
  }
  public function cancelSubscriptionOnExpiry($source, $package) {}
  public function onIpnTransaction($rawData){
  }
  public function onTransactionIpn(Payment_Model_Order $order,  $rawData) {  }
  function setConfig(){}
  function test(){}
}
