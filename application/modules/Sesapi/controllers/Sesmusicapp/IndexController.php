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
class Sesmusicapp_IndexController extends Sesapi_Controller_Action_Standard {
	public function init(){
		if($album_id = $this->_getParam('album_id', null)){
			$album = Engine_Api::_()->getItem('sesmusic_album', $album_id);
      if( null !== $album ) {
        Engine_Api::_()->core()->setSubject($album);
      }
		}
	}
	public function getAllWidgets(){
    $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
    $coreContentTableName = $coreContentTable->info('name');
    $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
    $corePagesTableName = $corePagesTable->info('name');
    $select = $corePagesTable->select()
      ->setIntegrityCheck(false)
      ->from($corePagesTable, null)
      ->where($coreContentTableName . '.type =?', 'widget')
      ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id')
      ->where($corePagesTableName . '.name = ?', 'sesmusicapp_index_home');
    return $select;
  }
	public function getWidgetData($widgetName){
		$coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
		$coreContentTableName = $coreContentTable->info('name');
		$corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
		$corePagesTableName = $corePagesTable->info('name');
		$select = $corePagesTable->select()
			->setIntegrityCheck(false)
			->from($corePagesTable, null)
			->where($coreContentTableName . '.name =?', $widgetName)
			->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id')
			->where($corePagesTableName . '.name = ?', 'sesmusicapp_index_home');
			$result = $select->query()->fetchRow();
	return $result;
  }
  public function homeAction() {
		$paginator = Zend_Paginator::factory($this->getAllWidgets());
		$paginator->setItemCountPerPage($this->_getParam('limit', 5));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$this->_setParam('limit', 5);
		$this->_setParam('page', 1);
		$counter = 0;
		//$data['data'] = array();
		$result = array();
		$search = false;
		$widgetCount = 0;
		foreach($paginator  as $widgetdata){
			$widget = $widgetdata;
			/*if($widgetdata->name != 'sesmusic.search'){
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Search');
			}else*/
			if($widgetdata->name == 'sesmusicapp.category'){
				
				$data= $this->getCategories($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Categories');
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			else  if($widgetdata->name == 'sesmusicapp.popular-artists'){
				$data= $this->getPopularArtists($widgetdata->params);

				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Popular Artist');
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			else if($widgetdata->name == 'sesmusicapp.popular-recommanded-songs'){
				$data= $this->getPopularRecommandedSongs($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Popular Recommanded Songs');
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			elseif($widgetdata->name == 'sesmusicapp.popular-albums'){
				$data= $this->getPopularAlbums($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Popular Albums');
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			elseif($widgetdata->name == 'sesmusicapp.featured-sponsored-hot-carousel'){
				$data= $this->getFeaturedSponsoredHotCarousel($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Featured Sponsored Hot Carousel');
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			elseif($widgetdata->name == 'sesmusicapp.popular-recommanded-other-related-songs'){
				$datafunction = $this->getPopularRecommandedOtherRelatedSongs($widgetdata->params);
					
				if(count($datafunction)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $datafunction;
						//print_r(json_encode($result));die;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Popular Recommanded Other Related Songs');
			
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			elseif($widgetdata->name == 'sesmusicapp.popular-playlist'){
				$data= $this->getPopularPlaylist($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Popular Playlist');
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			elseif($widgetdata->name == 'sesmusicapp.recently-viewed-item'){
				$data= $this->getRecentlyViewedItem($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Recently Viewed Item');
				$widgetCount = $widgetCount+1 ;
				$counter++;
			}
			elseif($widgetdata->name == 'sesmusicapp.you-may-also-like-album-songs'){
				$data= $this->getYouMayAlsoLikeAlbumSongs($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('You may also like Album or Songs');
				$counter++;
				$widgetCount = $widgetCount+1 ;
			}
			elseif($widgetdata->name == 'sesmusicapp.album-song-playlist-artist-day-of-the'){
				$data= $this->getAlbumSongPlaylistArtistDayOfThe($widgetdata->params);
				if(count($data)){
					if($widgetCount < 3){
						$result[$counter]['data'] = $data;
					}
				}else{
					continue;
				}
				$result[$counter]['name'] = $widgetdata->name;
				$result[$counter]['order'] = $widgetdata->order;
				$params = json_decode($widgetdata->params);
				$result[$counter]['params'] = $widgetdata->params;
				$result[$counter]['label'] = $params->title?$this->view->translate($params->title): $this->view->translate('Album Song Playlist Artist of the day');
				$counter++;
				$widgetCount = $widgetCount+1 ;
			}else{
			continue;
			}
			
		}
		
		$resultdata['all_data'] = $result ? $result :array();
			
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $resultdata), $extraParams));
  }
	public function albumSongPlaylistArtistDayOfTheAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getAlbumSongPlaylistArtistDayOfThe($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function getAlbumSongPlaylistArtistDayOfThe($encodeParams){
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
		
		$contentType = $params->contentType?$params->contentType:'album';
    $viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    $favouriteTable = Engine_Api::_()->getDbTable('favourites', 'sesmusic');
    if ($contentType == 'album') {
      $item = Engine_Api::_()->getDbtable('albums', 'sesmusic')->getOfTheDayResults();
      //Get all settings
			
      $information = $params->information?$params->information:array('featured', 'sponsored', 'hot', 'likeCount', 'commentCount', 'viewCount', 'ratingCount', 'songsCount', 'title', 'postedby');

      $canAddPlaylist = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_album');
      $canAddFavourite = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_album');
      if ($item && $item->album_id)
        $isFavourite = $favouriteTable->isFavourite(array('resource_type' => "sesmusic_album", 'resource_id' => $item->album_id));
      $socialshare_enable_plusicon = $params->socialshare_enable_plusicon?$params->socialshare_enable_plusicon:1;
      $socialshare_icon_limit = $params->socialshare_icon_limit?$params->socialshare_icon_limit:2;
      $albumlink = unserialize($settings->getSetting('sesmusic.albumlink'));
      $allowShowRating = $allowShowRating = $settings->getSetting('sesmusic.ratealbum.show', 1);
      $allowRating = $allowRating = $settings->getSetting('sesmusic.album.rating', 1);
      if ($allowRating == 0) {
        if ($allowShowRating == 0)
          $showRating = false;
        else
          $showRating = true;
      }
      else
        $showRating = true;
      $showRating = $showRating;
      $value = $album = $item; //Engine_Api::_()->getItem('sesmusic_album', $item->album_id);
			
      if (empty($album))
        return array();
			$optionCounter = 0;
			foreach($albumlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}
				elseif($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
			$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylist;
			$result['settings']['canadd_fav'] = $canAddFavourite;
			$result['items'] = $value->toArray();
			$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
				$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
				$isFavourite = Engine_Api::_()->getDbTable('favourites', 'sesmusic')->isFavourite(array('resource_type' => "sesmusic_album", 'resource_id' => $value->album_id));
				
				if($canAddFavourite && !empty($viewer_id) && $information && in_array('favourite', $information))
					$result['items']["is_content_favourite"] = $isFavourite ? true : false;
				if($canLike && $information && in_array('addLikeButton', $information))
					$result['items']["is_content_like"] = $isLike ? true : false;
				
				//if($value->photo_id)
					$result['items']["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
				
					$result['items']["user_title"] = $value->getOwner()->getTitle();
				
				if($value->album_cover)
					$result['items']["cover"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->album_cover, '', ""));
				
				if ($showRating && !empty($information) && in_array('ratingCount', $information))
					$result['items']["show_rating"] = true;
				
				if($canAddPlaylist && !empty($information) && in_array('addplaylist', $information))
					$result['items']["canadd_playlist"] = true;
				
				if($albumlink && in_array('share', $albumlink) && !empty($information) && in_array('share', $information)){
					$result['items']["can_share"] = true;
					$result['items']["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());;
					$result['items']["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
					$result['items']["share"]["title"] = $value->getTitle();
					$result['items']["share"]["description"] = strip_tags($value->getDescription());
					$result['items']["share"]["setting"] = $shareType;
					$result['items']["share"]['urlParams'] = array("type" => $value->getType(),
					"id" => $value->getIdentity());
				}
    } 
		elseif ($contentType == 'albumsong') {
      $item = Engine_Api::_()->getDbtable('albumsongs', 'sesmusic')->getOfTheDayResults();
      //Get all settings
      $information = $params->information?$params->information:array('featured', 'sponsored', 'hot', 'likeCount', 'commentCount', 'viewCount', 'ratingCount', 'title', 'postedby');
      //Songs settings.
      $songlink = unserialize($settings->getSetting('sesmusic.songlink'));
      $socialshare_enable_plusicon = $params->socialshare_enable_plusicon?$params->socialshare_enable_plusicon:1;
      $socialshare_icon_limit = $params->socialshare_icon_limit?$params->socialshare_icon_limit:2;
      //Favourite work
      if ($item)
        $isFavourite = $favouriteTable->isFavourite(array('resource_type' => "sesmusic_albumsong", 'resource_id' => $item->albumsong_id));
      $canAddPlaylistAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_song');
      $addfavouriteAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_song');
      $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
      $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
      if ($allowRating == 0) {
        if ($allowShowRating == 0)
          $showRating = false;
        else
          $showRating = true;
      }
      else
        $showRating = true;
      $showAlbumSongRating = $showRating;
      //Album and Song object according to song_id and alsbum_id: Written by SocialEngineSolutions
      $value = $song = $item; //Engine_Api::_()->getItem('sesmusic_albumsong', $item->albumsong_id);
      if ($song)
        $album = Engine_Api::_()->getItem('sesmusic_album', $song->album_id);
      if (empty($song))
        return array();
			foreach($songlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showAlbumSongRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylistAlbumSong;
			$result['settings']['canadd_fav'] = $addfavouriteAlbumSong;
			$counter =0;
			$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
			$result['items'][$counter] = $value->toArray();
			$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
			if($addfavouriteAlbumSong && !empty($viewer_id) && $information && in_array('favourite', $information) )
				$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
			$downloadAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'download_song');
			if($value->download &&  $downloadAlbumSong && $viewer->getIdentity())
				$result['items'][$counter]["can_download"] = true;
			else
				$result['items'][$counter]["can_download"] = false;
			if($canLike && $information && in_array('addLikeButton', $information))
				$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
			
			if ($showAlbumSongRating && !empty($information) && in_array('ratingCount', $information))
				$result['items'][$counter]["show_rating"] = true;
		
			if($canAddPlaylistAlbumSong && !empty($information) && in_array('addplaylist', $information))
				$result['items'][$counter]["canadd_playlist"] = true;
			
			$result['items'][$counter]["user_title"] = $value->getOwner()->getTitle();
			
			//if($value->song_url)
				$result['items'][$counter]['song_url'] = $this->getBaseUrl(false,$value->getFilePath());
			//if($value->photo_id)
				$result['items'][$counter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
			if($value->song_cover)
				$result['items'][$counter]["cover"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->song_cover, '', ""));
			
			if($songlink && in_array('share', $songlink) && !empty($information) && in_array('share', $information)){
				$result['items'][$counter]["can_share"] = true;
				$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
				$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
				$result['items'][$counter]["share"]["title"] = $value->getTitle();
				$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
				$result['items'][$counter]["share"]["setting"] = $shareType;
				$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),
					"id" => $value->getIdentity());
			}
    } 
		elseif ($contentType == 'artist') {
      $item = Engine_Api::_()->getDbtable('artists', 'sesmusic')->getOfTheDayResults();
      //Get all settings
      $information = $params->information?$params->information:array('ratingCount', 'favouriteCount', 'title');
      if ($item)
        $isFavourite = $favouriteTable->isFavourite(array('resource_type' => "sesmusic_artist", 'resource_id' => $item->artist_id));

      $allowShowRating = $settings->getSetting('sesmusic.rateartist.show', 1);
      $allowRating = $settings->getSetting('sesmusic.artist.rating', 1);
      if ($allowRating == 0) {
        if ($allowShowRating == 0)
          $showRating = false;
        else
          $showRating = true;
      }
      else
        $showRating = true;
      $showArtistRating = $showRating;
      $artistlink = unserialize($settings->getSetting('sesmusic.artistlink'));
      $artist = $value = $item; //Engine_Api::_()->getItem('sesmusic_artist', $item->artist_id);
      if (empty($artist))
        return array();
			foreach($artistlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showArtistRating;
			$result['settings']['canadd_fav'] = $isFavourite;
				$counter = 0;
				$result['items'][$counter] = $value->toArray();
				
				if($artistlink && in_array('favourite', $artistlink)){
					$result['items'][$counter]['is_content_favourite'] = $isFavourite ? true :  false;
				}
				$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_artist', $viewer, 'comment');
				$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
				if($canLike && $information && in_array('addLikeButton', $information))
				$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
				if ($showArtistRating && !empty($information) && in_array('ratingCount', $information)){
					$result['items'][$counter]['show_rating'] = $showArtistRating ? true : false;
				}
				if($value->artist_photo)
        $result['items'][$counter]['images'] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->artist_photo, '', ""));
				if($artistlink && in_array('share', $artistlink) && !empty($information) && in_array('share', $information)){
					$result['items'][$counter]["can_share"] = true;
					$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
					$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
					$result['items'][$counter]["share"]["title"] = $value->getTitle();
					$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
					$result['items'][$counter]["share"]["setting"] = $shareType;
					$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),
						"id" => $value->getIdentity());
			}
    } 
		elseif ($contentType == 'playlist') {
      $item = Engine_Api::_()->getDbtable('playlists', 'sesmusic')->getOfTheDayResults();
      $information = $params->information?$params->information:array('ratingCount', 'favouriteCount', 'title');
      $playlist = $item; //Engine_Api::_()->getItem('sesmusic_playlist', $item->playlist_id);
      if (empty($playlist))
        return array();
      //Songs settings.
      $songlink = unserialize($settings->getSetting('sesmusic.songlink'));
      $canAddPlaylistAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_song');
      $downloadAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'download_song');
      $canAddFavouriteAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_song');
      $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
      $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
      if ($allowRating == 0) {
        if ($allowShowRating == 0)
          $showRating = false;
        else
          $showRating = true;
      }
      else
        $showRating = true;
      $showAlbumSongRating = $showRating;
			$songCount = Engine_Api::_()->getDbtable('playlistsongs', 'sesmusic')->playlistSongsCount(array('playlist_id' => $item->playlist_id));
			$optionCounter = 0;
			foreach($songlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylistAlbumSong;
			$result['settings']['canadd_fav'] = $addfavouriteAlbumSong;
			$result['settings']['can_download'] = $downloadAlbumSong;
			$result['settings']['song_count'] = $songCount;
			$data = $value =  $item;
			$counter = 0;
			// playlist
			$result['items'][$counter] = $value->toArray();
			if(!empty($information) && in_array('postedby', $information))
				$result['items'][$counter]["owner"] = $value->getOwner()->getTitle();
			//if($value->photo_id)
					$result['items'][$counter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
			// playlist 's Song
			$songs = $item->getSongs();
			$songCounter = 0;
			foreach ($songs as $song){
				$song = Engine_Api::_()->getItem('sesmusic_albumsong', $song->albumsong_id);
				$result['items'][$counter]['song_list'][$songCounter] = $song->toArray();
				if($value->photo_id)
					$result['items'][$counter]['song_list'][$songCounter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
				$songCounter++;
			}
    }
		return $result;
	}
	public function youMayAlsoLikeAlbumSongsAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getYouMayAlsoLikeAlbumSongs($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function getYouMayAlsoLikeAlbumSongs($encodeParams){
		
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
	
		$viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $contentType = $contentType = $params->contentType?$params->contentType:'albums';
    $viewType = $params->viewType?$params->viewType:'gridview';
    $information = $params->information?$params->information:array('featuredLabel', 'sponsoredLabel', 'newLabel', 'likeCount', 'commentCount', 'viewCount', 'songsCount', 'title', 'postedby');
    $height = $params->height?$params->height:200;
    $width = $params->width?$params->width:100;
    $settings = Engine_Api::_()->getApi('settings', 'core');
    //Album Settings
    $albumlink = unserialize($settings->getSetting('sesmusic.albumlink'));

    $param = array();
    $param['showPhoto'] = $params->showPhoto ? $params->showPhoto:1;
    $param['limit'] = $params->itemCount?$params->itemCount:3;
    $param['popularity'] = 'You May Also Like';

		
    if ($contentType == 'albums') {
      $param['column'] = array('album_id', 'title', 'description', 'photo_id', 'owner_id', 'view_count', 'like_count', 'comment_count', 'song_count', 'featured', 'hot', 'sponsored', 'rating', 'special');
      $data =  Engine_Api::_()->getDbtable('albums', 'sesmusic')->widgetResults($param);
    } else {
      $params['column'] = array('album_id', "albumsong_id", 'title', 'description', 'photo_id', 'view_count', 'like_count', 'comment_count', 'download_count', 'featured', 'hot', 'sponsored', 'rating', 'track_id', 'song_url');
      $data = Engine_Api::_()->getDbtable('albumsongs', 'sesmusic')->widgetResults($param);
    }
		echo '<pre>';print_r(count($data));die;
		$counter = 0;
		
		foreach ($data as $value){
			$result['items'][$counter] = $value->toArray();
			if($contentType == 'albums'){
				$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
				$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
				$isFavourite = Engine_Api::_()->getDbTable('favourites', 'sesmusic')->isFavourite(array('resource_type' => "sesmusic_album", 'resource_id' => $value->album_id));
				if($canAddFavourite && !empty($viewer_id) && $information && in_array('favourite', $information))
					$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
				if($canLike && $information && in_array('addLikeButton', $information))
					$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
				
				if($value->photo_id)
					$result['items'][$counter]["photo"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->photo_id, '', ""));
				
				if($value->album_cover)
					$result['items'][$counter]["cover"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->album_cover, '', ""));
				
				if ($showRating && !empty($information) && in_array('ratingCount', $information))
					$result['items'][$counter]["show_rating"] = true;
				
				if($canAddPlaylist && !empty($information) && in_array('addplaylist', $information))
					$result['items'][$counter]["canadd_playlist"] = true;
				
				if($albumlink && in_array('share', $albumlink) && !empty($information) && in_array('share', $information)){
					$result['items'][$counter]["can_share"] = true;
					$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
				$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
				$result['items'][$counter]["share"]["title"] = $value->getTitle();
				$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
				$result['items'][$counter]["share"]["setting"] = $shareType;
				$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),
					"id" => $value->getIdentity());
				}
			}
			if($contentType == 'songs'){
				
				$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
				$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
				if($addfavouriteAlbumSong && !empty($viewer_id) && $information && in_array('favourite', $information) )
					$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
				
				if($canLike && $information && in_array('addLikeButton', $information))
					$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
				
				if ($showAlbumSongRating && !empty($information) && in_array('ratingCount', $information))
					$result['items'][$counter]["show_rating"] = true;
			
				if($canAddPlaylistAlbumSong && !empty($information) && in_array('addplaylist', $information))
					$result['items'][$counter]["canadd_playlist"] = true;

				//if($value->song_url)
					$result['items'][$counter]['song_url'] = $this->getBaseUrl(false,$value->getFilePath());
				if($value->photo_id)
					$result['items'][$counter]["photo"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->photo_id, '', ""));
				if($value->song_cover)
					$result['items'][$counter]["cover"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->song_cover, '', ""));
				
				if($songlink && in_array('share', $songlink) && !empty($information) && in_array('share', $information)){
					$result['items'][$counter]["can_share"] = true;
					$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
					$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
					$result['items'][$counter]["share"]["title"] = $value->getTitle();
					$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
					$result['items'][$counter]["share"]["setting"] = $shareType;
					$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),
						"id" => $value->getIdentity());
				}
			}
			
			$counter++;
		}
		return $result;
	}
	public function recentlyViewedItemAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getRecentlyViewedItem($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function getRecentlyViewedItem($encodeParams){
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
	
		$settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$type = $params->criteria?$params->criteria:'by_me';
    $contentType = $content_type  = $params->category?$params->category:'sesmusic_album';
    $viewType = $params->viewType ? $params->viewType : 'listView';
    $userId = Engine_Api::_()->user()->getViewer()->getIdentity();
		
    if (($type == 'by_me' || $type == 'by_myfriend') && $userId == 0) {
      return array();
    }

    $limit = $params->limit_data?$params->limit_data:10;
    $type = $criteria = $params->criteria?$params->criteria:'by_me';
    $height = $defaultHeight = $params->height?$params->height:180;
    $socialshare_enable_plusicon = $socialshare_enable_plusicon = $params->socialshare_enable_plusicon ? $params->socialshare_enable_plusicon : 1;
    $socialshare_icon_limit = $params->socialshare_icon_limit ? $params->socialshare_icon_limit:2;
    $width = $defaultWidth = $params->width?$params->width:'180';
    $title_truncation = $params->title_truncation ? $params->title_truncation:'45';
    $information = $params->information ? $params->information:array('likeCount', 'commentCount', 'ratingCount', 'postedby', 'viewCount');
    $canAddPlaylist = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_album');
    $canAddFavourite = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_album');
    $albumlink = unserialize($settings->getSetting('sesmusic.albumlink'));
    $allowShowRating = $settings->getSetting('sesmusic.ratealbum.show', 1);
    $allowRating = $settings->getSetting('sesmusic.album.rating', 1);

    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $showRating = $showRating;

    //Songs Settings
		
    //Songs settings.
    $songlink = unserialize($settings->getSetting('sesmusic.songlink'));

    $information = $params->information ? $params->information:array('featuredLabel', 'sponsoredLabel', 'newLabel', 'likeCount', 'commentCount', "downloadCount", 'viewCount', 'title', 'postedby');


    $canAddPlaylistAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_song');

    $addfavouriteAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_song');

    $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
    $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $showAlbumSongRating = $showRating;
		$optionCounter = 0;
		if($contentType == 'sesmusic_albumsong'){
			foreach($songlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showAlbumSongRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylistAlbumSong;
			$result['settings']['canadd_fav'] = $addfavouriteAlbumSong;
		}
		$optionCounter = 0 ;
		if($contentType == 'sesmusic_album'){
			foreach($albumlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylist;
			$result['settings']['canadd_fav'] = $canAddFavourite;
		}
		
    if ($content_type == 'sesmusic_album') {
      $param = array('type' => 'sesmusic_album','paginator'=>true , 'limit' => $limit, 'criteria' => $criteria);
    } else if ($content_type == 'sesmusic_albumsong') {
      $param = array('type' => 'sesmusic_albumsong','paginator'=>true , 'limit' => $limit, 'criteria' => $criteria);
    }
    else
     return array();
	 
    $data = $recentData = Engine_Api::_()->getDbtable('recentlyviewitems', 'sesmusic')->getitem($param);
    //if (count($recentData) == 0)
      //return array();
		$page = $this->_getParam('page', 1);
		$limit = $params->limit ? $params->limit:$this->_getParam('limit',5);
	
		if(count($data)){
		     $result['settings']['has_more'] = $data->getTotalItemCount() > $limit ? true : false;
		    $data->setItemCountPerPage($limit);
		     $data->setCurrentPageNumber($page);
		    $typeWidget = $type;
		$counter = 0;
			foreach($recentData as $item){
				$value = Engine_Api::_()->getItem($item->resource_type,$item->resource_id);
				if(!$value)
					continue;
				$result['items'][$counter] = $value->toArray();
				if($item->resource_type == 'sesmusic_album'){
					$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
					$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
					$isFavourite = Engine_Api::_()->getDbTable('favourites', 'sesmusic')->isFavourite(array('resource_type' => "sesmusic_album", 'resource_id' => $value->album_id));
					if($canAddFavourite && !empty($viewer_id) && $information && in_array('favourite', $information))
						$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
					if($canLike && $information && in_array('addLikeButton', $information))
						$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
					
					if($value->photo_id)
						$result['items'][$counter]["photo"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->photo_id, '', ""));
					
					if($value->album_cover)
						$result['items'][$counter]["cover"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->album_cover, '', ""));
					
					if ($showRating && !empty($information) && in_array('ratingCount', $information))
						$result['items'][$counter]["show_rating"] = true;
					
					if($canAddPlaylist && !empty($information) && in_array('addplaylist', $information))
						$result['items'][$counter]["canadd_playlist"] = true;
					
					if($albumlink && in_array('share', $albumlink) && !empty($information) && in_array('share', $information)){
						$result['items'][$counter]["can_share"] = true;
						$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
						$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
						$result['items'][$counter]["share"]["title"] = $value->getTitle();
						$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
						$result['items'][$counter]["share"]["setting"] = $shareType;
						$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),"id" => $value->getIdentity());
					}
				}
				if($item->resource_type == 'sesmusic_albumsong'){
					$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
					$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
					if($addfavouriteAlbumSong && !empty($viewer_id) && $information && in_array('favourite', $information) )
						$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
					$downloadAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'download_song');
					if($value->download &&  $downloadAlbumSong && $viewer->getIdentity())
						$result['items'][$counter]["can_download"] = true;
					else
						$result['items'][$counter]["can_download"] = false;
				
					if($canLike && $information && in_array('addLikeButton', $information))
						$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
				
					if ($showAlbumSongRating && !empty($information) && in_array('ratingCount', $information))
						$result['items'][$counter]["show_rating"] = true;
					
			
					if($canAddPlaylistAlbumSong && !empty($information) && in_array('addplaylist', $information))
						$result['items'][$counter]["canadd_playlist"] = true;

					//if($value->song_url)
						$result['items'][$counter]['song_url'] = $this->getBaseUrl(false,$value->getFilePath());
					//if($value->photo_id)
						$result['items'][$counter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
					//if($value->song_cover)
						//$result['items'][$counter]["cover"] = //$this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->song_cover, '', ""));
				
					if($songlink && in_array('share', $songlink) && !empty($information) && in_array('share', $information)){
						$result['items'][$counter]["can_share"] = true;
						$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
						$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
						$result['items'][$counter]["share"]["title"] = $value->getTitle();
						$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
						$result['items'][$counter]["share"]["setting"] = $shareType;
						$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),"id" => $value->getIdentity());
					}
				}
				$counter++;
				$result['pagging']['total_page'] = $data->getPages()->pageCount;
				$result['pagging']['total'] = $data->getTotalItemCount();
				$result['pagging']['current_page'] = $data->getCurrentPageNumber();
				$result['pagging']['next_page'] = $result['pagging']['current_page'] + 1;
			}
		}else{
			 $result['settings']['has_more'] = false;
			return array();
		}
		return $result;
	}
	public function popularRecommandedOtherRelatedSongsAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getPopularRecommandedOtherRelatedSongs($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function getPopularRecommandedOtherRelatedSongs($encodeParams){
		if(!$encodeParams)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
		$coreApi = Engine_Api::_()->core();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    $showType = $param->showType?$param->showType:'all';

    /*if ($showType == 'other') {
      $albumSong = $coreApi->getSubject('sesmusic_albumsong');
      if (!$albumSong)
        return;
    } elseif ($showType == 'related') {
      $albumsong = $coreApi->getSubject('sesmusic_albumsong');
      if (!$albumsong)
        return ;
      $album = Engine_Api::_()->getItem('sesmusic_album', $albumsong->album_id);
      if (!$album)
        return ;
      if (!$album->category_id)
        return $this->setNoRender();
    } elseif ($showType == 'artistOtherSongs') {
      $artist_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('artist_id');
      $artist = Engine_Api::_()->getItem('sesmusic_artists', $artist_id);
      if (!$artist)
        return;
    } elseif ($showType == 'otherSongView') {
      $albumsong = $coreApi->getSubject('sesmusic_albumsong');
      if (!$albumsong)
        return;
      $album = Engine_Api::_()->getItem('sesmusic_album', $albumsong->album_id);
      if (!$album)
        return;
    }*/

    $viewType = $params->viewType?$params->viewType:'gridview';
    $height = $params->height?$params->height: 200;
    $socialshare_enable_plusicon = $params->socialshare_enable_plusicon?$params->socialshare_enable_plusicon:1;
    $socialshare_icon_limit = $params->socialshare_icon_limit?$params->socialshare_icon_limit: 2;
    $width = $params->width?$params->width:100;
    $showLyrics = $params->showLyrics?$params->showLyrics:0;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //Songs settings.
    $songlink = unserialize($settings->getSetting('sesmusic.songlink'));
    $information = $params->information?$params->information:array('featuredLabel', 'sponsoredLabel', 'newLabel', 'likeCount', 'commentCount', "downloadCount", 'viewCount', 'title', 'postedby');
    $canAddPlaylistAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_song');
    $addfavouriteAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_song');
    $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
    $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $showAlbumSongRating = $showRating;
		$optionCounter = 0;
		
		foreach($songlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showAlbumSongRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylistAlbumSong;
			$result['settings']['canadd_fav'] = $addfavouriteAlbumSong;

    $param = array();
    /*if ($showType == 'recommanded') {
      $param['widgteName'] = 'Recommanded Album Songs';
    } elseif ($showType == 'other') {
      $param['widgteName'] = 'Other Album Songs';
      $param['albumsong_id'] = $albumSong->albumsong_id;
    } elseif ($showType == 'related') {
      $param['widgteName'] = 'Related Album Songs';
      $param['album_id'] = $album->album_id;
      $param['category_id'] = $album->category_id;
    } elseif ($showType == 'artistOtherSongs') {
      $param['widgteName'] = 'Artist Other Songs';
      $param['artist_id'] = $artist_id;
    } elseif ($showType == 'otherSongView') {
      $param['widgteName'] = 'Other Songs of Music Album';
      $param['album_id'] = $album->album_id;
    }*/

    $param['popularity'] = $params->popularity?$params->popularity:'creation_date';
		if (isset($param['popularity'])) {
      switch ($param['popularity']) {
        case "featured" :
          $param['displayContentType'] = 'featured';
				break;
				case "sponsored" :
          $param['displayContentType'] = 'sponsored';
				break;
				case "hot" :
          $param['displayContentType'] = 'hot';
				break;
				case "upcoming" :
          $param['displayContentType'] = 'upcoming';
				break;
				case "bothfesp" :
          $param['displayContentType'] = 'bothfesp';
				break;	
			}
    }
    $param['limit'] = $params->limit?$params->limit:3;
		$param['paginator'] = true;
    $param['column'] = array('download','albumsong_id', 'album_id', 'title', 'photo_id', 'lyrics', 'view_count', 'like_count', 'comment_count', "download_count", 'featured', 'hot', 'sponsored', 'rating', 'artists', 'file_id', 'track_id', 'song_url', 'upcoming', 'play_count', 'store_link');
    $data = Engine_Api::_()->getDbtable('albumsongs', 'sesmusic')->widgetResults($param);
		$limit = $params->limit ? $params->limit:$this->_getParam('limit',5);
		$page = $this->_getParam('page', 1);
		
		$result['settings']['has_more'] = $data->getTotalItemCount() > $limit ? true : 
		false;
		
		$data->setItemCountPerPage($limit);
    $data->setCurrentPageNumber($page);
		$counter = 0;
		if($data->getTotalItemCount()){
			foreach ($data as $value){
			$result['items'][$counter] = $value->toArray();
			$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
			$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
			if($addfavouriteAlbumSong && !empty($viewer_id) && $information && in_array('favourite', $information) )
				$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
			
			if($canLike && $information && in_array('addLikeButton', $information))
				$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
			
			if ($showAlbumSongRating && !empty($information) && in_array('ratingCount', $information))
				$result['items'][$counter]["show_rating"] = true;
		
			if($canAddPlaylistAlbumSong && !empty($information) && in_array('addplaylist', $information))
				$result['items'][$counter]["canadd_playlist"] = true;
			$downloadAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'download_song');
			if($value->download &&  $downloadAlbumSong && $viewer->getIdentity())
				$result['items'][$counter]["can_download"] = true;
			else
				$result['items'][$counter]["can_download"] = false;
			//if($value->song_url)
				$result['items'][$counter]['song_url'] = $this->getBaseUrl(false,$value->getFilePath());
			
			//if($value->photo_id)
				$result['items'][$counter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
			//if($value->song_cover)
				//$result['items'][$counter]["cover"] = //$this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->song_cover, '', ""));
			
			if($songlink && in_array('share', $songlink) && !empty($information) && in_array('share', $information)){
				$result['items'][$counter]["can_share"] = true;
				$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
				$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
				$result['items'][$counter]["share"]["title"] = $value->getTitle();
				$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
				$result['items'][$counter]["share"]["setting"] = $shareType;
				$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),"id" => $value->getIdentity());
			}
			$counter++;
		}
		$result['pagging']['total_page'] = $data->getPages()->pageCount;
		$result['pagging']['total'] = $data->getTotalItemCount();
		$result['pagging']['current_page'] = $data->getCurrentPageNumber();
		$result['pagging']['next_page'] = $result['pagging']['current_page'] + 1;
		}else{
			return array();
		}
		return $result;
	}
	public function featuredSponsoredHotCarouselAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getFeaturedSponsoredHotCarousel($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function popularPlaylistAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getPopularPlaylist($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function getFeaturedSponsoredHotCarousel($encodeParams){
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);

		$settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    $contentType = $contentType = $params->contentType?$params->contentType:'albums';
    
    $viewType = $params->viewType?$params->viewType:'horizontal';
    $height = $params->height?$params->height:'200';
    
    $socialshare_enable_plusicon = $params->socialshare_enable_plusicon?$params->socialshare_enable_plusicon: 1;
    $socialshare_icon_limit = $params->socialshare_icon_limit?$params->socialshare_icon_limit:2;
    
    $width = $params->width?$params->width:'180';
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $settings = Engine_Api::_()->getApi('settings', 'core');

    //Album Settings
    $albumlink = unserialize($settings->getSetting('sesmusic.albumlink'));
		$canAddPlaylist = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_album');
    $canAddFavourite = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_album');

    //Songs Settings
    $songlink = unserialize($settings->getSetting('sesmusic.songlink'));
    $canAddPlaylistAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'playlist_song');
    $addfavouriteAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_song');

    //Album Rating Work
    $allowShowRating = $settings->getSetting('sesmusic.ratealbum.show', 1);
    $allowRating = $settings->getSetting('sesmusic.album.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $showRating = $showRating;
    //Song Rating Work
    $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
    $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $showAlbumSongRating = $showRating;
		$optionCounter = 0;
		if($contentType == 'songs'){
			foreach($songlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showAlbumSongRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylistAlbumSong;
			$result['settings']['canadd_fav'] = $addfavouriteAlbumSong;
		}
		$optionCounter = 0 ;
		if($contentType == 'albums'){
			foreach($albumlink as $link){
				if($link == 'share'){
					$result['settings']['option'][$optionCounter]['name'] = 'share';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
				}else if($link == 'report'){
					$result['settings']['option'][$optionCounter]['name'] = 'report';
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
				}else{
					$result['settings']['option'][$optionCounter]['name'] = $link;
					$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
				}
				$optionCounter++;
			} 
			$result['settings']['show_rating'] = $showRating;
			$result['settings']['canadd_playlist'] = $canAddPlaylist;
			$result['settings']['canadd_fav'] = $canAddFavourite;
		}
    $param = array();
    $param['popularity'] = $params->popularity?$params->popularity:'creation_date';
    $param['displayContentType'] = $params->displayContentType?$params->displayContentType:'featured';
    $param['limit'] = $params->limit?$params->limit:3;
    $information = $params->information?$params->information:array('featured', 'sponsored', 'hot', 'likeCount', 'commentCount', 'viewCount', 'songsCount', 'title', 'postedby', 'ratingCount');

    if ($contentType == 'albums') {
      $param['column'] = array('*');
			$param['widgteName'] = "Tabbed Widget";
      $data = $results = Engine_Api::_()->getDbtable('albums', 'sesmusic')->widgetResults($param);
    } elseif ($contentType == 'songs') {
      $param['column'] = array('*');
			$param['paginator'] = true;
      $data = $results = Engine_Api::_()->getDbtable('albumsongs', 'sesmusic')->widgetResults($param);
    }
		$limit = $params->limit ? $params->limit:$this->_getParam('limit',5);
		$page = $this->_getParam('page', 1);
		$result['settings']['has_more'] = $data->getTotalItemCount() > $limit ? true : false;
		$data->setItemCountPerPage($limit);
    $data->setCurrentPageNumber($page);
		$counter = 0;
		if($data->getTotalItemCount()){
			foreach ($data as $value){
				$result['items'][$counter] = $value->toArray();
				if($contentType == 'albums'){
					$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
					$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
					$isFavourite = Engine_Api::_()->getDbTable('favourites', 'sesmusic')->isFavourite(array('resource_type' => "sesmusic_album", 'resource_id' => $value->album_id));
					if($canAddFavourite && !empty($viewer_id) && $information && in_array('favourite', $information))
						$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
					if($canLike && $information && in_array('addLikeButton', $information))
						$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
					
					//if($value->photo_id)
						$result['items'][$counter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
					
					if($value->album_cover)
						$result['items'][$counter]["cover"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->album_cover, '', ""));
					
					if ($showRating && !empty($information) && in_array('ratingCount', $information))
						$result['items'][$counter]["show_rating"] = true;
					
					if($canAddPlaylist && !empty($information) && in_array('addplaylist', $information))
						$result['items'][$counter]["canadd_playlist"] = true;
					
					if($albumlink && in_array('share', $albumlink) && !empty($information) && in_array('share', $information)){
						$result['items'][$counter]["can_share"] = true;
						$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
						$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
						$result['items'][$counter]["share"]["title"] = $value->getTitle();
						$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
						$result['items'][$counter]["share"]["setting"] = $shareType;
						$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),"id" => $value->getIdentity());
					}
				}
				if($contentType == 'songs'){
					
					$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
					$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
					$downloadAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'download_song');
					if($value->download &&  $downloadAlbumSong && $viewer->getIdentity())
						$result['items'][$counter]["can_download"] = true;
					else
						$result['items'][$counter]["can_download"] = false;
					if($addfavouriteAlbumSong && !empty($viewer_id) && $information && in_array('favourite', $information) )
						$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
					
					if($canLike && $information && in_array('addLikeButton', $information))
						$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
					
					if ($showAlbumSongRating && !empty($information) && in_array('ratingCount', $information))
						$result['items'][$counter]["show_rating"] = true;
				
					if($canAddPlaylistAlbumSong && !empty($information) && in_array('addplaylist', $information))
						$result['items'][$counter]["canadd_playlist"] = true;

					//if($value->song_url)
						$result['items'][$counter]['song_url'] = $this->getBaseUrl(false,$value->getFilePath());
					//if($value->photo_id)
						$result['items'][$counter]['images'] = $this->getBaseUrl(false, $value->getPhotoUrl());
					
					if($songlink && in_array('share', $songlink) && !empty($information) && in_array('share', $information)){
						$result['items'][$counter]["can_share"] = true;
						$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
						$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
						$result['items'][$counter]["share"]["title"] = $value->getTitle();
						$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
						$result['items'][$counter]["share"]["setting"] = $shareType;
						$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),
							"id" => $value->getIdentity());
					}
				}
				$counter++;
			}
			$result['pagging']['total_page'] = $data->getPages()->pageCount;
			$result['pagging']['total'] = $data->getTotalItemCount();
			$result['pagging']['current_page'] = $data->getCurrentPageNumber();
			$result['pagging']['next_page'] = $result['pagging']['current_page'] + 1;
		}else{
			return array();
		}
		return $result;
	}
	public function getPopularPlaylist($encodeParams){
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
	
		$coreApi = Engine_Api::_()->core();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    $thiswidth = $params->width ? $params->width:100;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $showType = $params->showType ? $params->showType: 'gridview';
    $viewType = $params->viewType ? $params->viewType:'horizontal';
    $height = $params->height ? $params->height:'200';

    $showOptionsType = $params->showOptionsType?$params->showOptionsType:'all';

    if ($showOptionsType == 'other') {
      $playlist = $coreApi->getSubject('sesmusic_playlist');
      if (!$playlist)
        return array();
    }

    $canAddFavourite = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'favourite_album');
    $albumlink = unserialize($settings->getSetting('sesmusic.albumlink'));
    $information = $params->information?$params->information:array('featured', 'viewCount', 'title', 'postedby');
    $param = array();
    if ($showOptionsType == 'recommanded') {
      $param['widgteName'] = 'Recommanded Playlist';
    } elseif ($showOptionsType == 'other') {
      $playlist_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('playlist_id');
      if ($playlist_id)
        $playlist = Engine_Api::_()->getItem('sesmusic_playlist', $playlist_id);
      $param['owner_id'] = $playlist->owner_id;
      $param['widgteName'] = 'Other Playlist';
      $param['playlist_id'] = $playlist->playlist_id;
    }
    $param['popularity'] = $params->popularity?$params->popularity:'creation_date';
		
