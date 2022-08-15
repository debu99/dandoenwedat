<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: VideoController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesevent_VideoController extends Sesapi_Controller_Action_Standard {
    public function init() {
        $id = $this->_getParam('video_id', $this->_getParam('id', null));
        if ($id && intval($id)) {
            $video = Engine_Api::_()->getItem('seseventvideo_video', $id);
            if ($video) {
                Engine_Api::_()->core()->setSubject($video);
            }
        }
    }
    public function viewAction() {
        $videoid = $this->_getParam('video_id',null);
        if (Engine_Api::_()->core()->hasSubject()){
            $video = Engine_Api::_()->core()->getSubject('seseventvideo_video');
        }
        else if($videoid)
        {
            $video = Engine_Api::_()->getItem('seseventvideo_video', $videoid);
        }else{
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
         //check dependent module sesprofile install or not
        if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslock'))) {
          //member level check for lock videos
          if ($viewer->getIdentity() == 0)
            $result['level'] = $level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
          else
            $result['level']=$level = $viewer;

          if (!Engine_Api::_()->authorization()->getPermission($level, 'seseventvideo_video', 'locked') && $video->is_locked) {
            $result['is_locekd'] = $locked = true;
          } else {
           $result['is_locekd'] = $locked = false;
          }
         $result['is_locekd']= $password = $video->password;
        } else {
          $result['is_locekd'] = $password = true;
        }
        $result['tags'] = $videoTags = $video->tags()->getTagMaps();
        // check if embedding is allowed
        $can_embed = true;
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.embeds', 1)) {
          $can_embed = false;
        } else if (isset($video->allow_embed) && !$video->allow_embed) {
          $can_embed = false;
        }
        // increment count
        $embedded = "";
        $mine = true;
        if ($video->status == 1) {
          if (!$video->isOwner($viewer)) {
            $video->view_count++;
            $video->save();
            $mine = false;
          }
          $embedded = $video->getRichContent(true,array(),'',$autoPlay);
        }
        $this->view->videoEmbedded = $embedded;
        if ($video->type == 3 && $video->status == 1) {
          if (!empty($video->file_id)) {
            $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
            if ($storage_file) {
              $video_location = $storage_file->map();
              $video_extension = $storage_file->extension;
            }
          }
        }
        // rating code
        $allowShowRating = $allowShowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.ratevideo.show', 1);
        $allowRating = $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.video.rating', 1);
        if ($allowRating == 0) {
          if ($allowShowRating == 0)
            $showRating = false;
          else
            $showRating = true;
        } else
          $showRating = true;
        $this->view->showRating = $showRating;
        if ($showRating) {
           $canRate = Engine_Api::_()->authorization()->isAllowed('seseventvideo_video', $viewer, 'rating');
           $allowRateAgain = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.ratevideo.again', 1);
          $allowRateOwn = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.ratevideo.own', 1);
          if ($canRate == 0 || $allowRating == 0)
            $allowRating = false;
          else
            $allowRating = true;
          if ($allowRateOwn == 0 && $mine)
            $allowMine = false;
          else
            $allowMine = true;
          $rating_type = 'seseventvideo_video';
          $rating_count = Engine_Api::_()->getDbTable('ratings', 'seseventvideo')->ratingCount($video->getIdentity(), $rating_type);
          $rated = Engine_Api::_()->getDbTable('ratings', 'seseventvideo')->checkRated($video->getIdentity(), $viewer->getIdentity(), $rating_type);
          $rating_sum = Engine_Api::_()->getDbTable('ratings', 'seseventvideo')->getSumRating($video->getIdentity(), $rating_type);
          if ($rating_count != 0) {
            $total_rating_average = $rating_sum / $rating_count;
          } else {
            $total_rating_average = 0;
          }
          if (!$allowRateAgain && $rated) {
            $rated = false;
          } else {
            $rated = true;
          }
          
        }
        $can_edit = 0;
        $can_delete = 0;
        if($viewer->getIdentity() != 0){
          $resourceItem = Engine_Api::_()->getItem('sesevent_event', $video->parent_id);
                 $parentedit = $resourceItem->authorization()->isAllowed($viewer, 'edit');
                $canEdit = $video->authorization()->isAllowed($viewer, 'edit');
                if(!$parentedit && !$canEdit)
                        $can_edit = false;
                else
                        $can_edit = true;
                $parentDelete = $parentDelete = $resourceItem->authorization()->isAllowed($viewer, 'delete');
                $canDelete = $video->authorization()->isAllowed($viewer, 'delete');
                if(!$parentDelete && !$canDelete)
                        $can_delete = false;
                else
                        $can_delete = true;
        }
        $getmodule = Engine_Api::_()->getDbTable('modules', 'core')->getModule('core');
        if (!empty($getmodule->version) && version_compare($getmodule->version, '4.8.8') >= 0){
                $doctype('XHTML1_RDFA');
                $docActive = true;
        }
        // end rating code
        $video = $video;
        
        if( !$video || $$video->status != 1 ){
             Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The video you are looking for does not exist or has not been processed yet.'), 'result' => array()));
        }
        if ( $this->video->type == 3 && $this->video_extension == 'mp4' ){
            
        }
        
    }
    public function eventvideoviewAction(){
        $videoid = $this->_getParam('video_id',null);
        if (Engine_Api::_()->core()->hasSubject()){
            $video = Engine_Api::_()->core()->getSubject('seseventvideo_video');
        }
        else if($videoid)
        {
            $video = Engine_Api::_()->getItem('seseventvideo_video', $videoid);
        }else{
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();        
        
        if($video->status != 1)
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("The video you are looking for does not exist or has not been processed yet."), 'result' => array()));
      
        $response = array();
        $response['video'] = $video->toArray();
        $response['video']['description'] = strip_tags($video->getDescription());

        $response['video']['tags'] = $video->tags()->getTagMaps()->toArray();
        if($viewer->getIdentity()){
          $menuoptions= array();
          $canEdit = $this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid();
          $counterMenu = 0;
          if($canEdit){
            $menuoptions[$counterMenu]['name'] = "edit";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
            $counterMenu++;
          }
          $canDelete = $this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->isValid();
          if($canDelete){
            $menuoptions[$counterMenu]['name'] = "delete";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
            $counterMenu++;
          }
          $menuoptions[$counterMenu]['name'] = "report";
          $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Video");

          $response['menus'] = $menuoptions;
        }
        $photo = $this->getBaseUrl(false,$video->getPhotoUrl());
        if($photo)
          $response['video']["share"]["imageUrl"] = $photo;
					$response['video']["share"]["url"] = $this->getBaseUrl(false,$video->getHref());
          $response['video']["share"]["title"] = $video->getTitle();
          $response['video']["share"]["description"] = strip_tags($video->getDescription());
          $response['video']["share"]['urlParams'] = array(
              "type" => $video->getType(),
              "id" => $video->getIdentity()
          );
        if(is_null($response['video']["share"]["title"]))
          unset($response['video']["share"]["title"]);

        if ($video->type == 3 || $video->type == "upload") {
          if (!empty($video->file_id)) {
            $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
            $response['video']['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
            $$response['video']['video_extension'] = $storage_file->extension;  
          }
        }else{
          $embedded = $video->getRichContent(true,array(),'',true);
          preg_match('/src="([^"]+)"/', $embedded, $match);
          if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
            $response['video']['iframeURL'] = str_replace('//','https://',$match[1]);
          }else{
            $response['video']['iframeURL'] = $match[1];
          }
        }
