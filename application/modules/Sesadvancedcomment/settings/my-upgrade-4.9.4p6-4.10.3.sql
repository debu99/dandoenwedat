ALTER TABLE `engine4_activity_actions` ADD `vote_up_count` INT(11) NOT NULL DEFAULT '0', ADD `vote_down_count` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `engine4_core_comments` ADD `vote_up_count` INT(11) NOT NULL DEFAULT '0', ADD `vote_down_count` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `engine4_activity_comments` ADD `vote_up_count` INT(11) NOT NULL DEFAULT '0', ADD `vote_down_count` INT(11) NOT NULL DEFAULT '0';


DROP TABLE IF EXISTS `engine4_sesadvancedcomment_voteupdowns`;
CREATE TABLE `engine4_sesadvancedcomment_voteupdowns` (
  `voteupdown_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(10) NOT NULL DEFAULT 'upvote',
  `resource_type` VARCHAR(100) NOT NULL,
  `resource_id` INT(11) NOT NULL,
  `user_type` VARCHAR(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;