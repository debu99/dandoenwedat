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
class Sesadvpmnt_Plugin_Gateway_Stripe extends Engine_Payment_Plugin_Abstract
{
  protected $_gatewayInfo;

  protected $_gateway;
  protected $_session;
  public function __construct(Zend_Db_Table_Row_Abstract $gatewayInfo)
  {
      $this->_gatewayInfo = $gatewayInfo;
      $this->_session = new Zend_Session_Namespace('Stripe_Error');
  }

  public function getService()
  {
    return $this->getGateway()->getService();
  }

  public function getGateway()
  {
    if( null === $this->_gateway ) {
        $class = 'Sesadvpmnt_Gateways_Stripe';
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

  public function createSubscriptionTransaction(User_Model_User $user,
      Zend_Db_Table_Row_Abstract $subscription,
      Payment_Model_Package $package,
      array $params = array())
  {
    // Process description
    $desc = $package->getPackageDescription();
    if( strlen($desc) > 127 ) {
      $desc = substr($desc, 0, 124) . '...';
    } else if( !$desc || strlen($desc) <= 0 ) {
      $desc = 'N/A';
    }
    if( function_exists('iconv') && strlen($desc) != iconv_strlen($desc) ) {
      // PayPal requires that DESC be single-byte characters
      $desc = @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $desc);
    }
    // This is a one-time fee
    if($package->isOneTime()) {
         return  $this->createOrderTransaction($params);
    } else {
        $secretKey = $this->_gatewayInfo->config['sesadvpmnt_stripe_secret'];
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $currency = $settings->getSetting('payment.currency', 'USD');
       try{
        \Stripe\Stripe::setApiKey($secretKey);
                $plan = \Stripe\Plan::create(array(
                    "amount" => $params['amount']*100,
                    "interval" => $package->recurrence_type,
                    "interval_count" => $package->recurrence,
                    "currency" => $currency,
                    "product" => [
                        "name" => $package->title,
                        "type" => "service"
                    ],
                ));
                $plan_id = $plan->id;
              $createSubscription = \Stripe\Subscription::create([
                "customer" =>  $params['customer'],
                "items" => [
                    [
                     "plan" => $plan_id,
                    ],
                ],
                 'metadata' =>['order_id'=>$params['order_id'],'type'=>$params['type'],'gateway'=> $this->_gatewayInfo->getIdentity()]
            ]);
            return $createSubscription;
        }catch(ExceptiocreateSubscriptionTransactionn $e) {
            throw new Payment_Model_Exception('There was an error processing your ' .
          'transaction. Please try again later.');
        }
    }
    // Create transaction
  }
  public function onSubscriptionReturn(
      Payment_Model_Order $order,$transaction)
  {
    if( $order->gateway_id != $this->_gatewayInfo->gateway_id ) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }

    // Get related info
    $user = $order->getUser();
    $subscription = $order->getSource();
    $package = $subscription->getPackage();

    // Check subscription state
    if( $subscription->status == 'active' ||
        $subscription->status == 'trial') {
      return 'active';
    } else if( $subscription->status == 'pending' ) {
      return 'pending';
    }

    // Check for cancel state - the user cancelled the transaction
    if($transaction->status == 'cancel' ) {
      // Cancel order and subscription?
      $order->onCancel();
      $subscription->onPaymentFailure();
      // Error
      throw new Payment_Model_Exception('Your payment has been cancelled and ' .
          'not been charged. If this is not correct, please try again later.');
    }

//     // One-time
    if($package->isOneTime()) {
      // Get payment state
      $paymentStatus = null;
      $orderStatus = null;
      switch($transaction->status) {
        case 'created':
        case 'pending':
          $paymentStatus = 'pending';
          $orderStatus = 'complete';
          break;

        case 'active':
        case 'succeeded':
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
        'gateway_id' => $this->_gatewayInfo->gateway_id,
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'order_id' => $order->order_id,
        'type' => 'payment',
        'state' => $paymentStatus,
        'gateway_transaction_id' => $transaction->id,
        'amount' => $transaction->amount/100, // @todo use this or gross (-fee)?
        'currency' => strtoupper($transaction->currency),
      ));
      $transaction_id = $transactionsTable->getAdapter()->lastInsertId();
      // Get benefit setting
      $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
          ->getBenefitStatus($user);
      // Check payment status
      if( $paymentStatus == 'okay' ||
          ($paymentStatus == 'pending' && $giveBenefit) ) {
     
        // Update subscription info
        $subscription->gateway_id = $this->_gatewayInfo->gateway_id;
        $subscription->gateway_profile_id = $transaction->id;
        // Payment success
        $subscription->onPaymentSuccess();
        $paymentTransaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
        //For Coupon
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')){
          $couponSessionCode = $package->getType().'-'.$package->package_id.'-'.$subscription->getType().'-'.$subscription->subscription_id.'-1';
          $paymentTransaction->ordercoupon_id = Engine_Api::_()->ecoupon()->setAppliedCouponDetails($couponSessionCode);
          $paymentTransaction->save();
        }
        //For Credit 
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit')) {
           $creditCode =  'credit'.'-payment-'.$package->package_id.'-'.$subscription->subscription_id;
          $sessionCredit = new Zend_Session_Namespace($creditCode);
          if(isset($sessionCredit->credit_value)){
            $paymentTransaction->credit_point = $sessionCredit->credit_value;  
            $paymentTransaction->credit_value =  $sessionCredit->purchaseValue;
            $paymentTransaction->save();
            $userCreditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
            $userCreditDetailTable->update(array('total_credit' => new Zend_Db_Expr('total_credit - ' . $sessionCredit->credit_value)), array('owner_id =?' => $order->user_id));
          }
        }
        unset($paymentTransaction);
        // send notification
        if( $subscription->didStatusChange() ) {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_active', array(
            'subscription_title' => $package->title,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
          ));
        }
        return 'active';
      }
      else if( $paymentStatus == 'pending' ) {

        // Update subscription info
        $subscription->gateway_id = $this->_gatewayInfo->gateway_id;
        $subscription->gateway_profile_id = $transaction->id;
        // Payment pending
        $subscription->onPaymentPending();
        // send notification
        if( $subscription->didStatusChange() ) {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_pending', array(
            'subscription_title' => $package->title,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
          ));
        }

        return 'pending';
      }
      else if( $paymentStatus == 'failed' ) {
        // Cancel order and subscription?
        $order->onFailure();
        $subscription->onPaymentFailure();
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
    } // For Recurring Payment
    else {
      // Create recurring payments profile
      $desc = $package->getPackageDescription();
      if( strlen($desc) > 127 ) {
        $desc = substr($desc, 0, 124) . '...';
      } else if( !$desc || strlen($desc) <= 0 ) {
        $desc = 'N/A';
      }
      if( function_exists('iconv') && strlen($desc) != iconv_strlen($desc) ) {
        // PayPal requires that DESC be single-byte characters
        $desc = @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $desc);
      }
      $order->state = 'complete';
      $order->gateway_order_id = $transaction->id;
      $order->gateway_transaction_id = $transaction->id;
      $order->save();

      // Get benefit setting
      $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
          ->getBenefitStatus($user);

      // Check profile status
      if($giveBenefit) {
        // Enable now
        $subscription->gateway_id = $this->_gatewayInfo->gateway_id;
        $subscription->gateway_profile_id = $transaction->id;
        $subscription->onPaymentSuccess();
        // send notification
        if( $subscription->didStatusChange()) {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_active', array(
            'subscription_title' => $package->title,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
          ));
        }

        return 'active';

      } else if($paymentStatus == 'pending') {
        // Enable later
        $subscription->gateway_id = $this->_gatewayInfo->gateway_id;
        $subscription->gateway_profile_id = $transaction->id;
        $subscription->onPaymentPending();
        // send notification
        if( $subscription->didStatusChange() ) {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_pending', array(
            'subscription_title' => $package->title,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
          ));
        }
        return 'pending';

      } else {
        // Cancel order and subscription?
        $order->onFailure();
        $subscription->onPaymentFailure();
        // This is a sanity error and cannot produce information a user could use
        // to correct the problem.
        throw new Payment_Model_Exception('There was an error processing your ' .
            'transaction. Please try again later.');
      }
    }
  }
  public function onSubscriptionTransactionReturn(Payment_Model_Order $order,array $params = array()){}
  public function onSubscriptionTransactionIpn(
      Payment_Model_Order $order,
      Engine_Payment_Ipn $ipn)
  {

  }

  public function cancelSubscription($transactionId, $note = null)
  {
        $profileId = null;

    if( $transactionId instanceof Payment_Model_Subscription ) {
      $package = $transactionId->getPackage();
      if( $package->isOneTime() ) {
        return $this;
      }
      $profileId = $transactionId->gateway_profile_id;
    }

    else if(is_string($transactionId) ) {
      $profileId = $transactionId;
    }

    else {
      // Should we throw?
      return $this;
    }
     $this->view->secretKey = $secretKey = $this->_gatewayInfo->config['sesadvpmnt_stripe_secret'];
     \Stripe\Stripe::setApiKey($secretKey);
     $sub = \Stripe\Subscription::retrieve($profileId);
   $cancel = $sub->cancel();


    return $this;
  }

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
      return 'https://dashboard.stripe.com/test/search?query' . $orderId;
    } else {
      return 'https://dashboard.stripe.com/search?query' . $orderId;
    }
  }

  public function getTransactionDetailLink($transactionId)
  {
    if( $this->getGateway()->getTestMode() ) {
      // Note: it doesn't work in test mode
      return 'https://dashboard.stripe.com/test/search?query' . $transactionId;
    } else {
      return 'https://dashboard.stripe.com/search?query' . $transactionId;
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

  public function createOrderTransaction($params = array()) {
      $secretKey = $this->_gatewayInfo->config['sesadvpmnt_stripe_secret'];
      $currencyValue = $params['change_rate'] ? $params['change_rate'] : 1;
        try {
            \Stripe\Stripe::setApiKey($secretKey);
            $transaction = \Stripe\Charge::create([
                'amount' => $params['amount']*100,
                'currency' => $params['currency'],
                'source' => $params['token'],
                'description' => 'Credits'.$params['amount'],
                'metadata' =>['order_id'=>$params['order_id'],'type'=>$params['type'],'change_rate'=>$currencyValue]
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
      $user = $order->getUser();
      $viewer = Engine_Api::_()->user()->getViewer();
      $orderPayment = $order->getSource();
      $paymentOrder = $order;
        switch($transaction->status) {
            case 'created':
            case 'pending':
                $paymentStatus = 'pending';
                $orderStatus = 'complete';
            break;
            case 'completed':
            case 'processed':
            case 'canceled_reversal':
            case 'succeeded':
            case 'paid':
                $paymentStatus = 'okay';
                $orderStatus = 'complete';
            break;
            case 'denied':
            case 'failed':
            case 'voided':
            case 'reversed':
            case 'refunded':
            case 'expired':
            default:
                $paymentStatus = 'failed';
                $orderStatus = 'failed'; // This should probably be 'failed'
            break;
        }
    if($transaction->metadata->type == "crowdfunding") {
        $currentCurrency = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();
        $defaultCurrency = Engine_Api::_()->sescrowdfunding()->defaultCurrency();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $currencyValue = 1;
        if($currentCurrency != $defaultCurrency) {
            $currencyValue = $settings->getSetting('sesmultiplecurrency.'.$currentCurrency);
        }
        $order->state = $orderStatus;
        $order->gateway_transaction_id = $transaction->id;
        $order->save();
         $crowdfundingItem = Engine_Api::_()->getItem('crowdfunding', $orderPayment->crowdfunding_id);
        $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')->getBenefitStatus($user);
        // Insert transaction
        $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
        $transactionsTable->insert(array(
            'user_id' => $order->user_id,
            'gateway_id' =>2,
            'timestamp' => new Zend_Db_Expr('NOW()'),
            'order_id' => $order->order_id,
            'type' => 'payment',
            'state' => $paymentStatus,
            'gateway_transaction_id' =>  $transaction->id,
            'amount' => $transaction->amount/100, // @todo use this or gross (-fee)?
            'currency' => strtoupper($transaction->currency),
        ));

        if( $paymentStatus == 'okay' || ($paymentStatus == 'pending' && $giveBenefit) ) {

            $orderPayment->change_rate = $currencyValue;
            $orderPayment->gateway_id = $this->_gatewayInfo->gateway_id;
            $orderPayment->gateway_transaction_id = $transaction->id;
            $orderPayment->currency_symbol = strtoupper($transaction->currency);
            $orderPayment->save();

            //update OWNER REMAINING amount
            $orderAmount = @round(($orderPayment->total_useramount/$currencyValue),2);
            $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sescrowdfunding');
            $tableName = $tableRemaining->info('name');
            $select = $tableRemaining->select()->from($tableName)->where('crowdfunding_id =?',$crowdfundingItem->crowdfunding_id);
            $select = $tableRemaining->fetchAll($select);
            if(count($select)){
                $tableRemaining->update(array('remaining_payment' => new Zend_Db_Expr("remaining_payment + $orderAmount")),array('crowdfunding_id =?'=>$crowdfundingItem->crowdfunding_id));
            } else {
                $tableRemaining->insert(array(
                    'remaining_payment' => $orderAmount,
                    'crowdfunding_id' => $crowdfundingItem->crowdfunding_id,
                ));
            }
            Engine_Api::_()->getDbtable('orders', 'sescrowdfunding')->update(array('state' => 'complete'), array('order_id =?' => $orderPayment->order_id));
            // Payment success
            $orderPayment->onOrderComplete();
            // send notification
            if( $orderPayment->state == 'complete' ) {
                $crowdfunding = Engine_Api::_()->getItem('crowdfunding', $orderPayment->crowdfunding_id);
                $total_amount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($orderPayment->total_amount, $orderPayment->currency_symbol);
                $total_useramount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($orderPayment->total_useramount, $orderPayment->currency_symbol);
                $commissionType = Engine_Api::_()->authorization()->getPermission($user,'crowdfunding','admin_commission');
                $commissionTypeValue = Engine_Api::_()->authorization()->getPermission($user,'crowdfunding','commission_value');
                //%age wise
                if($commissionType == 1 && $commissionTypeValue > 0){
                    $orderPayment->commission_amount = round(($orderPayment->total_amount/$currencyValue) * ($commissionTypeValue/100),2);
                    $orderPayment->save();
                } else if($commissionType == 2 && $commissionTypeValue > 0) {
                    $orderPayment->commission_amount = $commissionTypeValue;
                    $orderPayment->save();
                }
                $commission_amount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($orderPayment->commission_amount, $orderPayment->currency_symbol);
                $crowdfundingPhoto = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') . $crowdfunding->getPhotoUrl();
                $body = '<table cellpadding="0" cellspacing="0" style="background:#f0f4f5;border:3px solid #f1f4f5;border-collapse:collapse;border-spacing:0;float:none;margin:0 auto;max-width:608px;padding:0;text-align:center;vertical-align:top;width:450px;max-width:100%;font-family: Arial,Helvetica,sans-serif;"><tr><td style="text-align:center;padding:10px;"><table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td style="padding:10px 0;color:#555;font-size:13px;">Free stock photos of crowdfunding · Pexels</td></tr><tr><td style="background-color:#fff;text-align:center;"><div style="width:150px;height:150px;float:left;"><a href="'.$crowdfunding->getHref().'"><img src="'.$crowdfundingPhoto.'" alt="" style="width:100%;height:100%;object-fit:cover;" /></a></div><div style="display: block; overflow: hidden; padding: 10px; text-align: left;"><a href="'.$crowdfunding->getHref().'" style="color: rgb(85, 85, 85); font-size: 17px; text-decoration: none; font-weight: bold;">'.$crowdfunding->getTitle().'</a></div></td></tr><tr><td style="height:20px;"></td></tr><tr><td><table style="color:#555;border:1px solid #ddd;background-color: rgb(255, 255, 255); width: 100%;font-size:13px;" cellspacing="0" cellpadding="10"><tbody><tr>';
                $body .= '<td align="left">Price</td><td align="right"><strong>'.$total_useramount.'</strong></td></tr><tr>';
                $body .= '<td align="left" style="border-top:1px dashed #ddd;">Total Paid</td>';
                $body .= '<td align="right" style="border-top:1px dashed #ddd;"><strong>'.$total_useramount.'</strong></td></tr></tbody></table></td></tr></table></td></tr></table>';

                //Notification send to Crowdfunding Owner When some one donate amount
                $crowdfunding->donate_count++;
                $crowdfunding->save();

                $user = Engine_Api::_()->getItem('user', $orderPayment->user_id);
                $owner = $crowdfunding->getOwner();
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $user, $crowdfunding, 'sescrowdfunding_donation_owner');

                //Donate invoice mail to doner
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sescrowdfundinginvoice_doner', array('invoice_body' => $body, 'host' => $_SERVER['HTTP_HOST']));

                //Crowdfunding Purchased Mail to Crowdfunding Owner
                $owner = Engine_Api::_()->getItem('user', $crowdfunding->owner_id);
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner, 'sescrowdfunding_donation_owner', array('crowdfunding_title' => $crowdfunding->getTitle(), 'object_link' => $user->getHref(), 'buyer_name' => $user->getTitle(), 'host' => $_SERVER['HTTP_HOST']));

                //Crowdfunding donation Mail send to admin
                $usersTable = Engine_Api::_()->getDbtable('users', 'user');
                $usersTableName = $usersTable->info('name');
                $datas = $usersTable->select()->from($usersTableName, array('user_id'))->where('level_id =?', 1)->query()->fetchAll();
                foreach($datas as $data) {
                $adminUser = Engine_Api::_()->getItem('user', $data['user_id']);
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($adminUser, 'sescrowdfunding_donation_adminemail', array('crowdfunding_title' => $crowdfunding->getTitle(), 'object_link' => $user->getHref(), 'buyer_name' => $user->getTitle(), 'host' => $_SERVER['HTTP_HOST'], 'total_amount' => $total_amount, 'total_useramount' => $total_useramount, 'commission_amount' => $commission_amount));
                }

            }
            $orderPayment->creation_date	= date('Y-m-d H:i:s');
            $orderPayment->save();
            return 'active';
        }
    } else if($transaction->metadata->type == "product") {
        $order->state = $orderStatus;
        $order->gateway_transaction_id = $transaction->id;
        $order->save();
        $session = new Zend_Session_Namespace('Payment_Sesproduct');
         $currency = $session->currency;
        $rate = $session->change_rate;
        if (!$rate)
            $rate = 1;
        $defaultCurrency = Engine_Api::_()->sesproduct()->defaultCurrency();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $currencyValue = 1;
        if ($currency != $defaultCurrency)
            $currencyValue = $settings->getSetting('sesmultiplecurrency.' . $currency);
        //Insert transaction
        //check product variations
        $orderTableName = Engine_Api::_()->getDbTable('orders','sesproduct');
        $select = $orderTableName->select()->where('parent_order_id =?',$orderPayment->getIdentity());
        $orders = $orderTableName->fetchAll($select);
        $orderIds = array();
        $storeIds = array();
        $totalPrice = 0;
        foreach ($orders as $order){
            $orderIds[] = $order->getIdentity();
            $totalPrice +=$order->total;
            $order->state = "processing";
            $order->save();
            //For Coupon
            $ordercoupon_id = 0;
            if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')){
              $couponSessionCode = '-'.'-stores-'.$order->store_id.'-0';
              $ordercoupon_id = Engine_Api::_()->ecoupon()->setAppliedCouponDetails($couponSessionCode);
            }
            $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'sesproduct');
            $transactionsTable->insert(array(
                'owner_id' => $order->user_id,
                'gateway_id' => $this->_gatewayInfo->gateway_id,
                'gateway_transaction_id' => $transaction->id,
                'gateway_profile_id' => $transaction->id,
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'modified_date' => new Zend_Db_Expr('NOW()'),
                'order_id' => $order->order_id,
                'state' => 'processing',
                'total_amount' => $order->total,
                'change_rate' => $rate,
                'ordercoupon_id'=> $ordercoupon_id,
                'gateway_type' => 'Stripe',
                'currency_symbol' => $currency,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            ));
            $transaction_id = $transactionsTable->getAdapter()->lastInsertId();
            $storeIds[]  = $order->store_id;
        }
      //get all order products
        $productTableName = Engine_Api::_()->getDbTable('orderproducts','sesproduct');
        $select = $productTableName->select()->where('order_id IN (?)', $orderIds);

        $products = $productTableName->fetchAll($select);
      // Get benefit setting
        $giveBenefit = // Get benefit setting
        $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'sesproduct')
            ->getBenefitStatus($user);
        //Check payment status

        if ($paymentStatus == 'okay' || $paymentStatus == 'active' ||
                ($paymentStatus == 'pending' && $giveBenefit)) {
            // Payment success
            try{
                Engine_Api::_()->sesproduct()->orderComplete($orderPayment,$products);
            }catch(Exception $e){
                throw new Payment_Model_Exception($e->getMessage());
            }
            // send notification
            try {
                    $getAdminnSuperAdmins = Engine_Api::_()->sesproduct()->getAdminnSuperAdmins();
                    $counter = 0;
                    $storeIds = array_unique($storeIds);
                    foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                        $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                        foreach($storeIds as $storeid) {
                            $store = Engine_Api::_()->getItem('stores', $storeid);
                            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sesproduct_product_orderplaced', array('sender_title' => $store->getOwner()->getTitle(), 'object_link' => $store->getHref(),'gateway_name'=>'paypal','buyer_name'=>$viewer->getTitle(),'host' => $_SERVER['HTTP_HOST']));

                            Engine_Api::_()->getApi('mail', 'core')->sendSystem($viewer->email, 'sesproduct_product_orderplacedtobuyer', array('host' => $_SERVER['HTTP_HOST'], 'order_id'=>$paymentOrder->order_id,'gateway_name'=>'paypal','buyer_name'=>$viewer->getTitle(),'object_link' => $store->getHref()));

                            if($counter)
                                Continue;
                            Engine_Api::_()->getApi('mail', 'core')->sendSystem($store->getOwner()->email, 'sesproduct_product_orderplaced', array('host' => $_SERVER['HTTP_HOST'], 'order_id'=>$paymentOrder->order_id,'gateway_name'=>'paypal','buyer_name'=>$viewer->getTitle(),'object_link' => $store->getHref()));
                        }
                        $counter++;
                }
            }catch(Exception $e){}

            return 'active';
        }
    } elseif($transaction->metadata->type == "courses") {

        $order->state = $orderStatus;
        $order->gateway_transaction_id = $transaction->id;
        $order->save();
        $session = new Zend_Session_Namespace('Payment_Courses');
         $currency = $session->currency;
        $rate = $session->change_rate;
        if (!$rate)
            $rate = 1;
        $defaultCurrency = Engine_Api::_()->courses()->defaultCurrency();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $currencyValue = 1;
        if ($currency != $defaultCurrency)
            $currencyValue = $settings->getSetting('sesmultiplecurrency.' . $currency);
        //Insert transaction


        //check product variations
        $orderTableName = Engine_Api::_()->getDbTable('orders','courses');
        $select = $orderTableName->select()->where('order_id =?',$orderPayment->getIdentity());
        $order = $orderTableName->fetchRow($select);

        $order->state = $orderStatus;
        $order->gateway_transaction_id = $transaction->id;
        $order->save();
        $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'courses');
        $transactionsTable->insert(array(
            'owner_id' => $order->user_id,
            'gateway_id' => $this->_gatewayInfo->gateway_id,
            'gateway_transaction_id' => $transaction->id,
            'gateway_profile_id' => $transaction->id,
            'creation_date' => new Zend_Db_Expr('NOW()'),
            'modified_date' => new Zend_Db_Expr('NOW()'),
            'order_id' => $order->order_id,
            'state' => 'processing',
            'item_count'=>$order->item_count,
            'total_amount' => $order->total_amount,
            'change_rate' => $rate,
            'gateway_type' => 'Stripe',
            'currency_symbol' => $currency,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
        ));
        $transaction_id = $transactionsTable->getAdapter()->lastInsertId();
      //get all order products
        $coursesTableName = Engine_Api::_()->getDbTable('ordercourses','courses');
        $select = $coursesTableName->select()->where('order_id =?',$orderPayment->getIdentity());
        $courses = $coursesTableName->fetchAll($select);
      // Get benefit setting
        $giveBenefit = // Get benefit setting
        $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'courses')
            ->getBenefitStatus($user);
        //Check payment status
        if ($paymentStatus == 'okay' || $paymentStatus == 'active' ||
                ($paymentStatus == 'pending' && $giveBenefit)) {
            // Payment success
            try{
                Engine_Api::_()->courses()->orderComplete($orderPayment,$courses);
            }catch(Exception $e){
                throw new Payment_Model_Exception($e->getMessage());
            }
            // send notification
            //try {
//                     $getAdminnSuperAdmins = Engine_Api::_()->courses()->getAdminnSuperAdmins();
//                     $counter = 0;
//                     $storeIds = array_unique($storeIds);
//                     foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
//                         $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
//                         foreach($storeIds as $storeid) {
//                             $store = Engine_Api::_()->getItem('stores', $storeid);
//                             Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sesproduct_product_orderplaced', array('sender_title' => $store->getOwner()->getTitle(), 'object_link' => $store->getHref(),'gateway_name'=>'paypal','buyer_name'=>$viewer->getTitle(),'host' => $_SERVER['HTTP_HOST']));
// 
//                             Engine_Api::_()->getApi('mail', 'core')->sendSystem($viewer->email, 'sesproduct_product_orderplacedtobuyer', array('host' => $_SERVER['HTTP_HOST'], 'order_id'=>$paymentOrder->order_id,'gateway_name'=>'paypal','buyer_name'=>$viewer->getTitle(),'object_link' => $store->getHref()));
// 
//                             if($counter)
//                                 Continue;
//                             Engine_Api::_()->getApi('mail', 'core')->sendSystem($store->getOwner()->email, 'sesproduct_product_orderplaced', array('host' => $_SERVER['HTTP_HOST'], 'order_id'=>$paymentOrder->order_id,'gateway_name'=>'paypal','buyer_name'=>$viewer->getTitle(),'object_link' => $store->getHref()));
//                         }
//                         $counter++;
//                 }
//             }catch(Exception $e){}

            return 'active';
        }
    } elseif($transaction->metadata->type == "sesevent_order") {
        return Engine_Api::_()->sesevent()->orderTicketTransactionReturn($order,$transaction,$this->_gatewayInfo);
    }
    return 'active';
  }
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
  public function getAdminGatewayForm(){
    return new Sesadvpmnt_Form_Admin_Settings_Stripe();
  }

  public function processAdminGatewayForm(array $values){
    return $values;
  }
  public function getGatewayUrl(){
  }
  function getSupportedBillingCycles(){
    return array(0=>'Day',1=>'Week',2=>'Month',3=>'Year');
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
  public function cancelSubscriptionOnExpiry($source, $package) {
      $this->view->secretKey = $secretKey = $this->_gatewayInfo->config['sesadvpmnt_stripe_secret'];
        if($package->duration_type != "forever"){
              $durationTime = (($package->duration > 1 || $package->duration == 0) ? ("+".$package->duration." ".$package->duration_type."s") : ("+".$package->duration." ".$package->duration_type));
                $subscriptionDate = strtotime($source->creation_date);
              $date = date($subscriptionDate,strtotime($durationTime));
        if(strtotime("now") >= $date ) {
          \Stripe\Stripe::setApiKey($secretKey);
          $sub = \Stripe\Subscription::retrieve($source->gateway_profile_id);
          $sub->cancel();
          echo "Subscription canceled";
        }
      }
      echo "Subscription Continue";
  }
  public function onIpnTransaction($rawData){
        $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
        $order = null;
        // Transaction IPN - get order by subscription_id
        if (!$order && !empty($rawData['data']['object']['subscription'])) {
            $gateway_order_id = $rawData['data']['object']['subscription'];
            $order = $ordersTable->fetchRow(array(
                'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
                'gateway_order_id = ?' => $gateway_order_id,
            ));
        }
        if ($order) {
            return $this->onTransactionIpn($order,$rawData);
        } else {
            throw new Engine_Payment_Plugin_Exception('Unknown or unsupported IPN type, or missing transaction or order ID');
        }
    }
    public function onTransactionIpn(Payment_Model_Order $order,  $rawData) {
        // Check that gateways match
        if ($order->gateway_id != $this->_gatewayInfo->gateway_id) {
            throw new Engine_Payment_Plugin_Exception('Gateways do not match');
        }
        // Get related info
        $user = $order->getUser();
        $source = $order->getSource();
        $package = $source->getPackage();
        $moduleName = explode("_", $package->getType());
        $moduleName = $moduleName['0'];
        // switch message_type
        switch ($rawData['type']) {
            case 'account.updated':
            case 'account.application.deauthorized':
            case 'account.external_account.created':
            case 'account.external_account.deleted':
            case 'account.external_account.updated':
            case 'application_fee.created':
            case 'application_fee.refunded':
            case 'application_fee.refund.updated':
            case 'balance.available':
            case 'bitcoin.receiver.created':
            case 'bitcoin.receiver.filled':
            case 'bitcoin.receiver.updated':
            case 'bitcoin.receiver.transaction.created':
            case 'charge.captured':
            case 'charge.failed':return false; break;
            case 'charge.refunded':
                // Payment Refunded
                $source->onRefund();
                // send notification
                if ($source->didStatusChange()) {
                    if ($moduleName == 'payment') {
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_refunded', array(
                            'subscription_title' => $package->title,
                            'subscription_description' => $package->description,
                            'subscription_terms' => $package->getPackageDescription(),
                            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
                        ));
                    } else {
                        Engine_Api::_()->$moduleName()->sendMail("REFUNDED", $source->getIdentity());
                    }
                }
                return true;
                break;
            case 'charge.succeeded':
            case 'charge.updated':
            case 'charge.dispute.closed':
            case 'charge.dispute.created':
            case 'charge.dispute.funds_reinstated':
            case 'charge.dispute.funds_withdrawn':
            case 'charge.dispute.updated':
            case 'coupon.created':
            case 'coupon.deleted':
            case 'coupon.updated':
            case 'customer.created':
            case 'customer.deleted':
            case 'customer.updated':
            case 'customer.bank_account.deleted':
            case 'customer.discount.created':
            case 'customer.discount.deleted':
            case 'customer.discount.updated':
            case 'customer.source.created':
            case 'customer.source.deleted':
            case 'customer.source.updated':
            case 'customer.subscription.created': return false; break;
            case 'customer.subscription.deleted':
                $source->onCancel();
                // send notification
                if ($source->didStatusChange()) {
                    if ($moduleName == 'payment') {
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_cancelled', array(
                            'subscription_title' => $package->title,
                            'subscription_description' => $package->description,
                            'subscription_terms' => $package->getPackageDescription(),
                            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
                        ));
                    } else {
                        Engine_Api::_()->$moduleName()->sendMail("CANCELLED", $source->getIdentity());
                    }
                }
                 return true;
                break;

            case 'customer.subscription.trial_will_end':return false; break;

            case 'customer.subscription.updated':
                $source->onPaymentSuccess();

                if ($source->didStatusChange()) {

                    if ($moduleName == 'payment') {
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_active', array(
                            'subscription_title' => $package->title,
                            'subscription_description' => $package->description,
                            'subscription_terms' => $package->getPackageDescription(),
                            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
                        ));
                    } else {
                        Engine_Api::_()->$moduleName()->sendMail("RECURRENCE", $source->getIdentity());
                    }
                }

                $this->cancelSubscriptionOnExpiry($source, $package);
                 return true;
                break;

            case 'invoice.created':break;

            case 'invoice.payment_failed':
                $source->onPaymentFailure();
                    if ($moduleName == 'payment') {
                        $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
                        $transactionsTable->insert(array(
                                'user_id' => $order->user_id,
                                'gateway_id' => $this->_gatewayInfo->gateway_id,
                                'timestamp' => new Zend_Db_Expr('NOW()'),
                                'order_id' => $order->order_id,
                                'type' => 'payment',
                                'state' => 'failed',
                                'gateway_transaction_id' => $rawData['data']['object']['charge'],
                                'amount' => $rawData['data']['object']['amount_paid']/100, // @todo use this or gross (-fee)?
                                'currency' => strtoupper($rawData['data']['object']['currency']),
                        ));
                        try {
                            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_overdue', array(
                                'subscription_title' => $package->title,
                                'subscription_description' => $package->description,
                                'subscription_terms' => $package->getPackageDescription(),
                                'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                                    Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
                            ));
                        } catch (Exception $e) {}
                    } else {
                        Engine_Api::_()->$moduleName()->sendMail("OVERDUE", $source->getIdentity());
                    }
                 return true;
                break;

            case 'invoice.payment_succeeded':
                $source->onPaymentSuccess();

                    if ($moduleName == 'payment') {
                        $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
                        $transactionsTable->insert(array(
                                'user_id' => $order->user_id,
                                'gateway_id' => $this->_gatewayInfo->gateway_id,
                                'timestamp' => new Zend_Db_Expr('NOW()'),
                                'order_id' => $order->order_id,
                                'type' => 'payment',
                                'state' => 'okay',
                                'gateway_transaction_id' => $rawData['data']['object']['charge'],
                                'amount' => $rawData['data']['object']['amount_paid']/100, // @todo use this or gross (-fee)?
                                'currency' => strtoupper($rawData['data']['object']['currency']),
                        ));
                    } else {
                        Engine_Api::_()->$moduleName()->sendMail("RECURRENCE", $source->getIdentity());
                    }
                $this->cancelSubscriptionOnExpiry($source, $package);
                return true;
               break;
            case 'invoice.updated':
            case 'invoiceitem.created':
            case 'invoiceitem.deleted':
            case 'invoiceitem.updated':
            case 'plan.created':
            case 'plan.deleted':
            case 'plan.updated':
            case 'recipient.created':
            case 'recipient.deleted':
            case 'recipient.updated':
            case 'transfer.created':
            case 'transfer.failed':
            case 'transfer.paid':
            case 'transfer.reversed':
            case 'transfer.updated': return false; break;

            default:
                throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
                    'type %1$s', $rawData['type']));
                break;
        }

        return $this;
    }
  function setConfig(){}
  function test(){}

}
