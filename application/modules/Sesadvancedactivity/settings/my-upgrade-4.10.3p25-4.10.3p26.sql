DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_filtersettings';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_filtermainsettings';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_filtercontentsettings';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedcomment_admin_main_emotionssettings';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedcomment_admin_main_emotionssettingsmain';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedcomment_admin_main_emotiongallery';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("sesadvancedactivity_admin_filter", "sesadvancedactivity", "Feeds Filtering", "", '{"route":"admin_default","module":"sesadvancedactivity","controller":"settings","action":"filter"}', "sesadvancedactivity_admin_main", "", 5),
("sesadvancedactivity_admin_main_filtermainsettings", "sesadvancedactivity", "Feeds Filtering Settings", "", '{"route":"admin_default","module":"sesadvancedactivity","controller":"settings","action":"filter"}', "sesadvancedactivity_admin_filter", "", 1),
("sesadvancedactivity_admin_main_filtercontentsettings", "sesadvancedactivity", "Manage Filters", "", '{"route":"admin_default","module":"sesadvancedactivity","controller":"settings","action":"filter-content"}', "sesadvancedactivity_admin_filter", "", 2);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("sesadvancedcomment_admin_emotio", "sesadvancedcomment", "Stickers Settings", "", '{"route":"admin_default","module":"sesadvancedcomment","controller":"emotion","action":"index"}', "sesadvancedcomment_admin_main", "", 3),
("sesadvancedcomment_admin_main_emotionssettingsmain", "sesadvancedcomment", "Stickers Categories", "", '{"route":"admin_default","module":"sesadvancedcomment","controller":"emotion","action":"index"}', "sesadvancedcomment_admin_emotio", "", 1),
("sesadvancedcomment_admin_main_emotiongallery", "sesadvancedcomment", "Stickers Packs", "", '{"route":"admin_default","module":"sesadvancedcomment","controller":"emotion","action":"gallery"}', "sesadvancedcomment_admin_emotio", "", 2);
