<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventticket
 * @package    Seseventticket
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: defaultsettings.php 2016-03-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
$db = Zend_Db_Table_Abstract::getDefaultAdapter();

//My Ticket Page
// Check if it's already been placed
$select = new Zend_Db_Select($db);
$hasWidget = $select
        ->from('engine4_core_pages', new Zend_Db_Expr('TRUE'))
        ->where('name = ?', 'sesevent_ticket_my-tickets')
        ->limit(1)
        ->query()
        ->fetchColumn();

// Add it
if (empty($hasWidget)) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_ticket_my-tickets',
      'displayname' => 'SES - Advanced Events - My Tickets',
      'title' => 'SES - Event Browse',
      'description' => 'This page is my tickets.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId();
  // Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $page_id,
      'order' => 1,
  ));
  $top_id = $db->lastInsertId();
  // Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
      'order' => 2,
  ));
  $main_id = $db->lastInsertId();
  // Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $top_id,
  ));
  $top_middle_id = $db->lastInsertId();
  // Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 1,
  ));
  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.my-tickets',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 2,
  ));
}

//Ticket Booking
$select = new Zend_Db_Select($db);
$hasWidget = $select
        ->from('engine4_core_pages', new Zend_Db_Expr('TRUE'))
        ->where('name = ?', 'sesevent_ticket_buy')
        ->limit(1)
        ->query()
        ->fetchColumn();
// Add it
if (empty($hasWidget)) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_ticket_buy',
      'displayname' => 'SES - Advanced Events - Ticket Booking',
      'title' => 'SES - Event Browse',
      'description' => 'This page is ticket booking.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId();
  // Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $page_id,
      'order' => 1,
  ));
  $top_id = $db->lastInsertId();
  // Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
      'order' => 2,
  ));
  $main_id = $db->lastInsertId();
  // Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $top_id,
  ));
  $top_middle_id = $db->lastInsertId();
  // Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 1,
  ));
  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.buy-ticket',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 1,
      'params' => '{"title":"","type":"form","nomobile":"0","name":"sesevent.buy-ticket"}',
  ));
}

$db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("sesevent_event_createticket", "sesevent", \'{item:$subject} has created a ticket {var:$ticketName} in event {item:$object}.\', 1, 5, 1, 1, 1, 1),
("sesevent_event_ticketpurchased", "sesevent", \'{item:$subject} has ordered ticket {var:$ticketname} from event {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_event_editticketdate", "sesevent", \'{item:$subject} has edited date {var:$editDateFormat} of ticket {var:$ticketName} in event {item:$object}.\', 1, 5, 1, 1, 1, 1);');

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("sesevent_event_ticketpurchased", "sesevent", \'{item:$subject} has ordered ticket {var:$ticketName} from event {item:$object}.\', 0, ""),
("sesevent_event_paymentrequest", "sesevent", \'{item:$subject} request payment {var:$requestAmount} for event {item:$object}.\', 0, ""),
("sesevent_event_adminpaymentcancel", "sesevent", \'{item:$subject} cancel your payment request for event {item:$object}.\', 0, ""),
("sesevent_event_adminpaymentapprove", "sesevent", \'{item:$subject} apporved your payment request for event {item:$object}.\', 0, "");');

$db->query('UPDATE `engine4_core_menuitems` SET `plugin` = "Sesevent_Plugin_Menus::canViewMultipleCurrency" WHERE `engine4_core_menuitems`.`name` = "sesevent_admin_main_currency";');

$db->query('UPDATE `engine4_core_menuitems` SET `params` = \'{"route":"admin_default","module":"sesmultiplecurrency","controller":"settings","action":"currency"}\' WHERE `engine4_core_menuitems`.`name` = "sesevent_admin_main_currency";');

$db->query("ALTER TABLE `engine4_sesevent_orders` ADD `credit_point` INT(11) NULL DEFAULT '0', ADD `credit_value` FLOAT NULL DEFAULT '0';");
$db->query("ALTER TABLE `engine4_sesevent_orders` ADD `ordercoupon_id` INT NULL DEFAULT '0';");
