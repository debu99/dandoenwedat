<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AjaxController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AjaxController extends Core_Controller_Action_Standard {
		//get event categories 
	public function customUrlCheckAction(){
		$value = $this->sanitize($this->_getParam('value', null));
		if(!$value){
			echo json_encode(array('error'=>true));die;
		}
		$event_id = $this->_getParam('event_id',null);
		$custom_url = Engine_Api::_()->getDbtable('events', 'sesevent')->checkCustomUrl($value,$event_id);
		if($custom_url){
			echo json_encode(array('error'=>true,'value'=>$value));die;
		}else{
			echo json_encode(array('error'=>false,'value'=>$value));die;	
		}
	}
	function sanitize($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
	}
	public function getEventAction(){
		$yearMonth = $this->_getParam('params',false);
		if($yearMonth){
			list($year,$month,$day) = explode('-',$yearMonth);
		}
		$this->view->viewmore = $this->_getParam('viewmore',0);
		$this->view->viewmoreT = $this->_getParam('viewmoreT',0);
		$params['year']=	(isset($year) ? $year : date('Y'));
		$params['month']	= (isset($month) ? $month : date('m'));
		$params['day'] =  (isset($day) ? $day : date('d'));
		$params['getEventLike'] = true;
		$this->view->currentDay = $yearMonth;
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$paginator = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventPaginator($params);
		$paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
		
	}
	public function sitememberAction(){
		$tablename = Engine_Api::_()->getDbtable('users', 'user');
		$select = $tablename->select();
		 $select->where('`'.$tablename->info('name').'`.`displayname` LIKE ?', '%'.$this->_getParam('text') .'%');
		$select->limit(30);
		 $data = array();
      foreach( $select->getTable()->fetchAll($select) as $friend ) {
        $data[] = array(
          'type'  => 'user',
          'id'    => $friend->getIdentity(),
          'guid'  => $friend->getGuid(),
          'label' => $friend->getTitle(),
          'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
          'url'   => $friend->getHref(),
        );
      }
	echo json_encode($data);die;
	}
	public function saveCheckoutAction(){
		$data = $this->_getParam('data',null);
		$price = 0;
		$service_tax = 0;
		$entertainment_tax = 0;
		$counter = 0;
		$total_tickets = 0;
		$errorArray = array();
		$error = false;
		$viewer = Engine_Api::_()->user()->getViewer();
		if($viewer->getIdentity() ==0){
			echo false;die;
		}
		if($data){
			$currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
			$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
			$settings = Engine_Api::_()->getApi('settings', 'core');
			$currencyValue = 1;
			if($currentCurrency != $defaultCurrency){
					//$currencyValue = $settings->getSetting('sesevent.'.$currentCurrency);
					$currencyValue = 1;
			}
			$db = Engine_Api::_()->getDbtable('orders', 'sesevent')->getAdapter();
    	$db->beginTransaction();
			foreach($data as $key=>$value){
				if($value['value'] == 0)	continue;
					$ticket = Engine_Api::_()->getItem('sesevent_ticket', $value['id']);
				//check ticket availability
					$ticketSold = (int) Engine_Api::_()->sesevent()->purchaseTicketCount($ticket->event_id,$ticket->ticket_id);
					if($ticketSold == 0 || $ticket->total == 0 || ($ticket->total >= ($ticketSold + $value['value'])) && !$error){
						$total_tickets = $value['value'] + $total_tickets;
					try{
						if($counter == 0){
							$tableOrder = Engine_Api::_()->getDbtable('orders', 'sesevent');
							$order = $tableOrder->createRow();
							$order->event_id = $ticket->event_id;
							$order->owner_id = $viewer->getIdentity();
							$order->state = 'incomplete';
							$order->creation_date	= date('Y-m-d H:i:s');
							$order->modified_date	= date('Y-m-d H:i:s');
							$order->ip_address = $_SERVER['REMOTE_ADDR'];
							$order->save();
						}
						$tableOrderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
     			  $orderTicket = $tableOrderTicket->createRow();
						$orderTicket->order_id = $order->order_id;
						$orderTicket->ticket_id = $value['id'];
						$orderTicket->event_id = $ticket->event_id;
						$orderTicket->owner_id = $viewer->getIdentity();
						$orderTicket->title = $ticket->name;
						$orderTicket->price = round($ticket->price,2);
						$orderTicket->quantity = $value['value'];
						$orderTicket->service_tax	 = $ticket->service_tax;
						$orderTicket->entertainment_tax	 = $ticket->entertainment_tax;
						$orderTicket->creation_date	= date('Y-m-d H:i:s');
						$orderTicket->modified_date	= date('Y-m-d H:i:s');
						$orderTicket->save();
					  
						$actPrice = round($ticket->price*$currencyValue,2);
						$price = round(($value['value']*$actPrice) + $price,2);		 
					  $service_tax = round(($actPrice * ($ticket->service_tax/100))*$value['value'] + $service_tax,2);
					  $entertainment_tax = round((($actPrice * ($ticket->entertainment_tax/100)))*$value['value'] + $entertainment_tax,2);
					}catch (Exception $e) {
						$db->rollBack();
						echo false;die;
					}
				}else{
						//select ticket count greater than available ticket
						$errorArray[$counter]['id'] = $ticket->ticket_id;
						$errorArray[$counter]['availability'] = $ticket->total - $ticketSold;
						$error = true;
					}
					$counter++;
			}
			//available ticket is less than choosen value
			if($error){
				echo json_encode($errorArray);die;
			}
			//success 
					$purchaseTotal = round($price,2);
					$order->total_service_tax	= $service_tax;
					$order->total_entertainment_tax		= $entertainment_tax;
					$order->total_amount	= $purchaseTotal;
					$order->total_tickets = $total_tickets;
					$order->save();
					$db->commit();
					$event = Engine_Api::_()->getItem('sesevent_event', $order->event_id);
					echo json_encode(array('redirect'=>$this->view->url(array('event_id' => $event->custom_url,'controller'=>'order','order_id'=>$order->order_id), 'sesevent_order', true)));die;
		}
		echo false;die;
	}
	public function checkoutAction(){
		$data = $this->_getParam('data',null);
		$price = 0;
		$service_tax = 0;
		$entertainment_tax = 0;
		if($data){
			$currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
			$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
			$settings = Engine_Api::_()->getApi('settings', 'core');
			$currencyValue = 1;
			if($currentCurrency != $defaultCurrency){
					$currencyValue = $settings->getSetting('sesmultiplecurrency.'.$currentCurrency);
			}
			foreach($data as $key=>$value){
				if(empty($value['value']) || $value['value'] == 0)	continue;
					$ticket = Engine_Api::_()->getItem('sesevent_ticket', $value['id']);
				 if($ticket){
					 $actPrice = round($ticket->price*$currencyValue,2);
					 $price = round($value['value']*$actPrice + $price,2);		 
					 $service_tax = round(($actPrice * ($ticket->service_tax/100))*$value['value'] + $service_tax,2);
					 $entertainment_tax = round(($actPrice * ($ticket->entertainment_tax/100))*$value['value'] + $entertainment_tax,2);
				 }
			}
			$purchaseTotal = round($price+$service_tax+$entertainment_tax,2);
			echo json_encode(array('price'=>Engine_Api::_()->sesevent()->getCurrencyPrice($price,$currentCurrency),'service_tax'=>Engine_Api::_()->sesevent()->getCurrencyPrice($service_tax,$currentCurrency),'entertainment_tax'=>Engine_Api::_()->sesevent()->getCurrencyPrice($entertainment_tax,$currentCurrency),'purchaseTotal'=>Engine_Api::_()->sesevent()->getCurrencyPrice($purchaseTotal,$currentCurrency)));die;
		}
		echo false;die;
	}
	public function offsiteMemberAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		$data = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHosts(array('owner_id'=>$viewer->getIdentity(),'value'=>$this->_getParam('value')));
		$result = array();
		foreach($data as $value){
		$result[] = array(
			'type' => 'user',
			'id' =>   $value['host_id'],
			'guid' => $value->getGuid(),
			'label' => $value['host_name'],
			'photo' => $this->view->itemPhoto($value, 'thumb.icon'),
			'url' => $value->getHref(),
		);
		}
		echo json_encode($result);die;
	}
  public function subcategoryAction() {
    $category_id = $this->_getParam('category_id', null);
    if ($category_id) {
			$subcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubcategory(array('category_id'=>$category_id,'column_name'=>'*'));
      $count_subcat = count($subcategory->toarray());
      if (isset($_POST['selected']))
        $selected = $_POST['selected'];
      else
        $selected = '';
      $data = '';
      if ($subcategory && $count_subcat) {
        $data .= '<option value=""></option>';
        foreach ($subcategory as $category) {
          $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
        }
      }
    }
    else
      $data = '';
    echo $data;
    die;
  }
	// get event subsubcategory 
  public function subsubcategoryAction() {
    $category_id = $this->_getParam('subcategory_id', null);
    if ($category_id) {
      $subcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubsubcategory(array('category_id'=>$category_id,'column_name'=>'*'));
      $count_subcat = count($subcategory->toarray());
      if (isset($_POST['selected']))
        $selected = $_POST['selected'];
      else
        $selected = '';
      $data = '';
      if ($subcategory && $count_subcat) {
        $data .= '<option value=""></option>';
        foreach ($subcategory as $category) {
          $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '">' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
        }
      }
    }
    else
      $data = '';
    echo $data;die;
  }
}