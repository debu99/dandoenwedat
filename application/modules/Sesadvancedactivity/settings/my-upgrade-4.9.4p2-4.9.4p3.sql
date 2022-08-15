ALTER TABLE `engine4_sesadvancedactivity_buysells` ADD `buy` VARCHAR(1000) NULL;
CREATE TABLE `engine4_sesadvancedactivity_pinposts` (
  `pinpost_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `resource_type` varchar(255) NOT NULL,
   PRIMARY KEY (`pinpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;