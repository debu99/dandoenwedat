/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventticket
 * @package    Seseventticket
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: my.sql 2016-03-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("sesevent_admin_main_seseventtickets", "seseventticket", "Tickets", "", '{"route":"admin_default","module":"sesevent","controller":"settings", "action":"extension"}', "sesevent_admin_main", "", 995),
("seseventticket_admin_main_settings", "seseventticket", "Ticket Settings", "", '{"route":"admin_default","module":"sesevent","controller":"settings", "action":"extension"}', "seseventticket_admin_main", "", 1);

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('sesevent_payment_ticket_pending', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[event_title],[event_description],[object_link]');
