<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ArtistController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesmusic_ArtistController extends Sesapi_Controller_Action_Standard {
   protected $_permission;
   public function init(){
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
  //Browse Action
  public function browseAction() {


    $viewer = Engine_Api::_()->user()->getViewer();
    $album_id = $this->_getParam('album_id',0);

    $searchOptionsType = array('searchBox', 'category', 'show', 'artists');

    $formFilter = new Sesmusic_Form_SearchArtist(array("fromApi"=>true));

    $formFilter->populate($_POST);
    $values = $formFilter->getValues();
    $values = array_filter($values);
    if (@$values['show'] == 2 && $viewer->getIdentity())
      $values['users'] = $viewer->membership()->getMembershipsOfIds();

    $values['paginator']  = true;
    $values['column'] = "*";
    $paginator = Engine_Api::_()->getDbTable('artists', 'sesmusic')->getArtistsPaginator($values);
    $paginator->setItemCountPerPage($this->_getParam('limit',20));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $songs = $this->getArtists($paginator);
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

  function getArtists($paginator){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      foreach($paginator as $albums){
        $album = $albums->toArray();
        $description = strip_tags($albums->getDescription());
        $description = preg_replace('/\s+/', ' ', $description);
        unset($album['description']);
        $album['description'] = $description;
        $album['resource_type'] = $albums->getType();
        if($viewer->getIdentity() != 0){
          $album['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($albums,'favourites','sesmusic','sesmusic_artist');
          $album['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($albums,'favourites','sesmusic','sesmusic_artist');
        }
        $result['artists'][$counterLoop] = $album;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($albums->artist_photo,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(false,$albums->getPhotoUrl());
        $result['artists'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }
      return $result;


  }

}
