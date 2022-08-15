<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: PhotoController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesalbum_PhotoController extends Sesapi_Controller_Action_Standard {
	//photo constructor function
  public function init() {
		if (0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
              null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id))) {
        Engine_Api::_()->core()->setSubject($photo);
    }
   if (strpos($_SERVER['REQUEST_URI'], 'get-photos') === false && strpos($_SERVER['REQUEST_URI'], 'o/like') === false) {
      if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));     
    }
  }
  
  function getPhotosAction(){
    $album_id = $this->_getParam('album_id',0);
    if(!$album_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array())); 
    $table = Engine_Api::_()->getItemTable('photo');
    $select = $table->select()->from($table)->where('album_id =?',$album_id);
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('limit', 20));
    $paginator->setCurrentPageNumber( $this->_getParam('page'));
    $result = $this->getPhotos($paginator);
      
  }
  public function getPhotos($paginator){
    $result = array();
    $counter = 0;
    foreach($paginator as $photos){
        $photo = $photos->toArray();
        if($photo)
          $album_photo['photos'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos,'',"");
        else
          continue;
        $result[$counter] = array_merge($photo,$album_photo);
        $counter++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    $results['photos'] = $result;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No photo created by you yet in this album.'), 'result' => array())); 
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $results),$extraParams));
    }
  }
	
	//photo view function
  public function viewAction() {
		if(!Engine_Api::_()->core()->hasSubject()){
		 $album_id = 	$this->getRequest()->getParam('album_id');
		 $url = Engine_Api::_()->sesalbum()->getHref($album_id);
		 header('location:'.$url);
		 die;
		}
    if (!$this->_helper->requireSubject('album_photo')->isValid())
      return;
	if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.checkalbum'))
      return $this->_forward('notfound', 'error', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->photo = $photo = Engine_Api::_()->core()->getSubject();
    $this->view->album = $album = $photo->getAlbum();
    if (!$viewer || !$viewer->getIdentity() || !$album->isOwner($viewer)) {
      $photo->view_count = new Zend_Db_Expr('view_count + 1');
      $photo->save();
    }
    /* Insert data for recently viewed widget */
    if ($viewer->getIdentity() != 0 && isset($photo->photo_id)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesalbum_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $photo->photo_id . '", "album_photo","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }
    // if this is sending a message id, the user is being directed from a coversation
    // check if member is part of the conversation
    $message_id = $this->getRequest()->getParam('message');
    $message_view = false;
    if ($message_id) {
      $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
      if ($conversation->hasRecipient(Engine_Api::_()->user()->getViewer()))
        $message_view = true;
    }
    $this->view->message_view = $message_view;
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid())
      return;
    if (!$message_view && !$this->_helper->requireAuth()->setAuthParams($photo, null, 'view')->isValid())
      return;
    $checkAlbum = Engine_Api::_()->getItem('album', $this->_getParam('album_id'));
    if (!($checkAlbum instanceof Core_Model_Item_Abstract) || !$checkAlbum->getIdentity() || $checkAlbum->album_id != $photo->album_id) {
      $this->_forward('requiresubject', 'error', 'core');
      return;
    }
		
    // Render
		if((Engine_Api::_()->getApi('core', 'sesbasic')->checkAdultContent(array('module'=>'sesalbum')) && $checkAlbum->adult) || $checkAlbum->owner_id == $viewer->getIdentity()) {
    	$this->_helper->content->setEnabled();
		} elseif($checkAlbum->adult) {
      $this->view->adultContent = true;
    } else {
			$this->_helper->content->setEnabled();
    }
  }
	//photo delete function
  public function deleteAction() {
    if (!$this->_helper->requireSubject('album_photo')->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid())
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('album_photo');
    $album = $photo->getParent();
    $this->view->form = $form = new Sesalbum_Form_Photo_Delete();
    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      // delete files from server
      $filesDB = Engine_Api::_()->getDbtable('files', 'storage');
      $filePath = $filesDB->fetchRow($filesDB->select()->where('file_id = ?', $photo->file_id))->storage_path;
      unlink($filePath);
      $thumbPath = $filesDB->fetchRow($filesDB->select()->where('parent_file_id = ?', $photo->file_id))->storage_path;
      unlink($thumbPath);
      // Delete image and thumbnail
      $filesDB->delete(array('file_id = ?' => $photo->file_id));
      $filesDB->delete(array('parent_file_id = ?' => $photo->file_id));
      // Check activity actions
      $attachDB = Engine_Api::_()->getDbtable('attachments', 'activity');
      $actions = $attachDB->fetchAll($attachDB->select()->where('type = ?', 'album_photo')->where('id = ?', $photo->photo_id));
      $actionsDB = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actions as $action) {
        $action_id = $action->action_id;
        $attachDB->delete(array('type = ?' => 'album_photo', 'id = ?' => $photo->photo_id));
        $action = $actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
        $count = $action->params['count'];
        if (!is_null($count) && ($count > 1)) {
          $action->params = array('count' => (integer) $count - 1);
          $action->save();
        } else {
          $action->delete();
        }
      }
      $photo->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    // get album_id 
    $ablum_id = (int) $this->_getParam('album_id', '0');
    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Photo deleted successfully.')),
                'layout' => 'default-simple',
                'parentRedirect' => Engine_Api::_()->sesalbum()->getHref($ablum_id),
    ));
  }
	//edit photo details from lightbox
  public function editDescriptionAction() {
    $status = true;
    $error = false;
    if (!$this->_helper->requireSubject('album_photo')->isValid()) {
      
    }
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid()) {
      $status = false;
      $error = true;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('album_photo');
    if ($status && !$error) {
      $values['title'] = $_POST['title'];
      $values['description'] = $_POST['description'];
      $values['location'] = $_POST['location'];
			//update location data in sesbasic location table
      if ($_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $_POST['photo_id'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesalbum_photo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
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
    }
    echo json_encode(array('status' => $status, 'error' => $error));die;
  }
}