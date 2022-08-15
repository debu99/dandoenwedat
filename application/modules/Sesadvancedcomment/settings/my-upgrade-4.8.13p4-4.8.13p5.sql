INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("sesadvancedcomment_admin_main_managereactions", "sesadvancedcomment", "Manage Reactions", "", '{"route":"admin_default","module":"sesadvancedcomment","controller":"manage-reactions","action":"index"}', "sesadvancedcomment_admin_main", "", 5);

DROP TABLE IF EXISTS `engine4_sesadvancedcomment_reactions`;
CREATE TABLE IF NOT EXISTS `engine4_sesadvancedcomment_reactions` (
  `reaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR( 255 ) NOT NULL,
  `file_id` int(11) NOT NULL DEFAULT "0",
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  PRIMARY KEY (`reaction_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

INSERT IGNORE INTO `engine4_sesadvancedcomment_reactions` (`reaction_id`, `title`, `enabled`, `file_id`) VALUES
(1, "Like", 1, 0),
(2, "Love", 1, 0),
(3, "Haha", 1, 0),
(4, "Wow", 1, 0),
(5, "Angry", 1, 0),
(6, "Sad", 1, 0);