    $param['limit'] = $params->limit?$params->limit:3;
		//echo '<pre>';print_r($param);die;
		
    $data = Engine_Api::_()->getDbtable('playlists', 'sesmusic')->getPlaylistPaginator($param);
		
		$counter = 0;
		$limit = $params->limit ? $params->limit:$this->_getParam('limit',5);
		$page = $this->_getParam('page', 1);
		$result['settings']['has_more'] = $data->getTotalItemCount() > $limit ? true : false;
		$data->setItemCountPerPage($limit);
    $data->setCurrentPageNumber($page);
		if($data->getTotalItemCount()){
			foreach ($data as $value){
				$result['items'][$counter] = $value->toArray();
				//if($value->photo_id)
					$result['items'][$counter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
					
				$result['pagging']['total_page'] = $data->getPages()->pageCount;
				$result['pagging']['total'] = $data->getTotalItemCount();
				$result['pagging']['current_page'] = $data->getCurrentPageNumber();
				$result['pagging']['next_page'] = $result['pagging']['current_page'] + 1;
				$counter++;
			}
		}else{
			return array();
		}
		return $result;
	}
	public function popularAlbumsAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getPopularAlbums($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function getPopularAlbums($encodeParams){
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
		 
		
		$showType = $showType = $params->showType?$params->showType:'all';
    $coreApi = Engine_Api::_()->core();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    if ($showType == 'other') {
      $this->getElement()->removeDecorator('Title');
      $album = $coreApi->getSubject('sesmusic_album');
      if (!$album)
        return;
    } elseif ($showType == 'related') {
      if (!$coreApi->hasSubject('sesmusic_album'))
        return;
      $album = $coreApi->getSubject('sesmusic_album');
      if (!$album)
        return;
      if (!$album->category_id)
        return ;
    }
    $viewType = $params->viewType?$params->viewType:'gridview';
    $height = $params->height?$params->height:200;
    $socialshare_enable_plusicon = $params->socialshare_enable_plusicon?$params->socialshare_enable_plusicon:1;
    $socialshare_icon_limit = $params->socialshare_icon_limit?$params->socialshare_icon_limit:2;
    $width = $params->width?$params->width:100;
    $viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$result = array();
    $canAddPlaylist = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'addplaylist_album');
    $canAddFavourite = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'addfavourite_album');
    $albumlink = unserialize($settings->getSetting('sesmusic.albumlink'));
    $allowShowRating = $settings->getSetting('sesmusic.ratealbum.show', 1);
    $allowRating = $settings->getSetting('sesmusic.album.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $showRating = $showRating;
		$optionCounter = 0;
		foreach($albumlink as $link){
			if($link == 'share'){
				$result['settings']['option'][$optionCounter]['name'] = 'share';
				$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
			}
			elseif($link == 'report'){
				$result['settings']['option'][$optionCounter]['name'] = 'report';
				$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
			}else{
				$result['settings']['option'][$optionCounter]['name'] = $link;
				$result['settings']['option'][$optionCounter]['label'] = $this->view->translate($link);
			}
			$optionCounter++;
		} 
		$result['settings']['show_rating'] = $showRating;
		$result['settings']['canadd_playlist'] = $canAddPlaylist;
		$result['settings']['canadd_fav'] = $canAddFavourite;

    $information = $params->information?$params->information:array('featuredLabel', 'sponsoredLabel', 'newLabel', 'likeCount', 'commentCount', 'viewCount', 'songsCount', 'title', 'postedby', 'ratingCount');

    $param = array();
    if ($showType == 'recommanded') {
      $param['widgteName'] = 'Recommanded Albums';
    } elseif ($showType == 'other') {
      $param['widgteName'] = 'Other Albums';
      $params['album_id'] = $album->album_id;
    } elseif ($showType == 'related') {
      $param['widgteName'] = 'Related Albums';
      $param['album_id'] = $album->album_id;
      $param['category_id'] = $album->category_id;
    }

    $param['popularity'] = $params->popularity?$params->popularity:'creation_date';
		if (isset($param['popularity'])) {
      switch ($param['popularity']) {
        case "featured" :
          $param['displayContentType'] = 'featured';
				break;
				case "sponsored" :
          $param['displayContentType'] = 'sponsored';
				break;
				case "hot" :
          $param['displayContentType'] = 'hot';
				break;
				case "upcoming" :
          $param['displayContentType'] = 'upcoming';
				break;
				case "bothfesp" :
          $param['displayContentType'] = 'bothfesp';
				break;	
			}
    }
    $param['showPhoto'] = $params->showPhoto?$params->showPhoto:0;
    //$param['limit'] = $params->limit?$params->limit:5;
    $param['column'] = array('*');
		$param['widgteName'] = "Tabbed Widget";
    $data = $albums = Engine_Api::_()->getDbtable('albums', 'sesmusic')->widgetResults($param);
		$counter = 0;
		$limit = $params->limit ? $params->limit:$this->_getParam('limit',5);
		$page = $this->_getParam('page', 1);
		$result['settings']['has_more'] = $data->getTotalItemCount() > $limit ? true : false;
		$data->setItemCountPerPage($limit);
    $data->setCurrentPageNumber($page);
		if($data->getTotalItemCount()){
			foreach($data as $value){
				$result['items'][$counter] = $value->toArray();
				$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_album', $viewer, 'comment');
				$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
				$isFavourite = Engine_Api::_()->getDbTable('favourites', 'sesmusic')->isFavourite(array('resource_type' => "sesmusic_album", 'resource_id' => $value->album_id));
				if($canAddFavourite && !empty($viewer_id) && $information && in_array('favourite', $information))
					$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
				
				if($canLike && $information && in_array('addLikeButton', $information))
					$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
				
				//if($value->photo_id)
					$result['items'][$counter]["images"] = $this->getBaseUrl(false, $value->getPhotoUrl());
				
				if($value->album_cover)
					$result['items'][$counter]["cover"] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->album_cover, '', ""));
				
				if ($showRating && !empty($information) && in_array('ratingCount', $information))
					$result['items'][$counter]["show_rating"] = true;
				
				if($canAddPlaylist && !empty($information) && in_array('addplaylist', $information))
					$result['items'][$counter]["canadd_playlist"] = true;
				
				if($albumlink && in_array('share', $albumlink) && !empty($information) && in_array('share', $information)){
					$result['items'][$counter]["can_share"] = true;
					$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
					$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
					$result['items'][$counter]["share"]["title"] = $value->getTitle();
					$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
					$result['items'][$counter]["share"]["setting"] = $shareType;
					$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),"id" => $value->getIdentity());
				}
			
				$counter++;
			}
			$result['pagging']['total_page'] = $data->getPages()->pageCount;
			$result['pagging']['total'] = $data->getTotalItemCount();
			$result['pagging']['current_page'] = $data->getCurrentPageNumber();
			$result['pagging']['next_page'] = $result['pagging']['current_page'] + 1;
		}else{
			return array();
		}
		return $result;
	}
  public function categoryAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getCategories($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function popularArtistsAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getPopularArtists($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function popularRecommandedSongsAction(){
		$encodeParams = $this->_getParam('params',null);
		$page = $this->_getParam('page',1);
		$limit = $this->_getParam('limit',5);
		$result = $this->getPopularRecommandedSongs($encodeParams);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	public function getPopularRecommandedSongs($encodeParams){
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
		
		$coreApi = Engine_Api::_()->core();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $authorizationApi = Engine_Api::_()->authorization();
    $showType = $params->showType?$params->showType:'all';

    if ($showType == 'other') {

      $albumSong = $coreApi->getSubject('sesmusic_albumsong');
      if (!$albumSong)
        return ;
    } elseif ($showType == 'related') {

      $albumsong = $coreApi->getSubject('sesmusic_albumsong');
      if (!$albumsong)
        return $this->setNoRender();

      $album = Engine_Api::_()->getItem('sesmusic_album', $albumsong->album_id);
      if (!$album)
        return ;

      if (!$album->category_id)
        return ;
    } elseif ($showType == 'artistOtherSongs') {
      $artist_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('artist_id');

      $artist = Engine_Api::_()->getItem('sesmusic_artists', $artist_id);
      if (!$artist)
        return ;
    } elseif ($showType == 'otherSongView') {
      $albumsong = $coreApi->getSubject('sesmusic_albumsong');
      if (!$albumsong)
        return ;

      $album = Engine_Api::_()->getItem('sesmusic_album', $albumsong->album_id);
      if (!$album)
        return ;
    }

    $viewType = $params->viewType?$params->viewType:'gridview';
    $height = $params->height?$params->height:200;
    
    $socialshare_enable_plusicon = $params->socialshare_enable_plusicon?$params->socialshare_enable_plusicon:1;
    $socialshare_icon_limit = $params->socialshare_icon_limit?$params->socialshare_icon_limit: 2;
    
    $width = $params->width?$params->width: 100;
    $showLyrics = $params->showLyrics?$params->showLyrics: 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //Songs settings.
    $songlink = unserialize($settings->getSetting('sesmusic.songlink'));
		$optionCounter = 0;
		$result = array();
		foreach($songlink as $link){
			if($link == 'share'){
				$result['settings']['option'][$optionCounter]['name'] = 'share';
				$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Share');
			}
			elseif($link == 'report'){
				$result['settings']['option'][$optionCounter]['name'] = 'report';
				$result['settings']['option'][$optionCounter]['label'] = $this->view->translate('Report');
			}else{
				$result['settings']['option'][$optionCounter]['name'] = $link;
				$result['settings']['option'][$optionCounter]['label'] = $link;
			}
			$optionCounter++;
		}
    $information = $params->information?$params->information :array('featuredLabel', 'sponsoredLabel', 'newLabel', 'likeCount', 'commentCount', "downloadCount", 'viewCount', 'title', 'postedby');


    $canAddPlaylistAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'addplaylist_albumsong');

    $addfavouriteAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'addfavourite_albumsong');
		$downloadAlbumSong = $authorizationApi->isAllowed('sesmusic_album', $viewer, 'download_song');
		

    $allowShowRating = $settings->getSetting('sesmusic.ratealbumsong.show', 1);
    $allowRating = $settings->getSetting('sesmusic.albumsong.rating', 1);
    if ($allowRating == 0) {
      if ($allowShowRating == 0)
        $showRating = false;
      else
        $showRating = true;
    }
    else
      $showRating = true;
    $showAlbumSongRating = $showRating;
		$result['settings']['show_rating'] = $showAlbumSongRating;
		$result['settings']['canadd_playlist'] = $canAddPlaylistAlbumSong;
		$result['settings']['canadd_fav'] = $addfavouriteAlbumSong;

    $param = array();
    if ($showType == 'recommanded') {
      $param['widgteName'] = 'Recommanded Album Songs';
    } elseif ($showType == 'other') {
      $param['widgteName'] = 'Other Album Songs';
      $param['albumsong_id'] = $albumSong->albumsong_id;
    } elseif ($showType == 'related') {
      $param['widgteName'] = 'Related Album Songs';
      $param['album_id'] = $album->album_id;
      $param['category_id'] = $album->category_id;
    } elseif ($showType == 'artistOtherSongs') {
      $param['widgteName'] = 'Artist Other Songs';
      $param['artist_id'] = $artist_id;
    } elseif ($showType == 'otherSongView') {
      $param['widgteName'] = 'Other Songs of Music Album';
      $param['album_id'] = $album->album_id;
    }
    $param['popularity'] = $params->popularity?$params->popularity:'creation_date';
    $param['limit'] = $this->_getParam('limit', 3);
    $param['paginator'] = true;

    $param['column'] = array('albumsong_id', 'album_id', 'title', 'photo_id', 'lyrics', 'view_count', 'like_count', 'comment_count', "download_count", 'featured', 'hot', 'sponsored','download', 'rating', 'artists', 'file_id', 'track_id', 'song_url', 'upcoming', 'play_count', 'store_link');
    $data = Engine_Api::_()->getDbtable('albumsongs', 'sesmusic')->widgetResults($param);
		$limit = $params->limit ? $params->limit:$this->_getParam('limit',5);
		$page = $this->_getParam('page', 1);
		$result['settings']['has_more'] = $data->getTotalItemCount() > $limit ? true : false;
		$data->setItemCountPerPage($limit);
    $data->setCurrentPageNumber($page);
		$counter = 0;
		if($data->getTotalItemCount()){
				foreach($data as $value){
				$result['items'][$counter] = $value->toArray();
				$canLike = Engine_Api::_()->authorization()->isAllowed('sesmusic_albumsong', $viewer, 'comment');
				$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($value, $viewer);
				if($addfavouriteAlbumSong && !empty($viewer_id) && $information && in_array('favourite', $information) )
					$result['items'][$counter]["is_content_favourite"] = $isFavourite ? true : false;
				if($value->download &&  $downloadAlbumSong && $viewer->getIdentity())
					$result['items'][$counter]["can_download"] = true;
				else
					$result['items'][$counter]["can_download"] = false;
				
				if($canLike && $information && in_array('addLikeButton', $information))
					$result['items'][$counter]["is_content_like"] = $isLike ? true : false;
				
				if ($showAlbumSongRating && !empty($information) && in_array('ratingCount', $information))
					$result['items'][$counter]["show_rating"] = true;
			
				if($canAddPlaylistAlbumSong && !empty($information) && in_array('addplaylist', $information))
					$result['items'][$counter]["canadd_playlist"] = true;

				//if($value->song_url)
					$result['items'][$counter]['song_url'] = $this->getBaseUrl(false,$value->getFilePath());
				//if($value->photo_id)
					$result['items'][$counter]['images'] = $this->getBaseUrl(false, $value->getPhotoUrl());
				if($songlink && in_array('share', $songlink) && !empty($information) && in_array('share', $information)){
					$result['items'][$counter]["can_share"] = true;
					$result['items'][$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $value->getPhotoUrl());
					$result['items'][$counter]["share"]["url"] = $this->getBaseUrl(false,$value->getHref());
					$result['items'][$counter]["share"]["title"] = $value->getTitle();
					$result['items'][$counter]["share"]["description"] = strip_tags($value->getDescription());
					$result['items'][$counter]["share"]["setting"] = $shareType;
					$result['items'][$counter]["share"]['urlParams'] = array("type" => $value->getType(),
						"id" => $value->getIdentity());
				}
				$counter++;
			}
			$result['pagging']['total_page'] = $data->getPages()->pageCount;
			$result['pagging']['total'] = $data->getTotalItemCount();
			$result['pagging']['current_page'] = $data->getCurrentPageNumber();
			$result['pagging']['next_page'] = $result['pagging']['current_page'] + 1;
		}else{
			return array();
		}
	return $result;
	}
	public function getPopularArtists($encodeParams){
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
		$viewType = $params->viewType?$params->viewType:'gridview';
    $height = $params->height?$params->height:200;
    $width = $params->width?$params->width:100;
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $param = array();
    $param['popularity'] = $params->popularity?$params->popularity:'favourite_count';
    $param['limit'] = $this->_getParam('limit', 5);
    $artits = Engine_Api::_()->getDbtable('artists', 'sesmusic')->getArtistsPaginator($param);
		$counter = 0;
		$limit = $params->limit ? $params->limit:$this->_getParam('limit',5);
		$page = $this->_getParam('page', 1) ;
		$result['settings']['has_more'] = $artits->getTotalItemCount() > $limit ? true : false;
		$artits->setItemCountPerPage($limit);
    $artits->setCurrentPageNumber($page);
		if($artits->getTotalItemCount()){
			foreach($artits as $value){
				$result['items'][$counter] = $value->toArray();
				if($value->artist_photo)
					$result['items'][$counter]['images'] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($value->artist_photo, '', ""));
				$counter++;
			}
			$result['pagging']['total_page'] = $artits->getPages()->pageCount;
			$result['pagging']['total'] = $artits->getTotalItemCount();
			$result['pagging']['current_page'] = $artits->getCurrentPageNumber();
			$result['pagging']['next_page'] = $result['pagging']['current_page'] + 1;
		}else{
			return array();
		}
		return $result;
	}
  public function getCategories($encodeParams) {
		if(!$encodeParams)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
		else
			$params = json_decode($encodeParams);
    $contentType = $params->contentType;
    $showType = $params->showType;
    $height = $params->height;
    $color = $params->color;
    $textHeight = $params->text_height;
    $image = $params->image;
    $storage = Engine_Api::_()->storage();
    $categoriesTable = Engine_Api::_()->getDbtable('categories', 'sesmusic');
		if ($showType == 'tagcloud' && $image == 0)
      $paginator = $categories = $categoriesTable->getCategory(array('column_name' => '*', 'image' => 1, 'param' => $contentType));
    else
      $paginator = $categories = $categoriesTable->getCategory(array('column_name' => '*', 'param' => $contentType));
		$counter = 0;
		$result = array();
		foreach($paginator as $value){
			$result['items'][$counter] = $value->toArray();
				if($value->cat_icon && $storage->get($value->cat_icon, ''))
				$result['items'][$counter]['cat_icon'] = $this->getBaseUrl(true,$storage->get($value->cat_icon, '')->getPhotoUrl());
			if($value->thumbnail && $storage->get($value->thumbnail, ''))
				$result['items'][$counter]['thumbnail'] = $this->getBaseUrl(true,$storage->get($value->thumbnail, '')->getPhotoUrl());
			if($value->colored_icon && $storage->get($value->colored_icon, ''))
				$result['items'][$counter]['colored_icon_photo'] = $this->getBaseUrl(true,$storage->get($value->colored_icon, '')->getPhotoUrl());
			$counter++;
		}
		return $result;
  }
  public function getArtists($values) {
    $params['popularity'] = $values['popularity'];
    $params['limit'] = $values['limit'];
    return Engine_Api::_()->getDbtable('artists', 'sesmusic')->getArtistsPaginator($params);
  }
	// demo
}
