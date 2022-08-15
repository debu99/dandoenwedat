<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: defaultsettings.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
$db = Zend_Db_Table_Abstract::getDefaultAdapter();

// Welcome page for event.
$db = Zend_Db_Table_Abstract::getDefaultAdapter();

$db->query('INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES ("seseventreview", "SES- Events Reviews and Ratings Extension", "SES- Events Reviews and Ratings Extension", "4.9.4", 1, "extra");');

$languages = Zend_Locale::getTranslationList('language', Zend_Registry::get('Locale'));
$languageList = Zend_Registry::get('Zend_Translate')->getList();
$page_id = $db->select()
  ->from('engine4_core_pages', 'page_id')
  ->where('name = ?', 'sesevent_index_welcome')
  ->limit(1)
  ->query()
  ->fetchColumn();

if( !$page_id ) {

  // Insert page
  $db->insert('engine4_core_pages', array(
    'name' => 'sesevent_index_welcome',
    'displayname' => 'SES - Advanced Events - Event Welcome Page',
    'title' => 'Event Welcome Page',
    'description' => 'This page is the event welcome page.',
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

  // Insert content
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.main-slideshows',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 3,
    'params' => '{"infoshow":["searchForVenue","findVenue","getStarted"],"sfvtextcolor":"#FFFFFF","sfvbtncolor":"#FF4C4C","fvbtextcolor":"#FFFFFF","fvbbtncolor":"#FF4C4C","gsttextcolor":"#FFFFFF","gstbgcolor":"#48B3B6","getStartedLink":"1","percentageWidth":"90","titleS":"Create, promote, manage, and host","titlecolor":"#FFFFFF","descriptionS":"Your meetings, conferences & special events, etc.","descriptioncolor":"#FFFFFF","margin_top":"-21px","height":"420","animationSpeed":"3000","navigation":"1","isfullwidth":"1","title":"","nomobile":"0","name":"sesevent.main-slideshows"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
    if ($language == 'en')
    $coulmnName = 'bodysimple';
    else
    $coulmnName = $language . '_bodysimple';
    $array[$coulmnName] = '<h2 style="font-size: 34px;margin: 30px 0 10px;text-align: center;">Featured Upcoming Events</h2>';
  }

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 4,
    'params' => json_encode($array),
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.featured-sponsored',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 5,
    'params' => '{"viewType":"gridInside","order":"ongoingSPupcomming","criteria":"1","info":"most_liked","show_criteria":["title","location","socialSharing","likeButton","favouriteButton","listButton","buy"],"grid_title_truncation":"45","list_title_truncation":"45","height":"190","width":"284","limit_data":"4","title":"","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
    if ($language == 'en')
    $coulmnName = 'bodysimple';
    else
    $coulmnName = $language . '_bodysimple';
    $array[$coulmnName] = '<div style="text-align: center;margin-bottom:50px;margin-top:10px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'upcoming\');"  class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">See All Upcoming Events</a></div>

    <h2 style="font-size: 34px;margin-bottom: 0;text-align: center;padding-top: 30px;border-top: 1px solid #cdcdcd;">Browse Events by Top Categories</h2>"';
  }

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 6,
    'params' => json_encode($array),
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.event-category-icons',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 7,
    'params' => '{"titleC":"Browse by Top Categories","height":"200","width":"175","alignContent":"center","criteria":"most_event","show_criteria":["title","countEvents"],"limit_data":"12","title":"","nomobile":"0","name":"sesevent.event-category-icons"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
    if ($language == 'en')
    $coulmnName = 'bodysimple';
    else
    $coulmnName = $language . '_bodysimple';
    $array[$coulmnName] = '<div style="text-align: center;margin-bottom:50px;margin-top:-50px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'categories\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Browse All Categories</a></div>

    <h2 style="font-size: 34px;margin-bottom: -10px;margin-top: 10px;border-top-width: 1px;text-align: center;padding-top: 30px;border-color: #cdcdcd;">Popular Events</h2>';
  }

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 8,
   'params' => json_encode($array),
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.tabbed-events',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 9,
    'params' => '{"enableTabs":["grid"],"openViewType":"grid","tabOption":"vertical","show_item_count":"0","show_criteria":["favouriteButton","listButton","likeButton","socialSharing","like","location","comment","favourite","buy","rating","view","title","startenddate","category"],"limit_data":"3","show_limited_data":"no","pagging":"pagging","grid_title_truncation":"35","advgrid_title_truncation":"45","list_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"45","masonry_description_truncation":"45","grid_description_truncation":"45","pinboard_description_truncation":"45","height":"160","width":"140","photo_height":"225","photo_width":"312","info_height":"130","advgrid_width":"344","advgrid_height":"222","pinboard_width":"250","masonry_height":"250","search_type":["ongoingSPupcomming","week","weekend","month","mostSPjoined","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","featured","sponsored","verified"],"ongoingSPupcomming_order":"1","ongoingSPupcomming_label":"Upcoming & Ongoing","upcoming_order":"2","upcoming_label":"Upcoming","ongoing_order":"3","ongoing_label":"Ongoing","past_order":"4","past_label":"Past","week_order":"5","week_label":"This Week","weekend_order":"6","weekend_label":"This Weekend","month_order":"7","month_label":"This Month","mostSPjoined_order":"8","mostSPjoined_label":"Most Joined Events","recentlySPcreated_order":"9","recentlySPcreated_label":"Recently Created","mostSPviewed_order":"10","mostSPviewed_label":"Most Viewed","mostSPliked_order":"11","mostSPliked_label":"Most Liked","mostSPcommented_order":"12","mostSPcommented_label":"Most Commented","mostSPrated_order":"13","mostSPrated_label":"Most Rated","mostSPfavourite_order":"14","mostSPfavourite_label":"Most Favourite","featured_order":"15","featured_label":"Featured","sponsored_order":"16","sponsored_label":"Sponsored","verified_order":"17","verified_label":"Verified","title":"","nomobile":"0","name":"sesevent.tabbed-events"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
    if ($language == 'en')
    $coulmnName = 'bodysimple';
    else
    $coulmnName = $language . '_bodysimple';
    $array[$coulmnName] = '<div style="text-align: center;margin-bottom: 30px;margin-top:10px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'browse\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Browse All Events</a></div>';
  }

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 10,
    'params' => json_encode($array),
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'seshtmlbackground.paralex-video',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 11,
    'params' => '{"bannervideo":"public\/admin\/GLASS_BACKGROUND_CLUB_CLIPCHAMP_720p.mp4","paralextitle":"<p style=\"text-align: center; margin-top: -25px; margin-bottom: 20px;\"><span style=\"font-size: 20px; font-weight: bold; letter-spacing: 5px;\"><span class=\"h2\" style=\"color: #ff4c4c;\">Largest Event Listing Platform<\/span><br><\/span><\/p>\r\n<h2 style=\"font-size: 35px; font-weight: normal; margin-bottom: 10px;\">Discover events near you and around your city and much more...<\/h2>\r\n<div style=\"width: 10%; display: inline-block; float: none; margin-bottom: 30px; border-bottom: 4px solid #FF4C4C;\">&nbsp;<\/div>\r\n<ul style=\"vertical-align: top;\">\r\n<li style=\"display: inline-block; width: 30%; padding: 0px 30px; vertical-align: top;\"><img src=\"http:\/\/demo.socialenginesolutions.com\/public\/sesWysiwygPhotos\/joinevent.png\" alt=\"\"><br><span style=\"display: block; margin: 10px 0px; font-size: 25px; font-weight: bold;\">JOIN EVENTS<\/span>\r\n<p style=\"font-size: 15px;\">Plan your weekends and involve your friends with our Social Integrations.<\/p>\r\n<\/li>\r\n<li style=\"display: inline-block; width: 30%; padding: 0px 30px; vertical-align: top;\"><img src=\"http:\/\/demo.socialenginesolutions.com\/public\/sesWysiwygPhotos\/ticketicon.png\" alt=\"\"><br><span style=\"display: block; margin: 10px 0px; font-size: 25px; font-weight: bold;\">BUY TICKETS<\/span>\r\n<p style=\"font-size: 15px;\">Purchase tickets of your favorite Online Events without waiting in queue.<\/p>\r\n<\/li>\r\n<\/ul>","height":"500","title":"","nomobile":"0","name":"seshtmlbackground.paralex-video"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
    if ($language == 'en')
    $coulmnName = 'bodysimple';
    else
    $coulmnName = $language . '_bodysimple';
    $array[$coulmnName] = '<h2 style="font-size: 34px;margin-bottom: -10px;text-align: center;margin-top:30px;">Browse Events by Countries</h2>"';
  }

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 12,
    'params' => json_encode($array),
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.country-tabbed-events',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 13,
    'params' => '{"enableTabs":["advgrid"],"openViewType":"advgrid","tabOption":"default","show_criteria":["favouriteButton","likeButton","joinedcount","socialSharing","listButton","like","location","comment","favourite","buy","rating","view","title","startenddate","category"],"limit_data":"4","show_item_count":"0","show_limited_data":"yes","pagging":"button","advgrid_title_truncation":"20","grid_title_truncation":"45","list_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"45","masonry_description_truncation":"45","grid_description_truncation":"45","pinboard_description_truncation":"45","height":"160","width":"140","photo_height":"160","photo_width":"250","advgrid_width":"284","advgrid_height":"355","info_height":"160","pinboard_width":"250","masonry_height":"250","country":["United Kingdom","United States"],"criteria":"ongoingSPupcomming","title":"","nomobile":"0","name":"sesevent.country-tabbed-events"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
