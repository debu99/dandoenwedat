 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_sesandroidapp', 'sesandroidapp', 'SES - Android Native...', '', '{"route":"admin_default","module":"sesandroidapp","controller":"settings","action":"index"}', 'core_admin_main_plugins', '', 999),
('sesandroidapp_admin_main_settings', 'sesandroidapp', 'Global Settings', '', '{"route":"admin_default","module":"sesandroidapp","controller":"settings"}', 'sesandroidapp_admin_main', '', 1);
