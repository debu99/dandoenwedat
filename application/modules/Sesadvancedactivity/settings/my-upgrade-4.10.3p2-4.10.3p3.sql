CREATE TABLE IF NOT EXISTS `engine4_sesadvancedactivity_details` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `commentable` TINYINT(1) NOT NULL DEFAULT "1",
  `schedule_time` varchar(256) NOT NULL,
  `sesapproved` TINYINT(1) NOT NULL DEFAULT "1",
  `reaction_id` INT(11) NOT NULL DEFAULT "0",
  `sesresource_id` INT( 11 ) NOT NULL DEFAULT "0",
  `sesresource_type` VARCHAR( 45 ) NULL,
  PRIMARY KEY (`detail_id`),
  UNIQUE( `detail_id`, `action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

INSERT IGNORE INTO engine4_sesadvancedactivity_details (`action_id`, `commentable`, `schedule_time`, `sesapproved`, `reaction_id`) SELECT `action_id`, `commentable`, `schedule_time`, `sesapproved`, `reaction_id` FROM engine4_activity_actions;

ALTER TABLE `engine4_activity_actions` DROP `commentable`, DROP `reaction_id`, DROP `schedule_time`, DROP `sesapproved`;
ALTER TABLE `engine4_activity_actions` DROP `sesresource_id`, DROP 
`sesresource_type`;
