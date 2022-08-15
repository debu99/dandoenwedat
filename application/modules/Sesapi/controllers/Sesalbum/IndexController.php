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
class Sesalbum_IndexController extends Sesapi_Controller_Action_Standard
{
  protected $_sesalbumEnabled = false;
  function init(){
    $this->_sesalbumEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum');
    parent::init();
  }

    public function createAction(){
      if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      $album_id = $this->_getParam('album_id',false);
      $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesalbum')->profileFieldId();
       // set up data needed to check quota
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['user_id'] = $viewer->getIdentity();
      $this->view->current_count = $current_count =Engine_Api::_()->getDbtable('albums', 'sesalbum')->getUserAlbumCount($values);
      $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'album', 'max_albums');
      if (($this->view->current_count >= $quota) && !empty($quota)) {
        // return error message
        $message = $this->view->translate('You have already uploaded the maximum number of albums allowed.');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
      }
      // Get form
      $this->view->form = $form = new Sesalbum_Form_Album(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));

      // Render
      $form->removeElement('album');
      $form->removeElement('lat');
      $form->removeElement('map-canvas');
      $form->removeElement('ses_location');
      $form->removeElement('lng');
      $form->removeElement('sesmediaimporter_data');
      $form->removeElement('fancyuploadfileids');
      $form->removeElement('tabs_form_albumcreate');
      $form->removeElement('drag-drop');

      $form->removeElement('from-url');
      $form->removeElement('file_multi_sesalbum');
      $form->removeElement('uploadFileContainer');
      // Check if post and populate
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
      $db = Engine_Api::_()->getItemTable('album')->getAdapter();
      $db->beginTransaction();
      try {

        $album = $form->saveValues(true);
        if(!empty($_FILES['image']['size'])){
          $photoTable = Engine_Api::_()->getItemTable('album_photo');
          $photo = $photoTable->createRow();
          $photo->setFromArray(array(
              'owner_type' => 'user',
              'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
          ));
          $photo->save();
          $photo->setPhoto($_FILES['image']);
          $photo->order = $photo->photo_id;
          $photo->album_id = $album->album_id;
          $photo->save();
          $api = Engine_Api::_()->getDbtable('actions', 'activity');
          $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'album_photo_new', null, array('count' =>  1));
          if( $action instanceof Activity_Model_Action && $count < 9)
          {
            $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
          }
        }
        if(!empty($_POST['location'])){
            $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
            if($latlng){
              $_POST['lat'] = $latlng['lat'];
              $_POST['lng'] = $latlng['lng'];
            }
          }
        // Add tags
        $values = $form->getValues();
        $tags = preg_split('/[,]+/', $values['tags']);
        $album->tags()->addTagMaps($viewer, $tags);
        if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $album->album_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesalbum_album")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }

     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('album_id'=>$album->getIdentity(),'message'=>$this->view->translate('Album created successfully.'))));

  }
  public function removeTagAction()
  {
    $tagmap_id = $this->_getParam('tagmap_id','');
    $subject = Engine_Api::_()->getItem('album_photo',$this->_getParam('photo_id'));
    if( !$this->_helper->requireUser()->isValid() || !$subject)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();

    // Get tagmao
    $tagmap = $subject->tags()->getTagMapById($tagmap_id);
    if( !($tagmap instanceof Core_Model_TagMap) ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Tagmap missing"), 'result' => array()));
    }

    // Can remove if: is tagger, is tagged, is owner of resource, has tag permission
    if( $viewer->getGuid() != $tagmap->tagger_type . '_' . $tagmap->tagger_id &&
        $viewer->getGuid() != $tagmap->tag_type . '_' . $tagmap->tag_id &&
        !$subject->isOwner($viewer) /* &&
        !$subject->authorization()->isAllowed($viewer, 'tag') */ ) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Not authorized'), 'result' => array()));
    }
    $tagmap->delete();

    // Get tags
      $tags = array();
      foreach ($subject->tags()->getTagMaps() as $tagmap) {
        $tags[] = array_merge($tagmap->toArray(), array(
            'id' => $tagmap->getIdentity(),
            'text' => $tagmap->getTitle(),
            'href' => $tagmap->getHref(),
            'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
        ));
      }
     $result["tags"] = $tags;
     $result['message'] = $this->view->translate("Tagged user removed successfully.");
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));

  }

  function getTaggedUserAction(){
    $photo_id = $this->_getParam('photo_id','');
    if(!$photo_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
    $subject = Engine_Api::_()->getItem('album_photo',$photo_id);
    $album = Engine_Api::_()->getItem('album',$subject->album_id);
    // Get tags
    $tags = array();
    foreach ($subject->tags()->getTagMaps() as $tagmap) {
      $owner = Engine_Api::_()->getItem('user',$tagmap->tag_id);
      if($owner && $owner->photo_id){
        $photo= $this->getBaseUrl(false,$owner->getPhotoUrl());
      }else
        $photo =  $this->getBaseUrl(true,'/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
      $tags[] = array_merge($tagmap->toArray(), array(
          'id' => $tagmap->getIdentity(),
          'label' => $tagmap->getTitle(),
          'untag'=>$album->isOwner($this->view->viewer()),
          'href' => $tagmap->getHref(),
          'photo' => $photo,
          'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
      ));
    }
    $result['tags'] = $tags;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }

  function addTagAction(){
     $photo_id = $this->_getParam('photo_id','');
     $user_id = $this->_getParam('user_id','');
     if(!$photo_id || !$user_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
     $subject = Engine_Api::_()->getItem('album_photo',$photo_id);
     if (!method_exists($subject, 'tags')) {
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("whoops! doesn\'t support tagging"), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    // GUID tagging
    if (null !== ($guid = $this->_getParam('user_id'))) {
      $tag = Engine_Api::_()->getItem('user',$user_id);
    }
    // STRING tagging
    else if (null !== ($text = $this->_getParam('label'))) {
      $tag = $text;
    }
    $extra['x'] = 0;
    $extra['y'] = 0;
    $extra['w'] = 48;
    $extra['h'] = 38;
    $tagmap = $subject->tags()->addTagMap($viewer, $tag, $extra);
    if (is_null($tagmap)) {
      // item has already been tagged
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Item Already Tagged")));
    }
    if (!$tagmap instanceof Core_Model_TagMap) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Tagmap was not recognised"), 'result' => array()));
    }
    // Do stuff when users are tagged
    if ($tag instanceof User_Model_User && !$subject->isOwner($tag) && !$viewer->isSelf($tag)) {
      // Add activity
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity(
                $viewer, $tag, 'tagged', '', array(
                'label' => $this->view->translate(str_replace('_', ' ', 'sesphoto'))
              )
      );
      if ($action)
        $action->attach($subject);
      // Add notification
      $type_name = $this->view->translate(str_replace('_', ' ', 'sesphoto'));
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
          $tag, $viewer, $subject, 'tagged', array(
            'object_type_name' => $type_name,
            'label' => $type_name,
          )
      );
    }

     // Get tags
      $tags = array();
      foreach ($subject->tags()->getTagMaps() as $tagmap) {
        $tags[] = array_merge($tagmap->toArray(), array(
            'id' => $tagmap->getIdentity(),
            'text' => $tagmap->getTitle(),
            'href' => $tagmap->getHref(),
            'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
        ));
      }
     $result["tags"] = $tags;
     $result['message'] = $this->view->translate("User tagged successfully.");
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));

  }

  function deleteAction(){
    $photo = Engine_Api::_()->getItem('album_photo',$this->_getParam('photo_id',''));
    $photo_id = $photo->getIdentity();
    $viewer = Engine_Api::_()->user()->getViewer();
    $album = Engine_Api::_()->getItem('album', $photo->album_id);
    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $photo->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate('Photo Deleted Successfully.')));
  }
  function uploadCoverAction(){
    $item_id = $this->_getParam('album_id', '0');
    $item = Engine_Api::_()->getItem('sesalbum_album', $item_id);
		if ($item_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));

		$art_cover = $item->art_cover;
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
        $item->setCoverPhoto($file);

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
  //remove cover photo action
	public function removeCoverAction(){
		$album_id = $this->_getParam('album_id', '0');
    $item = Engine_Api::_()->getItem('sesalbum_album', $album_id);
		if ($album_id == 0 || !$item)
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing','result'=>''));
		if(isset($item->art_cover) && $item->art_cover > 0){
			$im = Engine_Api::_()->getItem('storage_file', $item->art_cover);
			$item->art_cover = 0;
			$item->save();
			$im->delete();
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->translate("Channel cover removed successfully.")));
	}
  function editDescriptionAction(){
    $photo = Engine_Api::_()->getItem('album_photo',$this->_getParam('photo_id',''));
    $photo_id = $photo->getIdentity();
    $description = $this->_getParam('description','');
    $photo->description = $description;
    $photo->save();
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Photo Description Updated successfully.")));
  }
  public function categoriesAction(){
    $params['countAlbums'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'sesalbum')->getCategory($params);
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $category){
      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;
      if($category->thumbnail != '' && !is_null($category->thumbnail) && intval($category->thumbnail)):
        $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl(""));
      endif;
      if($category->cat_icon != '' && !is_null($category->cat_icon) && intval($category->cat_icon)):
        $catgeoryArray["category"][$counter]["cat_icon"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->cat_icon)->getPhotoUrl('thumb.icon'));
      endif;
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s album', '%s albums', $category->total_album_categories), $this->view->locale()->toNumber($category->total_album_categories));

      $counter++;
    }
    $catgeoryArray['can_create'] = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ? true : false;
    if($catgeoryArray <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array()));
    else
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array()));
  }
  public function getAlbumsAction(){
     $user = Engine_Api::_()->user()->getViewer();
     $table = Engine_Api::_()->getItemTable('album');
     $select = $table->select()->from($table)->where('owner_id =?',$user->getIdentity())->order('creation_date DESC');
     $paginator = Zend_Paginator::factory($select);
     $paginator->setItemCountPerPage($this->_getParam('limit', 10));
     $paginator->setCurrentPageNumber( $this->_getParam('page'));
     $result = $this->getAlbums($paginator);
  }
  public function getAlbums($paginator,$return = false){
    $result = array();
    $counter = 0;
    $canFavourite =  Engine_Api::_()->authorization()->isAllowed('album',Engine_Api::_()->user()->getViewer(), 'favourite_album');
    foreach($paginator as $albums){
        $album = $albums->toArray();
        $album['photo_count'] = $albums->count();
        $album['user_title'] = $albums->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0){
          $album['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($albums);
          $album['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($albums);
          if($canFavourite && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.allowfavourite', 1)){
            $album['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($albums,'favourites','sesalbum','album');
            $album['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($albums,'favourites','sesalbum','album');
          }
        }
        $photo = Engine_Api::_()->getItem('photo',$album["photo_id"]);
        if($photo)
          $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo,'',"");
        else
          continue;
        if(!count($images))
          $images['images']['main'] = $this->getBaseUrl(true,$albums->getPhotoUrl());
        $result[$counter] = array_merge($album,$album_photo);
        $counter++;
    }
    if($return)
      return $result;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $results['albums'] = $result;
    $results['can_create'] = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ? true : false;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No album created by you yet.'), 'result' => array()));
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $results),$extraParams));
    }
  }
 function searchFormAction(){
    $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));
   $search_for = $this-> _getParam('search_for', 'album');
   $default_search_type = $this->_getParam('default_search_type', 'recentlySPcreated');

      $form = new Sesalbum_Form_Search(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $this->_getParam('location', 'yes'),'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));

    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];
      }
    }
    if(count($filterOptions)){
		$arrayOptions = $filterOptions;
		$filterOptions = array();
		foreach ($arrayOptions as $key=>$filterOption) {
      $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
      $filterOptions[$key] = ucwords($value);
    }
		$filterOptions = array(''=>'')+$filterOptions;
		 $form->sort->setMultiOptions($filterOptions);
		 $form->sort->setValue($default_search_type);
	 }
   $form->removeElement('loading-img-sesalbum');
   $form->removeElement('lng');
   $form->removeElement('lat');
    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
    $this->generateFormFields($formFields);
  }
  public function browseAction()
  {
    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("permission_error"), 'result' => array()));
   $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));
   $search_for = $this-> _getParam('search_for', 'album');
   $default_search_type = $this->_getParam('default_search_type', 'recentlySPcreated');

      $form = new Sesalbum_Form_Search(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => 'yes','kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));


    if(count($filterOptions)){
      $arrayOptions = $filterOptions;
      $filterOptions = array();
      foreach ($arrayOptions as $filterOption) {
        $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
        $filterOptions[$filterOption] = ucwords($value);
      }
       $filterOptions = array(''=>'')+$filterOptions;
       $form->sort->setMultiOptions($filterOptions);
       $form->sort->setValue($default_search_type);
	  }
    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];
      }
    }
    $value = array();
    $form->populate($_POST);
    $user_id = $this->_getParam('user_id',false);
    $options = $form->getValues();
    $value['sort'] = $options["sort"];

    if(isset($value['sort']) && $value['sort'] != ''){
			$value['getParamSort'] = str_replace('SP','_',$value['sort']);
		}else
			$value['getParamSort'] = 'creation_date';

		switch($value['getParamSort']) {
      case 'most_viewed':
        $options['order'] = 'view_count';
        break;
			case 'most_favourite':
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.allowfavourite', 1)) {
        $options['order'] = 'favourite_count';
        }
        break;
			case 'most_liked':
				$options['order'] = 'like_count';
				break;
			case 'most_commented':
				$options['order'] = 'comment_count';
				break;
			case 'featured':
				$options['order'] = 'is_featured';
				break;
			case 'sponsored':
				$options['order'] = 'is_sponsored';
				break;
			case 'most_rated':
				$options['order'] = 'rating';
				break;
      case 'creation_date':
      default:
        $options['order'] = 'creation_date';
        break;
    }


    // Get search params
    $page = (int)  $this->_getParam('page', 1);

    if($user_id){
      $options['allowSpecialAlbums'] = true;
      $options["user_id"] = $user_id;
    }
    $options['text'] = !empty($options['search_text']) ? $options['search_text'] : '';
    $paginator = Engine_Api::_()->getDbTable('albums', 'sesalbum')->getAlbumSelect($options);;
    $page = (int)  $this->_getParam('page', 1);

    // Build paginator
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($page);

    $result = $this->getAlbums($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('Does not exist member.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }

  public function manageAction()
  {
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $type = 'album';
	  $paginator = Engine_Api::_()->getDbTable('albums', 'sesalbum')->profileAlbums(array('userId' =>Engine_Api::_()->user()->getViewer()->getIdentity()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $result = $this->getAlbums($paginator,true);

    $menuoptions= array();
    $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'album', 'edit');
    $counter = 0;
    //$menuoptions[$counter]['name'] = "managephotos";
    //$menuoptions[$counter]['label'] = $this->view->translate("Manage Photos");
    //$counter++;
    if($canEdit){
      $menuoptions[$counter]['name'] = "edit";
      $menuoptions[$counter]['label'] = $this->view->translate("Edit Settings");
      $counter++;
    }
    $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'album', 'delete');
    if($canDelete){
      $menuoptions[$counter]['name'] = "delete";
      $menuoptions[$counter]['label'] = $this->view->translate("Delete Album");
    }
    $results['menus'] = $menuoptions;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $results['albums'] = $result;
    $results['can_create'] = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ? true : false;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $results),$extraParams));
  }
  function searchFormPhotosAction(){
    $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));
   $search_for = $this-> _getParam('search_for', 'photo');
   $default_search_type = $this->_getParam('default_search_type', 'recentlySPcreated');

      $form = new Sesalbum_Form_Search(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $this->_getParam('location', 'yes'),'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));

    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];
      }
    }
    if(count($filterOptions)){
		$arrayOptions = $filterOptions;
		$filterOptions = array();
		foreach ($arrayOptions as $key=>$filterOption) {
      $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
      $filterOptions[$key] = ucwords($value);
    }
		$filterOptions = array(''=>'')+$filterOptions;
		 $form->sort->setMultiOptions($filterOptions);
		 $form->sort->setValue($default_search_type);
	 }
   $form->removeElement('loading-img-sesalbum');
   $form->removeElement('lng');
   $form->removeElement('lat');
    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
    $this->generateFormFields($formFields);
  }
  public function browsePhotoAction(){

    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
   $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));
   $search_for = $this-> _getParam('search_for', 'photo');
   $default_search_type = $this->_getParam('default_search_type', 'recentlySPcreated');

      $form = new Sesalbum_Form_Search(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => 'yes','kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));

    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];
      }
    }
    if(count($filterOptions)){
		$arrayOptions = $filterOptions;
		$filterOptions = array();
		foreach ($arrayOptions as $filterOption) {
      $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
      $filterOptions[$filterOption] = ucwords($value);
    }
		$filterOptions = array(''=>'')+$filterOptions;
		 $form->sort->setMultiOptions($filterOptions);
		 $form->sort->setValue($default_search_type);
	 }
    $form->populate($_POST);
    // Get search params
    $page = (int)  $this->_getParam('page', 1);
    $options = $form->getValues();
    $defaultOpenTab = ($form->sort->getValue());
    $options['text'] = !empty($options['search']) ? $options['search'] : '';
    switch($defaultOpenTab){
			case 'recentlySPcreated':
				$popularCol = 'creation_date';
				$type = 'creation';
			break;
			case 'mostSPviewed':
				$popularCol = 'view_count';
				$type = 'view';
			break;
			case 'mostSPliked':
				$popularCol = 'like_count';
				$type = 'like';
			break;
			case 'mostSPcommented':
				$popularCol = 'comment_count';
				$type = 'comment';
			break;
			case 'mostSPrated':
				$popularCol = 'rating';
				$type = 'rating';
			break;
			case 'featured':
				$popularCol = 'is_featured';
				$type = 'is_featured';
				$fixedData = 'is_featured';
			break;
			case 'sponsored':
				$popularCol = 'is_sponsored';
				$type = 'is_sponsored';
				$fixedData = 'is_sponsored';
			break;
			case 'mostSPfavourite':
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.allow.favouritephoto', 1)) {
                    $popularCol = 'favourite_count';
                    $type = 'favourite';
				}
			break;
			case 'mostSPdownloaded':
				$popularCol = 'download_count';
				$type = 'download';
			break;
			default:
			break;
		}
		$this->view->type = $type;
		$this->view->itemOrigTitle = isset($defaultOptions[$defaultOpenTab]) ? $defaultOptions[$defaultOpenTab] : 'items';
		$options['popularCol'] = isset($popularCol) ? $popularCol : 'creation_date';
    $paginator = Engine_Api::_()->getDbTable('photos', 'sesalbum')->tabWidgetPhotos($options);
    $page = (int)  $this->_getParam('page', 1);
    // Build paginator
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($page);

    $photos = $this->getPhotos($paginator);

    if($page == 1 && count($photos)){
      $counter = 0;
      foreach($photos as $val){
        $headerPhotos[] = $val;
        unset($photos[$counter]);
        if($counter == 2)
          break;
        $counter++;
      }
      $result['headerPhotos'] = $headerPhotos;
      $photos =  array_values($photos);
    }
    $result['photos'] = $photos;
    $result['can_create'] = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ? true : false;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate('No Photo Created Yet.')));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

  }
public function getPhotos($paginator){
      $result = array();
    $counter = 0;
    $canFavourite =  Engine_Api::_()->authorization()->isAllowed('album',Engine_Api::_()->user()->getViewer(), 'favourite_photo');
    foreach($paginator as $photos){
        $photo = $photos->toArray();
        $photo['user_title'] = $photos->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0){
          $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
          $photo['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($photos);
          if($canFavourite && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.allow.favouritephoto', 1)){
            $photo['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($photos,'favourites','sesalbum','album_photo');
            $photo['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($photos,'favourites','sesalbum','album_photo');
          }
        }

        $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos,'',"");

        if(!count($images))
          $album_photo['images']['main'] = $this->getBaseUrl(true,$photos->getPhotoUrl());
        $result[$counter] = array_merge($photo,$album_photo);
        $counter++;
    }
    return $result;
  }

}