if ($language == 'en')
$coulmnName = 'bodysimple';
else
$coulmnName = $language . '_bodysimple';
$array[$coulmnName] = '<div style="text-align: center;margin-bottom:50px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'locations\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Events in All Locations</a></div>

 <h2 style="font-size: 34px;margin-bottom: -10px;margin-top: 10px;border-top-width: 1px;text-align: center;padding-top: 30px;border-color: #cdcdcd;">Popular Hosts</h2>';
  }

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 14,
    'params' => json_encode($array),
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.featured-sponsored-host',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 15,
    'params' => '{"viewType":"grid","criteria":"5","info":"most_viewed","show_criteria":["view","favourite","hostEventCount","host","socialSharing","favouriteButton"],"grid_title_truncation":"45","list_title_truncation":"45","height":"150","width":"158","limit_data":"7","contentInsideOutside":"in","mouseOver":"1","title":"","nomobile":"0","name":"sesevent.featured-sponsored-host"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
    if ($language == 'en')
    $coulmnName = 'bodysimple';
    else
    $coulmnName = $language . '_bodysimple';
    $array[$coulmnName] = '<div style="text-align: center;margin-bottom:20px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'browse-host\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Browse All Hosts</a></div>';
  }

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesbasic.simple-html-block',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => 16,
   'params' => json_encode($array),
  ));
}


// Event Home Page
$select = $db->select()
        ->from('engine4_core_pages')
        ->where('name = ?', 'sesevent_index_home')
        ->limit(1);
$info = $select->query()->fetch();
if (empty($info)) {
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_home',
      'displayname' => 'SES - Advanced Events - Event Home',
      'title' => 'Event Home',
      'description' => 'This is the event home page.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId('engine4_core_pages');

  //CONTAINERS
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'main',
      'parent_content_id' => null,
      'order' => 2,
      'params' => '',
  ));
  $container_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'middle',
      'parent_content_id' => $container_id,
      'order' => 6,
      'params' => '',
  ));
  $middle_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'top',
      'parent_content_id' => null,
      'order' => 1,
      'params' => '',
  ));
  $topcontainer_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'left',
      'parent_content_id' => $container_id,
      'order' => 4,
      'params' => '',
  ));
  $left_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'middle',
      'parent_content_id' => $topcontainer_id,
      'order' => 6,
      'params' => '',
  ));
  $topmiddle_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'right',
      'parent_content_id' => $container_id,
      'order' => 5,
      'params' => '',
  ));
  $right_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'parent_content_id' => $topmiddle_id,
      'order' => 3,
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.category-carousel',
      'parent_content_id' => $topmiddle_id,
      'order' => 4,
      'params' => '{"title_truncation_grid":"45","description_truncation_grid":"45","height":"300","speed":"300","width":"400","autoplay":"1","criteria":"most_event","show_criteria":["title","description","countEvents","socialshare"],"isfullwidth":"1","limit_data":"0","title":"Popular Categories","nomobile":"0","name":"sesevent.category-carousel"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-home-error',
      'parent_content_id' => $topmiddle_id,
      'order' => 5,
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.of-the-day',
      'parent_content_id' => $left_id,
      'order' => 8,
      'params' => '{"viewType":"gridOutside","show_criteria":["title","location","socialSharing","likeButton","favouriteButton","listButton"],"grid_title_truncation":"30","list_title_truncation":"45","height":"240","width":"180","title":"Event of the Day","nomobile":"0","name":"sesevent.of-the-day"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'parent_content_id' => $left_id,
      'order' => 9,
      'params' => '{"viewType":"gridInside","gridInsideOutside":"in","mouseOver":"over","order":"","criteria":"5","info":"most_joined","show_criteria":["title","location","host","joinedcount","socialSharing","likeButton","favouriteButton","listButton"],"grid_title_truncation":"25","list_title_truncation":"20","height":"180","width":"180","limit_data":"3","title":"Most Joined Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'parent_content_id' => $left_id,
      'order' => 10,
      'params' => '{"viewType":"list","gridInsideOutside":"in","mouseOver":"over","order":"ongoingSPupcomming","criteria":"5","info":"most_liked","show_criteria":["title","location","startenddate","socialSharing","likeButton","favouriteButton","listButton"],"grid_title_truncation":"45","list_title_truncation":"15","height":"180","width":"180","limit_data":"3","title":"Most Liked Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'parent_content_id' => $left_id,
      'order' => 11,
      'params' => '{"viewType":"list","order":"ongoingSPupcomming","criteria":"5","info":"most_commented","show_criteria":["title","location","startenddate","socialSharing","likeButton","favouriteButton","listButton"],"grid_title_truncation":"45","list_title_truncation":"15","height":"180","width":"180","limit_data":"3","title":"Most Commented Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.recently-viewed-item',
      'parent_content_id' => $topmiddle_id,
      'order' => 12,
      'params' => '{"view_type":"list","gridInsideOutside":"in","mouseOver":"over","criteria":"on_site","show_criteria":["title","location"],"grid_title_truncation":"45","list_title_truncation":"18","height":"180","width":"180","limit_data":"3","title":"Recently Viewed Events","nomobile":"0","name":"sesevent.recently-viewed-item"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.browse-search',
      'parent_content_id' => $middle_id,
      'order' => 14,
      'params' => '{"view_type":"horizontal","search_type":["recentlySPcreated","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","mostSPjoined","featured","sponsored","verified"],"view":["0","1","ongoing","past","week","weekend","future","month"],"default_search_type":"creation_date ASC","alphabet":"no","friend_show":"no","search_title":"yes","browse_by":"no","categories":"yes","location":"yes","kilometer_miles":"no","start_date":"no","end_date":"no","country":"no","state":"no","city":"no","zip":"no","venue":"no","title":"","nomobile":"0","name":"sesevent.browse-search"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored-carousel',
      'parent_content_id' => $middle_id,
      'order' => 15,
      'params' => '{"order":"ongoingSPupcomming","criteria":"6","info":"most_viewed","show_criteria":["title","location","startenddate","socialSharing","likeButton","favouriteButton","listButton"],"list_title_truncation":"20","gridInsideOutside":"in","mouseOver":"over","imageheight":"215","viewType":"horizontal","height":"215","width":"215","limit_data":"8","title":"Verified Events","nomobile":"0","name":"sesevent.featured-sponsored-carousel"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.tabbed-events',
      'parent_content_id' => $middle_id,
      'order' => 16,
      'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"advgrid","tabOption":"default","show_item_count":"0","show_criteria":["favouriteButton","listButton","likeButton","socialSharing","location","buy","title","startenddate","category","host","listdescription","commentpinboard"],"limit_data":" 8","show_limited_data":"no","pagging":"pagging","grid_title_truncation":"33","advgrid_title_truncation":"33","list_title_truncation":"45","pinboard_title_truncation":"33","masonry_title_truncation":"33","list_description_truncation":"45","masonry_description_truncation":"45","grid_description_truncation":"45","pinboard_description_truncation":"45","height":"160","width":"215","photo_height":"190","photo_width":"315","info_height":"135","advgrid_width":"318","advgrid_height":"360","pinboard_width":"325","masonry_height":"280","search_type":["ongoingSPupcomming","week","weekend","month","mostSPjoined"],"ongoingSPupcomming_order":"1","ongoingSPupcomming_label":"Upcoming & Ongoing","upcoming_order":"2","upcoming_label":"Upcoming","ongoing_order":"3","ongoing_label":"Ongoing","past_order":"4","past_label":"Past","week_order":"5","week_label":"This Week","weekend_order":"6","weekend_label":"This Weekend","month_order":"7","month_label":"This Month","mostSPjoined_order":"8","mostSPjoined_label":"Most Joined Events","recentlySPcreated_order":"9","recentlySPcreated_label":"Recently Created","mostSPviewed_order":"10","mostSPviewed_label":"Most Viewed","mostSPliked_order":"11","mostSPliked_label":"Most Liked","mostSPcommented_order":"12","mostSPcommented_label":"Most Commented","mostSPrated_order":"13","mostSPrated_label":"Most Rated","mostSPfavourite_order":"14","mostSPfavourite_label":"Most Favourite","featured_order":"15","featured_label":"Featured","sponsored_order":"16","sponsored_label":"Sponsored","verified_order":"17","verified_label":"Verified","title":"Popular Events","nomobile":"0","name":"sesevent.tabbed-events"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored-carousel',
      'parent_content_id' => $right_id,
      'order' => 18,
      'params' => '{"order":"ongoingSPupcomming","criteria":"2","info":"most_liked","show_criteria":["title","like","comment","view","favourite","location","host","startenddate","socialSharing","likeButton","favouriteButton","listButton"],"list_title_truncation":"16","gridInsideOutside":"out","mouseOver":"","imageheight":"180","viewType":"vertical","height":"305","width":"180","limit_data":"8","title":"Sponsored Events","nomobile":"0","name":"sesevent.featured-sponsored-carousel"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.tag-cloud',
      'parent_content_id' => $right_id,
      'order' => 19,
      'params' => '{"color":"#000000","text_height":"15","height":"150","itemCountPerPage":"25","title":"Popular Tags","nomobile":"0","name":"sesevent.tag-cloud"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'parent_content_id' => $right_id,
      'order' => 20,
      'params' => '{"viewType":"gridInside","gridInsideOutside":"in","mouseOver":"over","order":"ongoingSPupcomming","criteria":"1","info":"most_liked","show_criteria":["title","category","location","startenddate","socialSharing","likeButton","favouriteButton","listButton","buy"],"grid_title_truncation":"25","list_title_truncation":"45","height":"180","width":"180","limit_data":"3","title":"Featured Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'parent_content_id' => $right_id,
      'order' => 15,
      'params' => '{"viewType":"list","gridInsideOutside":"in","mouseOver":"over","order":"ongoingSPupcomming","criteria":"5","info":"favourite_count","show_criteria":["title","location","startenddate"],"grid_title_truncation":"45","list_title_truncation":"15","height":"180","width":"180","limit_data":"3","title":"Most Favourite Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'parent_content_id' => $right_id,
      'order' => 15,
      'params' => '{"viewType":"list","order":"","criteria":"5","info":"most_rated","show_criteria":["title","location","startenddate"],"grid_title_truncation":"45","list_title_truncation":"18","height":"180","width":"180","limit_data":"3","title":"Top Rated Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

}


