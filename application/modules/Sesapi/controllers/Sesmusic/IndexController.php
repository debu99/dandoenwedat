<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesmusic_IndexController extends Sesapi_Controller_Action_Standard
{
  protected $_permission;
  public function init() {
		// only show to member_level if authorized
    if (!$this->_helper->requireAuth()->setAuthParams('sesmusic_album', null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      $id = $this->_getParam('album_id',0);
      if ($id) {
        $album = Engine_Api::_()->getItem('sesmusic_albums', $id);
        if ($album) {
            Engine_Api::_()->core()->setSubject($album);
        }
      }
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    $authorizationApi = Engine_Api::_()->authorization();
    $allowShowRating = $settings->getSetting('sesmusic.ratealbum.show', 1);
    $allowRating = $settings->getSetting('sesmusic.album.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = 0;
      else
        $showRating = 1;
    }
    else
      $showRating = 1;
    $this->_permission = array('canCreateAlbums'=>Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'create') ,'canAlbumAddPlaylist' => $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_album')  ,'canAlbumAddFavourite'=>$authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_album')  ,'canAlbumShowRating'=>$showRating);
    if(isset($album)){
      $this->_permission['canEdit'] = $album->authorization()->isAllowed($viewer, 'edit');
      $this->_permission['canDelete'] = $album->authorization()->isAllowed($viewer, 'delete');
    }
  }
  public function searchFormAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $searchOptionsType = array('searchBox', 'category', 'view', 'show', 'artists');
    $formFilter = new Sesmusic_Form_SearchAlbums();
    $formFilter->popularity->setValue('creation_date');
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($formFilter,true);
    $this->generateFormFields($formFields);
  }

  public function browseAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $searchOptionsType = array('searchBox', 'category', 'view', 'show', 'artists');
    $formFilter = new Sesmusic_Form_SearchAlbums();
    $formFilter->popularity->setValue('creation_date');
    $formFilter->populate($_POST);
    $values = $formFilter->getValues();
    $values = array_filter($values);
    if (@$values['show'] == 2 && $viewer->getIdentity())
      $values['users'] = $viewer->membership()->getMembershipsOfIds();

    $type = $this->_getParam('type','');
    if ($type == "manage" && $viewer->getIdentity()){
      $values['users'] = $viewer->getIdentity();
    }elseif ($type == "manage"){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    if(!empty($_POST['title_name']))
      $values['title'] = $_POST['title_name'];
    $category_id = $this->_getParam('category_id',0);
    if($category_id){
      $values['category_id'] = $category_id;
      $category = Engine_Api::_()->getItem('sesmusic_categories',$category_id);
      if($category)
        $categoryName = $category->category_name;
    }
    if (!empty($_POST['user_id']))
      $values["user"] = $_POST['user_id'];
    $paginator = Engine_Api::_()->getDbTable('albums', 'sesmusic')->getPlaylistPaginator($values);
    $paginator->setItemCountPerPage($this->_getParam('limit',20));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $albums = $this->getAlbums($paginator,$type);
    $result = $albums;
    if(!empty($categoryName))
      $result['category_title'] = $categoryName;
    $result['permission'] = $this->_permission;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No music album created yet.', 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  function getAlbums($paginator,$manage = ""){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();

      foreach($paginator as $albums){
        $album = $albums->toArray();
        $description = strip_tags($albums->getDescription());
        $description = preg_replace('/\s+/', ' ', $description);
        unset($album['description']);
        $album['user_title'] = Engine_Api::_()->getItem('user',$album['owner_id'])->getTitle();
        $album['description'] = $description;
        $album['resource_type'] = $albums->getType();
        if($manage){
            $viewer = Engine_Api::_()->user()->getViewer();
            $menuoptions= array();
            $canEdit = $this->_helper->requireAuth()->setAuthParams($albums, null, 'edit')->isValid();
            $counterMenu = 0;
            if($canEdit){
              $menuoptions[$counterMenu]['name'] = "edit";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
              $counterMenu++;
            }
            $canDelete = $this->_helper->requireAuth()->setAuthParams($albums, null, 'delete')->isValid();
            if($canDelete){
              $menuoptions[$counterMenu]['name'] = "delete";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
            }
            $album['menus'] = $menuoptions;
        }
        if($viewer->getIdentity() != 0){
          $album['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($albums);
          $album['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($albums);
          $album['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($albums,'favourites','sesmusic','sesmusic_albums');
          $album['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($albums,'favourites','sesmusic','sesmusic_albums');
        }

        $result['albums'][$counterLoop] = $album;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($albums,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$albums->getPhotoUrl());
        $result['albums'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }
      return $result;
  }
   //remove cover photo action
	public function removePhotoAction(){
		$item_id = $this->_getParam('album_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_album', $item_id);
		if ($item_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));
		if(isset($item->photo_id) && $item->photo_id > 0){
			$im = Engine_Api::_()->getItem('storage_file', $item->photo_id);
			$item->photo_id = 0;
			$item->save();
			$im->delete();
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->translate("Music album photo removed successfully.")));
	}
	//remove cover photo action
	public function removeCoverAction(){
		$album_id = $this->_getParam('album_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_album', $album_id);
		if ($album_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));
		if(isset($item->album_cover) && $item->album_cover > 0){
			$im = Engine_Api::_()->getItem('storage_file', $item->album_cover);
			$item->album_cover = 0;
			$item->save();
			$im->delete();
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->translate("Music album cover removed successfully.")));
	}

  //update cover photo function
	public function editCoverphotoAction(){
		$item_id = $this->_getParam('album_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_album', $item_id);
		if ($item_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));

		$art_cover = $item->album_cover;
    $resource_type = $this->_getParam('resource_type','');
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photo = Engine_Api::_()->getItem('album_photo',$photo_id);
    }

    if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) || !empty($photo)) {
      $db = $item->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        if(!empty($photo))
          $file = $photo;
        else
          $file = $_FILES['image'];
        $item->setAlbumCover($file);

        if($art_cover != 0){
          $im = Engine_Api::_()->getItem('storage_file', $art_cover);
          $im->delete();
        }
        $db->commit();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your cover photo updated successfully.")));
      }
      // If an exception occurred within the image adapter, it's probably an invalid image
      catch( Engine_Image_Adapter_Exception $e )
      {
         $db->rollBack();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The uploaded file is not supported or is corrupt.'), 'result' => array()));
      }
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
	}
  //update cover photo function
	public function editMainphotoAction(){
		$item_id = $this->_getParam('album_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_album', $item_id);
		if ($item_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));

		$art_cover = $item->photo_id;
    $resource_type = $this->_getParam('resource_type','');
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photo = Engine_Api::_()->getItem('album_photo',$photo_id);
    }

    if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) || !empty($photo)) {
      $db = $item->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        if(!empty($photo))
          $file = $photo;
        else
          $file = $_FILES['image'];
        $item->setPhoto($file);

        if($art_cover != 0){
          $im = Engine_Api::_()->getItem('storage_file', $art_cover);
          $im->delete();
        }
        $db->commit();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your music album photo updated successfully.")));
      }
      // If an exception occurred within the image adapter, it's probably an invalid image
      catch( Engine_Image_Adapter_Exception $e )
      {
         $db->rollBack();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The uploaded file is not supported or is corrupt.'), 'result' => array()));
      }
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
	}
  //Album Delete Action
  public function deleteAction() {

    $album = Engine_Api::_()->getItem('sesmusic_album', $this->getRequest()->getParam('album_id'));

    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));

    if (!$album) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Album doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }


    $ratingTable = Engine_Api::_()->getDbtable('ratings', 'sesmusic');
    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();
    try {
//All songs delete from the album
      foreach ($album->getSongs() as $song) {
        $ratingTable->delete(array('resource_id =?' => $song->albumsong_id, 'resource_type =?' => 'sesmusic_albumsong'));
        Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->delete(array('albumsong_id =?' => $song->albumsong_id));
        $song->deleteUnused();
      }
//Delete rating accociate with deleted album
      $ratingTable->delete(array('resource_id =?' => $album->album_id, 'resource_type =?' => 'sesmusic_album'));
      $album->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected albums has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
  }
  //Rating Action
  public function rateAction() {

    $rating = $this->_getParam('rating');
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $item = Engine_Api::_()->getItem($resource_type, $resource_id);

    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
    $activityStreamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $table = Engine_Api::_()->getDbtable('ratings', 'sesmusic');

    $db = $table->getAdapter();
    $db->beginTransaction();
    try {

      $table->setRating($resource_id, $viewer_id, $rating, $resource_type);
      $item = Engine_Api::_()->getItem($resource_type, $resource_id);
      $item->rating = $table->getRating($item->getIdentity(), $resource_type);
      $rating_sum = $table->getSumRating($item->getIdentity(), $resource_type);
      $item->save();

      if ($resource_type == 'sesmusic_album') {
        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "sesmusic_album_rating", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
        $owner = $item->getOwner();
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'sesmusic_album_rating');
        $result = $activityTable->fetchRow(array('type =?' => "sesmusic_album_rating", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
        if (!$result) {
          $action = $activityTable->addActivity($viewer, $item, 'sesmusic_album_rating');
          if ($action)
            $activityTable->attachActivity($action, $item);
        }
      } elseif ($resource_type == 'sesmusic_albumsong') {
        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "sesmusic_albumsong_rating", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
        $owner = $item->getOwner();
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'sesmusic_albumsong_rating');
        $result = $activityTable->fetchRow(array('type =?' => "sesmusic_albumsong_rating", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
        if (!$result) {
          $action = $activityTable->addActivity($viewer, $item, 'sesmusic_albumsong_rating');
          if ($action) {
            $activityStreamTable->delete(array('action_id =?' => $action->action_id));
            $db->query("INSERT INTO `engine4_activity_stream` (`target_type`, `target_id`, `subject_type`, `subject_id`, `object_type`, `object_id`, `type`, `action_id`) VALUES
('everyone', 0, 'user', $viewer_id, 'sesmusic_albumsong', $resource_id, 'sesmusic_albumsong_rating', $action->action_id),
('members', $viewer_id, 'user', $viewer_id, 'sesmusic_albumsong', $resource_id, 'sesmusic_albumsong_rating', $action->action_id),
('owner', $viewer_id, 'user', $viewer_id, 'sesmusic_albumsong', $resource_id, 'sesmusic_albumsong_rating', $action->action_id),
('parent', $viewer_id, 'user', $viewer_id, 'sesmusic_albumsong', $resource_id, 'sesmusic_albumsong_rating', $action->action_id),
('registered', 0, 'user', $viewer_id, 'sesmusic_albumsong', $resource_id, 'sesmusic_albumsong_rating', $action->action_id);");
            $activityTable->attachActivity($action, $item);
          }
        }
      } elseif ($resource_type == 'sesmusic_artists') {
        $result = $activityTable->fetchRow(array('type =?' => "sesmusic_artist_rating", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
        if (!$result) {
          $action = $activityTable->addActivity($viewer, $item, 'sesmusic_artist_rating');
          if ($action) {
            $activityStreamTable->delete(array('action_id =?' => $action->action_id));
            $db->query("INSERT INTO `engine4_activity_stream` (`target_type`, `target_id`, `subject_type`, `subject_id`, `object_type`, `object_id`, `type`, `action_id`) VALUES
('everyone', 0, 'user', $viewer_id, 'sesmusic_artist', $resource_id, 'sesmusic_artist_rating', $action->action_id),
('members', $viewer_id, 'user', $viewer_id, 'sesmusic_artist', $resource_id, 'sesmusic_artist_rating', $action->action_id),
('owner', $viewer_id, 'user', $viewer_id, 'sesmusic_artist', $resource_id, 'sesmusic_artist_rating', $action->action_id),
('parent', $viewer_id, 'user', $viewer_id, 'sesmusic_artist', $resource_id, 'sesmusic_artist_rating', $action->action_id),
('registered', 0, 'user', $viewer_id, 'sesmusic_artist', $resource_id, 'sesmusic_artist_rating', $action->action_id);");
            $activityTable->attachActivity($action, $item);
          }
        }
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Rating done successfully.'), 'result' => array()));
  }
  public function editAction(){
    if(!Engine_Api::_()->core()->hasSubject()){
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
    }
    $album = Engine_Api::_()->core()->getSubject();
    //Only members can upload music
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'edit')->isValid())
				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => array()));

    //Get form
    $this->view->form = $form = new Sesmusic_Form_Edit(array('fromApi'=>true));
    $form->populate($album->toArray());
    $form->removeElement('file');
    $form->removeElement('album_cover');
    $form->removeElement('musicalbum_cover_preview');
    $form->removeElement('remove_album_cover');
    $form->removeElement('musicalbum_main_preview');
    $form->removeElement('remove_album_main');
    $form->removeElement('album_id');
    $form->removeElement('cancel');

    if($album->category_id && $form->getElement('category_id'))
      $form->getElement('category_id')->setValue($album->category_id);
    if($album->subcat_id && $form->getElement('subcat_id'))
      $form->getElement('subcat_id')->setValue($album->subcat_id);
    if($album->subsubcat_id && $form->getElement('subsubcat_id'))
      $form->getElement('subsubcat_id')->setValue($album->subsubcat_id);
    if($this->_getParam('getForm')) {

      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory
      $newFormFieldsArray = array();
      if(count($formFields) && $album->category_id){
            foreach($formFields as $fields){
              foreach($fields as $field){
                  $subcat = array();
                  if($fields['name'] == "subcat_id"){
                    $subcat = Engine_Api::_()->getItemTable('sesmusic_categories')->getModuleSubcategory(array('category_id'=>$album->category_id,'column_name'=>'*','param'=>'album'));
                  }else if($fields['name'] == "subsubcat_id"){
                    if($album->subcat_id)
                    $subcat = Engine_Api::_()->getItemTable('sesmusic_categories')->getModuleSubSubcategory(array('category_id'=>$album->subcat_id,'column_name'=>'*','param'=>'album'));
                  }
                    if(count($subcat)){
                      $arrayCat = array();
                      foreach($subcat as $cat){
                        $arrayCat[$cat->getIdentity()] = $cat->getTitle();
                      }
                      $fields["multiOptions"] = $arrayCat;
                    }
              }
              $newFormFieldsArray[] = $fields;
            }
            if(!count($newFormFieldsArray))
              $newFormFieldsArray = $formFields;
        $this->generateFormFields($newFormFieldsArray);
      }
      $this->generateFormFields($formFields);
    }

     // Check if valid
    if( !$form->isValid($_POST) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }

     $db = Engine_Api::_()->getDbTable('albums', 'sesmusic')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      $album->title = $values['title'];
      $album->description = $values['description'];
      $album->search = $values['search'];
      if (isset($values['category_id']))
        $album->category_id = $values['category_id'];
      if (isset($values['subcat_id']))
        $album->subcat_id = $values['subcat_id'];
      if (isset($values['subsubcat_id']))
        $album->subsubcat_id = $values['subsubcat_id'];
      if (isset($values['label']))
        $album->label = $values['label'];

   $_roles = array(
      'everyone' => 'Everyone',
      'registered' => 'All Registered Members',
      'owner_network' => 'Friends and Networks',
      'owner_member_member' => 'Friends of Friends',
      'owner_member' => 'Friends Only',
      'owner' => 'Just Me'
  );
      //Authorizations
    $auth = Engine_Api::_()->authorization()->context;
    $prev_allow_comment = $prev_allow_view = false;
    foreach ($_roles as $role => $role_label) {
      //Allow viewers
      if ($values['auth_view'] == $role || $prev_allow_view) {
        $auth->setAllowed($album, $role, 'view', true);
        $prev_allow_view = true;
      } else
        $auth->setAllowed($album, $role, 'view', 0);

      //Allow comments
      if ($values['auth_comment'] == $role || $prev_allow_comment) {
        $auth->setAllowed($album, $role, 'comment', true);
        $prev_allow_comment = true;
      } else
        $auth->setAllowed($album, $role, 'comment', 0);
    }

      //Rebuild privacy
    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
    foreach ($actionTable->getActionsByObject($album) as $action) {
      $actionTable->resetActivityBindings($action);
    }

    if (!empty($_FILES['image']['size']))
      $album->setPhoto($_FILES['image']);

      $activity = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activity->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'sesmusic_album_addnew', null, array());
      if (null !== $action)
        $activity->attachActivity($action, $album);

      $db->commit();

     //Count Songs according to album_id
      $song_count = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->songsCount($album->album_id);
      $album->song_count = $song_count;
      $album->save();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Music album edited successfully.")));
  }
    //Edit Action
  public function editAlbumAction() {
    //Catch uploads from FLASH fancy-uploader and redirect to uploadSongAction()


    //Only members can upload music
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


    if (!$this->_helper->requireSubject('sesmusic_album')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


    //Get album
    $this->view->album = $album = Engine_Api::_()->core()->getSubject('sesmusic_album');

    //Only user and admins and moderators can edit

		if($album->resource_type == 'sesblog_blog') {
			$blog = Engine_Api::_()->getItem('sesblog_blog', $album->resource_id);
			if (!Engine_Api::_()->sesblog()->checkBlogAdmin($blog) && !$this->_helper->requireAuth()->setAuthParams($album, null, 'edit')->isValid()) {
				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

			}
		}
		else {
			if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'edit')->isValid()) {
				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

			}
		}

    //Make form
    $this->view->form = $form = new Sesmusic_Form_Edit();

    if ($album->category_id)
      $form->category_id->setValue($album->category_id);

    if ($album->subcat_id)
      $form->subcat_id->setValue($album->subcat_id);

    if ($album->subsubcat_id)
      $form->subsubcat_id->setValue($album->subsubcat_id);

    $form->populate($album);

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

    $db = Engine_Api::_()->getDbTable('albums', 'sesmusic')->getAdapter();
    $db->beginTransaction();
    try {

      $form->saveValues();
      $db->commit();

      //Count Songs according to album_id
      $song_count = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->songsCount($album->album_id);
      $album->song_count = $song_count;
      $album->save();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

    }
		if($album->resource_type == 'sesblog_blog') {
			$tab_id = Engine_Api::_()->sesbasic()->getWidgetTabId(array('name' => 'sesblog.profile-musicalbums'));
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('album_id'=>$album->album_id,'message'=>$this->view->translate('Album Edited successfully.'))));
		}
		else {
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('album_id'=>$album->album_id,'message'=>$this->view->translate('Album Edited successfully.'))));
		}
  }
	protected function handleIframelyInformation($uri) {
		$iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
		if (parse_url($uri, PHP_URL_SCHEME) === null) {
				$uri = "http://" . $uri;
		}
		$uriHost = Zend_Uri::factory($uri)->getHost();
		if ($iframelyDisallowHost && in_array($uriHost, $iframelyDisallowHost)) {
				return;
		}
		$config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
		$iframely = Engine_Iframely::factory($config)->get($uri);
		if (!in_array('player', array_keys($iframely['links']))) {
				return;
		}
		$information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
		if (!empty($iframely['links']['thumbnail'])) {
				$information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
				if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
						$information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
						$information['thumbnail'] = "http://" . $information['thumbnail'];
				}
		}
		if (!empty($iframely['meta']['title'])) {
				$information['title'] = $iframely['meta']['title'];
		}
		if (!empty($iframely['meta']['description'])) {
				$information['description'] = $iframely['meta']['description'];
		}
		if (!empty($iframely['meta']['duration'])) {
				$information['duration'] = $iframely['meta']['duration'];
		}
		$information['code'] = $iframely['html'];
		return $information;
  }

  public function createAction(){
    //Only members can upload music
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


    if (!$this->_helper->requireAuth()->setAuthParams('sesmusic_album', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


    //Check for single song
    $this->view->uploadCheck = $uploadCheck = Zend_Controller_Front::getInstance()->getRequest()->getParam('upload', null);

    $parent_type = $this->_getParam('resource_type', null);
    $parent_id =  $this->_getParam('resource_id', null);

		if($parent_id && $parent_type && $parent_type == 'sesblog_blog'){
			$blog	= Engine_Api::_()->getItem($parent_type,$parent_id);
			if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesblogpackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblogpackage.enable.package', 1)){
				$package = $blog->getPackage();
				$musicLeft = $package->allowUploadMusic($blog->orderspackage_id,true);
				if(!$musicLeft)
					Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
			}
		}

		$sesmusic_create = Zend_Registry::isRegistered('sesmusic_create') ? Zend_Registry::get('sesmusic_create') : null;
    //if(!empty($sesmusic_create)) {
      //Get form
      $this->view->form = $form = new Sesmusic_Form_Create();
    //}
		if(isset($track) && count($track)){
			$form->populate(array('title'=>$track->title,'description'=>$track->description));
		}
    $this->view->album_id = $this->_getParam('album_id', '0');
    $form->removeElement('musicalbum_cover_preview');
    $form->removeElement('musicalbum_main_preview');
    $form->removeElement('soundcloudIds');
    $form->removeElement('options');
		if($uploadCheck == 'song' ){
			$form->removeElement('title');
			$form->removeElement('description');
			$form->removeElement('checking');
		}
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
    // Check only for once song
      if(count($_FILES['musicupload']) == 0 || empty($_FILES['musicupload']['name'])) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Upload at least one song - it is required."), 'result' => array()));
      }
		// check for video
		if($uploadCheck == 'song' && $_POST['youtube_video']){
			$information = $this->handleIframelyInformation($_POST['youtube_video']);
			if (empty($information)) {
				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("video does not exist."), 'result' => array()));
			}else{
				$form->is_video_found->setValue(1);
			}
		}
    //Process
    $db = Engine_Api::_()->getDbTable('albums', 'sesmusic')->getAdapter();
    $db->beginTransaction();
    try {
      if(!empty($_FILES['musicupload'])){
        $counter = 0;
        $fancyId = array();
        foreach($_FILES['musicupload']['name'] as $key => $upload){
            $file = array();
            $file['name'] = $upload;
            $file['tmp_name'] = $_FILES['musicupload']['tmp_name'][$key];
            $file['type'] = $_FILES['musicupload']['type'][$key];
            $file['error'] = $_FILES['musicupload']['error'][$key];
            $file['size'] = $_FILES['musicupload']['size'][$key];
            $fileUpload = $this->uploadSong($file);
            $fancyId[] = $fileUpload->getIdentity();
        }
        $form->fancyuploadfileids->setValue(implode(' ',$fancyId));
      }
      $album = $this->view->form->saveValues();
      $db->commit();
      //Count Songs according to album_id
      $song_count = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->songsCount($album->album_id);
      $album->song_count = $song_count;
      $album->save();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }

    //Single Song Work
    if($uploadCheck == 'song') {
      $getAllSongs = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->getAllSongs($album->getIdentity());
      $albumsong_id = $getAllSongs[0]['albumsong_id'];
      if($albumsong_id) {
        //Feed generate when single song upload
        $activity = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $activity->addActivity(Engine_Api::_()->user()->getViewer(), $getAllSongs[0], 'sesmusic_song_new');
        if (null !== $action)
          $activity->attachActivity($action, $getAllSongs[0]);
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('song_id'=>$albumsong_id,'message'=>$this->view->translate('Song created successfully.'))));
      }
    } else {
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('album_id'=>$album->getIdentity(),'message'=>$this->view->translate('Music Album created successfully.'))));
    }
  }
  function uploadSong($file){
    $db = Engine_Api::_()->getDbtable('albums', 'sesmusic')->getAdapter();
    $db->beginTransaction();
    try {
      $song = Engine_Api::_()->getApi('core', 'sesmusic')->createSong($file);
      $this->view->status = true;
      $this->view->song = $song;
      $this->view->albumsong_id = $song->getIdentity();
      $this->view->song_url = $song->getHref();
      $db->commit();
      return $song;
    } catch (Sesmusic_Model_Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
}
