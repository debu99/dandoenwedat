
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_sesevent', 'sesevent', 'SES - Advanced Events', '', '{"route":"admin_default","module":"sesevent","controller":"settings"}', 'core_admin_main_plugins', '', 1),
('sesevent_admin_main_settings', 'sesevent', 'Global Settings', '', '{"route":"admin_default","module":"sesevent","controller":"settings"}', 'sesevent_admin_main', '', 1),
('sesevent_admin_main_subgloablsetting', 'sesevent', 'Global Settings', '', '{"route":"admin_default","module":"sesevent","controller":"settings"}', 'sesevent_admin_main_settings', '', 1);


INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_sesevent_accepted', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_sesevent_payment_success', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[registration_number],[item_title]'),
('notify_sesevent_approve', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_sesevent_discussion_response', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_sesevent_discussion_reply', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_sesevent_invite', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('sesevent_event_create', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link]'),
('sesevent_ticketpurchased_eventowner', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link],[buyer_name]'),
('sesevent_ticketpayment_requestadmin', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link],[buyer_name]'),
('sesevent_ticketpayment_adminrequestcancel', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link]'),
('sesevent_ticketpayment_adminrequestapproved', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link]'),
('sesevent_sponsorshippurchased_eventowner', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link],[buyer_name]'),
('sesevent_sponsorshippayment_requestadmin', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link],[buyer_name]'),
('sesevent_sponsorshippayment_adminrequestcancel', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link]'),
('sesevent_sponsorshippayment_adminrequestapproved', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link]'),
('sesevent_rsvp_change', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link],[rsvp_changetext]'),
('sesevent_tiketinvoice_buyer', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link],[invoice_body]'),
('sesevent_tikets_details', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[event_title],[object_link],[ticket_body]'),
('sesevent_event_adminapproved', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[event_title]'),
('sesevent_event_admindisapproved', 'sesevent', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[event_title]');
