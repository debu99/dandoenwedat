DROP TABLE IF EXISTS `engine4_sesbasic_likes`;
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