<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSponsorshipController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AdminSponsorshipController extends Core_Controller_Action_Admin {

  public function indexAction() { 

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_managesponsorship');

    $this->view->formFilter = $formFilter = new Sesevent_Form_Admin_FilterSponsorshipOrder();
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();

    $values = array_merge(array('order' => $_GET['order'], 'order_direction' => $_GET['order_direction']), $values);

    $this->view->assign($values);

    $eventTableName = Engine_Api::_()->getItemTable('sesevent_event')->info('name');
    $sponsorshipordersTable = Engine_Api::_()->getDbTable('sponsorshiporders', 'sesevent');
    $sponsorshipordersTableName = $sponsorshipordersTable->info('name');
    
    $sponsorshipTable = Engine_Api::_()->getDbTable('sponsorships', 'sesevent');
    $sponsorshipTableName = $sponsorshipTable->info('name');

    $select = $sponsorshipordersTable->select()
            ->setIntegrityCheck(false)
            ->from($sponsorshipordersTableName)
            ->joinLeft($eventTableName, "$sponsorshipordersTableName.event_id = $eventTableName.event_id", 'title')
            ->join($sponsorshipTableName, "$eventTableName.event_id = $sponsorshipTableName.event_id", 'title as spo_title')
            ->where($sponsorshipordersTableName . '.state = ?', 'complete')
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'sponsorshiporder_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));

    if (!empty($_GET['name']))
      $select->where($sponsorshipTableName . '.title LIKE ?', '%' . $_GET['name'] . '%');

    if (!empty($_GET['creation_date']))
      $select->where($sponsorshipordersTableName . '.creation_date LIKE ?', $_GET['creation_date'] . '%');

    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function viewAction() {
    $this->view->item = Engine_Api::_()->getItem('sesevent_sponsorshiporder', $this->_getParam('id', null));
  }
  
  
  public function manageSponsorshipPaymentEventOwnerAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_managesopnshorshipeventowner');

    $this->view->formFilter = $formFilter = new Sesevent_Form_Admin_FilterSponsorshipPaymentEventOwner();
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();

    $values = array_merge(array('order' => $_GET['order'], 'order_direction' => $_GET['order_direction']), $values);

    $this->view->assign($values);

    $eventTableName = Engine_Api::_()->getItemTable('sesevent_event')->info('name');
    
    $ordersTable = Engine_Api::_()->getDbTable('usersponsorshippayrequests', 'sesevent');
    $ordersTableName = $ordersTable->info('name');

    $sponsorshipTable = Engine_Api::_()->getDbTable('sponsorships', 'sesevent');
    $sponsorshipTableName = $sponsorshipTable->info('name');
    
    
    $select = $ordersTable->select()
            ->setIntegrityCheck(false)
            ->from($ordersTableName)
            ->joinLeft($eventTableName, "$ordersTableName.event_id = $eventTableName.event_id", 'title')
           ->where($ordersTableName . '.state = ?', 'complete')
           ->join($sponsorshipTableName, "$eventTableName.event_id = $sponsorshipTableName.event_id", 'title as spo_title')
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'usersponsorshippayrequest_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));

    if (!empty($_GET['name']))
      $select->where($sponsorshipTableName . '.title LIKE ?', '%' . $_GET['name'] . '%');

    if (!empty($_GET['creation_date']))
      $select->where($ordersTableName . '.creation_date LIKE ?', $_GET['creation_date'] . '%');

    $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  public function viewPaymentrequestAction() {
    $this->view->item = Engine_Api::_()->getItem('sesevent_usersponsorshippayrequest', $this->_getParam('id', null));
  }
}
