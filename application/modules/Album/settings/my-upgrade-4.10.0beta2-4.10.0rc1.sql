-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('album_main_browse_photos', 'album', 'Browse Photos', 'Album_Plugin_Menus::canViewAlbums', '{"route":"album_general", "controller": "index", "action":"browse-photos"}', 'album_main', '', 1);

UPDATE `engine4_core_menuitems`
SET `order` = 2
WHERE `name` = 'album_main_browse';

UPDATE `engine4_core_menuitems`
SET `order` = 3
WHERE `name` = 'album_main_manage';

UPDATE `engine4_core_menuitems`
SET `order` = 4
WHERE `name` = 'album_main_upload';
