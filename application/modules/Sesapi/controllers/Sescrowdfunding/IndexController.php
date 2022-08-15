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
  
class Sescrowdfunding_IndexController extends Sesapi_Controller_Action_Standard {
	public function init(){
		$crowdfunding_id = $this->_getParam('crowdfunding_id');
		$crowdfunding = null;
		$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		if ($crowdfunding) {
			if ($crowdfunding) {
				Engine_Api::_()->core()->setSubject($crowdfunding);
			} else {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			}
		}
	}
	
	public function browsesearchAction(){
		$filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed', 'mostSPdonated' => 'Most Donated','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','mostSPrated'=>'Most Rated'));

    $this->view->view_type = $this-> _getParam('view_type', 'horizontal');
    $this->view->search_for = $search_for = $this-> _getParam('search_for', 'crowdfunding');
    $default_search_type = $this-> _getParam('default_search_type', 'mostSPliked');

    if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding_enable_location', 1))
      $location = 'yes';
    else
      $location = 'no';

    $searchForm = $this->view->form = new Sescrowdfunding_Form_Search(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type,'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes')));

    if($this->_getParam('search_type','crowdfunding') !== null && $this->_getParam('browse_by', 'yes') == 'yes'){
      $arrayOptions = $filterOptions;
      $filterOptions = array();
      foreach ($arrayOptions as $key=>$filterOption) {
        if(is_numeric($key))
        $columnValue = $filterOption;
        else
        $columnValue = $key;
				$value = str_replace(array('SP',''), array(' ',' '), $columnValue);
				$filterOptions[$columnValue] = ucwords($value);
      }
      $filterOptions = array(''=>'')+$filterOptions;
      $searchForm->sort->setMultiOptions($filterOptions);
      $searchForm->sort->setValue($default_search_type);
    }
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $searchForm->setMethod('get')->populate($request->getParams());
		$searchForm->removeElement('lat');
		$searchForm->removeElement('lng');
		if ($this->_getParam('getForm')) {
				$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
				$this->generateFormFields($formFields, array('resources_type' => 'sespage_page'));
		} else {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
	}
	
	public function menusAction(){
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescrowdfunding_main', array());
		$menu_counter = 0;
		foreach ($menus as $menu) {
			$class = end(explode(' ', $menu->class));
			if ('sescrowdfunding_main_browsehome' == $class)
					continue;
				
			$result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
			$result_menu[$menu_counter]['action'] = $class;
			$result_menu[$menu_counter]['isActive'] = $menu->active;
			$menu_counter++;
		}
		$result['menus'] = $result_menu;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result)));
  }
	
	public function browseAction(){
		$value['status'] = 1;
    $value['search'] = 1;
		$value['draft'] = "0";
    $value['visible'] = "1";
		if($this->_getParam('search'))
			$value['text'] = $this->_getParam('search');
		if ($this->_getParam('search')) {
			switch ($this->_getParam('search')) {
				case 'most_viewed':
					$value['popularCol'] = 'view_count';
					break;
				case 'most_liked':
					$value['popularCol'] = 'like_count';
					break;
				case 'most_commented':
					$value['popularCol'] = 'comment_count';
					break;
                case 'most_donated':
					$value['popularCol'] = 'donate_count';
					break;
				case 'featured':
					$value['popularCol'] = 'featured';
					$value['fixedData'] = 'featured';
					break;
				case 'most_rated':
					$value['popularCol'] = 'rating';
					break;
				case 'recently_created':
					default:
					$value['popularCol'] = 'creation_date';
					break;
			}
		}
		if($this->_getParam('show'))
			$value['show'] = $this->_getParam('show');
		if($this->_getParam('category_id'))
			$value['category_id'] = $this->_getParam('category_id');
		if($this->_getParam('subcat_id'))
			$value['subcat_id'] = $this->_getParam('subcat_id');
		if($this->_getParam('subsubcat_id'))
			$value['subsubcat_id'] = $this->_getParam('subsubcat_id');
		
		$paginator = Engine_Api::_()->getDbtable('crowdfundings', 'sescrowdfunding')->getSescrowdfundingsPaginator($value);
		
		$paginator->setItemCountPerPage($this->_getParam('limit','10'));
    $paginator->setCurrentPageNumber($this->_getParam('page','1'));
		
		if ($this->_getParam('page','1') == '1' &&  !$this->_getParam('category_id') && !$this->_getParam('search')) {
			$value = array();
			$categories = Engine_Api::_()->getDbTable('categories', 'sescrowdfunding')->getCategory($value);
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
				$result_category[$category_counter]['total_crowdfundings_categories'] = $category->total_crowdfundings_categories;
				$result_category[$category_counter]['category_id'] = $category->category_id;
				$category_counter++;
			}
			$result['category'] = $result_category;
		}
		
		$result['campaigns'] = $this->getCampaigns($paginator);
		
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	
	public function getCampaigns($paginator,$manage = false){
		$result = array();
		$counter = 0 ;
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$enableShare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.enable.sharing', 1);
		$progressBackgroundColor = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.outercolor', 'fbfbfb');
		$progressFillColor = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.fillcolor', 'fbfbfb');
		
			
		foreach($paginator as $campaign){
			$owner = $campaign->getOwner();
			$canComment =  $campaign->authorization()->isAllowed($viewer, 'comment');
			$LikeStatus = Engine_Api::_()->sescrowdfunding()->getLikeStatusCrowdfunding($campaign->crowdfunding_id, $campaign->getType());
			$result[$counter] = $campaign->toArray();
			
			
			/*--------------------------manage work ----------------------------------------*/
			if($manage){
				$optionCounter = 0;
				if($campaign->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit')){
					$result[$counter]['options'][$optionCounter]['name'] = 'edit';
					$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Edit');
					$optionCounter++;
				}
				if($campaign->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){
					$result[$counter]['options'][$optionCounter]['name'] = 'delete';
					$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Delete');
					$optionCounter++;
				}
			}
			
			/*------------------------ owner's data ---------------------------- */
			$result[$counter]['owner_title'] = $owner->getTitle();
			$ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner,'',"thumb.profile");
			$result[$counter]['owner_image'] = $ownerimage;
			
			/*------------------------ category's title ---------------------------- */
			if ($campaign->category_id) {
				$category = Engine_Api::_()->getItem('sescrowdfunding_category', $campaign->category_id);
				if ($category) {
					$result[$counter]['category_title'] = $category->category_name;
					if ($campaign->subcat_id) {
						$subcat = Engine_Api::_()->getItem('sescrowdfunding_category', $campaign->subcat_id);
						if ($subcat) {
							$result[$counter]['subcategory_title'] = $subcat->category_name;
							if ($campaign->subsubcat_id) {
								$subsubcat = Engine_Api::_()->getItem('sescrowdfunding_category', $campaign->subsubcat_id);
								if ($subsubcat) {
										$result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
								}
							}
						}
					}
				}
			}
			
			/*------------------------ main image ---------------------------- */
			$result[$counter]['images']['main']= $this->getBaseUrl(true, $campaign->getPhotoUrl());
			
			/*------------------------ share object ---------------------------- */
			if($enableShare){
				$result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $campaign->getPhotoUrl());
				$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$campaign->getHref());
				$result[$counter]["share"]["title"] = $campaign->getTitle();
				$result[$counter]["share"]["description"] = strip_tags($campaign->getDescription());
				$result[$counter]["share"]["setting"] = $enableShare;
				$result[$counter]["share"]['urlParams'] = array(
					"type" => $campaign->getType(),
					"id" => $campaign->getIdentity()
				);
			}
			
			/*------------------------ tags ---------------------------- */
			$tags = array();
			foreach ($campaign->tags()->getTagMaps() as $tagmap) {
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
			
			if($canComment){
				$result[$counter]['is_content_like'] = $LikeStatus?true:false;
			}
			
			$currency = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();
			$totalGainAmount = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getCrowdfundingTotalAmount(array('crowdfunding_id' => $campaign->crowdfunding_id));
			$getDoners = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getDoners(array('crowdfunding_id' => $campaign->crowdfunding_id));
			$totalGainAmountwithCu = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($totalGainAmount,$currency);
			$result[$counter]['gain_price'] = $totalGainAmount ? $totalGainAmount : 0;
			$totalAmount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($campaign->price,$currency);
			$totalPerAmountGain = (($totalGainAmount * 100) / $campaign->price);
			if($totalPerAmountGain > 100) {
				$totalPerAmountGain = 100;
			} else if(empty($totalPerAmountGain)) {
				$totalPerAmountGain = 0;
			}
			
			$daysLeft = 0;
			$fromDate = date('Y-m-d',strtotime($campaign->publish_date));
			$curDate = date('Y-m-d');
			$daysLeft = abs(strtotime($curDate) - strtotime($fromDate));
			$days = $daysLeft/(60 * 60 * 24);
			
			if(empty($campaign->show_start_time) && $campaign->publish_date != '' && strtotime($campaign->publish_date) > time()) {
			$result[$counter]['campaign_expiration_label'] = $this->view->translate(array('%s day', '%s days', $days), $this->view->locale()->toNumber($days));
			}elseif(strtotime($campaign->publish_date) < time() && empty($campaign->show_start_time)) {
				$result[$counter]['campaign_expiration_label'] = $this->view->translate("Expired");
			}
			/*------------------------ progress bar detail ---------------------------- */
			$result[$counter]['progressbar_background_color'] = $progressBackgroundColor;
			$result[$counter]['progressbar_fill_color'] = $progressFillColor;
			$result[$counter]['gain_amount'] = $totalGainAmountwithCu;
			$result[$counter]['donor_count'] = $getDoners;
			$result[$counter]['total_amount'] = $totalAmount;
			
			
				
			$counter++;
		}
		return $result;
	}
	
	public function categoriesAction(){
		
		$paginator = Engine_Api::_()->getDbTable('categories', 'sescrowdfunding')->getCategory(array('paginator' => true));
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
		if ($paginator->getCurrentPageNumber() == 1) {
			$categories = Engine_Api::_()->getDbtable('categories', 'sescrowdfunding')->getCategory(array('column_name' => '*', 'limit' => 25));
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
				$result_category[$category_counter]['total_crowdfundings_categories'] = $category->total_crowdfundings_categories;
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
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	
	public function getCategory($categoryPaginator){
		$result = array();
		$counter = 0;
		foreach ($categoryPaginator as $categories) {
			$category = $categories->toArray();
			$params['category_id'] = $categories->category_id;
			$params['limit'] = 5;
			$paginator = Engine_Api::_()->getDbTable('crowdfundings', 'sescrowdfunding')->getSescrowdfundingsPaginator($params);
			$paginator->setItemCountPerPage(3);
			$paginator->setCurrentPageNumber(1);
			if($paginator->getTotalItemCount() > 0){
				$result[$counter] = $category;
				$result[$counter]['items'] = $this->getCampaigns($paginator);
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

	public function manageAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $values['manage-widget'] = 1;
		$paginator = Engine_Api::_()->getItemTable('crowdfunding')->getSescrowdfundingsPaginator($values);
    $paginator->setItemCountPerPage(15);
    $paginator->setCurrentPageNumber(10);
		$manage = true;
		
		$data['campaigns'] = $this->getCampaigns($paginator,$manage);
		
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
	
	public function manageDonationsAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    if(empty($viewer->getIdentity()))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    $orders = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getAllDonations(array('owner_id' => $viewer->getIdentity()));
    $paginator = Zend_Paginator::factory($orders);
    
		$paginator->setItemCountPerPage($this->_getParam('limit','10'));
    $paginator->setCurrentPageNumber($this->_getParam('page','1'));
    
		$result['donations'] = $this->getDonations($paginator);
		
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	
	public function getDonations($paginator,$received = false){
		$counter= 0;
		$result = array();
		$currency = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();
		foreach($paginator as $donation){
			$result[$counter] = $donation->toArray();
		
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $donation->crowdfunding_id);
			$result[$counter]['title'] = $crowdfunding->getTitle();
			$donationAmount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($donation->total_useramount);
			$result[$counter]['images']['main'] = $this->getBaseUrl(false, $crowdfunding->getPhotoUrl());
			$result[$counter]['owner_title'] = $crowdfunding->getOwner()->getTitle();
			if($crowdfunding->category_id){
				$category = Engine_Api::_()->getItem('sescrowdfunding_category', $crowdfunding->category_id);
				if($category)
				$result[$counter]['category_title'] = $category->getTitle();
			}
			if($received)
				$result[$counter]['donation_label'] = $this->view->translate("You have donated %s", $donationAmount);
			else
				$result[$counter]['donation_label'] = $this->view->translate("Donation Amount %s", $donationAmount);
			$counter++;
		}
		return $result;
	}
	
	public function manageReceivedDonationsAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    if(empty($viewer->getIdentity()))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    $values['user_id'] = $viewer->getIdentity();
    $values['fetchAll'] = 1;
    $getCrowdfundings = Engine_Api::_()->getItemTable('crowdfunding')->getSescrowdfundingsSelect($values);

    $crowdfundingIds = array('0');
    foreach($getCrowdfundings as $getCrowdfunding) {
      $crowdfundingIds[] = $getCrowdfunding->crowdfunding_id;
    }

    $orders = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getAllDonations(array('crowdfundingIds' => $crowdfundingIds));
    $paginator = Zend_Paginator::factory($orders);
    $paginator->setItemCountPerPage($this->_getParam('limit','25'));
		$paginator->setCurrentPageNumber($this->_getParam('page','1'));
		
		$result['donations'] = $this->getDonations($paginator);
		
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}

	public function viewAction(){
		//Check permission
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewerId = $viewer->getIdentity();
        $id = $this->_getParam('crowdfunding_id');
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $crowdfunding_id = $crowdfunding_id = Engine_Api::_()->getDbtable('crowdfundings', 'sescrowdfunding')->getCrowdfundingId($id);
        if(!Engine_Api::_()->core()->hasSubject())
            $sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
        else
            $sescrowdfunding = Engine_Api::_()->core()->getSubject();

		$progressBackgroundColor = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.outercolor', 'fbfbfb');
		$progressFillColor = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.fillcolor', 'fbfbfb');
		if((!$id || !$this->_helper->requireSubject()->isValid()) || !$this->_helper->requireAuth()->setAuthParams($sescrowdfunding, $viewer, 'view')->isValid()){
			print_r(!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid());
			die;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
			

		//Prepare data
		$result = array();
		$result = $sescrowdfunding->toArray();
		$result['images']['main'] = $this->getBaseUrl(false, $sescrowdfunding->getPhotoUrl());
		$category = Engine_Api::_()->getItem('sescrowdfunding_category', $sescrowdfunding->category_id);
		$result['category_title'] = $category->getTitle();
		/*$currency = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();
		$totalGainAmount = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getCrowdfundingTotalAmount(array('crowdfunding_id' => $sescrowdfunding->crowdfunding_id)); 
		$totalGainAmount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($totalGainAmount,$currency);
		$result['total_donated_amount'] = $totalGainAmount;
		$goal = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($sescrowdfunding->price,$currency);		
    $result['total_goal_amount'] = $goal;*/
		
		$currency = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();
		$totalGainAmount = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getCrowdfundingTotalAmount(array('crowdfunding_id' => $sescrowdfunding->crowdfunding_id));
		$getDoners = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getDoners(array('crowdfunding_id' => $sescrowdfunding->crowdfunding_id));
		$totalGainAmountwithCu = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($totalGainAmount,$currency);
		$result['gain_price'] = $totalGainAmount ? $totalGainAmount : 0;
		
		$totalAmount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($sescrowdfunding->price,$currency);
		$totalPerAmountGain = (($totalGainAmount * 100) / $sescrowdfunding->price);
		if($totalPerAmountGain > 100) {
			$totalPerAmountGain = 100;
		} else if(empty($totalPerAmountGain)) {
			$totalPerAmountGain = 0;
		}
		
        $owner = $sescrowdfunding->getOwner();
		$result['owner_title'] = $owner->getTitle();
        if( !$sescrowdfunding->isOwner($viewer) ) {
             Engine_Api::_()->getDbtable('crowdfundings', 'sescrowdfunding')->update(array('view_count' => new Zend_Db_Expr('view_count + 1')), array('crowdfunding_id = ?' => $sescrowdfunding->getIdentity()));
         }

		$locationLatLng = Engine_Api::_()->getDbtable('locations', 'sesbasic')->getLocationData($sescrowdfunding->getType(),
        $sescrowdfunding->getIdentity());
        if($locationLatLng) {
            $result['location_object'] = $locationLatLng->toArray();
            $result['lat'] = $locationLatLng->lat;
            $result['lng'] = $locationLatLng->lng;
        }
    //Get tags
        $tags = array();
        foreach ($sescrowdfunding->tags()->getTagMaps() as $tagmap) {
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
            $result['tag'] = $tags;
        }


        $j = 0;
        if (!empty($sescrowdfunding->photo_id)) {
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('View Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'view';
            $j++;
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Change Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'upload';
            $j++;
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Remove Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'removePhoto';
            $j++;
        } else {
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Upload Profile Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'upload';
            $j++;
        }
        $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Choose From Albums');
        $result['updateProfilePhoto'][$j]['name'] = 'album';
        $j++;


        $rating_count = Engine_Api::_()->sescrowdfunding()->ratingCount($sescrowdfunding->getIdentity());
		$result['rating_count'] = $rating_count;
        $rated = Engine_Api::_()->sescrowdfunding()->checkRated($sescrowdfunding->getIdentity(), $viewer->getIdentity());
		$result['is_rated'] = $rated;
		
		$daysLeft = 0;
		$fromDate = date('Y-m-d',strtotime($sescrowdfunding->publish_date));
		$curDate = date('Y-m-d');
		$daysLeft = abs(strtotime($curDate) - strtotime($fromDate));
		$days = $daysLeft/(60 * 60 * 24);
		
		if(empty($sescrowdfunding->show_start_time) && $sescrowdfunding->publish_date != '' && strtotime($sescrowdfunding->publish_date) > time()) {
		$result['campaign_expiration_label'] = $this->view->translate(array('%s day', '%s days', $days), $this->view->locale()->toNumber($days));
		}elseif(strtotime($sescrowdfunding->publish_date) < time() && empty($sescrowdfunding->show_start_time)) {
			$result['campaign_expiration_label'] = $this->view->translate("Expired");
		}
		/*------------------------ progress bar detail ---------------------------- */
		$result['progressbar_background_color'] = $progressBackgroundColor;
		$result['progressbar_fill_color'] = $progressFillColor;
		$result['gain_amount'] = $totalGainAmountwithCu;
		$result['donor_count'] = $getDoners;
		$result['total_amount'] = $totalAmount;
		$buttonCounter = 0;
		if($viewer_id && $viewer_id != $sescrowdfunding->owner_id && !empty($sescrowdfunding->show_start_time)) {
			$result['button']['name'] = 'donate';
			$result['button']['label'] = $this->view->translate("DONATE");
		}elseif($viewer_id && strtotime($sescrowdfunding->publish_date) > time() && $viewer_id != $sescrowdfunding->owner_id) { 
			$result['button']['name'] = 'donate';
			$result['button']['label'] = $this->view->translate("DONATE");
		}else{
			$result['button']['name'] = 'complete';
			$result['button']['label'] = $this->view->translate("Successfully Completed");
		}
		
		$enableShare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.enable.sharing', 1);
		if($enableShare){
			$result["share"]["imageUrl"] = $this->getBaseUrl(false, $sescrowdfunding->getPhotoUrl());
			$result["share"]["url"] = $this->getBaseUrl(false,$sescrowdfunding->getHref());
			$result["share"]["title"] = $sescrowdfunding->getTitle();
			$result["share"]["description"] = strip_tags($sescrowdfunding->getDescription());
			$result["share"]["setting"] = $enableShare;
			$result["share"]['urlParams'] = array(
				"type" => $sescrowdfunding->getType(),
				"id" => $sescrowdfunding->getIdentity()
			);
		}
		$canComment =  $sescrowdfunding->authorization()->isAllowed($viewer, 'comment');
		$LikeStatus = Engine_Api::_()->sescrowdfunding()->getLikeStatusCrowdfunding($sescrowdfunding->crowdfunding_id, $sescrowdfunding->getType());
		if($canComment){
				$result['is_content_like'] = $LikeStatus?true:false;
		}
		$buttoncounter = 0;
		if($sescrowdfunding->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit')) {
            $data['buttons'][$buttoncounter]['name'] = 'edit';
            $data['buttons'][$buttoncounter]['label'] = $this->view->translate('Edit');
            $buttoncounter++;
        }
        if($sescrowdfunding->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')) {
            $data['buttons'][$buttoncounter]['name'] = 'delete';
            $data['buttons'][$buttoncounter]['label'] = $this->view->translate('Delete');
            $buttoncounter++;
        }
        if($sescrowdfunding->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'contactinfo')){
            $data['buttons'][$buttoncounter]['name'] = 'updatecontactinfo';
            $data['buttons'][$buttoncounter]['label'] = $this->view->translate('Update Contect Information');
            $buttoncounter++;
        }
        if($sescrowdfunding->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'seo')){
            $data['buttons'][$buttoncounter]['name'] = 'seo';
            $data['buttons'][$buttoncounter]['label'] = $this->view->translate('SEO');
            $buttoncounter++;
        }
        $authorization = Engine_Api::_()->authorization();
        if($authorization->isAllowed('crowdfunding', $levelId, 'auth_insightrpt') && $viewer_id && $viewer_id != $sescrowdfunding->owner_id){
            $data['buttons'][$buttoncounter]['name'] = 'report';
            $data['buttons'][$buttoncounter]['label'] = $this->view->translate("Report");

        }

		$tabcounter = 0;
		$data['menus'][$tabcounter]['name'] = 'updates';
		$data['menus'][$tabcounter]['label'] = $this->view->translate('Updates');
		$tabcounter++;
		$data['campaign'] = $result;
		$data['menus'][$tabcounter]['name'] = 'description';
		$data['menus'][$tabcounter]['label'] = $this->view->translate('Description');
		$tabcounter++;
		$data['menus'][$tabcounter]['name'] = 'overview';
		$data['menus'][$tabcounter]['label'] = $this->view->translate('Overview');
		$tabcounter++;
		$data['menus'][$tabcounter]['name'] = 'aboutme';
		$data['menus'][$tabcounter]['label'] = $this->view->translate('About Me');
		$tabcounter++;
		$values = array('crowdfunding_id' => $sescrowdfunding->crowdfunding_id,'order' => 'recent', 'fetchAll' => true);
		
    $donors = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getAllDoners($values);
		if(count($donors)>0){
			$data['menus'][$tabcounter]['name'] = 'donors';
			$data['menus'][$tabcounter]['label'] = $this->view->translate('Donors');
			$tabcounter++;
		}
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.enable.location', 1)) {
      $data['menus'][$tabcounter]['name'] = 'map';
			$data['menus'][$tabcounter]['label'] = $this->view->translate('Map');
			$tabcounter++;
    }
		$announcements = Engine_Api::_()->getDbTable('announcements', 'sescrowdfunding')->getCrowdfundingAnnouncementPaginator(array('crowdfunding_id' => $sescrowdfunding->crowdfunding_id));
		
		if ($announcements->getTotalItemCount() >0){
			$data['menus'][$tabcounter]['name'] = 'announcements';
			$data['menus'][$tabcounter]['label'] = $this->view->translate('Announcements');
			$tabcounter++;
		}
		
		$rewards = Engine_Api::_()->getDbTable('rewards', 'sescrowdfunding')->getCrowdfundingRewardPaginator(array('crowdfunding_id' => $sescrowdfunding->crowdfunding_id));
		
		if ($rewards->getTotalItemCount() > 0){
			$data['menus'][$tabcounter]['name'] = 'rewards';
			$data['menus'][$tabcounter]['label'] = $this->view->translate('Rewards');
			$tabcounter++;
		}



        $dashTable = Engine_Api::_()->getDbtable('dashboards', 'sescrowdfunding');
        $authorization = Engine_Api::_()->authorization();
        $manage_crowdfunding_albums = $dashTable->getDashboardsItems(array('type' => 'manage_crowdfunding_albums'));
        $levelId = Engine_Api::_()->getItem('user',$sescrowdfunding->owner_id)->level_id;
        if(!empty($manage_crowdfunding_albums) && $manage_crowdfunding_albums->enabled && $authorization->isAllowed('crowdfunding', $levelId, 'album')){
            $buttonCounter = 0;
            $data['button']['name'] = 'addphotos';
            $data['button']['label'] = $this->view->translate('Add More Photos');
        }

        $storage = Engine_Api::_()->storage();
        $table = Engine_Api::_()->getDbTable('photos', 'sescrowdfunding');
        $select = $table->select()->where('crowdfunding_id =?', $sescrowdfunding->getIdentity())->order('photo_id DESC');
        $photos = $table->fetchAll($select);
        $counter = 0;
        foreach($photos as $image){
            $data['slide_image'][$counter] =   $image->toArray();
            $data['slide_image'][$counter]['images'] = $this->getBaseUrl(false,$storage->get($image->file_id, '')->getPhotoUrl());
            $optionCounter = 0;
            if(!empty($manage_crowdfunding_albums) && $manage_crowdfunding_albums->enabled && $authorization->isAllowed('crowdfunding', $levelId, 'album')){
                $data['slide_image'][$counter]['options'][$optionCounter]['name'] = 'delete';
                $data['slide_image'][$counter]['options'][$optionCounter]['label'] = $this->view->translate('Delete');
            }
            $counter++;
        }





		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data)));
	}
    public function mainuploadphotoAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $crowed = Engine_Api::_()->core()->getSubject();
        if (!$crowed)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This Crowdfunding does not exist.'), 'result' => array()));
        $photo = $crowed->photo_id;
        if (isset($_FILES['image']))
            $data = $_FILES['image'];
        $photo_id = $this->_getParam('photo_id',0);
        if($photo_id){
            $data = Engine_Api::_()->getItem('album_photo',$photo_id);
        }
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $crowed->setPhoto($data, '', 'profile');
        if ($photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $photo);
            $im->delete();
        }
        $file = array('main' => $crowed->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => $this->view->translate('Successfully photo uploaded.')));
    }
    public function removephotoAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        } else {
            $crowed = Engine_Api::_()->core()->getSubject();
        }
        if (!$crowed)
            $crowed = Engine_Api::_()->getItem('crowdfunding', $this->_getparam('crowdfunding_id', null));
        if (!$crowed)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Crowdfunding does not exists.'), 'result' => array()));
        if (isset($crowed->photo_id) && $crowed->photo_id > 0) {
            $crowed->photo_id = 0;
            $crowed->save();
        }
        $file = array('main' => $crowed->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully photo deleted.'), 'images' => $file));
    }
	public function overviewAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		
		$editOverview = $crowdfunding->authorization()->isAllowed($viewer, 'edit');
		if (!$crowdfunding && !$editOverview && (!$crowdfunding->overview || is_null($crowdfunding->overview))) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		$result = $crowdfunding->toArray();
		$counter = 0;
		if($editOverview) { 
			if($crowdfunding->overview){
				$result['options']['name'] = $overviewicon = "edit";
				$result['options']['label'] = $overviewtext = $this->view->translate("Change Overview");
			}else{
				$result['options']['name'] = $overviewicon = "sesbasic_icon_add";
				$result['options']['label'] = $overviewtext = $this->view->translate("Add Overview");
			} 
		}

		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
	}
	
	public function updateOverviewAction(){
   
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		
    // Create form
    $form = new Sescrowdfunding_Form_Dashboard_Overview();
    $form->populate($crowdfunding->toArray());
    if($this->_getParam('getForm')){
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding'));
		}
    //Check post/form
    //If not post or form not valid, return
    if(!$this->getRequest()->isPost())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    if(!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
    $db = Engine_Api::_()->getDbTable('crowdfundings', 'sescrowdfunding')->getAdapter();
    $db->beginTransaction();
    try {
      $crowdfunding->setFromArray($_POST);
      $crowdfunding->save();
      $db->commit();
      $form->addNotice('Changes saved.');
    } catch (Exception $e) {
      $db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Changes saved.','crowdfunding_id' => $crowdfunding->crowdfunding_id)));
  }
	
	public function donorAction(){
		
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
    $recent = 'recent';
    $crowdfunding_id =  $crowdfunding->crowdfunding_id;
    $paginator = Engine_Api::_()->getDbTable('orders', 'sescrowdfunding')->getAllDoners(array('crowdfunding_id' => $crowdfunding_id, 'order' => $recent));
    $paginator->setItemCountPerPage($this->_getParam('limit','10'));
    $paginator->setCurrentPageNumber($this->_getParam('page','1'));
		$counter = 0;
		$result = array();
		
		
		foreach($paginator as $donor){
			$user = Engine_Api::_()->getItem('user', $donor->user_id);
			//$result[$counter] = $user->toArray();
			
			$result[$counter]['donor_title'] = $user->getTitle();
			$result[$counter]['user_id'] = $user->getIdentity();
			if($user->photo_id) { 
				$result[$counter]['donor_photo'] = $this->getBaseUrl(false, $user->getPhotoUrl('thumb.profile')); 
			} else { 
				$result[$counter]['donor_photo'] = $photo = $this->getBaseUrl(false, '/application/modules/User/externals/images/nophoto_user_thumb_profile.png'); 
			}
			$currency = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();
			$result[$counter]['total_donated_amount'] = $totalGainAmountwithCu = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($donor->total_amount,$currency);
			
			$result[$counter]['crowdfunding_creation_date'] = $donor->creation_date;
			
			$counter++;
		}
		$data['donors'] = $result;
		
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}

	public function rewardAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		
		$paginator = Engine_Api::_()->getDbTable('rewards', 'sescrowdfunding')->getCrowdfundingRewardPaginator(array('crowdfunding_id' => $crowdfunding->crowdfunding_id));

		$paginator->setItemCountPerPage($this->_getParam('limit_data', 10));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
		
		$result = array();
		$counter = 0 ;
		$currency = Engine_Api::_()->sescrowdfunding()->getCurrentCurrency();
		foreach($paginator as $reward){
			$result[$counter] = $reward->toArray();
			$result[$counter]['minimum_donation_amount'] = $totalAmount = Engine_Api::_()->sescrowdfunding()->getCurrencyPrice($reward->doner_amount, $currency);
			$photo = Engine_Api::_()->storage()->get($reward->photo_id, '');
      if($photo) {
				$result[$counter]['reward_photo'] = $this->getBaseUrl(false,$photo->getPhotoUrl());
      }
			
			$counter++;
		}
		$data['rewards'] = $result;
		
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
	
	public function likeAction() {

    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

    $type = 'crowdfunding';
    $dbTable = 'crowdfundings';
    $resorces_id = 'crowdfunding_id';
    $notificationType = 'liked';
    $actionType = 'sescrowdfunding_like_crowdfunding';

    $item_id = $this->_getParam('id',$this->_getParam('crowdfunding_id'));
    if (intval($item_id) == 0) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $itemTable = Engine_Api::_()->getDbtable($dbTable, 'sescrowdfunding');
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
				$temp['data']['message'] = $this->view->translate('Successfully Unliked.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }

      //$itemTable->update(array('like_count' => new Zend_Db_Expr('like_count - 1')), array($resorces_id . ' = ?' => $item_id));

      $item = Engine_Api::_()->getItem($type, $item_id);
      $item->like_count--;
      $item->save();
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

        //$itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array($resorces_id . '= ?' => $item_id));
        $item = Engine_Api::_()->getItem($type, $item_id);
        $item->like_count++;
        $item->save();

        //Commit
        $db->commit();
				$temp['data']['message'] = $this->view->translate('Successfully Liked.');
      } catch (Exception $e) {
        $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }

      //Send notification and activity feed work.
      $item = Engine_Api::_()->getItem($type, $item_id);
      $subject = $item;
      $owner = $subject->getOwner();
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
	       $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
	       Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
	       Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
	       $result = $activityTable->fetchRow(array('type =?' => $actionType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));

	      if (!$result) {
          if($subject && empty($subject->title) && $this->_getParam('type') == 'sescrowdfunding_photo') {
            $album_id = $subject->album_id;
            $subject = Engine_Api::_()->getItem('sescrowdfunding_album', $album_id);
          }
	        $action = $activityTable->addActivity($viewer, $subject, $actionType);
	        if ($action)
	          $activityTable->attachActivity($action, $subject);
        }
      }
      $temp['data']['like_count'] = $item->like_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    }
  }
	
	public function descriptionAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		
		$dashTable = Engine_Api::_()->getDbtable('dashboards', 'sescrowdfunding'); 
		$authorization = Engine_Api::_()->authorization();
		$manage_crowdfunding_albums = $dashTable->getDashboardsItems(array('type' => 'manage_crowdfunding_albums'));

		$result['description'] = $crowdfunding->toArray();
		$enableShare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescrowdfunding.enable.sharing', 1);
		if($enableShare){
			$result['description']["share"]["imageUrl"] = $this->getBaseUrl(false, $crowdfunding->getPhotoUrl());
			$result['description']["share"]["url"] = $this->getBaseUrl(false,$crowdfunding->getHref());
			$result['description']["share"]["title"] = $crowdfunding->getTitle();
			$result['description']["share"]["description"] = strip_tags($crowdfunding->getDescription());
			$result['description']["share"]["setting"] = $enableShare;
			$result['description']["share"]['urlParams'] = array(
				"type" => $crowdfunding->getType(),
				"id" => $crowdfunding->getIdentity()
			);
		}
        $levelId = Engine_Api::_()->getItem('user',$crowdfunding->owner_id)->level_id;
        if(!empty($manage_crowdfunding_albums) && $manage_crowdfunding_albums->enabled && $authorization->isAllowed('crowdfunding', $levelId, 'album')){
            $buttonCounter = 0;
            $result['button']['name'] = 'addphotos';
            $result['button']['label'] = $this->view->translate('Add More Photos');
        }

        $storage = Engine_Api::_()->storage();
    $table = Engine_Api::_()->getDbTable('photos', 'sescrowdfunding');
    $select = $table->select()->where('crowdfunding_id =?', $crowdfunding->getIdentity())->order('photo_id DESC');
    $photos = $table->fetchAll($select);
		$counter = 0;
		foreach($photos as $image){
			$result['slide_image'][$counter] =   $image->toArray();
			$result['slide_image'][$counter]['images'] = $this->getBaseUrl(false,$storage->get($image->file_id, '')->getPhotoUrl());
			$optionCounter = 0;
			if(!empty($manage_crowdfunding_albums) && $manage_crowdfunding_albums->enabled && $authorization->isAllowed('crowdfunding', $levelId, 'album')){
				$result['slide_image'][$counter]['options'][$optionCounter]['name'] = 'delete';
				$result['slide_image'][$counter]['options'][$optionCounter]['label'] = $this->view->translate('Delete');
			}
			$counter++;
		}
		
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
	}
	
	public function uploadAction() {
    $crowdfunding_id = $this->_getParam('crowdfunding_id', null);
    $sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $this->_getParam('crowdfunding_id', null));
        $form = new Sescrowdfunding_Form_Dashboard_UploadPhotos();
        if( !$form->isValid($this->getRequest()->getPost()))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));


    $db = Engine_Api::_()->getItemTable('sescrowdfunding_album')->getAdapter();
    $db->beginTransaction();
    try {
        foreach ($_FILES['attachmentImage']['name'] as $key=>$name) {
            $file = array();
            $file['name'] = $_FILES['attachmentImage']['name'][$key];
            $file['tmp_name'] = $_FILES['attachmentImage']['tmp_name'][$key];
            $file['size'] = $_FILES['attachmentImage']['size'][$key];
            $file['error'] = $_FILES['attachmentImage']['error'][$key];
            $file['type'] = $_FILES['attachmentImage']['type'][$key];
                $_POST['file'] = $this->uploadPhotoAction($sescrowdfunding,$file);
            //Get form
            $album_id = Engine_Api::_()->getDbtable('albums', 'sescrowdfunding')->getAlbumId($crowdfunding_id);
            if (null !== $album_id && $album_id) {
                $form->populate(array('album' => $album_id, 'photo_id' => $_POST['file']));
            }
            $form->saveValues();
            $db->commit();
        }
    }
    catch(Exception $e ) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Changes saved.','crowdfunding_id' => $sescrowdfunding->crowdfunding_id)));
  }
	
	public function uploadPhotoAction($sescrowdfunding,$file) {

    if(!$this->_helper->requireUser()->checkRequire()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Max file size limit exceeded (probably).'), 'result' => array()));
    }

    if( !$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid request method'), 'result' => array()));
    }

    if(empty($_GET['isURL']) || $_GET['isURL'] == 'false'){
			$isURL = false;
			$values = $this->getRequest()->getPost();
			if (empty($file) && !isset($file)) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('No file'), 'result' => array()));
			}

			if (!isset($file) || !is_uploaded_file($file['tmp_name'])) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid Upload'), 'result' => array()));
			}
			$uploadSource = $file;
    } else {
        $uploadSource = $_POST['attachmentImage'];
        $isURL = true;
    }


    $db = Engine_Api::_()->getDbtable('photos', 'sescrowdfunding')->getAdapter();
    $db->beginTransaction();

    try {

      $viewer = Engine_Api::_()->user()->getViewer();
      $photoTable = Engine_Api::_()->getDbtable('photos', 'sescrowdfunding');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array('user_id' => $viewer->getIdentity()));
      $photo->save();

      $photo->order = $photo->photo_id;
      $photo->setPhoto($uploadSource, $isURL);
      $photo->save();

      $this->view->status = true;
      $this->view->name = $file['name'];
      $this->view->photo_id = $photo->photo_id;
      $photo->crowdfunding_id = $sescrowdfunding->crowdfunding_id;
      $photo->save();

      $db->commit();
      return $photo->photo_id;

    } catch( Sescrowdfunding_Model_Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));

    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('An error occurred.'), 'result' => array()));
    }
  }
	
	public function removeAction() {
		if(!$this->_getParam('photo_id'))
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		//GET PHOTO ID AND ITEM
		$photo_id = (int) $this->_getParam('photo_id');
		$photo = Engine_Api::_()->getItem('sescrowdfunding_photo', $photo_id);
		$db = Engine_Api::_()->getDbTable('photos', 'sescrowdfunding')->getAdapter();
		$db->beginTransaction();
		try {
				$photo->delete();
				$db->commit();
		} catch (Exception $e) {
				$db->rollBack();
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Changes saved.','crowdfunding_id' => $crowdfunding->crowdfunding_id)));
	}

	public function announcementAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		$paginator = Engine_Api::_()->getDbTable('announcements', 'sescrowdfunding')->getCrowdfundingAnnouncementPaginator(array('crowdfunding_id' => $crowdfunding->crowdfunding_id));
		$paginator->setItemCountPerPage($this->_getParam('limit_data', 5));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$result = array();
		$dashTable = Engine_Api::_()->getDbtable('dashboards', 'sescrowdfunding'); 
		$authorization = Engine_Api::_()->authorization();
		$levelId = Engine_Api::_()->getItem('user',$crowdfunding->owner_id)->level_id;
		$announce = $dashTable->getDashboardsItems(array('type' => 'announcement'));
		if(@$announce->enabled && $authorization->isAllowed('crowdfunding', $levelId, 'auth_announce')){
			$buttonCounter = 0;
			$result['button']['name'] = 'postanouncement';
			$result['button']['label'] = $this->view->translate('Post New Announcement');
		}
		
		$counter = 0;
		foreach($paginator as $announcement){
			$result['announcements'][$counter] = $announcement->toArray();
			if(@$announce->enabled && $authorization->isAllowed('crowdfunding', $levelId, 'auth_announce')){
				$optioncounter =0;
				$result['announcements'][$counter]['options'][$optioncounter]['name'] = 'edit';
				$result['announcements'][$counter]['options'][$optioncounter]['label'] = $this->view->translate('edit');
				$optioncounter++;
				$result['announcements'][$counter]['options'][$optioncounter]['name'] = 'delete';
				$result['announcements'][$counter]['options'][$optioncounter]['label'] = $this->view->translate('Delete');
			}
			$counter++;
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
	}
	
	public function postAnnouncementAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		$announcement = Engine_Api::_()->getItem('sescrowdfunding_announcement', $this->_getParam('id'));
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
    $form = new Sescrowdfunding_Form_Dashboard_Postannouncement();
    if($this->_getParam('getForm')){
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding'));
		}
    //Check post/form
    //If not post or form not valid, return
    if(!$this->getRequest()->isPost())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    if(!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
    $announcementTable = Engine_Api::_()->getDbTable('announcements', 'sescrowdfunding');
    $db = $announcementTable->getAdapter();
    $db->beginTransaction();
    $viewer = Engine_Api::_()->user()->getViewer();
    try {
      $announcement = $announcementTable->createRow();
      $announcement->setFromArray(array_merge(array(
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
          'crowdfunding_id' => $crowdfunding->crowdfunding_id), $form->getValues()));
      $announcement->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' =>array()));
    }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('success_message' => 'Changes saved.','announcement_id' => $announcement->getIdentity())));
  }
	
	public function editAnnouncementAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		$announcement = Engine_Api::_()->getItem('sescrowdfunding_announcement', $this->_getParam('id'));
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
    $form = new Sescrowdfunding_Form_Dashboard_Editannouncement();
    $form->title->setValue($announcement->title);
    $form->body->setValue($announcement->body);
    if($this->_getParam('getForm')){
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding'));
		}
    //Check post/form
    //If not post or form not valid, return
    if(!$this->getRequest()->isPost())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    if(!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
    $announcement->title = $_POST['title'];
    $announcement->body = $_POST['body'];
    $announcement->save();
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Changes saved.','announcement_id' => $announcement->getIdentity())));
  }

  public function deleteAnnouncementAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		$announcement = Engine_Api::_()->getItem('sescrowdfunding_announcement', $this->_getParam('id'));
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
    //$this->view->form = $form = new Sescrowdfunding_Form_Dashboard_Deleteannouncement();
    if (!$this->getRequest()->isPost())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    $announcement->delete();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Changes saved.','crowdfunding_id' => $crowdfunding->crowdfunding_id)));
  }
	
	public function postRewardAction() {
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
    
    $form = new Sescrowdfunding_Form_Dashboard_Postreward();
    if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost()))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		
    $rewardTable = Engine_Api::_()->getDbTable('rewards', 'sescrowdfunding');
    $db = $rewardTable->getAdapter();
    $db->beginTransaction();
    $viewer = Engine_Api::_()->user()->getViewer();
    try {
      $reward = $rewardTable->createRow();
      $values = $form->getValues();

      $reward->setFromArray(array_merge(array(
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
          'crowdfunding_id' => $crowdfunding->crowdfunding_id), $values));
      $reward->save();

      if(!empty($_FILES['photo_file']['name'])) {
        $file_ext = pathinfo($_FILES['photo_file']['name']);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile($form->photo_file, array(
          'parent_id' => $reward->getIdentity(),
          'parent_type' => $reward->getType(),
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        ));
        // Remove temporary file
        @unlink($file['tmp_name']);
        $reward->photo_id = $storageObject->file_id;
        $reward->save();
      }

      $db->commit();
			$viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$crowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$crowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
      // Redirect
      $this->_redirectCustom(array('route' => 'sescrowdfunding_dashboard', 'action' => 'rewards', 'crowdfunding_id' => $crowdfunding->custom_url));
    } catch (Exception $e) {
      $db->rollBack();
    }
  }

	public function createAction() {

    //Auth Check
    if( !$this->_helper->requireUser()->isValid() || !$this->_helper->requireAuth()->setAuthParams('crowdfunding', null, 'create')->isValid()) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

		$viewer = Engine_Api::_()->user()->getViewer();
    $session = new Zend_Session_Namespace();
		if(empty($_POST))
      unset($session->album_id);

    if (isset($sescrowdfunding->category_id) && $sescrowdfunding->category_id != 0) {
      $category_id = $sescrowdfunding->category_id;
    } else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
      $category_id = $_POST['category_id'];
    else
      $category_id = 0;

    if (isset($sescrowdfunding->subsubcat_id) && $sescrowdfunding->subsubcat_id != 0) {
      $subsubcat_id = $sescrowdfunding->subsubcat_id;
    } else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
      $subsubcat_id = $_POST['subsubcat_id'];
    else
      $subsubcat_id = 0;

    if (isset($sescrowdfunding->subcat_id) && $sescrowdfunding->subcat_id != 0) {
      $subcat_id = $sescrowdfunding->subcat_id;
    } else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
      $subcat_id = $_POST['subcat_id'];
    else
      $subcat_id = 0;

    

    //$paginator = Engine_Api::_()->getDbtable('crowdfundings', 'sescrowdfunding')->getSescrowdfundingsPaginator($values);

    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sescrowdfunding', 'max');
    $current_count = 0; //$paginator->getTotalItemCount();

    $categories = Engine_Api::_()->getDbtable('categories', 'sescrowdfunding')->getCategoriesAssoc();

    $form = new Sescrowdfunding_Form_Create(array('defaultProfileId' => 1));
		$form->removeElement('lat');
		$form->removeElement('map-canvas');
		$form->removeElement('ses_location');
		$form->removeElement('lng');
		$form->removeElement('submit_check');
		
		
		if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding'));
		}
    //If not post or form not valid, return
    if(!$this->getRequest()->isPost())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    if(!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}

    //Check custom url
    if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
      $custom_url = Engine_Api::_()->getDbtable('crowdfundings', 'sescrowdfunding')->checkCustomUrl($_POST['custom_url']);
      if ($custom_url) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Custom Url is not available. Please select another URL.'), 'result' => array()));
      }
    }

    //Process
    $table = Engine_Api::_()->getDbTable('crowdfundings', 'sescrowdfunding');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {

      $values = $form->getValues();
      $values['owner_id'] = $viewer->getIdentity();
      //Create
      $sescrowdfunding = $table->createRow();
      if (is_null($values['subsubcat_id']))
        $values['subsubcat_id'] = 0;
      if (is_null($values['subcat_id']))
        $values['subcat_id'] = 0;
      $values['crowdfunding_contact_name'] = $viewer->getTitle();
      $values['crowdfunding_contact_email'] = $viewer->email;
      $sescrowdfunding->setFromArray($values);

      //Set photo
      if( !empty($values['photo_file']) ) {
        $sescrowdfunding->setPhoto($form->photo_file);
      }
			if(isset($_POST['start_date']) && $_POST['start_date'] != '') {
				$starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
				$sescrowdfunding->publish_date =$starttime;
			}

			if(isset($_POST['start_date']) && $viewer->timezone && $_POST['start_date'] != ''){
				//Convert Time Zone
				$oldTz = date_default_timezone_get();
				date_default_timezone_set($viewer->timezone);
				$start = strtotime($_POST['start_date'].' '.$_POST['start_time']);
				date_default_timezone_set($oldTz);
				$sescrowdfunding->publish_date = date('Y-m-d H:i:s', $start);
			} else {
				$sescrowdfunding->publish_date = date('Y-m-d H:i:s',strtotime("-2 minutes", time()));
			}

      $sescrowdfunding->save();
      $crowdfunding_id = $sescrowdfunding->crowdfunding_id;

      // Custom url work
      if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
        $sescrowdfunding->custom_url = $_POST['custom_url'];
      else
        $sescrowdfunding->custom_url = $sescrowdfunding->crowdfunding_id;

      $sescrowdfunding->save();

      //Location work
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
        Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $crowdfunding_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","crowdfunding")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }

      //Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }
      if (!Engine_Api::_()->authorization()->getPermission($viewer, 'crowdfunding', 'crwdapprove')) {
          $sescrowdfunding->approved = 0;
          $sescrowdfunding->save();
      }
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $videoMax = array_search(@$values['auth_video'], $roles);
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($sescrowdfunding, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($sescrowdfunding, $role, 'comment', ($i <= $commentMax));

        $auth->setAllowed($sescrowdfunding, $role, 'video', ($i <= $videoMax));
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $sescrowdfunding->save();
      $sescrowdfunding->tags()->addTagMaps($viewer, $tags);

        //Add fields
        $customfieldform = $form->getSubForm('fields');
        if ($customfieldform) {
            $customfieldform->setItem($sescrowdfunding);
            $customfieldform->saveValues();
        }

      $session = new Zend_Session_Namespace();

      if(!empty($session->album_id)) {

				$album_id = $session->album_id;
				if(isset($crowdfunding_id) && isset($sescrowdfunding->title)) {

					Engine_Api::_()->getDbTable('albums', 'sescrowdfunding')->update(array('crowdfunding_id' => $crowdfunding_id,'owner_id' => $viewer->getIdentity(),'title' => $sescrowdfunding->title), array('album_id = ?' => $album_id));
					if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
						Engine_Api::_()->getDbTable('albums', 'sescrowdfunding')->update(array('photo_id' => $_POST['cover']), array('album_id = ?' => $album_id));
					}

					Engine_Api::_()->getDbTable('photos', 'sescrowdfunding')->update(array('crowdfunding_id' => $crowdfunding_id), array('album_id = ?' => $album_id));
					unset($session->album_id);
				}
      }

      //Add activity only if sescrowdfunding is published
      //if( $values['draft'] == 0 && (!$sescrowdfunding->publish_date || strtotime($sescrowdfunding->publish_date) <= time())) {
      if( $values['draft'] == 0) {

        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sescrowdfunding, 'sescrowdfunding_create');
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sescrowdfunding);
        }

      	$sescrowdfunding->draft = 0;
      	$sescrowdfunding->save();
      }
      //Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully created','crowdfunding_id' => $sescrowdfunding->crowdfunding_id)));
  }
	
	public function deleteAction() {

    $sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $this->_getParam('crowdfunding_id'));
		
    if( !$this->_helper->requireAuth()->setAuthParams($sescrowdfunding, null, 'delete')->isValid()) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    //$form = new Sescrowdfunding_Form_Delete();

    if( !$sescrowdfunding ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Sescrowdfunding entry doesn\'t exist or not authorized to delete'), 'result' => array()));
      
    }
    if( !$this->getRequest()->isPost() ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid request method'), 'result' => array()));
    }
    $db = $sescrowdfunding->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->sescrowdfunding()->deleteCrowdfunding($sescrowdfunding);;
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Your crowdfunding entry has been deleted.')));
  }
	
	public function rateAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();

    $rating = $this->_getParam('rating');
    $crowdfunding_id =  $this->_getParam('crowdfunding_id');
		if(!$crowdfunding_id){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
    $table = Engine_Api::_()->getDbtable('ratings', 'sescrowdfunding');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      Engine_Api::_()->sescrowdfunding()->setRating($crowdfunding_id, $user_id, $rating);
      $crowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
      $crowdfunding->rating = Engine_Api::_()->sescrowdfunding()->getRating($crowdfunding->getIdentity());
      $crowdfunding->save();
      $db->commit();
    }catch( Exception $e ){
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    $total = Engine_Api::_()->sescrowdfunding()->ratingCount($crowdfunding->getIdentity());

    $data = array(
      'total' => $total,
      'rating' => $rating,
    );
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $data));
  }
	
	public function editAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$this->_helper->requireSubject()->isValid() || !$this->_helper->requireAuth()->setAuthParams('crowdfunding', $viewer, 'edit')->isValid()) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			
    if( !$this->_helper->requireUser()->isValid() ) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

    $viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$sescrowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$sescrowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}

    if (isset($sescrowdfunding->category_id) && $sescrowdfunding->category_id != 0)
      $category_id = $sescrowdfunding->category_id;
    else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
      $category_id = $_POST['category_id'];
    else
      $category_id = 0;

    if (isset($sescrowdfunding->subsubcat_id) && $sescrowdfunding->subsubcat_id != 0)
      $subsubcat_id = $sescrowdfunding->subsubcat_id;
    else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
      $subsubcat_id = $_POST['subsubcat_id'];
    else
      $subsubcat_id = 0;

    if (isset($sescrowdfunding->subcat_id) && $sescrowdfunding->subcat_id != 0)
      $subcat_id = $sescrowdfunding->subcat_id;
    else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
      $subcat_id = $_POST['subcat_id'];
    else
      $subcat_id = 0;



