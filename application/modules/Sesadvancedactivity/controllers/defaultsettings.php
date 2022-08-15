<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: defaultsettings.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

$db = Zend_Db_Table_Abstract::getDefaultAdapter();
$this->writeEnabledModulesFile();
//Memories On This Day Page
$page_id = $db->select()
  ->from('engine4_core_pages', 'page_id')
  ->where('name = ?', 'sesadvancedactivity_index_onthisday')
  ->limit(1)
  ->query()
  ->fetchColumn();

// insert if it doesn't exist yet
if( !$page_id ) {
  // Insert page
  $db->insert('engine4_core_pages', array(
    'name' => 'sesadvancedactivity_index_onthisday',
    'displayname' => 'SNS - Professional Activity & Comments - Memories On This Day Page',
    'title' => 'Memories On This Day',
    'description' => 'This page show memories and feeds on this day.',
    'custom' => 0,
  ));
  $page_id = $db->lastInsertId();

  // Insert top
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'top',
    'page_id' => $page_id,
    'order' => 1,
  ));
  $top_id = $db->lastInsertId();

  // Insert main
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'main',
    'page_id' => $page_id,
    'order' => 2,
  ));
  $main_id = $db->lastInsertId();

  // Insert top-middle
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'middle',
    'page_id' => $page_id,
    'parent_content_id' => $top_id,
  ));
  $top_middle_id = $db->lastInsertId();

  // Insert main-middle
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'middle',
    'page_id' => $page_id,
    'parent_content_id' => $main_id,
    'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();

  // Insert main-left
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'left',
    'page_id' => $page_id,
    'parent_content_id' => $main_id,
    'order' => 1,
  ));
  $main_left_id = $db->lastInsertId();

  // Insert content
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesadvancedactivity.feed',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 1,
  ));
  // Insert content
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesadvancedactivity.onthisday-banner',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 1,
  ));
  // insert left content
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'user.home-links',
    'page_id' => $page_id,
    'parent_content_id' => $main_left_id,
    'order' => 1,
  ));
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'core.statistics',
    'page_id' => $page_id,
    'parent_content_id' => $main_left_id,
    'order' => 2,
  ));
}

//Welcome Tab Page
$page_id = $db->select()
  ->from('engine4_core_pages', 'page_id')
  ->where('name = ?', 'sesadvancedactivity_index_welcome')
  ->limit(1)
  ->query()
  ->fetchColumn();
$widgetOrder = 1;
// insert if it doesn't exist yet
if( !$page_id ) {
  // Insert page
  $db->insert('engine4_core_pages', array(
    'name' => 'sesadvancedactivity_index_welcome',
    'displayname' => 'SNS - Professional Activity & Comments - Welcome Tab Page',
    'title' => 'Welcome Tab Page',
    'description' => 'This page shows welcome tab in activity feeds.',
    'custom' => 0,
  ));
  $page_id = $db->lastInsertId();

  // Insert main
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'main',
    'page_id' => $page_id,
    'order' => 2,
  ));
  $main_id = $db->lastInsertId();

  // Insert main-middle
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'middle',
    'page_id' => $page_id,
    'parent_content_id' => $main_id,
    'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
    'page_id' => $page_id,
    'type' => 'widget',
    'name' => 'sesadvancedactivity.welcometab-sections',
    'parent_content_id' => $main_middle_id,
    'order' => $widgetOrder++,
    'params' => '{"title":"","displaysections":"4","nomobile":"0","name":"sesadvancedactivity.welcometab-sections"}',
  ));

  $db->insert('engine4_core_content', array(
    'page_id' => $page_id,
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'parent_content_id' => $main_middle_id,
    'order' => $widgetOrder++,
    'params' => '{"bodysimple":"<div class=\"sesact_welcome_txt_block sesbasic_clearfix\">\r\n  <p class=\"sesact_welcome_txt_block_des\">Welcome to the new feeds. You can now share your files, sell products, upload multiple photos, videos & much more. Schedule post to be shared at a later date and choose targeted audience for your posts. So, start sharing!!<\/p>\r\n\t<p class=\"sesact_welcome_txt_block_img\">\r\n\t\t<img src=\"application\/modules\/Sesadvancedactivity\/externals\/images\/welcome.png\" alt=\"\" \/>\r\n\t<\/p>\r\n<\/div>","show_content":"1","title":"","nomobile":"0","name":"sesbasic.simple-html-block"}',
  ));

  $db->insert('engine4_core_content', array(
    'page_id' => $page_id,
    'type' => 'widget',
    'name' => 'sesadvancedactivity.welcometab-sections',
    'parent_content_id' => $main_middle_id,
    'order' => $widgetOrder++,
    'params' => '{"title":"","displaysections":"1","nomobile":"0","name":"sesadvancedactivity.welcometab-sections"}',
  ));
  $db->insert('engine4_core_content', array(
    'page_id' => $page_id,
    'type' => 'widget',
    'name' => 'sesadvancedactivity.welcometab-sections',
    'parent_content_id' => $main_middle_id,
    'order' => $widgetOrder++,
    'params' => '{"title":"","displaysections":"2","nomobile":"0","name":"sesadvancedactivity.welcometab-sections"}',
  ));
  $db->insert('engine4_core_content', array(
    'page_id' => $page_id,
    'type' => 'widget',
    'name' => 'sesadvancedactivity.welcometab-sections',
    'parent_content_id' => $main_middle_id,
    'order' => $widgetOrder++,
    'params' => '{"title":"","displaysections":"3","nomobile":"0","name":"sesadvancedactivity.welcometab-sections"}',
  ));
}


