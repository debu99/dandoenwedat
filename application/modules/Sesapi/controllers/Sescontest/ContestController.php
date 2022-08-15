<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ContestController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


class Sescontest_ContestController extends Sesapi_Controller_Action_Standard {

    public function init() {
        $id = $this->_getParam('contest_id', null);
        if ($id) {
            $contest = Engine_Api::_()->getItem('contest', $id);
            if ($contest) {
                Engine_Api::_()->core()->setSubject($contest);
            }
        }
    }

    public function viewAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
		//echo '<pre>';print_r();die;
		
        $contestId = $this->_getParam('contest_id', null);
        if (!$contestId)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject())
            $contest = Engine_Api::_()->getItem('contest', $this->_getParam('contest_id', null));
        else
            $contest = Engine_Api::_()->core()->getSubject();
        if(!$contest)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => $this->view->translate('There are no results that match your search. Please try again.')));
		/*if (!$this->_helper->requireAuth()->setAuthParams('contest', null, 'view')->isValid()){
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
		}
		if($contest->authorization()->isAllowed($viewer, 'view')==1 && $contest->user_id != $viewer_id){
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
		}*/
		if (!$this->_helper->requireAuth()->setAuthParams('contest', $viewer, 'view')->isValid()){
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
		}
		$result['contest'] = $contest->toArray();
        $contestTags = $contest->tags()->getTagMaps();
        //$canComment = $contest->authorization()->isAllowed($viewer, 'comment');
        $canShare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.allow.share', 1);  
        $canComment = Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'create');
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.allow.follow', 1);
        $isContestEdit = Engine_Api::_()->sescontest()->contestPrivacy($contest, 'edit');
		$canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest_allow_favourite', 0);
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescontestpackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontestpackage.enable.package', 0)) {
            $params = Engine_Api::_()->getItem('sescontestpackage_package', $contest->package_id)->params;
            $params = json_decode($params, true);
            $canUploadCover = $params['upload_cover'];
        } else {
            $canUploadCover = Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'upload_cover');
        }
        $result['contest']['can_upload_cover'] = $canUploadCover ? true : false;
        $isContestDelete = Engine_Api::_()->sescontest()->contestPrivacy($contest, 'delete');
        $result['contest']['can_delete'] = $isContestDelete ? true : false;
        $owner = $contest->getOwner();
        $result['contest']['owner_title'] = $owner->getTitle();
        if ($contest->category_id) {
            $category = Engine_Api::_()->getItem('sescontest_category', $contest->category_id);
            if ($category) {
                $result['contest']['category_title'] = $category->category_name;
                if ($contest->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('sescontest_category', $contest->subcat_id);
                    if ($subcat) {
                        $result['contest']['subcategory_title'] = $subcat->category_name;
                        if ($contest->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('sescontest_category', $contest->subsubcat_id);
                            if ($subsubcat) {
                                $result['contest']['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }
		
        $tags = array();
        foreach ($contest->tags()->getTagMaps() as $tagmap) {
            $tags[] = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
            ));
        }
        if (count($tags)) {
            $result['contest']['tag'] = $tags;
        }
        $participate = Engine_Api::_()->getDbTable('participants', 'sescontest')->hasParticipate($viewer->getIdentity(), $contest->contest_id);
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $dateArray = Engine_Api::_()->getDbTable('participants', 'sescontest')->checkContestTime($viewer_id, $contest->contest_id);
		if(Engine_Api::_()->storage()->get($contest->photo_id)){
			$contestphoto = $this->getbaseurl(false,Engine_Api::_()->storage()->get($contest->photo_id)->getPhotoUrl('thumb.icon'));
			$result['contest']['contest_image'] = $contestphoto ? $contestphoto :$this->getbaseurl(false, 'application/modules/user/externals/images/nophoto_user_thumb_icon.png');
		}
		    $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner,'',"");
        if ($ownerimage)
            $result['contest']['owner_image'] = $ownerimage;
		    $coverImage = $contest->getCoverPhotoUrl();
        if ($coverImage)
            $result['contest']['cover_image'] = $this->getBaseUrl(false,$coverImage);
        if ($contest->contest_type == 3)
            $result['contest']['contest_type_label'] = $this->view->translate('Video Contest');
        elseif ($contest->contest_type == 4)
            $result['contest']['contest_type_label'] = $this->view->translate('Audio Contest');
        elseif ($contest->contest_type == 2)
            $result['contest']['contest_type_label'] = $this->view->translate('Photo Contest');
        elseif ($contest->contest_type == 2)
            $result['contest']['contest_type_label'] = $this->view->translate('Writing Contest');

        $updateCoverPhotocounter = 0;
        if ($isContestEdit && $canUploadCover) {
            if ($contest->cover && $contest->cover != 0 && $contest->cover != '') {
                $result['contest']['updateCoverPhoto'][$updateCoverPhotocounter]['name'] = 'changecover';
                $result['contest']['updateCoverPhoto'][$updateCoverPhotocounter]['label'] = $this->view->translate("Change Cover Photo");
                $updateCoverPhotocounter++;
                $result['contest']['updateCoverPhoto'][$updateCoverPhotocounter]['name'] = 'removecover';
                $result['contest']['updateCoverPhoto'][$updateCoverPhotocounter]['label'] = $this->view->translate("Remove Cover Photo");
                $updateCoverPhotocounter++;
            } else {
                $result['contest']['updateCoverPhoto'][$updateCoverPhotocounter]['name'] = 'addcover';
                $result['contest']['updateCoverPhoto'][$updateCoverPhotocounter]['label'] = $this->view->translate("Add Cover Photo");
                $updateCoverPhotocounter++;
            }
        }
       $edit_photoEdit = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'edit_photo'));
       $awardsEdit = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'award'));
       $rulesEdit = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'rule'));
       $contact_informationEdit = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'contact_information'));
       $seoEdit = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'seo'));
       $overviewEdit = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'overview'));
	   $manage_participant = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'participant'));
	   $contact_all_participant = Engine_Api::_()->getDbtable('dashboards', 'sescontest')->getDashboardsItems(array('type' => 'contact_participants')); 
      $optionscounter = 0;
        if ($isContestEdit) {
            $result['contest']['options'][$optionscounter]['name'] = 'edit';
            $result['contest']['options'][$optionscounter]['label'] = $this->view->translate("Edit Contest");
            $optionscounter++;
        if($contest->rules){
           $result['contest']['rule_option']['name'] = 'change';
           $result['contest']['rule_option']['label'] = $this->view->translate("Change Rules");
         } else {
          $result['contest']['rule_option']['name'] = 'add';
          $result['contest']['rule_option']['label'] = $this->view->translate("Add Rules");
         }
        }
        if($edit_photoEdit->enabled && $isContestEdit){
          $result['contest']['options'][$optionscounter]['name'] = 'editPhoto';
          $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($edit_photoEdit->title);
          $optionscounter++;
        }elseif (Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'upload_mainphoto') && $isContestEdit){
          $result['contest']['options'][$optionscounter]['name'] = 'editPhoto';
          $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($edit_photoEdit->title);
          $optionscounter++;
        }
        if($awardsEdit->enabled && $isContestEdit){
          $result['contest']['options'][$optionscounter]['name'] = 'editAwards';
          $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($awardsEdit->title);
          $optionscounter++;
        }
        if($rulesEdit->enabled && $isContestEdit){
          $result['contest']['options'][$optionscounter]['name'] = 'editRules';
          $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($rulesEdit->title);
          $optionscounter++;
        }
      /*if($contact_informationEdit->enabled && $isContestEdit){
        $result['contest']['options'][$optionscounter]['name'] = 'editInformation';
        $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($contact_informationEdit->title);
        $optionscounter++;
      }else*/
		  if($contact_informationEdit->enabled && Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contactinfo') && $isContestEdit){
        $result['contest']['options'][$optionscounter]['name'] = 'editInformation';
        $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($contact_informationEdit->title);
        $optionscounter++;
      }
      /*if( && $isContestEdit){
        $result['contest']['options'][$optionscounter]['name'] = 'editSeo';
        $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($seoEdit->title);
        $optionscounter++;
		
      }else*/
	  if($seoEdit->enabled && Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contest_seo') && $isContestEdit){
        $result['contest']['options'][$optionscounter]['name'] = 'editSeo';
        $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($seoEdit->title);
        $optionscounter++;
		
      }
      if(Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contest_overview') && $isContestEdit){
        $result['contest']['options'][$optionscounter]['name'] = 'editOverview';
        $result['contest']['options'][$optionscounter]['label'] =  $this->view->translate($overviewEdit->title);
        $optionscounter++;
      }
        if ($isContestDelete) {
            $result['contest']['options'][$optionscounter]['name'] = 'delete';
            $result['contest']['options'][$optionscounter]['label'] = $this->view->translate("Delete Contest");
            $optionscounter++;
        }
        if ($canShare && $viewer_id) {
            $result['contest']['options'][$optionscounter]['name'] = 'share';
            $result['contest']['options'][$optionscounter]['label'] = $this->view->translate("Share Contest");
            $optionscounter++;
        }
        if (($viewer_id && $viewer_id != $contest->user_id) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.allow.report', 1)) {
            $result['contest']['options'][$optionscounter]['name'] = 'report';
            $result['contest']['options'][$optionscounter]['label'] = $this->view->translate("Report Contest");
            $optionscounter++;
        }
		if(@$contact_all_participant->enabled && $contest->joinstarttime <= time() && $isContestEdit){
			 $result['contest']['options'][$optionscounter]['name'] = 'contactparticipant';
            $result['contest']['options'][$optionscounter]['label'] = $this->view->translate($contact_all_participant->title);
            $optionscounter++;
		}
        if (strtotime($contest->endtime) > time()) {
            $result['contest']['countDownTime'] = strtotime($contest->endtime);
            $result['contest']['finishMessage'] = $this->view->translate("Contest has Ended.");
        }
        if (strtotime($contest->joinendtime) > time()) {

            $result['contest']['countDownTime'] = strtotime($contest->joinendtime);
            $result['contest']['finishMessage'] = $this->view->translate("Contest entry submission has Ended.");
        }
        if (strtotime($contest->votingendtime) > time()) {
            $result['contest']['countDownTime'] = strtotime($contest->votingendtime);
            $result['contest']['finishMessage'] = $this->view->translate("Contest voting has Ended.");
        }
        if (strtotime($contest->starttime) > time()) {
            $result['contest']['countDownTime'] = strtotime($contest->starttime);
            $result['contest']['finishMessage'] = $this->view->translate("Contest has Started.");
        }
        if (strtotime($contest->votingstarttime) > time()) {
            $result['contest']['countDownTime'] = strtotime($contest->votingstarttime);
            $result['contest']['finishMessage'] = $this->view->translate("Contest Voting has Started.");
        }
        if (strtotime($contest->joinstarttime) < time()) {
            $result['contest']['countDownTime'] = strtotime($contest->joinstarttime);
            $result['contest']['finishMessage'] = $this->view->translate("Contest Participation has Started.");
        }
        if (strtotime(date('Y-m-d H:i:s')) > strtotime($contest->endtime)) {
            $result['contest']['contest_status']['name'] = 'ended';
            $result['contest']['contest_status']['label'] = $this->view->translate('Ended');
        } elseif (strtotime(date('Y-m-d H:i:s')) < strtotime($contest->starttime)) {
            $result['contest']['contest_status']['name'] = 'comingsoon';
            $result['contest']['contest_status']['label'] = $this->view->translate('Coming Soon');
        } elseif (strtotime(date('Y-m-d H:i:s')) >= strtotime($contest->starttime)) {
            $result['contest']['contest_status']['name'] = 'active';
            $result['contest']['contest_status']['label'] = $this->view->translate('Active');
        }
        if (isset($dateArray['join_start_date'])) {
            $result['contest']['entry_status']['name'] = 'entrysubmissionstarted';
            $result['contest']['entry_status']['label'] = $this->view->translate("Entries Submission Started");
        } elseif (isset($dateArray['join_end_date']) && strtotime(date('Y-m-d H:i:s')) < strtotime($this->contest->endtime)) {
            $result['contest']['entry_status']['name'] = 'entrysubmissionended';
            $result['contest']['entry_status']['label'] = $this->view->translate("Entries Submission Ended");
        }
        if (isset($dateArray['voting_start_date'])) {
            $result['contest']['voting_status']['name'] = 'votingstarted';
            $result['contest']['voting_status']['label'] = $this->view->translate("Voting Started");
        } elseif (isset($dateArray['voting_end_date']) && strtotime(date('Y-m-d H:i:s')) < strtotime($this->contest->endtime)) {
            $result['contest']['voting_status']['name'] = 'votingended';
            $result['contest']['voting_status']['label'] = $this->view->translate("Voting Ended");
        }
        if ($contest->is_approved) {
			if ($canShare) {
				$result['contest']["share"]["imageUrl"] = $this->getBaseUrl(false, $contest->getPhotoUrl());
				$result['contest']["share"]["url"] = $this->getBaseUrl(false,$contest->getHref());
				$result['contest']["share"]["title"] = $contest->getTitle();
				$result['contest']["share"]["description"] = strip_tags($contest->getDescription());
				$result['contest']["share"]['urlParams'] = array(
					"type" => $contest->getType(),
					"id" => $contest->getIdentity()
				);
			}
            $likeStatus = Engine_Api::_()->sescontest()->getLikeStatus($contest->contest_id, $contest->getType());
            $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sescontest')->isFavourite(array('resource_id' => $contest->contest_id, 'resource_type' => $contest->getType()));
            $followStatus = Engine_Api::_()->getDbTable('followers', 'sescontest')->isFollow(array('resource_id' => $contest->contest_id, 'resource_type' => $contest->getType()));

            //$result['contest']['can_share'] = $canShare ? true : false;
            //$result['contest']['can_comment'] = $canComment ? true : false;
            //$result['contest']['can_follow'] = $canFollow ? true : false;
			if($viewer_id > 0)
            $result['contest']['is_content_like'] =   $likeStatus ? true : false;
			if($canFavourite)
            $result['contest']['is_content_favourite'] =  $favouriteStatus ? true : false;
			if($canFollow)
				$result['contest']['is_content_follow'] =  $followStatus ? true : false;
			}
        if (isset($participate['can_join']) && isset($participate['show_button']) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontestjoinfees.allow.entryfees', 1)) {
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescontestjoinfees') && $contest->entry_fees > 0) {
               
                $result['contest']['join'] = $this->view->translate('Join Contest in %s', Engine_Api::_()->sescontestjoinfees()->getCurrencyPrice($contest->entry_fees));
            } else {
               
                $result['contest']['join'] = $this->view->translate('Join Contest');
            }
        }

        $defaultParams = array();
        $defaultParams['isSesapi'] = 1;
        $defaultParams['starttime'] = true;
        $defaultParams['endtime'] = true;
        $defaultParams['timezone'] = true;
        $defaultParams['resulttime'] = true;
        $strtime = $this->view->contestStartEndDates($contest, $defaultParams);
        list($result['contest']['calanderStartTime'], $result['contest']['calanderEndTime']) = explode('ENDDATE', strip_tags($strtime));

        $defaultParams = array();
        $defaultParams['joinstarttime'] = true;
        $defaultParams['joinendtime'] = true;
        $defaultParams['timezone'] = true;
        $defaultParams['isSesapi'] = 1;
        $defaultParams['resulttime'] = true;
        $strtime = $this->view->contestStartEndDates($contest, $defaultParams);
        list($result['contest']['joinedStartTime'], $result['contest']['joinedEndTime']) = explode('ENDDATE', strip_tags($strtime));

        $defaultParams = array();
        $defaultParams['votingstarttime'] = true;
        $defaultParams['votingendtime'] = true;
        $defaultParams['resulttime'] = true;
        $defaultParams['isSesapi'] = 1;
        $defaultParams['timezone'] = true;
        $strtime = $this->view->contestStartEndDates($contest, $defaultParams);
        list($result['contest']['votingStartTime'], $result['contest']['votingEndTime']) = explode('ENDDATE', strip_tags($strtime));

        $type = "winner";
        $winners = Engine_Api::_()->getDbTable('participants', 'sescontest')->getContestMembers($contest->contest_id,$type);
        $menuscounter = 0;
		    $result['menus'][$menuscounter]['name'] = 'updates';
        $result['menus'][$menuscounter]['label'] = $this->view->translate('Updates');
        $menuscounter++;
        if(count($winners)){
          $result['menus'][$menuscounter]['name'] = 'winners';
          $result['menus'][$menuscounter]['label'] = $this->view->translate('Winners');
          $menuscounter++;
        }
        $result['menus'][$menuscounter]['name'] = 'entries';
        $result['menus'][$menuscounter]['label'] = $this->view->translate('Entries');
        $menuscounter++;
        $result['menus'][$menuscounter]['name'] = 'details';
        $result['menus'][$menuscounter]['label'] = $this->view->translate('Details');
        $menuscounter++;
        $result['menus'][$menuscounter]['name'] = 'awards';
        $result['menus'][$menuscounter]['label'] = $this->view->translate('Awards');
        $menuscounter++;
        $result['menus'][$menuscounter]['name'] = 'rules';
        $result['menus'][$menuscounter]['label'] = $this->view->translate('Rules');
        $menuscounter++;
        $result['menus'][$menuscounter]['name'] = 'comments';
        $result['menus'][$menuscounter]['label'] = $this->view->translate('Comments');
        $menuscounter++;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }

    public function uploadCoverAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $contestId = $this->_getParam('contest_id', null);
        if (!$contestId)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject())
            $contest = Engine_Api::_()->getItem('contest', $this->_getParam('contest_id', null));
        else
            $contest = Engine_Api::_()->core()->getSubject();
        $cover_photo = $contest->cover;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        $contest->setCoverPhoto($data);
        if ($cover_photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $cover_photo);
            $im->delete();
        }
        $file['main'] = $this->getBaseUrl(true,$contest->getCoverPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully cover photo uploaded.'), 'images' => $file)));
    }

    public function removeCoverAction() {
        $contestId = $this->_getParam('contest_id', null);
        if (!$contestId)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject())
            $contest = Engine_Api::_()->getItem('contest', $this->_getParam('contest_id', null));
        else
            $contest = Engine_Api::_()->core()->getSubject();
        if (!$contest)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You are searching for Data does not exist.'), 'result' => array()));

        if (isset($contest->cover) && $contest->cover > 0) {
            $im = Engine_Api::_()->getItem('storage_file', $contest->cover);
            $contest->cover = 0;
            $contest->save();
            $im->delete();
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully deleted cover photo.'))));
    }

    public function followAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        $item_id = $this->_getParam('contest_id');
        if (!$item_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));

        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
        }
        $Fav = Engine_Api::_()->getDbTable('followers', 'sescontest')->getItemFollower('contest', $item_id);
        $followerItem = Engine_Api::_()->getDbtable('contests', 'sescontest');
        if (count($Fav) > 0) {
            //delete		
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Fav->delete();
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count - 1')), array('contest_id = ?' => $item_id));
            $item = Engine_Api::_()->getItem('contest', $item_id);
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'follow_sescontest', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
            Engine_Api::_()->sesapi()->deleteFeed(array('type' => 'sescontest_follow_contest', "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('successfully unfollowed.'))));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('followers', 'sescontest')->getAdapter();
            $db->beginTransaction();
            try {
                $follow = Engine_Api::_()->getDbTable('followers', 'sescontest')->createRow();
                $follow->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $follow->resource_type = 'contest';
                $follow->resource_id = $item_id;
                $follow->save();
                $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count + 1')), array('contest_id = ?' => $item_id));
                // Commit
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            //send notification and activity feed work.
            $item = Engine_Api::_()->getItem('contest', @$item_id);
            $subject = $item;
            $owner = $subject->getOwner();
            if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item->getOwner(), $viewer, $item, 'follow_sescontest');
                $result = $activityTable->fetchRow(array('type =?' => 'sescontest_follow_contest', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                if (!$result) {
                    $action = $activityTable->addActivity($viewer, $subject, 'sescontest_follow_contest');
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner, 'follow_sescontest', array('member_name' => $viewer->getTitle(), 'contest_title' => $subject->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST'], 'queue' => true));
            }
            $follower_id = 1;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('follow_id' => $follow->follower_id, 'message' => $this->view->translate('successfully followed.'))));
        }
    }

    public function favouriteAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        }
        if (!$this->_getParam('type') || !$this->_getParam('id')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        if ($this->_getParam('type') == 'contest') {
            $type = 'contest';
            $dbTable = 'contests';
            $resorces_id = 'contest_id';
            $notificationType = 'sescontest_favourite_contest';
        } else {
            $type = 'participant';
            $dbTable = 'participants';
            $resorces_id = 'participant_id';
            $notificationType = 'sescontest_favourite_contest_entry';
        }
        $item_id = $this->_getParam('id');
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $Fav = Engine_Api::_()->getDbTable('favourites', 'sescontest')->getItemfav($type, $item_id);
        $favItem = Engine_Api::_()->getDbtable($dbTable, 'sescontest');
        if (count($Fav) > 0) {
            //delete		
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Fav->delete();
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
            $item = Engine_Api::_()->getItem($type, $item_id);
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
            Engine_Api::_()->sesapi()->deleteFeed(array('type' => $notificationType, "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully unfavourited.'), 'count' => $item->favourite_count)));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('favourites', 'sescontest')->getAdapter();
            $db->beginTransaction();
            try {
                $fav = Engine_Api::_()->getDbTable('favourites', 'sescontest')->createRow();
                $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $fav->resource_type = $type;
                $fav->resource_id = $item_id;
                $fav->save();
                $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1')), array($resorces_id . '= ?' => $item_id));
                // Commit
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
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
                }
            }
            //End Activity Feed Work
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully favourited.'), 'count' => $item->favourite_count)));
        }
    }

    public function likeAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        if ($viewer_id == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        }
        $type = $this->_getParam('type', false);
        $item_id = $this->_getParam('id');
        if(!$type || !$item_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));

        
        if ($type == 'contest') {
            $dbTable = 'contests';
            $resorces_id = 'contest_id';
            $notificationType = 'sescontest_like_contest';
        } elseif ($type == 'participant') {
            $dbTable = 'participants';
            $resorces_id = 'participant_id';
            $notificationType = 'sescontest_like_contest_entry';
        }
        
        if (intval($item_id) == 0 || !$item_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->transalte('Invalid argument supplied.'), 'result' => array()));
        }
        $itemTable = Engine_Api::_()->getDbtable($dbTable, 'sescontest');
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
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            $item = Engine_Api::_()->getItem($type, $item_id);
            $owner = $item->getOwner();
            Engine_Api::_()->sesapi()->deleteFeed(array('type' => $notificationType, "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message'=>'Successfully unliked.','like_count'=>$item->like_count)));
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
                if ($type == 'participant' &&  Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.vote.integrate', 0)) {
                    $voteType = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'allow_entry_vote');
                    $entry = Engine_Api::_()->getItem($type, $item_id);
                    $contest_id = $entry->contest_id;
                    if ($voteType != 0 && (($voteType == 1 && $entry->owner_id != $viewer_id) || $voteType == 2)) {
                        $contest = Engine_Api::_()->getItem('contest', $contest_id);
                        if (strtotime($contest->votingstarttime) <= time() && strtotime($contest->endtime) > time()) {
                            $hasVoted = Engine_Api::_()->getDbTable('votes', 'sescontest')->hasVoted($viewer_id, $contest_id, $item_id);
                            if ($hasVoted) {
                                $voted = 0;
                            } else {
                                $isViewerJury = 0;
                                if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescontestjurymember')) {
                                    $isViewerJury = Engine_Api::_()->getDbTable('members', 'sescontestjurymember')->isJuryMember(array('user_id' => $viewer_id, 'contest_id' => $contest_id));
                                }
                                if (!$isViewerJury) {
                                    $votingTable = Engine_Api::_()->getDbTable('votes', 'sescontest');
                                    $voteCount = Engine_Api::_()->authorization()->getPermission($levelId, 'participant', 'votecount_weight');
                                    if (!$voteCount)
                                        $voteCount = 1;
                                    $vote = $votingTable->createRow();
                                    $vote->contest_id = $contest_id;
                                    $vote->participant_id = $item_id;
                                    $vote->owner_id = $viewer_id;
                                    $vote->creation_date = date('Y-m-d h:i:s');
                                    $vote->save();
                                    $itemTable->update(array('vote_date' => date('Y-m-d h:i:s'), 'vote_count' => new Zend_Db_Expr("vote_count + $voteCount")), array('participant_id= ?' => $item_id));
                                    $voted = 1;
                                } else {
                                    $voted = 0;
                                }
                            }
                        }
                    }
                }
                //Commit
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            //Send notification and activity feed work.
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
            if ($type == 'contest') {
                $contestFollowers = Engine_Api::_()->getDbTable('followers', 'sescontest')->getFollowers($subject->contest_id);
                if (count($contestFollowers) > 0) {
                    foreach ($contestFollowers as $follower) {
                        $user = Engine_Api::_()->getItem('user', $follower->user_id);
                        if ($user->getIdentity()) {
                            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'contest_like_followed');
                        }
                    }
                }
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('message'=>$this->view->translate('Successfully liked.'),'like_count'=>$item->like_count,'vote_status' => $voted,)));
        }
    }

    public function deleteAction() {
        $contestId = $this->getRequest()->getParam('contest_id');
		if(!$contestId)
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'parameter_missing', 'result' => array()));
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        $sescontest = Engine_Api::_()->getItem('contest', $contestId);
		if(!$sescontest)
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('There are no results that match your search. Please try again.'), 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams($sescontest, null, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			// In smoothbox
			//    $form = new Sescontest_Form_Delete(); // if needed then sent form
        if (!$sescontest) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Contest entry does not exist or not authorized to delete'), 'result' => array()));
        }
		
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid request method'), 'result' => array()));
        }
        $db = $sescontest->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $sescontest->delete();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => 'Your contest has been deleted successfully!'));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }

    public function createAction() {
      if (!$this->_helper->requireUser->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
	  $viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      $totalContest = Engine_Api::_()->getDbTable('contests', 'sescontest')->countContests($viewer->getIdentity());
      $allowContestCount = Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contest_count');
      if (!$this->_helper->requireAuth()->setAuthParams('contest', $viewer, 'create')->isValid())
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      //Start Package Work
       if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescontestpackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontestpackage.enable.package', 0) && (_SESAPI_VERSION_ANDROID >= 2.5 || _SESAPI_VERSION_IOS >= 1.7)) {
           $package_id = $this->_getParam('package_id', 0);
           $existingpackage_id = $this->_getParam('existing_package_id', 0);
         $package = Engine_Api::_()->getItem('sescontestpackage_package', $package_id);
         $existingpackage = Engine_Api::_()->getItem('sescontestpackage_orderspackage', $existingpackage_id);
         if ($existingpackage) {
           $package = Engine_Api::_()->getItem('sescontestpackage_package', $existingpackage->package_id);
         }

         if (!$package && !$existingpackage) {
           // check package exists for this member level
           $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'sescontestpackage')->getPackage(array('member_level' => $viewer->level_id));
           if (count($packageMemberLevel)) {
            // redirect to package page
             $packageResult = $this->contestPackage();
             if($packageResult){
                 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $packageResult));
             }else{
                 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data Not Found.'), 'result' => array()));
             }
           }
         }

         if ($existingpackage) {
           $canCreate = Engine_Api::_()->getDbTable('orderspackages', 'sescontestpackage')->checkUserPackage($this->_getParam('existing_package_id', 0), $viewer->getIdentity());
           echo '<pre>';print_r($canCreate);die;
           if (!$canCreate){
               //$packageResult = $this->contestPackage();
               Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have not permission to access this resource'), 'result' =>array()));
           }
           //return $this->_helper->redirector->gotoRoute(array('action' => 'contest'), 'sescontestpackage_general', true);
         }
       }
      //End Package Work
       $resource_id = $this->_getParam('resource_id', 0);
       $resource_type = $this->_getParam('resource_type', 0);
       $widget_id = $this->_getParam('widget_id', 0);
      if ($resource_id && $resource_type) {
        $item = Engine_Api::_()->getItem($resource_type, $resource_id);
        if (!$item)
           Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('There are no results that match your search. Please try again.'), 'result' => array()));
      }
    $category_id = $this->_getParam('category_id',0);
      if ($totalContest >= $allowContestCount && $allowContestCount != 0) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('You have no permission to create or create more Contest.'), 'result' => array()));
      } else {
        if (!$category_id && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.category.selection', 1)) {
          $categories = Engine_Api::_()->getDbTable('categories', 'sescontest')->getCategory(array('fetchAll' => true));
          if (count($categories)) {
              $categoryShowAbletypeimage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest_category_icon', 0);
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
                  $result_category['category'][$category_counter]['total_contest_categories'] = $category->total_contest_categories;
                  $category_counter++;
              }
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result_category));
          }
        }
        $form = new Sescontest_Form_Create(array(
            'defaultProfileId' => 1,
            'smoothboxType' => $sessmoothbox,
        ));
      $form->removeElement('contest_timezone_popup');
          $form->removeElement('contest_custom_datetimes');
          $form->removeElement('contest_timezone_popup');
          $form->removeElement('contest_timezone_popup_hidden');
          $form->removeElement('photo-uploader');
      $form->removeElement('contest_main_photo_preview');
      $form->removeElement('photo-uploader');
      $form->removeElement('removeimage');
      $form->removeElement('removeimage2');
        if ($category_id )
          $form->category_id->setValue($category_id);
        //START QUICK CONTEST CREATION WORK
        $refereneId = $this->_getParam('ref', 0);
        if ($refereneId ) {
          $contest = Engine_Api::_()->getItem('contest', $refereneId);
          $form->title->setValue($contest->title);
          $tagStr = '';
          foreach ($contest->tags()->getTagMaps() as $tagMap) {
            $tag = $tagMap->getTag();
            if (!isset($tag->text))
              continue;
            if ('' !== $tagStr)
              $tagStr .= ', ';
            $tagStr .= $tag->text;
          }
          $form->populate(array(
              'tags' => $tagStr,
          ));
          $form->contest_type->setValue($contest->contest_type);
          $form->category_id->setValue($contest->category_id);
          $form->subcat_id->setValue($contest->subcat_id);
          $form->subsubcat_id->setValue($contest->subsubcat_id);
          $form->description->setValue($contest->description);
          $form->award->setValue($contest->award);
          $form->rules->setValue($contest->rules);
          $form->vote_type->setValue($contest->vote_type);
        }
      }
      //END QUICK CONTEST CREATION WORK
      // Not post/invalid

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
      //check custom url
      if (isset($_POST['custom_url_contest']) && !empty($_POST['custom_url_contest'])) {
        $custom_url = Engine_Api::_()->getDbtable('contests', 'sescontest')->checkCustomUrl($_POST['custom_url_contest']);
        if ($custom_url) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Custom Url not available.Please select other."), 'result' => array()));
        }
      }
      // Process
      $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'] . ' ' . $_POST['start_time'])) : '';
      $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'] . ' ' . $_POST['end_time'])) : '';
      $joinStartTime = isset($_POST['join_start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['join_start_date'] . ' ' . $_POST['join_start_time'])) : '';
      $joinEndTime = isset($_POST['join_end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['join_end_date'] . ' ' . $_POST['join_end_time'])) : '';
      $votingStartTime = isset($_POST['voting_start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['voting_start_date'] . ' ' . $_POST['voting_start_time'])) : '';
      $votingEndTime = isset($_POST['voting_end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['voting_end_date'] . ' ' . $_POST['voting_end_time'])) : '';
      $resutTime = isset($_POST['result_date']) ? date('Y-m-d H:i:s', strtotime($_POST['result_date'] . ' ' . $_POST['result_time'])) : '';
      $values = $form->getValues();
      $values['user_id'] = $viewer->getIdentity();
      $values['timezone'] = isset($_POST['timezone']) ? $_POST['timezone'] : '';
      if (empty($values['timezone'])) {
        $values['timezone'] = $viewer->timezone;
      }
    if (empty($values['timezone'])) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Timezone is a required field.'));
        }
      if (strtotime($starttime)>=strtotime($endtime)) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Start Time must be less than End Time.'));
        }
      if(strtotime( $joinStartTime) < strtotime($starttime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Join Start Time must be less than Contest Start Time.'));
      }
      if(strtotime($joinEndTime) > strtotime($endtime) ){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Join End Time must be less than Contest End Time.'));
      }
      if(strtotime( $joinStartTime) >= strtotime($joinEndTime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Join Start Time must be less than Join End Time.'));
      }
      if(strtotime( $votingStartTime) >= strtotime( $joinStartTime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Voting Start Time must be less than Join Start Time.'));
      }
      if(strtotime($votingStartTime) >= strtotime($votingEndTime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Voting Start Time must be less than Voting End Time.'));
      }
      if(strtotime($votingEndTime) >= strtotime($endtime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Voting End Time must be less than Contest Start Time.'));
      }
	  if(strtotime($resutTime) < strtotime($endtime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Result date is lower than the contest End date. Please enter the result date greater than or equal to contest End date.'));
      }


      $error = Engine_Api::_()->sescontest()->dateValidations($_POST);
      if ($error[0]) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_($error[1]));

      }
      $settings = Engine_Api::_()->getApi('settings', 'core');
      if ($settings->getSetting('sescontest.contestmainphoto', 1)) {
        if (isset($values['photo']) && empty($values['photo'])) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
        }
      }
    if ($values['vote_type'] == 1 && !isset($_POST['result_date']) &&  !isset($_POST['result_time'])){
      $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
    }
    if (!$form->isValid($_POST)) {
              $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
              if (count($validateFields))
                  $this->validateFormFields($validateFields);
        }
      // Convert times
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($values['timezone']);
      $start = strtotime($starttime);
      $end = strtotime($endtime);
      $joinStart = strtotime($joinStartTime);
      $joinEnd = strtotime($joinEndTime);
      $votingStart = strtotime($votingStartTime);
      $votingEnd = strtotime($votingEndTime);
      $ResultDate = strtotime($resutTime);
      date_default_timezone_set($oldTz);
      $values['starttime'] = date('Y-m-d H:i:s', $start);
      $values['endtime'] = date('Y-m-d H:i:s', $end);
      $values['joinstarttime'] = date('Y-m-d H:i:s', $joinStart);
      $values['joinendtime'] = date('Y-m-d H:i:s', $joinEnd);
      $values['votingstarttime'] = date('Y-m-d H:i:s', $votingStart);
      $values['votingendtime'] = date('Y-m-d H:i:s', $votingEnd);
      $values['resulttime'] = date('Y-m-d H:i:s', $ResultDate);
      if (!$values['vote_type'])
        $values['resulttime'] = '';
      $contestTable = Engine_Api::_()->getDbtable('contests', 'sescontest');
      $db = $contestTable->getAdapter();
      $db->beginTransaction();
      try {
        // Create contest
        $contest = $contestTable->createRow();

        $sescontest_draft = $settings->getSetting('sescontest.draft', 1);
        if (empty($sescontest_draft)) {
          $values['draft'] = 1;
        }
        if (empty($values['category_id']))
          $values['category_id'] = 0;
        if (empty($values['subsubcat_id']))
          $values['subsubcat_id'] = 0;
        if (empty($values['subcat_id']))
          $values['subcat_id'] = 0;
        if (isset($package)) {
          $values['package_id'] = $package->getIdentity();
          if ($package->isFree()) {
            if (isset($params['contest_approve']) && $params['contest_approve'])
              $values['is_approved'] = 1;
          } else
            $values['is_approved'] = 0;
          if ($existingpackage) {
            $values['existing_package_order'] = $existingpackage->getIdentity();
            $values['orderspackage_id'] = $existingpackage->getIdentity();
            $existingpackage->item_count = $existingpackage->item_count - 1;
            $existingpackage->save();
            $params = json_decode($package->params, true);
            if (isset($params['contest_approve']) && $params['contest_approve'])
              $values['is_approved'] = 1;
            if (isset($params['contest_featured']) && $params['contest_featured'])
              $values['featured'] = 1;
            if (isset($params['contest_sponsored']) && $params['contest_sponsored'])
              $values['sponsored'] = 1;
            if (isset($params['contest_verified']) && $params['contest_verified'])
              $values['verified'] = 1;
            if (isset($params['contest_hot']) && $params['contest_hot'])
              $values['hot'] = 1;
          }
        } else {
          if (!isset($package) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescontestpackage')) {
            $values['package_id'] = Engine_Api::_()->getDbTable('packages', 'sescontestpackage')->getDefaultPackage();
          }
        }
        $contest->setFromArray($values);
		//echo '<pre>';print_r($values);die;
		$searchSetting = $settings->getSetting('sescontest.search', 1);
		if($searchSetting)
			$contest->search = 1;
		else
			$contest->search = 0;
		
        $contest->save();

        //Start Default Package Order Work
        if (isset($package) && $package->isFree()) {
          if (!$existingpackage) {
            $transactionsOrdersTable = Engine_Api::_()->getDbtable('orderspackages', 'sescontestpackage');
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
            $contest->orderspackage_id = $transactionsOrdersTable->getAdapter()->lastInsertId();
            $contest->existing_package_order = 0;
          } else {
            $existingpackage->item_count = $existingpackage->item_count--;
            $existingpackage->save();
          }
        }
        //End Default package Order Work

        if ($resource_id && $resource_type) {
          $contest->resource_id = $resource_id;
          $contest->resource_type = $resource_type;
          $contest->save();
        }
        $contest->seo_keywords = implode(',', $tags);
        //$contest->seo_title = $contest->title;
        $contest->save();
        $tags = preg_split('/[,]+/', $values['tags']);
        $contest->tags()->addTagMaps($viewer, $tags);

        $count = 0;
        if (!empty($values['award']))
          $count++;
        if (!empty($values['award2']))
          $count++;
        if (!empty($values['award3']))
          $count++;
        if (!empty($values['award4']))
          $count++;
        if (!empty($values['award5']))
          $count++;

        $contest->award_count = $count;
        $contest->save();

        if (!isset($package)) {
          if (!Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contest_approve'))
            $contest->is_approved = 0;
          if (Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contest_featured'))
            $contest->featured = 1;
          if (Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'autosponsored'))
            $contest->sponsored = 1;
          if (Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contest_verified'))
            $contest->verified = 1;
          if (Engine_Api::_()->authorization()->getPermission($levelId, 'contest', 'contest_hot'))
            $contest->hot = 1;
        }

        // Add photo
        if (!empty($values['photo'])) {
          $contest->setPhoto($form->photo);
        }
        // Set auth
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

        if (empty($values['auth_view'])) {
          $values['auth_view'] = 'everyone';
        }
        if (empty($values['auth_comment'])) {
          $values['auth_comment'] = 'everyone';
        }
        $viewMax = array_search($values['auth_view'], $roles);
        $commentMax = array_search($values['auth_comment'], $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($contest, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($contest, $role, 'comment', ($i <= $commentMax));
        }
        //Add fields
        $customfieldform = $form->getSubForm('fields');
        if ($customfieldform) {
          $customfieldform->setItem($contest);
          $customfieldform->saveValues();
        }
        $contest->save();
        // Commit
        $db->commit();
        if (!empty($_POST['custom_url_contest']) && $_POST['custom_url_contest'] != '')
          $contest->custom_url = $_POST['custom_url_contest'];
        else
          $contest->custom_url = $contest->contest_id;
        $contest->save();
        $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.autoopenpopup', 1);
        if ($autoOpenSharePopup && $contest->draft && $contest->is_approved) {
          $_SESSION['newContest'] = true;
        }
        //Start Activity Feed Work
        if ($contest->draft == 1 && $contest->is_approved == 1) {
          $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
          $action = $activityApi->addActivity($viewer, $contest, 'sescontest_create');
          if ($action) {
            $activityApi->attachActivity($action, $contest);
          }
        }
        //End Activity Feed Work
        //Start Send Approval Request to Admin
        if (!$contest->is_approved) {
          if (isset($package) && $package->price > 0) {
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($contest->getOwner(), $viewer, $contest, 'sescontest_payment_notify_contest');
          } else {
            Engine_Api::_()->sescontest()->sendMailNotification(array('contest' => $contest));
          }
        }
        if (!empty($item)) {
          $tab = "";
          if ($widget_id)
            $tab = "/tab/" . $widget_id;
          //header('location:' . $item->getHref() . $tab);
          //die;
        }

        //End Work Here.
        $redirection = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.redirect', 1);
        $redirect = $redirection?'view':'dashboard';
        if (!$contest->is_approved) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Contest created successfully and send to admin approval.'), 'contest_id' => 0)));
        } else{
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('contest_id' => $contest->getIdentity(),'redirect'=>$redirect, 'success_message' => $this->view->translate('Contest created successfully.'))));
      }

      } catch (Engine_Image_Exception $e) {
        $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The image you selected was too large.'), 'result' => array()));
      } catch (Exception $e) {
        $db->rollBack();
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }

    public function contestPackage() {
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $result = array();
        $packages = $packageMemberLevel = Engine_Api::_()->getDbTable('packages', 'sescontestpackage')->getPackage(array('member_level' => $viewer->level_id, 'enabled' => 0));
        if (!count($packageMemberLevel) || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontestpackage.enable.package', 0))
            return true;
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'sescontestpackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontestpackage.package.info', array_keys($information));
        if(count($existingleftpackages)){
            $counterleft = 0;
            foreach ($existingleftpackages as $leftpackages) {
                $package = Engine_Api::_()->getItem('sescontestpackage_package', $leftpackages->package_id);
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
                                $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sescontestpackage()->getCurrencyPrice($package->price,'','',true));
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
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sescontestpackage()->getCurrencyPrice($package->price,'','',true));
                    else
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                    $paramscounter ++;
                    if(in_array('featured',$showinfo)){
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_featured';
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_sponsored'];
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                        $paramscounter ++;
                    }

                    if(in_array('sponsored',$showinfo)){
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_verified';
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_verified'];
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                        $paramscounter ++;
                    }
                    if(in_array('hot',$showinfo)){
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_hot';
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_hot'];
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                        $paramscounter ++;
                    }

                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_bgphoto';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_bgphoto'];
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
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_choose_style';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_choose_style'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                    if(in_array('description',$showinfo)){
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                        $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                        $paramscounter ++;
                    }
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_count';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Contests Count');
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
                    $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->sescontestpackage()->getCurrencyPrice($package->price,'','',true);
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
                        $result['packages'][$counter]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sescontestpackage()->getCurrencyPrice($packages->price,'','',true));
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
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sescontestpackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'contest_featured';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['contest_sponsored'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('sponsored',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'contest_verified';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['contest_verified'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'contest_hot';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['contest_hot'];
                    $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'contest_bgphoto';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['contest_bgphoto'];
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
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'contest_choose_style';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $enableModulesPackages['contest_choose_style'];
                $result['packages'][$counter]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['packages'][$counter]['params'][$paramscounter]['name'] = 'package_description';
                    $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->description;
                    $paramscounter ++;
                }
                $result['packages'][$counter]['params'][$paramscounter]['name'] = 'contest_count';
                $result['packages'][$counter]['params'][$paramscounter]['label'] = $this->view->translate('Contests Count');
                $result['packages'][$counter]['params'][$paramscounter]['value'] = $packages->item_count;
                $paramscounter ++;
                $result['packages'][$counter]['price_type'] = Engine_Api::_()->sescontestpackage()->getCurrencyPrice($packages->price,'','',true);
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
        $existingleftpackages = Engine_Api::_()->getDbTable('orderspackages', 'sescontestpackage')->getLeftPackages(array('owner_id' => $viewer->getIdentity()));
        $information = array('description' => 'Package Description', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'hot' => 'Hot', 'custom_fields' => 'Custom Fields');
        $showinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontestpackage.package.info', array_keys($information));
        $currentCurrency =  Engine_Api::_()->sescontestpackage()->getCurrentCurrency();
        $result = array();
        $counterleft =0;
        if(count($existingleftpackages)){
            foreach($existingleftpackages as $packageleft)	{
                $package = Engine_Api::_()->getItem('sescontestpackage_package',$packageleft->package_id);
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
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sescontestpackage()->getCurrencyPrice($package->price,'','',true));
                    else
                        $result['existingleftpackages'][$counterleft]['payment_type'] =  $this->view->translate('Free');
                }else{
                    $result['existingleftpackages'][$counterleft]['payment_type'] = $this->view->translate("FREE");
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'recurrence_type';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Billing Cycle');
                if($package->recurrence_type == 'day')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Daily');
                elseif($package->price && $package->recurrence_type != 'forever')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate(ucfirst($package->recurrence_type).'ly');
                elseif($package->recurrence_type == 'forever')
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = sprintf($this->view->translate('One-time fee of %1$s'), Engine_Api::_()->sescontestpackage()->getCurrencyPrice($package->price,'','',true));
                else
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $this->view->translate('Free');
                $paramscounter ++;
                if(in_array('featured',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_featured';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Featured');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_sponsored'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }

                if(in_array('sponsored',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_verified';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Verified');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_verified'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                if(in_array('hot',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_hot';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Hot');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_hot'];
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_bgphoto';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Background Photo');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_bgphoto'];
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
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_choose_style';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Select Design Layout');
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $enableModules['contest_choose_style'];
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['action'] = 'image';
                $paramscounter ++;
                if(in_array('description',$showinfo)){
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'package_description';
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Package Description');
                    $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['value'] = $package->description;
                    $paramscounter ++;
                }
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['name'] = 'contest_count';
                $result['existingleftpackages'][$counterleft]['params'][$paramscounter]['label'] = $this->view->translate('Contests Count');
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
                $result['existingleftpackages'][$counterleft]['price_type'] = Engine_Api::_()->sescontestpackage()->getCurrencyPrice($package->price,'','',true);
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
        $tableTransaction = Engine_Api::_()->getItemTable('sescontestpackage_transaction');
        $tableTransactionName = $tableTransaction->info('name');
        $contestTable = Engine_Api::_()->getDbTable('contests', 'sescontest');
        $contestTableName = $contestTable->info('name');
        $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
        $select = $tableTransaction->select()
            ->setIntegrityCheck(false)
            ->from($tableTransactionName)
            ->joinLeft($tableUserName, "$tableTransactionName.owner_id = $tableUserName.user_id", 'username')
            ->where($tableUserName . '.user_id IS NOT NULL')
            ->joinLeft($contestTableName, "$tableTransactionName.transaction_id = $contestTableName.transaction_id", 'contest_id')
            ->where($contestTableName . '.contest_id IS NOT NULL')
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
        $result['title'] = $this->view->translate('View Transactions of Contest Packages');
        $counter = 0;
        foreach($paginator as $item){
            $user = Engine_Api::_()->getItem('user',$item->owner_id);
            $contest = Engine_Api::_()->getItem('contest',$item->contest_id);
            $package = Engine_Api::_()->getItem('sescontestpackage_package',$item->package_id);
            $data[$counter]['transaction_id'] = $item->transaction_id;
            $data[$counter]['id'] = $item->contest_id;
            $data[$counter]['title'] = $this->view->translate(Engine_Api::_()->sesbasic()->textTruncation($contest->getTitle(),25));
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
        $form = new Sescontestpackage_Form_Cancel();
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

        Engine_Api::_()->getDbTable('packages','sescontestpackage')->cancelSubscription(array('package_id' => $packageId));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => array('message'=>$this->view->translate('Your Package Subscription has been Deleted Successfully.'))));
    }
    public function editAction() {
    $contestId = $this->_getParam('contest_id',null);
    if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
               $contest = Engine_Api::_()->core()->getSubject();
          else
        $contest = Engine_Api::_()->getItem('contest', $contestId);
    $contest = Engine_Api::_()->getItem('contest', $contestId);
	//custom_url_contest
	//echo '<pre>';print_r($contest->toArray());die;
    if(!$contest)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      $previous_starttime = $contest->starttime;
      $previous_endtime = $contest->endtime;
      $previous_joinstarttime = $contest->joinstarttime;
      $previous_joinendtime = $contest->joinendtime;
      $previous_votingstarttime = $contest->votingstarttime;
      $previous_votingendtime = $contest->votingendtime;
    $result_time = $contest->resulttime;
      //Contest Category and profile fileds
       $defaultProfileId = 1;
      if (isset($contest->category_id) && $contest->category_id != 0)
        $category_id = $contest->category_id;
      else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
        $category_id = $_POST['category_id'];
      else
        $category_id = 0;
      if (isset($contest->subsubcat_id) && $contest->subsubcat_id != 0)
        $subsubcat_id = $contest->subsubcat_id;
      else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
        $subsubcat_id = $_POST['subsubcat_id'];
      else
        $subsubcat_id = 0;
      if (isset($contest->subcat_id) && $contest->subcat_id != 0)
        $subcat_id = $contest->subcat_id;
      else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
        $subcat_id = $_POST['subcat_id'];
      else
        $subcat_id = 0;
      //Contest category and profile fields
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer)))
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    // contest time
    $contestStartDate = date('Y-m-d h:i:s', strtotime($previous_starttime));
    $contest_start_date = date('m/d/y', strtotime($contestStartDate));
    $contest_start_time = date('g:i A', strtotime($contestStartDate));
    $contestEndDate = date('Y-m-d h:i:s', strtotime($previous_endtime));
    $contest_end_date = date('m/d/y', strtotime($contestEndDate));
    $contest_end_time = date('g:i A', strtotime($contestEndDate));
    // join time
    $joinStartDate = date('Y-m-d h:i:s', strtotime($previous_joinstarttime));
    $join_start_date = date('m/d/y', strtotime($joinStartDate));
    $join_start_time = date('g:i A', strtotime($joinStartDate));
    $joinEndDate = date('Y-m-d h:i:s', strtotime($previous_joinendtime));
    $join_end_date = date('m/d/y', strtotime($joinEndDate));
    $join_end_time = date('g:i A', strtotime($joinEndDate));
    // voting time
    $votingStartDate = date('Y-m-d h:i:s', strtotime($previous_votingstarttime));
    $voting_start_date = date('m/d/y', strtotime($votingStartDate));
    $voting_start_time = date('g:i A', strtotime($votingStartDate));
    $votingEndDate = date('Y-m-d h:i:s', strtotime($previous_votingendtime));
    $voting_end_date = date('m/d/y', strtotime($votingEndDate));
    $voting_end_time = date('g:i A', strtotime($votingEndDate));
    // result time
    $resultDate = date('Y-m-d h:i:s', strtotime($result_time));
    $result_start_date = date('m/d/y', strtotime($resultDate));
    $result_start_time = date('g:i A', strtotime($resultDate));

      $form = new Sescontest_Form_Edit(array('defaultProfileId' => $defaultProfileId));
      $category_id = $contest->category_id;
      $subcat_id = $contest->subcat_id;
      $subsubcat_id = $contest->subsubcat_id;
      $tagStr = '';
      foreach ($contest->tags()->getTagMaps() as $tagMap) {
        $tag = $tagMap->getTag();
        if (!isset($tag->text))
          continue;
        if ('' !== $tagStr)
          $tagStr .= ', ';
        $tagStr .= $tag->text;
      }
	  
      $form->populate(array(
          'tags' => $tagStr,
      ));
      if (!$this->getRequest()->isPost()) {
        // Populate auth
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        foreach ($roles as $role) {
          if (isset($form->auth_view->options[$role]) && $auth->isAllowed($contest, $role, 'view'))
            $form->auth_view->setValue($role);
          if (isset($form->auth_comment->options[$role]) && $auth->isAllowed($contest, $role, 'comment'))
            $form->auth_comment->setValue($role);
        }
        if ($form->draft->getValue() == 1)
          $form->removeElement('draft');
      }
    $form->populate($contest->toArray());
    if($contest->custom_url){
        if($form->custom_url_contest != null)
            $form->custom_url_contest->setValue($contest->custom_url);
    }
    if($form->start_date)
      $form->start_date->setValue($contest_start_date);
    if($form->start_time)
      $form->start_time->setValue($contest_start_time);
    if($form->end_date)
      $form->end_date->setValue($contest_end_date);
    if($form->end_time)
      $form->end_time->setValue($contest_end_time);
    if($form->join_start_date)
      $form->join_start_date->setValue($join_start_date);
    if($form->join_start_time)
      $form->join_start_time->setValue($join_start_time);
    if($form->join_end_date)
      $form->join_end_date->setValue($join_end_date);
    if($form->join_end_time)
      $form->join_end_time->setValue($join_end_time);
    if($form->join_start_date)
      $form->voting_start_date->setValue($voting_start_date);
    if($form->voting_start_time)
      $form->voting_start_time->setValue($voting_start_time);
    if($form->voting_end_date)
      $form->voting_end_date->setValue($voting_end_date);
    if($form->voting_end_time)
      $form->voting_end_time->setValue($voting_end_time);
    if($form->result_date)
      $form->result_date->setValue($result_start_date);
    if($form->result_time)
      $form->result_time->setValue($result_start_time);
     $form->removeElement('contest_custom_datetimes');
     $form->removeElement('contest_timezone_popup');
     $form->removeElement('award');
     $form->removeElement('draft');
	 if($form->getElement('contest_timezone_popup_hidden'))
		 $form->removeElement('contest_timezone_popup_hidden');
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
      //check custom url
      if (isset($_POST['custom_url_contest']) && !empty($_POST['custom_url_contest'])) {
        $custom_url = Engine_Api::_()->getDbtable('contests', 'sescontest')->checkCustomUrl($_POST['custom_url_contest'], $contest->contest_id);
        if ($custom_url) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Custom Url not available.Please select other.'));
        }
      }
      $values = $form->getValues();
	 
      if (strtotime($contest->starttime) > time()) {
        $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'] . ' ' . $_POST['start_time'])) : '';
        $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'] . ' ' . $_POST['end_time'])) : '';
        $joinStartTime = isset($_POST['join_start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['join_start_date'] . ' ' . $_POST['join_start_time'])) : '';
        $joinEndTime = isset($_POST['join_end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['join_end_date'] . ' ' . $_POST['join_end_time'])) : '';
        $votingStartTime = isset($_POST['voting_start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['voting_start_date'] . ' ' . $_POST['voting_start_time'])) : '';
        $votingEndTime = isset($_POST['voting_end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['voting_end_date'] . ' ' . $_POST['voting_end_time'])) : '';
        $resutTime = isset($_POST['result_date']) ? date('Y-m-d H:i:s', strtotime($_POST['result_date'] . ' ' . $_POST['result_time'])) : '';
        // Process
        $values['timezone'] = $_POST['timezone'] ? $_POST['timezone'] : '';
        $values['show_timezone'] = !empty($_POST['show_timezone']) ? $_POST['show_timezone'] : '0';
        $values['show_endtime'] = !empty($_POST['show_endtime']) ? $_POST['show_endtime'] : '0';
        $values['show_starttime'] = !empty($_POST['show_starttime']) ? $_POST['show_starttime'] : '0';
        if (empty($values['timezone'])) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Timezone is a required field.'));
        }
      if (strtotime($starttime) >= strtotime($endtime)) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Start Time must be less than End Time.'));
        }
      if(strtotime( $joinStartTime) < strtotime($starttime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Join Start Time must be less than Contest Start Time.'));
      }
      if(strtotime($joinEndTime) > strtotime($endtime) ){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Join End Time must be less than Contest End Time.'));
      }
      if(strtotime( $joinStartTime) >= strtotime($joinEndTime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Join Start Time must be less than Join End Time.'));
      }
      if(strtotime( $votingStartTime) >= strtotime( $joinStartTime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Voting Start Time must be less than Join Start Time.'));
      }
      if(strtotime($votingStartTime) >= strtotime($votingEndTime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Voting Start Time must be less than Voting End Time.'));
      }
      if(strtotime($votingEndTime) >= strtotime($endtime)){
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Voting End Time must be less than Contest Start Time.'));
      }

        $error = Engine_Api::_()->sescontest()->dateValidations($_POST);
        if (isset($error[0]) && $error[0]) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_($error[1]));
        }
      if (!$form->isValid($_POST)) {
              $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
              if (count($validateFields))
                  $this->validateFormFields($validateFields);
        }
        // Convert times
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($values['timezone']);
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        $joinStart = strtotime($joinStartTime);
        $joinEnd = strtotime($joinEndTime);
        $votingStart = strtotime($votingStartTime);
        $votingEnd = strtotime($votingEndTime);
        $ResultDate = strtotime($resutTime);
        date_default_timezone_set($oldTz);
        $values['starttime'] = date('Y-m-d H:i:s', $start);
        $values['endtime'] = date('Y-m-d H:i:s', $end);
        $values['joinstarttime'] = date('Y-m-d H:i:s', $joinStart);
        $values['joinendtime'] = date('Y-m-d H:i:s', $joinEnd);
        $values['votingstarttime'] = date('Y-m-d H:i:s', $votingStart);
        $values['votingendtime'] = date('Y-m-d H:i:s', $votingEnd);
        $values['resulttime'] = date('Y-m-d H:i:s', $ResultDate);
      }

      // Process
      $db = Engine_Api::_()->getItemTable('contest')->getAdapter();
      $db->beginTransaction();
      try {
        if (!($values['draft']))
          unset($values['draft']);
        $contest->setFromArray($values);
        $contest->save();
        $tags = preg_split('/[,]+/', $values['tags']);
        $contest->tags()->setTagMaps($viewer, $tags);

        if (!$values['vote_type'])
			$values['resulttime'] = '';
	  
        $contest->save();
		if (!empty($_POST['custom_url_contest']) && $_POST['custom_url_contest'] != '')
          $contest->custom_url = $_POST['custom_url_contest'];
        else
          $contest->custom_url = $contest->contest_id;
	  $contest->save();

        // Add photo
        if (!empty($values['photo'])) {
          $contest->setPhoto($form->photo);
        }
        // Add cover photo
        if (!empty($values['cover'])) {
          $contest->setCover($form->cover);
        }
        // Set auth
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (empty($values['auth_view']))
          $values['auth_view'] = 'everyone';
        if (empty($values['auth_comment']))
          $values['auth_comment'] = 'everyone';
        $viewMax = array_search($values['auth_view'], $roles);
        $commentMax = array_search($values['auth_comment'], $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($contest, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($contest, $role, 'comment', ($i <= $commentMax));
        }
        //Add fields
        $customfieldform = $form->getSubForm('fields');
        if ($customfieldform) {
          $customfieldform->setItem($contest);
          $customfieldform->saveValues();
        }
        $contest->save();
        $db->commit();
        //Start Activity Feed Work
        if (isset($values['draft']) && $contest->draft == 1 && $contest->is_approved == 1) {
          $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
          $action = $activityApi->addActivity($viewer, $contest, 'sescontest_create');
          if ($action) {
            $activityApi->attachActivity($action, $contest);
          }
        }
        //End Activity Feed Work
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
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' => $this->view->translate('Contest edited successfully.'))));
      } catch (Exception $e) {
        $db->rollBack();
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }

    public function mainphotoAction() {
      if (!Engine_Api::_()->authorization()->isAllowed('contest', $this->view->viewer(), 'upload_mainphoto'))
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $contestId = $this->_getParam('contest_id',null);
    if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
      $contest = Engine_Api::_()->core()->getSubject();
    else
      $contest = Engine_Api::_()->getItem('contest', $contestId);
    if(!$contest)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      // Get form
    $form = new Sescontest_Form_Dashboard_Mainphoto();
      if (empty($contest->photo_id)) {
        $form->removeElement('remove');
      }
    $form->removeElement('current');
    $form->populate($contest->toArray());

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
      // Uploading a new photo
      if ($form->Filedata->getValue() !== null) {
        $db = $contest->getTable()->getAdapter();
        $db->beginTransaction();
        try {
          $fileElement = $form->Filedata;
          $photo_id = Engine_Api::_()->sesapi()->setPhoto($fileElement, false, false, 'sescontest', 'contest', '', $contest, true);
          $contest->photo_id = $photo_id;
          $contest->save();
          $db->commit();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' => $this->view->translate('Mainphoto edited successfully.'))));
        }
        // If an exception occurred within the image adapter, it's probably an invalid image
        catch (Engine_Image_Adapter_Exception $e) {
          $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The uploaded file is not supported or is corrupt.'), 'result' => array()));
        }
        // Otherwise it's probably a problem with the database or the storage system (just throw it)
        catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
      }
    }

    public function seoAction() {
      $contestId = $this->_getParam('contest_id',null);
    if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
      $contest = Engine_Api::_()->core()->getSubject();
    else
      $contest = Engine_Api::_()->getItem('contest', $contestId);
    if(!$contest)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer)) || !Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'contest_seo'))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error.'), 'result' => array()));
      // Create form
      $form = new Sescontest_Form_Dashboard_Seo();
      $form->populate($contest->toArray());
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
      $db = Engine_Api::_()->getDbtable('contests', 'sescontest')->getAdapter();
      $db->beginTransaction();
      try {
        $contest->setFromArray($_POST);
        $contest->save();
        $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' => $this->view->translate('SEO edited successfully.'))));
      } catch (Exception $e) {
        $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }

    public function contactInformationAction() {
      $contestId = $this->_getParam('contest_id',null);
    if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
      $contest = Engine_Api::_()->core()->getSubject();
    else
      $contest = Engine_Api::_()->getItem('contest', $contestId);
    if(!$contest)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer)) || !Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'contactinfo'))
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error.'), 'result' => array()));
      // Create form
      $form = new Sescontest_Form_Dashboard_Contactinformation();
      $form->populate($contest->toArray());
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
      $db = Engine_Api::_()->getDbtable('contests', 'sescontest')->getAdapter();
      $db->beginTransaction();
      try {
        $contest->contest_contact_name = isset($_POST['contest_contact_name']) ? $_POST['contest_contact_name'] : '';
        $contest->contest_contact_email = isset($_POST['contest_contact_email']) ? $_POST['contest_contact_email'] : '';
        $contest->contest_contact_phone = isset($_POST['contest_contact_phone']) ? $_POST['contest_contact_phone'] : '';
        $contest->contest_contact_website = isset($_POST['contest_contact_website']) ? $_POST['contest_contact_website'] : '';
        $contest->contest_contact_facebook = isset($_POST['contest_contact_facebook']) ? $_POST['contest_contact_facebook'] : '';
        $contest->contest_contact_twitter = isset($_POST['contest_contact_twitter']) ? $_POST['contest_contact_twitter'] : '';
        $contest->contest_contact_linkedin = isset($_POST['contest_contact_linkedin']) ? $_POST['contest_contact_linkedin'] : '';
        $contest->save();
        $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' =>$this->view->translate('Information Edited Successfully.'))));
      } catch (Exception $e) {
        $db->rollBack();
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }

    public function rulesAction() {
      $contestId = $this->_getParam('contest_id',null);
    if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
      $contest = Engine_Api::_()->core()->getSubject();
    else
      $contest = Engine_Api::_()->getItem('contest', $contestId);
    if(!$contest)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer)))
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error.'), 'result' => array()));
      // Create form
      $form = new Sescontest_Form_Dashboard_Rules();
      $form->populate($contest->toArray());
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
      $db = Engine_Api::_()->getDbtable('contests', 'sescontest')->getAdapter();
      $db->beginTransaction();
      try {
        $contest->setFromArray($_POST);
        $contest->save();
        $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' =>$this->view->translate('Rules Edited Successfully.'))));
      } catch (Exception $e) {
        $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }

    public function awardAction() {
      $contestId = $this->_getParam('contest_id',null);
    if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
      $contest = Engine_Api::_()->core()->getSubject();
    else
      $contest = Engine_Api::_()->getItem('contest', $contestId);
    if(!$contest)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer)))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error.'), 'result' => array()));
      // Create form
       $form = new Sescontest_Form_Dashboard_Award();
      $form->populate($contest->toArray());
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
      $db = Engine_Api::_()->getDbtable('contests', 'sescontest')->getAdapter();
      $db->beginTransaction();
      try {
        $contest->setFromArray($_POST);
        $count = 0;
        if (!empty($_POST['award']))
          $count++;
        if (!empty($_POST['award2']))
          $count++;
        if (!empty($_POST['award3']))
          $count++;
        if (!empty($_POST['award4']))
          $count++;
        if (!empty($_POST['award5']))
          $count++;
        $contest->award_count = $count;
        $contest->save();
        $db->commit();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' =>$this->view->translate('Award Edited Successfully.'))));
      } catch (Exception $e) {
        $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }

    public function overviewAction() {
      $contestId = $this->_getParam('contest_id',null);
    if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
      $contest = Engine_Api::_()->core()->getSubject();
    else
      $contest = Engine_Api::_()->getItem('contest', $contestId);
    if(!$contest)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer)) || !Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'contest_overview'))
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error.'), 'result' => array()));
      // Create form
      $this->view->form = $form = new Sescontest_Form_Dashboard_Overview();
      $form->populate($contest->toArray());
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
      $db = Engine_Api::_()->getDbtable('contests', 'sescontest')->getAdapter();
      $db->beginTransaction();
      try {
        $contest->setFromArray($_POST);
        $contest->save();
        $db->commit();
        //Activity Feed Work
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $activityApi->addActivity($viewer, $contest, 'contest_editcontestoverview');
        if ($action) {
          $activityApi->attachActivity($action, $contest);
        }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' =>$this->view->translate('Overview Edited Successfully.'))));
      } catch (Exception $e) {
        $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }

    public function backgroundphotoAction() {
   $contestId = $this->_getParam('contest_id',null);
	if (Engine_Api::_()->core()->hasSubject('sescontest_review'))
		$contest = Engine_Api::_()->core()->getSubject();
	else
		$contest = Engine_Api::_()->getItem('contest', $contestId);
	if(!$contest)
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer)) || !Engine_Api::_()->authorization()->isAllowed('contest', $viewer, 'contest_bgphoto'))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error.'), 'result' => array()));
    // Create form
    $this->view->form = $form = new Sescontest_Form_Dashboard_Backgroundphoto();
	$form->removeElement('dragdropbackground');
	$form->removeElement('contest_main_photo_preview');
	$form->removeElement('removeimage');
	$form->removeElement('removeimage2');
	
    $form->populate($contest->toArray());
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
    $db = Engine_Api::_()->getDbtable('contests', 'sescontest')->getAdapter();
    $db->beginTransaction();
    try {
      $contest->setBackgroundPhoto($_FILES['background'], 'background');
      $contest->save();
      $db->commit();
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' =>$this->view->translate('Background Photo Edited Successfully.'))));
    } catch (Exception $e) {
      $db->rollBack();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }

    public function contestWinnerAction(){
    $contestId = $this->_getParam('contest_id',null);
    if ($contestId)
      $contest = Engine_Api::_()->getItem('contest', $contestId);
    if(!$contest)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $filterOptionCounter = 0;
    $filteroption[$filterOptionCounter]['name'] = 'high';
    $filteroption[$filterOptionCounter]['label'] = $this->view->translate('Rank High to Low');
    $filterOptionCounter++;
    $filteroption[$filterOptionCounter]['name'] = 'low';
    $filteroption[$filterOptionCounter]['label'] = $this->view->translate('Rank Low to High');
    $params['sort']= $this->_getParam('search_filter',null);
    $params['contest_id'] = $contestId;
    $params['fetchAll'] = true ;
    $paginator = Engine_Api::_()->getDbTable('participants', 'sescontest')->getWinnerSelect($params);
    $counter = 0;
    $result = array();
    $viewer = Engine_Api::_()->user()->getViewer();
    foreach ($paginator as $entries){
      $entry = $entries->toArray();
      $result[$counter] = $entry;
      $contest = Engine_Api::_()->getItem('contest', $entries->contest_id);
      $result[$counter]['contest_type'] = $contest->contest_type;
      $canComment = Engine_Api::_()->authorization()->isAllowed('participant', $viewer, 'comment');
      $likeStatus = Engine_Api::_()->sescontest()->getLikeStatus($entries->participant_id, $entries->getType());
      $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sescontest')->isFavourite(array('resource_id' => $entries->participant_id, 'resource_type' => $entries->getType()));
      $owner = $entries->getOwner();
      $title = $entries->getTitle();
      if ($title)
        $result[$counter]['entry_title'] = $title;
      $viewerId = $viewer->getIdentity();
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
      if ($likeStatus)
        $result[$counter]['is_content_like'] = true;
      else
        $result[$counter]['is_content_like'] = false;
      if ($favouriteStatus)
        $result[$counter]['is_content_favourite'] = true;
      else
        $result[$counter]['is_content_favourite'] = false;
      if ($canComment)
        $result[$counter]['can_comment'] = true;
      else
        $result[$counter]['can_comment'] = false;
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sescontest.entry.allow.favourite', 1) && $viewer->getIdentity())
        $result[$counter]['can_follow'] = true;
      else
        $result[$counter]['can_follow'] = false;

      $counter++;
    }
    if($result){
      $data['options'] = $filteroption;
      $data['winners'] = $result;
    }

    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data)));
  }
  
    public function contactParticipantsAction() {
		//$is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
	 $contestId = $this->_getParam('contest_id',null);
       if ($contestId)
         $contest = Engine_Api::_()->getItem('contest', $contestId);
       if(!$contest)
         Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
	   $contest = Engine_Api::_()->core()->getSubject();
	   $viewer = Engine_Api::_()->user()->getViewer();
	   if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $contest->isOwner($viewer )) || !Engine_Api::_()->authorization()->isAllowed('contest', $viewer , 'contparticipant'))
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
	   $participants = Engine_Api::_()->getDbTable('participants', 'sescontest')->getContestMembers($contest->getIdentity(), '');
	   $result = array();
	   if(count($participants) > 0){
		   $counter = 0;
		   foreach($participants as $participant){
			   $result['members'][$counter] = $participant->toArray();
			   $counter++;
		   }
		    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' =>$result ));
	   }else{
		   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' =>$result ));
	   }
	}
	
	function sendMessage($participants, $contest) {
    // Assign the composing stuff
    $composePartials = array();
    $prohibitedPartials = array('_composeTwitter.tpl', '_composeFacebook.tpl');
    foreach (Zend_Registry::get('Engine_Manifest') as $data) {
      if (empty($data['composer'])) {
        continue;
      }
      foreach ($data['composer'] as $type => $config) {
        // is the current user has "create" privileges for the current plugin
        if (isset($config['auth'], $config['auth'][0], $config['auth'][1])) {
          $isAllowed = Engine_Api::_()
                  ->authorization()
                  ->isAllowed($config['auth'][0], null, $config['auth'][1]);

          if (!empty($config['auth']) && !$isAllowed) {
            continue;
          }
        }
        if (!in_array($config['script'][0], $prohibitedPartials)) {
          $composePartials[] = $config['script'];
        }
      }
    }
    //$this->view->composePartials = $composePartials;
    // Create form
    $form = new Sescontest_Form_Dashboard_Compose();
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
    // Process
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      // Try attachment getting stuff
      $attachment = null;
      $attachmentData = $this->getRequest()->getParam('attachment');
      if (!empty($attachmentData) && !empty($attachmentData['type'])) {
        $type = $attachmentData['type'];
        $config = null;
        foreach (Zend_Registry::get('Engine_Manifest') as $data) {
          if (!empty($data['composer'][$type])) {
            $config = $data['composer'][$type];
          }
        }
        if ($config) {
          $plugin = Engine_Api::_()->loadClass($config['plugin']);
          $method = 'onAttach' . ucfirst($type);
          $attachment = $plugin->$method($attachmentData);
          $parent = $attachment->getParent();
          if ($parent->getType() === 'user') {
            $attachment->search = 0;
            $attachment->save();
          } else {
            $parent->search = 0;
            $parent->save();
          }
        }
      }

      $viewer = Engine_Api::_()->user()->getViewer();
      $values = $form->getValues();
      $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
      if ($actionName == 'contact-participants') {
        foreach ($participants as $participant)
          $userIds[] = $participant->user_id;
      } else
      $userIds = $_POST['winner'];
      $recipientsUsers = Engine_Api::_()->getItemMulti('user', $userIds);
      // Create conversation
      $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
              $viewer, $userIds, $values['title'], $values['body'], $attachment
      );
      // Send notifications
      foreach ($recipientsUsers as $user) {
        if ($user->getIdentity() == $viewer->getIdentity()) {
          continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                $user, $viewer, $conversation, 'message_new'
        );
      }
      // Increment messages counter
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');
      // Commit
      $db->commit();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('contest_id' => $contest->getIdentity(), 'success_message' =>$this->view->translate('Message sent successfully.'))));
    } catch (Exception $e) {
      $db->rollBack();
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
}
