<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: OrderController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_OrderController extends Core_Controller_Action_Standard
{
    public function init()
    {
        if (!$this->_helper->requireUser->isValid()) {
            return;
        }

        $id = $this->_getParam('order_id', null);
        $order = Engine_Api::_()->getItem('sesevent_order', $id);

        if ($order) {
            Engine_Api::_()->core()->setSubject($order);
        } else {
            return $this->_forward('requireauth', 'error', 'core');
        }

    }
    public function indexAction()
    {
        $order_id = $this->_getParam('order_id', null);
        if (!$order_id) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $id = $this->_getParam('event_id', null);

        $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
        $event = null;

        if ($event_id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        }

        if ($event) {
            $this->view->event = $event;
        } else {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $this->view->order = $order = Engine_Api::_()->core()->getSubject();
        if ($order->state == 'complete') {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->fnamelname = Engine_Api::_()->sesbasic()->getUserFnameLname();

        $this->view->ticketDetail = $ticketDetails = $order->getTicket(array('order_id' => $order->order_id, 'user_id' => $viewer->getIdentity(), 'event_id' => $event->event_id));
        if (!$ticketDetails) {
            return $this->_forward('requireauth', 'error', 'core');
        }

    }
    public function checkoutAction()
    {
        $order_id = $this->_getParam('order_id', null);
        if (!$order_id) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $id = $this->_getParam('event_id', null);
        $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
        $event = null;

        if ($event_id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
            if ($event) {
                $this->view->event = $event;
            } else {
                return $this->_forward('requireauth', 'error', 'core');
            }

        }

        if (!$event) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $this->view->order = $order = Engine_Api::_()->core()->getSubject();
        if ($order->state == 'complete') {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->ticketDetail = $ticketDetails = $order->getTicket(array('order_id' => $order->order_id, 'user_id' => $viewer->getIdentity(), 'event_id' => $event->event_id));
        $postTicketUser = isset($_POST) ? $_POST : '';

        //save details of ticket buyer.
        if (is_array($postTicketUser) && count($postTicketUser)) {
            //delete previous details if any
            $db = Engine_Db_Table::getDefaultAdapter();
            $db->query("DELETE FROM engine4_sesevent_orderticketdetails WHERE order_id = " . $order->order_id);
            //update buyer info in order
            $order->fname = isset($_POST['fname_owner']) ? $_POST['fname_owner'] : '';
            $order->lname = isset($_POST['lname_owner']) ? $_POST['lname_owner'] : '';
            $order->email = isset($_POST['email_owner']) ? $_POST['email_owner'] : '';
            $order->mobile = isset($_POST['mobile_owner']) ? $_POST['mobile_owner'] : '';
            $order->company_title = isset($_POST['cdetails_owner']) ? $_POST['cdetails_owner'] : '';
            $order->save();
            //save details again
            foreach ($postTicketUser['firstName'] as $key => $val) {
                $counter = 1;
                foreach ($val as $keyTic => $valTk) {
                    //generate ticket code
                    $ticketCode = Engine_Api::_()->sesevent()->generateTicketCode(8, 'orderticketdetails');
                    $registration_number = $ticketCode;
                    $lastName = isset($_POST['lastName'][$key][$counter]) ? $_POST['lastName'][$key][$counter] : '';
                    $email = isset($_POST['email'][$key][$counter]) ? $_POST['email'][$key][$counter] : '';
                    $mobile = isset($_POST['mobile'][$key][$counter]) ? $_POST['mobile'][$key][$counter] : '';
                    $db->query("INSERT INTO engine4_sesevent_orderticketdetails (first_name,last_name,email,ticket_id,ticket_number,order_id,mobile,registration_number) VALUES('" . $valTk . "','" . $lastName . "','" . $email . "','" . $key . "','" . $keyTic . "','" . $order->order_id . "','" . $mobile . "','" . $registration_number . "')");
                    $counter++;
                }
            }
        }
        //check free ticket order
        if ($order->total_amount <= 0) {
            $url = $this->view->escape($this->view->url(array('action' => 'free-order')));
            header('location:' . $url);die;
        }
        // Gateways
        $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
        $gatewaySelect = $gatewayTable->select()->where('enabled = ?', 1);
        $gateways = $gatewayTable->fetchAll($gatewaySelect);
        $gatewayPlugins = array();
        foreach ($gateways as $gateway) {
            $gatewayPlugins[] = array(
                'gateway' => $gateway,
                'plugin' => $gateway->getGateway(),
            );
        }
        $this->view->gateways = $gatewayPlugins;
    }

    public function freeOrderAction()
    {
        $order_id = $this->_getParam('order_id', null);
        if (!$order_id) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $id = $this->_getParam('event_id', null);
        $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
        $event = null;

        if ($event_id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
            if ($event) {
                $this->view->event = $event;
            } else {
                return $this->_forward('requireauth', 'error', 'core');
            }
        }

        if (!$event) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $this->view->order = $order = $eventOrder = Engine_Api::_()->core()->getSubject();
        $order->gateway_type = $this->view->translate('FREE');
        $order->save();
        if ($order->state == 'complete') {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id' => $order->order_id, 'state' => 'complete'));
        // success
        $order->onOrderComplete();
        $session = new Zend_Session_Namespace();
        $session->sesevent_order_id = $order->order_id;
        $url = $this->view->escape($this->view->url(array('action' => 'success', 'state' => 'success')));
        //send email
        $this->view->buyer = Engine_Api::_()->getItem('user', $order->owner_id);
        $this->view->viewer = $viewer = $user = Engine_Api::_()->user()->getViewer();
        $orderDetails = Engine_Api::_()->getDbTable('orderticketdetails', 'sesevent')->orderTicketDetails(array('order_id' => $order->order_id));

        $event->addBuyerToAttendingList();
        $this->sendTicketEmail();
        header('location:' . $url);die;
    }

    public function processAction()
    {

        $gatewayId = $this->_getParam('gateway_id', null);
        $order_id = $this->_getParam('order_id', null);
        if (!$order_id) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $id = $this->_getParam('event_id', null);
        $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
        $event = null;
        if ($event_id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        }

        if ($event) {
            $this->view->event = $event;
        } else {
            return $this->_forward('requireauth', 'error', 'core');
        }

        if (!$event) {
            return $this->_forward('requireauth', 'error', 'core');
        }

        $this->view->order = $order = Engine_Api::_()->core()->getSubject();
        if ($order->state == 'complete') {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        if (!$gatewayId ||
            !($gateway = Engine_Api::_()->getItem('sesevent_gateway', $gatewayId)) ||
            !($gateway->enabled)) {
            header("location:" . $this->view->escape($this->view->url(array('action' => 'checkout'))));
            die;
        }

        $this->view->gateway = $gateway;
        $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway();
        if ($gateway->plugin == "Sesadvpmnt_Plugin_Gateway_Stripe") {
            return $this->_forward('success', 'utility', 'core', array(
                'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('route' => 'default', 'module' => 'sesadvpmnt', 'controller' => 'payment', 'action' => 'index', 'type' => 'sesevent_order', 'order_id' => $order->order_id, 'gateway_id' => $gateway->gateway_id), 'default', true),
                'messages' => array($this->view->message),
            ));
        } else if ($gateway->plugin == "Epaytm_Plugin_Gateway_Paytm") {
            return $this->_forward('success', 'utility', 'core', array(
                'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('route' => 'default', 'module' => 'epaytm', 'controller' => 'payment', 'action' => 'index', 'type' => 'sesevent_order', 'order_id' => $order->order_id, 'gateway_id' => $gateway->gateway_id), 'default', true),
                'messages' => array($this->view->message),
            ));
        }
        // Prepare host info
        $schema = 'http://';
        if (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) {
            $schema = 'https://';
        }
        $host = $_SERVER['HTTP_HOST'];
        // Prepare transaction
        $params = array();
        $params['language'] = $viewer->language;
        $localeParts = explode('_', $viewer->language);
        if (count($localeParts) > 1) {
            $params['region'] = $localeParts[1];
        }
        $params['vendor_order_id'] = $order_id;
        $params['return_url'] = $schema . $host
        . $this->view->escape($this->view->url(array('action' => 'return')))
            . '/?state=' . 'return';
        $params['cancel_url'] = $this->view->escape($schema . $host
            . $this->view->url(array('action' => 'return')))
            . '/?state=' . 'cancel';
        $params['ipn_url'] = $schema . $host
        . $this->view->url(array('action' => 'index', 'controller' => 'ipn', 'module' => 'payment'), 'default');
        if ($gatewayId == 1) {
            $gatewayPlugin->createProduct(array_merge($order->getGatewayParams(), array('approved_url' => $params['return_url'])));
        }
        $plugin = $gateway->getPlugin();
        $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
        // Process
        $ordersTable->insert(array(
            'user_id' => $viewer->getIdentity(),
            'gateway_id' => $gateway->gateway_id,
            'state' => 'pending',
            'creation_date' => new Zend_Db_Expr('NOW()'),
            'source_type' => 'sesevent_order',
            'source_id' => $order->order_id,
        ));

        $session = new Zend_Session_Namespace();
        $session->sesevent_order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
        // Process transaction
        $transaction = $plugin->createOrderTransaction($viewer, $order, $event, $params);
        // Pull transaction params
        $this->view->transactionUrl = $transactionUrl = $gatewayPlugin->getGatewayUrl();
        $this->view->transactionMethod = $transactionMethod = $gatewayPlugin->getGatewayMethod();
        $this->view->transactionData = $transactionData = $transaction->getData();
        // Handle redirection
        if ($transactionMethod == 'GET') {
            $transactionUrl .= '?' . http_build_query($transactionData);
            return $this->_helper->redirector->gotoUrl($transactionUrl, array('prependBase' => false));
        }
        // Post will be handled by the view script
    }
    public function returnAction()
    {
        $this->view->order = $order = Engine_Api::_()->core()->getSubject();
        //if($order->state == 'complete')
        //return $this->_forward('notfound', 'error', 'core');
        $session = new Zend_Session_Namespace();
        // Get order
        $orderId = $this->_getParam('order_id', null);
        $orderPaymentId = $session->sesevent_order_id;
        $orderPayment = Engine_Api::_()->getItem('payment_order', $orderPaymentId);
        if (!$orderPayment || ($orderId != $orderPayment->source_id) ||
            ($orderPayment->source_type != 'sesevent_order') ||
            !($user_order = $orderPayment->getSource())) {
            return $this->_helper->redirector->gotoRoute(array(), 'sesevent_general', true);
        }
        $gateway = Engine_Api::_()->getItem('sesevent_gateway', $orderPayment->gateway_id);
        if (!$gateway) {
            return $this->_helper->redirector->gotoRoute(array(), 'sesevent_general', true);
        }

        // Get gateway plugin
        $params = $this->_getAllParams();
        $plugin = $gateway->getPlugin();
        unset($session->errorMessage);
        try {
            //generate ticket code
            if (!$order->ragistration_number) {
                $order->ragistration_number = Engine_Api::_()->sesevent()->generateTicketCode(8);
                $order->save();
            }
            if ($params['state'] != 'cancel') {
                //get all params
                $status = $plugin->orderTicketTransactionReturn($orderPayment, $params);
            } else {
                $status = 'cancel';
                $session->errorMessage = $this->view->translate('Your payment has been cancelled and not been charged. If this is not correct, please try again later.');
            }
        } catch (Payment_Model_Exception $e) {
            $status = 'failure';
            $session->errorMessage = $e->getMessage();
        }
        return $this->_finishPayment($status, $orderPayment->source_id);
    }
    protected function _finishPayment($state = 'active', $orderPaymentId)
    {
        $session = new Zend_Session_Namespace();
        // Clear session
        $errorMessage = $session->errorMessage;
        $session->errorMessage = $errorMessage;
        // Redirect
        if ($state == 'free') {
            $session->unsetAll();
            return $this->_helper->redirector->gotoRoute(array('sesevent_event'), 'default', true);
        } else {
            $url = $this->view->escape($this->view->url(array('action' => 'finish', 'state' => $state)));
            header('location:' . $url);die;
        }
    }
    public function finishAction()
    {
        $session = new Zend_Session_Namespace();
        $orderTrabsactionDetails = array('state' => $this->_getParam('state'), 'errorMessage' => $session->errorMessage);
        $session->sesevent_order_details = $orderTrabsactionDetails;
        $url = $this->view->escape($this->view->url(array('action' => 'success')));
        header('location:' . $url);die;
    }
    public function checkorderAction()
    {
        $order_id = $this->_getParam('order_id', null);
        $checkOrderStatus = Engine_Api::_()->getDbtable('orders', 'sesevent')->getOrderStatus($order_id);
        if ($checkOrderStatus) {
            echo json_encode(array('status' => true));die;
        } else {
            echo json_encode(array('status' => false));die;
        }

    }
    public function successAction()
    {
        $session = new Zend_Session_Namespace();
        $order_id = $this->_getParam('order_id', null);
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->order = $order = Engine_Api::_()->core()->getSubject();
        $orderEvent = Engine_Api::_()->getItem('sesevent_order', $order_id);
        $checkOrderStatus = Engine_Api::_()->getDbtable('orders', 'sesevent')->getOrderStatus($order_id);

        if (!$order || $order->owner_id != $viewer->getIdentity()) {
            return $this->_forward('notfound', 'error', 'core');
        }

        if (!$order_id) {
            return $this->_forward('notfound', 'error', 'core');
        }

        if ($this->_getParam('redirect_status') == 'failed') {
            return $this->_forward('notfound', 'error', 'core');
        }

        $id = $this->_getParam('event_id', null);
        $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
        $event = null;
        if ($event_id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        }

        if ($event) {
            $this->view->event = $event;
        } else {
            return $this->_forward('notfound', 'error', 'core');
        }

        $state = $this->_getParam('state');
        if (!$state) {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->view->error = $error = $session->errorMessage;
        $session->unsetAll();
        $this->view->state = $state;

        //get ticket count in an order
        $this->view->getTicketCount = $order->getTicketCount();
        if (!$this->view->getTicketCount) {
            return $this->_forward('notfound', 'error', 'core');
        }
    }

    public function viewAction()
    {
        $order_id = $this->_getParam('order_id', null);
        if (!$order_id) {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->view->format = $this->_getParam('format', '');
        $id = $this->_getParam('event_id', null);
        $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
        $event = null;
        if ($event_id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        }

        if ($event) {
            $this->view->event = $event;
        } else {
            return $this->_forward('notfound', 'error', 'core');
        }

        if (!$event) {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->view->order = $order = Engine_Api::_()->core()->getSubject();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->orderDetails = Engine_Api::_()->getDbTable('orderticketdetails', 'sesevent')->orderTicketDetails(array('order_id' => $order->order_id));
        $this->view->orderTickets = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getOrderTicketDetails(array('order_id' => $order->order_id));
    }
    public function printTicketAction()
    {
        $this->view->order = $order = Engine_Api::_()->core()->getSubject();
        $this->view->event = $event = Engine_Api::_()->getItem('sesevent_event', $order->event_id);
        $this->view->buyer = Engine_Api::_()->getItem('user', $order->owner_id);
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->orderDetails = Engine_Api::_()->getDbTable('orderticketdetails', 'sesevent')->orderTicketDetails(array('order_id' => $order->order_id));
    }

    public function emailTicketAction()
    {
        $this . sendTicketEmail();
        $url = $_SERVER['HTTP_REFERER'];
        $this->_redirect($url);
    }

    private function sendTicketEmail()
    {
        $this->view->order = $order = $eventOrder = Engine_Api::_()->core()->getSubject();
        //$user = $order->getUser();
        $this->view->event = $event = Engine_Api::_()->getItem('sesevent_event', $order->event_id);
        $this->view->buyer = Engine_Api::_()->getItem('user', $order->owner_id);
        $this->view->viewer = $viewer = $user = Engine_Api::_()->user()->getViewer();
        $orderDetails = Engine_Api::_()->getDbTable('orderticketdetails', 'sesevent')->orderTicketDetails(array('order_id' => $order->order_id));

        //Ticket Details
        $ticketsContent = '';
        $pdfCreate = false;
        //send pdf ticket if seseventpdf extention enabled and activated
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventpdfticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventpdfticket.pluginactivated')) {
            try {
                $mailApi = Engine_Api::_()->getApi('mail', 'core');
                $mail = $mailApi->create();
                $adminEmail = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.contact');
                $adminTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.name');
                $mail->setFrom($adminEmail, $adminTitle)
                    ->setSubject("Your ticket to event" . $event->getTitle())
                    ->setBodyHtml('Hello');
                $mail->addTo($order->getOwner()->email);
                foreach ($orderDetails as $keyDet => $item) {
                    $itemId = $item->getIdentity();
                    $pdfname = Engine_Api::_()->getApi('core', 'seseventpdfticket')->createPdfFile($item, $event, $eventOrder, $user);
                    if (!$pdfname) {
                        $pdfCreate = false;
                        break;
                    } else {
                        try {
                            $pdfTicketFile = APPLICATION_PATH . '/public/sesevent_ticketpdf/' . $pdfname;
                            $handle = @fopen($pdfTicketFile, "r");
                            while (($buffer = fgets($handle)) !== false) {
                                $content .= $buffer;
                            }
                            $attachment = $mail->createAttachment($content);
                            $attachment->filename = "eventticket_$itemId" . ".pdf";
                        } catch (Exception $e) {
                            $pdfCreate = false;
                            break;
                            //silence
                        }
                    }
                    $pdfCreate = true;
                }
                if ($pdfCreate) {
                    $mailApi->send($mail);
                }

            } catch (Exception $e) {
                //silence
                $pdfCreate = false;
            }
            $url = $_SERVER['HTTP_REFERER'];
            $this->_redirect($url);
        }
        if (!$pdfCreate) {
            foreach ($orderDetails as $keyDet => $item) {
                $ticketsContent .= '<table style="width:100%;"><tr><td><table border="0" cellpadding="0" cellpadding="0"  style="border-collapse:collapse;width:800px;margin:0 auto;font:normal 13px Arial,Helvetica,sans-serif;border:5px solid #ddd;background-color:#fff;"><tbody><tr valign="top"><td style="border-right:5px solid #ddd;width:590px;"><div style="border-bottom:5px solid #ddd;height:110px;display:block;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Event</div>';
                $ticketsContent .= '<div style="font-size:20px;margin-top:40px;position:inherit;text-align:center;">';
                $ticketsContent .= $event->getTitle();
                $ticketsContent .= '</div>';
                $ticketsContent .= '</div><div style="border-bottom:5px solid #ddd;border-right:5px solid #ddd;float:left;height:120px;width:280px;position:relative;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Date+Time</div><div style="bottom:5px;font-size:13px;position:absolute;right:5px;max-width:90%;">';
                $dateinfoParams['starttime'] = true;
                $dateinfoParams['endtime'] = true;
                $dateinfoParams['timezone'] = true;
                $dateinfoParams['isPrint'] = true;
                $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
                $ticketsContent .= $view->eventStartEndDates($event, $dateinfoParams);
                $ticketsContent .= '</div></div><div style="border-bottom:5px solid #ddd;float:left;height:120px;width:275px;position:relative;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Location</div><div style="bottom:5px;font-size:13px;position:absolute;right:5px;max-width:90%;">';
                if ($event->location && !$event->is_webinar && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)) {
                    $venue_name = '';
                    if ($event->venue_name) {
                        $venue_name = '<br />' . $event->venue_name;
                    }
                    $location = $event->location . $venue_name;
                } else {
                    $location = 'Webinar Event';
                }
                $ticketsContent .= $location;
                $ticketsContent .= '</div></div>';
                $ticketsContent .= '<div style="border-bottom:5px solid #ddd;clear:both;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Order Info</div><div style="margin:30px 5px 20px;text-align:right;">';
                $ticketsContent .= 'Order # ' . $eventOrder->order_id;
                $ticketsContent .= 'Ordered by ' . $user->getTitle();
                $ticketsContent .= 'on ' . Engine_Api::_()->sesevent()->dateFormat($eventOrder->creation_date);
                $ticketsContent .= '</div></div>';
                $ticketsContent .= '<div style="clear:both;float:left;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Attendee Info</div><div style="margin:30px 5px 20px;text-align:right;">';
                $ticketsContent .= $item->first_name . ' ' . $item->last_name . '<br />';
                $ticketsContent .= $item->mobile . '<br />' . $item->email;
                $ticketsContent .= '</div></div></td>';
                $ticketsContent .= '<td style="width:238px;">
	      <div style="height:110px;width:100%;">';
                $eventPhoto = (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Registry::get('StaticBaseUrl') . $event->getPhotoUrl();
                $ticketsContent .= '<img alt="" src="' . $eventPhoto . '" style="height:100%;object-fit:contain;padding:10px;width:100%;"></div><div style="border-bottom:5px solid #ddd;float:left;height:60px;margin-top:60px;position:relative;width:100%;"><div style="color:#999;font-size:14px;left:5px;position:absolute;top:5px;">Payment Method</div><div style="font-size:17px;margin:30px 0 20px;text-align:center;">';
                $ticketsContent .= $eventOrder->gateway_type;
                $ticketsContent .= '</div></div><div style="display:block;float:left;position:relative;text-align:center;width:100%;">';
                if ($item->registration_number) {
                    $fileName = $item->getType() . '_' . $item->getIdentity() . '.png';
                    if (!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/' . $fileName)) {
                        $fileName = Engine_Api::_()->sesevent()->generateQrCode($item->registration_number, $fileName);
                    } else {
                        $fileName = (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Registry::get('StaticBaseUrl') . '/public/sesevent_qrcode/' . $fileName;
                    }
                } else {
                    $qrCode = '';
                }

                $ticketsContent .= '<img alt="' . $item->registration_number . '" src="' . $fileName . '" style="margin-top:20px;max-width:100px;"></div></td>';
                $ticketsContent .= '</tr></tbody></table></td></tr></table>';
            }
        }
        if (!$pdfCreate) {
            //Tickets Details
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($order->getOwner(), 'sesevent_tikets_details', array('host' => $_SERVER['HTTP_HOST'], 'ticket_body' => $ticketsContent, 'event_title' => $event->getTitle()));
        }
    }
}
