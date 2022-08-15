DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'core_admin_plugins_sesbasic';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesbasic_admin_overview';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('core_admin_plugins_sesbasic', 'sesbasic', 'SES - Basic Required', '', '{"route":"admin_default","module":"sesbasic","controller":"settings","action":"global"}', 'core_admin_main_plugins', '', 999);

ALTER TABLE `engine4_sesbasic_menusicons` ADD `icon_type` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `engine4_sesbasic_menusicons` ADD `font_icon` VARCHAR(255) NOT NULL;
ALTER TABLE `engine4_sesbasic_menusicons` ADD `menusicon_id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`menusicon_id`);
