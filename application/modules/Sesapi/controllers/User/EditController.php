<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: EditController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class User_EditController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      // Can specifiy custom id
      $id = $this->_getParam('id', null);
      $subject = null;
      if( null === $id ) {
        $subject = Engine_Api::_()->user()->getViewer();
        Engine_Api::_()->core()->setSubject($subject);
      } else {
        $subject = Engine_Api::_()->getItem('user', $id);
        Engine_Api::_()->core()->setSubject($subject);
      }
    }
    // if( !empty($id) ) {
    //   $params = array('id' => $id);
    // } else {
    //   $params = array();
    // }
    // // Set up require's
    // $this->_helper->requireUser();
    // $this->_helper->requireSubject('user');
    // $this->_helper->requireAuth()->setAuthParams(
    //   null,
    //   null,
    //   'edit'
    // );
  }
  public function profileAction()
  {
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();


    // General form w/o profile type
    $aliasedFields = $user->fields()->getFieldsObjectsByAlias();
    $this->view->topLevelId = $topLevelId = 0;
    $this->view->topLevelValue = $topLevelValue = null;
    if( isset($aliasedFields['profile_type']) ) {
      $aliasedFieldValue = $aliasedFields['profile_type']->getValue($user);
      $topLevelId = $aliasedFields['profile_type']->field_id;
      $topLevelValue = ( is_object($aliasedFieldValue) ? $aliasedFieldValue->value : null );
      if( !$topLevelId || !$topLevelValue ) {
        $topLevelId = null;
        $topLevelValue = null;
      }
      $this->view->topLevelId = $topLevelId;
      $this->view->topLevelValue = $topLevelValue;
    }
    // Get form
    $form = $this->view->form = new Sesapi_Form_Standard(array(
      'item' => Engine_Api::_()->core()->getSubject(),
      'topLevelId' => $topLevelId,
      'topLevelValue' => $topLevelValue,
    ));
    if($this->_getParam('getForm')){
     $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
     $this->generateFormFields($formFields);
    //$form->generate();
    }else if($this->_getParam('validateFieldsForm')) {
      $values = $this->getRequest()->getPost();
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      foreach($formFields as $key => $value){
        if($value['type'] == "Date"){
          $date = $values[$value['name']];
          if(!empty($date) && !is_null($date)){
            $values[$value['name']] = array();
            $values[$value['name']]['month'] = date('m',strtotime($date));
            $values[$value['name']]['year'] = date('Y',strtotime($date));
            $values[$value['name']]['day'] = date('d',strtotime($date));
          }
        }else if($value['type'] == "MultiCheckbox"){
          $arrayValues = $valuesArray = array();
          $valuesArray = $values[$value['name']];
          unset($values[$value['name']]);
          $counter = 0;
          foreach($valuesArray as $key=>$val){
            $arrayValues[$counter] = $key;
            $counter++;
          }
          $values[$value['name']] = $arrayValues;
        }
      }
      if( !$form->isValid($values) ) {
        $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
        $this->validateFormFields($validateFields);
      }else{
        $form->saveValues();
        // Update display name
        $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
        $user->setDisplayName($aliasValues);
        //$user->modified_date = date('Y-m-d H:i:s');
        $user->save();
        // update networks
        Engine_Api::_()->network()->recalculate($user);
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your profile edit successfully.")));
      }
    }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
  }
  public function photoAction()
  {
    ini_set('memory_limit', '-1');
    $resource_type = $this->_getParam('resource_type','album_photo');
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photo = Engine_Api::_()->getItem($resource_type,$photo_id);
    }


    if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) || !empty($photo)) {
      $db = $user->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        if(!empty($photo))
          $file = $photo;
        else
          $file = $_FILES['image'];
        $user->setPhoto($file);

        $iMain = Engine_Api::_()->getItem('storage_file', $user->photo_id);

        // Insert activity
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'profile_photo_update',
          '{item:$subject} added a new profile photo.');

        // Hooks to enable albums to work
        if( $action ) {

            $iMain = Engine_Api::_()->getItem('storage_file', $user->photo_id);
            if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
                $event = Engine_Hooks_Dispatcher::_()
                    ->callEvent('onUserPhotoUpload', array(
                        'user' => $user,
                        'file' => $iMain,
                    ));
            } else if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum')) {
                $event = Engine_Hooks_Dispatcher::_()
                    ->callEvent('onUserProfilePhotoUpload', array(
                        'user' => $user,
                        'file' => $iMain,
                    ));
            }
          $attachment = $event->getResponse();
          if( !$attachment ) $attachment = $iMain;
          // We have to attach the user himself w/o album plugin
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
        }
        $db->commit();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your profile photo updated successfully.")));
      }

      // If an exception occurred within the image adapter, it's probably an invalid image
      catch( Engine_Image_Adapter_Exception $e )
      {
         $db->rollBack();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The uploaded file is not supported or is corrupt.'), 'result' => array()));
      }

      // Otherwise it's probably a problem with the database or the storage system (just throw it)
      catch( Exception $e )
      {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->__toString(), 'result' => array()));
      }
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
  }
  public function removeCoverAction()
  {
    // Get form
    $user = Engine_Api::_()->core()->getSubject();
    $user->coverphoto = 0;
    $user->coverphotoparams = null;
    $user->save();

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your cover photo has been removed.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));

  }
  public function coverAction(){
    $user = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photoAlbum = Engine_Api::_()->getItem('album_photo',$photo_id);
    }
		$art_cover = $user->coverphoto;
		if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0)) {
      try{
        $file = $_FILES['image'];
        $this->setCoverPhoto($file, $user);
      }catch(Exception $e){
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate($e), 'result' => array()));
      }
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your cover photo edit successfully.")));
   }

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
  }
  private function setCoverPhoto($photo, $user, $level_id = null)
  {
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
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $coreSettings = Engine_Api::_()->getApi('settings', 'core');
    $mainHeight = $coreSettings->getSetting('main.photo.height', 1600);
    $mainWidth = $coreSettings->getSetting('main.photo.width', 1600);
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize($mainWidth, $mainHeight)
      ->write($mainPath)
      ->destroy();
    $normalHeight = $coreSettings->getSetting('normal.photo.height', 375);
    $normalWidth = $coreSettings->getSetting('normal.photo.width', 375);
    // Resize image (normal)
    $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize($normalWidth, $normalHeight)
      ->write($normalPath)
      ->destroy();
    $coverPath = $path . DIRECTORY_SEPARATOR . $base . '_c.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(1500, 1500)
      ->write($coverPath)
      ->destroy();
    if (!empty($user)) {
      $params = array(
        'parent_type' => $user->getType(),
        'parent_id' => $user->getIdentity(),
        'user_id' => $user->getIdentity(),
        'name' => basename($fileName),
      );
      try {
        $iMain = $filesTable->createFile($mainPath, $params);
        $iIconNormal = $filesTable->createFile($normalPath, $params);
        $iMain->bridge($iIconNormal, 'thumb.normal');
        $iCover = $filesTable->createFile($coverPath, $params);
        $iMain->bridge($iCover, 'thumb.cover');
        $user->coverphoto = $iMain->file_id;
        $user->save();
      } catch (Exception $e) {
        @unlink($mainPath);
        @unlink($normalPath);
        @unlink($coverPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE
          && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
          throw new Album_Model_Exception($e->getMessage(), $e->getCode());
        } else {
          throw $e;
        }
      }
      @unlink($mainPath);
      @unlink($normalPath);
      @unlink($coverPath);
      if (!empty($tmpRow)) {
        $tmpRow->delete();
      }
      return $user;
    } else {
      try {
        $iMain = $filesTable->createSystemFile($mainPath);
        $iIconNormal = $filesTable->createSystemFile($normalPath);
        $iMain->bridge($iIconNormal, 'thumb.normal');
        $iCover = $filesTable->createSystemFile($coverPath);
        $iMain->bridge($iCover, 'thumb.cover');
        // Remove temp files
        @unlink($mainPath);
        @unlink($normalPath);
        @unlink($coverPath);
      } catch (Exception $e) {
        @unlink($mainPath);
        @unlink($normalPath);
        @unlink($coverPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE
          && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
          throw new Album_Model_Exception($e->getMessage(), $e->getCode());
        } else {
          throw $e;
        }
      }
      Engine_Api::_()->getApi("settings", "core")
        ->setSetting("usercoverphoto.preview.level.id.$level_id", $iMain->file_id);
      return $user;
    }
  }
  public function removePhotoAction()
  {
    // Get form
    $user = Engine_Api::_()->core()->getSubject();
    $user->photo_id = 0;
    $user->save();

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));

  }
}
