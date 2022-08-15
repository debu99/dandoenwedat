UPDATE `engine4_core_menuitems` SET `enabled` = '0'
WHERE `engine4_core_menuitems`.`name` = 'sesevent_profile_share';

ALTER TABLE `engine4_sesevent_events` ADD `change_title_count` INT(10) DEFAULT 0 AFTER `save_count`;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`)
VALUES ('sesevent_profile_copy', 'sesevent', 'Copy This Event', 'Sesevent_Plugin_Menus', '', 'sesevent_profile', '', 1, 0, 6);

-- 2021-04-15
INSERT INTO `engine4_core_pages` (`name`, `displayname`, `url`, `title`, `description`, `keywords`, `custom`, `fragment`, `layout`, `levels`, `provides`, `view_count`) VALUES
('ememsub_settings_index', 'User Subscription Page', NULL, 'Subscription', '', '', 0, 0, '', NULL, 'no-subject', 0);

INSERT INTO `engine4_core_content` (`page_id`, `type`, `name`, `parent_content_id`, `order`, `params`, `attribs`) VALUES
((SELECT page.page_id FROM engine4_core_pages  page WHERE page.NAME = 'ememsub_settings_index'), 'container', 'main', NULL, 1, '[""]', NULL);
INSERT INTO `engine4_core_content` (`page_id`, `type`, `name`, `parent_content_id`, `order`, `params`, `attribs`)
SELECT page.page_id, 'container', 'middle', content.content_id, 1, '[""]', NULL
FROM engine4_core_pages page JOIN engine4_core_content content ON page.page_id = content.page_id
WHERE page.NAME='ememsub_settings_index' AND content.NAME='main';

INSERT IGNORE INTO `engine4_activity_notificationtypes`
    (`type`, `module`, `body`, `is_request`, `handler`, `default`, `sesandoidapp_enable_pushnotification`)
VALUES ('sesevent_new_event', 'sesevent', 'Event {item:$object} has been created.', 0, '', 1, 1),
       ('sesevent_last_minute_event', 'sesevent', 'Event {item:$object} will be started soon.', 0, '', 1, 1),
       ('sesevent_new_online_event', 'sesevent', 'Event {item:$object} has been created.', 0, '', 1, 1),
       ('sesevent_last_minute_online_event', 'sesevent', 'Event {item:$object} will be started soon.', 0, '', 1, 1);

drop table if exists `engine4_user_regions`;
create table `engine4_user_regions`
(
    `region_id`   int(11) unsigned not null auto_increment primary key,
    `title`       varchar(128) not null
);

insert ignore into `engine4_user_regions` (`title`) values
('Noord-Holland'),
('Zuid-Holland'),
('Zeeland'),
('Noord-Brabant'),
('Utrecht'),
('Flevoland'),
('Friesland'),
('Groningen'),
('Drenthe'),
('Overijssel'),
('Gelderland'),
('Limburg');

drop table if exists `engine4_user_regionvalues`;
create table `engine4_user_regionvalues` (
     `regionvalue_id` int(11) unsigned not null auto_increment primary key,
     `region_id` int(11) unsigned not null,
     `user_id` int(11) unsigned not null
);


insert ignore into `engine4_core_mailtemplates` (`type`, `module`, `vars`, `default`) values
('notify_sesevent_new_event', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_last_minute_event', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_new_online_event', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_last_minute_online_event', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1);

alter table `engine4_sesevent_events` add `region_id` int(10) after `location`;

/* update type 128 characters for engine4_user_emailsettings */
alter table `engine4_user_emailsettings` change column `type` `type` varchar(128);

/* add task to send mail for last minute event */
insert ignore into `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`)
values ('Last Minute Event Mail', 'sesevent', 'Sesevent_Plugin_Task_LastMinuteMail', 43200);

-- add field is_sent_lastminute for engine4_sesevent_events
alter table `engine4_sesevent_events` add `is_send_lastminute` tinyint default 0 after `is_approved`;

-- notification for organizer and participants
INSERT IGNORE INTO `engine4_activity_notificationtypes`
(`type`, `module`, `body`, `is_request`, `handler`, `default`, `sesandoidapp_enable_pushnotification`)
VALUES ('sesevent_organizer_reach_minimum_partis', 'sesevent', 'Your event {item:$object} reach a minimum number of participants.', 0, '', 1, 1),
       ('sesevent_organizer_reach_maximum_partis', 'sesevent', 'Your event {item:$object} reach a maximum number of participants.', 0, '', 1, 1),
       ('sesevent_joined_reach_minimum_partis', 'sesevent', 'The event {item:$object} you joined reach a minimum number of participants.', 0, '', 1, 1),
       ('sesevent_joined_reach_maximum_partis', 'sesevent', 'The event {item:$object} you joined reach a maximum number of participants.', 0, '', 1, 1);
insert ignore into `engine4_core_mailtemplates` (`type`, `module`, `vars`, `default`) values
('notify_sesevent_organizer_reach_minimum_partis', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_organizer_reach_maximum_partis', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_joined_reach_minimum_partis', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_date],[object_time],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_joined_reach_maximum_partis', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1);

-- 2021-05-18: update timeout for cron task Last Minute Event Mail
update `engine4_core_tasks` set `timeout` = 21600
where `plugin` = 'Sesevent_Plugin_Task_LastMinuteMail';
alter table `engine4_sesevent_events` add `is_send_before_24h` tinyint default 0 after `is_send_lastminute`;
alter table `engine4_sesevent_events` add `is_send_to_favorite` tinyint default 0 after `is_send_before_24h`;

INSERT IGNORE INTO `engine4_activity_notificationtypes`
(`type`, `module`, `body`, `is_request`, `handler`, `default`, `sesandoidapp_enable_pushnotification`)
VALUES ('sesevent_organizer_remind', 'sesevent', 'Your event {item:$object} will start in the next 24 hours.', 0, '', 1, 1),
       ('sesevent_joined_remind', 'sesevent', 'Your event {item:$object} you joined will start in the next 24 hours.', 0, '', 1, 1),
       ('sesevent_fav_almost_full', 'sesevent', 'The event {item:$object} you favorite is almost full.', 0, '', 1, 1);
insert ignore into `engine4_core_mailtemplates` (`type`, `module`, `vars`, `default`) values
('notify_sesevent_organizer_remind', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_joined_remind', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1),
('notify_sesevent_fav_almost_full', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]',1);

-- 2021-05-25: DAN-31 - add check send reach min and max for event
alter table `engine4_sesevent_events` add `is_send_reach_min` tinyint default 0 after `is_send_before_24h`;
alter table `engine4_sesevent_events` add `is_send_reach_max` tinyint default 0 after `is_send_reach_min`;
--  ađd params for last minute and new event
update `engine4_core_mailtemplates` set `vars` = '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_date],[object_time],[object_link],[object_photo],[object_description]'
where `type` = 'notify_sesevent_new_event'
   or `type` = 'notify_sesevent_last_minute_event'
   or `type` = 'notify_sesevent_new_online_event'
   or `type` = 'notify_sesevent_last_minute_online_event';

-- 2021-06-02: Add params to notify emails
UPDATE engine4_core_mailtemplates SET `vars` = '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_date],[object_time]'
WHERE `type` = 'notify_sesevent_organizer_remind';

UPDATE engine4_core_mailtemplates SET `vars` = '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_date],[object_time]'
WHERE `type` = 'notify_sesevent_joined_remind';

UPDATE engine4_core_mailtemplates SET `vars` = '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_date],[object_time]'
WHERE `type` = 'notify_sesevent_joined_remind';

-- Update data type of starttime and endtime:
ALTER TABLE `engine4_sesevent_events` CHANGE `starttime` `starttime` DATETIME  NOT NULL;
ALTER TABLE `engine4_sesevent_events` CHANGE `endtime` `endtime` DATETIME  NOT NULL;
