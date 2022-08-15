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
class Sesbusiness_IndexController extends Sesapi_Controller_Action_Standard
{
    public function init()
    {
        if (!$this->_helper->requireAuth()->setAuthParams('businesses', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        $business_id = $this->_getParam('business_id');

        $business = null;
        $business = Engine_Api::_()->getItem('businesses', $business_id);
        if ($business) {
            if ($business) {
                Engine_Api::_()->core()->setSubject($business);
            } else {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            }
        }
    }
    public function browsesearchAction()
    {
        $view_type = $this->_getParam('view_type', 'horizontal');
        $defaultProfileId = 1;
        $search_for = $search_for = $this->_getParam('search_for', 'page');
        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corebusinessesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corebusinessesTableName = $corebusinessesTable->info('name');
        $select = $corebusinessesTable->select()
            ->setIntegrityCheck(false)
            ->from($corebusinessesTable, null)
            ->where($coreContentTableName . '.name=?', 'sesbusiness.browse-search')
            ->joinLeft($coreContentTableName, $corebusinessesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
            ->where($corebusinessesTableName . '.name = ?', 'sesbusiness_index_browse');
        $id = $select->query()->fetchColumn();
        $form = new Sesbusiness_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $form->setMethod('get')->populate($request->getParams());
        if($form->getElement('lat')){
          $form->removeElement('lat');
          $form->removeElement('lng');
        }
        $form->removeElement('cancel');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
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
            case 'close':
                $params['sort'] = 'close';
                break;
            case 'open':
                $params['sort'] = 'open';
                break;
        }
        $params['widgetManage'] = true;
        $paginator = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')->getBusinessPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.share', 0);
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'bs_can_join') : 0;
        $filterOptionsMenu = array();
        $filterMenucounter = 0;
        $resultmenu[$filterMenucounter]['name'] = 'open';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Open');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'close';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Close');
        $filterMenucounter++;
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
            $businessCounter = 0;
            foreach ($paginator as $businesses) {
                $businessArray = $businesses->toArray();
                if (!$canFavourite)
                    unset($businessArray['favourite_count']);
                if (!$canFollow)
                    unset($businessArray['follow_count']);
                unset($businessArray['location']);
                $result[$businessCounter] = $businessArray;
                $statsCounter = 0;
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($businesses, '', "");
                if (image) {
                    $result[$businessCounter]['images'] = $image;
                } else {
                    $result[$businessCounter]['images'] = $image;
                }
                $isBusinessEdit = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness')->toCheckUserBusinessRole($viewer->getIdentity(), $businesses->getIdentity(), 'manage_dashboard', 'edit');
                $isBusinessDelete = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness')->toCheckUserBusinessRole($viewer->getIdentity(), $businesses->getIdentity(), 'manage_dashboard', 'delete');
                $buttonCounter = 0;
                if ($isBusinessEdit) {
                    $result[$businessCounter]['buttons'][$buttonCounter]['name'] = 'edit';
                    $result[$businessCounter]['buttons'][$buttonCounter]['label'] = 'Edit';
                    $buttonCounter++;
                }
                if ($isBusinessDelete) {
                    $result[$businessCounter]['buttons'][$buttonCounter]['name'] = 'delete';
                    $result[$businessCounter]['buttons'][$buttonCounter]['label'] = 'Delete';
                    $buttonCounter++;
                }
                if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesbusinesspackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspackage.enable.package', 0) && (_SESAPI_VERSION_ANDROID >= 2.5 || _SESAPI_VERSION_IOS >= 1.7)) {
                    $package = Engine_Api::_()->getItem('sesbusinesspackage_package', $businesses->package_id);
                    if($package){
                        if (!$package->isFree()) {
                            $transaction = Engine_Api::_()->getDbTable('transactions', 'sesbusinesspackage')->getItemTransaction(array('order_package_id' => $businesses->orderspackage_id, 'business' => $businesses));
                            if ($transaction) {
                                if ($package->isOneTime()) {
                                    if ($package->is_renew_link) {
                                        if (!empty($transaction->expiration_date) && $transaction->expiration_date != '3000-00-00 00:00:00') {
                                            $datediff = strtotime($transaction->expiration_date) - time();
                                            $daysLeft = floor($datediff / (60 * 60 * 24));
                                            if ($daysLeft <= $renew_link_days || strtotime($transaction->expiration_date) <= time()) {
                                                $result[$businessCounter]['buttons'][$buttonCounter]['name'] = 'payment';
                                                $result[$businessCounter]['buttons'][$buttonCounter]['value'] = $this->getBaseUrl(true, $this->view->url(array('business_id' => $businesses->business_id, 'action' => 'index'), 'sesbusinesspackage_payment', true));
                                                $result[$businessCounter]['buttons'][$buttonCounter]['label'] = $this->view->translate("Renew Page Payment");
                                                $buttonCounter++;
                                            }
                                        }else {
                                            $result[$businessCounter]['buttons'][$buttonCounter]['name'] = 'package_state';
                                            $result[$businessCounter]['buttons'][$buttonCounter]['value'] = ucwords($transaction->state);
                                            $result[$businessCounter]['buttons'][$buttonCounter]['label'] = $this->view->translate("Payment Status");
                                            $buttonCounter++;
                                        }
                                    }
                                }
                            }else {
                                $result[$businessCounter]['buttons'][$buttonCounter]['name'] = 'payment';
                                $result[$businessCounter]['buttons'][$buttonCounter]['value'] = $this->getBaseUrl(true, $this->view->url(array('business_id' => $businesses->business_id, 'action' => 'index'), 'sesbusinesspackage_payment', true));
                                $result[$businessCounter]['buttons'][$buttonCounter]['label'] = $this->view->translate("Make Payment");
                                $buttonCounter++;
                            }
                        }
                    }
                }
                $businessCounter++;
            }
        }
        $data['businesses'] = $result;
        if ($this->_getParam('page', 1))
            $data['filterMenuOptions'] = $resultmenu;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data), $extraParams));
    }
    public function checkVersion($android,$ios){
        if(is_numeric(_SESAPI_VERSION_ANDROID) && _SESAPI_VERSION_ANDROID >= $android)
            return  true;
        if(is_numeric(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS >= $ios)
            return true;
        return false;
    }
    public function menuAction()
    {
        $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbusiness_main', array());
        $menu_counter = 0;
        foreach ($menus as $menu) {
            $class = end(explode(' ', $menu->class));
            if ($class != "sesbusiness_main_categories" && $class != "sesbusiness_main_manage_package" && $class != 'sesbusiness_main_browse' && $class != 'sesbusiness_main_featured' && $class != 'sesbusiness_main_featured' && $class != 'sesbusiness_main_verified' && $class != 'sesbusiness_main_sponsored' && $class != 'sesbusiness_main_hot' && $class != 'sesbusiness_main_create' && $class != 'sesbusiness_main_manage' && $class != 'sesbusiness_main_businessalbumbrowse' && 'sesbusinessvideo_main_browsehome' != $class && 'sesbusiness_main_businesspollbrowse' != $class)
                continue;
            if($class == "sesbusiness_main_manage_package" && !($this->checkVersion(2.6,1.7)))
                continue;
            if($class == "sesbusinessvideo_main_browsehome" && !($this->checkVersion(2.6,1.7)))
                continue;
            if($class == "sesbusiness_main_businesspollbrowse" && !($this->checkVersion(2.6,1.7)))
                continue;
            $result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
            $result_menu[$menu_counter]['action'] = $class;
            $result_menu[$menu_counter]['isActive'] = $menu->active;
            $menu_counter++;
        }
        $result['menus'] = $result_menu;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function browseAction(){
        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corebusinessesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corebusinessesTableName = $corebusinessesTable->info('name');
        $select = $corebusinessesTable->select()
            ->setIntegrityCheck(false)
            ->from($corebusinessesTable, null)
            ->where($coreContentTableName . '.name=?', 'sesbusiness.browse-search')
            ->joinLeft($coreContentTableName, $corebusinessesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
            ->where($corebusinessesTableName . '.name = ?', 'sesbusiness_index_browse');
        $id = $select->query()->fetchColumn();
        if (!empty($_POST['location'])) {
            $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
            if ($latlng) {
                $_POST['lat'] = $latlng['lat'];
                $_POST['lng'] = $latlng['lng'];
            }
        }
        $form = new Sesbusiness_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
        $form->populate($_POST);
        $params = $form->getValues();
		if($params['sort']){
			$params['info'] = $params['sort'];
		}
		$value = array();
		if(isset($_POST['filter_sort'])){
			 switch ($_POST['filter_sort']) {
            case "sesbusiness_main_featured":
               $value['sort'] = 'featured';
			    break;
            case "sesbusiness_main_verified":
                $value['sort'] = 'verified';
				 break;
            case "sesbusiness_main_sponsored":
                $value['sort'] = 'sponsored';
				 break;
            case "sesbusiness_main_hot":
                $value['sort'] = 'hot';
				 break;
			}
		}

        $value['status'] = 1;
        $value['search'] = 1;
        $value['draft'] = "1";
        if (isset($params['search']))
            $params['text'] = addslashes($params['search']);
        $params['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
        $params = array_merge($params, $value);
        //echo '<pre>';print_r($params);die;
        $paginator = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')
            ->getBusinessPaginator($params);

        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $page = $this->_getParam('page', 1);

		
        if ($page == 1 && $_POST['filter_sort'] == 'sesbusiness_main_browse') {
            $categories = Engine_Api::_()->getDbtable('categories', 'sesbusiness')->getCategory(array('column_name' => '*', 'limit' => 25));
            $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbusiness_main', array());
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
                $result_category[$category_counter]['total_business_categories'] = $category->total_business_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;
                $category_counter++;
            }
            if (!isset($params['category_id'])) {
                $result['category'] = $result_category;
                if ($paginator->getTotalItemCount() > 0) {
                    $popularBusinesses = $this->getPopularbusinesses($paginator);
                    if (count($popularBusinesses)) {
                        $result['popularbusinesses'] = $this->getPopularbusinesses($paginator);
                    }
                }
            }
        }
        $result['businesses'] = $this->getbusinesses($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getPopularbusinesses($businessPaginator){
        $businessid = array();
        foreach ($businessPaginator as $businesses) {
            $businessid[] = $businesses->business_id;
        }
        $params['info'] = 'most_viewed';
        $params['notBusinessId'] = $businessid;
        $paginator = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')->getBusinessPaginator($params);
        $paginator->setItemCountPerPage(6);
        $paginator->setCurrentPageNumber(1);
        $result = $this->getbusinesses($paginator);
        return $result;
    }
    public function getBusinesses($paginator){
        $result = array();
        $counter = 0;
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_favourite', 0);
		    $likeFollowIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.integration', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.share', 1);
        $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_show_userdetail', 0);
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
		    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'bs_can_join') : 0;
        foreach ($paginator as $businesses) {
            $business = $businesses->toArray();
            $result[$counter] = $business;
            $result[$counter]['likeFollowIntegrate'] = $likeFollowIntegrate?true:false;
            if(!$hideIdentity)
            $result[$counter]['owner_title'] = $businesses->getOwner()->getTitle();
            $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
            $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
            $result[$counter]['currency'] = $curArr[$currency];
            if ($businesses->category_id) {
                $category = Engine_Api::_()->getItem('sesbusiness_category', $businesses->category_id);
                if ($category) {
                    $result[$counter]['category_title'] = $category->category_name;
                    if ($businesses->subcat_id) {
                        $subcat = Engine_Api::_()->getItem('sesbusiness_category', $businesses->subcat_id);
                        if ($subcat) {
                            $result[$counter]['subcategory_title'] = $subcat->category_name;
                            if ($businesses->subsubcat_id) {
                                $subsubcat = Engine_Api::_()->getItem('sesbusiness_category', $businesses->subsubcat_id);
                                if ($subsubcat) {
                                    $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                                }
                            }
                        }
                    }
                }
            }
            $tags = array();
            foreach ($businesses->tags()->getTagMaps() as $tagmap) {
                $tags[] = array_merge($tagmap->toArray(),
                    array(
                        'id' => $tagmap->getIdentity(),
                        'text' => $tagmap->getTitle(),
                        'href' => $tagmap->getHref(),
                        'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                    ));
            }
            if (count($tags)) {
                $result[$counter]['tag'] = $tags;
            }
            if ($businesses->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.location', 1)) {
                unset($business['location']);
                $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('businesses', $businesses->getIdentity());
                if ($location) {
                    $businessdata['location'] = $location->toArray();
                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.map.integration', 1)) {
                        $businessdata['location']['showMap'] = true;
                    } else {
                        $businessdata['location']['showMap'] = false;
                    }
                }
            }
            $result[$counter]['images']['main']= $this->getBaseUrl(true, $businesses->getPhotoUrl());
            $result[$counter]['cover_image']['main'] = $this->getBaseUrl(true, $businesses->getCoverPhotoUrl());
            $result[$counter]['cover_images']['main'] = $result[$counter]['cover_image']['main'];
            $showLoginformFalse = false;
			
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.contact.details', 1)) {
                $showLoginformFalse = true;
            }
            $i = 0;
            if ($businesses->business_contact_email || $businesses->business_contact_phone || $businesses->business_contact_website) {
                if ($businesses->business_contact_email) {

                    $result[$counter]['buttons'][$i]['name'] = 'mail';
                    $result[$counter]['buttons'][$i]['label'] = 'Send Email';
                    $result[$counter]['buttons'][$i]['value'] = $businesses->business_contact_email;
                    $i++;

                }
                if ($businesses->business_contact_phone) {
                    $result[$counter]['buttons'][$i]['name'] = 'phone';
                    $result[$counter]['buttons'][$i]['label'] = 'Call';
                    $result[$counter]['buttons'][$i]['value'] = $businesses->business_contact_phone;
                    $i++;
                }
                if ($businesses->business_contact_website) {

                    $result[$counter]['buttons'][$i]['name'] = 'website';
                    $result[$counter]['buttons'][$i]['label'] = 'Visit Website';
                    $result[$counter]['buttons'][$i]['value'] = $businesses->business_contact_website;
                    $i++;
                }

                $result[$counter]['showLoginForm'] = $showLoginformFalse;


            }

            if ($businesses->is_approved) {
				
                if ($shareType) {
                    $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $businesses->getPhotoUrl());;
										$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$businesses->getHref());
                    $result[$counter]["share"]["title"] = $businesses->getTitle();
                    $result[$counter]["share"]["description"] = strip_tags($businesses->getDescription());
					$result[$counter]["share"]["setting"] = $shareType;
                    $result[$counter]["share"]['urlParams'] = array(
                        "type" => $businesses->getType(),
                        "id" => $businesses->getIdentity()
                    );
                }
            }
            if ($businesses->is_approved) {
				if($viewerId != $businesses->owner_id){
				    $result[$counter]['buttons'][$i]['name'] = 'contact';
					$result[$counter]['buttons'][$i]['label'] = 'Contact';
					$i++;
				}
                if ($shareType) {
                    $result[$counter]['buttons'][$i]['name'] = 'share';
                    $result[$counter]['buttons'][$i]['label'] = 'Share';
                    $i++;
                }
                if ($canJoin) {
                   // if ($viewerId) {
                        $row = $businesses->membership()->getRow($viewer);
                        if (null === $row) {
                            if ($businesses->membership()->isResourceApprovalRequired()) {
                                $result[$counter]['buttons'][$i]['name'] = 'request';
                                $result[$counter]['buttons'][$i]['label'] = 'Request Membership';
                                $i++;
                            } else {
                                $result[$counter]['buttons'][$i]['name'] = 'join';
                                $result[$counter]['buttons'][$i]['label'] = 'Join Business';
                                $i++;
                            }
                        } else if ($row->active) {
                            if (!$businesses->isOwner($viewer)) {
                                $result[$counter]['buttons'][$i]['label'] = 'Leave Business';
                                $result[$counter]['buttons'][$i]['name'] = 'leave';
                                $i++;
                            }
                        } else if (!$row->resource_approved && $row->user_approved) {
                            $result[$counter]['buttons'][$i]['label'] = 'Cancel Membership Request';
                            $result[$counter]['buttons'][$i]['name'] = 'cancel';
                            $i++;

                        } else if (!$row->user_approved && $row->resource_approved) {
                            $result[$counter]['buttons'][$i]['label'] = 'Accept Membership Request';
                            $result[$counter]['buttons'][$i]['name'] = 'accept';
                            $i++;
                            $result[$counter]['buttons'][$i]['label'] = 'Ignore Membership Request';
                            $result[$counter]['buttons'][$i]['name'] = 'reject';
                        }
                  //  }
                }
            }
            if ($viewerId != 0) {    
                $result[$counter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($businesses);
                $result[$counter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($businesses);
                if ($canFavourite) {
                    $result[$counter]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($businesses, 'favourites', 'sesbusiness', 'businesses', 'owner_id');
                    $result[$counter]['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($businesses, 'favourites', 'sesbusiness', 'businesses', 'owner_id');
                }
                if ($canFollow) {
                    $result[$counter]['is_content_follow'] = $this->contentFollow($businesses, 'followers', 'sesbusiness', 'businesses', 'owner_id');
                    $result[$counter]['content_follow_count'] = (int)$this->getContentFollowCount($businesses, 'followers', 'sesbusiness', 'businesses', 'owner_id');
                }

            }

            if ($businesses->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.location', 1)) {
                unset($businesses['location']);
                $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('businesses', $businesses->getIdentity());
                if ($location) {
                    $result[$counter]['location'] = $location->toArray();
                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.map.integration', 1)) {
                        $result[$counter]['location']['showMap'] = true;
                    } else {
                        $result[$counter]['location']['showMap'] = false;
                    }
                }
            }
            $counter++;
        }
		
        $results['businesses'] = $result;
        return $result;
    }
    public function contentFollow($subject = null,$tableName = "",$modulename = "",$resource_type = "",$column_name = "user_id"){
    $viewer = Engine_Api::_()->user()->getViewer();
    //return if non logged in user or content empty
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
    public function categoriesAction(){
        $params =array();
        $paginator = Engine_Api::_()->getDbTable('categories', 'sesbusiness')->getBusinessPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        if ($paginator->getCurrentPageNumber() == 1) {
            $categories = Engine_Api::_()->getDbtable('categories', 'sesbusiness')->getCategory(array('column_name' => '*', 'limit' => 25));
            $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbusiness_main', array());
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
                $result_category[$category_counter]['total_business_categories'] = $category->total_business_categories;
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
    public function getCategory($categoryPaginator){
        $result = array();
        $counter = 0;
        foreach ($categoryPaginator as $categories) {
            $business = $categories->toArray();
            $params['category_id'] = $categories->category_id;
            $params['limit'] = 5;
            $paginator = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')->getBusinessPaginator($params);
            $paginator->setItemCountPerPage(3);
            $paginator->setCurrentPageNumber(1);
			if($paginator->getTotalItemCount() > 0){
				$result[$counter] = $business;
				$result[$counter]['items'] = $this->getbusinesses($paginator);
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
    public function contactAction(){
        $ownerId[] = $this->_getParam('owner_id', 0);
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        // Get form
        if (!$this->_getParam('owner_id', 0)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $form = new Sesbusiness_Form_ContactOwner();
        $form->business_owner_id->setValue($this->_getParam('owner_id', 0));

        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
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

            if ($values['business_owner_id'] != $viewer->getIdentity()) {

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
    public function joinAction()
    {
        $business_id = $this->getParam('business_id', 0);
        if (!$business_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $item = Engine_Api::_()->getItem('businesses', $business_id);
        if ($item->membership()->isResourceApprovalRequired()) {
            $row = $item->membership()->getReceiver()
                ->select()
                ->where('resource_id = ?', $item->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->query()
                ->fetch(Zend_Db::FETCH_ASSOC, 0);;


            if (empty($row)) {

                // has not yet requested an invite
                $message = $this->request();
                if ($message == 'Successfully requested.') {
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message,'menus'=>$this->getButtonMenus($item))));
                } else {
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('database_error'), 'result' => array()));

                }
            } elseif ($row['user_approved'] && !$row['resource_approved']) {

                // has requested an invite; show cancel invite business
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Has requested an invite'),'menus'=>$this->getButtonMenus($item))));
                //              return $this->_helper->redirector->gotoRoute(array('action' => 'cancel', 'format' => 'smoothbox'));
            }


        }

        $form = new Sesbusiness_Form_Member_Join();
        //      $form->business_owner_id->setValue($this->_getParam('owner_id',0));
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
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
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'sesbusiness_business_join');

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_sesbusiness_business_businessjoined', array('business_title' => $item->getTitle(), 'sender_title' => $viewer->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));

            //Send to all joined members
            $joinedMembers = Engine_Api::_()->sesbusiness()->getallJoinedMembers($item);

            foreach ($joinedMembers as $joinedMember) {
                if ($joinedMember->user_id == $item->owner_id) continue;
                $joinedMember = Engine_Api::_()->getItem('user', $joinedMember->user_id);
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($joinedMember, $viewer, $item, 'sesbusiness_business_businessesijoinedjoin');

                Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_sesbusiness_business_joinbusinessjoined', array('business_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }

            $followerMembers = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->getFollowers($item->getIdentity());

            foreach ($followerMembers as $followerMember) {
                if ($followerMember->owner_id == $item->owner_id) continue;
                $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($followerMember, $viewer, $item, 'sesbusiness_business_businessesifollowedjoin');

                Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_sesbusiness_business_joinedbusinessfollowed', array('business_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }

            // Add activity if membership status was not valid from before
            if (!$membership_status) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $item, 'sesbusiness_business_join');
                if ($action) {
                    $activityApi->attachActivity($action, $item);
                }
            }
            $db->commit();

            $viewerId = $viewer->getIdentity();
            $result['message'] = $this->view->translate('Business Successfully Joined.');
            $result['menus']  = $this->getButtonMenus($item);

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

        } catch (Exception $e) {
            $db->rollBack();
            $result['message'] = 'Database Error.';
            //              throw $e;
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
        $form = new Sesbusiness_Form_Member_Request();

        // Process form
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $subject->membership()->addMember($viewer)->setUserApproved($viewer);

                // Add notification
                $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
                $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'sesbusiness_approve');

                $db->commit();
                $messgae = 'Successfully requested.';
            } catch (Exception $e) {
                $db->rollBack();
                $messgae = 'database_error';
            }

            return $messgae;
        }
    }
    public function likeAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if ($viewer_id == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        $type = $this->_getParam('type', false);
        $item_id = $this->_getParam('id');
        if ($type == 'businesses') {
            $dbTable = 'businesses';
            $resorces_id = 'business_id';
            $notificationType = 'businesses_like';
        } elseif ($type == 'sesbusiness_photo') {
            $dbTable = 'photos';
            $resorces_id = 'photo_id';
            $notificationType = 'sesbusiness_photo_like';
        } elseif ($type == 'sesbusiness_album') {
            $dbTable = 'albums';
            $resorces_id = 'album_id';
            $notificationType = 'sesbusiness_album_like';
        }
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));

        }
        $itemTable = Engine_Api::_()->getDbtable($dbTable, 'sesbusiness');
        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
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
                $temp['data']['message'] = $this->view->translate('Business Successfully Unliked.');
            } catch (Exception $e) {
                $db->rollBack();
				        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$this->getMessage(), 'result' => array()));
            }
            $item = Engine_Api::_()->getItem($type, $item_id);
            $owner = $item->getOwner();
            if (!empty($notificationType)) {
                Engine_Api::_()->sesapi()->deleteFeed(array('type' => $notificationType, "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')
                    ->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item
                        ->getType(), "object_id = ?" => $item->getIdentity()));
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
                //Commit
                $db->commit();
                $temp['data']['message'] = $this->view->translate('Business Successfully Liked.');
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            //Send notification and activity feed work.
            $item = Engine_Api::_()->getItem($type, $item_id);
            $subject = $item;
            $owner = $subject->getOwner();
            if ($notificationType && $owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
                //Send to all joined members
                if ($type == 'businesses') {
                    $joinedMembers = Engine_Api::_()->sesbusiness()->getallJoinedMembers($item);
                    foreach ($joinedMembers as $joinedMember) {
                        $joinedMember = Engine_Api::_()->getItem('user', $joinedMember->user_id);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($joinedMember, $viewer, $subject, 'sesbusiness_business_businessesijoinedlike');
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_sesbusiness_business_likeBusinessjoined', array('business_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                    $followerMembers = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->getFollowers($item->getIdentity());
                    foreach ($followerMembers as $followerMember) {
                        $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($followerMember, $viewer, $subject, 'sesbusiness_business_businessesifollowedlike');
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($followerMember, 'notify_sesbusiness_business_likebusinessfollowed', array('business_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_sesbusiness_business_businessliked', array('business_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }


                //$result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
				
                if ($notificationType == 'sesbusiness_business_like') {
                    $action = $activityTable->addActivity($viewer, $subject, $notificationType);
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                } else if ($notificationType == 'sesbusiness_album_like') {
                    $business = Engine_Api::_()->getItem('businesses', $subject->business_id);
                    $albumlink = '<a href="' . $subject->getHref() . '">' . 'album' . '</a>';
                    $businesslink = '<a href="' . $business->getHref() . '">' . $business->getTitle() . '</a>';
                    $action = $activityTable->addActivity($viewer, $subject, $notificationType, null, array('albumlink' => $albumlink, 'businessname' => $businesslink));
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                } else if ($notificationType == 'sesbusiness_photo_like') {
                    $business = Engine_Api::_()->getItem('businesses', $subject->business_id);
                    $photolink = '<a href="' . $subject->getHref() . '">' . 'photo' . '</a>';
                    $businesslink = '<a href="' . $business->getHref() . '">' . $business->getTitle() . '</a>';
                    $action = $activityTable->addActivity($viewer, $subject, $notificationType, null, array('photolink' => $photolink, 'businessname' => $businesslink));
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                }
            }
            if ($type == 'businesses') {
                $businessFollowers = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->getFollowers($subject->business_id);
                if (count($businessFollowers) > 0) {
                    foreach ($businessFollowers as $follower) {
                        $user = Engine_Api::_()->getItem('user', $follower->owner_id);
                        if ($user->getIdentity()) {
                            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'sesbusiness_business_like_followed');
                        }
                    }
                }
            }

            $temp['data']['like_count'] = $item->like_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));

        }
    }
    public function followAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        $item_id = $this->_getParam('id',$this->_getParam('business_id',0));
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
        }
        $Fav = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->getItemFollower('businesses', $item_id);
        $followerItem = Engine_Api::_()->getDbtable('businesses', 'sesbusiness');

        if (count($Fav) > 0) {
            //delete
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Fav->delete();
                $db->commit();
                $temp['data']['message'] = 'Business Successfully Unfollowed.';
            } catch (Exception $e) {
                $db->rollBack();
                $temp['data']['message'] = $e->getMessage();
            }
            $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count - 1')), array('business_id = ?' => $item_id));
            $item = Engine_Api::_()->getItem('businesses', $item_id);
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'sesbusiness_business_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
            Engine_Api::_()->sesapi()->deleteFeed(array('type' => 'sesbusiness_business_follow', "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
            $temp['data']['follow_count'] = $item->follow_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));

        } else {

            //update
            $db = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->getAdapter();
            $db->beginTransaction();
            try {
                $follow = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->createRow();
                $follow->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $follow->resource_type = 'businesses';
                $follow->resource_id = $item_id;
                $follow->save();
                $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count + 1')), array('business_id = ?' => $item_id));
                // Commit
                $db->commit();
                $temp['data']['message'] = 'Business Successfully Followed.';
            } catch (Exception $e) {

                $db->rollBack();
                $temp['data']['message'] = 'Database Error.';

                //               throw $e;
            }
            //send notification and activity feed work.
            $item = Engine_Api::_()->getItem('businesses', @$item_id);
            $subject = $item;
            $owner = $subject->getOwner();

            if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item->getOwner(), $viewer, $item, 'sesbusiness_business_follow');
                $result = $activityTable->fetchRow(array('type =?' => 'sesbusiness_business_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                if (!$result) {
                    $action = $activityTable->addActivity($viewer, $subject, 'sesbusiness_business_follow');
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_sesbusiness_business_businessfollowed', array('business_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            $temp['data']['follow_count'] = $item->follow_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
        }
    }
    public function favouriteAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        if ($this->_getParam('type') == 'businesses') {
            $type = 'businesses';
            $dbTable = 'businesses';
            $resorces_id = 'business_id';
            $notificationType = 'sesbusiness_business_favourite';
        } elseif ($this->_getParam('type') == 'sesbusiness_photo') {
            $type = 'sesbusiness_photo';
            $dbTable = 'photos';
            $resorces_id = 'photo_id';
            $notificationType = '';
        } elseif ($this->_getParam('type') == 'sesbusiness_album') {
            $type = 'sesbusiness_album';
            $dbTable = 'albums';
            $resorces_id = 'album_id';
            $notificationType = '';
        }
        $item_id = $this->_getParam('id');
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));

        }
        $Fav = Engine_Api::_()->getDbTable('favourites', 'sesbusiness')->getItemfav($type, $item_id);
        $favItem = Engine_Api::_()->getDbtable($dbTable, 'sesbusiness');
        if (count($Fav) > 0) {
            //delete
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Fav->delete();
                $db->commit();
                $temp['data']['message'] = 'Business Successfully Unfavourited.';
            } catch (Exception $e) {
                $db->rollBack();
                $temp['data']['message'] = 'Database Error.';
                //                throw $e;
            }
            $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
            $item = Engine_Api::_()->getItem($type, $item_id);
            if ($notificationType) {
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
                Engine_Api::_()->sesapi()->deleteFeed(array('type' => $notificationType, "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
            }


            $temp['data']['favourite_count'] = $item->favourite_count;

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));


        } else {
            //update
            $db = Engine_Api::_()->getDbTable('favourites', 'sesbusiness')->getAdapter();
            $db->beginTransaction();
            try {
                $fav = Engine_Api::_()->getDbTable('favourites', 'sesbusiness')->createRow();
                $fav->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $fav->resource_type = $type;
                $fav->resource_id = $item_id;
                $fav->save();
                $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1')), array($resorces_id . '= ?' => $item_id));
                // Commit
                $db->commit();
                $temp['data']['message'] = 'Business Successfully Favourited.';
            } catch (Exception $e) {
                $db->rollBack();
                $temp['data']['message'] = 'Database Error.';
                //                throw $e;
            }
            //Send Notification and Activity Feed Work.
            $item = Engine_Api::_()->getItem(@$type, @$item_id);
            if (@$notificationType) {
                $subject = $item;
                $owner = $subject->getOwner();
                if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && @$notificationType) {
                    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
                    $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                    if (!$result) {
                        $action = $activityTable->addActivity($viewer, $subject, $notificationType);
                        if ($action)
                            $activityTable->attachActivity($action, $subject);
                    }
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_sesbusiness_business_businessfollowed', array('business_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
            }
            //End Activity Feed Work
            //            $this->view->favourite_id = 1;
            $temp['data']['favourite_count'] = $item->favourite_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));

        }
    }
    public function viewAction()
    {
        $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('business_id', null);
        $business_id = Engine_Api::_()->getDbtable('businesses', 'sesbusiness')->getBusinessId($id);
        if (!Engine_Api::_()->core()->hasSubject()) {
            $business = Engine_Api::_()->getItem('businesses', $business_id);
        } else {
            $business = Engine_Api::_()->core()->getSubject();
        }
		if(!$business)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
		 $viewer = Engine_Api::_()->user()->getViewer();
		 $viewerId = $viewer->getIdentity();
		 $viewer = ( $viewer && $viewer->getIdentity() ? $viewer : null );

	if (!$this->_helper->requireAuth()->setAuthParams($business, $viewer, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $sesprofilelock_enable_module = (array)Engine_Api::_()->getApi('settings', 'core')->getSetting('sesprofilelock.enable.modules');
        if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesprofilelock')) && in_array('sesbusiness', $sesprofilelock_enable_module) && $viewerId != $business->owner_id) {
            $cookieData = '';
            if ($business->enable_lock && !in_array($business->business_id, explode(',', $cookieData))) {
                $locked = true;
            } else {
                $locked = false;
            }
            $password = $business->business_password;
        } else {

            $password = true;
        }
        $businessTable = Engine_Api::_()->getDbtable('businesses', 'sesbusiness');
        $owner = $business->getOwner();
        if($viewerId && !$business->isOwner($viewer) ) {
            $businessTable->update(array(
                'view_count' => new Zend_Db_Expr('view_count + 1'),
            ), array(
                'business_id = ?' => $business->getIdentity(),
            ));
        }

        $result = $this->getbusiness($business);
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function getbusiness($business)
    {
        $businessdata = $business->toArray();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        // Get category
        if (!empty($business->category_id)) {
            $category = Engine_Api::_()->getDbtable('categories', 'sesbusiness')->find($business->category_id)->current();
        }
        $businessTags = $business->tags()->getTagMaps();
		    $likeFollowIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.integration', 0);
        $canComment = $business->authorization()->isAllowed($viewer, 'comment');
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.share', 1);
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_follow', 0);
        $canJoin = Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'bs_can_join');
        $isBusinessEdit = Engine_Api::_()->sesbusiness()->businessPrivacy($business, 'edit');
        $canUploadCover = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'upload_cover');
        $canUploadPhoto = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'upload_mainphoto');

        $isBusinessDelete = Engine_Api::_()->sesbusiness()->businessPrivacy($business, 'delete');
        $likeStatus = Engine_Api::_()->sesbusiness()->getLikeStatus($business->business_id, $business->getType());
        $followStatus = Engine_Api::_()->getDbTable('followers', 'sesbusiness')->isFollow(array('resource_id' => $business->business_id, 'resource_type' => $business->getType()));
        $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sesbusiness')->isFavourite(array('resource_id' => $business->business_id, 'resource_type' => $business->getType()));


        $owner = $business->getOwner();
        $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_show_userdetail', 0);
        if(!$hideIdentity)
            $businessdata['owner_title'] = $business->getOwner()->getTitle();
        $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
        $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
        $businessdata['currency'] = $curArr[$currency];
        $businessdata['likeFollowIntegrate'] = $likeFollowIntegrate?true:false;
        if ($likeStatus && $viewer_id) {
            $businessdata['is_content_like'] = true;
        } else {
            $businessdata['is_content_like'] = false;
        }
		if($canFollow){
            $businessdata['is_content_follow'] = $followStatus >0?true:false;
		}
		if($canFavourite){
            $businessdata['is_content_follow'] = $favouriteStatus >0?true:false;
		}
        if ($business->category_id) {
            $category = Engine_Api::_()->getItem('sesbusiness_category', $business->category_id);
            if ($category) {
                $businessdata['category_title'] = $category->category_name;

                if ($business->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('sesbusiness_category', $business->subcat_id);
                    if ($subcat) {
                        $businessdata['subcategory_title'] = $subcat->category_name;
                        if ($business->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('sesbusiness_category', $business->subsubcat_id);
                            if ($subsubcat) {
                                $businessdata['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }

        $item = Engine_Api::_()->getItem('businesses', $business->business_id);
        $joinedMembers = Engine_Api::_()->sesbusiness()->getallJoinedMembers($item);
        $memberCount = count($joinedMembers);
        $businessdata['memberCount'] = $memberCount;

        $tags = array();
        foreach ($business->tags()->getTagMaps() as $tagmap) {
            $tags[] = array_merge($tagmap->toArray(),
                array(
                    'id' => $tagmap->getIdentity(),
                    'text' => $tagmap->getTitle(),
                    'href' => $tagmap->getHref(),
                    'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                ));
        }
        if (count($tags)) {
            $businessdata['tag'] = $tags;

        }
        $businessdata['images']['main'] = $this->getBaseUrl(true, $business->getPhotoUrl());
        $businessdata['cover_image']['main'] = $this->getBaseUrl(true, $business->getCoverPhotoUrl());
        $businessdata['cover_images']['main'] = $businessdata['cover_image']['main'];
        $showLoginformFalse = false;
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.contact.details', 1) && $viewerId == 0) {
            $showLoginformFalse = true;
        }
        $l = 0;
        if ($business->business_contact_email || $business->business_contact_phone || $business->business_contact_website) {
            if ($business->business_contact_email) {

                $businessdata['buttons'][$l]['name'] = 'mail';
                $businessdata['buttons'][$l]['label'] = 'Send Email';
                $businessdata['buttons'][$l]['value'] = $business->business_contact_email;
                $l++;

            }
            if ($business->business_contact_phone) {
                $businessdata['buttons'][$l]['name'] = 'phone';
                $businessdata['buttons'][$l]['label'] = 'Call';
                $businessdata['buttons'][$l]['value'] = $business->business_contact_phone;
                $l++;
            }
            if ($business->business_contact_website) {

                $businessdata['buttons'][$l]['name'] = 'website';
                $businessdata['buttons'][$l]['label'] = 'Visit Website';
                $businessdata['buttons'][$l]['value'] = $business->business_contact_website;
                $l++;
            }

            $businessdata['showLoginForm'] = $showLoginformFalse;


        }

        $canCall = Engine_Api::_()->getDbTable('callactions', 'sesbusiness')->getCallactions(array('business_id' => $business->getIdentity()));
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
        $businessdata['is_feed_allowed'] = true;
        if( !$business->authorization()->isAllowed($this->view->viewer(), 'view') )
            $businessdata['is_feed_allowed'] = false;
            

        $i = 0;
        if ($business->business_contact_email || $business->business_contact_phone || $business->business_contact_website) {
            if ($business->business_contact_email) {

                $result['about'][$i]['name'] = 'mail';
                $result['about'][$i]['label'] = 'Send Email';
                $result['about'][$i]['value'] = $business->business_contact_email;
                $i++;

            }
            if ($business->business_contact_phone) {
                $result['about'][$i]['name'] = 'phone';
                $result['about'][$i]['label'] = 'View Phone number';
                $result['about'][$i]['value'] = $business->business_contact_phone;
                $i++;
            }
            if ($business->business_contact_website) {

                $result['about'][$i]['name'] = 'website';
                $result['about'][$i]['label'] = 'Visit Website';
                $result['about'][$i]['value'] = $business->business_contact_website;
                $i++;
            }
            if ($business->creation_date) {
                $result['about'][$i]['name'] = 'createDate';
                $result['about'][$i]['label'] = 'Create Date';
                $result['about'][$i]['value'] = $business->creation_date;
                $i++;
            }
            if ($business->category_id) {
                $category = Engine_Api::_()->getItem('sesbusiness_category', $business->category_id);
                if ($category) {
                    $result['about'][$i]['name'] = 'category';
                    $result['about'][$i]['label'] = 'Category Title';
                    $result['about'][$i]['value'] = $category->category_name;
                    //                     $businessdata['about'][$i]['id'] = $business->category_id;
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

        $relatedParams['category_id'] = $business->category_id;
        $businessid = array();
        $businessid[] = $business->business_id;
        $relatedParams['notBusinessId'] = $businessid;

        if ($businesses = $this->relatedbusinesses($relatedParams)) {
            $result['relatedbusinesses'] = $businesses;
        }
        $result['photo'] = $this->photo($business->business_id);


        if ($business->is_approved) {
            if ($shareType) {

                $businessdata["share"]["imageUrl"] = $this->getBaseUrl(false, $business->getPhotoUrl());
								$businessdata["share"]["url"] = $this->getBaseUrl(false,$business->getHref());
                $businessdata["share"]["title"] = $business->getTitle();
                $businessdata["share"]["description"] = strip_tags($business->getDescription());
                $businessdata["share"]["setting"] = $shareType;
                $businessdata["share"]['urlParams'] = array(
                    "type" => $business->getType(),
                    "id" => $business->getIdentity()
                );
            }
        }
        $m = 0;
        if ($business->is_approved) {
            if($viewerId != $business->owner_id) {
                $businessdata['buttons'][$m]['name'] = 'contact';
                $businessdata['buttons'][$m]['label'] = 'Contact';
                $m++;
            }
            if ($shareType) {
                $businessdata['buttons'][$m]['name'] = 'share';
                $businessdata['buttons'][$m]['label'] = 'Share';
                $m++;
            }
            if ($canJoin) {
                $joincounter = 0;
               // if ($viewerId) {
                    //                    $m++;
                    $row = $business->membership()->getRow($viewer);
                    if (null === $row) {
                        if ($business->membership()->isResourceApprovalRequired()) {
                            $businessdata['join'][$joincounter]['name'] = 'request';
                            $businessdata['join'][$joincounter]['label'] = 'Request Membership';
                            $joincounter++;

                        } else {
                            $businessdata['join'][$joincounter]['name'] = 'join';
                            $businessdata['join'][$joincounter]['label'] = 'Join Business';
                            $joincounter++;
                        }
                    } else if ($row->active) {
                        if (!$business->isOwner($viewer)) {
                            $businessdata['join'][$joincounter]['label'] = 'Leave Business';
                            $businessdata['join'][$joincounter]['name'] = 'leave';
                            $joincounter++;
                        }
                    } else if (!$row->resource_approved && $row->user_approved) {
                        $businessdata['join'][$joincounter]['label'] = 'Cancel Membership Request';
                        $businessdata['join'][$joincounter]['name'] = 'cancel';
                        $joincounter++;

                    } else if (!$row->user_approved && $row->resource_approved) {
                        $businessdata['join'][$joincounter]['label'] = 'Accept Membership Request';
                        $businessdata['join'][$joincounter]['name'] = 'accept';
                        $joincounter++;
                        $businessdata['join'][$joincounter]['label'] = 'Ignore Membership Request';
                        $businessdata['join'][$joincounter]['name'] = 'reject';
                    }
              //  }
            }
        }


        if ($viewer->getIdentity() != 0) {
            $businessdata['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($business);
            $businessdata['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($business);
            if ($canFavourite) {
                $businessdata['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($business, 'favourites', 'sesbusiness', 'businesses', 'owner_id');
                $businessdata['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($business, 'favourites', 'sesbusiness', 'businesses', 'owner_id');
            }
            if ($canFollow) {
                $businessdata['is_content_follow'] = $this->contentFollow($business, 'followers', 'sesbusiness', 'businesses', 'owner_id');
                $businessdata['content_follow_count'] = (int)$this->getContentFollowCount($business, 'favourites', 'sesbusiness', 'businesses', 'owner_id');
            }
        }

        if ($business->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.location', 1)) {
            unset($business['location']);
            $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('businesses', $business->getIdentity());
            if ($location) {
                $businessdata['location'] = $location->toArray();
                if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.map.integration', 1)) {
                    $businessdata['location']['showMap'] = true;
                } else {
                    $businessdata['location']['showMap'] = false;
                }
            }
        }


        if ($isBusinessDelete) {
            $businessdata['businessDelete'] = true;
        } else {
            $businessdata['businessDelete'] = false;
        }


        if ($isBusinessEdit) {
            // cover photo upload
            if ($canUploadCover) {
                $i = 0;
                if (isset($business->cover) && $business->cover != 0 && $business->cover != '') {
                    $businessdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Change Cover Photo');
                    $businessdata['updateCoverPhoto'][$i]['name'] = 'upload';
                    $i++;
                    $businessdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Remove Cover Photo');
                    $businessdata['updateCoverPhoto'][$i]['name'] = 'removePhoto';
                    $i++;
                    $businessdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('View Cover Photo');
                    $businessdata['updateCoverPhoto'][$i]['name'] = 'view';
                    $i++;
                } else {
                    $businessdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Upload Cover Photo');
                    $businessdata['updateCoverPhoto'][$i]['name'] = 'upload';
                    $i++;
                }
                //$businessdata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Choose From Albums');
                //$businessdata['updateCoverPhoto'][$i]['name'] = 'album';
            }

            // photo upload

          if($canUploadPhoto){
            $j = 0;
            if (!empty($business->photo_id)) {
                $businessdata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Change Photo');
                $businessdata['updateProfilePhoto'][$j]['name'] = 'upload';
                $j++;
                $businessdata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Remove Photo');
                $businessdata['updateProfilePhoto'][$j]['name'] = 'removePhoto';
                
            } else {
                $businessdata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Upload Photo');
                $businessdata['updateProfilePhoto'][$j]['name'] = 'upload';
                $j++;

            }                
          }
        }

        //navigation
        $result['options'] = $this->getNavigation($business,$viewer);
        $tabcounter = 0;
			  $result['menus'][$tabcounter]['name'] = 'posts';
        $result['menus'][$tabcounter]['label'] = 'Posts';
        $tabcounter++;
        $result['menus'][$tabcounter]['name'] = 'info';
        $result['menus'][$tabcounter]['label'] = 'Info';
        $tabcounter++;
			  $result['menus'][$tabcounter]['name'] = 'album';
        $result['menus'][$tabcounter]['label'] = 'Albums';
        $tabcounter++;
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.location', 1)) {
            $result['menus'][$tabcounter]['name'] = 'map';
            $result['menus'][$tabcounter]['label'] = 'Locations';
            $tabcounter++;
        }
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesbusinessvideo')) {
           //custom change video Tab disable
          $result['menus'][$tabcounter]['name'] = 'video';
          $result['menus'][$tabcounter]['label'] = 'Videos';
          $tabcounter++;
        }
        if(Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'auth_subbusiness') ) {
            $result['menus'][$tabcounter]['name'] = 'associatebusinesses';
            $result['menus'][$tabcounter]['label'] = 'Associated businesses';
            $tabcounter++;
        }
        $business_allow_announcement = Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_allow_announcement');
        $business_service = Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_service');
        $business_overview = Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'bs_overview');
        //if ($business_allow_announcement) {
            $result['menus'][$tabcounter]['name'] = 'announcements';
            $result['menus'][$tabcounter]['label'] = 'Announcements';
            $tabcounter++;
        //}
        $result['menus'][$tabcounter]['name'] = 'members';
        $result['menus'][$tabcounter]['label'] = 'Members';
        $tabcounter++; 
        if ($business_service && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.service', 0)) {
            $result['menus'][$tabcounter]['name'] = 'services';
            $result['menus'][$tabcounter]['label'] = 'Services';
            $tabcounter++;
        }
        if ($business_overview) {
            $result['menus'][$tabcounter]['name'] = 'overview';
            $result['menus'][$tabcounter]['label'] = 'Overview';
			 $tabcounter++;
        }
		if($viewer->getIdentity() > 0 && !$business->isOwner($viewer) && Engine_Api::_()->authorization()->getPermission($viewer, 'businesses', 'auth_claim')){
			$result['menus'][$tabcounter]['name'] = 'claim';
            $result['menus'][$tabcounter]['label'] = 'Claim businesses';
			 $tabcounter++;
		}
		if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesbusinesspoll') && (_SESAPI_VERSION_ANDROID >= 2.6 || _SESAPI_VERSION_IOS >= 1.7)){
            $result['menus'][$tabcounter]['name'] = 'poll';
            $result['menus'][$tabcounter]['label'] = 'Polls';
        }
        $result['business'] = $businessdata;
        $result = $result;
        return $result;
    }
	public function claimAction(){

		$viewer = Engine_Api::_()->user()->getViewer();
		if( !$viewer || !$viewer->getIdentity() ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if( !$this->_helper->requireUser()->isValid() ){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if(!Engine_Api::_()->authorization()->getPermission($viewer, 'businesses', 'auth_claim')){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		$business_id = $this->_getParam('business_id',0);
        $business = null;
		if($business_id){
            $business = Engine_Api::_()->getItem('businesses', $business_id);
		}

        $business_title = $business->getTitle();
		if($business_title)
			$_POST['title'] = $business_title;
		$form = new Sesbusiness_Form_Claim();
		if(isset($_POST))
		$form->populate($_POST);
	 // check for claim already exist or not
        $businessClaimTable = Engine_Api::_()->getDbtable('claims', 'sesbusiness');
        $businessClaimTableName = $businessClaimTable->info('name');
		$selectClaimTable = $businessClaimTable->select()
		  ->from($businessClaimTableName, 'business_id')
		  ->where('user_id =?', $viewer->getIdentity());
		  $selectClaimTable->where('business_id =?', $business_id);
		$claimedbusinesses = $businessClaimTable->fetchAll($selectClaimTable);
		if(count($claimedbusinesses->toArray()) >0){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
		}
		if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
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
		$table = Engine_Api::_()->getDbtable('claims', 'sesbusiness');
		$db = $table->getAdapter();
		$db->beginTransaction();
		try {
			// Create Claim
			$viewer = Engine_Api::_()->user()->getViewer();
			$sesbusinessClaim = $table->createRow();
			$sesbusinessClaim->user_id = $viewer->getIdentity();
			$sesbusinessClaim->business_id = $values['business_id'];
			$sesbusinessClaim->title = $values['title'];
			$sesbusinessClaim->user_email = $values['user_email'];
			$sesbusinessClaim->user_name = $values['user_name'];
			$sesbusinessClaim->contact_number = $values['contact_number'];
			$sesbusinessClaim->description = $values['description'];
			$sesbusinessClaim->save();
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
        $businessItem = Engine_Api::_()->getItem('businesses', $values['business_id']);
        $businessOwnerId = $businessItem->owner_id;
		$owner = $businessItem->getOwner();
        $businessOwnerEmail = Engine_Api::_()->getItem('user', $businessOwnerId)->email;
		$fromAddress = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.from', 'admin@' . $_SERVER['HTTP_HOST']);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($businessOwnerEmail, 'sesbusiness_business_owner_claim', $mail_settings);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($fromAddress, 'sesbusiness_site_owner_for_claim', $mail_settings);
		//Send notification to business owner
		Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $businessItem, 'sesuser_claim_business');
		//Send notification to all superadmins
		$getAllSuperadmins = Engine_Api::_()->user()->getSuperAdmins();
		foreach($getAllSuperadmins as $getAllSuperadmin) {
		  Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($getAllSuperadmin, $viewer, $businessItem, 'sesuser_claimadmin_business');
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
	}
    function getNavigation($business,$viewer){
    $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbusiness_profile');
    $navigationCounter = 0;
	$viewerId = $viewer->getIdentity();
	$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
	$canJoin = Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'bs_can_join');
	foreach ($navigation as $link) {
  $class = end(explode(' ', $link->class));

        $label = $this->view->translate($link->getLabel());
   if ($class != "sesbusiness_profile_addtoshortcut") {
            $action = '';
            if ($class == 'sesbusiness_profile_dashboard') {
                $label = $label;
                $action = 'dashboard';
                $baseurl = $this->getBaseUrl();
                $custumurl = $business->custom_url;
                $pluralurl = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_businesses_manifest', null);
                if ($pluralurl) {
                    $url = $baseurl . $pluralurl . '/dashboard/edit/' . $custumurl;
                } else {
                    $url = $baseurl . 'dashboard/edit/' . $custumurl;
                }
                $value = $url;
            } elseif ($class == 'sesbusiness_profile_member') {
      $row = $business->membership()->getRow($viewer);
      if (null === $row) {
			if ($business->membership()->isResourceApprovalRequired()) {
					$action = 'request';
				} else {
					$action = 'join';
				}
			} else if ($row->active) {
				if (!$business->isOwner($viewer)) {
					$action = 'leave';
				}
			}
            } elseif ($class == 'sesbusiness_profile_invite') {
                $action = 'invite';
            } elseif ($class == 'sesbusiness_profile_report') {
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.report', 1))
            continue;
                $action = 'report';
            } elseif ($class  == 'sesbusiness_profile_share') {
                $action = 'share';
            } elseif ($class  == 'sesbusiness_profile_member') {
                $action = 'join';
            } elseif ($class == 'sesbusiness_profile_delete') {
                $action = 'delete';
            } elseif ($class == 'sesbusiness_profile_subbusiness') {
                $action = 'createAssociateBusiness';
            } elseif ($class == 'sesbusiness_profile_like') {
                $action = 'likeasyourbusiness';
            } elseif ($class == 'sesbusiness_profile_unlike') {
                $action = 'unlikeasyourbusiness';
            }
            if ($class == 'sesbusiness_profile_dashboard') {
                $result[$navigationCounter]['label'] = $label;
                $result[$navigationCounter]['name'] = $action;
                $result[$navigationCounter]['value'] = $value;
                $navigationCounter++;
				if($this->_helper->requireAuth()->setAuthParams('businesses', null, 'edit')->isValid()){
					 $result[$navigationCounter]['label'] = $this->view->translate('Edit Business');
                $result[$navigationCounter]['name'] = 'edit';
                $navigationCounter++;
				}
            }elseif($class == 'sesbusiness_profile_delete'){
				if(!$this->_helper->requireAuth()->setAuthParams('businesses', null, 'delete')->isValid())
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
  
  /*if ($canJoin) {
		$joincounter = 0;
		if ($viewerId) {
		$row = $business->membership()->getRow($viewer);
		if (null === $row) {
			if ($business->membership()->isResourceApprovalRequired()) {
				$result[$navigationCounter]['name'] = 'request';
				$result[$navigationCounter]['label'] = 'Request Membership';
				$joincounter++;

			} else {
				$result[$navigationCounter]['name'] = 'join';
				$result[$navigationCounter]['label'] = 'Join Business';
				$joincounter++;
			}
		} else if ($row->active) {
			if (!$business->isOwner($viewer)) {
				$result[$navigationCounter]['label'] = 'Leave Business';
				$result[$navigationCounter]['name'] = 'leave';
				$joincounter++;
			}
		} else if (!$row->resource_approved && $row->user_approved) {
			$result[$navigationCounter]['label'] = 'Cancel Membership Request';
			$result[$navigationCounter]['name'] = 'cancel';
			$joincounter++;

		} else if (!$row->user_approved && $row->resource_approved) {
			$result[$navigationCounter]['label'] = 'Accept Membership Request';
			$result[$navigationCounter]['name'] = 'accept';
			$joincounter++;
			$result[$navigationCounter]['label'] = 'Ignore Membership Request';
			$result[$navigationCounter]['name'] = 'reject';
		}
	  }
	}
	*/
      
    return $result;
}
    function getType($type)
    {
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
    public function relatedbusinesses($params)
    {
        $paginator = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')
            ->getBusinessPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result = $this->getbusinesses($paginator);
        return $result;
    }
    public function photo($businessid)
    {
        $params['business_id'] = $businessid;
        $paginator = Engine_Api::_()->getDbTable('photos', 'sesbusiness')
            ->getPhotoPaginator($params);
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber(1);
        $i = 0;
        foreach ($paginator as $photos) {
            $images = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");
            if (!count($images)) {
                $images['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';
                $images['normal'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';

            }
            $result[$i]['images'] = $images;
            $result[$i]['photo_id'] = $photos->getIdentity();
            $result[$i]['album_id'] = $photos->album_id;
            $i++;

        }
        return $result;

    }
    public function infoAction()
    {
        $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('business_id', null);
        if (!$id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        $business_id = Engine_Api::_()->getDbtable('businesses', 'sesbusiness')->getBusinessId($id);

        if (!Engine_Api::_()->core()->hasSubject()) {
            $business = Engine_Api::_()->getItem('businesses', $business_id);
        } else {
            $business = Engine_Api::_()->core()->getSubject();
        }

        $result['information'] = $this->getInformation($business);


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));


    }
	public function moreMembersAction(){
		$id = $this->_getParam('business_id',null);
		if(!$id){
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
        $business_id = $id;
        if (!Engine_Api::_()->core()->hasSubject()) {
            $business = Engine_Api::_()->getItem('businesses', $business_id);
        } else {
            $business = Engine_Api::_()->core()->getSubject();
        }
        $businesscheck = false;
		if($this->_getParam('type',null) == 'like'){
			$coreLikeTable = Engine_Api::_()->getDbTable('likes', 'core');
			$select = $coreLikeTable->select()->from($coreLikeTable->info('name'), 'poster_id')
            ->where('resource_id =?', $business->business_id )
            ->where('resource_type =?', 'businesses');
		}else if($this->_getParam('type',null) == 'follow'){
			$followTable = Engine_Api::_()->getDbTable('followers', 'sesbusiness');
			$select = $followTable->select()->from($followTable->info('name'), 'owner_id')
            ->where('resource_id =?', $business->business_id )
            ->where('resource_type =?', 'businesses');
		}else if($this->_getParam('type',null) == 'favourite'){
			$favouriteTable = Engine_Api::_()->getDbTable('favourites', 'sesbusiness');
			$select = $favouriteTable->select()->from($favouriteTable->info('name'), 'owner_id')
            ->where('resource_id =?', $business->business_id )
            ->where('resource_type =?', 'businesses');
		}else if($this->_getParam('type',null) == 'business'){
			$tableLikebusinesses = Engine_Api::_()->getDbTable('likebusinesses', 'sesbusiness');
			$select = $tableLikebusinesses->select()->where('business_id =?', $business->business_id);
            $businesscheck = true;
      	}
		
		if($select){
			$Members = Zend_Paginator::factory($select);
		}
		
	   if(count($Members) && $businesscheck) {
			$likebusinessesCounter = 0;
			foreach ($Members as $likebusiness) {
				$item = Engine_Api::_()->getItem('businesses', $likebusiness->like_business_id);
				if ($item) {
					$nameLike = $item->getTitle();;
					$image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
					if ($image) {
						$result['business_liked_by_this_business'][$likebusinessesCounter]['images'] = $image;
					}
					if ($nameLike) {
						$result['business_liked_by_this_business'][$likebusinessesCounter]['name'] = $nameLike;
					}
					$result['business_liked_by_this_business'][$likebusinessesCounter]['business_id'] = $item->business_id;
				}
				$likebusinessesCounter++;
			}
		}
	 if (count($Members) && !$businesscheck && $this->_getParam('type',null) != 'like') {
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
		if (count($Members) && !$businesscheck && $this->_getParam('type',null) == 'like') {
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
		// Set item count per business and current page number
        $Members->setItemCountPerPage($this->_getParam('limit', 10));
        $Members->setCurrentPageNumber($this->_getParam('page', 1));
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
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
      ;

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
    public function userAge($member){
    $getFieldsObjectsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($member); 
    if (!empty($getFieldsObjectsByAlias['birthdate'])) {
      $optionId = $getFieldsObjectsByAlias['birthdate']->getValue($member);
      if ($optionId && @$optionId->value) {
        $age = floor((time() - strtotime($optionId->value)) / 31556926);
        return $this->view->translate(array('%s year old', '%s years old', $age), $this->view->locale()->toNumber($age));
      }
    }
    return "";  
  }
    public function getInformation($businesses)
    {
        $result = $businesses->toArray();

        $openhourstable = Engine_Api::_()->getDbTable('openhours', 'sesbusiness');
        $select = $openhourstable->select()
            ->from($openhourstable->info('name'))
            ->where('business_id =?', $businesses->getIdentity());
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
        $owner = $businesses->getOwner();
        $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_show_userdetail', 0);
        if(!$hideIdentity){
          $result['basicInformation'][$basicInformationCounter]['name'] = 'createdby';
          $result['basicInformation'][$basicInformationCounter]['value'] = $owner->displayname;
          $result['basicInformation'][$basicInformationCounter]['label'] = 'Created By';
          $basicInformationCounter++;
        }
        $result['basicInformation'][$basicInformationCounter]['name'] = 'creationdate';
        $result['basicInformation'][$basicInformationCounter]['value'] = $businesses->creation_date;
        $result['basicInformation'][$basicInformationCounter]['label'] = 'Created on';
        $basicInformationCounter++;
        $statsCounter = 0;


        $state[$statsCounter]['name'] = 'comment';
        $state[$statsCounter]['value'] = $businesses->comment_count;
        $state[$statsCounter]['label'] = 'Comments';
        $statsCounter++;


        $state[$statsCounter]['name'] = 'like';
        $state[$statsCounter]['value'] = $businesses->like_count;
        $state[$statsCounter]['label'] = 'Likes';
        $statsCounter++;


        $state[$statsCounter]['name'] = 'view';
        $state[$statsCounter]['value'] = $businesses->view_count;
        $state[$statsCounter]['label'] = 'Views';
        $statsCounter++;


        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness_allow_follow', 0);

        if ($canFavourite) {
            $state[$statsCounter]['name'] = 'favourite';
            $state[$statsCounter]['value'] = $businesses->favourite_count;
            $state[$statsCounter]['label'] = 'Favourites';
            $statsCounter++;
        }

        if ($canFollow) {
            $state[$statsCounter]['name'] = 'follow';
            $state[$statsCounter]['value'] = $businesses->follow_count;
            $state[$statsCounter]['label'] = 'Follows';
        }


        $statsCounter++;

        $result['basicInformation'][$basicInformationCounter]['name'] = 'stats';
        $result['basicInformation'][$basicInformationCounter]['value'] = $state;
        $result['basicInformation'][$basicInformationCounter]['label'] = 'Stats';
        $basicInformationCounter++;

        if ($businesses->category_id) {
            $category = Engine_Api::_()->getItem('sesbusiness_category', $businesses->category_id);
            if ($category) {
                $result['basicInformation'][$basicInformationCounter]['name'] = 'category';
                $result['basicInformation'][$basicInformationCounter]['value'] = $category->category_name;
                $result['basicInformation'][$basicInformationCounter]['label'] = 'Category';

                $basicInformationCounter++;
                if ($businesses->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('sesbusiness_category', $businesses->subcat_id);
                    if ($subcat) {
                        $result['basicInformation'][$basicInformationCounter]['name'] = 'subcategory';
                        $result['basicInformation'][$basicInformationCounter]['value'] = $subcat->category_name;
                        $result['basicInformation'][$basicInformationCounter]['label'] = 'Sub Category';
                        $basicInformationCounter++;
                        if ($businesses->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('sesbusiness_category', $businesses->subsubcat_id);
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
        $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($businesses);
        if (count($fieldStructure)) { // @todo figure out right logic
            $content = $this->view->fieldSesapiValueLoop($businesses, $fieldStructure);;
            $counter = 0;
            foreach ($content as $key => $value) {
                $result['profileDetail'][$counter]['label'] = $key;
                $result['profileDetail'][$counter]['value'] = $value;
                $counter++;
            }
        }

        $result['Detail'] = $businesses->description;
        $contactInformationCounter = 0;
        if ($businesses->business_contact_phone){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'phone';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'View Phone Number';
          $result['contactInformation'][$contactInformationCounter]['value'] = $businesses->business_contact_phone;
          $contactInformationCounter++;
        }
        if ($businesses->business_contact_email){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'mail';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'Send Email';   
          $result['contactInformation'][$contactInformationCounter]['value'] = $$businesses->business_contact_email;
          $contactInformationCounter++;
        }
        if ($businesses->business_contact_website){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'website';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'Visit Website';
          $result['contactInformation'][$contactInformationCounter]['value'] = $businesses->business_contact_website;
          $contactInformationCounter++;
        }
        if ($businesses->business_contact_facebook){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'facebook';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'Facebook.com';
          $result['contactInformation'][$contactInformationCounter]['value'] = $businesses->business_contact_facebook;
          $contactInformationCounter++;
        }
        if ($businesses->business_contact_linkedin){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'linkedin';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'Linkedin';
          $result['contactInformation'][$contactInformationCounter]['value'] = $businesses->business_contact_linkedin;
          $contactInformationCounter++;
        }
        if ($businesses->business_contact_twitter){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'twitter';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'Twitter';
          $result['contactInformation'][$contactInformationCounter]['value'] = $businesses->business_contact_twitter;
          $contactInformationCounter++;
        }
        if ($businesses->business_contact_instagram){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'instagram';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'Instagram.com';
          $result['contactInformation'][$contactInformationCounter]['value'] = $businesses->business_contact_instagram;
          $contactInformationCounter++;
        }
        if ($businesses->business_contact_pinterest){
          $result['contactInformation'][$contactInformationCounter]['name'] = 'pinterest';
          $result['contactInformation'][$contactInformationCounter]['label'] = 'Pinterest.com';
          $result['contactInformation'][$contactInformationCounter]['value'] = $businesses->business_contact_pinterest;
        }
        $likeMembers = Engine_Api::_()->sesbusiness()->getMemberByLike($businesses->business_id);
        $favMembers = Engine_Api::_()->sesbusiness()->getMemberFavourite($businesses->business_id);
        $followMembers = Engine_Api::_()->sesbusiness()->getMemberFollow($businesses->business_id);
        $tableLikebusinesses = Engine_Api::_()->getDbTable('likebusinesses', 'sesbusiness');
        $selelct = $tableLikebusinesses->select()->where('business_id =?', $businesses->business_id);
        $likeBusinessResult = $tableLikebusinesses->fetchAll($selelct);
        if (count($likeBusinessResult)) {
            $likebusinessesCounter = 0;
            $result['total_business_liked_by_this_business'] = count($likeBusinessResult) > 4 ? count($likeBusinessResult) - 4 : 0;
            foreach ($likeBusinessResult as $likebusiness) {
              if($likebusinessesCounter > 3)
                break;
                $item = Engine_Api::_()->getItem('businesses', $likebusiness->like_business_id);
                if ($item) {
                    $nameLike = $item->getTitle();;
                    $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
                    if ($image) {
                        $result['business_liked_by_this_business'][$likebusinessesCounter]['images'] = $image;
                    }
                    if ($nameLike) {
                        $result['business_liked_by_this_business'][$likebusinessesCounter]['name'] = $nameLike;
                    }
                    $result['business_liked_by_this_business'][$likebusinessesCounter]['business_id'] = $item->business_id;
                }else{
                  $result['total_business_liked_by_this_business'] = $result['total_business_liked_by_this_business'] > 0 ? $result['total_business_liked_by_this_business'] - 1 : 0;
                }
                $likebusinessesCounter++;
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

        if (count($followMembers) && $canFollow) {
            $followCounter = 0;
            $result['total_people_who_follow_this'] = count($followMembers) > 4 ? count($followMembers) - 4 : 0;
            foreach ($followMembers as $member) {
               if($followCounter > 3)
                break;
                $item = Engine_Api::_()->getItem('user', $member['owner_id']);
                if(!$item){
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


        return $result;

    }
    public function memberAction()
    {

        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('businesses');
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }

        // Get params
        $business = $this->_getParam('page', 1);
        $search = $this->_getParam('search');
        $waiting = $this->_getParam('waiting', false);

        // Prepare data
        $business = Engine_Api::_()->core()->getSubject();

        // get viewer
        $viewer = Engine_Api::_()->user()->getViewer();

        $result = array();
        if ($viewer->getIdentity() && ($business->isOwner($viewer))) {
            $waitingMembers = Zend_Paginator::factory($business->membership()->getMembersSelect(false));
            if ($waitingMembers->getTotalItemCount() > 0 && !$waiting) {
                $result['options']["label"] = $this->view->translate('See Waiting');
                $result['options']["name"] = 'waiting';
                $result['options']["value"] = '1';
            }
        }

        // if not showing waiting members, get full members
        $select = $business->membership()->getMembersObjectSelect();

        if ($search)
            $select->where('displayname LIKE ?', '%' . $search . '%');
        $fullMembers = Zend_Paginator::factory($select);

        if ($fullMembers->getTotalItemCount() > 0 && ($viewer->getIdentity() && ($business->isOwner($viewer))) && $waiting) {
            $result['options']["label"] = $this->view->translate('View all approved members');
            $result['options']["name"] = 'waiting';
            $result['options']["value"] = '0';
        }
        
        // if showing waiting members, or no full members
        if (($viewer->getIdentity() && ($business->isOwner($viewer))) && ($waiting || ($fullMembers->getTotalItemCount() <= 0 && $search == ''))) {
            $paginator = $waitingMembers;
            $waiting = true;
        } else {
            $paginator = $fullMembers; 
            $waiting = false;
        }

        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', $business));

        $result['members'] = array();
        $counterLoop = 0;
        foreach ($paginator as $member) {
            if (!empty($member->resource_id)) {
                $memberInfo = $member;
                $member = Engine_Api::_()->getItem('user', $memberInfo->user_id);
            } else {
                $memberInfo = $business->membership()->getMemberInfo($member);
            }

            if (!$member->getIdentity())
                continue;
            $resource = $member->toArray();
            unset($resource['lastlogin_ip']);
            unset($resource['creation_ip']);
            $result['members'][$counterLoop] = $resource;
            $result['members'][$counterLoop]['owner_photo'] = Engine_Api::_()->sesapi()->getPhotoUrls($member, '', "");
//         $member->userImage($member->getIdentity(),'thumb.profile');
            if ($business->isOwner($viewer) && !$business->isOwner($member)) {
                $optionCounter = 0;
                if (!$business->isOwner($member) && $memberInfo->active == true) {
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
    public function getChildCount()
    {
        return $this->_childCount;
    }
    public function announcementAction()
    {

        $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('business_id', null);
        if (!$id) {

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing.'), 'result' => array()));
        }
        if (!Engine_Api::_()->core()->hasSubject()) {
            $business = Engine_Api::_()->getItem('businesses', $id);
        } else {
            $business = Engine_Api::_()->core()->getSubject();
        }
        $paginator = Engine_Api::_()->getDbTable('announcements', 'sesbusiness')->getBusinessAnnouncementPaginator(array('business_id' => $business->business_id));
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
        $subject = Engine_Api::_()->core()->getSubject('businesses');

        if (!$subject) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }

        $paginator = Engine_Api::_()->getDbTable('services', 'sesbusiness')->getServicePaginator(array('business_id' => $subject->getIdentity(), 'widgettype' => 'widget'));
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        //Manage Apps Check
        $isCheck = Engine_Api::_()->getDbTable('managebusinessapps', 'sesbusiness')->isCheck(array('business_id' => $subject->getIdentity(), 'columnname' => 'service'));


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

        if (!Engine_Api::_()->core()->hasSubject() || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.location', 1)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $business = Engine_Api::_()->core()->getSubject();
        $paginator = Engine_Api::_()->getDbTable('locations', 'sesbusiness')->getBusinessLocationPaginator(array('business_id' => $business->business_id));
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

            $locationPhotos = Engine_Api::_()->getDbTable('locationphotos', 'sesbusiness')->getLocationPhotos(array('business_id' => $business->business_id, 'location_id' => $location->location_id));
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
        $business = Engine_Api::_()->core()->getSubject();
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
		
        $paginator = Engine_Api::_()->getDbTable('albums', 'sesbusiness')->getAlbumSelect(array('business_id' => $business->business_id, 'order' => $orderval,'search'=>$search));
		$albumCount = Engine_Api::_()->getDbTable('albums', 'sesbusiness')->getUserBusinessAlbumCount(array('business_id' => $business->business_id, 'user_id' => $viewer->getIdentity()));
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $getBusinessRolePermission = Engine_Api::_()->sesbusiness()->getBusinessRolePermission($business->getIdentity(), 'post_content', 'album', false);
        $canUpload = $getBusinessRolePermission ? $getBusinessRolePermission : $this->_helper->requireAuth()->setAuthParams('businesses', null, 'album')->isValid();
        $optioncounter = 0;
		$quota = Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'business_album_count');
		if($albumCount >= $quota && $quota != 0){
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
            $photo = Engine_Api::_()->getItem('sesbusiness_photo',$item->photo_id);
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
    public function associatedAction()
    {
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $business = Engine_Api::_()->core()->getSubject();
        $params['parent_id'] = $business->business_id;


        $paginator = $paginator = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')
            ->getBusinessPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result['businesses'] = $this->getbusinesses($paginator);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));


    }
    public function albumviewAction()
    {

        $albumid = $this->_getParam('album_id', 0);
        $businessId = $this->_getParam('business_id', 0);
        if (!$albumid) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        if (Engine_Api::_()->core()->hasSubject()) {
            $business = Engine_Api::_()->core()->getSubject();
            $album = Engine_Api::_()->getItem('sesbusiness_album', $albumid);
        } else {
            $album = Engine_Api::_()->getItem('sesbusiness_album', $albumid);
        }
        $business = Engine_Api::_()->getItem('businesses', $album->business_id);
        if (!$business) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $photoTable = Engine_Api::_()->getItemTable('sesbusiness_photo');
        $mine = true;
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!$viewer) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        }
        $viewer_id = $viewer->getIdentity();

        $result['album'] = $album->toArray();
        $result['album']['user_title'] = $viewer->getOwner()->getTitle();
        $category = Engine_Api::_()->getItem('sesbusiness_category', $business->category_id);
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
            $canEdit = $editBusinessRolePermission = Engine_Api::_()->sesbusiness()->getBusinessRolePermission($business->getIdentity(), 'allow_plugin_content', 'edit');
            $editBusinessRolePermission = Engine_Api::_()->sesbusiness()->getBusinessRolePermission($business->getIdentity(), 'allow_plugin_content', 'edit');
            $canEditMemberLevelPermission = $editBusinessRolePermission ? $editBusinessRolePermission : $business->authorization()->isAllowed($viewer, 'edit');
            $deleteBusinessRolePermission = Engine_Api::_()->sesbusiness()->getBusinessRolePermission($business->getIdentity(), 'allow_plugin_content', 'delete');
            $canDeleteMemberLevelPermission = $deleteBusinessRolePermission ? $deleteBusinessRolePermission : $business->authorization()->isAllowed($viewer, 'delete');
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

        $canComment = $business->authorization()->isAllowed($viewer, 'comment');


        if ($canComment)
            $result['album']['is_comment'] = true;
        else
            $result['album']['is_comment'] = false;

        $result['album']['user_image'] = $userimage;

        $paginator = $photoTable->getPhotoPaginator(array('album' => $album));
        $paginator->setItemCountPerPage('limit', 10);
        $paginator->setCurrentPageNumber('page_number', 1);

        $photoCounter = 0;
        foreach ($paginator as $photo) {
            $result['photos'][$photoCounter] = $photo->toArray();
            $result['photos'][$photoCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "");
            $albumLikeStatus = Engine_Api::_()->sesbusiness()->getLikeStatusBusiness($photo->photo_id, 'sesbusiness_photo');
            $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'sesbusiness')->isFavourite(array('resource_type' => 'sesbusiness_photo', 'resource_id' => $photo->photo_id));
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


        $businessItem = $business;


        if (isset($album->art_cover) && $album->art_cover != 0 && $album->art_cover != '') {
            $albumArtCover = Engine_Api::_()->storage()->get($album->art_cover, '')->getPhotoUrl();
            $result['album']['albumArtCover'] = $this->getBaseurl(false, $albumArtCover);
            $result['album']['cover_pic'] = $this->getBaseurl(false, $albumArtCover);
        } else {
            $albumArtCover = '';
        }
        if(!$albumArtCover){
          $albumImage = Engine_Api::_()->sesbusiness()->getAlbumPhoto($album->getIdentity(), 0, 3);
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
    public function editalbumAction()
    {
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $album_id = $this->_getParam('album_id', false);

        if (!$album_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        else
            $album = Engine_Api::_()->getItem('sesbusiness_album', $album_id);

        $business = Engine_Api::_()->getItem('businesses', $album->business_id);

        if ($business) {
            Engine_Api::_()->core()->setSubject($business);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        // Make form
        $form = new Sesbusiness_Form_Album_Edit();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
        }
        $form->populate($album->toArray());
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

        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $album_id = $this->_getParam('album_id', false);
        if ($album_id)
            $album = Engine_Api::_()->getItem('sesbusiness_album', $album_id);
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));

        $business = Engine_Api::_()->getItem('businesses', $album->business_id);
        if ($business) {
            Engine_Api::_()->core()->setSubject($business);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        // In smoothbox

        $form = new Sesbusiness_Form_Album_Delete();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
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
    public function likeasbusinessAction()
    {
        $id = $this->_getParam('id');
        if (!$id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $business = Engine_Api::_()->getItem('businesses', $id);
        $business_id = $this->_getParam('business_id');
        $table = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness');
        
        $business_ids = $this->_getParam('business_ids');
        if($business_ids){
            $table = Engine_Api::_()->getDbTable('likebusinesses', 'sesbusiness');
            foreach($business_ids as $business_id){
              $row = $table->createRow();
              $row->business_id = $business_id;
              $row->like_business_id = $business->business_id;
              $row->user_id = $viewer->getIdentity();
              $row->save();
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'businesses liked succuessfully.')));
        }
        
        if ($business_id) {
            $table = Engine_Api::_()->getDbTable('likebusinesses', 'sesbusiness');
            $row = $table->createRow();
            $row->business_id = $business_id;
            $row->like_business_id = $business->business_id;
            $row->user_id = $viewer->getIdentity();
            $row->save();
            $message = $this->view->translate('%s has been liked as Your Business.', $business->getTitle());
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message)));
        }
        $selelct = $table->select()->where('user_id =?', $viewer->getIdentity())->where('memberrole_id =?', 1)->where('business_id !=?', $business->getIdentity())
            ->where('business_id NOT IN (SELECT business_id FROM engine4_sesbusiness_likebusinesses WHERE like_business_id = ' . $business->business_id . ")");
        $mybusinesses = ($table->fetchAll($selelct));
        if (count($mybusinesses)) {
            $result = array();
            $result['title'] = 'Like ' . $business->getTitle() . ' as Your Business';
            $result['description'] = "Likes will show up on your Business's timeline. Which Business do you want to like " . $business->getTitle() . " as?";
            $result['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($business, '', "");
            $counter = 0;
            foreach ($mybusinesses as $mybusiness) {
                $business = Engine_Api::_()->getItem('businesses', $mybusiness->business_id);
                if (!$business)
                    continue;
                $businessdata[$counter]['business_id'] = $business->business_id;
                $businessdata[$counter]['business_title'] = $business->getTitle();
                $counter++;
            }
            $result['business'] = $businessdata;
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

    }
    public function unlikeasbusinessAction()
    {
        $id = $this->_getParam('id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $business = Engine_Api::_()->getItem('businesses', $id);
        $business_id = $this->_getParam('business_id');
        $table = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness');
        
        $business_ids = $this->_getParam('business_ids');
        if($business_ids){
            $table = Engine_Api::_()->getDbTable('likebusinesses', 'sesbusiness');
            foreach($business_ids as $business_id){
              $select = $table->select()->where('business_id =?', $business_id)->where('like_business_id =?', $business->getIdentity());
              $row = $table->fetchRow($select);
              if ($row)
                  $row->delete();
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'businesses succuessfully removed.')));
        }
        if ($business_id) {
            $table = Engine_Api::_()->getDbTable('likebusinesses', 'sesbusiness');
            $select = $table->select()->where('business_id =?', $business_id)->where('like_business_id =?', $business->getIdentity());
            $row = $table->fetchRow($select);
            if ($row)
                $row->delete();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => 'succuessfully removed.')));
        }
        $selelct = $table->select()->where('user_id =?', $viewer->getIdentity())->where('memberrole_id =?', 1)->where('business_id !=?', $business->getIdentity())
            ->where('business_id IN (SELECT business_id FROM engine4_sesbusiness_likebusinesses WHERE like_business_id = ' . $business->business_id . ")");

        $mybusinesses = ($table->fetchAll($selelct));
        if (count($mybusinesses)) {
            $result = array();
            $result['title'] = "Remove " . $business->getTitle() . " from my Business's favorites";
            $result['description'] = "For which business would you like to remove  " . $business->getTitle() . " from favorites?";
            $result['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($business, '', "");
            $counter = 0;
            foreach ($mybusinesses as $mybusiness) {
                $business = Engine_Api::_()->getItem('businesses', $mybusiness->business_id);
                if (!$business)
                    continue;
                $businessdata[$counter]['business_id'] = $business->business_id;
                $businessdata[$counter]['business_title'] = $business->getTitle();

                $counter++;
            }
            $result['business'] = $businessdata;

        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

    }
    public function leaveAction()
    {
        // Check auth
        if (!$this->_helper->requireUser()->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        if (!$this->_helper->requireSubject()->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $subject = Engine_Api::_()->core()->getSubject();
        $viewerId = $viewer->getIdentity();


        if ($subject->isOwner($viewer)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'businesses', 'bs_can_join') : 0;


        if (1) {
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $subject->membership()->removeMember($viewer);
                $db->commit();
                $result['message'] = $this->view->translate('You have successfully left this business.');
               $result['menus'] = $this->getButtonMenus($subject);
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
        }
    }
    public function inviteAction()
    {
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject('businesses')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

        // @todo auth
        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();
        $business = Engine_Api::_()->core()->getSubject();

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
        $form = new Sesbusiness_Form_Invite();

        $count = 0;
        foreach ($friends as $friend) {
            if ($business->membership()->isMember($friend, null)) {
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
                $form->getElement('all')->setName('sesbusiness_choose_all');

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
//        if (!$form->isValid($this->getRequest()->getPost())) {
//            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('validation_fail'), 'result' => array()));
//        }
        // Process
        $table = $business->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $usersIds = $form->getValue('users');

            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            foreach ($friends as $friend) {
                if (!in_array($friend->getIdentity(), $usersIds)) {
                    continue;
                }

                $business->membership()->addMember($friend)->setResourceApproved($friend);
                $notifyApi->addNotification($friend, $viewer, $business, 'sesbusiness_invite');
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
            $formFields[0]['label'] = "Business Overview";
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
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
        }
        $subject->overview = $_POST['overview'];
        $subject->save();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Business overview saved successfully.'))));
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
    public function deleteAction()
    {

        $businessid = $this->getParam('business_id');
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

        $sesbusiness = Engine_Api::_()->getItem('businesses', $this->getRequest()->getParam('business_id'));
        if (!$sesbusiness)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This business does not exist.'), 'result' => array()));


        if (!Engine_Api::_()->getDbTable('businessroles', 'sesbusiness')->toCheckUserBusinessRole($this->view->viewer()->getIdentity(), $sesbusiness->getIdentity(), 'manage_dashboard', 'delete'))
            if (!$this->_helper->requireAuth()->setAuthParams($sesbusiness, null, 'delete')->isValid())
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

        // In smoothbox
//    $this->_helper->layout->setLayout('default-simple');

        $form = new Sesbusiness_Form_Delete();


        if (!$sesbusiness) {
            $status['status'] = false;
            $error = Zend_Registry::get('Zend_Translate')->_("Business entry doesn't exist or not authorized to delete");
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
        }

        if (!$this->getRequest()->isPost()) {
            $status['status'] = false;
            $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
        }
        $db = $sesbusiness->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $sesbusiness->delete();
            $db->commit();
            $status['status'] = true;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this business.'), $status)));

        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function addmorephotosAction()
    {
        $album_id = $this->_getParam('album_id', false);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('sesbusiness_album', $album_id);
            $business_id = $album->business_id;
        } else {

        }

        $form = new Sesbusiness_Form_Album();
        $business = Engine_Api::_()->getItem('businesses', $business_id);

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();

        $photoTable = Engine_Api::_()->getDbTable('photos', 'sesbusiness');
        $uploadSource = $_FILES['attachmentImage'];


        $photoArray = array(
            'business_id' => $business->business_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'sesbusiness')->getAdapter();
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
        $_POST['business_id'] = $business_id;
        $_POST['file'] = implode(' ', $uploadSource);
        $form->album->setValue($album_id);
        $album = $form->saveValues();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('album_id' => $album->album_id, 'message' => $this->view->translate('Photo added successfully.'))));
    }
    public function uploadphotoAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
       if($viewer->getIdentity() == 0){
           Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
       }
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $business = Engine_Api::_()->core()->getSubject();
        if (!$business)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This business does not exist.'), 'result' => array()));
        $photo = $business->photo_id;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        else if(isset($_FILES['image']))
          $data = $_FILES['image'];
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $business->setPhoto($data, '', 'profile');


        $getPhotoId = Engine_Api::_()->getDbTable('photos', 'sesbusiness')->getPhotoId($business->photo_id);
        $photo = Engine_Api::_()->getItem('sesbusiness_photo', $getPhotoId);

        $businesslink = '<a href="' . $business->getHref() . '">' . $business->getTitle() . '</a>';
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $photo, 'sesbusiness_business_pfphoto', null, array('businessname' => $businesslink));
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
            	$detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
			  if($detail_id) {
				$detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
				$detailAction->sesresource_id = $business->getIdentity();
				$detailAction->sesresource_type = $business->getType();
				$detailAction->save();
			  }
        }


        if ($action)
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);

        $file = array('main' => $business->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully photo uploaded.'), 'images' => $file)));
    }
    protected function setPhoto($photo, $id) {
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
        $params = array(
            'parent_type' => 'video',
            'parent_id' => $id,
            'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
            'name' => $fileName,
        );
        // Save
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
        $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_main.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(500, 500)
                ->write($mainPath)
                ->destroy();
        // Store
        try {
          $iMain = $filesTable->createFile($mainPath, $params);
        } catch (Exception $e) {
          // Remove temp files
          @unlink($mainPath);
          // Throw
          if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
            throw new sesbusinessvideo_Model_Exception($e->getMessage(), $e->getCode());
          } else {
            throw $e;
          }
        }
        // Remove temp files
        @unlink($mainPath);
        // Update row
        // Delete the old file?
        if (!empty($tmpRow)) {
          $tmpRow->delete();
        }
        return $iMain->file_id;
    }
    public function removephotoAction()
    {

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        } else {
            $business = Engine_Api::_()->core()->getSubject();

        }
        if (!$business)
            $business = Engine_Api::_()->getItem('businesses', $this->_getparam('business_id', null));

        if (!$business)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        if (isset($business->photo_id) && $business->photo_id > 0) {
            $business->photo_id = 0;
            $business->save();
        }
        $file = array('main' => $business->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully photo deleted.'), 'images' => $file));

//    echo json_encode(array('file' => $business->getPhotoUrl()));
//    die;
    }
    public function uploadcoverAction()
    {
        if (!Engine_Api::_()->core()->hasSubject()) {
            $business = Engine_Api::_()->getItem('businesses', $this->_getparam('business_id', null));
		}else{
            $business = Engine_Api::_()->core()->getSubject();
		}
        if (!$business)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $cover_photo = $business->cover;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        else if(isset($_FILES['image']))
          $data = $_FILES['image'];
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $business->setCoverPhoto($data);

        $viewer = Engine_Api::_()->user()->getViewer();
        $getPhotoId = Engine_Api::_()->getDbTable('photos', 'sesbusiness')->getPhotoId($business->cover);
        $photo = Engine_Api::_()->getItem('sesbusiness_photo', $getPhotoId);
        $businesslink = '<a href="' . $business->getHref() . '">' . $business->getTitle() . '</a>';
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $photo, 'sesbusiness_business_coverphoto', null, array('businessname' => $businesslink));
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
			$detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
      if($detail_id) {
        $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
        $detailAction->sesresource_id = $business->getIdentity();
        $detailAction->sesresource_type = $business->getType();
        $detailAction->save();
      }
        }
        if ($action)
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);
        if ($cover_photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $cover_photo);
            $im->delete();
        }
        $file['main'] = $business->getCoverPhotoUrl();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully cover photo uploaded.'), 'images' => $file)));
    }
    public function removecoverAction()
    {

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $business = Engine_Api::_()->core()->getSubject();
        if (!$business)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        if (isset($business->cover) && $business->cover > 0) {
            $im = Engine_Api::_()->getItem('storage_file', $business->cover);
            $business->cover = 0;
            $business->save();
            $im->delete();
        }

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully deleted cover photo.'))));

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
    //edit photo details from lightbox
    public function editDescriptionAction() {
    $status = true;
    $error = false;

    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid()) {
      $status = false;
      $error = true;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('sesbusiness_photo');
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
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array('')));
      }
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array($this->view->translate('Photo edited successfully.'))));
  }
    public function lightboxAction()
    {
        $photo = Engine_Api::_()->getItem('sesbusiness_photo', $this->_getParam('photo_id'));
        $business_id = $this->_getparam('business_id', $photo->business_id);

        if ($photo && !$this->_getParam('album_id', null)) {
            $album_id = $photo->album_id;
        } else {
            $album_id = $this->_getParam('album_id', null);
        }
        $business = Engine_Api::_()->getItem('businesses', $business_id);

        if ($album_id && null !== ($album = Engine_Api::_()->getItem('sesbusiness_album', $album_id))) {
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid Request'), 'result' => array()));
        }

        $photo_id = $photo->getIdentity();

//        if (!$this->_helper->requireSubject('sesbusiness_photo')->isValid())
//          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        if (!$this->_helper->requireAuth()->setAuthParams('businesses', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));

        $viewer = Engine_Api::_()->user()->getViewer();

        $albumData = array();
        if ($viewer->getIdentity() > 0) {

            $menu = array();
            $counterMenu = 0;
            $menu[$counterMenu]["name"] = "save";
            $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");
            $counterMenu++;

            $canEdit = Engine_Api::_()->sesbusiness()->getBusinessRolePermission($business->getIdentity(), 'allow_plugin_content', 'edit');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "edit";
                $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");
                $counterMenu++;
            }

            $can_delete = Engine_Api::_()->sesbusiness()->getBusinessRolePermission($business->getIdentity(), 'allow_plugin_content', 'delete');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "delete";
                $menu[$counterMenu]["label"] = $this->view->translate("Delete Photo");
                $counterMenu++;
            }
            $menu[$counterMenu]["name"] = "report";
            $menu[$counterMenu]["label"] = $this->view->translate("Report Photo");

            $counterMenu++;

            $menu[$counterMenu]["name"] = "makeprofilephoto";
            $menu[$counterMenu]["label"] = $this->view->translate("Make Profile Photo");
            $counterMenu++;
            $albumData['menus'] = $menu;
            $canComment = $business->authorization()->isAllowed($viewer, 'comment') ? true : false;

            $albumData['can_comment'] = $canComment;

            $sharemenu = array();
            if ($viewer->getIdentity() > 0) {
                $sharemenu[0]["name"] = "siteshare";
                $sharemenu[0]["label"] = $this->view->translate("Share");
            }
            $sharemenu[1]["name"] = "share";
            $sharemenu[1]["label"] = $this->view->translate("Share Outside");
            $albumData['share'] = $sharemenu;
        }

        $condition = $this->_getParam('condition');
        if (!$condition) {
            $next = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, ">="), true);
            $previous = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, "<"), true);
            $array_merge = array_merge($previous, $next);

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
                $recArray = array();
                $reactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getPaginator();
                $counterReaction = 0;

                foreach ($reactions as $reac) {
                    if (!$reac->enabled)
                        continue;
                    $albumData['reaction_plugin'][$counterReaction]['reaction_id'] = $reac['reaction_id'];
                    $albumData['reaction_plugin'][$counterReaction]['title'] = $this->view->translate($reac['title']);
                    $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
                    $albumData['reaction_plugin'][$counterReaction]['image'] = $icon['main'];
                    $counterReaction++;
                }

            }
        } else {
            $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, $condition), true);
        }
        $albumData['module_name'] = 'album';
        $albumData['photos'] = $array_merge;

        if (count($albumData['photos']) <= 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('No photo created in this album yet.'), 'result' => array()));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $albumData)));
    }
    public function nextPreviousImage($photo_id, $album_id, $condition = "<=")
    {

        $photoTable = Engine_Api::_()->getItemTable('sesbusiness_photo');
        $select = $photoTable->select()
            ->where('album_id =?', $album_id)
            ->where('photo_id ' . $condition . ' ?', $photo_id)
            ->order('order ASC')
            ->limit(20);
        return $photoTable->fetchAll($select);
    }
    public function getPhotos($paginator, $updateViewCount = false)
    {


        $result = array();
        $counter = 0;

        foreach ($paginator as $photos) {
            $photo = $photos->toArray();
            $photos->view_count = new Zend_Db_Expr('view_count + 1');
            $photos->save();
            $photo['user_title'] = $photos->getOwner()->getTitle();
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer_id = $viewer->getIdentity();
            if ($viewer_id != 0) {
                $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $photo['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($photos);
            }

            $attachmentItem = $photos;
            if ($attachmentItem->getPhotoUrl())
                $photo["shareData"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());

            $photo["shareData"]["title"] = $attachmentItem->getTitle();
            $photo["shareData"]["description"] = strip_tags($attachmentItem->getDescription());

            $photo["shareData"]['urlParams'] = array(
                "type" => $photos->getType(),
                "id" => $photos->getIdentity()
            );

            if (is_null($photo["shareData"]["title"]))
                unset($photo["shareData"]["title"]);

            $owner = $photos->getOwner();
            $photo['owner']['title'] = $owner->getTitle();
            $photo['owner']['id'] = $owner->getIdentity();
            $photo["owner"]['href'] = $owner->getHref();
            $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");

            $photo['can_comment'] = $photos->getParent()->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $photo['module_name'] = 'sesbusiness';
            if ($photo['can_comment']) {
                if ($viewer_id) {
                    $itemTable = Engine_Api::_()->getItemTable($photos->getType(), $photos->getIdentity());
                    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
                    $tableMainLike = $tableLike->info('name');
                    $select = $tableLike->select()
                        ->from($tableMainLike)
                        ->where('resource_type = ?', $photos->getType())
                        ->where('poster_id = ?', $viewer_id)
                        ->where('poster_type = ?', 'user')
                        ->where('resource_id = ?', $photos->getIdentity());
                    $resultData = $tableLike->fetchRow($select);
                    if ($resultData) {
                        $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id); 
                        $photo['reaction_type'] = $item_activity_like->type;
                    }
                }

                $photo['resource_type'] = $photos->getType();
                $photo['resource_id'] = $photos->getIdentity();

                $table = Engine_Api::_()->getDbTable('likes', 'core');
                $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
                $coreliketableName = $coreliketable->info('name');
                $recTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->info('name');
                $select = $table->select()->from($table->info('name'), array('total' => new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?', $photos->getIdentity())->group('type')->setIntegrityCheck(false);
                $select->where('resource_type =?', $photos->getType());
                $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
                $select->joinLeft($recTable, $recTable . '.reaction_id =' . $coreliketableName . '.type', array('file_id'))->where('enabled =?', 1)->order('total DESC');
                $resultData = $table->fetchAll($select);
                $photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $reactionData = array();
                $reactionCounter = 0;
                if (count($resultData)) {
                    foreach ($resultData as $type) {
                        $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)', $type['total'], Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                        $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                        $reactionCounter++;
                    }
                    $photo['reactionData'] = $reactionData;

                }

                if ($photo['is_like']) {
                    $photo['is_like'] = true;
                    $like = true;
                    $type = $photo['reaction_type'];
                    $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type));
                    $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
                } else {
                    $photo['is_like'] = false;
                    $like = false;
                    $type = '';
                    $imageLike = '';
                    $text = 'Like';
                }
                if (empty($like)) {
                    $photo["like"]["name"] = "like";
                } else {
                    $photo["like"]["name"] = "unlike";
                }

                // Get tags
                $tags = array();
                foreach ($photos->tags()->getTagMaps() as $tagmap) {
                    $tags[] = array_merge($tagmap->toArray(), array(
                        'id' => $tagmap->getIdentity(),
                        'text' => $tagmap->getTitle(),
                        'href' => $tagmap->getHref(),
                        'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                    ));
                }

                if ($tags)
                    $photo["tags"] = $tags;
                if ($type)
                    $photo["like"]["type"] = $type;
                if ($imageLike)
                    $photo["like"]["image"] = $imageLike;
                $photo["like"]["label"] = $this->view->translate($text);
                $photo['reactionUserData'] = $this->view->FluentListUsers($photos->likes()->getAllLikesUsers(), '', $photos->likes()->getLike($viewer), $viewer);
            }
            if (!count($album_photo['images']))
                $album_photo['images']['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl());
            $result[$counter] = array_merge($photo, $album_photo);
            $counter++;
        }
        return $result;
    }
    public function addAction() {
		$video_id = $this->_getParam('video_id', false);
		if ($video_id) {
		  $params['video_id'] = $video_id;
		  $insertVideo = Engine_Api::_()->sesbusinessvideo()->deleteWatchlaterVideo($params);
		}else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
		}
		if($insertVideo['status'] == 'insert'){
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully added to watch later.'))));
		}else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully deleted from watch later.'))));
		}
	}
	function getVideos($paginator,$checkProfile){
		$counter = 0;
		$result = array();
		foreach ($paginator as $item){
			$result[$counter] = $item->toArray();
			if($checkProfile['business_id'] > 0){
				$viewer = Engine_Api::_()->user()->getViewer();
				$viewerId = $viewer->getIdentity();
				$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
				if($viewerId>0) {
					$can_edit = Engine_Api::_()->authorization()->getPermission($levelId, 'businessvideo', 'edit');
					$can_delete = Engine_Api::_()->authorization()->getPermission($levelId, 'businessvideo', 'delete');
					if($can_edit &&  $item->status !=2 && $can_delete && $item->owner_id == Engine_Api::_()->user()->getViewer()->getIdentity()){
						$optionCounter = 0;
					if($can_edit){
						$result[$counter]['options'][$optionCounter]['label'] =$this->view->translate('Edit Video');
						$result[$counter]['options'][$optionCounter]['name'] = 'edit';
						$optionCounter++;
					}
					if($can_delete && $item->status !=2){
						$result[$counter]['options'][$optionCounter]['label'] =$this->view->translate('Delete Video');
						$result[$counter]['options'][$optionCounter]['name'] = 'delete';
						$optionCounter++;
					}
					}
				}
                $business = Engine_Api::_()->getItem('businesses',$checkProfile['business_id']);
				$result[$counter]['business_title'] = $business->getTitle();
				
			}else{
                $business = Engine_Api::_()->getItem('businesses',$item->business_id);
				$result[$counter]['business_title'] = $business->getTitle();
			}
			
			if($item->code && $item->type == 'iframely'){
				$embedded = $item->code;
			  preg_match('/src="([^"]+)"/', $embedded, $match);
			  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				$result[$counter]['video']  = str_replace('//','https://',$match[1]);
			  }else{
				  
				$result[$counter]['video']  = $match[1];
			  }
			}else{
				$embedded = $item->getRichContent(true,array(),'','');
			  preg_match('/src="([^"]+)"/', $embedded, $match);
			  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				  
				$result[$counter]['video']  = str_replace('//','https://',$match[1]);
			  }else{
				  
				$result[$counter]['video']  = $match[1];
			  }
			}
			 if($item->getType() == 'businessvideo'){
				$allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.video.rating',1);
				$allowShowPreviousRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.ratevideo.show',1);
			if($allowRating == 0){
				if($allowShowPreviousRating == 0){
					$ratingShow = false;
				}
				 else{
					  $ratingShow = true;
				 }
			  }else{
				  $ratingShow = true;
			  }
			}else{
				$ratingShow = true;
			}
			if($ratingShow)
			$result[$counter]['rating_show'] = $ratingShow;
			$result[$counter]['image'] = $this->getBaseUrl(true, $item->getPhotoUrl());
			if( $item->duration >= 3600 ) {
				$result[$counter]['duration'] = gmdate("H:i:s", $item->duration);
			  } else {
				$result[$counter]['duration'] = gmdate("i:s", $item->duration);
			  }
			  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.enable.watchlater',1)){
				  if(isset($item->watchlater_id)){
				  $result[$counter]['watch_later']['option']['label'] = $this->view->translate('Remove from Watch Later');
				  $result[$counter]['watch_later']['option']['name'] = 'removewatchlater';
				  $result[$counter]['hasWatchlater'] = true;
				  }else{
					  $result[$counter]['watch_later']['option']['label'] =$this->view->translate('Add to Watch Later');
					  $result[$counter]['watch_later']['option']['name'] = 'addtowatchlater';
					  $result[$counter]['hasWatchlater'] = false;
				  }
			  }
			  $viewer = Engine_Api::_()->user()->getViewer();
			  $viewerId = $viewer->getIdentity();
			  if($viewerId != 0 ){
				   $canComment =  $item->authorization()->isAllowed($viewer, 'comment');
				   $result[$counter]['can_comment'] = $canComment?true:false;
				   $result[$counter]['can_like'] = true;
				  $LikeStatus = Engine_Api::_()->sesbusinessvideo()->getLikeStatusVideo($item->video_id,'businessvideo');
				  $result[$counter]['is_content_like'] = $LikeStatus?true:false;
			  }else{
				  $result[$counter]['can_comment'] = false;
				  $result[$counter]['can_like'] = false;
			  }
			  $itemtype = 'businessvideo';
              $getId = 'video_id';
			  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.enable.favourite', 1) && isset($item->favourite_count)){
				  $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesbusinessvideo')->isFavourite(array('resource_type'=>$itemtype,'resource_id'=>$item->$getId));
				  $result[$counter]['is_content_favourite'] = $favStatus?true:false;
				  $result[$counter]['can_favourite'] = true;
				  $result[$counter]['fovourite_show'] = true;
			  }
			  $owner = $item->getOwner();
			  $result[$counter]['owner_title'] = $ownerTitle = $owner->getTitle();
			  if($item->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo_enable_location', 1)){
				  $result[$counter]['location_show'] =true;
			  }else{
				  $result[$counter]['location_show'] =false;
			  }
			  $counter++;
		}
		return $result;
	}
	public function browsevideoAction(){
		$value['status'] = 1;
		$value['watchLater'] = true;
		$value['search'] = 1;
		$paginator = Engine_Api::_()->getDbTable('videos', 'sesbusinessvideo')->getVideo($value,$paginator = true);
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$checkProfile['business_id'] = null;
		$data['videos'] = $this->getVideos($paginator,$checkProfile);
			$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
			$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
			$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
			$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
	public function profileVideosAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $businessId = $this->_getParam('business_id',null);
		if(!$businessId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
            $business = Engine_Api::_()->core()->getSubject();
		}else{
            $business = Engine_Api::_()->getItem('businesses',$businessId);
		}
		if(!$business){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$sort = $this->_getParam('sort', null);
		$search = $this->_getParam('search', null);
		$paginator = Engine_Api::_()->getDbTable('videos', 'sesbusinessvideo')->getVideo(array('parent_id' => $business->getIdentity(), 'parent_type' =>$business->getType(), 'text' => $search, 'sort' => $sort));
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$checkProfile['business_id'] = $business->getIdentity();
		$allowVideo  = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'video');
		$canUpload = $canUpload = $business->authorization()->isAllowed($viewer, 'video');
		if($allowVideo && $canUpload){
			$data['button']['label'] = $this->view->translate('Post New Video');
			$data['button']['name'] = 'create';
		}
		$sortCounter = 0;
		$data['sort'][$sortCounter]['name'] = 'creation_date';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Recently Created');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_liked';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Liked');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_viewed';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Viewed');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_commented';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Commented');
		$sortCounter++;
		$data['videos'] = $this->getVideos($paginator,$checkProfile);
			$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
			$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
			$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
			$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
    public function createVideoAction() {
    if (!$this->_helper->requireUser->isValid())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    // Upload video
	  if (isset($_FILES['Filedata']) && !empty($_FILES['Filedata']['name']))
      $_POST['id'] = $this->uploadVideoAction();
    $viewer = Engine_Api::_()->user()->getViewer();
	$viewerId = $viewer->getIdentity();
	$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    $values['user_id'] = $viewer->getIdentity();
    $parent_id = $parent_id = $this->_getParam('parent_id', null);
    $parent_type = $parent_type = 'businesses';
    if( $parent_id &&  $parent_type)
        $parentItem = Engine_Api::_()->getItem($parent_type, $parent_id);
    if(!$parentItem)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $paginator = Engine_Api::_()->getApi('core', 'sesbusinessvideo')->getVideosPaginator($values);
    $quota = $quota = Engine_Api::_()->authorization()->getPermission($levelId, 'businessvideo', 'max');
    $current_count = $currentCount = $paginator->getTotalItemCount();
	if (($current_count >= $quota) && !empty($quota)){
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have already uploaded the maximum If you would like to upload a new video, please an old one first.'), 'result' => array()));
	}
    //Create form
    $form = $form = new Sesbusinessvideo_Form_Video();
	if ($this->_getParam('type', false))
      $form->getElement('type')->setValue($this->_getParam('type'));
		$form->removeElement('lat');
		$form->removeElement('map-canvas');
		$form->removeElement('ses_location');
		$form->removeElement('lng');
        if($form->removeElement('is_locked'))
            $form->removeElement('password');
        if($form->removeElement('password'))
            $form->removeElement('password');
  
	 if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues('url');
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    }
	
    // Process
    $values = $form->getValues();
    $values['parent_id'] = $parent_id;
    $values['parent_type'] = $parent_type;
    $values['owner_id'] = $viewer->getIdentity();
    $insert_action = false;
    $db = Engine_Api::_()->getDbtable('videos', 'sesbusinessvideo')->getAdapter();
    $db->beginTransaction();
    try {
		$viewer = Engine_Api::_()->user()->getViewer();
		$isApproveUploadOption = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('businessvideo', $viewer, 'video_approve');
		$approveUploadOption = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('businessvideo', $viewer, 'video_approve_type');
		$approve = 1;
		if($isApproveUploadOption){
			foreach($approveUploadOption as $valuesIs){
				if ($values['type'] == 3 && $valuesIs == 'myComputer') {
					//my computer
					$approve = 0;
					break;
				}elseif($valuesIs == "iframely"){
             $approve = 0;
						 break;
          }
				}
			}
		
		//Create video
		$table = Engine_Api::_()->getDbtable('videos', 'sesbusinessvideo');
		if($values['type'] == 'iframely') {
			$information = $this->handleIframelyInformation($values['url']);
			if (empty($information)) {
				$form->addError('We could not find a video there - please check the URL and try again.');
			}
			$values['code'] = $information['code'];
			$values['thumbnail'] = $information['thumbnail'];
			$values['duration'] = $information['duration'];
			$video = $table->createRow();
		}
	  else if ($values['type'] == 3) {
		$video = Engine_Api::_()->getItem('businessvideo', $this->_getParam('id'));
	  } else
        $video = $table->createRow();
		  if ($values['type'] == 3 && isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {
			$values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
		  }
        //disable lock if password not set.
        if (isset($values['is_locked']) && $values['is_locked'] && $values['password'] == '')
          $values['is_locked'] = '0';
		if(empty($_FILES['photo_id']['name'])){
			unset($values['photo_id']);
		}
		$values['approve'] = $approve;
        $video->setFromArray($values);
        $video->save();
        // Add fields
        $customfieldform = $form->getSubForm('fields');
        if (!is_null($customfieldform)) {
          $customfieldform->setItem($video);
          $customfieldform->saveValues();
        }
        $thumbnail = $values['thumbnail'];
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
        $thumbnail_parsed = @parse_url($thumbnail);
        if (@GetImageSize($thumbnail)) {
            $valid_thumb = true;
        } else {
            $valid_thumb = false;
        }
        if(isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '' && $values['type'] != 3 ){
            $video->photo_id = $this->setPhoto($form->photo_id, $video->video_id, true);
            $video->save();
        } else if($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
          $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
          $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
          $src_fh = fopen($thumbnail, 'r');
          $tmp_fh = fopen($tmp_file, 'w');
          stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
          //resize video thumbnails
          $image = Engine_Image::factory();
          $image->open($tmp_file)
                  ->resize(500, 500)
                  ->write($thumb_file)
                  ->destroy();
          try {
            $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                'parent_type' => $video->getType(),
                'parent_id' => $video->getIdentity()
            ));
            // Remove temp file
            @unlink($thumb_file);
            @unlink($tmp_file);
						$video->photo_id = $thumbFileRow->file_id;
						$video->save();
          } catch (Exception $e){
						 @unlink($thumb_file);
             @unlink($tmp_file);
						}
        }
			if($values['type'] == 'iframely') {
				$video->status = 1;
				$video->save();
				$video->type = 'iframely';
				$insert_action = true;
			}
			if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $video->video_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","businessvideo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
          }
        if ($values['ignore'] == true) {
          $video->status = 1;
          $video->save();
          $insert_action = true;
        }
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_view']))
          $auth_view = $values['auth_view'];
        else
          $auth_view = "everyone";
        $viewMax = array_search($auth_view, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
        }
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_comment']))
          $auth_comment = $values['auth_comment'];
        else
          $auth_comment = "everyone";
        $commentMax = array_search($auth_comment, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $video->tags()->addTagMaps($viewer, $tags);
        $db->commit();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' ,'result' => array('message'=>$this->view->translate('Video created successfully.'),'video_id' => $video->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage() , 'result' =>array()));
      //throw $e;
    }
    $db->beginTransaction();
    try {
      if ($approve) {
        $owner = $video->getOwner();
        //Create Activity Feed

        if($parent_id && $parent_type) {
		      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $parentItem, 'sesbusiness_business_editeventvideo');
	        if ($action != null) {
	          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
	        }
        } else {
	        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'sespgvido_crte');
	        if ($action != null) {
	          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
	        }
        }
				// Rebuild privacy
				$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
				foreach ($actionTable->getActionsByObject($video) as $action) {
					$actionTable->resetActivityBindings($action);
				}
      }
      $db->commit();
	  $values = $form->getValues('url');
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Video created successfully.'),'approve'=>'1','video_id' => $video->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage() , 'result' =>array()));
      //throw $e;
    }
  }
    public function uploadVideoAction() {
    if (!$this->_helper->requireUser()->checkRequire()) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    if (!$this->getRequest()->isPost()) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $values = $this->getRequest()->getPost();
    if (empty($_FILES['Filedata'])) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('No file');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid Upload') . print_r($_FILES, true);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt','zip');
    if (in_array(pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION), $illegal_extensions)) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $db = Engine_Api::_()->getDbtable('videos', 'sesbusinessvideo')->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['owner_id'] = $viewer->getIdentity();
      $params = array(
          'owner_type' => 'user',
          'owner_id' => $viewer->getIdentity()
      );
      $video = Engine_Api::_()->sesbusinessvideo()->createVideo($params, $_FILES['Filedata'], $values);
      $result['status'] = true;
      $result['name'] = $_FILES['Filedata']['name'];
      $result['code'] = $video->code;
      $result['video_id'] = $video->video_id;
      // sets up title and owner_id now just incase members switch business as soon as upload is completed
      $video->title = $_FILES['Filedata']['name'];
      $video->owner_id = $viewer->getIdentity();
      $video->save();
      $db->commit();
	   return $video->video_id;
    } catch (Exception $e) {
      $db->rollBack();
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('An error occurred.') . $e;
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
  }
    public function handleIframelyInformation($uri) {
        $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
        if (parse_url($uri, PHP_URL_SCHEME) === null) {
            $uri = "http://" . $uri;
        }
        $uriHost = Zend_Uri::factory($uri)->getHost();
        if ($iframelyDisallowHost && in_array($uriHost, $iframelyDisallowHost)) {
            return;
        }
        $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
        $iframely = Engine_Iframely::factory($config)->get($uri);
        if (!in_array('player', array_keys($iframely['links']))) {
            return;
        }
        $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
        if (!empty($iframely['links']['thumbnail'])) {
            $information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
            if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
                $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
                $information['thumbnail'] = "http://" . $information['thumbnail'];
            }
        }
        if (!empty($iframely['meta']['title'])) {
            $information['title'] = $iframely['meta']['title'];
        }
        if (!empty($iframely['meta']['description'])) {
            $information['description'] = $iframely['meta']['description'];
        }
        if (!empty($iframely['meta']['duration'])) {
            $information['duration'] = $iframely['meta']['duration'];
        }
        $information['code'] = $iframely['html'];
        return $information;
    }
    public function viewVideoAction(){
		$videoid = $this->_getParam('video_id',null);
        if (Engine_Api::_()->core()->hasSubject()){
            $video = Engine_Api::_()->core()->getSubject('businessvideo');
        }
        else if($videoid)
        {
            $video = Engine_Api::_()->getItem('businessvideo', $videoid);
        }else{
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
        }
		$result = $video->toArray();
		$result['user_title'] = $video->getOwner()->getTitle();
		$owneritem = Engine_Api::_()->getItem('user', $video->owner_id);
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owneritem, "", "");
        $thumbimage = Engine_Api::_()->sesapi()->getPhotoUrls($owneritem, "", "thumb.profile");
		
		$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesbusinessvideo')->isFavourite(array('resource_type'=>$video->getType(),'resource_id'=>$video->getIdentity()));
		$LikeStatus = Engine_Api::_()->sesbusinessvideo()->getLikeStatusVideo($video->getIdentity(),$video->getType());
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.enable.favourite', 1)){
			$result['is_content_favourite'] = $favStatus?true:false;
		}
		$result['is_content_like'] = $LikeStatus?true:false;
		if ($ownerimage){
			$result['owner_image'] = $ownerimage;
			
			$result['user_image'] = $thumbimage['main'];
		}

		$viewer = Engine_Api::_()->user()->getViewer();
		 if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslock'))) {
			 $viewer = Engine_Api::_()->user()->getViewer();
			  if ($viewer->getIdentity() == 0)
				$result['level'] = $level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
			  else
				$result['level'] = $level = $viewer;
			$viewerId = $viewer->getIdentity();
			$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
			  if (!Engine_Api::_()->authorization()->getPermission($levelId, 'businessvideo', 'locked') && $video->is_locked) {
				$result['is_locekd'] = $locked = true;
			  } else {
				$result['is_locekd'] = $locked = false;
			  }
			  $result['password'] = $video->password;
		 }else{
			  $result['password'] =  true;
		 }
		 $videoTags = $video->tags()->getTagMaps();
			$can_embed = true;
			if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.embeds', 1)) {
			  $can_embed = false;
			} else if (isset($video->allow_embed) && !$video->allow_embed) {
			  $can_embed = false;
			}
			$result['can-embed'] = $can_embed = $can_embed;
			// increment count
			$embedded = "";
			$mine = true;
			if ($video->status == 1) {
			  if (!$video->isOwner($viewer)) {
				$video->view_count++;
				$video->save();
				$mine = false;
			  }
			  $result['embedded'] = $embe = $video->getRichContent(true,array(),'',true);
			}
			if($video->code && $video->type == 'iframely'){
				
				$embe = $video->code;
				//$embe = $video->getRichContent(true,array(),'',true);
			  //preg_match('/src="([^"]+)"/', $embe, $match);
			  //if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				//$result['iframeURL']  = str_replace('//','https://',$match[1]);
			 // }else{
				$result['iframeURL']  = $embe;
			  //}
			}else{
				
				$storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
				if ($storage_file) {
				  $result['iframeURL'] = $this->getBaseUrl('false',$storage_file->map());
				  $result['video_extension'] = $storage_file->extension;
				}
			}
			$photo = $this->getBaseUrl(false,$video->getPhotoUrl());
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.enable.socialshare', 1)){
				if($photo)
				$result["share"]["imageUrl"] = $photo;
			$result["share"]["url"] = $this->getBaseUrl(false,$video->getHref());
			$result["share"]["title"] = $video->getTitle();
			  $result["share"]["description"] = strip_tags($video->getDescription());
			  $result["share"]['urlParams'] = array(
				  "type" => $video->getType(),
				  "id" => $video->getIdentity()
			  );
			}
			if(is_null($result['video']["share"]["title"]))
			  unset($result['video']["share"]["title"]);
			if ($video->type == 3 && $video->status == 1) {
			  if (!empty($video->file_id)) {
				$storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
				if ($storage_file) {
				  $result['video_location']  = $storage_file->map();
				  $result['video_extension']  = $storage_file->extension;
				}
			  }
			}
			 $result['allowShowRating']  = $allowShowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.ratevideo.show', 1);
			$result['allowRating'] = $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.video.rating', 1);
			$result['getAllowRating'] = $allowRating;
			if ($allowRating == 0) {
			  if ($allowShowRating == 0)
				$showRating = false;
			  else
				$showRating = true;
			} else
			  $showRating = true;
			$result['showRating']  = $showRating;
			if ($showRating) {
			  $result['canRate'] = $canRate = Engine_Api::_()->authorization()->isAllowed('businessvideo', $viewer, 'rating');
			   $result['allowRateAgain'] = $allowRateAgain = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.ratevideo.again', 1);
			  $result['allowRateOwn'] = $allowRateOwn = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.ratevideo.own', 1);
			  if ($canRate == 0 || $allowRating == 0)
				$allowRating = false;
			  else
				$allowRating = true;
			  if ($allowRateOwn == 0 && $mine)
				$allowMine = false;
			  else
				$allowMine = true;
			 $result['allowMine'] = $allowMine;
			 $result['allowRating']  = $allowRating;
			  $result['rating_type']  = $rating_type = 'businessvideo';
			  $result['rating_count'] = $rating_count = Engine_Api::_()->getDbTable('ratings', 'sesbusinessvideo')->ratingCount($video->getIdentity(), $rating_type);
		  $result['rated'] = $rated = Engine_Api::_()->getDbTable('ratings', 'sesbusinessvideo')->checkRated($video->getIdentity(), $viewer->getIdentity(), $rating_type);
		  $rating_sum = Engine_Api::_()->getDbTable('ratings', 'sesbusinessvideo')->getSumRating($video->getIdentity(), $rating_type);
		  if ($rating_count != 0) {
			$result['total_rating_average']  = $rating_sum / $rating_count;
		  } else {
			$result['total_rating_average'] = 0;
		  }
		  if (!$allowRateAgain && $rated) {
				$rated = false;
			  } else {
				$rated = true;
			  }
			  $result['ratedAgain'] = $rated;
		}
		 if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.enable.watchlater',1)){
				  if(isset($video->watchlater_id)){
				  $result['watch_later']['option']['label'] = $this->view->translate('Remove from Watch Later');
				  $result['watch_later']['option']['name'] = 'removewatchlater';
				  $result['hasWatchlater'] = true;
				  }else{
					  $result['watch_later']['option']['label'] =$this->view->translate('Add to Watch Later');
					  $result['watch_later']['option']['name'] = 'addtowatchlater';
					   $result['hasWatchlater'] = false;
				  }
			  }
		
		//$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
		 $result['can_edit'] = 0;
		$result['can_delete'] = 0;
			$videoCounter = 0;
		if($viewer->getIdentity() != 0){
			$resourceItem = Engine_Api::_()->getItem('businesses', $video->parent_id);
			if(count($resourceItem)>0)
			$result['resourceItem'] = $resourceItem;
			$result['parentedit'] = $parentedit = $resourceItem->authorization()->isAllowed($viewer, 'edit');
			$canEdit = $video->authorization()->isAllowed($viewer, 'edit');
			if(!$parentedit && !$canEdit){
				$result['can_edit'] = false;
			}
			else{
				$result['can_edit'] = true;
				$can[$videoCounter]['name'] = 'edit';
				$can[$videoCounter]['label'] = $this->view->translate('Edit Video');
				$videoCounter++;
			}
			$result['parentDelete'] = $parentDelete = $resourceItem->authorization()->isAllowed($viewer, 'delete');
			$canDelete = $video->authorization()->isAllowed($viewer, 'delete');
			if(!$parentDelete && !$canDelete){
				$result['can_delete'] = false;
			}
			else{
				$result['can_delete'] = true;
				$can[$videoCounter]['name'] = 'delete';
				$can[$videoCounter]['label'] = $this->view->translate('Delete Video');
				$videoCounter++;
			}

			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessvideo.enable.report',1) && $viewerId != $video->owner_id){
				$can[$videoCounter]['name'] = 'report';
				$can[$videoCounter]['label'] = $this->view->translate('Report');
				$videoCounter++;
			}
		}
		$rating['code']  = 100;
        $rating['message']  = '';
        $rating['total_rating_average']  = $video->rating;
		$result['rating'] = $rating;
		$data['video'] = $result;
		$data['menus'] = $can;
        $video = $video;
        if( !$video || $video->status != 1 ){
             Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The video you are looking for does not exist or has not been processed yet.'), 'result' => array()));
        }else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => $data));
		} 
	}
	public function geturlAction(){
		$video_id = $this->_getParam('video_id',null);
		
		if(!$video_id){
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' =>array()));
		}
		$video = Engine_Api::_()->getItem('businessvideo', $video_id);
		//$embe = $video->code;
		echo $this->view->embe = $video->getRichContent(true,array(),'',true);
    exit;
		//Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' =>array('url'=>$embe)));
		
	}
    public function rateAction() {
	 if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    $rating = $this->_getParam('rating');
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
	if(!$rating || !$resource_id || !$resource_type)
		 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('parameter_missing'), 'result' => array()));
    $table = Engine_Api::_()->getDbtable('ratings', 'sesbusinessvideo');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getDbtable('ratings', 'sesbusinessvideo')->setRating($resource_id, $user_id, $rating, $resource_type);
      if ($resource_type && $resource_type == 'businessvideo')
        $item = Engine_Api::_()->getItem('businessvideo', $resource_id);
      $item->rating = Engine_Api::_()->getDbtable('ratings', 'sesbusinessvideo')->getRating($item->getIdentity(), $resource_type);
      $item->save();
      if ($resource_type == 'businessvideo') {
        $type = 'sespgvido_rating';
      }
      $result = Engine_Api::_()->getDbtable('actions', 'activity')->fetchRow(array('type =?' => $type, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      if (!$result) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $item, $type);
        if ($action)
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $total = Engine_Api::_()->getDbtable('ratings', 'sesbusinessvideo')->ratingCount($item->getIdentity(), $resource_type);
    $rating_sum = Engine_Api::_()->getDbtable('ratings', 'sesbusinessvideo')->getSumRating($item->getIdentity(), $resource_type);
    $data = array();
    $totalTxt = $this->view->translate(array('%s rating', '%s ratings', $total), $total);
    $data = array(
        'total' => $total,
        'rating' => $rating,
        'totalTxt' => str_replace($total, '', $totalTxt),
        'rating_sum' => $rating_sum
    );
	Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('message'=>$this->view->translate('Successfully rated.'))));
  }
    public function editVideoAction() {
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $video = Engine_Api::_()->getItem('businessvideo', $this->_getParam('video_id'));
    // Render
    $this->_helper->content->setEnabled();
    $this->view->parentItem = $resourceItem = Engine_Api::_()->getItem('businesses', $video->parent_id);
    $canEditParent = $resourceItem->authorization()->isAllowed($viewer, 'edit');
    $canEdit = $video->authorization()->isAllowed($viewer, 'edit');
    if(!$canEdit && !$canEditParent)
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $form = new Sesbusinessvideo_Form_Edit();

	$latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('businessvideo',$video->video_id);
	//if($latLng){
		if($form->getElement('lat'))
			$form->removeElement('lat');
		if($form->getElement('lng'))
			$form->removeElement('lng');
		if($form->getElement('ses_location'))
			$form->removeElement('ses_location');
		if($form->getElement('map-canvas'))
			$form->removeElement('map-canvas');
        if($form->removeElement('is_locked'))
            $form->removeElement('password');
        if($form->removeElement('password'))
            $form->removeElement('password');
	//}
	if($form->getElement('location'))
	$form->getElement('location')->setValue($video->location);
    $form->getElement('search')->setValue($video->search);
    $form->getElement('title')->setValue($video->title);
    $form->getElement('description')->setValue($video->description);

    //if ($form->getElement('is_locked'))
     // $form->getElement('is_locked')->setValue($video->is_locked);
    //if ($form->getElement('password'))
     // $form->getElement('password')->setValue($video->password);
    // authorization
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    foreach ($roles as $role) {
      if (1 === $auth->isAllowed($video, $role, 'view')) {
        $form->auth_view->setValue($role);
      }
      if (1 === $auth->isAllowed($video, $role, 'comment')) {
        $form->auth_comment->setValue($role);
      }
    }
    // prepare tags
    $videoTags = $video->tags()->getTagMaps();
    $tagString = '';
    foreach ($videoTags as $tagmap) {
      if ($tagString !== '')
        $tagString .= ', ';
      $tagString .= $tagmap->getTag()->getTitle();
    }
    $this->view->tagNamePrepared = $tagString;
    $form->tags->setValue($tagString);
     if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
    
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    //if (!$form->isValid($this->getRequest()->getPost())) {
      // Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
   // }
    // Process
    $db = Engine_Api::_()->getDbtable('videos', 'sesbusinessvideo')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      if (isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {
		  
        $values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
      } else {
        if (empty($values['photo_id'])){
          unset($values['photo_id']);
				}
      }
		if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslock'))) {
			//disable lock if password not set.
			if (!$values['is_locked']) {
				$values['is_locked'] = '0';
				$values['password'] = '';
			}else
				unset($values['password']);
		}
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('video_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","businessvideo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      $video->setFromArray($values);
      $video->save();
      // Add fields
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
        $customfieldform->setItem($video);
        $customfieldform->saveValues();
      }
      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_view'])
        $auth_view = $values['auth_view'];
      else
        $auth_view = "everyone";
      $viewMax = array_search($auth_view, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
      }
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_comment'])
        $auth_comment = $values['auth_comment'];
      else
        $auth_comment = "everyone";
      $commentMax = array_search($auth_comment, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
      }
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $video->tags()->setTagMaps($viewer, $tags);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($video) as $action) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Video edited successfully.'),'video_id' => $video->getIdentity())));
  }
	public function deleteVideoAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
	$videoid = $this->_getParam('video_id');
	if(!$videoid)
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $video = Engine_Api::_()->getItem('businessvideo',$videoid);
    $resourceItem = Engine_Api::_()->getItem('businesses', $video->parent_id);
	if(!$resourceItem)
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('data not found'), 'result' => array()));
    $canEdit = $video->authorization()->isAllowed($viewer, 'delete');
	$canEditParent = $resourceItem->authorization()->isAllowed($viewer, 'delete');
    if(!$canEdit && !$canEditParent)
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$video) {
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Video doesn\'t exists or not authorized to delete'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    $db = $video->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getApi('core', 'sesbusinessvideo')->deleteVideo($video);
      $db->commit();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('message'=>$this->view->translate('video deleted successfully'))));
    } catch (Exception $e) {
      $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
    //item liked as per item tye given
    function likeVideoAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
	    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    }
    $type = 'businessvideo';
    $dbTable = 'videos';
    $resorces_id = 'video_id';
    $notificationType = 'liked';
    $item_id = $this->_getParam('resource_id');
    if (intval($item_id) == 0) {
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $itemTable = Engine_Api::_()->getDbtable($dbTable, 'sesbusinessvideo');
    $select = $tableLike->select()->from($tableMainLike)->where('resource_type =?', $type)->where('poster_id =?', Engine_Api::_()->user()->getViewer()->getIdentity())->where('poster_type =?', 'user')->where('resource_id =?', $item_id);
    $Like = $tableLike->fetchRow($select);
    if (count($Like) > 0) {
      //delete
      $db = $Like->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Like->delete();
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully Unliked.');
      } catch (Exception $e) {
        $db->rollBack();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //$itemTable->update(array(
        //  'like_count' => new Zend_Db_Expr('like_count - 1'),
          //    ), array(
          //$resorces_id . ' = ?' => $item_id,
     // ));
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
       //$temp['like_count'] = $item->like_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $like = $tableLike->createRow();
        $like->poster_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $like->resource_type = $type;
        $like->resource_id = $item_id;
        $like->poster_type = 'user';
        $like->save();
        $itemTable->update(array(
            'like_count' => new Zend_Db_Expr('like_count + 1'),
                ), array(
            $resorces_id . '= ?' => $item_id,
        ));
        // Commit
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully liked.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem($type, $item_id);
      $subject = $item;
      $owner = $subject->getOwner();
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
        $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
        $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
        if (!$result) {
          $action = $activityTable->addActivity($viewer, $subject, $notificationType);
          if ($action)
            $activityTable->attachActivity($action, $subject);
        }
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
  }
    function favouriteVideoAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    }
    $type = 'businessvideo';
    $dbTable = 'videos';
    $resorces_id = 'video_id';
    $notificationType = 'sespgvido_fav';

    $item_id = $this->_getParam('resource_id');
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $Fav = Engine_Api::_()->getDbTable('favourites', 'sesbusinessvideo')->getItemfav($type, $item_id);
    $favItem = Engine_Api::_()->getDbtable($dbTable, 'sesbusinessvideo');
    if (count($Fav) > 0) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully unfavourited.');
      } catch (Exception $e) {
        $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
	  //$temp['data']['favourite_count'] = $item->favourite_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('favourites', 'sesbusinessvideo')->getAdapter();
      $db->beginTransaction();
      try {
        $fav = Engine_Api::_()->getDbTable('favourites', 'sesbusinessvideo')->createRow();
        $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $fav->resource_type = $type;
        $fav->resource_id = $item_id;
        $fav->save();
        $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1'),
                ), array(
            $resorces_id . '= ?' => $item_id,
        ));
        // Commit
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully favourited.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem(@$type, @$item_id);
      if ($this->_getParam('type') != 'sesbusinessvideo_artist') {
        $subject = $item;
        $owner = $subject->getOwner();
        if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
          $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
          Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
          $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          if (!$result) {
            $action = $activityTable->addActivity($viewer, $subject, $notificationType);
            if ($action)
              $activityTable->attachActivity($action, $subject);
          }
        }
      }
       //$temp['data']['favourite_count'] = $item->favourite_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
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
        $value['business'] = isset($_POST['business']) ? $_POST['business'] : 1;
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
        //$value['showdefaultalbum'] = 0;
        $value['business_id'] = $this->_getParam('business_id',0);
        $paginator = Engine_Api::_()->getDbTable('albums', 'sesbusiness')->getAlbumSelect($value);
        $paginator->setItemCountPerPage($this->_getParam('limit', 1));
        $paginator->setCurrentPageNumber($this->_getParam('page', 10));
        $albumCounter = 0;
        foreach ($paginator as $item) {
            $owner = $item->getOwner();
            $ownertitle = $owner->displayname;
            $result['albums'][$albumCounter] = $item->toArray();
            $result['albums'][$albumCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "") ? Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "") : $result['members'][$counterLoop]['owner_photo'] = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
            $result['albums'][$albumCounter]['user_title'] = $ownertitle;
            $albumLikeStatus = Engine_Api::_()->sesbusiness()->getLikeStatus($item->getIdentity(), $item->getType());
            $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'sesbusiness')->isFavourite(array('resource_type' => 'album', 'resource_id' => $item->album_id));
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
        $result['can_create'] = $canCreate?true:false;
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
        $searchForm = new Sesbusiness_Form_AlbumSearch(array('searchTitle' => $this->_getParam('search_title', 'yes'), 'browseBy' => $this->_getParam('browse_by', 'yes'), 'searchFor' => $search_for, 'FriendsSearch' => $this->_getParam('friend_show', 'yes'), 'defaultSearchtype' => $default_search_type));
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
        $searchForm->removeElement('loading-img-sesbusiness');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
            $this->generateFormFields($formFields);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array())));

        }
    }
	function getButtonMenus($businesses){
        $viewer = $this->view->viewer();
        $showLoginformFalse = false;
         if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.enable.contact.details', 1)) {
            $showLoginformFalse = true;
        }
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusiness.allow.share', 0);
        $i = 0;
        if ($businesses->business_contact_email || $businesses->business_contact_phone || $businesses->business_contact_website) {
            if ($businesses->business_contact_email) {

                $result[$i]['name'] = 'mail';
                $result[$i]['label'] = 'Send Email';
                $result[$i]['value'] = $businesses->business_contact_email;
                $i++;

            }
            if ($businesses->business_contact_phone) {
                $result[$i]['name'] = 'phone';
                $result[$i]['label'] = 'Call';
                $result[$i]['value'] = $businesses->business_contact_phone;
                $i++;
            }
            if ($businesses->business_contact_website) {

                $result[$i]['name'] = 'website';
                $result[$i]['label'] = 'Visit Website';
                $result[$i]['value'] = $businesses->business_contact_website;
                $i++;
            }
        }
  
    if ($businesses->is_approved) {
        $result[$i]['name'] = 'contact';
        $result[$i]['label'] = 'Contact';
        $i++;
        if ($shareType) {  
            $result[$i]['name'] = 'share';
            $result[$i]['label'] = 'Share';
            $i++;  
        }
        if ($viewerId) {
          $row = $businesses->membership()->getRow($viewer);
          if (null === $row) {
              if ($businesses->membership()->isResourceApprovalRequired()) {
                  $result[$i]['name'] = 'request';
                  $result[$i]['label'] = 'Request Membership';
                  $i++;
              } else {
                  $result[$i]['name'] = 'join';
                  $result[$i]['label'] = 'Join Business';
                  $i++;
              }
          } else if ($row->active) {
              if (!$businesses->isOwner($viewer)) {
                  $result[$i]['label'] = 'Leave Business';
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
    public function createalbumAction(){
		
        $business_id = $this->_getParam('business_id', false);
        $album_id = $this->_getParam('album_id', 0);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('sesbusiness_album', $album_id);
            $business_id = $album->business_id;
        } else {
            $business_id = $business_id;
        }
        $business = Engine_Api::_()->getItem('businesses', $business_id);
		if(!$business_id)
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array())));
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        $current_count = Engine_Api::_()->getDbTable('albums', 'sesbusiness')->getUserAlbumCount($values);
        $quota = $quota = 0;
        // Get form
        $form = new Sesbusiness_Form_Album();
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
            $this->generateFormFields($formFields, array('resources_type' => 'businesses'));
        }
        if (!$form->isValid($this->getRequest()->getPost())){
          $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        $db = Engine_Api::_()->getItemTable('sesbusiness_album')->getAdapter();
        $db->beginTransaction();
        try {
            $photoTable = Engine_Api::_()->getDbTable('photos', 'sesbusiness');
			$uploadSource = $_FILES['image'];
			$photoArray = array(
            'business_id' => $business->business_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'sesbusiness')->getAdapter();
        $db->beginTransaction();
        try {
                $images['name'] = $uploadSource['name'][$counter];
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
				$albumdata = $album?$album:false;
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
        $_POST['business_id'] = $business->business_id;
        $_POST['file'] = implode(' ', $photosource);
            $album = $form->saveValues();
            // Add tags
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Created.'), 'album_id' => $album->getIdentity()))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array())));
        }
    }
    public function acceptAction()
    {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject('businesses')->isValid())
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
                $action = $activityApi->addActivity($viewer, $subject, 'sesbusiness_business_join');
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have accepted the invite to the business'),'menus'=>$this->getButtonMenus($subject))));
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
        if (!$this->_helper->requireSubject('businesses')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_misssing', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $user = Engine_Api::_()->getItem('user', (int)$this->_getParam('user_id'));
            $subject->membership()->removeMember($user);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'sesbusiness_reject');
            // Set the request as handled
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $viewer, $subject, 'sesbusiness_invite');
            if ($notification) {
                $notification->mitigated = true;
                $notification->save();
            }
            $db->commit();
            $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the business');
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
        $business = Engine_Api::_()->core()->getSubject();
        if (!$business->membership()->isMember($user)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Cannot remove a non-member.'), 'result' => array()));
        }
        $db = $business->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Remove membership
            $business->membership()->removeMember($user);
            // Remove the notification?
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $business->getOwner(), $business, 'sesbusiness_approve');
            if ($notification) {
                $notification->delete();
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('message' => $this->view->translate('The selected member has been removed from this business.'),'menus'=>$this->getButtonMenus($subject))));
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
        if (!$this->_helper->requireSubject('businesses')->isValid())
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
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'sesbusiness_accepted');
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Business request approved'),'menus'=>$this->getButtonMenus($subject))));
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

        $subject = Engine_Api::_()->core()->getSubject('businesses');
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $subject->membership()->removeMember($user);

            // Remove the notification?
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $subject->getOwner(), $subject, 'sesbusiness_approve');
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
}
