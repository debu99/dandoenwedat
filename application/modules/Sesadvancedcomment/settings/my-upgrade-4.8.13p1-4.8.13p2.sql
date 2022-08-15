INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("sesadvancedcomment_tagged_people", "sesadvancedcomment", '{item:$subject} mention you in a {var:$commentLink}.', 0, "", 1),
("sesadvancedcomment_taggedreply_people", "sesadvancedcomment", '{item:$subject} mention you in a {var:$commentLink} on comment.', 0, "", 1);