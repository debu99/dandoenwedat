<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sponsorshiporder.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Sponsorshiporder extends Core_Model_Item_Collection {
	protected $_searchTriggers = false;
  protected $_modifiedTriggers = false;
  protected $_user;
	protected $_product;
  protected $_gateway;
  protected $_source;
	public function getTicket($params = array()){
		$orderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
		$orderTicketTableName =  $orderTicket->info('name');
	  $select = $orderTicket->select()->where('order_id =?',$params['order_id']);
		if(isset($params['user_id']))
			$select->where('owner_id =?',$params['user_id']);
		if(isset($params['event_id']))
			$select->where('event_id =?',$params['event_id']);
		return $orderTicket->fetchAll($select);
	}
	public function getTicketCount($params = array()){
		$orderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
		$orderTicketTableName =  $orderTicket->info('name');
	  return $orderTicket->select()
              ->from($orderTicketTableName, new Zend_Db_Expr('SUM(quantity)'))
              ->where('order_id =?', $this->order_id)
              ->limit(1)
              ->query()
              ->fetchColumn();
	}
	// Events
	public function onOrderRefund(){
	if( $this->state == 'pending' ) {
			$this->state = 'refunded';
		}
		$this->save();
		return $this;
	}
	public function onOrderPending()
	{
		if( $this->state != 'pending' ) {
			$this->state = 'pending';
		}
		$this->save();
		return $this;
	}
	public function onOrderCancel()
	{
		if( $this->state != 'pending' ) {
			$this->state = 'cancelled';
		}
		$this->save();
		return $this;
	}
	
	public function onOrderFailure()
	{
		if( $this->state != 'pending' ) {
			$this->state = 'failed';
		}
		$this->save();
		return $this;
	}
	
	public function onOrderIncomplete()
	{
		if( $this->state != 'pending' ) {
			$this->state = 'incomplete';
		}
		$this->save();
		return $this;
	}
	
 public function onOrderComplete()
	{
		if( $this->state != 'pending' ) {
			$this->state = 'complete';
		}
		$this->save();
		return $this;
 }
 public function getProduct() {
		if (null === $this->_product) {
			$productTable = Engine_Api::_()->getDbtable('products', 'payment');
			$this->_product = $productTable->fetchRow($productTable->select()
											->where('extension_type = ?', 'sesevent_sponsorshiporder')
											->where('extension_id = ?', $this->getIdentity())
											->limit(1));
			// Create a new product?
			if (!$this->_product) {
					$this->_product = $productTable->createRow();
					$this->_product->setFromArray($this->getProductParams());
					$this->_product->save();
			}
		}
		return $this->_product;
	}
	public function getProductParams() {
			return array(
					'title' => 'Order',
					'description' => 'sesevent_sponsorship',
					'price' => @round(($this->total_amount), 2),
					'extension_id' => $this->getIdentity(),
					'extension_type' => 'sesevent_sponsorshiporder',
			);
    }
	public function getGatewayIdentity() {
				return $this->getProduct()->sku;
	}
	public function getGatewayParams($params = array()) {
		//get site community title
		$title = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title', '');
		$params['name'] = $title . ' Order No #' . $this->sponsorshiporder_id;
		$params['price'] = @round(($this->total_amount), 2);
		$params['description'] = 'Orders #' . $this->sponsorshiporder_id . ' on ' . $title;
		$params['vendor_product_id'] = $this->getProduct()->sku;
		$params['recurring'] = false;
		$params['tangible'] = false;
		return $params;
    }
}