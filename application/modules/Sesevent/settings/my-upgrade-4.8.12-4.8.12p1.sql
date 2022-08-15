INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES 
("sesevent_like_event", "sesevent", '{item:$subject} likes the event {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventalbum", "sesevent", '{item:$subject} likes the event album {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventphoto", "sesevent", '{item:$subject} likes the event photo {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventlist", "sesevent", '{item:$subject} likes the event list {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventspeaker", "sesevent", '{item:$subject} likes the event speaker {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventhost", "sesevent", '{item:$subject} likes the event host {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_event", "sesevent", '{item:$subject} favourite the event {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_eventlist", "sesevent", '{item:$subject} favourite the event list {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_eventspeaker", "sesevent", '{item:$subject} favourite the event speaker {item:$object}:', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_eventhost", "sesevent", '{item:$subject} favourite the event host {item:$object}:', 1, 5, 1, 1, 1, 1);

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("sesevent_like_event", "sesevent", '{item:$subject} likes your event {item:$object}.', 0, ""),
("sesevent_like_eventalbum", "sesevent", '{item:$subject} likes your event album {item:$object}.', 0, ""),
("sesevent_like_eventphoto", "sesevent", '{item:$subject} likes your event photo {item:$object}.', 0, ""),
("sesevent_like_eventlist", "sesevent", '{item:$subject} likes your event list {item:$object}.', 0, ""),
("sesevent_like_eventspeaker", "sesevent", '{item:$subject} likes your event speaker {item:$object}.', 0, ""),
("sesevent_like_eventhost", "sesevent", '{item:$subject} likes your event host {item:$object}.', 0, ""),
("sesevent_favourite_event", "sesevent", '{item:$subject} favourite your event {item:$object}.', 0, ""),
("sesevent_favourite_eventlist", "sesevent", '{item:$subject} favourite your event list {item:$object}.', 0, ""),
("sesevent_favourite_eventspeaker", "sesevent", '{item:$subject} favourite your event speaker {item:$object}.', 0, ""),
("sesevent_favourite_eventhost", "sesevent", '{item:$subject} favourite your event host {item:$object}.', 0, "");