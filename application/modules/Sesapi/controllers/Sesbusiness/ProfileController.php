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

class Sesbusiness_ProfileController extends Sesapi_Controller_Action_Standard
{
    public function init(){
      if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    public function createAction(){
      if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $category_id = $this->_getParam('category_id', 0);
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
       // if (!$this->_helper->requireUser->isValid())
         //   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        $categoryShowAble = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.category.selection', 0);
        $totalbusiness = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')->countbusinesses($viewer->getIdentity());
        $allowbusinessCount = Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_count');
        $errorcode['error_code'] = 'PME' . $totalbusiness;
        if ($allowbusinessCount != 0 && $totalbusiness >= $allowbusinessCount) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have reached the limit of business creation. Please contact to the site administrator.'), 'result' => $errorcode));
        }
        //Start Package Work
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesbusinesspackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspackage.enable.package', 0) && (_SESAPI_VERSION_ANDROID >= 2.5 || _SESAPI_VERSION_IOS >= 1.7)) {
            $package_id = $this->_getParam('package_id', 0);
            $existingpackage_id = $this->_getParam('existing_package_id', 0);
            $package = Engine_Api::_()->getItem('sesbusinesspackage_package', $package_id);
            $existingpackage = Engine_Api::_()->getItem('sesbusinesspackage_orderspackage', $existingpackage_id);
            if ($existingpackage) {
                $package = Engine_Api::_()->getItem('sesbusinesspackage_package', $existingpackage->package_id);
            }
            if (!$package && !$existingpackage) {
                // check package exists for this member level
                $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'sesbusinesspackage')->getPackage(array('member_level' => $viewer->level_id));
                if (count($packageMemberLevel)) {
                    // redirect to package business
                    $packageResult = $this->businessPackage();
                    if($packageResult){
                        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $packageResult));
                    }else{
                        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data Not Found.'), 'result' => array()));
                    }
                }
            }

            if ($existingpackage) {
                $canCreate = Engine_Api::_()->getDbTable('orderspackages', 'sesbusinesspackage')->checkUserPackage($this->_getParam('existing_package_id', 0), $viewer->getIdentity());
                if (!$canCreate){
                    //$packageResult = $this->businessPackage();
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have not permission to access this resource'), 'result' =>array()));
                }
                //return $this->_helper->redirector->gotoRoute(array('action' => 'business'), 'sesbusinesspackage_general', true);
            }
        }
        //End Package Work
        $categories = Engine_Api::_()->getDbtable('categories', 'sesbusiness')->getCategory();
		$checkCategoriesForMemberLevel = Engine_Api::_()->getDbTable('categories', 'sesbusiness')->getCategoriesAssoc(array('member_levels' => 1));
        if ($categoryShowAble && !$category_id && count($categories) && $checkCategoriesForMemberLevel) {
            $categoryShowAbletypeimage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_category_icon', 0);
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
                $result_category['category'][$category_counter]['total_business_categories'] = $category->total_business_categories;
                $category_counter++;
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result_category));
        }
        $parentId = $this->_getParam('parent_id', 0);
        $subbusinessCreatePemission = false;
        if ($parentId) {
          $subject = Engine_Api::_()->getItem('businesses', $parentId);
          if ($subject) {
            if ((!Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'auth_subbusiness') || $subject->parent_id)) {
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            $subbusinessCreatePemission = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness')->toCheckUserbusinessRole($viewer->getIdentity(), $subject->getIdentity(), 'post_behalf_business');
            if (!$subbusinessCreatePemission)
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            }
          }
        }

        $quckCreate = 0;

        $form = new Sesbusiness_Form_Create();
        $form->removeElement('removeimage');
		if($form->getElement('enable_lock')){
			$form->removeElement('enable_lock');
			$form->removeElement('business_password');
		}
        $form->removeElement('removeimage2');
        $form->removeElement('business_main_photo_preview');
        $form->removeElement('photo-uploader');
        if ($form->getElement('category_id'))
            $form->getElement('category_id')->setValue($this->_getParam('category_id'));
        if ($form->getElement('business_location'))
            $form->getElement('business_location')->setLabel('Location');
       
       if($_GET['sesapi_platform'] == 1){
         if($form->getElement('member_title_singular')){
          $form->getElement('member_title_singular')->setDescription("");  
          $form->getElement('member_title_plural')->setDescription("");  
         }
       }
       
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
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
        if (!$quckCreate && isset($_POST['custom_url_business']) && !empty($_POST['custom_url_business'])) {
            $custom_url = Engine_Api::_()->getDbtable('businesses', 'sesbusiness')->checkCustomUrl($_POST['custom_url_business']);
            if ($custom_url) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("custom_url_taken"), 'result' => array()));
            }
        }
        $values = array();
        if (!$quckCreate) {
            $values = $form->getValues();
            $values['location'] = isset($_POST['business_location']) ? $_POST['business_location'] : '';
        }
        $values['owner_id'] = $viewer->getIdentity();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        if (!$quckCreate && $settings->getSetting('sesbusiness.businessmainphoto', 1)) {
            if (empty($_FILES['photo']['size']) && empty($_FILES['image']['size'])) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Main Photo is a required field.'), 'result' => array()));
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
            $values['approval'] = $settings->getSetting('sesbusiness.default.joinoption', 1) ? 0 : 1;
        elseif (!isset($values['approval']))
            $values['approval'] = $settings->getSetting('sesbusiness.default.approvaloption', 1) ? 0 : 1;

        $businessTable = Engine_Api::_()->getDbtable('businesses', 'sesbusiness');
        $db = $businessTable->getAdapter();
        $db->beginTransaction();
        try {
            // Create business
            $business = $businessTable->createRow();
            if (!$quckCreate && empty($_POST['lat'])) {
                unset($values['location']);
                unset($values['lat']);
                unset($values['lng']);
                unset($values['venue_name']);
            }
            if (!empty($_POST['business_location'])) {
                $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['business_location']);
                if ($latlng) {
                    $values['lat'] = $_POST['lat'] = $latlng['lat'];
                    $values['lng'] = $_POST['lng'] = $latlng['lng'];
					$values['location'] = $_POST['business_location'];
                }
            }
            $sesbusiness_draft = $settings->getSetting('sesbusiness.draft', 1);
            if (empty($sesbusiness_draft)) {
                $values['draft'] = 1;
            }
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.global.search', 1) && !isset($values['search'])){
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
            if(!empty($_POST['custom_url_business']))
              $values['custom_url'] = $_POST['custom_url_business'];
            if (isset($package)) {
                $values['package_id'] = $package->getIdentity();
                if ($package->isFree()) {
                    if (isset($params['business_approve']) && $params['business_approve'])
                        $values['is_approved'] = 1;
                } else
                    $values['is_approved'] = 0;
                if ($existingpackage) {
                    $values['existing_package_order'] = $existingpackage->getIdentity();
                    $values['orderspackage_id'] = $existingpackage->getIdentity();
                    $existingpackage->item_count = $existingpackage->item_count - 1;
                    $existingpackage->save();
                    $params = json_decode($package->params, true);
                    if (isset($params['business_approve']) && $params['business_approve'])
                        $values['is_approved'] = 1;
                    if (isset($params['business_featured']) && $params['business_featured'])
                        $values['featured'] = 1;
                    if (isset($params['business_sponsored']) && $params['business_sponsored'])
                        $values['sponsored'] = 1;
                    if (isset($params['business_verified']) && $params['business_verified'])
                        $values['verified'] = 1;
                    if (isset($params['business_hot']) && $params['business_hot'])
                        $values['hot'] = 1;
                }
            } else {
                if (!isset($package) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesbusinesspackage')) {
                    $values['package_id'] = Engine_Api::_()->getDbTable('packages', 'sesbusinesspackage')->getDefaultPackage();
                }
            }

            $business->setFromArray($values);
            if (isset($_POST['sesbusiness_title'])) {
                $business->title = $_POST['sesbusiness_title'];
                $business->category_id = $_POST['category_id'];
                if (isset($_POST['subcat_id']))
                    $business->category_id = $_POST['category_id'];
                if (isset($_POST['subsubcat_id']))
                    $business->category_id = $_POST['category_id'];
            }
            $business->parent_id = $parentId;
            if (!isset($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            $business->view_privacy = $values['auth_view'];
            $business->businessestyle = 1;
            $business->save();

            //Start Default Package Order Work
            if (isset($package) && $package->isFree()) {
                if (!$existingpackage) {
                    $transactionsOrdersTable = Engine_Api::_()->getDbtable('orderspackages', 'sesbusinesspackage');
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
                    $business->orderspackage_id = $transactionsOrdersTable->getAdapter()->lastInsertId();
                    $business->existing_package_order = 0;
                } else {
                    $existingpackage->item_count = $existingpackage->item_count--;
                    $existingpackage->save();
                }
            }
            //End Default package Order Work

            if (isset($values['tags']) && count($values['tags'])>0) {
                $tags = preg_split('/[,]+/', $values['tags']);
                $business->tags()->addTagMaps($viewer, $tags);
                $business->seo_keywords = implode(',', $tags);
                $business->save();
            }
            if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && !empty($_POST['business_location'])) {
                $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                $dbGetInsert->query('INSERT INTO engine4_sesbusiness_locations (business_id,location,venue, lat, lng ,city,state,zip,country,address,address2, is_default) VALUES ("' . $business->business_id . '","' . $_POST['business_location'] . '", "' . $_POST['venue_name'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "1") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue_name'] . '"');
                $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $business->business_id . '","' . $_POST['business_location'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "businesses")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
            }
            //Manage Apps
            Engine_Db_Table::getDefaultAdapter()->query('INSERT IGNORE INTO `engine4_sesbusiness_managebusinessapps` (`business_id`) VALUES ("' . $business->business_id . '");');

            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.auto.join', 1)) {
                $business->membership()->addMember($viewer)->setUserApproved($viewer)->setResourceApproved($viewer);
            }
            if (!isset($package)) {
                if (!Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_approve'))
                    $business->is_approved = 0;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_featured'))
                    $business->featured = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_sponsored'))
                    $business->sponsored = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_verified'))
                    $business->verified = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_hot'))
                    $business->hot = 1;
            }
            // Add photo

            if (!empty($_FILES['photo']['size'])) {
                $business->setPhoto($form->photo, '', 'profile');
            }
            if (!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) {
                $business->setPhoto($_FILES['image'], '', 'profile');
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $subbusinessRoles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network');
            if (!isset($values['auth_view']) || empty($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            if (!isset($values['auth_comment']) || empty($values['auth_comment'])) {
                $values['auth_comment'] = 'everyone';
            }

            $createSubbusinessMax = array_search($values['create_sub_business'], $subbusinessRoles);
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $albumMax = array_search($values['auth_album'], $roles);
            $videoMax = array_search($values['auth_video'], $roles);
            foreach ($roles as $i => $role) {
                $auth->setAllowed($business, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($business, $role, 'comment', ($i <= $commentMax));

                $auth->setAllowed($business, $role, 'album', ($i <= $albumMax));
                $auth->setAllowed($business, $role, 'video', ($i <= $videoMax));
            }
            foreach ($subbusinessRoles as $i => $role) {
                $auth->setAllowed($business, $role, 'create_sub_business', ($i <= $createSubbusinessMax));
            }
            if (!$quckCreate) {
                //Add fields
                $customfieldform = $form->getSubForm('fields');
                if ($customfieldform) {
                    $customfieldform->setItem($business);
                    $customfieldform->saveValues();
                }
            }
            $business->save();
            if (!$business->custom_url)
                $business->custom_url = $business->getIdentity();
           
            $business->save();
            $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.autoopenpopup', 1);
            if ($autoOpenSharePopup && $business->draft && $business->is_approved) {
                $_SESSION['newbusiness'] = true;
            }
            //insert admin of business
            $businessRole = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness')->createRow();
            $businessRole->user_id = $this->view->viewer()->getIdentity();
            $businessRole->business_id = $business->getIdentity();
            $businessRole->memberrole_id = 1;
            $businessRole->save();
            // Commit
            $db->commit();
            //Start Activity Feed Work
            if ($business->draft == 1 && $business->is_approved == 1) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $business, 'sesbusiness_business_create');
                if ($action) {
                    $activityApi->attachActivity($action, $business);
                }
            }

            //End Activity Feed Work
            //Start Send Approval Request to Admin
            if (!$business->is_approved) {
                if (isset($package) && $package->price > 0) {
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($business->getOwner(), $viewer, $business, 'sesbusiness_payment_notify_business');
                } else {
                    $getAdminnSuperAdmins = Engine_Api::_()->sesbusiness()->getAdminnSuperAdmins();
                    foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                        $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $business, 'sesbusiness_waitingadminapproval');
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_sesbusiness_business_adminapproval', array('sender_title' => $business->getOwner()->getTitle(), 'adminmanage_link' => 'admin/sesbusiness/manage', 'object_link' => $business->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($business->getOwner(), 'notify_sesbusiness_business_businesssentforapproval', array('business_title' => $business->getTitle(), 'object_link' => $business->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($viewer, $viewer, $business, 'sesbusiness_business_waitingapproval');
                //Engine_Api::_()->sesbusiness()->sendMailNotification(array('business' => $business));
            }

            //Send mail to all super admin and admins
            if ($business->is_approved) {
                $getAdminnSuperAdmins = Engine_Api::_()->sesbusiness()->getAdminnSuperAdmins();
                foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                    $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_sesbusiness_business_superadmin', array('sender_title' => $business->getOwner()->getTitle(), 'object_link' => $business->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                $receiverEmails = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.receivenewalertemails');
                if (!empty($receiverEmails)) {
                    $receiverEmails = explode(',', $receiverEmails);
                    foreach ($receiverEmails as $receiverEmail) {
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiverEmail, 'notify_sesbusiness_business_superadmin', array('sender_title' => $business->getOwner()->getTitle(), 'object_link' => $business->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                }
            }

			$redirection = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.redirect', 1);
			if(!$business->is_approved)
				$redirect = 'manage';
			else if($business->is_approved && $redirection == 1)
				$redirect = 'dashboard';
			else 
				$redirect = 'view';
            //End Work Here.
            if (!$business->is_approved) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('business created successfully and send to admin approval.'), 'business_id' => 0,'redirect'=>$redirect)));
            } else
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('business_id' => $business->getIdentity(), 'success_message' => $this->view->translate('business created successfully.'),'redirect'=>$redirect)));
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The image you selected was too large.'), 'result' => array()));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function businessPackage() {
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $result = array();
        $packages = $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'sesbusinesspackage')->getPackage(array('member_level' => $viewer->level_id, 'enabled' => 0));
        if (!count($packageMemberLevel) || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspackage.enable.package', 0))
            return true;
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'sesbusinesspackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspackage.package.info', array_keys($information));
        if(count($existingleftpackages)){
            $counterleft = 0;
            foreach ($existingleftpackages as $leftpackages) {
                $package = Engine_Api::_()->getItem('sesbusinesspackage_package', $leftpackages->package_id);
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
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($package->price,'','',true));
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
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_featured';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_sponsored'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                if(in_array('sponsored',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_verified';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_verified'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_hot';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_hot'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_bgphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_bgphoto'];
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
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_choose_style';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_choose_style'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_count';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Businesses Count');
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
                $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($package->price,'','',true);
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
                        $result['packages'][$counter]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($packages->price,'','',true));
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
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($packages->price,'','',true));
                else
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'business_featured';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['business_sponsored'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('sponsored',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'business_verified';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['business_verified'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'business_hot';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['business_hot'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'business_bgphoto';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['business_bgphoto'];
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
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'business_choose_style';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['business_choose_style'];
                $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'package_description';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->description;
                    $paramscounter ++;
                }
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'business_count';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Businesses Count');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->item_count;
                $paramscounter ++;
                $result['packages'][$counter]['price_type'] = Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($packages->price,'','',true);
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
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'sesbusinesspackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspackage.package.info', array_keys($information));
        $currentCurrency =  Engine_Api::_()->sesbusinesspackage()->getCurrentCurrency();
        $result = array();
        $counterleft =0;
        if(count($existingleftpackages)){
            foreach($existingleftpackages as $packageleft)	{
                $package = Engine_Api::_()->getItem('sesbusinesspackage_package',$packageleft->package_id);
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
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($package->price,'','',true));
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
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_featured';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_sponsored'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                if(in_array('sponsored',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_verified';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_verified'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_hot';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_hot'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_bgphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_bgphoto'];
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
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_choose_style';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['business_choose_style'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'business_count';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Businesses Count');
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
                $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->sesbusinesspackage()->getCurrencyPrice($package->price,'','',true);
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
        $tableTransaction = Engine_Api::_()->getItemTable('sesbusinesspackage_transaction');
        $tableTransactionName = $tableTransaction->info('name');
        $businessTable = Engine_Api::_()->getDbTable('businesses', 'sesbusiness');
        $businessTableName = $businessTable->info('name');
        $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
        $select = $tableTransaction->select()
            ->setIntegrityCheck(false)
            ->from($tableTransactionName)
            ->joinLeft($tableUserName, "$tableTransactionName.owner_id = $tableUserName.user_id", 'username')
            ->where($tableUserName . '.user_id IS NOT NULL')
            ->joinLeft($businessTableName, "$tableTransactionName.transaction_id = $businessTableName.transaction_id", 'business_id')
            ->where($businessTableName . '.business_id IS NOT NULL')
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
        $result['title'] = $this->view->translate('View Transactions of Business Packages');
        $counter = 0;
        foreach($paginator as $item){
            $user = Engine_Api::_()->getItem('user',$item->owner_id);
            $business = Engine_Api::_()->getItem('businesses',$item->business_id);
            $package = Engine_Api::_()->getItem('sesbusinesspackage_package',$item->package_id);
            $data[$counter]['transaction_id'] = $item->transaction_id;
            $data[$counter]['id'] = $item->business_id;
            $data[$counter]['title'] = $this->view->translate(Engine_Api::_()->sesbasic()->textTruncation($business->getTitle(),25));
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
        $form = new Sesbusinesspackage_Form_Cancel();
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

        Engine_Api::_()->getDbTable('packages','sesbusinesspackage')->cancelSubscription(array('package_id' => $packageId));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => array('message'=>$this->view->translate('Your Package Subscription has been Deleted Successfully.'))));
    }
    public function editAction(){
        $business_id = $this->_getParam('business_id', null);
        if (!$business_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject()) {
            $business = Engine_Api::_()->getItem('businesses', $business_id);
        } else {
            $business = Engine_Api::_()->core()->getSubject();
        }
        $previousTitle = $business->getTitle();
        $defaultProfileId = 1;
        if (isset($business->category_id) && $business->category_id != 0)
            $category_id = $business->category_id;
        else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
            $category_id = $_POST['category_id'];
        else
            $category_id = 0;
        if (isset($business->subsubcat_id) && $business->subsubcat_id != 0)
            $subsubcat_id = $business->subsubcat_id;
        else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
            $subsubcat_id = $_POST['subsubcat_id'];
        else
            $subsubcat_id = 0;
        if (isset($business->subcat_id) && $business->subcat_id != 0)
            $subcat_id = $business->subcat_id;
        else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
            $subcat_id = $_POST['subcat_id'];
        else
            $subcat_id = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        if (!Engine_Api::_()->sesbusiness()->businessRolePermission($business, Zend_Controller_Front::getInstance()->getRequest()->getActionName())) {
            if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $business->isOwner($viewer)))
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
        }

        $form = new Sesbusiness_Form_Edit(array('defaultProfileId' => $defaultProfileId));
    		$form->removeElement('business_location');
        if($form->getElement('enable_lock')){
            $form->removeElement('enable_lock');
            $form->removeElement('business_password');
        }
        if($_GET['sesapi_platform'] == 1){
          if( $form->getElement('member_title_singular')){
            $form->getElement('member_title_singular')->setDescription("");  
            $form->getElement('member_title_plural')->setDescription("");  
          }
         }

        $tagStr = '';
        foreach ($business->tags()->getTagMaps() as $tagMap) {
            $tag = $tagMap->getTag();
            if (!isset($tag->text))
                continue;
            if ('' !== $tagStr)
                $tagStr .= ', ';
            $tagStr .= $tag->text;
        }
        $values = $business->toArray();
        $values['tags'] = $tagStr;
        $values['networks'] = ltrim($business['networks']);
		 //if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seslocation')) {
			//$form->getElement('business_location')->setLabel('Location');
		 //}
        $form->populate($values);
        //if (!$this->getRequest()->isPost()) {

            // Populate auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $subbusinessRoles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network');
            foreach ($roles as $role) {
                if (isset($form->auth_view->options[$role]) && $auth->isAllowed($business, $role, 'view'))
                    $form->auth_view->setValue($role);
                if (isset($form->auth_comment->options[$role]) && $auth->isAllowed($business, $role, 'comment'))
                    $form->auth_comment->setValue($role);
                if (isset($form->auth_album->options[$role]) && $auth->isAllowed($business, $role, 'album'))
                    $form->auth_album->setValue($role);

                if (isset($form->auth_video->options[$role]) && $auth->isAllowed($business, $role, 'video'))
                    $form->auth_video->setValue($role);
            }
            foreach ($subbusinessRoles as $role) {
                if (isset($form->create_sub_business->options[$role]) && $auth->isAllowed($business, $role, 'create_sub_business'))
                    $form->create_sub_business->setValue($role);
            }
			if($business->custom_url && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.edit.url', 0) )
            $form->custom_url_business->setValue($business->custom_url);
            if ($form->draft->getValue() == 1)
                $form->removeElement('draft');
        //}

        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
        }

        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        if (isset($_POST['custom_url_business']) && !empty($_POST['custom_url_business'])) {
            $custom_url = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')->checkCustomUrl($_POST['custom_url_business'], $business->business_id);
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
            $values['approval'] = $settings->getSetting('sesbusiness.default.joinoption', 1) ? 0 : 1;
        elseif (!isset($values['approval']))
            $values['approval'] = $settings->getSetting('sesbusiness.default.approvaloption', 1) ? 0 : 1;
        // Process
        $db = Engine_Api::_()->getItemTable('businesses')->getAdapter();
        $db->beginTransaction();
        try {
            if (!($values['draft']))
                unset($values['draft']);
            $business->setFromArray($values);
            $business->save();
            $tags = preg_split('/[,]+/', $values['tags']);
            $business->tags()->setTagMaps($viewer, $tags);
            if (!$values['vote_type'])
                $values['resulttime'] = '';
            if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
                $business->custom_url = $_POST['custom_url'];
            else if(!empty($_POST['custom_url_business']))
              $business->custom_url = $_POST['custom_url_business'];
            $business->save();
            $newbusinessTitle = $business->getTitle();
            // Add photo
            if (!empty($values['photo'])) {
                $business->setPhoto($form->photo);
            }
            // Add cover photo
            if (!empty($values['cover'])) {
                $business->setCover($form->cover);
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            if (empty($values['auth_view']))
                $values['auth_view'] = 'everyone';
            if (empty($values['auth_comment']))
                $values['auth_comment'] = 'everyone';
            $createSubbusinessMax = array_search($values['create_sub_business'], $subbusinessRoles);
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            $albumMax = array_search(@$values['auth_album'], $roles);
            $videoMax = array_search(@$values['auth_video'], $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($business, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($business, $role, 'comment', ($i <= $commentMax));

                $auth->setAllowed($business, $role, 'album', ($i <= $albumMax));
                $auth->setAllowed($business, $role, 'video', ($i <= $videoMax));
            }
            foreach ($subbusinessRoles as $i => $role) {
                $auth->setAllowed($business, $role, 'create_sub_business', ($i <= $createSubbusinessMax));
            }
            $business->save();
            $db->commit();
            //Start Activity Feed Work
            if (isset($values['draft']) && $business->draft == 1 && $business->is_approved == 1) {
                $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
            }
            if ($previousTitle != $newbusinessTitle) {
                //Send to all joined members
                $joinedMembers = Engine_Api::_()->sesbusiness()->getallJoinedMembers($business);
                foreach ($joinedMembers as $joinedMember) {
                    if ($joinedMember->user_id == $business->owner_id)
                        continue;
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $business, 'sesbusiness_business_businessesijoinedbusinessnamechanged', array('old_business_title' => $previousTitle, 'new_business_link' => $newbusinessTitle));
                }
                //Send to all followed members
                $followerMembers = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->getFollowers($business->getIdentity());
                foreach ($followerMembers as $followerMember) {
                    if ($followerMember->owner_id == $business->owner_id)
                        continue;
                    $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);

                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $business, 'sesbusiness_businessfollowednamechange', array('old_business_title' => $previousTitle, 'new_business_link' => $newbusinessTitle));

                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($followerMember, 'notify_sesbusiness_business_businessnamechanged', array('business_title' => $business->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $business->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                //Send to all favourites members
                $followerMembers = Engine_Api::_()->getDbTable('favourites', 'sesbusiness')->getAllFavMembers($business->getIdentity());
                foreach ($followerMembers as $followerMember) {
                    if ($followerMember->owner_id == $business->owner_id)
                        continue;
                    $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $business, 'sesbusiness_business_businessesifavousitebusinessnamechanged', array('old_business_title' => $previousTitle, 'new_business_link' => $newbusinessTitle));
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
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('business_id' => $business->getIdentity(), 'success_message' => $this->view->translate('business edited successfully.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }

}


