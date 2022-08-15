CREATE TABLE IF NOT EXISTS `engine4_sesadvancedactivity_tagitems` (
  `tagitem_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `resource_id` INT(11) NOT NULL,
  `resource_type` VARCHAR(255) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `action_id` INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES ('sesadvancedactivity_tagged_item', 'sesadvancedactivity', '{item:$subject} tagged your {var:$itemurl} in a {var:$postLink}.', '0', '', '1');