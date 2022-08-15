<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_SearchTicketController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    $value = array();
    $this->view->event_id = $event_id = $value['event_id'] = $this->_getParam('event_id', false);
		$this->view->backOrder = false;
    if (!$event_id)
      return $this->setNoRender();
    $this->view->is_search_ajax = $is_search_ajax = isset($_POST['is_search_ajax']) ? $_POST['is_search_ajax'] : false;
    if (!$is_search_ajax) {
			$order_id= $this->_getParam('order_id', false);
			
      $this->view->searchForm = $searchForm = new Sesevent_Form_Searchticket();
			if($order_id){
				$this->view->backOrder = true;
				$searchArray['order_id'] = $order_id;
				$searchForm->order_id->setValue($order_id);
			}
    }
    $this->view->event = $event = Engine_Api::_()->getItem("sesevent_event", $event_id);
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
      parse_str($_POST['searchParams'], $searchArray);

    $value['order_id'] = isset($searchArray['order_id']) ? $searchArray['order_id'] : '';
		$value['registration_number'] = isset($searchArray['registration_number']) ? $searchArray['registration_number'] : '';
    $value['buyer_name'] = isset($searchArray['buyer_name']) ? $searchArray['buyer_name'] : '';
		$value['email'] = isset($searchArray['email']) ? $searchArray['email'] : '';
		$value['mobile'] = isset($searchArray['mobile']) ? $searchArray['mobile'] : '';
		$value['creation_date'] = isset($searchArray['creation_date']) ? $searchArray['creation_date'] : '';
    $this->view->orders = $orders = Engine_Api::_()->getDbtable('orderticketdetails', 'sesevent')->searchTickets($value);
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->paginator = $paginator = Zend_Paginator::factory($orders);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(10);
		
  }

}
