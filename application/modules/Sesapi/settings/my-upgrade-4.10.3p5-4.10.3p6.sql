/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my-upgrade-4.10.3p5-4.10.3p6.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

DELETE FROM engine4_core_menuitems WHERE name='sesapi_admin_main_documentation';
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `params`, `menu`,`enabled`,`order`) VALUES
('sesapi_admin_main_androidapp', 'sesapi', 'Android  Mobile App', '{"route":"admin_default","module":"sesandroidapp","controller":"settings","action":"index"}', 'sesapi_admin_main', 1,4);