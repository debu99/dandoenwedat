<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminPaymentSponsorshipController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AdminPaymentSponsorshipController extends Core_Controller_Action_Admin {
  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_sponsorshippaymentrequest');
    $paymentTable = Engine_Api::_()->getItemTable('sesevent_usersponsorshippayrequest');
		$paymentTableName = $paymentTable->info('name');
    $select = $paymentTable->select()
            ->from($paymentTableName)
            ->order('usersponsorshippayrequest_id ASC')
						->where('state =?','pending');
		
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
	public function approveAction(){ 
		$this->view->event = $event = Engine_Api::_()->getItem('sesevent_event', $this->getRequest()->getParam('event_id'));
	  $paymnetReq = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $this->getRequest()->getParam('id'));  
		// In smoothbox
    $this->_helper->layout->setLayout('default-simple');
		$gateway_enable = Engine_Api::_()->getDbtable('usergateways', 'sesevent')->getUserGateway(array('event_id'=>$event->event_id,'user_id'=>$event->user_id));
		if(empty($gateway_enable)){
			$this->view->disable_gateway = true;		
}else{
		$this->view->disable_gateway = false;	
    // Make form
   $this->view->form = $form = new Sesevent_Form_Admin_Sponsorship_Approve();
	 $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
	 $remainingAmount  =  Engine_Api::_()->getDbtable('remainingsponsorshippayments', 'sesevent')->getEventSponsorshipRemainingAmount(array('event_id'=>$event->event_id));
		$orderDetails  =  Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getSponsorshipStats(array('event_id'=>$event->event_id));
		$value = array();
		$value['total_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['totalAmountSale'],$defaultCurrency);
		$value['total_commission_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['commission_amount'],$defaultCurrency);
		$value['remaining_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($remainingAmount->remaining_payment,$defaultCurrency);
		//set value to form
		if($this->_getParam('id',false)){
				$item = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $this->_getParam('id'));
				if($item){
					$itemValue = $item->toArray();
					$value = array_merge($itemValue,$value);
					$value['requested_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($itemValue['requested_amount'],$defaultCurrency);
				}else{
					return $this->_forward('requireauth', 'error', 'core');	
				}
		}
	  if(empty($_POST))
		  $form->populate($value);
		
		if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
		if($item->requested_amount < $_POST['release_amount']){
			$form->addError('Release amount must be less than or equal to requested amount.');
			return;
		}
			$db = Engine_Api::_()->getDbtable('usersponsorshippayrequests', 'sesevent')->getAdapter();
    	$db->beginTransaction();
		try{
			$tableOrder = Engine_Api::_()->getDbtable('usersponsorshippayrequests', 'sesevent');
			$order = $item;
			$order->release_amount = $_POST['release_amount'];
			$order->admin_message = $_POST['admin_message'];
			$order->release_date	 = date('Y-m-d h:i:s');
			$order->save();
			$db->commit();
			
		  //Notification work
      $viewer = Engine_Api::_()->user()->getViewer();
			$owner = $event->getOwner();
			Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $event, 'sesevent_event_adminsponsorshippaymentapprove', array());
			
			//Payment approve mail send to event owner
			Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner, 'sesevent_sponsorshippayment_adminrequestapproved', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
			
			$session = new Zend_Session_Namespace();
      $session->payment_request_id = $order->usersponsorshippayrequest_id;
			$this->view->status = true;
			$this->view->message = Zend_Registry::get('Zend_Translate')->_('Processing...');
			return $this->_forward('success', 'utility', 'core', array(
						'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('route' => 'default','module' => 'sesevent', 'controller' => 'payment-sponsorship', 'action' => 'process'),'admin_default',true),
						'messages' => array($this->view->message)
			));
		}catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
}
	}
	public function processAction(){
		$session = new Zend_Session_Namespace();
		$viewer = Engine_Api::_()->user()->getViewer();		
		if(!$session->payment_request_id)
			return $this->_forward('requireauth', 'error', 'core');
		
		$item = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $session->payment_request_id);
		$event = Engine_Api::_()->getItem('sesevent_event', $item->event_id);
    // Get gateway
    $gatewayId = $item->gateway_id;
		$gateway = Engine_Api::_()->getDbtable('usergateways', 'sesevent')->getUserGateway(array('event_id'=>$event->event_id,'user_id'=>$event->user_id));
		if( !$gatewayId ||
        !($gateway) ||
        !($gateway->enabled) ) {
       return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
    }
    $this->view->gateway = $gateway;
		$this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway('sponsorship');
		$plugin = $gateway->getPlugin('sponsorship');
		$ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
    // Process
    $ordersTable->insert(array(
        'user_id' => $viewer->getIdentity(),
        'gateway_id' => 2,
        'state' => 'pending',
        'creation_date' => new Zend_Db_Expr('NOW()'),
        'source_type' => 'sesevent_usersponsorshippayrequest',
        'source_id' => $item->usersponsorshippayrequest_id,
    ));
		$session = new Zend_Session_Namespace();
    $session->sesevent_order_id = $order_id = $ordersTable->getAdapter()->lastInsertId(); 
		$session->sesevent_item_id = $item->getIdentity();    
    // Prepare host info
    $schema = 'http://';
    if( !empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"]) ) {
      $schema = 'https://';
    }
    $host = $_SERVER['HTTP_HOST'];
    // Prepare transaction
    $params = array();
    $params['language'] = $viewer->language;
    $localeParts = explode('_', $viewer->language);
		if( count($localeParts) > 1 ) {
			$params['region'] = $localeParts[1];
		}
    $params['vendor_order_id'] = $order_id;
    $params['return_url'] = $schema . $host
      .  $this->view->url(array('action' => 'return', 'controller' => 'payment-sponsorship', 'module' => 'sesevent'), 'admin_default', true)
      . '/?state=' . 'return&order_id=' . $order_id;
    $params['cancel_url'] = $schema . $host
      .  $this->view->url(array('action' => 'return', 'controller' => 'payment-sponsorship', 'module' => 'sesevent'), 'admin_default', true)
      . '/?state=' . 'cancel&order_id=' . $order_id;
    $params['ipn_url'] = $schema . $host
      .  $this->view->url(array('action' => 'index', 'controller' => 'ipn', 'module' => 'payment'), 'admin_default', true).'&order_id=' . $order_id;
    // Process transaction
	
    $transaction = $plugin->createOrderTransaction($item,$event,$params);
		
    // Pull transaction params
    $this->view->transactionUrl = $transactionUrl = $gatewayPlugin->getGatewayUrl();		
    $this->view->transactionMethod = $transactionMethod = $gatewayPlugin->getGatewayMethod();
    $this->view->transactionData = $transactionData = $transaction->getData();
    // Handle redirection
    if( $transactionMethod == 'GET' ) {
     $transactionUrl .= '?' . http_build_query($transactionData);
     return $this->_helper->redirector->gotoUrl($transactionUrl, array('prependBase' => false));
    }
    // Post will be handled by the view script
	}
	public function returnAction() {
		$session = new Zend_Session_Namespace();
    // Get order
		$orderId = $this->_getParam('order_id', null);
		$orderPaymentId = $session->sesevent_order_id;
		$orderPayment = Engine_Api::_()->getItem('payment_order', $orderPaymentId);
		$item_id = $session->sesevent_item_id ;
		$item = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $item_id);
    if (!$orderPayment || ($orderId != $orderPaymentId) ||
			 ($orderPayment->source_type != 'sesevent_usersponsorshippayrequest') ||
			 !($user_order = $orderPayment->getSource()) ) {
			return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
    }
		$gateway = Engine_Api::_()->getDbtable('usergateways', 'sesevent')->getUserGateway(array('event_id'=>$user_order->event_id));    
		if( !$gateway )
      return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
    // Get gateway plugin
    $plugin = $gateway->getPlugin('sponsorship');
    unset($session->errorMessage);
    try {
     //get all params 
      $params = $this->_getAllParams();
      $status = $plugin->orderTransactionReturn($orderPayment, $params,$item);
    } catch (Payment_Model_Exception $e) {
      $status = 'failure';
      $session->errorMessage = $e->getMessage();
    }
    return $this->_finishPayment($status,$orderPayment->source_id);
  }
  protected function _finishPayment($state = 'active',$orderPaymentId) {
		$session = new Zend_Session_Namespace();
    // Clear session
    $errorMessage = $session->errorMessage;
    $session->unsetAll();
    $session->errorMessage = $errorMessage;
    // Redirect
    if ($state == 'free') {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    } else {
			 return $this->_helper->redirector->gotoRoute(array('action' => 'finish', 'state' => $state));
    }
  }
  public function finishAction() {
    $session = new Zend_Session_Namespace();
    if (!empty($session->sesevent_order_id))
      $session->sesevent_order_id = '';
    $orderTrabsactionDetails = array('state' => $this->_getParam('state'), 'errorMessage' => $session->errorMessage);
    $session->sesevent_order_details = $orderTrabsactionDetails;
		$state = $this->_getParam('state');
	  if(!$state)
	 	 return $this->_forward('notfound', 'error', 'core');
		$this->view->error = $error =  $session->errorMessage;
		$session->unsetAll();
  } 
	public function cancelAction(){
		$this->view->event = $event = Engine_Api::_()->getItem('sesevent_event', $this->getRequest()->getParam('event_id'));
	  $paymnetReq = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $this->getRequest()->getParam('id'));  

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Make form
   $this->view->form = $form = new Sesbasic_Form_Delete();
		 $form->setTitle('Cancel Payment request?');
		 $form->setDescription('Are you sure that you want to cancel this payment request?');
		 $form->submit->setLabel('Cancel');
		 
    if (!$paymnetReq) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Paymnet request doesn't exists or not authorized to cancel");
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $paymnetReq->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $paymnetReq->state = 'cancelled';
			$paymnetReq->save();
      $db->commit();
      
      //Notification work
      $viewer = Engine_Api::_()->user()->getViewer();
			$owner = $event->getOwner();
			Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $event, 'sesevent_event_adminsponsorshippaymentcancel', array());
			
			//Payment cancel mail send to event owner
			Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner, 'sesevent_sponsorshippayment_adminrequestcancel', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
			
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Payment Request has been cancelled.');
    return $this->_forward('success', 'utility', 'core', array(
               'smoothboxClose' => 10,
							 'parentRefresh' => 10,
               'messages' => array($this->view->message)
    ));	
	}
}
