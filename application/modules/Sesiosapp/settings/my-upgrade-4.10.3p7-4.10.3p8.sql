 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my-upgrade-4.10.3p7-4.10.3p8.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Groups','Sesgroup','1','1','21','0','core_main_sesgroup','1','0','0','sesgroup','1.5');
INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Pages','Sespage','1','1','22','0','core_main_sespage','1','0','0','sespage','1.5');
INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Events','Sesevent','1','1','23','0','core_main_sesevent','1','0','0','sesevent','1.5');
INSERT IGNORE INTO `engine4_core_menuitems`(`name`, `module`, `label`, `params`, `menu`,  `enabled`,`order`) VALUES ('sesiosapp_admin_main_graphic','sesiosapp','Graphic Assets','{"route":"admin_default","module":"sesiosapp","controller":"graphic"}','sesiosapp_admin_main','1','5');
INSERT IGNORE INTO `engine4_core_menuitems`(`name`, `module`, `label`, `params`, `menu`,  `enabled`,`order`) VALUES ('sesiosapp_admin_main_background','sesiosapp','Background Images','{"route":"admin_default","module":"sesiosapp","controller":"settings","action":"background"}','sesiosapp_admin_main','1','5');
CREATE TABLE IF NOT EXISTS `engine4_sesiosapp_graphics` (
  `graphic_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255)  NOT NULL DEFAULT "",
  `description` text ,
  `title_color` varchar(255)  NOT NULL DEFAULT "FFFFFF",
  `description_color` varchar(255)  NOT NULL DEFAULT "FFFFFF",
  `background_color` VARCHAR(255) NOT NULL,
  `status` tinyint(1) DEFAULT "1",
  `file_id` int(11) DEFAULT "0",
  `order` int(11) DEFAULT "0",
  `creation_date` datetime DEFAULT NULL,
  PRIMARY KEY (`graphic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
