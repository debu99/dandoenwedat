--
-- indexing for table `engine4_album_albums`
--

ALTER TABLE `engine4_album_albums` ADD INDEX(`type`);

ALTER TABLE `engine4_album_albums` ADD INDEX(`creation_date`);

ALTER TABLE `engine4_album_albums` ADD INDEX(`modified_date`);

ALTER TABLE `engine4_album_albums` ADD INDEX(`view_count`);

ALTER TABLE `engine4_album_albums` ADD INDEX(`comment_count`);

ALTER TABLE `engine4_album_albums` ADD INDEX(`like_count`);

ALTER TABLE `engine4_album_albums` ADD INDEX(`view_privacy`);

ALTER TABLE `engine4_album_albums` ADD INDEX(`networks`);

--
-- indexing for table `engine4_album_photos`
--
ALTER TABLE `engine4_album_photos` ADD INDEX(`creation_date`);

ALTER TABLE `engine4_album_photos` ADD INDEX(`modified_date`);

ALTER TABLE `engine4_album_photos` ADD INDEX(`view_count`);

ALTER TABLE `engine4_album_photos` ADD INDEX(`comment_count`);

ALTER TABLE `engine4_album_photos` ADD INDEX(`like_count`);

ALTER TABLE `engine4_album_photos` ADD INDEX(`order`);


UPDATE `engine4_core_menuitems` SET `params` = '{"route":"album_general","action":"browse","icon":"fa fa-image"}' WHERE `name` = 'core_main_album';
