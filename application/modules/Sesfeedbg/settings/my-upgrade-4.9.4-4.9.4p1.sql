ALTER TABLE `engine4_sesfeedbg_backgrounds` ADD `starttime` DATE NULL, ADD `endtime` DATE NULL;
ALTER TABLE `engine4_sesfeedbg_backgrounds` ADD `enableenddate` TINYINT(1) NOT NULL DEFAULT "1";