// Event Browse Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_browse')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$page_id) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_browse',
      'displayname' => 'SES - Advanced Events - Event Browse Page',
      'title' => 'SES - Event Browse',
      'description' => 'This page lists events.',
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

  // Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 1,
  ));
  $main_right_id = $db->lastInsertId();

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 3,
  ));

  // Insert search
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-search',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 4,
      'params' => '{"view_type":"horizontal","search_type":["recentlySPcreated","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","mostSPjoined","featured","sponsored","verified"],"view":["0","1","ongoing","past","week","weekend","future","month"],"default_search_type":"view_count DESC","alphabet":"yes","friend_show":"yes","search_title":"yes","browse_by":"yes","categories":"yes","location":"yes","kilometer_miles":"yes","start_date":"yes","end_date":"yes","country":"yes","state":"yes","city":"yes","zip":"yes","venue":"yes","title":"Search Events","nomobile":"0","name":"sesevent.browse-search"}',
  ));

  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-events',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 7,
      'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"advgrid","show_criteria":["verifiedLabel","listButton","favouriteButton","likeButton","socialSharing","joinedcount","location","buy","title","startenddate","category","host","listdescription","pinboarddescription","commentpinboard"],"limit_data":"12","pagging":"button","order":"mostSPliked","show_item_count":"1","list_title_truncation":"60","grid_title_truncation":"45","advgrid_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"170","grid_description_truncation":"45","pinboard_description_truncation":"45","masonry_description_truncation":"45","height":"215","width":"300","photo_height":"290","photo_width":"296","info_height":"160","advgrid_height":"370","advgrid_width":"297","pinboard_width":"250","masonry_height":"350","title":"","nomobile":"0","name":"sesevent.browse-events"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.location-detect',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 9,
      'params' => '{"title":"","name":"sesevent.location-detect"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu-quick',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 10,
      'params' => '{"popup":["1"],"title":"","nomobile":"0","name":"sesevent.browse-menu-quick"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored-carousel',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 11,
      'params' => '{"order":"ongoingSPupcomming","criteria":"6","info":"most_liked","show_criteria":["title","category","location","startenddate","rating","featuredLabel","sponsoredLabel","socialSharing","likeButton","favouriteButton","listButton"],"list_title_truncation":"45","gridInsideOutside":"out","mouseOver":"over","imageheight":"225","viewType":"vertical","height":"250","width":"180","limit_data":"6","title":"Verified Events","nomobile":"0","name":"sesevent.featured-sponsored-carousel"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.recently-viewed-item',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 12,
      'params' => '{"view_type":"gridInside","gridInsideOutside":"out","mouseOver":"over","criteria":"on_site","show_criteria":["title","location","startenddate"],"grid_title_truncation":"15","list_title_truncation":"45","height":"180","width":"180","limit_data":"3","title":"Recently Viewed Events","nomobile":"0","name":"sesevent.recently-viewed-item"}',
  ));
}


//Event Upcoming Page

$page_id = $db->select()
->from('engine4_core_pages', 'page_id')
->where('name = ?', 'sesevent_index_upcoming')
->limit(1)
->query()
->fetchColumn();

// insert if it doesn't exist yet
if (!$page_id) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_upcoming',
      'displayname' => 'SES - Advanced Events - Event Upcoming Page',
      'title' => 'SES - Event Upcoming',
      'description' => 'This page lists events.',
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

  // Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 1,
  ));
  $main_right_id = $db->lastInsertId();

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 3,
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.location-detect',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 4,
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored-carousel',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 5,
      'params' => '{"order":"","criteria":"3","info":"most_liked","show_criteria":["title","category","like","comment","view","favourite","location","host","startenddate","rating","socialSharing","likeButton","favouriteButton","listButton"],"list_title_truncation":"21","gridInsideOutside":"in","mouseOver":"over","imageheight":"250","viewType":"horizontal","height":"250","width":"287","limit_data":"10","title":"Important Events","nomobile":"0","name":"sesevent.featured-sponsored-carousel"}',
  ));

  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-events',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 8,
      'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"grid","show_criteria":["featuredLabel","sponsoredLabel","verifiedLabel","listButton","favouriteButton","likeButton","socialSharing","joinedcount","location","buy","title","startenddate","category","host","listdescription","pinboarddescription","commentpinboard"],"limit_data":"12","pagging":"pagging","order":"mostSPliked","show_item_count":"1","list_title_truncation":"60","grid_title_truncation":"30","advgrid_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"170","grid_description_truncation":"45","pinboard_description_truncation":"45","masonry_description_truncation":"45","height":"215","width":"300","photo_height":"290","photo_width":"296","info_height":"160","advgrid_height":"370","advgrid_width":"297","pinboard_width":"250","masonry_height":"350","title":"","nomobile":"0","name":"sesevent.browse-events"}',
  ));

  // Insert search
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.of-the-day',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 10,
      'params' => '{"viewType":"gridInside","gridInsideOutside":"out","mouseOver":"over","show_criteria":["title","location","startenddate","socialSharing","likeButton","favouriteButton","listButton","buy"],"grid_title_truncation":"45","list_title_truncation":"45","height":"180","width":"180","title":" Event of the Day","nomobile":"0","name":"sesevent.of-the-day"}',
  ));

  // Insert gutter menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-search',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 11,
      'params' => '{"view_type":"vertical","search_type":["recentlySPcreated","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","mostSPjoined","featured","sponsored","verified"],"view":["0","1","ongoing","past","week","weekend","future","month"],"default_search_type":"creation_date ASC","alphabet":"no","friend_show":"yes","search_title":"yes","browse_by":"yes","categories":"yes","location":"yes","kilometer_miles":"yes","start_date":"yes","end_date":"yes","country":"no","state":"no","city":"no","zip":"no","venue":"no","title":"","nomobile":"0","name":"sesevent.browse-search"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu-quick',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 12,
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 13,
      'params' => '{"viewType":"list","gridInsideOutside":"in","mouseOver":"over","order":"ongoingSPupcomming","criteria":"5","info":"most_joined","show_criteria":["title","like","comment","view","favourite","location","startenddate","rating","featuredLabel","sponsoredLabel","verifiedLabel","socialSharing","likeButton","favouriteButton","listButton","buy"],"grid_title_truncation":"20","list_title_truncation":"17","height":"180","width":"180","limit_data":"3","title":"Most Joined Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 14,
      'params' => '{"viewType":"list","gridInsideOutside":"in","mouseOver":"over","order":"ongoingSPupcomming","criteria":"5","info":"most_liked","show_criteria":["title","like","comment","view","favourite","location","joinedcount","startenddate","socialSharing","likeButton","favouriteButton","listButton"],"grid_title_truncation":"45","list_title_truncation":"17","height":"180","width":"180","limit_data":"3","title":"Most Liked Events","nomobile":"0","name":"sesevent.featured-sponsored"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.recently-viewed-item',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 15,
      'params' => '{"view_type":"list","gridInsideOutside":"in","mouseOver":"over","criteria":"by_me","show_criteria":["title","location","joinedcount","startenddate"],"grid_title_truncation":"45","list_title_truncation":"17","height":"180","width":"180","limit_data":"2","title":"Recently Viewed by You","nomobile":"0","name":"sesevent.recently-viewed-item"}',
  ));
}

//Event Past Page
$page_id = $db->select()
->from('engine4_core_pages', 'page_id')
->where('name = ?', 'sesevent_index_past')
->limit(1)
->query()
->fetchColumn();

// insert if it doesn't exist yet
if (!$page_id) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_past',
      'displayname' => 'SES - Advanced Events - Event Past Page',
      'title' => 'SES - Event Past',
      'description' => 'This page lists events.',
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

  // Insert main-right
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 1,
  ));
  $main_right_id = $db->lastInsertId();

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 3,
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.location-detect',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 4,
  ));

  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-events',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 7,
      'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"list","show_criteria":["listButton","favouriteButton","likeButton","socialSharing","joinedcount","location","buy","title","startenddate","host","listdescription","pinboarddescription","commentpinboard"],"limit_data":"6","pagging":"auto_load","order":null,"show_item_count":"1","list_title_truncation":"60","grid_title_truncation":"30","advgrid_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"170","grid_description_truncation":"45","pinboard_description_truncation":"45","masonry_description_truncation":"45","height":"215","width":"300","photo_height":"219","photo_width":"296","info_height":"160","advgrid_height":"315","advgrid_width":"297","pinboard_width":"250","masonry_height":"350","title":"","nomobile":"0","name":"sesevent.browse-events"}',
  ));

  // Insert search
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-search',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 9,
      'params' => '{"view_type":"vertical","search_type":["recentlySPcreated","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","mostSPjoined","featured","sponsored","verified"],"view":["0","1","ongoing","past","week","weekend","future","month"],"default_search_type":"creation_date ASC","alphabet":"no","friend_show":"yes","search_title":"yes","browse_by":"yes","categories":"yes","location":"yes","kilometer_miles":"yes","start_date":"no","end_date":"no","country":"no","state":"no","city":"no","zip":"no","venue":"no","title":"","nomobile":"0","name":"sesevent.browse-search"}',
  ));

  // Insert gutter menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu-quick',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => 10,
  ));
}

//Browse List Page
$widgetOrder = 1;
$select = $db->select()
        ->from('engine4_core_pages')
        ->where('name = ?', 'sesevent_list_browse')
        ->limit(1);
