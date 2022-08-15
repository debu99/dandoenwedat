 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_sesiosapp', 'sesiosapp', 'SES - iOS Native...', '', '{"route":"admin_default","module":"sesiosapp","controller":"settings","action":"index"}', 'core_admin_main_plugins', '', 999),
('sesiosapp_admin_main_settings', 'sesiosapp', 'Global Settings', '', '{"route":"admin_default","module":"sesiosapp","controller":"settings"}', 'sesiosapp_admin_main', '', 1);
