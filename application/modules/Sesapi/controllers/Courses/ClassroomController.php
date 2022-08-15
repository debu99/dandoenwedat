<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ClasroomController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Courses_ClassroomController extends Sesapi_Controller_Action_Standard
{
    protected $_innerCalling = false;
    public function init(){
        if (!$this->_helper->requireAuth()->setAuthParams('classroom', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        $classroom_id = $this->_getParam('classroom_id');
        $classroom = null;
        $classroom = Engine_Api::_()->getItem('classroom', $classroom_id);
        if ($classroom) {
          if ($classroom) {
            Engine_Api::_()->core()->setSubject($classroom);
          } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
          }
        }
    }
    public function createAction(){
      if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewerId = $viewer->getIdentity();
      $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      if (!$this->_helper->requireUser->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      
      $totalClassroom = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')->countClassrooms($viewer->getIdentity());
      $allowClassroomCount = Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'classroom_count');
      $errorcode['error_code'] = 'PME' . $totalClassroom;
      if ($allowClassroomCount != 0 && $totalClassroom >= $allowClassroomCount) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have reached the limit of classroom creation. Please contact to the site administrator.'), 'result' => $errorcode));
      }
        $quckCreate = 0;
        $form = new Eclassroom_Form_Classroom_Create(array('fromApi'=>true));
        $form->removeElement('removeimage');
        $form->removeElement('removeimage2');
        $form->removeElement('classroom_main_photo_preview');
        $form->removeElement('photo-uploader');
        if ($form->getElement('category_id'))
            $form->getElement('category_id')->setValue($this->_getParam('category_id'));
        if ($form->getElement('classroom_location'))
            $form->getElement('classroom_location')->setLabel('Location');

        if($_GET['sesapi_platform'] == 1){
          if($form->getElement('member_title_singular')){
            $form->getElement('member_title_singular')->setDescription("");
            $form->getElement('member_title_plural')->setDescription("");
          }
        }

        if ($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
        }
        if (!$form->isValid($_POST)) {
          $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
          if (count($validateFields))
              $this->validateFormFields($validateFields);
        }
        
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }
        //check custom url
        if (!$quckCreate && isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
            $custom_url = Engine_Api::_()->getDbtable('classrooms', 'eclassroom')->checkCustomUrl($_POST['custom_url']);
            if ($custom_url) {
              $form->addError($this->view->translate("Custom URL is not available. Please select another URL."));
            }
        }
        $values = array();
        if (!$quckCreate) {
          $values = $form->getValues();
          $values['location'] = isset($_POST['location']) ? $_POST['location'] : '';
        }
        $values['owner_id'] = $viewer->getIdentity();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        if (!$quckCreate && $settings->getSetting('eclassroom.classmainphoto', 1)) {
          if (empty($_FILES['photo']['size']) && empty($_FILES['image']['size'])) {
            $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Main Photo is a required field.'), 'result' => array()));
          }
        }
        if (isset($values['networks'])) {
          //Start Network Work
          $networkValues = array();
          foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
            $networkValues[] = $network->network_id;
          }
          if (@$values['networks'])
            $values['networks'] = ',' . implode(',', $values['networks']);
          else
            $values['networks'] = '';
          //End Network Work
        }
        if (!isset($values['can_join']))
          $values['can_join'] = $settings->getSetting('eclassroom.default.joinoption', 1) ? 0 : 1;
        if (!isset($values['can_invite']))
          $values['can_invite'] = $settings->getSetting('eclassroom.invite.people.default', 1) ? 0 : 1;

        $classroomTable = Engine_Api::_()->getDbTable('classrooms', 'eclassroom');
        $db = $classroomTable->getAdapter();
        $db->beginTransaction();
        try {
             // Create classroom
            $classroom = $classroomTable->createRow();
            if (!$quckCreate && empty($_POST['lat'])) {
                unset($values['location']);
                unset($values['lat']);
                unset($values['lng']);
                unset($values['venue_name']);
            }
            $classroom_draft = $settings->getSetting('classroom.draft', 1);
            if (empty($classroom_draft)) {
                $values['draft'] = 1;
            }
            if (!$quckCreate) {
                if (empty($values['category_id']))
                $values['category_id'] = 0;
                if (empty($values['subsubcat_id']))
                $values['subsubcat_id'] = 0;
                if (empty($values['subcat_id']))
                $values['subcat_id'] = 0;
            }
            $classroom->setFromArray($values);
            if(!isset($values['search']))
                $classroom->search = 1;
            else
                $classroom->search = $values['search'];
            if (isset($_POST['title']))
                $classroom->title = $_POST['title'];
            if (isset($_POST['category_id']))
                $classroom->category_id = $_POST['category_id'];
            if (isset($_POST['subcat_id']))
                $classroom->category_id = $_POST['category_id'];
            if (isset($_POST['subsubcat_id']))
                $classroom->category_id = $_POST['category_id'];

            $classroom->parent_id = $parentId;
            if (!isset($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
                $classroom->view_privacy = $values['auth_view'];
                $classroom->save();
            if (!$quckCreate) {
                $tags = preg_split('/[,]+/', $values['tags']);
                $classroom->tags()->addTagMaps($viewer, $tags);
                $classroom->seo_keywords = implode(',', $tags);
                $classroom->save();
            }
            if (!$quckCreate) {
                //Add fields
                $customfieldform = $form->getSubForm('fields');
                if ($customfieldform) {
                    $customfieldform->setItem($classroom);
                    $customfieldform->saveValues();
                }
            }
            if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && !empty($_POST['location'])) {
                $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                $dbGetInsert->query('INSERT INTO engine4_eclassroom_locations (classroom_id,location,venue, lat, lng ,city,state,zip,country,address,address2, is_default) VALUES ("' . $classroom->classroom_id . '","' . $_POST['location'] . '", "' . $_POST['venue_name'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "1") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue_name'] . '"');

                $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $classroom->classroom_id . '","' . $_POST['location'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "classroom")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
            }
              //Manage Apps
            Engine_Db_Table::getDefaultAdapter()->query('INSERT IGNORE INTO `engine4_eclassroom_manageclassroomapps` (`classroom_id`) VALUES ("' . $classroom->classroom_id . '");');
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.auto.join', 1)) {
              $classroom->membership()->addMember($viewer)->setUserApproved($viewer)->setResourceApproved($viewer);
            }
            if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
                $classroom->custom_url = $_POST['custom_url'];
            else
                $classroom->custom_url = $classroom->classroom_id;
            if (!Engine_Api::_()->authorization()->getPermission($viewer, 'eclassroom', 'cls_approve'))
                $classroom->is_approved = 0;
            if (Engine_Api::_()->authorization()->getPermission($viewer, 'eclassroom', 'bs_featured'))
                $classroom->featured = 1;
            if (Engine_Api::_()->authorization()->getPermission($viewer, 'eclassroom', 'bs_sponsored'))
                $classroom->sponsored = 1;
            if (Engine_Api::_()->authorization()->getPermission($viewer, 'eclassroom', 'bs_verified'))
                $classroom->verified = 1;
            if (Engine_Api::_()->authorization()->getPermission($viewer, 'eclassroom', 'classroom_hot'))
                $classroom->hot = 1;
            $classroom->save();
            // Add photo

            if (!empty($_FILES['photo']['size'])) {
                $classroom->setPhoto($form->photo, '', 'profile');
            }
            if (!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) {
                $classroom->setPhoto($_FILES['image'], '', 'profile');
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            if (!isset($values['auth_view']) || empty($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            if (!isset($values['auth_comment']) || empty($values['auth_comment'])) {
                $values['auth_comment'] = 'everyone';
            }
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            $albumMax = array_search($values['auth_album'], $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($classroom, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($classroom, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($classroom, $role, 'album', ($i <= $albumMax));
            }
            $db->commit();
            $classroomname = '<a href="'.$classroom->getHref().'">'.$classroom->getTitle().'</a>';
            $userName = '<a href="'.$viewer->getHref().'">'.$viewer->getTitle().'</a>';
            // Add activity only if sesproduct is published
            if($values['draft'] && $classroom->is_approved == 1) {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $classroom, 'eclassroom_classroom_create');
                // make sure action exists before attaching the sesproduct to the activity
                if($action) {
                  Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $classroom);
                }
                //Tag Work
                if($action && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
                  $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                  foreach($tags as $tag) {
                    $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
                  }
                }
                $users = array_unique(array_merge($likesClassroom ,$followerClassroom, $favouriteClassroom), SORT_REGULAR);
                foreach($users as $user){ 
                    $usersOject = Engine_Api::_()->getItem('user', $user);
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($usersOject, $viewer, $classroom, 'eclassroom_classroom_create');

                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($usersOject->email, 'eclassroom_classroom_create', array('host' => $_SERVER['HTTP_HOST'], 'classroom_name' => $classroomname,'object_link'=>$classroom->getHref()));
                }
            }
            $emails = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.receivenewalertemails', null);
            if(!empty($emails)) {
                $emailArray = explode(",",$emails);
                foreach($emailArray as $email) {
                    $email = str_replace(' ', '', $email);
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'eclassroom_classroom_create', array('host' => $_SERVER['HTTP_HOST'], 'classroom_name' => $classroomname,'object_link'=>$classroom->getHref()));
                }
            }
            //Start Send Approval Request to Admin
            try {
              if (!$classroom->is_approved) {
                $getAdminnSuperAdmins = Engine_Api::_()->courses()->getAdminnSuperAdmins();
                  foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {  
                    $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $classroom, 'eclassroom_waitingadminapproval');
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'eclassroom_waitingadminapproval', array('sender_title' => $classroom->getOwner()->getTitle(), 'adminmanage_link' => 'admin/eclassroom/manage', 'classroom_name' => $classroomname, 'object_link' => $classroom->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                $receiverEmails = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.receivenewalertemails');
                if (!empty($receiverEmails)) {
                  $receiverEmails = explode(',', $receiverEmails);
                  foreach ($receiverEmails as $receiverEmail) {
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiverEmail, 'eclassroom_waitingadminapproval', array('sender_title' => $classroom->getOwner()->getTitle(), 'classroom_name' => $classroomname, 'object_link' => $classroom->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                  }
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($classroom->getOwner(), 'eclassroom_classroom_wtapr', array('classroom_title' => $classroom->getTitle(),'classroom_name' => $classroomname, 'object_link' => $classroom->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($viewer, $viewer, $classroom, 'eclassroom_classroom_wtapr');
              }
              //Send mail to all super admin and admins
              if ($classroom->is_approved) {
                $getAdminnSuperAdmins = Engine_Api::_()->courses()->getAdminnSuperAdmins();
                foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                  $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                  Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'eclassroom_waitingadminapproval', array('sender_title' => $classroom->getOwner()->getTitle(),'classroom_name' => $classroomname,'object_link' => $classroom->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                $receiverEmails = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.receivenewalertemails');
                if (!empty($receiverEmails)) {
                  $receiverEmails = explode(',', $receiverEmails);
                  foreach ($receiverEmails as $receiverEmail) {
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiverEmail, 'eclassroom_waitingadminapproval', array('sender_title' => $classroom->getOwner()->getTitle(), 'classroom_name' => $classroomname, 'object_link' => $classroom->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                  }
                }
                Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($classroom->getOwner(), $viewer, $classroom, 'eclassroom_classsroom_adminaapr');
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($classroom->getOwner(), 'eclassroom_classroom_adminaapr', array('classroom_title' => $classroom->getTitle(),'classroom_name' => $classroomname, 'object_link' => $classroom->getHref(), 'host' => $_SERVER['HTTP_HOST']));
              } 
            } catch(Exception $e) {}
              $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.autoopenpopup', 1);
              if ($autoOpenSharePopup) {
                $_SESSION['newClassroom'] = true;
              }
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $e->getMessage(), 'result' => array()));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('classroom_id' => $classroom->getIdentity(),'message' => $this->view->translate('Classroom created successfully.'))));
    }
    
    public function editAction() {
      $classroom_id = $this->_getParam('classroom_id', null);
      if (!$classroom_id)
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      if (!Engine_Api::_()->core()->hasSubject()) {
          $classroom = Engine_Api::_()->getItem('classroom', $classroom_id);
      } else {
          $classroom = Engine_Api::_()->core()->getSubject();
      }
      $previousTitle = $classroom->getTitle();
      $defaultProfileId = 1;
      if (isset($classroom->category_id) && $classroom->category_id != 0)
          $category_id = $classroom->category_id;
      else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
          $category_id = $_POST['category_id'];
      else
          $category_id = 0;
      if (isset($classroom->subsubcat_id) && $classroom->subsubcat_id != 0)
          $subsubcat_id = $classroom->subsubcat_id;
      else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
          $subsubcat_id = $_POST['subsubcat_id'];
      else
          $subsubcat_id = 0;
      if (isset($classroom->subcat_id) && $classroom->subcat_id != 0)
          $subcat_id = $classroom->subcat_id;
      else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
          $subcat_id = $_POST['subcat_id'];
      else
          $subcat_id = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      if( !$this->_helper->requireSubject()->isValid() ) 
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $classroom->isOwner($viewer)))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
      $form = new Eclassroom_Form_Classroom_Edit(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));
      $form->removeElement('classroom_location');
      if($_GET['sesapi_platform'] == 1){
        if( $form->getElement('member_title_singular')){
          $form->getElement('member_title_singular')->setDescription("");
          $form->getElement('member_title_plural')->setDescription("");
        }
        }
      $tagStr = '';
      foreach ($classroom->tags()->getTagMaps() as $tagMap) {
          $tag = $tagMap->getTag();
          if (!isset($tag->text))
              continue;
          if ('' !== $tagStr)
              $tagStr .= ', ';
          $tagStr .= $tag->text;
      }
      $values = $classroom->toArray();
      $values['tags'] = $tagStr;
      $values['networks'] = ltrim($classroom['networks']);
      $form->populate($values);
      // Populate auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      foreach( $roles as $role ) {
        if ($form->auth_view){
          if( $auth->isAllowed($classroom, $role, 'view') ) {
          $form->auth_view->setValue($role);
          }
        }
        if ($form->auth_comment){
          if( $auth->isAllowed($classroom, $role, 'comment') ) {
            $form->auth_comment->setValue($role);
          }
        }
        if ($form->auth_album){
          if($auth->isAllowed($classroom, $role, 'album') ) {
            $form->auth_album->setValue($role);
          }
        }
      }
      if ($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
      }
      if (!$form->isValid($_POST)) {
          $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
          if (count($validateFields))
              $this->validateFormFields($validateFields);
      }

      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.classmainphoto', 1)) {
          if (empty($_FILES['photo']['size']) && empty($_FILES['image']['size'])) {
            $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Main Photo is a required field.'), 'result' => array()));
        }
    }
      //check custom url
      if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
          $custom_url = Engine_Api::_()->getDbtable('classrooms', 'eclassroom')->checkCustomUrl($_POST['custom_url'],$classroom->classroom_id);
          if ($custom_url) {
              $form->addError($this->view->translate("Custom URL not available. Please select other."));
          }
      }
      $db = Engine_Api::_()->getItemTable('classroom')->getAdapter();
      $db->beginTransaction();
      try {
            $values = $form->getValues();
          $classroom->setFromArray($values);
          $classroom->modified_date = date('Y-m-d H:i:s');

          if(isset($values['levels']))
              $values['levels'] = implode(',',$values['levels']);
          if(isset($values['networks']))
              $values['networks'] = implode(',',$values['networks']);
          $values['ip_address'] = $_SERVER['REMOTE_ADDR'];
          $classroom->save();
          // Auth
          if( empty($values['auth_view']) ) {
              $values['auth_view'] = 'everyone';
          }
          if( empty($values['auth_comment']) ) {
              $values['auth_comment'] = 'everyone';
          }
          $viewMax = array_search($values['auth_view'], $roles);
          $commentMax = array_search($values['auth_comment'], $roles);
          $lectureCreate = array_search(isset($values['auth_ltr_create']) ? $values['auth_ltr_create']: '', $roles);
          $tstCreate = array_search(isset($values['auth_tst_create']) ? $values['auth_tst_create']: '', $roles);
          foreach( $roles as $i => $role ) {
              $auth->setAllowed($classroom, $role, 'view', ($i <= $viewMax));
              $auth->setAllowed($classroom, $role, 'comment', ($i <= $commentMax));
              $auth->setAllowed($classroom, $role, 'album', ($i <= $lectureCreate));
          }
          // handle tags
          $tags = preg_split('/[,]+/', $values['tags']);
          $classroom->tags()->setTagMaps($viewer, $tags);
          if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
              $classroom->custom_url = $_POST['custom_url'];
          else
              $classroom->custom_url = $classroom->classroom_id;
          $classroom->save();
          $db->commit();
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('classroom_id' => $classroom->getIdentity(), 'success_message' => $this->view->translate('Classroom edited successfully.'))));
    }
    public function deleteAction()
    {
      $classroomId = $this->getParam('classroom_id',$this->getParam('id'));
      if (!$this->_helper->requireUser()->isValid())
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
      $classroom = Engine_Api::_()->getItem('classroom', $classroomId);
      if (!$classroom)
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This classroom does not exist.'), 'result' => array()));
      if (!$this->_helper->requireAuth()->setAuthParams($classroom, null, 'delete')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

      $form = new Sesbasic_Form_Admin_Delete();
      $form->setTitle('Delete Classroom?');
      $form->setDescription('Are you sure that you want to delete this Classroom? It will not be recoverable after being deleted.');
      $form->submit->setLabel('Delete');
      if (!$this->getRequest()->isPost()) {
          $status['status'] = false;
          $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
      }
      $db = $classroom->getTable()->getAdapter();
      $db->beginTransaction();
      try {
          Engine_Api::_()->courses()->deleteClassroom($classroom);
          $db->commit();
          $status['status'] = true;
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted classroom.'), $status)));
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
  }
  public function browseAction(){
    $isSearch = $this->_getParam('search', 0);
    $isCategory = $this->_getParam('category_id', 0);
    $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
    $coreContentTableName = $coreContentTable->info('name');
    $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
    $corePagesTableName = $corePagesTable->info('name');
    $select = $corePagesTable->select()
        ->setIntegrityCheck(false)
        ->from($corePagesTable, null)
        ->where($coreContentTableName . '.name=?', 'eclassroom.browse-search')
        ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
        ->where($corePagesTableName . '.name = ?', 'eclassroom_index_browse');
    $id = $select->query()->fetchColumn();
    if (!empty($_POST['location'])) {
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if ($latlng) {
          $_POST['lat'] = $latlng['lat'];
          $_POST['lng'] = $latlng['lng'];
      }
    }
    $form = new Eclassroom_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
    $form->populate($_POST);
    $params = $form->getValues();
    $value = array();
    $value['status'] = 1;
    $value['search'] = 1;
    $value['draft'] = "1";
    if (isset($params['search']))
        $params['text'] = addslashes($params['search']);
    $params['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
    $params = array_merge($params, $value);
    $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')
        ->getClassroomPaginator($params);
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $page = $this->_getParam('page', 1);
    if ($page == 1 && @$_POST['filter_sort'] == 'classroom_main_browse') { 
        $categories = Engine_Api::_()->getDbtable('categories', 'eclassroom')->getCategory(array('column_name' => '*', 'limit' => 25));
        $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('eclassroom_main', array());
        $category_counter = 0;
        foreach ($categories as $category) {
            if ($category->thumbnail)
                $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
            if ($category->cat_icon)
                $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
            if ($category->colored_icon)
                $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
            $result_category[$category_counter]['slug'] = $category->slug;
            $result_category[$category_counter]['category_name'] = $category->category_name;
            $result_category[$category_counter]['total_classroom_categories'] = $category->total_classroom_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;
            $category_counter++;
        }
        if (!isset($params['category_id'])) {
          $result['category'] = $result_category;
          if (count($this->getPopularClassrooms($paginator))) {
              $result['popularClassrooms'] = $this->getPopularClassrooms($paginator);
          }
        }
    }
    $result['classrooms'] = $this->getClassrooms($paginator);
    $this->_innerCalling = true;
    if ($page == 1 && $paginator->getTotalItemCount() > 0){
        if(empty($isSearch) && empty($isCategory)){
          $hotClassroom = $this->hotAction();
            if(sizeof($hotClassroom))
                $result['hot_classroom'] = $hotClassroom;
            $featuredClassroom = $this->featuredAction();
            if(sizeof($featuredClassroom))
                $result['featured_classroom'] = $featuredClassroom;
            $verifiedClassroom = $this->verifiedAction();
            if(sizeof($verifiedClassroom))
                $result['verified_classroom'] = $verifiedClassroom;
      }
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function manageAction()
  {
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();
      if (!$viewer_id) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      }
      $params['user_id'] = $viewer_id;
      $defaultOpenTab = $this->_getParam('search_type', 'recentlySPcreated');
      switch ($defaultOpenTab) {
          case 'recentlySPcreated':
              $params['sort'] = 'creation_date';
              break;
          case 'mostSPviewed':
              $params['sort'] = 'view_count';
              break;
          case 'mostSPliked':
              $params['sort'] = 'like_count';
              break;
          case 'mostSPcommented':
              $params['sort'] = 'comment_count';
              break;
          case 'mostSPfavourite':
              $params['sort'] = 'favourite_count';
              break;
          case 'mostSPfollowed':
              $params['sort'] = 'follow_count';
              break;
          case 'featured':
              $params['sort'] = 'featured';
              break;
          case 'sponsored':
              $params['sort'] = 'sponsored';
              break;
          case 'verified':
              $params['sort'] = 'verified';
              break;
          case 'hot':
              $params['sort'] = 'hot';
              break;
          case 'mostSPcourses':
              $params['courses'] = 'Courses';
              break;
      }
        $params['widgetManage'] = true;
        $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')->getClassroomPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.share', 0);
        $viewerId = $viewer->getIdentity();
        $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'eclassroom', 'eclassroom_can_join') : 0;
        $filterOptionsMenu = array();
        $filterMenucounter = 0;
        $resultmenu[$filterMenucounter]['name'] = 'recentlySPcreated';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Recently Created');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPliked';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Liked');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPcommented';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Commented');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPviewed';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most viewed');
        $filterMenucounter++;
        if ($canFavourite) {
            $resultmenu[$filterMenucounter]['name'] = 'mostSPfavourite';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Favourited');
            $filterMenucounter++;
        }
        if ($canJoin) {
            $resultmenu[$filterMenucounter]['name'] = 'mostSPjoined';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Joined');
            $filterMenucounter++;
        }
        $resultmenu[$filterMenucounter]['name'] = 'featured';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Featured');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'sponsored';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Sponsored');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'verified';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Verified');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'hot';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Hot');
        if ($paginator) {
            $classroomCounter = 0;
            foreach ($paginator as $classroom) {
                $classroomArray = $classroom->toArray();
                if (!$canFavourite)
                    unset($classroomArray['favourite_count']);
                if (!$canFollow)
                    unset($classroomArray['follow_count']);
                if ($classroom->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.location', 1)) {
                  unset($classroom['location']);
                  $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('classrooms', $classroom->getIdentity());
                  if ($location) {
                      $result[$classroomCounter]['location'] = $location->toArray();
                      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.map.integration', 1)) {
                          $result[$classroomCounter]['location']['showMap'] = true;
                      } else {
                          $result[$classroomCounter]['location']['showMap'] = false;
                      }
                  }
                }
                $result[$classroomCounter] = $classroomArray;
                $statsCounter = 0;
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($classroom, '', "");
                if (image) {
                    $result[$classroomCounter]['images'] = $image;
                } else {
                    $result[$classroomCounter]['images'] = $image;
                }
                $classroomCounter++;
            }
        }
        $data['classrooms'] = $result;
        if ($this->_getParam('page', 1))
          $data['filterMenuOptions'] = $resultmenu;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data), $extraParams));
    }
  public function getClassrooms($paginator){ 
    $result = array();
    $counter = 0;
    $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_allow_favourite', 0);
    $likeFollowIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.integration', 0);
    $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.follow', 1);
    $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.share', 1);
    $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_show_userdetail', 0);
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'bs_can_join') : 0;
    foreach ($paginator as $classroom) {
        $result[$counter] = $classroom->toArray();
        $result[$counter]['likeFollowIntegrate'] = $likeFollowIntegrate? true : false;
        $result[$counter]['rating'] = round($classroom->rating, 2);
        if(!$hideIdentity)
        $result[$counter]['owner_title'] = $classroom->getOwner()->getTitle();
        if ($classroom->category_id) {
            $category = Engine_Api::_()->getItem('eclassroom_category', $classroom->category_id);
            if ($category) {
                $result[$counter]['category_title'] = $category->category_name;
                if ($classroom->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('eclassroom_category', $classroom->subcat_id);
                    if ($subcat) {
                        $result[$counter]['subcategory_title'] = $subcat->category_name;
                        if ($classroom->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('eclassroom_category', $classroom->subsubcat_id);
                            if ($subsubcat) {
                                $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }
        $tags = array();
        foreach ($classroom->tags()->getTagMaps() as $tagmap) {
            $arrayTag = $tagmap->toArray();
            if(!$tagmap->getTag())
                continue;
            $tags[] = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
            ));
        }
        if (count($tags)) {
            $result[$counter]['tag'] = $tags;
        }
        $result[$counter]['images']['main']= $this->getBaseUrl(true, $classroom->getPhotoUrl());
        $result[$counter]['cover_image']['main'] = $this->getBaseUrl(true, $classroom->getCoverPhotoUrl());
        $result[$counter]['cover_images']['main'] = $result[$counter]['cover_image']['main'];
        $showLoginformFalse = false;
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.contact.details', 1) && $viewerId == 0) {
            $showLoginformFalse = true;
        }
        $i = 0;
        if ($classroom->classroom_contact_email || $classroom->classroom_contact_phone || $classroom->classroom_contact_website) {
            if ($classroom->classroom_contact_email) {
                $result[$counter]['menus'][$i]['name'] = 'mail';
                $result[$counter]['menus'][$i]['label'] = 'Send Email';
                $result[$counter]['menus'][$i]['value'] = $classroom->classroom_contact_email;
                $i++;
            }
            if ($classroom->classroom_contact_phone) {
                $result[$counter]['menus'][$i]['name'] = 'phone';
                $result[$counter]['menus'][$i]['label'] = 'Call';
                $result[$counter]['menus'][$i]['value'] = $classroom->classroom_contact_phone;
                $i++;
            }
            if ($classroom->classroom_contact_website) {
                $result[$counter]['menus'][$i]['name'] = 'website';
                $result[$counter]['menus'][$i]['label'] = 'Visit Website';
                $result[$counter]['menus'][$i]['value'] = $classroom->classroom_contact_website;
                $i++;
            }
            $result[$counter]['showLoginForm'] = $showLoginformFalse;
        }
        if ($classroom->is_approved) {
            if ($shareType) {
                $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $classroom->getPhotoUrl());
                $result[$counter]["share"]["url"] = $this->getBaseUrl(false,$classroom->getHref());
                $result[$counter]["share"]["title"] = $classroom->getTitle();
                $result[$counter]["share"]["description"] = strip_tags($classroom->getDescription());
                $result[$counter]["share"]["setting"] = $shareType;
                $result[$counter]["share"]['urlParams'] = array(
                    "type" => $classroom->getType(),
                    "id" => $classroom->getIdentity()
                );
            }
        }
        if ($classroom->is_approved) {
          if($viewerId != $classroom->owner_id){
              $result[$counter]['menus'][$i]['name'] = 'contact';
            $result[$counter]['menus'][$i]['label'] = 'Contact';
            $i++;
          }
          if ($shareType) {
              $result[$counter]['menus'][$i]['name'] = 'share';
              $result[$counter]['menus'][$i]['label'] = 'Share';
              $i++;
          }
          $result[$counter]['showloginform_for_join_share'] = !$viewerId ? true : false;
          if ($canJoin) {
            //  if ($viewerId) {
                  $row = $classroom->membership()->getRow($viewer);
                  if (null === $row) {
                      if ($classroom->membership()->isResourceApprovalRequired()) {
                          $result[$counter]['menus'][$i]['name'] = 'request';
                          $result[$counter]['menus'][$i]['label'] = 'Request Membership';
                          $i++;
                      } else {
                          $result[$counter]['menus'][$i]['name'] = 'join';
                          $result[$counter]['menus'][$i]['label'] = 'Join Classroom';
                          $i++;
                      }
                  } else if ($row->active) {
                      if (!$classroom->isOwner($viewer)) {
                          $result[$counter]['menus'][$i]['label'] = 'Leave Classroom';
                          $result[$counter]['menus'][$i]['name'] = 'leave';
                          $i++;
                      }
                  } else if (!$row->resource_approved && $row->user_approved) {
                      $result[$counter]['menus'][$i]['label'] = 'Cancel Membership Request';
                      $result[$counter]['menus'][$i]['name'] = 'cancel';
                      $i++;

                  } else if (!$row->user_approved && $row->resource_approved) {
                      $result[$counter]['menus'][$i]['label'] = 'Accept Membership Request';
                      $result[$counter]['menus'][$i]['name'] = 'accept';
                      $i++;
                      $result[$counter]['menus'][$i]['label'] = 'Ignore Membership Request';
                      $result[$counter]['menus'][$i]['name'] = 'reject';
                  }
            //  }
          }
        }
        if ($viewerId != 0) {
            $result[$counter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($classroom);
            $result[$counter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($classroom);
            if ($canFavourite) {
                $result[$counter]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($classroom, 'favourites', 'eclassroom', 'classrooms', 'owner_id');
                $result[$counter]['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($classroom, 'favourites', 'eclassroom', 'classrooms', 'owner_id');
            }
            if ($canFollow) {
                $result[$counter]['is_content_follow'] = $this->contentFollow($classroom, 'followers', 'eclassroom', 'classrooms', 'owner_id');
                $result[$counter]['content_follow_count'] = (int)$this->getContentFollowCount($classroom, 'followers', 'eclassroom', 'classrooms', 'owner_id');
            }
        }
        if ($classroom->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.location', 1)) {
            unset($classroom['location']);
            $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('classrooms', $classroom->getIdentity());
            if ($location) {
                $result[$counter]['location'] = $location->toArray();
                if (Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.map.integration', 1)) {
                    $result[$counter]['location']['showMap'] = true;
                } else {
                    $result[$counter]['location']['showMap'] = false;
                }
            }
        }
        $counter++;
    }
    $results['classrooms'] = $result;
    return $result;
  }
  public function getPopularClassrooms($classroomPaginator){
      $params['info'] = 'most_viewed';
      $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')->getClassroomPaginator($params);
      $paginator->setItemCountPerPage(6);
      $paginator->setCurrentPageNumber(1);
      $result = $this->getClassrooms($paginator);
      return $result;
  }
  public function browsesearchAction()
  {
    $defaultProfileId = 1;
    $search_for = $search_for = $this->_getParam('search_for', 'classroom');
    $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
    $coreContentTableName = $coreContentTable->info('name');
    $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
    $corePagesTableName = $corePagesTable->info('name');
    $select = $corePagesTable->select()
        ->setIntegrityCheck(false)
        ->from($corePagesTable, null)
        ->where($coreContentTableName . '.name=?', 'eclassroom.browse-search')
        ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
        ->where($corePagesTableName . '.name = ?', 'eclassroom_index_browse');
    $id = $select->query()->fetchColumn();
    $form = new Eclassroom_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $form->setMethod('get')->populate($request->getParams());
    if($form->getElement('lat')){
      $form->removeElement('lat');
      $form->removeElement('lng');
    }
    $form->removeElement('cancel');
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'classrooms'));
    } else {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
  }
  public function featuredAction()
  {
    $params['sort'] = 'featured';
    $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')
        ->getClassroomPaginator($params);
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    if ($paginator->getCurrentPageNumber() == 1) {
        $categories = Engine_Api::_()->getDbtable('categories', 'eclassroom')->getCategory(array('column_name' => '*', 'limit' => 25));
        $category_counter = 0;
        foreach ($categories as $category) {
            if ($category->thumbnail)
                $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
            if ($category->cat_icon)
                $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
            if ($category->colored_icon)
                $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
            $result_category[$category_counter]['slug'] = $category->slug;
            $result_category[$category_counter]['category_name'] = $category->category_name;
            $result_category[$category_counter]['total_classroom_categories'] = $category->total_classroom_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;

            $category_counter++;
        }
        $result['category'] = $result_category;
    }
    if($this->_innerCalling){
      $paginator->setItemCountPerPage(5);
      return $this->getClassrooms($paginator);
    }
    $result['classrooms'] = $this->getClassrooms($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function sponsoredAction()
  {
    $params['sort'] = 'sponsored';
    $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')
        ->getClassroomPaginator($params);
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    if ($paginator->getCurrentPageNumber() == 1) {
      $categories = Engine_Api::_()->getDbtable('categories', 'eclassroom')->getCategory(array('column_name' => '*', 'limit' => 25));
      $category_counter = 0;
      foreach ($categories as $category) {
          if ($category->thumbnail)
              $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
          if ($category->cat_icon)
              $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
          if ($category->colored_icon)
              $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
          $result_category[$category_counter]['slug'] = $category->slug;
          $result_category[$category_counter]['category_name'] = $category->category_name;
          $result_category[$category_counter]['total_classroom_categories'] = $category->total_classroom_categories;
          $result_category[$category_counter]['category_id'] = $category->category_id;
          $category_counter++;
      }
      $result['category'] = $result_category;
    }
    if($this->_innerCalling){
      $paginator->setItemCountPerPage(5);
      return $this->getClassrooms($paginator);
    }
    $result['classrooms'] = $this->getClassrooms($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function verifiedAction()
  {
    $params['sort'] = 'verified';
    $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')
        ->getClassroomPaginator($params);
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    if ($paginator->getCurrentPageNumber() == 1) {
        $categories = Engine_Api::_()->getDbtable('categories', 'eclassroom')->getCategory(array('column_name' => '*', 'limit' => 25));
        $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('eclassroom_main', array());
        $category_counter = 0;
        $menu_counter = 0;
        foreach ($categories as $category) {
            if ($category->thumbnail)
                $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
            if ($category->cat_icon)
                $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
            if ($category->colored_icon)
                $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
            $result_category[$category_counter]['slug'] = $category->slug;
            $result_category[$category_counter]['category_name'] = $category->category_name;
            $result_category[$category_counter]['total_classroom_categories'] = $category->total_classroom_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;
            $category_counter++;
        }
        $result['category'] = $result_category;
    }
    if($this->_innerCalling){
      $paginator->setItemCountPerPage(5);
      return $this->getClassrooms($paginator);
    }
    $result['classrooms'] = $this->getClassrooms($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function hotAction()
  {
    $params['sort'] = 'hot';
    $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')
        ->getClassroomPaginator($params);
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    if ($paginator->getCurrentPageNumber() == 1) {
        $categories = Engine_Api::_()->getDbtable('categories', 'eclassroom')->getCategory(array('column_name' => '*', 'limit' => 25));
        $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('eclassroom_main', array());
        $category_counter = 0;
        $menu_counter = 0;
        foreach ($categories as $category) {
            if ($category->thumbnail)
                $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
            if ($category->cat_icon)
                $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
            if ($category->colored_icon)
                $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
            $result_category[$category_counter]['slug'] = $category->slug;
            $result_category[$category_counter]['category_name'] = $category->category_name;
            $result_category[$category_counter]['total_classroom_categories'] = $category->total_classroom_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;
            $category_counter++;
        }
        $result['category'] = $result_category;
    }
    if($this->_innerCalling){
      $paginator->setItemCountPerPage(5);
      return $this->getClassrooms($paginator);
    }
    $result['classrooms'] = $this->getClassrooms($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function categoriesAction(){
    $paginator = Engine_Api::_()->getDbTable('categories', 'eclassroom')->getClassroomPaginator($params);
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    if ($paginator->getCurrentPageNumber() == 1) {
        $categories = Engine_Api::_()->getDbtable('categories', 'eclassroom')->getCategory(array('column_name' => '*', 'limit' => 25));
        $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('eclassroom_main', array());
        $category_counter = 0;
        $menu_counter = 0;
        foreach ($categories as $category) {
            if ($category->thumbnail)
                $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
            if ($category->cat_icon)
                $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
            if ($category->colored_icon)
                $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
            $result_category[$category_counter]['slug'] = $category->slug;
            $result_category[$category_counter]['category_name'] = $category->category_name;
            $result_category[$category_counter]['total_classroom_categories'] = $category->total_classroom_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;
            $category_counter++;
        }
        $result['category'] = $result_category;
    }
    $result['categories'] = $this->getCategory($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function categoryAction(){
    $params['countBlogs'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'eclassroom')->getCategory($params);
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $category){
      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;
      if($category->thumbnail != '' && !is_null($category->thumbnail) && intval($category->thumbnail)):
        $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl(''));
      endif;
      if($category->cat_icon != '' && !is_null($category->cat_icon) && intval($category->cat_icon)):
        $catgeoryArray["category"][$counter]["cat_icon"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->cat_icon)->getPhotoUrl('thumb.icon'));
      endif;
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s classroom', '%s classrooms', $category->total_classroom_categories), $this->view->locale()->toNumber($category->total_classroom_categories));
      $counter++;
    }
    if($catgeoryArray <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array())); 
  }
  public function getCategory($categoryPaginator) {
      $result = array();
      $counter = 0;
      foreach ($categoryPaginator as $categories) {
          $classroom = $categories->toArray();
          $params['category_id'] = $categories->category_id;
          $params['limit'] = 5;
          $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')->getClassroomPaginator($params);
          $paginator->setItemCountPerPage(3);
          $paginator->setCurrentPageNumber(1);
          if($paginator->getTotalItemCount() > 0){
            $result[$counter] = $classroom;
            $result[$counter]['items'] = $this->getClassrooms($paginator);
            if ($paginator->getTotalItemCount() > 3) {
              $result[$counter]['see_all'] = true;
            } else {
              $result[$counter]['see_all'] = false;
            }
            $counter++;
          }
      }
      $results = $result;
      return $results;
  }
  public function contentFollow($subject = null,$tableName = "",$modulename = "",$resource_type = "",$column_name = "user_id"){
    $viewer = Engine_Api::_()->user()->getViewer();
    if (empty($subject) || empty($viewer))
        return;
    if ($viewer->getIdentity())
    {
      $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
      $select->where('resource_id =?',$subject->getIdentity())->where($column_name.' =?',$viewer->getIdentity());
      if($resource_type)
        $select->where('resource_type =?',$resource_type);
      $follow = (int) Zend_Paginator::factory($select)->getTotalItemCount();
    }
    return !empty($follow) ? true : false;
  }
  public function getContentFollowCount($subject,$tableName = "",$modulename = "",$resources_type = "",$column_name = "resource_id"){
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!$tableName || !$modulename)
      return 0;
    $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
    $select->where('resource_id =?',$subject->getIdentity());
    if($resources_type)
          $select->where('resource_type =?',$resources_type);
    return (int) Zend_Paginator::factory($select)->getTotalItemCount();
  }
  public function editoverviewAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
    }
    $subject = Engine_Api::_()->core()->getSubject();
    if ($this->_getParam('getForm')) {
        $formFields = array();
        $formFields[0]['name'] = "overview";
        $formFields[0]['type'] = "Textarea";
        $formFields[0]['multiple'] = "";
        $formFields[0]['label'] = "Classroom Overview";
        $formFields[0]['description'] = "";
        $formFields[0]['isRequired'] = "1";
        $formFields[0]['value'] = $subject->overview;
        $formFields[1]['name'] = "submit";
        $formFields[1]['type'] = "Button";
        $formFields[1]['multiple'] = "";
        $formFields[1]['label'] = "Save Changes";
        $formFields[1]['description'] = "";
        $formFields[1]['isRequired'] = "0";
        $formFields[1]['value'] = '';
        $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
    }
    $subject->overview = $_POST['overview'];
    $subject->save();
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Classroom overview saved successfully.'))));
  }
  public function overviewAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    $subject = Engine_Api::_()->core()->getSubject();
    $editOverview = $subject->authorization()->isAllowed($viewer, 'edit');
    if (!$editOverview && (!$subject->overview || is_null($subject->overview))) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('There are no results that match your search. Please try again.'), 'result' => array()));
    }
    if ($editOverview) {
      if ($subject->overview) {
          $result['button'][0]['name'] = "editoverview";
          $result['button'][0]['lable'] = $this->view->translate("Change Overview");
      } else {
          $result['button'][0]['name'] = "editoverview";
          $result['button'][0]['lable'] = $this->view->translate("Add Overview");
      }
    }
    if ($subject->overview) {
        $result['overview'] = $subject->overview;
    } else {
        $result['overview'] = $this->view->translate("There is currently no overview.");
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
  }
  public function viewAction()
  {
    $classroom_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('classroom_id', null);
    if (!Engine_Api::_()->core()->hasSubject()) {
        $classroom = Engine_Api::_()->getItem('classroom', $classroom_id);
    } else {
        $classroom = Engine_Api::_()->core()->getSubject();
    }
    if(!$classroom)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer = ( $viewer && $viewer->getIdentity() ? $viewer : null );
    if (!$this->_helper->requireAuth()->setAuthParams($classroom, $viewer, 'view')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    $result = $this->getclassroom($classroom);
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
  }
  function likeAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if ($viewer_id == 0) {
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    $type = $this->_getParam('type', false);
   if($type == 'eclassroom_photo') {
      $dbTable = 'photos';
      $resorces_id = 'photo_id';
      $notificationType = 'eclassroom_photo_like';
    } elseif($type == 'eclassroom_album') {
      $dbTable = 'albums';
      $resorces_id = 'album_id';
      $notificationType = 'eclassroom_album_like';
    } else {
      $dbTable = 'classrooms';
      $resorces_id = 'classroom_id';
      $notificationType = 'eclassroom_classroom_like';
      $type = "classroom";
    }

    $item_id = $this->_getParam('id',$this->_getParam('classroom_id',0));
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $itemTable = Engine_Api::_()->getDbTable($dbTable, 'eclassroom');
    $tableLike = Engine_Api::_()->getDbTable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $select = $tableLike->select()
            ->from($tableMainLike)
            ->where('resource_type = ?', $type)
            ->where('poster_id = ?', $viewer_id)
            ->where('poster_type = ?', 'user')
            ->where('resource_id = ?', $item_id);
    $result = $tableLike->fetchRow($select);
    if (count($result) > 0) {
      //delete
      $db = $result->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $result->delete();
        $db->commit();
        $temp['data']['message'] = $this->view->translate('Classroom Successfully Unliked.');
      } catch (Exception $e) {
        $db->rollBack();
        $temp['data']['message'] = $this->view->translate('Database Error.');
      }
      $item = Engine_Api::_()->getItem($type, $item_id);
      $owner = $item->getOwner();
      if(!empty($notificationType)) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      }
      $temp['data']['like_count'] = $item->like_count;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $like = $tableLike->createRow();
        $like->poster_id = $viewer_id;
        $like->resource_type = $type;
        $like->resource_id = $item_id;
        $like->poster_type = 'user';
        $like->save();
        $itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array($resorces_id . '= ?' => $item_id));
        $temp['data']['message'] = $this->view->translate('Classroom Successfully Liked.');
        //Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        $temp['data']['message'] = $this->view->translate('Database Error.');
      }
      //Send notification and activity feed work.
      $item = Engine_Api::_()->getItem($type, $item_id);
      $subject = $item;
      $owner = $subject->getOwner();
      if ($notificationType && $owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
        $activityTable = Engine_Api::_()->getDbTable('actions', 'activity');
        //Send to all joined members
        if($type == 'classroom') {
          if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_like','notification_type'=>'site_notification', 'user_id' => $owner->getIdentity()))) {
            Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          }
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
          // Classroom admin notifications and email work
          $getAllClassroomAdmins = Engine_Api::_()->getDbTable('classroomroles', 'eclassroom')->getAllClassroomAdmins(array('classroom_id' => $item->getIdentity(), 'user_id' => $item->owner_id));
          foreach($getAllClassroomAdmins as $getAllClassroomAdmin) {
            $classroomadmin = Engine_Api::_()->getItem('user', $getAllClassroomAdmin->user_id);
            if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_like','notification_type'=>'site_notification', 'user_id' => $classroomadmin->getIdentity()))) {
              Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
            }
          }
          $joinedMembers = Engine_Api::_()->eclassroom()->getallJoinedMembers($item);
          foreach($joinedMembers as $joinedMember) {
            $joinedMember = Engine_Api::_()->getItem('user', $joinedMember->user_id);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, $viewer, $subject, 'eclassroom_classroom_bsjoinlike');

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'eclassroom_classroom_bsjoinlike', array('classroom_name' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
          }

          $followerMembers = Engine_Api::_()->getDbTable('followers', 'eclassroom')->getFollowers($item->getIdentity());
          foreach($followerMembers as $followerMember) {
            $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($followerMember, $viewer, $subject, 'eclassroom_classroom_bsfollwlike');

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($followerMember, 'eclassroom_classroom_bsfollwlike', array('classroom_name' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
          }

          Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'eclassroom_classroom_like', array('classroom_name' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        } else {
          Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
        }
        if($notificationType == 'eclassroom_classroom_like') {
          $action = $activityTable->addActivity($viewer, $subject, $notificationType);
          if ($action)
            $activityTable->attachActivity($action, $subject);
        } else if($notificationType == 'eclassroom_album_like') { 
          $classroom = Engine_Api::_()->getItem('classroom', $subject->classroom_id);
          $albumlink = '<a href="' . $subject->getHref() . '">' . 'album' . '</a>';
          $classroomlink = '<a href="' . $classroom->getHref() . '">' . $classroom->getTitle() . '</a>';
          $action = $activityTable->addActivity($viewer, $subject, $notificationType, null, array('albumlink' => $albumlink, 'classroomname' => $classroomlink));
          if ($action)
            $activityTable->attachActivity($action, $subject);
        } else if($notificationType == 'eclassroom_photo_like') {
          $classroom = Engine_Api::_()->getItem('classroom', $subject->classroom_id);
          $photolink = '<a href="' . $subject->getHref() . '">' . 'photo' . '</a>';
          $classroomlink = '<a href="' . $classroom->getHref() . '">' . $classroom->getTitle() . '</a>';
          $action = $activityTable->addActivity($viewer, $subject, $notificationType, null, array('photolink' => $photolink, 'classroomname' => $classroomlink));
          if ($action)
            $activityTable->attachActivity($action, $subject);
        }
      }
      $temp['data']['like_count'] = $item->like_count;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    }
  }
  //item favourite as per item tye given
  function favouriteAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($viewer->getIdentity() == 0) {
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    if ($this->_getParam('type') == 'eclassroom_photo') {
      $type = 'eclassroom_photo';
      $dbTable = 'photos';
      $resorces_id = 'photo_id';
      $notificationType = 'eclassroom_photo';
    } elseif ($this->_getParam('type') == 'eclassroom_album') {
      $type = 'eclassroom_album';
      $dbTable = 'albums';
      $resorces_id = 'album_id';
      $notificationType = 'eclassroom_album';
    } else {
      $type = 'classroom';
      $dbTable = 'classrooms';
      $resorces_id = 'classroom_id';
      $notificationType = 'eclassroom_classroom_favourite';
    }
    $item_id = $this->_getParam('id',$this->_getParam('classroom_id',0));
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $Fav = Engine_Api::_()->getDbTable('favourites', 'eclassroom')->getItemfav($type, $item_id);
    $favItem = Engine_Api::_()->getDbTable($dbTable, 'eclassroom');
    if (count($Fav) > 0) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
        $temp['data']['message'] = $this->view->translate('Classroom Successfully Unfavourited.');
      } catch (Exception $e) {
        $db->rollBack();
        $temp['data']['message'] = $this->view->translate('Database Error.');
      }
      $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
      $item = Engine_Api::_()->getItem($type, $item_id);
      if($notificationType) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
        Engine_Api::_()->sesapi()->deleteFeed(array('type' => $notificationType, "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
      }
      $temp['data']['favourite_count'] = $item->favourite_count;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('favourites', 'eclassroom')->getAdapter();
      $db->beginTransaction();
      try {
        $fav = Engine_Api::_()->getDbTable('favourites', 'eclassroom')->createRow();
        $fav->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $fav->resource_type = $type;
        $fav->resource_id = $item_id;
        $fav->save();
        $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1')), array($resorces_id . '= ?' => $item_id));
        $temp['data']['message'] = $this->view->translate('Classroom Successfully Favourited.');
        // Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        $temp['data']['message'] = $this->view->translate('Database Error.');
      }
      //Send Notification and Activity Feed Work.
      $item = Engine_Api::_()->getItem(@$type, @$item_id);
      if (@$notificationType) {
        $subject = $item;
        $owner = $subject->getOwner();
        if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && @$notificationType) {
          $activityTable = Engine_Api::_()->getDbTable('actions', 'activity');
          $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          if (!$result) {
            $action = $activityTable->addActivity($viewer, $subject, $notificationType);
            if ($action)
              $activityTable->attachActivity($action, $subject);
          }
          if($type == 'classrooms') {
            if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_favourite','notification_type'=>'site_notification', 'user_id' => $item->getOwner()->getIdentity()))) {
              Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
            }
            if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_favourite','notification_type'=>'email_notification', 'user_id' => $item->getOwner()->getIdentity()))) {
              Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_eclassroom_classroom_classroomfollowed', array('classroom_name' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            // Classroom admin notifications and email work
            $getAllClassroomAdmins = Engine_Api::_()->getDbTable('classroomroles', 'eclassroom')->getAllClassroomAdmins(array('classroom_id' => $item->getIdentity(), 'user_id' => $item->owner_id));
            foreach($getAllClassroomAdmins as $getAllClassroomAdmin) {
              $classroomadmin = Engine_Api::_()->getItem('user', $getAllClassroomAdmin->user_id);
              if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_favourite','notification_type'=>'site_notification', 'user_id' => $classroomadmin->getIdentity()))) {
                Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
              }
              if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_favourite','notification_type'=>'email_notification', 'user_id' => $classroomadmin->getIdentity()))) {
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_eclassroom_classroom_classroomfollowed', array('classroom_name' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
              }
            }
          } else {
            Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_eclassroom_classroom_classroomfollowed', array('classroom_name' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
          }
        }
      }
      $temp['data']['favourite_count'] = $item->favourite_count;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    }
  }
  function followAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($viewer->getIdentity() == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    $item_id = $this->_getParam('id',$this->_getParam('classroom_id',0));
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $Fav = Engine_Api::_()->getDbTable('followers', 'eclassroom')->getItemFollower('classroom', $item_id);
    $followerItem = Engine_Api::_()->getDbTable('classrooms', 'eclassroom');
    if (count($Fav) > 0) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
        $temp['data']['message'] = $this->view->translate('Classroom Successfully Unfollowed.');
      } catch (Exception $e) {
        $db->rollBack();
        $temp['data']['message'] = $e->getMessage();
      }
      $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count - 1')), array('classroom_id = ?' => $item_id));
      $item = Engine_Api::_()->getItem('classroom', $item_id);
      Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'eclassroom_classroom_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->sesapi()->deleteFeed(array('type' => 'eclassroom_classroom_follow', "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
      $temp['data']['follow_count'] = $item->follow_count;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('followers', 'eclassroom')->getAdapter();
      $db->beginTransaction();
      try {
        $follow = Engine_Api::_()->getDbTable('followers', 'eclassroom')->createRow();
        $follow->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $follow->resource_type = 'classroom';
        $follow->resource_id = $item_id;
        $follow->save();
        $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count + 1')), array('classroom_id = ?' => $item_id));
        $temp['data']['message'] = $this->view->translate('Classroom Successfully Followed.');
        // Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        $temp['data']['message'] = 'Database Error.';
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem('classroom', @$item_id);
      $owner = $item->getOwner();
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
        $activityTable = Engine_Api::_()->getDbTable('actions', 'activity');
        $result = $activityTable->fetchRow(array('type =?' => 'eclassroom_classroom_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
        if (!$result) {
          $action = $activityTable->addActivity($viewer, $item, 'eclassroom_classroom_follow');
          if ($action)
            $activityTable->attachActivity($action, $item);
        }
        if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_follow','notification_type'=>'site_notification', 'user_id' => $item->getOwner()->getIdentity()))) {
          Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item->getOwner(), $viewer, $item, 'eclassroom_classroom_follow');
        }
        if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_follow','notification_type'=>'email_notification', 'user_id' => $item->getOwner()->getIdentity()))) {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'eclassroom_classroom_follow', array('classroom_name' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        }
        // Classroom admin notifications and email work
        $getAllClassroomAdmins = Engine_Api::_()->getDbTable('classroomroles', 'eclassroom')->getAllClassroomAdmins(array('classroom_id' => $item->getIdentity(), 'user_id' => $item->owner_id));
        foreach($getAllClassroomAdmins as $getAllClassroomAdmin) {
          $classroomadmin = Engine_Api::_()->getItem('user', $getAllClassroomAdmin->user_id);
          if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_follow','notification_type'=>'site_notification', 'user_id' => $classroomadmin->getIdentity()))) {
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($classroomadmin, $viewer, $item, 'eclassroom_classroom_follow');
          }
          if(Engine_Api::_()->getDbTable('notifications','eclassroom')->getNotifications(array('classroom_id'=>$item->getIdentity(), 'type'=>'notification_type','role'=>'new_follow','notification_type'=>'email_notification', 'user_id' => $classroomadmin->getIdentity()))) {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($classroomadmin, 'eclassroom_classroom_follow', array('classroom_name' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
          }
        }
      }
      $temp['data']['follow_count'] = $item->follow_count;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    }
  }
  public function getclassroom($classroom)
  {
      $classroomdata = $classroom->toArray();
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewerId = $viewer->getIdentity();
      $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      // Get category
      if (!empty($classroom->category_id)) {
          $category = Engine_Api::_()->getDbtable('categories', 'eclassroom')->find($classroom->category_id)->current();
      }
      $classroomTags = $classroom->tags()->getTagMaps();
      $likeFollowIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.integration', 0);
      $canComment = $classroom->authorization()->isAllowed($viewer, 'comment');
      $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.share', 1);
      $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.favourite', 1);
      $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.follow', 1);
      $canJoin = Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'bs_can_join');
      $isClassroomEdit = Engine_Api::_()->eclassroom()->privacy($classroom, 'edit');
      $canUploadCover = Engine_Api::_()->authorization()->isAllowed('classroom', $viewer, 'upload_cover');
      $canUploadPhoto = Engine_Api::_()->authorization()->isAllowed('classroom', $viewer, 'upload_mainphoto');

      $isClassroomDelete = Engine_Api::_()->eclassroom()->privacy($classroom, 'delete');
      $likeStatus = Engine_Api::_()->eclassroom()->getLikeStatus($classroom->classroom_id, $classroom->getType());
      $followStatus = Engine_Api::_()->getDbTable('followers', 'eclassroom')->isFollow(array('resource_id' => $classroom->classroom_id, 'resource_type' => $classroom->getType()));
      $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'eclassroom')->isFavourite(array('resource_id' => $classroom->classroom_id, 'resource_type' => $classroom->getType()));
      $owner = $classroom->getOwner();
      $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_show_userdetail', 0);
      if(!$hideIdentity)
      $classroomdata['owner_title'] = $classroom->getOwner()->getTitle();
      $classroomdata['rating'] = round($classroom->rating, 2);
      $classroomdata['likeFollowIntegrate'] = $likeFollowIntegrate ? true : false;
      if ($likeStatus && $viewer_id) {
          $classroomdata['is_content_like'] = true;
      } else {
          $classroomdata['is_content_like'] = false;
      }
      if($canFollow){
        $classroomdata['is_content_follow'] = $followStatus > 0 ? true : false;
      }
      if($canFavourite){
        $classroomdata['is_content_favourite'] = $favouriteStatus >0 ? true : false;
      }
      $classroomdata['can_contact'] = ($classroom->owner_id == $viewer_id) ? false : true;
      if ($classroom->category_id) {
          $category = Engine_Api::_()->getItem('eclassroom_category', $classroom->category_id);
          if ($category) {
              $classroomdata['category_title'] = $category->category_name;
              if ($classroom->subcat_id) {
                  $subcat = Engine_Api::_()->getItem('eclassroom_category', $classroom->subcat_id);
                  if ($subcat) {
                      $classroomdata['subcategory_title'] = $subcat->category_name;
                      if ($classroom->subsubcat_id) {
                          $subsubcat = Engine_Api::_()->getItem('eclassroom_category', $classroom->subsubcat_id);
                          if ($subsubcat) {
                              $classroomdata['subsubcategory_title'] = $subsubcat->category_name;
                          }
                      }
                  }
              }
          }
      }
      $item = Engine_Api::_()->getItem('classroom', $classroom->classroom_id);
      $joinedMembers = Engine_Api::_()->eclassroom()->getallJoinedMembers($item);
      $memberCount = count($joinedMembers);
      $classroomdata['memberCount'] = $memberCount;
      $tags = array();
      foreach ($classroom->tags()->getTagMaps() as $tagmap) {
          $arrayTag = $tagmap->toArray();
          if(!$tagmap->getTag())
              continue;
          $tags[] = array_merge($tagmap->toArray(), array(
              'id' => $tagmap->getIdentity(),
              'text' => $tagmap->getTitle(),
              'href' => $tagmap->getHref(),
              'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
          ));
      }
      if (count($tags)) {
          $classroomdata['tag'] = $tags;
      }
      $classroomdata['images']['main'] = $this->getBaseUrl(true, $classroom->getPhotoUrl());
      $classroomdata['cover_image']['main'] = $this->getBaseUrl(true, $classroom->getCoverPhotoUrl());
      $classroomdata['cover_images']['main'] = $classroomdata['cover_image']['main'];
      $showLoginformFalse = false;
      if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.contact.details', 1) && $viewerId == 0) {
          $showLoginformFalse = true;
      }
      $l = 0;
      if ($classroom->classroom_contact_email || $classroom->classroom_contact_phone || $classroom->classroom_contact_website) {
        if ($classroom->classroom_contact_email) {
            $classroomdata['menus'][$l]['name'] = 'mail';
            $classroomdata['menus'][$l]['label'] = 'Send Email';
            $classroomdata['menus'][$l]['value'] = $classroom->classroom_contact_email;
            $l++;
        }
        if ($classroom->classroom_contact_phone) {
            $classroomdata['menus'][$l]['name'] = 'phone';
            $classroomdata['menus'][$l]['label'] = 'Call';
            $classroomdata['menus'][$l]['value'] = $classroom->classroom_contact_phone;
            $l++;
        }
        if ($classroom->classroom_contact_website) {
            $classroomdata['menus'][$l]['name'] = 'website';
            $classroomdata['menus'][$l]['label'] = 'Visit Website';
            $classroomdata['menus'][$l]['value'] = $classroom->classroom_contact_website;
            $l++;
        }
        $classroomdata['showLoginForm'] = $showLoginformFalse;
      }
      $canCall = Engine_Api::_()->getDbTable('callactions', 'eclassroom')->getCallactions(array('classroom_id' => $classroom->getIdentity()));
      if ($canCall) {
          $result['callToAction']['label'] = $this->getType($canCall->type);
          if ($canCall->type == 'callnow') {
              $result['callToAction']['name'] = 'call';
              $result['callToAction']['value'] = $canCall->params;
          } else if ($canCall->type == 'sendmessage') {
              $result['callToAction']['name'] = 'message';
              $result['callToAction']['value'] = $canCall->params;
              $result['callToAction']['owner_id'] = $canCall->owner_id;
              $result['callToAction']['owner_title'] = Engine_Api::_()->getItem('user',$canCall->owner_id)->getTitle();
          }elseif ($canCall->type == 'sendemail') {
              $result['callToAction']['name'] = 'mail';
              $result['callToAction']['value'] = $canCall->params;
          }else{
              $result['callToAction']['name'] = $canCall->type;
              $result['callToAction']['value'] = $canCall->params;
          }
      }
      $classroomdata['is_feed_allowed'] = true;
      if( !$classroom->authorization()->isAllowed($this->view->viewer(), 'view') )
          $classroomdata['is_feed_allowed'] = false;
      $i = 0;
      if ($classroom->classroom_contact_email || $classroom->classroom_contact_phone || $classroom->classroom_contact_website) {
        if ($classroom->classroom_contact_email) {

            $result['about'][$i]['name'] = 'mail';
            $result['about'][$i]['label'] = 'Send Email';
            $result['about'][$i]['value'] = $classroom->classroom_contact_email;
            $i++;

        }
        if ($classroom->classroom_contact_phone) {
            $result['about'][$i]['name'] = 'phone';
            $result['about'][$i]['label'] = 'View Phone number';
            $result['about'][$i]['value'] = $classroom->classroom_contact_phone;
            $i++;
        }
        if ($classroom->classroom_contact_website) {

            $result['about'][$i]['name'] = 'website';
            $result['about'][$i]['label'] = 'Visit Website';
            $result['about'][$i]['value'] = $classroom->classroom_contact_website;
            $i++;
        }
        if ($classroom->creation_date) {
            $result['about'][$i]['name'] = 'createDate';
            $result['about'][$i]['label'] = 'Create Date';
            $result['about'][$i]['value'] = $classroom->creation_date;
            $i++;
        }
        if ($classroom->category_id) {
            $category = Engine_Api::_()->getItem('eclassroom_category', $classroom->category_id);
            if ($category) {
                $result['about'][$i]['name'] = 'category';
                $result['about'][$i]['label'] = 'Category Title';
                $result['about'][$i]['value'] = $category->category_name;
            }
            $i++;
        }
        if (count($tags)) {
            $result['about'][$i]['name'] = 'tag';
            $result['about'][$i]['value'] = 'Tag';
            $i++;
        }
        $result['about'][$i]['name'] = 'seeall';
        $result['about'][$i]['value'] = 'See All';
        $result['showLoginForm'] = $showLoginformFalse;
    }
    $relatedParams['category_id'] = $classroom->category_id;
    $relatedParams['notClassroomId'] = $classroom->classroom_id;
    if ($classrooms = $this->relatedclassrooms($relatedParams)) {
        $result['relatedClassrooms'] = $classrooms;
    }
    $result['photo'] = $this->photo($classroom->classroom_id);
    if ($classroom->is_approved) {
        if ($shareType) {
            $classroomdata["share"]["imageUrl"] = $this->getBaseUrl(false, $classroom->getPhotoUrl());
            $classroomdata["share"]["url"] = $this->getBaseUrl(false,$classroom->getHref());
            $classroomdata["share"]["title"] = $classroom->getTitle();
            $classroomdata["share"]["description"] = strip_tags($classroom->getDescription());
            $classroomdata["share"]["setting"] = $shareType;
            $classroomdata["share"]['urlParams'] = array(
                "type" => $classroom->getType(),
                "id" => $classroom->getIdentity()
            );
        }
    } 
    $m = 0;
    if ($classroom->is_approved) {
        if($viewerId != $classroom->owner_id) {
            $classroomdata['menus'][$m]['name'] = 'contact';
            $classroomdata['menus'][$m]['label'] = 'Contact';
            $m++;
        }
        if ($shareType) {
            $classroomdata['menus'][$m]['name'] = 'share';
            $classroomdata['menus'][$m]['label'] = 'Share';
            $m++;
        }
        $result['showloginform_for_join_share'] = !$viewerId ? true : false;
        if ($canJoin) {
            $joincounter = 0;
            // if ($viewerId) {
                //                    $m++;
                $row = $classroom->membership()->getRow($viewer);
                if (null === $row) {
                    if ($classroom->membership()->isResourceApprovalRequired()) {
                        $classroomdata['join'][$joincounter]['name'] = 'request';
                        $classroomdata['join'][$joincounter]['label'] = 'Request Membership';
                        $joincounter++;

                    } else {
                        $classroomdata['join'][$joincounter]['name'] = 'join';
                        $classroomdata['join'][$joincounter]['label'] = 'Join Classroom';
                        $joincounter++;
                    }
                } else if ($row->active) {
                    if (!$classroom->isOwner($viewer)) {
                        $classroomdata['join'][$joincounter]['label'] = 'Leave Classroom';
                        $classroomdata['join'][$joincounter]['name'] = 'leave';
                        $joincounter++;
                    }
                } else if (!$row->resource_approved && $row->user_approved) {
                    $classroomdata['join'][$joincounter]['label'] = 'Cancel Membership Request';
                    $classroomdata['join'][$joincounter]['name'] = 'cancel';
                    $joincounter++;

                } else if (!$row->user_approved && $row->resource_approved) {
                    $classroomdata['join'][$joincounter]['label'] = 'Accept Membership Request';
                    $classroomdata['join'][$joincounter]['name'] = 'accept';
                    $joincounter++;
                    $classroomdata['join'][$joincounter]['label'] = 'Ignore Membership Request';
                    $classroomdata['join'][$joincounter]['name'] = 'reject';
                    $joincounter++;
                }
            // }
        }
    }
    if ($viewer->getIdentity() != 0) {
        $classroomdata['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($classroom);
        $classroomdata['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($classroom);
        if ($canFavourite) {
            $classroomdata['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($classroom, 'favourites', 'eclassroom', 'classroom', 'owner_id');
            $classroomdata['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($classroom, 'favourites', 'eclassroom', 'classroom', 'owner_id');
        }
        if ($canFollow) {
            $classroomdata['is_content_follow'] = $this->contentFollow($classroom, 'followers', 'eclassroom', 'classroom', 'owner_id');
            $classroomdata['content_follow_count'] = (int)$this->getContentFollowCount($classroom, 'favourites', 'eclassroom', 'classroom', 'owner_id');
        }
    }
    if ($classroom->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.location', 1)) {
        unset($classroom['location']);
        $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('classroom', $classroom->getIdentity());
        if ($location) {
            $classroomdata['location'] = $location->toArray();
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.map.integration', 1)) {
                $classroomdata['location']['showMap'] = true;
            } else {
                $classroomdata['location']['showMap'] = false;
            }
        }
    }
    if ($isClassroomDelete) {
        $classroomdata['classroomDelete'] = true;
    } else {
        $classroomdata['classroomDelete'] = false;
    }
    if ($isClassroomEdit) {
        // cover photo upload
        if ($canUploadCover) {
            $i = 0;
            if (isset($classroom->cover) && $classroom->cover != 0 && $classroom->cover != '') {
                $classroomdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Change Cover Photo');
                $classroomdata['updateCoverPhoto'][$i]['name'] = 'upload';
                $i++;
                $classroomdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Remove Cover Photo');
                $classroomdata['updateCoverPhoto'][$i]['name'] = 'removePhoto';
                $i++;
            } else {
                $classroomdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Upload Cover Photo');
                $classroomdata['updateCoverPhoto'][$i]['name'] = 'upload';
                $i++;
            }
        }
      if($canUploadPhoto){
        $j = 0;
        if (!empty($classroom->photo_id)) {
            $classroomdata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Change Photo');
            $classroomdata['updateProfilePhoto'][$j]['name'] = 'upload';
            $j++;
            $classroomdata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Remove Photo');
            $classroomdata['updateProfilePhoto'][$j]['name'] = 'removePhoto';
        } else {
            $classroomdata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Upload Photo');
            $classroomdata['updateProfilePhoto'][$j]['name'] = 'upload';
            $j++;
        }
      }
    }
    //navigation
    $result['options'] = $this->getNavigation($classroom,$viewer);
    $tabcounter = 0;
    $result['menus'][$tabcounter]['name'] = 'posts';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Posts');
    $tabcounter++;
    if(($classroom instanceof Core_Model_Item_Abstract) && $classroom->getIdentity() && method_exists($classroom, 'comments') && method_exists($classroom, 'likes')) {
        $result['menus'][$tabcounter]['name'] = 'comments';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Comments');
        $tabcounter++;
    }
    $result['menus'][$tabcounter]['name'] = 'info';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Info');
    $tabcounter++;
    $result['menus'][$tabcounter]['name'] = 'album';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Albums');
    $tabcounter++;
    $result['menus'][$tabcounter]['name'] = 'course';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Courses');
    $tabcounter++;
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.location', 1)){
        $result['menus'][$tabcounter]['name'] = 'map';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Locations');
        $tabcounter++;
    }
    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('eclassroomvideo')) {
        //custom change video Tab disable
      $result['menus'][$tabcounter]['name'] = 'video';
      $result['menus'][$tabcounter]['label'] = $this->view->translate('Videos');
      $tabcounter++;
    }
    if(Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'auth_subclassroom') ) {
        $result['menus'][$tabcounter]['name'] = 'associateClassrooms';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Associated Classrooms');
        $tabcounter++;
    }
    $classroom_allow_announcement = Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'classroom_allow_announcement');
    $classroom_service = Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'classroom_service');
    $classroom_overview = Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'bs_overview');
    //if ($classroom_allow_announcement) {
        $result['menus'][$tabcounter]['name'] = 'announcements';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Announcements');
        $tabcounter++;
    //}
    $result['menus'][$tabcounter]['name'] = 'members';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Members');
    $tabcounter++;
    if ($classroom_service && Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.service', 0)) {
        $result['menus'][$tabcounter]['name'] = 'services';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Services');
        $tabcounter++;
    }
    if ($classroom_overview) {
        $result['menus'][$tabcounter]['name'] = 'overview';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Overview');
        $tabcounter++;
    }
		if($viewer->getIdentity() > 0 && !$classroom->isOwner($viewer) && Engine_Api::_()->authorization()->getPermission($viewer, 'classroom', 'auth_claim') && (_SESAPI_VERSION_ANDROID >= 2.4 || _SESAPI_VERSION_IOS >= 1.5)){
			$result['menus'][$tabcounter]['name'] = 'claim';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Claim Classroom');
			$tabcounter++;
		}
    $result['menus'][$tabcounter]['name'] = 'review';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Reviews');
    $tabcounter++;
    $result['classroom'] = $classroomdata;
    $result = $result;
    return $result;
  }
  public function relatedclassrooms($params)
  {
    $paginator = Engine_Api::_()->getDbTable('classrooms', 'eclassroom')
        ->getClassroomPaginator($params);
    $paginator->setItemCountPerPage($this->_getParam('limit', 5));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $result = $this->getClassrooms($paginator);
    return $result;
  }
  public function photo($classroomid)
  {
    $params['classroom_id'] = $classroomid;
    $paginator = Engine_Api::_()->getDbTable('photos', 'eclassroom')
        ->getPhotoPaginator($params);
    $paginator->setItemCountPerPage(5);
    $paginator->setCurrentPageNumber(1);
    $i = 0;
    foreach ($paginator as $photos) {
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");
      if (!count($images)) {
        $images['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Eclassroom/externals/images/nophoto_classroom_thumb_profile.png';
        $images['normal'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Eclassroom/externals/images/nophoto_classroom_thumb_profile.png';
      }
      $result[$i]['images'] = $images;
      $result[$i]['photo_id'] = $photos->getIdentity();
      $result[$i]['album_id'] = $photos->album_id;
      $i++;
    }
    return $result;
  }
  function getNavigation($classroom,$viewer){
    $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('eclassroom_profile');
    $navigationCounter = 0;
    $viewerId = $viewer->getIdentity();
    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    $canJoin = Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'bs_can_join');
    foreach ($navigation as $link) {
        $class = end(explode(' ', $link->class));
        $label = $this->view->translate($link->getLabel());
        if ($class != "eclassroom_profile_addtoshortcut") {
          $action = 'cancel';
          if ($class == 'eclassroom_profile_dashboard') {
              $label = $label;
              $action = 'dashboard';
              $baseurl = $this->getBaseUrl();
              $custumurl = $classroom->custom_url;
              $pluralurl = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.singular.manifest', 'classroom');
              if ($pluralurl) {
                  $url = $baseurl . $pluralurl . '-dashboard/edit/' . $custumurl;
              } else {
                  $url = $baseurl . 'dashboard/edit/' . $custumurl;
              }
              $value = $url;
          } elseif ($class == 'eclassroom_profile_member') {
            $row = $classroom->membership()->getRow($viewer);
            if (null === $row) {
            if ($classroom->membership()->isResourceApprovalRequired()) {
                $action = 'request';
              } else {
                $action = 'join';
              }
            } else if ($row->active) {
              if (!$classroom->isOwner($viewer)) {
                $action = 'leave';
              }
            }
          } elseif ($class == 'eclassroom_profile_invite') {
              $action = 'invite';
          } elseif ($class == 'eclassroom_profile_report' && $viewer->getIdentity() != $classroom->owner_id) {
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.report', 1) )
            continue;
            $action = 'report';
          } elseif ($class  == 'eclassroom_profile_share') {
              $action = 'share';
          } elseif ($class  == 'eclassroom_profile_member') {
              $action = 'join';
          } elseif ($class == 'eclassroom_profile_delete') {
              $action = 'delete';
          } elseif ($class == 'eclassroom_profile_like' && $viewer->getIdentity() != $classroom->owner_id) {
              $action = 'likeasyourclassroom';
          } elseif ($class == 'eclassroom_profile_unlike' && $viewer->getIdentity() != $classroom->owner_id) {
              $action = 'unlikeasyourclassroom';
          } elseif ($class  == 'eclassroom_icon_classroom_accept') {
              $action = 'accept';
          } elseif ($class  == 'eclassroom_icon_classroom_reject') {
              $action = 'reject';
          }
          if ($class == 'eclassroom_profile_dashboard') {
              $result[$navigationCounter]['label'] = $label;
              $result[$navigationCounter]['name'] = $action;
              $result[$navigationCounter]['value'] = $value;
              $navigationCounter++;
            if($this->_helper->requireAuth()->setAuthParams('classroom', null, 'edit')->isValid()){
              $result[$navigationCounter]['label'] = $this->view->translate('Edit Classroom');
                    $result[$navigationCounter]['name'] = 'edit';
                    $navigationCounter++;
            }
          }elseif($class == 'eclassroom_profile_delete'){
            if(!$this->_helper->requireAuth()->setAuthParams('classroom', null, 'delete')->isValid())
              continue;
            $result[$navigationCounter]['label'] = $label;
            $result[$navigationCounter]['name'] = $action;
            $navigationCounter++;
          } else {
              $result[$navigationCounter]['label'] = $label;
              $result[$navigationCounter]['name'] = $action;
              $navigationCounter++;
          }
      }
    }
    return $result;
  }
  public function reviewAction(){
		$classroomId = $this->_getParam('classroom_id');
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$classroomId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$classroom = Engine_Api::_()->core()->getSubject();
		}else{
			$classroom = Engine_Api::_()->getItem('classroom',$classroomId);
		}
		if(!$classroom){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.review', 1)){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		if (!Engine_Api::_()->sesapi()->getViewerPrivacy('eclass_review', 'view'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.owner.review', 1)) {
			$allowedCreate = true;
		} else {
			if ($classroom->classroom_id == $viewer->getIdentity())
				$allowedCreate = false;
			else
				$allowedCreate = true;
		}
		$cancreate = Engine_Api::_()->sesapi()->getViewerPrivacy('eclass_review', 'create');
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$editReviewPrivacy = Engine_Api::_()->sesapi()->getViewerPrivacy('eclass_review', 'edit');
		$reviewTable = Engine_Api::_()->getDbtable('reviews', 'eclassroom');
		$isReview = $hasReview = (int) $reviewTable->isReview(array('classroom_id' => $classroom->getIdentity(), 'column_name' => 'review_id'));
		if($viewer->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.review', 1) && $allowedCreate){
			if($cancreate && !$isReview){
				$result['button']['label'] = $this->view->translate('Write a Review');
				$result['button']['name'] = 'createreview';
			}
			if($editReviewPrivacy && $isReview){
				$result['button']['label'] = $this->view->translate('Update Review');
				$result['button']['name'] = 'updatereview';
			}
		}
		$table = Engine_Api::_()->getItemTable('eclassroom_review');
		$classroom_id = $classroom->getIdentity();
		$params['classroom_id'] = $classroom_id;
		$select = $table->getClassroomReviewSelect($params);
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		$result['reviews'] = $this->getReviews($paginator,$classroom);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	public function reviewCreateAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
		$classroomId = $this->_getParam('classroom_id');
		$viewer = Engine_Api::_()->user()->getViewer();
    if(empty($classroomId))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$classroom = Engine_Api::_()->core()->getSubject();
		}else{
			$classroom = Engine_Api::_()->getItem('classroom',$classroomId);
		}
		if(!$classroom){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
    //check review exists
    $isReview = $hasReview = Engine_Api::_()->getDbtable('reviews', 'eclassroom')->isReview(array('classroom_id' => $classroom->getIdentity(), 'column_name' => 'review_id'));
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.owner.review', 1)) {
        $allowedCreate = true;
    } else {
        if ($classroom->owner_id == $viewer->getIdentity())
            $allowedCreate = false;
        else
            $allowedCreate = true;
    }
    if (($isReview && empty($classroomId)) || !$allowedCreate)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    if ($hasReview && Engine_Api::_()->sesapi()->getViewerPrivacy('eclass_review', 'edit')) {
      $reviewTable = Engine_Api::_()->getDbtable('reviews', 'eclassroom');
      $select = $reviewTable->select()
              ->where('classroom_id = ?', $classroom->getIdentity())
              ->where('owner_id =?', $viewer->getIdentity());
      $reviewObject = $reviewTable->fetchRow($select);
      $form = new Eclassroom_Form_Review_Create(array( 'reviewId' => $reviewObject->review_id, 'classroomItem' =>$classroom));
      $form->populate($reviewObject->toArray());
      $form->rate_value->setvalue($reviewObject->rating);
      $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'eclassroom', 'controller' => 'review', 'action' => 'edit', 'review_id' => $reviewObject->review_id), 'default', true));
    } else {
        $form = new Eclassroom_Form_Review_Create(array('classroomItem' =>$classroom));
    }
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'eclassroom'));
    }
    if (!$this->getRequest()->isPost()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
          $this->validateFormFields($validateFields);
    }
    if($_POST['rate_value'] == "0.0"){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error' => '1', 'error_message' => "Rating required.", 'result' => array()));
    }
  $values = $_POST;
  $values['rating'] = $_POST['rate_value'];
  $values['owner_id'] = $viewer->getIdentity();
  $values['classroom_id'] = $classroom->getIdentity();
  $reviews_table = Engine_Api::_()->getDbtable('reviews', 'eclassroom');
  $db = $reviews_table->getAdapter();
  $db->beginTransaction();
  try {
      $review = $reviews_table->createRow();
      $review->setFromArray($values);
      $review->description = $_POST['description'];
      $review->save();
      $reviewObject = $review;
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      //tak review ids from post
      $table = Engine_Api::_()->getDbtable('parametervalues', 'eclassroom');
      $tablename = $table->info('name');
      foreach ($_POST as $key => $reviewC) {
          if (count(explode('_', $key)) != 3 || !$reviewC)
              continue;
          $key = str_replace('review_parameter_', '', $key);
          if (!is_numeric($key))
              continue;
          $parameter = Engine_Api::_()->getItem('eclassroom_parameter', $key);
          $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $viewer->getIdentity() . '","' . $classroom->getIdentity() . '","' . $review->getIdentity() . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
          $dbObject->query($query);
          $ratingP = $table->getRating($key);
          $parameter->rating = $ratingP;
          $parameter->save();
      }
      $db->commit();
      //save rating in parent table if exists
      if (isset($classroom->rating)) {
          $classroom->rating = Engine_Api::_()->getDbtable('reviews', 'eclassroom')->getRating($review->classroom_id);
          $classroom->save();
      }
      $review->save();
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $classroom, 'eclassroom_reviewpost');
      if ($action != null) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $review);
      }
      if ($classroom->owner_id != $viewer->getIdentity()) {
          $itemOwner = $classroom->getOwner('user');
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($itemOwner, $viewer, $review, 'eclassroom_reviewpost');
      }
      $db->commit();
      $stats = Engine_Api::_()->eclassroom()->getWidgetParams($viewer->getIdentity());
      $this->view->stats = count($stats) ? $stats : $this->_getParam('stats', array('featured', 'sponsored', 'likeCount', 'commentCount', 'viewCount', 'title', 'postedBy', 'pros', 'cons', 'description', 'creationDate', 'recommended', 'parameter', 'rating'));
      if (Engine_Api::_()->sesapi()->getViewerPrivacy('eclass_review', 'edit')) {
          $form = new Eclassroom_Form_Review_Create(array( 'reviewId' => $reviewObject->review_id, 'classroomItem' => $classroom));
          $form->populate($reviewObject->toArray());
          $form->rate_value->setvalue($reviewObject->rating);
          $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'eclassroom', 'controller' => 'review', 'action' => 'edit', 'review_id' => $reviewObject->review_id), 'default', true));
      }
      $this->view->rating_count = Engine_Api::_()->getDbTable('reviews', 'eclassroom')->ratingCount($classroom->getIdentity());
      $this->view->rating_sum = $userInfoItem->rating;
			$msg = $isReview ? $this->view->translate('Review edited successfully.') : $this->view->translate('Review created successfully.');
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('review_id' => $review->getIdentity(), 'message' =>$msg)));
        } catch (Exception $e) {
            $db->rollBack();
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function getReviews($paginator,$classroom){
		$counter = 0;
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		foreach($paginator as $item){
			$result[$counter] = $item->toArray();
			$owner = $item->getOwner();
			$result[$counter]['classroom']['images'] = $this->getBaseUrl(true, $classroom->getPhotoUrl());
			$result[$counter]['classroom']['title'] = $classroom->getTitle();
			$result[$counter]['classroom']['Guid'] = $classroom->getGuid();
			$result[$counter]['classroom']['id'] = $classroom->getIdentity();
			$result[$counter]['owner']['id'] = $owner->getIdentity();
			$result[$counter]['owner']['Guid'] = $owner->getGuid();
			$result[$counter]['owner']['title'] = $owner->getTitle();
			$result[$counter]['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
			$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'eclassroom')->getParameters(array('content_id'=>$item->getIdentity(),'user_id'=>$item->owner_id));
			$perameterCounter = 0;
			foreach($reviewParameters as $reviewP){ 
				$result[$counter]['review_perameter'][$perameterCounter] = $reviewP->toArray();
				$perameterCounter++;
			}
			$result[$counter]['can_show_pros']  = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.show.pros', 1) ? true : false;
			$result[$counter]['can_show_cons']  = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.show.cons', 1) ? true : false;
			
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.votes', 1)){
				$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','eclassroom')->isReviewVote(array('review_id'=>$item->getIdentity(),'classroom_id'=>$classroom->getIdentity(),'type'=>1));
				$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','eclassroom')->isReviewVote(array('review_id'=>$item->getIdentity(),'classroom_id'=>$classroom->getIdentity(),'type'=>2));
				$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','eclassroom')->isReviewVote(array('review_id'=>$item->getIdentity(),'classroom_id'=>$classroom->getIdentity(),'type'=>3));
				$voteCounter = 0;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.first', 'Useful'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 1;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypeone ? true : false;
				$voteCounter++;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.second', 'Funny'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 2;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypetwo ? true : false;
				$voteCounter++;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.third', 'Cool'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 3;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypethree ? true : false;
				$voteCounter++;
			}
				$ownerSelf = $viewer->getIdentity() == $item->owner_id ? true : false;
				$counterOption = 0;
				if($item->authorization()->isAllowed($viewer, 'edit')) {
					$result[$counter]['options'][$counterOption]['name'] = 'edit'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Edit Review'); 
					$counterOption++;
				}
				if($item->authorization()->isAllowed($viewer, 'delete')) {
					$result[$counter]['options'][$counterOption]['name'] = 'delete'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Delete Review'); 
					$counterOption++;
				}
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.show.report', 1) && $viewer->getIdentity()){
					$result[$counter]['options'][$counterOption]['name'] = 'report'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Report'); 
					$counterOption++;
				}
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.share', 1) && $viewer->getIdentity()){
					$result[$counter]['options'][$counterOption]['name'] = 'share'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Share Review'); 
					$counterOption++;
				}
			$counter++;
		}
		return $result;
	}
	public function editReviewAction() {
    $review_id = $this->_getParam('review_id', null);
    $subject = Engine_Api::_()->getItem('eclassroom_review', $review_id);
    if (!Engine_Api::_()->sesapi()->getViewerPrivacy('eclass_review', 'edit'))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $item = Engine_Api::_()->getItem('classroom', $subject->classroom_id);
    if (!$review_id || !$subject)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $form = new Eclassroom_Form_Review_Edit(array('reviewId' => $subject->review_id,  'classroomItem' => $item));
    $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'eclassroom', 'controller' => 'review', 'action' => 'edit-review', 'review_id' => $review_id), 'default', true));
    $title = Zend_Registry::get('Zend_Translate')->_('Edit a Review for "<b>%s</b>".');
    $form->setTitle(sprintf($title, $subject->getTitle()));
    $form->setDescription("Please fill below information.");
    $form->populate($subject->toArray());
    $form->rate_value->setValue($subject->rating);
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'eclassroom_review','rate_value'=>$subject->rating));
    }
    if (!$this->getRequest()->isPost()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
          $this->validateFormFields($validateFields);
    }
    $values = $_POST;
    $values['rating'] = $_POST['rate_value'];
    $reviews_table = Engine_Api::_()->getDbtable('reviews', 'eclassroom');
    $db = $reviews_table->getAdapter();
    $db->beginTransaction();
    try {
        $subject->setFromArray($values);
        $subject->save();
        $table = Engine_Api::_()->getDbtable('parametervalues', 'eclassroom');
        $tablename = $table->info('name');
        $dbObject = Engine_Db_Table::getDefaultAdapter();
        foreach ($_POST as $key => $reviewC) {
            if (count(explode('_', $key)) != 3 || !$reviewC)
                continue;
            $key = str_replace('review_parameter_', '', $key);
            if (!is_numeric($key))
                continue;
            $parameter = Engine_Api::_()->getItem('eclassroom_parameter', $key);
            $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $subject->owner_id . '","' . $item->owner_id . '","' . $subject->review_id . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
            $dbObject->query($query);
            $ratingP = $table->getRating($key);
            $parameter->rating = $ratingP;
            $parameter->save();
        }
        if (isset($item->rating)) {
            $item->rating = Engine_Api::_()->getDbtable('reviews', 'eclassroom')->getRating($subject->classroom_id);
            $item->save();
        }
        $subject->save();
        $reviewObject = $subject;
        $db->commit();
        $message = Zend_Registry::get('Zend_Translate')->_('The selected review has been edited.');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' =>$message)));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
	public function likeReviewAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    $item_id = $this->_getParam('id',$this->_getParam('review_id'));
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $itemTable = Engine_Api::_()->getItemTable('eclassroom_review');
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $select = $tableLike->select()
        ->from($tableMainLike)
        ->where('resource_type = ?', 'eclassroom_review')
        ->where('poster_id = ?', $viewer_id)
        ->where('poster_type = ?', 'user')
        ->where('resource_id = ?', $item_id);
    $result = $tableLike->fetchRow($select);
    if (count($result) > 0) {
      //delete
      $db = $result->getTable()->getAdapter();
      $db->beginTransaction();
      try {
          $result->delete();
          $db->commit();
          $temp['data']['message'] = $this->view->translate('Review Successfully Unliked.');
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
      $selectUser = $itemTable->select()->where('review_id =?', $item_id);
      $item = $user = $itemTable->fetchRow($selectUser);
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
          $like = $tableLike->createRow();
          $like->poster_id = $viewer_id;
          $like->resource_type = 'eclassroom_review';
          $like->resource_id = $item_id;
          $like->poster_type = 'user';
          $like->save();
          $itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array('review_id = ?' => $item_id));
          //Commit
          $db->commit();
          $temp['data']['message'] = $this->view->translate('Review Successfully liked.');
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
      //Send notification and activity feed work.
      $selectUser = $itemTable->select()->where('review_id =?', $item_id);
      $item = $itemTable->fetchRow($selectUser);
      $subject = $item;
      $owner = $subject->getOwner();
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer_id) {
          $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
          Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'liked', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, 'liked');
          $result = $activityTable->fetchRow(array('type =?' => 'liked', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          if (!$result) {
              $action = $activityTable->addActivity($viewer, $subject, 'liked');
              if ($action)
                  $activityTable->attachActivity($action, $subject);
          }
      }
    }
    $temp['data']['like_count'] = $item->like_count;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
  }
	public function deleteReviewAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!$this->getRequest()->getParam('review_id'))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $review = Engine_Api::_()->getItem('eclassroom_review', $this->getRequest()->getParam('review_id'));
    if(!$review){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    }
    $content_item = Engine_Api::_()->getItem('classroom', $review->classroom_id);
    if (!Engine_Api::_()->authorization()->isAllowed('eclass_review',$viewer, 'delete'))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        // In smoothbox
    if ($this->getRequest()->isPost()) {
      $db = $review->getTable()->getAdapter();
      $db->beginTransaction();
      try {
          $reviewParameterTable = Engine_Api::_()->getDbTable('parametervalues', 'eclassroom');
          $select = $reviewParameterTable->select()->where('content_id =?', $review->review_id);
          $parameters = $reviewParameterTable->fetchAll($select);
          if (count($parameters) > 0) {
              foreach ($parameters as $parameter) {
                  $reviewParameterTable->delete(array('parametervalue_id =?' => $parameter->parametervalue_id));
              }
          }
          $review->delete();
          $db->commit();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message'=>'The selected review has been deleted.')));
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
    }else{
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
  }
	public function reviewVotesAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    $item_id = $this->_getParam('id',$this->_getParam('review_id'));
    $type = $this->_getParam('type');
    if (intval($item_id) == 0 || ($type != 1 && $type != 2 && $type != 3)) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $itemTable = Engine_Api::_()->getItemTable('eclassroom_review');
    $tableVotes = Engine_Api::_()->getDbtable('reviewvotes', 'eclassroom');
    $tableMainVotes = $tableVotes->info('name');
    $review = Engine_Api::_()->getItem('eclassroom_review',$item_id);
    $classroom = Engine_Api::_()->getItem('classroom',$review->classroom_id);
    $select = $tableVotes->select()
        ->from($tableMainVotes)
        ->where('review_id = ?', $item_id)
        ->where('user_id = ?', $viewer_id)
        ->where('type =?', $type);
    $result = $tableVotes->fetchRow($select);
    if ($type == 1)
        $votesTitle = 'useful_count';
    else if ($type == 2)
        $votesTitle = 'funny_count';
    else
      $votesTitle = 'cool_count';
    if (count($result) > 0) {
      //delete
      $db = $result->getTable()->getAdapter();
      $db->beginTransaction();
      try {
          $result->delete();
          $itemTable->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' - 1')), array('review_id = ?' => $item_id));
          $db->commit();
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      $selectReview = $itemTable->select()->where('review_id =?', $item_id);
      $review = $itemTable->fetchRow($selectReview);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0','condition' => 'reduced', 'error_message' => '', 'result' => array('status'=>true,'count'=>$review->{$votesTitle})));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('reviewvotes', 'eclassroom')->getAdapter();
      $db->beginTransaction();
      try {
          $votereview = $tableVotes->createRow();
          $votereview->user_id = $viewer_id;
          $votereview->review_id = $item_id;
          $votereview->type = $type;
          $votereview->save();
          $itemTable->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' + 1')), array('review_id = ?' => $item_id));
          //Commit
          $db->commit();
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //Send notification and activity feed work.
      $selectReview = $itemTable->select()->where('review_id =?', $item_id);
      $review = $itemTable->fetchRow($selectReview);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('status'=>true,'condition' => 'increment','count'=>$review->{$votesTitle})));
    }
  }
	public function reviewViewAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $review_id = $this->_getParam('review_id', null);
		if(!$review_id){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.review', 1))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			
    if (!Engine_Api::_()->sesapi()->getViewerPrivacy('eclass_review', 'view'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		$review = Engine_Api::_()->getItem('eclassroom_review', $review_id);
		$classroom = Engine_Api::_()->getItem('classroom', $review->classroom_id);
		if(!$review)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    //Increment view count
    if (!$viewer->isSelf($review->getOwner())) {
        $review->view_count++;
        $review->save();
    }
		$params = array();
		$result = array();
		/*----------------make data-----------------------------*/
		$counter = 0;
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$result = $review->toArray();
		$reviewer = Engine_Api::_()->getItem('user', $review->owner_id);
		$owner = $reviewer->getOwner();
		$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'eclassroom')->getParameters(array('content_id'=>$review->getIdentity(),'user_id'=>$review->owner_id));
		$likeStatus = Engine_Api::_()->courses()->getLikeStatus($review->review_id,$review->getType());
		$ownerSelf = $viewerId == $review->owner_id ? true : false;
		$parameterCounter = 0;
		if(count($reviewParameters)>0){
			foreach($reviewParameters as $reviewP){ 
				$result['review_perameter'][$parameterCounter] = $reviewP->toArray();
				$parameterCounter++;
			}
		}
		$result['classroom']['images'] = $this->getBaseUrl(true, $classroom->getPhotoUrl());
		$result['classroom']['title'] = $classroom->getTitle();
		$result['classroom']['Guid'] = $classroom->getGuid();
		$result['classroom']['id'] = $classroom->getIdentity();
		$result['owner']['id'] = $owner->getIdentity();
		$result['owner']['Guid'] = $owner->getGuid();
		$result['owner']['title'] = $owner->getTitle();
		$result['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
		$result['show_pros'] = true;
		$result['show_cons'] = true;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.votes', 1)){
			$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','eclassroom')->isReviewVote(array('review_id'=>$review->getIdentity(),'classroom_id'=>$classroom->getIdentity(),'type'=>1));
			$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','eclassroom')->isReviewVote(array('review_id'=>$review->getIdentity(),'classroom_id'=>$classroom->getIdentity(),'type'=>2));
			$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','eclassroom')->isReviewVote(array('review_id'=>$review->getIdentity(),'classroom_id'=>$classroom->getIdentity(),'type'=>3));
			$result['voting']['label'] = $this->view->translate("Was this Review...?");
			$bttonCounter	= 0 ;			
			$result['voting']['buttons'][$bttonCounter]['name'] = 'useful';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.first', 'Useful'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypeone ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $review->useful_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'funny';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.second', 'Funny'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypetwo ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $review->funny_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'cool';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.review.third', 'Cool'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypethree ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $review->cool_count;
			
		}
		if($review->authorization()->isAllowed($viewer, 'comment')){
			$result['is_content_like'] = $likeStatus ? true : false;
		}
		$optionCounter = 0;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.show.report', 1) && $viewerId && $viewerId != $owner->getIdentity()){
			$result['options'][$optionCounter]['name'] = 'report';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Report');
			$optionCounter++;
		}
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.share', 1) && $viewerId){
			$result['options'][$optionCounter]['name'] = 'share';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Share');
			$optionCounter++;
			
			/*------------- share object -----------------*/
				$result["share"]["imageUrl"] = $this->getBaseUrl(false, $review->getPhotoUrl());
				$result["share"]["url"] = $this->getBaseUrl(false,$review->getHref());
				$result["share"]["title"] = $review->getTitle();
				$result["share"]["description"] = strip_tags($review->getDescription());
				$result["share"]["setting"] = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.share', 1);
				$result["share"]['urlParams'] = array(
					"type" => $review->getType(),
					"id" => $review->getIdentity()
				);
				/*------------- share object -----------------*/
		}
		if($viewerId && $viewerId == $owner->getIdentity() && Engine_Api::_()->authorization()->isAllowed('eclass_review',$viewer, 'edit')) { 
			$result['options'][$optionCounter]['name'] = 'edit';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Edit Review');
			$optionCounter++;
		}
		if($viewerId && $viewerId == $owner->getIdentity() && Engine_Api::_()->authorization()->isAllowed('eclass_review',$viewer, 'delete')) {
			$result['options'][$optionCounter]['name'] = 'delete';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Delete Review');
			$optionCounter++;
		}
		/*----------------make data-----------------------------*/
		$data['review'] = $result;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data)));
	}
	public function claimAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		if( !$viewer || !$viewer->getIdentity() ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if( !$this->_helper->requireUser()->isValid() ){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if(!Engine_Api::_()->authorization()->getPermission($viewer, 'eclassroom', 'auth_claim')){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		$classroom_id = $this->_getParam('classroom_id',0);
		if(!$classroom_id){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		$classroom = null;
		if($classroom_id){
			$classroom = Engine_Api::_()->getItem('classroom', $classroom_id);
		}
    if(!$classroom){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$classrom_title = $classroom->getTitle();
		if($classrom_title)
			$_POST['title'] = $classrom_title;
		$form = new Eclassroom_Form_Claim();
		if(isset($_POST))
      $form->populate($_POST);
	 // check for claim already exist or not
		$classroomClaimTable = Engine_Api::_()->getDbtable('claims', 'eclassroom');
		$classroomClaimTableName = $classroomClaimTable->info('name');
		$selectClaimTable = $classroomClaimTable->select()
		  ->from($classroomClaimTableName, 'classroom_id')
		  ->where('user_id =?', $viewer->getIdentity());
		  $selectClaimTable->where('classroom_id =?', $classroom_id);
		$claimedClassroom = $classroomClaimTable->fetchAll($selectClaimTable);
		if(count($claimedClassroom->toArray()) >0){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
		}
		if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
    }
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    //is post
    if (!$form->isValid($this->getRequest()->getPost())) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    }
		$values = $form->getValues();
		// Process
		$table = Engine_Api::_()->getDbtable('claims', 'eclassroom');
		$db = $table->getAdapter();
		$db->beginTransaction();
		try {
			// Create Claim
			$viewer = Engine_Api::_()->user()->getViewer();
			$eclassroomClaim = $table->createRow();
			$eclassroomClaim->user_id = $viewer->getIdentity();
			$eclassroomClaim->classroom_id = $values['classroom_id'];
			$eclassroomClaim->title = $values['title'];
			$eclassroomClaim->user_email = $values['user_email'];
			$eclassroomClaim->user_name = $values['user_name'];
			$eclassroomClaim->contact_number = $values['contact_number'];
			$eclassroomClaim->description = $values['description'];
			$eclassroomClaim->save();
			// Commit
			$db->commit();
		}
		catch( Exception $e ) {
			$db->rollBack();
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
		}
		$mail_settings = array('sender_title' => $values['user_name']);
		$body = '';
		$body .= $this->view->translate("Email: %s", $values['user_email']) . '<br />';
		if(isset($values['contact_number']) && !empty($values['contact_number']))
		$body .= $this->view->translate("Claim Owner Contact Number: %s", $values['contact_number']) . '<br />';
		$body .= $this->view->translate("Claim Reason: %s", $values['description']) . '<br /><br />';
		$mail_settings['message'] = $body;
		$classroomItem = Engine_Api::_()->getItem('classroom', $values['classroom_id']);
		$classroomOwnerId = $classroomItem->owner_id;
		$owner = $classroomItem->getOwner();
		$classroomOwnerEmail = Engine_Api::_()->getItem('user', $classroomOwnerId)->email;
		$fromAddress = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.from', 'admin@' . $_SERVER['HTTP_HOST']);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($classroomOwnerEmail, 'eclassroom_classroom_owner_claim', $mail_settings);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($fromAddress, 'eclassroom_site_owner_for_claim', $mail_settings);
		//Send notification to classroom owner
		Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $classroomItem, 'sesuser_claim_classroom');
		//Send notification to all superadmins
		$getAllSuperadmins = Engine_Api::_()->user()->getSuperAdmins();
		foreach($getAllSuperadmins as $getAllSuperadmin) {
		  Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($getAllSuperadmin, $viewer, $classroomItem, 'sesuser_claimadmin_classroom');
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
	}
  public function announcementAction()
  {
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('classroom_id', null);
    if (!$id) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing.'), 'result' => array()));
    }
    if (!Engine_Api::_()->core()->hasSubject()) {
      $classroom = Engine_Api::_()->getItem('classroom', $id);
    } else {
      $classroom = Engine_Api::_()->core()->getSubject();
    }
    $paginator = Engine_Api::_()->getDbTable('announcements', 'eclassroom')->getClassroomAnnouncementPaginator(array('classroom_id' => $classroom->classroom_id));
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $result = array();
    $announcementCounter = 0;
    foreach ($paginator as $announcement) {
      $result['announcements'][$announcementCounter]['announcement_id'] = $announcement->getIdentity();
      $result['announcements'][$announcementCounter]['title'] = $announcement->title;
      $result['announcements'][$announcementCounter]['creation_date'] = $announcement->creation_date;
      $result['announcements'][$announcementCounter]['detail'] = $announcement->body;
      $announcementCounter++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function servicesAction()
  {
    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('classroom');
    if (!$subject) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
    $paginator = Engine_Api::_()->getDbTable('services', 'eclassroom')->getServicePaginator(array('classroom_id' => $subject->getIdentity(), 'widgettype' => 'widget'));
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    //Manage Apps Check
    $isCheck = Engine_Api::_()->getDbTable('manageclassroomapps', 'eclassroom')->isCheck(array('classroom_id' => $subject->getIdentity(), 'columnname' => 'service'));
    if (empty($isCheck)) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    $servicesCounter = 0;
    $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
    foreach ($paginator as $item) {
        $result['services'][$servicesCounter]['images']['main'] = $this->getBaseUrl(true,$item->getPhotoUrl());
        $result['services'][$servicesCounter]['title'] = $item->title;
        if ($item->duration && $item->duration_type) {
            $result['services'][$servicesCounter]['duration'] = $item->duration;
            $result['services'][$servicesCounter]['durationtype'] = $item->duration_type;
            $result["services"][$servicesCounter]['service_type'] = $item->duration.' '.$item->duration_type;
        }
        if ($item->price) {
            $result['services'][$servicesCounter]['price'] = $item->price;
            $result['services'][$servicesCounter]['service_price'] = $curArr[$currency].$item->price;
        }
        if ($item->description) {
            $result['services'][$servicesCounter]['description'] = $item->description;
        }
        $servicesCounter++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));
  }
  public function mapAction()
  {
    if (!Engine_Api::_()->core()->hasSubject() || !Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.location', 1)) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    $classroom = Engine_Api::_()->core()->getSubject();
    $paginator = Engine_Api::_()->getDbTable('locations', 'eclassroom')->getClassroomLocationPaginator(array('classroom_id' => $classroom->classroom_id));
    $paginator->setItemCountPerPage(5);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $locationCounter = 0;
    foreach ($paginator as $location) {
      $result['locations'][$locationCounter] = $location->toArray();
      $result['locations'][$locationCounter]['title'] = $location->title;
      $result['locations'][$locationCounter]['location'] = $location->location;
      $result['locations'][$locationCounter]['venue'] = $location->venue;
      $result['locations'][$locationCounter]['address'] = $location->address;
      $result['locations'][$locationCounter]['address2'] = $location->address2;
      $result['locations'][$locationCounter]['city'] = $location->city;
      $result['locations'][$locationCounter]['zip'] = $location->zip;
      $result['locations'][$locationCounter]['state'] = $location->state;
      $result['locations'][$locationCounter]['country'] = $location->country;
      $locationPhotos = Engine_Api::_()->getDbTable('locationphotos', 'eclassroom')->getLocationPhotos(array('classroom_id' => $classroom->classroom_id, 'location_id' => $location->location_id));
      $photosCounter = 0;
      foreach ($locationPhotos as $photo) {
          $result['locations'][$locationCounter]['photos'][$photosCounter]['photoId'] = $photo->locationphoto_id;
          $result['locations'][$locationCounter]['photos'][$photosCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo, '', "");
          $photosCounter++;
      }
      $locationCounter++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));
  }
  public function albumAction()
  {
    if (!Engine_Api::_()->core()->hasSubject()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    $classroom = Engine_Api::_()->core()->getSubject();
    $order = $this->_getParam('sort','album_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    $search = $this->_getParam('search',null);
    switch($order){
        case 'most_commented':
          $orderval = 'comment_count';
          break;
        case 'most_viewed':
          $orderval = 'view_count';
          break;
        case "most_liked":
          $orderval = 'like_count';
          break;
        case "creation_date":
          $orderval = 'creation_date';
          break;
    }
    if(!$orderval)
      $orderval = 'album_id';
    $paginator = Engine_Api::_()->getDbTable('albums', 'eclassroom')->getAlbumSelect(array('classroom_id' => $classroom->classroom_id, 'order' => $orderval,'search'=>$search));
    $albumCount = Engine_Api::_()->getDbTable('albums', 'eclassroom')->getUserClassroomAlbumCount(array('classroom_id' => $classroom->classroom_id, 'user_id' => $viewer->getIdentity()));
    $paginator->setItemCountPerPage(5);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $getClassroomRolePermission = Engine_Api::_()->eclassroom()->getClassroomRolePermission($classroom->getIdentity(), 'post_content', 'album', false);
    $canUpload = $getClassroomRolePermission ? $getClassroomRolePermission : $this->_helper->requireAuth()->setAuthParams('classroom', null, 'album')->isValid();
    $optioncounter = 0;
    $quota = Engine_Api::_()->authorization()->getPermission($levelId, 'classroom', 'classroom_album_count');
    if($albumCount >= $quota ){
      $allowMore = false;
    }else{
      $allowMore = true;
    }
    if ($canUpload && $allowMore) {
        $result['can_create'] = true;
    } else {
        $result['can_create'] = false;
    }
    $result['menus'][$optioncounter]['name'] = 'creation_date';
    $result['menus'][$optioncounter]['label'] = $this->view->translate('Recently Created');
    $optioncounter++;
    $result['menus'][$optioncounter]['name'] = 'most_liked';
    $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Liked');
    $optioncounter++;
    $result['menus'][$optioncounter]['name'] = 'most_viewed';
    $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Viewed');
    $optioncounter++;
    $result['menus'][$optioncounter]['name'] = 'most_commented';
    $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Commented');
    $optioncounter++;
    $albumCounter = 0;
    foreach ($paginator as $item) {
        $owner = $item->getOwner();
        $ownertitle = $owner->displayname;
        $result['albums'][$albumCounter] = $item->toArray();
        $photo = Engine_Api::_()->getItem('eclassroom_photo',$item->photo_id);
        if($photo)
            $result['albums'][$albumCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "") ? Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "") : $item->getPhotoUrl();
        else
            $result['albums'][$albumCounter]['images'] =  $this->getBaseUrl(true, $item->getPhotoUrl());
        $result['albums'][$albumCounter]['user_title'] = $ownertitle;
        $result['albums'][$albumCounter]['photo_count'] = $item->count();
        $albumCounter++;
    }

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function albumviewAction()
  {
    $albumid = $this->_getParam('album_id', 0);
    $classroomId = $this->_getParam('classroom_id', 0);
    if (!$albumid) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    if (Engine_Api::_()->core()->hasSubject()) {
        $classroom = Engine_Api::_()->core()->getSubject();
        $album = Engine_Api::_()->getItem('eclassroom_album', $albumid);
    } else {
        $album = Engine_Api::_()->getItem('eclassroom_album', $albumid);
        $classroom = Engine_Api::_()->getItem('classroom', $album->classroom_id);
    }
    $photoTable = Engine_Api::_()->getItemTable('eclassroom_photo');
    $mine = true;
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    $viewer_id = $viewer->getIdentity();
    $result['album'] = $album->toArray();
    $result['album']['user_title'] = $viewer->getOwner()->getTitle();
    $category = Engine_Api::_()->getItem('eclassroom_category', $classroom->category_id);
    if ($category)
        $result['album']['category_title'] = $category->category_name;
          $attachmentItem = $album;
        if ($attachmentItem->getPhotoUrl())
            $result['album']["share"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());
        $result['album']["share"]["url"] = $this->getBaseUrl(false,$attachmentItem->getHref());
        $result['album']["share"]["title"] = $attachmentItem->getTitle();
        $result['album']["share"]["description"] = strip_tags($attachmentItem->getDescription());
        $result['album']["share"]['urlParams'] = array(
            "type" => $album->getType(),
            "id" => $album->getIdentity()
        );
    if ($viewer->getIdentity() > 0) {
        $canEdit = $editClassroomRolePermission = Engine_Api::_()->eclassroom()->getClassroomRolePermission($classroom->getIdentity(), 'allow_plugin_content', 'edit');
        $editClassroomRolePermission = Engine_Api::_()->eclassroom()->getClassroomRolePermission($classroom->getIdentity(), 'allow_plugin_content', 'edit');
        $canEditMemberLevelPermission = $editClassroomRolePermission ? $editClassroomRolePermission : $classroom->authorization()->isAllowed($viewer, 'edit');
        $deleteClassroomRolePermission = Engine_Api::_()->eclassroom()->getClassroomRolePermission($classroom->getIdentity(), 'allow_plugin_content', 'delete');
        $canDeleteMemberLevelPermission = $deleteClassroomRolePermission ? $deleteClassroomRolePermission : $classroom->authorization()->isAllowed($viewer, 'delete');
    }
    $menusCounter = 0;
    if ($canEditMemberLevelPermission == 1) {
        if ($viewer->getIdentity() == $album->owner_id || $canEditMemberLevelPermission) {
            $result['album']['is_edit'] = true;
            $result['menus'][$menusCounter]['name'] = 'edit';
            $result['menus'][$menusCounter]['label'] = $this->view->translate('Edit');
            $menusCounter++;
        } else {
            $result['album']['is_edit'] = false;
        }
    } else if ($canEditMemberLevelPermission == 2) {
        $result['album']['is_edit'] = true;
        $result['menus'][$menusCounter]['name'] = 'edit';
        $result['menus'][$menusCounter]['label'] = $this->view->translate('Edit');
        $menusCounter++;
    } else {
        $result['album']['is_edit'] = false;
    }
    $result['album']['is_delete'] = true;
    if ($canDeleteMemberLevelPermission == 1) {
        if ($viewer->getIdentity() == $album->owner_id || $canDeleteMemberLevelPermission) {
            $result['album']['is_delete'] = true;
            $result['menus'][$menusCounter]['name'] = 'delete';
            $result['menus'][$menusCounter]['label'] = $this->view->translate('Delete');
            $menusCounter++;
        } else {
            $result['album']['is_delete'] = false;
        }
    } else if ($canDeleteMemberLevelPermission == 2) {
        $result['album']['is_delete'] = true;
        $result['menus'][$menusCounter]['name'] = 'delete';
        $result[$menusCounter]['label'] = $this->view->translate('Delete');
        $menusCounter++;
    } else {
        $result['album']['is_delete'] = false;
    }
    if ($viewer_id != $album->owner_id) {
        $result['menus'][$menusCounter]['name'] = 'report';
        $result['menus'][$menusCounter]['label'] = $this->view->translate("Report");
        $result['menus'][$menusCounter]["params"]['id'] = $album->getIdentity();
        $result['menus'][$menusCounter]["params"]['type'] = $album->getType();
        $menusCounter++;
    }
    $result['menus'][$menusCounter]['name'] = 'uploadphoto';
    $result['menus'][$menusCounter]['label'] = $this->view->translate("Upload more photos");
    $menusCounter++;
    $userimage = Engine_Api::_()->sesapi()->getPhotoUrls($album->getOwner(), '', "");
    $canComment = $classroom->authorization()->isAllowed($viewer, 'comment');
    if ($canComment)
        $result['album']['is_comment'] = true;
    else
        $result['album']['is_comment'] = false;
    $result['album']['user_image'] = $userimage;
    $paginator = $photoTable->getPhotoPaginator(array('album' => $album));
    $paginator->setItemCountPerPage('limit', 10);
    $paginator->setCurrentPageNumber('classroom_number', 1);

    $photoCounter = 0;
    foreach ($paginator as $photo) {
        $result['photos'][$photoCounter] = $photo->toArray();
        $result['photos'][$photoCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "");
        $albumLikeStatus = Engine_Api::_()->eclassroom()->getLikeStatusClassroom($photo->photo_id, 'eclassroom_photo');
        $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'eclassroom')->isFavourite(array('resource_type' => 'eclassroom_photo', 'resource_id' => $photo->photo_id));
        if ($albumLikeStatus)
            $result['photos'][$photoCounter]['like_status'] = true;
        else
            $result['photos'][$photoCounter]['like_status'] = false;
        if ($albumFavStatus)
            $result['photos'][$photoCounter]['fav-satus'] = true;
        else
            $result['photos'][$photoCounter]['fav-satus'] = false;

        if ($albumLikeStatus) {
            $result['photos'][$photoCounter]['is_content_like'] = true;
        } else {
            $result['photos'][$photoCounter]['is_content_like'] = false;
        }
        if ($albumFavStatus) {
            $result['photos'][$photoCounter]['is_content_favourite'] = true;
        } else {
            $result['photos'][$photoCounter]['is_content_favourite'] = false;
        }
        $photoCounter++;
    }
    if (isset($album->art_cover) && $album->art_cover != 0 && $album->art_cover != '') {
        $albumArtCover = Engine_Api::_()->storage()->get($album->art_cover, '')->getPhotoUrl();
        $result['album']['albumArtCover'] = $this->getBaseurl(false, $albumArtCover);
        $result['album']['cover_pic'] = $this->getBaseurl(false, $albumArtCover);
    } else {
        $albumArtCover = '';
    }
    if(!$albumArtCover){
      $albumImage = Engine_Api::_()->eclassroom()->getAlbumPhoto($album->getIdentity(), 0, 3);
      $countTotal = count($albumImage);
      $result['album']['image_count'] = $countTotal;
      $imageCounter = 0;
      foreach ($albumImage as $photo) {
          $imageURL[$imageCounter] =  $this->getBaseurl(false, $photo->getPhotoUrl('thumb.normalmain'));;
          $imageCounter++;
      }
      $result['album']['cover_pic'] = $imageURL;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if (count($result) > 0)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    else
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate(' There are no results that match your search. Please try again.'), 'result' => array()));
  }
  public function createalbumAction(){
      $classroom_id = $this->_getParam('classroom_id', false);
      $album_id = $this->_getParam('album_id', 0);
      if ($album_id) {
          $album = Engine_Api::_()->getItem('eclassroom_album', $album_id);
          $classroom_id = $album->classroom_id;
      } else {
          $classroom_id = $classroom_id;
      }
      $classroom = Engine_Api::_()->getItem('classroom', $classroom_id);
      if(!$classroom_id)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array())));
      // set up data needed to check quota
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['user_id'] = $viewer->getIdentity();
      $current_count = Engine_Api::_()->getDbTable('albums', 'eclassroom')->getUserAlbumCount($values);
      $quota = $quota = 0;
      // Get form
      $form = new Eclassroom_Form_Album();
      $form->removeElement('fancyuploadfileids');
      $form->removeElement('tabs_form_albumcreate');
      $form->removeElement('drag-drop');
      $form->removeElement('from-url');
      $form->removeElement('file_multi');
      $form->removeElement('uploadFileContainer');
      // Render
      $form->populate(array('album' => $album_id));
      if ($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
      }
      if (!$form->isValid($this->getRequest()->getPost())){
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
          if (count($validateFields))
              $this->validateFormFields($validateFields);
      }
      $db = Engine_Api::_()->getItemTable('eclassroom_album')->getAdapter();
      $db->beginTransaction();
      try {
        $photoTable = Engine_Api::_()->getDbTable('photos', 'eclassroom');
        $uploadSource = $_FILES['image'];
        $photoArray = array(
          'classroom_id' => $classroom->classroom_id,
          'user_id' => $viewer->getIdentity(),
          'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'eclassroom')->getAdapter();
        $db->beginTransaction();
        try {
            $images['name'] = $name;
            $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
            $images['error'] = $uploadSource['error'][$counter];
            $images['size'] = $uploadSource['size'][$counter];
            $images['type'] = $uploadSource['type'][$counter];
            $photo = $photoTable->createRow();
            $photo->setFromArray($photoArray);
            $photo->save();
            $albumdata = $album ? $album :false;
            $photo = $photo->setAlbumPhoto($uploadSource, false, false, $albumdata);
            $photo->collection_id = $photo->album_id;
            $photo->save();
            $photosource[] = $photo->getIdentity();
            $counter++;
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $_POST['classroom_id'] = $classroom->classroom_id;
        $_POST['file'] = implode(' ', $photosource);
        $album = $form->saveValues();
          // Add tags
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Created.'), album_id => $album->getIdentity()))));
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array())));
      }
  }
  public function editalbumAction()
  {
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $album_id = $this->_getParam('album_id', false);
    if (!$album_id)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    else
        $album = Engine_Api::_()->getItem('eclassroom_album', $album_id);
//     if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
//         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    // Make form
   
    $form = new Eclassroom_Form_Album_Edit();
    $form->populate($album->toArray());
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
    }
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    //is post
    if (!$form->isValid($this->getRequest()->getPost())) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    // Process
    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();
    try {
        $values = $form->getValues();
        $album->setFromArray($values);
        $album->save();
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate("You have successfully edtited this album."))));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function deletealbumAction()
  {
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    if ($this->_getParam('album_id', false))
        $album = Engine_Api::_()->getItem('eclassroom_album', $this->_getParam('album_id', false));
    else
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    if (!$this->_helper->requireAuth()->setAuthParams('eclassroom', null, 'delete')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    // In smoothbox
    $form = new Eclassroom_Form_Album_Delete();
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
    }
    if (!$album) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Album does not exists or not authorized to delete'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();
    try {
        $album->delete();
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('Message' => $this->view->translate('album deleted successfully.'))));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function browsealbumAction(){
    // Default param options
    if (count($_POST)) {
        $params = $_POST;
    }
    $searchArray = array();
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
        parse_str($_POST['searchParams'], $searchArray);
    $value['classroom'] = isset($_POST['classroom']) ? $_POST['classroom'] : 1;
    $value['sort'] = isset($searchArray['sort']) ? $searchArray['sort'] : (isset($_GET[' ']) ? $_GET['sort'] : (isset($params['sort']) ? $params['sort'] : $this->_getParam('sort', 'mostSPliked')));
    $value['show'] = isset($searchArray['show']) ? $searchArray['show'] : (isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ? $params['show'] : ''));
    $value['search'] = isset($searchArray['search']) ? $searchArray['search'] : (isset($_GET['search']) ? $_GET['search'] : (isset($params['search']) ? $params['search'] : ''));
    $value['user_id'] = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($params['user_id']) ? $params['user_id'] : '');
    $value['show_criterias'] = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'by', 'title', 'socialSharing', 'view', 'photoCount', 'favouriteCount', 'favouriteButton', 'likeButton', 'featured', 'sponsored'));
    foreach ($value['show_criterias'] as $show_criteria)
        if (isset($value['sort']) && $value['sort'] != '') {
            $value['getParamSort'] = str_replace('SP', '_', $value['sort']);
        } else {
            $value['getParamSort'] = 'creation_date';
        }
    switch ($value['getParamSort']) {
        case 'most_viewed':
            $value['order'] = 'view_count';
            break;
        case 'most_favourite':
            $value['order'] = 'favourite_count';
            break;
        case 'most_liked':
            $value['order'] = 'like_count';
            break;
        case 'most_commented':
            $value['order'] = 'comment_count';
            break;
        case 'featured':
            $value['order'] = 'featured';
            break;
        case 'sponsored':
            $value['order'] = 'sponsored';
            break;
        case 'creation_date':
        default:
            $value['order'] = 'creation_date';
            break;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    //Check for deault album
    $value['showdefaultalbum'] = 0;
    $value['classroom_id'] = $this->_getParam('classroom_id',0);
    $paginator = Engine_Api::_()->getDbTable('albums', 'eclassroom')->getAlbumSelect($value);
    $paginator->setItemCountPerPage($this->_getParam('limit', 1));
    $paginator->setCurrentPageNumber($this->_getParam('classroom', 10));
    $albumCounter = 0;
    foreach ($paginator as $item) {
        $owner = $item->getOwner();
        $ownertitle = $owner->displayname;
        $result['albums'][$albumCounter] = $item->toArray();
        $result['albums'][$albumCounter]['images'] = $this->getBaseUrl(true, (!empty($item->photo_id)) ? $item->getPhotoUrl() : '/application/modules/User/externals/images/nophoto_classroom_thumb_profile.png');
        $result['albums'][$albumCounter]['user_title'] = $ownertitle;
        $result['albums'][$albumCounter]['photo_count'] = $item->count();
        $albumLikeStatus = Engine_Api::_()->eclassroom()->getLikeStatus($item->getIdentity(), $item->getType());
        $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'eclassroom')->isFavourite(array('resource_type' => 'album', 'resource_id' => $item->album_id));
        if ($albumLikeStatus) {
            $result['albums'][$albumCounter]['is_content_like'] = true;
        } else {
            $result['albums'][$albumCounter]['is_content_like'] = false;
        }
        if ($albumFavStatus) {
            $result['albums'][$albumCounter]['is_content_favourite'] = true;
        } else {
            $result['albums'][$albumCounter]['is_content_favourite'] = false;
        }
        $albumCounter++;
    }
    $canCreate = Engine_Api::_()->authorization()->isAllowed('album', null, 'create');
    $result['can_create'] = $canCreate ? true : false;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function albumsearchformAction(){
    $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created', 'mostSPviewed' => 'Most Viewed', 'mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented', 'mostSPfavourite' => 'Most Favourite'));
    $search_for = $this->_getParam('search_for', 'album');
    $default_search_type = $this->_getParam('default_search_type', 'mostSPliked');
    $searchForm = new Eclassroom_Form_AlbumSearch(array('searchTitle' => $this->_getParam('search_title', 'yes'), 'browseBy' => $this->_getParam('browse_by', 'yes'), 'searchFor' => $search_for, 'FriendsSearch' => $this->_getParam('friend_show', 'yes'), 'defaultSearchtype' => $default_search_type));
    if (isset($_GET['tag_name'])) {
        $searchForm->getElement('search')->setValue($_GET['tag_name']);
    }
    if ($this->_getParam('search_type') !== null && $this->_getParam('browse_by', 'yes') == 'yes') {
        $arrayOptions = $filterOptions;
        $filterOptions = array();
        foreach ($arrayOptions as $filterOption) {
            $value = str_replace(array('SP', ''), array(' ', ' '), $filterOption);
            $filterOptions[$filterOption] = ucwords($value);
        }
        $filterOptions = array('' => '') + $filterOptions;
        $searchForm->sort->setMultiOptions($filterOptions);
        $searchForm->sort->setValue($default_search_type);
    }
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $searchForm->setMethod('get')->populate($request->getParams());
    $searchForm->removeElement('loading-img-eclassroom');
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
        $this->generateFormFields($formFields);
    } else {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array())));
    }
  }
  public function inviteAction()
  {
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$this->_helper->requireSubject('classroom')->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    // @todo auth
    // Prepare data
    $viewer = Engine_Api::_()->user()->getViewer();
    $classroom = Engine_Api::_()->core()->getSubject();
    // Prepare friends
    $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
    $friendsIds = $friendsTable->select()
        ->from($friendsTable, 'user_id')
        ->where('resource_id = ?', $viewer->getIdentity())
        ->where('active = ?', true)
        ->limit(100)
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN);
    if (!empty($friendsIds)) {
        $friends = Engine_Api::_()->getItemTable('user')->find($friendsIds);
    } else {
        $friends = array();
    }
    // Prepare form
    $form = new Eclassroom_Form_Invite();
    $count = 0;
    foreach ($friends as $friend) {
        if ($classroom->membership()->isMember($friend, null)) {
            continue;
        }
        $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
        $count++;
    }
    if ($count == 1)
        $form->removeElement('all');
    else if (!$count)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have no friends you can invite.'))));
    if ($this->_getParam('getForm')) {
        if ($form->getElement('all'))
            $form->getElement('all')->setName('eclassroom_choose_all');

        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
    }
    // Not posting
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (count($validateFields))
            $this->validateFormFields($validateFields);
    }
    // Process
    $table = $classroom->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
        $usersIds = (array) $form->getValue('users');
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        foreach ($friends as $friend) {
          if (!in_array($friend->getIdentity(), $usersIds)) {
            continue;
          }
          $classroom->membership()->addMember($friend)->setResourceApproved($friend);
          $notifyApi->addNotification($friend, $viewer, $classroom, 'eclassroom_invite');
        }
        if ($count > 1) {
            $message = $this->view->translate('All members invited.');
        } else {
            $message = $this->view->translate('member invited.');
        }
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $message)));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function joinAction()
  {
    $classroom_id = $this->getParam('classroom_id', 0);
    if (!$classroom_id) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $item = Engine_Api::_()->getItem('classroom', $classroom_id);
    if ($item->membership()->isResourceApprovalRequired()) {
      $row = $item->membership()->getReceiver()
          ->select()
          ->where('resource_id = ?', $item->getIdentity())
          ->where('user_id = ?', $viewer->getIdentity())
          ->query()
          ->fetch(Zend_Db::FETCH_ASSOC, 0);
      if (empty($row)) { 
          // has not yet requested an invite
          $message = $this->request();
          if ($message == 'Successfully requested.') {
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message,'menus'=>$this->getButtonMenus($item))));
          } else {
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('database_error'), 'result' => array()));
          }
      } elseif ($row['user_approved'] && !$row['resource_approved']) {
          // has requested an invite; show cancel invite classroom
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Has requested an invite'),'menus'=>$this->getButtonMenus($item))));
      }
    }
    $form = new Eclassroom_Form_Member_Join();
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
          $this->validateFormFields($validateFields);
    }
    $db = $item->membership()->getReceiver()->getTable()->getAdapter(); 
    $db->beginTransaction();
    try {
      $membership_status = $item->membership()->getRow($viewer)->active;
      if (!$membership_status) {
          $item->membership()->addMember($viewer)->setUserApproved($viewer);
          $row = $item->membership()->getRow($viewer);
          $row->save();
      }
      $owner = $item->getOwner();
//           Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'eclassroom_classroom_join');
//           Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_eclassroom_classroom_classroomjoined', array('classroom_title' => $item->getTitle(), 'sender_title' => $viewer->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
//           //Send to all joined members
//           $joinedMembers = Engine_Api::_()->eclassroom()->getallJoinedMembers($item);
//           foreach ($joinedMembers as $joinedMember) {
//               if ($joinedMember->user_id == $item->owner_id) continue;
//               $joinedMember = Engine_Api::_()->getItem('user', $joinedMember->user_id);
//               Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($joinedMember, $viewer, $item, 'eclassroom_classroom_classroomijoinedjoin');
//               Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_eclassroom_classroom_joinclassroomjoined', array('classroom_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
//           }
//           $followerMembers = Engine_Api::_()->getDbTable('followers', 'eclassroom')->getFollowers($item->getIdentity());
//           foreach ($followerMembers as $followerMember) {
//               if ($followerMember->owner_id == $item->owner_id) continue;
//               $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
//               Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($followerMember, $viewer, $item, 'eclassroom_classroom_classroomifollowedjoin');
//               Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_eclassroom_classroom_joinedclassroomfollowed', array('classroom_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
//           }
//           // Add activity if membership status was not valid from before
//           if (!$membership_status) {
//               $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
//               $action = $activityApi->addActivity($viewer, $item, 'eclassroom_classroom_join');
//               if ($action) {
//                   $activityApi->attachActivity($action, $item);
//               }
//           }

      $db->commit();
      $viewerId = $viewer->getIdentity();
      $result['message'] = $this->view->translate('Classroom Successfully Joined.');
      $result['menus']  = $this->getButtonMenus($item);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    } catch (Exception $e) {
      $db->rollBack();
      $result['message'] = 'Database Error.';
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => $result));
    }
  }
  public function request()
  {
        // Check resource approval
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));


        // Make form
        $form = new Eclassroom_Form_Member_Request();

        // Process form
        if ($form->isValid($this->getRequest()->getPost())) {
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $subject->membership()->addMember($viewer)->setUserApproved($viewer);

                // Add notification
                $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
                $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'eclassroom_approve');

                $db->commit();
                $messgae = 'Successfully requested.';
            } catch (Exception $e) {
                $db->rollBack();
                $messgae = 'database_error';
            }
            return $messgae;
        }
  }
  public function requestAction()
  {
    // Check resource approval
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    // Make form
    $form = new Eclassroom_Form_Member_Request();
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
          $this->validateFormFields($validateFields);
    }
    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $subject->membership()->addMember($viewer)->setUserApproved($viewer);
            // Add notification
            $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
            $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'eclassroom_reqjoin');
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'eclassroom_reqjoin', array('classroom_name' => $subject->getTitle(),'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            $followerMembers = Engine_Api::_()->getDbTable('followers', 'eclassroom')->getFollowers($subject->classroom_id);
            foreach($followerMembers as $followerMember) {
                $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                $notifyApi->addNotification($followerMember, $viewer, $subject, 'eclassroom_bsreqjoin');
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($followerMember, 'eclassroom_bsreqjoin', array('classroom_name' => $subject->getTitle(),'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            } 
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
  }
  public function acceptAction()
  {
      // Check auth
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$this->_helper->requireSubject('classroom')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try {
        $membership_status = $subject->membership()->getRow($viewer)->active;
        $subject->membership()->setUserApproved($viewer);
        $row = $subject->membership()->getRow($viewer);
        $row->save();
        // Add activity
        if (!$membership_status) {
            $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
            $action = $activityApi->addActivity($viewer, $subject, 'eclassroom_classroom_join');
        }
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have accepted the invite to the classroom'),'menus'=>$this->getButtonMenus($subject))));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function rejectAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject('classroom')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_misssing', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try {
        $user = Engine_Api::_()->getItem('user', (int)$this->_getParam('user_id'));
        $subject->membership()->removeMember($user);
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'eclassroom_reject');
        // Set the request as handled
        $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
            $viewer, $subject, 'eclassroom_invite');
        if($notification){
          $notification->mitigated = true;
          $notification->save();
        }
        $db->commit();
        $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the classroom');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message,'menus'=>$this->getButtonMenus($subject))));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function removeAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
    // Get user
    if (0 === ($user_id = (int)$this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('member does not exist.'), 'result' => array()));
    }
    $classroom = Engine_Api::_()->core()->getSubject();
    if (!$classroom->membership()->isMember($user)) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Cannot remove a non-member.'), 'result' => array()));
    }
    $db = $classroom->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try {
        // Remove membership
        $classroom->membership()->removeMember($user);
        // Remove the notification?
        $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
            $classroom->getOwner(), $classroom, 'eclassroom_approve');
        if ($notification) {
            $notification->delete();
        }
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('message' => $this->view->translate('The selected member has been removed from this classroom.'),'menus'=>$this->getButtonMenus($subject))));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function approveAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject('classroom')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
    // Get user
    if (0 === ($user_id = (int)$this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('user does not exist.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try {
        $subject->membership()->setResourceApproved($user);
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'eclassroom_accepted');
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Classroom request approved'),'menus'=>$this->getButtonMenus($subject))));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function cancelAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $user_id = $this->_getParam('user_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if (!$subject->authorization()->isAllowed($viewer, 'invite') &&
        $user_id != $viewer->getIdentity() &&
        $user_id) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    if ($user_id) {
        $user = Engine_Api::_()->getItem('user', $user_id);
        if (!$user) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
    } else {
        $user = $viewer;
    }
    $subject = Engine_Api::_()->core()->getSubject('classroom');
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try {
        $subject->membership()->removeMember($user);
        // Remove the notification?
        $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
            $subject->getOwner(), $subject, 'eclassroom_approve');
        if ($notification) {
            $notification->delete();
        }
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Your invite request has been cancelled.'),'menus'=>$this->getButtonMenus($subject))));
    } catch (Exception $e) {
        $db->rollBack();
        $message = $e->getMessage();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
    }
  }	
  function getButtonMenus($classroom){
    $viewer = $this->view->viewer();
    $showLoginformFalse = false;
      if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.enable.contact.details', 1)) {
        $showLoginformFalse = true;
    }
    $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom.allow.share', 0);
    $i = 0;
    if ($classroom->classroom_contact_email || $classroom->classroom_contact_phone || $classroom->classroom_contact_website) {
      if ($classroom->classroom_contact_email) {
          $result[$i]['name'] = 'mail';
          $result[$i]['label'] = 'Send Email';
          $result[$i]['value'] = $classroom->classroom_contact_email;
          $i++;
      }
      if ($classroom->classroom_contact_phone) {
          $result[$i]['name'] = 'phone';
          $result[$i]['label'] = 'Call';
          $result[$i]['value'] = $classroom->classroom_contact_phone;
          $i++;
      }
      if ($classroom->classroom_contact_website) {
          $result[$i]['name'] = 'website';
          $result[$i]['label'] = 'Visit Website';
          $result[$i]['value'] = $classroom->classroom_contact_website;
          $i++;
      }
    }
    if ($classroom->is_approved) {
      $result[$i]['name'] = 'contact';
      $result[$i]['label'] = 'Contact';
      $i++;
      if ($shareType) {
          $result[$i]['name'] = 'share';
          $result[$i]['label'] = 'Share';
          $i++;
      }
      if ($viewerId) {
        $row = $classroom->membership()->getRow($viewer);
        if (null === $row) {
            if ($classroom->membership()->isResourceApprovalRequired()) {
                $result[$i]['name'] = 'request';
                $result[$i]['label'] = 'Request Membership';
                $i++;
            } else {
                $result[$i]['name'] = 'join';
                $result[$i]['label'] = 'Join Classroom';
                $i++;
            }
        } else if ($row->active) {
            if (!$classroom->isOwner($viewer)) {
                $result[$i]['label'] = 'Leave Classroom';
                $result[$i]['name'] = 'leave';
                $i++;
            }
        } else if (!$row->resource_approved && $row->user_approved) {
            $result[$i]['label'] = 'Cancel Membership Request';
            $result[$i]['name'] = 'cancel';
            $i++;
        } else if (!$row->user_approved && $row->resource_approved) {
            $result[$i]['label'] = 'Accept Membership Request';
            $result[$i]['name'] = 'accept';
            $i++;
            $result[$i]['label'] = 'Ignore Membership Request';
            $result[$i]['name'] = 'reject';
        }
      }
    }
    return $result;
  }
  public function contactAction(){
    $ownerId[] = $this->_getParam('owner_id', 0);
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    // Get form
    if (!$this->_getParam('owner_id', 0)) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    $form = new Courses_Form_ContactOwner();
    $form->classroom_owner_id->setValue($this->_getParam('owner_id', 0));
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'classroom'));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (count($validateFields))
            $this->validateFormFields($validateFields);
    }
    // Process
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
        $viewer = Engine_Api::_()->user()->getViewer();
        $values = $form->getValues();
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $ownerId);
        $attachment = null;
        if ($values['classroom_owner_id'] != $viewer->getIdentity()) {
            // Create conversation
            $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send($viewer, $ownerId, $values['title'], $values['body'], $attachment);
        }
        // Send notifications
        foreach ($recipientsUsers as $user) {
            if ($user->getIdentity() == $viewer->getIdentity()) {
                continue;
            }
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $conversation, 'message_new');
        }
        // Increment messages counter
        Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');
        // Commit
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Message sent successfully.')));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function infoAction()
  {
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('classroom_id', null);
    if (!$id) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }

    $classroom_id = Engine_Api::_()->getDbtable('classrooms', 'eclassroom')->getClassroomId($id);
    if (!Engine_Api::_()->core()->hasSubject()) {
        $classroom = Engine_Api::_()->getItem('classroom', $classroom_id);
    } else {
        $classroom = Engine_Api::_()->core()->getSubject();
    }
    if(!$classroom){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    }
    $result['information'] = $this->getInformation($classroom);
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
  }
  public function getInformation($classroom){

        $result = $classroom->toArray();
        $openhourstable = Engine_Api::_()->getDbTable('openhours', 'eclassroom');
        $select = $openhourstable->select()
        ->from($openhourstable->info('name'))
        ->where('classroom_id =?', $classroom->getIdentity());
        $row = $openhourstable->fetchRow($select);
        $color = "";
        $data = "";
        $hours = "";

        if ($row) {
            $result['operating_hours']['label'] = $row->timezone;
            $params = json_decode($row->params, true);
            $hoursCounter = 0;
            if ($params['type'] == "selected") {
                unset($params['type']);
                for ($i = date('N'); $i < 8; $i++) {
                    if (!empty($params[$i])) {
                        $time = "";
                        foreach ($params[$i] as $key => $value) {
                            $time = $value['starttime'] . ' - ' . $value['endtime'] . '<br>';
                        }
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';
                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = $time;
                        $hoursCounter++;
                    } else {
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';
                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = 'Closed';
                        $hoursCounter++;
                    }
                }

                for ($i = 1; $i < date('N'); $i++) {
                    if (!empty($params[$i])) {
                        $time = "";
                        foreach ($params[$i] as $key => $value) {
                            $time = $value['starttime'] . ' - ' . $value['endtime'] . '<br>';
                        }
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';

                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = $time;
                        $hoursCounter++;
                    } else {
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';

                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = 'Closed';
                        $hoursCounter++;
                    }
                }

            } else if ($params['type'] == "always") {

                $color = "green";
                $data = "Always Open";
                $result['operating_hours']['value'][$hoursCounter]['label'] = 'Always';
                $result['operating_hours']['value'][$hoursCounter]['value'] = $data;
            } else if ($params['type'] == "notavailable") {
                $data = "Not Available";
                $result['operating_hours']['value'][$hoursCounter]['label'] = 'Not Available';
                $result['operating_hours']['value'][$hoursCounter]['value'] = $data;
            } else if ($params['type'] == "closed") {
                $color = "red";
                $data = "Permanently closed";
                $result['operating_hours']['value'][$hoursCounter]['label'] = 'Closed';
                $result['operating_hours']['value'][$hoursCounter]['value'] = $data;
            }
        }
        $basicInformationCounter = 0;
        $owner = $classroom->getOwner();
        $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_show_userdetail', 0);
        if(!$hideIdentity){
            $result['basicInformation'][$basicInformationCounter]['name'] = 'createdby';
            $result['basicInformation'][$basicInformationCounter]['value'] = $owner->displayname;
            $result['basicInformation'][$basicInformationCounter]['label'] = 'Created By';
            $basicInformationCounter++;
        }
        $result['basicInformation'][$basicInformationCounter]['name'] = 'creationdate';
        $result['basicInformation'][$basicInformationCounter]['value'] = $classroom->creation_date;
        $result['basicInformation'][$basicInformationCounter]['label'] = 'Created on';
        $basicInformationCounter++;
        $statsCounter = 0;


        $state[$statsCounter]['name'] = 'comment';
        $state[$statsCounter]['value'] = $classroom->comment_count;
        $state[$statsCounter]['label'] = 'Comments';
        $statsCounter++;


        $state[$statsCounter]['name'] = 'like';
        $state[$statsCounter]['value'] = $classroom->like_count;
        $state[$statsCounter]['label'] = 'Likes';
        $statsCounter++;


        $state[$statsCounter]['name'] = 'view';
        $state[$statsCounter]['value'] = $classroom->view_count;
        $state[$statsCounter]['label'] = 'Views';
        $statsCounter++;


        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('eclassroom_allow_follow', 0);

        if ($canFavourite) {
            $state[$statsCounter]['name'] = 'favourite';
            $state[$statsCounter]['value'] = $classroom->favourite_count;
            $state[$statsCounter]['label'] = 'Favourites';
            $statsCounter++;
        }

        if ($canFollow) {
            $state[$statsCounter]['name'] = 'follow';
            $state[$statsCounter]['value'] = $classroom->follow_count;
            $state[$statsCounter]['label'] = 'Follows';
        }


        $statsCounter++;

        $result['basicInformation'][$basicInformationCounter]['name'] = 'stats';
        $result['basicInformation'][$basicInformationCounter]['value'] = $state;
        $result['basicInformation'][$basicInformationCounter]['label'] = 'Stats';
        $basicInformationCounter++;

        if ($classroom->category_id) {
            $category = Engine_Api::_()->getItem('eclassroom_category', $classroom->category_id);
            if ($category) {
                $result['basicInformation'][$basicInformationCounter]['name'] = 'category';
                $result['basicInformation'][$basicInformationCounter]['value'] = $category->category_name;
                $result['basicInformation'][$basicInformationCounter]['label'] = 'Category';

                $basicInformationCounter++;
                if ($classroom->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('eclassroom_category', $classroom->subcat_id);
                    if ($subcat) {
                        $result['basicInformation'][$basicInformationCounter]['name'] = 'subcategory';
                        $result['basicInformation'][$basicInformationCounter]['value'] = $subcat->category_name;
                        $result['basicInformation'][$basicInformationCounter]['label'] = 'Sub Category';
                        $basicInformationCounter++;
                        if ($classroom->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('eclassroom_category', $classroom->subsubcat_id);
                            if ($subsubcat) {
                                $result['basicInformation'][$basicInformationCounter]['name'] = 'subsubcategory';
                                $result['basicInformation'][$basicInformationCounter]['value'] = $subsubcat->category_name;
                                $result['basicInformation'][$basicInformationCounter]['label'] = 'Sub Sub Category';
                                $basicInformationCounter++;
                            }
                        }
                    }
                }
            }
        }


        $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
        $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($classroom);
        if (count($fieldStructure)) { // @todo figure out right logic
            $content = $this->view->fieldSesapiValueLoop($classroom, $fieldStructure);;
            $counter = 0;
            foreach ($content as $key => $value) {
                $result['profileDetail'][$counter]['label'] = $key;
                $result['profileDetail'][$counter]['value'] = $value;
                $counter++;
            }
        }

        $result['Detail'] = $classroom->description;
        $contactInformationCounter = 0;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'phone';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'View Phone Number';
        if ($classroom->classroom_contact_phone)
            $result['contactInformation'][$contactInformationCounter]['value'] = $classroom->classroom_contact_phone;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'mail';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Send Email';
        if ($classroom->classroom_contact_email)
            $result['contactInformation'][$contactInformationCounter]['value'] = $$classroom->classroom_contact_email;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'website';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Visit Website';
        if ($classroom->classroom_contact_website)
            $result['contactInformation'][$contactInformationCounter]['value'] = $classroom->classroom_contact_website;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'facebook';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Facebook.com';
        if ($classroom->classroom_contact_facebook)
            $result['contactInformation'][$contactInformationCounter]['value'] = $classroom->classroom_contact_facebook;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'linkedin';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Linkedin';
        if ($classroom->classroom_contact_linkedin)
            $result['contactInformation'][$contactInformationCounter]['value'] = $classroom->classroom_contact_linkedin;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'twitter';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Twitter';
        if ($classroom->classroom_contact_twitter)
            $result['contactInformation'][$contactInformationCounter]['value'] = $classroom->classroom_contact_twitter;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'instagram';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Instagram.com';
        if ($classroom->classroom_contact_instagram)
            $result['contactInformation'][$contactInformationCounter]['value'] = $classroom->classroom_contact_instagram;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'pinterest';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Pinterest.com';
        if ($classroom->classroom_contact_pinterest)
            $result['contactInformation'][$contactInformationCounter]['value'] = $classroom->classroom_contact_pinterest;

        $likeMembers = Engine_Api::_()->courses()->getMemberByLike($classroom->classroom_id);
        $favMembers = Engine_Api::_()->courses()->getMemberFavourite($classroom->classroom_id);
        $followMembers = Engine_Api::_()->courses()->getMemberFollow($classroom->classroom_id);
        $tableLikepages = Engine_Api::_()->getDbTable('likeclassrooms', 'eclassroom');
        $selelct = $tableLikepages->select()->where('classroom_id =?', $classroom->classroom_id);
        $likePageResult = $tableLikepages->fetchAll($selelct);

        if (count($likePageResult)) {
            $likePagesCounter = 0;
            $result['total_page_liked_by_this_page'] = count($likePageResult) > 4 ? count($likePageResult) - 4 : 0;

            foreach ($likePageResult as $likepage) {
                if($likePagesCounter > 3)
                    break;
                $item = Engine_Api::_()->getItem('eclassroom', $likepage->like_page_id);

                if ($item) {
                    $nameLike = $item->getTitle();;
                    $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
                    if ($image) {
                        $result['page_liked_by_this_page'][$likePagesCounter]['images'] = $image;
                    }
                    if ($nameLike) {
                        $result['page_liked_by_this_page'][$likePagesCounter]['name'] = $nameLike;
                    }
                    $result['page_liked_by_this_page'][$likePagesCounter]['classroom_id'] = $item->classroom_id;
                }else{
                    $result['total_page_liked_by_this_page'] = $result['total_page_liked_by_this_page'] > 0 ? $result['total_page_liked_by_this_page'] - 1 : 0;
                }
                $likePagesCounter++;
            }
        }

        if (count($likeMembers)) {
            $likeCounter = 0;
            $result['total_people_who_liked'] = count($likeMembers) > 4 ? count($likeMembers) - 4 : 0;
            foreach ($likeMembers as $member) {
                if($likeCounter > 3)
                    break;  
                $item = Engine_Api::_()->getItem('user', $member['poster_id']);
                if(!$item){
                    $result['total_people_who_liked'] = $result['total_people_who_liked'] > 0 ? $result['total_people_who_liked'] - 1 : 0;
                    continue; 
                }
                $nameLike = $item->getTitle();
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
                if ($image) {
                    $result['people_who_liked'][$likeCounter]['images'] = $image;
                }
                if ($nameLike) {
                    $result['people_who_liked'][$likeCounter]['name'] = $nameLike;
                }

                $result['people_who_liked'][$likeCounter]['user_id'] = $item->user_id;
                $likeCounter++;
            }
        }
        if (count($followMembers) && $canFollow) {

            $followCounter = 0;
            $result['total_people_who_follow_this'] = count($followMembers) > 4 ? count($followMembers) - 4 : 0;
            foreach ($followMembers as $member) {
                if($followCounter > 3)
                    break;
                $item = Engine_Api::_()->getItem('user', $member['owner_id']);


                if(count($item->toArray()) == 0){
                    $result['total_people_who_follow_this'] = $result['total_people_who_follow_this'] > 0 ? $result['total_people_who_follow_this'] - 1 : 0;
                    continue;
                }


                $name = $item->getTitle();

                $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");


                if ($image) {
                    $result['people_who_follow_this'][$followCounter]['images'] = $image;
                }
                if ($name) {
                    $result['people_who_follow_this'][$followCounter]['name'] = $name;
                }
                $result['people_who_follow_this'][$followCounter]['user_id'] = $item->user_id;
                $followCounter++;
            }

        }


        if (count($favMembers) && $canFavourite) {
            $favCounter = 0;
            $result['total_people_who_favourited'] = count($favMembers) > 4 ? count($favMembers) - 4 : 0;
            foreach ($favMembers as $member) {
                if($favCounter > 3)
                    break;
                $item = Engine_Api::_()->getItem('user', $member['owner_id']);
                if(!$item){
                    $result['total_people_who_favourited'] = $result['total_people_who_favourited']> 0 ? $result['total_people_who_favourited'] - 1 : 0;
                    continue;
                }
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
                $nameFav = $item->getTitle();
                if ($image) {
                    $result['people_who_favourited'][$favCounter]['images'] = $image;
                } else {

                }
                if ($nameFav) {
                    $result['people_who_favourited'][$favCounter]['name'] = $nameFav;
                }
                $result['people_who_favourited'][$favCounter]['user_id'] = $item->user_id;
                $favCounter++;
            }
        }

        return $result;
    }
    function getDay($number)
    {
        switch ($number) {
            case 1:
                return "Monday";
                break;
            case 2:
                return "Tuesday";
                break;
            case 3:
                return "Wednesday";
                break;
            case 4:
                return "Thursday";
                break;
            case 5:
                return "Friday";
                break;
            case 6:
                return "Saturday";
                break;
            case 7:
                return "Sunday";
                break;
        }
    }
  public function memberAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('classroom');
    if (!$subject->authorization()->isAllowed($viewer, 'view')) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    // Get params
    $page = $this->_getParam('page', 1);
    $search = $this->_getParam('search');
    $waiting = $this->_getParam('waiting', false);
    // Prepare data
    $classroom = Engine_Api::_()->core()->getSubject();
    $result = array();
    if ($viewer->getIdentity() && ($classroom->isOwner($viewer))) {
        $waitingMembers = Zend_Paginator::factory($classroom->membership()->getMembersSelect(false));
        if ($waitingMembers->getTotalItemCount() > 0 && !$waiting) {
            $result['options']["label"] = $this->view->translate('See Waiting');
            $result['options']["name"] = 'waiting';
            $result['options']["value"] = '1';
        }
    }
    // if not showing waiting members, get full members
    $select = $classroom->membership()->getMembersObjectSelect();
    if ($search)
        $select->where('displayname LIKE ?', '%' . $search . '%');
    $fullMembers = Zend_Paginator::factory($select);
    if ($fullMembers->getTotalItemCount() > 0 && ($viewer->getIdentity() && ($classroom->isOwner($viewer))) && $waiting) {
        $result['options']["label"] = $this->view->translate('View all approved members');
        $result['options']["name"] = 'waiting';
        $result['options']["value"] = '0';
    }
    // if showing waiting members, or no full members
    if (($viewer->getIdentity() && ($classroom->isOwner($viewer))) && ($waiting || ($fullMembers->getTotalItemCount() <= 0 && $search == ''))) {
        $paginator = $waitingMembers;
        $waiting = true;
    } else {
        $paginator = $fullMembers;
        $waiting = false;
    }
    // Set item count per classroom and current classroom number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerClassroom', 10));
    $paginator->setCurrentPageNumber($page);
    $result['members'] = array();
    $counterLoop = 0;
    foreach ($paginator as $member) {
      if (!empty($member->resource_id)) {
          $memberInfo = $member;
          $member = Engine_Api::_()->getItem('user', $memberInfo->user_id);
      } else {
          $memberInfo = $classroom->membership()->getMemberInfo($member);
      }
      if (!$member->getIdentity())
          continue;
      $resource = $member->toArray();
      if ($classroom->isOwner($member)){
        $resource['displayname'] = $resource['displayname'] . " (owner)";
      }
      unset($resource['lastlogin_ip']);
      unset($resource['creation_ip']);
      $result['members'][$counterLoop] = $resource;
      $result['members'][$counterLoop]['owner_photo'] = $this->getBaseUrl(true, (!empty($member->photo_id)) ? $member->getPhotoUrl() : '/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
      if ($classroom->isOwner($viewer) && !$classroom->isOwner($member)) {
          $optionCounter = 0;
          if (!$classroom->isOwner($member) && $memberInfo->active == true) {
              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'removemember';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Remove Member');
              $optionCounter++;
          }
          if ($memberInfo->active == false && $memberInfo->resource_approved == false) {
              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'approverequest';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Approve Request');
              $optionCounter++;
              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'rejectrequest';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Reject Request');
              $optionCounter++;
          }
          if ($memberInfo->active == false && $memberInfo->resource_approved == true) {
              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'cancelinvite';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Cancel Invite');
              $optionCounter++;
          }
      }
      $counterLoop++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function moreMembersAction(){
		$id = $this->_getParam('classroom_id',null);
		if(!$id){
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
    if (!Engine_Api::_()->core()->hasSubject()) {
      $classroom = Engine_Api::_()->getItem('classroom', $id);
    } else {
      $classroom = Engine_Api::_()->core()->getSubject();
    }
		$classroomcheck = false;
		if($this->_getParam('type',null) == 'like'){
			$coreLikeTable = Engine_Api::_()->getDbTable('likes', 'core');
			$select = $coreLikeTable->select()->from($coreLikeTable->info('name'), 'poster_id')
            ->where('resource_id =?', $classroom->classroom_id )
            ->where('resource_type =?', 'classroom');
		}else if($this->_getParam('type',null) == 'follow'){
			$followTable = Engine_Api::_()->getDbTable('followers', 'eclassroom');
			$select = $followTable->select()->from($followTable->info('name'), 'owner_id')
            ->where('resource_id =?', $classroom->classroom_id )
            ->where('resource_type =?', 'classroom');
		}else if($this->_getParam('type',null) == 'favourite'){
			$favouriteTable = Engine_Api::_()->getDbTable('favourites', 'eclassroom');
			$select = $favouriteTable->select()->from($favouriteTable->info('name'), 'owner_id')
            ->where('resource_id =?', $classroom->classroom_id )
            ->where('resource_type =?', 'classroom');
		}else if($this->_getParam('type',null) == 'classroom'){
			$tableLikeclassrooms = Engine_Api::_()->getDbTable('likeclassrooms', 'eclassroom');
			$select = $tableLikeclassrooms->select()->where('classroom_id =?', $classroom->classroom_id);
			$classroomcheck = true;
		}
		if($select){
			$Members = Zend_Paginator::factory($select);
		}
		$Members->setItemCountPerPage($this->_getParam('limit', 10));
		$Members->setCurrentPageNumber($this->_getParam('page', 1));
    if(count($Members) && $classroomcheck) {
			$likeClassroomsCounter = 0;
			foreach ($Members as $likeclassroom) {
				$item = Engine_Api::_()->getItem('classroom', $likeclassroom->like_classroom_id);
				if ($item) {
					$nameLike = $item->getTitle();;
					$image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
					if ($image) {
						$result['classroom_liked_by_this_classroom'][$likeClassroomsCounter]['images'] = $image;
					}
					if ($nameLike) {
						$result['classroom_liked_by_this_classroom'][$likeClassroomsCounter]['name'] = $nameLike;
					}
					$result['classroom_liked_by_this_classroom'][$likeClassroomsCounter]['classroom_id'] = $item->classroom_id;
				}
				$likeClassroomsCounter++;
			}
		}
    if (count($Members) && !$classroomcheck && $this->_getParam('type',null) != 'like') {
      if(!empty($_GET['sesapi_platform']) && $_GET['sesapi_platform'] == 1){
        foreach ($Members as $user)
          $userIds[] = $user['owner_id'];
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $userIds);
        $result = $this->memberResult($recipientsUsers);
      }else{
			  $Counter = 0;
        foreach ($Members as $member) {
          $item = Engine_Api::_()->getItem('user', $member['owner_id']);
          $nameLike = $item->getTitle();
          $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
          if ($image) {
            $result['members'][$Counter]['images'] = $image;
          }
          if ($nameLike) {
            $result['members'][$Counter]['name'] = $nameLike;
          }
          $result['members'][$Counter]['user_id'] = $item->user_id;
          $Counter++;
        }
      }
		}
		if (count($Members) && !$classroomcheck && $this->_getParam('type',null) == 'like') {
      if(!empty($_GET['sesapi_platform']) && $_GET['sesapi_platform'] == 1){
        foreach ($Members as $user)
          $userIds[] = $user['poster_id'];
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $userIds);
        $result = $this->memberResult($recipientsUsers);
      }else{
			  $Counter = 0;
        foreach ($Members as $member) {
          $item = Engine_Api::_()->getItem('user', $member['poster_id']);
          $itemArray = $item->toArray();
          if(!empty($itemArray)){
            $nameLike = $item->getTitle();
          $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
          if ($image) {
            $result['members'][$Counter]['images'] = $image;
          }
          if ($nameLike) {
            $result['members'][$Counter]['name'] = $nameLike;
          }
          $result['members'][$Counter]['user_id'] = $item->user_id;
          $Counter++;
          }
        }
      }
		}
		// Set item count per classroom and current classroom number
    $extraParams['pagging']['total_page'] = $Members->getPages()->pageCount;
    $extraParams['pagging']['total'] = $Members->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $Members->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));
	}
  public function memberResult($paginator){
    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')){
      $memberEnable = true;
    }
    $followActive = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active',1);
    if($followActive){
      $unfollowText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.unfollowtext','Unfollow'));
      $followText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.followtext','Follow'));
    }
    foreach($paginator as $member){
      $result['notification'][$counterLoop]['user_id'] = $member->getIdentity();
      $result['notification'][$counterLoop]['title'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $member->getTitle());
      if(!empty($member->location))
          $result['notification'][$counterLoop]['location'] =   $member->location;
      //follow
      if($followActive && $viewer->getIdentity() && $viewer->getIdentity() != $member->getIdentity()){
          $FollowUser = Engine_Api::_()->sesmember()->getFollowStatus($member->user_id);
          if(!$FollowUser){
              $result['notification'][$counterLoop]['follow']['action'] = 'follow';
              $result['notification'][$counterLoop]['follow']['text'] = $followText;
          }else{
              $result['notification'][$counterLoop]['follow']['action'] = 'unfollow';
              $result['notification'][$counterLoop]['follow']['text'] = $unfollowText;
          }
      }
      if(!empty($memberEnable)){
      //mutual friends
      $mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($member, $viewer);
      if(!$member->isSelf($viewer)){
          $result['notification'][$counterLoop]['mutualFriends'] = $mfriend == 1 ? $mfriend.$this->view->translate(" mutual friend") : $mfriend.$this->view->translate(" mutual friends");
      }
      }
      $result['notification'][$counterLoop]['user_image'] = $this->userImage($member->getIdentity(),"thumb.profile");
      $result['notification'][$counterLoop]['membership'] = $this->friendRequest($member);
      $counterLoop++;
    }
    return $result;
  }
  public function leaveAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    if ($subject->isOwner($viewer))
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
       
    // Make form
    $form = new Eclassroom_Form_Member_Leave();
    if($form->getElement('token'))
        $form->removeElement('token');
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
    }
    // Not posting
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (count($validateFields))
            $this->validateFormFields($validateFields);
    }
    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $subject->membership()->removeMember($viewer);
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => 'Classroom left')));
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }
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
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity());
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
  
  public function addmorephotosAction()
    {
        $album_id = $this->_getParam('album_id', false);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('eclassroom_album', $album_id);
            $classroom_id = $album->classroom_id;
        } else {

        }

        $form = new Eclassroom_Form_Album();
        $classroom = Engine_Api::_()->getItem('classroom', $classroom_id);

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();

        $photoTable = Engine_Api::_()->getDbTable('photos', 'eclassroom');
        $uploadSource = $_FILES['attachmentImage'];


        $photoArray = array(
            'classroom_id' => $classroom->classroom_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'eclassroom')->getAdapter();
        $db->beginTransaction();
        try {
            foreach ($uploadSource['name'] as $name) {
                $images['name'] = $name;
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
                $photo = $photo->setAlbumPhoto($images, false, false, $album);
                $photo->collection_id = $photo->album_id;
                $photo->save();
                $photosource[] = $photo->getIdentity();
                $counter++;
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $_POST['classroom_id'] = $classroom_id;
        $_POST['file'] = implode(' ', $uploadSource);
        $form->album->setValue($album_id);
        $album = $form->saveValues();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('album_id' => $album->album_id, 'message' => $this->view->translate('Photo added successfully.'))));
    }

  public function uploadphotoAction()
  {
    if (!Engine_Api::_()->core()->hasSubject()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    $classroom = Engine_Api::_()->core()->getSubject();
    if (!$classroom)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This classroom does not exist.'), 'result' => array()));
    $photo = $classroom->photo_id;
    if (isset($_FILES['Filedata']))
        $data = $_FILES['Filedata'];
    else if (isset($_FILES['webcam']))
        $data = $_FILES['webcam'];
    else if(isset($_FILES['image']))
      $data = $_FILES['image'];
    if (!$data) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    $classroom->setPhoto($data, '', 'profile');

    $viewer = Engine_Api::_()->user()->getViewer();
    $getPhotoId = Engine_Api::_()->getDbTable('photos', 'eclassroom')->getPhotoId($classroom->photo_id);
    $photo = Engine_Api::_()->getItem('eclassroom_photo', $getPhotoId);

        $classroomlink = '<a href="' . $classroom->getHref() . '">' . $classroom->getTitle() . '</a>';
    $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $photo, 'eclassroom_classroom_pfphoto', null, array('classroomname' => $classroomlink));
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
      $detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
      if($detail_id) {
        $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
        $detailAction->sesresource_id = $classroom->getIdentity();
        $detailAction->sesresource_type = $classroom->getType();
        $detailAction->save();
      }
    }
    if ($action)
      Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $photo);
        $file = array('main' => $this->getBaseUrl(true,$classroom->getPhotoUrl()));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully photo uploaded.'), 'images' => $file)));
  }
  public function removephotoAction()
  {
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        } else {
            $classroom = Engine_Api::_()->core()->getSubject();

        }
        if (!$classroom)
            $classroom = Engine_Api::_()->getItem('classroom', $this->_getparam('classroom_id', null));

        if (!$classroom)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        if (isset($classroom->photo_id) && $classroom->photo_id > 0) {
            $classroom->photo_id = 0;
            $classroom->save();
        }
        $file = array('main' => $classroom->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully photo deleted.'), 'images' => $file));
//    echo json_encode(array('file' => $classroom->getPhotoUrl()));
//    die;
    }
  public function uploadcoverAction()
  {
      if (!Engine_Api::_()->core()->hasSubject()) {
            $classroom = Engine_Api::_()->getItem('classroom', $this->_getparam('classroom_id', null));
      }else{
        $classroom = Engine_Api::_()->core()->getSubject();
      }
        $classroom = Engine_Api::_()->core()->getSubject();
        if (!$classroom)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $cover_photo = $classroom->cover;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        else if(isset($_FILES['image']))
          $data = $_FILES['image'];
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $classroom->setCoverPhoto($data);

        $viewer = Engine_Api::_()->user()->getViewer();
        $getPhotoId = Engine_Api::_()->getDbTable('photos', 'eclassroom')->getPhotoId($classroom->cover);
        $photo = Engine_Api::_()->getItem('eclassroom_photo', $getPhotoId);
        $classroomlink = '<a href="' . $classroom->getHref() . '">' . $classroom->getTitle() . '</a>';
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $photo, 'eclassroom_classroom_coverphoto', null, array('classroomname' => $classroomlink));
      if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && !empty($action)) {
        $detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
        if($detail_id) {
          $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
          $detailAction->sesresource_id = $classroom->getIdentity();
          $detailAction->sesresource_type = $classroom->getType();
          $detailAction->save();
        }
      }
      if ($action)
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);
      if ($cover_photo != 0) {
          $im = Engine_Api::_()->getItem('storage_file', $cover_photo);
          $im->delete();
      }
      $file['main'] = $this->getBaseUrl(true, $classroom->getCoverPhotoUrl());
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully cover photo uploaded.'), 'images' => $file)));
  }
  public function removecoverAction()
  {

      if (!Engine_Api::_()->core()->hasSubject()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      }
      $classroom = Engine_Api::_()->core()->getSubject();
      if (!$classroom)
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      if (isset($classroom->cover) && $classroom->cover > 0) {
          $im = Engine_Api::_()->getItem('storage_file', $classroom->cover);
          $classroom->cover = 0;
          $classroom->save();
          $im->delete();
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully deleted cover photo.'))));

  }

  function getType($type){
    switch ($type) {
        case "booknow":
        return 'Book Now';
        case "callnow":
        return "Call Now";
        case "contactus":
        return "Contact Us";
        case "sendmessage":
        return "Send Message";
        case "signup":
        return "Sign Up";
        case "sendemail":
        return "Send Email";
        case "watchvideo":
        return "Watch Video";
        case "learnmore":
        return "Learn More";
        case "shopnow":
        return "Shop Now";
        case "seeoffers":
        return "See Offers";
        case "useapp":
        return "Use App";
        case "playgames":
        return "Play Games";
    }
    return "";
}
}