//        it is hardcoded
        unset($response['video']['rating']);
        $response['video']['rating']['code']  = 100;
        $response['video']['rating']['message']  = '';
        $response['video']['rating']['total_rating_average']  = $video->rating;
        if($viewer->getIdentity()){
            $response['video']['canEdit'] = $video->authorization()->isAllowed($viewer, 'edit');
            $response['video']['canDelete'] = $video->authorization()->isAllowed($viewer, 'delete');
        }
        if (!$viewer->isSelf($video->getOwner())){
            $video->view_count++;
            $video->save();
          }
        $response['video']['user_image'] = $this->userImage($video->getOwner()->getIdentity(),"thumb.profile");
        $response['video']['user_id'] = $video->getOwner()->getIdentity();
        $response['video']['user_title'] = $video->getOwner()->getTitle();
        $response['video']['resource_type'] = 'video';
        //similar videos
        $similarVideos = $this->getVideos($this->getSimilarVideos($video));
        if(count($similarVideos) > 0){
          $response['similar_videos'] = $similarVideos;
        } 
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$response));
    }
    public function getVideos($paginator) {
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        foreach ($paginator as $videos) {
          try{
            $video = $videos->toArray();
            $result[$counter] = $video;            
            $href = $videos->getHref();
            $imageURL = $videos->getPhotoUrl();
            $result[$counter]['images']['main'] = $this->getbaseurl(false, $imageURL);
            if (isset($videos->like_count)) {
                $result[$counter]['like_count'] = $videos->like_count;
            }
            if (isset($videos->comment_count)) {
                $result[$counter]['comment_count'] = $videos->comment_count;
            }

            if (isset($videos->favourite_count)) {
                $result[$counter]['favourite_count'] = $videos->favourite_count;
            }
            if (isset($videos->view_count)) {
                $result[$counter]['view_count'] = $videos->view_count;
            }
            $counter++;
          }catch(Exception $e){
              
          }
        }
        return $result;
    }
    protected function getSimilarVideos($video){
        $table = Engine_Api::_()->getDbTable('videos','seseventvideo');
        $tableName = $table->info('name');
        $select = $table->select()->where('video_id != ?',$video->getIdentity())->where('parent_id =?',$video->parent_id)->limit(10);
        $result = $table->fetchAll($select);  
        return $result;
    }
    public function createAction() {
    // Upload video
    if (!$this->_helper->requireUser->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
      
    if (!$this->_helper->requireAuth()->setAuthParams('seseventvideo_video', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    //check ses modules integration
    $values['parent_id'] = $parent_id = $this->_getParam('parent_id', null);
    $values['parent_type'] = $parent_type = $this->_getParam('parent_type', null);
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
	$viewerId = $viewer->getIdentity();
	$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getApi('core', 'seseventvideo')->getVideosPaginator($values);
    $quota = Engine_Api::_()->authorization()->getPermission($levelId, 'seseventvideo_video', 'max');
    $currentCount = $paginator->getTotalItemCount();
    if ($quota)
      $leftVideos = $quota - $currentCount;
    else
      $leftVideos = 0; //o means unlimited
    if (($currentCount >= $quota) && !empty($quota)){
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of videos allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
    }
		//Create form
    $form = new Seseventvideo_Form_Video(array('fromApi'=>true));
    $form->removeElement('lat');
    $form->removeElement('lng');
    $form->removeElement('map-canvas'); // this 
    $form->removeElement('ses_location');
    $form->removeElement('embedUrl');
    if($form->getElement('photo_id'))
    $form->removeElement('photo_id');
   if ($this->_getParam('type', false))
      $form->getElement('type')->setValue($this->_getParam('type'));
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    // Check if valid
    if( !$form->isValid($this->getRequest()->getPost()) ) { 
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }
    // Process
    $values = $form->getValues();
    if(!isset($values['resource_video_type']))
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('send video type.'), 'result' => array()));
    if(empty($values['rotation']))
      $values['rotation'] = 0;
        $video_type = $_POST['type'] = $_POST['resource_video_type'];
        parse_str( parse_url( $_POST['url'], PHP_URL_QUERY ), $my_array_of_vars );
    if ($_POST['resource_video_type'] == "1") {
      $typeVideo = 'youtube';
      //$paramsValidate['code'] = $values['code'] = $my_array_of_vars['v'];
       preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $_POST['url'], $match);
      $youtube_id = $match[1];
      
      $paramsValidate['code'] = $values['code'] = $youtube_id;
    } else if ($video_type == "2") {
      $typeVideo = 'vimeo';
      if (preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $_POST['url'], $regs)) {
            $paramsValidate['code'] = $values['code'] = $regs[3];
        }
    } else if ($video_type == '4') {
      $typeVideo = 'dailymotion';
      $id = strtok(basename($_POST['url']), '_');
      $paramsValidate['code'] = $values['code'] = $id;
    } else if ($video_type == '5') {
      $typeVideo = 'youtubePlaylist';
      $paramsValidate['code'] = $values['code'] = $my_array_of_vars['list'];
    } else if ($video_type == '17') {
      $typeVideo = 'embedCode';
      $paramsValidate['code'] = $values['code'] = $_POST['url'];
    }else if ($video_type == '16') {
      $typeVideo = 'fromurl';
      $paramsValidate['code'] = $values['code'] = $_POST['url'];
    }
    $paramsValidate['resource_video_type'] = $typeVideo;
    if($_POST['type'] != 3)
      $validateVideo = $this->validationAction($paramsValidate);
    else{
      $validateVideo = empty($_FILES['video']['name']) ?  0 : 1;
    }
    if(!$validateVideo){
      if($_POST['type'] != 3)
        $error = ('Please select valid upload url for video.');
      else
        $error = ('Please select video to upload.');
    }
    $values['type'] = $_POST['type'];
    $values['parent_id'] = $parent_id = $this->_getParam('parent_id', null);
    $values['parent_type'] = $parent_type = $this->_getParam('parent_type', null);
    if( $values['parent_id'] &&  $values['parent_type'])
        $parentItem = Engine_Api::_()->getItem($parent_type, $parent_id);
    $values['owner_id'] = $viewer->getIdentity();
    $insert_action = false;
    $db = Engine_Api::_()->getItemTable('seseventvideo_video')->getAdapter();
    $db->beginTransaction();
    if(!empty($_POST['rotation'])){
        $rotation = $_POST['rotation'];
        if($rotation == 1){
            $_POST['rotation'] = 90;  
        }else if($rotation == 2){
            $_POST['rotation'] = 180;  
        }else if($rotation == 3){
            $_POST['rotation'] = 270;  
        }  
    }
    try {
        $viewer = Engine_Api::_()->user()->getViewer();
        $isApproveUploadOption = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('seseventvideo_video', $viewer, 'video_approve');
        $approveUploadOption = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('seseventvideo_video', $viewer, 'video_approve_type');
        $approve = 1;
        if($isApproveUploadOption){
            foreach($approveUploadOption as $valuesIs){
                if ($values['type'] == 1 && $valuesIs == 'youtube') {
                    //youtube
                    $approve = 0;
                    break;
                }else if ($values['type'] == 2 && $valuesIs == 'vimeo') {
                    //vimeo
                    $approve = 0;
                    break;
                }else if ($values['type'] == 3 && $valuesIs == 'myComputer') {
                    //my computer
                    $approve = 0;
                    break;
                }
            }
        }
      //Create video
      $table = Engine_Api::_()->getItemTable('seseventvideo_video');
      if ($values['type'] == 3) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['owner_id'] = $viewer->getIdentity();
        $params = array(
            'owner_type' => 'user',
            'owner_id' => $viewer->getIdentity()
        );
        $video = Engine_Api::_()->video()->createVideo($params, $_FILES['video'], $values);
        if(empty($values['title'])){
           $video->title = $this->view->translate('Untitled Video'); 
           $video->save();  
        }
      }else{
        $video = $table->createRow();
      }
      if ($values['type'] == 3 && isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {
        $values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
      }
				if(empty($_FILES['photo_id']['name'])) {
					unset($values['photo_id']);
				}
        if(empty($values['category_id']) || is_null($values['category_id'])){
          $values['category_id'] = ""; 
        }
        $video->setFromArray($values);
        $video->save();
        // Now try to create thumbnail
        $thumbnail = $this->handleThumbnail($values['type'], $values['code']);
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
        $thumbnail_parsed = @parse_url($thumbnail);
				$imageUploadSize = @getimagesize($thumbnail);
				$width = isset($imageUploadSize[0]) ? $imageUploadSize[0] : '';
        $height = isset($imageUploadSize[1]) ? $imageUploadSize[1] : '';
        if (@$imageUploadSize && $width > 120 && $height > 90) {
          $valid_thumb = true;
        } else{
                if($values['type'] == 1) {
                    $thumbnail = "http://img.youtube.com/vi/".$values['code']."/hqdefault.jpg";
                    if(@getimagesize($thumbnail)) {
                         $valid_thumb = true;
                         $thumbnail_parsed = @parse_url($thumbnail);
                    }else {
                     $valid_thumb = false;
                    }
                }else {
                    $valid_thumb = false;
                }
            }
			if(isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '' && $values['type'] != 3 ){
				$video->photo_id = $this->setPhoto($form->photo_id, $video->video_id, true);
				$video->save();
			}else if($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && (in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) || $video->type == 105)) {
          $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
          $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
          $src_fh = fopen($thumbnail, 'r');
          $tmp_fh = fopen($tmp_file, 'w');
          stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
          //resize video thumbnails
          $image = Engine_Image::factory();
          $image->open($tmp_file)
                  ->resize(500, 500)
                  ->write($thumb_file)
                  ->destroy();
          try {
            $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                'parent_type' => $video->getType(),
                'parent_id' => $video->getIdentity()
            ));
            // Remove temp file
            @unlink($thumb_file);
            @unlink($tmp_file);
						$video->photo_id = $thumbFileRow->file_id;
						$video->save();
          } catch (Exception $e){
						 @unlink($thumb_file);
             @unlink($tmp_file);
						}
        }
			  if($values['type'] == 16){
				 $videoUrl = Engine_Api::_()->video()->createVideo(array(), $values['code'], $values,$video);
				 $video->status = 1;
				 $video->save();
			  }
				if($values['type'] != 3){
					$information = $this->handleInformation($values['type'], $values['code']);
					if(is_array($information)){
						$video->duration = isset($information['duration']) ? $information['duration'] : '';
						if (!$video->description) {
							$video->description = isset($information['description']) ? $information['description'] : '';
						}
						if (!$video->title) {
							$video->title = $information['title'];
						}					
						$video->status = 1;
						$video->save();
						// Insert new action item
						$insert_action = true;
					}
				}
        if ($values['ignore'] == true) {
          $video->status = 1;
          $video->save();
          $insert_action = true;
        }
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_view']))
          $auth_view = $values['auth_view'];
        else
          $auth_view = "everyone";
        $viewMax = array_search($auth_view, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
        }
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_comment']))
          $auth_comment = $values['auth_comment'];
        else
          $auth_comment = "everyone";
        $commentMax = array_search($auth_comment, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $video->tags()->addTagMaps($viewer, $tags);
        $db->commit();
				
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      if ($approve && $video->status == 1) {
        $owner = $video->getOwner();
        //Create Activity Feed 
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'video_create');
        if ($action != null) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
        }
				// Rebuild privacy
				$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
				foreach ($actionTable->getActionsByObject($video) as $action) {
					$actionTable->resetActivityBindings($action);
				}
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $result["video"]["message"] = $this->view->translate("Video created successfully.");
    $result['video']['id'] = $video->getIdentity();
    if (($video->type == 3 && $video->status != 1) || !$approve) {
      $result['video']['redirect'] = "manage";
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
    }
    $result['video']['redirect'] = "video_view";
    $result['video']['id'] = $video->getIdentity();
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }
    public function editAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $video = Engine_Api::_()->getItem('seseventvideo_video', $this->_getParam('video_id'));
        if (!$this->_helper->requireUser()->isValid())
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));

        if (!$this->_helper->requireSubject()->isValid())
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));


        if (!$this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid()) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
        }
        $form = new Seseventvideo_Form_Edit();
        $form->populate($video->toArray());
        $latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('seseventvideo_video',$video->video_id);
        if($latLng){
            if($form->getElement('lat'))
                $form->getElement('lat')->setValue($latLng->lat);
            if($form->getElement('lng'))
                $form->getElement('lng')->setValue($latLng->lng);
        }
        if($form->getElement('map-canvas'))
        $form->removeElement('map-canvas'); // this 
        if($form->getElement('ses_location'))
        $form->removeElement('ses_location');
        if($form->getElement('embedUrl'))
          $form->removeElement('embedUrl');
        if($form->getElement('location'))
            $form->getElement('location')->setValue($video->location);
        $form->getElement('search')->setValue($video->search);
        $form->getElement('title')->setValue($video->title);
        $form->getElement('description')->setValue($video->description);
        if ($form->getElement('is_locked'))
            $form->getElement('is_locked')->setValue($video->is_locked);
        if ($form->getElement('password'))
            $form->getElement('password')->setValue($video->password);
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        foreach ($roles as $role) {
            if (1 === $auth->isAllowed($video, $role, 'view')) {
                $form->auth_view->setValue($role);
            }
            if (1 === $auth->isAllowed($video, $role, 'comment')) {
                $form->auth_comment->setValue($role);
            }
        }
        // prepare tags
        $videoTags = $video->tags()->getTagMaps();
        $tagString = '';
        foreach ($videoTags as $tagmap) {
          if ($tagString !== '')
            $tagString .= ', ';
          $tagString .= $tagmap->getTag()->getTitle();
        }