$info = $select->query()->fetch();
if (empty($info)) {
  $widgetOrder = 1;
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_list_browse',
      'displayname' => 'SES - Advanced Events - Browse Event Lists Page',
      'title' => 'Browse Event Lists Page',
      'description' => 'This is the event lists page.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId('engine4_core_pages');
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'main',
      'parent_content_id' => null,
      'order' => 2,
  ));
  $container_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'middle',
      'parent_content_id' => $container_id,
      'order' => 6,
  ));
  $middle_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'top',
      'parent_content_id' => null,
      'order' => 1,
  ));
  $topcontainer_id = $db->lastInsertId('engine4_core_content');


  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'middle',
      'parent_content_id' => $topcontainer_id,
      'order' => 6,
  ));
  $topmiddle_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'right',
      'parent_content_id' => $container_id,
      'order' => 5,
  ));
  $right_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'parent_content_id' => $topmiddle_id,
      'order' => $widgetOrder++,
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.popular-lists',
      'parent_content_id' => $topmiddle_id,
      'order' => $widgetOrder++,
      'params' => '{"showOptionsType":"all","showType":"carouselview","popularity":"featured","information":["viewCount","title","postedby","share","eventcount","favouriteButton","favouriteCount","likeButton","socialSharing","likeCount","showEventsList"],"viewType":"horizontal","height":"200","width":"285","limit":"10","title":"Featured Lists","nomobile":"0","name":"sesevent.popular-lists"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.browse-lists',
      'parent_content_id' => $middle_id,
      'order' => $widgetOrder++,
      'params' => '{"popularity":"event_count","listCount":"1","Type":"pagging","information":["viewCount","title","postedby","share","eventcount","favouriteButton","favouriteCount","featuredLabel","sponsoredLabel","likeButton","socialSharing","likeCount","showEventsList"],"height":"200","width":"297","titletruncation":"16","itemCount":"12","title":"","nomobile":"0","name":"sesevent.browse-lists"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.of-the-day-list',
      'parent_content_id' => $right_id,
      'order' => $widgetOrder++,
      'params' => '{"information":["viewCount","title","postedby","share","eventcount","favouriteButton","favouriteCount","featuredLabel","sponsoredLabel","likeButton","socialSharing","likeCount","showEventsList"],"height":"200","titletruncation":"16","title":"List of the Day","nomobile":"0","name":"sesevent.of-the-day-list"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.list-browse-search',
      'parent_content_id' => $right_id,
      'order' => $widgetOrder++,
      'params' => '{"searchOptionsType":["searchBox","view","show"],"title":"","nomobile":"0","name":"sesevent.list-browse-search"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.popular-lists',
      'parent_content_id' => $right_id,
      'order' => $widgetOrder++,
      'params' => '{"showOptionsType":"all","showType":"carouselview","popularity":"creation_date","information":["title","postedby","share","eventcount","favouriteButton","favouriteCount","likeButton","socialSharing","likeCount","showEventsList"],"viewType":"vertical","height":"190","width":"250","limit":"7","title":"Sponsored Lists","nomobile":"0","name":"sesevent.popular-lists"}',
  ));

}


//Browse Tags Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_tags')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (!$page_id) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_tags',
      'displayname' => 'SES - Advanced Events - Browse Tags Page',
      'title' => 'Events Browse Tags Page',
      'description' => 'This page is the browse events tag page.',
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
      'order' => 6
  ));
  $top_middle_id = $db->lastInsertId();
  // Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 6
  ));
  $main_middle_id = $db->lastInsertId();
  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 3,
  ));
  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.tag-events',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 4,
  ));
}

//Manage Event Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_manage')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$page_id) {
  $widgetOrder = 1;
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_manage',
      'displayname' => 'SES - Advanced Events - Event Manage Page',
      'title' => 'My Events',
      'description' => 'This page lists a user\'s events.',
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

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => $widgetOrder++,
  ));

  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.manage-events',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"advgrid","show_criteria":["favouriteButton","listButton","likeButton","joinedcount","socialSharing","like","location","comment","favourite","buy","rating","view","title","startenddate","category","by","host","listdescription","griddescription","pinboarddescription","commentpinboard","eventcount","share","showEventsList"],"limit_data":"20","pagging":"button","advgrid_title_truncation":"25","grid_title_truncation":"25","list_title_truncation":"50","pinboard_title_truncation":"25","masonry_title_truncation":"25","list_description_truncation":"100","masonry_description_truncation":"45","grid_description_truncation":"38","pinboard_description_truncation":"40","width_lists":"308","width_hosts":"310","height_hosts":"220","advgrid_width":"308","advgrid_height":"395","height":"200","width":"280","photo_height":"150","photo_width":"308","info_height":"215","pinboard_width":"250","masonry_height":"330","search_type":["all","joinedEvents","hostedEvents","save","like","favourite","featured","sponsored","verified","lists","hosts"],"all_order":"1","all_label":"Owned Events","joinedEvents_order":"2","joinedEvents_label":"Joined Events Only","hostedEvents_order":"3","hostedEvents_label":"Hosted Events Only","save_order":"4","save_label":"Saved Events","like_order":"5","like_label":"Liked Events","favourite_order":"6","favourite_label":"Favourite Events","featured_order":"7","featured_label":"Featured Events","sponsored_order":"8","sponsored_label":"Sponsored Events","verified_order":"9","verified_label":"Verified Events","lists_order":"10","lists_label":"My Lists","hosts_order":"11","hosts_label":"My Hosts","title":"","nomobile":"0","name":"sesevent.manage-events"}',
  ));
}


//Event Category Browse Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_category_browse')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (!$page_id) {
  $widgetOrder = 1;
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_category_browse',
      'displayname' => 'SES - Advanced Events - Browse Categories Page',
      'title' => 'Browse Categories Page',
      'description' => 'This page is the browse events categories page.',
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
      'order' => 6
  ));
  $top_middle_id = $db->lastInsertId();
  // Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 6
  ));
  $main_middle_id = $db->lastInsertId();

	$db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'parent_content_id' => $top_middle_id,
      'order' => $widgetOrder++,
      'params' => '',
  ));
	$db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.banner-category',
      'parent_content_id' => $top_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur massa neque, ullamcorper at justo eu, cursus sodales ante.","sesevent_categorycover_photo":"public\/admin\/event_category_banner.jpg","title":"Event Categories","nomobile":"0","name":"sesevent.banner-category"}',
  ));

  $array['show_content'] = 1;
  $array['title'] = '';
  $array['nomobile'] = 0;
  $array['name'] = 'sesbasic.simple-html-block';

  foreach ($languageList as $key => $language) {
    if ($language == 'en')
    $coulmnName = 'bodysimple';
    else
    $coulmnName = $language . '_bodysimple';
    $array[$coulmnName] = '<div style="font-size:30px;margin-bottom: 15px;margin:15px">All Categories</div>';
  }

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesbasic.simple-html-block',
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => json_encode($array),
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-category',
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"height":"140","width":"285","alignContent":"center","criteria":"most_event","show_criteria":["title","countEvents"],"title":"","nomobile":"0","name":"sesevent.event-category"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.category-associate-event',
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"view_type":"2","show_criteria":["title","by","description","like","view","comment","favourite","featuredLabel","sponsoredLabel","albumPhoto","joinedcount","photoThumbnail","favouriteButton","likeButton","listButton","socialSharing","location","startenddate"],"photo_height":"190","photo_width":"282","info_height":"180","pagging":"button","count_event":"1","criteria":"most_event","category_limit":"5","event_limit":"4","seemore_text":"+ See all [category_name]","allignment_seeall":"left","title_truncation":"20","description_truncation":"40","title":"","nomobile":"0","name":"sesevent.category-associate-event"}',
  ));
}


//Event Location Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_locations')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (!$page_id) {
  $widgetOrder = 1;
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_locations',
      'displayname' => 'SES - Advanced Events - Events Location Page',
      'title' => 'Event Locations',
      'description' => 'This page show event locations.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId();

  //Insert top
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $page_id,
      'order' => 1,
  ));
  $top_id = $db->lastInsertId();

  //Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
      'order' => 2,
  ));
  $main_id = $db->lastInsertId();

  //Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $top_id,
  ));
  $top_middle_id = $db->lastInsertId();

  //Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();

  //Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-search',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"view_type":"horizontal","search_type":["recentlySPcreated","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","mostSPjoined","featured","sponsored","verified"],"view":["0","1","ongoing","past","week","weekend","future","month"],"default_search_type":"creation_date ASC","show_advanced_search":"yes","alphabet":"no","friend_show":"yes","search_title":"yes","browse_by":"yes","categories":"yes","location":"yes","kilometer_miles":"yes","start_date":"yes","end_date":"yes","country":"yes","state":"yes","city":"yes","zip":"yes","venue":"yes","title":"","nomobile":"0","name":"sesevent.browse-search"}',
  ));
  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.event-location',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"location":"","lat":"","lng":"","show_criteria":["featuredLabel","sponsoredLabel","verifiedLabel","location","listButton","favouriteButton","likeButton","socialSharing","like","comment","favourite","view","by","host"],"location-data":null,"title":"","nomobile":"0","name":"sesevent.event-location"}',
  ));
}


//Event Calender Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_calender')
        ->limit(1)
        ->query()
        ->fetchColumn();

// insert if it doesn't exist yet
if (!$page_id) {
  $widgetOrder = 1;
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_calender',
      'displayname' => 'SES - Advanced Events - Event Calender Page',
      'title' => 'SES - Event Calender',
      'description' => 'This page lists events.',
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

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => $widgetOrder++,
  ));

  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.calender',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"viewmore":"3","loadData":"viewmore","title":"","nomobile":"0","name":"sesevent.calender"}',
  ));
}


