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
class Album_IndexController extends Sesapi_Controller_Action_Standard {

  public function browseAction() {

    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("You don't have permission to access this resource"), 'result' => array()));
    }

    $settings = Engine_Api::_()->getApi('settings', 'core');

    // Get params
    switch($this->_getParam('sort', 'recent')) {
      case 'popular':
        $order = 'view_count';
        break;
      case 'recent':
      default:
        $order = 'modified_date';
        break;
    }

    $userId = $this->_getParam('user');
    $this->view->excludedLevels = $excludedLevels = array(1, 2, 3);   // level_id of Superadmin,Admin & Moderator
    $registeredPrivacy = array('everyone', 'registered');
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() && !in_array($viewer->level_id, $excludedLevels) && empty($userId) ) {
      $viewerId = $viewer->getIdentity();
      $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $this->view->viewerNetwork = $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
      if( !empty($viewerNetwork) ) {
        array_push($registeredPrivacy,'owner_network');
      }

      $friendsIds = $viewer->membership()->getMembersIds();
      $friendsOfFriendsIds = $friendsIds;
      foreach( $friendsIds as $friendId ) {
        $friend = Engine_Api::_()->getItem('user', $friendId);
        $friendMembersIds = $friend->membership()->getMembersIds();
        $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
      }
    }

    // Prepare data
    $table = Engine_Api::_()->getItemTable('album');
    if( !in_array($order, $table->info('cols')) ) {
      $order = 'modified_date';
    }

    $select = $table->select();

    if( !$viewer->getIdentity() ) {
      $select->where("view_privacy = ?", 'everyone');
    } elseif( $userId ) {
      $owner = Engine_Api::_()->getItem('user', $userId);
      if( $owner ) {
        $select = $table->getAlbumSelect(array('owner' => $owner));
      }
    } elseif( !in_array($viewer->level_id, $excludedLevels) ) {
      $select->Where("owner_id = ?", $viewerId)
        ->orwhere("view_privacy IN (?)", $registeredPrivacy);
      if( !empty($friendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member' AND owner_id IN (?)", $friendsIds);
      }
      if( !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member_member' AND owner_id IN (?)", $friendsOfFriendsIds);
      }
      if( empty($viewerNetwork) && !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_network' AND owner_id IN (?)", $friendsOfFriendsIds);
      }

      $subquery = $select->getPart(Zend_Db_Select::WHERE);
      $select ->reset(Zend_Db_Select::WHERE);
      $select ->where(implode(' ',$subquery));
    }

    $select->where("search = 1")
      ->order($order . ' DESC');
    if ($this->_getParam('category_id')) $select->where("category_id = ?", $this->_getParam('category_id'));

    if ($this->_getParam('search', false)) {
      $select->where('title LIKE ? OR description LIKE ?', '%'.$this->_getParam('search').'%');
    }

    $canCreate = Engine_Api::_()->authorization()->isAllowed('album', null, 'create');
    $paginator = $this->view->paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($settings->getSetting('album_page', 28));
    $paginator->setCurrentPageNumber( $this->_getParam('page') );

    $result = $this->getAlbums($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('Does not exist member.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

    $searchForm = new Album_Form_Search();
    $searchForm->getElement('sort')->setValue($this->_getParam('sort'));
    $searchForm->getElement('search')->setValue($this->_getParam('search'));
    $category_id = $searchForm->getElement('category_id');
    if ($category_id) {
      $category_id->setValue($this->_getParam('category_id'));
    }
    //$this->view->searchParams = $searchForm->getValues();
  }
    public function browsePhotosAction() {
        return $this->_forward('browse-photo', null, null, array('format' => 'json'));
    }
  public function browsePhotoAction() {

    if(!$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
    }

    $this->view->canCreate = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->checkRequire();
    $this->view->form = $form = new Album_Form_Photo_Search();
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    } else {
      $values = array();
    }
    $this->view->formValues = array_filter($values);

    if (!empty($values['tag'])) {
      $this->view->tag = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
    }

    if (!empty($params['search'])) {
      $this->view->search = $params['search'];
    }

    switch($this->_getParam('sort', 'recent')) {
      case 'popular':
        $order = 'view_count';
        break;
      case 'recent':
      default:
        $order = 'modified_date';
        break;
    }
    $excludedLevels = array(1, 2, 3);   // level_id of Superadmin,Admin & Moderator
    $registeredPrivacy = array('everyone', 'registered');
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() && !in_array($viewer->level_id, $excludedLevels) ) {
      $viewerId = $viewer->getIdentity();
      $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $this->view->viewerNetwork = $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
      if( !empty($viewerNetwork) ) {
        array_push($registeredPrivacy,'owner_network');
      }

      $friendsIds = $viewer->membership()->getMembersIds();
      $friendsOfFriendsIds = $friendsIds;
      foreach( $friendsIds as $friendId ) {
        $friend = Engine_Api::_()->getItem('user', $friendId);
        $friendMembersIds = $friend->membership()->getMembersIds();
        $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
      }
    }

    // Prepare data
    $albumTable = Engine_Api::_()->getItemTable('album');
    $select = $albumTable->select()->from($albumTable->info('name'), 'album_id');

    if( !$viewer->getIdentity() ) {
      $select->where("view_privacy = ?", 'everyone');
    } elseif( !in_array($viewer->level_id, $excludedLevels) ) {
      $select->Where("owner_id = ?", $viewerId)
        ->orwhere("view_privacy IN (?)", $registeredPrivacy);
      if( !empty($friendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member' AND owner_id IN (?)", $friendsIds);
      }
      if( !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member_member' AND owner_id IN (?)", $friendsOfFriendsIds);
      }
      if( empty($viewerNetwork) && !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_network' AND owner_id IN (?)", $friendsOfFriendsIds);
      }

      $subquery = $select->getPart(Zend_Db_Select::WHERE);
      $select ->reset(Zend_Db_Select::WHERE);
      $select ->where(implode(' ',$subquery));
    }

    $select->where("search = 1");
    $albums = $albumTable->fetchAll($select);
    $albumIds = array();
    foreach ($albums as $album) {
      $albumIds[] = $album->album_id;
    }

    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    $this->view->paginator = $paginator = $photoTable->getPhotoPaginator(array_merge(
      ['album_ids' => $albumIds, 'order' => $order],
      $values
    ));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));

    $counterPhoto = 0;
    foreach($paginator as $photos) {

      if($photos) {
        $image = $photos->getPhotoUrl();
        if(!$image) continue;
        $album_photo[$counterPhoto]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id,'','',true);
        $album_photo[$counterPhoto]['photo_id'] = $photos['photo_id'];
        $album_photo[$counterPhoto]['album_id'] = $photos['album_id'];
        $counterPhoto++;
      }
    }
    if($counterPhoto > 0) {
      $result['photos'] = $album_photo;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    $result['can_create'] = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ? true : false;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => array('error'=>$this->view->translate('Nobody has uploaded any photo yet.'))),$extraParams));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }


  public function manageAction() {

    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();

//     $search_form = new Album_Form_Search();
//     if ($this->getRequest()->isPost() && $search_form->isValid($this->getRequest()->getPost())) {
//       $this->_helper->redirector->gotoRouteAndExit(array(
//         'page'   => 1,
//         'sort'   => $this->getRequest()->getPost('sort'),
//         'search' => $this->getRequest()->getPost('search'),
//         'category_id' => $this->getRequest()->getPost('category_id'),
//       ));
//     } else {
//       $search_form->getElement('search')->setValue($this->_getParam('search'));
//       $search_form->getElement('sort')->setValue($this->_getParam('sort'));
//       if($search_form->getElement('category_id')) $search_form->getElement('category_id')->setValue($this->_getParam('category_id'));
//     }
//
//     // Render
//     $this->_helper->content
//         //->setNoRender()
//         ->setEnabled()
//         ;

    // Get params
    $page = $this->_getParam('page');

    // Get params
    switch($this->_getParam('sort', 'recent')) {
      case 'popular':
        $order = 'view_count';
        break;
      case 'recent':
      default:
        $order = 'modified_date';
        break;
    }

    // Prepare data
    $user = Engine_Api::_()->user()->getViewer();
    $table = Engine_Api::_()->getItemTable('album');

    if( !in_array($order, $table->info('cols')) ) {
      $order = 'modified_date';
    }

    $select = $table->select()
      ->where('owner_id = ?', $user->getIdentity())
      ->order($order . ' DESC');;

    if ($this->_getParam('category_id')) $select->where("category_id = ?", $this->_getParam('category_id'));

    if ($this->_getParam('search', false)) {
      $select->where('title LIKE ? OR description LIKE ?', '%'.$this->_getParam('search').'%');
    }

    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($page);
    $result = $this->getAlbums($paginator,true);

    $menuoptions= array();
    $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'album', 'edit');
    $counter = 0;
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


  public function categoriesAction() {

    $params['countAlbums'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'album')->getCategoriesAssoc();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {
      if($key == '') continue;
      $category = Engine_Api::_()->getItem('album_category', $key);
      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;
      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');

      //Albums Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $category->getIdentity(), 'table_name' => 'albums', 'module_name' => 'album'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s album', '%s albums', $Itemcount), $this->view->locale()->toNumber($Itemcount));

      $counter++;
    }
    $catgeoryArray['module_name'] = 'album';
    $catgeoryArray['can_create'] = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ? true : false;
    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array()));
  }


  public function createAction() {

    if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $album_id = $this->_getParam('album_id',false);

    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();

    // Get form
    $form = new Album_Form_Album();
    $form->removeElement('album');
    $form->addElement('File', 'file', array(
      'label' => 'Main Photo',
      'description' => '',
      'order' => '99999',
      'required'=> 1,
      'allowEmpty'=> false,
      'priority' => 99998,
    ));

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'album'));
    }

    // Check if valid
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }
    if (!isset($_FILES['image']['size']) || empty($_FILES['image']['size'])){ 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Main Photo: Please complete this field - it is required.'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('invalid_request'), 'result' => array()));
    }

    $db = Engine_Api::_()->getItemTable('album')->getAdapter();
    $db->beginTransaction();
    try {

      $album = $form->saveValues();

      if(!empty($_FILES['image']['size'])) {

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
        if( $action instanceof Activity_Model_Action && $count < 9) {
          $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
        }
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('album_id' => $album->getIdentity(), 'message'=>$this->view->translate('Album created successfully.'))));
  }


  //Photo Delete
  public function deleteAction() {

    $viewer = Engine_Api::_()->user()->getViewer();

    $photo = Engine_Api::_()->getItem('album_photo',$this->_getParam('photo_id',''));
    $photo_id = $photo->getIdentity();
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

  // Remove tag
  public function removeTagAction() {

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
    if( $viewer->getGuid() != $tagmap->tagger_type . '_' . $tagmap->tagger_id && $viewer->getGuid() != $tagmap->tag_type . '_' . $tagmap->tag_id && !$subject->isOwner($viewer) ) {
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

  function getTaggedUserAction() {

    $photo_id = $this->_getParam('photo_id','');

    if(!$photo_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));

    $subject = Engine_Api::_()->getItem('album_photo',$photo_id);
    $album = Engine_Api::_()->getItem('album',$subject->album_id);

    // Get tags
    $tags = array();
    foreach ($subject->tags()->getTagMaps() as $tagmap) {

      $owner = Engine_Api::_()->getItem('user',$tagmap->tag_id);
      if($owner && $owner->photo_id) {
        $photo= $this->getBaseUrl(false,$owner->getPhotoUrl());
      } else
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

  function addTagAction() {

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
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $tag, 'tagged', '', array('label' => 'photo'));      if ($action)
        $action->attach($subject);

      // Add notification
      $type_name = $this->view->translate(str_replace('_', ' ','photo'));
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
        $tag, $viewer, $action, 'tagged', array(
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

	// Edit photo description
  function editDescriptionAction() {

    $photo = Engine_Api::_()->getItem('album_photo',$this->_getParam('photo_id',''));
    $photo_id = $photo->getIdentity();
    $description = $this->_getParam('description','');
    $photo->description = $description;
    $photo->save();
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Photo Description Updated successfully.")));
  }

  // Get all albums
  public function getAlbumsAction() {

    $user = Engine_Api::_()->user()->getViewer();
    $table = Engine_Api::_()->getItemTable('album');
    $select = $table->select()->from($table)->where('owner_id =?',$user->getIdentity())->order('creation_date DESC');
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber( $this->_getParam('page'));
    $result = $this->getAlbums($paginator);
  }


  public function getAlbums($paginator,$return = false) {

    $result = array();
    $counter = 0;

    foreach($paginator as $albums) {

      $album = $albums->toArray();
      $album['photo_count'] = $albums->count();
      $album['user_title'] = $albums->getOwner()->getTitle();
      if($this->view->viewer()->getIdentity() != 0){
        $album['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($albums);
        $album['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($albums);
      }
      $photo = Engine_Api::_()->getItem('photo',$album["photo_id"]);
      if($photo)
        $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo,'',"");
      else
        continue;

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
    $results['module_name'] = 'album';
    $results['can_create'] = $this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ? true : false;

    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No album created by you yet.'), 'result' => array()));
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $results),$extraParams));
    }
  }

  public function searchFormAction() {

    $filterOptions = (array)$this->_getParam('search_type', array('recent' => 'Most Recent','popular' => 'Most Popular'));
    $search_for = $this-> _getParam('search_for', 'album');

    $default_search_type = $this->_getParam('default_search_type', 'recentlySPcreated');

    $form = new Album_Form_Search();

    if(count($filterOptions)) {
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

    if($form->find){
      $form->removeElement('find');
      $form->addElement('Button', 'find', array(
        'type' => 'submit',
        'label' => 'Search',
        'ignore' => true,
        'order' => 10000001,
      ));
    }
    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
    $this->generateFormFields($formFields);
  }
}
