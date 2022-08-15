<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Usergateway.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Usergateway extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  protected $_modifiedTriggers = false;
  
  /**
   * @var Engine_Payment_Plugin_Abstract
   */
  protected $_plugin;
  
  /**
   * Get the payment plugin
   *
   * @return Engine_Payment_Plugin_Abstract
   */
  public function getPlugin($is_sponsorship = 'event')
  { 
    if( null === $this->_plugin ) {
			$settings = Engine_Api::_()->getApi('settings', 'core');
   	  $userGatewayEnable = $settings->getSetting('sesevent.userGateway', 'paypal');
			if($userGatewayEnable == 'paypal' && $this->sponsorship == "Sesevent_Plugin_Gateway_Sponsorship_Owner"){
				if($is_sponsorship == 'sponsorship'){
					$class = 'Sesevent_Plugin_Gateway_Sponsorship_Owner';
      	   Engine_Loader::loadClass('Sesevent_Plugin_Gateway_Sponsorship_Owner');
				}else{
				   $class = 'Sesevent_Plugin_Gateway_Event_PayPal';
      	   Engine_Loader::loadClass('Sesevent_Plugin_Gateway_Event_PayPal');
				}
			} else {
         $class = $this->sponsorship;
         Engine_Loader::loadClass($this->sponsorship);
			}
      $plugin = new $class($this);
      if( !($plugin instanceof Engine_Payment_Plugin_Abstract) ) {
        throw new Engine_Exception(sprintf('Payment plugin "%1$s" must ' .
            'implement Engine_Payment_Plugin_Abstract', $class));
      }
      $this->_plugin = $plugin;
    }
    return $this->_plugin;
  }

  /**
   * Get the payment gateway
   * 
   * @return Engine_Payment_Gateway
   */
  public function getGateway($is_sponsorship = 'event')
  {
   if($this->_plugin == 'Sesevent_Plugin_Gateway_Sponsorship_Owner')
    	return $this->getPlugin($is_sponsorship)->getGateway();
		else
			return $this->getPlugin($is_sponsorship)->getGateway();
  }

  /**
   * Get the payment service api
   * 
   * @return Zend_Service_Abstract
   */
  public function getService()
  {
    return $this->getPlugin()->getService();
  }
}
