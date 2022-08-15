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

class Estore_ProfileController extends Sesapi_Controller_Action_Standard
{
    public function init(){
        if (!$this->_helper->requireAuth()->setAuthParams('stores', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }
    
    public function createAction(){
    
      if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

      $viewer = Engine_Api::_()->user()->getViewer();
      $viewerId = $viewer->getIdentity();
      $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      
      if (!$this->_helper->requireUser->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      
      $totalStore = Engine_Api::_()->getDbTable('stores', 'estore')->countStores($viewer->getIdentity());
      $allowStoreCount = Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_count');
      $errorcode['error_code'] = 'PME' . $totalStore;
      if ($allowStoreCount != 0 && $totalStore >= $allowStoreCount) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have reached the limit of store creation. Please contact to the site administrator.'), 'result' => $errorcode));
      }
        
        //Start Package Work
//         if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('estorepackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('estorepackage.enable.package', 0) && (_SESAPI_VERSION_ANDROID >= 2.5 || _SESAPI_VERSION_IOS >= 1.7)) {
//             $package_id = $this->_getParam('package_id', 0);
//             $existingpackage_id = $this->_getParam('existing_package_id', 0);
//             $package = Engine_Api::_()->getItem('estorepackage_package', $package_id);
//             $existingpackage = Engine_Api::_()->getItem('estorepackage_orderspackage', $existingpackage_id);
//             if ($existingpackage) {
//                 $package = Engine_Api::_()->getItem('estorepackage_package', $existingpackage->package_id);
//             }
//             if (!$package && !$existingpackage) {
//                 // check package exists for this member level
//                 $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'estorepackage')->getPackage(array('member_level' => $viewer->level_id));
//                 if (count($packageMemberLevel)) {
//                     // redirect to package store
//                     $packageResult = $this->storePackage();
//                     if($packageResult){
//                         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $packageResult));
//                     }else{
//                         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data Not Found.'), 'result' => array()));
//                     }
//                 }
//             }
// 
//             if ($existingpackage) {
//                 $canCreate = Engine_Api::_()->getDbTable('orderspackages', 'estorepackage')->checkUserPackage($this->_getParam('existing_package_id', 0), $viewer->getIdentity());
//                 if (!$canCreate){
//                     //$packageResult = $this->storePackage();
//                     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have not permission to access this resource'), 'result' =>array()));
//                 }
//                 //return $this->_helper->redirector->gotoRoute(array('action' => 'store'), 'estorepackage_general', true);
//             }
//         }
        //End Package Work
        
        $parentId = $this->_getParam('parent_id', 0);
        $subStoreCreatePemission = false;
        if ($parentId) {
          $subject = Engine_Api::_()->getItem('stores', $parentId);
          if ($subject) {
            if ((!Engine_Api::_()->authorization()->isAllowed('stores', $viewer, 'auth_substore') || $subject->parent_id)) {
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            $subStoreCreatePemission = Engine_Api::_()->getDbTable('storeroles', 'estore')->toCheckUserStoreRole($viewer->getIdentity(), $subject->getIdentity(), 'post_behalf_store');
            if (!$subStoreCreatePemission)
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            }
          }
        }
        $quckCreate = 0;
        $form = new Estore_Form_Create();
        $form->removeElement('removeimage');
        if($form->getElement('enable_lock')){
          $form->removeElement('enable_lock');
          $form->removeElement('store_password');
        }
        $form->removeElement('removeimage2');
        $form->removeElement('store_main_photo_preview');
        $form->removeElement('photo-uploader');
        if ($form->getElement('category_id'))
            $form->getElement('category_id')->setValue($this->_getParam('category_id'));
        if ($form->getElement('store_location'))
            $form->getElement('store_location')->setLabel('Location');

        if($_GET['sesapi_platform'] == 1){
          if($form->getElement('member_title_singular')){
            $form->getElement('member_title_singular')->setDescription("");
            $form->getElement('member_title_plural')->setDescription("");
          }
        }

        if ($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields, array('resources_type' => 'stores'));
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
        if (!$quckCreate && isset($_POST['custom_url_store']) && !empty($_POST['custom_url_store'])) {
            $custom_url = Engine_Api::_()->getDbtable('stores', 'estore')->checkCustomUrl($_POST['custom_url_store']);
            if ($custom_url) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("custom_url_taken"), 'result' => array()));
            }
        }
        
