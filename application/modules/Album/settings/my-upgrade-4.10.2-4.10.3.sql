
ALTER TABLE `engine4_album_albums` CHANGE `view_privacy` `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'everyone';

UPDATE `engine4_album_albums` SET `view_privacy` = 'everyone' WHERE (`view_privacy` = '' OR `view_privacy` IS NULL);
