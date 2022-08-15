<?php

class Sesbasic_Plugin_Gateway_User_PayPal extends Engine_Payment_Plugin_Abstract {

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
    public function getService($module_name = 'user')
    {
        return $this->getGateway($module_name)->getService();
    }

    /**
    * Get the gateway object
    *
    * @return Engine_Payment_Gateway
    */
    public function getGateway($is_sponsorship = 'user')
    {

        if($is_sponsorship == 'sescrowdfunding') {
            $currency = Engine_Api::_()->sescrowdfunding()->defaultCurrency();
        } else {
            $currency = Engine_Api::_()->sesbasic()->defaultCurrency();
        }

        if( null === $this->_gateway ) {
            $class = 'Engine_Payment_Gateway_PayPal';
            Engine_Loader::loadClass($class);
            $gateway = new $class(array(
                'config' => (array) $this->_gatewayInfo->config,
                'testMode' => $this->_gatewayInfo->test_mode,
                'currency' => $currency,
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

    /**
    * Create a transaction for a user order
    *
    * @param User_Model_User $user
    * @param Zend_Db_Table_Row_Abstract $order
    * @param Zend_Db_Table_Row_Abstract $event
    * @param array $params
    *
    */
    public function createOrderTransaction($order,$event,array $params = array()) {

        if($event->getType() == 'crowdfunding') {
            //Crowdfunding plugin work
            $params['moduleName'] = 'sescrowdfunding';
            $params['driverSpecificParams']['PayPal'] = array(
                'AMT' => @round($order->release_amount, 2),
                'ITEMAMT' => @round($order->release_amount, 2),
                'ITEMS' => array(
                array(
                    'NAME' => $event->title,
                    'DESC' => $order->admin_message,
                    'AMT' => @round($order->release_amount, 2),
                    ),
                ),
                'SOLUTIONTYPE' => 'sole',
            );
        } else {
            $params['driverSpecificParams']['PayPal'] = array(
                'AMT' => @round($order->remaining_payment, 2),
                'ITEMAMT' => @round($order->remaining_payment, 2),
                'ITEMS' => array(
                array(
                'NAME' => $event->displayname,
                'DESC' => 'Owner Payment Made',
                'AMT' => @round($order->remaining_payment, 2),
                ),
                ),
                'SOLUTIONTYPE' => 'sole',
            );
        }

        // Should fix some issues with GiroPay
        if( !empty($params['return_url']) ) {
            $params['driverSpecificParams']['PayPal']['GIROPAYSUCCESSURL'] = $params['return_url']
                . ( false === strpos($params['return_url'], '?') ? '?' : '&' ) . 'giropay=1';
            $params['driverSpecificParams']['PayPal']['BANKTXNPENDINGURL'] = $params['return_url']
                . ( false === strpos($params['return_url'], '?') ? '?' : '&' ) . 'giropay=1';
        }
        if( !empty($params['cancel_url']) ) {
            $params['driverSpecificParams']['PayPal']['GIROPAYCANCELURL'] = $params['cancel_url']
            . ( false === strpos($params['return_url'], '?') ? '?' : '&' ) . 'giropay=1';
        }
        // Create transaction
        $transaction = $this->createTransaction($params);
        return $transaction;
    }

    public function orderTransactionReturn($order,$params = array()) {


        // Get related info
        $user = $order->getUser();
        $orderPayment = $order->getSource();
        $module_name = 'user';
        //Crowdfunding plugin work
        if($order->source_type == 'sescrowdfunding_userpayrequest') {
            $module_name = 'sescrowdfunding';
        }

        // Check for cancel state - the user cancelled the transaction
        if( $params['state'] == 'cancel' ) {
            // Cancel order and subscription?
            $order->onCancel();
            $orderPayment->onOrderFailure();
            // Error
            throw new Payment_Model_Exception('Your payment has been cancelled and ' .
                'not been charged. If this is not correct, please try again later.');
        }

        // Check params
        if( empty($params['token']) ) {
            // Cancel order and subscription?
            $order->onFailure();
            $orderPayment->onOrderFailure();
            // to correct the problem.
            throw new Payment_Model_Exception('There was an error processing your ' .
            'transaction. Please try again later.');
        }
        // Get details
        try {
            $data = $this->getService($module_name)->detailExpressCheckout($params['token']);
        } catch( Exception $e ) {
            // Cancel order and subscription?
            $order->onFailure();
            $orderPayment->onOrderFailure();
            // This is a sanity error and cannot produce information a user could use
            // to correct the problem.
            throw new Payment_Model_Exception('There was an error processing your ' .
            'transaction. Please try again later.');
        }
        // Let's log it
        $this->getGateway($module_name)->getLog()->log('ExpressCheckoutDetail: ' . print_r($data, true), Zend_Log::INFO);


        // Do payment
        try {
            $rdata = $this->getService($module_name)->doExpressCheckoutPayment($params['token'],
            $params['PayerID'], array(
            'PAYMENTACTION' => 'Sale',
            'AMT' => $data['AMT'],
            'CURRENCYCODE' => $this->getGateway($module_name)->getCurrency(),
            ));
        } catch( Exception $e ) {
            // Log the error
            $this->getGateway($module_name)->getLog()->log('DoExpressCheckoutPaymentError: '
                . $e->__toString(), Zend_Log::ERR);
            // Cancel order and subscription?
            $order->onFailure();
            // $orderPayment->onOrderFailure();
            // This is a sanity error and cannot produce information a user could use
            // to correct the problem.
            throw new Payment_Model_Exception('There was an error processing your ' .
                'transaction. Please try again later.');
        }

        // Let's log it
        $this->getGateway($module_name)->getLog()->log('DoExpressCheckoutPayment: '
          . print_r($rdata, true), Zend_Log::INFO);

        // Get payment state
        $paymentStatus = null;
        $orderStatus = null;

        switch( strtolower($rdata['PAYMENTINFO'][0]['PAYMENTSTATUS']) ) {
            case 'created':
            case 'pending':
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
        $order->gateway_transaction_id = $rdata['PAYMENTINFO'][0]['TRANSACTIONID'];
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
            'gateway_transaction_id' => $rdata['PAYMENTINFO'][0]['TRANSACTIONID'],
            'amount' => $rdata['AMT'], // @todo use this or gross (-fee)?
            'currency' => $rdata['PAYMENTINFO'][0]['CURRENCYCODE'],
        ));

        // Get benefit setting
        $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')->getBenefitStatus($user);

        // Check payment status
        if( $paymentStatus == 'okay' || ($paymentStatus == 'pending' && $giveBenefit) ) {

            //Crowdfunding plugin work
            if($order->source_type == 'sescrowdfunding_userpayrequest') {
                // Update order table info
                $orderPayment->gateway_id = 2;
                $orderPayment->gateway_transaction_id = $rdata['PAYMENTINFO'][0]['TRANSACTIONID'];
                $orderPayment->currency_symbol = $rdata['PAYMENTINFO'][0]['CURRENCYCODE'];
                $orderPayment->release_date = date('Y-m-d H:i:s');
                $orderPayment->save();
            }

            if($order->source_type == 'sescrowdfunding_userpayrequest') {
                $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sescrowdfunding');
            } else if($order->source_type == 'sesvideosell_remainingpayment') {
                $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sesvideosell');
            }
            $tableName = $tableRemaining->info('name');

            if($order->source_type == 'sescrowdfunding_userpayrequest') {
                $select = $tableRemaining->select()->from($tableName)->where('crowdfunding_id =?',$orderPayment->crowdfunding_id);

                $select = $tableRemaining->fetchAll($select);
                $remainingAmt = $select[0]['remaining_payment'] - $rdata['AMT'];
                if($remainingAmt < 0)
                    $orderAmount = 0;
                else
                    $orderAmount = $remainingAmt;

                $tableRemaining->update(array('remaining_payment' => $remainingAmt),array('crowdfunding_id =?'=>$orderPayment->crowdfunding_id));
            } else if($order->source_type == 'sesvideosell_remainingpayment') {

                $select = $tableRemaining->select()->from($tableName)->where('user_id =?',$orderPayment->user_id);

                $select = $tableRemaining->fetchAll($select);
                $remainingAmt = $select[0]['remaining_payment'] - $rdata['AMT'];
                if($remainingAmt < 0)
                    $orderAmount = 0;
                else
                    $orderAmount = $remainingAmt;

                $tableRemaining->update(array('remaining_payment' => $remainingAmt),array('user_id =?'=>$orderPayment->user_id));
            }

            if($order->source_type == 'sescrowdfunding_userpayrequest') {

                // Payment success
                $orderPayment->onOrderComplete();

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
            $orderPayment->gateway_profile_id = $rdata['PAYMENTINFO'][0]['TRANSACTIONID'];
                    $orderPayment->save();
            // Order pending
            $orderPayment->onOrderPending();
                    //update ticket state
            // send notification
            /*Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_pending', array(
                'subscription_title' => $package->title,
                'subscription_description' => $package->description,
                'subscription_terms' => $package->getPackageDescription(),
                'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                    Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            }*/
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

    // Check that gateways match
    if( $order->gateway_id != $this->_gatewayInfo->gateway_id ) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
    // Get related info
    $user = $order->getUser();
    $orderTicket = $order->getSource();
    // Get IPN data
    $rawData = $ipn->getRawData();
    // Get tx table
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    // Chargeback --------------------------------------------------------------
    if( !empty($rawData['case_type']) && $rawData['case_type'] == 'chargeback' ) {
      $orderTicket->onOrderFailure(); // or should we use pending?
			//update ticket state
			Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
    }
    // Transaction Type --------------------------------------------------------
    else if( !empty($rawData['txn_type']) ) {
      switch( $rawData['txn_type'] ) {
        // @todo see if the following types need to be processed:
        // — adjustment express_checkout new_case
        case 'express_checkout':
          // Only allowed for one-time
            switch( $rawData['payment_status'] ) {
              case 'Created': // Not sure about this one
              case 'Pending':
                // @todo this might be redundant
                // Get benefit setting
                $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')->getBenefitStatus($user);
                if( $giveBenefit ) {
                  $orderTicket->onOrderSuccess();
									//update ticket state
									Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
                } else {
                  $orderTicket->onOrderPending();
									//update ticket state
									Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'pending'));
                }
                break;
              case 'Completed':
              case 'Processed':
              case 'Canceled_Reversal': // Not sure about this one
                $orderTicket->onOrderSuccess();
								//update ticket state
									Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
                break;
              case 'Denied':
              case 'Failed':
              case 'Voided':
              case 'Reversed':
                $orderTicket->onOrderFailure();
								//update ticket state
									Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
                break;
              case 'Refunded':
                $orderTicket->onOrderRefund();
								//update ticket state
									Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'refunded'));
                break;
              case 'Expired': // Not sure about this one
                break;
              default:
                throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
                    'payment status %1$s', $rawData['payment_status']));
                break;
            }

          break;
        // What is this?
        default:
          throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
              'type %1$s', $rawData['txn_type']));
          break;
      }
    }
    // Payment Status ----------------------------------------------------------
    else if( !empty($rawData['payment_status']) ) {
      switch( $rawData['payment_status'] ) {
        case 'Created': // Not sure about this one
        case 'Pending':
          // Get benefit setting
          $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')->getBenefitStatus($user);
          if( $giveBenefit ) {
                  $orderTicket->onOrderSuccess();
									//update ticket state
									Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
                } else {
                  $orderTicket->onOrderPending();
									//update ticket state
									Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'pending'));
                }
          break;
        case 'Completed':
        case 'Processed':
        case 'Canceled_Reversal': // Not sure about this one
          $orderTicket->onOrderSuccess();
					//update ticket state
						Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
          break;
        case 'Denied':
        case 'Failed':
        case 'Voided':
        case 'Reversed':
           $orderTicket->onOrderFailure();
					//update ticket state
						Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));

          break;
        case 'Refunded':
         $orderTicket->onOrderRefund();
					//update ticket state
						Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'refunded'));
					break;
        case 'Expired': // Not sure about this one
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
   * Process ipn of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  public function onSubscriptionTransactionIpn(
      Payment_Model_Order $order,
      Engine_Payment_Ipn $ipn)
  {}
  /**
   * Cancel a subscription (i.e. disable the recurring payment profile)
   *
   * @params $transactionId
   * @return Engine_Payment_Plugin_Abstract
   */
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
    else if( is_string($transactionId) ) {
      $profileId = $transactionId;
    }
    else {
      // Should we throw?
      return $this;
    }
    try {
      $r = $this->getService()->cancelRecurringPaymentsProfile($profileId, $note);
    } catch( Exception $e ) {
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
  public function getOrderDetailLink($orderId)
  {
    // @todo make sure this is correct
    // I don't think this works
    if( $this->getGateway()->getTestMode() ) {
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
  public function getTransactionDetailLink($transactionId)
  {
    // @todo make sure this is correct
    if( $this->getGateway()->getTestMode() ) {
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
  {
    $rawData = $ipn->getRawData();
    $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    // Find transactions -------------------------------------------------------
    $transactionId = null;
    $parentTransactionId = null;
    $transaction = null;
    $parentTransaction = null;
    // Fetch by txn_id
    if( !empty($rawData['txn_id']) ) {
      $transaction = $transactionsTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_transaction_id = ?' => $rawData['txn_id'],
      ));
      $parentTransaction = $transactionsTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_parent_transaction_id = ?' => $rawData['txn_id'],
      ));
    }
    // Fetch by parent_txn_id
    if( !empty($rawData['parent_txn_id']) ) {
      if( !$transaction ) {
        $parentTransaction = $transactionsTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_parent_transaction_id = ?' => $rawData['parent_txn_id'],
        ));
      }
      if( !$parentTransaction ) {
        $parentTransaction = $transactionsTable->fetchRow(array(
          'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
          'gateway_transaction_id = ?' => $rawData['parent_txn_id'],
        ));
      }
    }
    // Fetch by transaction->gateway_parent_transaction_id
    if( $transaction && !$parentTransaction &&
        !empty($transaction->gateway_parent_transaction_id) ) {
      $parentTransaction = $transactionsTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_parent_transaction_id = ?' => $transaction->gateway_parent_transaction_id,
      ));
    }
    // Fetch by parentTransaction->gateway_transaction_id
    if( $parentTransaction && !$transaction &&
        !empty($parentTransaction->gateway_transaction_id) ) {
      $transaction = $transactionsTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_parent_transaction_id = ?' => $parentTransaction->gateway_transaction_id,
      ));
    }
    // Get transaction id
    if( $transaction ) {
      $transactionId = $transaction->gateway_transaction_id;
    } else if( !empty($rawData['txn_id']) ) {
      $transactionId = $rawData['txn_id'];
    }
    // Get parent transaction id
    if( $parentTransaction ) {
      $parentTransactionId = $parentTransaction->gateway_transaction_id;
    } else if( $transaction && !empty($transaction->gateway_parent_transaction_id) ) {
      $parentTransactionId = $transaction->gateway_parent_transaction_id;
    } else if( !empty($rawData['parent_txn_id']) ) {
      $parentTransactionId = $rawData['parent_txn_id'];
    }
    // Fetch order -------------------------------------------------------------
    $order = null;
    // Transaction IPN - get order by invoice
    if( !$order && !empty($rawData['invoice']) ) {
      $order = $ordersTable->find($rawData['invoice'])->current();
    }
    // Subscription IPN - get order by recurring_payment_id
    if( !$order && !empty($rawData['recurring_payment_id']) ) {
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
    if( !$order && $parentTransactionId ) {
      $order = $ordersTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_transaction_id = ?' => $parentTransactionId,
      ));
    }
    // Transaction IPN - get order by txn_id
    if( !$order && $transactionId ) {
      $order = $ordersTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_transaction_id = ?' => $transactionId,
      ));
    }
    // Transaction IPN - get order through transaction
    if( !$order && !empty($transaction->order_id) ) {
      $order = $ordersTable->find($parentTransaction->order_id)->current();
    }
    // Transaction IPN - get order through parent transaction
    if( !$order && !empty($parentTransaction->order_id) ) {
      $order = $ordersTable->find($parentTransaction->order_id)->current();
    }
    // Process generic IPN data ------------------------------------------------
    // Build transaction info
    if( !empty($rawData['txn_id']) ) {
      $transactionData = array(
        'gateway_id' => $this->_gatewayInfo->gateway_id,
      );
      // Get timestamp
      if( !empty($rawData['payment_date']) ) {
        $transactionData['timestamp'] = date('Y-m-d H:i:s', strtotime($rawData['payment_date']));
      } else {
        $transactionData['timestamp'] = new Zend_Db_Expr('NOW()');
      }
      // Get amount
      if( !empty($rawData['mc_gross']) ) {
        $transactionData['amount'] = $rawData['mc_gross'];
      }
      // Get currency
      if( !empty($rawData['mc_currency']) ) {
        $transactionData['currency'] = $rawData['mc_currency'];
      }
      // Get order/user
      if( $order ) {
        $transactionData['user_id'] = $order->user_id;
        $transactionData['order_id'] = $order->order_id;
      }
      // Get transactions
      if( $transactionId ) {
        $transactionData['gateway_transaction_id'] = $transactionId;
      }
      if( $parentTransactionId ) {
        $transactionData['gateway_parent_transaction_id'] = $parentTransactionId;
      }
      // Get payment_status
      switch( $rawData['payment_status'] ) {
        case 'Canceled_Reversal': // @todo make sure this works
        case 'Completed':
        case 'Created':
        case 'Processed':
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'okay';

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
      if( !$transaction ) {
        $transactionsTable->insert($transactionData);
      }
      // Update transaction
      else {
        unset($transactionData['timestamp']);
        $transaction->setFromArray($transactionData);
        $transaction->save();
      }
      // Update parent transaction on refund?
      if( $parentTransaction && in_array($transactionData['type'], array('refund','reversal')) ) {
        $parentTransaction->state = $transactionData['state'];
        $parentTransaction->save();
      }
    }
    // Process specific IPN data -----------------------------------------------
    if( $order ) {
      $ipnProcessed = false;
      // Subscription IPN
      if( $order->source_type == 'sesevent_order' ) {
        $this->onOrderTicketTransactionIpn($order, $ipn);
        $ipnProcessed = true;
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
  public function getAdminGatewayForm()
  {
    return new Sesbasic_Form_Admin_Gateway_PayPal();
  }
  public function processAdminGatewayForm(array $values)
  {
    return $values;
  }
}
