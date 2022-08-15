<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminOffersController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_AdminOffersController extends Core_Controller_Action_Admin {

  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_offer');
    $this->view->formFilter = $formFilter = new Sescredit_Form_Admin_Offer_Filter();
    $offerTable = Engine_Api::_()->getDbTable('offers', 'sescredit');
    $select = $offerTable->select()
            ->from($offerTable->info('name'), array('*'))
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'offer_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));
    $values = array();
    $minPoint = isset($_GET['min_point']) ? ($_GET['min_point'] == '' ? 0 : $_GET['min_point']) : 0;
    $maxPoint = isset($_GET['max_point']) ? ($_GET['max_point'] == '' ? 10000 : $_GET['max_point']) : 10000;

    $select->where("point between $minPoint and $maxPoint");

    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $values = array_merge(array(
        'order' => isset($_GET['order']) ? $_GET['order'] : '',
        'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
            ), $values);
    $this->view->assign($values);
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
      if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
      $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function createAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_offer');
    $this->view->form = $form = new Sescredit_Form_Admin_Offer_Create();

    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    $values = $form->getValues();
    $date = $values['show_date_field'];
    if ($values['offer_time'] && empty($date)) {
      $form->addError($this->view->translate("Choose End Date."));
      return;
    }
    if (!empty($date)) {
      $date = explode('-', $date);
      $startTime = $date[0];
      $endTime = $date[1];
    } else {
      $startTime = $endTime = '';
    }
    $offerTable = Engine_Api::_()->getDbTable('offers', 'sescredit');
    $db = $offerTable->getAdapter();
    $db->beginTransaction();
    try {
      $offer = $offerTable->createRow();
      $offer->setFromArray($values);
      $offer->starttime = (!empty($startTime)) ? date('Y-m-d', strtotime($startTime)) : '';
      $offer->endtime = (!empty($endTime)) ? date('Y-m-d', strtotime($endTime)) : '';
      $offer->save();
      // Commit
      $db->commit();
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  public function editAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_offer');
    $this->view->offer_id = $offerId = $this->_getParam('id');
    $offer = Engine_Api::_()->getItem('sescredit_offer', $offerId);
    $this->view->form = $form = new Sescredit_Form_Admin_Offer_Edit();
    // Populate form
    $form->populate($offer->toArray());
    if ($offer->starttime != '0000-00-00 00:00:00') {
      $form->show_date_field->setValue(date('Y/m/d', strtotime($offer->starttime)) . ' - ' . date('Y/m/d', strtotime($offer->endtime)));
    }
    // Check post/form
    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();
      $date = $values['show_date_field'];
      if ($values['offer_time'] && empty($date)) {
        $form->addError($this->view->translate("Choose End Date."));
        return;
      }
      if (!empty($date)) {
        $date = explode('-', $date);
        $startTime = $date[0];
        $endTime = $date[1];
      } else {
        $startTime = $endTime = '';
      }
      $offer->setFromArray($values);
      $offer->starttime = (!empty($startTime)) ? date('Y-m-d', strtotime($startTime)) : '';
      $offer->endtime = (!empty($endTime)) ? date('Y-m-d', strtotime($endTime)) : '';
      $offer->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function deleteAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete This Offer?');
    $form->setDescription('Are you sure that you want to delete this offer entry? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $offer = Engine_Api::_()->getItem('sescredit_offer', $this->_getParam('id'));
        $offer->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('You have been deleted row successfully.')
      ));
    }
  }

  public function enableAction() {
    $offer_id = $this->_getParam('id');
    if (!empty($offer_id)) {
      $offer = Engine_Api::_()->getItem('sescredit_offer', $offer_id);
      $offer->enable = !$offer->enable;
      $offer->save();
    }
    if (isset($_SERVER['HTTP_REFERER']))
      $url = $_SERVER['HTTP_REFERER'];
    else
      $url = 'admin/sescredit/offers';
    $this->_redirect($url);
  }

}
