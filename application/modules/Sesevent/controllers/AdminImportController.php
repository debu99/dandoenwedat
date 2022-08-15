<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminManageController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 ini_set('memory_limit', '6004M');
class Sesevent_AdminImportController extends Core_Controller_Action_Admin {
	function getCoordinates($address){
		$address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern	 
		$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";
		$response = file_get_contents($url);
		$json = json_decode($response,TRUE); //generate array object from the response from the web
		if(!empty($json['results'][0]['geometry']['location']['lat']))
			return array('lat'=>$json['results'][0]['geometry']['location']['lat'],'lng'=>$json['results'][0]['geometry']['location']['lng']);
		else 
			return '';
	}
  public function indexAction() {	 
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_importevent');
		$setting = Engine_Api::_()->getApi('settings', 'core');
    $viewer = $this->view->viewer();
		$db = Engine_Db_Table::getDefaultAdapter();
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesevent') && $setting->getSetting('sesevent.pluginactivated') && isset($_GET['is_ajax'])) {
				//add column in SE core category table
				try{
					 //import category to event category table
					 $categories = $db->query("SELECT * FROM engine4_event_categories")->fetchAll();
					 $categoryTable = Engine_Api::_()->getDbTable('categories','sesevent');
					 foreach($categories as $category){
							//check category exists in event title evennt_category	
							$hasCategory = $categoryTable->select()->from($categoryTable->info('name'),'category_id')->where('title =?',$category['title'])->query()->fetchColumn();
							if($hasCategory){
								$db->query("UPDATE engine4_event_events SET event_category = ".$hasCategory.' WHERE category_id ='.$category['category_id']);
							}else{
								$db->query("INSERT INTO `engine4_sesevent_categories`(`slug`, `category_name`, `subcat_id`, `subsubcat_id`, `title`, `description`, `color`, `thumbnail`, `cat_icon`, `colored_icon`, `order`, `profile_type`, `profile_type_review`) VALUES ('".$category['title']."','".$category['title']."',0,0,'".$category['title']."','','','','','','',0,0)");
								$lastInsertId = $db->lastInsertId();
								$db->query("UPDATE engine4_event_events SET event_category = ".$lastInsertId.' WHERE category_id ='.$category['category_id']);
							}
					 }
				}catch(Exception $e){
					// silence
				}		
				//insert membership data
				$db->query('INSERT IGNORE INTO engine4_sesevent_membership (`resource_id`, `user_id`, `active`, `resource_approved`, `user_approved`, `message`, `rsvp`, `title`) SELECT `resource_id`, `user_id`, `active`, `resource_approved`, `user_approved`, `message`, `rsvp`, `title` FROM engine4_event_membership');
				//fetch all events from SE event table
				$events = $db->query("SELECT * FROM engine4_event_events WHERE sesevent_import = 0")->fetchAll();
				//loop over all events
				foreach($events as $key => $event){
					$event = (object) $event;			
				$owner = Engine_Api::_()->getItem('user',$event->user_id);	
				if(!$owner)
					continue;
				// Process
				$starttime = isset($event->starttime) ? date('Y-m-d H:i:s',strtotime($event->starttime)) : '';
				$endtime = isset($event->endtime) ? date('Y-m-d H:i:s',strtotime($event->endtime)) : '';
				$values['user_id'] = $event->user_id;
				$values['parent_id'] = $event->parent_id;
				$values['parent_type'] = $event->parent_type;
				$values['search'] = $event->search;
				$values['timezone'] = !empty($owner->timezone) ? $owner->timezone : '';
				$locationCoordinates = !empty($event->location) ? $this->getCoordinates($event->location) : '';
				$values['location'] = !empty($event->location) ? $event->location : '';
				$values['show_timezone'] = 1;
				$values['show_endtime'] = 1;
				$values['show_starttime'] = 1;
				$values['venue_name'] =  '';
				$values['photo_id'] = $event->photo_id;
				$values['view_count'] = $event->view_count;
				$values['member_count'] = $event->member_count;
				$values['creation_date'] = $event->creation_date;
				$values['description'] = $event->description;
				$values['title'] = $event->title;
				// Convert times
				$oldTz = date_default_timezone_get();
				date_default_timezone_set($values['timezone']);
				$start = strtotime($starttime);
				$end = strtotime($endtime);
				date_default_timezone_set($oldTz);
				$values['starttime'] = date('Y-m-d H:i:s', $start);
				$values['endtime'] = date('Y-m-d H:i:s', $end);
				$db = Engine_Api::_()->getDbtable('events', 'sesevent')->getAdapter();
				$db->beginTransaction();
				try {
					// Create event
					$table = Engine_Api::_()->getDbtable('events', 'sesevent');
					$eventIn = $table->createRow();
					$values['is_sponsorship'] = 0;
					//set location
					if (empty($locationCoordinates)) {
						unset($values['location']);
						unset($values['lat']);
						unset($values['lng']);
						$values['is_webinar'] = 1;
					} else
						$values['is_webinar'] = 0;
						$values['is_approved'] = 	1;
					//Host save function
					$values['host_type'] = 'site';
					$_POST['toValues'] = $owner->getIdentity();
					$_POST['host_type'] = 'site';
					$host_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $_POST['toValues'], 'host_type' => 'site')); 
					if(!empty($host_id)) {
						$values['host'] = $host_id;
						unset($values['toValues']);
					} else {
						$values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form,'site',$_POST,'import');
						unset($values['toValues']);
					}
					$values['featured'] = 	(int)Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_sponsored');
					$values['sponsored'] = 	(int)Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_featured');
					$values['verified'] = 	(int)Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_verified');
					$values['ip_address'] = $_SERVER['REMOTE_ADDR'];
					$values['draft'] = 1;
					$values['status'] = 1;
					$values['approval'] = $event->approval;
					$values['invite'] = $event->invite;
					$values['category_id'] = $event->event_category;
					if(empty($values['subsubcat_id']))
					$values['subsubcat_id'] = 0;
					if(empty($values['subcat_id']))
					$values['subcat_id'] = 0;
					$eventIn->setFromArray($values);
					$eventIn->save();
					if (!empty($locationCoordinates)) {
					 if (isset($locationCoordinates['lat']) && isset($locationCoordinates['lng']) && $locationCoordinates['lat'] != '' && $locationCoordinates['lng'] != '' && !empty($event->location)) {
							$db->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $eventIn->event_id . '","'.$locationCoordinates['location'].'", "' . $locationCoordinates['lat'] . '","' . $locationCoordinates['lng'] . '","","","","","","",  "sesevent_event")	ON DUPLICATE KEY UPDATE	lat = "' . $locationCoordinates['lat'] . '" , lng = "' . $locationCoordinates['lng'] . '",city = "", state = "", country = "", zip = "", address = "", address2 = "", venue = ""');
						}
					}
					
					$eventIn->seo_title = $event->title;
					$eventIn->save();
					
					$db->query("INSERT IGNORE INTO engine4_authorization_allow (`resource_type`, `resource_id`, `action`, `role`, `role_id`, `value`, `params`) SELECT 'sesevent_event', '".$eventIn->event_id."', `action`, `role`, `role_id`, `value`, `params` FROM engine4_authorization_allow WHERE resource_id = ".$event->event_id.' AND resource_type = "event" ');
					
					// Set auth
					$auth = Engine_Api::_()->authorization()->context;
					$roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
					
					$videoMax = 'everyone';
					$musicMax = 'everyone';
					$topicMax = 'everyone';
					$ratingMax = 'everyone';
					foreach ($roles as $i => $role) {
						$auth->setAllowed($eventIn, $role, 'video', ($i <= $videoMax));
						$auth->setAllowed($eventIn, $role, 'music', ($i <= $musicMax));
						$auth->setAllowed($eventIn, $role, 'topic', ($i <= $topicMax));
						$auth->setAllowed($eventIn, $role, 'rating', ($i <= $ratingMax));
					}
					$eventIn->save();
					//import topics
					$db->query("INSERT IGNORE INTO `engine4_sesevent_topics` (`topic_id`,`event_id`, `user_id`, `title`, `creation_date`, `modified_date`, `sticky`, `closed`, `view_count`, `post_count`, `lastpost_id`, `lastposter_id`) SELECT `topic_id`,'".$eventIn->event_id."', `user_id`, `title`, `creation_date`, `modified_date`, `sticky`, `closed`, `view_count`, `post_count`, `lastpost_id`, `lastposter_id` FROM engine4_event_topics WHERE event_id = ".$event->event_id);			
					//insert topic watches
					$db->query("INSERT IGNORE INTO `engine4_sesevent_topicwatches`(`resource_id`, `topic_id`, `user_id`, `watch`) SELECT '".$eventIn->event_id."', `topic_id`, `user_id`, `watch` FROM engine4_event_topicwatches WHERE resource_id = ".$event->event_id);
					//inser post
					$db->query("INSERT INTO `engine4_sesevent_posts` (`post_id`, `topic_id`, `event_id`, `user_id`, `body`, `creation_date`, `modified_date`) SELECT `post_id`, `topic_id`, '".$eventIn->event_id."', `user_id`, `body`, `creation_date`, `modified_date` FROM engine4_event_posts WHERE event_id = ".$event->event_id);
					//insert style
					$db->query("INSERT INTO `engine4_core_styles`(`type`, `id`, `style`) SELECT 'sesevent_event', '".$eventIn->event_id."', `style` FROM engine4_core_styles WHERE type = 'event' AND id = ".$event->event_id);
					//import albums
					$db->query("INSERT IGNORE INTO `engine4_sesevent_albums`(`album_id`,`event_id`, `owner_id`, `title`, `description`, `creation_date`, `modified_date`, `search`, `photo_id`, `view_count`, `comment_count`, `collectible_count`)  SELECT `album_id`,'".$eventIn->event_id."','".$owner->getIdentity()."', `title`, `description`, `creation_date`, `modified_date`, `search`, `photo_id`, `view_count`, `comment_count`, `collectible_count` FROM engine4_event_albums WHERE event_id = ".$event->event_id);
					//import photos
					$db->query("INSERT IGNORE INTO `engine4_sesevent_photos`(`photo_id`,`album_id`, `event_id`, `user_id`, `title`, `description`, `collection_id`, `file_id`, `creation_date`, `modified_date`, `view_count`, `comment_count`) SELECT `photo_id`,`album_id`, '".$eventIn->event_id."', `user_id`, `title`, `description`, `collection_id`, `file_id`, `creation_date`, `modified_date`, `view_count`, `comment_count` FROM engine4_event_photos WHERE event_id = ".$event->event_id);
					//import likes
					$db->query("INSERT IGNORE INTO `engine4_core_likes`(`resource_type`, `resource_id`, `poster_type`, `poster_id`) SELECT 'sesevent_event', '".$eventIn->event_id."' , `poster_type`, `poster_id` FROM engine4_core_likes WHERE resource_id = ".$event->event_id.' AND resource_type = "event" ');
					//import comments
					$db->query("INSERT IGNORE INTO `engine4_core_comments`(`resource_type`, `resource_id`, `poster_type`, `poster_id`,`body`,`like_count`) SELECT 'sesevent_event', '".$eventIn->event_id."' , `poster_type`, `poster_id`,`body`,`like_count` FROM engine4_core_comments WHERE resource_id = ".$event->event_id.' AND resource_type = "event" ');
					// Commit
					$db->commit();
					$eventIn->creation_date = $event->creation_date;
					$eventIn->custom_url = $this->slug($eventIn->getSlug(),$table);
					$eventIn->save();
					$db->query("UPDATE engine4_event_events SET sesevent_import = 1 WHERE event_id =".$event->event_id);
					} catch (Engine_Image_Exception $e) {
						$db->rollBack();
						echo json_encode(array('error_code'=>1));die;
					}
					//success
				}	
		}
		if(Engine_Api::_()->getDbTable('modules','core')->getModule('event')){
			try{
				$db->query("ALTER TABLE  `engine4_event_events` ADD  `event_category` INT( 11 ) NOT NULL DEFAULT '0'");
				$db->query("ALTER TABLE  `engine4_event_events` ADD  `sesevent_import` INT( 11 ) NOT NULL DEFAULT '0'");	
			}catch(Exception $e){
				//silence	
			}
			$this->view->events = $db->query("SELECT * FROM engine4_event_events WHERE sesevent_import = 0")->fetchAll();
		}else
			$this->view->events  = array();
		
	}
	protected function slug($slug = '',$eventTable){
		$checkSlug = 1;
		$counter = 1;
		do {
			$checkSlug =  $eventTable->select()->from($eventTable->info('name'),'custom_url')->where('custom_url =?',$slug)->query()->fetchColumn();
			if($checkSlug){
				$slug = $checkSlug.$counter;	
			}
			$counter++;
		} while ($checkSlug != 0);
		return $slug;
	}
}