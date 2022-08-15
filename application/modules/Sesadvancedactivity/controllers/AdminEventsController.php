<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminEventsController.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_AdminEventsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedactivity_admin_main_events');
    $this->view->formFilter = $formFilter = new Sesadvancedactivity_Form_Admin_Filter();
    $values = $formFilter->getValues();
    if(!empty($_GET))
      $formFilter->populate($_GET);
    if ($this->getRequest()->isPost() && $formFilter->isValid($this->getRequest()->getPost())) {
      $values = $_POST;
        foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $event = Engine_Api::_()->getItem('sesadvancedactivity_event', $value);
          $event->delete();
        }
      }
        $this->_helper->redirector->gotoRoute(array());
    }
    $table = Engine_Api::_()->getDbtable('events', 'sesadvancedactivity');
    $select = $table->select()->order('creation_date DESC');
    $page = $this->_getParam('page', 1);
		if( !empty($_GET['title']) ) 
      $select->where('title LIKE ?', '%' . $_GET['title'] . '%');
    if( !empty($_GET['date']) ) 
      $select->where('date LIKE ?', '%' . $_GET['date'] . '%');
    if( !empty($_GET['active']) || $_GET['active'] == "0") 
      $select->where('active =?',$_GET['active']);
      
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber( $page );
  }
  public function createAction(){
    $id = $this->_getParam('id',false);
    $this->view->upload_max_size = $upload_max_size = ini_get('upload_max_filesize');
    $this->view->form = $form = new Sesadvancedactivity_Form_Admin_Createevent();
    $this->view->visibility = $form->getValue('visibility');
      if($id){
        $item = Engine_Api::_()->getItem('sesadvancedactivity_event',$id);
        $form->populate($item->toArray());
        $form->setTitle('Edit This Custom Notification');
        $form->submit->setLabel('Edit');
      }
      
    // Check if post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }

    if($this->getRequest()->isPost() && (empty($_FILES['file']['size']) || (int)$_FILES['file']['size'] > (int)$max_file_upload_in_bytes)){
      $form->file->addError('File was not uploaded and size not more than '.$upload_max_size);
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    $starttime = $form->getValue('visibility') == 4 && $form->getValue('starttime') == '0000-00-00';
    $endtime = $form->getValue('visibility') == 4 && $form->getValue('endtime') == '0000-00-00';

    if ($starttime && $endtime) {
      $form->starttime->setRequired(true);
      $form->starttime->setAllowEmpty(false);
      $form->starttime->addError('Please select a start date from the calendar.');
      $form->endtime->setRequired(true);
      $form->endtime->setAllowEmpty(false);
      $form->endtime->addError('Please select a end date from the calendar.');
      return;
    }
    
    if ($starttime) {
      $form->starttime->setRequired(true);
      $form->starttime->setAllowEmpty(false);
      $form->starttime->addError('Please select a start date from the calendar.');
      return;
    }

    if ($endtime) {
      $form->endtime->setRequired(true);
      $form->endtime->setAllowEmpty(false);
      $form->endtime->addError('Please select a end date from the calendar.');
      return;
    }

    $values = $form->getValues();
    if($values['visibility'] != "4"){
      unset($values['starttime']);
      unset($values['endtime']);
    }
    $select = Engine_Api::_()->getDbtable('events', 'sesadvancedactivity')->select()->where('date =?',date('Y-m-d',strtotime($values['date'])));
    if($item) 
      $select->where('event_id !=?',$item->getIdentity());
    $eventExists = Engine_Api::_()->getDbtable('events', 'sesadvancedactivity')->fetchRow($select);
    if($eventExists)
      return $form->AddError('Event with same date already exists');
    $db = Engine_Api::_()->getDbtable('events', 'sesadvancedactivity')->getAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {
      $filterTable = Engine_Api::_()->getDbtable('events', 'sesadvancedactivity');
      if(empty($id)){
       $item = $filterTable->createRow();
       $item->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      }
      $item->setFromArray($values);
      $item->save();
      if(!empty($_FILES['file']['name']))
       $item->file_id = $this->setPhoto($form->file,$item->getIdentity());
      $item->save();
      $db->commit();
    }catch(Exception $e){
      $db->rollBack();
      throw $e;  
    }
    $this->_forward('success', 'utility', 'core', array(
                    'smoothboxClose' => 10,
                    'parentRefresh'=> 10,
                    'messages' => array('Event Created Successfully.')
    ));
  }
  	protected function setPhoto($photo,$id){
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if( $photo instanceof Storage_Model_File ) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    }else if( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    if( !$fileName ) {
      $fileName = $file;
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' =>'sesadvancedactivity_event',
      'parent_id' => $id,
      'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
      'name' => $fileName,
    );
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
		$mainPath = $path . DIRECTORY_SEPARATOR . $base . '_poster.' . $extension;
		copy($file,$mainPath);
    // Store
    try {
			 $iMain = $filesTable->createFile($mainPath, $params);
    } catch( Exception $e ) {
      // Remove temp files
			 @unlink($mainPath);
      // Throw
      if( $e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE ) {
        throw new Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }    
    // Remove temp files
		@unlink($mainPath);
    // Update row
    // Delete the old file?
    if( !empty($tmpRow) ) {
      $tmpRow->delete();
    }
    return $iMain->file_id;  	
	}
    public function enabledAction() {
    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesadvancedactivity_event', $id);
      $item->active = !$item->active;
      $item->save();
    }
    
    $this->_redirect('admin/sesadvancedactivity/events');
  }
  public function deleteAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();
    $form->setTitle('Delete this Custom Notification?');
    $form->setDescription('Are you sure that you want to delete this custom notification? It will not be recoverable after being deleted. If any user has already shared this notification, then that notification will also be deleted from the shared feed.');
    $form->submit->setLabel('Delete');
    

    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
    
      //Delete all event share activity
      $results = Engine_Api::_()->getDbtable('attachments', 'sesadvancedactivity')->getAllEvents($id);
      if(count($results) > 0) {
        foreach($results as $result) {
          $action = Engine_Api::_()->getItem('sesadvancedactivity_action', $result->action_id)->delete();
        }
      }

      $item = Engine_Api::_()->getItem('sesadvancedactivity_event', $id)->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Event Deleted Successfully.')
      ));
    }
  }
}