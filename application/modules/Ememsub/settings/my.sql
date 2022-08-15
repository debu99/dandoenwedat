 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my.sql 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_ememsub', 'ememsub', 'SNS - Membership Subscription Pricing Table & Plan Layout Plugin', '', '{"route":"admin_default","module":"ememsub","controller":"settings"}', 'core_admin_main_plugins', '', 999),
("ememsub_admin_main_setting", "ememsub", "Global Settings", "", '{"route":"admin_default","module":"ememsub","controller":"settings"}', "ememsub_admin_main", "", 1);
