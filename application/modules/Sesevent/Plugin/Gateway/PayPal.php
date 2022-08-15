<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: PayPal.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Plugin_Gateway_PayPal extends Engine_Payment_Plugin_Abstract
{
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
      $class = 'Engine_Payment_Gateway_PayPal';
      Engine_Loader::loadClass($class);
      $gateway = new $class(array(
        'config' => (array) $this->_gatewayInfo->config,
        'testMode' => $this->_gatewayInfo->test_mode,
        'currency' => Engine_Api::_()->sesevent()->getCurrentCurrency(),
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
    $transaction->process($this->getGateway());
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
  public function createOrderTransaction($viewer,$order,$event,array $params = array())
  {
    // description
    $description = $event->title;
    if( strlen($description) > 128 ) {
      $description = substr($description, 0, 125) . '...';
    } else if( !$description || !strlen($description) ) {
      $description = 'N/A';
    }
    if( function_exists('iconv') && strlen($description) != iconv_strlen($description) ) {
      // PayPal requires that DESC be single-byte characters
      $description = @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $description);
    }
		$currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
		$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$currencyValue = 1;
		if($currentCurrency != $defaultCurrency){
				$currencyValue = $settings->getSetting('sesmultiplecurrency.'.$currentCurrency);
		}
		$ticket_order = array();
		$orderTicket = $order->getTicket(array('order_id'=>$order->order_id,'event_id'=>$event->event_id,'user_id'=>$viewer->user_id));
		$priceTotal = $entertainment_tax = $service_tax = $totalTicket =  0;
		foreach($orderTicket as $val){
			$ticket = Engine_Api::_()->getItem('sesevent_ticket', $val['ticket_id']);
			$price = @round($ticket->price*$currencyValue,2);
			$entertainmentTax = @round($ticket->entertainment_tax,2);
			$taxEntertainment = @round($price *($entertainmentTax/100),2);
			$serviceTax = @round($ticket->service_tax,2);
			$taxService = @round($price *($serviceTax/100),2);
			$priceTotal = @round($val['quantity']*$price + $priceTotal,2);		 
		  $service_tax = @round(($taxService*$val['quantity']) + $service_tax,2);
		  $entertainment_tax = @round(($taxEntertainment * $val['quantity']) + $entertainment_tax,2);
			$totalTicket = $val['quantity']+$totalTicket;
			$ticket_order[] = array(
				'NAME' => $ticket->name,
				'AMT' => $price,
				'QTY' => $val['quantity'],
			);
		}
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')):
      $couponSessionCode = '-'.'-'.$event->getType().'-'.$event->event_id.'-0'; 
      $priceTotal = @isset($_SESSION[$couponSessionCode]) ? round($priceTotal - $_SESSION[$couponSessionCode]['discount_amount']) : $priceTotal;
    endif;
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit')):
      $creditCode =  'credit'.'-sesevent-'.$this->event->event_id.'-'.$this->event->event_id;
      $sessionCredit = new Zend_Session_Namespace($creditCode);
      if(isset($sessionCredit->total_amount) && $sessionCredit->total_amount > 0): 
        $priceTotal = $sessionCredit->total_amount;
      endif;
    endif;
		$totalTaxtAmt = @round($service_tax+$entertainment_tax,2);
		$subTotal = @round($priceTotal-$totalTaxtAmt,2);
		$order->total_amount = @round(($priceTotal/$currencyValue),2);
		$order->change_rate = $currencyValue;
		$order->total_service_tax = @round(($service_tax/$currencyValue),2);
		$order->total_entertainment_tax = @round(($entertainment_tax/$currencyValue),2);
		$order->creation_date	= date('Y-m-d H:i:s');
		$totalAmount = round($priceTotal+$service_tax+$entertainment_tax,2);
		$order->total_tickets = $totalTicket;
		$order->gateway_type = 'Paypal';
		$commissionType = Engine_Api::_()->authorization()->getPermission($viewer,'sesevent_event','event_admincomn');
		$commissionTypeValue = Engine_Api::_()->authorization()->getPermission($viewer,'sesevent_event','event_commission');
		//%age wise
		if($commissionType == 1 && $commissionTypeValue > 0){
				$order->commission_amount = round(($priceTotal/$currencyValue) * ($commissionTypeValue/100),2);
		}else if($commissionType == 2 && $commissionTypeValue > 0){
				$order->commission_amount = $commissionTypeValue;
		}
		$order->save();
		$params['driverSpecificParams']['PayPal'] = array(
			'AMT' => @round($priceTotal+$totalTaxtAmt, 2),
			'ITEMAMT' => @round($priceTotal, 2),
			'SELLERID' => '1',
      'INVNUM' => $order->getIdentity(),
			'DESC'=>$description,
			'SHIPPINGAMT' => 0,
			'TAXAMT'=>$totalTaxtAmt,
			'SOLUTIONTYPE' => 'sole',
	);
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
	public function orderTicketTransactionReturn($order,$params = array()){
    // Check that gateways match
    if( $order->gateway_id != $this->_gatewayInfo->gateway_id ) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }    
    // Get related info
    $user = $order->getUser();
    $orderTicket = $order->getSource();
    if ($orderTicket->state == 'pending') 
    {
      return 'pending';
    }
    // Check for cancel state - the user cancelled the transaction
    if( $params['state'] == 'cancel' ) {
      // Cancel order and subscription?
      $order->onCancel();
      $orderTicket->onOrderFailure();
			Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
      // Error
      throw new Payment_Model_Exception('Your payment has been cancelled and ' .
          'not been charged. If this is not correct, please try again later.');
    }
    // Check params
    if( empty($params['token']) ) {
      // Cancel order and subscription?
      $order->onFailure();
      $orderTicket->onOrderFailure();
      // This is a sanity error and cannot produce information a user could use
			Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
      // to correct the problem.
      throw new Payment_Model_Exception('There was an error processing your ' .
          'transaction. Please try again later.');
    }
    // Get details
    try {
      $data = $this->getService()->detailExpressCheckout($params['token']);
    } catch( Exception $e ) {
      // Cancel order and subscription?
      $order->onFailure();
      $orderTicket->onOrderFailure();
			Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
      throw new Payment_Model_Exception('There was an error processing your ' .
          'transaction. Please try again later.');
    }
    // Let's log it
    $this->getGateway()->getLog()->log('ExpressCheckoutDetail: '
        . print_r($data, true), Zend_Log::INFO);
		//payment currency
		$currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
		$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$currencyValue = 1;
		if($currentCurrency != $defaultCurrency){
				$currencyValue = $settings->getSetting('sesmultiplecurrency.'.$currentCurrency);
		}
      // Do payment
      try {
        $rdata = $this->getService()->doExpressCheckoutPayment($params['token'],
         $params['PayerID'], array(
          'PAYMENTACTION' => 'Sale',
          'AMT' => $data['AMT'],
          'CURRENCYCODE' => $this->getGateway()->getCurrency(),
        ));
      } catch( Exception $e ) {
        // Log the error
        $this->getGateway()->getLog()->log('DoExpressCheckoutPaymentError: '
            . $e->__toString(), Zend_Log::ERR);   
        // Cancel order and subscription?
        $order->onFailure();
        $orderTicket->onOrderFailure();
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
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
      // Check payment status
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')){
          $couponSessionCode = '-'.'-sesevent_event-'.$orderTicket->event_id.'-0'; 
          $orderTicket->ordercoupon_id = Engine_Api::_()->ecoupon()->setAppliedCouponDetails($couponSessionCode);
          $orderTicket->save();
      }
      //For Credit 
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit') && isset($params['creditCode'])) {
        $sessionCredit = new Zend_Session_Namespace($params['creditCode']);
        $orderTicket->credit_point = $sessionCredit->credit_value;  
        $orderTicket->credit_value =  $sessionCredit->purchaseValue;
        $transaction->save();
        $userCreditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
        $userCreditDetailTable->update(array('total_credit' => new Zend_Db_Expr('total_credit - ' . $sessionCredit->credit_value)), array('owner_id =?' => $order->user_id));
      }
      // Insert transaction
      $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
      $transactionsTable->insert(array(
        'user_id' => $order->user_id,
        'gateway_id' => $this->_gatewayInfo->gateway_id,
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'order_id' => $order->order_id,
        'type' => 'payment',
        'state' => $paymentStatus,
        'gateway_transaction_id' => $rdata['PAYMENTINFO'][0]['TRANSACTIONID'],
        'amount' => $rdata['AMT'], // @todo use this or gross (-fee)?
        'currency' => $rdata['PAYMENTINFO'][0]['CURRENCYCODE'],
      ));
      // Get benefit setting
      $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
          ->getBenefitStatus($user); 
      // Check payment status
      if( $paymentStatus == 'okay' ||
          ($paymentStatus == 'pending' && $giveBenefit) ) {
        // Update order table info
        $orderTicket->gateway_id = $this->_gatewayInfo->gateway_id;
        $orderTicket->gateway_transaction_id = $rdata['PAYMENTINFO'][0]['TRANSACTIONID'];
				$orderTicket->currency_symbol = $rdata['PAYMENTINFO'][0]['CURRENCYCODE'];
				$orderTicket->change_rate = $currencyValue;
				$orderTicket->save();
				$orderAmount = round($orderTicket->total_service_tax + $orderTicket->total_entertainment_tax + $orderTicket->total_amount,2);
				$commissionValue = round($orderTicket->commission_amount,2);
				if(isset($commissionValue) && $orderAmount > $commissionValue){
					$orderAmount = $orderAmount - $commissionValue;	
				}else{
					$orderTicket->commission_amount = 0;
				}
				//update EVENT OWNER REMAINING amount
				$tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sesevent');
				$tableName = $tableRemaining->info('name');
				$select = $tableRemaining->select()->from($tableName)->where('event_id =?',$orderTicket->event_id);
				$select = $tableRemaining->fetchAll($select);
				if(count($select)){
					$tableRemaining->update(array('remaining_payment' => new Zend_Db_Expr("remaining_payment + $orderAmount")),array('event_id =?'=>$orderTicket->event_id));
				}else{
					$tableRemaining->insert(array(
						'remaining_payment' => $orderAmount,
						'event_id' => $orderTicket->event_id,
					));
				}
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
        // Payment success
        $orderTicket->onOrderComplete();
        // send notification
        if( $orderTicket->state == 'complete' ) {
          $ticket_id=  Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getTicketId(array('order_id'=>$orderTicket->order_id));
          $tickets = Engine_Api::_()->getItem('sesevent_ticket', $ticket_id);
          $eventOrder = Engine_Api::_()->getItem('sesevent_order', $orderTicket->order_id);
		      //Notification Work
		      $event = Engine_Api::_()->getItem('sesevent_event', $orderTicket->event_id);
					$owner = $event->getOwner();
		      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $user, $event, 'sesevent_event_ticketpurchased', array("ticketName" => $tickets->name));
		      //Activity Feed Work
		      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
		      $action = $activityApi->addActivity($user, $event, 'sesevent_event_ticketpurchased', '',  array("ticketname" => '<b>' . $tickets->name . '</b>'));
			    if ($action) {
				    $activityApi->attachActivity($action, $event);
			    }
			    $totalAmount = @round($orderTicket->total_amount + $orderTicket->total_service_tax + $orderTicket->total_entertainment_tax,2);
			    if($orderTicket->total_tickets){
				    $total_price_t = @round($orderTicket->total_tickets * $tickets->price,2);
				  } else { 
					  $total_price_t = @round($tickets->price,2);
				  }
				  if($eventOrder->total_service_tax > 0){
				    $service_tax_t = Engine_Api::_()->sesevent()->getCurrencyPrice(@round($eventOrder->total_service_tax,2), $eventOrder->currency_symbol, $eventOrder->change_rate);
				  } else { 
					  $service_tax_t = "-";
				  }
				  if($eventOrder->total_entertainment_tax){
				    $entertainment_tax_t = Engine_Api::_()->sesevent()->getCurrencyPrice(@round($eventOrder->total_entertainment_tax,2), $eventOrder->currency_symbol, $eventOrder->change_rate);
				  } else { 
					  $entertainment_tax_t = "-";
				  }
					if($totalAmount <= 0) {
						$grandTottal = 'FREE';
					} else {
					  $grandTottal = Engine_Api::_()->sesevent()->getCurrencyPrice($totalAmount, $eventOrder->currency_symbol, $eventOrder->change_rate);
				  }
				  $orderTicketsDetails = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getOrderTicketDetails(array('order_id' => $orderTicket->order_id));
				  if($eventOrder->ragistration_number) {
						$fileName = $eventOrder->getType().'_'.$eventOrder->getIdentity().'.png';
						if(!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/'.$fileName)){ 
							$qrCode = Engine_Api::_()->sesevent()->generateQrCode($eventOrder->ragistration_number,$fileName);
						}else{
							$qrCode = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') .'/public/sesevent_qrcode/'.$fileName;
						}
					}else
						$qrCode = '';
				  $ticketDetails = '';
				  foreach($orderTicketsDetails as $orderTiDetails) {
	          $ticketDetails .= '<tr><td>'.$orderTiDetails['title'] .'</td>';
	          $ticketDetails .= '<td align="right">';
            if($orderTiDetails->price <= 0){
	            $ticketDetails .= 'FREE';
            } else {
              $ticketDetails.= Engine_Api::_()->sesevent()->getCurrencyPrice($orderTiDetails->price,$eventOrder->currency_symbol,$eventOrder->change_rate); 
            }
            $ticketDetails .= '<br />';
            if($orderTiDetails->service_tax > 0) {
	            $ticketDetails .= 'Service Tax:' . @round($orderTiDetails->service_tax,2).'%';
	            $ticketDetails .= '<br />';
            }
            if($orderTiDetails->entertainment_tax >0) {
			        $ticketDetails .= 'Entertainment Tax:' . @round($orderTiDetails->entertainment_tax,2).'%'; 
		        }
		        $ticketDetails .= '</td>';
	          $ticketDetails .= '<td align="center">' .$orderTiDetails->quantity . '</td>';
	          $price = $orderTiDetails->price; 
	          if($price <= 0) {
	            $ticketDetails .= '<td align="center">';
		          $ticketDetails .= 'FREE';
	          } else {
	            $ticketDetails .= '<td align="right">';
		          $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(round($price*$orderTiDetails->quantity,2),$eventOrder->currency_symbol,$eventOrder->change_rate);
		          $ticketDetails .= '<br />';
	          }
	          if($orderTiDetails->service_tax > 0) {
		          $serviceTax = round(($price *($orderTiDetails->service_tax/100) )*$orderTiDetails->quantity,2); 
		          $ticketDetails .= 'Service Tax:';
		          $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($serviceTax,2),$eventOrder->currency_symbol,$eventOrder->change_rate);
		          $ticketDetails .= '<br />';
		        }
		        if($orderTiDetails->entertainment_tax > 0) { 
			        $entertainmentTax = round(($price *($orderTiDetails->entertainment_tax/100) ) * $orderTiDetails->quantity,2);
			        $ticketDetails .= 'Entertainment Tax:';
			        $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($entertainmentTax,2),$eventOrder->currency_symbol,$eventOrder->change_rate);
			      }
			      $ticketDetails .= '</td>';
						$ticketDetails .= '</tr>';
		      }
		      $totalAmount = @round($orderTicket->total_amount + $orderTicket->total_service_tax + $orderTicket->total_entertainment_tax,2);
		      $totalAmounts = '[';
		      $totalAmounts .= 'Total:';
		      if($totalAmount <= 0) {
		      $totalAmounts .= 'FREE';
		      } else {
			      $totalAmounts .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($totalAmount,2),$orderTicket->currency_symbol, $orderTicket->change_rate);
		      }
		      $totalAmounts .= ']';
		      $sub_total = '';
		      if($orderTicket->total_amount <= 0) {
			      $sub_total .= 'FREE';
		      } else {
			      $sub_total .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($orderTicket->total_amount,2), $orderTicket->currency_symbol, $orderTicket->change_rate);
		      }
		      
			    $body .= '<table style="background-color:#f9f9f9;border:#ececec solid 1px;width:100%;"><tr><td><div style="margin:0 auto;width:600px;font:normal 13px Arial,Helvetica,sans-serif;padding:20px;"><div style="margin-bottom:10px;overflow:hidden;"><div style="float:left;"><b>Order Id: #' . $orderTicket->order_id . '</b></div><div style="float:right;"><b>'.$totalAmounts.'</b></div></div><table style="background-color:#fff;border:#ececec solid 1px;margin-bottom:20px;" cellpadding="0" cellspacing="0" width="100%"><tr valign="top" style="width:50%;"><td><div style="border-bottom:#ececec solid 1px;padding:20px;"><b style="display:block;margin-bottom:5px;">Ordered For</b><span style="display:block;margin-bottom:5px;"><a href="'.( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .$event->getHref().'" style="color:#39F;text-decoration:none;">'.$event->getTitle().'</a></span><span style="display:block;margin-bottom:5px;">'.$event->starttime.' - '.$event->endtime.'</span></div><div style="padding:20px;border-bottom:#ececec solid 1px;"> <b style="display:block;margin-bottom:5px;">Ordered By</b><span style="display:block;margin-bottom:5px;"><a href="'.( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .$orderTicket->getOwner()->getHref().'" style="color:#39F;text-decoration:none;">'.$orderTicket->fname.'</a></span><span style="display:block;margin-bottom:5px;">'.$orderTicket->email.'</span></div><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Payment Information</b><span style="display:block;margin-bottom:5px;">Payment Method: '.$orderTicket->gateway_type.'</span></div></td><td style="border-left:#ececec solid 1px;width:50%;"><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Order Information</b><span style="display:block;margin-bottom:5px;">Ordered Date: '.$orderTicket->creation_date.'</span>';
			    
			    if($orderTicket->total_service_tax)
				    $body .= '<span style="display:block;margin-bottom:5px;">Service Tax: $'.round($orderTicket->total_service_tax,2).'</span>';
			    
			    if($orderTicket->total_entertainment_tax)
				    $body .= '<span style="display:block;margin-bottom:5px;">Entertainment Tax: $'.round($orderTicket->total_entertainment_tax,2).'</span>';
			    
			    $body .= '</div>';
			    
			    if($qrCode)
				    $body .= '<div style="padding:20px;text-align:center;"><img style="height:150px;width:150px;" src="'.$qrCode.'"></div>';

			    $body .= '</td></tr></table><div style="margin-bottom:10px;"><b class="bold">Order Details</b></div><table bordercolor="#ececec"  border="1" style="background-color:#fff;margin-bottom:20px;border-collapse: collapse;" cellpadding="10" cellspacing="0" width="100%"><tbody><tr><th>Ticket Name</th><th>Price</th><th>Quantity</th><th>Sub Total</th></tr>' . $ticketDetails . '</tbody></table><div style="background-color:#fff;border:1px solid #ececec;padding:10px;"><div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Sub Total</span><span style="float:right;">'.$sub_total.'</span> </div>';
			    
			    if($service_tax_t)
				    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Service Taxes</span><span style="float:right;">'.$service_tax_t.'</span></div>';
			    
			    if($entertainment_tax_t)
				    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Entertainment Taxes</span><span style="float:right;">'.$entertainment_tax_t.'</span></div>';
			    
			    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;"><b>Grand Total</b></span><span style="float:right;"><b>'.$grandTottal.'</b></span></div></div></div> </td></tr></table>';

			    //Ticket Details
			    $orderDetails = Engine_Api::_()->getDbTable('orderticketdetails', 'sesevent')->orderTicketDetails(array('order_id' => $orderTicket->order_id));		
			    $ticketsContent = '';
					$pdfCreate = false;
				 //send pdf ticket if seseventpdf extention enabled and activated
				 if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventpdfticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventpdfticket.pluginactivated')){
					 try{						
						$mailApi = Engine_Api::_()->getApi('mail', 'core');
						$mail = $mailApi->create();
						$adminEmail = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.contact');
						$adminTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.name');
						$mail->setFrom($adminEmail, $adminTitle)
										->setSubject("Your ticket to event" . $event->getTitle())
										->setBodyHtml('Hello');
						$mail->addTo($orderTicket->getOwner()->email);						
						 foreach($orderDetails as $keyDet => $item) {
							 	$itemId = $item->getIdentity();
								$pdfname =	Engine_Api::_()->getApi('core', 'seseventpdfticket')->createPdfFile($item,$event,$eventOrder,$user);
								if(!$pdfname){
										$pdfCreate = false;
										break;
								}else{
								 try{
									$pdfTicketFile = APPLICATION_PATH . '/public/sesevent_ticketpdf/'.$pdfname;
									$handle = @fopen($pdfTicketFile, "r");
									while (($buffer = fgets($handle)) !== false) {
										$content .= $buffer;
									}
									$attachment = $mail->createAttachment($content);
									$attachment->filename = "eventticket_$itemId".".pdf";
								 }catch(Exception $e){
										 $pdfCreate = false;
										 break;
										//silence 
									}
								}
								$pdfCreate = true;
						 }
						 if($pdfCreate)
							 $mailApi->send($mail);
					 }catch( Exception $e ){
							//silence 
							$pdfCreate = false;
					 }
				}
				if(!$pdfCreate){
			    foreach($orderDetails as $keyDet => $item) {
				    $ticketsContent .= '<table style="width:100%;"><tr><td><table border="0" cellpadding="0" cellpadding="0"  style="border-collapse:collapse;width:800px;margin:0 auto;font:normal 13px Arial,Helvetica,sans-serif;border:5px solid #ddd;background-color:#fff;"><tbody><tr valign="top"><td style="border-right:5px solid #ddd;width:590px;"><div style="border-bottom:5px solid #ddd;height:110px;display:block;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Event</div>';
				    $ticketsContent .= '<div style="font-size:20px;margin-top:40px;position:inherit;text-align:center;">';
				    $ticketsContent .= $event->getTitle(); 
				    $ticketsContent .= '</div>';
				    $ticketsContent .= '</div><div style="border-bottom:5px solid #ddd;border-right:5px solid #ddd;float:left;height:120px;width:280px;position:relative;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Date+Time</div><div style="bottom:5px;font-size:13px;position:absolute;right:5px;max-width:90%;">';
						$dateinfoParams['starttime'] = true;
						$dateinfoParams['endtime']  =  true;
						$dateinfoParams['timezone']  = true; 
						$dateinfoParams['isPrint']  = true; 
						$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
				    $ticketsContent .= $view->eventStartEndDates($event, $dateinfoParams);
				    $ticketsContent .= '</div></div><div style="border-bottom:5px solid #ddd;float:left;height:120px;width:275px;position:relative;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Location</div><div style="bottom:5px;font-size:13px;position:absolute;right:5px;max-width:90%;">';
				    if($event->location && !$event->is_webinar && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)) {
					    $venue_name = '';
							if($event->venue_name){ 
								$venue_name = '<br />'. $event->venue_name;
							}
					    $location = $event->location . $venue_name;
				    } else {
					    $location = 'Webinar Event';
				    }
				    $ticketsContent .= $location;
				    $ticketsContent .= '</div></div>';
				    $ticketsContent .= '<div style="border-bottom:5px solid #ddd;clear:both;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Order Info</div><div style="margin:30px 5px 20px;text-align:right;">';
				    $ticketsContent .= 'Order # ' .$eventOrder->order_id;
				    $ticketsContent .= 'Ordered by ' .$user->getTitle();
				    $ticketsContent .= 'on ' . Engine_Api::_()->sesevent()->dateFormat($eventOrder->creation_date);
				    $ticketsContent .= '</div></div>';
				    $ticketsContent .= '<div style="clear:both;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Attendee Info</div><div style="margin:30px 5px 20px;text-align:right;">';
				    $ticketsContent .= $item->first_name .' '. $item->last_name . '<br />';
				    $ticketsContent .= $item->mobile . '<br />' . $item->email;
				    $ticketsContent .= '</div></div></td>';
				    $ticketsContent .= '<td style="width:238px;">
            <div style="height:110px;width:100%;">';
            $eventPhoto = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') . $event->getPhotoUrl();
            $ticketsContent .= '<img alt="" src="'.$eventPhoto.'" style="height:100%;object-fit:contain;padding:10px;width:100%;"></div><div style="border-bottom:5px solid #ddd;float:left;height:60px;margin-top:60px;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Payment Method</div><div style="font-size:17px;margin:30px 0 20px;text-align:center;">';
            $ticketsContent .= $eventOrder->gateway_type;
            $ticketsContent .= '</div></div><div style="display:block;float:left;position:relative;text-align:center;width:100%;">';
						if($item->registration_number) {
						$fileName = $item->getType().'_'.$item->getIdentity().'.png';
						if(!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/'.$fileName)){ 
							$fileName = Engine_Api::_()->sesevent()->generateQrCode($item->registration_number,$fileName);
						} else{ 
							$fileName = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') .'/public/sesevent_qrcode/'.$fileName;
						}
					}else
						$qrCode = '';						
            $ticketsContent .= '<img alt="'.$item->registration_number.'" src="'.$fileName.'" style="margin-top:20px;max-width:100px;"></div></td>';
				    $ticketsContent .= '</tr></tbody></table></td></tr></table>';
			    }
				}
				try{
			    //insert in membership table
					$membershipTable = Engine_Api::_()->getDbtable('membership', 'sesevent');
					$membershipTable->insert(array(
						'user_id' => $orderTicket->owner_id,
						'resource_id' => $orderTicket->event_id,
						'active' => 1,
						'resource_approved' => 1,
						'user_approved' => '1',
						'rsvp' => 2,
					));
				}catch (Exception $e){
					//silence	
				}
			if(!$pdfCreate){
			    //Tickets Details
			    Engine_Api::_()->getApi('mail', 'core')->sendSystem($orderTicket->getOwner(), 'sesevent_tikets_details', array('host' => $_SERVER['HTTP_HOST'], 'ticket_body' => $ticketsContent, 'event_title' => $event->getTitle()));
			}
				  //Ticket invoice mail to buyer
			    Engine_Api::_()->getApi('mail', 'core')->sendSystem($orderTicket->getOwner(), 'sesevent_tiketinvoice_buyer', array('invoice_body' => $body, 'host' => $_SERVER['HTTP_HOST']));
			
			    //Ticket Purchased Mail to Event Owner
			    $event_owner = Engine_Api::_()->getItem('user', $event->user_id);
			    Engine_Api::_()->getApi('mail', 'core')->sendSystem($event_owner, 'sesevent_ticketpurchased_eventowner', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'buyer_name' => $user->getTitle(), 'host' => $_SERVER['HTTP_HOST']));
        }
				$orderTicket->creation_date	= date('Y-m-d H:i:s');
				$orderTicket->save();
        return 'active';
      }
      else if( $paymentStatus == 'pending' ) {
        // Update order  info
        $orderTicket->gateway_id = $this->_gatewayInfo->gateway_id;
        $orderTicket->gateway_profile_id = $rdata['PAYMENTINFO'][0]['TRANSACTIONID'];
				$orderTicket->save();
        // Order pending
        $orderTicket->onOrderPending();
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'pending'));

        //Send Mail
        $event = Engine_Api::_()->getItem('sesevent_event', $orderTicket->event_id);
        
				Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sesevent_payment_ticket_pending', array('event_title' => $event->title, 'evnet_description' => $event->description, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        
        return 'pending';
      }
      else if( $paymentStatus == 'failed' ) {
        // Cancel order and subscription?
        $order->onFailure();
        $orderTicket->onOrderFailure();
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
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
  public function onSubscriptionTransactionReturn(
      Payment_Model_Order $order, array $params = array())
  {}
	public function onOrderTicketTransactionIpn(
      Payment_Model_Order $order,
      Engine_Payment_Ipn $ipn
		){
		
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
    return new Sesevent_Form_Admin_Gateway_PayPal();
  }
  public function processAdminGatewayForm(array $values)
  {
    return $values;
  }
}
