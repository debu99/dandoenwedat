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

class Sescontest_IndexController extends Sesapi_Controller_Action_Standard {

    public function init() {
         $id = $this->_getParam('contest_id', null);
        if ($id) {
            $contest = Engine_Api::_()->getItem('contest', $id);
            if ($contest) {
                Engine_Api::_()->core()->setSubject($contest);
            }
        }
    }
    public function contestEntriesAction(){
		$searchEntry = $this->_getParam('search_filter',null);
      switch ($searchEntry) {
        case 'newest':
          $value['sort'] = 'creation_date';
          break;
        case 'Oldest':
          $value['sort'] = 'old';
          break;
        case 'mostSPViewed':
          $value['sort'] = 'view_count';
          break;
        case 'mostSPvoted':
          $value['sort'] = 'vote_count';
          break;
        case 'mostSPliked':
          $value['sort'] = 'like_count';
          break;
        case 'mostSPcommented':
          $value['sort'] = 'comment_count';
          break;
        case 'mostSPfavorite':
          $value['sort'] = 'favourite_count';
          break;
      }
        $contest_id = $this->_getParam('contest_id', null);
        if (!$contest_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject()) {
            $contest = Engine_Api::_()->getItem('contest', $contest_id);
            Engine_Api::_()->core()->setSubject($contest);
        } else {
            $contest = Engine_Api::_()->core()->getSubject();
        }
        if (!contest)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => $this->view->translate(' There are no results that match your search. Please try again.')));

        $canComment = Engine_Api::_()->authorization()->isAllowed('participant', $this->view->viewer(), 'comment');
        $params = array('contest_id' => $contest->contest_id);
		if($value)
			$params = array_merge($params,$value);
        $paginator = Engine_Api::_()->getDbTable('participants', 'sescontest')->getParticipantPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		
		$filterOptionCounter = 0;
		$filteroption[$filterOptionCounter]['name'] = 'newest';
		$filteroption[$filterOptionCounter]['label'] = $this->view->translate('Newest');
		$filterOptionCounter++;
		$filteroption[$filterOptionCounter]['name'] = 'oldest';
		$filteroption[$filterOptionCounter]['label'] = $this->view->translate('Oldest');
		$filterOptionCounter++;
		$filteroption[$filterOptionCounter]['name'] = 'mostSPvoted';
		$filteroption[$filterOptionCounter]['label'] = $this->view->translate('Most Voted');
		$filterOptionCounter++;
		$filteroption[$filterOptionCounter]['name'] = 'mostSPliked';
		$filteroption[$filterOptionCounter]['label'] = $this->view->translate('Most Liked');
		$filterOptionCounter++;
		$filteroption[$filterOptionCounter]['name'] = 'mostSPcommented';
		$filteroption[$filterOptionCounter]['label'] = $this->view->translate('Most Commented');
		$filterOptionCounter++;
		$filteroption[$filterOptionCounter]['name'] = 'mostSPViewed';
		$filteroption[$filterOptionCounter]['label'] = $this->view->translate('Most Viewed');
		$filterOptionCounter++;
		$filteroption[$filterOptionCounter]['name'] = 'mostSPfavorite';
		$filteroption[$filterOptionCounter]['label'] = $this->view->translate('Most Favourit');
		if($this->getEntries($paginator)){
			$result['options'] = $filteroption;
			$result['entries'] = $this->getEntries($paginator);
		}
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }
    
	public function browseAction() {
        if ($this->_getParam('category', null) == 1) {
            $categoryobject = Engine_Api::_()->getDbTable('categories', 'sescontest')->getCategory(array('countContests' => true, 'limit' => 20, 'fetchAll' => true));
            $categorycounter = 0;
            foreach ($categoryobject as $category) {
                $categorydata[$categorycounter] = $category->toArray();
                $color_icon = $category->colored_icon;
                // it will be apply 
                //   $image = Engine_Api::_()->storage()->get($category->colored_icon)->getPhotoUrl();
                $image = $category->getPhotoUrl('colored_icon');
                if ($image)
                    $categorydata[$categorycounter]['image'] = $this->getbaseurl(false, $image);
                else
                    $categorydata[$categorycounter]['image'] = $this->getbaseurl(false, 'application/modules/Sescontest/externals/images/contest-icon-big.png');
                $categorycounter++;
            }
            $result['category'] = $categorydata;
        }
			$form = new Sescontest_Form_Search();
			$form->populate($_POST);
			$value = $form->getValues();
        if ($this->_getParam('search',null))
            $params['text'] = addslashes($this->_getParam('search',null));
        $params['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
		if($value)
			$params = array_merge($params, $value);
        $search = $this->_getParam('filter', null);
        switch ($search) {
            case 'sescontest_main_endedcontest':
                $params['sort'] = 'ended';
                break;
            case 'sescontest_main_activecontest':
                $params['sort'] = 'ongoing';
                break;
            case 'sescontest_main_comingsooncontest':
                $params['sort'] = 'upcoming';
                break;
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
            case 'mostSPjoined':
                $params['sort'] = 'join_count';
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
        }
		
        $paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')->getContestPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $manage['manage'] = 0;
        $contest = $this->getContests($paginator, $manage);
        if ($contest)
            $result['contests'] = $contest;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
	public function getContests($paginator, $manage) {
        $result = array();
        $counter = 0;
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest_allow_share', 0);
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canComment = Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'create');
        $canJoin = $viewerId ? Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'page_can_join') : 0;
        foreach ($paginator as $contests) {
            $contest = $contests->toArray();
            //echo '<pre>';print_r($contest);die;
            $result[$counter] = $contest;
            $result[$counter]['owner_title'] = $contests->getOwner()->getTitle();
            if ($manage['manage'] == 1) {
                $optioncouter = 0;
                if (Engine_Api::_()->sescontest()->contestPrivacy($contests, 'edit')) {
                    $result[$counter]['options'][$optioncouter]['name'] = 'edit';
                    $result[$counter]['options'][$optioncouter]['label'] = $this->view->translate("Edit Contest");
                    $optioncouter++;
                }
                if ($contests->authorization()->isAllowed($viewer, 'delete')) {
                    $result[$counter]['options'][$optioncouter]['name'] = 'delete';
                    $result[$counter]['options'][$optioncouter]['label'] = $this->view->translate("Delete Contest");
                    $optioncouter++;
                }
                if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescontestpackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontestpackage.enable.package', 0) && (_SESAPI_VERSION_ANDROID >= 2.5 || _SESAPI_VERSION_IOS >= 1.7)) {
                    $package = Engine_Api::_()->getItem('sescontestpackage_package', $contests->package_id);
                    if($package){
                        if (!$package->isFree()) {
                            $transaction = Engine_Api::_()->getDbTable('transactions', 'sescontestpackage')->getItemTransaction(array('order_package_id' => $contests->orderspackage_id, 'contest' => $contests));
                            if ($transaction) {
                                if ($package->isOneTime()) {
                                    if ($package->is_renew_link) {
                                        if (!empty($transaction->expiration_date) && $transaction->expiration_date != '3000-00-00 00:00:00') {
                                            $datediff = strtotime($transaction->expiration_date) - time();
                                            $daysLeft = floor($datediff / (60 * 60 * 24));
                                            if ($daysLeft <= $renew_link_days || strtotime($transaction->expiration_date) <= time()) {
                                                $result[$counter]['options'][$optioncouter]['name'] = 'payment';
                                                $result[$counter]['options'][$optioncouter]['value'] = $this->getBaseUrl(true,$this->view->url(array('contest_id' => $contests->contest_id,'action'=>'index'), 'sescontestpackage_payment', true));
                                                $result[$counter]['options'][$optioncouter]['label'] = $this->view->translate("Reniew Contest Payment");
                                                $optioncouter++;
                                            }
                                        }else {
                                            $result[$counter]['options'][$optioncouter]['name'] = 'package_state';
                                            $result[$counter]['options'][$optioncouter]['value'] = ucwords($transaction->state);
                                            $result[$counter]['options'][$optioncouter]['label'] = $this->view->translate("Payment Status");
                                            $optioncouter++;

                                        }
                                    }
                                }
                            } else {
                                $result[$counter]['options'][$optioncouter]['name'] = 'payment';
                                $result[$counter]['options'][$optioncouter]['value'] = $this->getBaseUrl(true,$this->view->url(array('contest_id' => $contests->contest_id,'action'=>'index'), 'sescontestpackage_payment', true));
                                $result[$counter]['options'][$optioncouter]['label'] = $this->view->translate("Make Payment");
                                $optioncouter++;
                            }
                        }
                    }
                }
                $currentTime = time();
                if (strtotime($contests->starttime) > $currentTime){
                    $result[$counter]['status'] = $this->view->translate('Coming Soon');
                    $result[$counter]['contest_status']['name'] = 'comingsoon';
                    $result[$counter]['contest_status']['label'] = $this->view->translate('Coming Soon');
                    $result[$counter]['contest_status']['value'] = '#ffcc00';
                }
                else if (strtotime($contests->endtime) < $currentTime){
                    $result[$counter]['status'] = $this->view->translate('Contest Expired');
                    $result[$counter]['contest_status']['name'] = 'expired';
                    $result[$counter]['contest_status']['label'] = $this->view->translate('Contest Expired');
                    $result[$counter]['contest_status']['value'] = '#dc1c1c';

                }
                else{
                    $result[$counter]['status'] = $this->view->translate('Contest ongoing');
                    $result[$counter]['contest_status']['name'] = 'ongoing';
                    $result[$counter]['contest_status']['label'] = $this->view->translate('Contest ongoing');
                    $result[$counter]['contest_status']['value'] =  '#17b138';
                }
                
            }
            $defaultParams = array();
            $defaultParams['isSesapi'] = 1;
            $defaultParams['starttime'] = true;
            $defaultParams['endtime'] = true;
            $defaultParams['timezone'] = true;
            $strtime = $this->view->contestStartEndDates($contests, $defaultParams);
            list($result[$counter]['calanderStartTime'], $result[$counter]['calanderEndTime']) = explode('ENDDATE', strip_tags($strtime));
            $likeStatus = Engine_Api::_()->sescontest()->getLikeStatus($contests->contest_id, $contests->getType());
            $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sescontest')->isFavourite(array('resource_id' => $contests->contest_id, 'resource_type' => $contests->getType()));
			$followStatus = Engine_Api::_()->getDbTable('followers', 'sescontest')->isFollow(array('resource_id' => $contests->contest_id,'resource_type' => $contests->getType())); 
            $currentTime = strtotime(date('Y-m-d H:i:s'));
            $participate = Engine_Api::_()->getDbTable('participants', 'sescontest')->hasParticipate($viewer->getIdentity(), $contests->contest_id);
            $countEntries = Engine_Api::_()->getDbTable('participants', 'sescontest')->getContestEntries($contests->contest_id);
            if ($viewer->getIdentity())
                $oldTz = date_default_timezone_get();
            $endtime = strtotime($contests->endtime);
            if ($viewer->getIdentity())
                date_default_timezone_set($viewer->timezone);

            $endtime = strtotime(date('Y-m-d H:i:s', $endtime));
            $currentTime = time();
            $diff = ($endtime - $currentTime);
            $temp = $diff / 86400;
            $dd = floor($temp);
            $temp = 24 * ($temp - $dd);
            $hh = floor($temp);
            $temp = 60 * ($temp - $hh);
            $mm = floor($temp);
            $temp = 60 * ($temp - $mm);
            $ss = floor($temp);
            if ($viewer->getIdentity())
                date_default_timezone_set($oldTz);
            $currentTime = strtotime(date('Y-m-d H:i:s'));
            if (strtotime($contests->endtime) > time()) {
              $timeLeft = strtotime($contests->endtime)- time();
                $result[$counter]['time_left'] = $timeLeft;
                $result[$counter]['current_time'] = time();
                if (isset($participate['can_join']) && isset($participate['show_button'])) {
                    $result[$counter]['join'] = $this->view->translate('JOIN NOW');
                } elseif (isset($participate['show_button'])) {
                    $result[$counter]['join'] = $this->view->translate('JOINED');
                }
            } else {
                $result[$counter]['status'] = $this->view->translate("ENDED");
                $totalVote = Engine_Api::_()->getDbTable('votes', 'sescontest')->getTotalVotes($contests->contest_id);
                if ($totalVote) {
                    if ($totalVote == 1)
                        $result[$counter]['votes'] = $this->view->translate("%s\nVote", $totalVote);
                    else
                        $result[$counter]['votes'] = $this->view->translate("%s\nVotes", $totalVote);
					$result[$counter]['vote_count'] = $totalVote;
                }else {
                    $result[$counter]['votes'] = $this->view->translate("0\nVotes", $totalVote);
					$result[$counter]['vote_count'] = $totalVote;
                }
            }
            if ($countEntries <= 1)
                $result[$counter]['entries'] = $this->view->translate(array('%s Entry', '%s Entry', $countEntries), $this->view->locale()->toNumber($countEntries));
            else
                $result[$counter]['entries'] = $this->view->translate(array('%s Entries', '%s Entries', $countEntries), $this->view->locale()->toNumber($countEntries));
            if ($contests->is_approved) {
                if ($shareType) {
                    $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $contests->getPhotoUrl());
                    $result[$counter]["share"]["url"] = $this->getBaseUrl(false,$contests->getHref());
                    $result[$counter]["share"]["title"] = $contests->getTitle();
                    $result[$counter]["share"]["description"] = strip_tags($contests->getDescription());
                    $result[$counter]["share"]['urlParams'] = array(
                        "type" => $contests->getType(),
                        "id" => $contests->getIdentity()
                    );
                }
            }
            if ($contests->verified)
                $result[$counter]['is_content_verified'] = true;
            else
                $result[$counter]['is_content_verified'] = false;
            if ($contests->sponsored)
                $result[$counter]['is_content_sponsored'] = true;
            else
                $result[$counter]['is_content_sponsored'] = false;
			if($canFollow){
				if($followStatus)
				 $result[$counter]['is_content_follow'] = true;
            else
                $result[$counter]['is_content_follow'] = false;
			}
            if ($contests->hot)
                $result[$counter]['is_content_hot'] = true;
            else
                $result[$counter]['is_content_hot'] = false;
			if ($viewerId){
				if ($likeStatus)
                $result[$counter]['is_content_like'] = true;
				else
                $result[$counter]['is_content_like'] = false;
			}
            if ($viewerId && $canFavourite) {
                if ($favouriteStatus)
                    $result[$counter]['is_content_favourite'] = true;
                else
                    $result[$counter]['is_content_favourite'] = false;
            }
            //if ($canFavourite)
               // $result[$counter]['can_favourite'] = true;
            //if ($canFollow && $viewerId != $contests->user_id)
               // $result[$counter]['can_follow'] = true;
            //if ($shareType)
                //$result[$counter]['can_share'] = true;
            if ($contests->category_id) {
                $category = Engine_Api::_()->getItem('sescontest_category', $contests->category_id);
                if ($category) {
                    $result[$counter]['category_title'] = $category->category_name;
                    if ($contests->subcat_id) {
                        $subcat = Engine_Api::_()->getItem('sescontest_category', $contests->subcat_id);
                        if ($subcat) {
                            $result[$counter]['subcategory_title'] = $subcat->category_name;
                            if ($contests->subsubcat_id) {
                                $subsubcat = Engine_Api::_()->getItem('sescontest_category', $contests->subsubcat_id);
                                if ($subsubcat) {
                                    $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                                }
                            }
                        }
                    }
                }
            }
            $tags = array();
            foreach ($contests->tags()->getTagMaps() as $tagmap) {
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

            $result[$counter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($contests, '', "");
//             $coverImage = $contests->getCoverPhotoUrl();
//             if ($coverImage)
//               $result[$counter]['cover_image'] = $this->getBaseUrl(false,$coverImage);

            $dateinfoParams['starttime'] = true;
            $dateinfoParams['endtime'] = true;
            $dateinfoParams['timezone'] = true;
            $counter++;
        }
        return $result;
    }

    public function categoryAction() {
        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
                ->setIntegrityCheck(false)
                ->from($corePagesTable, null)
                ->where($coreContentTableName . '.name=?', 'sescontest.browse-search')
                ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                ->where($corePagesTableName . '.name = ?', 'sescontest_index_browse');
        $id = $select->query()->fetchColumn();
        $params = Engine_Api::_()->sescontest()->getWidgetParams($id);
        // Category Paginator
        $categoryobject = Engine_Api::_()->getDbTable('categories', 'sescontest')->getCategory(array('countContests' => true, 'limit' => 20, 'fetchAll' => true));

        $categorycounter = 0;
        foreach ($categoryobject as $category) {
            $categorydata[$categorycounter] = $category->toArray();
            $color_icon = $category->colored_icon;
            // it will be apply 
//            $image = Engine_Api::_()->storage()->get($category->colored_icon)->getPhotoUrl();
            $image = $category->getPhotoUrl('colored_icon');
            if ($image)
                $categorydata[$categorycounter]['image'] = $this->getbaseurl(false, $image);
            else
                $categorydata[$categorycounter]['image'] = $this->getbaseurl(false, 'application/modules/Sescontest/externals/images/contest-icon-big.png');
            $categorycounter++;
        }
        $result['category'] = $categorydata;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }

    public function menusAction() {
        $navigation = Engine_Api::_()
                ->getApi('menus', 'core')
                ->getNavigation('sescontest_main', array());
        if (count($navigation) == 1) {
            $navigation = null;
        }
        $menu_counter = 0;

        foreach ($navigation as $menu) {
            $class = end(explode(' ', $menu->class));
            if($class != "sescontest_main_manage_package" && $class != "sescontest_main_browse" && $class != 'sescontest_main_entries-browse' && $class != 'sescontest_main_winner-browse'  && $class != 'sescontest_main_categories' && $class != 'sescontest_main_create' && $class != 'sescontest_main_manage' && $class != 'sescontest_main_photocontest' && $class != 'sescontest_main_videocontest' && $class != 'sescontest_main_textcontest' && $class != 'sescontest_main_audiocontest' && $class != 'sescontest_main_activecontest' && $class != 'sescontest_main_comingsooncontest' && $class != 'sescontest_main_endedcontest' )
            continue;
            if ($class != 'sescontest_main_pinboard' && $class != "sescontest_main_home" && $class != 'sescontestjoinfees_main_myorders') {
                $result_menu['menus'][$menu_counter]['label'] = $this->view->translate($menu->label);
                $result_menu['menus'][$menu_counter]['action'] = $class;
                $result_menu['menus'][$menu_counter]['isActive'] = $menu->active;
                $menu_counter++;
            }
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result_menu)));
    }

    public function comingsoonAction() {
        $search = $this->_getParam('search', null);

        switch ($search) {
            case 'ended':
                $params['sort'] = 'ended';
                break;
            case 'active':
                $params['sort'] = 'ongoing';
                break;
            case 'upcoming':
                $params['sort'] = 'upcoming';
                break;
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
            case 'mostSPjoined':
                $params['sort'] = 'join_count';
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
        }
        $paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')->getContestPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		if($this->getContests($paginator))
        $result['contests'] = $this->getContests($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function categoriesAction() {

        $categoryobject = Engine_Api::_()->getDbTable('categories', 'sescontest')->getCategory(array('countContests' => true, 'limit' => 20, 'fetchAll' => true));

        $categorycounter = 0;
        foreach ($categoryobject as $category) {
            $categorydata[$categorycounter] = $category->toArray();
            $color_icon = $category->colored_icon;
            // it will be apply 
//            $image = Engine_Api::_()->storage()->get($category->colored_icon)->getPhotoUrl();
            $image = $category->getPhotoUrl('colored_icon');
            if ($image)
                $categorydata[$categorycounter]['image'] = $this->getbaseurl(false, $image);
            else
                $categorydata[$categorycounter]['image'] = $this->getbaseurl(false, 'application/modules/Sescontest/externals/images/contest-icon-big.png');
            $categorycounter++;
        }
        $result['category'] = $categorydata;


        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
                ->setIntegrityCheck(false)
                ->from($corePagesTable, null)
                ->where($coreContentTableName . '.name=?', 'sescontest.category-associate-contests')
                ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                ->where($corePagesTableName . '.name = ?', 'sescontest_category_browse');
        $id = $select->query()->fetchColumn();

        $params = Engine_Api::_()->sescontest()->getWidgetParams($id);
        $params['paginator'] = true;
        $paginator = Engine_Api::_()->getDbTable('categories', 'sescontest')->getCategory($params, array('paginator' => true));
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		if($this->getCategory($paginator))
        $result['categories'] = $this->getCategory($paginator);


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function getCategory($categoryPaginator) {
        $result = array();
        $counter = 0;
        foreach ($categoryPaginator as $categories) {
            $category = $categories->toArray();
            if ($categories->total_contest_categories == 0)
                continue;
            $result[$counter] = $category;
            $result[$counter]['count'] = $categories->total_contest_categories;

            $params['category_id'] = $categories->category_id;
            $params['limit'] = 3;
            $paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')->getContestPaginator($params);

            $paginator->setItemCountPerPage(3);
            if ($paginator->getPages()->pageCount > 1) {
                $result[$counter]['see_all'] = true;
            } else {
                $result[$counter]['see_all'] = false;
            }
            $manage['manage'] = 0;
            $contest = $this->getContests($paginator, $manage);
            if ($contest)
                $result[$counter]['items'] = $contest;
            $counter++;
        }
        $results = $result;
        return $results;
    }

    public function browsecategoriesAction() {

        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
                ->setIntegrityCheck(false)
                ->from($corePagesTable, null)
                ->where($coreContentTableName . '.name=?', 'sescontest.category-carousel')
                ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                ->where($corePagesTableName . '.name = ?', 'sescontest_category_browse');
        $id = $select->query()->fetchColumn();

        $params = Engine_Api::_()->sescontest()->getWidgetParams($id);

        $value = array();
        $value['order'] = $params['order'];
        $value['info'] = $params['info'];
        $paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')
                ->getContestSelect(array_merge($value, array('search' => 1, 'fetchAll' => true, 'limit' => 3)));
        $params['criteria'] = 'most_event';
        $params['countEvents'] = 1;
        $params['paginator'] = 1;
        $paginator = Engine_Api::_()->getDbTable('categories', 'secontest')->getCategory($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result['category'] = $this->getcategories($paginator);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        if (count($result['category']) <= 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate(' There are no results that match your search. Please try again.'), 'result' => array()));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function browseMediaContestAction() {

        $media = $this->_getParam('media', null);
        if ($media == 1) {
            $type = 'text';
            $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
            $coreContentTableName = $coreContentTable->info('name');
            $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
            $corePagesTableName = $corePagesTable->info('name');
            $select = $corePagesTable->select()
                    ->setIntegrityCheck(false)
                    ->from($corePagesTable, null)
                    ->where($coreContentTableName . '.name=?', 'sescontest.mediatype-banner')
                    ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                    ->where($corePagesTableName . '.name = ?', 'sescontest_media_text');
            $id = $select->query()->fetchColumn();
            $params = Engine_Api::_()->sescontest()->getWidgetParams($id);
        } else if ($media == 2) {
            $type = 'photo';
            $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
            $coreContentTableName = $coreContentTable->info('name');
            $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
            $corePagesTableName = $corePagesTable->info('name');
            $select = $corePagesTable->select()
                    ->setIntegrityCheck(false)
                    ->from($corePagesTable, null)
                    ->where($coreContentTableName . '.name=?', 'sescontest.mediatype-banner')
                    ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                    ->where($corePagesTableName . '.name = ?', 'sescontest_media_photo');
            $id = $select->query()->fetchColumn();
            $params = Engine_Api::_()->sescontest()->getWidgetParams($id);
        } else if ($media == 3) {
            $type = 'video';
            $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
            $coreContentTableName = $coreContentTable->info('name');
            $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
            $corePagesTableName = $corePagesTable->info('name');
            $select = $corePagesTable->select()
                    ->setIntegrityCheck(false)
                    ->from($corePagesTable, null)
                    ->where($coreContentTableName . '.name=?', 'sescontest.mediatype-banner')
                    ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                    ->where($corePagesTableName . '.name = ?', 'sescontest_media_video');
            $id = $select->query()->fetchColumn();
            $params = Engine_Api::_()->sescontest()->getWidgetParams($id);
        } else if ($media == 4) {
            $type = 'audio';
            $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
            $coreContentTableName = $coreContentTable->info('name');
            $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
            $corePagesTableName = $corePagesTable->info('name');
            $select = $corePagesTable->select()
                    ->setIntegrityCheck(false)
                    ->from($corePagesTable, null)
                    ->where($coreContentTableName . '.name=?', 'sescontest.mediatype-banner')
                    ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                    ->where($corePagesTableName . '.name = ?', 'sescontest_media_audio');
            $id = $select->query()->fetchColumn();
            $params = Engine_Api::_()->sescontest()->getWidgetParams($id);
        }

        if ($params) {
            $banner = Engine_Api::_()->getDbTable('medias', 'sescontest')->getBannerid($type);
            if ($banner) {
                $photo = Engine_Api::_()->storage()->get($banner);

                if ($photo) {
                    $photourl = $photo->getPhotoUrl('thumb.normal');
                    if ($photourl) {
                        $result['banner']['image'] = $this->getbaseurl(false, $photourl);
                    }
                }
            }
            $result['banner']['banner_title'] = $params['banner_title'];
            $result['banner']['description'] = $params['description'];
        }

        $param['media'] = $media;
        $param['search'] = $this->_getParam('search', null);
        if (isset($values['search']))
            $values['text'] = addslashes($params['search']);
        $values['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
        $values = array_merge($param, $values);
        $paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')->getContestPaginator($values);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $manage['manage'] = 0;
        $contest = $this->getContests($paginator, $manage);
        if ($contest)
            $result['contests'] = $contest;

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function manageContestAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (!$viewer_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $params['user_id'] = $viewer_id;
        $search = $this->_getParam('search_filter', null);
        switch ($search) {
            case 'ended':
                $params['sort'] = 'ended';
                break;
            case 'active':
                $params['sort'] = 'ongoing';
                break;
            case 'upcoming':
                $params['sort'] = 'upcoming';
                break;
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
            case 'mostSPjoined':
                $params['sort'] = 'join_count';
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
            default:
                $params['sort'] = 'creation_date';
        }
        $params['widgetManage'] = true;
        $menus = $this->_getParam('menus', null);
        if ($menus == 1) {
            $filterMenucounter = 0;
            $resultmenu[$filterMenucounter]['name'] = 'upcoming';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Coming Soon');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'recentlySPcreated';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Recently Created');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'mostSPviewed';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Viewed');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'mostSPliked';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Liked');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'mostSPcommented';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Commented');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'mostSPfollowed';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Followed');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'mostSPjoined';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Joined');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'mostSPfavourite';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Favourited');
            $filterMenucounter++;
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
            $filterMenucounter++;
            $result['menus'] = $resultmenu;
        }
        $paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')->getContestPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $manage['manage'] = 1;
        $contest = $this->getContests($paginator, $manage);
        if ($contest)
            $result['contests'] = $contest;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function searchFilterAction() {
        $searchForm = new Sescontest_Form_Search();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
            $this->generateFormFields($formFields);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
    }

    public function browseEntriesAction() {
        $searchArray = array();
        if (isset($_POST['searchParams']) && $_POST['searchParams'])
            parse_str($_POST['searchParams'], $searchArray);
        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
                ->setIntegrityCheck(false)
                ->from($corePagesTable, null)
                ->where($coreContentTableName . '.name=?', 'sescontest.browse-entries')
                ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                ->where($corePagesTableName . '.name = ?', 'sescontest_index_entries');
        $id = $select->query()->fetchColumn();
        $params = Engine_Api::_()->sescontest()->getWidgetParams($id);
        $form = new Sescontest_Form_WinnerSearch();
        $form->populate($_POST);
        $params = $form->getValues();
        if (!empty($searchArray)) {
            foreach ($searchArray as $key => $search) {
                $params[$key] = $search;
            }
        }
        if (isset($params['search']))
            $params['text'] = addslashes($params['search']);

        if (isset($params['search_entry']))
            $params['entry_text'] = addslashes($params['search_entry']);

        if (isset($_GET['contest_id']) && !empty($_GET['contest_id']))
            $params['contest_id'] = $_GET['contest_id'];
        $paginator = Engine_Api::_()->getDbTable('participants', 'sescontest')->getParticipantPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		if($this->getEntries($paginator))
        $result['entries'] = $this->getEntries($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function getEntries($paginator) {
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        foreach ($paginator as $entries) {
            $entry = $entries->toArray();
            $result[$counter] = $entry;
            $contest = Engine_Api::_()->getItem('contest', $entries->contest_id);
            $result[$counter]['contest_type'] = $contest->contest_type;
            $canComment = Engine_Api::_()->authorization()->isAllowed('participant', $viewer, 'comment');
            $likeStatus = Engine_Api::_()->sescontest()->getLikeStatus($entries->participant_id, $entries->getType());
			$canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.allow.follow', 1);
			$followStatus = Engine_Api::_()->getDbTable('followers', 'sescontest')->isFollow(array('resource_id' => $entries->participant_id,'resource_type' => $entries->getType()));
            $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sescontest')->isFavourite(array('resource_id' => $entries->participant_id, 'resource_type' => $entries->getType()));
            $owner = $entries->getOwner();
            $title = $entries->getTitle();
            if ($title)
                $result[$counter]['entry_title'] = $title;
			$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
			
            $voteType = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'allow_entry_vote');
            		
		if ($voteType != 0 && (($voteType == 1 && $entries->owner_id != $viewer->getIdentity()) || $voteType == 2)){
			if(strtotime($contest->votingstarttime) <= time() && strtotime($contest->votingendtime) > time() && strtotime($contest->endtime) > time()){
				$hasVoted = Engine_Api::_()->getDbTable('votes', 'sescontest')->hasVoted($viewer->getIdentity(), $contest->contest_id, $entries->participant_id);
				if($hasVoted)
					$result[$counter]['is_vote'] = true;
				else
					$result[$counter]['is_vote'] = false;
			}
		}
           
			
            $image = $entries->getPhotoUrl('thumb.main');
            if ($image)
                $result[$counter]['entry_image'] = $this->getbaseurl(false, $image);
            $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
            if ($ownerimage)
                $result[$counter]['owner_image'] = $ownerimage;
            $result[$counter]['owner_title'] = $owner->getTitle();
            $result[$counter]['contest_title'] = $contest->getTitle();
            $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.share', 1);
            if ($shareType) {
                $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $entries->getPhotoUrl());
								$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$entries->getHref());
                $result[$counter]["share"]["title"] = $entries->getTitle();
                $result[$counter]["share"]["description"] = strip_tags($entries->getDescription());
                $result[$counter]["share"]['urlParams'] = array(
                    "type" => $entries->getType(),
                    "id" => $entries->getIdentity()
                );
            }
			if($viewer->getIdentity()){
				if ($likeStatus)
                $result[$counter]['is_content_like'] = true;
				else
                $result[$counter]['is_content_like'] = false;
			}
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.favourite', 1) && $viewer->getIdentity()){
				 if ($favouriteStatus)
                $result[$counter]['is_content_favourite'] = true;
				else
                $result[$counter]['is_content_favourite'] = false;
			}
           
            if ($canComment)
                $result[$counter]['can_comment'] = true;
            else
                $result[$counter]['can_comment'] = false;
			if($canFollow)
                $result[$counter]['can_content_follow'] = $followStatus? true:false;
//            $number = Engine_Api::_()->sescontest()->ordinal($entries->rank);
//            $result[$counter]['award_label'] = $this->view->translate("%s Award", $number);
            $counter++;
        } // End Foreach
        return $result;
    }

    public function browseWinnerAction() {
        $searchArray = array();
        if (isset($_POST['searchParams']) && $_POST['searchParams'])
            parse_str($_POST['searchParams'], $searchArray);

        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
                ->setIntegrityCheck(false)
                ->from($corePagesTable, null)
                ->where($coreContentTableName . '.name=?', 'sescontest.winners-listing')
                ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
                ->where($corePagesTableName . '.name = ?', 'sescontest_index_winner');
        $id = $select->query()->fetchColumn();
        $params = Engine_Api::_()->sescontest()->getWidgetParams($id);

        $form = new Sescontest_Form_WinnerSearch();
        $form->populate($_POST);
        $params = $form->getValues();

        if (!empty($searchArray)) {
            foreach ($searchArray as $key => $search) {
                $params[$key] = $search;
            }
        }
        if (isset($params['search']))
            $params['contest_text'] = addslashes($params['search']);

        if (isset($params['search_entry']))
            $params['entry_text'] = addslashes($params['search_entry']);
        $params['winner'] = 1;

        $paginator = Engine_Api::_()->getDbTable('participants', 'sescontest')->getWinnerPaginator($params);

        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		if($this->getWinners($paginator))
        $result['winners'] = $this->getWinners($paginator);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function getWinners($paginator) {
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();

        foreach ($paginator as $entries) {
            $entry = $entries->toArray();
            $result[$counter] = $entry;
            $contest = Engine_Api::_()->getItem('contest', $entries->contest_id);
            $result[$counter]['contest_type'] = $contest->contest_type;
            $canComment = Engine_Api::_()->authorization()->isAllowed('participant', $viewer, 'comment');
            $likeStatus = Engine_Api::_()->sescontest()->getLikeStatus($entries->participant_id, $entries->getType());
            $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sescontest')->isFavourite(array('resource_id' => $entries->participant_id, 'resource_type' => $entries->getType()));
			$followStatus = Engine_Api::_()->getDbTable('followers', 'sescontest')->isFollow(array('resource_id' => $entries->participant_id,'resource_type' => $entries->getType()));
            $owner = $entries->getOwner();
            $title = $entries->getTitle();
            if ($title)
                $result[$counter]['entry_title'] = $title;
			$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;

            $voteType = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'allow_entry_vote');

            if ($voteType != 0 && (($voteType == 1 && $entries->owner_id != $viewer) || $voteType == 2))
                $canIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.vote.integrate', 0);
            else
                $canIntegrate = 0;

            $image = $entries->getPhotoUrl('thumb.main');
            if ($image)
                $result[$counter]['entry_image'] = $this->getbaseurl(false, $image);


            $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
            if ($ownerimage)
                $result[$counter]['owner_image'] = $ownerimage;
            $result[$counter]['owner_title'] = $owner->getTitle();
            $result[$counter]['contest_title'] = $contest->getTitle();

            $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.share', 1);

            if ($shareType) {
                $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $entries->getPhotoUrl());
								$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$entries->getHref());
                $result[$counter]["share"]["title"] = $entries->getTitle();
                $result[$counter]["share"]["description"] = strip_tags($entries->getDescription());
                $result[$counter]["share"]['urlParams'] = array(
                    "type" => $entries->getType(),
                    "id" => $entries->getIdentity()
                );
            }

            if ($likeStatus && $viewer->getIdentity())
                $result[$counter]['is_content_like'] = true;
            else
                $result[$counter]['is_content_like'] = false;
			if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.favourite', 1) && $viewer->getIdentity()){
				 if ($favouriteStatus)
                $result[$counter]['is_content_favourite'] = true;
				else
                $result[$counter]['is_content_favourite'] = false;
			}
            if ($canComment)
                $result[$counter]['can_comment'] = true;
            else
                $result[$counter]['can_comment'] = false;

            if ($followStatus && $viewer->getIdentity())
                $result[$counter]['can_follow'] = true;
            else
                $result[$counter]['can_follow'] = false;

            $number = Engine_Api::_()->sescontest()->ordinal($entries->rank);
            $result[$counter]['award_label'] = $this->view->translate("%s Award", $number);

            $counter++;
        } // Foreach End

        return $result;
    }

    public function searchWinnerFilterAction() {
        $search_for = $this->_getParam('search_for', 'contest');
        $searchForm = new Sescontest_Form_WinnerSearch();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
            $this->generateFormFields($formFields);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
    }
    
	public function categoryviewAction() {
        $categoryId = $this->_getParam('category_id', null);
        if ($categoryId) {
            $category = Engine_Api::_()->getItem('sescontest_category', $categoryId);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate(' parameter_missing'), 'result' => array()));
        }
        $result['contest_category'] = $category->toArray();
        if (isset($category->thumbnail) && !empty($category->thumbnail)) {
            $image = Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl('thumb.main');
            $result['contest_category']['images']['main'] = $this->getbaseurl(false, $image);
        }
        if ($category->subcat_id == 0 && $category->subsubcat_id == 0) {
            $innerCatData = Engine_Api::_()->getDbtable('categories', 'sescontest')->getModuleSubcategory(array('category_id' => $category->category_id, 'column_name' => '*', 'countEvents' => true, 'getcategory0' => true));
            $columnCategory = 'category_id';
            $countersubCat = 0;
            foreach ($innerCatData as $item) {
                $result['category'][$countersubCat] = $item->toArray();
                if ($item->thumbnail != '' && !is_null($item->thumbnail) && intval($item->thumbnail)) {
                    $image = Engine_Api::_()->storage()->get($item->thumbnail)->getPhotoUrl('thumb.main');
                    $result['category'][$countersubCat]['images']['main'] = $this->getbaseurl(false, $image);
                }
                if ($item->cat_icon != '' && !is_null($item->cat_icon) && intval($item->cat_icon)) {
                    $image = Engine_Api::_()->storage()->get($item->thumbnail)->getPhotoUrl('thumb.icon');
                    $result['category'][$countersubCat]['images']['icon'] = $this->getbaseurl(false, $image);
                }
				
                //$result['sub_category'][$countersubCat]['count'] = $this->view->translate(array('%s contest', '%s contests', $item->total_contests_categories), $this->view->locale()->toNumber($item->total_events_categories));
                 $countersubCat++;
            }
        }
        $params['category_id'] = $categoryId;
		$paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')->getContestPaginator($params);
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $manage['manage'] = 0;
        $contest = $this->getContests($paginator, $manage);
        if ($contest)
            $result['contests'] = $contest;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
		 
	}
}
