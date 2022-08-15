DROP TABLE IF EXISTS `engine4_sesbasic_menuitems`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_menuitems` (
  `menuitem_id` int(11)  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `module` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `label` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `plugin` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `params` text COLLATE utf8_unicode_ci NOT NULL,
  `menu` varchar(256) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `submenu` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `custom` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint(6) NOT NULL DEFAULT '999',
  `file_id` int(11) NOT NULL,
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `id` (`id`, `name`),
  KEY `LOOKUP` (`name`,`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `engine4_sesbasic_menuitems` (`id`, `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) 
SELECT `id`,`name`, `module`, `label`, `plugin`, `params`, "sesbasic_mini", `submenu`, `enabled`, `custom`, `order` FROM engine4_core_menuitems WHERE menu = "core_mini";

INSERT IGNORE INTO `engine4_sesbasic_menuitems` ( `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES
("core_mini_notification", "user", "Notifications", "", '{"route":"default","module":"sesbasic","controller":"notifications","action":"pulldown"}', "sesbasic_mini", "",1,0, 999),
("core_mini_friends", "user", "Friend Requests", "", '{"route":"default","module":"sesbasic","controller":"index","action":"friend-request"}', "sesbasic_mini", "",1,0,  999);

INSERT IGNORE INTO `engine4_core_menus` ( `name`, `type`, `title`, `order`) VALUES ( 'sesbasic_mini', 'standard', 'SES - Mini Navigation Menu', 2);

DROP TABLE IF EXISTS `engine4_sesbasic_menusicons`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_menusicons` (
  `menu_id` int(11) NOT NULL,
  `icon_id` int(11) NOT NULL,
  `type` VARCHAR(45) NOT NULL DEFAULT "mainicon",
   UNIQUE KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engine4_sesbasic_notificationreads`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_notificationreads` (
  `notificationread_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `item_id` int(11) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `user_id` int(11) NOT NULL,
   UNIQUE KEY `menu_type` (`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

UPDATE engine4_sesbasic_menuitems SET `order` = 5  WHERE name = "core_mini_profile";
UPDATE engine4_sesbasic_menuitems SET `order` = 8  WHERE name = "core_mini_notification";
UPDATE engine4_sesbasic_menuitems SET `order` = 7  WHERE name = "core_mini_messages";
UPDATE engine4_sesbasic_menuitems SET `order` = 6  WHERE name = "core_mini_friends";
UPDATE engine4_sesbasic_menuitems SET `order` = 4  WHERE name = "core_mini_settings";
UPDATE engine4_sesbasic_menuitems SET `order` = 3  WHERE name = "core_mini_admin";
UPDATE engine4_sesbasic_menuitems SET `order` = 2  WHERE name = "core_mini_auth";
UPDATE engine4_sesbasic_menuitems SET `order` = 1  WHERE name = "core_mini_signup";

UPDATE `engine4_sesbasic_menuitems` SET `enabled` = '0' WHERE `engine4_sesbasic_menuitems`.`name` = 'core_mini_update';
UPDATE `engine4_sesbasic_menuitems` SET `enabled` = '0' WHERE `engine4_sesbasic_menuitems`.`name` = 'core_mini_profile';