INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('SNS: Advanced Activity - Cleanup Feed Privacy', 'sesadvancedactivity', 'Sesadvancedactivity_Plugin_Task_Cleanup', 86400);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sesadvancedactivity_admin_main_feedsharing", "sesadvancedactivity", "Feed Sharing Settings", "", '{"route":"admin_default","module":"sesadvancedactivity","controller":"settings","action":"feedsharing"}', "sesadvancedactivity_admin_main", "", 888);