//Hashtag Feeds Page
$page_id = $db->select()
  ->from('engine4_core_pages', 'page_id')
  ->where('name = ?', 'sesadvancedactivity_index_hashtag')
  ->limit(1)
  ->query()
  ->fetchColumn();
$widgetOrder = 1;
// insert if it doesn't exist yet
if( !$page_id ) {
  // Insert page
  $db->insert('engine4_core_pages', array(
    'name' => 'sesadvancedactivity_index_hashtag',
    'displayname' => 'SNS - Professional Activity & Comments - Hashtag Feeds Page',
    'title' => 'Hashtags',
    'description' => 'This page show hashtag feeds.',
    'custom' => 0,
  ));
  $page_id = $db->lastInsertId();

  // Insert top
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'top',
    'page_id' => $page_id,
    'order' => 1,
  ));
  $top_id = $db->lastInsertId();

  // Insert main
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'main',
    'page_id' => $page_id,
    'order' => 2,
  ));
  $main_id = $db->lastInsertId();

  // Insert top-middle
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'middle',
    'page_id' => $page_id,
    'parent_content_id' => $top_id,
  ));
  $top_middle_id = $db->lastInsertId();

  // Insert main-middle
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'middle',
    'page_id' => $page_id,
    'parent_content_id' => $main_id,
    'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();

  // Insert main-left
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'left',
    'page_id' => $page_id,
    'parent_content_id' => $main_id,
    'order' => 1,
  ));
  $main_left_id = $db->lastInsertId();

  // Insert main-right
  $db->insert('engine4_core_content', array(
    'type' => 'container',
    'name' => 'right',
    'page_id' => $page_id,
    'parent_content_id' => $main_id,
    'order' => 3,
  ));
  $main_right_id = $db->lastInsertId();

  // Insert content
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesadvancedactivity.feed',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => $widgetOrder++,
    'params' => '{"title":"What\'s New","design":"3","scrollfeed":"1","autoloadTimes":"3","userphotoalign":"left","enablewidthsetting":"0","sesact_image1":null,"sesact_image1_width":"500","sesact_image1_height":"450","sesact_image2":null,"sesact_image2_width":"289","sesact_image2_height":"200","sesact_image3":null,"sesact_image3_bigwidth":"328","sesact_image3_bigheight":"300","sesact_image3_smallwidth":"250","sesact_image3_smallheight":"150","sesact_image4":null,"sesact_image4_bigwidth":"578","sesact_image4_bigheight":"300","sesact_image4_smallwidth":"192","sesact_image4_smallheight":"100","sesact_image5":null,"sesact_image5_bigwidth":"289","sesact_image5_bigheight":"260","sesact_image5_smallwidth":"289","sesact_image5_smallheight":"130","sesact_image6":null,"sesact_image6_width":"289","sesact_image6_height":"150","sesact_image7":null,"sesact_image7_bigwidth":"192","sesact_image7_bigheight":"150","sesact_image7_smallwidth":"144","sesact_image7_smallheight":"150","sesact_image8":null,"sesact_image8_width":"144","sesact_image8_height":"150","sesact_image9":null,"sesact_image9_width":"192","sesact_image9_height":"150","nomobile":"0","name":"sesadvancedactivity.feed"}',
  ));
  // insert left content
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'user.home-links',
    'page_id' => $page_id,
    'parent_content_id' => $main_left_id,
    'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'core.statistics',
    'page_id' => $page_id,
    'parent_content_id' => $main_left_id,
    'order' => $widgetOrder++,
    'params' => '{"title":"Statistics"}',
  ));
  // insert right content
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesadvancedactivity.top-trends',
    'page_id' => $page_id,
    'parent_content_id' => $main_right_id,
    'order' => $widgetOrder++,
    'params' => '{"title":"Trending","limit":"10","nomobile":"0","name":"sesadvancedactivity.top-trends"}',
  ));
}