//Browse Hosts Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_browse-host')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (!$page_id) {
  $widgetOrder = 1;
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_browse-host',
      'displayname' => 'SES - Advanced Events - Browse Hosts Page',
      'title' => 'Browse Hosts',
      'description' => 'This page display lists of artists.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId();
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'top',
      'page_id' => $page_id,
      'order' => 1,
  ));
  $top_id = $db->lastInsertId();
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
      'order' => 2,
  ));
  $main_id = $db->lastInsertId();
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $top_id,
  ));
  $top_middle_id = $db->lastInsertId();
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'right',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 1,
  ));
  $main_right_id = $db->lastInsertId();

  //Top Main
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => $widgetOrder++,
  ));

  //Middle
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesspectromedia.banner',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"is_full":"0","is_pattern":"0","banner_image":"public\/admin\/host-banner.png","banner_title":"","title_button_color":"FFFFFF","description":"","description_button_color":"FFFFFF","button1":"0","button1_text":"Button - 1","button1_text_color":"0295FF","button1_color":"FFFFFF","button1_mouseover_color":"EEEEEE","button1_link":"","button2":"0","button2_text":"Button - 2","button2_text_color":"FFFFFF","button2_color":"0295FF","button2_mouseover_color":"067FDE","button2_link":"","button3":"0","button3_text":"Button - 3","button3_text_color":"FFFFFF","button3_color":"F25B3B","button3_mouseover_color":"EA350F","button3_link":"","height":"280","title":"","nomobile":"0","name":"sesspectromedia.banner"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-hosts',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => $widgetOrder++,
      'params' => '{"paginationType":"pagging","title_truncation":"45","popularity":"view_count","information":["featuredLabel","sponsoredLabel","verifiedLabel","view","favourite","follow","hostEventCount","favouriteButton","socialSharing"],"height":"200","width":"220","contentInsideOutside":"out","mouseOver":"0","itemCount":"20","title":"","nomobile":"0","name":"sesevent.browse-hosts"}',
  ));

  //Right
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.hostoftheday',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => $widgetOrder++,
      'params' => '{"infoshow":["verified","view","favourite","follow","hostEventCount","favouriteButton","socialSharing"],"height":"200","width":"200","contentInsideOutside":"in","mouseOver":"1","title":"Host of the Day","nomobile":"0","name":"sesevent.hostoftheday"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.host-browse-search',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => $widgetOrder++,
      'params' => '{"searchOptionsType":["searchBox","show"],"title":"","nomobile":"0","name":"sesevent.host-browse-search"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored-host',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => $widgetOrder++,
      'params' => '{"viewType":"list","criteria":"5","info":"favourite_count","show_criteria":["view","favourite","hostEventCount","host","featuredLabel","sponsoredLabel","verifiedLabel","socialSharing","favouriteButton"],"grid_title_truncation":"45","list_title_truncation":"45","height":"180","width":"180","limit_data":"3","contentInsideOutside":"in","mouseOver":"1","title":"Most Favourite Hosts","nomobile":"0","name":"sesevent.featured-sponsored-host"}',
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.featured-sponsored-host',
      'page_id' => $page_id,
      'parent_content_id' => $main_right_id,
      'order' => $widgetOrder++,
      'params' => '{"viewType":"grid","criteria":"5","info":"most_viewed","show_criteria":["view","favourite","hostEventCount","host","featuredLabel","sponsoredLabel","verifiedLabel","socialSharing","favouriteButton"],"grid_title_truncation":"45","list_title_truncation":"45","height":"180","width":"180","limit_data":"3","contentInsideOutside":"in","mouseOver":"0","title":"Most Viewed Hosts","nomobile":"0","name":"sesevent.featured-sponsored-host"}',
  ));
}

//Review view Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'seseventreview_index_view')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (!$page_id) {
  $widgetOrder = 1;
  $db->insert('engine4_core_pages', array(
      'name' => 'seseventreview_index_view',
      'displayname' => 'SES - Advanced Events - Review View Page',
      'title' => 'Event Review View',
      'description' => 'This page displays a review entry.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId();

  // Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
  ));
  $main_id = $db->lastInsertId();

  // Insert left
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'left',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 1,
  ));
  $left_id = $db->lastInsertId();

  // Insert middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 2,
  ));
  $middle_id = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'seseventreview.owner-photo',
      'parent_content_id' => $left_id,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'seseventreview.profile-options',
      'parent_content_id' => $left_id,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'seseventreview.breadcrumb',
      'parent_content_id' => $middle_id,
      'order' => $widgetOrder++,
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'seseventreview.profile-review',
      'parent_content_id' => $middle_id,
      'order' => $widgetOrder++,
      'params' => '{"stats":["featured","sponsored","likeCount","commentCount","viewCount","title","pros","cons","description","recommended","postedin","creationDate","parameter","rating","customfields"],"title":"","nomobile":"0","name":"seseventreview.profile-review"}',
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'core.comments',
      'parent_content_id' => $middle_id,
      'order' => $widgetOrder++,
      'params' => '{"title":"Comments"}',
  ));
}

//Photo View Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_photo_view')
        ->limit(1)
        ->query()
        ->fetchColumn();

if (!$page_id) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_photo_view',
      'displayname' => 'SES - Advanced Events - Photo View Page',
      'title' => 'Album Photo View',
      'description' => 'This page displays an album\'s photo.',
      'provides' => 'subject=sesevent_photo',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId();

  // Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
      'order' => 2
  ));
  $main_id = $db->lastInsertId();

  // Insert middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 6,
  ));
  $middle_id = $db->lastInsertId();

  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.breadcrumb',
      'page_id' => $page_id,
      'parent_content_id' => $middle_id,
      'order' => 3,
  ));
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.photo-view-page',
      'page_id' => $page_id,
      'parent_content_id' => $middle_id,
      'order' => 4,
      'params' => '{"title":"","nomobile":"0","name":"sesevent.photo-view-page"}'
  ));
}

//Event List View Page
$select = $db->select()
        ->from('engine4_core_pages')
        ->where('name = ?', 'sesevent_list_view')
        ->limit(1);
$info = $select->query()->fetch();
if (empty($info)) {

  $widgetOrder = 1;
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_list_view',
      'displayname' => 'SES - Advanced Events - Event List View Page',
      'title' => 'Event List View Page',
      'description' => 'This is the event list view page.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId('engine4_core_pages');

  //CONTAINERS
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'main',
      'parent_content_id' => null,
      'order' => 2,
      'params' => '',
  ));
  $container_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'middle',
      'parent_content_id' => $container_id,
      'order' => 6,
      'params' => '',
  ));
  $middle_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'top',
      'parent_content_id' => null,
      'order' => 1,
      'params' => '',
  ));
  $topcontainer_id = $db->lastInsertId('engine4_core_content');


  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'middle',
      'parent_content_id' => $topcontainer_id,
      'order' => 6,
      'params' => '',
  ));
  $topmiddle_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.breadcrumb',
      'parent_content_id' => $topmiddle_id,
      'order' => $widgetOrder++,
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.list-view-page',
      'parent_content_id' => $topmiddle_id,
      'order' => $widgetOrder++,
      'params' => '{"informationList":["editButton","deleteButton","viewCountList","eventCountList","descriptionList","postedby","shareList","favouriteButtonList","favouriteCountList","likeButtonList","featuredLabelList","sponsoredLabelList","socialSharingList","likeCountList","reportList"],"title":"","nomobile":"0","name":"sesevent.list-view-page"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.host-speaker-events',
      'parent_content_id' => $middle_id,
      'order' => $widgetOrder++,
      'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"list","tabOption":"filter","show_item_count":"0","show_criteria":["verifiedLabel","favouriteButton","listButton","likeButton","socialSharing","joinedcount","like","location","comment","favourite","buy","rating","view","title","startenddate","category","by","host","listdescription","griddescription","pinboarddescription","commentpinboard"],"limit_data":"9","show_limited_data":"no","pagging":"button","grid_title_truncation":"45","advgrid_title_truncation":"45","list_title_truncation":"70","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"100","masonry_description_truncation":"45","grid_description_truncation":"36","pinboard_description_truncation":"45","height":"180","width":"270","photo_height":"200","photo_width":"282","info_height":"216","advgrid_width":"382","advgrid_height":"400","pinboard_width":"250","masonry_height":"400","search_type":["ongoingSPupcomming","upcoming","ongoing","past","week","weekend","month","mostSPjoined"],"ongoingSPupcomming_order":"1","ongoingSPupcomming_label":"Upcoming & Ongoing","upcoming_order":"2","upcoming_label":"Upcoming","ongoing_order":"3","ongoing_label":"Ongoing","past_order":"4","past_label":"Past","week_order":"5","week_label":"This Week","weekend_order":"6","weekend_label":"This Weekend","month_order":"7","month_label":"This Month","mostSPjoined_order":"8","mostSPjoined_label":"Most Joined Events","recentlySPcreated_order":"9","recentlySPcreated_label":"Recently Created","mostSPviewed_order":"10","mostSPviewed_label":"Most Viewed","mostSPliked_order":"11","mostSPliked_label":"Most Liked","mostSPcommented_order":"12","mostSPcommented_label":"Most Commented","mostSPrated_order":"13","mostSPrated_label":"Most Rated","mostSPfavourite_order":"14","mostSPfavourite_label":"Most Favourite","featured_order":"15","featured_label":"Featured","sponsored_order":"16","sponsored_label":"Sponsored","verified_order":"17","verified_label":"Verified","title":"","nomobile":"0","name":"sesevent.host-speaker-events"}',
  ));
}

//Event Album View Page
$page_id = $db->select()
  ->from('engine4_core_pages', 'page_id')
  ->where('name = ?', 'sesevent_album_view')
  ->limit(1)
  ->query()
  ->fetchColumn();
if (!$page_id) {
	  // Insert page
	  $db->insert('engine4_core_pages', array(
	'name' => 'sesevent_album_view',
	'displayname' => 'SES - Advanced Event - Album View Page',
	'title' => 'Album View Page',
	'description' => 'This page displays an album.',
	'provides' => 'subject=album',
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
	'order' => 6,
	  ));
	  $main_middle_id = $db->lastInsertId();

	  $db->insert('engine4_core_content', array(
	'type' => 'widget',
	'name' => 'sesevent.breadcrumb',
	'page_id' => $page_id,
	'parent_content_id' => $main_middle_id,
	'order' => 3,
	'params' => ''
	  ));

	  $db->insert('engine4_core_content', array(
	'type' => 'widget',
	'name' => 'sesevent.album-view-page',
	'page_id' => $page_id,
	'parent_content_id' => $main_middle_id,
	'order' => 4,
	'params' => ''
	  ));
}

