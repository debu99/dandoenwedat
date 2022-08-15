<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvpmnt_Api_Core extends Core_Api_Abstract
{
    public function getFileUrl($image)
    {

        $table = Engine_Api::_()->getDbTable('files', 'core');
        $result = $table->select()
            ->from($table->info('name'), 'storage_file_id')
            ->where('storage_path =?', $image)
            ->query()
            ->fetchColumn();
        if (!empty($result)) {
            $storage = Engine_Api::_()->getItem('storage_file', $result);
            return $storage->map();
        } else {
            return $image;
        }
    }
    public function getPaymentInfo($module)
    {
        switch ($module) {
            case 'user':
                $params['returnUrl'] = $this->view->url(array('action' => 'profile', 'controller' => 'edit'), 'user_extended');
                break;
            case 'product':
                $params['returnUrl'] = $this->view->url(array('action' => '_finish'), 'sesproduct_payment');
                break;
        }
        return $params;

    }
    public function orderTicketTransactionReturn($order, $transaction, $gatewayInfo)
    {
        // Check that gateways match
        if ($order->gateway_id != $gatewayInfo->gateway_id) {
            throw new Engine_Payment_Plugin_Exception('Gateways do not match');
        }
        // Get related info
        $user = $order->getUser();
        $orderTicket = $order->getSource();
        if ($orderTicket->state == 'pending') {
            return 'pending';
        }
        // Check for cancel state - the user cancelled the transaction
        if ($transaction->status == 'cancel') {
            // Cancel order and subscription?
            $order->onCancel();
            $orderTicket->onOrderFailure();
            Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id' => $orderTicket->order_id, 'state' => 'failed'));
            // Error
            throw new Payment_Model_Exception('Your payment has been cancelled and ' .
                'not been charged. If this is not correct, please try again later.');
        }
        //payment currency
        $currentCurrency = Engine_Api::_()->sesevent()->getCurrentCurrency();
        $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $currencyValue = 1;
        if ($currentCurrency != $defaultCurrency) {
            $currencyValue = $settings->getSetting('sesmultiplecurrency.' . $currentCurrency);
        }

        // Get payment state
        $paymentStatus = null;
        $orderStatus = null;
        switch ($transaction->status) {
            case 'created':
            case 'pending':
                $paymentStatus = 'pending';
                $orderStatus = 'complete';
                break;
            case 'completed':
            case 'processed':
            case 'canceled_reversal':
            case 'succeeded':
                $paymentStatus = 'okay';
                $orderStatus = 'complete';
                break;
            case 'denied':
            case 'failed':
            case 'voided':
            case 'reversed':
            case 'refunded':
            case 'expired':
            default:
                $paymentStatus = 'failed';
                $orderStatus = 'failed'; // This should probably be 'failed'
                break;
        }
        // Update order with profile info and complete status?
        $order->state = $orderStatus;
        $order->gateway_transaction_id = $transaction->id;
        $order->save();
        // Insert transaction
        $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
        $transactionsTable->insert(array(
            'user_id' => $order->user_id,
            'gateway_id' => $gatewayInfo->gateway_id,
            'timestamp' => new Zend_Db_Expr('NOW()'),
            'order_id' => $order->order_id,
            'type' => 'payment',
            'state' => $paymentStatus,
            'gateway_transaction_id' => $transaction->id,
            'amount' => $transaction->amount / 100, // @todo use this or gross (-fee)?
            'currency' => strtoupper($transaction->currency),
        ));
        // Get benefit setting
        $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
            ->getBenefitStatus($user);
        // Check payment status
        if ($paymentStatus == 'okay' ||
            ($paymentStatus == 'pending' && $giveBenefit)) {
            // Update order table info
            $orderTicket->gateway_id = $gatewayInfo->gateway_id;
            $orderTicket->gateway_transaction_id = $transaction->id;
            $orderTicket->currency_symbol = strtoupper($transaction->currency);
            $orderTicket->change_rate = $currencyValue;
            $orderTicket->save();
            $orderAmount = round($orderTicket->total_service_tax + $orderTicket->total_entertainment_tax + $orderTicket->total_amount, 2);
            $commissionValue = round($orderTicket->commission_amount, 2);
            if (isset($commissionValue) && $orderAmount > $commissionValue) {
                $orderAmount = $orderAmount - $commissionValue;
            } else {
                $orderTicket->commission_amount = 0;
            }
            //update EVENT OWNER REMAINING amount
            $tableRemaining = Engine_Api::_()->getDbtable('remainingpayments', 'sesevent');
            $tableName = $tableRemaining->info('name');
            $select = $tableRemaining->select()->from($tableName)->where('event_id =?', $orderTicket->event_id);
            $select = $tableRemaining->fetchAll($select);
            if (count($select)) {
                $tableRemaining->update(array('remaining_payment' => new Zend_Db_Expr("remaining_payment + $orderAmount")), array('event_id =?' => $orderTicket->event_id));
            } else {
                $tableRemaining->insert(array(
                    'remaining_payment' => $orderAmount,
                    'event_id' => $orderTicket->event_id,
                ));
            }
            //update ticket state
            Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id' => $orderTicket->order_id, 'state' => 'complete'));
            // Payment success
            $orderTicket->onOrderComplete();
            // send notification
            if ($orderTicket->state == 'complete') {
                $ticket_id = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getTicketId(array('order_id' => $orderTicket->order_id));
                $tickets = Engine_Api::_()->getItem('sesevent_ticket', $ticket_id);
                $eventOrder = Engine_Api::_()->getItem('sesevent_order', $orderTicket->order_id);
                //Notification Work
                $event = Engine_Api::_()->getItem('sesevent_event', $orderTicket->event_id);
                $owner = $event->getOwner();
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $user, $event, 'sesevent_event_ticketpurchased', array("ticketName" => $tickets->name));
                //Activity Feed Work
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($user, $event, 'sesevent_event_ticketpurchased', '', array("ticketname" => '<b>' . $tickets->name . '</b>'));
                if ($action) {
                    $activityApi->attachActivity($action, $event);
                }
                $totalAmount = @round($orderTicket->total_amount + $orderTicket->total_service_tax + $orderTicket->total_entertainment_tax, 2);
                if ($orderTicket->total_tickets) {
                    $total_price_t = @round($orderTicket->total_tickets * $tickets->price, 2);
                } else {
                    $total_price_t = @round($tickets->price, 2);
                }
                if ($eventOrder->total_service_tax > 0) {
                    $service_tax_t = Engine_Api::_()->sesevent()->getCurrencyPrice(@round($eventOrder->total_service_tax, 2), $eventOrder->currency_symbol, $eventOrder->change_rate);
                } else {
                    $service_tax_t = "-";
                }
                if ($eventOrder->total_entertainment_tax) {
                    $entertainment_tax_t = Engine_Api::_()->sesevent()->getCurrencyPrice(@round($eventOrder->total_entertainment_tax, 2), $eventOrder->currency_symbol, $eventOrder->change_rate);
                } else {
                    $entertainment_tax_t = "-";
                }
                if ($totalAmount <= 0) {
                    $grandTottal = 'FREE';
                } else {
                    $grandTottal = Engine_Api::_()->sesevent()->getCurrencyPrice($totalAmount, $eventOrder->currency_symbol, $eventOrder->change_rate);
                }
                $orderTicketsDetails = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getOrderTicketDetails(array('order_id' => $orderTicket->order_id));
                if ($eventOrder->ragistration_number) {
                    $fileName = $eventOrder->getType() . '_' . $eventOrder->getIdentity() . '.png';
                    if (!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/' . $fileName)) {
                        $qrCode = Engine_Api::_()->sesevent()->generateQrCode($eventOrder->ragistration_number, $fileName);
                    } else {
                        $qrCode = (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Registry::get('StaticBaseUrl') . '/public/sesevent_qrcode/' . $fileName;
                    }
                } else {
                    $qrCode = '';
                }

                $ticketDetails = '';
                foreach ($orderTicketsDetails as $orderTiDetails) {
                    $ticketDetails .= '<tr><td>' . $orderTiDetails['title'] . '</td>';
                    $ticketDetails .= '<td align="right">';
                    if ($orderTiDetails->price <= 0) {
                        $ticketDetails .= 'FREE';
                    } else {
                        $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice($orderTiDetails->price, $eventOrder->currency_symbol, $eventOrder->change_rate);
                    }
                    $ticketDetails .= '<br />';
                    if ($orderTiDetails->service_tax > 0) {
                        $ticketDetails .= 'Service Tax:' . @round($orderTiDetails->service_tax, 2) . '%';
                        $ticketDetails .= '<br />';
                    }
                    if ($orderTiDetails->entertainment_tax > 0) {
                        $ticketDetails .= 'Entertainment Tax:' . @round($orderTiDetails->entertainment_tax, 2) . '%';
                    }
                    $ticketDetails .= '</td>';
                    $ticketDetails .= '<td align="center">' . $orderTiDetails->quantity . '</td>';
                    $price = $orderTiDetails->price;
                    if ($price <= 0) {
                        $ticketDetails .= '<td align="center">';
                        $ticketDetails .= 'FREE';
                    } else {
                        $ticketDetails .= '<td align="right">';
                        $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(round($price * $orderTiDetails->quantity, 2), $eventOrder->currency_symbol, $eventOrder->change_rate);
                        $ticketDetails .= '<br />';
                    }
                    if ($orderTiDetails->service_tax > 0) {
                        $serviceTax = round(($price * ($orderTiDetails->service_tax / 100)) * $orderTiDetails->quantity, 2);
                        $ticketDetails .= 'Service Tax:';
                        $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($serviceTax, 2), $eventOrder->currency_symbol, $eventOrder->change_rate);
                        $ticketDetails .= '<br />';
                    }
                    if ($orderTiDetails->entertainment_tax > 0) {
                        $entertainmentTax = round(($price * ($orderTiDetails->entertainment_tax / 100)) * $orderTiDetails->quantity, 2);
                        $ticketDetails .= 'Entertainment Tax:';
                        $ticketDetails .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($entertainmentTax, 2), $eventOrder->currency_symbol, $eventOrder->change_rate);
                    }
                    $ticketDetails .= '</td>';
                    $ticketDetails .= '</tr>';
                }
                $totalAmount = @round($orderTicket->total_amount + $orderTicket->total_service_tax + $orderTicket->total_entertainment_tax, 2);
                $totalAmounts = '[';
                $totalAmounts .= 'Total:';
                if ($totalAmount <= 0) {
                    $totalAmounts .= 'FREE';
                } else {
                    $totalAmounts .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($totalAmount, 2), $orderTicket->currency_symbol, $orderTicket->change_rate);
                }
                $totalAmounts .= ']';
                $sub_total = '';
                if ($orderTicket->total_amount <= 0) {
                    $sub_total .= 'FREE';
                } else {
                    $sub_total .= Engine_Api::_()->sesevent()->getCurrencyPrice(@round($orderTicket->total_amount, 2), $orderTicket->currency_symbol, $orderTicket->change_rate);
                }

                $body .= '<table style="background-color:#f9f9f9;border:#ececec solid 1px;width:100%;"><tr><td><div style="margin:0 auto;width:600px;font:normal 13px Arial,Helvetica,sans-serif;padding:20px;"><div style="margin-bottom:10px;overflow:hidden;"><div style="float:left;"><b>Order Id: #' . $orderTicket->order_id . '</b></div><div style="float:right;"><b>' . $totalAmounts . '</b></div></div><table style="background-color:#fff;border:#ececec solid 1px;margin-bottom:20px;" cellpadding="0" cellspacing="0" width="100%"><tr valign="top" style="width:50%;"><td><div style="border-bottom:#ececec solid 1px;padding:20px;"><b style="display:block;margin-bottom:5px;">Ordered For</b><span style="display:block;margin-bottom:5px;"><a href="' . (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref() . '" style="color:#39F;text-decoration:none;">' . $event->getTitle() . '</a></span><span style="display:block;margin-bottom:5px;">' . $event->starttime . ' - ' . $event->endtime . '</span></div><div style="padding:20px;border-bottom:#ececec solid 1px;"> <b style="display:block;margin-bottom:5px;">Ordered By</b><span style="display:block;margin-bottom:5px;"><a href="' . (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $orderTicket->getOwner()->getHref() . '" style="color:#39F;text-decoration:none;">' . $orderTicket->fname . '</a></span><span style="display:block;margin-bottom:5px;">' . $orderTicket->email . '</span></div><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Payment Information</b><span style="display:block;margin-bottom:5px;">Payment Method: ' . $orderTicket->gateway_type . '</span></div></td><td style="border-left:#ececec solid 1px;width:50%;"><div style="padding:20px;"><b style="display:block;margin-bottom:5px;">Order Information</b><span style="display:block;margin-bottom:5px;">Ordered Date: ' . $orderTicket->creation_date . '</span>';

                if ($orderTicket->total_service_tax) {
                    $body .= '<span style="display:block;margin-bottom:5px;">Service Tax: $' . round($orderTicket->total_service_tax, 2) . '</span>';
                }

                if ($orderTicket->total_entertainment_tax) {
                    $body .= '<span style="display:block;margin-bottom:5px;">Entertainment Tax: $' . round($orderTicket->total_entertainment_tax, 2) . '</span>';
                }

                $body .= '</div>';

                if ($qrCode) {
                    $body .= '<div style="padding:20px;text-align:center;"><img style="height:150px;width:150px;" src="' . $qrCode . '"></div>';
                }

                $body .= '</td></tr></table><div style="margin-bottom:10px;"><b class="bold">Order Details</b></div><table bordercolor="#ececec"  border="1" style="background-color:#fff;margin-bottom:20px;border-collapse: collapse;" cellpadding="10" cellspacing="0" width="100%"><tbody><tr><th>Ticket Name</th><th>Price</th><th>Quantity</th><th>Sub Total</th></tr>' . $ticketDetails . '</tbody></table><div style="background-color:#fff;border:1px solid #ececec;padding:10px;"><div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Sub Total</span><span style="float:right;">' . $sub_total . '</span> </div>';

                if ($service_tax_t) {
                    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Service Taxes</span><span style="float:right;">' . $service_tax_t . '</span></div>';
                }

                if ($entertainment_tax_t) {
                    $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;">Entertainment Taxes</span><span style="float:right;">' . $entertainment_tax_t . '</span></div>';
                }

                $body .= '<div style="margin-bottom:5px;overflow:hidden;"><span style="float:left;"><b>Grand Total</b></span><span style="float:right;"><b>' . $grandTottal . '</b></span></div></div></div> </td></tr></table>';

                //Ticket Details
                $orderDetails = Engine_Api::_()->getDbTable('orderticketdetails', 'sesevent')->orderTicketDetails(array('order_id' => $orderTicket->order_id));
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
                        $mail->addTo($orderTicket->getOwner()->email);
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
                try {
                    //insert in membership table
                    $membershipTable = Engine_Api::_()->getDbtable('membership', 'sesevent');
                    $membershipTable->insert(array(
                        'user_id' => $orderTicket->owner_id,
                        'resource_id' => $orderTicket->event_id,
                        'active' => 1,
                        'resource_approved' => 1,
                        'user_approved' => '1',
                        'rsvp' => 2,
                    ));
                } catch (Exception $e) {
                    //silence
                }
                if (!$pdfCreate) {
                    //Tickets Details
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($orderTicket->getOwner(), 'sesevent_tikets_details', array('host' => $_SERVER['HTTP_HOST'], 'ticket_body' => $ticketsContent, 'event_title' => $event->getTitle()));
                }
                //Ticket invoice mail to buyer
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($orderTicket->getOwner(), 'sesevent_tiketinvoice_buyer', array('invoice_body' => $body, 'host' => $_SERVER['HTTP_HOST']));

                //Ticket Purchased Mail to Event Owner
                $event_owner = Engine_Api::_()->getItem('user', $event->user_id);
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($event_owner, 'sesevent_ticketpurchased_eventowner', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'buyer_name' => $user->getTitle(), 'host' => $_SERVER['HTTP_HOST']));
            }
            $orderTicket->creation_date = date('Y-m-d H:i:s');
            $orderTicket->save();
            return 'active';
        } else if ($paymentStatus == 'pending') {
            // Update order  info
            $orderTicket->gateway_id = $gatewayInfo->gateway_id;
            $orderTicket->gateway_profile_id = $transaction->id;
            $orderTicket->save();
            // Order pending
            $orderTicket->onOrderPending();
            //update ticket state
            Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id' => $orderTicket->order_id, 'state' => 'pending'));

            //Send Mail
            $event = Engine_Api::_()->getItem('sesevent_event', $orderTicket->event_id);

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sesevent_payment_ticket_pending', array('event_title' => $event->title, 'evnet_description' => $event->description, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));

            return 'pending';
        } else if ($paymentStatus == 'failed') {
            // Cancel order and subscription?
            $order->onFailure();
            $orderTicket->onOrderFailure();
            //update ticket state
            Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->updateTicketOrderState(array('order_id' => $orderTicket->order_id, 'state' => 'failed'));
            // Payment failed
            throw new Payment_Model_Exception('Your payment could not be ' .
                'completed. Please ensure there are sufficient available funds ' .
                'in your account.');
        } else {
            // This is a sanity error and cannot produce information a user could use
            // to correct the problem.
            throw new Payment_Model_Exception('There was an error processing your ' .
                'transaction. Please try again later.');
        }
    }
}
