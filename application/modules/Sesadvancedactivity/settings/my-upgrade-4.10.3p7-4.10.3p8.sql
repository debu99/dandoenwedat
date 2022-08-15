INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('sesadvancedcomment_admin_main_cmtsettings', 'sesadvancedcomment', 'Comment Settings', '', '{"route":"admin_default","module":"sesadvancedcomment","controller":"settings","action":"index"}', 'sesadvancedactivity_admin_main', '', 700),
('sesfeedbg_admin_main_febgsettings', 'sesfeedbg', 'Feed Backgrounds', '', '{"route":"admin_default","module":"sesfeedbg","controller":"manage","action":"index"}', 'sesadvancedactivity_admin_main', '', 701),
('sesfeedgif_admin_main_fegifsettings', 'sesfeedgif', 'Feed GIF', '', '{"route":"admin_default","module":"sesfeedgif","controller":"settings"}', 'sesadvancedactivity_admin_main', '', 702),
('sesfeelingactivity_admin_main_flngsettings', 'sesfeelingactivity', 'Feelings', '', '{"route":"admin_default","module":"sesfeelingactivity","controller":"settings"}', 'sesadvancedactivity_admin_main', '', 704);

ALTER TABLE engine4_sesadvancedactivity_details DROP INDEX `detail_id`;
ALTER TABLE engine4_sesadvancedactivity_details ADD CONSTRAINT action_id_unique UNIQUE (action_id);
