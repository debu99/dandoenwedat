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
class Sesqa_IndexController extends Sesapi_Controller_Action_Standard{
	public function init(){
	// only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('sesqa_question', null, 'view')->isValid())
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    // Get subject
    $question = null;
    if( null !== ($questionIdentity = $this->_getParam('question_id',$this->_getParam('id')))) {
      $question = Engine_Api::_()->getItem('sesqa_question', $questionIdentity);
      if( null !== $question ) {
        Engine_Api::_()->core()->setSubject($question);
      }
    }
	}
	public function menuAction(){
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesqa_main', array());
		$menu_counter = 0;
		foreach ($menus as $menu) {
			$class = end(explode(' ', $menu->class));
			$result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
			$result_menu[$menu_counter]['action'] = $class;
			$result_menu[$menu_counter]['isActive'] = $menu->active;
			$menu_counter++;
		}
		$result['menus'] = $result_menu;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result)));
	}
	public function browseAction(){
		$coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
		$coreContentTableName = $coreContentTable->info('name');
		$corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
		$corePagesTableName = $corePagesTable->info('name');
		$select = $corePagesTable->select()
			->setIntegrityCheck(false)
			->from($corePagesTable, null)
			->where($coreContentTableName . '.name=?', 'sesqa.browse-search')
			->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
			->where($corePagesTableName . '.name = ?', 'sesqa_index_browse');
		$id = $select->query()->fetchColumn();
		$default_search_type = $this-> _getParam('default_search_type', 'recentlySPcreated');
		if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa_enable_location', 1)){
			$location = 'yes';
		}else
			$location = 'no';
		$form = new Sesqa_Form_Browsesearch(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type,'startendTime' => $this->_getParam('search_startendtime', 'yes')));
		$form->populate($_POST);
		$params = $form->getValues();
		$value = array();
		$value['search'] = 1;
    $value['draft'] = 1;
		if ($this->_getParam('search_type',null)){
      switch ($this->_getParam('search_type',null)){
        case 'most_viewed':
          $value['popularCol'] = 'view_count';
          break;
        case 'most_liked':
          $value['popularCol'] = 'like_count';
          break;
        case 'most_commented':
          $value['popularCol'] = 'comment_count';
          break;
        case 'most_favourite':
          if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.allow.favourite', 1)) {
          $value['popularCol'] = 'favourite_count';
          }
          break;
        case 'homost_answered':
          $value['popularCol'] = 'answer_count';
          break;
        case 'most_voted':
          $value['popularCol'] = 'vote_count';
          break;
        case 'unanswered':
          $value['popularCol'] = "unanswered";
        break;
        case 'featured':
          $value['fixedColumn'] = "featured";
        break;
        case 'sponsored':
          $value['fixedColumn'] = "sponsored";
        break;
        case 'verified':
          $value['fixedColumn'] = "verified";
        break;
        case 'hot':
          $value['fixedColumn'] = "hot";
        break;
        case 'new':
          $value['fixedColumn'] = "new";
        break;
        case 'recently_created':
          $value['popularCol'] = 'creation_date';
				break;
				default:
					$value['popularCol'] = 'creation_date';
				break;
      }
    }else{
			$value['popularCol'] = 'creation_date';
		}
		$params = array_merge($params, $value);
		$paginator =  Engine_Api::_()->getDbTable('questions','sesqa')->getQuestionPaginator($params);
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$page = $this->_getParam('page', 1);
		$manage = false;
		$result['questions'] = $this->getQuestions($paginator,$manage);
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	public function browsesearchAction(){
    $option = array('recentlySPcreated' =>'Recently Created','mostSPviewed' =>'Most Viewed','mostSPliked'=>'Most Liked','mostSPcommented' =>'Most Commented','mostSPvoted' => 'Most Voted','mostSPfavourite' => 'Most Favourite','homostSPanswered' =>'Most Answered','unanswered' =>'Unanswered');
    $filterOptions = (array)$this->_getParam('search_type', $option);
    $this->view->view_type = $this-> _getParam('view_type', 'horizontal');
		$setting = Engine_Api::_()->getApi('settings', 'core');
		$default_search_type = $this-> _getParam('default_search_type', 'recentlySPcreated');
		if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa_enable_location', 1)){
			$location = 'yes';
		}else
			$location = 'no';
		$arrayOptions = $filterOptions;
		$filterOptions = array();
    $enableNewSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa_enable_newLabel', 1);
		foreach ($arrayOptions as $key=>$filterOption) {
      if($filterOption == "new" && !$enableNewSetting)
        continue;
      $value = isset($option[$filterOption]) ? $option[$filterOption] : str_replace(array('SP',''), array(' ',' '), $filterOption);
      $filterOptions[$filterOption] = ucwords($value);
    }
		$filterOptions = array(''=>'')+$filterOptions;
		$form = new Sesqa_Form_Browsesearch(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type,'startendTime' => $this->_getParam('search_startendtime', 'yes')));
		$form->order->setMultiOptions($option);
		$form->removeElement('lat');
		$form->removeElement('lng');
		$form->removeElement('loading-img-sesqa');
		if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'sespage_page'));
		} else {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
	}
	public function getQuestions($paginator,$manage){
		$result = array();
		$counter = $polllabel = 0;
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		foreach($paginator as $question){
			$questionlabel =  0;
			$result[$counter] = $question->toArray();
			if($manage){
				$optionCounter = 0;
				$canEdit = $question->authorization()->isAllowed($viewer, 'edit');
				$canDelete = $question->authorization()->isAllowed($viewer, 'delete');
				if($canEdit){
					$result[$counter]['options'][$optionCounter]['name'] = 'edit';
					$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('SESEdit');
					$optionCounter++;
				}
				if($canDelete){
					$result[$counter]['options'][$optionCounter]['name'] = 'delete';
					$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('SESDelete');
				}
			}
			$LikeStatus = Engine_Api::_()->sesqa()->getLikeStatusQuestion($question->getIdentity(),$question->getType());
			$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesqa')->isFavourite(array('resource_type'=>$question->getType(),'resource_id'=>$question->getIdentity()));
			$FollowUser = Engine_Api::_()->sesqa()->getFollowStatus($question->getIdentity());
			$shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('qanda_allow_sharing', 1);
			$pollOptions = count($question->getOptions());
			$owner = $question->getOwner();
			$ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner,'',"thumb.profile");
			if(empty($ownerimage)) {
        $photos = array();
        $photos["main"] = $this->getBaseUrl(false,"application/modules/User/externals/images/nophoto_user_thumb_profile.png");
        $result[$counter]['owner_image'] = $photos;
			} else {
        $result[$counter]['owner_image'] = $ownerimage;
			}
			
			$result[$counter]['owner_title'] = $owner->getTitle();
			$result[$counter]['vote_count'] = Engine_Api::_()->sesqa()->voteCount($question);
			$newSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa_new_label', 5);
			$enableNewSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa_enable_newLabel', 1);
			$isVote = Engine_Api::_()->getDbTable('voteupdowns','sesqa')->isVote(array('resource_id'=>$question->getIdentity(),'resource_type'=>$question->getType(),'user_id'=>$viewer->getIdentity(),'user_type'=>'user'));
			if($isVote){
				$result[$counter]['has_voted'] = $isVote->type;
			}
			if($newSetting && $enableNewSetting && strtotime(date("Y-m-d H:i:s")) <= strtotime($question->creation_date." + ".$newSetting." Days")){
				$result[$counter]['new_question_label'][$questionlabel]['label'] = $this->view->translate('New Question');
				$questionlabel++;
			}
			if($question->hot){
				$result[$counter]['new_question_label'][$questionlabel]['label'] = $this->view->translate('Hot Question');
				$questionlabel++;
			}
			if($question->sponsored){
				$result[$counter]['new_question_label'][$questionlabel]['label'] = $this->view->translate('Sponsored Question');
				$questionlabel++;
			}
			if($question->verified){
				$result[$counter]['new_question_label'][$questionlabel]['label'] = $this->view->translate('Verified Question');
				$questionlabel++;
			}
			if($question->featured){
				$result[$counter]['new_question_label'][$questionlabel]['label'] = $this->view->translate('Featured Question');
				$questionlabel++;
			}
			$pollOptions = count($question->getOptions());
			if($pollOptions || $question->best_answer){
				if($pollOptions){
					$result[$counter]['poll_label']['label'] = $this->view->translate('SESPoll');
				}
			}
			if($question->category_id){
				$category_id = $question->category_id;
				$category = Engine_Api::_()->getItem('sesqa_category',$question->category_id);
				if($category){
					$result[$counter]['category_title'] = $category->category_name;
				}
			}
			$result[$counter]['creation_date'] = date('d M Y',strtotime($question->creation_date));
			$tags = array();
			foreach ($question->tags()->getTagMaps() as $tagmap) {
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
			if($viewer->getIdentity()){
				$result[$counter]['is_content_like'] = $LikeStatus?true:false;
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.allow.favourite', 1)) {
				$result[$counter]['is_content_favourite'] = $favStatus?true:false;
				}
				$result[$counter]['is_content_follow'] = $FollowUser?true:false;
			}
			if($shareType){
				$result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $question->getPhotoUrl());
				$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$question->getHref());
				$result[$counter]["share"]["title"] = $question->getTitle();
				$result[$counter]["share"]["description"] = strip_tags($question->getDescription());
				$result[$counter]["share"]["setting"] = $shareType;
				$result[$counter]["share"]['urlParams'] = array(
					"type" => $question->getType(),
					"id" => $question->getIdentity()
				);
			}
			$counter++;
		}
		return $result;
	}
	public function categoriesAction(){
		$paginator = Engine_Api::_()->getDbTable('categories', 'sesqa')->getCategory(array('paginator'=>true));
		$paginator->setItemCountPerPage(20);
		$paginator->setCurrentPageNumber(1);
		$categorydata = array();
		$categorycounter = 0;
		foreach ($paginator as $category) {
			$categorydata[$categorycounter] = $category->toArray();
			$color_icon = $category->colored_icon;
			$image = $category->getPhotoUrl('colored_icon');
			if($image)
				$categorydata[$categorycounter]['image'] = $this->getbaseurl(false, $image);
			$categorycounter++;
		}
		$result['category'] = $categorydata;
		$page = $this->_getParam('page', 1);
		if($this->getCategory($paginator))
		    $result['categories'] = $this->getCategory($paginator);
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	public function getCategory($categoryPaginator) {
		$result = array();
		$counter = 0;
		foreach ($categoryPaginator as $categories) {
			$category = $categories->toArray();
			if ($categories->total_qa_categories == 0)
			continue;
			$result[$counter] = $category;
			$result[$counter]['count'] = $categories->total_qa_categories;
			$params['category_id'] = $categories->category_id;
			$params['limit'] = 3;
			$paginator =  Engine_Api::_()->getDbTable('questions','sesqa')->getQuestionPaginator($params);
			$paginator->setItemCountPerPage(3);
			if ($paginator->getPages()->pageCount > 1) {
					$result[$counter]['see_all'] = true;
			} else {
					$result[$counter]['see_all'] = false;
			}
			$manage = false;
			$question = $this->getQuestions($paginator,$manage);
			if ($question)
					$result[$counter]['items'] = $question;
			$counter++;
		}
		$results = $result;
		return $results;
	}
	public function categoryviewAction(){
		$categoryId = $this->_getParam('category_id', null);
		$subCategoryId = $this->_getParam('sub_category_id', null);
		$subSubCategoryId = $this->_getParam('Sub_subcategory_id', null);
		if ($categoryId) {
			$category = Engine_Api::_()->getItem('sesqa_category', $categoryId);
		}else if($subCategoryId){
			$category = Engine_Api::_()->getItem('sesqa_category', $subCategoryId);
		}else if($subSubCategoryId){
			$category = Engine_Api::_()->getItem('sesqa_category', $subSubCategoryId);
		} else {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => ' parameter_missing', 'result' => array()));
		}
		if($this->_getParam('page',null)==1){
			$result['qa_category'] = $category->toArray();
		  if (isset($category->thumbnail) && !empty($category->thumbnail)) {
				$image = Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl('thumb.main');
				$result['qa_category']['images']['main'] = $this->getbaseurl(false, $image);
			}
		  if ($category->subcat_id == 0 && $category->subsubcat_id == 0) {
				$innerCatData = Engine_Api::_()->getDbtable('categories', 'sesqa')->getModuleSubcategory(array('category_id' => $category->category_id, 'column_name' => '*', 'countQuestions' => true, 'getcategory0' => true));
				$columnCategory = 'category_id';
				$countersubCat = 0;
				foreach ($innerCatData as $item) {
					$result['sub_category'][$countersubCat] = $item->toArray();
					if ($item->thumbnail != '' && !is_null($item->thumbnail) && intval($item->thumbnail)) {
						$image = Engine_Api::_()->storage()->get($item->thumbnail)->getPhotoUrl('thumb.main');
						$result['sub_category'][$countersubCat]['images']['main'] = $this->getbaseurl(false, $image);
					}
					if ($item->cat_icon != '' && !is_null($item->cat_icon) && intval($item->cat_icon)){
						$image = Engine_Api::_()->storage()->get($item->thumbnail)->getPhotoUrl('thumb.icon');
						$result['sub_category'][$countersubCat]['images']['icon'] = $this->getbaseurl(false, $image);
					}
					$result['sub_category'][$countersubCat]['count'] = $this->view->translate(array('%s question', '%s questions', $item->total_qa_categories), $this->view->locale()->toNumber($item->total_qa_categories));
					$countersubCat++;
				}
			}
		}
		$data['category_id'] = $categoryId;
		if($subCategoryId)
			$data['subcat_id'] = $subCategoryId;
		if($subSubCategoryId)
		$data['subsubcat_id'] = $subSubCategoryId;
		$data['getcategory0'] = true;
		$paginator = Engine_Api::_()->getDbTable('questions','sesqa')->getQuestionPaginator($data);
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$manage = false;
		if($this->getQuestions($paginator,$manage))
		$result['questions'] = $this->getQuestions($paginator,$manage);
		$counter = 0;
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
	}
  public function manageAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		$user_id = $viewer_id = $viewer->getIdentity();
		if (!$viewer_id) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
		}
		$value['getParamSort'] = $this->_getParam('search_type','my_questions');
		if (isset($value['getParamSort'])) {
      switch ($value['getParamSort']) {
				case 'my_questions':
					$paginator = $this->view->paginator = Engine_Api::_()->getDbTable('questions','sesqa')->getQuestionPaginator(array('user_id'=>$user_id,'managePage'=>true,'popularCol'=>'creation_date'));
					break;
				case 'question_answered':
					$questionTable = Engine_Api::_()->getDbTable('questions','sesqa');
					$answerTable = Engine_Api::_()->getDbTable('answers','sesqa');
					$customSelect = $questionTable->info('name').'.question_id IN (SELECT question_id FROM '.$answerTable->info('name').' WHERE owner_id = '.$user_id.')';
					$select = $questionTable->select()->where($customSelect);
					$paginator = $this->view->paginator = Zend_Paginator::factory($select);
					break;
				case 'question_voted':
					$questionTable = Engine_Api::_()->getDbTable('questions','sesqa');
					$votesTable = Engine_Api::_()->getDbTable('votes','sesqa');
					$customSelect = $questionTable->info('name').'.question_id IN (SELECT question_id FROM '.$votesTable->info('name').' WHERE user_id = '.$user_id.' GROUP BY question_id)';
					$select = $questionTable->select()->where($customSelect);
					$paginator = $this->view->paginator = Zend_Paginator::factory($select);
					break;
				case 'question_upvoted':
					$questionTable = Engine_Api::_()->getDbTable('questions','sesqa');
					$votesTable = Engine_Api::_()->getDbTable('voteupdowns','sesqa');
					$customSelect = $questionTable->info('name').'.question_id IN (SELECT resource_id FROM '.$votesTable->info('name').' WHERE user_id = '.$user_id.' AND resource_type = "sesqa_question" AND type = "upvote")';
					$select = $questionTable->select()->where($customSelect);
					$paginator = $this->view->paginator = Zend_Paginator::factory($select);
				break;
				case 'question_downvoted':
					$questionTable = Engine_Api::_()->getDbTable('questions','sesqa');
					$votesTable = Engine_Api::_()->getDbTable('voteupdowns','sesqa');
					$customSelect = $questionTable->info('name').'.question_id IN (SELECT resource_id FROM '.$votesTable->info('name').' WHERE user_id = '.$user_id.' AND resource_type = "sesqa_question" AND type = "downvote")';
					$select = $questionTable->select()->where($customSelect);
					$paginator = $this->view->paginator = Zend_Paginator::factory($select);
				break;
				case 'question_liked':
					$questionTable = Engine_Api::_()->getDbTable('questions','sesqa');
					$likesTable = Engine_Api::_()->getDbTable('likes','core');
					$customSelect = $questionTable->info('name').'.question_id IN (SELECT resource_id FROM '.$likesTable->info('name').' WHERE poster_id = '.$user_id.' AND resource_type = "sesqa_question" AND poster_type = "user")';
					$select = $questionTable->select()->where($customSelect);
					$paginator = $this->view->paginator = Zend_Paginator::factory($select);
					break;
				case 'question_favourite':
					$questionTable = Engine_Api::_()->getDbTable('questions','sesqa');
					$favTable = Engine_Api::_()->getDbTable('favourites','sesqa');
					$customSelect = $questionTable->info('name').'.question_id IN (SELECT resource_id FROM '.$favTable->info('name').' WHERE user_id = '.$user_id.' AND resource_type = "sesqa_question")';
					$select = $questionTable->select()->where($customSelect);
					$paginator = $this->view->paginator = Zend_Paginator::factory($select);
					break;
				case 'question_follow':
					$questionTable = Engine_Api::_()->getDbTable('questions','sesqa');
					$followsTable = Engine_Api::_()->getDbTable('follows','sesqa');
					$customSelect = $questionTable->info('name').'.question_id IN (SELECT resource_id FROM '.$followsTable->info('name').' WHERE user_id = '.$user_id.')';
					$select = $questionTable->select()->where($customSelect);
					$paginator = $this->view->paginator = Zend_Paginator::factory($select);
					break;
			}
		}

		if ($this->_getParam('menus',null) == 1) {
			$filterMenucounter = 0;
			$resultmenu[$filterMenucounter]['name'] = 'my_questions';
			$resultmenu[$filterMenucounter]['label'] = $this->view->translate('My Questions');
			$filterMenucounter++;
			$resultmenu[$filterMenucounter]['name'] = 'question_answered';
			$resultmenu[$filterMenucounter]['label'] = $this->view->translate('Answered Questions');
			$filterMenucounter++;
			$resultmenu[$filterMenucounter]['name'] = 'question_voted';
			$resultmenu[$filterMenucounter]['label'] = $this->view->translate('Voted Questions');
			$filterMenucounter++;
			$resultmenu[$filterMenucounter]['name'] = 'question_upvoted';
			$resultmenu[$filterMenucounter]['label'] = $this->view->translate('Up Voted Questions');
			$filterMenucounter++;
			$resultmenu[$filterMenucounter]['name'] = 'question_downvoted';
			$resultmenu[$filterMenucounter]['label'] = $this->view->translate('Down Voted Questions');
			$filterMenucounter++;
			$resultmenu[$filterMenucounter]['name'] = 'question_liked';
			$resultmenu[$filterMenucounter]['label'] = $this->view->translate('Liked Questions');
			$filterMenucounter++;
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.allow.favourite', 1)) {
                $resultmenu[$filterMenucounter]['name'] = 'question_favourite';
                $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Favourite Questions');
                $filterMenucounter++;
			}
			$resultmenu[$filterMenucounter]['name'] = 'question_follow';
			$resultmenu[$filterMenucounter]['label'] = $this->view->translate('Question Followed');
			$filterMenucounter++;
			$result['menus'] = $resultmenu;
		}
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$manage = true;
		$result['questions'] = $this->getQuestions($paginator,$manage);
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
	}
	public function voteAction(){
    // Check auth
    if( !$this->_helper->requireUser()->isValid()){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    //if( !$this->_helper->requireSubject()->isValid() ) {
      //Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
   // }
    //if(!$this->_helper->requireAuth()->setAuthParams(null, null, 'vote')->isValid()) {
     //  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
   // }

    // Check method
    if(!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    $option_id = $this->_getParam('option_id');
    $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('question.canchangevote', true);
    $question = Engine_Api::_()->core()->getSubject('sesqa_question');
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$question ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This question does not seem to exist anymore.'), 'result' => array()));
    }
   // $hashElement = $this->view->voteHashSesqa($question)->getElement();
    //if (!$hashElement->isValid($this->_getParam('token'))) {
      //$this->view->success = false;
      //$this->view->error = join(';', $hashElement->getMessages());
      //return;
    //}
    if( $question->open_close ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This question is closed.'), 'result' => array()));
    }
    if( $question->hasVoted($viewer) && !$canChangeVote && !$question->multi ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have already voted on this question, and are not permitted to change your vote.'), 'result' => array()));
    }
    $db = Engine_Api::_()->getDbtable('questions', 'sesqa')->getAdapter();
    $db->beginTransaction();
    try {
      $question->vote($viewer, $option_id);
      $db->commit();
			$data['success'] = true;
			$data['votes_total'] = $question->vote_count;
			$data['token'] = $this->view->voteHashSesqa($question)->generateHash();
    } catch( Exception $e ) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $e->getMessage(), 'result' => array()));
    }
    $pollOptions = array();
		$counter = 0;
    foreach( $question->getOptions()->toArray() as $option ) {
      $data['vote_detail'][$counter] = $this->view->translate(array('%s vote', '%s votes', $option['votes']), $this->view->locale()->toNumber($option['votes']));
      $counter++;
    }
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => $data));
  }
  public function likeAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    $type = 'sesqa_question';
    $dbTable = 'questions';
    $resorces_id = 'question_id';
    $notificationType = 'liked';
    $item_id = $this->_getParam('id');
    if (intval($item_id) == 0) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $itemTable = Engine_Api::_()->getDbtable($dbTable, 'sesqa');
    $select = $tableLike->select()->from($tableMainLike)->where('resource_type =?', $type)->where('poster_id =?', Engine_Api::_()->user()->getViewer()->getIdentity())->where('poster_type =?', 'user')->where('resource_id =?', $item_id);
    $Like = $tableLike->fetchRow($select);
    if (count($Like) > 0) {
      //delete
      $db = $Like->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Like->delete();
        $db->commit();
				$temp['data']['message'] = $this->view->translate('Poll Successfully Unliked.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $e->getMessage(), 'result' => array()));
      }
      $item = Engine_Api::_()->getItem($type, $item_id);
      $item->like_count = $item->like_count--;
      $item->save();
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
			$temp['data']['like_count'] = $item->like_count;
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
				$temp['data']['message'] = $this->view->translate('Poll Successfully liked.');
      } catch (Exception $e) {
        $db->rollBack();
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
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
			$temp['data']['like_count'] = $item->like_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
  }
  public function favouriteAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    $type = 'sesqa_question';
    $dbTable = 'questions';
    $resorces_id = 'question_id';
    $notificationType = 'sesqa_question_favourite';
    $item_id = $this->_getParam('id');
    if (intval($item_id) == 0) {
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $Fav = Engine_Api::_()->getDbTable('favourites', 'sesqa')->getItemfav($type, $item_id);
    $favItem = Engine_Api::_()->getDbtable($dbTable, 'sesqa');
    if (count($Fav) > 0) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
				$temp['data']['message'] = 'Poll Successfully Unfavourited.';
      } catch (Exception $e) {
        $db->rollBack();
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
      $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
      $temp['data']['favourite_count'] = $item->favourite_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('favourites', 'sesqa')->getAdapter();
      $db->beginTransaction();
      try {
        $fav = Engine_Api::_()->getDbTable('favourites', 'sesqa')->createRow();
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
				$temp['data']['message'] = 'Poll Successfully favourited.';
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem(@$type, @$item_id);
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
      $temp['data']['favourite_count'] = $item->favourite_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
  }
	public function followAction() {
	if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
	}
	$item_id = $this->_getParam('id');
	if (intval($item_id) == 0) {
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
	}
	$itemObj = Engine_Api::_()->getItem('sesqa_question',$item_id);
	$viewer = Engine_Api::_()->user()->getViewer();
	$viewer_id = $viewer->getIdentity();
	$itemTable = Engine_Api::_()->getItemTable('sesqa_question');
	$tableFollow = Engine_Api::_()->getDbtable('follows', 'sesqa');
	$tableMainFollow = $tableFollow->info('name');
	$select = $tableFollow->select()
		->from($tableMainFollow)
		->where('resource_id = ?', $item_id)
		->where('user_id = ?', $viewer_id);
	$result = $tableFollow->fetchRow($select);
	if (count($result) > 0){
		//delete
		$db = $result->getTable()->getAdapter();
		$db->beginTransaction();
		try {
			$result->delete();
			//$itemObj->follow_count = $itemObj->follow_count--;
			//$itemObj->save();
			$itemTable->update(array('follow_count' => new Zend_Db_Expr('follow_count - 1')), array('question_id = ?' => $item_id));
			$db->commit();
			$temp['data']['message'] = 'Page Successfully Unfollowed.';
			$user = Engine_Api::_()->getItem('user', $item_id);
			//Unfollow notification Work: Delete follow notification and feed
			Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "sesqa_qafollow", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $user->getType(), "object_id = ?" => $user->getIdentity()));
			Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => "sesqa_follow", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $user->getType(), "object_id = ?" => $user->getIdentity()));
		} catch (Exception $e) {
			$db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
		}
		$selectUser = $itemTable->select()->where('question_id =?', $item_id);
		$user = $itemTable->fetchRow($selectUser);
		$temp['data']['follow_count'] = $item->follow_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
	} else {
		//update
		$db = Engine_Api::_()->getDbTable('follows', 'sesqa')->getAdapter();
		$db->beginTransaction();
		try {
			$follow = $tableFollow->createRow();
			$follow->user_id = $viewer_id;
			$follow->resource_id = $item_id;
			$follow->save();
		 // $itemObj->follow_count = $itemObj->follow_count++;
			//$itemObj->save();
			$itemTable->update(array('follow_count' => new Zend_Db_Expr('follow_count + 1')), array('question_id = ?' => $item_id));
			//Commit
			$db->commit();
			$temp['data']['message'] = 'Page Successfully Followed.';
		} catch (Exception $e) {
			$db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
		}
		//Send notification and activity feed work.
		$selectUser = $itemTable->select()->where('question_id =?', $item_id);
		$item = $itemTable->fetchRow($selectUser);
		$subject = $item;
		$owner = $subject->getOwner();
		if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer_id) {
			$activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
			Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'sesqa_qafollow', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
			Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, 'sesqa_qafollow');
			$result = $activityTable->fetchRow(array('type =?' => 'sesqa_follow', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
			if (!$result) {
				$action = $activityTable->addActivity($viewer, $subject, 'sesqa_follow');
			}
			//echo '<pre>';print_r($subject->getOwner()->getTitle());die;
			//follow mail to another user
			//Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner()->email, 'sesqa_qafollow', array('sender_title' => $viewer->getTitle(), 'object_link' => $viewer->getHref(), 'host' => $_SERVER['HTTP_HOST'], 'title' => $question->getTitle(), 'member_name' => $viewer->getTitle()));
		}
			$temp['data']['follow_count'] = $item->follow_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
		}
	}
	public function deleteAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $question = Engine_Api::_()->core()->getSubject('sesqa_question');
    if (!$this->_helper->requireAuth()->setAuthParams($question, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    // In smoothbox
		//$this->_helper->layout->setLayout('default-simple');
    $form = new Sesbasic_Form_Delete();
    //$form->setTitle('Delete Question?');
    //$form->setDescription('Are you sure that you want to delete this question? It will not be recoverable after being deleted. ');
    //$form->submit->setLabel('Delete');
    if (!$question) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Question doesn't exists or not authorized to delete"), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Invalid request method"), 'result' => array()));
    }
    $redirectUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sesqa_general', true);
    $db = $question->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getApi('core', 'sesqa')->deleteQuestion($question);
      $db->commit();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Question has been deleted.'), $status)));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
	}
	public function createAction(){
    if( !$this->_helper->requireAuth()->setAuthParams('sesqa_question', null, 'view')->isValid() )
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
     // Render
    //$sesqa_create = Zend_Registry::isRegistered('sesqa_create') ? Zend_Registry::get('sesqa_create') : null;
    //if(!empty($sesqa_create)) {
     // $this->_helper->content->setEnabled();
    //}
    if (!$this->_helper->requireAuth()->setAuthParams('sesqa_question', null, 'create')->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $values['fetchAll'] = true;
    $data['current_count'] = $current_count = count(Engine_Api::_()->getDbtable('questions', 'sesqa')->getQuestions($values));
    $data['quota'] = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesqa_question', 'max');
    $options = array();
    $data['maxOptions'] = $maxOptions = $max_options = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.maxoptions', 15);
		$data['resources_type'] = 'sesqa_question';
    $form = new Sesqa_Form_Create();
    // Check options
    $options = (array) $this->_getParam('optionsArray');
    $options = array_filter(array_map('trim', $options));
    $options = array_slice($options, 0, $max_options);
    $data['options'] = $options;
    $data['multi'] = !empty($_POST['multi']) ? $_POST['multi'] : "";
    if(Engine_Api::_()->core()->hasSubject('sesqa_question')){
      $subject = Engine_Api::_()->core()->getSubject();
      $arrayQuestion = $subject->toArray();
      if($subject->location){
        $latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData($subject->getType(),$subject->getIdentity());
        if($latLng){
          $arrayQuestion['lat'] = $latLng["lat"];
          $arrayQuestion['lng'] = $latLng["lng"];
        }
      }
      $form->populate($arrayQuestion);
      $form->title->setValue('');
      $options = (array) $this->_getParam('optionsArray');
      $pollOptions = $subject->getOptions();
      if(!count($options)){
        foreach($pollOptions as $optn){
           $options[] = $optn->poll_option;
        }
      }
    $data['isPollDisable'] = $isPollDisable = $question->vote_count && count($pollOptions);
    $options = array_filter(array_map('trim', $options));
    $options = array_slice($options, 0, $max_options);
    $data['options'] = $options;
    if($form->video)
      $form->video->setValue($subject->video_url);
      // authorization
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      foreach ($roles as $role) {
        if (1 === $auth->isAllowed($subject, $role, 'view')) {
          $form->auth_view->setValue($role);
        }
        if (1 === $auth->isAllowed($subject, $role, 'comment')) {
          $form->auth_comment->setValue($role);
        }
        if (1 === $auth->isAllowed($subject, $role, 'answer')) {
          $form->auth_answer->setValue($role);
        }
      }
      // prepare tags
      $questionTags = $subject->tags()->getTagMaps();
      $tagString = '';
      foreach ($questionTags as $tagmap) {
        $tag = $tagmap->getTag();
        if(isset($tag)) {
        if ($tagString !== '')
          $tagString .= ', ';
        $tagString .= $tag->getTitle();
        }
      }
      if($form->tags)
        $form->tags->setValue($tagString);
      if($subject->draft == 1)
        $form->removeElement('draft');
    }
			$form->removeElement('lat');
			$form->removeElement('map-canvas');
			$form->removeElement('ses_location');
			$form->removeElement('lng');
    if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields,$data);
		}
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
    if(!empty($_POST['is_poll']) &&  (empty($options) || !is_array($options) || count($options) < 2 )) {
			$form->addError('You must provide at least two possible answers.');
    }
		if (!$form->isValid($_POST)) {
			$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
    foreach( $options as $index => $option ) {
      if( strlen($option) > 255 ) {
        $options[$index] = Engine_String::substr($option, 0, 255);
      }
    }

    $db = Engine_Api::_()->getItemTable('sesqa_question')->getAdapter();
    $sesqaOptionsTable = Engine_Api::_()->getDbtable('options', 'sesqa');
    $db->beginTransaction();
    try {
      $question = Engine_Api::_()->getItemTable('sesqa_question')->createRow();
      // Add tags
      $values = $form->getValues();
      if( empty($values['auth_view']) )
       $values['auth_view'] = 'everyone';

      if( empty($values['auth_comment']) )
       $values['auth_comment'] = 'everyone';

      if( empty($values['auth_answer']) )
       $values['auth_answer'] = 'everyone';
      $values['owner_id'] = $viewer->getIdentity();
      $values['view_privacy'] = $values['auth_view'];
      $question->setFromArray($values);

      if(!empty($_FILES['photo']['name']) && $values['mediatype'] == 1)
       $question->setPhoto($form->photo);

      if($values['video'] && $values['mediatype'] == 2) {
        $information = $this->handleIframelyInformation($values['video']);
        try{
          $question->setPhoto($information['thumbnail']);
        }catch(Exception $e){
          //silence
        }
        $question->video_url = $values['video'];
        $question->code = $information['code'];
      }
      $question->save();
      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $answerMax = array_search($values['auth_answer'], $roles);
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($question, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($question, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($question, $role, 'answer', ($i <= $answerMax));
      }
      $auth->setAllowed($question, 'registered', 'vote', true);
      // Add tags
      $tags = preg_split('/[,]+/', trim($values['tags'],', '));
      $question->tags()->addTagMaps($viewer, $tags);
      if(!empty($_POST['is_poll'])){
        // poll data insert
        $censor = new Engine_Filter_Censor();
        $html = new Engine_Filter_Html(array('AllowedTags'=> array('a')));
        foreach( $options as $option ) {
          $option = $censor->filter($html->filter($option));
          $sesqaOptionsTable->insert(array(
            'question_id' => $question->getIdentity(),
            'poll_option' => $option,
          ));
        }
        $question->multi = !empty($_POST['multi']) ? $_POST['multi'] : 0;
        $question->save();
      }
        //Auto Approve Work
			if(Engine_Api::_()->authorization()->isAllowed('sesqa_question', null, 'sesqa_autoapp')) {
				$question->approved = 1;
				$question->save();
			} else if(empty($question->approved)) {
				$getSuperAdmins = Engine_Api::_()->user()->getSuperAdmins();
				foreach($getSuperAdmins as $getSuperAdmin) {
						$admin = Engine_Api::_()->getItem('user', $getSuperAdmin->user_id);
						Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($admin, $admin, $question, 'sesqa_qaaprvwaiting');

						Engine_Api::_()->getApi('mail', 'core')->sendSystem($getSuperAdmin->email, 'sesqa_qaaprvwaiting', array('host' => $_SERVER['HTTP_HOST'], 'queue' => false, 'title' => $question->title, 'question_link' => $question->getHref()));
				}
			}
      //update location data in sesbasic location table
      if ($_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $question->getIdentity() . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","'.$question->getType().'")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      if($question->draft == 1){
				// Add activity only if question is published
				$action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $question, 'sesqa_question_new');
				// make sure action exists before attaching the question to the activity
				if( $action )
					Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $question);
				if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
					$dbGetInsert = Engine_Db_Table::getDefaultAdapter();
					foreach($tags as $tag)
						$dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
				}
      }
      $db->commit();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'','result'=>array('question_id'=>$question->question_id,'success_message'=>$this->view->translate('Question has created Successfully.'))));
    }catch(Exception $e){
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage(),'result'=>array()));
    }

  }
  public function editAction(){
    $question = Engine_Api::_()->core()->getSubject('sesqa_question');
		if (!$this->_helper->requireSubject()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		if(!$this->_helper->requireAuth()->setAuthParams($question, null, 'view')->isValid() ) return;
			$viewer = Engine_Api::_()->user()->getViewer();
   // $sesqa_create = Zend_Registry::isRegistered('sesqa_create') ? Zend_Registry::get('sesqa_create') : null;
    //if(!empty($sesqa_create)) {
      $form = new Sesqa_Form_Create();
    //}
    $form->populate($question->toArray());
    $data['options'] = array();
    $data['maxOptions'] = $max_options = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.maxoptions', 15);
    $data['multi'] = !empty($_POST['multi']) ? $_POST['multi'] : ($question->multi) ? $question->multi : "";
    $draftOldValue = $question->draft;
    // Check options
    $options = (array) $this->_getParam('optionsArray');
    $pollOptions = $question->getOptions();
    if(!count($options)){
      foreach($pollOptions as $optn){
         $options[] = $optn->poll_option;
      }
    }
    $latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData($question->getType(),$question->getIdentity());
		if($latLng){
			if($form->getElement('lat'))
				$form->getElement('lat')->setValue($latLng->lat);
			if($form->getElement('lng'))
				$form->getElement('lng')->setValue($latLng->lng);
		}
    $data['isPollDisable'] = $question->vote_count && count($pollOptions);
    $options = array_filter(array_map('trim', $options));
    $options = array_slice($options, 0, $max_options);
    $data['options'] = $options;
    if($form->video)
      $form->video->setValue($question->video_url);
    // authorization
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    foreach ($roles as $role) {
      if (1 === $auth->isAllowed($question, $role, 'view')) {
        $form->auth_view->setValue($role);
      }
      if (1 === $auth->isAllowed($question, $role, 'comment')) {
        $form->auth_comment->setValue($role);
      }
      if (1 === $auth->isAllowed($question, $role, 'answer')) {
        $form->auth_answer->setValue($role);
      }
    }
    // prepare tags
    $questionTags = $question->tags()->getTagMaps();
    $tagString = '';
    foreach ($questionTags as $tagmap) {
      $tag = $tagmap->getTag();
      if(isset($tag)) {
      if ($tagString !== '')
        $tagString .= ', ';
      $tagString .= $tag->getTitle();
      }
    }
    if($form->tags)
    $form->tags->setValue($tagString);
    if($question->draft == 1)
      $form->removeElement('draft');
			$form->removeElement('lat');
			$form->removeElement('map-canvas');
			$form->removeElement('ses_location');
			$form->removeElement('lng');
    if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields,$data);
		}
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
    if(!empty($_POST['is_poll']) &&  (empty($options) || !is_array($options) || count($options) < 2 )) {
			$form->addError('You must provide at least two possible answers.');
    }
		if (!$form->isValid($_POST)) {
			$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
    foreach( $options as $index => $option ) {
      if( strlen($option) > 255 ) {
        $options[$index] = Engine_String::substr($option, 0, 255);
      }
    }
    $db = Engine_Api::_()->getItemTable('sesqa_question')->getAdapter();
    $sesqaOptionsTable = Engine_Api::_()->getDbtable('options', 'sesqa');
    $db->beginTransaction();

    try {
      // Add tags
      $values = $form->getValues();
      if( empty($values['auth_view']) )
        $values['auth_view'] = 'everyone';
      if( empty($values['auth_comment']) )
       $values['auth_comment'] = 'everyone';
      if( empty($values['auth_answer']) )
       $values['auth_answer'] = 'everyone';
      $values['view_privacy'] = $values['auth_view'];
      $question->setFromArray($values);
      if(!empty($_FILES['photo']['name']) && $values['mediatype'] == 1)
       $quote->setPhoto($_FILES['photo']);
      if($values['video'] && $values['mediatype'] == 2 && $question->video_url != $values['video']) {
        $information = $this->handleIframelyInformation($values['video']);
        try{
          $question->setPhoto($information['thumbnail']);
        }catch(Exception $e){
          //silence
        }
        $question->video_url = $values['video'];
        $question->code = $information['code'];
      }

      $question->save();

      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $answerMax = array_search($values['auth_answer'], $roles);
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($question, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($question, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($question, $role, 'answer', ($i <= $answerMax));
      }
      $auth->setAllowed($question, 'registered', 'vote', true);
      // Add tags
      $tags = preg_split('/[,]+/', trim($values['tags'],', '));
      $question->tags()->setTagMaps($viewer, $tags);
      $isPollOptionUpdate = $this->pollOptionUpdate($pollOptions,$_POST['optionsArray']);
      if(!empty($_POST['is_poll'])){
         if(($isPollOptionUpdate && !$question->vote_count) || !count($pollOptions)){
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query("DELETE FROM engine4_sesqa_options WHERE question_id = ".$question->getIdentity());
          // poll data insert
          $censor = new Engine_Filter_Censor();
          $html = new Engine_Filter_Html(array('AllowedTags'=> array('a')));
          foreach( $options as $option ){
            $option = $censor->filter($html->filter($option));
            $sesqaOptionsTable->insert(array(
              'question_id' => $question->getIdentity(),
              'poll_option' => $option,
            ));
          }
          $question->multi = !empty($_POST['multi']) ? $_POST['multi'] : 0;
          $question->save();
         }

      }else{
        //remove poll options
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query("DELETE FROM engine4_sesqa_options WHERE question_id = ".$question->getIdentity());
        $question->vote_count = 0;
        $question->save();
      }
      //update location data in sesbasic location table
      if ($_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $question->getIdentity() . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","'.$question->getType().'")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      if($draftOldValue == 0 && $question->draft == 1){
        // Add activity only if question is published
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $question, 'sesqa_question_new');
        // make sure action exists before attaching the question to the activity
        if( $action )
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $question);
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          foreach($tags as $tag)
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
        }
      }
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'','result'=>array('array'=>$question->question_id,'success_message'=>$this->view->translate('Question has edited Successfully.'))));
    }catch(Exception $e){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage(),'result'=>array()));
    }
  }
	 public function handleIframelyInformation($uri) {

    $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesquote_iframely_disallow');
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
	function pollOptionUpdate($questionPolls,$postOptions){
    foreach($questionPolls->toArray() as $key => $qpoll){
        if(empty($postOptions[$key]) || $qpoll['poll_option'] != $postOptions[$key]){
          return true;
        }
    }
    return false;
  }
	public function viewAction(){
	  $question = Engine_Api::_()->core()->getSubject('sesqa_question');
	  if (!$this->_helper->requireSubject()->isValid())
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
	  if(!$this->_helper->requireAuth()->setAuthParams($question, null, 'view')->isValid() )
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('you do not have permission to view this'), 'result' => array()));
		$viewer = Engine_Api::_()->user()->getViewer();
		/* Insert data for recently viewed widget */
		if ($viewer->getIdentity() != 0 && ($question->getIdentity())) {
			$dbObject = Engine_Db_Table::getDefaultAdapter();
			$dbObject->query('INSERT INTO engine4_sesqa_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $question->getIdentity() . '", "'.$question->getType().'","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
		}
		if( !$viewer || !$viewer->getIdentity() || !$question->isOwner($viewer) ) {
			$question->view_count = new Zend_Db_Expr('view_count + 1');
			$question->save();
		}
		$question_id = $this->_getParam('question_id',0);
    $is_paging_content = 0;
    if(!$question_id && !$this->_getParam('question','') && !Engine_Api::_()->core()->hasSubject('sesqa_question')){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    if(!$question_id)
      $question = ($this->_getParam('question','')) ? $this->_getParam('question','') : Engine_Api::_()->core()->getSubject('sesqa_question');
    else{
      $question = Engine_Api::_()->getItem('sesqa_question',$question_id);
    }
		$result['question'] = $question->toArray();
		$result['question']['vote_buttons'] = $this->voteButtons($question,array());
		$newSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa_new_label', 5);
    $enableNewSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa_enable_newLabel', 1);
		$counter_label = 0;
		if($newSetting && $enableNewSetting && strtotime(date("Y-m-d H:i:s")) <= strtotime($question->creation_date." + ".$newSetting." Days")){
			$result['question']['labels'][$counter_label] = $this->view->translate('New Question');
			$counter_label++;
		}
		if($question->hot){
			$result['question']['labels'][$counter_label] = $this->view->translate('Hot Question');
			$counter_label++;
		}
		if($question->sponsored){
			$result['question']['labels'][$counter_label] = $this->view->translate('Sponsored Question');
			$counter_label++;
		}
		if($question->verified){
			$result['question']['labels'][$counter_label] = $this->view->translate('Verified Question');
			$counter_label++;
		}
		if($question->featured){
			$result['question']['labels'][$counter_label] = $this->view->translate('Featured Question');
			$counter_label++;
		}
		$result['question']['open_close_label'] = $question->open_close ? $this->view->translate('SESClose') : $this->view->translate("SESOpen");
		$tags = array();
		foreach ($question->tags()->getTagMaps() as $tagmap) {

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
		if (count($tags)){
			$result['question']['tag'] = $tags;
		}
		$result['question']['total_vote']  = $question->vote_count;



    $owner = $owner = $question->getOwner();
    $viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $questionOptions = $question->getOptions();
    $hasVoted = $question->viewerVoted();
    $multiVote = $question->multi;
		$result['question']['has_voted_id'] = $hasVoted ;
    $showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.showpiechart', false);
    $canVote = $question->authorization()->isAllowed($viewer, 'answer');
    $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.canchangevote', true);
    $hideLinks = true;
    $getTitle = true;
    $layoutOri = $this->view->layout()->orientation;
		if ($layoutOri == 'right-to-left') {
				$this->view->direction = 'rtl';
		} else {
				$this->view->direction = 'ltr';
		}
		$language = explode('_', $this->view->locale()->getLocale()->__toString());
		$language = $language[0];
    $paginator = Engine_Api::_()->getDbTable('answers','sesqa')->getAnswersPaginator(array('question_id'=>$question->getIdentity(),'paginator'=>true,'answer_id'=>$this->_getParam('answer_id','')));
    $limit_data  = $this->_getParam('limit', 5);
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($limit_data);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$answerCounter = 0;
		foreach($paginator as $answer){
			$ques = null;
			$result['answers'][$answerCounter] = $answer->toArray();
			$result['answers'][$answerCounter]['buttons'] = $this->voteButtons($ques,$answer);
			$options_counter = 0;
			if($viewer->getIdentity()  && $viewer->getIdentity() == $answer->owner_id){
				$result['answers'][$answerCounter]['options'][$options_counter]['name'] = 'edit';
				$result['answers'][$answerCounter]['options'][$options_counter]['label'] = $this->view->translate('SESEdit');
				$options_counter++;
				$result['answers'][$answerCounter]['options'][$options_counter]['name'] = 'delete';
				$result['answers'][$answerCounter]['options'][$options_counter]['label'] = $this->view->translate('SESDelete');
				$options_counter++;
			}
			$isVote = Engine_Api::_()->getDbTable('voteupdowns','sesqa')->isVote(array('resource_id'=>$answer->getIdentity(),'resource_type'=>$answer->getType(),'user_id'=>$viewer->getIdentity(),'user_type'=>'user'));
			if($isVote){
				$result['answers'][$answerCounter]['has_voted'] = $isVote->type;
			}

			$result['answers'][$answerCounter]['owner_title'] = $answer->getOwner()->getTitle();
			$result['answers'][$answerCounter]['owner_image'] = Engine_Api::_()->sesapi()->getPhotoUrls($answer->getOwner(), '', "");
			if($answer->authorization()->isAllowed($viewer, 'comment') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
				$hasComment = Engine_Api::_()->sesqa()->hasAnswerComment($answer->getIdentity());
				if(!hasComment){
					$result['answers'][$answerCounter]['comment']['name'] = 'comment';
					$result['answers'][$answerCounter]['comment']['label'] = $this->view->translate('Add a comment');
				}
			}
			$answerCounter++;
		}
		$questionCounter = 0;
		foreach($questionOptions as $options){
			$result['question_options'][$questionCounter]  = $options->toArray();
			if( in_array($options->poll_option_id,$hasVoted))
				$result['question_options'][$questionCounter]['has_voted'] = true;
			else
				$result['question_options'][$questionCounter]['has_voted'] = false;
			$pct = $question->vote_count ? floor(100*($options->votes/$question->vote_count)): 0;
    if (!$pct)
			$pct = 1;
			$result['question_options'][$questionCounter]['vote_percent'] = $pct;
			$questionCounter++;
		}
		$isVote = Engine_Api::_()->getDbTable('voteupdowns','sesqa')->isVote(array('resource_id'=>$question->getIdentity(),'resource_type'=>$question->getType(),'user_id'=>$viewer->getIdentity(),'user_type'=>'user'));
		if($isVote){
			$result['question']['has_voted'] = $isVote->type;
		}
    $can_edit = 0;
		$can_delete = 0;

		if($viewer->getIdentity() != 0){
			$can_edit = $question->authorization()->isAllowed($viewer, 'edit');
			$can_delete = $question->authorization()->isAllowed($viewer, 'delete');
		}
		if($question->getPhotoUrl()){
		$result['question']['question_photo'] = $this->getBaseUrl(true, $question->getPhotoUrl());
		}
		$options_counter = 0;
		if($can_edit){
			$result['question']['options'][$options_counter]['name'] = 'edit';
			$result['question']['options'][$options_counter]['label'] = $this->view->translate('SESEdit');
			$options_counter++;
		}
		if($can_delete){
			$result['question']['options'][$options_counter]['name'] = 'delete';
			$result['question']['options'][$options_counter]['label'] = $this->view->translate('SESDelete');
			$options_counter++;
		}
		$statscounter = 0;
		if($viewer->getIdentity() != 0 ){
			$LikeStatus = Engine_Api::_()->sesqa()->getLikeStatusQuestion($question->getIdentity(),$question->getType());
				$result['question']['is_content_like'] = $LikeStatus?true:false;
				$statscounter++;
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.allow.favourite', 1)) {
			$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesqa')->isFavourite(array('resource_type'=>$question->getType(),'resource_id'=>$question->getIdentity()));
				$result['question']['is_content_favourite'] = $favStatus?true:false;
				$statscounter++;
            }
			$FollowUser = Engine_Api::_()->sesqa()->getFollowStatus($question->getIdentity());
				$result['question']['is_content_follow'] = $FollowUser?true:false;
				$statscounter++;
			$report = Engine_Api::_()->getApi('settings', 'core')->getSetting('qanda_allow_reporting', '1');
				$result['question']['can_report'] = $report?true:false;
				$statscounter++;
				if($report && !$question->isOwner($viewer)){
					$result['question']['options'][$options_counter]['name'] = 'report';
					$result['question']['options'][$options_counter]['label'] = $this->view->translate('Report');
					$options_counter++;
				}
			$canshare = Engine_Api::_()->getApi('settings', 'core')->getSetting('qanda_allow_sharing', 1);
				$result['question']['can_share'] = $canshare?true:false;
			if($canshare){
				$result['question']["share"]["imageUrl"] = $this->getBaseUrl(false, $question->getPhotoUrl());
				$result['question']["share"]["url"] = $this->getBaseUrl(false,$question->getHref());
				$result['question']["share"]["title"] = $question->getTitle();
				$result['question']["share"]["description"] = strip_tags($question->getDescription());
				$result['question']["share"]["setting"] = $canshare;
				$result['question']["share"]['urlParams'] = array(
					"type" => $question->getType(),
					"id" => $question->getIdentity()
				);
				$statscounter++;
			}
			$result['question']['owner_title'] = $question->getOwner()->getTitle();
			$category = Engine_Api::_()->getItem('sesqa_category',$question->category_id);
			 if($category){
				 $result['question']['category_title'] = $category->category_name;
			 }
			// if($question->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesqa.enable.location', 1)){

			// }
		}
    $canCasteAnswerVote = Engine_Api::_()->authorization()->isAllowed('sesqa_question', null, 'vote_answer');
    $canCasteQuestionVote = Engine_Api::_()->authorization()->isAllowed('sesqa_question', null, 'vote_question');
    $tags = $question->tags()->getTagMaps();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
  }
	public function moreAnswersAction(){
		$question = Engine_Api::_()->core()->getSubject('sesqa_question');
	  if (!$this->_helper->requireSubject()->isValid())
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
	  if(!$this->_helper->requireAuth()->setAuthParams($question, null, 'view')->isValid() )
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		$viewer = Engine_Api::_()->user()->getViewer();
		$paginator = Engine_Api::_()->getDbTable('answers','sesqa')->getAnswersPaginator(array('question_id'=>$question->getIdentity(),'paginator'=>true,'answer_id'=>$this->_getParam('answer_id','')));
    $limit_data  = $this->_getParam('limit', 5);
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($limit_data);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$answerCounter = 0;
		foreach($paginator as $answer){
			$ques = null;
			$result['answers'][$answerCounter] = $answer->toArray();
			$result['answers'][$answerCounter]['buttons'] = $this->voteButtons($ques,$answer);
			$options_counter = 0;
			if($viewer->getIdentity()  && $viewer->getIdentity() == $answer->owner_id){
				$result['answers'][$answerCounter]['options'][$options_counter]['name'] = 'edit';
				$result['answers'][$answerCounter]['options'][$options_counter]['label'] = $this->view->translate('SESEdit');
				$options_counter++;
				$result['answers'][$answerCounter]['options'][$options_counter]['name'] = 'delete';
				$result['answers'][$answerCounter]['options'][$options_counter]['label'] = $this->view->translate('SESDelete');
				$options_counter++;
			}
			$result['answers'][$answerCounter]['owner_title'] = $answer->getOwner()->getTitle();
			$result['answers'][$answerCounter]['owner_photo'] = Engine_Api::_()->sesapi()->getPhotoUrls($answer->getOwner(), '', "");
			if($answer->authorization()->isAllowed($viewer, 'comment') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
				$hasComment = Engine_Api::_()->sesqa()->hasAnswerComment($answer->getIdentity());
				if(!hasComment){
					$result['answers'][$answerCounter]['comment']['name'] = 'comment';
					$result['answers'][$answerCounter]['comment']['label'] = $this->view->translate('Add a comment');
				}
			}
			$answerCounter++;
		}
		$questionCounter = 0;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
	}
	public function voteButtons($question,$answer){
		$item = !empty($answer) ? $answer : $question;
		$viewer = Engine_Api::_()->user()->getViewer();
		$isVote = Engine_Api::_()->getDbTable('voteupdowns','sesqa')->isVote(array('resource_id'=>$item->getIdentity(),'resource_type'=>$item->getType(),'user_id'=>$viewer->getIdentity(),'user_type'=>'user'));
		$classUp = $classDown = "";
		if($item->getType() == "sesqa_question"){
			$canCasteVote = Engine_Api::_()->authorization()->isAllowed('sesqa_question', null, 'vote_question');
		}else{
			$canCasteVote = Engine_Api::_()->authorization()->isAllowed('sesqa_question', null, 'vote_answer');
		}
		if(!empty($item) && $canCasteVote){
			$classUp = "sesqa_upvote_btn_a";
			$classDown = "sesqa_downvote_btn_a";
		}
		$result['vote_count'] = $item->upvote_count - $item->downvote_count ;
	}
	public function voteupAction(){
    $itemguid = $this->_getParam('itemguid',0);
    $userguid = $this->_getParam('userguid',0);
    $type = $this->_getParam('type','upvote');
    $item = Engine_Api::_()->getItemByGuid($itemguid);
    $user = Engine_Api::_()->getItemByGuid($userguid);
    $isVote = Engine_Api::_()->getDbTable('voteupdowns','sesqa')->isVote(array('resource_id'=>$item->getIdentity(),'resource_type'=>$item->getType(),'user_id'=>$user->getIdentity(),'user_type'=>$user->getType()));
    $checkType = "";
    if($isVote)
      $checkType = $isVote->type;
    if($checkType != "upvote" && $type == "upvote"){
      //up vote
      $table = Engine_Api::_()->getDbTable('voteupdowns','sesqa');
      $vote = $table->createRow();
      $vote->type = "upvote";
      $vote->resource_type = $item->getType();
      $vote->resource_id = $item->getIdentity();
      $vote->user_type = $user->getType();
      $vote->user_id = $user->getIdentity();
      $vote->save();
      $item->upvote_count = new Zend_Db_Expr('upvote_count + 1');
      if($isVote){
         $isVote->delete();
         $item->downvote_count = new Zend_Db_Expr('downvote_count - 1');
      }
      $item->save();
    }else{
      //down vote
      $table = Engine_Api::_()->getDbTable('voteupdowns','sesqa');
      $vote = $table->createRow();
      $vote->type = "downvote";
      $vote->resource_type = $item->getType();
      $vote->resource_id = $item->getIdentity();
      $vote->user_type = $user->getType();
      $vote->user_id = $user->getIdentity();
      $vote->save();
      $item->downvote_count = new Zend_Db_Expr('downvote_count + 1');
      if($isVote){
         $isVote->delete();
         $item->upvote_count = new Zend_Db_Expr('upvote_count - 1');
      }
      $item->save();
    }
    $markasBest = false;
    if($item->getType() == "sesqa_answer")
      $markasBest = true;
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' =>array('downvote_count'=>$item->downvote_count,'upvote_count'=>$item->upvote_count,'type'=>$isVote->type)));
  }
	public function createAnswerAction(){
    $question_id = $this->_getParam('question_id',0);
    $question = Engine_Api::_()->getItem('sesqa_question',$question_id);
    if(!$question_id || !$question){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
    }
    $answerTable = Engine_Api::_()->getItemTable('sesqa_answer');
    $answer = $answerTable->createRow();
    $answer->description = $this->_getParam('data','');
		$viewer = Engine_Api::_()->user()->getViewer();
    $answer->owner_id = $viewer->getIdentity();
    $answer->creation_date = date('Y-m-d H:i:s',time());
    $answer->question_id = $question_id;
    $answer->save();
    $question->answer_count = new Zend_Db_Expr('answer_count + 1');
    $question->save();
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    $commentMax = array_search('everyone', $roles);
    foreach( $roles as $i => $role ) {
      $auth->setAllowed($answer, $role, 'comment', ($i <= $commentMax));
    }
    //notification
    if($question->owner_id != $viewer->getIdentity()) {
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($question->getOwner(), $question->getOwner(), $question, 'sesqa_qaanswered');
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($question->getOwner()->email, 'sesqa_qaanswered', array('host' => $_SERVER['HTTP_HOST'], 'queue' => false, 'title' => $question->title, 'question_link' => $question->getHref(), 'member_name' => $viewer->getTitle()));
    }
    $getQuesitonFollowers = Engine_Api::_()->getDbTable('follows', 'sesqa')->getQuesitonFollowers($question->getIdentity());
    foreach($getQuesitonFollowers as $getQuesitonFollower) {
        $user = Engine_Api::_()->getItem('user', $getQuesitonFollower->user_id);
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $user, $question, 'sesqa_qanewanswer');
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user->email, 'sesqa_qanewanswer', array('host' => $_SERVER['HTTP_HOST'], 'queue' => false, 'title' => $question->title, 'question_link' => $question->getHref(), 'member_name' => $viewer->getTitle()));
    }
		$dataAnswer = array();
		$dataAnswer = $answer->toArray();
		$owner = $answer->getOwner();
		$options_counter = 0;
		if($viewer->getIdentity()  && $viewer->getIdentity() == $answer->owner_id){
			$dataAnswer['options'][$options_counter]['name'] = 'edit';
			$dataAnswer['options'][$options_counter]['label'] = $this->view->translate('SESEdit');
			$options_counter++;
			$dataAnswer['options'][$options_counter]['name'] = 'delete';
			$dataAnswer['options'][$options_counter]['label'] = $this->view->translate('SESDelete');
			$options_counter++;
		}
		$dataAnswer['owner_id'] = $answer->getOwner()->getIdentity();
		$dataAnswer['owner_title'] = $answer->getOwner()->getTitle();
		$dataAnswer['owner_image'] = Engine_Api::_()->sesapi()->getPhotoUrls($answer->getOwner(), '', "");
		if($answer->authorization()->isAllowed($viewer, 'comment') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
			$hasComment = Engine_Api::_()->sesqa()->hasAnswerComment($answer->getIdentity());
			if(!$hasComment){
				$dataAnswer['comment']['name'] = 'comment';
				$dataAnswer['comment']['label'] = $this->view->translate('Add a comment');
			}
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => array('answer'=>$dataAnswer)));
  }
  public function deleteAnswerAction(){
    $answer_id = $this->_getParam('answer_id',0);
    $answer = Engine_Api::_()->getItem('sesqa_answer',$answer_id);
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$answer_id || !$answer || $answer->owner_id != $viewer->getIdentity()){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
    }

    $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete Answer?');
    $form->setDescription('Are you sure that you want to delete this answer? It will not be recoverable after being deleted. ');
    $form->submit->setLabel('Delete');

    if (!$answer) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Answer doesn\'t exists or not authorized to delete'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid request method'), 'result' =>array()));
    }
    $db = $answer->getTable()->getAdapter();
    $db->beginTransaction();
    try {

      $question = Engine_Api::_()->getItem('sesqa_question',$answer->question_id);
      if($answer->best_answer){
        $question->best_answer  = 0;
        $question->save();
      }


      $answer->delete();
      $question->answer_count = new Zend_Db_Expr('answer_count - 1');
      $question->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' =>array()));
    }
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' =>array('success_message'=>$this->view->translate('Answer has been deleted.'),'answer_id'=>$answer_id)));

  }
	public function editAnswerAction(){
    $answer_id = $this->_getParam('answer_id',$this->_getParam('id',0));
    $answer = Engine_Api::_()->getItem('sesqa_answer',$answer_id);
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$answer_id || !$answer || $answer->owner_id != $viewer->getIdentity()){
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
    }
    $data = $this->_getParam('data','');
    $answer->description = $data;
    $answer->save();
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'' , 'result' =>array('success_message'=>$this->view->translate('Answer has been Edited Successfully.'),'answer_id'=>$answer_id)));
  }
	function markBestAction(){
    $answer_id = $this->_getParam('answer_id',$this->_getParam('id',0));
    $answer = Engine_Api::_()->getItem('sesqa_answer',$answer_id);
    $question = Engine_Api::_()->getItem('sesqa_question',$answer->question_id);
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$answer_id || !$answer || $question->owner_id != $viewer->getIdentity()){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('perameter_missing'), 'result' => array()));
    }
    $question = Engine_Api::_()->getItem('sesqa_question',$answer->question_id);
    $isMark = $answer->best_answer;
    $mark=true;
    $olsQuestionBest = $question->best_answer;
    $question->best_answer = $answer->getIdentity();
    if($isMark){
      $question->best_answer = 0;
      $question->save();
      $mark=false;
    }
    if($olsQuestionBest){
      $answerOld = Engine_Api::_()->getItem('sesqa_answer',$olsQuestionBest);
      if($answerOld){
        $answerOld->best_answer = 0;
      }
      $answerOld->save();
    }
    $question->save();

    //notification
    if($answer->owner_id != $viewer->getIdentity()) {
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($answer->getOwner(), $answer->getOwner(), $question, 'sesqa_qabestanswer');

        Engine_Api::_()->getApi('mail', 'core')->sendSystem($answer->getOwner()->email, 'sesqa_qabestanswer', array('host' => $_SERVER['HTTP_HOST'], 'queue' => false, 'title' => $question->title, 'question_link' => $question->getHref(), 'member_name' => $viewer->getTitle()));
    }

    $getQuesitonFollowers = Engine_Api::_()->getDbTable('follows', 'sesqa')->getQuesitonFollowers($answer->question_id);
    foreach($getQuesitonFollowers as $getQuesitonFollower) {
        $user = Engine_Api::_()->getItem('user', $getQuesitonFollower->user_id);
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $user, $question, 'sesqa_bestmarkfollwd');

        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user->email, 'sesqa_bestmarkfollwd', array('host' => $_SERVER['HTTP_HOST'], 'queue' => false, 'title' => $question->title, 'question_link' => $question->getHref(), 'member_name' => $viewer->getTitle()));
    }
    $answer->best_answer = !$answer->best_answer;
    $answer->save();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'' , 'result' =>array('success_message'=>$this->view->translate('Answer Successfully Marked as Best Answer.'))));
  }
}
