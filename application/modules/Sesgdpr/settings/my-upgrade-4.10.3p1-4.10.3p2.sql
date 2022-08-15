INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
("sesgdpr_admin_reply", "sesgdpr", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[host],[object_link],[subject],[body]"),
("sesgdpr_consent_user", "sesgdpr", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[host],[object_link],[subject],[body]");

INSERT INTO `engine4_sesgdpr_services` ( `name`, `url`, `description`, `enabled`, `creation_date`, `modified_date`) VALUES
( 'Facebook', 'https://www.facebook.com/', 'We currently use Facebook to login on our website.', 1, NOW(), NOW()),
( 'Youtube', 'https://www.youtube.com/', 'We currently allow you to create videos from Youtube and view them on our site.', 1, NOW(), NOW()),
( 'Twitter', 'https://twitter.com', 'We currently use Twitter to login on our website.', 1, NOW(), NOW());