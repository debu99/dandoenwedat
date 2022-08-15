<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminGatewayController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AdminGatewayController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
		$this->_redirect('admin/payment/gateway');
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_gateway');
    // Test curl support
    if( !function_exists('curl_version') ||
        !($info = curl_version()) ) {
      $this->view->error = $this->view->translate('The PHP extension cURL ' .
          'does not appear to be installed, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }
    else if( !($info['features'] & CURL_VERSION_SSL) ||
        !in_array('https', $info['protocols']) ) {
      $this->view->error = $this->view->translate('The installed version of ' .
          'the cURL PHP extension does not support HTTPS, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }

    // Make paginator
    $select = Engine_Api::_()->getDbtable('gateways', 'sesevent')->select()
        ->where('`plugin` != ?', 'Sesevent_Plugin_Gateway_Testing');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function editAction()
  {
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_gateway');
    // Get gateway
    $gateway = Engine_Api::_()->getDbtable('gateways', 'sesevent')
      ->find($this->_getParam('gateway_id'))
      ->current();
    // Make form
    $this->view->form = $form = $gateway->getPlugin()->getAdminGatewayForm();
    if ( _ENGINE_ADMIN_NEUTER ) {
        return;
    }
    // Populate form
    $form->populate($gateway->toArray());
    if( is_array($gateway->config) ) {
      $form->populate($gateway->config);
    }
    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    // Process
    $values = $form->getValues();
    $enabled = (bool) $values['enabled'];
    unset($values['enabled']);
    // Validate gateway config
    if( $enabled ) {
      $gatewayObject = $gateway->getGateway();
      try {
        $gatewayObject->setConfig($values);
        $response = $gatewayObject->test();
				
      } catch( Exception $e ) {
        $enabled = false;
        $form->populate(array('enabled' => false));
        $form->addError(sprintf('Gateway login failed. Please double check ' .
            'your connection information. The gateway has been disabled. ' .
            'The message was: [%2$d] %1$s', $e->getMessage(), $e->getCode()));
      }
    } else {
      $form->addError('Gateway is currently disabled.');
    }
    // Process
    $message = null;
    try {
      $values = $gateway->getPlugin()->processAdminGatewayForm($values);
    } catch( Exception $e ) {
      $message = $e->getMessage();
      $values = null;
    }
    if( null !== $values ) {
      $gateway->setFromArray(array(
        'enabled' => $enabled,
        'config' => $values,
      ));
      $gateway->save();
      $form->addNotice('Changes saved.');
    } else {
      $form->addError($message);
    }
  }

  public function deleteAction()
  {
    $this->view->form = $form = new Sesevent_Form_Admin_Gateway_Delete();
  }
}