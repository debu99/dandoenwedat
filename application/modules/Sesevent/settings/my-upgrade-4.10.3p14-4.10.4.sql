INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sesevent_admin_main_integrateothermodule", "sesevent", "Integrate Plugins", "", '{"route":"admin_default","module":"sesevent","controller":"integrateothermodule","action":"index"}', "sesevent_admin_main", "", 995);

DROP TABLE IF EXISTS `engine4_sesevent_integrateothermodules`;
CREATE TABLE IF NOT EXISTS `engine4_sesevent_integrateothermodules` (
  `integrateothermodule_id` int(11) unsigned NOT NULL auto_increment,
  `module_name` varchar(64) NOT NULL,
  `content_type` varchar(64) NOT NULL,
  `content_url` varchar(255) NOT NULL,
  `content_id` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`integrateothermodule_id`),
  UNIQUE KEY `content_type` (`content_type`,`content_id`),
  KEY `module_name` (`module_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

ALTER TABLE `engine4_sesevent_events` ADD `resource_type` VARCHAR(128) NULL;
ALTER TABLE `engine4_sesevent_events` ADD `resource_id` INT(11) NOT NULL DEFAULT "0";
