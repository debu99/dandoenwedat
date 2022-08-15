<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AlbumController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesalbum_AlbumController extends Sesapi_Controller_Action_Standard {

  //album constructor function
  public function init() {
    if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid())
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if (0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
            null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id))) {
      Engine_Api::_()->core()->setSubject($photo);
    } else if (0 !== ($album_id = (int) $this->_getParam('album_id')) &&
            null !== ($album = Engine_Api::_()->getItem('album', $album_id))) {
      Engine_Api::_()->core()->setSubject($album);
    }

  }

  //album view function.
  public function lightboxAction() {
    $photo = Engine_Api::_()->core()->getSubject();
    if($photo && !$this->_getParam('album_id',null)){
      $album_id = $photo->album_id;
    }else{
      $album_id = $this->_getParam('album_id',null);
    }
    if ($album_id &&
            null !== ($album = Engine_Api::_()->getItem('album', $album_id))) {
    }else{
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));
    }

    $photo_id = $photo->getIdentity();
    if (!$this->_helper->requireSubject('album_photo')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();

    $albumData = array();
    if($viewer->getIdentity() > 0){
      $menu = array();
      $counterMenu = 0;
      $menu[$counterMenu]["name"] = "save";
      $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");
      $counterMenu++;
			$canEdit  = $album->authorization()->isAllowed($viewer, 'edit') ? true : false;
      if($canEdit){
        $menu[$counterMenu]["name"] = "edit";
        $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");
        $counterMenu++;
      }

			$can_delete  = $album->authorization()->isAllowed($viewer,'delete') ? true : false;
      if($canEdit){
        $menu[$counterMenu]["name"] = "delete";
        $menu[$counterMenu]["label"] = $this->view->translate("Delete Photo");
        $counterMenu++;
      }
      $menu[$counterMenu]["name"] = "report";
      $menu[$counterMenu]["label"] = $this->view->translate("Report Photo");
      $counterMenu++;

      $menu[$counterMenu]["name"] = "makeprofilephoto";
      $menu[$counterMenu]["label"] = $this->view->translate("Make Profile Photo");
      $counterMenu++;
      $albumData['menus'] = $menu;
      $can_tag = $album->authorization()->isAllowed($viewer, 'tag') ? true : false;
      $canUntagGlobal = $album->isOwner($viewer) ? true : false;
      $canComment =  $album->authorization()->isAllowed($viewer, 'comment') ? true : false;

      $albumData['can_comment'] = $canComment;
      $albumData['can_tag'] = $can_tag;
      $albumData['can_untag'] = $canUntagGlobal;

      $sharemenu = array();
      if($viewer->getIdentity() > 0){
        $sharemenu[0]["name"] = "siteshare";
        $sharemenu[0]["label"] = $this->view->translate("Share");
      }
      $sharemenu[1]["name"] = "share";
      $sharemenu[1]["label"] = $this->view->translate("Share Outside");
      $albumData['share'] = $sharemenu;
		}

    $condition = $this->_getParam('condition');
    if(!$condition){
      $next = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,">="),true);
      $previous = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,"<"),true);
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
      $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,$condition),true);
    }
    $albumData['photos'] = $array_merge;
    if(count($albumData['photos']) <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('No photo created in this album yet.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $albumData)));

  }
  public function nextPreviousImage($photo_id,$album_id,$condition = "<="){
    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    $select = $photoTable->select();
    $select->where('album_id =?',$album_id);
    $select->where('photo_id '.$condition.' ?',$photo_id);
    $select->order('order ASC');
    $select->limit(20);
    return $photoTable->fetchAll($select);
  }
  //album view function.
  public function viewAction() {
    if (!$this->_helper->requireSubject('album')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $album = Engine_Api::_()->core()->getSubject();
    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $album = Engine_Api::_()->core()->getSubject();

    if ($viewer->getIdentity() != 0 && isset($album->album_id)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesalbum_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $album->album_id . '", "album","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }

    $albumData = array();

    $albumData['album'] = $album->toArray();

     //option upload cover photo
      if($album->isOwner($viewer)){
        $coverPhotoOptions[] = array('label'=>$this->view->translate('Upload Cover Photo'),'name'=>'upload_cover');
        $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");
        if($isAlbumEnable)
         $coverPhotoOptions[] = array('label'=>$this->view->translate('Choose From Albums'),'name'=>'choose_from_albums');
         if($album->art_cover){
          $coverPhotoOptions[] = array('label'=>$this->view->translate('View Cover Photo'),'name'=>'view_cover_photo');
          $coverPhotoOptions[] = array('label'=>$this->view->translate('Remove Cover Photo'),'name'=>'remove_cover_photo');
         }
        $albumData['album']['cover_image_options'] = $coverPhotoOptions;
      }

    if($viewer->getIdentity() > 0){
			$albumData['album']['permission']['canEdit'] = $canEdit = $viewPermission = $album->authorization()->isAllowed($viewer, 'edit') ? true : false;
			$albumData['album']['permission']['canComment'] =  $album->authorization()->isAllowed($viewer, 'comment') ? true : false;
			$albumData['album']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'album', 'create') ? true : false;
			$albumData['album']['permission']['can_delete'] = $canDelete  = $album->authorization()->isAllowed($viewer,'delete') ? true : false;

      $menuoptions= array();
      $counter = 0;
      if($canEdit){
        $menuoptions[$counter]['name'] = "addmorephotos";
        $menuoptions[$counter]['label'] = $this->view->translate("Add More Photos");
        $counter++;
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit Settings");
        $counter++;
      }
      if($canDelete){
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete Album");
        $counter++;
      }
      $menuoptions[$counter]['name'] = "report";
      $menuoptions[$counter]['label'] = $this->view->translate("Report Album");
      $albumData['menus'] = $menuoptions;
		}
    $albumData['album']['user_title'] = $album->getOwner()->getTitle();
    $owner = $album->getOwner();
    if($owner && $owner->photo_id){
        $photo= $this->getBaseUrl(false,$owner->getPhotoUrl());
        $albumData['album']['user_image']  = $photo;
      }else
        $albumData['album']['user_image'] =  $this->getBaseUrl(true,'/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
    if($this->view->viewer()->getIdentity() != 0){
      $albumData['album']['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($album);
      $albumData['album']['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($album);
      $canFavourite =  Engine_Api::_()->authorization()->isAllowed('album',Engine_Api::_()->user()->getViewer(), 'favourite_album');
      if($canFavourite && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.allowfavourite', 1)){
        $albumData['album']['can_favorite'] = true;
        $albumData['album']['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($album,'favourites','sesalbum','album');
        $albumData['album']['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($album,'favourites','sesalbum','album');
      }else
        $albumData['album']['can_favorite'] = false;
    }
    if (!$viewer || !$viewer->getIdentity() || !$album->isOwner($viewer)) {
      $album->view_count = new Zend_Db_Expr('view_count + 1');
      $album->save();
    }
    $albumData['album']["share"]["name"] = "share";
    $albumData['album']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$album->getPhotoUrl());
    if($photo)
    $albumData['album']["share"]["imageUrl"] = $photo;
		$albumData['album']["share"]["url"] = $this->getBaseUrl(false,$album->getHref());
    $albumData['album']["share"]["title"] = $album->getTitle();
    $albumData['album']["share"]["description"] = strip_tags($album->getDescription());
    $albumData['album']["share"]['urlParams'] = array(
        "type" => $album->getType(),
        "id" => $album->getIdentity()
    );
    if(is_null($albumData['album']["share"]["title"]))
      unset($albumData['album']["share"]["title"]);

    if(isset($album->art_cover) && $album->art_cover){
      $albumData['album']['cover_pic'] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($album->art_cover, '')->getPhotoUrl());
    }else if(isset($album->art_cover) && !$album->art_cover){
      $albumImage = Engine_Api::_()->sesalbum()->getAlbumPhoto($album->getIdentity(),0,2);
      foreach( $albumImage as $photo ){
         $coverimage[] = $this->getBaseUrl(false,$photo->getPhotoUrl('thumb.normalmain','','notcheck'));
      }
      $albumData['album']['cover_pic'] = $coverimage;
    }

    $albumData['album']['albumTags'] = $album->tags()->getTagMaps()->toArray();
    $albumData['album']['canDownload'] = Engine_Api::_()->authorization()->isAllowed('album',$viewer, 'download');
    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    $paginator = $photoTable->getPhotoPaginator(array(
        'album' => $album,
    ));
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));

    $albumData['photos'] = $this->getPhotos($paginator);

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    //echo "<pre>";var_dump($albumData);die;
    if($albumData['photos'] <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('No photo created in this album yet.'), 'result' => array()));
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $albumData),$extraParams));
    }

  }
  public function getPhotos($paginator,$updateViewCount = false){
      $result = array();
    $counter = 0;
    $canFavourite =  Engine_Api::_()->authorization()->isAllowed('album',Engine_Api::_()->user()->getViewer(), 'favourite_photo');
    foreach($paginator as $photos){
        $photo = $photos->toArray();
        $photos->view_count = new Zend_Db_Expr('view_count + 1');
        $photos->save();
        $photo['user_title'] = $photos->getOwner()->getTitle();
        if($this->view->viewer()->getIdentity() != 0){
          $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
          $photo['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($photos);
          if($canFavourite && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.allow.favouritephoto', 1)){
            $photo['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($photos,'favourites','sesalbum','album_photo');
            $photo['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($photos,'favourites','sesalbum','album_photo');
          }
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

        $photo['can_comment'] = $photos->getParent()->authorization()->isAllowed($this->view->viewer(), 'comment') ? true : false;
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

            $select->where('resource_type =?',$photos->getType());
            $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
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
            // Get tags
            $tags = array();
            foreach ($photos->tags()->getTagMaps() as $tagmap) {
              $tags[] = array_merge($tagmap->toArray(), array(
                  'id' => $tagmap->getIdentity(),
                  'text' => $tagmap->getTitle(),
                  'href' => $tagmap->getHref(),
                  'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
              ));
            }
            $photo["tags"] = $tags;
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
    return $result;
  }
  //function for autosuggest album
  public function getAlbumAction() {
    $sesdata = array();
    $value['text'] = $this->_getParam('text');
    $albums = Engine_Api::_()->getDbTable('albums', 'sesalbum')->getAlbumsAction($value);
    foreach ($albums as $album) {
      $album_icon_photo = $this->view->itemPhoto($album, 'thumb.icon');
      $sesdata[] = array(
          'id' => $album->album_id,
          'label' => $album->title,
          'photo' => $album_icon_photo
      );
    }
    return $this->_helper->json($sesdata);
  }

  //album edit action
  public function editAction() {
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if (!$this->_helper->requireSubject('album')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    // Prepare data
    $this->view->album = $album = Engine_Api::_()->core()->getSubject();
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesalbum')->profileFieldId();


    // Make form
    $this->view->form = $form = new Sesalbum_Form_Album_Edit(array('defaultProfileId' => $defaultProfileId));
		$form->removeElement('lat');
    $form->removeElement('map-canvas');
    $form->removeElement('ses_location');
    $form->removeElement('lng');
    $tagStr = '';
    foreach ($album->tags()->getTagMaps() as $tagMap) {
      $tag = $tagMap->getTag();
      if (!isset($tag->text))
        continue;
      if ('' !== $tagStr)
        $tagStr .= ', ';
      $tagStr .= $tag->text;
    }
    $form->populate(array(
        'tags' => $tagStr,
    ));
     // Check if post and populate
    if($this->_getParam('getForm')) {
      $form->populate($album->toArray());
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      foreach ($roles as $role) {
        if (1 === $auth->isAllowed($album, $role, 'view') && isset($form->auth_view)) {
          $form->auth_view->setValue($role);
        }
        if (1 === $auth->isAllowed($album, $role, 'comment') && isset($form->auth_comment)) {
          $form->auth_comment->setValue($role);
        }
        if (1 === $auth->isAllowed($album, $role, 'tag') && isset($form->auth_tag)) {
          $form->auth_tag->setValue($role);
        }
      }
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory
      $newFormFieldsArray = array();
      if(count($formFields) && $album->category_id){
          foreach($formFields as $fields){
            foreach($fields as $field){
                $subcat = array();
                if($fields['name'] == "subcat_id"){
                  $subcat = Engine_Api::_()->getItemTable('sesalbum_category')->getModuleSubcategory(array('category_id'=>$album->category_id,'column_name'=>'*'));
                }else if($fields['name'] == "subsubcat_id"){
                  if($album->subcat_id)
                  $subcat = Engine_Api::_()->getItemTable('sesalbum_category')->getModuleSubSubcategory(array('category_id'=>$album->subcat_id,'column_name'=>'*'));
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
      }
      if(!count($newFormFieldsArray))
        $newFormFieldsArray = $formFields;
      $this->generateFormFields($newFormFieldsArray);
    }
    if(!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    // Process
    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      $album->setFromArray($values);
      $album->save();

      if(!empty($_POST['location']) && $_POST['location'] != $album->location){
        $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
        if($latlng){
          $_POST['lat'] = $latlng['lat'];
          $_POST['lng'] = $latlng['lng'];
        }
      }

      //save lat lng for location in sesbasic location table.
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('album_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesalbum_album")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      // Add fields
      $customfieldform = $form->getSubForm('fields');
			if($customfieldform){
      	$customfieldform->setItem($album);
      	$customfieldform->saveValues();
			}
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $album->tags()->setTagMaps($viewer, $tags);
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
      if (empty($values['auth_tag'])) {
        $values['auth_tag'] = key($form->auth_tag->options);
        if (empty($values['auth_tag'])) {
          $values['auth_tag'] = 'owner_member';
        }
      }
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $tagMax = array_search($values['auth_tag'], $roles);
      //set roles
      foreach ($roles as $i => $role) {
        $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($album, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($album, $role, 'tag', ($i <= $tagMax));
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($album) as $action) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('album_id'=>$album->getIdentity(),'message'=>$this->view->translate('Album Edit successfully.'))));
  }

  // album delete action
  public function deleteAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $album = Engine_Api::_()->getItem('album', $this->getRequest()->getParam('album_id'));
    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));
    // In smoothbox
    $this->view->form = $form = new Sesalbum_Form_Album_Delete();

    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));
    }
    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $album->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected albums have been successfully deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
  }

  // function for edit photo action
  public function editphotosAction() {
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $pageNumber = isset($_POST['page']) ? $_POST['page'] : 1;
    if (!$is_ajax) {
      if (!$this->_helper->requireUser()->isValid())
        return;
      if (!$this->_helper->requireSubject('album')->isValid())
        return;
      if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
        return;
    }
    if (!$is_ajax) {
      // Get navigation
      $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
              ->getNavigation('sesalbum_main');
      // Hack navigation
      foreach ($navigation->getPages() as $page) {
        if ($page->route != 'sesalbum_general' || $page->action != 'manage')
          continue;
        $page->active = true;
      }
    }
    // Prepare data
    $this->view->album = $album = Engine_Api::_()->core()->getSubject();
    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    $this->view->paginator = $paginator = $photoTable->getPhotoPaginator(array(
        'album' => $album,
        'order' => 'order ASC'
    ));
    $this->view->album_id = $album->album_id;
    $paginator->setCurrentPageNumber($pageNumber);
    $itemCount = (count($_POST) > 0 && !$is_ajax) ? count($_POST) : 10;
    $paginator->setItemCountPerPage($itemCount);
    $this->view->page = $pageNumber;
    // Get albums
    $myAlbums = Engine_Api::_()->getDbtable('albums', 'sesalbum')->editPhotos();
    $albumOptions = array('' => '');
    foreach ($myAlbums as $myAlbum) {
      $albumOptions[$myAlbum['album_id']] = $myAlbum['title'];
    }
    if (count($albumOptions) == 1) {
      $albumOptions = array();
    }
    // Make form
    $this->view->form = $form = new Sesalbum_Form_Album_Photos();
    foreach ($paginator as $photo) {
      $subform = new Sesalbum_Form_Album_EditPhoto(array('elementsBelongTo' => $photo->getGuid()));
      $subform->populate($photo->toArray());
      $form->addSubForm($subform, $photo->getGuid());
      $form->cover->addMultiOption($photo->getIdentity(), $photo->getIdentity());
      if (empty($albumOptions)) {
        $subform->removeElement('move');
      } else {
        $subform->move->setMultiOptions($albumOptions);
      }
    }
    if ($is_ajax) {
      return;
    }
    if (!$this->getRequest()->isPost()) {
      return;
    }
    $table = $album->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $values = $_POST;
      if (!empty($values['cover'])) {
        $album->photo_id = $values['cover'];
        $album->save();
      }
      // Process
      foreach ($paginator as $photo) {
        if (isset($_POST[$photo->getGuid()])) {
          $values = $_POST[$photo->getGuid()];
        } else {
          continue;
        }
        unset($values['photo_id']);
        if (isset($values['delete']) && $values['delete'] == '1') {
          $photo->delete();
        } else if (!empty($values['move'])) {
          $nextPhoto = $photo->getNextPhoto();
          $old_album_id = $photo->album_id;
          $photo->album_id = $values['move'];
          $photo->save();
          // Change album cover if necessary
          if (($nextPhoto instanceof Sesalbum_Model_Photo) &&
                  (int) $album->photo_id == (int) $photo->getIdentity()) {
            $album->photo_id = $nextPhoto->getIdentity();
            $album->save();
          }
          // Remove activity attachments for this photo
          Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($photo);
        } else {
          $photo->setFromArray($values);
          $photo->save();
        }
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    //send to specific album view page.
    return $this->_helper->redirector->gotoRoute(array('action' => 'view', 'album_id' => $album->album_id), 'sesalbum_specific', true);
  }

  public function uploadsAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
      $album_id = $this->_getParam('album_id','');
      $album = Engine_Api::_()->getItem('album',$album_id);
      if(!$album || !$album->authorization()->isAllowed($viewer, 'edit'))
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));

       ini_set("memory_limit","240M");

       if(!empty($_FILES["attachmentImage"]) && count($_FILES["attachmentImage"]) > 0){
           // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('album');
          $type = 'wall';
          $photoTable = Engine_Api::_()->getItemTable('photo');
          $auth = Engine_Api::_()->authorization()->context;
          try{
            if(count($_FILES['attachmentImage']['name'])){
              $api = Engine_Api::_()->getDbtable('actions', 'activity');
              $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'album_photo_new', null, array('count' =>  count($_FILES['attachmentImage']['name'])));
            }
           $counter = 0;
           foreach($_FILES['attachmentImage']['name'] as $image){
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
              $photo->order = $photo->photo_id;
              $photo->album_id = $album->album_id;
              $photo->save();
              if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
              }
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
