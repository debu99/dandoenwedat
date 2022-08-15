<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Stripe.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvpmnt_Form_Admin_Settings_Stripe extends Engine_Form {

  public function init() {

    $this->setTitle('Payment Gateway: Stripe');

    $description = $this->getTranslator()->translate('SESADVPMNT_FORM_ADMIN_GATEWAY_STRIPE_DESCRIPTION');
    $description = vsprintf($description, array(
      'https://dashboard.stripe.com/register',
      'https://dashboard.stripe.com/account/webhooks',
      'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'sesadvpmnt',
          'controller' => 'ipn',
          'action' => 'stripe'
        ), 'default', true),
    ));
    $this->setDescription($description);

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    //New File System Code
    $banner_options = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
    foreach( $files as $file ) {
      $banner_options[$file->storage_path] = $file->name;
    }
	  $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
	  $fileLink = $view->baseUrl() . '/admin/files/';
     $settings = Engine_Api::_()->getApi('settings', 'core');
     $this->setTitle('Manage Your Stripe Account');
     $this->addElement('Text', "sesadvpmnt_stripe_publish", array(
            'label' => 'Stripe Publishable key',
            'required' => true,
            'allowEmpty' => false,
     ));
      $this->addElement('Text', "sesadvpmnt_stripe_secret", array(
            'label' => 'Stripe Secret key',
            'required' => true,
            'allowEmpty' => false,
      ));
      $this->addElement('Text', "sesadvpmnt_endpoint_secret", array(
       'label' => 'Stripe Endpoint Secret',
       'required' => true,
       'description'=> "<span>You need to register a webhook via the stripe dashboard with the following url: <small>https://dandoenwedat.com/sesadvpmnt/payment/newstripefulfill/route/default/type/sesevent_order/order_id/dummy/gateway_id/dummy</small></span>",
       'allowEmpty' => false,
       'escape'=>false
      ));
      $this->getElement("sesadvpmnt_endpoint_secret")->getDecorator('Description')->setEscape(false);

      $this->addElement('Text', "sesadvpmnt_stripe_title", array(
            'label' => 'Stripe Form Title.',
     ));
     $this->addElement('Text', "sesadvpmnt_stripe_description", array(
            'label' => 'Stripe Form Description.',
     ));
     $this->addElement('Select', 'sesadvpmnt_stripe_logo', array(
            'label' => 'Site Logo',
            'multiOptions' => $banner_options,
    ));
    $this->sesadvpmnt_stripe_description->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    $this->addElement('Radio', "enabled", array(
            'label' => 'Enable?',
            'multiOptions' => array('1' => 'Yes', '0' => 'No'),
     ));
      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));
  }

}