$db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_details (`action_id`) SELECT `action_id` FROM engine4_activity_actions;');

$db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_activitylikes (`activity_like_id`) SELECT `like_id` FROM engine4_activity_likes as t ON DUPLICATE KEY UPDATE activity_like_id=t.like_id;');

$db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_corelikes (`core_like_id`) SELECT `like_id` FROM engine4_core_likes as t ON DUPLICATE KEY UPDATE core_like_id=t.like_id');

$db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_activitycomments (`activity_comment_id`) SELECT `comment_id` FROM engine4_activity_comments as t ON DUPLICATE KEY UPDATE activity_comment_id=t.comment_id;');

$db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_corecomments (`core_comment_id`) SELECT `comment_id` FROM engine4_core_comments as t ON DUPLICATE KEY UPDATE core_comment_id=t.comment_id;');

//Update all core feed to our feed
$db->query("UPDATE `engine4_core_content` SET `name` = 'sesadvancedactivity.feed' WHERE `engine4_core_content`.`name` = 'activity.feed';");

//Default Settings
$db->query('INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
("sesadvancedactivity.adsenable", "0"),
("sesadvancedactivity.adsrepeatenable", "0"),
("sesadvancedactivity.adsrepeattimes", "15"),
("sesadvancedactivity.advancednotification", "0"),
("sesadvancedactivity.allowlistprivacy", "1"),
("sesadvancedactivity.allowprivacysetting", "1"),
("sesadvancedactivity.attachment.count", "9"),
("sesadvancedactivity.bigtext", "1"),
("sesadvancedactivity.composeroptions.0", "photo"),
("sesadvancedactivity.composeroptions.1", "sesadvancedactivitylink"),
("sesadvancedactivity.composeroptions.10", "sesadvancedactivitylinkedin"),
("sesadvancedactivity.composeroptions.11", "sesadvancedactivitytargetpost"),
("sesadvancedactivity.composeroptions.2", "video"),
("sesadvancedactivity.composeroptions.3", "music"),
("sesadvancedactivity.composeroptions.4", "buysell"),
("sesadvancedactivity.composeroptions.5", "tagUseses"),
("sesadvancedactivity.composeroptions.6", "fileupload"),
("sesadvancedactivity.composeroptions.7", "smilesses"),
("sesadvancedactivity.composeroptions.8", "locationses"),
("sesadvancedactivity.composeroptions.9", "shedulepost"),
("sesadvancedactivity.countfriends", "3"),
("sesadvancedactivity.dobadd", "1"),
("sesadvancedactivity.enableonthisday", "1"),
("sesadvancedactivity.eneblelikecommentshare", "1"),
("sesadvancedactivity.findfriends", "1"),
("sesadvancedactivity.fonttextsize", "24"),
("sesadvancedactivity.friendnotificationbirthday", "1"),
("sesadvancedactivity.friendrequest", "1"),
("sesadvancedactivity.language", "en"),
("sesadvancedactivity.linkedin.enable", "1"),
("sesadvancedactivity.makelandingtab", "0"),
("sesadvancedactivity.networkbasedfiltering", "0"),
("sesadvancedactivity.notificationbirthday", "1"),
("sesadvancedactivity.notificationday", "1"),
("sesadvancedactivity.notificationfriends", "1"),
("sesadvancedactivity.notificationfriendsdays", "30"),
("sesadvancedactivity.numberofdays", "3"),
("sesadvancedactivity.numberoffriends", "3"),
("sesadvancedactivity.profilephotoupload", "0"),
("sesadvancedactivity.reportenable", "1"),
("sesadvancedactivity.showwelcometab", "1"),
("sesadvancedactivity.socialshare", "1"),
("sesadvancedactivity.tabsettings", "Welcome to [site_title], [user_name]"),
("sesadvancedactivity.tabvisibility", "0"),
("sesadvancedactivity.textlimit", "120"),
("sesadvancedactivity.translate", "1"),
("sesadvancedactivity.visiblesearchfilter", "6");');

$db->query('INSERT IGNORE INTO `engine4_sesadvancedactivity_filterlists` (`filtertype`, `module`, `title`, `active`, `is_delete`, `order`) VALUES
("all", "Core", "All Updates", 1, 0, 1),
("my_networks", "Networks", "My Network", 1, 0, 3),
("my_friends", "Members", "Friends", 1, 0, 2),
("posts", "Core", "Posts", 1, 0, 12),
("saved_feeds", "Core", "Saved Feeds", 1, 0, 13),
("post_self_buysell", "Core", "Sell Something", 1, 0, 9),
("post_self_file", "Core", "Files", 1, 0, 10),
("scheduled_post", "Core", "Scheduled Post", 1, 0, 11),
("event", "Events", "Events", 1, 1, 7),
("album", "Albums", "Photos", 1, 1, 4),
("blog", "Blogs", "Blogs", 1, 1, 8),
("music", "Music", "Music", 1, 1, 6),
("video", "Videos", "Videos", 1, 1, 5),
("poll", "Polls", "Polls", 1, 1, 5),
("group", "Groups", "Groups", 1, 1, 5),
("classified", "Classifieds", "Classifieds", 1, 1, 5),
("sesevent", "SNS - Advanced Events Plugin", "Events", 1, 1, 7),
("sesalbum", "SNS - Advanced Photos & Albums Plugin", "Photos", 1, 1, 4),
("sesblog", "Advanced Blog Plugin", "Blogs", 1, 1, 8),
("sesmusic", "Advanced Music Albums, Songs & Playlists Plugin", "Music", 1, 1, 6),
("sesvideo", "SNS - Advanced Videos & Channels Plugin", "Videos", 1, 1, 5);');

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("sesadvancedactivity_tagged_people", "sesadvancedactivity", \'{item:$subject} tagged you in a {var:$postLink}.\', 0, "", 1),
("sesadvancedactivity_scheduled_live", "sesadvancedactivity", "Your scheduled post has been made live.", 0, "", 1);');

$db->query('UPDATE  `engine4_activity_actiontypes` SET  `module` =  "sesadvancedactivity" WHERE  `engine4_activity_actiontypes`.`type` = "post_self_link";');
$db->query('UPDATE  `engine4_activity_actiontypes` SET  `module` =  "sesadvancedactivity" WHERE  `engine4_activity_actiontypes`.`type` = "post_self_video";');
$db->query('UPDATE  `engine4_activity_actiontypes` SET  `module` =  "sesadvancedactivity" WHERE  `engine4_activity_actiontypes`.`type` = "post_self_photo";');
$db->query('UPDATE  `engine4_activity_actiontypes` SET  `module` =  "sesadvancedactivity" WHERE  `engine4_activity_actiontypes`.`type` = "post_self_music";');

$db->query('UPDATE `engine4_core_menuitems` SET `params` = \'{"route":"sesadvancedactivity_onthisday"}\' WHERE `engine4_core_menuitems`.`name` = "sesadvancedactivity_index_onthisday";');

$db->query('INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
("SNS - Advanced Activity - Cleanup Feed Privacy", "sesadvancedactivity", "Sesadvancedactivity_Plugin_Task_Cleanup", 86400);');
$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sesadvancedactivity_admin_main_feedsharing", "sesadvancedactivity", "Feed Sharing Settings", "", \'{"route":"admin_default","module":"sesadvancedactivity","controller":"settings","action":"feedsharing"}\', "sesadvancedactivity_admin_main", "", 888);');

$db->query('UPDATE `engine4_activity_notificationtypes` SET `body` = \'Your scheduled {var:$postLink} has been made live.\' WHERE `engine4_activity_notificationtypes`.`type` = "sesadvancedactivity_scheduled_live";');

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES ("sesadvancedactivity_tagged_item", "sesadvancedactivity", \'{item:$subject} tagged your {var:$itemurl} in a {var:$postLink}.\', "0", "", "1");');

$db->query('INSERT IGNORE INTO `engine4_sesadvancedactivity_filterlists` (`filtertype`, `module`, `title`, `active`, `is_delete`, `order`, `file_id`) VALUES ("share", "core", "Share Feeds", "1", "0", 10, "0");');


//Category Icon for Comments
$select = Engine_Api::_()->getDbTable('emotioncategories', 'sesadvancedcomment')->select()->order('category_id ASC');
$paginator = Engine_Api::_()->getDbTable('emotioncategories', 'sesadvancedcomment')->fetchAll($select);
foreach($paginator as $result) {
	$title = lcfirst($result->title);
  if($title == 'in Love') {
    $title = 'inlove';
  }
  if($title == 'in love') {
    $title = 'inlove';
  }
	$PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesadvancedcomment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "category" . DIRECTORY_SEPARATOR;
	if (is_file($PathFile . $title . '.png'))  {
		$pngFile = $PathFile . $title . '.png';
		$photo_params = array(
				'parent_id' => $result->category_id,
				'parent_type' => "sesadvancedcomment_category",
		);
		$photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
		if (!empty($photoFile->file_id)) {
			$db->update('engine4_sesadvancedcomment_emotioncategories', array('file_id' => $photoFile->file_id), array('category_id = ?' => $result->category_id));
		}
	}
}

//Emotions Gallery image for Comments
$emotiongalleriesselect = Engine_Api::_()->getDbTable('emotiongalleries', 'sesadvancedcomment')->select()->order('gallery_id ASC');
$paginator = Engine_Api::_()->getDbTable('emotiongalleries', 'sesadvancedcomment')->fetchAll($emotiongalleriesselect);
foreach($paginator as $result) {
	$title = strtolower($result->title);
  if($title == 'lazy life line') {
    $title = 'lazylifeline';
  } else if($title == 'tom and jerry') {
    $title = 'tomandjerry';
  }
	$PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesadvancedcomment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "stickers" . DIRECTORY_SEPARATOR . "galleryimages" . DIRECTORY_SEPARATOR;
	if (is_file($PathFile . $title . '.png'))  {
		$pngFile = $PathFile . $title . '.png';
		$photo_params = array(
				'parent_id' => $result->gallery_id,
				'parent_type' => "sesadvancedcomment_gallery",
		);
		$photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
		if (!empty($photoFile->file_id)) {
			$db->update('engine4_sesadvancedcomment_emotiongalleries', array('file_id' => $photoFile->file_id), array('gallery_id = ?' => $result->gallery_id));
		}
	}
}

//Upload emotion Files in Gallery
$emotionfilesTable = Engine_Api::_()->getDbtable('emotionfiles', 'sesadvancedcomment');
$emotiongalleriesselect = Engine_Api::_()->getDbTable('emotiongalleries', 'sesadvancedcomment')->select()->order('gallery_id ASC');
$paginator = Engine_Api::_()->getDbTable('emotiongalleries', 'sesadvancedcomment')->fetchAll($emotiongalleriesselect);

foreach($paginator as $result) {

  $title = $result->title;
  if($title == 'Meep') {
    $title == 'Meep';
  } elseif($title == 'Minions') {
    $title = 'minions';
  } elseif($title == 'Lazy Life Line') {
    $title = 'LazyLifeLine';
  } elseif($title == 'Waddles') {
    $title = 'waddles';
  } elseif($title == 'Panda') {
    $title = 'panda';
  } elseif($title == 'Tom And Jerry') {
    $title = 'tomandjerry';
  }

  $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesadvancedcomment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "stickers" . DIRECTORY_SEPARATOR . $title . DIRECTORY_SEPARATOR;

  for($i= 1;$i<=40;$i++) {
    if (is_file($PathFile . $i . '.png')) {
      $item = $emotionfilesTable->createRow();
      $values['gallery_id'] = $result->gallery_id;
      $item->setFromArray($values);
      $item->save();
      $pngFile = $PathFile . $i . '.png';
      $storage = Engine_Api::_()->getItemTable('storage_file');
      $storageObject = $storage->createFile($pngFile, array(
        'parent_id' => $item->getIdentity(),
        'parent_type' => $item->getType(),
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
      ));
      // Remove temporary file
      @unlink($file['tmp_name']);
      $item->photo_id = $storageObject->file_id;
      $item->save();
    }
  }
}

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("sesadvancedcomment_tagged_people", "sesadvancedcomment", \'{item:$subject} mention you in a {var:$commentLink}.\', 0, "", 1),
("sesadvancedcomment_taggedreply_people", "sesadvancedcomment", \'{item:$subject} mention you in a {var:$commentLink} on comment.\', 0, "", 1);');

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("sesadvancedactivity_reacted_love", "sesadvancedactivity", \'{item:$subject} reacted to your {item:$object:$label}.\', 0, "", 1),
("sesadvancedactivity_reacted_haha", "sesadvancedactivity", \'{item:$subject} reacted to your {item:$object:$label}.\', 0, "", 1),
("sesadvancedactivity_reacted_wow", "sesadvancedactivity", \'{item:$subject} reacted to your {item:$object:$label}.\', 0, "", 1),
("sesadvancedactivity_reacted_angry", "sesadvancedactivity", \'{item:$subject} reacted to your {item:$object:$label}.\', 0, "", 1),
("sesadvancedactivity_reacted_sad", "sesadvancedactivity", \'{item:$subject} reacted to your {item:$object:$label}.\', 0, "", 1);');

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("sesadvancedcomment_admin_main_managereactions", "sesadvancedcomment", "Manage Reactions", "", \'{"route":"admin_default","module":"sesadvancedcomment","controller":"manage-reactions","action":"index"}\', "sesadvancedcomment_admin_main", "", 5);');

$db->query('CREATE TABLE IF NOT EXISTS `engine4_sesadvancedcomment_reactions` (
  `reaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR( 255 ) NOT NULL,
  `file_id` int(11) NOT NULL DEFAULT "0",
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  PRIMARY KEY (`reaction_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;');

$db->query('INSERT IGNORE INTO `engine4_sesadvancedcomment_reactions` (`reaction_id`, `title`, `enabled`, `file_id`) VALUES
(1, "Like", 1, 0),
(2, "Love", 1, 0),
(3, "Haha", 1, 0),
(4, "Wow", 1, 0),
(5, "Angry", 1, 0),
(6, "Sad", 1, 0);');

//Upload Reactions
$reactionsTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment');
$emotiongalleriesselect = $reactionsTable->select()->order('reaction_id ASC');
$paginator = $reactionsTable->fetchAll($emotiongalleriesselect);

foreach($paginator as $result) {

  $title = $result->title;
  if($title == 'Like') {
    $title = 'icon-like';
  } elseif($title == 'Love') {
    $title = 'icon-love';
  } elseif($title == 'Sad') {
    $title = 'icon-sad';
  } elseif($title == 'Wow') {
    $title = 'icon-wow';
  } elseif($title == 'Haha') {
    $title = 'icon-haha';
  } elseif($title == 'Angry') {
    $title = 'icon-angery';
  }

  $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesadvancedcomment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR;

	if (is_file($PathFile . $title . '.png'))  {
		$pngFile = $PathFile . $title . '.png';
		$photo_params = array(
				'parent_id' => $result->reaction_id,
				'parent_type' => "sesadvancedcomment_reaction",
		);
		$photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
		if (!empty($photoFile->file_id)) {
			$db->update('engine4_sesadvancedcomment_reactions', array('file_id' => $photoFile->file_id), array('reaction_id = ?' => $result->reaction_id));
		}
	}
}
Engine_Api::_()->getApi('settings', 'core')->setSetting('sesadvancedcomment.managereactions', 1);

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES ("sesadvancedcomment_replycomment", "sesadvancedcomment", \'{item:$subject} replied to your comment on a {item:$object:$label}.\', 0, "", 1);');

$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sesadvancedcomment_emotiongalleries'")->fetch();
if (!empty($table_exist)) {
    $enabled = $db->query("SHOW COLUMNS FROM engine4_sesadvancedcomment_emotiongalleries LIKE 'enabled'")->fetch();
    if (empty($enabled)) {
        $db->query('ALTER TABLE `engine4_sesadvancedcomment_emotiongalleries` ADD `enabled` TINYINT(1) NOT NULL DEFAULT "1";');
    }
}

$db->query('CREATE TABLE IF NOT EXISTS `engine4_sesadvancedcomment_voteupdowns` (
  `voteupdown_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(10) NOT NULL DEFAULT "upvote",
  `resource_type` VARCHAR(100) NOT NULL,
  `resource_id` INT(11) NOT NULL,
  `user_type` VARCHAR(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;');

$db->query('INSERT IGNORE INTO `engine4_authorization_permissions` (`level_id`, `type`, `name`, `value`, `params`) VALUES
(1, "sesfeedbg", "enablefeedbg", 1, NULL),
(1, "sesfeedbg", "max", 3, "12"),
(2, "sesfeedbg", "enablefeedbg", 1, NULL),
(2, "sesfeedbg", "max", 3, "12"),
(3, "sesfeedbg", "enablefeedbg", 1, NULL),
(3, "sesfeedbg", "max", 3, "12"),
(4, "sesfeedbg", "enablefeedbg", 1, NULL),
(4, "sesfeedbg", "max", 3, "12");');

$sesfeedbg_backgrounds_table_exist = $db->query('SHOW TABLES LIKE \'engine4_sesfeedbg_backgrounds\'')->fetch();
if($sesfeedbg_backgrounds_table_exist) {

  $starttime = $db->query('SHOW COLUMNS FROM engine4_sesfeedbg_backgrounds LIKE \'starttime\'')->fetch();
  if (empty($starttime)) {
    $db->query('ALTER TABLE `engine4_sesfeedbg_backgrounds` ADD `starttime` DATE NULL, ADD `endtime` DATE NULL;');
  }

  $endtime = $db->query('SHOW COLUMNS FROM engine4_sesfeedbg_backgrounds LIKE \'endtime\'')->fetch();
  if (empty($endtime)) {
    $db->query('ALTER TABLE `engine4_sesfeedbg_backgrounds` CHANGE `endtime` `endtime` DATE NULL;');
  }

  $enableenddate = $db->query('SHOW COLUMNS FROM engine4_sesfeedbg_backgrounds LIKE \'enableenddate\'')->fetch();
  if (empty($enableenddate)) {
    $db->query('ALTER TABLE `engine4_sesfeedbg_backgrounds` ADD `enableenddate` TINYINT(1) NOT NULL DEFAULT "1";');
  }

  $featured = $db->query('SHOW COLUMNS FROM engine4_sesfeedbg_backgrounds LIKE \'featured\'')->fetch();
  if (empty($featured)) {
    $db->query('ALTER TABLE `engine4_sesfeedbg_backgrounds` ADD `featured` TINYINT(1) NOT NULL DEFAULT "0";');
  }
}
// Upload Backgrounds
$this->uploadBackgrounds();

// $composerOptions = array_merge(array('sesfeedgif'), Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.composeroptions', 1));
// Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesadvancedactivity.composeroptions');
// Engine_Api::_()->getApi('settings', 'core')->setSetting('sesadvancedactivity.composeroptions', $composerOptions);

$db->query('INSERT IGNORE INTO `engine4_authorization_permissions` (`level_id`, `type`, `name`, `value`, `params`) VALUES
(1, "sesfeedgif", "enablefeedgif", 1, NULL),
(2, "sesfeedgif", "enablefeedgif", 1, NULL),
(3, "sesfeedgif", "enablefeedgif", 1, NULL),
(4, "sesfeedgif", "enablefeedgif", 1, NULL);');

$db->query('INSERT IGNORE INTO `engine4_authorization_permissions` (`level_id`, `type`, `name`, `value`, `params`) VALUES
(1, "sesfeedgif", "enablecommentgif", 1, NULL),
(2, "sesfeedgif", "enablecommentgif", 1, NULL),
(3, "sesfeedgif", "enablecommentgif", 1, NULL),
(4, "sesfeedgif", "enablecommentgif", 1, NULL);');

$this->uploadFeelingsMainIconsActivity();

//Feeling Work
$this->uploadFeelingsActivity();


// $composerOptions = array_merge(array('feelingssctivity'), Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.composeroptions', 1));
// Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesadvancedactivity.composeroptions');
// Engine_Api::_()->getApi('settings', 'core')->setSetting('sesadvancedactivity.composeroptions', $composerOptions);

$feelingposts_table = $db->query('SHOW TABLES LIKE \'engine4_sesadvancedactivity_feelingposts\'')->fetch();
if($feelingposts_table) {
  $feeling_custom = $db->query('SHOW COLUMNS FROM engine4_sesadvancedactivity_feelingposts LIKE \'feeling_custom\'')->fetch();
  if (empty($feeling_custom)) {
    $db->query('ALTER TABLE `engine4_sesadvancedactivity_feelingposts` ADD `feeling_custom` TINYINT(1) NOT NULL DEFAULT "0";');
  }

  $feeling_customtext = $db->query('SHOW COLUMNS FROM engine4_sesadvancedactivity_feelingposts LIKE \'feeling_customtext\'')->fetch();
  if (empty($feeling_customtext)) {
    $db->query('ALTER TABLE `engine4_sesadvancedactivity_feelingposts` ADD `feeling_customtext` VARCHAR(255) NULL;');
  }
}

$feelings_table = $db->query('SHOW TABLES LIKE \'engine4_sesfeelingactivity_feelings\'')->fetch();
if($feelings_table) {
  $enabled = $db->query('SHOW COLUMNS FROM engine4_sesfeelingactivity_feelings LIKE \'enabled\'')->fetch();
  if (empty($enabled)) {
    $db->query('ALTER TABLE `engine4_sesfeelingactivity_feelings` ADD `enabled` TINYINT(1) NOT NULL DEFAULT "1";');
  }
}

$hides_table = $db->query('SHOW TABLES LIKE \'engine4_sesadvancedactivity_hides\'')->fetch();
if($hides_table) {
    $subject_id = $db->query('SHOW COLUMNS FROM engine4_sesadvancedactivity_hides LIKE \'subject_id\'')->fetch();
    if (empty($subject_id)) {
        $db->query("ALTER TABLE `engine4_sesadvancedactivity_hides` ADD `subject_id` INT NULL DEFAULT NULL;");
    }
}
$db->query("DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeedgif_admin_main_feedgif';");

$db->query("INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES
('sesadvancedactivity_admin_main_level', 'sesadvancedactivity', 'Member Level Settings', '', '{\"route\":\"admin_default\",\"module\":\"sesadvancedactivity\",\"controller\":\"level\",\"action\":\"index\"}', 'sesadvancedactivity_admin_main', '', 1, 0, 4);");

$db->query("DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeelingactivity_admin_main_level';");

$db->query("DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeedgif_admin_main_level';");

$db->query("DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesfeedbg_admin_main_level';");

$db->query('INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    "sesadvactivity" as `type`,
    "cmtattachement" as `name`,
    5 as `value`,
    \'["stickers","gif","emotions"]\' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");');

$db->query('INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    "sesadvactivity" as `type`,
    "composeroptions" as `name`,
    5 as `value`,
    \'["sesfeedgif","feelingssctivity","locationses","shedulepost","enablefeedbg","sesadvancedactivitytargetpost","fileupload","buysell"]\' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");');

$db->query("INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'sesadvactivity' as `type`,
    'sesfeedbg_max' as `name`,
    3 as `value`,
    '12' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');");

$db->query("DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_activittyfeedset';");
$db->query("DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_adcampaign';");
$db->query("DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`name` = 'sesadvancedactivity_admin_main_reports';");