//     if( !Engine_Api::_()->core()->hasSubject('crowdfunding') )
//       Engine_Api::_()->core()->setSubject($sescrowdfunding);

    //Get navigation
    $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescrowdfunding_main');

    $categories = Engine_Api::_()->getDbtable('categories', 'sescrowdfunding')->getCategoriesAssoc();

    //Prepare form
    $form = new Sescrowdfunding_Form_Edit();

    //Populate form
    $form->populate($sescrowdfunding->toArray());

    $latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('sescrowdfunding',$sescrowdfunding->crowdfunding_id);
    if($latLng) {
      if($form->getElement('lat'))
        $form->getElement('lat')->setValue($latLng->lat);
      if($form->getElement('lng'))
        $form->getElement('lng')->setValue($latLng->lng);
    }

    if($form->getElement('location'))
      $form->getElement('location')->setValue($sescrowdfunding->location);

		if($form->getElement('category_id'))
      $form->getElement('category_id')->setValue($sescrowdfunding->category_id);

    $tagStr = '';
    foreach( $sescrowdfunding->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      if( '' !== $tagStr ) $tagStr .= ', ';
      $tagStr .= $tag->text;
    }

    $form->populate(array(
      'tags' => $tagStr,
    ));
    
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    foreach( $roles as $role ) {
      if ($form->auth_view){
        if( $auth->isAllowed($sescrowdfunding, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($sescrowdfunding, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }

        if (isset($form->auth_video->options[$role]) && $auth->isAllowed($sescrowdfunding, $role, 'video'))
            $form->auth_video->setValue($role);
    }

    //Hide status change if it has been already published
    if( $sescrowdfunding->draft == "0" )
      $form->removeElement('draft');
		$form->removeElement('lat');
		$form->removeElement('map-canvas');
		$form->removeElement('ses_location');
		$form->removeElement('lng');
		$form->removeElement('submit_check');
		
		if($this->_getParam('getForm')){
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding'));
		}
    //Check post/form
    //If not post or form not valid, return
    if(!$this->getRequest()->isPost())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    if(!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}

    //Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {

      $values = $form->getValues();

      $sescrowdfunding->setFromArray($values);
      $sescrowdfunding->modified_date = date('Y-m-d H:i:s');

			if(isset($_POST['start_date']) && $_POST['start_date'] != ''){
				$starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
      	$sescrowdfunding->publish_date =$starttime;
			}
			if(empty($sescrowdfunding->crowdfunding_contact_name))
        $sescrowdfunding->crowdfunding_contact_name = $viewer->getTitle();
      if(empty($sescrowdfunding->crowdfunding_contact_email))
        $sescrowdfunding->crowdfunding_contact_email = $viewer->email;

      $sescrowdfunding->save();

      unset($_POST['title']);
      unset($_POST['tags']);
      unset($_POST['category_id']);
      unset($_POST['subcat_id']);
      unset($_POST['MAX_FILE_SIZE']);
      unset($_POST['body']);
      unset($_POST['search']);
      unset($_POST['execute']);
      unset($_POST['token']);
      unset($_POST['submit']);

      //Location work
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
        Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $sescrowdfunding->crowdfunding_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","crowdfunding") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }

      if(isset($values['draft']) && !$values['draft']) {
        $currentDate = date('Y-m-d H:i:s');
        if($sescrowdfunding->publish_date < $currentDate) {
          $sescrowdfunding->publish_date = $currentDate;
          $sescrowdfunding->save();
        }
      }

      //Auth
      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $videoMax = array_search(@$values['auth_video'], $roles);
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($sescrowdfunding, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($sescrowdfunding, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($sescrowdfunding, $role, 'video', ($i <= $videoMax));
      }

      //Handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $sescrowdfunding->tags()->setTagMaps($viewer, $tags);

			//Upload main image
			if(isset($_FILES['photo_file']) && $_FILES['photo_file']['name'] != ''){
				$photo_id = $sescrowdfunding->setPhoto($form->photo_file,'direct');
			}

      //Insert new activity if sescrowdfunding is just getting published
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($sescrowdfunding);
      if( count($action->toArray()) <= 0 && @$values['draft'] == '0' && (!$sescrowdfunding->publish_date || strtotime($sescrowdfunding->publish_date) <= time())) {

        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sescrowdfunding, 'sescrowdfunding_create');
        if( $action != null ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sescrowdfunding);
        }

        $sescrowdfunding->draft = 1;
      	$sescrowdfunding->save();
      }

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($sescrowdfunding) as $action ) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully Edited','crowdfunding_id' => $sescrowdfunding->crowdfunding_id)));
  }

	public function contactInformationAction() {
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$sescrowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$sescrowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		$crowdfunding = $sescrowdfunding;
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $crowdfunding->isOwner($viewer)))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    //Create form
    $form = new Sescrowdfunding_Form_Dashboard_Contactinformation();
    $form->populate($crowdfunding->toArray());
		if($this->_getParam('getForm')){
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding'));
		}
    //Check post/form
    //If not post or form not valid, return
    if(!$this->getRequest()->isPost())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    if(!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
    $db = Engine_Api::_()->getDbtable('crowdfundings', 'sescrowdfunding')->getAdapter();
    $db->beginTransaction();
    try {
      $crowdfunding->crowdfunding_contact_name = isset($_POST['crowdfunding_contact_name']) ? $_POST['crowdfunding_contact_name'] : '';

      $crowdfunding->crowdfunding_contact_email = isset($_POST['crowdfunding_contact_email']) ? $_POST['crowdfunding_contact_email'] : '';

      $crowdfunding->crowdfunding_contact_country = isset($_POST['crowdfunding_contact_country']) ? $_POST['crowdfunding_contact_country'] : '';

      $crowdfunding->crowdfunding_contact_state = isset($_POST['crowdfunding_contact_state']) ? $_POST['crowdfunding_contact_state'] : '';

      $crowdfunding->crowdfunding_contact_city = isset($_POST['crowdfunding_contact_city']) ? $_POST['crowdfunding_contact_city'] : '';

      $crowdfunding->crowdfunding_contact_street = isset($_POST['crowdfunding_contact_street']) ? $_POST['crowdfunding_contact_street'] : '';

      $crowdfunding->crowdfunding_contact_phone = isset($_POST['crowdfunding_contact_phone']) ? $_POST['crowdfunding_contact_phone'] : '';

      $crowdfunding->crowdfunding_contact_website = isset($_POST['crowdfunding_contact_website']) ? $_POST['crowdfunding_contact_website'] : '';

      $crowdfunding->crowdfunding_contact_facebook = isset($_POST['crowdfunding_contact_facebook']) ? $_POST['crowdfunding_contact_facebook'] : '';

      $crowdfunding->crowdfunding_contact_twitter = isset($_POST['crowdfunding_contact_twitter']) ? $_POST['crowdfunding_contact_twitter'] : '';

      $crowdfunding->crowdfunding_contact_aboutme = isset($_POST['crowdfunding_contact_aboutme']) ? $_POST['crowdfunding_contact_aboutme'] : '';

      $crowdfunding->save();
      $db->commit();
      $form->addNotice('Your changes have been saved.');
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully Edited','crowdfunding_id' => $crowdfunding->crowdfunding_id)));
  }

	public function aboutmeAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$sescrowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$sescrowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		$result = array();
		$dashTable = Engine_Api::_()->getDbtable('dashboards', 'sescrowdfunding');
		$authorization = Engine_Api::_()->authorization();
		$levelId = Engine_Api::_()->getItem('user',$sescrowdfunding->owner_id)->level_id;
		$contact_information = $dashTable->getDashboardsItems(array('type' => 'contact_information'));
		if(!empty($contact_information) && $contact_information->enabled && $authorization->isAllowed('crowdfunding', $levelId, 'contactinfo')){
			$buttonCounter = 0 ;
			$result['button']['name'] = 'edit';
			$result['button']['label'] = $this->view->translate('Update Contact Information');
		}
		$counter = 0;
		if($sescrowdfunding->crowdfunding_contact_name){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Full Name');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_name;
			$counter++;
		}
		if($sescrowdfunding->crowdfunding_contact_email){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Email:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_email;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_country){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Country:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_country;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_state){
			$result['aboutme'][$counter]['label'] = $this->view->translate('State:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_state;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_city){
			$result['aboutme'][$counter]['label'] = $this->view->translate('City:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_city;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_street){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Street:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_street;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_phone){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Phone:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_phone;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_website){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Website URL:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_website;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_facebook){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Facebook URL:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_facebook;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_twitter){
			$result['aboutme'][$counter]['label'] = $this->view->translate('Twitter URL:');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_twitter;
			$counter++;
		}if($sescrowdfunding->crowdfunding_contact_aboutme){
			$result['aboutme'][$counter]['label'] = $this->view->translate('About Me');
			$result['aboutme'][$counter]['value'] = $sescrowdfunding->crowdfunding_contact_aboutme;
			$counter++;
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
	}
	
	public function seoAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$sescrowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$sescrowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		$crowdfunding = $sescrowdfunding;
    // Create form
    $form = new Sescrowdfunding_Form_Dashboard_Seo();

    $form->populate($crowdfunding->toArray());
    if($this->_getParam('getForm')){
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding'));
		}
    //Check post/form
    //If not post or form not valid, return
    if(!$this->getRequest()->isPost())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    if(!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
    $db = Engine_Api::_()->getDbTable('crowdfundings', 'sescrowdfunding')->getAdapter();
    $db->beginTransaction();
    try {
      $crowdfunding->setFromArray($_POST);
      $crowdfunding->save();
      $db->commit();
      $form->addNotice('Changes saved.');
    } catch (Exception $e) {
      $db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Changes saved.','crowdfunding_id' => $crowdfunding->crowdfunding_id)));
  }
	
	public function donateFormAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		$crowdfunding_id = $this->_getParam('crowdfunding_id', null);
		if (!Engine_Api::_()->core()->hasSubject()) {
			$sescrowdfunding = Engine_Api::_()->getItem('crowdfunding', $crowdfunding_id);
		} else {
			$sescrowdfunding = Engine_Api::_()->core()->getSubject();
		}
		if (!$sescrowdfunding) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}

    $form = new Sesbasic_Form_Delete();
		$form->setTitle('Crowdfunding Donation');
		$form->setDescription('Enter your Donation Amount.');
		$form->removeElement('submit');
		$form->removeElement('cancel');
		$form->addElement('Text', 'price', array(
      'label' => 'Donation Amount',
      'description' => 'Enter your Donation Amount which you want to donate in this crowdfunding',
			'placeholder'=> '0.00',
      'order' => '1',
    ));
		$form->addElement('button', 'button', array(
      'label' => 'Donate',
      'order' => '2',
    ));
		$url = $this->getBaseUrl(false,'sescrowdfunding/order/process/crowdfunding_id/'.$crowdfunding_id.'/gateway_id/2/price/');
		 
		$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
		$this->generateFormFields($formFields, array('resources_type' => 'crowdfunding','payment_url'=>$url));
	}
	
}

