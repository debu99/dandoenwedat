ALTER TABLE `engine4_sesadvancedactivity_hides` ADD `subject_id` INT NULL DEFAULT NULL AFTER `user_id`;

DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeedgif_admin_main_feedgif';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES ('sesadvancedactivity_admin_main_level', 'sesadvancedactivity', 'Member Level Settings', '', '{\"route\":\"admin_default\",\"module\":\"sesadvancedactivity\",\"controller\":\"level\",\"action\":\"index\"}', 'sesadvancedactivity_admin_main', '', 1, 0, 4);

DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeelingactivity_admin_main_level';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeedgif_admin_main_level';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeedbg_admin_main_level';

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'sesadvactivity' as `type`,
    'cmtattachement' as `name`,
    5 as `value`,
    '["stickers","gif","emotions"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'sesadvactivity' as `type`,
    'composeroptions' as `name`,
    5 as `value`,
    '["sesfeedgif","feelingssctivity","locationses","shedulepost","enablefeedbg","sesadvancedactivitytargetpost","fileupload","buysell"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'sesadvactivity' as `type`,
    'sesfeedbg_max' as `name`,
    3 as `value`,
    '12' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_activittyfeedset';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_adcampaign';
DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_reports';