//Event Host Profile Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_viewhost')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (!$page_id) {
  $widgetOrder = 1;
  //Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_viewhost',
      'displayname' => 'SES - Advanced Events - Event Host Profile Page',
      'title' => 'Event Host Profile',
      'description' => 'This is the event host profile page.',
      'custom' => 0,
  ));
  $page_id = $db->lastInsertId();

  //Insert main
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
      'order' => 2,
  ));
  $main_id = $db->lastInsertId();

  //Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 2,
  ));
  $main_middle_id = $db->lastInsertId();


  //Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.breadcrumb',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 4,
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.profile-nonsitehost',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 5,
      'params' => '{"infoshow":["profilePhoto","displayname","detaildescription","phone","email","view","favourite","hostEventCount","follow","website","facebook","twitter","linkdin","googleplus","verifiedLabel","followButton","favouriteButton","socialSharing"],"descriptionText":"About Host","title":"","nomobile":"0","name":"sesevent.profile-nonsitehost"}',
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.host-speaker-events',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 6,
      'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"pinboard","tabOption":"filter","show_item_count":"1","show_criteria":["featuredLabel","sponsoredLabel","verifiedLabel","favouriteButton","listButton","likeButton","socialSharing","joinedcount","like","location","comment","favourite","buy","rating","view","title","startenddate","category","by","host","listdescription","griddescription","pinboarddescription","commentpinboard"],"limit_data":"10","show_limited_data":"no","pagging":"button","grid_title_truncation":"25","advgrid_title_truncation":"45","list_title_truncation":"60","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"100","masonry_description_truncation":"45","grid_description_truncation":"45","pinboard_description_truncation":"45","height":"200","width":"280","photo_height":"210","photo_width":"284","info_height":"220","advgrid_width":"284","advgrid_height":"370","pinboard_width":"250","masonry_height":"250","search_type":["ongoingSPupcomming","past","week","weekend","month","recentlySPcreated","featured","sponsored","verified"],"ongoingSPupcomming_order":"2","ongoingSPupcomming_label":"Upcoming & Ongoing","upcoming_order":"9","upcoming_label":"Upcoming","ongoing_order":"4","ongoing_label":"Ongoing","past_order":"3","past_label":"Past","week_order":"5","week_label":"This Week","weekend_order":"6","weekend_label":"This Weekend","month_order":"7","month_label":"This Month","mostSPjoined_order":"8","mostSPjoined_label":"Most Joined Events","recentlySPcreated_order":"1","recentlySPcreated_label":"All Hosted Events","mostSPviewed_order":"10","mostSPviewed_label":"Most Viewed","mostSPliked_order":"11","mostSPliked_label":"Most Liked","mostSPcommented_order":"12","mostSPcommented_label":"Most Commented","mostSPrated_order":"13","mostSPrated_label":"Most Rated","mostSPfavourite_order":"14","mostSPfavourite_label":"Most Favourite","featured_order":"15","featured_label":"Featured","sponsored_order":"16","sponsored_label":"Sponsored","verified_order":"17","verified_label":"Verified","title":"","nomobile":"0","name":"sesevent.host-speaker-events"}',
  ));
}

//Event Category View Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_category_index')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (!$page_id) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_category_index',
      'displayname' => 'SES - Advanced Events - Category View Page',
      'title' => 'Category View Page',
      'description' => 'This page is the category view page.',
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
  // Insert main-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 6
  ));
  $main_middle_id = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 4,
  ));

  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.category-view',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 5,
      'params' => '{"show_subcat":"1","show_subcatcriteria":["title","icon","countEvents"],"heightSubcat":"140","widthSubcat":"226","dummy1":null,"show_criteria":["featuredLabel","sponsoredLabel","like","comment","joinedcount","startenddate","rating","view","title","by","favourite"],"pagging":"pagging","event_limit":"9","height":"160","width":"160","title":"","nomobile":"0","name":"sesevent.category-view"}',
  ));
}


//Event Create Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'sesevent_index_create')
        ->limit(1)
        ->query()
        ->fetchColumn();
// insert if it doesn't exist yet
if (!$page_id) {
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_index_create',
      'displayname' => 'SES - Advanced Events - Event Create Page',
      'title' => 'SES - Event Create',
      'description' => 'This page allows users to create events.',
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

  // Insert menu
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesevent.browse-menu',
      'page_id' => $page_id,
      'parent_content_id' => $top_middle_id,
      'order' => 1,
  ));

  // Insert content
  $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'core.content',
      'page_id' => $page_id,
      'parent_content_id' => $main_middle_id,
      'order' => 1,
  ));
}

 // Event Profile Page

$select = new Zend_Db_Select($db);
$hasWidget = $select
	->from('engine4_core_pages', new Zend_Db_Expr('TRUE'))
	->where('name = ?', 'sesevent_profile_index')
	->limit(1)
	->query()
	->fetchColumn()
;

// Add it
if (empty($hasWidget)) {

  $db->insert('engine4_core_pages', array(
      'name' => 'sesevent_profile_index',
      'displayname' => 'SES - Advanced Events - Event Profile',
      'title' => 'SES - Event Profile',
      'description' => 'This is the profile for an event.',
      'custom' => 0,
      'provides' => 'subject=event',
  ));
  $page_id = $db->lastInsertId('engine4_core_pages');

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
  $container_id = $db->lastInsertId();

  // Insert top-middle
  $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $top_id,
      'order' => 6,
  ));
  $top_middle_id = $db->lastInsertId();

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'middle',
      'parent_content_id' => $container_id,
      'order' => 6,
  ));
  $middle_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'container',
      'name' => 'right',
      'parent_content_id' => $container_id,
      'order' => 5,
  ));
  $right_id = $db->lastInsertId('engine4_core_content');

  // middle column
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'core.container-tabs',
      'parent_content_id' => $middle_id,
      'order' => 7,
      'params' => '{"max":"7","title":"","nomobile":"0","name":"core.container-tabs"}',
  ));
  $tab_id = $db->lastInsertId('engine4_core_content');

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-cover',
      'parent_content_id' => $top_middle_id,
      'order' => 4,
      'params' => '{"showCriterias":["title","mainPhoto","hostedby","startEndDate","location","commentCount","likeCount","favouriteCount","viewCount","guestCount","advShare","likeBtn","favouriteBtn","socialShare","listBtn","join","addtocalender","bookNow"],"photo":"mPhoto","fullwidth":"1","padding":"-21","showCalander":["google","yahoo","msn","outlook","ical"],"height":"450","optionInsideOutside":"1","title":"","nomobile":"0","name":"sesevent.event-cover"}',
  ));

  // tabs
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-info',
      'parent_content_id' => $tab_id,
      'order' => 8,
      'params' => '{"title":"Info","name":"sesevent.event-info"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'activity.feed',
      'parent_content_id' => $tab_id,
      'order' => 9,
      'params' => '{"title":"Updates"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-overview',
      'parent_content_id' => $tab_id,
      'order' => 10,
      'params' => '{"title":"Overview","name":"sesevent.event-overview"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.profile-members',
      'parent_content_id' => $tab_id,
      'order' => 11,
      'params' => '{"title":"Guests","titleCount":true}',
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.profile-photos',
      'parent_content_id' => $tab_id,
      'order' => 12,
      'params' => '{"title":"Albums","titleCount":false,"load_content":"auto_load","sort":"recentlySPcreated","insideOutside":"inside","fixHover":"fix","show_criteria":["like","comment","view","title","by","socialSharing","photoCount","likeButton"],"title_truncation":"45","limit_data":"20","height":"200","width":"236","nomobile":"0","itemCountPerPage":"10","name":"sesevent.profile-photos"}',
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-map',
      'parent_content_id' => $tab_id,
      'order' => 13,
      'params' => '{"title":"Location","titleCount":true,"name":"sesevent.event-map"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.profile-discussions',
      'parent_content_id' => $tab_id,
      'order' => 14,
      'params' => '{"title":"Discussions","titleCount":true}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'seseventvideo.profile-videos',
      'parent_content_id' => $tab_id,
      'order' => 15,
      'params' => '{"enableTabs":["list","grid","pinboard"],"openViewType":"list","show_criteria":["featuredLabel","sponsoredLabel","hotLabel","watchLater","favouriteButton","likeButton","socialSharing","like","comment","favourite","rating","view","title","by","duration","description"],"pagging":"auto_load","title_truncation_list":"45","title_truncation_grid":"45","DescriptionTruncationList":"45","height":"160px","width":"140px","limit_data":"20","title":"Videos","nomobile":"0","name":"seseventvideo.profile-videos"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'seseventreview.content-profile-reviews',
      'parent_content_id' => $tab_id,
      'order' => 16,
      'params' => '{"stats":["likeCount","commentCount","viewCount","title","share","report","pros","cons","description","recommended","postedBy","parameter","creationDate","rating"],"title":"Reviews","nomobile":"0","name":"seseventreview.content-profile-reviews"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-termsandconditions',
      'parent_content_id' => $tab_id,
      'order' => 17,
      'params' => '{"title":"Terms & Conditions","titleCount":true,"name":"sesevent.event-termsandconditions"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'core.profile-links',
      'parent_content_id' => $tab_id,
      'order' => 18,
      'params' => '{"title":"Links","titleCount":true}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.recently-viewed-item',
      'parent_content_id' => $middle_id,
      'order' => 19,
      'params' => '{"view_type":"gridInside","gridInsideOutside":"in","mouseOver":"","criteria":"by_me","show_criteria":["title","location"],"grid_title_truncation":"18","list_title_truncation":"45","height":"180","width":"220","limit_data":"4","title":"Recently Viewed by You","nomobile":"0","name":"sesevent.recently-viewed-item"}',
  ));

  // right column
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-profile-status',
      'parent_content_id' => $right_id,
      'order' => 21,
      'params' => '{"title":"","name":"sesevent.event-profile-status"}',
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.save-button',
      'parent_content_id' => $right_id,
      'order' => 22,
      'params' => '{"title":"","name":"sesevent.save-button"}',
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-label',
      'parent_content_id' => $right_id,
      'order' => 23,
      'params' => '{"option":["featured","sponsored","verified","offtheday"],"title":"","nomobile":"0","name":"sesevent.event-label"}',
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.profile-rsvp',
      'parent_content_id' => $right_id,
      'order' => 24,
  ));
  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.buy-ticket',
      'parent_content_id' => $right_id,
      'order' => 25,
      'params' => '{"title":"Buy Ticket","type":"button","nomobile":"0","name":"sesevent.buy-ticket"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-guest-information',
      'parent_content_id' => $right_id,
      'order' => 26,
      'params' => '{"guestCount":"4","height":"45","width":"40","title":"","nomobile":"0","name":"sesevent.event-guest-information"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.event-location-sidebar',
      'parent_content_id' => $right_id,
      'order' => 27,
      'params' => '{"title":"When & Where","name":"sesevent.event-location-sidebar"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.show-same-tags',
      'parent_content_id' => $right_id,
      'order' => 28,
      'params' => '{"title":"Similar Events","viewType":"list","gridInsideOutside":"in","mouseOver":"over","show_criteria":["title","location"],"grid_title_truncation":"45","list_title_truncation":"18","height":"180","width":"180","limit_data":"3","nomobile":"0","name":"sesevent.show-same-tags"}',
  ));

  $db->insert('engine4_core_content', array(
      'page_id' => $page_id,
      'type' => 'widget',
      'name' => 'sesevent.show-also-liked',
      'parent_content_id' => $right_id,
      'order' => 29,
      'params' => '{"title":"People Also Liked","viewType":"list","gridInsideOutside":"in","mouseOver":"over","show_criteria":["title","location"],"grid_title_truncation":"45","list_title_truncation":"18","height":"180","width":"180","limit_data":"3","nomobile":"0","name":"sesevent.show-also-liked"}',
  ));
  $db->insert('engine4_core_content', array(
    'page_id' => $page_id,
    'type' => 'widget',
    'name' => 'sesevent.profile-join-leave',
    'parent_content_id' => $right_id,
    'order' => 30,
));
}

