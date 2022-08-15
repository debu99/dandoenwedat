<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ChanelController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesvideo_ChanelController extends Sesapi_Controller_Action_Standard {
  protected $_permission = array();
  public function init() {
		if(!$this->_helper->requireAuth()->setAuthParams('sesvideo_chanel', null, 'view')->isValid())
			  Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $setting = Engine_Api::_()->getApi('settings', 'core');
    if (!$setting->getSetting('video_enable_chanel', 1)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $this->_permission = array('canCreateVideo'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'create'),'watchLater'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.watchlater', 1),'canCreatePlaylist'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'addplaylist_video'),'canCreateChannel'=>Engine_Api::_()->authorization()->isAllowed('sesvideo_chanel', null, 'create'),'canChannelEnable'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video_enable_chanel', 1));
  }

  public function indexAction() {
      $chanel_id = $this->_getParam('chanel_id', false);
      if ($chanel_id) {
        $chanel_id = Engine_Api::_()->getDbtable('chanels', 'sesvideo')->getChanelId($chanel_id);
      } else {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));
      }
      $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $chanel_id);
      if ($chanel) {
        Engine_Api::_()->core()->setSubject($chanel);
      }
      if (!$this->_helper->requireSubject()->isValid())
        return;
      $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!$subject->isOwner($viewer)) {
        $subject->view_count++;
        $subject->save();
      }

      if (!$this->_helper->requireAuth()->setAuthParams($chanel, $viewer, 'view')->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

      /* Insert data for recently viewed widget */
      if ($viewer->getIdentity() != 0 && isset($chanel->chanel_id)) {
        $dbObject = Engine_Db_Table::getDefaultAdapter();
        $dbObject->query('INSERT INTO engine4_sesvideo_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $chanel->chanel_id . '", "sesvideo_chanel","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
      }

      $response["channel"] = $chanel->toArray();
      if(empty( $response["channel"]['overview']))
         $response["channel"]['overview'] = "";
      $countVideo = $chanel->countVideos();
      $response["channel"]["description"] = preg_replace('/\s+/', ' ', $chanel["description"]);
      $response["channel"]['user_title'] = $chanel->getOwner()->getTitle();
      if($this->view->viewer()->getIdentity() != 0){
        $response["channel"]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($chanel);
        $response["channel"]['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($chanel);
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavc', 1)) {
            $response["channel"]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($chanel,'favourites','sesvideo','sesvideo_chanel');
            $response["channel"]['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($chanel,'favourites','sesvideo','sesvideo_chanel');
        }
      }


      $response["channel"]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($chanel,'',"");
      if(!count( $response["channel"]['images']))
         $response["channel"]['images']['main'] = $this->getBaseUrl(true,$chanel->getPhotoUrl());

      if($chanel->cover_id)
        $response["channel"]['cover'] = Engine_Api::_()->sesapi()->getPhotoUrls($chanel->cover_id,'',"");

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum'))
      $response["channel"]['photos'] = $chanel->count();
      if(isset($chanel->follow) && $chanel->follow == 1 && Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.subscription',1)){
         if($chanel->follow)
          $response["channel"]['isFollorActive'] = 1;
         $response["channel"]['follow_videos'] = $countVideo;
         $follow =  Engine_Api::_()->getDbtable('chanelfollows', 'sesvideo')->checkFollow(Engine_Api::_()->user()->getViewer()->getIdentity(),$chanel->chanel_id);
         $response["channel"]['isFollow'] = $follow;
      }

      if($viewer->getIdentity()){
        $menuoptions= array();
        $canEdit = $chanel->authorization()->isAllowed($viewer, 'edit');
        $counterMenu = 0;
        if($canEdit){
          $menuoptions[$counterMenu]['name'] = "edit";
          $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit Channel");
          $counterMenu++;

          $menuoptions[$counterMenu]['name'] = "videos";
          $menuoptions[$counterMenu]['label'] = $this->view->translate("Add Videos");
          $counterMenu++;
        }
        $canDelete = $chanel->authorization()->isAllowed($viewer, 'delete');
        if($canDelete){
          $menuoptions[$counterMenu]['name'] = "delete";
          $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete Channel");
          $counterMenu++;
        }
          $menuoptions[$counterMenu]['name'] = "report";
          $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Channel");
          $response["channel"]['menus'] = $menuoptions;
      }

      if(isset($canEdit) && $canEdit){
        $profilePhotoOptions[] = array('label'=>$this->view->translate('Upload Photo'),'name'=>'upload_photo');
        $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");;
        if($isAlbumEnable)
         $profilePhotoOptions[] = array('label'=>$this->view->translate('Choose From Albums'),'name'=>'choose_from_albums');
        if($chanel->thumbnail_id){
          $profilePhotoOptions[] = array('label'=>$this->view->translate('View Profile Photo'),'name'=>'view_profile_photo');
          $profilePhotoOptions[] = array('label'=>$this->view->translate('Remove Profile Photo'),'name'=>'remove_profile_photo');
        }
        $response["channel"]['profile_image_options'] = $profilePhotoOptions;

        $coverPhotoOptions[] = array('label'=>$this->view->translate('Upload Cover Photo'),'name'=>'upload_cover');
        $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");
        if($isAlbumEnable)
         $coverPhotoOptions[] = array('label'=>$this->view->translate('Choose From Albums'),'name'=>'choose_from_albums');
         if($chanel->cover_id){
          $coverPhotoOptions[] = array('label'=>$this->view->translate('View Cover Photo'),'name'=>'view_cover_photo');
          $coverPhotoOptions[] = array('label'=>$this->view->translate('Remove Cover Photo'),'name'=>'remove_cover_photo');
         }
        $response["channel"]['cover_image_options'] = $coverPhotoOptions;

      }


      $tabs[] = array(
          'label' => $this->view->translate('What\'s New'),
          'name' => 'updates',
      );
      if($countVideo > 0)
      $tabs[] = array(
          'label' => $countVideo == 1 ? $this->view->translate("Video") : $this->view->translate("Videos"),
          'name' => 'videos',
          'totalCount' => $countVideo
      );
      $editOverview = Engine_Api::_()->authorization()->isAllowed('sesvideo_chanel', $viewer, 'edit');
      if (!$editOverview && (!$chanel->overview || is_null($chanel->overview))) {}
      else
        $tabs[] = array(
          'label' => $this->view->translate("Overview") ,
          'name' => 'overview',
      );

      $tabs[] = array(
          'label' => $this->view->translate("Info"),
          'name' => 'info',
      );

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum')){
        $countPhotos = $chanel->count();
        $tabs[] = array(
            'label' => $countPhotos == 1 ? $this->view->translate("Photo") : $this->view->translate("Photos"),
            'name' => 'photos',
            'totalCount' => $countPhotos
        );
      }
      $tabs[] = array(
          'label' => $chanel->follow_count == 1 ? $this->view->translate("Follower") : $this->view->translate("Followers"),
          'name' => 'follower',
          'totalCount' => $chanel->follow_count
      );
      $response['channel']['tabs'] = $tabs;

      //share
      $photo = $this->getBaseUrl(false,$chanel->getPhotoUrl());
      if($photo)
      $response['channel']["share"]["imageUrl"] = $photo;
			$response['channel']["share"]["url"] = $this->getBaseUrl(false,$chanel->getHref());
      $response['channel']["share"]["title"] = $chanel->getTitle();
      $response['channel']["share"]["description"] = strip_tags($chanel->getDescription());
      $response['channel']["share"]['urlParams'] = array(
          "type" => $chanel->getType(),
          "id" => $chanel->getIdentity()
      );
      if(is_null($response['channel']["share"]["title"]))
        unset($response['channel']["share"]["title"]);


      //rating code
      $allowShowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratechanel.show', 1);
      $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.chanel.rating', 1);
      if ($allowRating == 0) {
        if ($allowShowRating == 0)
          $showRating = false;
        else
          $showRating = true;
      } else
      $showRating = true;
      if ($showRating) {
        $canRate = Engine_Api::_()->authorization()->isAllowed('sesvideo_chanel', $viewer, 'rating_chanel');
        $allowRateAgain = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratechanel.again', 1);
        $allowRateOwn = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratechanel.own', 1);
        if ($canRate == 0 || $allowRating == 0)
          $allowRating = false;
        else
          $allowRating = true;
        if ($allowRateOwn == 0 && $video->owner_id == $viewer->getIdentity())
          $allowMine = false;
        else
          $allowMine = true;
        $rating_type = "sesvideo_chanel";
        $rating_count = Engine_Api::_()->getDbTable('ratings', 'sesvideo')->ratingCount($chanel->getIdentity(), $rating_type);
        $rated = Engine_Api::_()->getDbTable('ratings', 'sesvideo')->checkRated($chanel->getIdentity(), $viewer->getIdentity(), $rating_type);
        $rating_sum = Engine_Api::_()->getDbTable('ratings', 'sesvideo')->getSumRating($chanel->getIdentity(), $rating_type);
        if ($rating_count != 0) {
          $total_rating_average = $rating_sum / $rating_count;
        } else {
          $total_rating_average = 0;
        }

        if (!$allowRateAgain && $rated) {
          $allowMine = false;
        } else {
          $allowMine = true;
        }
        if($viewer->getIdentity() == 0){
          $rate = 0;
          $message = $this->view->translate('please login to rate');
        }else if($allowShowRating == 1 && $allowRating == 0){
          $rate = 3;
          $message = $this->view->translate('rating is disabled');
        }else if($allowRateAgain == 0 && $rated){
          $rate = 1;
          $message = $this->view->translate("you already rated");
        }else if($canRate == 0 && $viewer_id != 0){
          $rate = 4;
          $message = $this->view->translate('rating is not allowed for your member level');
        }else if(!$allowMine){
          $rate = 2;
          $message = $this->view->translate('rating on own video not allowed');;
        }else {
          $rate = 100;
          $message = "";
        }
          unset($response['channel']['rating']);
          $condition['code'] = $rate;
          $condition['message'] = $message;
          $response['channel']['rating'] = $condition;
          $response['channel']['rating']['total_rating_average'] = $total_rating_average;
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $response));
  }

  function infoAction(){
      $chanel_id = $this->_getParam('chanel_id','');
      $chanel = Engine_Api::_()->getItem('sesvideo_chanel',$chanel_id);
      if(!$chanel)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
      $counter = 0;
      $category = Engine_Api::_()->getItem('sesvideo_category',$chanel->category_id);
      if($category){
        $response['info'][$counter]['category']['name'] = $category->getTitle();
        $response['info'][$counter]['category']['label'] = $this->view->translate('Category');
        $response['info'][$counter]['category']['id'] = $category->getIdentity();
        $counter++;
        if($chanel->subcat_id){
            $subcat = Engine_Api::_()->getItem('sesvideo_category',$chanel->subcat_id);
            if($subcat){
              $response['info'][$counter]['subcategory']['name'] = $subcat->getTitle();
              $response['info'][$counter]['subcategory']['id'] = $subcat->getIdentity();
              $response['info'][$counter]['subcategory']['label'] = $this->view->translate('Sub Category');
              $counter++;
              if($chanel->subsubcat_id){
                  $subsubcat = Engine_Api::_()->getItem('sesvideo_category',$chanel->subsubcat_id);
                  if($subsubcat){
                    $response['info'][$counter]['subsubcategory']['name'] = $subsubcat->getTitle();
                    $response['info'][$counter]['subsubcategory']['id'] = $subsubcat->getIdentity();
                    $response['info'][$counter]['subsubcategory']['label'] = $this->view->translate('Sub Sub Category');
                    $counter++;
                  }
              }
            }
        }
      }

      $keywords = array();
      $counterTag = 0;
      foreach ($chanel->tags()->getTagMaps() as $tagmap) {
        $tag = $tagmap->getTag();
        $keywords[$counterTag]['name'] = $tag->getTitle();
        $keywords[$counterTag]['id'] = $tagmap->getIdentity();
        $counterTag++;
      }
      if(count($keywords)){
        $response['info'][$counter]['tags'] = $keywords;
        $counter++;
      }
      if($chanel->description)
        $response['info'][$counter]['description']  = $chanel->description;

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$response));
  }

  public function overviewAction() {
    $chanel_id = $this->_getParam('chanel_id', false);
    if (!$this->_helper->requireAuth()->setAuthParams('sesvideo_chanel', null, 'edit')->isValid()) {
      return;
    }
    if ($chanel_id) {
      $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $chanel_id);
      // In smoothbox
      $this->_helper->layout->setLayout('default-simple');
      $this->view->form = $form = new Sesvideo_Form_Chanel_Overview();
      $form->populate($chanel->toArray());
      if (!$chanel) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_("Channel doesn't exists or not authorized");
        return;
      }
      if (!$this->getRequest()->isPost()) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        return;
      }
      $db = $chanel->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $chanel->overview = $_POST['overview'];
        $chanel->save();
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Channel Overview has been updated successfully.');
        $db->commit();
        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => Array($this->view->message),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
                    'smoothboxClose' => false,
        ));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }
	//update cover photo function
	public function editCoverphotoAction(){
		$chanel_id = $this->_getParam('chanel_id', '0');
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $chanel_id);
		if ($chanel_id == 0 || !$chanel)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));

		$art_cover = $chanel->cover_id;
    $resource_type = $this->_getParam('resource_type','');
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photo = Engine_Api::_()->getItem('album_photo',$photo_id);
    }

    if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) || !empty($photo)) {
      $db = $chanel->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        if(!empty($photo))
          $file = $photo;
        else
          $file = $_FILES['image'];
        $chanel->setCoverPhoto($file);

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
		$chanel_id = $this->_getParam('chanel_id', '0');
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $chanel_id);
		if ($chanel_id == 0 || !$chanel)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));

		$art_cover = $chanel->thumbnail_id;
    $resource_type = $this->_getParam('resource_type','');
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photo = Engine_Api::_()->getItem('album_photo',$photo_id);
    }

    if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) || !empty($photo)) {
      $db = $chanel->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        if(!empty($photo))
          $file = $photo;
        else
          $file = $_FILES['image'];
        $chanel->thumbnail_id = $this->setPhoto($file, $chanel->chanel_id);
        $chanel->save();
        if($art_cover != 0){
          $im = Engine_Api::_()->getItem('storage_file', $art_cover);
          $im->delete();
        }
        $db->commit();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your channel photo updated successfully.")));
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
  //remove cover photo action
	public function removePhotoAction(){
		$chanel_id = $this->_getParam('chanel_id', '0');
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $chanel_id);
		if ($chanel_id == 0 || !$chanel)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));
		if(isset($chanel->thumbnail_id) && $chanel->thumbnail_id > 0){
			$im = Engine_Api::_()->getItem('storage_file', $chanel->thumbnail_id);
			$chanel->thumbnail_id = 0;
			$chanel->save();
			$im->delete();
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'','result'=>$this->view->translate("Channel photo removed successfully.")));
	}
	//remove cover photo action
	public function removeCoverAction(){
		$chanel_id = $this->_getParam('chanel_id', '0');
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $chanel_id);
		if ($chanel_id == 0 || !$chanel)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));
		if(isset($chanel->cover_id) && $chanel->cover_id > 0){
			$im = Engine_Api::_()->getItem('storage_file', $chanel->cover_id);
			$chanel->cover_id = 0;
			$chanel->save();
			$im->delete();
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'','result'=>$this->view->translate("Channel cover removed successfully.")));
	}
  public function searchFormAction(){
      $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','hot' => 'Hot','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));

   $search_for = $this-> _getParam('search_for', 'chanel');
    $setting = Engine_Api::_()->getApi('settings', 'core');

    $default_search_type = $this-> _getParam('default_search_type', 'recentlySPcreated');
    if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo_enable_location', 1)){
			$location = 'yes';
		}else
			$location = 'no';
	  $searchForm = new Sesvideo_Form_Browsesearch(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'price' => $this->_getParam('price', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));
    if($this->_getParam('search_type','chanel') !== null && $this->_getParam('browse_by', 'yes') == 'yes'){
        $arrayOptions = $filterOptions;
      if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavc', 1)) {
        unset($arrayOptions['mostSPfavourite']);
      }
      $filterOptions = array();
      foreach ($arrayOptions as $key=>$filterOption) {
        $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
        $filterOptions[$key] = ucwords($value);
      }
      $filterOptions = array(''=>'')+$filterOptions;
       $searchForm->sort->setMultiOptions($filterOptions);
       $searchForm->sort->setValue($default_search_type);
    }
    $searchForm->removeElement('lat');
    $searchForm->removeElement('lng');
    $searchForm->removeElement('loading-img-sesvideo');
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($searchForm,true);
    $this->generateFormFields($formFields);

  }
  public function browseAction() {

    $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','hot' => 'Hot','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));

   $search_for = $this-> _getParam('search_for', 'chanel');
    $setting = Engine_Api::_()->getApi('settings', 'core');

    $default_search_type = $this-> _getParam('default_search_type', 'recentlySPcreated');
    if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo_enable_location', 1)){
			$location = 'yes';
		}else
			$location = 'no';
	  $searchForm = new Sesvideo_Form_Browsesearch(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'price' => $this->_getParam('price', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));
    if($this->_getParam('search_type','chanel') !== null && $this->_getParam('browse_by', 'yes') == 'yes'){
      $arrayOptions = $filterOptions;
      if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavc', 1)) {
        unset($arrayOptions['mostSPfavourite']);
      }
      $filterOptions = array();
      foreach ($arrayOptions as $key=>$filterOption) {
        $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
        $filterOptions[$key] = ucwords($value);
      }
      $filterOptions = array(''=>'')+$filterOptions;
       $searchForm->sort->setMultiOptions($filterOptions);
       $searchForm->sort->setValue($default_search_type);
    }

    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];
      }
    }
    $searchForm->populate($_POST);
    $manage = $this->_getParam('type','');
    $value = $searchForm->getValues();
    if($manage == "manage"){
      if($this->view->viewer()->getIdentity()){
        $value['user_id'] = $this->view->viewer()->getIdentity();
        $value["manageChannel"] = "manageVideo";
      }
      else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
    }
    if (isset($value['sort']) && $value['sort'] != '') {
      $value['getParamSort'] = str_replace('SP', '_', $value['sort']);
    } else
      $value['getParamSort'] = 'creation_date';
    if (isset($value['getParamSort'])) {
      switch ($value['getParamSort']) {
        case 'most_viewed':
          $value['popularCol'] = 'view_count';
          break;
        case 'most_liked':
          $value['popularCol'] = 'like_count';
          break;
        case 'most_commented':
          $value['popularCol'] = 'comment_count';
          break;
        case 'most_favourite':
          $value['popularCol'] = 'favourite_count';
          break;
        case 'hot':
          $value['popularCol'] = 'is_hot';
					$value['fixedData'] = 'is_hot';
          break;
        case 'sponsored':
          $value['popularCol'] = 'is_sponsored';
					$value['fixedData'] = 'is_sponsored';
          break;
				case 'featured':
          $value['popularCol'] = 'is_featured';
					$value['fixedData'] = 'is_featured';
          break;
        case 'most_rated':
          $value['popularCol'] = 'rating';
          break;
        case 'recently_created':
        default:
          $value['popularCol'] = 'creation_date';
          break;
      }
    }
    if(!$manage){
		  $value['search'] = 1;
    }
    if(!empty($_POST['search']))
      $value['text'] = $_POST['search'];

    $paginator = Engine_Api::_()->getDbTable('chanels', 'sesvideo')->getChanels(($value), true, $value);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $result["permission"] =  $this->_permission;
    $result['channels'] = $this->getChannels($paginator,$manage);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No video uploaded yet.', 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

  }
  protected function getChannels($paginator,$manage = ""){
    $result = array();
    $counter = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    foreach($paginator as $channel){
        $item = $channel->toArray();
        $item["description"] = preg_replace('/\s+/', ' ', $item["description"]);
        $item['user_title'] = $channel->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0){
          $item['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($channel);
          $item['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($channel);
          if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavc', 1)) {
            $item['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($channel,'favourites','sesvideo','sesvideo_chanel');
            $item['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($channel,'favourites','sesvideo','sesvideo_chanel');
          }
        }
        $item['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($channel,'',"");
        if(!count($images))
          $images['images']['main'] = $this->getBaseUrl(true,$channel->getPhotoUrl());

        if($channel->cover_id)
          $item['cover'] = Engine_Api::_()->sesapi()->getPhotoUrls($channel->cover_id,'',"");

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum'))
        $item['photos'] = $channel->count();
        if(isset($channel->follow) && $channel->follow == 1 && Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.subscription',1)){
           if($channel->follow)
            $item['isFollorActive'] = 1;
           $item['follow_videos'] = $channel->follow_videos;

           $follow =  Engine_Api::_()->getDbtable('chanelfollows', 'sesvideo')->checkFollow(Engine_Api::_()->user()->getViewer()->getIdentity(),$channel->chanel_id);
           $item['isFollow'] = $follow;
        }

        if($manage){
            $menuoptions= array();
            $canEdit = $channel->authorization()->isAllowed($viewer, 'edit');
            $counterMenu = 0;
            if($canEdit){
              $menuoptions[$counterMenu]['name'] = "edit";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
              $counterMenu++;
            }
            $canDelete = $channel->authorization()->isAllowed($viewer, 'delete');
            if($canDelete){
              $menuoptions[$counterMenu]['name'] = "delete";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
            }
            $item['menus'] = $menuoptions;
        }

        $result[$counter] = array_merge($item,array());

        $counter++;
    }
      return $result;
  }
  //get search chanel
  public function getChanelAction() {
    $sesdata = array();
    $value['search'] = $this->_getParam('text', '');
    $chanels = Engine_Api::_()->getDbtable('chanels', 'sesvideo')->getChanels($value);
    foreach ($chanels as $chanel) {
      $chanel_icon = $this->view->itemPhoto($chanel, 'thumb.icon');
      $sesdata[] = array(
          'id' => $chanel->chanel_id,
          'chanel_id' => $chanel->chanel_id,
          'label' => $chanel->title,
          'photo' => $chanel_icon,
      );
    }
    return $this->_helper->json($sesdata);
  }
  public function createAction() {
    if (!$this->_helper->requireUser->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams('sesvideo_chanel', null, 'create')->isValid())
      return;
		$optionsEnableChanel = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.chaneloption', 0);

    // Render
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $user_id = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getApi('core', 'sesvideo')->getChanelPaginator($values);
    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'maxchanel');
    $this->view->current_count = $paginator->getTotalItemCount();

    if (($this->view->current_count >= $this->view->quota) && !empty($quota)){
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of channels allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
    }



    // Create form
    $this->view->form = $form = new Sesvideo_Form_ChanelApi();
    if ($this->_getParam('type', false))
      $form->getElement('type')->setValue($this->_getParam('type'));

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

    if(!empty($_POST['custom_url'])){
      $check = $this->checkurlAction($_POST['custom_url'],0);
      if(!$check){
          $message = $this->view->translate("custom_url_taken");
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
      }
    }


    // Process
    $values = $form->getValues();
    $values['owner_id'] = $viewer->getIdentity();
    $insert_action = false;
    $db = Engine_Api::_()->getDbtable('chanels', 'sesvideo')->getAdapter();
    $db->beginTransaction();
    $dbChanel = Engine_Api::_()->getDbtable('chanelvideos', 'sesvideo')->getAdapter();
    $dbChanel->beginTransaction();
    try {
      // Create video
      $table = Engine_Api::_()->getDbtable('chanels', 'sesvideo');
      $chanel = $table->createRow();
      if (is_null($values['subsubcat_id']))
        $values['subsubcat_id'] = 0;
      if (is_null($values['subcat_id']))
        $values['subcat_id'] = 0;
      $chanel->setFromArray($values);
      $chanel->save();
      // Now try to create thumbnail
      //if (isset($_FILES['chanel_cover']['name']) && $_FILES['chanel_cover']['name'] != '') {
       // $chanel->cover_id = $this->setPhoto($form->chanel_cover, $chanel->chanel_id, true);
     // }
      if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '' && $_FILES['image']['size'] > 0) {
        $chanel->thumbnail_id = $this->setPhoto($_FILES['image'], $chanel->chanel_id);
      }
      $chanel->save();
      if (empty($_POST['custom_url']) && $_POST['custom_url'] == '') {
        $chanel->custom_url = $chanel->chanel_id;
      } else {
        $chanel->custom_url = $_POST['custom_url'];
      }
      $chanel->save();
      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if (empty($values['auth_view'])) {
        $values['auth_view'] = key($form->auth_view->options);
        if (empty($values['auth_view'])) {
          $values['auth_view'] = 'everyone';
        }
      }
      if (empty($values['auth_comment'])) {
        $values['auth_comment'] = key($form->auth_comment->options);
        if (empty($values['auth_comment'])) {
          $values['auth_comment'] = 'owner_member';
        }
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      //set roles
      foreach ($roles as $i => $role) {
        $auth->setAllowed($chanel, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($chanel, $role, 'comment', ($i <= $commentMax));
      }
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $chanel->tags()->addTagMaps($viewer, $tags);

      //Create Activity Feed
      $owner = $chanel->getOwner();
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $chanel, 'sesvideo_chanel_create');
      if ($action != null) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $chanel);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>array()));
    }
    $db->beginTransaction();
    try {
      if ($insert_action) {
        $owner = $video->getOwner();
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $chanel, 'video_chanel_new');
        if ($action != null) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $chanel);
        }
      }
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($chanel) as $action) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>array()));
    }
    // Now try to create videos
    if (isset($values['video_ids']) && $values['video_ids'] != '' && isset($chanel->chanel_id)) {
      $explodeIds = explode(',', rtrim($values['video_ids'], ','));
      $queryString = '';
      $runQuery = false;
      foreach ($explodeIds as $valuesChanel) {
        if (intval($valuesChanel) == 0 || $valuesChanel == '')
          continue;
        $valueChanels['chanel_id'] = $chanel->chanel_id;
        $valueChanels['video_id'] = $valuesChanel;
        $valueChanels['owner_id'] = $user_id;
        $valueChanels['creation_date'] = 'NOW()';
        $valueChanels['modified_date'] = 'NOW()';
        $queryString .= '(' . implode(',', $valueChanels) . '),';
        $runQuery = true;
      }

      //Activity Feed work
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity(Engine_Api::_()->user()->getViewer(), $chanel, 'sesvideo_chanel_new', null, array('count' => count(explode(',', rtrim($values['video_ids'], ',')))));
//      if ($action instanceof Activity_Model_Action && $count < 8) {
//        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
//      }
      if ($runQuery) {
        $dbObject = Engine_Db_Table::getDefaultAdapter();
        $query = 'INSERT IGNORE INTO engine4_video_chanelvideos (`chanel_id`, `video_id` ,`owner_id`,`creation_date`,`modified_date`) VALUES ';
        $stmt = $dbObject->query($query . rtrim($queryString, ','));
      }
    }
    $result['chanel']['chanel_id'] = $chanel->getIdentity();
    $result['chanel']['message'] = $this->view->translate('Chanel created successfully.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$result));
  }
  public function videosAction(){
    $chanel_id = $this->_getParam('chanel_id',2);
    if(!$chanel_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));

    $response = array();
    $data = $this->_getParam('data', 'my_created');
    $is_chanel = $this->_getParam('is_chanel', false);
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($data || $is_chanel) {
      $value['criteria'] = $data;
        $value['info'] = 'my_created';
        if ($data == 'my_created') {
          $value['user_id'] = $viewer->getIdentity();
          $paginator = Engine_Api::_()->getDbtable('videos', 'sesvideo')->getVideo($value);
        } else if ($data == 'watch_later') {
					 $getVideoItem = 'getVideoWatch';
          $paginator = Engine_Api::_()->getDbtable('watchlaters', 'sesvideo')->getWatchlaterItems($value);
        } else if ($data == 'liked_videos') {
          $getVideoItem = 'getVideoItem';
          $paginator = Engine_Api::_()->sesvideo()->getLikesContents(array('resource_type' => 'video'));
        } else if ($data == 'rated_videos') {
          $getVideoItem = 'getVideoItem';
          $paginator = Engine_Api::_()->getDbTable('ratings', 'sesvideo')->getRatedItems(array('resource_type' => 'video'));
        }

        $page = $this->_getParam('page',1);
        $paginator->setItemCountPerPage($this->_getParam('limit'));
        $paginator->setCurrentPageNumber($page);

        $enableOptioninChanel = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.chaneloption',array('my_created','liked_videos','rated_videos','watch_later'));
        $arrayOptions = array();
        $counter = 0;
        foreach ($enableOptioninChanel as $key => $valueoptions) {
          $arrayOptions[$counter]['name'] = $valueoptions;
          $arrayOptions[$counter]['label'] = ucwords(str_replace('_', ' ', $valueoptions));
          $counter++;
        }
        $response['menus'] = $arrayOptions;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
        $response['video'] = $this->getVideos($paginator,$chanel_id,$getVideoItem);
        if($response['video'] <= 0)
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('No video for this search criteria'), 'result' => array()));
        else
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $response),$extraParams));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('parameter_missing'), 'result' => array()));
  }
   protected function getVideos($paginator,$chanel_id = "",$type = ""){
    $result = array();
    $counter = 0;
    $table = Engine_Api::_()->getDbTable('chanelvideos','sesvideo');
    foreach($paginator as $videos){
        if($type == "getVideoItem"){
          $videos = Engine_Api::_()->getItem('sesvideo_video',$videos->resource_id);
        }
        //check video is on chanel
        $select = $table->select()->from($table->info('name'))->where('video_id =?',$videos->getIdentity())->where('chanel_id =?',$chanel_id);
        $exists = $table->fetchRow($select);
        if($exists){
          $video['already_added'] = true;
        }else{
          $video['already_added'] = false;
        }
        $video['title'] = $videos->getTitle();
        $video['video_id'] = $videos->getIdentity();
        if( $videos->duration >= 3600 ) {
          $duration = gmdate("H:i:s", $videos->duration);
        } else {
          $duration = gmdate("i:s", $videos->duration);
        }
        $video['duration'] = $duration;
        $video['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($videos,'',"");
        if(!count($video['images']))
          $video['images']['main'] = $this->getBaseUrl(false,$videos->getPhotoUrl());
        $result[$counter] = array_merge($video,array());
        $counter++;
    }
      return $result;
  }
  public function deleteVideoAction(){
    $id = $this->_getParam('resource_id','0');
    $video_id = $this->_getParam('video_id','0');
    if(!$id || !$video_id){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));
    }
    $dbObject = Engine_Db_Table::getDefaultAdapter();
    $queryVideos = $dbObject->query('DELETE FROM engine4_video_chanelvideos WHERE  `chanel_id` = ' . $id .' && video_id ='.$video_id);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate('Video deleted successfully.')));
  }

  public function deleteAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $this->getRequest()->getParam('chanel_id'));
    if (!$chanel->authorization()->isAllowed($viewer, 'delete'))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $chanel_id = $this->getRequest()->getParam('chanel_id', false);
    if ($chanel_id) {
      // In smoothbox

      if (!$chanel) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_("Channel doesn't exists or not authorized to delete");
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      if (!$this->getRequest()->isPost()) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $query = $dbObject->query('DELETE FROM engine4_video_chanels WHERE  `chanel_id` = ' . $chanel_id);
      $queryVideos = $dbObject->query('DELETE FROM engine4_video_chanelvideos WHERE  `chanel_id` = ' . $chanel_id);
      $queryFollow = $dbObject->query('DELETE FROM engine4_video_chanelfollows WHERE  `chanel_id` = ' . $chanel_id);
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Channel has been deleted.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
    }
  }

  public function deletePhotoAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $chanelPhoto = Engine_Api::_()->getItem('sesvideo_chanelphoto', $this->getRequest()->getParam('photo_id'));
    if (!$this->_helper->requireAuth()->setAuthParams('sesvideo_chanelphoto', $viewer, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $photo_id = $this->getRequest()->getParam('photo_id');
    if ($photo_id) {
       if (!$chanelPhoto) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_("Channel Photo doesn't exists or not authorized to delete");
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      if (!$this->getRequest()->isPost()) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $query = $dbObject->query('DELETE FROM engine4_video_chanelphotos WHERE  `chanelphoto_id` = ' . $photo_id);
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Channel photo has been deleted successfully.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
    }
  }

  public function photosAction() {
    $channel_id = $this->_getParam('chanel_id',2);
    $viewer = Engine_Api::_()->user()->getViewer();
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $channel_id);
    if ($chanel)
      Engine_Api::_()->core()->setSubject($chanel);
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));

    /* check sesalbum plugin enable or not ,if no then return */
    if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum'))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));

    $paginator = Engine_Api::_()->getDbTable('chanelphotos', 'sesvideo')->chanelphotos($channel_id);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));

    $canEdit = $chanel->authorization()->isAllowed($viewer, 'edit') ? true : false;
     if($canEdit){
       $counter = 0;
        $menuoptions[$counter]['name'] = "addmorephotos";
        $menuoptions[$counter]['label'] = $this->view->translate("Add More Photos");
        $counter++;
        $albumData['menus'] = $menuoptions;
      }

    $albumData['photos'] = $this->getPhotos($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    //echo "<pre>";var_dump($albumData);die;
    if($albumData['photos'] <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No photo created in this channel yet.', 'result' => array()));
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $albumData),$extraParams));
    }
  }
   public function getPhotos($paginator){
    $result = array();
    $counter = 0;
    foreach($paginator as $photos){
        $photo = $photos->toArray();
        $photo['photo_id'] = $photo['chanelphoto_id'];
        $photo['album_id'] = $photo['chanel_id'];
        //unset($photo['chanelphoto_id']);
        //unset($photo['chanel_id']);
        $canFavourite =  Engine_Api::_()->authorization()->isAllowed('album',Engine_Api::_()->user()->getViewer(), 'favourite_photo');
        $photo['user_title'] = $photos->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0){
          $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
          $photo['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($photos);
          //$photo['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($photos,'favourites','sesalbum','album_photo');
          //$photo['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($photos,'favourites','sesalbum','album_photo');
        }

          $attachmentItem = $photos;
          if($attachmentItem->getPhotoUrl())
          $photo["shareData"]["imageUrl"] = $this->getBaseurl(false,$attachmentItem->getPhotoUrl());
          $photo["shareData"]["title"] = $attachmentItem->getTitle();
          $photo["shareData"]["description"] = strip_tags($attachmentItem->getDescription());

          $photo["shareData"]['urlParams'] = array(
              "type" => $photos->getType(),
              "id" => $photos->getIdentity()
          );
          if(is_null($photo["shareData"]["title"]))
            unset($photo["shareData"]["title"]);


        $owner = $photos->getOwner();
        $photo['owner']['title'] = $owner ->getTitle();
        $photo['owner']['id'] =  $owner->getIdentity();
        $photo["owner"]['href'] = $owner->getHref();
        $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos,'',"");

        $photo['can_comment'] = $photos->authorization()->isAllowed($this->view->viewer(), 'comment') ? true : false;
         if ($photo['can_comment']) {
            $viewer_id = $this->view->viewer()->getIdentity();
            if($viewer_id){
              $itemTable = Engine_Api::_()->getItemTable($photos->getType(),$photos->getIdentity());
              $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
              $tableMainLike = $tableLike->info('name');
              $select = $tableLike->select()
                    ->from($tableMainLike)
                    ->where('resource_type = ?', $photos->getType())
                    ->where('poster_id = ?', $viewer_id)
                    ->where('poster_type = ?', 'user')
                    ->where('resource_id = ?', $photos->getIdentity());
              $resultData = $tableLike->fetchRow($select);
             if ($resultData) {
                  $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id);
                  $photo['reaction_type'] = $item_activity_like->type;
              }
            }
            $photo['resource_type'] = $photos->getType();
            $table = Engine_Api::_()->getDbTable('likes','core');
            $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
            $coreliketableName = $coreliketable->info('name');
            $recTable = Engine_Api::_()->getDbTable('reactions','sesadvancedcomment')->info('name');
            $select = $table->select()->from($table->info('name'),array('total'=>new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?',$photos->getIdentity())->group('type')->setIntegrityCheck(false);
            $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
            $select->where('resource_type =?',$photos->getType());
            $select->joinLeft($recTable,$recTable.'.reaction_id ='.$coreliketableName.'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
            $resultData =  $table->fetchAll($select);

            $photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
            $reactionData = array();
            $reactionCounter = 0;
            if(count($resultData)){
              foreach($resultData as $type){
                $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['total'],Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                $reactionCounter++;
              }
              $photo['reactionData'] = $reactionData;
            }
            if($photo['is_like']){
               $photo[$counter]['is_like'] = true;
                $like = true;
                $type = $photo['reaction_type'];
                $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->sesadvancedcomment()->likeImage($type));
                $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
             }else{
               $photo[$counter]['is_like'] = false;
                $like = false;
                $type = '';
                $imageLike = '';
                $text = 'Like';
            }
            if(empty($like)) {
                $photo[$counter]["like"]["name"] = "like";
            }else {
                $photo[$counter]["like"]["name"] = "unlike";
            }

            $photo["like"]["type"] = $type;
            $photo["like"]["image"] = $imageLike;
            $photo["like"]["title"] = $this->view->translate($text);
            $photo['reactionUserData'] = $this->view->FluentListUsers($photos->likes()->getAllLikesUsers(),'',$photos->likes()->getLike($this->view->viewer()),$this->view->viewer());
        }
        if(!count($album_photo['images']))
          $album_photo['images']['main'] = $this->getBaseUrl(true,$photos->getPhotoUrl());
        $result[$counter] = array_merge($photo,$album_photo);
        $counter++;
    }
    //echo "<prE>";var_dump($result);die;
    return $result;
  }
  public function uploadPhotoAction() {
    if (!$this->_helper->requireAuth()->setAuthParams('sesvideo_chanel', null, 'create')->isValid())
      return;

    if (!$this->_helper->requireUser()->checkRequire()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    if (empty($_GET['isURL']) || $_GET['isURL'] == 'false') {
      $isURL = false;
      $values = $this->getRequest()->getPost();
      if (empty($values['Filename']) && !isset($_FILES['Filedata'])) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
        return;
      }
      if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
        return;
      }
      $uploadSource = $_FILES['Filedata'];
    } else {
      $uploadSource = $_POST['Filedata'];
      $isURL = true;
    }

    $db = Engine_Api::_()->getDbtable('chanelphotos', 'sesvideo')->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $photoTable = Engine_Api::_()->getDbtable('chanelphotos', 'sesvideo');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
          'owner_id' => $viewer->getIdentity()
      ));
      $photo->save();
      $photo->order = $photo->chanelphoto_id;
      $setPhoto = $photo->setPhoto($uploadSource, $isURL);
      if (!$setPhoto) {
        $db->rollBack();
        $this->view->status = false;
        $this->view->error = 'An error occurred.';
        return;
      }
      $photo->save();
      $this->view->status = true;
      $this->view->photo_id = $photo->chanelphoto_id;
      $this->view->url = $photo->getPhotoUrl('thumb.normal');
      $db->commit();
    } catch (Sesvideo_Model_Exception $e) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
      throw $e;
      return;
    }
  }

  //ACTION FOR PHOTO DELETE
  public function removeAction() {
    if (empty($_POST['photo_id']))
      die('error');
    //GET PHOTO ID AND ITEM
    $photo_id = (int) $this->_getParam('photo_id');
    $photo = Engine_Api::_()->getItem('sesvideo_chanelphoto', $photo_id);
    $db = Engine_Api::_()->getDbTable('chanelphotos', 'sesvideo')->getAdapter();
    $db->beginTransaction();
    try {
      $photo->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  //location chanel photo
  public function locationAction() {
    $this->view->type = $this->_getParam('type', 'sesvideo_chanelphoto');
    $this->view->photo_id = $photo_id = $this->_getParam('photo_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->getItem('sesvideo_chanelphoto', $photo_id);
    $this->view->photo = $photo;
    $this->view->form = $form = new Sesvideo_Form_Chanel_Location();
    $form->populate($photo->toArray());
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    $values = $form->getValues();
    //update location data in sesbasic location table
    if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
      $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
      $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $photo_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesvideo_chanelphoto")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
    }
    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $photo->setFromArray($values);
      $photo->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your location have been saved.')),
                'layout' => 'default-simple',
                'parentRefresh' => false,
                'smoothboxClose' => true,
    ));
  }

  public function editPhotoAction() {
    $this->view->photo_id = $photo_id = $this->_getParam('photo_id');
    $this->view->photo = Engine_Api::_()->getItem('sesvideo_chanelphoto', $photo_id);
  }

  //edit photo details from lightbox
  public function editDetailAction() {
    $status = true;
    $error = false;
    if (!$this->_helper->requireAuth()->setAuthParams('sesvideo_chanelphoto', null, 'edit')->isValid()) {
      $status = false;
      $error = true;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->getItem('sesvideo_chanelphoto', $_POST['photo_id']);
    if ($status && !$error) {
      $values['title'] = $_POST['title'];
      $values['description'] = $_POST['description'];
      $values['location'] = $_POST['location'];
      //update location data in sesbasic location table
      if ($_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $_POST['photo_id'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesvideo_chanelphoto")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      $db = $photo->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $photo->setFromArray($values);
        $photo->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        $status = false;
        $error = true;
      }
    }
    echo json_encode(array('status' => $status, 'error' => $error));
    die;
  }

  //edit photo details from light function.
  public function saveInformationAction() {
    $photo_id = $this->_getParam('photo_id');
    $title = $this->_getParam('title', null);
    $description = $this->_getParam('description', null);
    $location = $this->_getParam('location', null);
    if (($this->_getParam('lat')) && ($this->_getParam('lng')) && $this->_getParam('lat', '') != '' && $this->_getParam('lng', '') != '') {
      $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
      $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $photo_id . '", "' . $this->_getParam('lat') . '","' . $this->_getParam('lng') . '","sesvideo_chanelphoto")	ON DUPLICATE KEY UPDATE	lat = "' . $this->_getParam('lat') . '" , lng = "' . $this->_getParam('lng') . '"');
    }
    Engine_Api::_()->getDbTable('chanelphotos', 'sesvideo')->update(array('title' => $title, 'description' => $description, 'location' => $location), array('chanelphoto_id = ?' => $photo_id));
  }

  public function editAction() {
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel', $this->_getParam('chanel_id'));
    if ($chanel)
      Engine_Api::_()->core()->setSubject($chanel);
    if (!$this->_helper->requireSubject()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));

    if ($viewer->getIdentity() != $chanel->owner_id && !$chanel->authorization()->isAllowed($viewer, 'edit')) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $this->view->form = $form = new Sesvideo_Form_Chanel_EditApi();
    if ($chanel) {
      $form->populate($chanel->toArray());
    }


    $chanelTags = $chanel->tags()->getTagMaps();
    $tagString = '';
    foreach ($chanelTags as $tagmap) {
      if ($tagString !== '')
        $tagString .= ', ';
      $tagString .= $tagmap->getTag()->getTitle();
    }
    $this->view->tagNamePrepared = $tagString;
    $form->tags->setValue($tagString);

    if($this->_getParam('getForm')) {
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      foreach ($roles as $role) {
        if (1 === $auth->isAllowed($chanel, $role, 'view') && isset($form->auth_view)) {
          $form->auth_view->setValue($role);
        }
        if (1 === $auth->isAllowed($chanel, $role, 'comment') && isset($form->auth_comment)) {
          $form->auth_comment->setValue($role);
        }
      }
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory
      $newFormFieldsArray = array();
      if(count($formFields) && $chanel->category_id){
            foreach($formFields as $fields){
              foreach($fields as $field){
                  $subcat = array();
                  if($fields['name'] == "subcat_id"){
                    $subcat = Engine_Api::_()->getItemTable('sesvideo_category')->getModuleSubcategory(array('category_id'=>$chanel->category_id,'column_name'=>'*'));
                  }else if($fields['name'] == "subsubcat_id"){
                    if($sesblog->subcat_id)
                    $subcat = Engine_Api::_()->getItemTable('sesvideo_category')->getModuleSubSubcategory(array('category_id'=>$chanel->subcat_id,'column_name'=>'*'));
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

    // Process
    $values = $form->getValues();
    //$values['owner_id'] = $viewer->getIdentity();
    $insert_action = false;
    $db = Engine_Api::_()->getDbtable('chanels', 'sesvideo')->getAdapter();
    $db->beginTransaction();
    $dbChanel = Engine_Api::_()->getDbtable('chanelvideos', 'sesvideo')->getAdapter();
    $dbChanel->beginTransaction();
    try {
      // Create video
      if (is_null($values['subsubcat_id']))
        $values['subsubcat_id'] = 0;
      if (is_null($values['subcat_id']))
        $values['subcat_id'] = 0;
      $chanel->setFromArray($values);
      $chanel->save();

      $deleteCo = $deleteTh = true;
      $previousCover = $chanel->cover_id;
      $previousThumbnail = $chanel->thumbnail_id;
      if (isset($values['remove_chanel_cover']) && !empty($values['remove_chanel_cover'])) {
        //Delete categories icon
        $coverIm = Engine_Api::_()->getItem('storage_file', $previousCover);
        $chanel->cover_id = 0;
        $chanel->save();
        $coverIm->delete();
        $deleteCo = false;
      }
      if (isset($values['remove_chanel_thumbnail']) && !empty($values['remove_chanel_thumbnail'])) {
        //Delete categories icon
        $thumbnailIcon = Engine_Api::_()->getItem('storage_file', $previousThumbnail);
        $chanel->thumbnail_id = 0;
        $chanel->save();
        $thumbnailIcon->delete();
        $deleteTh = false;
      }

      // Now try to create thumbnail
      if (isset($_FILES['chanel_cover']['name']) && $_FILES['chanel_cover']['name'] != '') {
        $CoverIconId = $this->setPhoto($form->chanel_cover, $chanel->chanel_id, true);
        if (!empty($CoverIconId)) {
          if ($previousCover && $deleteCo) {
            $chanelIcon = Engine_Api::_()->getItem('storage_file', $previousCover);
            $chanelIcon->delete();
          }
          $chanel->cover_id = $CoverIconId;
          $chanel->save();
        }
      }
      if (isset($_FILES['chanel_thumbnail']['name']) && $_FILES['chanel_thumbnail']['name'] != '') {
        $ThumbnailIconId = $this->setPhoto($form->chanel_thumbnail, $chanel->chanel_id);
        if (!empty($ThumbnailIconId)) {
          if ($previousThumbnail && $deleteTh) {
            $chanelThub = Engine_Api::_()->getItem('storage_file', $previousThumbnail);
            $chanelThub->delete();
          }
          $chanel->thumbnail_id = $ThumbnailIconId;
          $chanel->save();
        }
      }
      $chanel->save();

      if (empty($_POST['custom_url']) && $_POST['custom_url'] == '') {
        $chanel->custom_url = $chanel->chanel_id;
      } else {
        $chanel->custom_url = $_POST['custom_url'];
      }
      $chanel->save();
      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if (empty($values['auth_view'])) {
        $values['auth_view'] = key($form->auth_view->options);
        if (empty($values['auth_view'])) {
          $values['auth_view'] = 'everyone';
        }
      }
      if (empty($values['auth_comment'])) {
        $values['auth_comment'] = key($form->auth_comment->options);
        if (empty($values['auth_comment'])) {
          $values['auth_comment'] = 'owner_member';
        }
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      //set roles
      foreach ($roles as $i => $role) {
        $auth->setAllowed($chanel, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($chanel, $role, 'comment', ($i <= $commentMax));
      }
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $chanel->tags()->addTagMaps($viewer, $tags);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>array()));

    }
    $db->beginTransaction();
    try {
      if ($insert_action) {
        $owner = $video->getOwner();
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $chanel, 'video_chanel_new');
        if ($action != null) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $chanel);
        }
      }
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($chanel) as $action) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>array()));

    }


    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>"",'result'=>$this->view->translate('Channel edited successfully.')));

  }
  public function videosSaveAction(){
    $chanel_id = $this->_getParam('chanel_id',false);
    $chanel = Engine_Api::_()->getItem('sesvideo_chanel',$chanel_id);
    if(!$chanel_id || !$chanel)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));


    $values["video_ids"] = $this->_getParam('video_ids',"");
    $values['delete_video_ids'] = $this->_getParam('delete_video_ids',"");

    if(empty($values["video_ids"]) && empty($values["delete_video_ids"]))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Videos updated successfuly in selected chanel.")));

    $dbObject = Engine_Db_Table::getDefaultAdapter();
		$existingVideoChanel = $dbObject->query('SELECT GROUP_CONCAT(video_id) as existing_video_ids FROM engine4_video_chanelvideos WHERE chanel_id = '.$chanel->chanel_id)->fetchColumn();
		if($existingVideoChanel && $existingVideoChanel != ''){
				$existingVideoChanel = explode(',',$existingVideoChanel);
		}

    // delete videos
    if (isset($values['delete_video_ids']) && $values['delete_video_ids'] != '' && isset($chanel->chanel_id)) {
      $ids = str_replace(' ', ',', $values['delete_video_ids']);
      $query = 'DELETE FROM engine4_video_chanelvideos WHERE (`video_id`) IN (' . rtrim($ids, ',') . ') AND `chanel_id` = ' . $chanel->chanel_id;
      $stmt = $dbObject->query($query . rtrim($queryString, ','));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    // Now try to create videos
    if (isset($values['video_ids']) && $values['video_ids'] != '' && isset($chanel->chanel_id)) {
      $explodeIds = explode(',', rtrim($values['video_ids'], ','));
      $queryString = '';
      $runQuery = false;
      foreach ($explodeIds as $valuesChanel) {
        if (intval($valuesChanel) == 0 || $valuesChanel == '')
          continue;
        $valueChanels['chanel_id'] = $chanel->chanel_id;
        $valueChanels['video_id'] = $valuesChanel;
        $valueChanels['owner_id'] = $viewer->getIdentity();
        $valueChanels['modified_date'] = 'NOW()';
        $queryString .= '(' . implode(',', $valueChanels) . '),';
        $runQuery = true;
      }
      if ($runQuery) {
        $query = 'INSERT IGNORE INTO engine4_video_chanelvideos (`chanel_id`, `video_id` ,`owner_id`,`creation_date`) VALUES ';
        $stmt = $dbObject->query($query . rtrim($queryString, ','));
      }
			$newVideos = array_diff(explode(',',rtrim($values['video_ids'],',')),$existingVideoChanel);
			$totalNewVideos = count($newVideos);
		if($totalNewVideos >0 && $existingVideoChanel > 0){
			$followerChannel = Engine_Api::_()->getDbtable('chanelfollows', 'sesvideo')->getChanelFollowers($chanel->chanel_id,false,$chanel->owner_id);
			$siteTitle = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']);
			$title = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->view->translate('_SITE_TITLE'));
			$logo = $dbObject->query("SELECT params FROM engine4_core_content WHERE page_id = 1 AND name = 'core.menu-logo'")->fetchColumn();
			if($logo && $logo != ''){
				$logoData = json_decode($logo,true);
				if(isset($logoData['logo']))
					$logoRe = '<img src="'.$siteUrl.$logoData['logo'].'" alt="" style="max-height:40px;" />';
			}
			if(empty($logoRe))
				$logoRe = $siteTitle;
			$contentEmailNotification =
			'<table width="100%" bgcolor="#f0f0f0" align="center" valign="top" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td><table width="680" align="center">
<tr><td style="padding:0 20px;"><table><tr><td bgcolor="#0186bf" style="padding:10px;"><a href="'.$siteUrl.'">'.$logoRe.'</a></td></tr><tr><td style="font-family: arial,Arial,sans-serif; font-size: 20px; line-height: 24px; font-weight: bold; color: rgb(34, 34, 34); text-decoration: none; padding: 20px 0px 10px;">Check out the latest video from your channel subscriptions for '.date('M d, Y').'.</td></tr>';
									$counter = 1;
								foreach($newVideos as $video){
									if($counter > 5)
										break;
									$counter++;
									$video = Engine_Api::_()->getItem('sesvideo_video', $video);
									$user =  Engine_Api::_()->getItem('user', $video->owner_id);
									if(!$video)
										continue;
									$contentEmailNotification .=
									'<tr><td><div style="width:100%;background:#fff;"><div><a href="'.$siteUrl.$video->getHref().'"><img src="'.$siteUrl.$video->getPhotoUrl().'" alt="" align="left" width="100%" /></a></div><div style="padding:15px;clear:both;"><div><a href="'.$video->getHref().'" style="font-family:arial,Arial,sans-serif;font-size:17px;color:#222222;line-height:15px;font-weight:bold;text-decoration:none;">'.$video->getTitle().'</a><span style="display:block; font-family:arial,Arial,sans-serif;color:#999999;font-size:12px;line-height:15px;text-decoration:none;margin-top:5px;">•&nbsp;'.$this->view->translate(array('%s view', '%s views', $video->view_count), $this->view->locale()->toNumber($video->view_count)).'</span></div><div style="clear:both;margin-top:15px;"><table><tr>
<td><img src="'.$chanel->getPhotoUrl().'" width="30" height="30" style="display:block" border="0" /></td><td style="padding-left:10px;"><a href="'.$siteUrl.$chanel->getHref().'" style="font-family:arial,Arial,sans-serif;font-size:12px;color:#222222;line-height:15px;text-decoration:none" target="_blank">'.$chanel->getTitle().'</a></td></tr></table>
</div></div></div></td></tr>';
								}
								$contentEmailNotification .= '<tr><td style="height:30px;"></td></tr><tr><td style="font-family:arial,Arial,sans-serif;font-size:20px;line-height:25px;letter-spacing:0px;font-weight:bold;color:#222222">Recommended <a style="float:right;font-size:15px;font-weight:bold;text-decoration:none;color:#0186bf;" href="">view more</a></td></tr><tr><td style="height:15px;"></td></tr>';
								//recommended Videos
								$recommendedVideos = Engine_Api::_()->getDbtable('videos', 'sesvideo')->getVideo(array('not_video_id',implode(',',$newVideos),'criteria'=>5,'info'=>'view_count','limit_data'=>6),false);
								if(count($recommendedVideos)){
									$contentEmailNotification .= '<tr><td>';
									$i=1;
									foreach($recommendedVideos as $value){
										$user =  Engine_Api::_()->getItem('user', $value->owner_id);
										if($i == 1  || $i == 4){
											$margin = '';
										}else
											$margin = 'margin-left:5%;';
								$contentEmailNotification .= '<div style="height:200px;width:30%;background:#fff;float:left;margin-bottom:20px;'.$margin.'"><div><img src="'.$siteUrl.$value->getPhotoUrl().'" alt="" align="left" width="100%" height="120" /></div><div style="padding:10px;clear:both;"><div><a href="'.$value->getHref().'" style="font-family:arial,Arial,sans-serif;font-size:13px;color:#222222;line-height:15px;font-weight:bold;text-decoration:none;">'.$value->getTitle().'</a>
<span style="display:block;"><a href="'.$siteUrl.$user->getHref().'" style="font-family:arial,Arial,sans-serif;font-size:12px;color:#999999;line-height:15px;letter-spacing:0px;text-decoration:none" target="_blank">by '.$user->getTitle().'</a>
</span><span style="display:block; font-family:arial,Arial,sans-serif;font-size:12px;color:#999999;line-height:15px;letter-spacing:0px">'.$this->view->translate(array('%s view', '%s views', $value->view_count), $this->view->locale()->toNumber($value->view_count)).'</span></div></div></div>';
								$i++;
						}
						$contentEmailNotification .= '</td></tr>';
				}
			$contentEmailNotification .= '</td></tr> </table></td></tr></tbody></table>';
			if(count($followerChannel)){
				foreach($followerChannel as $follower){
					$userObj = Engine_Api::_()->user()->getUser($follower['owner_id']);
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($userObj->email, 'SESVIDEO_CHANNEL_SUBSCRIPTION_EMAIL', array(
							'content' => $contentEmailNotification,
							'title' => $chanel->title,
            ));
				}
			}
		}
   }
   Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Videos updated successfuly in selected chanel.")));
  }
  protected function setPhoto($photo, $id, $resize = false) {
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
        'parent_type' => 'sesvideo_chanel',
        'parent_id' => $id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $fileName,
    );
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    if ($resize) {
      // Resize image (main)
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_cover.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->write($mainPath)
              ->destroy();
    } else {
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_main.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(500, 500)
              ->write($mainPath)
              ->destroy();
    }
    // normal main  image resize
    $normalMainPath = $path . DIRECTORY_SEPARATOR . $base . '_icon.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(100, 100)
            ->write($normalMainPath)
            ->destroy();
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
      $iNormalMain = $filesTable->createFile($normalMainPath, $params);
      $iMain->bridge($iNormalMain, 'thumb.icon');
    } catch (Exception $e) {
      // Remove temp files
      @unlink($mainPath);
      @unlink($normalMainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Sesvideo_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
    @unlink($mainPath);
    @unlink($normalMainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }

  public function followersAction(){
      $resource_id = $this->_getParam('resource_id','2');
      $chanel = Engine_Api::_()->getItem('sesvideo_chanel',$resource_id);
      if(!$resource_id || !$chanel)
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));

      $paginator = Engine_Api::_()->getDbtable('Chanelfollows', 'sesvideo')->getChanelFollowers($resource_id);
      $page = (int)  $this->_getParam('page', 1);
      // Build paginator
      $paginator->setItemCountPerPage($this->_getParam('limit',10));
      $paginator->setCurrentPageNumber($page);

      $result = $this->memberResult($paginator);
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
      if($result <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Does not exist member.', 'result' => array()));
      else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  public function memberResult($paginator){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')){
        $memberEnable = true;
      }
      foreach($paginator as $member){
        $member = Engine_Api::_()->getItem('user',$member->owner_id);
        if(!$member)
          continue;
        $result['notification'][$counterLoop]['user_id'] = $member->getIdentity();
        $result['notification'][$counterLoop]['title'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $member->getTitle());

        $age = $this->userAge($member);
        if($age){
          $result['notification'][$counterLoop]['age'] =  $age ;
        }
        //user location
        if(!empty($member->location))
           $result['notification'][$counterLoop]['location'] =   $member->location;

       if(!empty($memberEnable)){
        //mutual friends
        $mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($member, $viewer);
        if($mfriend && !$member->isSelf($viewer)){
          $result['notification'][$counterLoop]['mutualFriends'] = $mfriend.' '.$this->view->translate("mutual friend");
        }
       }
        $result['notification'][$counterLoop]['user_image'] = $this->userImage($member->getIdentity(),"thumb.profile");
        $result['notification'][$counterLoop]['membership'] = $this->friendRequest($member);
        $counterLoop++;
      }
      return $result;
  }
  public function friendRequest($subject){

    $viewer = Engine_Api::_()->user()->getViewer();

    // Not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return "";
    }

    // No blocked
    if( $viewer->isBlockedBy($subject) ) {
      return "";
    }

    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if( !$eligible ) {
      return '';
    }

    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {

      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
      ;

      $data = $select->query()->fetch();

      if( empty($data) ) {
        return '';
      }
    }

    // One-way mode
    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
    if( !$direction ) {
      $viewerRow = $viewer->membership()->getRow($subject);
      $subjectRow = $subject->membership()->getRow($viewer);
      $params = array();

      // Viewer?
      if( null === $subjectRow ) {
        // Follow
        return array(
          'label' => $this->view->translate('Follow'),
          'action' => 'add',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
        );
      } else if( $subjectRow->resource_approved == 0 ) {
        // Cancel follow request
        return array(
          'label' => $this->view->translate('Cancel Request'),
          'action'=>'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      } else {
        // Unfollow
        return array(
          'label' => $this->view->translate('Unfollow'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      }
      // Subject?
      if( null === $viewerRow ) {
        // Do nothing
      } else if( $viewerRow->resource_approved == 0 ) {
        // Approve follow request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',

        );
      } else {
        // Remove as follower?
        return array(
          'label' => $this->view->translate('Unfollow'),
           'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      }
      if( count($params) == 1 ) {
        return $params[0];
      } else if( count($params) == 0 ) {
        return "";
      } else {
        return $params;
      }
    }

    // Two-way mode
    else {

      $table =  Engine_Api::_()->getDbTable('membership','user');
      $select = $table->select()
        ->where('resource_id = ?', $viewer->getIdentity())
        ->where('user_id = ?', $subject->getIdentity());
      $select = $select->limit(1);
      $row = $table->fetchRow($select);

      if( null === $row ) {
        // Add
        return array(
          'label' => $this->view->translate('Add Friend'),
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          'action' => 'add',
        );
      } else if( $row->user_approved == 0 ) {
        // Cancel request
        return array(
          'label' => $this->view->translate('Cancel Friend'),
          'action' => 'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      } else if( $row->resource_approved == 0 ) {
        // Approve request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',

        );
      } else {
        // Remove friend
        return array(
          'label' => $this->view->translate('Remove Friend'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      }
    }
  }
  public function userAge($member){
    $getFieldsObjectsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($member);
    if (!empty($getFieldsObjectsByAlias['birthdate'])) {
      $optionId = $getFieldsObjectsByAlias['birthdate']->getValue($member);
      if ($optionId && @$optionId->value) {
        $age = floor((time() - strtotime($optionId->value)) / 31556926);
        return $this->view->translate(array('%s year old', '%s years old', $age), $this->view->locale()->toNumber($age));
      }
    }
    return "";
  }

  public function chanelDataAction() {
    $chanel_id = $this->_getParam('chanel_id', false);
    if ($chanel_id) {
      if (isset($_POST['params']))
        $params = json_decode($_POST['params'], true);
      $this->view->category_limit = $category_limit = isset($params['category_limit']) ? $params['category_limit'] : $this->_getParam('category_limit', '10');
      $this->view->video_limit = $video_limit = isset($params['video_limit']) ? $params['video_limit'] : $this->_getParam('video_limit', '8');
      $this->view->chanel_limit = $chanel_limit = isset($params['chanel_limit']) ? $params['chanel_limit'] : $this->_getParam('chanel_limit', '8');
      $this->view->count_chanel = $count_chanel = isset($params['count_chanel']) ? $params['count_chanel'] : $this->_getParam('count_chanel', '1');
      $this->view->width = $width = isset($params['width']) ? $params['width'] : $this->_getParam('width', '120');
      $this->view->height = $height = isset($params['height']) ? $params['height'] : $this->_getParam('height', '80');
      $this->view->seemore_text = $seemore_text = isset($params['seemore_text']) ? $params['seemore_text'] : $this->_getParam('seemore_text', '+ See all [category_name]');
      $this->view->allignment_seeall = $allignment_seeall = isset($params['allignment_seeall']) ? $params['allignment_seeall'] : $this->_getParam('allignment_seeall', 'left');
      $this->view->title_truncation = $title_truncation = isset($params['title_truncation']) ? $params['title_truncation'] : $this->_getParam('title_truncation', '100');
      $this->view->description_truncation = $description_truncation = isset($params['description_truncation']) ? $params['description_truncation'] : $this->_getParam('description_truncation', '150');
      $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('by', 'view', 'title', 'follow', 'followButton', 'featuredLabel', 'sponsoredLabel', 'description', 'chanelPhoto', 'chanelVideo', 'chanelThumbnail','rating'));
      foreach ($show_criterias as $show_criteria)
        $this->view->{$show_criteria . 'Active'} = $show_criteria;
      $resultArray = array();
      $chanelDatas = $resultArray['chanel_data'] = Engine_Api::_()->getDbTable('chanels', 'sesvideo')->getChanels(array('chanel_id' => $chanel_id), false);
      if (in_array('chanelVideo', $show_criterias)) {
        foreach ($chanelDatas as $chanelData) {
          $resultArray['videos'] = Engine_Api::_()->getDbTable('chanelvideos', 'sesvideo')->getChanelAssociateVideos($chanelData, array('limit_data' => $video_limit, 'paginator' => false));
        }
      }
      $this->view->resultArray = $resultArray;
    } else {
      $this->_forward('requireauth', 'error', 'core');
    }
  }

  public function checkurlAction($data = "",$chanel_id = "") {
    $return = 0;
    $httpConfig = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://";
    $setting = Engine_Api::_()->getApi('settings', 'core');
    $url = $httpConfig . $_SERVER['HTTP_HOST'] . '/' . $setting->getSetting('video.videos.manifest', 'videos') . '/' . $setting->getSetting('video.chanel.manifest', 'chanels') . '/' . $data;
    if ($data) {
      //if (!preg_match('/[^A-Za-z0-9]/', $data)) {
        $paginator = Engine_Api::_()->getDbtable('chanels', 'sesvideo')->checkUrl($data, $chanel_id);
        $slugExists = $paginator->getTotalItemCount();
        if ($slugExists <= 0)
          $return = 1;
        else
          $return = 0;
      //} else {
        //$return = 0;
      //}
    } else {
      $return = 1;
    }
    return $return;
  }

  public function followAction() {

    if (!$this->_helper->requireUser->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $chanelId = $this->_getParam('chanel_id');
    $userId = Engine_Api::_()->user()->getViewer()->getIdentity();

    if ($userId == 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

		$chanel = Engine_Api::_()->getItem('sesvideo_chanel', $chanelId);
    if(!$chanel){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
    }
    $checkFollow = Engine_Api::_()->getDbtable('chanelfollows', 'sesvideo')->checkFollow($userId, $chanelId);

    if ($checkFollow == 0) {
      $chanelFollow = Engine_Api::_()->getDbtable('chanelfollows', 'sesvideo')->createRow();
      $chanelFollow->chanel_id = $chanelId;
      $chanelFollow->owner_id = $userId;
      $chanelFollow->creation_date = 'NOW()';
      $chanelFollow->save();

      $viewer = Engine_Api::_()->user()->getViewer();

      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'sesvideo_chanel_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $chanel->getType(), "object_id = ?" => $chanel->getIdentity()));

      $owner = $chanel->getOwner();
      if ($chanel->owner_id != $viewer->getIdentity()) {
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $chanel, 'sesvideo_chanel_follow');
      }

      $result = Engine_Api::_()->getDbtable('actions', 'activity')->fetchRow(array('type =?' => 'sesvideo_chanel_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $chanel->getType(), "object_id = ?" => $chanel->getIdentity()));
      if (!$result) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $chanel, 'sesvideo_chanel_follow');
        if ($action)
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $chanel);
      }
			$chanel->follow_count = $chanel->follow_count + 1;
			$chanel->save();
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' =>$this->view->translate('Channel follow successfully.')));
    } else {
      $chanelFollow = Engine_Api::_()->getDbtable('chanelfollows', 'sesvideo')->delete(array('chanel_id =?' => $chanelId, 'owner_id =?' => $userId));
			$chanel->follow_count = $chanel->follow_count - 1;
			$chanel->save();
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' =>$this->view->translate('Channel un-followed successfully.')));
    }
  }

  public function deleteFollowChanelAction() {
    if (!$this->_helper->requireUser->isValid())
      return;
    $chanelID = $this->_getParam('chanel_id');
    $userID = $this->_getParam('user_id');
    $chanelFollow = Engine_Api::_()->getDbtable('chanelfollows', 'sesvideo')->delete(array('chanel_id =?' => $chanelID, 'owner_id =?' => $userID));
  }

   //album view function.
  public function lightboxAction() {
    $photo = Engine_Api::_()->getItem('sesvideo_chanelphoto',$this->_getParam('photo_id'));
    if (0 !== ($album_id = (int) $this->_getParam('album_id')) &&
            null !== ($album = Engine_Api::_()->getItem('sesvideo_chanel', $album_id)) && $photo) {
    }else{
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array()));
    }

    $photo_id = $photo->getIdentity();

    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();

    $albumData = array();
    if($viewer->getIdentity() > 0){
      $menu = array();
      $counterMenu = 0;
      $menu[$counterMenu]["name"] = "save";
      $menu[$counterMenu]["label"] = "Save Photo";
      $counterMenu++;
			$canEdit  = $album->authorization()->isAllowed($viewer, 'edit') ? true : false;
      if($canEdit){
        $menu[$counterMenu]["name"] = "edit";
        $menu[$counterMenu]["label"] = "Edit Photo";
        $counterMenu++;
      }

			$can_delete  = $album->authorization()->isAllowed($viewer,'delete') ? true : false;
      if($canEdit){
        $menu[$counterMenu]["name"] = "delete";
        $menu[$counterMenu]["label"] = "Delete Photo";
        $counterMenu++;
      }
      $menu[$counterMenu]["name"] = "report";
      $menu[$counterMenu]["label"] = "Report Photo";
      $counterMenu++;

      $menu[$counterMenu]["name"] = "makeprofilephoto";
      $menu[$counterMenu]["label"] = "Make Profile Photo";
      $counterMenu++;
      $albumData['menus'] = $menu;
      $canComment =  $album->authorization()->isAllowed($viewer, 'comment') ? true : false;

      $albumData['can_comment'] = $canComment;

      $sharemenu = array();
      if($viewer->getIdentity() > 0){
        $sharemenu[0]["name"] = "siteshare";
        $sharemenu[0]["label"] = "Share";
      }
      $sharemenu[1]["name"] = "share";
      $sharemenu[1]["label"] = "Share Outside";
      $albumData['share'] = $sharemenu;
		}

    $condition = $this->_getParam('condition');
    if(!$condition){
      $next = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,">="));
      $previous = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,"<"));
      $array_merge = array_merge($previous,$next);

      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
        $recArray = array();
        $reactions = Engine_Api::_()->getDbTable('reactions','sesadvancedcomment')->getPaginator();
        $counterReaction = 0;

        foreach($reactions as $reac){
          if(!$reac->enabled)
            continue;
          $albumData['reaction_plugin'][$counterReaction]['reaction_id']  = $reac['reaction_id'];
          $albumData['reaction_plugin'][$counterReaction]['title']  = $this->view->translate($reac['title']);
          $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id,'','');
          $albumData['reaction_plugin'][$counterReaction]['image']  = $icon['main'];
          $counterReaction++;
        }

      }

    }else{
      $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,$condition));
    }
    $albumData['photos'] = $array_merge;
    if(count($albumData['photos']) <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No photo created in this album yet.', 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $albumData)));

  }
  function editDescriptionAction(){
    $photo = Engine_Api::_()->getItem('sesvideo_chanelphoto',$this->_getParam('photo_id',''));
    $photo_id = $photo->getIdentity();
    $description = $this->_getParam('description','');
    $photo->description = $description;
    $photo->save();
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Photo Description Updated successfully."));
  }
  public function nextPreviousImage($photo_id,$album_id,$condition = "<="){
    $photoTable = Engine_Api::_()->getItemTable('sesvideo_chanelphoto');
    $select = $photoTable->select();
    $select->where('chanel_id =?',$album_id);
    $select->where('chanelphoto_id '.$condition.' ?',$photo_id);
    $select->order('order ASC');
    $select->limit(20);
    return $photoTable->fetchAll($select);
  }
  public function uplaodPhotoAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
      $album_id = $this->_getParam('chanel_id','');
      $album = Engine_Api::_()->getItem('sesvideo_chanel',$album_id);
      if(!$album || !$album->authorization()->isAllowed($viewer, 'edit'))
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Invalid Request', 'result' => array()));

       ini_set("memory_limit","240M");

       if(!empty($_FILES["attachmentImage"]) && count($_FILES["attachmentImage"]) > 0){
           // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('sesvideo_chanel');
          $type = 'wall';
          $photoTable = Engine_Api::_()->getItemTable('sesvideo_chanelphoto');
          $auth = Engine_Api::_()->authorization()->context;
          try{
            if(count($_FILES['attachmentImage']['name'])){
              $api = Engine_Api::_()->getDbtable('actions', 'activity');
              $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'album_photo_new', null, array('count' =>  count($_FILES['attachmentImage']['name'])));
            }
           $counter = 0;
           foreach($_FILES['attachmentImage'] as $image){
              $uploadimage = array();
              if ($_FILES['attachmentImage']['name'][$counter] == "")
               continue;
              $uploadimage["name"] = $_FILES['attachmentImage']['name'][$counter];
              $uploadimage["type"] = $_FILES['attachmentImage']['type'][$counter];
              $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$counter];
              $uploadimage["error"] = $_FILES['attachmentImage']['error'][$counter];
              $uploadimage["size"] = $_FILES['attachmentImage']['size'][$counter];
              $photo = $photoTable->createRow();
              $photo->setFromArray(array(
                  'owner_type' => 'user',
                  'owner_id' => $viewer->getIdentity()
              ));
              $photo->save();
              $photo->setPhoto($uploadimage);
              $photo->chanel_id = $album->chanel_id;
              $photo->save();

              // Authorizations
              $auth->setAllowed($photo, 'everyone', 'view', true);
              $auth->setAllowed($photo, 'everyone', 'comment', true);
              if( $action instanceof Activity_Model_Action && $counter < 9)
              {
                $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
              }
            $counter++;
          }
          }catch(Exception $e){
            $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
          }
      }
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Photo uploaded successfully.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
  }
}
