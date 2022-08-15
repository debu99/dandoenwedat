<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ProfileController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesgroup_ProfileController extends Sesapi_Controller_Action_Standard
{
    public function init(){
        if (!$this->_helper->requireAuth()->setAuthParams('sesgroup_group', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    public function createAction(){
        $category_id = $this->_getParam('category_id', 0);
        $viewer = Engine_Api::_()->user()->getViewer();
      if (!$this->_helper->requireAuth()->setAuthParams('sesgroup_group', null, 'create')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
      $viewerId = $viewer->getIdentity();
      $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        $categoryShowAble = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup.category.selection', 0);
        $totalGroup = Engine_Api::_()->getDbTable('groups', 'sesgroup')->countGroups($viewer->getIdentity());
        $allowGroupCount = Engine_Api::_()->authorization()->getPermission($levelId, 'sesgroup_group', 'group_count');
        $errorcode['error_code'] = 'PME' . $totalGroup;
        if ($allowGroupCount != 0 && $totalGroup >= $allowGroupCount) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have reached the limit of group creation. Please contact to the site administrator.'), 'result' => $errorcode));
        }
        //Start Package Work
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesgrouppackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppackage.enable.package', 0) && (_SESAPI_VERSION_ANDROID >= 2.5 || _SESAPI_VERSION_IOS >= 1.7)) {
            $package_id = $this->_getParam('package_id', 0);
            $existingpackage_id = $this->_getParam('existing_package_id', 0);
            $package = Engine_Api::_()->getItem('sesgrouppackage_package', $package_id);
            $existingpackage = Engine_Api::_()->getItem('sesgrouppackage_orderspackage', $existingpackage_id);
            if ($existingpackage) {
                $package = Engine_Api::_()->getItem('sesgrouppackage_package', $existingpackage->package_id);
            }
            if (!$package && !$existingpackage) {
                // check package exists for this member level
                $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'sesgrouppackage')->getPackage(array('member_level' => $viewer->level_id));
                if (count($packageMemberLevel)) {
                    // redirect to package group
                    $packageResult = $this->groupPackage();
                    if($packageResult){
                        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $packageResult));
                    }else{
                        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data Not Found.'), 'result' => array()));
                    }
                }
            }

            if ($existingpackage) {
                $canCreate = Engine_Api::_()->getDbTable('orderspackages', 'sesgrouppackage')->checkUserPackage($this->_getParam('existing_package_id', 0), $viewer->getIdentity());
                if (!$canCreate){
                    //$packageResult = $this->groupPackage();
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have not permission to access this resource'), 'result' =>array()));
                }
                //return $this->_helper->redirector->gotoRoute(array('action' => 'group'), 'sesgrouppackage_general', true);
            }
        }
        //End Package Work
        $categories = Engine_Api::_()->getDbtable('categories', 'sesgroup')->getCategory();
		$checkCategoriesForMemberLevel = Engine_Api::_()->getDbTable('categories', 'sesgroup')->getCategoriesAssoc(array('member_levels' => 1));
        if ($categoryShowAble && !$category_id && count($categories) && $checkCategoriesForMemberLevel) {
            $categoryShowAbletypeimage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup_category_icon', 0);
            $category_counter = 0;
            foreach ($categories as $category) {
				
                if ($category->thumbnail && $categoryShowAbletypeimage == 2)
                    $result_category['category'][$category_counter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
                if ($category->cat_icon && $categoryShowAbletypeimage == 1)
                    $result_category['category'][$category_counter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
                if ($category->colored_icon && $categoryShowAbletypeimage == 0)
                    $result_category['category'][$category_counter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
                $result_category['category'][$category_counter]['slug'] = $category->slug;
                $result_category['category'][$category_counter]['category_id'] = $category->category_id;
                $result_category['category'][$category_counter]['category_name'] = $category->category_name;
                $result_category['category'][$category_counter]['total_group_categories'] = $category->total_group_categories;
                $category_counter++;
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result_category));
        }
        $parentId = $this->_getParam('parent_id', 0);
        $subGroupCreatePemission = false;
        if ($parentId) {
          $subject = Engine_Api::_()->getItem('sesgroup_group', $parentId);
          if ($subject) {
            if ((!Engine_Api::_()->authorization()->isAllowed('sesgroup_group', $viewer, 'auth_subgroup') || $subject->parent_id)) {
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            $subGroupCreatePemission = Engine_Api::_()->getDbTable('grouproles', 'sesgroup')->toCheckUserGroupRole($viewer->getIdentity(), $subject->getIdentity(), 'post_behalf_group');
            if (!$subGroupCreatePemission)
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            }
          }
        }
        $quckCreate = 0;
        $form = new Sesgroup_Form_Create();
        $form->removeElement('removeimage');
		if($form->getElement('enable_lock')){
			$form->removeElement('enable_lock');
			$form->removeElement('group_password');
		}
		if($form->getElement('can_join')){
			$form->removeElement('can_join');
		}
        $form->removeElement('removeimage2');
        $form->removeElement('group_main_photo_preview');
        $form->removeElement('photo-uploader');
        if ($form->getElement('category_id'))
            $form->getElement('category_id')->setValue($this->_getParam('category_id'));
        if ($form->getElement('group_location'))
            $form->getElement('group_location')->setLabel('Location');
       
       if($_GET['sesapi_platform'] == 1){
         if($form->getElement('member_title_singular')){
          $form->getElement('member_title_singular')->setDescription("");  
          $form->getElement('member_title_plural')->setDescription("");  
         }
       }
       
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesgroup_group'));
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
        if (!$quckCreate && isset($_POST['custom_url_group']) && !empty($_POST['custom_url_group'])) {
            $custom_url = Engine_Api::_()->getDbtable('groups', 'sesgroup')->checkCustomUrl($_POST['custom_url_group']);
            if ($custom_url) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("custom_url_taken"), 'result' => array()));
            }
        }
        $values = array();
        if (!$quckCreate) {
            $values = $form->getValues();
            $values['location'] = isset($_POST['group_location']) ? $_POST['group_location'] : '';
        }
        $values['can_join'] = 1;
        $values['owner_id'] = $viewer->getIdentity();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        if (!$quckCreate && $settings->getSetting('sesgroup.groupmainphoto', 1)) {
            if (empty($_FILES['photo']['size']) && empty($_FILES['image']['size'])) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Main Photo is a required field.'), 'result' => array()));
                //        $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
            }
        }
        if (isset($values['networks'])) {
            //Start Network Work
            $networkValues = array();
            foreach (Engine_Api::_()->getDbtable('networks', 'network')->fetchAll() as $network) {
                $networkValues[] = $network->network_id;
            }
            if (@$values['networks'])
                $values['networks'] = ',' . implode(',', $values['networks']);
            else
                $values['networks'] = '';
            //End Network Work
        }
        if (!isset($values['can_invite'])) {
            $values['can_invite'] = 1;
        }
        if (!isset($values['can_join']))
            $values['approval'] = $settings->getSetting('sesgroup.default.joinoption', 1) ? 0 : 1;
        elseif (!isset($values['approval']))
            $values['approval'] = $settings->getSetting('sesgroup.default.approvaloption', 1) ? 0 : 1;

        $groupTable = Engine_Api::_()->getDbtable('groups', 'sesgroup');
        $db = $groupTable->getAdapter();
        $db->beginTransaction();
        try {
            // Create group
            $group = $groupTable->createRow();
            if (!$quckCreate && empty($_POST['lat'])) {
                unset($values['location']);
                unset($values['lat']);
                unset($values['lng']);
                unset($values['venue_name']);
            }
            if (!empty($_POST['group_location'])) {
                $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['group_location']);
                if ($latlng) {
                    $values['lat'] = $_POST['lat'] = $latlng['lat'];
                    $values['lng'] = $_POST['lng'] = $latlng['lng'];
					$values['location'] = $_POST['group_location'];
                }
            }
            $sesgroup_draft = $settings->getSetting('sesgroup.draft', 1);
            if (empty($sesgroup_draft)) {
                $values['draft'] = 1;
            }
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup.global.search', 1) && !isset($values['search'])){
                $values['search'] = 1;
            }
            if (!$quckCreate) {
                if (empty($values['category_id']))
                    $values['category_id'] = 0;
                if (empty($values['subsubcat_id']))
                    $values['subsubcat_id'] = 0;
                if (empty($values['subcat_id']))
                    $values['subcat_id'] = 0;
            }
            if(!empty($_POST['custom_url_group']))
              $values['custom_url'] = $_POST['custom_url_group'];
            if (isset($package)) {
                $values['package_id'] = $package->getIdentity();
                if ($package->isFree()) {
                    if (isset($params['group_approve']) && $params['group_approve'])
                        $values['is_approved'] = 1;
                } else
                    $values['is_approved'] = 0;
                if ($existingpackage) {
                    $values['existing_package_order'] = $existingpackage->getIdentity();
                    $values['orderspackage_id'] = $existingpackage->getIdentity();
                    $existingpackage->item_count = $existingpackage->item_count - 1;
                    $existingpackage->save();
                    $params = json_decode($package->params, true);
                    if (isset($params['group_approve']) && $params['group_approve'])
                        $values['is_approved'] = 1;
                    if (isset($params['group_featured']) && $params['group_featured'])
                        $values['featured'] = 1;
                    if (isset($params['group_sponsored']) && $params['group_sponsored'])
                        $values['sponsored'] = 1;
                    if (isset($params['group_verified']) && $params['group_verified'])
                        $values['verified'] = 1;
                    if (isset($params['group_hot']) && $params['group_hot'])
                        $values['hot'] = 1;
                }
            } else {
                if (!isset($package) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesgrouppackage')) {
                    $values['package_id'] = Engine_Api::_()->getDbTable('packages', 'sesgrouppackage')->getDefaultPackage();
                }
            }

            $group->setFromArray($values);
            if (isset($_POST['sesgroup_title'])) {
                $group->title = $_POST['sesgroup_title'];
                $group->category_id = $_POST['category_id'];
                if (isset($_POST['subcat_id']))
                    $group->category_id = $_POST['category_id'];
                if (isset($_POST['subsubcat_id']))
                    $group->category_id = $_POST['category_id'];
            }
            $group->parent_id = $parentId;
            if (!isset($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            $group->view_privacy = $values['auth_view'];
            $group->groupstyle = 1;
            $group->save();

            //Start Default Package Order Work
            if (isset($package) && $package->isFree()) {
                if (!$existingpackage) {
                    $transactionsOrdersTable = Engine_Api::_()->getDbtable('orderspackages', 'sesgrouppackage');
                    $transactionsOrdersTable->insert(array(
                        'owner_id' => $viewer->user_id,
                        'item_count' => ($package->item_count - 1 ),
                        'package_id' => $package->getIdentity(),
                        'state' => 'active',
                        'expiration_date' => '3000-00-00 00:00:00',
                        'ip_address' => $_SERVER['REMOTE_ADDR'],
                        'creation_date' => new Zend_Db_Expr('NOW()'),
                        'modified_date' => new Zend_Db_Expr('NOW()'),
                    ));
                    $group->orderspackage_id = $transactionsOrdersTable->getAdapter()->lastInsertId();
                    $group->existing_package_order = 0;
                } else {
                    $existingpackage->item_count = $existingpackage->item_count--;
                    $existingpackage->save();
                }
            }
            //End Default package Order Work

            if (isset($values['tags']) && count($values['tags'])>0) {
                $tags = preg_split('/[,]+/', $values['tags']);
                $group->tags()->addTagMaps($viewer, $tags);
                $group->seo_keywords = implode(',', $tags);
                $group->save();
            }
            if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && !empty($_POST['group_location'])) {
                $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                $dbGetInsert->query('INSERT INTO engine4_sesgroup_locations (group_id,location,venue, lat, lng ,city,state,zip,country,address,address2, is_default) VALUES ("' . $group->group_id . '","' . $_POST['group_location'] . '", "' . $_POST['venue_name'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "1") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue_name'] . '"');
                $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $group->group_id . '","' . $_POST['group_location'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "sesgroup_group")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
            }
            //Manage Apps
            Engine_Db_Table::getDefaultAdapter()->query('INSERT IGNORE INTO `engine4_sesgroup_managegroupapps` (`group_id`) VALUES ("' . $group->group_id . '");');

            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup.auto.join', 1)) {
                $group->membership()->addMember($viewer)->setUserApproved($viewer)->setResourceApproved($viewer);
            }
            if (!isset($package)) {
                if (!Engine_Api::_()->authorization()->getPermission($levelId, 'sesgroup_group', 'group_approve'))
                    $group->is_approved = 0;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'sesgroup_group', 'group_featured'))
                    $group->featured = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'sesgroup_group', 'group_sponsored'))
                    $group->sponsored = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'sesgroup_group', 'group_verified'))
                    $group->verified = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'sesgroup_group', 'group_hot'))
                    $group->hot = 1;
            }
            // Add photo

            if (!empty($_FILES['photo']['size'])) {
                $group->setPhoto($form->photo, '', 'profile');
            }
            if (!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) {
                $group->setPhoto($_FILES['image'], '', 'profile');
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $subgroupRoles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network');
            if (!isset($values['auth_view']) || empty($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            if (!isset($values['auth_comment']) || empty($values['auth_comment'])) {
                $values['auth_comment'] = 'everyone';
            }
            $createSubgroupMax = array_search($values['create_sub_group'], $subgroupRoles);
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $albumMax = array_search($values['auth_album'], $roles);
            $videoMax = array_search($values['auth_video'], $roles);
            foreach ($roles as $i => $role) {
                $auth->setAllowed($group, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($group, $role, 'comment', ($i <= $commentMax));

                $auth->setAllowed($group, $role, 'album', ($i <= $albumMax));
                $auth->setAllowed($group, $role, 'video', ($i <= $videoMax));
            }
            foreach ($subgroupRoles as $i => $role) {
                $auth->setAllowed($group, $role, 'create_sub_group', ($i <= $createSubgroupMax));
            }
            if (!$quckCreate) {
                //Add fields
                $customfieldform = $form->getSubForm('fields');
                if ($customfieldform) {
                    $customfieldform->setItem($group);
                    $customfieldform->saveValues();
                }
            }
            $group->save();
            if (!$group->custom_url)
                $group->custom_url = $group->getIdentity();
           
            $group->save();
            $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup.autoopenpopup', 1);
            if ($autoOpenSharePopup && $group->draft && $group->is_approved) {
                $_SESSION['newGroup'] = true;
            }
            //insert admin of group
            $groupRole = Engine_Api::_()->getDbTable('grouproles', 'sesgroup')->createRow();
            $groupRole->user_id = $this->view->viewer()->getIdentity();
            $groupRole->group_id = $group->getIdentity();
            $groupRole->memberrole_id = 1;
            $groupRole->save();
            // Commit
            $db->commit();
            //Start Activity Feed Work
            if ($group->draft == 1 && $group->is_approved == 1) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $group, 'sesgroup_group_create');
                if ($action) {
                    $activityApi->attachActivity($action, $group);
                }
            }
            //End Activity Feed Work
            //Start Send Approval Request to Admin
            if (!$group->is_approved) {
                if (isset($package) && $package->price > 0) {
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($group->getOwner(), $viewer, $group, 'sesgroup_payment_notify_group');
                } else {
                    $getAdminnSuperAdmins = Engine_Api::_()->sesgroup()->getAdminnSuperAdmins();
                    foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                        $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $group, 'sesgroup_group_waitingadminapproval');

                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_sesgroup_group_adminapproval', array('sender_title' => $group->getOwner()->getTitle(), 'adminmanage_link' => 'admin/sesgroup/manage', 'object_link' => $group->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($group->getOwner(), 'notify_sesgroup_group_groupsentforapproval', array('group_title' => $group->getTitle(), 'object_link' => $group->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($viewer, $viewer, $group, 'sesgroup_group_waitingapproval');
                Engine_Api::_()->sesgroup()->sendMailNotification(array('group' => $group));
            }
            //Send mail to all super admin and admins
            if ($group->is_approved) {
                $getAdminnSuperAdmins = Engine_Api::_()->sesgroup()->getAdminnSuperAdmins();
                foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                    $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_sesgroup_group_superadmin', array('sender_title' => $group->getOwner()->getTitle(), 'object_link' => $group->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                $receiverEmails = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup.receivenewalertemails');
                if (!empty($receiverEmails)) {
                    $receiverEmails = explode(',', $receiverEmails);
                    foreach ($receiverEmails as $receiverEmail) {
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiverEmail, 'notify_sesgroup_group_superadmin', array('sender_title' => $group->getOwner()->getTitle(), 'object_link' => $group->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                }
            }
			$redirection = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup.redirect', 1);
			if(!$group->is_approved)
				$redirect = 'manage';
			else if($group->is_approved && $redirection == 1)
				$redirect = 'dashboard';
			else 
				$redirect = 'view';
            //End Work Here.
            if (!$group->is_approved) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Group created successfully and send to admin approval.'), 'group_id' => 0,'redirect'=>$redirect)));
            } else
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('group_id' => $group->getIdentity(), 'success_message' => $this->view->translate('Group created successfully.'),'redirect'=>$redirect)));
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The image you selected was too large.'), 'result' => array()));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function groupPackage() {
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $result = array();
        $packages = $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'sesgrouppackage')->getPackage(array('member_level' => $viewer->level_id, 'enabled' => 0));
        if (!count($packageMemberLevel) || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppackage.enable.package', 0))
            return true;
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'sesgrouppackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppackage.package.info', array_keys($information));
        if(count($existingleftpackages)){
            $counterleft = 0;
            foreach ($existingleftpackages as $leftpackages) {
                $package = Engine_Api::_()->getItem('sesgrouppackage_package', $leftpackages->package_id);
                $enableModules = json_decode($package->params, true);
                $result['existingleftpackages'][$counterleft] = $package->toArray();
                //$result['existingleftpackages'][$counterleft]['params'] = $enableModules;
                $result['existingleftpackages'][$counterleft]['params'] = array();
                $paramscounter = 0;
                $result['existingleftpackages'][$counterleft]['existing_package_id'] = $leftpackages->getIdentity();
                if(!$package->isFree()){
                    if($package->recurrence_type == 'day')
                        $result['existingleftpackages'][$counterleft]['payment_type'] = $this->view->translate('Daily');
                    elseif($package->price && $package->recurrence_type != 'forever')
                        $result['existingleftpackages'][$counterleft]['payment_type']  = $this->view->translate(ucfirst($package->recurrence_type).'ly');
                    elseif($package->recurrence_type == 'forever')
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($package->price,'','',true));
                    else
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  $this->view->translate('Free');
                }else{
                    $result['existingleftpackages'][$counterleft]['payment_type'] = $this->view->translate("FREE");
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'billing_duration';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Billing Duration');
                if($package->duration_type == 'forever'){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Forever');
                }
                else{
                    if($package->duration > 1){
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->duration . ' ' . ucfirst($package->duration_type).'s';
                    }
                    else{
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] =   $package->duration . ' ' . ucfirst($package->duration_type);
                    }
                }
                $paramscounter++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'recurrence_type';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Billing Cycle');
                if($package->recurrence_type == 'day')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Daily');
                elseif($package->price && $package->recurrence_type != 'forever')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate(ucfirst($package->recurrence_type).'ly');
                elseif($package->recurrence_type == 'forever')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_featured';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_sponsored'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                if(in_array('sponsored',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_verified';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_verified'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_hot';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_hot'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_bgphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_bgphoto'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'upload_mainphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Main Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['upload_mainphoto'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'upload_cover';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Cover Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['upload_cover'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_choose_style';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_choose_style'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_count';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Pages Count');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = !$package->item_count? $this->view->translate("Unlimited") : $package->item_count.' ( '.$leftpackages->item_count.' Left )' ;
                $paramscounter ++;
                $paramscountersuscribe = 0 ;
                if(!$package->isOneTime()){
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['name']= 'creation_date';
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['label']= $this->view->translate("Subscribed on: ");
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['value']=  date('d F Y', strtotime($leftpackages->creation_date));
                    $paramscountersuscribe++;
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['name']= 'expiration_date';
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['label']= $this->view->translate("Next Payment Date: ");
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['value']=  date('d F Y', strtotime($leftpackages->expiration_date));
                    $paramscountersuscribe++;
                }
                $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($package->price,'','',true);
                $counterleft++;
            }
        }
        $counter = 0;
        if(count($packages)){
            foreach($packages as $packages){
                $enableModulesPackages = json_decode($packages->params,true);
                $result['packages'][$counter] = $packages->toArray();
                //$result['packages'][$counter]['params'] = $enableModulesPackages;
                $result['packages'][$counter]['params'] = array();
                $paramscounter = 0;
                if(!$packages->isFree()){
                    if($packages->recurrence_type == 'day')
                        $result['packages'][$counter]['payment_type'] = $this->view->translate('Daily');
                    elseif($packages->price && $packages->recurrence_type != 'forever')
                        $result['packages'][$counter]['payment_type']  = $this->view->translate(ucfirst($packages->recurrence_type).'ly');
                    elseif($packages->recurrence_type == 'forever')
                        $result['packages'][$counter]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($packages->price,'','',true));
                    else
                        $result['packages'][$counter]['payment_type'] =  $this->view->translate('Free');
                }else{
                    $result['packages'][$counter]['payment_type'] = $this->view->translate("FREE");
                }
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'billing_duration';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Billing Duration');
                 if($packages->duration_type == 'forever'){
                     $result['packages'][$counter]['params'][$paramscounter]['value'] = $this->view->translate('Forever');
                 }
                else{
                     if($packages->duration > 1){
                         $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->duration . ' ' . ucfirst($packages->duration_type).'s';
                     }
                     else{
                         $result['packages'][$counter]['params'][$paramscounter]['value'] =   $packages->duration . ' ' . ucfirst($packages->duration_type);
                      }
                }

                $paramscounter ++;
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'recurrence_type';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Billing Cycle');
                if($packages->recurrence_type == 'day')
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $this->view->translate('Daily');
                elseif($packages->price && $packages->recurrence_type != 'forever')
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $this->view->translate(ucfirst($packages->recurrence_type).'ly');
                elseif($packages->recurrence_type == 'forever')
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($packages->price,'','',true));
                else
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'group_featured';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['group_sponsored'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('sponsored',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'group_verified';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['group_verified'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'group_hot';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['group_hot'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'group_bgphoto';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['group_bgphoto'];
                $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'upload_mainphoto';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Main Photo');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['upload_mainphoto'];
                $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'upload_cover';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Cover Photo');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['upload_cover'];
                $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'group_choose_style';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['group_choose_style'];
                $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'package_description';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->description;
                    $paramscounter ++;
                }
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'group_count';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Pages Count');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->item_count;
                $paramscounter ++;
                $result['packages'][$counter]['price_type'] = Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($packages->price,'','',true);
                $counter++;
            }
        }
        return $result;
        //$this->_helper->content->setEnabled();
    }
    public function packageAction(){
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'sesgrouppackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppackage.package.info', array_keys($information));
        $currentCurrency =  Engine_Api::_()->sesgrouppackage()->getCurrentCurrency();
        $result = array();
        $counterleft =0;
        if(count($existingleftpackages)){
            foreach($existingleftpackages as $packageleft)	{
                $package = Engine_Api::_()->getItem('sesgrouppackage_package',$packageleft->package_id);
                $enableModules = json_decode($package->params,true);
                $result['existingleftpackages'][$counterleft] = $package->toArray();
                // $result['existingleftpackages'][$counterleft]['params'] = $enableModules;
                $result['existingleftpackages'][$counterleft]['params'] = array();
                $paramscounter = 0;
                $result['existingleftpackages'][$counterleft]['existing_package_id'] = $packageleft->getIdentity();
                if(!$package->isFree()){
                    if($package->recurrence_type == 'day')
                        $result['existingleftpackages'][$counterleft]['payment_type'] = $this->view->translate('Daily');
                    elseif($package->price && $package->recurrence_type != 'forever')
                        $result['existingleftpackages'][$counterleft]['payment_type']  = $this->view->translate(ucfirst($package->recurrence_type).'ly');
                    elseif($package->recurrence_type == 'forever')
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($package->price,'','',true));
                    else
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  $this->view->translate('Free');
                }else{
                    $result['existingleftpackages'][$counterleft]['payment_type'] = $this->view->translate("FREE");
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'billing_duration';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Billing Duration');
                if($package->duration_type == 'forever'){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Forever');
                }
                else{
                    if($package->duration > 1){
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->duration . ' ' . ucfirst($package->duration_type).'s';
                    }
                    else{
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] =   $package->duration . ' ' . ucfirst($package->duration_type);
                    }
                }
                $paramscounter++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'recurrence_type';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Billing Cycle');
                if($package->recurrence_type == 'day')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Daily');
                elseif($package->price && $package->recurrence_type != 'forever')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate(ucfirst($package->recurrence_type).'ly');
                elseif($package->recurrence_type == 'forever')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_featured';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_sponsored'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                if(in_array('sponsored',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_verified';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_verified'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_hot';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_hot'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_bgphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_bgphoto'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'upload_mainphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Main Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['upload_mainphoto'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'upload_cover';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Cover Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['upload_cover'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_choose_style';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['group_choose_style'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'group_count';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Pages Count');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = !$package->item_count? $this->view->translate("Unlimited") : $package->item_count.' ( '.$packageleft->item_count.' Left )' ;
                $paramscounter ++;
                $paramscountersuscribe = 0 ;
                if(!$package->isOneTime()){
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['name']= 'creation_date';
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['label']= $this->view->translate("Subscribed on: ");
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['value']=  date('d F Y', strtotime($packageleft->creation_date));
                    $paramscountersuscribe++;
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['name']= 'expiration_date';
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['label']= $this->view->translate("Next Payment Date: ");
                    $result['existingleftpackages'][$counterleft]['subscribe_detail'][$paramscountersuscribe]['value']=  date('d F Y', strtotime($packageleft->expiration_date));
                    $paramscountersuscribe++;
                }
                $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->sesgrouppackage()->getCurrencyPrice($package->price,'','',true);
                $counterleft++;
            }
        }else{
            $result['message'] =  $this->view->translate('You have not subscribed to any package yet!');
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' =>$result));

    }
    public function transactionsAction() {
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $tableTransaction = Engine_Api::_()->getItemTable('sesgrouppackage_transaction');
        $tableTransactionName = $tableTransaction->info('name');
        $groupTable = Engine_Api::_()->getDbTable('groups', 'sesgroup');
        $groupTableName = $groupTable->info('name');
        $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
        $select = $tableTransaction->select()
            ->setIntegrityCheck(false)
            ->from($tableTransactionName)
            ->joinLeft($tableUserName, "$tableTransactionName.owner_id = $tableUserName.user_id", 'username')
            ->where($tableUserName . '.user_id IS NOT NULL')
            ->joinLeft($groupTableName, "$tableTransactionName.transaction_id = $groupTableName.transaction_id", 'group_id')
            ->where($groupTableName . '.group_id IS NOT NULL')
            ->where($tableTransactionName . '.owner_id =?', $viewer->getIdentity());
        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($this->_getParam('limit',10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result = $this->getTransactions($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getTransactions($paginator){
        $result = array();
        $result['title'] = $this->view->translate('View Transactions of Page Packages');
        $counter = 0;
        foreach($paginator as $item){
            $user = Engine_Api::_()->getItem('user',$item->owner_id);
            $group = Engine_Api::_()->getItem('sesgroup_group',$item->group_id);
            $package = Engine_Api::_()->getItem('sesgrouppackage_package',$item->package_id);
            $data[$counter]['transaction_id'] = $item->transaction_id;
            $data[$counter]['id'] = $item->group_id;
            $data[$counter]['title'] = $this->view->translate(Engine_Api::_()->sesbasic()->textTruncation($group->getTitle(),25));
            $data[$counter]['package'] = $this->view->translate(Engine_Api::_()->sesbasic()->textTruncation($package->title,25));
            $data[$counter]['gateway'] = $item->gateway_type;;
            $data[$counter]['status'] = $this->view->translate(ucfirst($item->state));
            $data[$counter]['amount'] = $package->getPackageDescription();
            $data[$counter]['date'] = $this->view->locale()->toDateTime($item->creation_date);
            $counter++;
        }
        $result['transactions'] = $data;
        return $result;
    }
    public function cancelAction() {
        $packageId = $this->_getParam('package_id', 0);
        $form = new Sesgrouppackage_Form_Cancel();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        if (!$form->isValid($this->getRequest()->getPost()))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));

        Engine_Api::_()->getDbTable('packages','sesgrouppackage')->cancelSubscription(array('package_id' => $packageId));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => array('message'=>$this->view->translate('Your Package Subscription has been Deleted Successfully.'))));
    }
    public function editAction(){
        $group_id = $this->_getParam('group_id', null);
        if (!$group_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject()) {
            $group = Engine_Api::_()->getItem('sesgroup_group', $group_id);
        } else {
            $group = Engine_Api::_()->core()->getSubject();
        }
        $previousTitle = $group->getTitle();
        $defaultProfileId = 1;
        if (isset($group->category_id) && $group->category_id != 0)
            $category_id = $group->category_id;
        else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
            $category_id = $_POST['category_id'];
        else
            $category_id = 0;
        if (isset($group->subsubcat_id) && $group->subsubcat_id != 0)
            $subsubcat_id = $group->subsubcat_id;
        else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
            $subsubcat_id = $_POST['subsubcat_id'];
        else
            $subsubcat_id = 0;
        if (isset($group->subcat_id) && $group->subcat_id != 0)
            $subcat_id = $group->subcat_id;
        else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
            $subcat_id = $_POST['subcat_id'];
        else
            $subcat_id = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        if (!Engine_Api::_()->sesgroup()->groupRolePermission($group, Zend_Controller_Front::getInstance()->getRequest()->getActionName())) {
            if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $group->isOwner($viewer)))
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
        }
        $form = new Sesgroup_Form_Edit(array('defaultProfileId' => $defaultProfileId));
    	$form->removeElement('group_location');
        if($form->getElement('enable_lock')){
            $form->removeElement('enable_lock');
            $form->removeElement('group_password');
        }
		if($form->getElement('can_join'))
			$form->removeElement('can_join');
		//if($form->getElement('approval'))
			//$form->removeElement('approval');
		//if($form->getElement('join_question'))
    	//$form->removeElement('join_question');
        if($_GET['sesapi_platform'] == 1){
          if( $form->getElement('member_title_singular')){
            $form->getElement('member_title_singular')->setDescription("");  
            $form->getElement('member_title_plural')->setDescription("");  
          }
         }
        $tagStr = '';
        foreach ($group->tags()->getTagMaps() as $tagMap) {
            $tag = $tagMap->getTag();
            if (!isset($tag->text))
                continue;
            if ('' !== $tagStr)
                $tagStr .= ', ';
            $tagStr .= $tag->text;
        }
        $values = $group->toArray();
        $values['tags'] = $tagStr;
        $values['networks'] = ltrim($group['networks']);
		 //if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seslocation')) {
			//$form->getElement('group_location')->setLabel('Location');
		 //}
        $form->populate($values);
        //if (!$this->getRequest()->isPost()) {
          
            // Populate auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $subgroupRoles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network');
            foreach ($roles as $role) {
                if (isset($form->auth_view->options[$role]) && $auth->isAllowed($group, $role, 'view'))
                    $form->auth_view->setValue($role);
                if (isset($form->auth_comment->options[$role]) && $auth->isAllowed($group, $role, 'comment'))
                    $form->auth_comment->setValue($role);
                if (isset($form->auth_album->options[$role]) && $auth->isAllowed($group, $role, 'album'))
                    $form->auth_album->setValue($role);

                if (isset($form->auth_video->options[$role]) && $auth->isAllowed($group, $role, 'video'))
                    $form->auth_video->setValue($role);
            }
            foreach ($subgroupRoles as $role) {
                if (isset($form->create_sub_group->options[$role]) && $auth->isAllowed($group, $role, 'create_sub_group'))
                    $form->create_sub_group->setValue($role);
            }
			if($group->custom_url && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgroup.edit.url', 0) )
            $form->custom_url_group->setValue($group->custom_url);
            if ($form->draft->getValue() == 1)
                $form->removeElement('draft');
        //}
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesgroup_group'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        if (isset($_POST['custom_url_group']) && !empty($_POST['custom_url_group'])) {
            $custom_url = Engine_Api::_()->getDbTable('groups', 'sesgroup')->checkCustomUrl($_POST['custom_url_group'], $group->group_id);
            if ($custom_url) {
                $form->addError($this->view->translate("Custom Url not available.Please select other."));
            }
        }
        $values = $form->getValues();
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
        if (!isset($values['can_invite'])) {
            $values['can_invite'] = 1;
        }
        $settings = Engine_Api::_()->getApi('settings', 'core');
        if (!isset($values['can_join']))
            $values['approval'] = $settings->getSetting('sesgroup.default.joinoption', 1) ? 0 : 1;
        elseif (!isset($values['approval']))
            $values['approval'] = $settings->getSetting('sesgroup.default.approvaloption', 1) ? 0 : 1;
        // Process
        $db = Engine_Api::_()->getItemTable('sesgroup_group')->getAdapter();
        $db->beginTransaction();
        try {
            if (!($values['draft']))
                unset($values['draft']);
            $group->setFromArray($values);
            $group->save();
            $tags = preg_split('/[,]+/', $values['tags']);
            $group->tags()->setTagMaps($viewer, $tags);
            if (!$values['vote_type'])
                $values['resulttime'] = '';
            if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
                $group->custom_url = $_POST['custom_url'];
            else if(!empty($_POST['custom_url_group']))
              $group->custom_url = $_POST['custom_url_group'];
            $group->save();
            $newgroupTitle = $group->getTitle();
            // Add photo
            if (!empty($values['photo'])) {
                $group->setPhoto($form->photo);
            }
            // Add cover photo
            if (!empty($values['cover'])) {
                $group->setCover($form->cover);
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            if (empty($values['auth_view']))
                $values['auth_view'] = 'everyone';
            if (empty($values['auth_comment']))
                $values['auth_comment'] = 'everyone';
            $createSubgroupMax = array_search($values['create_sub_group'], $subgroupRoles);
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            $albumMax = array_search(@$values['auth_album'], $roles);
            $videoMax = array_search(@$values['auth_video'], $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($group, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($group, $role, 'comment', ($i <= $commentMax));

                $auth->setAllowed($group, $role, 'album', ($i <= $albumMax));
                $auth->setAllowed($group, $role, 'video', ($i <= $videoMax));
            }
            foreach ($subgroupRoles as $i => $role) {
                $auth->setAllowed($group, $role, 'create_sub_group', ($i <= $createSubgroupMax));
            }
            $group->save();
            $db->commit();
            //Start Activity Feed Work
            if (isset($values['draft']) && $group->draft == 1 && $group->is_approved == 1) {
                $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
            }
            if ($previousTitle != $newgroupTitle) {
                //Send to all joined members
                $joinedMembers = Engine_Api::_()->sesgroup()->getallJoinedMembers($group);
                foreach ($joinedMembers as $joinedMember) {
                    if ($joinedMember->user_id == $group->owner_id)
                        continue;
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $group, 'sesgroup_group_groupsijoinedgroupnamechanged', array('old_group_title' => $previousTitle, 'new_group_link' => $newgroupTitle));
                }
                //Send to all followed members
                $followerMembers = Engine_Api::_()->getDbTable('followers', 'sesgroup')->getFollowers($group->getIdentity());
                foreach ($followerMembers as $followerMember) {
                    if ($followerMember->owner_id == $group->owner_id)
                        continue;
                    $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $group, 'sesgroup_group_groupsifollowedgroupnamechanged', array('old_group_title' => $previousTitle, 'new_group_link' => $newgroupTitle));

                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($followerMember, 'notify_sesgroup_group_groupnamechanged', array('group_title' => $group->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $group->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                //Send to all favourites members
                $followerMembers = Engine_Api::_()->getDbTable('favourites', 'sesgroup')->getAllFavMembers($group->getIdentity());
                foreach ($followerMembers as $followerMember) {
                    if ($followerMember->owner_id == $group->owner_id)
                        continue;
                    $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $group, 'sesgroup_group_groupsifavousitegroupnamechanged', array('old_group_title' => $previousTitle, 'new_group_link' => $newgroupTitle));
                }
            }
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The image you selected was too large.'), 'result' => array()));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $db->beginTransaction();
        try {
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('group_id' => $group->getIdentity(), 'success_message' => $this->view->translate('Group edited successfully.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }

}


