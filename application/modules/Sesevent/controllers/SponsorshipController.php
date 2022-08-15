<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: SponsorshipController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_SponsorshipController extends Core_Controller_Action_Standard {
  public function init() {
    if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'view')->isValid())
      return;
    if (!$this->_helper->requireUser->isValid())
      return;
    $id = $this->_getParam('event_id', null);
    $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
    if ($event_id) {
      $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
      if ($event && $event->is_approved)
        Engine_Api::_()->core()->setSubject($event);
      else
        return $this->_forward('notfound', 'error', 'core');
    } else
      return $this->_forward('notfound', 'error', 'core');
  }
	public function createAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
		$is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
		if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'create')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Sponsorship_Create();   
    if (!$this->getRequest()->isPost() || $is_ajax_content) {
			 return;
		}
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    // Process
    $values = $form->getValues();
    $db = Engine_Api::_()->getItemTable('sesevent_sponsorship')->getAdapter();
    $db->beginTransaction();
    try {
			$table = Engine_Api::_()->getDbtable('sponsorships', 'sesevent');
      $sponsorship = $table->createRow();
			$values['event_id'] = $event->event_id;
			$values['owner_id'] = $viewer->getIdentity();
      $sponsorship->setFromArray($values);
      $sponsorship->save();
      // Add photo
      if (!empty($values['photo'])) {
        $sponsorship->setPhoto($form->photo);
      }
      
			//Activity Feed Work
			$activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
			$action = $activityApi->addActivity($viewer, $event, 'sesevent_event_createsponsorship', '', array('sponsorshipName' => '<b>' .$sponsorship->title . '</b>'));
			if ($action) {
				$activityApi->attachActivity($action, $event);
			}
			
      $db->commit();
			echo json_encode(array('redirect'=>'sesevent_manage_sponsorships','status'=>true));die;
    } catch (Engine_Image_Exception $e) {
      $db->rollBack();
      $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
    } catch (Exception $e) {
      $db->rollBack();
			$form->addError(Zend_Registry::get('Zend_Translate')->_('Something went wrong,please try again later.'));
    }
  }
	public function editAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
		$is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
   	$sponsorship_id = $this->_getParam('id',0);
		if(!($sponsorship = Engine_Api::_()->getItem('sesevent_sponsorship', $sponsorship_id))){
			return;
		}
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Sponsorship_Edit();   
    if (!$this->getRequest()->isPost() || $is_ajax_content) {
			 $form->populate($sponsorship->toArray());
			 if($sponsorship->status)
			 	$form->removeElement('status');
			 return;
		}
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    // Process
    $values = $form->getValues();
    $db = Engine_Api::_()->getItemTable('sesevent_sponsorship')->getAdapter();
    $db->beginTransaction();
    try {
			if($values['remove_sponsorship_photo']){
				$values['photo_id']	= '';
			}else{
				// Add photo
				if (!empty($values['photo'])) {
					$sponsorship->setPhoto($form->photo);
				}
			}
			$values['event_id'] = $event->event_id;
			$values['owner_id'] = $viewer->getIdentity();
      $sponsorship->setFromArray($values);
      $sponsorship->save();
      $db->commit();
			echo json_encode(array('redirect'=>'sesevent_manage_sponsorships','status'=>true));die;
    } catch (Engine_Image_Exception $e) {
      $db->rollBack();
      $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
    } 
	}
  //get sales report
  public function salesReportsAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
    $this->view->form = $form = new Sesevent_Form_Sponsorship_Searchsponsorshipsalereport();
    $value = array();
    if (isset($_GET['eventSponsorshipId']))
      $value['eventSponsorshipId'] = $_GET['eventSponsorshipId'];
    if (isset($_GET['startdate']))
      $value['startdate'] = $value['start'] = date('Y-m-d', strtotime($_GET['startdate']));
    if (isset($_GET['enddate']))
     $value['enddate'] = $value['end'] = date('Y-m-d', strtotime($_GET['enddate']));
    if (isset($_GET['type']))
      $value['type'] = $_GET['type'];
   if (!count($value)) {
      $value['enddate'] = date('Y-m-d', strtotime(date('Y-m-d')));
      $value['startdate'] = date('Y-m-d', strtotime('-30 days'));
      $value['type'] = $form->type->getValue();
    }
    $form->populate($value);
    $this->view->eventSponsorshipSaleData = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getReportData($value);
  }

  public function paymentTransactionAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    $this->view->paymentRequests = Engine_Api::_()->getDbtable('usersponsorshippayrequests', 'sesevent')->getPaymentRequests(array('event_id' => $event->event_id, 'state' => 'complete'));
  }

  //get payment to admin information
  public function paymentRequestsAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->thresholdAmount = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_sponsothre');
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
    //get total amount of ticket sold in given event	 
		$this->view->userGateway = Engine_Api::_()->getDbtable('usergateways', 'sesevent')->getUserGateway(array('event_id' => $event->event_id, 'user_id' => $viewer->user_id));
    $this->view->orderDetails = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getSponsorshipStats(array('event_id' => $event->event_id));
    //get ramaining amount
    $remainingAmount = Engine_Api::_()->getDbtable('remainingsponsorshippayments', 'sesevent')->getEventSponsorshipRemainingAmount(array('event_id' => $event->event_id));
    if (!$remainingAmount) {
      $this->view->remainingAmount = 0;
    } else
      $this->view->remainingAmount = $remainingAmount->remaining_payment;
    $this->view->paymentRequests = Engine_Api::_()->getDbtable('usersponsorshippayrequests', 'sesevent')->getPaymentRequests(array('event_id' => $event->event_id));
  }

  public function paymentRequestAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->thresholdAmount = $thresholdAmount = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_sponsothre');
    //get remaining amount
    $remainingAmount = Engine_Api::_()->getDbtable('remainingsponsorshippayments', 'sesevent')->getEventSponsorshipRemainingAmount(array('event_id' => $event->event_id));
    if (!$remainingAmount) {
      $this->view->remainingAmount = 0;
    } else {
      $this->view->remainingAmount = $remainingAmount->remaining_payment;
    }
    $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
    $orderDetails = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getSponsorshipStats(array('event_id' => $event->event_id));
    $this->view->form = $form = new Sesevent_Form_Sponsorship_Paymentrequest();
    $value = array();
    $value['total_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['totalAmountSale'], $defaultCurrency);
    $value['total_commission_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['commission_amount'], $defaultCurrency);
    $value['remaining_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($remainingAmount->remaining_payment, $defaultCurrency);
    $value['requested_amount'] = round($remainingAmount->remaining_payment, 2);
    //set value to form
    if ($this->_getParam('id', false)) {
      $item = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $this->_getParam('id'));
      if ($item) {
        $itemValue = $item->toArray();
       // unset($value['requested_amount']);
        $value = array_merge($itemValue, $value);
      } else {
        return $this->_forward('requireauth', 'error', 'core');
      }
    }
    if (empty($_POST))
      $form->populate($value);

    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    if ($thresholdAmount > $remainingAmount->remaining_payment && empty($_POST)) {
      $this->view->message = 'Remaining amount is less than Threshold amount.';
      $this->view->errorMessage = true;
      return;
    } else if (isset($_POST['requested_amount']) && $_POST['requested_amount'] > round($remainingAmount->remaining_payment, 2)) {
      $form->addError('Requested amount must be less than or equal to remaining amount.');
      return;
    } else if (isset($_POST['requested_amount']) && $thresholdAmount > $_POST['requested_amount']) {
      $form->addError('Requested amount must be greater than or equal to threshold amount.');
      return;
    }

    $db = Engine_Api::_()->getDbtable('usersponsorshippayrequests', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $tableOrder = Engine_Api::_()->getDbtable('usersponsorshippayrequests', 'sesevent');
      if (isset($itemValue))
        $order = $item;
      else
        $order = $tableOrder->createRow();
      $order->requested_amount = $_POST['requested_amount'];
      $order->user_message = $_POST['user_message'];
      $order->event_id = $event->event_id;
      $order->owner_id = $viewer->getIdentity();
      $order->user_message = $_POST['user_message'];
      $order->creation_date = date('Y-m-d h:i:s');
      $order->currency_symbol = $defaultCurrency;
			$settings = Engine_Api::_()->getApi('settings', 'core');
   	  $userGatewayEnable = $settings->getSetting('sesevent.userGateway', 'paypal');
			
      $order->save();
      $db->commit();
      
      //Notification work
			$owner_admin = Engine_Api::_()->getItem('user', 1);
			Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner_admin, $viewer, $event, 'sesevent_event_sponsorshippaymentrequest', array('requestAmount' => $_POST['requested_amount']));
			
			//Payment request mail send to admin
			$event_owner = Engine_Api::_()->getItem('user', $event->user_id);
			Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner_admin, 'sesevent_sponsorshippayment_requestadmin', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'event_owner' => $event_owner->getTitle(), 'host' => $_SERVER['HTTP_HOST']));
      
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Payment request send successfully.');
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => 10,
                  'parentRefresh' => 10,
                  'messages' => array($this->view->message)
      ));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  //delete payment request
  public function deletePaymentAction() {

    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $paymnetReq = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $this->getRequest()->getParam('id'));
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid())
      return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Make form
    $this->view->form = $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete Sponsorship Payment request?');
    $form->setDescription('Are you sure that you want to delete this payment request? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    if (!$paymnetReq) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Paymnet request doesn't exists or not authorized to delete");
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
      $paymnetReq->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Payment Request has been deleted.');
    return $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => 10,
                'parentRefresh' => 10,
                'messages' => array($this->view->message)
    ));
  }

  //get paymnet detail
  public function detailPaymentAction() {
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $this->view->item = $paymnetReq = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $this->getRequest()->getParam('id'));
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;

    if (!$paymnetReq) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Paymnet request doesn't exists or not authorized to delete");
      return;
    }
  }
  public function manageSponsorshipAction() {

    $value = array();

    $this->view->event = $event = Engine_Api::_()->core()->getSubject();

    $this->view->event_id = $value['event_id'] = $event->getIdentity();

    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;

    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
    $this->view->is_search_ajax = $is_search_ajax = isset($_POST['is_search_ajax']) ? $_POST['is_search_ajax'] : false;
    if (!$is_search_ajax) {
      $this->view->searchForm = $searchForm = new Sesevent_Form_ManageSponsorship();
    }

    if (isset($_POST['searchParams']) && $_POST['searchParams'])
      parse_str($_POST['searchParams'], $searchArray);

    $value['title'] = isset($searchArray['title']) ? $searchArray['title'] : '';

    $this->view->eventSponsorship = Engine_Api::_()->getDbtable('sponsorships', 'sesevent')->getSponsorship($value);
  }

  public function deleteSponsorshipAction() {
    $sponsorship_id = $this->_getParam('id');
    $sponsorship = Engine_Api::_()->getItem('sesevent_sponsorship', $sponsorship_id);
    if (!$sponsorship)
      return $this->_forward('requireauth', 'error', 'core');
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid() || $event->isOwner($viewer)))
      return;
    $db = $sponsorship->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $sponsorship->is_delete = '1';
      $sponsorship->save();
      $db->commit();
      echo true;die;
    } catch (Exception $e) {
      $db->rollBack();
      echo false;
      die;
    }
  }
  public function salesStatsAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() || $event->isOwner($viewer)))
      return;
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
    $this->view->todaySale = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getSaleStats(array('stats' => 'today', 'event_id' => $event->event_id));
    $this->view->weekSale = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getSaleStats(array('stats' => 'week', 'event_id' => $event->event_id));
    $this->view->monthSale = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getSaleStats(array('stats' => 'month', 'event_id' => $event->event_id));

    //get getEventStats
    $this->view->sponsorshipStatsSale = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent')->getSponsorshipStats(array('event_id' => $event->event_id));
  }

  public function manageOrdersAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
  }
	public function viewSponsorshipAction(){
		//Render
    $this->_helper->content->setEnabled();
	}
	public function viewAction(){
		$sponsorshiporder_id = $this->_getParam('sponsorshiporder_id', null);
		if(!$sponsorshiporder_id)
			return $this->_forward('notfound', 'error', 'core');
		$id = $this->_getParam('event_id', null);
		$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$this->view->order = $order =  Engine_Api::_()->getItem('sesevent_sponsorshiporder', $sponsorshiporder_id);
		$this->view->sponsorship = $sponsorship =  Engine_Api::_()->getItem('sesevent_sponsorship', $order->sponsorship_id);
		$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
	}
	//sponsorship details
	public function detailsAction(){
		$sponsorship_id = $this->_getParam('id',0);
		if(!$sponsorship_id)
			 return $this->_forward('notfound', 'error', 'core');
		$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$viewer = Engine_Api::_()->user()->getViewer();
		if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()))
			return;
		$this->view->sponsorship = $sponsorship = Engine_Api::_()->getItem('sesevent_sponsorship', $sponsorship_id);
		if(!$sponsorship)
			 return $this->_forward('notfound', 'error', 'core');
		if (!$this->getRequest()->isPost()) {
			 //delete older details of user of same sponsorship
			 $db = Engine_Db_Table::getDefaultAdapter();
			 $db->query("DELETE FROM engine4_sesevent_sponsorshipdetails WHERE event_id = ".$event->getIdentity().' AND sponsorship_id	= '.$sponsorship_id.' AND user_id = '.$viewer->getIdentity());
			 return;
		}
    // Process
    $db = Engine_Api::_()->getItemTable('sesevent_sponsorshipdetail')->getAdapter();
    $db->beginTransaction();
    try {
			$table = Engine_Api::_()->getDbtable('sponsorshipdetails', 'sesevent');
      $sponsorshipdetail = $table->createRow();
			$values['description'] = $_POST['description'];
			$values['title'] = $_POST['title'];
			$values['website'] = $_POST['website'];
			$values['sponsorship_id'] = $sponsorship_id;
			$values['event_id'] = $event->event_id;
			$values['user_id'] = $viewer->getIdentity();
      $sponsorshipdetail->setFromArray($values);
      $sponsorshipdetail->save();
			if(isset($_FILES['logo']) && $_FILES['logo']['name'])
				$sponsorshipdetail->setPhoto($_FILES['logo']);
      $db->commit();
			$url = $this->view->escape($this->view->url(array('action' => 'checkout','detail_id'=>$sponsorshipdetail->sponsorshipdetail_id)));
			header('location:'.$url);
    } catch (Engine_Image_Exception $e) {
      $db->rollBack();
      $form->addError(Zend_Registry::get('Zend_Translate')->_('Unable to proccess request.'));
    } 
	}
	//sponsorship payment functions
		public function checkoutAction(){
		$sponsorship_id = $this->_getParam('id',0);
		$this->view->sponsorshipdetail_id = $sponsorshipdetail_id = $this->_getParam('detail_id',0);
		$sponsorshipdetail = Engine_Api::_()->getItem('sesevent_sponsorshipdetail', $sponsorshipdetail_id);
		$this->view->sponsorship = $sponsorship = Engine_Api::_()->getItem('sesevent_sponsorship', $sponsorship_id);
		if(!$sponsorship_id || !$sponsorshipdetail_id || !$sponsorshipdetail || !$sponsorship)
			 return $this->_forward('notfound', 'error', 'core');
		$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$viewer = Engine_Api::_()->user()->getViewer();
		if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()))
			return;
		//check free ticket order
		if($sponsorship->price <= 0){
			$url = $this->view->escape($this->view->url(array('action' => 'free-sponsorship')));
			header('location:'.$url);die;
		}
		// Gateways
    $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'sesevent');
    $gatewaySelect = $gatewayTable->select()
      ->where('enabled = ?', 1)
      ;
    $gateways = $gatewayTable->fetchAll($gatewaySelect);
    $gatewayPlugins = array();
    foreach( $gateways as $gateway ) {
      $gatewayPlugins[] = array(
        'gateway' => $gateway,
        'plugin' => $gateway->getGateway(),
      );
    }
    $this->view->gateways = $gatewayPlugins;
	}
	
	public function processAction()
  {
		if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()))
      return;
    // Get gateway
    $gatewayId = $this->_getParam('gateway_id', null);
		$sponsorship_id = $this->_getParam('id', null);
		$sponsorshipdetail_id = $this->_getParam('detail_id',0);
		$sponsorshipdetail = Engine_Api::_()->getItem('sesevent_sponsorshipdetail', $sponsorshipdetail_id);
		$this->view->sponsorship = $sponsorship = Engine_Api::_()->getItem('sesevent_sponsorship', $sponsorship_id);
		if(!$sponsorship_id || !$gatewayId || !$sponsorship || !$sponsorshipdetail)
			return $this->_forward('notfound', 'error', 'core');
		$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();	
			//delete any pending member and sponsorship order if any
		$db = Engine_Db_Table::getDefaultAdapter();
		$db->query("DELETE FROM engine4_sesevent_sponsorshipmembers WHERE event_id = ".$event->getIdentity().' AND sponsorship_id	= '.$sponsorship_id.' AND owner_id = '.$viewer->getIdentity() .' AND status = "pending"');
		$db->query("DELETE FROM engine4_sesevent_sponsorshiporders WHERE event_id = ".$event->getIdentity().' AND sponsorship_id	= '.$sponsorship_id.' AND owner_id = '.$viewer->getIdentity().' AND state = "pending"');
    if( !$gatewayId ||
        !($gateway = Engine_Api::_()->getItem('sesevent_gateway', $gatewayId)) ||
        !($gateway->enabled) ) {
      header("location:".$this->view->escape($this->view->url(array('action' => 'checkout'))));die;
    }
		//insert order for Sponsorship
		$sponsorshipmemeberTable = Engine_Api::_()->getDbtable('sponsorshipmembers', 'sesevent');
		$session = new Zend_Session_Namespace();
    // Process
    $sponsorshipmemeberTable->insert(array(
        'owner_id' => $viewer->getIdentity(),
				'event_id' => $event->getIdentity(),
        'sponsorship_id' => $sponsorship_id,
        'status' => 'pending',
        'creation_date' => new Zend_Db_Expr('NOW()'),
    ));
		$sponsorshipmemberid = $sponsorshipmemeberTable->getAdapter()->lastInsertId(); 
		if($sponsorshipmemberid){
			$db = Engine_Api::_()->getItemTable('sesevent_sponsorshiporder')->getAdapter();
			$db->beginTransaction();
			try {
				$table = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent');
				$sponsorshiporder = $table->createRow();
				$values['event_id'] = $event->event_id;
				$values['sponsorshipmemeber_id'] = $sponsorshipmemberid;
				$values['owner_id'] = $viewer->getIdentity();
				$values['state'] = 'incomplete';
				$values['total_amount'] = $sponsorship->price;
				$values['sponsorship_id'] = $sponsorship_id;
				$values['ip_address	'] = $_SERVER['REMOTE_ADDR'];
				$sponsorshiporder->setFromArray($values);
				$sponsorshiporder->save();
				$sponsorshipdetail->sponsorshipmemeber_id = $sponsorshipmemberid;
				$sponsorshipdetail->save();
				$db->commit();
			} catch (Engine_Image_Exception $e) {
				$db->rollBack();
				$memberSponsorship = Engine_Api::_()->getItem('sesevent_sponsorshipmember', $sponsorshipmemberid);
				if($memberSponsorship){
					$memberSponsorship->delete();
					$memberSponsorship->save();
				}	
				throw $e->getMessage();
			}
		}else{
				header("location:".$this->view->escape($this->view->url(array('action' => 'checkout'))));die;
		}
    $this->view->gateway = $gateway;
		$this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway('sponsorship');
		if ($gatewayId == 1) {
			$gatewayPlugin->createProduct($sponsorshiporder->getGatewayParams());
		}
		$plugin = $gateway->getPlugin('sponsorship');
		$ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
    // Process
    $ordersTable->insert(array(
			'user_id' => $viewer->getIdentity(),
			'gateway_id' => $gateway->gateway_id,
			'state' => 'pending',
			'creation_date' => new Zend_Db_Expr('NOW()'),
			'source_type' => 'sesevent_sponsorshiporder',
			'source_id' => $sponsorshiporder->getIdentity(),
    ));
		$session = new Zend_Session_Namespace();
    $session->sesevent_order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();    
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
      . $this->view->escape($this->view->url(array('action' => 'return','order_id'=>$sponsorshiporder->getIdentity(),'sponsorshipmember_id'=>$sponsorshipmemberid)))
      . '/?state=' . 'return';
    $params['cancel_url'] = $this->view->escape($schema . $host
      . $this->view->url(array('action' => 'return','order_id'=>$sponsorshiporder->getIdentity(),'sponsorshipmember_id'=>$sponsorshipmemberid)))
      . '/?state=' . 'cancel';
    $params['ipn_url'] = $schema . $host
      . $this->view->url(array('action' => 'index', 'controller' => 'ipn', 'module' => 'payment'), 'default');
    // Process transaction
    $transaction = $plugin->createOrderTransaction($viewer,$sponsorshiporder,$event,$params,$sponsorship);
    // Pull transaction params
    $this->view->transactionUrl = $transactionUrl = $gatewayPlugin->getGatewayUrl();
    $this->view->transactionMethod = $transactionMethod = $gatewayPlugin->getGatewayMethod();
    $this->view->transactionData = $transactionData = $transaction->getData();
    // Handle redirection
    if( $transactionMethod == 'GET' ) {
     $transactionUrl .= '?' . http_build_query($transactionData);
     return $this->_helper->redirector->gotoUrl($transactionUrl, array('prependBase' => false));
    }
  }
	public function returnAction() {
		 $orderId = $this->_getParam('order_id',0);
		 $sponsormemberid = $this->_getParam('sponsorshipmember_id',0);
		if(!$orderId || !$sponsormemberid)
			return $this->_forward('notfound', 'error', 'core');
		$sponsormember = Engine_Api::_()->getItem('sesevent_sponsorshipmember', $sponsormemberid);
		$this->view->order = $order = Engine_Api::_()->getItem('sesevent_sponsorship', $orderId);
		$session = new Zend_Session_Namespace();
    // Get order
		$orderPaymentId = $session->sesevent_order_id;
		$orderPayment = Engine_Api::_()->getItem('payment_order', $orderPaymentId);
    if (!$orderPayment || ($orderId != $orderPayment->source_id) ||
			 ($orderPayment->source_type != 'sesevent_sponsorshiporder') ||
			 !($user_order = $orderPayment->getSource()) ) {
			return $this->_helper->redirector->gotoRoute(array(), 'sesevent_general', true);
    }
    $gateway = Engine_Api::_()->getItem('sesevent_gateway', $orderPayment->gateway_id);    
    if( !$gateway )
      return $this->_helper->redirector->gotoRoute(array(), 'sesevent_general', true);
    // Get gateway plugin
    $plugin = $gateway->getPlugin('sponsorship');
    unset($session->errorMessage);
    try {
     //get all params 
      $params = $this->_getAllParams();
      $status = $plugin->orderTransactionReturn($orderPayment, $params,$sponsormember);
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
      return $this->_helper->redirector->gotoRoute(array('sesevent_event'), 'default', true);
    } else {
			$url =  $this->view->escape($this->view->url(array('action' => 'finish', 'state' => $state)));
     header('location:'.$url);die;
    }
  }
  public function finishAction() {
    $session = new Zend_Session_Namespace();
    if (!empty($session->sesevent_order_id))
      $session->sesevent_order_id = '';
    $orderTrabsactionDetails = array('state' => $this->_getParam('state'), 'errorMessage' => $session->errorMessage);
    $session->sesevent_order_details = $orderTrabsactionDetails;
		$url =  $this->view->escape($this->view->url(array('action' => 'success')));
    header('location:'.$url);die;
  }	
	public function freeSponsorshipAction(){
		$sponsorship_id = $this->_getParam('id', null);
		if(!$sponsorship_id)
			return $this->_forward('notfound', 'error', 'core');
   	$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
		//delete any pending member and sponsorship order if any
		$db = Engine_Db_Table::getDefaultAdapter();
		$db->query("DELETE FROM engine4_sesevent_sponsorshipmembers WHERE event_id = ".$event->getIdentity().' AND sponsorship_id	= '.$sponsorship_id.' AND owner_id = '.$viewer->getIdentity() .' AND status = "pending"');
		$db->query("DELETE FROM engine4_sesevent_sponsorshiporders WHERE event_id = ".$event->getIdentity().' AND sponsorship_id	= '.$sponsorship_id.' AND owner_id = '.$viewer->getIdentity().' AND state = "pending"');
		$sponsorshipmemeberTable = Engine_Api::_()->getDbtable('sponsorshipmembers', 'sesevent');
		$session = new Zend_Session_Namespace();
    // Process
    $sponsorshipmemeberTable->insert(array(
        'owner_id' => $viewer->getIdentity(),
				'event_id' => $event->getIdentity(),
        'sponsorship_id' => $sponsorship_id,
        'status' => 'complete',
        'creation_date' => new Zend_Db_Expr('NOW()'),
    ));
		$sponsorshipmemberid = $sponsorshipmemeberTable->getAdapter()->lastInsertId(); 
		if($sponsorshipmemberid){
			$db = Engine_Api::_()->getItemTable('sesevent_sponsorshiporder')->getAdapter();
			$db->beginTransaction();
			try {
				$table = Engine_Api::_()->getDbtable('sponsorshiporders', 'sesevent');
				$sponsorship = $table->createRow();
				$values['event_id'] = $event->event_id;
				$values['sponsorshipmemeber_id'] = $sponsorshipmemberid;
				$values['owner_id'] = $viewer->getIdentity();
				$values['state'] = 'complete';
				$values['gateway_type'] = 'FREE';
				$values['sponsorship_id'] = $sponsorship_id;
				$values['ip_address	'] = $_SERVER['REMOTE_ADDR'];
				$sponsorship->setFromArray($values);
				$sponsorship->save();
				$db->commit();
			} catch (Engine_Image_Exception $e) {
				$db->rollBack();
				$memberSponsorship = Engine_Api::_()->getItem('sesevent_sponsorshipmember', $sponsorshipmemberid);
				if($memberSponsorship){
					$memberSponsorship->delete();
					$memberSponsorship->save();
				}	
				$status = 'failure';
				$session->errorMessage = $e->getMessage();
			}
		}else{
				$status = 'failure';
				$session->errorMessage = $this->view->translate("Something went wrong,please try again later.");
		}
		$url =  $this->view->escape($this->view->url(array('action' => 'success','state'=>'success','order_id'=>$sponsorship->sponsorshiporder_id)));
    header('location:'.$url);die;	
	}
	public function successAction(){
		$session = new Zend_Session_Namespace();
		$order_id = $this->_getParam('order_id', null);
		$sponsorship_id = $this->_getParam('id', null);
		if(!$order_id || !$sponsorship_id)
			return $this->_forward('notfound', 'error', 'core');
		$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$this->view->order = $order =  Engine_Api::_()->getItem('sesevent_sponsorshiporder', $order_id);
		$this->view->sponsorship = $sponsorship =  Engine_Api::_()->getItem('sesevent_sponsorship', $sponsorship_id);
		$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();		
		$state = $this->_getParam('state');
	  if(!$state || !$order || !$sponsorship)
	 	 return $this->_forward('notfound', 'error', 'core');
		$this->view->error = $error =  $session->errorMessage;
		$session->unsetAll();
		if(($state == 'active' || $state == 'success') && empty($error)){
			$this->view->success = true;
		}
	}
	public function sponsorshipRequestAction(){
		$event = $this->view->event = Engine_Api::_()->core()->getSubject();
		$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
		$this->view->form = $form = new Sesevent_Form_Sponsorship_Request();
		$this->view->sponsorshiprequest_id = $sponsorshiprequest_id = $this->_getParam('sponsorshiprequest_id',null);
		if (!$this->getRequest()->isPost()) {
			if($sponsorshiprequest_id){
			 $sponsorshiprequest = Engine_Api::_()->getItem('sesevent_sponsorshiprequest',$sponsorshiprequest_id);
			 $form->populate($sponsorshiprequest->toArray());
			}
			 return;
		}
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    // Process
    $values = $form->getValues();
    $db = Engine_Api::_()->getItemTable('sesevent_sponsorshiprequest')->getAdapter();
    $db->beginTransaction();
    try {
			if(empty($sponsorshiprequest)){
				$table = Engine_Api::_()->getDbtable('sponsorshiprequests', 'sesevent');
      	$sponsorshiprequest = $table->createRow();
			}
			$values['description'] = $values['description'];
			$values['event_id'] = $event->event_id;
			$values['user_id'] = $viewer->getIdentity();
      $sponsorshiprequest->setFromArray($values);
      $sponsorshiprequest->save();
      $db->commit();
			$this->view->status = true;
			$this->view->message = Zend_Registry::get('Zend_Translate')->_('Sponsorship Request has been submitted successfully.');
			return $this->_forward('success', 'utility', 'core', array(
									'smoothboxClose' => 3000,
									'parentRefresh' => false,
									'messages' => array($this->view->message)
			));
    } catch (Engine_Image_Exception $e) {
      $db->rollBack();
      $form->addError(Zend_Registry::get('Zend_Translate')->_('Unable to proccess request.'));
    } 
	}
	public function requestSponsorshipAction(){
		$this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$this->view->event_id = $params['event_id'] = $event->getIdentity();
		$is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
		if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->_forward('notfound', 'error', 'core');
		}
		$is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
		$this->view->sponsorshipRequest = Engine_Api::_()->getDbtable('sponsorshiprequests', 'sesevent')->getRequests($params);
	}
	public function deleteRequestAction(){
    $paymnetReq = Engine_Api::_()->getItem('sesevent_sponsorshiprequest', $this->getRequest()->getParam('id'));
    $viewer = Engine_Api::_()->user()->getViewer();
		$this->view->event_id = $event = Engine_Api::_()->core()->getSubject();;
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid()){
      echo false;die;
		}

   
    if (!$paymnetReq) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Sponsorship request doesn't exists or not authorized to delete");
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
     	$db->query("DELETE FROM engine4_sesevent_sponsorshiprequests WHERE sponsorshiprequest_id = ".$paymnetReq->sponsorshiprequest_id);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
			echo false;die;
    }

    echo true;die;
	}
	public function viewRequestAction(){
	 $paymnetReq = $this->view->request = Engine_Api::_()->getItem('sesevent_sponsorshiprequest', $this->getRequest()->getParam('id'));
	 if(!$paymnetReq)
		return;
			
	}
	public function emailUserAction(){
		
	}
}