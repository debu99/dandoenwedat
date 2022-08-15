<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: PlaylistController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesvideo_PlaylistController extends Sesapi_Controller_Action_Standard {
  protected $_permission = array();
  public function init() {
    //Get viewer info
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    //Get subject
    if (null !== ($playlist_id = $this->_getParam('playlist_id')) && null !== ($playlist = Engine_Api::_()->getItem('sesvideo_playlist', $playlist_id)) && $playlist instanceof Sesvideo_Model_Playlist && !Engine_Api::_()->core()->hasSubject()) {
      Engine_Api::_()->core()->setSubject($playlist);
    }
    $this->_permission = array('canCreateVideo'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'create'),'watchLater'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.watchlater', 1),'canCreatePlaylist'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'addplaylist_video'),'canCreateChannel'=>Engine_Api::_()->authorization()->isAllowed('sesvideo_chanel', null, 'create'),'canChannelEnable'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video_enable_chanel', 1));
  }
  public function searchFormAction(){
    $searchOptionsType = $this->_getParam('searchOptionsType', array('searchBox', 'view', 'show'));
    
    $formFilter = new Sesvideo_Form_SearchPlaylist();
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($formFilter->isValid($_POST))
      $values = $formFilter->getValues();
    else
      $values = array();

    if (@$values['show'] == 2 && $viewer->getIdentity())
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($formFilter,true);
    $this->generateFormFields($formFields); 
  }
  public function browseAction() {
    $searchOptionsType = $this->_getParam('searchOptionsType', array('searchBox', 'view', 'show'));
    
    $formFilter = new Sesvideo_Form_SearchPlaylist();
    $viewer = Engine_Api::_()->user()->getViewer();
    $formFilter->populate($_POST);
    if ($formFilter->isValid($_POST))
      $values = $formFilter->getValues();
    else
      $values = array();
    if(empty($_POST['popularity']))
      $values['popularity'] = "creation_date";
    if (@$values['show'] == 2 && $viewer->getIdentity())
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    
    $manage = $this->_getParam('type','');
    if($manage == "manage"){
      if($this->view->viewer()->getIdentity()){
        $values['user'] = $this->view->viewer()->getIdentity();
      }else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'','result'=>'invalid_request'));
    }
    if(!empty($_POST['title_name']))
      $values['title'] = $_POST['title_name'];
    $paginator = Engine_Api::_()->getDbTable('playlists', 'sesvideo')->getPlaylistPaginator($values);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $result["permission"] =  $this->_permission;
    $result['playlists'] = $this->getPlaylists($paginator,$manage);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No playlist created yet.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams)); 
  }
  protected function getPlaylists($paginator,$manage = ""){
    $result = array();
    $counter = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    foreach($paginator as $playlist){ 
        $item = $playlist->toArray();
        $item["description"] = preg_replace('/\s+/', ' ', $item["description"]);
        $item['user_title'] = $playlist->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0){
          $item['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($playlist);
          $item['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($playlist);
          $item['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($playlist,'favourites','sesvideo','sesvideo_playlist');
          $item['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($playlist,'favourites','sesvideo','sesvideo_playlist');
        }        
        $item['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($playlist,'',"");
        if(!count($images))
          $images['images']['main'] = $this->getBaseUrl(true,$playlist->getPhotoUrl());
        
        if($manage){
            $menuoptions= array();
            $canEdit = $viewer->getIdentity() == $playlist->owner_id;
            $counterMenu = 0;
            if($canEdit){
              $menuoptions[$counterMenu]['name'] = "edit";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
              $counterMenu++;
            }
            $canDelete = $viewer->getIdentity() == $playlist->owner_id;
            if($canDelete){
              $menuoptions[$counterMenu]['name'] = "delete";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
            }
            $item['menus'] = $menuoptions;
        }
        
        if($playlist->cover_id)
          $item['cover'] = Engine_Api::_()->sesapi()->getPhotoUrls($playlist->cover_id,'',"");
        $item['videos_count'] = Engine_Api::_()->getDbtable('playlistvideos', 'sesvideo')->playlistVideosCount(array('playlist_id' => $playlist->playlist_id));        
        $result[$counter] = array_merge($item,array());
        $counter++;
    }
      return $result;
  }
  //View Action
  public function viewAction() {   
    
    //Check subject
    if (!$this->_helper->requireSubject()->isValid())
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'','result'=>'invalid_request'));

    //Get viewer/subject
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();
    $this->view->playlist = $playlist = Engine_Api::_()->core()->getSubject('sesvideo_playlist');
    
    if($this->_getParam('page',1) == 1){
      
      if(!$viewer->isSelf($playlist->getOwner())){
        if($playlist->is_private){
           Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'','result'=>'permission_error'));
        }
      }
      //Increment view count
      if (!$viewer->isSelf($playlist->getOwner())) {
        $playlist->view_count++;
        $playlist->save();
      }
       /* Insert data for recently viewed widget */
      if ($viewer->getIdentity() != 0 && isset($chanel->chanel_id)) {
        $dbObject = Engine_Db_Table::getDefaultAdapter();
        $dbObject->query('INSERT INTO engine4_sesvideo_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $playlist->playlist_id . '", "sesvideo_playlist","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
      }
      $playlists = $this->getPlaylists(array(0=>$playlist));
      $result['playlist'] = $playlists[0];
    }
      
    $paginator = $playlist->getVideos(array('playlist_id' => $playlist->getIdentity(), 'order' => true), true);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $result['videos'] = $this->getVideos($paginator,"");
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No video uploaded yet.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  protected function getVideos($paginator,$manage = ""){
    $result = array();
    $counter = 0;
    foreach($paginator as $videos){ 
        $videos = $videoV = Engine_Api::_()->getItem('sesvideo_video',$videos["file_id"]);
        $video = $videos->toArray();
        $video["description"] = preg_replace('/\s+/', ' ', $video["description"]);
        $video['user_title'] = $videos->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0){
          try{
          $video['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($videos);
          $video['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($videos);
          $video['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($videos,'favourites','sesvideo','sesvideo_video');
          $video['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($videos,'favourites','sesvideo','sesvideo_video');
          }catch(Exception $e){}
        }        
        if($manage){
           $viewer = Engine_Api::_()->user()->getViewer();
            $menuoptions= array();
            $canEdit = $this->_helper->requireAuth()->setAuthParams($videos, null, 'edit')->isValid();
            $counterMenu = 0;
            if($canEdit){
              $menuoptions[$counterMenu]['name'] = "edit";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
              $counterMenu++;
            }
            $canDelete = $this->_helper->requireAuth()->setAuthParams($videos, null, 'delete')->isValid();
            if($canDelete){
              $menuoptions[$counterMenu]['name'] = "delete";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
            }
            $video['menus'] = $menuoptions;
        }
        if( $videos->duration >= 3600 ) {
          $duration = gmdate("H:i:s", $videos->duration);
        } else {
          $duration = gmdate("i:s", $videos->duration);
        }
        $video['duration'] = $duration;
        if($this->_permission["watchLater"] && $this->view->viewer()->getIdentity()){
          if(empty($video["watchlater_id"]) && is_null($video["watchlater_id"])){
              $video["watchlater_id"] = 0;
          }
          $video["canWatchlater"] = true;
        }else{
          $video["canWatchlater"] = false;  
        }
        $video['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($videos,'',"");
        if(!count($video['images']))
          $video['images']['main'] = $this->getBaseUrl(false,$videos->getPhotoUrl());
       
           $videoV = Engine_Api::_()->getItem('video',$videos->video_id);
          if ($videoV->type == 3) {
            if (!empty($videoV->file_id)) {
              $storage_file = Engine_Api::_()->getItem('storage_file', $videoV->file_id);
              $video['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
              $video['video_extension'] = $storage_file->extension;  
            }
          }else{
            $embedded = $videoV->getRichContent(true,array(),'',true);
            
            preg_match('/src="([^"]+)"/', $embedded, $match);
            if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
              $video['iframeURL'] = str_replace('//','https://',$match[1]);
            }else{
              $video['iframeURL'] = $match[1];
            }
         }
        $result[$counter] = array_merge($video,array());
        $counter++;
    }
      return $result;
  }
  //Delete playlist songs Action
  public function deletePlaylistvideoAction() {

    //Get video/playlist
    $video = Engine_Api::_()->getItem('sesvideo_playlistvideo', $this->_getParam('playlistvideo_id'));

    $playlist = $video->getParent();

    //Check song/playlist
    if (!$video || !$playlist) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      return;
    }

    //Get file
    $file = Engine_Api::_()->getItem('storage_file', $video->file_id);
    if (!$file) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      return;
    }

    $db = $video->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      Engine_Api::_()->getDbtable('playlistvideos', 'sesvideo')->delete(array('playlistvideo_id =?' => $this->_getParam('playlistvideo_id')));
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      $this->view->success = false;
      $this->view->error = $this->view->translate('Unknown database error');
      throw $e;
    }

    $this->view->success = true;
  }

  //Edit Action
  public function editAction() {
    //Only members can upload video
    if (!$this->_helper->requireUser()->isValid())
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'','result'=>'permission_error'));

    //Get playlist
    $this->view->playlist = $playlist = Engine_Api::_()->getItem('sesvideo_playlist', $this->_getParam('playlist_id'));

    //Make form
    $this->view->form = $form = new Sesvideo_Form_EditPlaylist();

    $form->populate($playlist->toarray());
    $form->removeElement('is_private');
    $form->removeElement('playlist_id');
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

    $values = $form->getValues();

    unset($values['file']);

    $db = Engine_Api::_()->getDbTable('playlists', 'sesvideo')->getAdapter();
    $db->beginTransaction();
    try {
      $playlist->title = $values['title'];
      $playlist->description = $values['description'];
      if (!is_null($values['is_private']))
			  $playlist->is_private = $values['is_private'];
      $playlist->save();

      //Photo upload for playlist
      if (!empty($_FILES['image']['size']) && $_FILES['image']['size'] > 0) {
        $previousPhoto = $playlist->photo_id;
        if ($previousPhoto) {
          $playlistPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $playlistPhoto->delete();
        }
        $playlist->setPhoto($_FILES['image'], 'mainPhoto');
      }

      if (isset($values['remove_photo']) && !empty($values['remove_photo'])) { die('asd');
        $storage = Engine_Api::_()->getItem('storage_file', $playlist->photo_id);
        $playlist->photo_id = 0;
        $playlist->save();
        if ($storage)
          $storage->delete();
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->translate('Playlist edited successfully.')));
  }

  //Delete Playlist Action
  public function deleteAction() {

    $playlist = Engine_Api::_()->getItem('sesvideo_playlist', $this->getRequest()->getParam('playlist_id'));
      
    if (!$playlist) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Playlist doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }

    $db = $playlist->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      //Delete all playlist videos which is related to this playlist
      Engine_Api::_()->getDbtable('playlistvideos', 'sesvideo')->delete(array('playlist_id =?' => $this->_getParam('playlist_id')));
      $playlist->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected playlist has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$this->view->message));
  }

  public function createAction() {

    //Check auth
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"user_not_autheticate",'result'=>""));

    if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'addplaylist_video')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"user_not_autheticate",'result'=>""));
     $playArray = array();
    //Set song
    $video = Engine_Api::_()->getItem('video', $this->_getParam('video_id'));
    $video_id = $video->video_id;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //Get form
    $this->view->form = $form = new Sesvideo_Form_Append();
    
    $playlistCount = Engine_Api::_()->getDbtable('playlists', 'sesvideo')->getPlaylistsCount(array('viewer_id' => $viewer->getIdentity(), 'column_name' => array('playlist_id', 'title')));
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'addplaylist_max');
    
    $playlists = array();
    if ($quota > count($playlistCount) || $quota == 0)
      $playArray[""] = Zend_Registry::get('Zend_Translate')->_('Create New Playlist');
        
    if ($form->playlist_id) {
      $alreadyExistsResults = Engine_Api::_()->getDbtable('playlistvideos', 'sesvideo')->getPlaylistVideos(array('column_name' => 'playlist_id', 'file_id' => $video_id));

      $allPlaylistIds = array();
      foreach ($alreadyExistsResults as $alreadyExistsResult) {
        $allPlaylistIds[] = $alreadyExistsResult['playlist_id'];
      }

      //Populate form
      $playlistTable = Engine_Api::_()->getDbtable('playlists', 'sesvideo');
      $select = $playlistTable->select()
              ->from($playlistTable, array('playlist_id', 'title'));

      if ($allPlaylistIds) {
        $select->where($playlistTable->info('name') . '.playlist_id NOT IN(?)', $allPlaylistIds);
      }

      $select->where('owner_id = ?', $viewer->getIdentity());
      $playlists = $playlistTable->fetchAll($select);
      if ($playlists)
        $playlists = $playlists->toArray();
     
      foreach ($playlists as $playlist){
        $playArray[$playlist['playlist_id']] = html_entity_decode($playlist['title']);
      }
      $form->playlist_id->setMultiOptions($playArray);
    }
    $form->removeElement('cancel');
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
    
    
    //Get values
    $values = $form->getValues();
    if (empty($values['playlist_id']) && empty($values['title'])){
      $message = ('Please enter a title or select a playlist.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message,'result'=>""));
    }

    //Process
    $playlistVideoTable = Engine_Api::_()->getDbtable('playlists', 'sesvideo');
    $db = $playlistVideoTable->getAdapter();
    $db->beginTransaction();
    try {
      //Existing playlist
      if (!empty($values['playlist_id'])) {
        $playlist = Engine_Api::_()->getItem('sesvideo_playlist', $values['playlist_id']);
        //Already exists in playlist
        $alreadyExists = Engine_Api::_()->getDbtable('playlistvideos', 'sesvideo')->checkVideosAlready(array('column_name' => 'playlistvideo_id', 'playlist_id' => $playlist->getIdentity(), 'file_id' => $video->file_id, 'playlistvideo_id' => $video_id));
        if ($alreadyExists){
          $message = ($this->view->translate("This playlist already has this video."));
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message,'result'=>"")); 
        }
      }
      //New playlist
      else {
        $playlist = $playlistVideoTable->createRow();
        $playlist->title = trim($values['title']);
        $playlist->description = $values['description'];
        $playlist->owner_id = $viewer->getIdentity();
        $playlist->save();
      }
      $playlist->video_count++;
      $playlist->save();
      //Add song
      $playlist->addVideo($video->file_id, $video_id);
      $playlistID = $playlist->getIdentity();

      //Photo upload for playlist
      if (!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) {
        $previousPhoto = $playlist->photo_id;
        if ($previousPhoto) {
          $playlistPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $playlistPhoto->delete();
        }
        $playlist->setPhoto($_FILES['image'], 'mainPhoto');
      }
      if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
        $storage = Engine_Api::_()->getItem('storage_file', $playlist->photo_id);
        $playlist->photo_id = 0;
        $playlist->save();
        if ($storage)
          $storage->delete();
      }
      $this->view->playlist = $playlist;

      //Activity Feed work
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $video, "sesvideo_playlist_create", '', array('playlist' => array($playlist->getType(), $playlist->getIdentity()),
      ));
      if ($action) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
      }

      $db->commit();
      //Response
      $this->view->success = true;
      $message = $this->view->translate('Video has been successfully added to your playlist.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$message)); 
      
    } catch (Sesvideo_Model_Exception $e) {
      $this->view->success = false;
      $this->view->error = $this->view->translate($e->getMessage());
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
    } catch (Exception $e) {
      $this->view->success = false;
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
    }
  }

}
