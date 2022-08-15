ALTER TABLE `engine4_sesevent_events` ADD `networks` VARCHAR(255) NULL, ADD `levels` VARCHAR(255) NULL;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    "sesevent_event" as `type`,
    "allow_levels" as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    "sesevent_event" as `type`,
    "allow_network" as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");