//Browse Reviews Page
$page_id = $db->select()
  ->from('engine4_core_pages', 'page_id')
  ->where('name = ?', 'seseventreview_index_browse')
  ->limit(1)
  ->query()
  ->fetchColumn();
// insert if it doesn't exist yet
if( !$page_id ) {
  $widgetOrder = 1;
  // Insert page
  $db->insert('engine4_core_pages', array(
    'name' => 'seseventreview_index_browse',
    'displayname' => 'SES - Advanced Events - Browse Reviews Page',
    'title' => 'Event Browse Reviews',
    'description' => 'This page show event browse reviews page.',
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

  // Insert menu
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.browse-menu',
    'page_id' => $page_id,
    'parent_content_id' => $top_middle_id,
    'order' => $widgetOrder++,
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.browse-review-search',
    'page_id' => $page_id,
    'parent_content_id' => $main_left_id,
    'order' => $widgetOrder++,
    'params' => '{"view_type":"vertical","review_title":"1","view":["likeSPcount","viewSPcount","commentSPcount","mostSPrated","leastSPrated","verified","featured"],"review_stars":"1","network":"1","title":"Review Browse Search","nomobile":"0","name":"sesevent.browse-review-search"}',
  ));

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesevent.browse-reviews',
    'page_id' => $page_id,
    'parent_content_id' => $main_middle_id,
    'order' => $widgetOrder++,
    'params' => '{"stats":["likeCount","commentCount","viewCount","title","share","report","pros","cons","description","recommended","postedBy","parameter","creationDate","rating"],"show_criteria":"","pagging":"button","limit_data":"9","title":"","nomobile":"0","name":"sesevent.browse-reviews"}',
  ));
}

