<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: 2Checkout.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Plugin_Gateway_2Checkout extends Engine_Payment_Plugin_Abstract
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
  }
  /**
   * Get the service API
   *
   * @return Engine_Service_2Checkout
   */
  public function getService()
  {
    return $this->getGateway()->getService();
  }
  /**
   * Get the gateway object
   *
   * @return Engine_Payment_Gateway_2Checkout
   */
  public function getGateway()
  {
    if( null === $this->_gateway ) {
      $class = 'Engine_Payment_Gateway_2Checkout';
      Engine_Loader::loadClass($class);
      $gateway = new $class(array(
        'config' => (array) $this->_gatewayInfo->config,
        'testMode' =>  $this->_gatewayInfo->test_mode,
        'currency' => Engine_Api::_()->sesevent()->getCurrentCurrency()
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
  public function detectIpn(array $params)
  {
    $expectedCommonParams = array(
      'message_type', 'message_description', 'timestamp', 'md5_hash',
      'message_id', 'key_count', 'vendor_id',
    );
    foreach( $expectedCommonParams as $key ) {
      if( !isset($params[$key]) ) {
        return false;
      }
    }
    return true;
  }
  // SE Specific
  /**
   * Create a transaction for a subscription
   *
   * @param User_Model_User $user
   * @param Zend_Db_Table_Row_Abstract $subscription
   * @param Zend_Db_Table_Row_Abstract $package
   * @param array $params
   * @return Engine_Payment_Gateway_Transaction
   */
	public function createSubscriptionTransaction(User_Model_User $user, Zend_Db_Table_Row_Abstract $advertisment, Payment_Model_Package $package, array $params = array()) {}
  public function createOrderTransaction($viewer,
        $order,$event,$params = array())
  {
    // Do stuff to params
    $params['fixed'] = true;
    $params['skip_landing'] = true; 
    // Lookup product id for this order
		$order->gateway_type = '2Checkout';
		$order->save();
    $productInfo = $this->getService()->detailVendorProduct($order->getGatewayIdentity());
		if (!isset($productInfo['product_id'])) {
        throw new Engine_Payment_Plugin_Exception('Unable to create product on 2checkout.');
    }
    $params['product_id'] = $productInfo['product_id'];
    $params['quantity'] = 1;
		$params['x_receipt_link_url'] = $params['return_url'];
		$params['return_url'] = $params['return_url'];
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
      Payment_Model_Order $order, array $params = array())
  {}
	public function orderTicketTransactionReturn($order,$params = array()){
    // Check that gateways match
    if( $order->gateway_id != $this->_gatewayInfo->gateway_id ) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
     // Get related info
    $user = $order->getUser();
    $orderTicket = $order->getSource();
  if($orderTicket->state == 'pending' ) {
      return 'pending';
    }
    // Let's log it
    $this->getGateway()->getLog()->log('Return: '
        . print_r($params, true), Zend_Log::INFO);
    // Check for processed
    if( empty($params['credit_card_processed']) ) {
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
			 $orderTicket->onOrderFailure();
			Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
      throw new Payment_Model_Exception('There was an error processing your ' .
          'transaction. Please try again later.');
    }
    // Ensure vendor ids match
    if( $params['sid'] != $this->getGateway()->getVendorIdentity() ) {
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
      throw new Payment_Model_Exception('There was an error processing your ' .
          'transaction. Please try again later.');
    }
    // Validate return
    try {
      $this->getGateway()->validateReturn($params);
    } catch( Exception $e ) {
      if( !$this->getGateway()->getTestMode() ) {
        // This is a sanity error and cannot produce information a user could use
        // to correct the problem.
        throw new Payment_Model_Exception('There was an error processing your ' .
            'transaction. Please try again later.');
      } else {
        echo $e; // For test mode
      }
    }
    // @todo process total?
    // Update order with profile info and complete status?
    $order->state = 'complete';
    $order->gateway_order_id = $params['order_number'];
    $order->save();
    // Transaction is inserted on IPN since it doesn't send the amount back
    // Get benefit setting
    $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
        ->getBenefitStatus($user);
    // Enable now
    if( $giveBenefit ) {
			$currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
			$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
			$settings = Engine_Api::_()->getApi('settings', 'core');
			$currencyValue = 1;
			if($currentCurrency != $defaultCurrency){
					$currencyValue = $settings->getSetting('sesmultiplecurrency.'.$currentCurrency);
			}				
      $orderTicket->gateway_id = $this->_gatewayInfo->gateway_id;
      $orderTicket->gateway_transaction_id = $params['order_number']; // This is the same as sale_id
			$orderTicket->currency_symbol = $currentCurrency;
			$orderTicket->change_rate = $currencyValue;
			$orderTicket->save();
				$orderAmount = round($orderTicket->total_service_tax + $orderTicket->total_entertainment_tax + $orderTicket->total_amount,2);
				$commissionValue = round($orderTicket->commission_amount,2);
				if(isset($commissionValue) && $orderAmount > $commissionValue){
					$orderAmount = $orderAmount - $commissionValue;	
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
					  $grandTottal = Engine_Api::_()->sesevent()->getCurrencyPrice(@round($totalAmount,2), $eventOrder->currency_symbol, $eventOrder->change_rate);
				  }				  
				  $orderTicketsDetails = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getOrderTicketDetails(array('order_id' => $orderTicket->order_id));
				  if($eventOrder->ragistration_number) {
						$fileName = $eventOrder->getType().'_'.$eventOrder->getIdentity().'.png';
						if(!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/'.$fileName)){ 
							$qrCode = Engine_Api::_()->sesevent()->generateQrCode($eventOrder->ragistration_number,$fileName);
						} else{ 
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
              $ticketDetails.= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($orderTiDetails->price,2),$eventOrder->currency_symbol,$eventOrder->change_rate); 
            }
            $ticketDetails .= '<br />';
            if($orderTiDetails->service_tax >0) {
	            $ticketDetails .= 'Service Tax:' . @round($orderTiDetails->service_tax,2).'%';
	            $ticketDetails .= '<br />';
            }
            if($orderTiDetails->entertainment_tax >0) {
			        $ticketDetails .= 'Entertainment Tax:' . @round($orderTiDetails->entertainment_tax,2).'%'; 
		        }
		        $ticketDetails .= '</td>';
	          $ticketDetails .= '<td align="center">' .$orderTiDetails->quantity . '</td>';	          
	          $ticketDetails .= '<td align="right">';
	          $price = $orderTiDetails->price; 
	          if($price <= 0) {
		          $ticketDetails .= 'FREE';
	          } else {
		          $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(round($price*$orderTiDetails->quantity,2),$this->order->currency_symbol,$this->order->change_rate);
		          $ticketDetails .= '<br />';
	          }
	          if($orderTiDetails->service_tax > 0) {
		          $serviceTax = round(($price *($orderTiDetails->service_tax/100) )*$orderTiDetails->quantity,2); 
		          $ticketDetails .= 'Service Tax:';
		          $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice($serviceTax,$this->order->currency_symbol,$this->order->change_rate);
		          $ticketDetails .= '<br />';
		        }
		        if($orderTiDetails->entertainment_tax > 0) { 
			        $entertainmentTax = round(($price *($orderTiDetails->entertainment_tax/100) ) * $orderTiDetails->quantity,2);
			        $ticketDetails .= 'Entertainment Tax:';
			        $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($entertainmentTax,2),$this->order->currency_symbol,$this->order->change_rate);
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
			    $body = '<table style="background-color:#f9f9f9;border:#ececec solid 1px;width:100%;"><tr><td><div style="margin:0 auto;width:600px;font:normal 13px Arial,Helvetica,sans-serif;padding:20px;"><div style="margin-bottom:10px;overflow:hidden;"><div style="float:left;"><b>Order Id: #' . $orderTicket->order_id . '</b></div><div style="float:right;"><b>'.$totalAmounts.'</b></div></div><table style="background-color:#fff;border:#ececec solid 1px;margin-bottom:20px;" cellpadding="0" cellspacing="0" width="100%"><tr valign="top" style="width:50%;"><td><div style="border-bottom:#ececec solid 1px;padding:20px;"><b style="display:block;margin-bottom:5px;">Ordered For</b><span style="display:block;margin-bottom:5px;"><a href="'.$event->getHref().'" style="color:#39F;text-decoration:none;">'.$event->getTitle().'</a></span><span style="display:block;margin-bottom:5px;">'.$event->starttime.' - '.$event->endtime.'</span></div><div style="padding:20px;border-bottom:#ececec solid 1px;"> <b style="display:block;margin-bottom:5px;">Ordered By</b><span style="display:block;margin-bottom:5px;"><a href="'.$orderTicket->getOwner()->getHref().'" style="color:#39F;text-decoration:none;">'.$orderTicket->fname.'</a></span><span style="display:block;margin-bottom:5px;">'.$orderTicket->email.'</span></div><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Payment Information</b><span style="display:block;margin-bottom:5px;">Payment Method: '.$orderTicket->gateway_type.'</span></div></td><td style="border-left:#ececec solid 1px;width:50%;"><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Order Information</b><span style="display:block;margin-bottom:5px;">Ordered Date: '.$orderTicket->creation_date.'</span><span style="display:block;margin-bottom:5px;">Service Tax: $'.round($orderTicket->total_service_tax,2).'</span>  <span style="display:block;margin-bottom:5px;">Entertainment Tax: $'.round($orderTicket->total_entertainment_tax,2).'</span> </div><div style="padding:20px;text-align:center;"><img style="height:150px;width:150px;" src="'.$qrCode.'"></div></td></tr></table><div style="margin-bottom:10px;"><b class="bold">Order Details</b></div><table border="1" bordercolor="#ececec" style="background-color:#fff;margin-bottom:20px;border-collapse: collapse;" cellpadding="10" cellspacing="0" width="100%"><tbody><tr><th>Ticket Name</th><th>Price</th><th>Quantity</th><th>Sub Total</th></tr>' . $ticketDetails . '</tbody></table><div style="background-color:#fff;border:1px solid #ececec;padding:10px;"><div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Sub Total</span><span style="float:right;">'.$sub_total.'</span> </div><div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Service Taxes</span><span style="float:right;">'.$service_tax_t.'</span> </div><div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Entertainment Taxes</span><span style="float:right;">'.$entertainment_tax_t.'</span> </div><div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;"><b>Grand Total</b></span><span style="float:right;"><b>'.$grandTottal.'</b></span></div></div></div> </td></tr></table>';			    
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
				    $ticketsContent .= 'Order #' .$eventOrder->order_id;
				    $ticketsContent .= 'Ordered by' .$user->getTitle();
				    $ticketsContent .= 'on' . Engine_Api::_()->sesevent()->dateFormat($eventOrder->creation_date);
				    $ticketsContent .= '</div></div>';

				    $ticketsContent .= '<div style="clear:both;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Attendee Info</div><div style="margin:30px 5px 20px;text-align:right;">';
				    $ticketsContent .= $item->first_name . $item->last_name . '<br />';
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
	}
	public function onOrderTicketTransactionIpn( Payment_Model_Order $order, Engine_Payment_Ipn $ipn){
    // Check that gateways match
    if( $order->gateway_id != $this->_gatewayInfo->gateway_id ) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
		$user = $order->getUser();
		$orderTicket = $order->getSource();
    // Get IPN data
    $rawData = $ipn->getRawData();
    // Get tx table
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');

    // Update subscription
    $subscriptionUpdated = false;
    if( !empty($rawData['sale_id']) && empty($subscription->gateway_profile_id) ) {
      $subscriptionUpdated = true;
      $subscription->gateway_profile_id = $rawData['sale_id'];
    }
    if( !empty($rawData['invoice_id']) && empty($subscription->gateway_transaction_id) ) {
      $subscriptionUpdated = true;
      $subscription->gateway_profile_id = $rawData['invoice_id'];
    }
    if( $subscriptionUpdated ) {
      $subscription->save();
    }
    // switch message_type
    switch( $rawData['message_type'] ) {
      case 'ORDER_CREATED':
      case 'FRAUD_STATUS_CHANGED':
      case 'INVOICE_STATUS_CHANGED':
        // Check invoice and fraud status
        if( strtolower($rawData['invoice_status']) == 'declined' ||
            strtolower($rawData['fraud_status']) == 'fail' ) {
          // Payment failure
           $orderTicket->onOrderFailure();
					//update ticket state
						Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
          // send notification
        } else if( strtolower($rawData['fraud_status']) == 'wait' ) {
          // This is redundant, the same thing is done upon return         
        } else {
          // Payment Success
           $orderTicket->onOrderSuccess();
					//update ticket state
					Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
          // send notification
        }
        break;
      case 'REFUND_ISSUED':
        // Payment Refunded
        $orderTicket->onOrderRefund();
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'refunded'));
        // send notification
        break;
      case 'RECURRING_INSTALLMENT_SUCCESS':
         $orderTicket->onOrderSuccess();
					//update ticket state
					Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
        // send notification
        break;
      case 'RECURRING_INSTALLMENT_FAILED':
				// Payment failure
				$orderTicket->onOrderFailure();
				//update ticket state
				Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
        // send notification
        break;
      case 'RECURRING_STOPPED':
         // Payment failure
				 $orderTicket->onOrderFailure();
				//update ticket state
					Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'failed'));
        // send notification
        break;
      case 'RECURRING_COMPLETE':
         // Payment failure
           $orderTicket->onOrderSuccess();
					//update ticket state
						Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id'=>$orderTicket->order_id,'state'=>'complete'));
        // send notification
        break;
      /*
      case 'RECURRING_RESTARTED':
        break;
       * 
       */
      default:
        throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
            'type %1$s', $rawData['message_type']));
        break;
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
  public function cancelSubscription($transactionId)
  {
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
    return 'https://www.2checkout.com/va/sales/detail?sale_id=' . $orderId;
  }
  /**
   * Generate href to a page detailing the transaction
   *
   * @param string $transactionId
   * @return string
   */
  public function getTransactionDetailLink($transactionId)
  {
    return 'https://www.2checkout.com/va/sales/get_list_sale_paged?invoice_id=' . $transactionId;
  }
  /**
   * Get raw data about an order or recurring payment profile
   *
   * @param string $orderId
   * @return array
   */
  public function getOrderDetails($orderId)
  {
    return $this->getService()->detailSale($orderId);
  }
  /**
   * Get raw data about a transaction
   *
   * @param $transactionId
   * @return array
   */
  public function getTransactionDetails($transactionId)
  {
    return $this->getService()->detailInvoice($transactionId);
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
    $transaction = null;
    // Fetch by invoice_id
    if( !empty($rawData['invoice_id']) ) {
      $transaction = $transactionsTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_transaction_id = ?' => $rawData['invoice_id'],
      ));
    }
    if( $transaction && !empty($transaction->gateway_transaction_id) ) {
      $transactionId = $transaction->gateway_transaction_id;
    } else {
      $transactionId = @$rawData['invoice_id'];
    }
    // Fetch order -------------------------------------------------------------
    $order = null;
    // Get order by vendor_order_id
    if( !$order && !empty($rawData['vendor_order_id']) ) {
      $order = $ordersTable->find($rawData['vendor_order_id'])->current();
    }
    // Get order by invoice_id
    if( !$order && $transactionId ) {
      $order = $ordersTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_transaction_id = ?' => $transactionId,
      ));
    }
    // Get order by sale_id
    if( !$order && !empty($rawData['sale_id']) ) {
      $order = $ordersTable->fetchRow(array(
        'gateway_id = ?' => $this->_gatewayInfo->gateway_id,
        'gateway_order_id = ?' => $rawData['sale_id'],
      ));
    }
    // Get order by order_id through transaction
    if( !$order && $transaction && !empty($transaction->order_id) ) {
      $order = $ordersTable->find($transaction->order_id)->current();
    }
    // Update order with order/transaction id if necessary
    $orderUpdated = false;
    if( !empty($rawData['invoice_id']) && empty($order->gateway_transaction_id) ) {
      $orderUpdated = true;
      $order->gateway_transaction_id = $rawData['invoice_id'];
    }
    if( !empty($rawData['sale_id']) && empty($order->gateway_order_id) ) {
      $orderUpdated = true;
      $order->gateway_order_id = $rawData['sale_id'];
    }
    if( $orderUpdated ) {
      $order->save();
    }
    // Process generic IPN data ------------------------------------------------
    // Build transaction info
    if( !empty($rawData['invoice_id']) ) {
      $transactionData = array(
        'gateway_id' => $this->_gatewayInfo->gateway_id,
      );
      // Get timestamp
      if( !empty($rawData['payment_date']) ) {
        $transactionData['timestamp'] = date('Y-m-d H:i:s', strtotime($rawData['timestamp']));
      } else {
        $transactionData['timestamp'] = new Zend_Db_Expr('NOW()');
      }
      // Get amount
      if( !empty($rawData['invoice_list_amount']) ) {
        $transactionData['amount'] = $rawData['invoice_list_amount'];
      } else if( $transaction ) {
        $transactionData['amount'] = $transaction->amount;
      } else if( !empty($rawData['item_list_amount_1']) ) {
        // For recurring success
        $transactionData['amount'] = $rawData['item_list_amount_1'];
      }
      // Get currency
      if( !empty($rawData['list_currency']) ) {
        $transactionData['currency'] = $rawData['list_currency'];
      } else if( $transaction ) {
        $transactionData['currency'] = $transaction->currency;
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
      if( !empty($rawData['sale_id']) ) {
        $transactionData['gateway_order_id'] = $rawData['sale_id'];
      }
      // Get payment_status
      if( !empty($rawData['invoice_status']) ) {
        if( $rawData['invoice_status'] == 'declined' ) {
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'failed';
        } else if( $rawData['fraud_status'] == 'fail' ) {
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'failed-fraud';
        } else if( $rawData['fraud_status'] == 'wait' ) {
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'pending-fraud';
        } else {
          $transactionData['type'] = 'payment';
          $transactionData['state'] = 'okay';
        }
      }
      if( $transaction &&
          ($transaction->type == 'refund' || $transaction->state == 'refunded') ) {
        $transactionData['type'] = $transaction->type;
        $transactionData['state'] = $transaction->state;
      }
      // Special case for refund_issued
      $childTransactionData = array();
      if( $rawData['message_type'] == 'REFUND_ISSUED' ) {
        $childTransactionData = $transactionData;
        $childTransactionData['gateway_parent_transaction_id'] = $childTransactionData['gateway_transaction_id'];
        //unset($childTransactionData['gateway_transaction_id']); // Should we unset this?
        $childTransactionData['amount'] = - $childTransactionData['amount'];
        $childTransactionData['type'] = 'refund';
        $childTransactionData['state'] = 'refunded';

        // Update parent transaction
        $transactionData['state'] = 'refunded';
      }
      // Insert or update transactions
      if( !$transaction ) {
        $transactionsTable->insert($transactionData);
      }
      // Update transaction
      else {
        unset($transactionData['timestamp']);
        $transaction->setFromArray($transactionData);
        $transaction->save();
      }
      // Insert new child transaction
      if( $childTransactionData ) {
        $childTransactionExists = $transactionsTable->select()
          ->from($transactionsTable, new Zend_Db_Expr('TRUE'))
          ->where('gateway_transaction_id = ?', $childTransactionData['gateway_transaction_id'])
          ->where('type = ?', $childTransactionData['type'])
          ->where('state = ?', $childTransactionData['state'])
          ->limit(1)
          ->query()
          ->fetchColumn();
        if( !$childTransactionExists ) {
          $transactionsTable->insert($childTransactionData);
        }
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
      // Unknown IPN - could not be processed
      if( !$ipnProcessed ) {
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
  public function getAdminGatewayForm()
  {
    return new Sesevent_Form_Admin_Gateway_2Checkout();
  }
  public function processAdminGatewayForm(array $values)
  {
    // Should we get the vendor_id and secret word?
    $info = $this->getService()->detailCompanyInfo();
    $values['vendor_id'] = $info['vendor_id'];
    $values['secret'] = $info['secret_word'];
    return $values;
  }
}