        $values = array();
        if (!$quckCreate) {
            $values = $form->getValues();
            $values['location'] = isset($_POST['store_location']) ? $_POST['store_location'] : '';
        }
        $values['owner_id'] = $viewer->getIdentity();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        if (!$quckCreate && $settings->getSetting('estore.storemainphoto', 1)) {
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
          $values['approval'] = $settings->getSetting('estore.default.joinoption', 1) ? 0 : 1;
        elseif (!isset($values['approval']))
          $values['approval'] = $settings->getSetting('estore.default.approvaloption', 1) ? 0 : 1;

        $storeTable = Engine_Api::_()->getDbtable('stores', 'estore');
        $db = $storeTable->getAdapter();
        $db->beginTransaction();
        try {
            // Create store
            $store = $storeTable->createRow();
            if (!$quckCreate && empty($_POST['lat'])) {
                unset($values['location']);
                unset($values['lat']);
                unset($values['lng']);
                unset($values['venue_name']);
            }
            if (!empty($_POST['store_location'])) {
                $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['store_location']);
                if ($latlng) {
                    $values['lat'] = $_POST['lat'] = $latlng['lat'];
                    $values['lng'] = $_POST['lng'] = $latlng['lng'];
                    $values['location'] = $_POST['store_location'];
                }
            }
            $estore_draft = $settings->getSetting('estore.draft', 1);
            if (empty($estore_draft)) {
                $values['draft'] = 1;
            }
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.global.search', 1) && !isset($values['search'])){
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
            if(!empty($_POST['custom_url_store']))
              $values['custom_url'] = $_POST['custom_url_store'];
            if (isset($package)) {
                $values['package_id'] = $package->getIdentity();
                if ($package->isFree()) {
                    if (isset($params['store_approve']) && $params['store_approve'])
                        $values['is_approved'] = 1;
                } else
                    $values['is_approved'] = 0;
                if ($existingpackage) {
                    $values['existing_package_order'] = $existingpackage->getIdentity();
                    $values['orderspackage_id'] = $existingpackage->getIdentity();
                    $existingpackage->item_count = $existingpackage->item_count - 1;
                    $existingpackage->save();
                    $params = json_decode($package->params, true);
                    if (isset($params['store_approve']) && $params['store_approve'])
                        $values['is_approved'] = 1;
                    if (isset($params['store_featured']) && $params['store_featured'])
                        $values['featured'] = 1;
                    if (isset($params['store_sponsored']) && $params['store_sponsored'])
                        $values['sponsored'] = 1;
                    if (isset($params['store_verified']) && $params['store_verified'])
                        $values['verified'] = 1;
                    if (isset($params['store_hot']) && $params['store_hot'])
                        $values['hot'] = 1;
                }
            } else {
                if (!isset($package) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('estorepackage')) {
                    $values['package_id'] = Engine_Api::_()->getDbTable('packages', 'estorepackage')->getDefaultPackage();
                }
            }

            $store->setFromArray($values);
            if (isset($_POST['estore_title'])) {
                $store->title = $_POST['estore_title'];
                $store->category_id = $_POST['category_id'];
                if (isset($_POST['subcat_id']))
                    $store->category_id = $_POST['category_id'];
                if (isset($_POST['subsubcat_id']))
                    $store->category_id = $_POST['category_id'];
            }
            $store->parent_id = $parentId;
            if (!isset($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            $store->view_privacy = $values['auth_view'];
            $store->storestyle = 1;
            $store->save();

            //Start Default Package Order Work
            if (isset($package) && $package->isFree()) {
                if (!$existingpackage) {
                    $transactionsOrdersTable = Engine_Api::_()->getDbtable('orderspackages', 'estorepackage');
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
                    $store->orderspackage_id = $transactionsOrdersTable->getAdapter()->lastInsertId();
                    $store->existing_package_order = 0;
                } else {
                    $existingpackage->item_count = $existingpackage->item_count--;
                    $existingpackage->save();
                }
            }
            //End Default package Order Work

            if (isset($values['tags']) && count($values['tags'])>0) {
                $tags = preg_split('/[,]+/', $values['tags']);
                $store->tags()->addTagMaps($viewer, $tags);
                $store->seo_keywords = implode(',', $tags);
                $store->save();
            }
            if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && !empty($_POST['store_location'])) {
                $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                $dbGetInsert->query('INSERT INTO engine4_estore_locations (store_id,location,venue, lat, lng ,city,state,zip,country,address,address2, is_default) VALUES ("' . $store->store_id . '","' . $_POST['store_location'] . '", "' . $_POST['venue_name'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "1") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue_name'] . '"');
                $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $store->store_id . '","' . $_POST['store_location'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "stores")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
            }
            //Manage Apps
            Engine_Db_Table::getDefaultAdapter()->query('INSERT IGNORE INTO `engine4_estore_managestoreapps` (`store_id`) VALUES ("' . $store->store_id . '");');

            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.auto.join', 1)) {
                $store->membership()->addMember($viewer)->setUserApproved($viewer)->setResourceApproved($viewer);
            }
            if (!isset($package)) {
                if (!Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_approve'))
                    $store->is_approved = 0;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_featured'))
                    $store->featured = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_sponsored'))
                    $store->sponsored = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_verified'))
                    $store->verified = 1;
                if (Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_hot'))
                    $store->hot = 1;
            }
            // Add photo

            if (!empty($_FILES['photo']['size'])) {
                $store->setPhoto($form->photo, '', 'profile');
            }
            if (!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) {
                $store->setPhoto($_FILES['image'], '', 'profile');
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $substoreRoles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network');
            if (!isset($values['auth_view']) || empty($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            if (!isset($values['auth_comment']) || empty($values['auth_comment'])) {
                $values['auth_comment'] = 'everyone';
            }
            $createSubstoreMax = array_search($values['create_sub_store'], $substoreRoles);
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $albumMax = array_search($values['auth_album'], $roles);
            $videoMax = array_search($values['auth_video'], $roles);
            foreach ($roles as $i => $role) {
                $auth->setAllowed($store, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($store, $role, 'comment', ($i <= $commentMax));

                $auth->setAllowed($store, $role, 'album', ($i <= $albumMax));
                $auth->setAllowed($store, $role, 'video', ($i <= $videoMax));
            }
            foreach ($substoreRoles as $i => $role) {
                $auth->setAllowed($store, $role, 'create_sub_store', ($i <= $createSubstoreMax));
            }
            if (!$quckCreate) {
                //Add fields
                $customfieldform = $form->getSubForm('fields');
                if ($customfieldform) {
                    $customfieldform->setItem($store);
                    $customfieldform->saveValues();
                }
            }
            $store->save();
            if (!$store->custom_url)
                $store->custom_url = $store->getIdentity();

            $store->save();
            $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.autoopenpopup', 1);
            if ($autoOpenSharePopup && $store->draft && $store->is_approved) {
                $_SESSION['newStore'] = true;
            }
            //insert admin of store
            $storeRole = Engine_Api::_()->getDbTable('storeroles', 'estore')->createRow();
            $storeRole->user_id = $this->view->viewer()->getIdentity();
            $storeRole->store_id = $store->getIdentity();
            $storeRole->memberrole_id = 1;
            $storeRole->save();
            // Commit
            $db->commit();
            //Start Activity Feed Work
            if ($store->draft == 1 && $store->is_approved == 1) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $store, 'estore_store_create');
                if ($action) {
                    $activityApi->attachActivity($action, $store);
                }
            }
            //End Activity Feed Work
            //Start Send Approval Request to Admin
            if (!$store->is_approved) {
                if (isset($package) && $package->price > 0) {
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($store->getOwner(), $viewer, $store, 'estore_payment_notify_store');
                } else {
                    $getAdminnSuperAdmins = Engine_Api::_()->estore()->getAdminnSuperAdmins();
                    foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                        $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $store, 'estore_store_waitingadminapproval');

                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_estore_store_adminapproval', array('sender_title' => $store->getOwner()->getTitle(), 'adminmanage_link' => 'admin/estore/manage', 'object_link' => $store->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($store->getOwner(), 'notify_estore_store_storesentforapproval', array('store_title' => $store->getTitle(), 'object_link' => $store->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($viewer, $viewer, $store, 'estore_store_waitingapproval');
                //Engine_Api::_()->estore()->sendMailNotification(array('store' => $store));
            }
            //Send mail to all super admin and admins
            if ($store->is_approved) {
                $getAdminnSuperAdmins = Engine_Api::_()->estore()->getAdminnSuperAdmins();
                foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
                    $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_estore_store_superadmin', array('sender_title' => $store->getOwner()->getTitle(), 'object_link' => $store->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                $receiverEmails = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.receivenewalertemails');
                if (!empty($receiverEmails)) {
                    $receiverEmails = explode(',', $receiverEmails);
                    foreach ($receiverEmails as $receiverEmail) {
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiverEmail, 'notify_estore_store_superadmin', array('sender_title' => $store->getOwner()->getTitle(), 'object_link' => $store->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                }
            }
			$redirection = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.redirect', 1);
			if(!$store->is_approved)
				$redirect = 'manage';
			else if($store->is_approved && $redirection == 1)
				$redirect = 'dashboard';
			else
				$redirect = 'view';
            //End Work Here.
            if (!$store->is_approved) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Store created successfully and send to admin approval.'), 'store_id' => 0,'redirect'=>$redirect)));
            } else
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('store_id' => $store->getIdentity(), 'success_message' => $this->view->translate('Store created successfully.'),'redirect'=>$redirect)));
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The image you selected was too large.'), 'result' => array()));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    
    public function storePackage() {
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $result = array();
        $packages = $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'estorepackage')->getPackage(array('member_level' => $viewer->level_id, 'enabled' => 0));
        if (!count($packageMemberLevel) || !Engine_Api::_()->getApi('settings', 'core')->getSetting('estorepackage.enable.package', 0))
            return true;
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'estorepackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorepackage.package.info', array_keys($information));
        if(count($existingleftpackages)){
            $counterleft = 0;
            foreach ($existingleftpackages as $leftpackages) {
                $package = Engine_Api::_()->getItem('estorepackage_package', $leftpackages->package_id);
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
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->estorepackage()->getCurrencyPrice($package->price,'','',true));
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
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->estorepackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_featured';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_sponsored'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                if(in_array('sponsored',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_verified';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_verified'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_hot';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_hot'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_bgphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_bgphoto'];
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
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_choose_style';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_choose_style'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_count';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Stores Count');
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
                $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->estorepackage()->getCurrencyPrice($package->price,'','',true);
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
                        $result['packages'][$counter]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->estorepackage()->getCurrencyPrice($packages->price,'','',true));
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
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->estorepackage()->getCurrencyPrice($packages->price,'','',true));
                else
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'store_featured';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['store_sponsored'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('sponsored',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'store_verified';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['store_verified'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'store_hot';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['store_hot'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'store_bgphoto';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['store_bgphoto'];
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
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'store_choose_style';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['store_choose_style'];
                $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'package_description';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->description;
                    $paramscounter ++;
                }
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'store_count';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Stores Count');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->item_count;
                $paramscounter ++;
                $result['packages'][$counter]['price_type'] = Engine_Api::_()->estorepackage()->getCurrencyPrice($packages->price,'','',true);
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
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'estorepackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorepackage.package.info', array_keys($information));
        $currentCurrency =  Engine_Api::_()->estorepackage()->getCurrentCurrency();
        $result = array();
        $counterleft =0;
        if(count($existingleftpackages)){
            foreach($existingleftpackages as $packageleft)	{
                $package = Engine_Api::_()->getItem('estorepackage_package',$packageleft->package_id);
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
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->estorepackage()->getCurrencyPrice($package->price,'','',true));
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
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->estorepackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_featured';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_sponsored'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                if(in_array('sponsored',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_verified';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_verified'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_hot';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_hot'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_bgphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_bgphoto'];
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
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_choose_style';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['store_choose_style'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'store_count';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Stores Count');
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
                $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->estorepackage()->getCurrencyPrice($package->price,'','',true);
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
        $tableTransaction = Engine_Api::_()->getItemTable('estorepackage_transaction');
        $tableTransactionName = $tableTransaction->info('name');
        $storeTable = Engine_Api::_()->getDbTable('stores', 'estore');
        $storeTableName = $storeTable->info('name');
        $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
        $select = $tableTransaction->select()
            ->setIntegrityCheck(false)
            ->from($tableTransactionName)
            ->joinLeft($tableUserName, "$tableTransactionName.owner_id = $tableUserName.user_id", 'username')
            ->where($tableUserName . '.user_id IS NOT NULL')
            ->joinLeft($storeTableName, "$tableTransactionName.transaction_id = $storeTableName.transaction_id", 'store_id')
            ->where($storeTableName . '.store_id IS NOT NULL')
            ->where($tableTransactionName . '.owner_id =?', $viewer->getIdentity());
        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($this->_getParam('limit',10));
        $paginator->setCurrentPageNumber($this->_getParam('store', 1));
        $result = $this->getTransactions($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getTransactions($paginator){
        $result = array();
        $result['title'] = $this->view->translate('View Transactions of Store Packages');
        $counter = 0;
        foreach($paginator as $item){
            $user = Engine_Api::_()->getItem('user',$item->owner_id);
            $store = Engine_Api::_()->getItem('store',$item->store_id);
            $package = Engine_Api::_()->getItem('estorepackage_package',$item->package_id);
            $data[$counter]['transaction_id'] = $item->transaction_id;
            $data[$counter]['id'] = $item->store_id;
            $data[$counter]['title'] = $this->view->translate(Engine_Api::_()->sesbasic()->textTruncation($store->getTitle(),25));
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
        $form = new Estorepackage_Form_Cancel();
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

        Engine_Api::_()->getDbTable('packages','estorepackage')->cancelSubscription(array('package_id' => $packageId));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => array('message'=>$this->view->translate('Your Package Subscription has been Deleted Successfully.'))));
    }
    
    public function editAction() {
		
        $store_id = $this->_getParam('store_id', null);
        
        if (!$store_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('stores', $store_id);
        } else {
            $store = Engine_Api::_()->core()->getSubject();
        }

        $previousTitle = $store->getTitle();
        $defaultProfileId = 1;
        if (isset($store->category_id) && $store->category_id != 0)
            $category_id = $store->category_id;
        else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
            $category_id = $_POST['category_id'];
        else
            $category_id = 0;
        if (isset($store->subsubcat_id) && $store->subsubcat_id != 0)
            $subsubcat_id = $store->subsubcat_id;
        else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
            $subsubcat_id = $_POST['subsubcat_id'];
        else
            $subsubcat_id = 0;
        if (isset($store->subcat_id) && $store->subcat_id != 0)
            $subcat_id = $store->subcat_id;
        else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
            $subcat_id = $_POST['subcat_id'];
        else
            $subcat_id = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        if (!Engine_Api::_()->estore()->storeRolePermission($store, Zend_Controller_Front::getInstance()->getRequest()->getActionName())) {
            if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $store->isOwner($viewer)))
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
        }
        $form = new Estore_Form_Edit(array('defaultProfileId' => $defaultProfileId));
    		$form->removeElement('store_location');
            if($form->getElement('enable_lock')){
                $form->removeElement('enable_lock');
                $form->removeElement('store_password');
            }
        if($_GET['sesapi_platform'] == 1){
          if( $form->getElement('member_title_singular')){
            $form->getElement('member_title_singular')->setDescription("");
            $form->getElement('member_title_plural')->setDescription("");
          }
         }
        $tagStr = '';
        foreach ($store->tags()->getTagMaps() as $tagMap) {
            $tag = $tagMap->getTag();
            if (!isset($tag->text))
                continue;
            if ('' !== $tagStr)
                $tagStr .= ', ';
            $tagStr .= $tag->text;
        }
        $values = $store->toArray();
        $values['tags'] = $tagStr;
        $values['networks'] = ltrim($store['networks']);
        //if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seslocation')) {
          //$form->getElement('store_location')->setLabel('Location');
        //}
        $form->populate($values);
        //if (!$this->getRequest()->isPost()) {

            // Populate auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $substoreRoles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network');
            foreach ($roles as $role) {
                if (isset($form->auth_view->options[$role]) && $auth->isAllowed($store, $role, 'view'))
                    $form->auth_view->setValue($role);
                if (isset($form->auth_comment->options[$role]) && $auth->isAllowed($store, $role, 'comment'))
                    $form->auth_comment->setValue($role);
                if (isset($form->auth_album->options[$role]) && $auth->isAllowed($store, $role, 'album'))
                    $form->auth_album->setValue($role);

                if (isset($form->auth_video->options[$role]) && $auth->isAllowed($store, $role, 'video'))
                    $form->auth_video->setValue($role);
            }
            foreach ($substoreRoles as $role) {
                if (isset($form->create_sub_store->options[$role]) && $auth->isAllowed($store, $role, 'create_sub_store'))
                    $form->create_sub_store->setValue($role);
            }
          if($store->custom_url && Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.edit.url', 0))
            $form->custom_url_store->setValue($store->custom_url);
            if ($form->draft->getValue() == 1)
                $form->removeElement('draft');
        //}
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        if (isset($_POST['custom_url_store']) && !empty($_POST['custom_url_store'])) {
            $custom_url = Engine_Api::_()->getDbTable('stores', 'estore')->checkCustomUrl($_POST['custom_url_store'], $store->store_id);
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
            $values['approval'] = $settings->getSetting('estore.default.joinoption', 1) ? 0 : 1;
        elseif (!isset($values['approval']))
            $values['approval'] = $settings->getSetting('estore.default.approvaloption', 1) ? 0 : 1;
        // Process
        $db = Engine_Api::_()->getItemTable('stores')->getAdapter();
        $db->beginTransaction();
        try {
            if (!($values['draft']))
                unset($values['draft']);
            $store->setFromArray($values);
            $store->save();
            $tags = preg_split('/[,]+/', $values['tags']);
            $store->tags()->setTagMaps($viewer, $tags);
            if (!$values['vote_type'])
                $values['resulttime'] = '';
            if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
                $store->custom_url = $_POST['custom_url'];
            else if(!empty($_POST['custom_url_store']))
              $store->custom_url = $_POST['custom_url_store'];
            $store->save();
            $newstoreTitle = $store->getTitle();
            // Add photo
            if (!empty($values['photo'])) {
                $store->setPhoto($form->photo);
            }
            // Add cover photo
            if (!empty($values['cover'])) {
                $store->setCover($form->cover);
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            if (empty($values['auth_view']))
                $values['auth_view'] = 'everyone';
            if (empty($values['auth_comment']))
                $values['auth_comment'] = 'everyone';
            $createSubstoreMax = array_search($values['create_sub_store'], $substoreRoles);
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);

            $albumMax = array_search(@$values['auth_album'], $roles);
            $videoMax = array_search(@$values['auth_video'], $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($store, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($store, $role, 'comment', ($i <= $commentMax));

                $auth->setAllowed($store, $role, 'album', ($i <= $albumMax));
                $auth->setAllowed($store, $role, 'video', ($i <= $videoMax));
            }
            foreach ($substoreRoles as $i => $role) {
                $auth->setAllowed($store, $role, 'create_sub_store', ($i <= $createSubstoreMax));
            }
            $store->save();
            $db->commit();
            //Start Activity Feed Work
            if (isset($values['draft']) && $store->draft == 1 && $store->is_approved == 1) {
                $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
            }
            if ($previousTitle != $newstoreTitle) {
                //Send to all joined members
                $joinedMembers = Engine_Api::_()->estore()->getallJoinedMembers($store);
                foreach ($joinedMembers as $joinedMember) {
                    if ($joinedMember->user_id == $store->owner_id)
                        continue;
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $store, 'estore_store_storesijoinedstorenamechanged', array('old_store_title' => $previousTitle, 'new_store_link' => $newstoreTitle));
                }
                //Send to all followed members
                $followerMembers = Engine_Api::_()->getDbTable('followers', 'estore')->getFollowers($store->getIdentity());
                foreach ($followerMembers as $followerMember) {
                    if ($followerMember->owner_id == $store->owner_id)
                        continue;
                    $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $store, 'estore_store_storesifollowedstorenamechanged', array('old_store_title' => $previousTitle, 'new_store_link' => $newstoreTitle));

                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($followerMember, 'notify_estore_store_storenamechanged', array('store_title' => $store->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $store->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
                //Send to all favourites members
                $followerMembers = Engine_Api::_()->getDbTable('favourites', 'estore')->getAllFavMembers($store->getIdentity());
                foreach ($followerMembers as $followerMember) {
                    if ($followerMember->owner_id == $store->owner_id)
                        continue;
                    $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($joinedMember, Engine_Api::_()->user()->getViewer(), $store, 'estore_store_storesifavousitestorenamechanged', array('old_store_title' => $previousTitle, 'new_store_link' => $newstoreTitle));
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
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('store_id' => $store->getIdentity(), 'success_message' => $this->view->translate('Store edited successfully.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
}