//categories default instalation work
//categoryname,banner image,simple icon,colored icon
// $catgoryData = array(0=>array('Uncategorized','uncategorize.jpg','uncategorize.png','uncategorize.png','F05D70'),1=>array('Entertainment','entertainment.jpg','entertainment.png','entertainment.png','D44F56'),2=>array('Food & Festivals','food-&-festivals.jpg','food-&-festivals.png','food-&-festivals.png','25B7D3'),3=>array('Business & Technology','business-&-technology.jpg','business-&-technology.png','business-&-technology.png','D44F56'),4=>array('Education & Arts','education.jpg','education.png','education.png','F4B858'),4=>array('Politics','politics.jpg','politics.png','politics.png','082C56'),5=>array('Pets','pets.jpg','pets.png','pets.png','FFD100'),6=>array('Shopping & Sales','shopping.jpg','shopping.png','shopping.png','9DD2D8'),7=>array('Sports & Adventure','sport.jpg','sport.png','sport.png','3CB29D'),8=>array('Health & Social','heath-and-social.jpg','health-&-social.png','health-&-social.png','8EC036'),9=>array('History & Spirituality','history-&-spirituality.jpg','history-&-spirituality.png','history-&-spirituality.png','22B0C9'),10=>array('Convention','convention.jpg','convention.png','convention.png','8E5A8E'));
// $Entertainment = array(0=>array('Just For Fun','just-for-fun.jpg','',''),1=>array('Kids','kids.jpg','',''),2=>array('Movie','movie.jpg','',''),3=>array('Nightlife','nightlife.jpg','',''));
// $FoodFestivals =array(0=>array('Festivals','festivals.jpg','',''),1=>array('Food','food.jpg','',''));
// $Festivals =array(0=>array('International','international.jpg','',''),1=>array('National','national.jpg','',''));
// $BusinessTechnology = array(0=>array('Organizations','organizations.jpg','',''),1=>array('Skills Development','skill-development.jpg','',''),2=>array('Business','business.jpg','',''),3=>array('Technology','technology.jpg','',''));
// $Business = array(0=>array('Conferences','conference.jpg','',''),1=>array('Business Fare','business-fare.jpg','',''),2=>array('Networking','networking.jpg','',''));
// $Technology = array(0=>array('Tech Fare','tech-fare.jpg','',''),1=>array('Science','science.jpg','',''));
// $EducationArts = array(0=>array('Literary','literary.jpg','',''),1=>array('Arts','art.jpg','',''),2=>array('Education','education.jpg','',''),3=>array('Learning','learning.jpg','',''),4=>array('Galleries','galleries.jpg','',''));
// $Education = array(0=>array('Workshops','workshops.jpg','',''),1=>array('Classes','classes.jpg','',''),2=>array('Seminars','seminar.jpg','',''),3=>array('Webinar','webinar.jpg','',''));
// $ShoppingSales = array(0=>array('Special Day Offer','special-day-offer.jpg','',''),1=>array('New Launch','new-launch.jpg','',''),2=>array('Shopping Carnival','shopping-carnival.jpg','',''),3=>array('Sales','sale.jpg','',''));
// $SportsAdventure = array(0=>array('Travel','travel.jpg','',''),1=>array('Sports','sports.jpg','',''),2=>array('Trekking & Hiking','trekking-hiking.jpg','',''));
// $Travel = array(0=>array('Leisure','leisure.jpg','',''),1=>array('Educational','educational.jpg','',''),2=>array('Historical','historical.jpg','',''));
// $Sports = array(0=>array('Football','football.jpg','',''),1=>array('Basketball','basketball.jpg','',''),2=>array('Other Sports','other-sports.jpg','',''));
// $HealthSocial = array(0=>array('Spirituality','spirituality.jpg','',''),1=>array('Museum','museum.jpg','',''),2=>array('History','historical.jpg','',''));
// $Convention = array(0=>array('Reunion','reunion.jpg','',''),1=>array('Parties','parties.jpg','',''),2=>array('Business Covention','business-convention.jpg','',''),3=>array('Educational Covention','educational-convention.jpg','',''),4=>array('Conference','conference.jpg','',''));
//
//         foreach ($catgoryData as $key => $value) {
//           //Upload categories icon
//           $db->query("INSERT IGNORE INTO `engine4_sesevent_categories` (`category_name`,`subcat_id`,`subsubcat_id`,`slug`,`description`) VALUES ( '" . $value[0] . "',0,0,'','')");
//           $catId = $db->lastInsertId();
//           $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesevent' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "category" . DIRECTORY_SEPARATOR ;
//
// 				 //colored icon upload
// 				  if (is_file($PathFile . "icons" . DIRECTORY_SEPARATOR ."color".DIRECTORY_SEPARATOR. $value[3]))
//             $color_icon = $this->setCategoryPhoto($PathFile . "icons" . DIRECTORY_SEPARATOR ."color".DIRECTORY_SEPARATOR. $value[3], $catId);
//           else
//             $color_icon = 0;
//
// 				 //simple icon
// 				  if (is_file($PathFile . "icons" . DIRECTORY_SEPARATOR . "white" . DIRECTORY_SEPARATOR . $value[2]))
//             $cat_icon = $this->setCategoryPhoto($PathFile . "icons" . DIRECTORY_SEPARATOR. "white" . DIRECTORY_SEPARATOR . $value[2], $catId);
//           else
//             $cat_icon = 0;
//
// 				 //banner image
// 				  if (is_file($PathFile . "banners" . DIRECTORY_SEPARATOR . $value[1]))
//             $thumbnail_icon = $this->setCategoryPhoto($PathFile . "banners" . DIRECTORY_SEPARATOR . $value[1], $catId, true);
//           else
//             $thumbnail_icon = 0;
//
// 				  $db->query("UPDATE `engine4_sesevent_categories` SET `cat_icon` = '" . $cat_icon . "',`thumbnail` = '" . $thumbnail_icon . "' ,`colored_icon` = '".$color_icon."' , `color` = '".$value[4]."' WHERE category_id = " . $catId);
//
// 					$valueName = str_replace(array(' ','&','/'),array('','',''),$value[0]);
// 					if(isset(${$valueName})){
// 						foreach(${$valueName} as $value){
// 							$db->query("INSERT IGNORE INTO `engine4_sesevent_categories` (`category_name`,`subcat_id`,`subsubcat_id`,`slug`,`description`) VALUES ( '" . $value[0] . "','".$catId."',0,'','')");
// 							$subId = $db->lastInsertId();
// 							$PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesevent' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "category" . DIRECTORY_SEPARATOR;
// 							/*//upload icons
// 							if (is_file($PathFile . "icons".DIRECTORY_SEPARATOR.'sub-categories'   . DIRECTORY_SEPARATOR . $value[2]))
// 								$cat_icon = $this->setCategoryPhoto($PathFile . "icons".DIRECTORY_SEPARATOR.'sub-categories'  . DIRECTORY_SEPARATOR . $value[2], $subId);
// 							else*/
// 								$cat_icon = 0;
// 							//upload banner image
// 							if (is_file($PathFile . "banners".DIRECTORY_SEPARATOR.'subcategory' . DIRECTORY_SEPARATOR . $value[1]))
// 								$thumbnail_icon = $this->setCategoryPhoto($PathFile . "banners".DIRECTORY_SEPARATOR.'subcategory' . DIRECTORY_SEPARATOR . $value[1], $subId, true);
// 							else
// 								$thumbnail_icon = 0;
//
// 							$db->query("UPDATE `engine4_sesevent_categories` SET `cat_icon` = '" . $cat_icon . "',`thumbnail` = '" . $thumbnail_icon . "' WHERE category_id = " . $subId);
// 							$valueSubName = str_replace(array(' ','&','/'),array('','',''),$value[0]);
// 							if(isset(${$valueSubName})){
// 								foreach(${$valueSubName} as $value){
// 									$db->query("INSERT IGNORE INTO `engine4_sesevent_categories` (`category_name`,`subcat_id`,`subsubcat_id`,`slug`,`description`) VALUES ( '" . $value[0] . "','0','".$catId."','','')");
// 									$subsubId = $db->lastInsertId();
// 									$PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesevent' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "category" . DIRECTORY_SEPARATOR;
//
// 									/*if (is_file($PathFile . "icons" .DIRECTORY_SEPARATOR.'sub-categories' . DIRECTORY_SEPARATOR . $value[2]))
// 										$cat_icon = $this->setCategoryPhoto($PathFile . "icons" .DIRECTORY_SEPARATOR.'sub-categories' . DIRECTORY_SEPARATOR . $value[2], $subsubId);
// 									else*/
// 									$cat_icon = 0;
// 									if (is_file($PathFile . "banners" .DIRECTORY_SEPARATOR.'subcategory' . DIRECTORY_SEPARATOR . $value[1]))
// 										$thumbnail_icon = $this->setCategoryPhoto($PathFile . "banners" .DIRECTORY_SEPARATOR.'subcategory' .  DIRECTORY_SEPARATOR . $value[1], $subsubId, true);
// 									else
// 										$thumbnail_icon = 0;
// 									$db->query("UPDATE `engine4_sesevent_categories` SET `cat_icon` = '" . $cat_icon . "',`thumbnail` = '" . $thumbnail_icon . "' WHERE category_id = " . $subsubId);
// 								}
// 							}
// 						}
// 					}
//           $runInstallCategory = true;
//         }
$db->query('UPDATE `engine4_sesevent_categories` set `slug` = LOWER(REPLACE(REPLACE(REPLACE(category_name,"&",""),"  "," ")," ","-")) where slug = "";');
$db->query('UPDATE `engine4_sesevent_categories` SET `order` = `category_id` WHERE `order` = 0;');
$db->query('UPDATE `engine4_sesevent_categories` set `title` = `category_name` where title = "" OR title IS NULL;');

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("sesbasic_admin_tooltip", "sesbasic", "Tooltip Settings", "", \'{"route":"admin_default","module":"sesbasic","controller":"tooltip","action":"index"}\', "sesbasic_admin_main", "", 4),
("sesbasic_admin_main_generaltooltip", "sesbasic", "General Settings", "", \'{"route":"admin_default","module":"sesbasic","controller":"tooltip","action":"index"}\', "sesbasic_admin_tooltipsettings", "", 1),
("sesbasic_admin_main_sesevent", "sesbasic", "Advanced Events", "", \'{"route":"admin_default","module":"sesbasic","controller":"tooltip","action":"index","modulename":"sesevent_event"}\', "sesbasic_admin_tooltipsettings", "", 2),
("sesevent_admin_main_importevent", "sesevent", "Import SE Event", "", \'{"route":"admin_default","module":"sesevent","controller":"import","action":"index"}\', "sesevent_admin_main", "", 999);
');

$db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("sesevent_like_event", "sesevent", \'{item:$subject} likes the event {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventalbum", "sesevent", \'{item:$subject} likes the event album {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventphoto", "sesevent", \'{item:$subject} likes the event photo {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventlist", "sesevent", \'{item:$subject} likes the event list {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventspeaker", "sesevent", \'{item:$subject} likes the event speaker {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_like_eventhost", "sesevent", \'{item:$subject} likes the event host {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_event", "sesevent", \'{item:$subject} favourite the event {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_eventlist", "sesevent", \'{item:$subject} favourite the event list {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_eventspeaker", "sesevent", \'{item:$subject} favourite the event speaker {item:$object}:\', 1, 5, 1, 1, 1, 1),
("sesevent_favourite_eventhost", "sesevent", \'{item:$subject} favourite the event host {item:$object}:\', 1, 5, 1, 1, 1, 1);');

$db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("sesevent_like_event", "sesevent", \'{item:$subject} likes your event {item:$object}.\', 0, ""),
("sesevent_like_eventalbum", "sesevent", \'{item:$subject} likes your event album {item:$object}.\', 0, ""),
("sesevent_like_eventphoto", "sesevent", \'{item:$subject} likes your event photo {item:$object}.\', 0, ""),
("sesevent_like_eventlist", "sesevent", \'{item:$subject} likes your event list {item:$object}.\', 0, ""),
("sesevent_like_eventspeaker", "sesevent", \'{item:$subject} likes your event speaker {item:$object}.\', 0, ""),
("sesevent_like_eventhost", "sesevent", \'{item:$subject} likes your event host {item:$object}.\', 0, ""),
("sesevent_favourite_event", "sesevent", \'{item:$subject} favourite your event {item:$object}.\', 0, ""),
("sesevent_favourite_eventlist", "sesevent", \'{item:$subject} favourite your event list {item:$object}.\', 0, ""),
("sesevent_favourite_eventspeaker", "sesevent", \'{item:$subject} favourite your event speaker {item:$object}.\', 0, ""),
("sesevent_favourite_eventhost", "sesevent", \'{item:$subject} favourite your event host {item:$object}.\', 0, "");');

$db->query('UPDATE `engine4_core_menuitems` SET `plugin` = "Sesevent_Plugin_Menus::canViewMultipleCurrency" WHERE `engine4_core_menuitems`.`name` = "sesevent_admin_main_currency";');

$db->query('ALTER TABLE `engine4_sesevent_categories` ADD `member_levels` VARCHAR(255) NULL DEFAULT NULL;');
$db->query('UPDATE `engine4_sesevent_categories` SET `member_levels` = "1,2,3,4" WHERE `engine4_sesevent_categories`.`subcat_id` = 0 and  `engine4_sesevent_categories`.`subsubcat_id` = 0;');


$db->query('ALTER TABLE `engine4_sesevent_events` ADD `networks` VARCHAR(255) NULL, ADD `levels` VARCHAR(255) NULL;');
$db->query('INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    "sesevent_event" as `type`,
    "allow_levels" as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");');
$db->query('INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    "sesevent_event" as `type`,
    "allow_network" as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN("public");');
  
$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sesevent_admin_main_integrateothermodule", "sesevent", "Integrate Plugins", "", \'{"route":"admin_default","module":"sesevent","controller":"integrateothermodule","action":"index"}\', "sesevent_admin_main", "", 995);');


$db->query('DROP TABLE IF EXISTS `engine4_sesevent_integrateothermodules`;');
$db->query('CREATE TABLE IF NOT EXISTS `engine4_sesevent_integrateothermodules` (
  `integrateothermodule_id` int(11) unsigned NOT NULL auto_increment,
  `module_name` varchar(64) NOT NULL,
  `content_type` varchar(64) NOT NULL,
  `content_url` varchar(255) NOT NULL,
  `content_id` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`integrateothermodule_id`),
  UNIQUE KEY `content_type` (`content_type`,`content_id`),
  KEY `module_name` (`module_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;');



$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sesevent_events'")->fetch();
if (!empty($table_exist)) {
  $resource_type = $db->query("SHOW COLUMNS FROM engine4_sesevent_events LIKE 'resource_type'")->fetch();
  if (empty($resource_type)) {
    $db->query('ALTER TABLE `engine4_sesevent_events` ADD `resource_type` VARCHAR(128) NULL;');
  }
  $resource_id = $db->query("SHOW COLUMNS FROM engine4_sesevent_events LIKE 'resource_id'")->fetch();
  if (empty($resource_id)) {
    $db->query('ALTER TABLE `engine4_sesevent_events` ADD `resource_id` INT(11) NOT NULL DEFAULT "0";');
  }
}
$db->query("ALTER TABLE `engine4_sesevent_usergateways` ADD `gateway_type` VARCHAR(64) NULL DEFAULT 'paypal' AFTER `test_mode`;");

$db->query('ALTER TABLE `engine4_sesevent_event_fields_meta` ADD `icon` TEXT NULL DEFAULT NULL;');
$db->query('ALTER TABLE `engine4_eventreview_fields_meta` ADD `icon` TEXT NULL DEFAULT NULL;');

