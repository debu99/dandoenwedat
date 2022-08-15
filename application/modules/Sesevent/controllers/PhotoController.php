<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: PhotoController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_PhotoController extends Core_Controller_Action_Standard {

  public function init() {
    if (!Engine_Api::_()->core()->hasSubject()) {
      if (0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
              null !== ($photo = Engine_Api::_()->getItem('sesevent_photo', $photo_id))) {
        Engine_Api::_()->core()->setSubject($photo);
      } else if (0 !== ($event_id = (int) $this->_getParam('event_id')) &&
              null !== ($event = Engine_Api::_()->getItem('sesevent_event', $event_id))) {
        Engine_Api::_()->core()->setSubject($event);
      }
    }

    $this->_helper->requireUser->addActionRequires(array(
        'upload',
        'upload-photo', // Not sure if this is the right
        'edit',
    ));

    $this->_helper->requireSubject->setActionRequireTypes(array(
        'list' => 'sesevent_event',
        'upload' => 'sesevent_event',
        'view' => 'sesevent_photo',
        'edit' => 'sesevent_photo',
    ));
  }

  public function listAction() {}
	//get images as per album id (advance lightbox)
	public function correspondingImageAction(){
		$album_id = $this->_getParam('album_id', false);
		$this->view->paginator = $paginator = Engine_Api::_()->getDbtable('photos', 'sesevent')->getPhotoSelect(array('album_id'=>$album_id,'limit_data'=>100));
	}
	//rotate photo action from lightbox and photo view page
  public function rotateAction() {
    if (!$this->_helper->requireSubject('sesevent_photo')->isValid())
      return;
		$event_id = $this->_getParam('event_id');
		$event = Engine_Api::_()->getItem('sesevent_event', $event_id);
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Invalid method');
      return;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('sesevent_photo');
    $angle = (int) $this->_getParam('angle', 90);
    if (!$angle || !($angle % 360)) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Invalid angle, must not be empty');
      return;
    }
    if (!in_array((int) $angle, array(90, 270))) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Invalid angle, must be 90 or 270');
      return;
    }
    // Get file
    $file = Engine_Api::_()->getItem('storage_file', $photo->file_id);
    if (!($file instanceof Storage_Model_File)) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Could not retrieve file');
      return;
    }
    // Pull photo to a temporary file
    $tmpFile = $file->temporary();
    // Operate on the file
    $image = Engine_Image::factory();
    $image->open($tmpFile)
            ->rotate($angle)
            ->write()
            ->destroy()
    ;
    // Set the photo
    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $photo->setPhoto($tmpFile);
      @unlink($tmpFile);
      $db->commit();
    } catch (Exception $e) {
      @unlink($tmpFile);
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->href = $photo->getPhotoUrl();
  }
	//flip photo action function 
  public function flipAction() {
   if (!$this->_helper->requireSubject('sesevent_photo')->isValid())
      return;
		$event_id = $this->_getParam('event_id');
		$event = Engine_Api::_()->getItem('sesevent_event', $event_id);
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Invalid method');
      return;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('sesevent_photo');
    $direction = $this->_getParam('direction');
    if (!in_array($direction, array('vertical', 'horizontal'))) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Invalid direction');
      return;
    }
    // Get file
    $file = Engine_Api::_()->getItem('storage_file', $photo->file_id);
    if (!($file instanceof Storage_Model_File)) {
      $this->view->status = false;
      $this->view->error = $this->view->translate('Could not retrieve file');
      return;
    }
    // Pull photo to a temporary file
    $tmpFile = $file->temporary();
    // Operate on the file
    $image = Engine_Image::factory();
    $image->open($tmpFile)
            ->flip($direction != 'vertical')
            ->write()
            ->destroy()
    ;
    // Set the photo
    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $photo->setPhoto($tmpFile,false,'flip');
      @unlink($tmpFile);
      $db->commit();
    } catch (Exception $e) {
      @unlink($tmpFile);
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->href = $photo->getPhotoUrl();
  }
  public function viewAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->photo = $photo = Engine_Api::_()->core()->getSubject();
		$event_id = $this->_getParam('event_id');
		$event = Engine_Api::_()->getItem('sesevent_event', $event_id);
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'view')->isValid()) {
      return;
    }

    if (!$viewer || !$viewer->getIdentity() || $photo->user_id != $viewer->getIdentity()) {
      $photo->view_count = new Zend_Db_Expr('view_count + 1');
      $photo->save();
    }
		// Render
    $this->_helper->content
            ->setEnabled();
  }


  public function deleteAction() {
    $photo = Engine_Api::_()->core()->getSubject();
    $event = $photo->getParent('sesevent_event');
		$album_id = $photo->album_id;
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid()) {
      return;
    }

    $this->view->form = $form = new Sesevent_Form_Photo_Delete();

    if (!$this->getRequest()->isPost()) {
      $form->populate($photo->toArray());
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $db = Engine_Api::_()->getDbtable('photos', 'sesevent')->getAdapter();
    $db->beginTransaction();

    try {
      $photo->delete();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
		$album = $photo = Engine_Api::_()->getItem('sesevent_album', $album_id);
    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Photo deleted')),
                'layout' => 'default-simple',
                'parentRedirect' => $album->getHref(),
                'closeSmoothbox' => true,
    ));
  }

}
