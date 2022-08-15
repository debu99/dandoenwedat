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
class Sesevent_Widget_ManageOrdersController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $value = array();
    $this->view->event_id = $event_id = $value['event_id'] = $this->_getParam('event_id', false);
    if (!$event_id)
      return $this->setNoRender();
    $this->view->is_search_ajax = $is_search_ajax = isset($_POST['is_search_ajax']) ? $_POST['is_search_ajax'] : false;
    if (!$is_search_ajax) {
      $this->view->searchForm = $searchForm = new Sesevent_Form_Searchorder();
    }
    $this->view->event = $event = Engine_Api::_()->getItem("sesevent_event", $event_id);
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
      parse_str($_POST['searchParams'], $searchArray);

    $value['order_id'] = isset($searchArray['order_id']) ? $searchArray['order_id'] : '';
    $value['registration_number'] = isset($searchArray['registration_number']) ? $searchArray['registration_number'] : '';
    $value['email'] = isset($searchArray['email']) ? $searchArray['email'] : '';
    $value['buyer_name'] = isset($searchArray['buyer_name']) ? $searchArray['buyer_name'] : '';
    $value['date_from'] = isset($searchArray['date']['date_from']) ? $searchArray['date']['date_from'] : '';
    $value['date_to'] = isset($searchArray['date']['date_to']) ? $searchArray['date']['date_to'] : '';
    $value['order_min'] = isset($searchArray['order']['order_min']) ? $searchArray['order']['order_min'] : '';
    $value['order_max'] = isset($searchArray['order']['order_max']) ? $searchArray['order']['order_max'] : '';
    $value['commision_min'] = isset($searchArray['commision']['commision_min']) ? $searchArray['commision']['commision_min'] : '';
    $value['commision_max'] = isset($searchArray['commision']['commision_max']) ? $searchArray['commision']['commision_max'] : '';
    $value['gateway'] = isset($searchArray['gateway']) ? $searchArray['gateway'] : '';

    $this->view->orders = $orders = Engine_Api::_()->getDbtable('orders', 'sesevent')->manageOrders($value);
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $this->view->page = $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->paginator = $paginator = Zend_Paginator::factory($orders);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(10);
  }

}
