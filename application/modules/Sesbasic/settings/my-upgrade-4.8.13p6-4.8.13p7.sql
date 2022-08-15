-- INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
-- ('sesbasic_admin_main_instagram', 'sesbasic', 'Instagram', '', '{"route":"admin_default","module":"sesbasic","controller":"settings","action":"instagram"}', 'sesbasic_admin_main', '', 11);

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