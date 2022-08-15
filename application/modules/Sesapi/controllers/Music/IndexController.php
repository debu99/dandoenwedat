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
class Music_IndexController extends Sesapi_Controller_Action_Standard {
  
  public function init()
  {
    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    }

    // Get viewer info
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id  = Engine_Api::_()->user()->getViewer()->getIdentity();
/*    
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$moduleName = $request->getModuleName();
		$actionName = $request->getActionName();
		$controllerName = $request->getControllerName();
		
		echo $moduleName . $controllerName . $actionName;die;*/
  }

  public function searchFormAction(){
    $viewer = Engine_Api::_()->user()->getViewer();    
    $formFilter = new Music_Form_Search();
    $formFilter->sort->setValue('recent');
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($formFilter,true);
    $this->generateFormFields($formFields); 
  }
  
  public function editAction() {

//     if(!Engine_Api::_()->core()->hasSubject()){
//        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
//     }
    
    $playlist_id = $this->_getParam('playlist_id', null);
    $playlist = Engine_Api::_()->getItem('music_playlist', $playlist_id);
    
    //Only members can upload music
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams($playlist, null, 'edit')->isValid())
				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => array()));

    //Get form
    $this->view->form = $form = new Music_Form_Edit(array('fromApi'=>true));
    $form->populate($playlist->toArray());
    $form->removeElement('file');
    $form->removeElement('playlist_id');
    $form->removeElement('cancel');

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

     $db = Engine_Api::_()->getDbTable('playlists', 'music')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();

      $playlist->title = $values['title'];
      $playlist->description = $values['description'];
      $playlist->search = $values['search'];

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
          $auth->setAllowed($playlist, $role, 'view', true);
          $prev_allow_view = true;
        } else
          $auth->setAllowed($playlist, $role, 'view', 0);

        //Allow comments
        if ($values['auth_comment'] == $role || $prev_allow_comment) {
          $auth->setAllowed($playlist, $role, 'comment', true);
          $prev_allow_comment = true;
        } else
          $auth->setAllowed($playlist, $role, 'comment', 0);
      }

      if (!empty($_FILES['art']['size']))
        $playlist->setPhoto($form->art);
        
      $playlist->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Playlist edited successfully.")));
  }
  
  public function createAction(){
  
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    //Get form
    $this->view->form = $form = new Music_Form_Create();
    $playlist_id = $this->_getParam('playlist_id', '0');

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
      
    //Process
    $db = Engine_Api::_()->getDbTable('playlists', 'music')->getAdapter();
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

        $form->file->setValue(implode(' ',$fancyId));
      }
      $album = $this->view->form->saveValues();
      $db->commit();
      //Count Songs according to album_id
      //$song_count = Engine_Api::_()->getDbTable('albumsongs', 'sesmusic')->songsCount($album->album_id);
      //$album->song_count = $song_count;
      //$album->save();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('playlist_id'=>$album->getIdentity(),'message'=>$this->view->translate('Playlist created successfully.'))));
  }
  
  function uploadSong($file){
    $db = Engine_Api::_()->getDbtable('playlists', 'music')->getAdapter();
    $db->beginTransaction();
    try {
      $song = Engine_Api::_()->getApi('core', 'music')->createSong($file);
      $this->view->status   = true;
      $this->view->song     = $song;
      $this->view->song_id  = $song->getIdentity();
      $this->view->song_url = $song->getHref();
      $db->commit();
      return $song;
    } catch (Music_Model_Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  
  public function browseAction() {

    // Get browse params
    $formFilter = new Music_Form_Search();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    } else {
      $values = array();
    }
    $formValues = array_filter($values);

    // Show
    $viewer = Engine_Api::_()->user()->getViewer();
    if( @$values['show'] == 2 && $viewer->getIdentity() ) {
      // Get an array of friend ids
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
      $values['searchBit'] = 1;
    }
    unset($values['show']);
    
    $type = $this->_getParam('type','');
    if ($type == "manage" && $viewer->getIdentity()){
      $values['user'] = $viewer->getIdentity();  
    } elseif ($type == "manage") {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    }

    // Get paginator
    $paginator = Engine_Api::_()->music()->getPlaylistPaginator($values);
    $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('music.playlistsperpage', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $result = $this->getPlaylists($paginator,$type);
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No music playlist created yet.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));  
  }
  
  function getPlaylists($paginator,$manage = "") {
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      
      foreach($paginator as $playlist){
        $album = $playlist->toArray();
        $description = strip_tags($playlist->getDescription());
        $description = preg_replace('/\s+/', ' ', $description);
        unset($album['description']);
        $album['user_title'] = Engine_Api::_()->getItem('user',$album['owner_id'])->getTitle();
        $album['description'] = $description;   
        $album['resource_type'] = $playlist->getType();
        if($manage){
            $viewer = Engine_Api::_()->user()->getViewer();
            $menuoptions= array();
            $canEdit = $this->_helper->requireAuth()->setAuthParams($playlist, null, 'edit')->isValid();
            $counterMenu = 0;
            if($canEdit){
              $menuoptions[$counterMenu]['name'] = "edit";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
              $counterMenu++;
            }
            $canDelete = $this->_helper->requireAuth()->setAuthParams($playlist, null, 'delete')->isValid();
            if($canDelete){
              $menuoptions[$counterMenu]['name'] = "delete";
              $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
            }
            $album['menus'] = $menuoptions;
        }
        if($viewer->getIdentity() != 0){
          $album['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($playlist);
          $album['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($playlist);
        }  
        
        $result['albums'][$counterLoop] = $album;
        if($playlist->photo_id)
          $images = Engine_Api::_()->sesapi()->getPhotoUrls($playlist,'','');
        else {
          $images = array('main' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'),'icon' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_thumb_icon.png'),'normal' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'),'profile' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'));          
        }
          
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$playlist->getPhotoUrl());
        $result['albums'][$counterLoop]['images'] = $images;
        $counterLoop++;  
      }
      return $result;
  }
  
  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $playlist = Engine_Api::_()->getItem('music_playlist', $this->getRequest()->getParam('playlist_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($playlist, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('permission_error'), 'result' => array()));

    if( !$playlist )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Playlist doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    $db = $playlist->getTable()->getAdapter();
    $db->beginTransaction();
    try
    {
      foreach( $playlist->getSongs() as $song ) {
        $song->deleteUnused();
      }
      $playlist->delete();
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected playlist has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
  }
}