//        $this->view->tagNamePrepared = $tagString;
        $form->tags->setValue($tagString);

        $form->removeElement('code');
        $form->removeElement('id');
        $form->removeElement('ignore');
        $form->removeElement('lat');
        $form->removeElement('lng');
        $form->removeElement('mapcanvas');
        $form->removeElement('ses_location');
        if($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields);
        }
         // Check if valid
        if( !$form->isValid($_POST) ) { 
          $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
          if(count($validateFields))
            $this->validateFormFields($validateFields);
        }
        // Process
        $db = Engine_Api::_()->getItemTable('seseventvideo_video')->getAdapter();
        $db->beginTransaction();
        try {
          $values = $form->getValues();
          if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '' && $_FILES['image']['size'] > 0) {
            $values['photo_id'] = $this->setPhoto($_FILES['image'], $video->video_id, true);
          } else {
            if (empty($values['photo_id'])){
              unset($values['photo_id']);
            }
          }
          $video->setFromArray($values);
          $video->save();
          // CREATE AUTH STUFF HERE
          $auth = Engine_Api::_()->authorization()->context;
          $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
          if ($values['auth_view'])
            $auth_view = $values['auth_view'];
          else
            $auth_view = "everyone";
          $viewMax = array_search($auth_view, $roles);
          foreach ($roles as $i => $role) {
            $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
          }
          $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
          if ($values['auth_comment'])
            $auth_comment = $values['auth_comment'];
          else
            $auth_comment = "everyone";
          $commentMax = array_search($auth_comment, $roles);
          foreach ($roles as $i => $role) {
            $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
          }
          // Add tags
          $tags = preg_split('/[,]+/', $values['tags']);
          $video->tags()->setTagMaps($viewer, $tags);
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
        $db->beginTransaction();
        try {
          // Rebuild privacy
          $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
          foreach ($actionTable->getActionsByObject($video) as $action) {
            $actionTable->resetActivityBindings($action);
          }
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
                            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Video edited successfully.")));
    }
    public function deleteAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $videoId = $this->_getParam('video_id');
        $video = Engine_Api::_()->getItem('seseventvideo_video', $videoId);
        $resourceItem = Engine_Api::_()->getItem('sesevent_event', $video->parent_id);
        if(!$resourceItem)
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
        $canEdit = $video->authorization()->isAllowed($viewer, 'delete');
        $canEditParent = $resourceItem->authorization()->isAllowed($viewer, 'delete');
        if(!$canEdit && !$canEditParent)
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => array()));
        // In smoothbox
        if (!$video) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Video does not exists or not authorized to delete.'), 'result' => array()));
        }
        
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));
        }
        $db = $video->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            Engine_Api::_()->getApi('core', 'seseventvideo')->deleteVideo($video);
            $db->commit();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$this->view->translate('Video has been deleted..'))));

        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$e->getMessage(), 'result' => array()));
        }
    }
    public function validationAction() {
        $video_type = $this->_getParam('type');
        $code = $this->_getParam('code');
        $ajax = $this->_getParam('ajax', false);
        $mURL = $this->_getParam('url');
        $valid = false;
        // check which API should be used
        if ($video_type == "youtube") {
          $valid = $this->checkYouTube($code);
        } else if ($video_type == "vimeo") {
          $valid = $this->checkVimeo($code);
        } else if ($video_type == 'dailymotion') {
          $valid = $this->checkdailymotion($code);
        } else if ($video_type == 'youtubePlaylist') {
          $valid = $this->checkYoutubePlaylist($code);
        } else if ($video_type == 'embedCode') {
          $valid = $this->checkembedCode($code);
        }else if ($video_type == 'fromurl') {
          $valid = $this->checkFromUrl($code);
        }
       return $valid;
    }
    public function addAction() {
        $video_id = $this->_getParam('video_id', false);
        if(!$video_id){
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
        }else{
            $params['video_id'] = $video_id;
            $insertVideo = Engine_Api::_()->seseventvideo()->deleteWatchlaterVideo($params);
        }
        if($insertVideo['status'] == 'insert'){
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully added to watch later.'))));
        }else if($insertVideo['status'] == 'delete'){
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully deleted from watch later.'))));
        }
    }
    public function rateAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $user_id = $viewer->getIdentity();
        $rating = $this->_getParam('rating');
        $resource_id = $this->_getParam('resource_id');
        $resource_type = $this->_getParam('resource_type');
        if(!$rating || !$resource_id || !$resource_type )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));

        $table = Engine_Api::_()->getDbtable('ratings', 'seseventvideo');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
          Engine_Api::_()->getDbtable('ratings', 'seseventvideo')->setRating($resource_id, $user_id, $rating, $resource_type);

          if ($resource_type && $resource_type == 'seseventvideo_video')
            $item = Engine_Api::_()->getItem('seseventvideo_video', $resource_id);

          $item->rating = Engine_Api::_()->getDbtable('ratings', 'seseventvideo')->getRating($item->getIdentity(), $resource_type);
          $item->save();
          if ($resource_type == 'seseventvideo_video') {
            $type = 'seseventvideo_video_rating';
          }

          $result = Engine_Api::_()->getDbtable('actions', 'activity')->fetchRow(array('type =?' => $type, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
          if (!$result) {
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $item, $type);
            if ($action)
              Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
          }
            $db->commit();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"", 'result' => array('message'=>$this->view->translate('Successfully rated.'))));

        } catch (Exception $e) {
          $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
    }
    public function handleThumbnail($type, $code = null) {
        switch ($type) {
          //youtube
          case "1":
            return "http://img.youtube.com/vi/$code/maxresdefault.jpg";
          //vimeo
          case "2":
            //thumbnail_medium
            $data = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$code.php"));
            $thumbnail = $data[0]['thumbnail_large'];
            return $thumbnail;
          case "4":
            $data = @file_get_contents("https://api.dailymotion.com/video/$code?fields=thumbnail_url");
            if ($data != '') {
              $data = json_decode($data, true);
              $thumbnail_url = (isset($data['thumbnail_url']) && $data['thumbnail_url']) ? $data['thumbnail_url'] : '';
              return $thumbnail_url;
            }
        }
  }
    protected function setPhoto($photo, $id) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    if (!$fileName) {
      $fileName = $file;
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'video',
        'parent_id' => $id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $fileName,
    );
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_main.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($mainPath)
            ->destroy();
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
    } catch (Exception $e) {
      // Remove temp files
      @unlink($mainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Seseventvideo_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
    @unlink($mainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }
    public function handleInformation($type, $code) {
    switch ($type) {
      //youtube
      case "1":
        $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.youtube.apikey');
        $data = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=' . $code . '&key=' . $key);
        if (empty($data)) {
          return;
        }
        $data = Zend_Json::decode($data);
        $information = array();
        $youtube_video = $data['items'][0];
        $information['title'] = $youtube_video['snippet']['title'];
        $information['description'] = $youtube_video['snippet']['description'];
        $information['duration'] = Engine_Date::convertISO8601IntoSeconds($youtube_video['contentDetails']['duration']);
        return $information;
      //vimeo
      case "2":
        //thumbnail_medium
        $data = simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
        $thumbnail = $data->video->thumbnail_medium;
        $information = array();
        $information['title'] = $data->video->title;
        $information['description'] = $data->video->description;
        $information['duration'] = $data->video->duration;
        return $information;
      case "4":
        $data = @file_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title");
        $data = json_decode($data, true);
        $information['title'] = $data['title'];
        $information['description'] = $data['description'];
        $information['duration'] = $data['duration'];
        return $information;
    }
  }
}