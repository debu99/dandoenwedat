INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_plugins_sesbasic', 'sesbasic', 'SES - Basic Required', '', '{"route":"admin_default","module":"sesbasic","controller":"settings","action":"global"}', 'core_admin_main_plugins', '', 999),
('sesbasic_admin_global', 'sesbasic', 'Global Settings', '', '{"route":"admin_default","module":"sesbasic","controller":"settings","action":"global"}', 'sesbasic_admin_main', '', 2),
('sesbasic_admin_colorpicker', 'sesbasic', 'Color Picker', '', '{"route":"admin_default","module":"sesbasic","controller":"settings","action":"color-chooser"}', 'sesbasic_admin_main', '', 3),
('sesbasic_admin_manage', 'sesbasic', 'Manage Video Lightbox', '', '{"route":"admin_default","module":"sesbasic","controller":"lightbox","action":"video"}', 'sesbasic_admin_main', '', 4),
('sesbasic_admin_memberlevel', 'sesbasic', 'Member Level Setting', '', '{"route":"admin_default","module":"sesbasic","controller":"lightbox","action":"index"}', 'sesbasic_admin_manage', '', 2),
('sesbasic_admin_videolightbox', 'sesbasic', 'Video Lightbox Settings', '', '{"route":"admin_default","module":"sesbasic","controller":"lightbox","action":"video"}', 'sesbasic_admin_manage', '', 1),
('sesbasic_admin_main_managesocialmedia', 'sesbasic', 'Manage Social Media Keys', '', '{"route":"admin_default","module":"sesbasic","controller":"settings","action":"social-media-key"}', 'sesbasic_admin_main', '', 11);

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_sesbasic_locations`
--
DROP TABLE IF EXISTS `engine4_sesbasic_locations`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_locations` (
`location_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`resource_id` INT( 11 ) NOT NULL ,
`lat` DECIMAL( 10, 8 ) NULL ,
`lng` DECIMAL( 11, 8 ) NULL ,
`resource_type` VARCHAR( 65 ) NOT NULL DEFAULT 'sesalbum',
`venue` VARCHAR(255) NULL,
`address` TEXT NULL,
`address2` TEXT NULL,
`city` VARCHAR(255) NULL,
`state` VARCHAR(255) NULL,
`zip` VARCHAR(255) NULL,
`country` VARCHAR(255) NULL,
`modified_date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY `uniqueKey` (`resource_id`,`resource_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `engine4_sesbasic_integrateothermodules`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_integrateothermodules` (
  `integrateothermodule_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL,
  `content_type` varchar(64) NOT NULL,
  `content_type_photo` varchar(64) NOT NULL,
  `content_id` varchar(64) NOT NULL,
  `content_id_photo` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`integrateothermodule_id`),
  UNIQUE KEY `content_type` (`type`,`content_type`,`content_id`),
  KEY `module_name` (`module_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_sesbasic_saves`
--

DROP TABLE IF EXISTS `engine4_sesbasic_saves`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_saves` (
`save_id` int(11) unsigned NOT NULL auto_increment,
`resource_type` varchar(64) NOT NULL,
`resource_id` INT( 11 ) NOT NULL ,
`poster_id` INT( 11 ) NOT NULL ,
`poster_type` varchar(64) NOT NULL,
`creation_date` datetime NOT NULL,
 PRIMARY KEY (`save_id`),
 KEY `resource_type` (`resource_type`, `resource_id`),
 KEY `poster_type` (`poster_type`, `poster_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


DROP TABLE IF EXISTS `engine4_sesbasic_instagram`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_instagram` (
  `instagram_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` INT(11) NOT NULL,
  `instagram_uid` varchar(45) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `expires` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
   PRIMARY KEY (`instagram_id`),
   UNIQUE KEY `instagram_uid` (`instagram_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


-- DROP TABLE IF EXISTS `engine4_sesbasic_plugins`;
-- CREATE TABLE IF NOT EXISTS `engine4_sesbasic_plugins` (
--   `plugin_id` int(11) NOT NULL AUTO_INCREMENT,
--   `module_name` varchar(64) NOT NULL,
--   `title` varchar(64) NOT NULL,
--   `description` text NULL,
--   `current_version` varchar(32) NOT NULL,
--   `site_version` varchar(32) NOT NULL,
--   `category` varchar(64) NOT NULL,
--   `pluginpage_link` VARCHAR(255) NOT NULL,
--   PRIMARY KEY (`plugin_id`),
--   KEY `module_name` (`module_name`)
-- ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


DROP TABLE IF EXISTS `engine4_sesbasic_usergateways`;
CREATE TABLE IF NOT EXISTS `engine4_sesbasic_usergateways` (
  `usergateway_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(128)  NOT NULL,
  `description` text ,
  `enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `plugin` varchar(128)  NOT NULL,
  `sponsorship` varchar(128)  NOT NULL,
  `config` mediumblob,
  `test_mode` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `gateway_type` varchar(64)  NOT NULL,
  PRIMARY KEY (`usergateway_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

/*
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('sesbasic', 'sesbasic', 'Payment Gateways', '', '{"route":"sesbasic_extended", "module":"sesbasic", "controller":"index", "action":"account-details"}', 'user_settings', '', 20);*/


INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('sesbasic_admin_managephotolightbox', 'sesbasic', 'Manage Photo Lightbox', '', '{"route":"admin_default","module":"sesbasic","controller":"photolightbox","action":"photo"}', 'sesbasic_admin_main', '', 4),
('sesbasic_admin_photolightboxphotolightbox', 'sesbasic', 'Photo Lightbox Settings', '', '{"route":"admin_default","module":"sesbasic","controller":"photolightbox","action":"photo"}', 'sesbasic_admin_managephotolightbox', '', 1),
('sesbasic_admin_memberlevelphotolightbox', 'sesbasic', 'Member Level Setting', '', '{"route":"admin_default","module":"sesbasic","controller":"photolightbox","action":"index"}', 'sesbasic_admin_managephotolightbox', '', 2);


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

CREATE TABLE IF NOT EXISTS `engine4_sesbasic_menusicons` (
	`menusicon_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `menu_id` int(11) NOT NULL,
  `icon_id` int(11) NOT NULL,
  `type` VARCHAR(45) NOT NULL DEFAULT "mainicon",
	`icon_type` TINYINT(1) NOT NULL DEFAULT '0',
	`font_icon` VARCHAR(255) NOT NULL,
   UNIQUE KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

CREATE TABLE IF NOT EXISTS `engine4_sesbasic_likes` (
  `like_id` int(11) unsigned NOT NULL auto_increment,
  `resource_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY  (`like_id`),
  KEY `resource_type` (`resource_type`, `resource_id`),
  KEY `poster_type` (`poster_type`, `poster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `engine4_sesbasic_userdetails` (
  `userdetail_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `country_code` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_number` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY  (`userdetail_id`),
  KEY `user_id` (`user_id`),
  KEY `phone_number` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
