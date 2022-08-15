<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: PaymentController.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
include_once APPLICATION_PATH . "/application/modules/Sesadvpmnt/Api/Stripe/init.php";
require 'vendor/autoload.php';

class Sesadvpmnt_PaymentController extends Core_Controller_Action_Standard
{
    /**
     * @var User_Model_User
     */
    protected $_user;

    /**
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * @var Payment_Model_Order
     */
    protected $order_id;

    protected $_type;

    /**
     * @var Payment_Model_Gateway
     */
    protected $_gateway;

    /**
     * @var Payment_Model_Subscription
     */
    protected $_item;
    protected $_sessionNames = array(
        'user' => 'Payment_Subscription',
        'product' => 'Payment_Sesproduct',
        'crowdfunding' => 'crowdfunding',
        'sescrowdfunding_userpayrequest' => 'sescrowdfunding_userpayrequest',
        'sesproduct_userpayrequest' => 'sesproduct_userpayrequest',
        'courses' => 'courses',
        'courses_userpayrequest' => 'courses_userpayrequest');
    /**
     * @var Payment_Model_Package
     */
    protected $_package;

    protected $_module;

    public function init()
    {
        // Get user and session
        $this->_user = Engine_Api::_()->user()->getViewer();
        $this->_session = new Zend_Session_Namespace('Payment_Subscription');
        $this->_session->gateway_id = $this->_getParam('gateway_id', false);
        $requestType = $this->_getParam('type', null);
        $sessionName = isset($requestType) ? $this->_sessionNames[$requestType] : '';
        $this->_session = new Zend_Session_Namespace($sessionName);
        // Check viewer and user
        if (!$this->_user || !$this->_user->getIdentity()) {
            if (!empty($this->_session->user_id)) {
                $this->_user = Engine_Api::_()->getItem('user', $this->_session->user_id);
            }
        }
    }
    public function newstripefulfillAction()
    {
        $table = Engine_Api::_()->getDbtable('gateways', 'payment');
        $select = $table->select()
            ->where('`plugin` = ?', 'Sesadvpmnt_Plugin_Gateway_Stripe');
        $gateway = $table->fetchRow($select);

        \Stripe\Stripe::setApiKey($gateway->config['sesadvpmnt_stripe_secret']);
        $endpoint_secret = $gateway->config['sesadvpmnt_endpoint_secret'];

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            exit();
        }

