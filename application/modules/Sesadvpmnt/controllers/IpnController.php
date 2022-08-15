<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IpnController.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

include_once APPLICATION_PATH . "/application/modules/Sesadvpmnt/Api/Stripe/init.php";
class Sesadvpmnt_IpnController extends Core_Controller_Action_Standard
{
  public function __call($method, array $arguments)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
// retrieve the request's body and parse it as JSON
    $body = @file_get_contents('php://input');
    $params = json_decode($body,true);
    $gatewayType = $params['action'];
    $gatewayId = ( !empty($params['gateway_id']) ? $params['gateway_id'] : null );
    if( !empty($gatewayType) && 'index' !== $gatewayType ) {
      $params['gatewayType'] = $gatewayType;
    } else {
      $gatewayType = null;
    }
    // Log ipn
    $ipnLogFile = APPLICATION_PATH . '/temporary/log/stripe-ipn.log';
    file_put_contents($ipnLogFile,
        date('c') . ': ' .
        print_r($params, true),
        FILE_APPEND);
    try {
      //Get gateways
      $type = "";
      if($params['type'] == "invoice.payment_succeeded"){
        $type = $params['data']['object']['lines']['data']['metadata']['type'];
      }
      if($type == "sespagepackage_gateway"){
        $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'sespagepackage');
        $gateways = $gatewayTable->fetchAll(array('enabled = ?' => 1));
      } else if($type == "sescontestpackage_gateway") {
        $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'sescontestpackage');
        $gateways = $gatewayTable->fetchAll(array('enabled = ?' => 1));
      } else if($type == "estorepackage_gateway") {
        $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'estorepackage');
        $gateways = $gatewayTable->fetchAll(array('enabled = ?' => 1));
      } else if($type == "estorepackage_gateway") {
        $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'estorepackage');
        $gateways = $gatewayTable->fetchAll(array('enabled = ?' => 1));
      } else {
          $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
          $gateways = $gatewayTable->fetchAll(array('enabled = ?' => 1));
          // Try to detect gateway
      }
      $activeGateway = null;
      foreach( $gateways as $gateway ) {
        $gatewayPlugin = $gateway->getPlugin();
        // Action matches end of plugin
      if($gateway->plugin == "Sesadvpmnt_Plugin_Gateway_Stripe" ) {
          $activeGateway = $gateway;
        }
      }
    } catch( Exception $e ) {
      // Gateway detection failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Gateway detection failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }
    // Gateway could not be detected
    if( !$activeGateway ) {
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Active gateway could not be detected.',
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }
    // Validate ipn
    try {
      $gateway = $activeGateway;
      $gatewayPlugin = $gateway->getPlugin();
    } catch( Exception $e ) {
      // IPN validation failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'IPN validation failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }
    // Process IPN
    try {
      $gatewayPlugin->onIpnTransaction($params);
    } catch( Exception $e ) {
      $gatewayPlugin->getGateway()->getLog()->log($e, Zend_Log::ERR);
      // IPN validation failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'IPN processing failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }
    // Exit
    echo 'OK';
    exit(0);
  }
  }
