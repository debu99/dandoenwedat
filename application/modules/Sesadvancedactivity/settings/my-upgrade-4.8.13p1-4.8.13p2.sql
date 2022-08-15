INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("sesadvancedactivity_tagged_people", "sesadvancedactivity", '{item:$subject} tagged you in a {var:$postLink}.', 0, "", 1),
("sesadvancedactivity_scheduled_live", "sesadvancedactivity", 'Your scheduled post has been made live.', 0, "", 1);

DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeedgif_admin_main_feedgif'