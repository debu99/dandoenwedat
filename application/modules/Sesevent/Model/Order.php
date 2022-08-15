<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Order.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Order extends Core_Model_Item_Collection
{
    protected $_searchTriggers = false;
    protected $_modifiedTriggers = false;
    protected $_user;
    protected $_product;
    protected $_gateway;
    protected $_source;
    public function getTicket($params = array())
    {
        $orderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
        $orderTicketTableName = $orderTicket->info('name');
        $select = $orderTicket->select()->where('order_id =?', $params['order_id']);
        if (isset($params['user_id'])) {
            $select->where('owner_id =?', $params['user_id']);
        }

        if (isset($params['event_id'])) {
            $select->where('event_id =?', $params['event_id']);
        }

        return $orderTicket->fetchAll($select);
    }
    public function getTicketCount($params = array())
    {
        $orderTicket = Engine_Api::_()->getDbtable('orderTickets', 'sesevent');
        $orderTicketTableName = $orderTicket->info('name');
        return $orderTicket->select()
            ->from($orderTicketTableName, new Zend_Db_Expr('SUM(quantity)'))
            ->where('order_id =?', $this->order_id)
            ->limit(1)
            ->query()
            ->fetchColumn();
    }
    // Events
    public function onOrderRefund()
    {
        if ($this->state == 'pending') {
            $this->state = 'refunded';
        }
        $this->save();
        return $this;
    }
    public function onOrderPending()
    {
        if ($this->state != 'pending') {
            $this->state = 'pending';
        }
        $this->save();
        return $this;
    }
    public function onOrderCancel()
    {
        if ($this->state != 'pending') {
            $this->state = 'cancelled';
        }
        $this->save();
        return $this;
    }

    public function onOrderFailure()
    {
        if ($this->state != 'pending') {
            $this->state = 'failed';
        }
        $this->save();
        return $this;
    }

    public function onOrderIncomplete()
    {
        if ($this->state != 'pending') {
            $this->state = 'incomplete';
        }
        $this->save();
        return $this;
    }

    public function onOrderComplete()
    {
        if ($this->state != 'pending') {
            $this->state = 'complete';
        }
		$this->save();
		$this->addBuyerToAttendingList();
        return $this;
    }
    public function getProduct()
    {
        if (null === $this->_product) {
            $productTable = Engine_Api::_()->getDbtable('products', 'payment');
            $this->_product = $productTable->fetchRow($productTable->select()
                    ->where('extension_type = ?', 'sesevent_order')
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
    public function getProductParams()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $commissionType = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_admincomn');
        $commissionTypeValue = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_commission');
        $orderAmount = round(($this->total_amount + $this->total_service_tax + $this->total_entertainment_tax), 2);
        $total_price = round($this->total_amount, 1);
        //%age wise
        $currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
        $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $currencyValue = 1;
        if ($currentCurrency != $defaultCurrency) {
            $currencyValue = $settings->getSetting('sesevent.' . $currentCurrency);
        }
        if ($commissionType == 1 && $commissionTypeValue > 0) {
            $this->commission_amount = round(($total_price / $currencyValue) * ($commissionTypeValue / 100), 2);
        } else if ($commissionType == 2 && $commissionTypeValue > 0) {
            $this->commission_amount = $commissionTypeValue;
        }
        $this->save();
        return array(
            'title' => 'order',
            'description' => 'sesevent_ticket',
            'price' => @round(($this->total_amount + $this->total_service_tax + $this->total_entertainment_tax), 2),
            'extension_id' => $this->getIdentity(),
            'extension_type' => 'sesevent_order',
        );
    }
    public function getGatewayIdentity()
    {
        return $this->getProduct()->sku;
    }
    public function getGatewayParams($params = array())
    {
        //get site community title
        $title = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title', '');
        $params['name'] = $title . ' Order No #' . $this->order_id;
        $params['price'] = @round(($this->total_amount + $this->total_service_tax + $this->total_entertainment_tax), 2);
        $params['description'] = 'Orders #' . $this->order_id . ' on ' . $title;
        $params['vendor_product_id'] = $this->getProduct()->sku;
        $params['recurring'] = false;
        $params['tangible'] = false;
        return $params;
    }

    public function addBuyerToAttendingList()
    {
       
        $event = Engine_Api::_()->getItem('sesevent_event', $this->event_id);
		$user = Engine_Api::_()->getItem('user',$this->owner_id);
		
        $db = $event->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            if (!$event->membership()->isMember($user)) {
                $event->membership()
                    ->addMember($user)
                    ->setUserApproved($user);
            }
            $event->increaseGenderCount($user);
            $row = $event->membership()
                ->getRow($user);

            $row->rsvp = 2; //attending
            $row->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
