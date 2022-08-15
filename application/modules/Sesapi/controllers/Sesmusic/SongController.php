<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: SongController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesmusic_SongController extends Sesapi_Controller_Action_Standard
{
  protected $_permission;
  public function init() {
		// only show to member_level if authorized
    if (!$this->_helper->requireAuth()->setAuthParams('sesmusic_album', null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      $id = $this->_getParam('song_id',$this->_getParam('albumsong_id',0));
      if ($id) {
        $song = Engine_Api::_()->getItem('sesmusic_albumsongs', $id);
        if ($song) {
            Engine_Api::_()->core()->setSubject($song);
        }
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
    if(isset($song)){
      $this->_permission['canEdit'] = $song->authorization()->isAllowed( $viewer, 'edit_song');
      $this->_permission['canDelete'] = $song->authorization()->isAllowed( $viewer, 'delete_song');
    }
  }
  public function searchFormAction(){
      $viewer = Engine_Api::_()->user()->getViewer();

    $searchOptionsType = array('searchBox', 'category', 'show', 'artists');

    $formFilter = new Sesmusic_Form_SearchSongs();

    $formFilter->popularity->setValue('creation_date');
    $formFilter->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($formFilter,true);
    $this->generateFormFields($formFields);
  }
  public function browseAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $album_id = $this->_getParam('album_id',0);

    $searchOptionsType = array('searchBox', 'category', 'show', 'artists');

    $formFilter = new Sesmusic_Form_SearchSongs();

    $formFilter->popularity->setValue('creation_date');
    $formFilter->populate($_POST);
    $values = $formFilter->getValues();
    $values = array_filter($values);
    if (@$values['show'] == 2 && $viewer->getIdentity())
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    if( $this->_getParam('lyrics', false)){
       $values['widgteName'] = 'Lyrics Action';
    }

    if(!empty($_POST['title_song']))
      $values['title'] = $_POST['title_song'];
    $category_id = $this->_getParam('category_id',0);
    if($category_id){
      $values['category_id'] = $category_id;
      $category = Engine_Api::_()->getItem('sesmusic_categories',$category_id);
      if($category)
        $categoryName = $category->category_name;
    }

    $values['paginator']  = true;
    $values['column'] = "*";
		if($this->_getParam('category_id')){
			$values['category_id'] = $this->_getParam('category_id');
		}

    $paginator = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->widgetResults($values);
    $paginator->setItemCountPerPage($this->_getParam('limit',20));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $songs = $this->getAlbums($paginator);
    $result = $songs;
    if(!empty($categoryName))
      $result['category_title'] = $categoryName;
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
  public function artistViewAction(){
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    $artist_id = $this->_getParam('artist_id',0);
    $artist = Engine_Api::_()->getItem('sesmusic_artist',$artist_id);
    if(!$artist || !$artist_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));

    $response['artists'] = $artist->toArray();

    if($viewer->getIdentity() != 0){
      $response['artists']['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($artist,'favourites','sesmusic','sesmusic_artist');
      $response['artists']['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($artist,'favourites','sesmusic','sesmusic_artist');
    }
    $images = Engine_Api::_()->sesapi()->getPhotoUrls($artist->artist_photo,'','');
    if(!count($images))
      $images['main'] = $this->getBaseUrl(true,$artist->getPhotoUrl());
    $response['artists']['images'] = $images;


    //Rating work

    $authorizationApi = Engine_Api::_()->authorization();
    $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sesmusic');
    $this->view->mine = $mine = false;

    $this->view->allowShowRating = $allowShowRating = $settings->getSetting('sesmusic.rateartist.show', 1);
    $this->view->allowRating = $allowRating = $settings->getSetting('sesmusic.artist.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $this->view->showRating = $showRating;

    if ($showRating) {
      $this->view->canRate = $canRate = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'rating_artist');
      $this->view->allowRateAgain = $allowRateAgain = $settings->getSetting('sesmusic.rateartist.again', 1);
      $this->view->allowRateOwn = $allowRateOwn = $settings->getSetting('sesmusic.rateartist.own', 1);

      if ($canRate == 0 || $allowRating == 0)
        $allowRating = false;
      else
        $allowRating = true;

      if ($allowRateOwn == 0 && $mine)
        $allowMine = false;
      else
        $allowMine = true;

      $this->view->allowMine = $allowMine;
      $this->view->allowRating = $allowRating;
      $this->view->rating_type = $rating_type = 'sesmusic_artists';
      $this->view->rating_count = $ratingTable->ratingCount($artist->getIdentity(), $rating_type);
      $this->view->rated = $rated = $ratingTable->checkRated($artist->getIdentity(), $viewer->getIdentity(), $rating_type);
      if (!$allowRateAgain && $rated)
        $rated = false;
      else
        $rated = true;
      $this->view->ratedAgain = $rated;
    }

    $rate = $song->rating;
    if($viewer->getIdentity() == 0){
      $rated = 0;
      $message = $this->view->translate("Please login to rate.");
    } elseif($this->view->allowShowRating == 1 && $this->view->allowRating == 0) {
       $rated = 3;
       $message = $this->view->translate('You are not allowed to rate.');;
    } elseif($this->view->allowRateAgain == 0 && $this->view->rated) {
      $rated = 1;
      $message = $this->view->translate('You already rated.');;
    } elseif($this->view->canRate == 0 && $viewer->viewer_id != 0) {
      $rated = 4;
      $message = $this->view->translate('You are not allowed to rate.');;
    } elseif(!$this->view->allowMine) {
      $rated = 2;
      $message = $this->view->translate('Rating on own album is not allowed.');;
    } else {
      $rated = 90;
      $message = $this->view->translate("");
    }
    $condition['code'] = $rated;
    $condition['message'] = $message;
    $response['artists']['rating'] = $condition;
    $response['artists']['rating']['total_rating_average'] = $artist->rating;

    if($viewer->getIdentity()){
      $menuoptions= array();
      $counterMenu = 0;
      $menuoptions[$counterMenu]['name'] = "report";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Artist");
      $response['artists']['menus'] = $menuoptions;
    }
    $values['artists'] = $artist_id;
    $values['popularity'] = "creation_date";
    $values['paginator']  = true;
    $values['column'] = "*";
    $paginator = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->widgetResults($values);
    $paginator->setItemCountPerPage($this->_getParam('limit',20));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $songs = $this->getAlbums($paginator);
    $result = array_merge($songs,$response);
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
  public function playlistViewAction(){
    error_reporting(E_ALL); ini_set('display_errors', 1);
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    $playlist_id = $this->_getParam('playlist_id','');
    $playlist = Engine_Api::_()->getItem('sesmusic_playlist',$playlist_id);
    if(!$playlist || !$playlist_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));

    $response['playlist'] = $playlist->toArray();
    $response['playlist']['owner_title'] = $playlist->getOwner()->getTitle();
    if($viewer->getIdentity() != 0){
      $response['playlist']['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($playlist,'favourites','sesmusic','sesmusic_playlist');
      $response['playlist']['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($playlist,'favourites','sesmusic','sesmusic_playlist');
    }

    $images = Engine_Api::_()->sesapi()->getPhotoUrls($playlist->photo_id,'','');
    if(!count($images))
      $images['main'] = $this->getBaseUrl(false,$playlist->getPhotoUrl());
    $response['playlist']['images'] = $images;

    if($viewer->getIdentity()){
      $menuoptions= array();
      $canEdit = $playlist->owner_id == $viewer->getIdentity();
      $counterMenu = 0;
      if($canEdit){
        $menuoptions[$counterMenu]['name'] = "edit";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit Playlist");
        $counterMenu++;
      }
      if($canEdit){
        $menuoptions[$counterMenu]['name'] = "delete";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete Playlist");
        $counterMenu++;
      }
      $menuoptions[$counterMenu]['name'] = "report";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Playlist");
      $response['playlist']['menus'] = $menuoptions;
    }

    $values['paginator']  = true;
    $paginator = $playlist->getSongs('',$values);
    $paginator->setItemCountPerPage($this->_getParam('limit',20));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $songs = $this->getAlbums($paginator,true);
    $result = array_merge($songs,$response);
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
  function getAlbums($paginator,$getItem = false){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      $uploadoption = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmusic.uploadoption', 'myComputer');
              $consumer_key =  Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmusic.scclientid');
      foreach($paginator as $albums){
        if($getItem){
          $playlistsong_id = $albums['playlistsong_id'];
          $albums = Engine_Api::_()->getItem('sesmusic_albumsongs',$albums->albumsong_id);
          if(!$albums)
            continue;

        }
        $parent = $albums->getParent();
        if(!$parent){
          continue;
        }
        $album = $albums->toArray();
        if($getItem)
          $album['playlistsong_id'] = $playlistsong_id;
        $description = strip_tags($albums->getDescription());
        $description = preg_replace('/\s+/', ' ', $description);
        unset($album['description']);
        $album['owner_id'] = $parent->getIdentity();
        $album['user_title'] = $parent->getOwner()->getTitle();
        $album['description'] = $description;
        $album['resource_type'] = $albums->getType();
        $parentAlbum = $albums->getParent();
        if($parentAlbum)
          $album['album_title'] = $parentAlbum->getTitle();

        if ($albums->artists) {
          $artists = json_decode($albums->artists);
          $counterArtist = 0;
          $artistArray = array();
          if ($artists){
            $artists_array = Engine_Api::_()->getDbTable('artists', 'sesmusic')->getArtists(array_merge(array('all'=>true),$artists));
            foreach($artists_array as $artist){

              $artistArray[$counterArtist]['artist_id'] = $artist['artist_id'];
              $artistArray[$counterArtist]['name'] = $artist['name'];

              $images = Engine_Api::_()->sesapi()->getPhotoUrls($artist['artist_photo'],'','');
              if(!count($images))
                $images['main'] = $this->getBaseUrl(true,$albums->getPhotoUrl());

              $artistArray[$counterArtist]['images'] = $images;

              $counterArtist++;
            }
            $album['artists'] = $artistArray;
          }else
            unset($album['artists']);
        }else
          unset($album['artists']);

        $photo = $this->getBaseUrl(false,$albums->getPhotoUrl());
        if($photo)
          $album["share"]["imageUrl"] = $photo;
					$album["share"]["url"] = $this->getBaseUrl(false,$albums->getHref());
          $album["share"]["title"] = $albums->getTitle();
          $album["share"]["description"] = strip_tags($albums->getDescription());
          $album["share"]['urlParams'] = array(
              "type" => $albums->getType(),
              "id" => $albums->getIdentity()
          );
        if(is_null($album["share"]["title"]))
          unset($album["share"]["title"]);

        if($viewer->getIdentity() != 0){
          $album['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($albums);
          $album['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($albums);
          $album['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($albums,'favourites','sesmusic','sesmusic_albumsong');
          $album['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($albums,'favourites','sesmusic','sesmusic_albumsong');
        }
        if($albums->category_id){
          $category = Engine_Api::_()->getItem('sesmusic_categories',$albums->category_id);
          if($category)
            $album['category_title'] = $category->getTitle();
        }
        if(($uploadoption == 'both' || $uploadoption == 'soundCloud') && $consumer_key){
          if($albums->track_id ){
            $uploadoption = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmusic.uploadoption', 'myComputer');
            $consumer_key =  Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmusic.scclientid');
            $URL = "http://api.soundcloud.com/tracks/$albums->track_id/stream?consumer_key=$consumer_key";
          }
        }else{
          if($albums->store_link){
              $storeLink = !empty($albums->store_link) ? (preg_match("#https?://#", $albums->store_link) === 0) ? 'http://'.$albums->store_link : $albums->store_link : '';
          }
          $URL = $this->getBaseUrl(false,$albums->getFilePath());
        }

        if(!empty($URL))
          $album['song_url'] = $URL;

        if(!empty($storeLink) && !is_null($storeLink))
          $album['store_link'] = $storeLink;

        $result['songs'][$counterLoop] = $album;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($albums,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$albums->getPhotoUrl());
        $result['songs'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }
      return $result;
  }
  public function viewAction(){
    if(!Engine_Api::_()->core()->hasSubject()){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
    }
    $song = Engine_Api::_()->core()->getSubject();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $response = $this->getAlbums(array(0=>$song));
    $response = $response['songs'][0];
    $viewer = Engine_Api::_()->user()->getViewer();
    if($viewer->getIdentity()){
      $menuoptions= array();
      $canEdit = $this->_helper->requireAuth()->setAuthParams($song, null, 'edit')->isValid();
      $counterMenu = 0;
      if($canEdit){
        $menuoptions[$counterMenu]['name'] = "edit";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
        $counterMenu++;
      }
      $canDelete = $this->_helper->requireAuth()->setAuthParams($song, null, 'delete')->isValid();
      if($canDelete){
        $menuoptions[$counterMenu]['name'] = "delete";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
        $counterMenu++;
      }
      $menuoptions[$counterMenu]['name'] = "report";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Album");
      $response['menus'] = $menuoptions;
    }

    $images = Engine_Api::_()->sesapi()->getPhotoUrls($song,'','');
    if(!count($images))
      $images['main'] = $this->getBaseUrl(true,$song->getPhotoUrl());
    $response['images'] = $images;
    if($song->song_cover)
     $response['cover'] = Engine_Api::_()->sesapi()->getPhotoUrls($song->song_cover,'',"");
    else{
      $photo = $settings->getSetting('sesmusic.songcover.photo');
      if(!$photo)
        $photo = '/application/modules/Sesmusic/externals/images/banner/cover.jpg';
      $response['cover']['main'] = $this->getBaseUrl(true,$photo);
    }
    if(isset($canEdit) && $canEdit){
      $profilePhotoOptions[] = array('label'=>'Upload Photo','name'=>'upload_photo');
      $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");;
      if($isAlbumEnable)
       $profilePhotoOptions[] = array('label'=>'Choose From Albums','name'=>'choose_from_albums');
      if($song->photo_id){
        $profilePhotoOptions[] = array('label'=>'View Profile Photo','name'=>'view_profile_photo');
        $profilePhotoOptions[] = array('label'=>'Remove Profile Photo','name'=>'remove_profile_photo');
      }
      $response['profile_image_options'] = $profilePhotoOptions;

      $coverPhotoOptions[] = array('label'=>'Upload Cover Photo','name'=>'upload_cover');
      $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");
      if($isAlbumEnable)
       $coverPhotoOptions[] = array('label'=>'Choose From Albums','name'=>'choose_from_albums');
       if($song->song_cover){
        $coverPhotoOptions[] = array('label'=>'View Cover Photo','name'=>'view_cover_photo');
        $coverPhotoOptions[] = array('label'=>'Remove Cover Photo','name'=>'remove_cover_photo');
       }
      $response['cover_image_options'] = $coverPhotoOptions;
    }

    //Rating work

    $authorizationApi = Engine_Api::_()->authorization();
    $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sesmusic');
    $this->view->mine = $mine = true;
    if (!$viewer->isSelf($song->getOwner()))
      $this->view->mine = $mine = false;

    $this->view->allowShowRating = $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
    $this->view->allowRating = $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $this->view->showRating = $showRating;

    if ($showRating) {
      $this->view->canRate = $canRate = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'rating_albumsong');
      $this->view->allowRateAgain = $allowRateAgain = $settings->getSetting('sesmusic.ratealbumsong.again', 1);
      $this->view->allowRateOwn = $allowRateOwn = $settings->getSetting('sesmusic.ratealbumsong.own', 1);

      if ($canRate == 0 || $allowRating == 0)
        $allowRating = false;
      else
        $allowRating = true;

      if ($allowRateOwn == 0 && $mine)
        $allowMine = false;
      else
        $allowMine = true;

      $this->view->allowMine = $allowMine;
      $this->view->allowRating = $allowRating;
      $this->view->rating_type = $rating_type = 'sesmusic_albumsong';
      $this->view->rating_count = $ratingTable->ratingCount($song->getIdentity(), $rating_type);
      $this->view->rated = $rated = $ratingTable->checkRated($song->getIdentity(), $viewer->getIdentity(), $rating_type);
      if (!$allowRateAgain && $rated)
        $rated = false;
      else
        $rated = true;
      $this->view->ratedAgain = $rated;
    }

    $rate = $song->rating;
      if($viewer->getIdentity() == 0){
        $rated = 0;
        $message = $this->view->translate("Please login to rate.");
      } elseif($this->view->allowShowRating == 1 && $this->view->allowRating == 0) {
         $rated = 3;
         $message = $this->view->translate('You are not allowed to rate.');;
      } elseif($this->view->allowRateAgain == 0 && $this->view->rated) {
        $rated = 1;
        $message = $this->view->translate('You already rated.');;
      } elseif($this->view->canRate == 0 && $viewer->viewer_id != 0) {
        $rated = 4;
        $message = $this->view->translate('You are not allowed to rate.');;
      } elseif(!$this->view->allowMine) {
        $rated = 2;
        $message = $this->view->translate('Rating on own album is not allowed.');;
      } else {
        $rated = 90;
        $message = $this->view->translate("");
      }
    $condition['code'] = $rated;
    $condition['message'] = $message;
    $response['rating'] = $condition;
    $response['rating']['total_rating_average'] = $song->rating;

     /* Insert data for recently viewed widget */
    if ($viewer->getIdentity() != 0 && isset($song->albumsong_id)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesmusic_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $song->albumsong_id . '", "sesmusic_albumsong","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }

    $photo = $this->getBaseUrl(false,$song->getPhotoUrl());
    if($photo)
      $response["share"]["imageUrl"] = $photo;
			$response["share"]["url"] = $this->getBaseUrl(false,$song->getHref());
      $response["share"]["title"] = $song->getTitle();
      $response["share"]["description"] = strip_tags($song->getDescription());
      $response["share"]['urlParams'] = array(
          "type" => $song->getType(),
          "id" => $song->getIdentity()
      );
    if(is_null($response["share"]["title"]))
      unset($response["share"]["title"]);
    $result['songs'] = $response;
    $result['permission'] = $this->_permission;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No songs created yet.', 'result' => $result));
  }
  //Delete Action
  public function deleteAction() {
    $albumsong = Engine_Api::_()->getItem('sesmusic_albumsong', $this->getRequest()->getParam('song_id'));
    $album = Engine_Api::_()->getItem('sesmusic_album', $albumsong->album_id);
    //Check subject
    if (!$albumsong) {
      $this->view->success = false;
      $this->view->error = $this->view->translate('Not a valid song');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }



    if (!$this->_helper->requireAuth()->setAuthParams('sesmusic_album', null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));




    if (!$albumsong) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Song doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    $db = $albumsong->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      //Delete ratings
      Engine_Api::_()->getDbtable('ratings', 'sesmusic')->delete(array('resource_id =?' => $this->_getParam('albumsong_id'), 'resource_type =?' => 'sesmusic_albumsong'));

      //Delete accociate playlistsong
      Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->delete(array('albumsong_id =?' => $this->_getParam('albumsong_id')));

      $file = Engine_Api::_()->getItem('storage_file', $albumsong->file_id);
      if ($file)
        $file->remove();

      //Delete album song
      $albumsong->delete();
      $album->song_count--;
      $album->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected song has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
  }
   public function albumViewAction(){
      $album_id  =  $this->_getParam('album_id',0);
      $viewer = Engine_Api::_()->user()->getViewer();
      $albums = Engine_Api::_()->getItem('sesmusic_album',$album_id);
       $settings = Engine_Api::_()->getApi('settings', 'core');
      if(!$albums)
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
      if(!$this->_getParam('getAlbum')){
        if (!$this->_helper->requireAuth()->setAuthParams($albums, $viewer, 'view')->isValid()) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        }

        $album = $albums->toArray();
        $description = strip_tags($albums->getDescription());
        $description = preg_replace('/\s+/', ' ', $description);
        unset($album['description']);
        $album['user_title'] = Engine_Api::_()->getItem('user',$album['owner_id'])->getTitle();
        $album['user_image'] = $this->userImage($albums->owner_id,"thumb.profile");

        $album['description'] = $description;
        $album['resource_type'] = $albums->getType();
          if (!$viewer->isSelf($albums->getOwner())){
            $albums->view_count++;
            $albums->save();
          }
        if($viewer->getIdentity() != 0){
          $album['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($albums);
          $album['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($albums);
          $album['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($albums,'favourites','sesmusic','sesmusic_albums');
          $album['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($albums,'favourites','sesmusic','sesmusic_albums');
        }

        if($viewer->getIdentity()){
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
            $counterMenu++;
          }
          $menuoptions[$counterMenu]['name'] = "report";
          $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Album");
          $album['menus'] = $menuoptions;
        }

        $result['albums'] = $album;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($albums,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$albums->getPhotoUrl());
        $result['albums']['images'] = $images;
        if($albums->album_cover){
         $result["albums"]['cover'] = Engine_Api::_()->sesapi()->getPhotoUrls($albums->album_cover,'',"");
        }
        else{
          $photo = $settings->getSetting('sesmusic.albumcover.photo');
          if(!$photo)
            $photo = '/application/modules/Sesmusic/externals/images/banner/cover.jpg';
          $result["albums"]['cover']['main'] = $this->getBaseUrl(true,$photo);
        }
       if(isset($canEdit) && $canEdit){
          $profilePhotoOptions[] = array('label'=>'Upload Photo','name'=>'upload_photo');
          $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");;
          if($isAlbumEnable)
           $profilePhotoOptions[] = array('label'=>'Choose From Albums','name'=>'choose_from_albums');
          if($albums->photo_id){
            $profilePhotoOptions[] = array('label'=>'View Profile Photo','name'=>'view_profile_photo');
            $profilePhotoOptions[] = array('label'=>'Remove Profile Photo','name'=>'remove_profile_photo');
          }
          $result["albums"]['profile_image_options'] = $profilePhotoOptions;

          $coverPhotoOptions[] = array('label'=>'Upload Cover Photo','name'=>'upload_cover');
          $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");
          if($isAlbumEnable)
           $coverPhotoOptions[] = array('label'=>'Choose From Albums','name'=>'choose_from_albums');
           if($albums->album_cover){
            $coverPhotoOptions[] = array('label'=>'View Cover Photo','name'=>'view_cover_photo');
            $coverPhotoOptions[] = array('label'=>'Remove Cover Photo','name'=>'remove_cover_photo');
           }
          $result["albums"]['cover_image_options'] = $coverPhotoOptions;

      }

         //Rating work
         $this->view->allowRateAgain = $this->view->allowShowRating = $this->view->allowRating = $this->view->rated = $this->view->canRate = $this->view->allowMine = 0;
         $authorizationApi = Engine_Api::_()->authorization();
        $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sesmusic');
        $this->view->mine = $mine = true;
        if (!$viewer->isSelf($albums->getOwner()))
          $this->view->mine = $mine = false;

        $this->view->allowShowRating = $allowShowRating = $settings->getSetting('sesmusic.ratealbum.show', 1);
        $this->view->allowRating = $allowRating = $settings->getSetting('sesmusic.album.rating', 1);

        if ($allowRating == 0) {
          if ($allowShowRating == 0)
            $showRating = false;
          else
            $showRating = true;
        }
        else
          $showRating = true;
        $this->view->showRating = $showRating;

        if ($showRating) {
          $this->view->canRate = $canRate = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'rating_album');
          $this->view->allowRateAgain = $allowRateAgain = $settings->getSetting('sesmusic.ratealbum.again', 1);
          $this->view->allowRateOwn = $allowRateOwn = $settings->getSetting('sesmusic.ratealbum.own', 1);

          if ($canRate == 0 || $allowRating == 0)
            $allowRating = false;
          else
            $allowRating = true;

          if ($allowRateOwn == 0 && $mine)
            $allowMine = false;
          else
            $allowMine = true;

          $this->view->allowMine = $allowMine;
          $this->view->allowRating = $allowRating;
          $this->view->rating_type = $rating_type = 'sesmusic_album';
          $this->view->rating_count = $ratingTable->ratingCount($albums->getIdentity(), $rating_type);
          $this->view->rated = $rated = $ratingTable->checkRated($albums->getIdentity(), $viewer->getIdentity(), $rating_type);

          if (!$allowRateAgain && $rated)
            $rated = false;
          else
            $rated = true;
          $this->view->ratedAgain = $rated;
        }

        $rate = $albums->rating;
        if($viewer->getIdentity() == 0){
          $rated = 0;
          $message = $this->view->translate("Please login to rate.");
        } elseif($this->view->allowShowRating == 1 && $this->view->allowRating == 0) {
           $rated = 3;
           $message = $this->view->translate('You are not allowed to rate.');;
        } elseif($this->view->allowRateAgain == 0 && $this->view->rated) {
          $rated = 1;
          $message = $this->view->translate('You already rated.');;
        } elseif($this->view->canRate == 0 && $viewer->getIdentity() != 0) {
          $rated = 4;
          $message = $this->view->translate('You are not allowed to rate.');;
        } elseif(!$this->view->allowMine) {
          $rated = 2;
          $message = $this->view->translate('Rating on own album is not allowed.');;
        } else {
          $rated = 90;
          $message = $this->view->translate("");
        }

        $condition['code'] = $rated;
        $condition['message'] = $message;
        $result['albums']['rating'] = $condition;
        $result['albums']['rating']['total_rating_average'] = $albums->rating;

         /* Insert data for recently viewed widget */
        if ($viewer->getIdentity() != 0 && isset($albums->album_id)) {
          $dbObject = Engine_Db_Table::getDefaultAdapter();
          $dbObject->query('INSERT INTO engine4_sesmusic_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $albums->album_id . '", "sesmusic_album","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
        }

        $photo = $this->getBaseUrl(false,$albums->getPhotoUrl());
        if($photo)
          $result['albums']["share"]["imageUrl"] = $photo;
					$result['albums']["share"]["url"] = $this->getBaseUrl(false,$albums->getHref());
          $result['albums']["share"]["title"] = $albums->getTitle();
          $result['albums']["share"]["description"] = strip_tags($albums->getDescription());
          $result['albums']["share"]['urlParams'] = array(
              "type" => $albums->getType(),
              "id" => $albums->getIdentity()
          );
        if(is_null($result['albums']["share"]["title"]))
          unset($result['albums']["share"]["title"]);
      }else{
        $result = array();
      }
      $values['album_id'] = $albums->getIdentity();
      $values['popularity'] = "creation_date";
      $values['paginator']  = true;
      $values['column'] = "*";
      $paginator = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->widgetResults($values);
      $paginator->setItemCountPerPage($this->_getParam('limit',20));
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      $result = array_merge($this->getAlbums($paginator),$result);
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

     //remove cover photo action
	public function removePhotoAction(){
		$item_id = $this->_getParam('song_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_albumsong', $item_id);
		if ($item_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));
		if(isset($item->photo_id) && $item->photo_id > 0){
			$im = Engine_Api::_()->getItem('storage_file', $item->photo_id);
			$item->photo_id = 0;
			$item->save();
			$im->delete();
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>$this->view->translate("Song photo removed successfully.")));
	}
	//remove cover photo action
	public function removeCoverAction(){
		$album_id = $this->_getParam('song_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_albumsong', $album_id);
		if ($album_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));
		if(isset($item->song_cover) && $item->song_cover > 0){
			$im = Engine_Api::_()->getItem('storage_file', $item->song_cover);
			$item->song_cover = 0;
			$item->save();
			$im->delete();
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>$this->view->translate("Song cover removed successfully.")));
	}

  //update cover photo function
	public function editCoverphotoAction(){
		$item_id = $this->_getParam('song_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_albumsong', $item_id);
		if ($item_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));

		$art_cover = $item->song_cover;
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
        $item->setPhoto($file,'songCover');

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
		$item_id = $this->_getParam('song_id', '0');
    $item = Engine_Api::_()->getItem('sesmusic_albumsong', $item_id);
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
        $item->setPhoto($file,'mainPhoto');

        if($art_cover != 0){
          $im = Engine_Api::_()->getItem('storage_file', $art_cover);
          $im->delete();
        }
        $db->commit();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your song photo updated successfully.")));
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
  public function editPlaylistAction(){
    $playlist_id = $this->_getParam('playlist_id',0);
    $playlist = Engine_Api::_()->getItem('sesmusic_playlist',$playlist_id);

    //Only members can upload music
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("permission_error"), 'result' => array()));

    if (!$playlist || !$playlist_id)
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("permission_error"), 'result' => array()));


    //Get playlist

    //Make form
    $this->view->form = $form = new Sesmusic_Form_EditPlaylist();
    if($form->getElement('playlist_mainphoto_preview'))
    $form->removeElement('playlist_mainphoto_preview');
    if($form->getElement('remove_photo'))
    $form->removeElement('remove_photo');
    if($form->getElement('playlist_id'))
    $form->removeElement('playlist_id');
    if($form->getElement('file'))
    $form->removeElement('file');

    $form->populate($playlist->toarray());


    //form
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


    $db = Engine_Api::_()->getDbTable('playlists', 'sesmusic')->getAdapter();
    $db->beginTransaction();
    try {
      $playlist->title = $values['title'];
      $playlist->description = $values['description'];
      $playlist->save();

      //Photo upload for playlist
      if (!empty($_FILES['image']['size'])) {
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

      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate("permission_error"), 'result' => $this->view->translate('Playlist edited successfully.')));

  }
   //Create New playlist Action
  public function appendSongsAction() {
    $album_id = $this->_getParam('album_id');
    //Check auth
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));


    if (!$this->_helper->requireAuth()->setAuthParams('sesmusic_album', null, 'playlist_album')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));


    $album_id = $this->_getParam('album_id');
    $album = Engine_Api::_()->getItem('sesmusic_album', $album_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //Get form
    $this->view->form = $form = new Sesmusic_Form_AppendSongs();

    $this->view->quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesmusic_album', 'addplaylist_max');

    //Populate form
    $playlistTable = Engine_Api::_()->getDbtable('playlists', 'sesmusic');

    $this->view->current_count = $playlists = $playlistTable->getPlaylistsCount(array('viewer_id' => $viewer->getIdentity(), 'column_name' => array('playlist_id', 'title')));
    foreach ($playlists as $playlist) {
      if ($playlist['playlist_id'] != $album_id) {
        $form->playlist_id->addMultiOption($playlist['playlist_id'], html_entity_decode($playlist['title']));
      }
    }

    //form

    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }

     // Check if valid
    if( !$form->isValid($_POST) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //if(count($validateFields))
        //$this->validateFormFields($validateFields);
    }

    //Get values
    $values = $form->getValues();

    if (empty($values['playlist_id']) && empty($values['title']))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Please enter a title or select a playlist.'), 'result' => array()));

    //Process
    $playlistSongsTable = Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic');
    $db = $playlistSongsTable->getAdapter();
    $db->beginTransaction();
    try {
      //Existing playlist
      if (!empty($values['playlist_id'])) {
        $playlist = Engine_Api::_()->getItem('sesmusic_playlist', $values['playlist_id']);
        $songs = $album->getSongs();
        $count = 0;
        foreach ($songs as $song) {

          //Already exists in playlist
          $alreadyExists = Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->checkSongsAlready(array('column_name' => 'albumsong_id', 'playlist_id' => $playlist->getIdentity(), 'file_id' => $song->file_id, 'albumsong_id' => $song->albumsong_id));
          if ($alreadyExists)
            $count++;
        }

        if (count($songs) == $count) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("This playlist already has this song. So, you can go to album view page and add songs in playlist."), 'result' => array()));
        }
      }
      //New playlist
      else {
        $playlist = $playlistTable->createRow();
        $playlist->title = trim($values['title']);
        $playlist->description = $values['description'];
        $playlist->owner_type = 'user';
        $playlist->owner_id = $viewer->getIdentity();
        $playlist->save();
      }

      //Add all songs in the playlists
      $songs = $album->getSongs();
      foreach ($songs as $song) {
        //Add song
        $playlist->addSong($song->file_id, $song->albumsong_id);
        $playlist->song_count++;
        $playlist->save();
      }

      $playlistID = $playlist->getIdentity();

      //Activity Feed work
      $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activityTable->addActivity($viewer, $album, "sesmusic_addalbumplaylist", '', array('playlist' => array($playlist->getType(), $playlist->getIdentity()),
      ));

      if ($action) {
        Engine_Api::_()->getDbtable('stream', 'activity')->delete(array('action_id =?' => $action->action_id));
        $db->query("INSERT INTO `engine4_activity_stream` (`target_type`, `target_id`, `subject_type`, `subject_id`, `object_type`, `object_id`, `type`, `action_id`) VALUES
('everyone', 0, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addalbumplaylist', $action->action_id),
('members', $viewer_id, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addalbumplaylist', $action->action_id),
('owner', $viewer_id, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addalbumplaylist', $action->action_id),
('parent', $viewer_id, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addalbumplaylist', $action->action_id),
('registered', 0, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addalbumplaylist', $action->action_id);");
        $activityTable->attachActivity($action, $album);
      }

      //Photo upload for playlist
      if (!empty($values['image']['size'])) {
        $previousPhoto = $playlist->photo_id;
        if ($previousPhoto) {
          $playlistPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $playlistPhoto->delete();
        }
        $playlist->setPhoto($values['image'], 'mainPhoto');
      }

      $this->view->playlist = $playlist;
      $db->commit();
      //Response
      $this->view->success = true;
      $this->view->message = $this->view->translate('Song has been successfully added to your playlist.');
      $result['playlist']["message"] = $this->view->translate('Song has been successfully added to your playlist.');
      $result['playlist']['playlist_id'] = $playlist->getIdentity();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' =>$result));
    } catch (Sesmusic_Model_Exception $e) {
      $this->view->success = false;
      $this->view->error = $this->view->translate($e->getMessage());
      $form->addError($e->getMessage());
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' =>""));
    } catch (Exception $e) {
      $this->view->success = false;
      $db->rollback();
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' =>$this->view->message));
  }

  //Create New playlist Action
  public function appendAction() {
    $album_id = $this->_getParam('album_id',0);
    if($album_id)
      return $this->_forward('append-songs', null, null, array('format' => 'json'));

    //Check auth
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams('sesmusic_album', null, 'playlist_song')->isValid())
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));


    if (!$this->_helper->requireSubject('sesmusic_albumsong')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));


    //Set song
    $song = Engine_Api::_()->core()->getSubject('sesmusic_albumsong');
    $albumsong_id = $song->albumsong_id;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //Get form
    $this->view->form = $form = new Sesmusic_Form_Append();


    $alreadyExistsResults = Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->getPlaylistSongs(array('column_name' => 'playlist_id', 'albumsong_id' => $albumsong_id));

    $allPlaylistIds = array();
    foreach ($alreadyExistsResults as $alreadyExistsResult) {
      $allPlaylistIds[] = $alreadyExistsResult['playlist_id'];
    }

    //Populate form
    $playlistTable = Engine_Api::_()->getDbtable('playlists', 'sesmusic');
    $select = $playlistTable->select()
            ->from($playlistTable, array('playlist_id', 'title'))
            ->where('owner_type = ?', 'user');

    if ($allPlaylistIds) {
      $select->where($playlistTable->info('name') . '.playlist_id NOT IN(?)', $allPlaylistIds);
    }

    $select->where('owner_id = ?', $viewer->getIdentity());
    $playlists = $playlistTable->fetchAll($select);
    //$this->view->current_count = $playlists;
    foreach ($playlists as $playlist) {
      // if ($playlist['playlist_id'] != $albumsong_id) {
      $form->playlist_id->addMultiOption($playlist['playlist_id'], html_entity_decode($playlist['title']));
      // }
    }

    //form get

    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }

     // Check if valid
    if( !$form->isValid($_POST) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //if(count($validateFields))
        //$this->validateFormFields($validateFields);
    }

    //Get values
    $values = $form->getValues();
    if (empty($values['playlist_id']) && empty($values['title']))
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Please enter a title or select a playlist.'), 'result' => array()));

    //Process
    $playlistSongsTable = Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic');
    $db = $playlistSongsTable->getAdapter();
    $db->beginTransaction();
    try {
      //Existing playlist
      if (!empty($values['playlist_id'])) {

        $playlist = Engine_Api::_()->getItem('sesmusic_playlist', $values['playlist_id']);

        //Already exists in playlist
        $alreadyExists = Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->checkSongsAlready(array('column_name' => 'albumsong_id', 'playlist_id' => $playlist->getIdentity(), 'file_id' => $song->file_id, 'albumsong_id' => $albumsong_id));

        if ($alreadyExists){
           Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("This playlist already has this song."), 'result' => array()));
        }
      }
      //New playlist
      else {
        $playlist = $playlistTable->createRow();
        $playlist->title = trim($values['title']);
        $playlist->description = $values['description'];
        $playlist->owner_type = 'user';
        $playlist->owner_id = $viewer->getIdentity();
        $playlist->save();
      }
      $playlist->song_count++;
      $playlist->save();
      //Add song
      $playlist->addSong($song->file_id, $albumsong_id);
      $playlistID = $playlist->getIdentity();

      //Activity Feed work
      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $song, "sesmusic_addplaylist", '', array('playlist' => array($playlist->getType(), $playlist->getIdentity()),
      ));
      if ($action) {
        Engine_Api::_()->getDbtable('stream', 'activity')->delete(array('action_id =?' => $action->action_id));
        $db->query("INSERT INTO `engine4_activity_stream` (`target_type`, `target_id`, `subject_type`, `subject_id`, `object_type`, `object_id`, `type`, `action_id`) VALUES
('everyone', 0, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addplaylist', $action->action_id),
('members', $viewer_id, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addplaylist', $action->action_id),
('owner', $viewer_id, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addplaylist', $action->action_id),
('parent', $viewer_id, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addplaylist', $action->action_id),
('registered', 0, 'user', $viewer_id, 'sesmusic_playlist', $playlistID, 'sesmusic_addplaylist', $action->action_id);");
        $activityApi->attachActivity($action, $song);
      }

      //Photo upload for playlist
      if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) {
        $previousPhoto = $playlist->photo_id;
        if ($previousPhoto) {
          $playlistPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $playlistPhoto->delete();
        }
        $playlist->setPhoto($_FILES['image'], 'mainPhoto');
      }

      $this->view->playlist = $playlist;
      $db->commit();
      //Response
      $this->view->success = true;
      $result['playlist']["message"] = $this->view->translate('Song has been successfully added to your playlist.');
      $result['playlist']['playlist_id'] = $playlist->getIdentity();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' =>$result));
    } catch (Sesmusic_Model_Exception $e) {
      $this->view->success = false;
      $this->view->error = $this->view->translate($e->getMessage());
      $form->addError($e->getMessage());
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    } catch (Exception $e) {
      $this->view->success = false;
      $db->rollback();
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("missing_parameter"), 'result' => array()));
  }

    //Edit Action
  public function editAction() {


    $this->view->albumsong = $albumsong = Engine_Api::_()->core()->getSubject();

    //Only members can upload music
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));

    if (!$this->_helper->requireSubject('sesmusic_albumsong')->isValid())
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));


    //Make form
    $this->view->form = $form = new Sesmusic_Form_SongEdit();

    if ($albumsong->subcat_id)
      $form->subcat_id->setValue($albumsong->subcat_id);

    if ($albumsong->subsubcat_id)
      $form->subsubcat_id->setValue($albumsong->subsubcat_id);

    $form->populate($albumsong->toarray());
    $form->removeElement('song_cover');

    $form->removeElement('song_cover_preview');
    $form->removeElement('song_mainphoto_preview');
    $form->removeElement('cancel');

    if ($albumsong->artists) {
      $artists_array = json_decode($albumsong->artists);
      if (count($artists_array) > 0)
        $form->artists->setValue(json_decode($albumsong->artists));
    }


   if($this->_getParam('getForm')) {

      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory
      $newFormFieldsArray = array();
      if(count($formFields) && $albumsong->category_id){
            foreach($formFields as $fields){
              foreach($fields as $field){
                  $subcat = array();
                  if($fields['name'] == "subcat_id"){
                    $subcat = Engine_Api::_()->getItemTable('sesmusic_categories')->getModuleSubcategory(array('category_id'=>$albumsong->category_id,'column_name'=>'*','param'=>'song'));
                  }else if($fields['name'] == "subsubcat_id"){
                    if($albumsong->subcat_id)
                    $subcat = Engine_Api::_()->getItemTable('sesmusic_categories')->getModuleSubSubcategory(array('category_id'=>$albumsong->subcat_id,'column_name'=>'*','param'=>'song'));
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

    $db = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();

      if (isset($values['artists'])){
        $artistsArray = array();
        foreach(array_keys($values['artists']) as $value){
          $artistsArray[] = (string) $value;
        }
        $values['artists'] = json_encode($artistsArray);
      }
      else
        $values['artists'] = json_encode(array());

      $albumsong->setFromArray($values);

      //Update title in playlistsong table
      Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->update(array('title' => $values['title']), array('albumsong_id = ?' => $albumsong_id));
      $albumsong->save();

      //Photo upload for song
      if (!empty($_FILES['file']['size'])) {
        $previousPhoto = $albumsong->photo_id;
        if ($previousPhoto) {
          $songPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $songPhoto->delete();
        }
        $albumsong->setPhoto($_FILES['file'], 'mainPhoto');
      }


      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

    }

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' =>$this->view->translate('Song edited successfully.')));

  }

}
