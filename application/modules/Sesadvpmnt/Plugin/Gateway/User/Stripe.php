<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Stripe.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
include_once APPLICATION_PATH . "/application/modules/Sesadvpmnt/Api/Stripe/init.php";
class Sesadvpmnt_Plugin_Gateway_User_Stripe extends Engine_Payment_Plugin_Abstract {

    protected $_gatewayInfo;
    protected $_gateway;

    // General
    /**
    * Constructor
    */
    public function __construct(Zend_Db_Table_Row_Abstract $gatewayInfo)
    {
        $this->_gatewayInfo = $gatewayInfo;

        // @todo
    }

    /**
    * Get the service API
    *
    * @return Engine_Service_PayPal
    */
    public function getService()
    {
        return $this->getGateway()->getService();
    }

    /**
    * Get the gateway object
    *
    * @return Engine_Payment_Gateway
    */
    public function getGateway()
    {
        if( null === $this->_gateway ) {
            $class = 'Sesadvpmnt_Gateways_Stripe';
            Engine_Loader::loadClass($class);
            $gateway = new $class(array(
                'config' => (array) $this->_gatewayInfo->config,
                'testMode' => $this->_gatewayInfo->test_mode,
            ));
            if( !($gateway instanceof Engine_Payment_Gateway) ) {
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
    public function createTransaction(array $params)
    {
        $transaction = new Engine_Payment_Transaction($params);
        $transaction->process($this->getGateway($params['moduleName']));
        return $transaction;
    }

    /**
    * Create an ipn object from specified parameters
    *
    * @return Engine_Payment_Ipn
    */
    public function createIpn(array $params)
    {
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
	public function createSubscriptionTransaction(User_Model_User $user, Zend_Db_Table_Row_Abstract $user_order, Payment_Model_Package $package, array $params = array()){}

    public function createOrderTransaction($param = array()) {
        $this->view->secretKey = $secretKey = $this->_gatewayInfo->config['sesadvpmnt_stripe_secret'];
        $gateway_id = $param['gateway_id'] ? $param['gateway_id'] : null;
           try {
                \Stripe\Stripe::setApiKey($secretKey);
                $transaction = \Stripe\Charge::create([
                    'amount' => $param['amount']*100,
                    'currency' => $param['currency'],
                    'source' => $param['token'],
                    'metadata' =>['order_id'=>$param['order_id'],'type'=>$param['type'],'gateway_id'=>$gateway_id]
                ]);
            } catch(\Stripe\Error\Card $e) {
                $body = $e->getJsonBody();
                $this->_session->errorMessage = $body['error'];
            } catch (\Stripe\Error\RateLimit $e) {
                $this->_session->errorMessage  = $e->getMessage();
            } catch (\Stripe\Error\InvalidRequest $e) {
                $this->_session->errorMessage = $e->getMessage();
            } catch (\Stripe\Error\Authentication $e) {
                $this->_session->errorMessage = $e->getMessage();
            } catch (\Stripe\Error\ApiConnection $e) {
                $this->_session->errorMessage = $e->getMessage();
            } catch (\Stripe\Error\Base $e) {
                $this->_session->errorMessage = $e->getMessage();
            } catch (Exception $e) {
                $this->_session->errorMessage = $e->getMessage();
            }
        return $transaction;
    }

    public function createOrderTransactionReturn($order,$transaction) {

        // Get related info
        $user = $order->getUser();
        $orderPayment = $order->getSource();
        $module_name = 'user';
         $viewer = Engine_Api::_()->user()->getViewer();
        $paymentStatus = null;
        $orderStatus = null;

        switch($transaction->status) {
            case 'created':
            case 'pending':
            case 'succeeded':
                $paymentStatus = 'pending';
                $orderStatus = 'complete';
            break;
            case 'completed':
            case 'processed':
            case 'canceled_reversal': // Probably doesn't apply
                $paymentStatus = 'okay';
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
        $order->gateway_transaction_id = $transaction->id;
        $order->save();

        // Insert transaction
        $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
        $transactionsTable->insert(array(
            'user_id' => $order->user_id,
            'gateway_id' =>2,
            'timestamp' => new Zend_Db_Expr('NOW()'),
            'order_id' => $order->order_id,
            'type' => 'payment',
            'state' => $paymentStatus,
            'gateway_transaction_id' => $transaction->id,
            'amount' => $transaction->amount/100, // @todo use this or gross (-fee)?
            'currency' => strtoupper($transaction->currency),
        ));

        // Get benefit setting
        $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')->getBenefitStatus($user);

        // Check payment status
        if( $paymentStatus == 'okay' || ($paymentStatus == 'pending' && $giveBenefit) ) {
                // Update order table info
            $orderPayment->gateway_id = $transaction->metadata->gateway_id;
            $orderPayment->gateway_transaction_id = $transaction->id;
            $orderPayment->currency_symbol = strtoupper($transaction->currency);
            $orderPayment->release_date = date('Y-m-d H:i:s');
            $orderPayment->gateway_type = "Stripe";
            $orderPayment->save();


            if($order->source_type == 'sescrowdfunding_userpayrequest') {
                $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sescrowdfunding');
            } else if($order->source_type == 'sesvideosell_remainingpayment') {
                $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sesvideosell');
            } else if($order->source_type == 'sesproduct_userpayrequest') {
                $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'estore');
            }else if($order->source_type == 'courses_userpayrequest') {
                $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'courses');
            }else if($order->source_type == 'sesevent_userpayrequest') {
                $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sesevent');
            }
            $tableName = $tableRemaining->info('name');

            if($order->source_type == 'sescrowdfunding_userpayrequest') {
                $select = $tableRemaining->select()->from($tableName)->where('crowdfunding_id =?',$orderPayment->crowdfunding_id);

                $select = $tableRemaining->fetchAll($select);
                $remainingAmt = $select[0]['remaining_payment'] - $transaction->amount/100;
                if($remainingAmt < 0)
                    $orderAmount = 0;
                else
                    $orderAmount = $remainingAmt;

                $tableRemaining->update(array('remaining_payment' => $remainingAmt),array('crowdfunding_id =?'=>$orderPayment->crowdfunding_id));
                $orderPayment->onOrderComplete();

            } else if($order->source_type == 'sesvideosell_remainingpayment') {

                $select = $tableRemaining->select()->from($tableName)->where('user_id =?',$orderPayment->user_id);

                $select = $tableRemaining->fetchAll($select);
                $remainingAmt = $select[0]['remaining_payment'] - $transaction->amount/100;
                if($remainingAmt < 0)
                    $orderAmount = 0;
                else
                    $orderAmount = $remainingAmt;

                $tableRemaining->update(array('remaining_payment' => $remainingAmt),array('user_id =?'=>$orderPayment->user_id));
                $orderPayment->onOrderComplete();

            } else if($order->source_type == 'sesproduct_userpayrequest') {
               //update EVENT OWNER REMAINING amount
              $select = $tableRemaining->select()->from($tableName)->where('store_id =?',$orderPayment->store_id);
              $select = $tableRemaining->fetchAll($select);
              $remainingAmt = $select[0]['remaining_payment'] - $transaction->amount/100;
              if($remainingAmt < 0)
                $orderAmount = 0;
              else
                $orderAmount = $remainingAmt;
              $tableRemaining->update(array('remaining_payment' => $remainingAmt),array('store_id =?'=>$orderPayment->store_id));
              $orderPayment->onOrderComplete();
                // send notification
                if( $orderPayment->state == 'complete' ) {
                    $store = Engine_Api::_()->getItem('stores', $orderPayment->store_id);
                    $owner = Engine_Api::_()->getItem('user', $store->owner_id);
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner->getOwner(), $viewer, $store, 'estore_approve_request');
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner->getOwner()->email, 'estore_approve_request', array('host' => $_SERVER['HTTP_HOST'], 'object_link' => $store->getHref()));
                }
                return 'active';
            }else if($order->source_type == 'courses_userpayrequest') {
               //update EVENT OWNER REMAINING amount
              $select = $tableRemaining->select()->from($tableName)->where('course_id =?',$orderPayment->course_id);
              $select = $tableRemaining->fetchAll($select);
              $remainingAmt = $select[0]['remaining_payment'] - $transaction->amount/100;
              if($remainingAmt < 0)
                $orderAmount = 0;
              else
                $orderAmount = $remainingAmt;
              $tableRemaining->update(array('remaining_payment' => $remainingAmt),array('course_id =?'=>$orderPayment->course_id));
              $orderPayment->onOrderComplete();
                // send notification
                if( $orderPayment->state == 'complete' ) {
                    $course = Engine_Api::_()->getItem('courses', $orderPayment->course_id);
                    $owner = Engine_Api::_()->getItem('user', $course->owner_id);
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner->getOwner(), $viewer, $course, 'estore_approve_request');
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner->getOwner()->email, 'courses_approve_request', array('host' => $_SERVER['HTTP_HOST'], 'object_link' => $course->getHref()));
                }
                return 'active';
            }else if($order->source_type == 'sesevent_userpayrequest') {
              //update EVENT OWNER REMAINING amount
              $select = $tableRemaining->select()->from($tableName)->where('event_id =?',$orderPayment->event_id);
              $select = $tableRemaining->fetchAll($select);
              $remainingAmt = $select[0]['remaining_payment'] - $transaction->amount/100;
              if($remainingAmt < 0)
                $orderAmount = 0;
              else
                $orderAmount = $remainingAmt;
                $tableRemaining->update(array('remaining_payment' => $remainingAmt),array('event_id =?'=>$orderPayment->event_id));
              // Payment success
              $orderPayment->onOrderComplete();
            }
            if($order->source_type == 'sescrowdfunding_userpayrequest') {

                // Payment success

                // send notification
                if( $orderPayment->state == 'complete' ) {
                    /*Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_active', array(
                    'subscription_title' => $package->title,
                    'subscription_description' => $package->description,
                    'subscription_terms' => $package->getPackageDescription(),
                    'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                        Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
                    ));*/
                }
            }
            return 'active';
        }
        else if( $paymentStatus == 'pending' ) {
            // Update order  info
            $orderPayment->gateway_id = $this->_gatewayInfo->gateway_id;
            $orderPayment->gateway_profile_id = $transaction->id;
                    $orderPayment->save();
            // Order pending
            $orderPayment->onOrderPending();

            return 'pending';
        }
        else if( $paymentStatus == 'failed' ) {
            // Cancel order and subscription?
            $order->onFailure();
            $orderPayment->onOrderFailure();

            // Payment failed
            throw new Payment_Model_Exception('Your payment could not be ' .
                'completed. Please ensure there are sufficient available funds ' .
                'in your account.');
        }
        else {
            // This is a sanity error and cannot produce information a user could use
            // to correct the problem.
            throw new Payment_Model_Exception('There was an error processing your ' .
                'transaction. Please try again later.');
        }
    }

    /**
    * Process return of subscription transaction
    *
    * @param Payment_Model_Order $order
    * @param array $params
    */
    public function onSubscriptionTransactionReturn(Payment_Model_Order $order, array $params = array()) {}

	public function onOrderTicketTransactionIpn(Payment_Model_Order $order, Engine_Payment_Ipn $ipn) {

	}
  /**
   * Process ipn of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  public function onSubscriptionTransactionIpn(
      Payment_Model_Order $order,
      Engine_Payment_Ipn $ipn)
  {}

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

  }
  /**
   * Generate href to a page detailing the transaction
   *
   * @param string $transactionId
   * @return string
   */
  public function getTransactionDetailLink($transactionId)
  {

  }
  /**
   * Get raw data about an order or recurring payment profile
   *
   * @param string $orderId
   * @return array
   */
  public function getOrderDetails($orderId)
  {
    // We don't know if this is a recurring payment profile or a transaction id,
    // so try both
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
  /**
   * Get raw data about a transaction
   *
   * @param $transactionId
   * @return array
   */
  public function getTransactionDetails($transactionId)
  {
    return $this->getService()->detailTransaction($transactionId);
  }
  // IPN
  /**
   * Process an IPN
   *
   * @param Engine_Payment_Ipn $ipn
   * @return Engine_Payment_Plugin_Abstract
   */
  public function onIpn(Engine_Payment_Ipn $ipn)
  { }
 function getSupportedCurrencies(){
      return array('USD'=>'USD','AED'=>'AED','AFN'=>'AFN','ALL'=>'ALL','AMD'=>'AMD','ANG'=>'ANG','AOA'=>'AOA','ARS'=>'ARS','AUD'=>'AUD'
      ,'AWG'=>'AWG','AZN','BAM'=>'BAM','BBD'=>'BBD','BDT'=>'BDT','BGN'=>'BGN','BIF'=>'BIF','BMD'=>'BMD','BND'=>'BND','BOB'=>'BOB','BRL'=>'BRL',
      'BSD'=>'BSD','BWP'=>'BWP','BZD'=>'BZD','CAD'=>'CAD','CDF'=>'CDF','CHF'=>'CHF','CLP'=>'CLP','CNY'=>'CNY','COP'=>'COP','CRC'=>'CRC','CVE'=>'CVE',
      'CZK'=>'CZK','DJF'=>'DJF','DKK'=>'DKK','DOP'=>'DOP','DZD'=>'DZD','EGP'=>'EGP','ETB'=>'ETB','EUR'=>'EUR','FJD'=>'FJD','FKP'=>'FKP','GBP'=>'GBP',
      'GEL'=>'GEL','GIP'=>'GIP','GMD'=>'GMD','GNF'=>'GNF','GTQ'=>'GTQ','GYD'=>'GYD','HKD'=>'HKD','HNL'=>'HNL','HRK'=>'HRK','HTG'=>'HTG','HUF'=>'HUF',
      'IDR'=>'IDR','ILS'=>'ILS','INR'=>'INR','ISK'=>'ISK','JMD'=>'JMD','JPY'=>'JPY','KES'=>'KES','KGS'=>'KGS','KHR'=>'KHR','KMF'=>'KMF','KRW'=>'KRW',
      'KYD'=>'KYD','KZT'=>'KZT','LAK'=>'LAK','LBP'=>'LBP','LKR'=>'LKR','LRD'=>'LRD','LSL'=>'LSL','MAD'=>'MAD','MDL'=>'MDL','MGA'=>'MGA','MKD','MMK'=>'MMK',
      'MNT'=>'MNT','MOP'=>'MOP','MRO'=>'MRO','MUR'=>'MUR','MVR'=>'MVR','MWK'=>'MWK','MXN'=>'MXN','MYR'=>'MYR','MZN'=>'MZN','NAD'=>'NAD','NGN'=>'NGN','NIO'=>'NIO',
      'NOK'=>'NOK','NPR'=>'NPR','NZD'=>'NZD','PAB'=>'PAB','PEN'=>'PEN','PGK'=>'PGK','PHP'=>'PHP','PKR'=>'PKR','PLN'=>'PLN','PYG'=>'PYG','QAR'=>'QAR','RON'=>'RON',
      'RSD'=>'RSD','RUB'=>'RUB','RWF'=>'RWF','SAR'=>'SAR','SBD'=>'SBD','SCR'=>'SCR','SEK'=>'SEK','SGD'=>'SGD','SHP'=>'SHP','SLL'=>'SLL','SOS'=>'SOS','SRD'=>'SRD',
      'STD'=>'STD','SZL'=>'SZL','THB'=>'THB','TJS'=>'TJS','TOP'=>'TOP','TRY'=>'TRY','TTD'=>'TTD','TWD'=>'TWD','TZS'=>'TZS','UAH'=>'UAH','UGX'=>'UGX','UYU'=>'UYU','UZS'=>'UZS','VND'=>'VND','VUV'=>'VUV','WST'=>'WST','XAF'=>'XAF','XCD'=>'XCD','XOF'=>'XOF','XPF'=>'XPF','YER'=>'YER','ZAR'=>'ZAR','ZMW'=>'ZMW');
 }


  // Forms
  /**
   * Get the admin form for editing the gateway info
   *
   * @return Engine_Form
   */
  public function getAdminGatewayForm()
  {
    return new Sesbasic_Form_Admin_Gateway_PayPal();
  }
  public function processAdminGatewayForm(array $values)
  {
    return $values;
  }
}
