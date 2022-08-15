<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminTicketsController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AdminTicketsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_seseventtickets');
    
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventticket_admin_main', array(), 'sesevent_admin_main_managetickets');
    
    $this->view->subsubNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main_managetickets', array(), 'sesevent_admin_main_manageticketssub');

    $this->view->formFilter = $formFilter = new Sesevent_Form_Admin_FilterTickets();
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $ticket = Engine_Api::_()->getItem('sesevent_ticket', $value);
          $ticket->delete();
        }
      }
    }

    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();

    $values = array_merge(array('order' => $_GET['order'], 'order_direction' => $_GET['order_direction']), $values);

    $this->view->assign($values);

    $eventTableName = Engine_Api::_()->getItemTable('sesevent_event')->info('name');
    $ticketsTable = Engine_Api::_()->getDbTable('tickets', 'sesevent');
    $ticketsTableName = $ticketsTable->info('name');
		$tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
    $select = $ticketsTable->select()
            ->setIntegrityCheck(false)
            ->from($ticketsTableName)
            ->joinLeft($eventTableName, "$ticketsTableName.event_id = $eventTableName.event_id", 'title')
						->where($eventTableName.'.event_id !=?','')
						->joinLeft($tableUserName, "$eventTableName.user_id = $tableUserName.user_id", 'username')
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'ticket_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));
						
		if (!empty($_GET['owner_name']))
      $select->where($tableUserName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');
		if (!empty($_GET['price']))
      $select->where($ticketsTableName . '.price LIKE ?', '%' . $_GET['price'] . '%');
		if (!empty($_GET['currency']))
      $select->where($ticketsTableName . '.currency LIKE ?', '%' . $_GET['currency'] . '%');
		if (!empty($_GET['total']))
      $select->where($ticketsTableName . '.total LIKE ?', '%' . $_GET['total'] . '%');
		
		
		
		
    if (!empty($_GET['name']))
      $select->where($ticketsTableName . '.name LIKE ?', '%' . $_GET['name'] . '%');
		
		if (!empty($_GET['event']))
      $select->where($eventTableName . '.title LIKE ?', '%' . $_GET['event'] . '%');

    if (!empty($_GET['type']))
      $select->where($ticketsTableName . '.type LIKE ?', '%' . $_GET['type'] . '%');

    if (!empty($_GET['creation_date']))
      $select->where($ticketsTableName . '.creation_date LIKE ?', $_GET['creation_date'] . '%');

    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function deleteAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $this->view->sesevent_id = $id = $this->_getParam('id');

    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete Ticket?');
    $form->setDescription('Are you sure that you want to delete this ticket? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    //Check post
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $event = Engine_Api::_()->getItem('sesevent_ticket', $id);
        $event->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('You have successfully delete ticket.')
      ));
    }
  }

  public function viewAction() {
    $this->view->item = Engine_Api::_()->getItem('sesevent_ticket', $this->_getParam('id', null));
  }

  public function manageTicketOrdersAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_seseventtickets');
    
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventticket_admin_main', array(), 'sesevent_admin_main_managetickets');
    
    $this->view->subsubNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main_managetickets', array(), 'sesevent_admin_main_manageticketorderssub');
    
    $this->view->formFilter = $formFilter = new Sesevent_Form_Admin_FilterTicketOrder();
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();

    $values = array_merge(array('order' => $_GET['order'], 'order_direction' => $_GET['order_direction']), $values);

    $this->view->assign($values);

    $eventTableName = Engine_Api::_()->getItemTable('sesevent_event')->info('name');
    $ordersTable = Engine_Api::_()->getDbTable('orders', 'sesevent');
    $ordersTableName = $ordersTable->info('name');
		$userName = Engine_Api::_()->getItemTable('user')->info('name');
    $select = $ordersTable->select()
            ->setIntegrityCheck(false)
            ->from($ordersTableName)
            ->joinLeft($eventTableName, "$ordersTableName.event_id = $eventTableName.event_id", 'title')
						->joinLeft($userName, "$userName.user_id = $eventTableName.user_id", null)
						->where($eventTableName.'.event_id !=?','')
            ->where($ordersTableName . '.state = ?', 'complete')
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'order_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));

    if (!empty($_GET['name']))
      $select->where($eventTableName . '.title LIKE ?', '%' . $_GET['name'] . '%');
		if (!empty($_GET['gateway']))
      $select->where($ordersTable . '.gateway_type LIKE ?', '%' . $_GET['gateway'] . '%');
		
		if (!empty($_GET['owner']))
      $select->where($userName . '.displayname LIKE ?', '%' . $_GET['owner'] . '%');
    if (!empty($_GET['creation_date']))
      $select->where($ordersTableName . '.creation_date LIKE ?', $_GET['creation_date'] . '%');

    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function viewTicketOrderAction() {
    $this->view->item = Engine_Api::_()->getItem('sesevent_order', $this->_getParam('id', null));
  }
  
    public function managePaymentEventOwnerAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_seseventtickets');
    
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventticket_admin_main', array(), 'sesevent_admin_main_paymentrequest');
    
    $this->view->subsubNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main_paymentrequest', array(), 'sesevent_admin_main_managepaymenteventownersub');

    $this->view->formFilter = $formFilter = new Sesevent_Form_Admin_FilterPaymentEventOwner();
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();

    $values = array_merge(array('order' => $_GET['order'], 'order_direction' => $_GET['order_direction']), $values);

    $this->view->assign($values);

    $eventTableName = Engine_Api::_()->getItemTable('sesevent_event')->info('name');
    $ordersTable = Engine_Api::_()->getDbTable('userpayrequests', 'sesevent');
    $ordersTableName = $ordersTable->info('name');

    $select = $ordersTable->select()
            ->setIntegrityCheck(false)
            ->from($ordersTableName)
            ->joinLeft($eventTableName, "$ordersTableName.event_id = $eventTableName.event_id", 'title')
            ->where($ordersTableName . '.state = ?', 'complete')
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'userpayrequest_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));

    if (!empty($_GET['name']))
      $select->where($eventTableName . '.title LIKE ?', '%' . $_GET['name'] . '%');

    if (!empty($_GET['creation_date']))
      $select->where($ordersTableName . '.creation_date LIKE ?', $_GET['creation_date'] . '%');
	 if (!empty($_GET['gateway']))
      $select->where($ordersTableName . '.gateway_type LIKE ?', $_GET['gateway'] . '%');
    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  
    public function viewPaymentrequestAction() {
    $this->view->item = Engine_Api::_()->getItem('sesevent_userpayrequest', $this->_getParam('id', null));
  }

}
