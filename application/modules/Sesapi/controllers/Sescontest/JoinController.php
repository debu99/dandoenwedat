<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: JoinController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sescontest_JoinController extends Sesapi_Controller_Action_Standard {
    public function init() {
		if (!$this->_helper->requireAuth()->setAuthParams('contest', null, 'view')->isValid())
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error.'), 'result' => array()));
		$viewer = $this->view->viewer();
		$entryId = $this->_getParam('id', null);
		if ($entryId) {
			$entry = Engine_Api::_()->getItem('participant', $entryId);
			if ($entry)
				Engine_Api::_()->core()->setSubject($entry);
			else
				 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
			$contest = Engine_Api::_()->getItem('contest', $entry->contest_id);
			if (!$contest->is_approved && $viewer->level_id != 1 && $viewer->level_id != 2)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
		}
    }
	
	public function createAction() {
		if (!$this->_helper->requireUser->isValid())
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
		// $this->_helper->content->setEnabled();
		$contest_id = $this->_getParam('contest_id', 0);
		$contest = Engine_Api::_()->getItem('contest', $contest_id);
		$form = new Sescontest_Form_Join_Create();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$participation = Engine_Api::_()->getDbTable('participants', 'sescontest')->hasParticipate($viewer->getIdentity(), $contest_id);
		if (!isset($participation['can_join'])) {
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
		}
		$custom_url = $contest->custom_url;
		$rules['rules'] = $contest->rules;
			if($rules)
				$form->getElement('contest_rules')->setValue($contest->rules);
		// Not post/invalid
		if ($this->_getParam('getForm')) {
			
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			  if($contest->contest_type == 2){
			$dummycheck = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'photo_options');
			}elseif($contest->contest_type ==3){
				$dummycheck = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'video_options');
			}elseif($contest->contest_type ==4){
				$dummycheck = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'music_options');
			}elseif($contest->contest_type ==1){
				$dummycheck = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'blog_options');
			}
			//$data = explode(",",str_replace('"', '', $dummycheck));
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields,$dummycheck);
		}
		if(empty($_FILES['photo']['name'])){
      $_FILES['photo'] = array();
    }
  
		if (!$form->isValid($_POST)) {
			$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
//		if (!$form->isValid($this->getRequest()->getPost()))
//			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
		$ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->sescontest_ffmpeg_path;
		$checkFfmpeg = Engine_Api::_()->sescontest()->checkFfmpeg($ffmpeg_path);
		$values = $_POST;
		$participantTable = Engine_Api::_()->getDbtable('participants', 'sescontest');
		$db = $participantTable->getAdapter();
		$db->beginTransaction();
		try {
		  // Create contest
		  $participant = $participantTable->createRow();
		  $participant->setFromArray($values);
		  $participant->media = $contest->contest_type;
		  $participant->contest_id = $contest->contest_id;
		  $participant->owner_id = $viewer->getIdentity();
		  $participant->creation_date = date('Y-m-d h:i:s');
		  $participant->votingstarttime = $contest->votingstarttime;
		  $participant->votingendtime = $contest->votingendtime;
		  if ($contest->contest_type == 1)
			$participant->description = $values['contest_description'];
		  $participant->save();
		  $tags = preg_split('/[,]+/', $values['tags']);
		  $participant->tags()->addTagMaps($viewer, $tags);
		  if (!empty($_FILES['entry_photo'])) {
			$photoType = 1;
			$participant->setPhoto($_FILES['entry_photo'], $photoType);
		  }
		  $storage = Engine_Api::_()->getItemTable('storage_file');
		  $params = array(
			  'parent_id' => $participant->getIdentity(),
			  'parent_type' => $participant->getType(),
			  'user_id' => $participant->owner_id,
		  );
		  if ($contest->contest_type == 2) {
			if (isset($_POST['record_photo']) && !empty($_POST['record_photo']) && $_POST['record_photo'] != 'undefined') {
			  $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['record_photo']));
			  $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/image' . time() . '.png';
			  file_put_contents($path, $data);
			  $participant->setPhoto($path);
			  @unlink($path);
			} elseif (isset($_POST['sescontest_link_id']) && !empty($_POST['sescontest_link_id'])) {
			  $fileId = Engine_Api::_()->getItem('album_photo', $_POST['sescontest_link_id'])->file_id;
			  $fileObject = Engine_Api::_()->getItem('storage_file', $fileId);
			  $participant->setPhoto($fileObject);
			} elseif (isset($_POST['sescontest_url_id']) && !empty($_POST['sescontest_url_id'])) {
			  $participant->setPhoto($_POST['sescontest_url_id'], 2);
			} else {
			  $participant->setPhoto($_FILES['photo']);
			}
		  } elseif ($contest->contest_type == 4) {
			if (isset($_FILES['webcam']) && !empty($_FILES['webcam']['name'])) {
			  $fileName = $_FILES['webcam']['name'];
			  //$file = Engine_Api::_()->storage()->create($_FILES['webcam'], $params);
			  //$path = APPLICATION_PATH . DIRECTORY_SEPARATOR;
			  if ($ffmpeg_path && $checkFfmpeg) {
				$tmp = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' .
						DIRECTORY_SEPARATOR . rand(0, 1000000) . '_vconverted.mp3';
				//$song = $path . $file->storage_path;
				$song = $_FILES['webcam']['tmp_name'];
				$output = null;
				$return = null;
				$output = exec("$ffmpeg_path -i $song -acodec libmp3lame $tmp", $output, $return);
				//$oldFile = Engine_Api::_()->getItem('storage_file', $file->getIdentity());
				$file = Engine_Api::_()->storage()->create($tmp, $params);
				//$file->name = $oldFile->name;
				$file->name = $fileName;
				$file->save();
				//$oldFile->delete();
				@unlink($tmp);
				$participant->file_id = $file->getIdentity();
			  }
			} elseif (isset($_POST['sescontest_link_id']) && !empty($_POST['sescontest_link_id'])) {
			  $sesmusic = Engine_Api::_()->getItem('sesmusic_albumsong', $_POST['sescontest_link_id']);
			  $trackId = $sesmusic->track_id;
			  if ($trackId) {
				$participant->track_id = $trackId;
			  } else {
				$fileObject = Engine_Api::_()->getItem('storage_file', $sesmusic->file_id);
				$db->insert('engine4_storage_files', array(
					'type' => 'song',
					'parent_type' => 'participant',
					'parent_id' => $participant->participant_id,
					'user_id' => $viewer->getIdentity(),
					'service_id' => Engine_Api::_()->getDbTable('services','storage')->getDefaultServiceIdentity(),
					'storage_path' => $fileObject->storage_path,
					'extension' => 'mp3',
					'name' => $fileObject->name,
					'mime_major' => 'application',
					'mime_minor' => 'octet-stream',
					'size' => $fileObject->size,
					'hash' => $fileObject->hash
				));
				$participant->file_id = $db->lastInsertId();
			  }
			} else {
			  $file = Engine_Api::_()->getItemTable('storage_file')->createFile($_FILES['sescontest_audio_file'], $params);
			  $participant->file_id = $file->file_id;
			}
			$participant->status = 1;
		  } elseif ($contest->contest_type == 3) {
			if (isset($_FILES['webcam']) && !empty($_FILES['webcam']['name'])) {
			  $file = Engine_Api::_()->getItemTable('storage_file')->createFile($_FILES['webcam'], $params);
			  $participant->file_id = $file->file_id;
			} elseif (isset($_POST['sescontest_link_id']) && !empty($_POST['sescontest_link_id'])) {
			  $sesvideo = Engine_Api::_()->getItem('video', $_POST['sescontest_link_id']);
			  if ($sesvideo->type == 3) {
				$fileObject = Engine_Api::_()->getItem('storage_file', $fileId);
				$storagePath = $fileObject->storage_path;
				$fileData = array('tmp_name' => $storagePath, 'size' => $fileObject->size, 'name' => $fileObject->name);
				$file = Engine_Api::_()->getItemTable('storage_file')->createFile($fileData, $params);
				$participant->file_id = $file->file_id;
			  } else {
				$participant->type = $sesvideo->type;
				$participant->code = $sesvideo->code;
			  }
			} else {
			  $file = Engine_Api::_()->getItemTable('storage_file')->createFile($_FILES['video'], $params);
			  $participant->file_id = $file->file_id;
			}
			if ($ffmpeg_path && $checkFfmpeg && empty($_POST['sescontest_link_id'])) {
			  Engine_Api::_()->getDbtable('jobs', 'core')->addJob('sescontest_video_encode', array(
				  'participant_id' => $participant->getIdentity(),
				  'type' => 'mp4',
			  ));
			} else {
			  $participant->status = 1;
			}
		  }
		  $participant->save();
		  $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
		  $action = $activityApi->addActivity($viewer, $participant, 'sescontest_create_entry');
		  if ($action) {
			$activityApi->attachActivity($action, $participant);
		  }
		  if ($participant->owner_id != $contest->user_id) {
			Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($contest->getOwner(), $viewer, $contest, 'sescontest_create_entry');
			Engine_Api::_()->getApi('mail', 'core')->sendSystem($contest->getOwner(), 'sescontest_create_entry', array('entry_title' => $participant->getTitle(), 'contest_title' => $contest->getTitle(), 'object_link' => $participant->getHref(), 'host' => $_SERVER['HTTP_HOST']));
		  }
		  $joinCount = $contest->join_count + 1;
		  Engine_Api::_()->getDbTable('contests', 'sescontest')->update(array('join_count' => $joinCount), array('contest_id =?' => $contest->contest_id));
		  $contestFollowers = Engine_Api::_()->getDbTable('followers', 'sescontest')->getFollowers($contest->contest_id);
		  if (count($contestFollowers) > 0) {
			foreach ($contestFollowers as $follower) {
			  $user = Engine_Api::_()->getItem('user', $follower->user_id);
			  if ($user->getIdentity()) {
				Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $contest, 'sescontest_create_entry_followed');
				Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'sescontest_create_entry_followed', array('entry_title' => $participant->getTitle(), 'contest_title' => $contest->getTitle(), 'object_link' => $participant->getHref(), 'host' => $_SERVER['HTTP_HOST'], 'queue' => true));
			  }
			}
		  }
		  // Commit
      $id = $participant->participant_id;
		  $db->commit();
		   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('entry_id'=>$id,'success_message' =>$this->view->translate('Joined Successfully.'))));
		} catch (Exception $e) {
		  $db->rollBack();
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
		}
	}
	
	public function editAction() {
    if (!$this->_helper->requireUser->isValid())
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $id = $this->_getParam('id', null);
    $entry = Engine_Api::_()->getItem('participant', $id);
    $this->view->form = $form = new Sescontest_Form_Join_Edit();
    $viewer = Engine_Api::_()->user()->getViewer();
    $form->populate($entry->toArray());
	$form->removeElement('contest_user_info');
	$form->removeElement('contest_basic_info');
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if ($settings->getSetting('sescontest.show.entrytag', 1)) {
		$entryTags = $entry->tags()->getTagMaps();
		$tagString = '';
	foreach ($entryTags as $tagmap) {
        if ($tagString !== '') {
          $tagString .= ', ';
        }
        $tagString .= $tagmap->getTag()->getTitle();
      }
      $this->view->tagNamePrepared = $tagString;
      $form->tags->setValue($tagString);
    }
    $userInfoOptions = $settings->getSetting('sescontest.user.info', array('name', 'gender', 'age', 'email', 'phone_no'));
    if (in_array('age', $userInfoOptions) && empty($entry->age)) {
      $form->age->setValue('');
    }
    if (in_array('phone_no', $userInfoOptions) && empty($entry->phoneno)) {
      $form->phoneno->setValue('');
    }
    // Not post/invalid
    if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields);
		}
	if (!$form->isValid($_POST)) {
		$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
		if (count($validateFields))
			$this->validateFormFields($validateFields);
	}
	if (!$form->isValid($this->getRequest()->getPost()))
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    $values = $form->getValues();
    $entry->setFromArray($values);
    $entry->save();
    $tags = preg_split('/[,]+/', $values['tags']);
    $entry->tags()->addTagMaps($viewer, $tags);
    $entry->save();
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('success_message' =>$this->view->translate('Your changes have been saved.'))));
  }
	
	public function deleteAction() {
    if (!$this->_helper->requireUser->isValid())
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    if (!Engine_Api::_()->authorization()->isAllowed('participant', $this->view->viewer(), 'deleteentry'))
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    // In smoothbox
    $contest = Engine_Api::_()->getItem('contest', $this->_getParam('contest_id', 0));
    $entry = Engine_Api::_()->getItem('participant', $this->_getParam('id', null));
    $form = new Sescontest_Form_Join_Delete();
    if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields);
	}
	if (!$form->isValid($_POST)) {
		$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
		if (count($validateFields))
			$this->validateFormFields($validateFields);
	}
	if (!$form->isValid($this->getRequest()->getPost()))
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    $db = $entry->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $entry->delete();
      $contest->join_count--;
      $contest->save();
      $db->commit();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('success_message' =>$this->view->translate('Your entry has been deleted.'))));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
    }
  }
	
	public function viewAction() {
		$viewer = Engine_Api::_()->user()->getViewer();
		$subject = Engine_Api::_()->core()->getSubject();
		if (!$subject->getOwner()->isSelf($viewer)) {
		  $subject->view_count++;
		  $subject->save();
		}
		if ($viewer->getIdentity() != 0) {
		  $dbObject = Engine_Db_Table::getDefaultAdapter();
		  $dbObject->query('INSERT INTO engine4_sescontest_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $subject->getIdentity() . '", "' . $subject->getType() . '","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
		}
		$id = $this->_getParam('id', null);
		if(!$id)
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
		$entry = Engine_Api::_()->getItem('participant', $id);
		$contest = Engine_Api::_()->getItem('contest', $entry->contest_id);
		if(!$entry)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Entry Does Not Exist.'), 'result' => array()));
		if(!$contest)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Contest does not exist.'), 'result' => array()));
		
		$contestType = $contest->contest_type;
		$result['entry'] = $entry->toarray();
		$result['entry']['contest_title'] = $contest->title;
		$result['entry']['contest_type'] =$contestType= $contest->contest_type;
		if($contestType == 3){
			$result['entry']['media_type']['label'] =$this->view->translate('Video Contest');
		}
		elseif($contestType == 2){
			$result['entry']['media_type']['label'] =$this->view->translate('Photo Contest');
		}
		elseif($contestType == 4){
			$result['entry']['media_type']['label'] = $this->view->translate('Audio Contest');
		}
		else{
			$result['entry']['media_type']['label'] = $this->view->translate('Writing Contest');
		}
		if ($contest->category_id) {
			$category = Engine_Api::_()->getItem('sescontest_category', $contest->category_id);
			if ($category) {
				$result['entry']['category_title'] = $category->category_name;
				if ($contest->subcat_id) {
					$subcat = Engine_Api::_()->getItem('sescontest_category', $contest->subcat_id);
					if ($subcat) {
						$result['entry']['subcategory_title'] = $subcat->category_name;
						if ($contest->subsubcat_id) {
							$subsubcat = Engine_Api::_()->getItem('sescontest_category', $contest->subsubcat_id);
							if ($subsubcat) {
								$result['entry']['subsubcategory_title'] = $subsubcat->category_name;
							}
						}
					}
				}
			}
		}

		if ($contestType == 3 && $entry->type == 3 && $entry->status == 1){
			if (!empty($entry->file_id)){
				$storage_file = Engine_Api::_()->getItem('storage_file', $entry->file_id);
			}
			if($storage_file){
				$video_location = $storage_file->map();
				$video_extension = $storage_file->extension;
				if(!$video_location)
					$result['entry']['video'] = $this->getbaseurl(false, $video_location);
				if($video_extension)
					$result['entry']['video_extension'] = $video_extension;
			}
		}
		if($contestType == 3){
			if ($entry->status == 1){
			$embedded = $entry->getRichContent(true,array(),'','');
			  preg_match('/src="([^"]+)"/', $embedded, $match);
			  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				$result['entry']['video']  = str_replace('//','https://',$match[1]);
			  }else{
				$result['entry']['video']  = $match[1];
			  }
			}
			$image = $entry->getPhotoUrl('thumb.main');
				if($image)
					$result['entry']['image'] =$this->getbaseurl(false, $image); 
		}
		elseif($contestType == 4){
			if($photo->track_id){
				$result['entry']['consumer_key'] = $consumer_key =  Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmusic.scclientid');
				$result['entry']['url'] = $URL = "http://api.soundcloud.com/tracks/$photo->track_id/stream?consumer_key=$consumer_key"; 
			}else{
				$file = Engine_Api::_()->getItem('storage_file', $entry->file_id);
				if ($file){
					$URL = $file->map();
					$result['entry']['audio'] = $this->getbaseurl(false, $URL); 
				}
				$image = $entry->getPhotoUrl();
				$result['entry']['image'] = $this->getbaseurl(false, $image);
			}
		}
		elseif($contestType == 1){
			$result['entry']['description'] = $entry->description;
			$image = $entry->getPhotoUrl();
				$result['entry']['image'] = $this->getbaseurl(false, $image);
		}else{
			
			if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum')){
				$image = $entry->getPhotoUrl();
				$result['entry']['image'] = $this->getbaseurl(false, $image);		
			}
		}
		$owner = $entry->getOwner();
		$canComment = Engine_Api::_()->authorization()->isAllowed('participant', $viewer, 'comment');
		$likeStatus = Engine_Api::_()->sescontest()->getLikeStatus($entry->participant_id,$entry->getType());
		if($likeStatus && $viewer->getIdentity())
			$result['entry']['is_content_like'] = true;
		else
			$result['entry']['is_content_like'] = false;
		$favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sescontest')->isFavourite(array('resource_id' => $entry->participant_id,'resource_type' => $entry->getType()));
		 if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.favourite', 1) && $viewer->getIdentity()){
			if($favouriteStatus)
				$result['entry']['is_content_favourite'] = true;
			else
				$result['entry']['is_content_favourite'] = false;
		 }
		if($canComment)
			$result['entry']['can_comment'] = true;
		else
			$result['entry']['can_comment'] = false;
		
		/*if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.favourite', 1))
			$result['entry']['can_favourite'] = true;
		else
			$result['entry']['can_favourite'] = false;
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.favourite', 1))
			$result['entry']['can_favourite'] = true;
		else
			$result['entry']['can_favourite'] = false;*/
		
		$optionCounter = 0;
		if($entry->authorization()->isAllowed($viewer, 'editentry')){
			$result['entry']['options'][$optionCounter]['name'] = 'edit';
			$result['entry']['options'][$optionCounter]['label'] = $this->view->translate('Edit Entry');
			$optionCounter++;
		}
		if(time() < strtotime($contest->votingstarttime) && Engine_Api::_()->authorization()->isAllowed('participant', $viewer, 'deleteentry')){
			$result['entry']['options'][$optionCounter]['name'] = 'delete';
			$result['entry']['options'][$optionCounter]['label'] = $this->view->translate('Delete Entry');
			$optionCounter++;
		}
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.share', 1)){
			$result['entry']['options'][$optionCounter]['name'] = 'share';
			$result['entry']['options'][$optionCounter]['label'] = $this->view->translate('Share Entry');
			$optionCounter++;

      $result['entry']["share"]["imageUrl"] = $this->getBaseUrl(false, $entry->getPhotoUrl());
			$result['entry']["share"]["url"] = $this->getBaseUrl(false,$entry->getHref());
      $result['entry']["share"]["title"] = $entry->getTitle();
      $result['entry']["share"]["description"] = strip_tags($entry->getDescription());
      $result['entry']["share"]['urlParams'] = array(
        "type" => $entry->getType(),
        "id" => $entry->getIdentity()
      );

		}
		if(($owner->user_id != $viewer->getIdentity()) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.allow.report', 1)){
			$result['entry']['options'][$optionCounter]['name'] = 'report';
			$result['entry']['options'][$optionCounter]['label'] = $this->view->translate('Report Entry');
			$optionCounter++;
		}   
		 if (!empty($contest->category_id))
		$category = Engine_Api::_()->getDbtable('categories', 'sescontest')->find($contest->category_id)->current();
		$entryTags = $entry->tags()->getTagMaps();
		foreach ($entryTags as $tagmap) {
			$tags[] = array_merge($tagmap->toArray(), array(
				'id' => $tagmap->getIdentity(),
				'text' => $tagmap->getTitle(),
				'href' => $tagmap->getHref(),
				'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
			));
		}
		if (count($tags)) {
			$result['entry']['tag'] = $tags;
        }
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$voteType = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'allow_entry_vote');
		if ($voteType != 0 && (($voteType == 1 && $entry->owner_id != $viewer->getIdentity()) || $voteType == 2)){
			if(strtotime($contest->votingstarttime) <= time() && strtotime($contest->votingendtime) > time() && strtotime($contest->endtime) > time()){
				$hasVoted = Engine_Api::_()->getDbTable('votes', 'sescontest')->hasVoted($viewer->getIdentity(), $contest->contest_id, $entry->participant_id);
				if($hasVoted)
					$result['entry']['is_vote'] = true;
				else
					$result['entry']['is_vote'] = false;
			}
			$canIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.vote.integrate', 0);
		}
		else
			$canIntegrate = 0;
		$ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner,'',"");
        if ($ownerimage)
            $result['entry']['owner_image'] = $ownerimage;
        $result['entry']['owner_title'] = $owner->getTitle();
		 $defaultParams = array();
        $defaultParams['votingstarttime'] = true;
        $defaultParams['votingendtime'] = true;
        $defaultParams['resulttime'] = true;
        $defaultParams['isSesapi'] = 1;
        $defaultParams['timezone'] = true;
        $strtime = $this->view->contestStartEndDates($contest, $defaultParams);
        list($result['entry']['votingStartTime'], $result['entry']['votingEndTime']) = explode('ENDDATE', strip_tags($strtime));
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' =>$result));
	}

	public function graphAction(){
		$id = $this->_getParam('id', null);
		if(!$id)
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
		$subject = Engine_Api::_()->getItem('participant', $id);
		$contest = Engine_Api::_()->getItem('contest', $subject->contest_id);
		if(!$subject)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Entry Does Not Exist.'), 'result' => array()));
		if(!$contest)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Contest does not exist.'), 'result' => array()));
		$interval = isset($_POST['type']) ? $_POST['type'] :'weekly';
		$votingStartTime = $contest->votingstarttime;
		$votingEndTime = $contest->votingendtime;
		$dateArray = $this->createDateRangeArray($contest->joinstarttime, $contest->votingendtime, $interval);
		$votetable = Engine_Api::_()->getDbTable('votes', 'sescontest');
		$voteSelect = $votetable->select()->from($votetable->info('name'), array(new Zend_Db_Expr('"vote" AS type'), 'SUM(jury_vote_count) as total', 'creation_date', 'DATE_FORMAT(creation_date,"%Y-%m-%d %H") as hourtime'));
		$voteSelect->where('contest_id=?', $contest->contest_id)
				->where('participant_id =?', $subject->participant_id);
		if ($interval == 'monthly')
		  $voteSelect->group("month(creation_date)");
		elseif ($interval == 'weekly')
		  $voteSelect->group("week(creation_date)");
		elseif ($interval == 'daily')
		  $voteSelect->group("DATE_FORMAT(creation_date, '%Y-%m-%d')");
		elseif ($interval == 'hourly') {
		  $voteSelect->where('DATE_FORMAT(creation_date,"%Y-%m-%d") =?', date('Y-m-d'));
		  $voteSelect->group("HOUR(creation_date)");
		}
		$likeTable = Engine_Api::_()->getDbTable('likes', 'sesbasic');
		$likeSelect = $likeTable->select()->from($likeTable->info('name'), array(new Zend_Db_Expr('"like" AS type'), 'COUNT(like_id) as total', 'creation_date', 'DATE_FORMAT(creation_date,"%Y-%m-%d %H") as hourtime'))
				->where('resource_type =?', 'participant')
				->where('resource_id =?', $subject->participant_id);
		if ($interval == 'monthly')
		  $likeSelect->group("month(creation_date)");
		elseif ($interval == 'weekly')
		  $likeSelect->group("week(creation_date)");
		elseif ($interval == 'daily')
		  $likeSelect->group("DATE_FORMAT(creation_date, '%Y-%m-%d')");
		elseif ($interval == 'hourly') {
		  $likeSelect->where('DATE_FORMAT(creation_date,"%Y-%m-%d") =?', date('Y-m-d'));
		  $likeSelect->group("HOUR(creation_date)");
		}
		$commentTable = Engine_Api::_()->getDbTable('comments', 'core');
		$commentSelect = $commentTable->select()->from($commentTable->info('name'), array(new Zend_Db_Expr('"comment" AS type'), 'COUNT(comment_id) as total', 'creation_date', 'DATE_FORMAT(creation_date,"%Y-%m-%d %H") as hourtime'))
				->where('resource_type =?', 'participant')
				->where('resource_id =?', $subject->participant_id);
		if ($interval == 'monthly')
		  $commentSelect->group("month(creation_date)");
		elseif ($interval == 'weekly')
		  $commentSelect->group("week(creation_date)");
		elseif ($interval == 'daily')
		  $commentSelect->group("DATE_FORMAT(creation_date, '%Y-%m-%d')");
		elseif ($interval == 'hourly') {
		  $commentSelect->where('DATE_FORMAT(creation_date,"%Y-%m-%d") =?', date('Y-m-d'));
		  $commentSelect->group("HOUR(creation_date)");
		}
		$favouriteTable = Engine_Api::_()->getDbTable('favourites', 'sescontest');
		$favouritesSelect = $favouriteTable->select()->from($favouriteTable->info('name'), array(new Zend_Db_Expr('"favourite" AS type'), 'COUNT(favourite_id) as total', 'creation_date', 'DATE_FORMAT(creation_date,"%Y-%m-%d %H") as hourtime'))
				->where('resource_type =?', 'participant')
				->where('resource_id =?', $subject->participant_id);
		if ($interval == 'monthly')
		  $favouritesSelect->group("month(creation_date)");
		elseif ($interval == 'weekly')
		  $favouritesSelect->group("week(creation_date)");
		elseif ($interval == 'daily')
		  $favouritesSelect->group("DATE_FORMAT(creation_date, '%Y-%m-%d')");
		elseif ($interval == 'hourly') {
		  $favouritesSelect->where('DATE_FORMAT(creation_date,"%Y-%m-%d") =?', date('Y-m-d'));
		  $favouritesSelect->group("HOUR(creation_date)");
		}
		$viewTable = Engine_Api::_()->getDbTable('recentlyviewitems', 'sescontest');
		$viewSelect = $viewTable->select()->from($viewTable->info('name'), array(new Zend_Db_Expr('"view" AS type'), 'COUNT(recentlyviewed_id) as total', 'creation_date', 'DATE_FORMAT(creation_date,"%Y-%m-%d %H") as hourtime'))
				->where('resource_type =?', 'participant')
				->where('resource_id =?', $subject->participant_id);
		if ($interval == 'monthly')
		  $viewSelect->group("month(creation_date)");
		elseif ($interval == 'weekly')
		  $viewSelect->group("week(creation_date)");
		elseif ($interval == 'daily')
		  $viewSelect->group("DATE_FORMAT(creation_date, '%Y-%m-%d')");
		elseif ($interval == 'hourly') {
		  $viewSelect->where('DATE_FORMAT(creation_date,"%Y-%m-%d") =?', date('Y-m-d'));
		  $viewSelect->group("HOUR(creation_date)");
		}
		$dataSelect = $viewSelect . '  UNION  ' . $favouritesSelect . '  UNION  ' . $commentSelect . '  UNION  ' . $likeSelect . '  UNION ' . $voteSelect;
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$results = $db->query($dataSelect)->fetchAll();
		$var1 = $var2 = $var3 = $var4 = $var5 = $var6 = array();
		$array1 = $array2 = $array3 = $array4 = $array5 = array();
		if ($interval == 'monthly') {
		  $result['graph']['headingTitle'] = $this->view->translate("Monthly Vote Report For ") . $subject->getTitle();
		  $result['graph']['XAxisTitle'] = $this->view->translate("Monthly Votes");
		  $result['graph']['likeHeadingTitle'] = $this->view->translate("Monthly Like Report For ") . $subject->getTitle();
		  $result['graph']['likeXAxisTitle'] = $this->view->translate("Monthly Likes");
		  $result['graph']['commentHeadingTitle'] = $this->view->translate("Monthly Comment Report For ") . $subject->getTitle();
		  $result['graph']['commentXAxisTitle'] = $this->view->translate("Monthly Comments");
		  $result['graph']['favouriteHeadingTitle'] = $this->view->translate("Monthly Favourite Report For ") . $subject->getTitle();
		  $result['graph']['favouriteXAxisTitle'] = $this->view->translate("Monthly Favourites");
		  $result['graph']['viewHeadingTitle'] = $this->view->translate("Monthly Views Report For ") . $subject->getTitle();
		  $result['graph']['viewXAxisTitle'] = $this->view->translate("Monthly Views");
		  foreach ($results as $resultData) {
			if ($resultData['type'] == 'vote')
			  $array1[date('Y-m', (strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'like')
			  $array2[date('Y-m', (strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'comment')
			  $array3[date('Y-m', (strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'favourite')
			  $array4[date('Y-m', (strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'view')
			  $array5[date('Y-m', (strtotime($resultData['creation_date'])))] = $resultData['total'];
		  }
		  foreach ($dateArray as $date) {
			if (!$is_ajax)
			  $var2[] = '"' . date("d", strtotime($date)) . '-' . date("M", strtotime($date)) . '"';
			else
			  $var2[] = date("d", strtotime($date)) . '-' . date("M", strtotime($date));
			if (isset($array1[date('Y-m', strtotime($date))])) {
			  $var1[] = (int) $array1[date('Y-m', strtotime($date))];
			} else {
			  $var1[] = 0;
			}
			if (isset($array2[date('Y-m', strtotime($date))])) {
			  $var3[] = (int) $array2[date('Y-m', strtotime($date))];
			} else {
			  $var3[] = 0;
			}
			if (isset($array3[date('Y-m', strtotime($date))])) {
			  $var4[] = (int) $array3[date('Y-m', strtotime($date))];
			} else {
			  $var4[] = 0;
			}
			if (isset($array4[date('Y-m', strtotime($date))])) {
			  $var5[] = (int) $array4[date('Y-m', strtotime($date))];
			} else {
			  $var5[] = 0;
			}
			if (isset($array5[date('Y-m', strtotime($date))])) {
			  $var6[] = (int) $array5[date('Y-m', strtotime($date))];
			} else {
			  $var6[] = 0;
			}
		  }
		} elseif ($interval == 'weekly') {
		  $result['graph']['headingTitle'] = $this->view->translate("Weekly Vote Report For ") . $subject->getTitle();
		  $result['graph']['XAxisTitle'] = $this->view->translate("Weekly Votes");
		  $result['graph']['likeHeadingTitle'] = $this->view->translate("Weekly Like Report For ") . $subject->getTitle();
		  $result['graph']['likeXAxisTitle'] = $this->view->translate("Weekly Likes");
		  $result['graph']['commentHeadingTitle'] = $this->view->translate("Weekly Comment Report For ") . $subject->getTitle();
		  $result['graph']['commentXAxisTitle'] = $this->view->translate("Weekly Comments");
		  $result['graph']['favouriteHeadingTitle'] = $this->view->translate("Weekly Favourite Report For ") . $subject->getTitle();
		  $result['graph']['favouriteXAxisTitle'] = $this->view->translate("Weekly Favourites");
		  $result['graph']['viewHeadingTitle'] = $this->view->translate("Weekly Views Report For ") . $subject->getTitle();
		  $result['graph']['viewXAxisTitle'] = $this->view->translate("Weekly Views");
		  foreach ($results as $resultData) {
			if ($resultData['type'] == 'vote')
			  $array1[date('Y-m-d', strtotime("last Sunday", strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'like')
			  $array2[date('Y-m-d', strtotime("last Sunday", strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'comment')
			  $array3[date('Y-m-d', strtotime("last Sunday", strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'favourite')
			  $array4[date('Y-m-d', strtotime("last Sunday", strtotime($resultData['creation_date'])))] = $resultData['total'];
			elseif ($resultData['type'] == 'view')
			  $array5[date('Y-m-d', strtotime("last Sunday", strtotime($resultData['creation_date'])))] = $resultData['total'];
		  }
		  $previousYear = '';
		  foreach ($dateArray as $date) {
			$year = date('Y', strtotime($date));
			if ($previousYear != $year)
			  $yearString = '-' . $year;
			else
			  $yearString = '';
			if (!$is_ajax)
			  $var2[] = '"' . (date("d-M", strtotime($date))) . $yearString . '"';
			else
			  $var2[] = (date("d-M", strtotime($date))) . $yearString;
			if (isset($array1[date('Y-m-d', strtotime($date))])) {
			  $var1[] = (int) $array1[date('Y-m-d', strtotime($date))];
			} else {
			  $var1[] = 0;
			}
			if (isset($array2[date('Y-m-d', strtotime($date))])) {
			  $var3[] = (int) $array2[date('Y-m-d', strtotime($date))];
			} else {
			  $var3[] = 0;
			}
			if (isset($array3[date('Y-m-d', strtotime($date))])) {
			  $var4[] = (int) $array3[date('Y-m-d', strtotime($date))];
			} else {
			  $var4[] = 0;
			}
			if (isset($array4[date('Y-m-d', strtotime($date))])) {
			  $var5[] = (int) $array4[date('Y-m-d', strtotime($date))];
			} else {
			  $var5[] = 0;
			}
			if (isset($array5[date('Y-m-d', strtotime($date))])) {
			  $var6[] = (int) $array5[date('Y-m-d', strtotime($date))];
			} else {
			  $var6[] = 0;
			}
			$previousYear = $year;
		  }
		} elseif ($interval == 'daily') {
		  $result['graph']['headingTitle'] = $this->view->translate("Daily Vote Report for ") . $subject->getTitle();
		  $result['graph']['XAxisTitle'] = $this->view->translate("Daily Votes");
		  $result['graph']['likeHeadingTitle'] = $this->view->translate("Daily Like Report for ") . $subject->getTitle();
		  $result['graph']['likeXAxisTitle'] = $this->view->translate("Daily Likes");
		  $result['graph']['commentHeadingTitle'] = $this->view->translate("Daily Comment Report for ") . $subject->getTitle();
		  $result['graph']['commentXAxisTitle'] = $this->view->translate("Daily Comments");
		  $result['graph']['favouriteHeadingTitle'] = $this->view->translate("Daily Favourite Report for ") . $subject->getTitle();
		  $result['graph']['favouriteXAxisTitle'] = $this->view->translate("Daily Favourites");
		  $result['graph']['viewHeadingTitle'] = $this->view->translate("Daily Views Report for ") . $subject->getTitle();
		  $result['graph']['viewXAxisTitle'] = $this->view->translate("Daily Views");
		  foreach ($results as $resultData) {
			if ($resultData['type'] == 'vote')
			  $array1[date('Y-m-d', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'like')
			  $array2[date('Y-m-d', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'comment')
			  $array3[date('Y-m-d', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'favourite')
			  $array4[date('Y-m-d', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'view')
			  $array5[date('Y-m-d', strtotime($resultData['creation_date']))] = $resultData['total'];
		  }
		  foreach ($dateArray as $date) {
			if (!$is_ajax)
			  $var2[] = '"' . date("d", strtotime($date)) . '-' . date("M", strtotime($date)) . '"';
			else
			  $var2[] = date("d", strtotime($date)) . '-' . date("M", strtotime($date));
			if (isset($array1[$date])) {
			  $var1[] = (int) $array1[$date];
			} else {
			  $var1[] = 0;
			}
			if (isset($array2[$date])) {
			  $var3[] = (int) $array2[$date];
			} else {
			  $var3[] = 0;
			}
			if (isset($array3[$date])) {
			  $var4[] = (int) $array3[$date];
			} else {
			  $var4[] = 0;
			}
			if (isset($array4[$date])) {
			  $var5[] = (int) $array4[$date];
			} else {
			  $var5[] = 0;
			}
			if (isset($array5[$date])) {
			  $var6[] = (int) $array5[$date];
			} else {
			  $var6[] = 0;
			}
		  }
		} elseif ($interval == 'hourly') {
		  $result['graph']['headingTitle'] = $this->view->translate("Hourly Vote Report For ") . $subject->getTitle();
		  $result['graph']['XAxisTitle'] = $this->view->translate("Hourly Votes");
		  $result['graph']['likeHeadingTitle'] = $this->view->translate("Hourly Like Report For ") . $subject->getTitle();
		  $result['graph']['likeXAxisTitle'] = $this->view->translate("Hourly Likes");
		  $result['graph']['commentHeadingTitle'] = $this->view->translate("Hourly Comment Report For ") . $subject->getTitle();
		  $result['graph']['commentXAxisTitle'] = $this->view->translate("Hourly Comments");
		  $result['graph']['favouriteHeadingTitle'] = $this->view->translate("Hourly Favourite Report For ") . $subject->getTitle();
		  $result['graph']['favouriteXAxisTitle'] = $this->view->translate("Hourly Favourites");
		  $result['graph']['viewHeadingTitle'] = $this->view->translate("Hourly Views Report For ") . $subject->getTitle();
		  $result['graph']['viewXAxisTitle'] = $this->view->translate("Hourly Views");
		  foreach ($results as $resultData) {
			if ($resultData['type'] == 'vote')
			  $array1[date('Y-m-d H', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'like')
			  $array2[date('Y-m-d H', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'comment')
			  $array3[date('Y-m-d H', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'favourite')
			  $array4[date('Y-m-d H', strtotime($resultData['creation_date']))] = $resultData['total'];
			elseif ($resultData['type'] == 'view')
			  $array5[date('Y-m-d H', strtotime($resultData['creation_date']))] = $resultData['total'];
		  }
		  foreach ($dateArray as $date) {
			$time = date("h A", strtotime($date . ':00:00'));
			if (!$is_ajax)
			  $var2[] = '"' . $time . '"';
			else
			  $var2[] = $time;
			if (isset($array1[$date])) {
			  $var1[] = (int) $array1[$date];
			} else {
			  $var1[] = 0;
			}
			if (isset($array2[$date])) {
			  $var3[] = (int) $array2[$date];
			} else {
			  $var3[] = 0;
			}
			if (isset($array3[$date])) {
			  $var4[] = (int) $array3[$date];
			} else {
			  $var4[] = 0;
			}
			if (isset($array4[$date])) {
			  $var5[] = (int) $array4[$date];
			} else {
			  $var5[] = 0;
			}
			if (isset($array5[$date])) {
			  $var6[] = (int) $array5[$date];
			} else {
			  $var6[] = 0;
			}
		  }
		}
		if($result['graph']['headingTitle']){
			 $grapData['graph'] = array('date' => $var2, 'voteCount' => $var1, 'likeCount' => $var3, 	'commentCount' => $var4, 'favouriteCount' => $var5, 'viewCount' => $var6, 'headingTitle' => $result['graph']['headingTitle'], 'XAxisTitle' => $result['graph']['XAxisTitle'], 'likeHeadingTitle' => $result['graph']['likeHeadingTitle'], 'likeXAxisTitle' => $result['graph']['likeXAxisTitle'], 'commentHeadingTitle' => $result['graph']['commentHeadingTitle'], 'commentXAxisTitle' => $result['graph']['commentXAxisTitle'], 'favouriteHeadingTitle' => $result['graph']['favouriteHeadingTitle'], 'favouriteXAxisTitle' => $result['graph']['favouriteXAxisTitle'], 'viewHeadingTitle' => $result['graph']['viewHeadingTitle'], 'viewXAxisTitle' =>$result['graph']['viewXAxisTitle']);
		}
		  $graphCounter = 0;
		  $grapData['graphOptions'][$graphCounter]['name'] = 'hourly';
		  $grapData['graphOptions'][$graphCounter]['label'] = $this->view->translate('Hourly');
		  $grapData['graphOptions'][$graphCounter]['active'] = $interval == 'hourly'?true:false ;
		  $graphCounter++;
		  $grapData['graphOptions'][$graphCounter]['name'] = 'daily';
		  $grapData['graphOptions'][$graphCounter]['label'] = $this->view->translate('Daily');
		  $grapData['graphOptions'][$graphCounter]['active'] = $interval == 'daily'?true:false ;
		  $graphCounter++;
		  $grapData['graphOptions'][$graphCounter]['name'] = 'weekly';
		  $grapData['graphOptions'][$graphCounter]['label'] = $this->view->translate('Weekly');
		  $grapData['graphOptions'][$graphCounter]['active'] = $interval == 'weekly'?true:false ;
		  $graphCounter++;
		  $grapData['graphOptions'][$graphCounter]['name'] = 'monthly';
		  $grapData['graphOptions'][$graphCounter]['label'] = $this->view->translate('Monthly');
		  $grapData['graphOptions'][$graphCounter]['active'] = $interval == 'monthly'?true:false ;
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' =>$grapData));
	}
	
	public function createDateRangeArray($strDateFrom = '', $strDateTo = '', $interval) {
    // takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates. 
    $aryRange = array();
    $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
    $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));
    if ($iDateTo >= $iDateFrom) {
      if ($interval == 'monthly') {
        array_push($aryRange, date('Y-m', $iDateFrom));
        $iDateFrom = strtotime('+1 Months', $iDateFrom);
        while ($iDateFrom < $iDateTo) {
          array_push($aryRange, date('Y-m', $iDateFrom));
          $iDateFrom += strtotime('+1 Months', $iDateFrom);
        }
      } elseif ($interval == 'weekly') {
        array_push($aryRange, date('Y-m-d', strtotime("last Sunday", $iDateFrom)));
        $iDateFrom = strtotime('+1 Weeks', $iDateFrom);
        while ($iDateFrom < $iDateTo) {
          array_push($aryRange, date('Y-m-d', strtotime("last Sunday", $iDateFrom)));
          $iDateFrom = strtotime('+1 Weeks', $iDateFrom);
        }
      } elseif ($interval == 'daily') {
        array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
        while ($iDateFrom < $iDateTo) {
          $iDateFrom += 86400; // add 24 hours
          array_push($aryRange, date('Y-m-d', $iDateFrom));
        }
      } elseif ($interval == 'hourly') {
        $iDateFrom = strtotime(date('Y-m-d 00:00:00'));
        $iDateTo = strtotime('+1 Day', $iDateFrom);

        array_push($aryRange, date('Y-m-d H', $iDateFrom));
        $iDateFrom = strtotime('+1 Hours', ($iDateFrom));

        while ($iDateFrom < $iDateTo) {
          array_push($aryRange, date('Y-m-d H', $iDateFrom));
          $iDateFrom = strtotime('+1 Hours', ($iDateFrom));
        }
      }
    }
    $preserve = $aryRange;
    return $preserve;
  }

	public function voteAction() {
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		if (!Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'allow_entry_vote') && !$viewerId) {
		  if (!$this->_helper->requireUser->isValid()) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
		  }
		}
		$contest_id = $this->_getparam('contest_id');
		$entry_id = $this->_getparam('id');
		$this->voting($contest_id, $entry_id);
	}
	
	protected function voting($contest_id, $entry_id, $type = false) {
    $entry = Engine_Api::_()->getItem('participant', $entry_id);
    $owner = $entry->getOwner();
    $viewer =  Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $hasVoted = Engine_Api::_()->getDbTable('votes', 'sescontest')->hasVoted($viewerId, $contest_id, $entry_id);
    if ($hasVoted) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have already voted'), 'result' => array()));
    }
    $votingTable = Engine_Api::_()->getDbTable('votes', 'sescontest');
    $db = $votingTable->getAdapter();
    $db->beginTransaction();
    try {
      $vote = $votingTable->createRow();
      $vote->contest_id = $contest_id;
      $vote->participant_id = $entry_id;
      $vote->owner_id = $viewerId;
      $vote->ip_address = $_SERVER["REMOTE_ADDR"];
      $vote->creation_date = date('Y-m-d h:i:s');
      $vote->save();
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescontestjurymember')) {
         $isViewerJury = Engine_Api::_()->getDbTable('members', 'sescontestjurymember')->isJuryMember(array('user_id' => $viewerId, 'contest_id' => $contest_id));
         if ($isViewerJury) {
           $voteCount = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'participant', 'juryVoteWeight');
         } else {
           $voteCount = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'participant', 'votecount_weight');
         }
       } else {
         $voteCount = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'participant', 'votecount_weight');
       }
       if(!$voteCount)
         $voteCount = 1;
       $vote->jury_vote_count = $voteCount;
       $vote->save();
       Engine_Api::_()->getDbTable('participants', 'sescontest')->update(array('vote_date' => date('Y-m-d h:i:s'), 'vote_count' => new Zend_Db_Expr("vote_count + $voteCount")), array('participant_id= ?' => $entry_id));
      $liked = 0;
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.vote.integrate', 0) && $viewerId) {
        $contest = Engine_Api::_()->getItem('contest', $contest_id);
        if ($contest->authorization()->isAllowed($viewer, 'comment')) {
          $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
          $tableMainLike = $tableLike->info('name');
          $select = $tableLike->select()
                  ->from($tableMainLike)
                  ->where('resource_type = ?', 'participant')
                  ->where('poster_id = ?', $viewerId)
                  ->where('poster_type = ?', 'user')
                  ->where('resource_id = ?', $entry_id);
          $result = $tableLike->fetchRow($select);
          if (count($result) == 0) {
            $like = $tableLike->createRow();
            $like->poster_id = $viewerId;
            $like->resource_type = 'participant';
            $like->resource_id = $entry_id;
            $like->poster_type = 'user';
            $like->save();
            Engine_Api::_()->getDbTable('participants', 'sescontest')->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array('participant_id = ?' => $entry_id));
            $liked = 1;
            if ($viewerId && $owner->getType() == 'user' && $owner->getIdentity() != $viewerId) {
              $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
              Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'sescontest_like_contest_entry', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $entry->getType(), "object_id = ?" => $entry->getIdentity()));
              Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $entry, 'sescontest_like_contest_entry');
              $result = $activityTable->fetchRow(array('type =?' => 'sescontest_like_contest_entry', "subject_id =?" => $viewerId, "object_type =? " => $entry->getType(), "object_id = ?" => $entry->getIdentity()));
              if (!$result) {
                $action = $activityTable->addActivity($viewer, $entry, 'sescontest_like_contest_entry');
                if ($action)
                  $activityTable->attachActivity($action, $entry);
              }
            }
          }
        }
      }
      //Commit
      $db->commit();
      //Start Voting Activity Feed Work
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewerId) {
        $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
        if ($viewerId) {
          Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'sescontest_vote_contest_entry', "subject_id =?" => $viewerId, "object_type =? " => 'participant', "object_id = ?" => $entry->getIdentity()));
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $entry, 'sescontest_vote_contest_entry');
          $result = $activityTable->fetchRow(array('type =?' => 'sescontest_vote_contest_entry', "subject_id =?" => $viewerId, "object_type =? " => 'participant', "object_id = ?" => $entry->getIdentity()));
          if (!$result) {
            $action = $activityTable->addActivity($viewer, $entry, 'sescontest_vote_contest_entry');
            if ($action)
              $activityTable->attachActivity($action, $entry);
          }
          $senderTitle = $viewer->getTitle();
        }
        else {
          $senderTitle = $this->view->translate('Anonymous User');
        }
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($entry->getOwner(), 'sescontest_vote_contest_entry', array('member_name' => $senderTitle, 'entry_title' => $entry->getTitle(), 'object_link' => $entry->getHref(), 'host' => $_SERVER['HTTP_HOST'], 'queue' => false));
      }
      if (!$type) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' =>'', 'error_message' => '', 'result' => array('success_message'=>$this->view->translate('Voted Successfully.'))));
      }
    } catch (Exception $e) {
      $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }

}
