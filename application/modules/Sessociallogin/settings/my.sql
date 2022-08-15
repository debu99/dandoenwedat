INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_sessociallogin', 'sessociallogin', 'SES - Social Media Login...', '', '{"route":"admin_default","module":"sessociallogin","controller":"settings"}', 'core_admin_main_plugins', '', 999),
('sessociallogin_admin_main_settings', 'sessociallogin', 'Global Settings', '', '{"route":"admin_default","module":"sessociallogin","controller":"settings"}', 'sessociallogin_admin_main', '', 1);
