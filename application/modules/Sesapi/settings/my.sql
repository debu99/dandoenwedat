/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_sesapi', 'sesapi', 'SES - SocialEngine REST APIs Plugin', '', '{"route":"admin_default","module":"sesapi","controller":"settings","action":"index"}', 'core_admin_main_plugins', '', 999),
('sesapi_admin_main_settings', 'sesapi', 'Global Settings', '', '{"route":"admin_default","module":"sesapi","controller":"settings"}', 'sesapi_admin_main', '', 1),
("sesapi_admin_main_iosapp", "sesapi", "iOS Mobile App", "Sesapi_Plugin_Menus::enableIosModule", '{"route":"admin_default","module":"sesiosapp","controller":"settings","target":"_blank"}', "sesapi_admin_main", "", 3);
