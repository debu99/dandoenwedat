<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Usersponsorshippayrequest.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Usersponsorshippayrequest extends Core_Model_Item_Collection {
	protected $_searchTriggers = false;
  protected $_modifiedTriggers = false;
  protected $_user;
  protected $_gateway;
  protected $_source;
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
		if( $this->state == 'pending' ) {
			$this->state = 'pending';
		}
		$this->save();
		return $this;
	}
	public function onOrderCancel()
	{
		if( $this->state == 'pending' ) {
			$this->state = 'cancelled';
		}
		$this->save();
		return $this;
	}
	
	public function onOrderFailure()
	{
		if( $this->state == 'pending' ) {
			$this->state = 'failed';
		}
		$this->save();
		return $this;
	}
	
	public function onOrderIncomplete()
	{
		if( $this->state == 'pending' ) {
			$this->state = 'incomplete';
		}
		$this->save();
		return $this;
	}
	
	public function onOrderComplete()
	{
		if( $this->state == 'pending' ) {
			$this->state = 'complete';
		}
		$this->save();
		return $this;
	}
}
