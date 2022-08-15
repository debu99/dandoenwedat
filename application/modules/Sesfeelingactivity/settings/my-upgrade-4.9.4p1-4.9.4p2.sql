ALTER TABLE `engine4_sesadvancedactivity_feelingposts` ADD `feeling_custom` TINYINT(1) NOT NULL DEFAULT "0";
ALTER TABLE `engine4_sesadvancedactivity_feelingposts` ADD `feeling_customtext` VARCHAR(255) NULL;
ALTER TABLE `engine4_sesfeelingactivity_feelings` ADD `enabled` TINYINT(1) NOT NULL DEFAULT "1";