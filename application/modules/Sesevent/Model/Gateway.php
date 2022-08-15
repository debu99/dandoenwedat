<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Gateway.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Gateway extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  protected $_modifiedTriggers = false;
  protected $_type = 'payment_gateway';
  /**
   * @var Engine_Payment_Plugin_Abstract
   */
  protected $_plugin;
  
  /**
   * Get the payment plugin
   *
   * @return Engine_Payment_Plugin_Abstract
   */
  public function getPlugin($type = 'ticket')
  {
		if($type == 'ticket'){
			if( null === $this->_plugin ) {
				$class = str_replace('Payment','Sesevent',$this->plugin);
				Engine_Loader::loadClass($class);
				$plugin = new $class($this);
				if( !($plugin instanceof Engine_Payment_Plugin_Abstract) ) {
					throw new Engine_Exception(sprintf('Payment plugin "%1$s" must ' .
							'implement Engine_Payment_Plugin_Abstract', $class));
				}
				$this->_plugin = $plugin;
			}
		}else{
				if( null === $this->_plugin ) {
				$class = str_replace('Payment','Sesevent',$this->sponsorship);
				Engine_Loader::loadClass($class);
				$plugin = new $class($this);
				if( !($plugin instanceof Engine_Payment_Plugin_Abstract) ) {
					throw new Engine_Exception(sprintf('Payment plugin "%1$s" must ' .
							'implement Engine_Payment_Plugin_Abstract', $class));
				}
				$this->_plugin = $plugin;
			}
		}
    return $this->_plugin;
  }

  /**
   * Get the payment gateway
   * 
   * @return Engine_Payment_Gateway
   */
  public function getGateway($type = 'ticket')
  {
		return $this->getPlugin($type)->getGateway();
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