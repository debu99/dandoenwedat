RENAME TABLE `engine4_seseventreview_review_fields_maps` TO `engine4_eventreview_fields_maps`;
RENAME TABLE `engine4_seseventreview_review_fields_meta` TO `engine4_eventreview_fields_meta`;
RENAME TABLE `engine4_seseventreview_review_fields_options` TO `engine4_eventreview_fields_options`;
RENAME TABLE `engine4_seseventreview_review_fields_search` TO `engine4_eventreview_fields_search`;
RENAME TABLE `engine4_seseventreview_review_fields_values` TO `engine4_eventreview_fields_values`;

UPDATE `engine4_authorization_permissions` SET `type` = "eventreview" WHERE `type` = 'seseventreview_review';

UPDATE `engine4_authorization_allow` SET `resource_type` = "eventreview" WHERE `resource_type` = 'seseventreview_review';
