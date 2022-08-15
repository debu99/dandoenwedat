<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Widget_PurchasePointsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $this->view->form = $form = new Sescredit_Form_Purchasepoint();
    $this->view->priceSymbol = $priceSymbol = Engine_Api::_()->sescredit()->getCurrencySymbol();
    $options = Engine_Api::_()->getDbTable('offers', 'sescredit')->getOffer();
    $multiOptions = $optionArray = array();
    foreach ($options as $option) {
      $multiOptions[$option->offer_id] = $option->point . " Point in " . Engine_Api::_()->sescredit()->getCurrencyPrice($option->point_value,'','',true);
      $optionArray[$option->offer_id]['point'] = $option->point;
      $optionArray[$option->offer_id]['value'] = Engine_Api::_()->sescredit()->getCurrencyPrice($option->point_value,'','',true);
    }
    if (count($options) < 1)
      $form->sescredit_site_offers->setDescription("No Offers Available.  ");
    $form->sescredit_site_offers->setMultiOptions($multiOptions);
    $this->view->optionArray = json_encode($optionArray);
    $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $gatewaySelect = $gatewayTable->select()->where('enabled = ?', 1);
    $gateways = $gatewayTable->fetchAll($gatewaySelect);

    $gatewayPlugins = array();
    foreach ($gateways as $gateway) {
      $gatewayPlugins[] = array(
          'gateway' => $gateway,
          'plugin' => $gateway->getGateway(),
      );
    };
    $this->view->gateways = $gatewayPlugins;
  }

}
