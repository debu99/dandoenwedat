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

class Sesmusic_PlaylistController extends Sesapi_Controller_Action_Standard {
  protected $_permission;
  public function init() {
    //Get viewer info
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    //Get subject
    if (null !== ($playlist_id = $this->_getParam('playlist_id')) && null !== ($playlist = Engine_Api::_()->getItem('sesmusic_playlist', $playlist_id)) && $playlist instanceof Sesmusic_Model_Playlist && !Engine_Api::_()->core()->hasSubject()) {
      Engine_Api::_()->core()->setSubject($playlist);
    }
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    $authorizationApi = Engine_Api::_()->authorization();
    $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
    $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = 0;
      else
        $showRating = 1;
    }else
      $showRating = 1;
    $this->_permission = array('canCreateAlbums'=>Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'create') ,'canAlbumAddPlaylist' => $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_album')  ,'canAlbumAddFavourite'=>$authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_album')  ,'canSongShowRating'=>$showRating,'canAddPlaylistAlbumSong'=>$authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_song'),'addfavouriteAlbumSong'=>$authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_song'));
    if(isset($playlist)){
      $this->_permission['canEdit'] = $playlist->authorization()->isAllowed( $viewer, 'edit_song');
      $this->_permission['canDelete'] = $playlist->authorization()->isAllowed( $viewer, 'delete_song');
    }
  }
  public function searchFormAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $album_id = $this->_getParam('album_id',0);

    $searchOptionsType = array('searchBox', 'category', 'show', 'artists');

    $formFilter = new Sesmusic_Form_SearchPlaylist(array('fromApi'=>true));

    $formFilter->popularity->setValue('creation_date');
    $formFilter->populate($_POST);
    $values = $formFilter->getValues();

    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($formFilter,true);
    $this->generateFormFields($formFields);
  }
  public function browseAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $album_id = $this->_getParam('album_id',0);

    $searchOptionsType = array('searchBox', 'category', 'show', 'artists');

    $formFilter = new Sesmusic_Form_SearchPlaylist(array('fromApi'=>true));

    $formFilter->popularity->setValue('creation_date');
    $formFilter->populate($_POST);
    $values = $formFilter->getValues();
    $values = array_filter($values);
    if (@$values['show'] == 2 && $viewer->getIdentity())
      $values['users'] = $viewer->membership()->getMembershipsOfIds();

    if(!empty($_POST['title_name']))
      $values['title'] = $_POST['title_name'];
    $values['paginator']  = true;
    $values['column'] = "*";

    $type = $this->_getParam('type','');
    if ($type == "manage" && $viewer->getIdentity()){
      $values['user'] = $viewer->getIdentity();
    }elseif ($type == "manage"){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $paginator = Engine_Api::_()->getDbTable('playlists', 'sesmusic')->getPlaylistPaginator($values);
    $paginator->setItemCountPerPage($this->_getParam('limit',20));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $songs = $this->getPlaylists($paginator,$type);
    $result = $songs;
    $result['permission'] = $this->_permission;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No songs created yet.', 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

  }
  function getPlaylists($paginator,$manage = ""){

      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();

      foreach($paginator as $albums){

        $album = $albums->toArray();
        $description = strip_tags($albums->getDescription());
        $description = preg_replace('/\s+/', ' ', $description);
        unset($album['description']);
        $album['owner_id'] = $albums->getOwner()->getIdentity();
        $album['user_title'] = $albums->getOwner()->getTitle();
        $album['description'] = $description;
        $album['resource_type'] = $albums->getType();
        if($viewer->getIdentity() != 0){
          $album['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($albums,'favourites','sesmusic','sesmusic_playlist');
          $album['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($albums,'favourites','sesmusic','sesmusic_playlist');
        }

        if($manage){
           $viewer = Engine_Api::_()->user()->getViewer();
            $menuoptions= array();
            $canEdit = Engine_Api::_()->user()->getViewer()->getIdentity() == $albums->owner_id;
            $counterMenu = 0;
            if($canEdit){
              $menuoptions[$counterMenu]['name'] = "edit";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
              $counterMenu++;
            }
            if($canEdit){
              $menuoptions[$counterMenu]['name'] = "delete";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
            }
            $album['menus'] = $menuoptions;
        }

        $result['playlists'][$counterLoop] = $album;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($albums,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(false,$albums->getPhotoUrl());
        $result['playlists'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }
      return $result;

  }


  //Delete Playlist Action
  public function deleteAction() {

    $playlist = Engine_Api::_()->getItem('sesmusic_playlist', $this->getRequest()->getParam('playlist_id'));

    if (!$playlist) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Playlist doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    $db = $playlist->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      //Delete all playlist songs which is related to this playlist
      Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->delete(array('playlist_id =?' => $this->_getParam('playlist_id')));
      $playlist->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected playlist has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
  }

  //Delete playlist songs Action
  public function deletePlaylistsongAction() {

    //Get song/playlist
    $song = Engine_Api::_()->getItem('sesmusic_playlistsongs', $this->_getParam('playlistsong_id'));
    $playlist = $song->getParent();

    //Check song/playlist
    if (!$song || !$playlist) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    //Get file
    $file = Engine_Api::_()->getItem('storage_file', $song->file_id);
    if (!$file) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Invalid playlist');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    $db = $song->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->delete(array('playlistsong_id =?' => $this->_getParam('playlistsong_id')));
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      $this->view->success = false;
      $this->view->error = $this->view->translate('Unknown database error');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate('Playlist song deleted successfully.')));
  }

}
