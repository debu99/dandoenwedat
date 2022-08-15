--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('like_album', 'album', '{item:$subject} liked {item:$owner}''s {item:$object:album}.', 1, 1, 1, 3, 3, 0),
('like_album_photo', 'album', '{item:$subject} liked {item:$owner}''s {item:$object:photo}.', 1, 1, 1, 3, 3, 0);

--
-- updating data for table `engine4_activity_actiontypes`
--

UPDATE `engine4_activity_actiontypes`
SET `commentable`=3,
    `shareable`=3,
    `body`='{item:$subject} commented on {item:$owner}''s {item:$object:album}.'
WHERE `type`='comment_album';

UPDATE `engine4_activity_actiontypes`
SET `commentable`=3,
    `shareable`=3,
    `body`='{item:$subject} commented on {item:$owner}''s {item:$object:photo}.'
WHERE `type`='comment_album_photo';


--
-- Adding and populating `like_count` column
--

ALTER TABLE `engine4_album_albums` ADD `like_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `comment_count`;

UPDATE `engine4_album_albums` SET `like_count`=(SELECT COUNT('like_id')
                                                  FROM `engine4_core_likes`
                                                  WHERE `resource_type`='album' and `resource_id`=`engine4_album_albums`.`album_id`
                                               );

ALTER TABLE `engine4_album_photos` ADD `like_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `comment_count`;

UPDATE `engine4_album_photos` SET `like_count`=(SELECT COUNT('like_id')
                                                  FROM `engine4_core_likes`
                                                  WHERE `resource_type`='album_photo' and `resource_id`=`engine4_album_photos`.`photo_id`
                                               );

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings`(`name`, `value`) VALUES ('album.searchable', 1);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `editable`, `is_generated`) VALUES
('post_self_multi_photo', 'album', '{item:$subject} added {var:$count} {item:$action:new photos}.\r\n{body:$body}', 1, 5, 1, 1, 1, 1, 0);

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- attach_max
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'album' as `type`,
    'attach_max' as `name`,
    3 as `value`,
    0 as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

--
-- Dumping data for table `engine4_core_banners`
--

INSERT IGNORE INTO `engine4_core_banners`(`name`, `module`, `title`, `body`, `photo_id`, `params`, `custom`) VALUES
('album', 'album', 'Explore. Create. Inspire', 'Share your passion to capture the world. Find inspiration in breathtaking Photos.',0,'{"label":"Add New Photos","route":"album_general","routeParams":{"action":"upload"}}',0);

--
-- Update `param` column in `engine4_core_menuitems` table
--

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"album_general","action":"browse","icon":"fa-image"}'
WHERE `name` = 'core_main_album';
