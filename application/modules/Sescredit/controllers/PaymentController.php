<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: PaymentController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_PaymentController extends Core_Controller_Action_Standard {

  /**
   * @var User_Model_User
   */
  protected $_user;

  /**
   * @var Zend_Session_Namespace
   */
  protected $_session;

  /**
   * @var Payment_Model_Order
   */
  protected $_order;

  /**
   * @var Payment_Model_Gateway
   */
  protected $_gateway;

  public function init() {
    // If there are no enabled gateways or packages, disable
    if (Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sescredit_general', true);
    }

    // Get user and session
    $this->_user = Engine_Api::_()->user()->getViewer();
    $this->_session = new Zend_Session_Namespace('Payment_Credit');
    $this->_session->gateway_id = $this->_getParam('gateway_id', 0);

    // Check viewer and user
    if (!$this->_user || !$this->_user->getIdentity()) {
      if (!empty($this->_session->user_id)) {
        $this->_user = Engine_Api::_()->getItem('user', $this->_session->user_id);
      }
      // If no user, redirect to home?
      if (!$this->_user || !$this->_user->getIdentity()) {
        $this->_session->unsetAll();
        return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sescredit_general', true);
      }
    }
  }

  public function indexAction() {
    return $this->_forward('gateway');
  }

  public function processAction() {
      if(!empty($_GET['sesapi_credit'])) {
          $_POST['sescredit_purchase_type'] = $_GET['sescredit_purchase_type'];
          $_POST['sescredit_number_point'] = $_GET['sescredit_number_point'];
          $_POST['sescredit_site_offers'] = $_GET['sescredit_site_offers'];
          $_POST['sescredit_purchase_type'] = $_GET['sescredit_purchase_type'];
      }
    // Get gateway
    $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);

    if ($_POST['sescredit_purchase_type']) {
      $options = Engine_Api::_()->getDbTable('offers', 'sescredit')->getOffer(array('offer_id' => $_POST['sescredit_number_point']));
      if (count($options) < 1) {
        return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sescredit_general', true);
      }
    }

    if (!$gatewayId ||
            !($gateway = Engine_Api::_()->getDbtable('gateways', 'sescredit')->find($gatewayId)->current()) ||
            !($gateway->enabled)) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'gateway'));
    }
    $this->view->gateway = $gateway;
    // For Coupon 
    $couponSessionCode = '-'.'-sescredit-'.'-0';
    
    // Process
    // Create order
    $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
    if (!empty($this->_session->order_id)) {
      $previousOrder = $ordersTable->find($this->_session->order_id)->current();
      if ($previousOrder && $previousOrder->state == 'pending') {
        $previousOrder->state = 'incomplete';
        $previousOrder->save();
      }
    }

    $orderDetailTable = Engine_Api::_()->getDbTable('orderdetails', 'sescredit');
    $db = $orderDetailTable->getAdapter();
    $db->beginTransaction();
    try {
      $orderDetail = $orderDetailTable->createRow();
      $orderDetail->purchase_type = $_POST['sescredit_purchase_type'];
      $orderDetail->point = $_POST['sescredit_number_point'];
      $orderDetail->offer_id = $_POST['sescredit_site_offers'];
      $orderDetail->save();
      // Commit
      $db->commit();
      $orderDetailId = $orderDetail->orderdetail_id;
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $ordersTable->insert(array(
        'user_id' => $this->_user->getIdentity(),
        'gateway_id' => $gateway->gateway_id,
        'state' => 'pending',
        'creation_date' => new Zend_Db_Expr('NOW()'),
        'source_type' => 'sescredit_orderdetail',
        'source_id' => $orderDetailId,
    ));
    $this->_session->order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
    $this->_session->currency = $currentCurrency = Engine_Api::_()->sescredit()->getCurrentCurrency();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->_session->change_rate = $settings->getSetting('sesmultiplecurrency.' . $currentCurrency);
    $gatewayType = Engine_Api::_()->getItem('payment_gateway',$gateway->gateway_id);
    // Unset certain keys
    unset($this->_session->package_id);
    unset($this->_session->gateway_id);
    // Get gateway plugin
    $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway();
    $plugin = $gateway->getPlugin();

    // Prepare host info
    $schema = 'http://';
    if (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) {
      $schema = 'https://';
    }
    $host = $_SERVER['HTTP_HOST'];

    // Prepare transaction
    $params = array();
    $params['language'] = $this->_user->language;
    $localeParts = explode('_', $this->_user->language);
    if (count($localeParts) > 1) {
      $params['region'] = $localeParts[1];
    }
    $params['vendor_order_id'] = $order_id;
    $params['return_url'] = $schema . $host
            . $this->view->url(array('action' => 'return'))
            . '?order_id=' . $order_id
            . '&state=' . 'return';
    $params['cancel_url'] = $schema . $host
            . $this->view->url(array('action' => 'return'))
            . '?order_id=' . $order_id
            . '&state=' . 'cancel';
    $params['ipn_url'] = $schema . $host . $this->view->url(array('action' => 'index', 'controller' => 'ipn', 'module' => 'sescredit'), 'default') . '?order_id=' . $order_id . '&gateway_id=' . $gatewayId;
    
    if ($orderDetail->purchase_type == 1) {
      $price = Engine_Api::_()->getItem('sescredit_offer', $orderDetail->offer_id)->point_value;
    } else {
      $price = $orderDetail->point / Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.creditvalue', '100');
    }
    $params['amount'] = @isset($_SESSION[$couponSessionCode]) ? round($price - $_SESSION[$couponSessionCode]['discount_amount']) : $price;
    $this->_session->amount = $params['amount'];
    if($gateway->plugin == "Sesadvpmnt_Plugin_Gateway_Stripe") {
          $this->view->currency = $currentCurrency;
          $this->view->publishKey = $gateway->config['sesadvpmnt_stripe_publish']; 
          $this->view->title = $gateway->config['sesadvpmnt_stripe_title'];
          $this->view->description = $gateway->config['sesadvpmnt_stripe_description'];
          $this->view->logo  = $gateway->config['sesadvpmnt_stripe_logo'];
          $this->view->returnUrl = $params['return_url'];
          $this->view->amount = $params['amount'];
          $this->renderScript('/application/modules/Sesadvpmnt/views/scripts/payment/index.tpl');
    } elseif($gateway->plugin  == "Epaytm_Plugin_Gateway_Paytm") {
        $paytmParams = $plugin->createPageTransaction($this->_user, $params);
        $secretKey  = $gateway->config['paytm_secret_key'];
        $this->view->paytmParams = $paytmParams;
        $this->view->checksum = getChecksumFromArray($paytmParams, $secretKey);
        if($gateway->test_mode){
          $this->view->url = "https://securegw-stage.paytm.in/order/process";
        } else {
          $this->view->url = "https://securegw.paytm.in/merchant-status/getTxnStatus";
        }
         $this->renderScript('/application/modules/Epaytm/views/scripts/payment/index.tpl');
    } else {
        // Process transaction
        $transaction = $plugin->createPageTransaction($this->_user, $params);
        // Pull transaction params
        $this->view->transactionUrl = $transactionUrl = $gatewayPlugin->getGatewayUrl();
        $this->view->transactionMethod = $transactionMethod = $gatewayPlugin->getGatewayMethod();
        $this->view->transactionData = $transactionData = $transaction->getData();
    }
    // Handle redirection
    if ($transactionMethod == 'GET') {
      $transactionUrl .= '?' . http_build_query($transactionData);
      return $this->_helper->redirector->gotoUrl($transactionUrl, array('prependBase' => false));
    }
  }

  public function returnAction() {
    // Get order
    if (!$this->_user ||
            !($orderId = $this->_getParam('order_id', $this->_session->order_id)) ||
            !($order = Engine_Api::_()->getItem('payment_order', $orderId)) ||
            $order->user_id != $this->_user->getIdentity() ||
            $order->source_type != 'sescredit_orderdetail' ||
            !($item = $order->getSource()) ||
            !($gateway = Engine_Api::_()->getDbtable('gateways', 'sescredit')->find($order->gateway_id)->current())) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sescredit_general', true);
    }

    // Get gateway plugin
    $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway();
    $plugin = $gateway->getPlugin();
    $params  = array();
    //For Coupon
    $couponSessionCode = '-'.'-sescredit-'.'-0';
    $params['amount'] = $this->_session->amount;
    $params['couponSessionCode'] = $couponSessionCode;
    // Process return
    unset($this->_session->errorMessage);
    try {
      if($gateway->plugin == "Sesadvpmnt_Plugin_Gateway_Stripe") {
          if(isset($_POST['stripeToken'])){
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $this->view->secretKey = $params['secretKey'] = $secretKey = $gateway->config['sesadvpmnt_stripe_secret'];
            \Stripe\Stripe::setApiKey($secretKey);
            $params['token'] = $_POST['stripeToken'];
            $params['currency'] = Engine_Api::_()->sescredit()->getCurrentCurrency();
            $params['gateway'] = $gateway;
            $params['order_id'] = $order->order_id;
            $params['type'] = "Payment_Credit";
            $transaction = $plugin->createPageTransaction($this->_user, $params);
          }
          $params['transaction'] = $transaction;
         $status = $plugin->onPageTransactionReturn($order,$params);
      } else {
        $status = $plugin->onPageTransactionReturn($order, array_merge($this->_getAllParams(),$params));
      }
    } catch (Payment_Model_Exception $e) {
      $status = 'failure';
      $this->_session->errorMessage = $e->getMessage();
    }
    return $this->_finishPayment($status);
  }

  public function finishAction() {
    $this->view->status = $status = $this->_getParam('state');
    $this->view->error = $this->_session->errorMessage;
  }

  protected function _finishPayment($state = 'active') {
    $viewer = Engine_Api::_()->user()->getViewer();
    $user = $this->_user;
    // No user?
    if (!$this->_user) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sescredit_general', true);
    }
    // Redirect
    if ($state == 'free') {
      return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sescredit_general', true);
    } else {
      return $this->_helper->redirector->gotoRoute(array('action' => 'finish', 'state' => $state));
    }
  }

}