        if ($event->type == 'checkout.session.completed' || $event->type == "charge.succeeded") {
            $session = $event->data->object;
            $this->fulfill_order($session);
            return http_response_code(200);
        } else {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            return http_response_code(200);
        }
    }

    public function fulfill_order($session)
    {
        $transaction = array(
            "id" => $session->id,
            "status" => "succeeded",
            "currency" => "EUR",
            "amount" => $session->amount_total,
            "metadata" => (object) array(
                "type" => $session->metadata['order_type'],
                "order_id" => $session->metadata['order_id'],
                "gateway_id" => $session->metadata['gateway_id'],
            ),
        );
        $transaction = (object) $transaction;

        switch ($session['metadata']['order_type']) {
            case 'user':
                $this->returnAction($transaction);
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                http_response_code(200);
                break;
            case 'sesevent_order':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $orderEvent = Engine_Api::_()->getItem('sesevent_order', $orderPayment->source_id);
                $event = Engine_Api::_()->getItem('sesevent_event', $orderEvent->event_id);
                $gateway = Engine_Api::_()->getItem('payment_gateway', $transaction->metadata->gateway_id);

                if (!$orderEvent->ragistration_number) {
                    $orderEvent->ragistration_number = Engine_Api::_()->sesevent()->generateTicketCode(8);
                    $orderEvent->save();
                }
                $plugin = $gateway->getPlugin();
                $state = $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                $this->_session->order_id = $transaction->metadata->order_id;

                $orderPayment = Engine_Api::_()->getItem('payment_order', $session['metadata']['order_id']);
                $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                return;
                break;
            default:
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                http_response_code(400);
        }
    }

    public function newstripefailureAction()
    {
        return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'status' => 'failure'));
    }

    public function indexAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
        $requestType = $this->_getParam('type', null);
        if ($requestType == "user") {
            $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
            if (!$gatewayId ||
                !($gateway = Engine_Api::_()->getItem('payment_gateway', $gatewayId)) ||
                !($gateway->enabled)) {
                return false;
            }
            $this->_gateway = $gateway;
            if (!($subscriptionId = $this->_getParam('subscription_id', $this->_session->subscription_id)) ||
                !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscriptionId)) ||
                !($package = Engine_Api::_()->getItem('payment_package', $subscription->package_id))) {
                return $this->_helper->redirector->gotoRoute(array('action' => 'choose'));
            } else {
                if (!empty($this->_session->order_id)) {
                    $previousOrder = $ordersTable->find($this->_session->order_id)->current();
                    if ($previousOrder && $previousOrder->state == 'pending') {
                        $previousOrder->state = 'incomplete';
                        $previousOrder->save();
                    }
                }
                $ordersTable->insert(array(
                    'user_id' => $this->_user->getIdentity(),
                    'gateway_id' => $gateway->gateway_id,
                    'state' => 'pending',
                    'creation_date' => new Zend_Db_Expr('NOW()'),
                    'source_type' => 'payment_subscription',
                    'source_id' => $subscription->subscription_id,
                ));
                $params['order_id'] = $this->_session->order_id = $this->order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
                // For Coupon
                $couponSessionCode = $package->getType() . '-' . $package->package_id . '-' . $subscription->getType() . '-' . $subscription->subscription_id . '-1';
                $params['amount'] = @isset($_SESSION[$couponSessionCode]) ? round($package->price - $_SESSION[$couponSessionCode]['discount_amount']) : $package->price;
                //For Credit integration
                $creditCode = 'credit' . '-payment-' . $package->package_id . '-' . $subscription->subscription_id;
                $sessionCredit = new Zend_Session_Namespace($creditCode);
                if (isset($sessionCredit->total_amount) && $sessionCredit->total_amount > 0) {
                    $params['amount'] = $sessionCredit->total_amount;
                }
                $this->view->amount = $params['amount'];
                $params['type'] = "user";
                $this->view->currency = $params['currency'] = $settings->getSetting('payment.currency', 'USD');

                \Stripe\Stripe::setApiKey($gateway->config['sesadvpmnt_stripe_secret']);
                $intent = \Stripe\PaymentIntent::create([
                    'amount' => $params['amount'] * 100,
                    'currency' => 'eur',
                    'payment_method_types' => ['ideal', 'card'],
                    'metadata' => [
                        "order_type" => "user",
                        "order_id" => $params['order_id'],
                        "gateway_id" => $gateway->gateway_id,
                    ],
                ]);
                $host_name = getenv('HTTP_HOST');
                $intent->return_url = "https://{$host_name}/payment/subscription/finish/state/active";
                $this->view->intent = $intent;
                $this->view->isRecurring = $package->recurrence > 0;
            }
        } elseif ($requestType == "pagepackage") {
            $this->_gateway = $gateway;
        } elseif ($requestType == "product") {
            $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
            if (!$gatewayId ||
                !($gateway = Engine_Api::_()->getItem('payment_gateway', $gatewayId)) ||
                !($gateway->enabled)) {
                return false;
            }
            $this->_gateway = $gateway;
            $this->order_id = $this->_getParam('order_id', $this->_session->order_id);
            $productOrder = Engine_Api::_()->getItem('sesproduct_order', $this->order_id);
            if (!empty($this->_session->order_id)) {
                $previousOrder = $ordersTable->find($this->_session->order_id)->current();
                if ($previousOrder && $previousOrder->state == 'pending') {
                    $previousOrder->state = 'incomplete';
                    $previousOrder->save();
                }
            }
            $ordersTable->insert(array(
                'user_id' => $this->_user->getIdentity(),
                'gateway_id' => $gateway->gateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'sesproduct_order',
                'source_id' => $this->_getParam('order_id'),
            ));
            $params['order_id'] = $this->_session->order_id = $this->order_id = $ordersTable->getAdapter()->lastInsertId();
            $params['amount'] = Engine_Api::_()->getDbTable('orders', 'sesproduct')->getTotalCartPrice($productOrder->getIdentity());
            $this->view->amount = $params['amount'];
            $this->view->currency = $this->_session->currency = $params['currency'] = Engine_Api::_()->sesproduct()->getCurrentCurrency();
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $this->_session->change_rate = $settings->getSetting('sesmultiplecurrency.' . $params['currency']);
            $params['type'] = "product";
        } elseif ($requestType == "crowdfunding") {
            $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
            if (!$gatewayId ||
                !($gateway = Engine_Api::_()->getItem('payment_gateway', $gatewayId)) ||
                !($gateway->enabled)) {
                return false;
            }
            $this->_gateway = $gateway;
            $resource_id = $this->_getParam('crowdfunding_id', null);
            $price = $this->_getParam('price', 0.00);
            if (empty($price)) {
                return $this->_forward('requireauth', 'error', 'core');
            }

            $resource = null;
            if ($resource_id) {
                $resource = Engine_Api::_()->getItem('crowdfunding', $resource_id);
                if ($resource) {
                    $this->view->crowdfunding = $resource;
                } else {
                    return $this->_forward('requireauth', 'error', 'core');
                }

            }
            if (!$resource) {
                return $this->_forward('requireauth', 'error', 'core');
            }

            $resource = Engine_Api::_()->getItem('crowdfunding', $resource_id);
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer_id = $viewer->getIdentity();
            $admin_commission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sescrowdfunding', 'sescrowdfunding_commison');
            $commison_amount = ($price * $admin_commission) / 100;
            $total_amount = $price + $commison_amount;
            $sescrowdfundingOrdersTable = Engine_Api::_()->getDbtable('orders', 'sescrowdfunding');
            $order = $sescrowdfundingOrdersTable->createRow();
            $values = array(
                'crowdfunding_id' => $resource_id,
                'user_id' => $viewer_id,
                'gateway_id' => $gateway->gateway_id,
                'gateway_type' => 'Stripe',
                'fname' => $viewer->displayname,
                'lname' => $viewer->displayname,
                'email' => $viewer->email,
                'commission_amount' => $commison_amount,
                'total_amount' => $total_amount,
                'total_useramount' => $price,
                'creation_date' => new Zend_Db_Expr('NOW()'),
            );
            $order->setFromArray($values);
            $order->save();
            $this->order_id = $order_id = $order->order_id;
            $ordersTable->insert(array(
                'user_id' => $viewer->getIdentity(),
                'gateway_id' => $gateway->gateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'sescrowdfunding_order',
                'source_id' => $order->order_id,
            ));
            $this->view->amount = $params['amount'] = $price;
            $params['type'] = "crowdfunding";
            $params['order_id'] = $order_id = $ordersTable->getAdapter()->lastInsertId();
            $this->view->currency = $params['currency'] = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();

        } elseif ($requestType == "sescrowdfunding_userpayrequest") {
            if (!$this->_session->payment_request_id) {
                return $this->_forward('requireauth', 'error', 'core');
            }

            $item = Engine_Api::_()->getItem('sescrowdfunding_userpayrequest', $this->_session->payment_request_id);
            $crowdfunding = Engine_Api::_()->getItem('crowdfunding', $item->crowdfunding_id);
            // Get gateway
            $gatewayId = $item->gateway_id;
            $sessionReturn = new Zend_Session_Namespace();
            $gateway = Engine_Api::_()->getDbtable('usergateways', 'sesbasic')->getUserGateway(array('user_id' => $crowdfunding->owner_id, 'gateway_type' => "stripe"));
            if (!$gatewayId || !($gateway) || !($gateway->enabled)) {
                return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
            }
            $params['gateway_id'] = $gatewayId;
            $this->_gateway = $this->view->gateway = $gateway;
            $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway(array('plugin' => $gateway->plugin, 'is_sponsorship' => 'sescrowdfunding'));
            $plugin = $gateway->getPlugin($gateway->plugin);
            //Process
            $ordersTable->insert(array(
                'user_id' => $viewer->getIdentity(),
                'gateway_id' => $gateway->usergateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'sescrowdfunding_userpayrequest',
                'source_id' => $item->userpayrequest_id,
            ));
            $sessionReturn->sescrowdfunding_item_id = $item->getIdentity();
            $params['order_id'] = $this->_session->sescrowdfunding_order_id = $ordersTable->getAdapter()->lastInsertId();
            $this->_session->sescrowdfunding_item_id = $item->getIdentity();
            $this->view->amount = $params['amount'] = $item->release_amount;
            $params['type'] = "sescrowdfunding_userpayrequest";
            $this->view->currency = $params['currency'] = $item->currency_symbol ? $item->currency_symbol : $settings->getSetting('payment.currency', 'USD');
        } elseif ($requestType == "sesproduct_userpayrequest") {
            if (!$this->_session->payment_request_id) {
                return $this->_forward('requireauth', 'error', 'core');
            }

            $item = Engine_Api::_()->getItem('sesproduct_userpayrequest', $this->_session->payment_request_id);
            $store = Engine_Api::_()->getItem('stores', $item->store_id);
            // Get gateway
            $gateway = Engine_Api::_()->getDbtable('usergateways', 'sesproduct')->getUserGateway(array('store_id' => $store->store_id, 'gateway_type' => 'stripe'));
            if (!($gateway) || !($gateway->enabled)) {
                return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
            }
            $sessionReturn = new Zend_Session_Namespace();
            $this->_gateway = $this->view->gateway = $gateway;
            $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway($gateway->plugin);
            $plugin = $gateway->getPlugin();
            // Process
            $ordersTable->insert(array(
                'user_id' => $viewer->getIdentity(),
                'gateway_id' => $gateway->usergateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'sesproduct_userpayrequest',
                'source_id' => $item->userpayrequest_id,
            ));
            $params['order_id'] = $this->_session->sesproduct_order_id = $ordersTable->getAdapter()->lastInsertId();
            $this->_session->sesproduct_item_id = $item->getIdentity();
            $sessionReturn->sesproduct_item_id = $item->getIdentity();
            $this->view->amount = $params['amount'] = $item->release_amount;
            $this->view->currency = $params['currency'] = $item->currency_symbol ? $item->currency_symbol : $settings->getSetting('payment.currency', 'USD');
            $params['type'] = "sesproduct_userpayrequest";
        } elseif ($requestType == "courses") {
            $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
            if (!$gatewayId ||
                !($gateway = Engine_Api::_()->getItem('payment_gateway', $gatewayId)) ||
                !($gateway->enabled)) {
                return false;
            }
            $this->_gateway = $gateway;
            $this->_session = new Zend_Session_Namespace('Payment_Courses');
            $this->order_id = $this->_getParam('order_id', $this->_session->order_id);
            $courseOrder = Engine_Api::_()->getItem('courses_order', $this->order_id);
            if (!empty($this->_session->order_id)) {
                $previousOrder = $ordersTable->find($this->_session->order_id)->current();
                if ($previousOrder && $previousOrder->state == 'pending') {
                    $previousOrder->state = 'incomplete';
                    $previousOrder->save();
                }
            }
            $ordersTable->insert(array(
                'user_id' => $this->_user->getIdentity(),
                'gateway_id' => $gateway->gateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'courses_order',
                'source_id' => $this->_getParam('order_id'),
            ));
            $params['order_id'] = $this->_session->order_id = $this->order_id = $ordersTable->getAdapter()->lastInsertId();
            $this->view->amount = $params['amount'] = $courseOrder->total_amount;
            $this->view->currency = $this->_session->currency = $params['currency'] = Engine_Api::_()->courses()->getCurrentCurrency();
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $this->_session->change_rate = $settings->getSetting('sesmultiplecurrency.' . $params['currency']);
            $params['type'] = "courses";
        } elseif ($requestType == "courses_userpayrequest") {
            $session = new Zend_Session_Namespace("courses_userpayrequest");
            if (!$session->payment_request_id) {
                return $this->_forward('requireauth', 'error', 'core');
            }

            $item = Engine_Api::_()->getItem('courses_userpayrequest', $session->payment_request_id);
            $course = Engine_Api::_()->getItem('courses', $item->course_id);
            // Get gateway
            $gateway = Engine_Api::_()->getDbtable('usergateways', 'courses')->getUserGateway(array('course_id' => $course->course_id, 'gateway_type' => 'stripe'));
            if (!($gateway) || !($gateway->enabled)) {
                return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
            }
            $sessionReturn = new Zend_Session_Namespace();
            $this->_gateway = $this->view->gateway = $gateway;
            $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway($gateway->plugin);
            $plugin = $gateway->getPlugin();
            // Process
            $ordersTable->insert(array(
                'user_id' => $viewer->getIdentity(),
                'gateway_id' => $gateway->usergateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'courses_userpayrequest',
                'source_id' => $item->userpayrequest_id,
            ));
            $sessionReturn->courses_order_id = $params['order_id'] = $session->courses_order_id = $ordersTable->getAdapter()->lastInsertId();
            $session->courses_item_id = $item->getIdentity();
            $sessionReturn->courses_item_id = $item->getIdentity();
            $this->view->amount = $params['amount'] = $item->release_amount;
            $this->view->currency = $params['currency'] = $item->currency_symbol ? $item->currency_symbol : $settings->getSetting('payment.currency', 'USD');
            $params['type'] = "courses_userpayrequest";
        } elseif ($requestType == "sesevent_order") {
            $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
            if (!$gatewayId ||
                !($gateway = Engine_Api::_()->getItem('payment_gateway', $gatewayId)) ||
                !($gateway->enabled)) {
                return false;
            }
            $this->_gateway = $gateway;
            $this->order_id = $this->_getParam('order_id', $this->_session->order_id);
            $order = Engine_Api::_()->getItem('sesevent_order', $this->order_id);
            $ordersTable->insert(array(
                'user_id' => $viewer->getIdentity(),
                'gateway_id' => $gateway->gateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'sesevent_order',
                'source_id' => $order->order_id,
            ));
            $this->_session->sesevent_order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
            $currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
            $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $currencyValue = 1;
            if ($currentCurrency != $defaultCurrency) {
                $currencyValue = $settings->getSetting('sesmultiplecurrency.' . $currentCurrency);
            }
            $ticket_order = array();
            $event = Engine_Api::_()->getItem('sesevent_event', $order->event_id);
            $orderTicket = $order->getTicket(array('order_id' => $order->order_id, 'event_id' => $event->event_id, 'user_id' => $viewer->user_id));
            $priceTotal = $entertainment_tax = $service_tax = $totalTicket = 0;
            foreach ($orderTicket as $val) {
                $ticket = Engine_Api::_()->getItem('sesevent_ticket', $val['ticket_id']);
                $price = @round($ticket->price * $currencyValue, 2);
                $entertainmentTax = @round($ticket->entertainment_tax, 2);
                $taxEntertainment = @round($price * ($entertainmentTax / 100), 2);
                $serviceTax = @round($ticket->service_tax, 2);
                $taxService = @round($price * ($serviceTax / 100), 2);
                $priceTotal = @round($val['quantity'] * $price + $priceTotal, 2);
                $service_tax = @round(($taxService * $val['quantity']) + $service_tax, 2);
                $entertainment_tax = @round(($taxEntertainment * $val['quantity']) + $entertainment_tax, 2);
                $totalTicket = $val['quantity'] + $totalTicket;
            }
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')):
                $couponSessionCode = '-' . '-' . $event->getType() . '-' . $event->event_id . '-0';
                $priceTotal = @isset($_SESSION[$couponSessionCode]) ? round($priceTotal - $_SESSION[$couponSessionCode]['discount_amount']) : $priceTotal;
            endif;
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit')):
                $creditCode = 'credit' . '-sesevent-' . $event->event_id . '-' . $event->event_id;
                $sessionCredit = new Zend_Session_Namespace($creditCode);
                if (isset($sessionCredit->total_amount) && $sessionCredit->total_amount > 0):
                    $priceTotal = $sessionCredit->total_amount;
                endif;
            endif;
            $totalTaxtAmt = @round($service_tax + $entertainment_tax, 2);
            $subTotal = @round($priceTotal - $totalTaxtAmt, 2);
            $order->total_amount = @round(($priceTotal / $currencyValue), 2);
            $order->change_rate = $currencyValue;
            $order->total_service_tax = @round(($service_tax / $currencyValue), 2);
            $order->total_entertainment_tax = @round(($entertainment_tax / $currencyValue), 2);
            $order->creation_date = date('Y-m-d H:i:s');
            $totalAmount = round($priceTotal + $service_tax + $entertainment_tax, 2);
            $order->total_tickets = $totalTicket;
            $order->gateway_type = 'Stripe';
            $commissionType = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_admincomn');
            $commissionTypeValue = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_commission');
            //%age wise
            if ($commissionType == 1 && $commissionTypeValue > 0) {
                $order->commission_amount = round(($priceTotal / $currencyValue) * ($commissionTypeValue / 100), 2);
            } else if ($commissionType == 2 && $commissionTypeValue > 0) {
                $order->commission_amount = $commissionTypeValue;
            }
            $order->save();
            $this->view->amount = $params['amount'] = @round($priceTotal + $totalTaxtAmt, 2);
            $this->view->currency = $params['currency'] = $currentCurrency;
            $params['type'] = "sesevent_order";
            $params['order_id'] = $order_id;

            \Stripe\Stripe::setApiKey($gateway->config['sesadvpmnt_stripe_secret']);
            $intent = \Stripe\PaymentIntent::create([
                'amount' => ($priceTotal + $totalTaxtAmt) * 100,
                'currency' => 'eur',
                'payment_method_types' => ['ideal', 'card'],
                'metadata' => [
                    "order_type" => "sesevent_order",
                    "order_id" => $order_id,
                    "gateway_id" => $gatewayId,
                ],
            ]);
            $this->view->intent = $intent;
            $host_name = getenv('HTTP_HOST');
            $this->view->intent->return_url = "https://{$host_name}/sesevent/order/success/event_id/{$event->custom_url}/order_id/{$this->order_id}/state/active";

        } elseif ($requestType == "sesevent_userpayrequest") {
            $session = new Zend_Session_Namespace();
            if (!$session->payment_request_id) {
                return $this->_forward('requireauth', 'error', 'core');
            }

            $order = Engine_Api::_()->getItem('sesevent_userpayrequest', $session->payment_request_id);
            $event = Engine_Api::_()->getItem('sesevent_event', $order->event_id);
            // Get gateway
            $gateway = Engine_Api::_()->getDbtable('usergateways', 'sesevent')->getUserGateway(array('event_id' => $event->event_id, 'gateway_type' => 'stripe'));
            if (!($gateway) || !($gateway->enabled)) {
                return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
            }
            $this->_gateway = $gateway;
            $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway();
            $plugin = $gateway->getPlugin();
            // Process
            $ordersTable->insert(array(
                'user_id' => $viewer->getIdentity(),
                'gateway_id' => $gateway->usergateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'sesevent_userpayrequest',
                'source_id' => $order->userpayrequest_id,
            ));
            $currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
            $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $currencyValue = 1;
            if ($currentCurrency != $defaultCurrency) {
                $currencyValue = $settings->getSetting('sesevent.' . $currentCurrency);
            }
            $session = new Zend_Session_Namespace();
            $session->sesevent_order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
            $session->sesevent_item_id = $order->getIdentity();
            $this->view->amount = $params['amount'] = @round($order->release_amount, 2);
            $this->view->currency = $params['currency'] = $currentCurrency;
            $params['type'] = "sesevent_userpayrequest";
            $params['order_id'] = $order_id;
        }
        if ($this->_getParam('type', null) == "booking") {
            if (!$gatewayId ||
                !($gateway = Engine_Api::_()->getItem('payment_gateway', $gatewayId)) ||
                !($gateway->enabled)) {
                return false;
            }
            $this->_gateway = $gateway;
            $viewer = Engine_Api::_()->user()->getViewer();
            $this->order_id = $this->_getParam('order_id', $this->_session->order_id);
            $order = Engine_Api::_()->getItem('booking_order', $this->order_id);
            $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
            $ordersTable->insert(array(
                'user_id' => $viewer->getIdentity(),
                'gateway_id' => $gateway->gateway_id,
                'state' => 'pending',
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'source_type' => 'booking_order',
                'source_id' => $order->order_id,
            ));
        }
        $this->view->publishKey = $publishKey = $gateway->config['sesadvpmnt_stripe_publish'];
        $this->view->title = $title = $gateway->config['sesadvpmnt_stripe_title'];
        $this->view->description = $description = $gateway->config['sesadvpmnt_stripe_description'];
        $this->view->logo = $logo = $gateway->config['sesadvpmnt_stripe_logo'];
        $this->view->request_type = $requestType;
        $plugin = $this->_gateway->getPlugin();
        $this->_type = $this->_getParam('type');
        // Unset certain keys
        unset($this->_session->gateway_id);
        if (!array_key_exists(strtoupper($params['currency']), $plugin->getSupportedCurrencies())) {
            return false;
        }

        if (isset($_POST['stripeToken'])) {
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $this->view->secretKey = $secretKey = $gateway->config['sesadvpmnt_stripe_secret'];
            \Stripe\Stripe::setApiKey($secretKey);
            if ($requestType == "user") {
                $params['token'] = $_POST['stripeToken'];
                $params['gateway'] = $gateway;
                if ($package->isOneTime()) {
                    $transaction = $plugin->createOrderTransaction($params);
                } else {
                    $customer = \Stripe\Customer::create([
                        "source" => $params['token'],
                        "email" => $_POST['stripeEmail'],
                    ]);
                    $params['customer'] = $customer->id;
                    $transaction = $plugin->createSubscriptionTransaction($this->_user,
                        $subscription, $package, $params);
                }
                $this->statusAction($transaction);
            } else {
                $params['token'] = $_POST['stripeToken'];
                $transactionInfo = $plugin->createOrderTransaction($params);
                $this->statusAction($transactionInfo);
            }
        }
        if ($this->_getParam('status', false)) {
            $session = new Zend_Session_Namespace('Stripe_Error');
            $this->view->error = $session->errorMessage;
        }
    }

    /*
     * $transaction
     * array['fields']
     *         ['id]         string ex.:ch_1HfOCSBIUyDQdS4SbprzLp0p
     *         ['status']    string status of the payment, ex.: 'succeeded' or 'active'
     *         ['currency']  string ex.: EUR
     *         ['amount']    string ex.: 500
     *         ['metadata']  array
     *              ['type]      string defines what category the payment is for. Possible values: 'user', 'product', 'courses' ... and 'sesevent_order'
     *              ['order_id]  string defines for which order the payment is made
     *
     * @param array $transaction see above
     *
     */
    public function statusAction($transaction)
    {
        switch ($transaction->status) {
            case "active":
            case "succeeded":
                $this->getPaymentInfo($transaction);
                break;
            default:
                if (!empty($transaction)) {
                    $secretKey = $this->_gateway->config['sesadvpmnt_stripe_secret'];
                    \Stripe\Stripe::setApiKey($secretKey);
                    $re = \Stripe\Refund::create([
                        "charge" => $transaction->id,
                    ]);
                }
                return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'status' => 'failure'));
        }
    }

    public function getPaymentInfo($transaction)
    {
        switch ($transaction->metadata->type) {
            case 'user':
                $this->returnAction($transaction);
                break;
            case 'product':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $plugin = $this->_gateway->getPlugin();
                $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                Engine_Api::_()->getDbTable('orders', 'sesproduct')->update(array('state' => 'complete'), array('order_id =?' => $this->order_id));
                Engine_Api::_()->getDbtable('orders', 'payment')->update(array('state' => 'complete'), array('order_id =?' => $this->order_id));
                return $this->_helper->redirector->gotoRoute(array('action' => 'return', 'order_id' => $orderPayment->source_id), 'sesproduct_payment');
                break;
            case 'courses':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $plugin = $this->_gateway->getPlugin();
                $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                Engine_Api::_()->getDbTable('orders', 'courses')->update(array('state' => 'complete'), array('order_id =?' => $this->order_id));
                Engine_Api::_()->getDbtable('orders', 'payment')->update(array('state' => 'complete'), array('order_id =?' => $this->order_id));
                return $this->_helper->redirector->gotoRoute(array('action' => 'return', 'order_id' => $orderPayment->source_id), 'courses_payment');
                break;
            case 'courses_userpayrequest':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $plugin = $this->_gateway->getPlugin();
                $state = $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                $session = new Zend_Session_Namespace();
                $session->payment_request_id = $transaction->metadata->order_id;
                $session->status = $state;
                return $this->_helper->redirector->gotoRoute(array('route' => 'default', 'module' => 'courses', 'controller' => 'payment', 'action' => 'return', 'type' => 'stripe'), 'admin_default');
                break;
            case 'crowdfunding':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $plugin = $this->_gateway->getPlugin();
                $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                Engine_Api::_()->getDbtable('orders', 'sescrowdfunding')->update(array('state' => 'complete'), array('order_id =?' => $this->order_id));
                Engine_Api::_()->getDbtable('orders', 'payment')->update(array('state' => 'complete'), array('order_id =?' => $this->order_id));
                return $this->_helper->redirector->gotoRoute(array('action' => 'return', 'order_id' => $this->order_id), 'sescrowdfunding_payment');
                break;
            case 'sescrowdfunding_userpayrequest':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $plugin = $this->_gateway->getPlugin();
                $status = $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                $params['state'] = $state;
                $session = new Zend_Session_Namespace();
                $session->payment_request_id = $transaction->metadata->order_id;
                $session->status = $status;
                return $this->_helper->redirector->gotoRoute(array('route' => 'default', 'module' => 'sescrowdfunding', 'controller' => 'payment', 'action' => 'return', 'type' => 'stripe'), 'admin_default');
                break;
            case 'sesproduct_userpayrequest':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $plugin = $this->_gateway->getPlugin();
                $state = $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                $session = new Zend_Session_Namespace();
                $session->sesproduct_order_id = $transaction->metadata->order_id;
                $session->status = $state;
                return $this->_helper->redirector->gotoRoute(array('route' => 'default', 'module' => 'sesproduct', 'controller' => 'payment', 'action' => 'return', 'type' => 'stripe'), 'admin_default');
                break;
            case 'sesevent_order':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $orderEvent = Engine_Api::_()->getItem('sesevent_order', $orderPayment->source_id);
                $event = Engine_Api::_()->getItem('sesevent_event', $orderEvent->event_id);
                if (!$orderEvent->ragistration_number) {
                    $orderEvent->ragistration_number = Engine_Api::_()->sesevent()->generateTicketCode(8);
                    $orderEvent->save();
                }
                $plugin = $this->_gateway->getPlugin();
                $state = $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                $this->_session->order_id = $transaction->metadata->order_id;
                return $this->_helper->redirector->gotoRoute(array('module' => 'sesevent', 'controller' => 'order', 'action' => 'finish', 'event_id' => $event->custom_url, 'order_id' => $orderPayment->source_id, 'state' => $state), 'default', true);
                break;
            case 'sesevent_userpayrequest':
                $orderPayment = Engine_Api::_()->getItem('payment_order', $transaction->metadata->order_id);
                $plugin = $this->_gateway->getPlugin();
                $state = $plugin->createOrderTransactionReturn($orderPayment, $transaction);
                $session = new Zend_Session_Namespace();
                $session->sesevent_item_id = $transaction->metadata->order_id;
                $session->status = $state;
                return $this->_helper->redirector->gotoRoute(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'payment', 'action' => 'return', 'type' => 'stripe'), 'admin_default');
                break;

        }
        return $params;

    }
    public function returnAction($transaction)
    {
        // Get order
        if (!$this->_user ||
            !($orderId = $transaction->metadata->order_id) ||
            !($order = Engine_Api::_()->getItem('payment_order', $orderId)) ||
            $order->source_type != 'payment_subscription' ||
            !($subscription = $order->getSource()) ||
            !($package = $subscription->getPackage()) ||
            !($gateway = Engine_Api::_()->getItem('payment_gateway', $order->gateway_id))) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }
        // Get gateway plugin
        $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway();
        $plugin = $gateway->getPlugin();
        // Process return
        unset($this->_session->errorMessage);
        try {
            $status = $plugin->onSubscriptionReturn($order, $transaction);
        } catch (Payment_Model_Exception $e) {
            $status = false;
            $this->_session->errorMessage = $e->getMessage();
        }
        return $this->_finishPayment($status);
    }
    protected function _checkSubscriptionStatus(
        Zend_Db_Table_Row_Abstract $subscription = null) {
        if (!$this->_user) {
            return false;
        }
        if (null === $subscription) {
            $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
            $subscription = $subscriptionsTable->fetchRow(array(
                'user_id = ?' => $this->_user->getIdentity(),
                'active = ?' => true,
            ));
        }
        if (!$subscription) {
            return false;
        }

        if ($subscription->status == 'active' ||
            $subscription->status == 'trial') {
            if (!$subscription->getPackage()->isFree()) {
                $this->_finishPayment('active');
            } else {
                $this->_finishPayment('free');
            }
            return true;
        } else if ($subscription->status == 'pending') {
            $this->_finishPayment('pending');
            return true;
        }

        return false;
    }
    protected function _finishPayment($state = 'active')
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $user = $this->_user;

        // No user?
        if (!$this->_user) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        // Log the user in, if they aren't already
        if (($state == 'active' || $state == 'free') &&
            $this->_user &&
            !$this->_user->isSelf($viewer) &&
            !$viewer->getIdentity()) {
            Zend_Auth::getInstance()->getStorage()->write($this->_user->getIdentity());
            Engine_Api::_()->user()->setViewer();
            $viewer = $this->_user;
        }

        // Handle email verification or pending approval
        if ($viewer->getIdentity() && !$viewer->enabled) {
            Engine_Api::_()->user()->setViewer(null);
            Engine_Api::_()->user()->getAuth()->getStorage()->clear();

            $confirmSession = new Zend_Session_Namespace('Signup_Confirm');
            $confirmSession->approved = $viewer->approved;
            $confirmSession->verified = $viewer->verified;
            $confirmSession->enabled = $viewer->enabled;
            return $this->_helper->_redirector->gotoRoute(array('action' => 'confirm'), 'user_signup', true);
        }

        // Clear session
        $errorMessage = $this->_session->errorMessage;
        $userIdentity = $this->_session->user_id;
        $this->_session->unsetAll();
        $this->_session->user_id = $userIdentity;
        $this->_session->errorMessage = $errorMessage;

        // Redirect
        if ($state == 'free') {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else {
            return $this->_helper->redirector->gotoRoute(array('action' => 'finish', 'state' => $state));
        }
    }

    public function finishAction()
    {
        $this->view->status = $status = $this->_getParam('state');
        $this->view->error = $this->_session->errorMessage;
        $this->view->url = $this->view->url(array(), 'default', true);
        // If user's member level changed then redirect to edit profile page.
        if (Engine_Api::_()->getDbtable('values', 'authorization')->changeUsersProfileType($this->_user)) {
            Engine_Api::_()->getDbtable('values', 'authorization')->resetProfileValues($this->_user);
            $this->view->url = $this->view->url(array('action' => 'profile', 'controller' => 'edit'), 'user_extended');
        }
    }

    protected function _checkDefaultPaymentPlan()
    {
        // No user?
        if (!$this->_user) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        // Handle default payment plan
        try {
            $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
            if ($subscriptionsTable) {
                $subscription = $subscriptionsTable->activateDefaultPlan($this->_user);
                if ($subscription) {
                    return $this->_finishPayment('free');
                }
            }
        } catch (Exception $e) {
            // Silence
        }

        // Fall-through
    }

}
