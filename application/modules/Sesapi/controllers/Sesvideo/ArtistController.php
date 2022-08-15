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

class Sesvideo_ArtistController extends Sesapi_Controller_Action_Standard {
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
  //Browse Action
  public function browseAction() {
    $paginator = Engine_Api::_()->getDbTable('artists', 'sesvideo')->getArtistsPaginator();
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    
    $result["permission"] =  $this->_permission;
    $result['artists'] = $this->getArtists($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No artist created yet.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    
  }
  protected function getArtists($paginator){
    $result = array();
    $counter = 0;
    $canFavourite = Engine_Api::_()->authorization()->isAllowed('video', $viewer, 'rating_artist');
    foreach($paginator as $artist){ 
        $item = $artist->toArray();
        $item["description"] = preg_replace('/\s+/', ' ', $item["description"]);
        $item['user_title'] = $artist->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0 && $canFavourite){
          $item['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($artist,'favourites','sesvideo','sesvideo_artist');
          $item['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($artist,'favourites','sesvideo','sesvideo_artist');
        }
        if($artist->artist_photo)
        $item['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($artist->artist_photo,'',"");
        if(!count($images))
          $images['images']['main'] = $this->getBaseUrl(true,$artist->getPhotoUrl());
        $item["rating"] = round($item["rating"],1);
        $result[$counter] = array_merge($item,array());
        $counter++;
    }
      return $result;
  }
  //Artist View Action
  public function viewAction() {
    $this->_helper->content->setEnabled();
  }

}
