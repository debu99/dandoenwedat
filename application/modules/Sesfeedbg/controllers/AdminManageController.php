<?php

class Sesfeedbg_AdminManageController extends Core_Controller_Action_Admin {

  public function indexAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $background = Engine_Api::_()->getItem('sesfeedbg_background', $value);
          if($background)
          $background->delete();
        }
      }
    }
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesfeedbg_admin_main_febgsettings');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesfeedbg_admin_main', array(), 'sesfeedbg_admin_main_feedbg');

    $this->view->paginator = Engine_Api::_()->getDbTable('backgrounds','sesfeedbg')->getPaginator(array('admin' => 0));
    $this->view->paginator->setItemCountPerPage(60);
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page',1));
  }

  public function enabledAction() {

    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesfeedbg_background', $id);
      $item->enabled = !$item->enabled;
      $item->save();
    }
    $this->_redirect('admin/sesfeedbg/manage');
  }

  public function featuredAction() {

    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesfeedbg_background', $id);
      $item->featured = !$item->featured;
      $item->save();
    }
    $this->_redirect('admin/sesfeedbg/manage');
  }

  public function orderAction() {

    if (!$this->getRequest()->isPost())
      return;

    $backgroundsTable = Engine_Api::_()->getDbtable('backgrounds', 'sesfeedbg');
    $backgrounds = $backgroundsTable->fetchAll($backgroundsTable->select());
    foreach ($backgrounds as $background) {
      $order = $this->getRequest()->getParam('managebackgrounds_' . $background->background_id);
      if (!$order)
        $order = 999;
      $background->order = $order;
      $background->save();
    }
    return;
  }

  public function createAction() {

    $id = $this->_getParam('id',false);

    $this->view->upload_max_size = $upload_max_size = ini_get('upload_max_filesize');
    $this->view->max_file_upload_in_bytes = $max_file_upload_in_bytes = Engine_Api::_()->sesfeedbg()->max_file_upload_in_bytes();

    $this->view->form = $form = new Sesfeedbg_Form_Admin_Background_Create();
    if($id){
      $item = Engine_Api::_()->getItem('sesfeedbg_background',$id);
      $form->populate($item->toArray());
      $form->setTitle('Edit this Background Image');
      $form->submit->setLabel('Save Changes');
      $this->view->enableenddate = $item->enableenddate;
    } else {
      $this->view->enableenddate = 0;
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

    if(!$form->isValid($this->getRequest()->getPost()) && !$id) {
      $this->view->enableenddate = $enableenddate = $form->getValue('enableenddate') ? 1 : 0;
      if($enableenddate){
        $form->endtime->setRequired(true);
        $form->endtime->setAllowEmpty(false); 
      }
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    if($form->getValue('enableenddate')=="1" && ($form->getValue('endtime') == '0000-00-00 00:00:00' || empty($form->getValue('endtime')))){
      $this->view->enableenddate = 1;
      $form->endtime->setRequired(true);
      $form->endtime->setAllowEmpty(false);
      $form->endtime->addError('Please select a date from the calendar.');
      return;
    }

    $values = $form->getValues();
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {

      $backgroundsTable = Engine_Api::_()->getDbtable('backgrounds', 'sesfeedbg');
      $values['starttime'] = date('Y-m-d',  strtotime($values['starttime']));
      if($values['enableenddate'] && $values['endtime'] != '0000-00-00') {
        $values['endtime'] = date('Y-m-d', strtotime($values['endtime']));
      } else {
        $values['endtime'] = null;
      }
      unset($values['file']);

      if(empty($id))
        $item = $backgroundsTable->createRow();

      $item->setFromArray($values);
      $item->save();

      if(!empty($_FILES['file']['name'])) {
        $file_ext = pathinfo($_FILES['file']['name']);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile($form->file, array(
          'parent_id' => $item->getIdentity(),
          'parent_type' => $item->getType(),
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        ));
        // Remove temporary file
        @unlink($file['tmp_name']);
        $item->file_id = $storageObject->file_id;
        $item->save();
      }
      $item->order = $item->getIdentity();
      $item->save();

      $db->commit();
    } catch(Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('Feed Background Image Uploaded Successfully.')
    ));
  }

  public function deleteAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesbasic_Form_Admin_Delete();

    $form->setTitle('Delete This Background Image');
    $form->setDescription('Are you sure that you want to delete this background image? It will not be recoverable after being deleted.');

    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $background = Engine_Api::_()->getItem('sesfeedbg_background', $id);
      $background->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Feed Background Image Deleteed Successfully.')
      ));
    }
  }

  public function uploadZipFileAction(){

    $this->view->form = $form = new Sesfeedbg_Form_Admin_Background_Zipupload();
    $this->view->upload_max_size = $upload_max_size = ini_get('upload_max_filesize');

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

    if(!empty($_FILES["file"]["name"])) {

        $file = $_FILES["file"];
        $filename = $file["name"];
        $tmp_name = $file["tmp_name"];
        $type = $file["type"];

        $name = explode(".", $filename);
        $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');

        if(in_array($type,$accepted_types)) { //If it is Zipped/compressed File
          $okay = true;
        }

        $continue = strtolower($name[1]) == 'zip' ? true : false; //Checking the file Extension

        if(!$continue) {
          $form->addError("The file you are trying to upload is not a .zip file. Please try again.");
          return;
        }


        /* here it is really happening */
        $ran = $name[0]."-".time()."-".rand(1,time());
        $dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/';
        $targetdir = $dir.$ran;
        $targetzip = $dir.$ran.".zip";

        if(move_uploaded_file($tmp_name, $targetzip)) { //Uploading the Zip File
          /* Extracting Zip File */
          $zip = new ZipArchive();
          $x = $zip->open($targetzip);  // open the zip file to extract
          if ($x === true) {
              $zip->extractTo($targetdir); // place in the directory with same name
              $zip->close();

              @unlink($targetzip); //Deleting the Zipped file
              // Get subdirectories
              chmod($targetdir, 0777) ;
              $directories = glob($targetdir.'*', GLOB_ONLYDIR);
              if ($directories !== FALSE) {
                $db = Engine_Db_Table::getDefaultAdapter();
                $db->beginTransaction();
                // If we're here, we're done
                $this->view->status = true;
                try {
                  foreach($directories as $directory) {
                    $path = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
                      foreach ($path as $file) {
                        if (!$file->isFile())
                          continue;
                        $base_name = basename($file->getFilename());
                        if (!($pos = strrpos($base_name, '.')))
                          continue;
                        $extension = strtolower(ltrim(substr($base_name, $pos), '.'));
                        if (!in_array($extension, array('gif', 'jpg', 'jpeg', 'png','JPEG','JPG','PNG','GIF')))
                          continue;
                        $this->uploadZipFile($file->getPathname());
                    }
                  }
                  $db->commit();
                  $this->rrmdir($targetdir);
                 } catch(Exception $e) {
                    $db->rollBack();
                    throw $e;
                 }
              }
          }
        } else {
            $form->addError("There was a problem with the upload. Please try again.");
            return;
        }
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('Zip images uploaded Successfully.')
    ));
  }

  private function uploadZipFile($file = '') {

    $backgroundsTable = Engine_Api::_()->getDbtable('backgrounds', 'sesfeedbg');

    $item = $backgroundsTable->createRow();
    $values['enabled'] = 1;
    $values['starttime'] = date('Y-m-d');
    $values['enableenddate'] = 1;
    $values['order'] = $item->getIdentity();
    $item->setFromArray($values);
    $item->save();

    if(!empty($file)) {
      $file_ext = pathinfo($file);
      $file_ext = $file_ext['extension'];
      $storage = Engine_Api::_()->getItemTable('storage_file');
      $fileUpload = array('name'=>basename($file),'tmp_name'=>$file,'size'=>filesize($file),'error'=>0);
      $storageObject = $storage->createFile($file, array(
        'parent_id' => $item->getIdentity(),
        'parent_type' => $item->getType(),
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
      ));
      // Remove temporary file
      @unlink($file['tmp_name']);
      $item->file_id = $storageObject->file_id;
      $item->save();
    }
  }

  private function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                $this->rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
  }
}
