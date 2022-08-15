/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my-upgrade-4.10.5p1-4.10.5p2.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Courses','courses','1','1','54','0','core_main_courses','2','0','0','courses','2.6');

INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Classroom','eclassroom','1','1','54','0','core_main_eclassroom','2','0','0','eclassroom','2.6');

ALTER TABLE `engine4_sesapi_menus` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `engine4_sesapi_menus` CHANGE `label` `label` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `engine4_sesapi_menus` CHANGE `module` `module` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
ALTER TABLE `engine4_sesapi_menus` CHANGE `url` `url` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
ALTER TABLE `engine4_sesapi_menus` CHANGE `class` `class` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
ALTER TABLE `engine4_sesapi_menus` CHANGE `module_name` `module_name` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
ALTER TABLE `engine4_sesapi_menus` CHANGE `module_name` `module_name` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
ALTER TABLE `engine4_sesapi_menus` CHANGE `version` `version` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;

INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Credit','sescredit','1','1','60','0','core_main_sescredit','2','0','0','sescredit','');
