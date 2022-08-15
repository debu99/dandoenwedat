
-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `editable`, `is_generated`) VALUES
('post_group', 'group', '{actors:$subject:$object}: {body:$body}', 1, 7, 1, 4, 1, 1, 0)
;


UPDATE `engine4_activity_actions`
SET `type` = 'post_group'
WHERE `type` = 'post' AND `object_type` LIKE 'group';

--
-- Adding and populating `like_count` column
--

ALTER TABLE `engine4_group_groups` ADD `like_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `view_count`;

UPDATE `engine4_group_groups` SET `like_count`=(SELECT COUNT('like_id')
                                                  FROM `engine4_core_likes`
                                                  WHERE `resource_type`='group' and `resource_id`=`engine4_group_groups`.`group_id`
                                               );

ALTER TABLE `engine4_group_albums` ADD `like_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `comment_count`;

UPDATE `engine4_group_albums` SET `like_count`=(SELECT COUNT('like_id')
                                                  FROM `engine4_core_likes`
                                                  WHERE `resource_type`='group_album' and `resource_id`=`engine4_group_albums`.`album_id`
                                               );

ALTER TABLE `engine4_group_photos` ADD `like_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `comment_count`;

UPDATE `engine4_group_photos` SET `like_count`=(SELECT COUNT('like_id')
                                                  FROM `engine4_core_likes`
                                                  WHERE `resource_type`='group_photo' and `resource_id`=`engine4_group_photos`.`photo_id`
                                               );

--
-- Adding and populating `comment_count` column
--

ALTER TABLE `engine4_group_groups` ADD `comment_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `view_count`;

UPDATE `engine4_group_groups` SET `comment_count`=(SELECT COUNT('comment_id')
                                                    FROM `engine4_core_comments`
                                                    WHERE `resource_type`='group' and `resource_id`=`engine4_group_groups`.`group_id`
                                                  );

--
-- Dumping data for table `engine4_core_banners`
--

INSERT IGNORE INTO `engine4_core_banners`(`name`, `module`, `title`, `body`, `photo_id`, `params`, `custom`) VALUES
('group', 'group', 'Get Together in Groups', 'Create and join interest based Groups. Bring people together.', 0, '{"label":"Create New Group","route":"group_general","routeParams":{"action":"create"}}', 0);

--
-- Update `param` column in `engine4_core_menuitems` table
--

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"group_general","icon":"fa-group"}'
WHERE `name` = 'core_main_group';
