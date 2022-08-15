ALTER TABLE  `engine4_sesadvancedactivity_eventmessages` CHANGE  `creation_date`  `creation_date` DATETIME NULL DEFAULT NULL ;
UPDATE `engine4_core_menuitems` SET `label` = 'SNS: Professional Activity & Comments' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_settings_sesadvancedactivity';
