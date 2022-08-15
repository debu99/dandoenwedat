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
class User_IndexController extends Sesapi_Controller_Action_Standard
{
  protected $_blockedUser = array();
  public function browseAction()
  {
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')){
      return $this->_forward('browse-data-sesmember', null, null, array('format' => 'json'));
    }else{
      return $this->_forward('browse-data', null, null, array('format' => 'json'));
    }
  }
    function recentlyViewedByMeAction(){
        $viewerId = $this->_getParam('viewer_id','');
        $paginator = Engine_Api::_()->getDbTable('userviews', 'sesmember')->whoViewedMe(array('resources_id' => $viewerId, 'paginator' => true, 'view_by_me' => true));
        $page = (int)  $this->_getParam('page', 1);
        // Build paginator
        $paginator->setItemCountPerPage($this->_getParam('limit',10));
        $paginator->setCurrentPageNumber($page);

        $result = $this->memberResult($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
        if($result <= 0)
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Does not exist member.', 'result' => array()));
        else
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    }
    function recentlyViewedMeAction(){
        $viewerId = $this->_getParam('viewer_id','');
        $paginator = Engine_Api::_()->getDbTable('userviews', 'sesmember')->whoViewedMe(array('resources_id' => $viewerId, 'paginator' => true));
        $page = (int)  $this->_getParam('page', 1);
        // Build paginator
        $paginator->setItemCountPerPage($this->_getParam('limit',10));
        $paginator->setCurrentPageNumber($page);

        $result = $this->memberResult($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
        if($result <= 0)
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Does not exist member.', 'result' => array()));
        else
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

    }
    function followersAction(){

        $viewerId = $this->_getParam('viewer_id','');
        $paginator = Engine_Api::_()->getDbTable('members', 'sesmember')->followers(array('user_id' => $viewerId, 'paginator' => true));
        $page = (int)  $this->_getParam('page', 1);
        // Build paginator
        $paginator->setItemCountPerPage($this->_getParam('limit',10));
        $paginator->setCurrentPageNumber($page);

        $result = $this->memberResult($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
        if($result <= 0)
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Does not exist member.', 'result' => array()));
        else
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

    }
    function followingAction(){
        $viewerId = $this->_getParam('viewer_id','');
        $paginator = Engine_Api::_()->getDbTable('members', 'sesmember')->following(array('user_id' => $viewerId, 'paginator' => true));
        $page = (int)  $this->_getParam('page', 1);
        // Build paginator
        $paginator->setItemCountPerPage($this->_getParam('limit',10));
        $paginator->setCurrentPageNumber($page);

        $result = $this->memberResult($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
        if($result <= 0)
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Does not exist member.', 'result' => array()));
        else
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

    }
  public function browseDataSesmemberAction(){
    
    $form = new Sesmember_Form_Filter_Browse(array('friendType' => 'yes', 'searchType' => 'yes', 'locationSearch' => 'yes', 'kilometerMiles' => 'yes', 'browseBy' => 'yes', 'searchTitle' => 'yes', 'FriendsSearch' => 'yes', 'citySearch' => 'yes', 'stateSearch' => 'yes', 'zipSearch' => 'yes', 'countrySearch' => 'yes', 'alphabetSearch' => 'yes', 'memberType' => 'yes', 'hasPhoto' => 'yes', 'isOnline' => 'yes', 'isVip' => 'yes', 'type' => 'user', 'networkGet' => 'yes', 'complimentGet' => 'yes'));
    
    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];  
      }  
    }
    
    $form->populate($_POST);
      if(empty($_POST['info'])){
          $form->order->setValue('creation_date DESC');
      }
    // Get search params
    $page = (int)  $this->_getParam('page', 1);
    $options = $form->getValues();
    $options['text'] = !empty($options['search_text']) ? $options['search_text'] : '';
    if(!empty($_POST['friend_id']))
      $options['friend_id'] = $_POST['friend_id'];
    if(!empty($_POST['action_id']))
      $options["action_id"] = $_POST['action_id'];
    $paginator = Engine_Api::_()->getDbTable('members','sesmember')->getMemberPaginator(array_merge($options, array('search' => 1)), $options);
     $page = (int)  $this->_getParam('page', 1);
    // Build paginator
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($page);
    
    $result = $this->memberResult($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Does not exist member.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams)); 
  }
  public function searchFormDataAction(){
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')){
        return $this->_forward('search-form-sesmember', null, null, array('format' => 'json'));
      }else{
        return $this->_forward('search-form', null, null, array('format' => 'json'));
      }
  }
  
  function searchFormSesmemberAction(){
    $page = Engine_Api::_()->sesapi()->getIdentityWidget('sesmember.browse-search','widget','sesmember_index_browse');
    if($page){
      $params = $page->params;  
      foreach($params as $key=>$param){
        $this->_setParam($key,$param);
      }
    }
      // Create form
    $default_search_type = $this->_getParam('default_search_type', 'like_count DESC');
    $search_type = $this->_getParam('search_type', array('recentlySPcreated' => 'Recently Signuped', 'mostSPviewed' => 'Most Viewed', 'mostSPliked' => 'Most Liked', 'mylike' => 'Members I Liked', 'myfollow' => 'Members I Followed', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'mostSPrated' => 'Most Rated', 'atoz' => 'A to Z', 'ztoa' => 'Z to A'));
    if (count($search_type))
      $browseBy = $this->_getParam('browse_by', 'yes');
    else
      $browseBy = 'no';

    $arrayView = array('0' => 'All\'s Users', '1' => 'My Friend\'s', 'week' => 'This Week', 'month' => 'This Month', '3' => 'Only My Network\'s');
    $defaultView = array('0', '1', '3', 'week', 'month');
    $friend_type = $this->_getParam('view', $defaultView);
   // if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0)
    //unset($friend_type['1']);
		if (count($friend_type))
		$friendOnly = $this->_getParam('friend_show', 'yes');
		else
		$friendOnly = 'no';

    $this->view->view_type = $this->_getParam('view_type', 'horizontal');
    if ($this->_getParam('location', 'yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.enable.location', 1))
      $location = 'yes';
    else
      $location = 'no';
    
      $form = new Sesmember_Form_Filter_Browse(array('friendType' => $friend_type, 'searchType' => $search_type, 'locationSearch' => $location, 'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'), 'browseBy' => $browseBy, 'searchTitle' => $this->_getParam('search_title'), 'friendsSearch' => $friendOnly, 'citySearch' => $this->_getParam('city', 'yes'), 'stateSearch' => $this->_getParam('state', 'yes'), 'zipSearch' => $this->_getParam('zip', 'yes'), 'countrySearch' => $this->_getParam('country', 'yes'), 'alphabetSearch' => $this->_getParam('alphabet', 'yes'), 'memberType' => $this->_getParam('member_type', 'yes'), 'hasPhoto' => $this->_getParam('has_photo', 'yes'), 'isOnline' => $this->_getParam('is_online', 'yes'), 'isVip' => $this->_getParam('is_vip', 'yes'), 'type' => 'user', 'networkGet' => $this->_getParam('network', 'yes'), 'complimentGet' => $this->_getParam('compliment', 'yes')));
      if($form->advanced_options_search_)
        $form->removeElement('advanced_options_search_');
      if($form->loadingimgsesmember){
        $form->removeElement('loadingimgsesmember');
      }
      if (!count($friend_type))
        $form->removeElement('view');
      else if ($form->view) {
        $viewArray = array();
        foreach ($friend_type as $val) {
          $viewArray[$val] = $arrayView[$val];
        }
  
        if (array_key_exists('3', $arrayView)) {
          $userjoinednetwork = Engine_Api::_()->getDbtable('membership', 'network')->fetchRow(array('user_id = ?' => Engine_Api::_()->user()->getViewer()->getIdentity()));
          if (!$userjoinednetwork)
            unset($viewArray['3']);
        }else if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0)
          unset($viewArray['3']);
  
        if (count($viewArray))
          $form->view->setMultiOptions($viewArray);
      }
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
      $this->generateFormFields($formFields);
  }
  
  public function searchFormAction(){
      $form = new Sesapi_Form_Membersearch(array('type' => 'user'));
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
      $this->generateFormFields($formFields);          
  }
  
  public function browseDataAction()
  {
    // Check form
    $form = new Sesapi_Form_Membersearch(array('type' => 'user'));

    $this->view->form = $form;
    $form->populate($_POST);
    // Get search params
    $page = (int)  $this->_getParam('page', 1);
    $options = $form->getValues();
    
    // Process options
    $tmp = array();
    $originalOptions = $options;
    foreach( $options as $k => $v ) {
      if( null == $v || '' == $v || (is_array($v) && count(array_filter($v)) == 0) ) {
        continue;
      } else if( false !== strpos($k, '_field_') ) {
        list($null, $field) = explode('_field_', $k);
        $tmp['field_' . $field] = $v;
      } else if( false !== strpos($k, '_alias_') ) {
        list($null, $alias) = explode('_alias_', $k);
        $tmp[$alias] = $v;
      } else {
        $tmp[$k] = $v;
      }
    }
    $options = $tmp;

    // Get table info
    $table = Engine_Api::_()->getItemTable('user');
    $userTableName = $table->info('name');

    $searchTable = Engine_Api::_()->fields()->getTable('user', 'search');
    $searchTableName = $searchTable->info('name');

    //extract($options); // displayname
    $profile_type = @$options['profile_type'];
    $displayname = @$options['search_text'];
    if (!empty($options)) 
      extract($options); // is_online, has_photo, submit

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $excludedLevels = array(1, 2, 3);
    $allBlockedUsers = array();

    if( $viewerId ) {
      $blockTable = Engine_Api::_()->getDbtable('block', 'user');
      $blockedSelect = $blockTable->select()
        ->from('engine4_user_block', 'blocked_user_id')
        ->where('user_id = ?', $viewerId);
      $blockedUsers = $blockTable->fetchAll($blockedSelect)->toArray();

      foreach( $blockedUsers as $blockedUser ) {
        array_push($allBlockedUsers, $blockedUser['blocked_user_id']);
      }
      $this->_blockedUser = $allBlockedUsers;

      if( !in_array($viewer->level_id, $excludedLevels) ) {
        $blockedBySelect = $blockTable->select()
          ->from('engine4_user_block', 'user_id')
          ->where('blocked_user_id = ?', $viewerId);
        $blockedByUsers = $blockTable->fetchAll($blockedBySelect)->toArray();

        foreach( $blockedByUsers as $blockedByUser ) {
          array_push($allBlockedUsers, $blockedByUser['user_id']);
        }
      } else {

        unset($allBlockedUsers);
      }
    }

    // Contruct query
    $select = $table->select()
      //->setIntegrityCheck(false)
      ->from($userTableName)
      ->joinLeft($searchTableName, "`{$searchTableName}`.`item_id` = `{$userTableName}`.`user_id`", null)
      //->group("{$userTableName}.user_id")
      ->where("{$userTableName}.search = ?", 1)
      ->where("{$userTableName}.enabled = ?", 1);
      
    if( !empty($allBlockedUsers) ) {
      $select->where("user_id NOT IN (?)", $allBlockedUsers);
    }
    $searchDefault = true;

      if(!empty($_POST['friend_id'])){
          $friend = Engine_Api::_()->getItem('user',$_POST['friend_id']);
          $friends = $friend->membership()->getMembershipsOfIds();
          if ($friends)
              $select->where($userTableName . '.user_id IN (?)', $friends);
          else
              $select->where($userTableName . '.user_id IN (?)', 0);
      }
    // Build the photo and is online part of query
    if( isset($has_photo) && !empty($has_photo) ) {
      $select->where($userTableName.'.photo_id != ?', "0");
      $searchDefault = false;
    }

    if( isset($is_online) && !empty($is_online) ) {
      $select
        ->joinRight("engine4_user_online", "engine4_user_online.user_id = `{$userTableName}`.user_id", null)
        ->group("engine4_user_online.user_id")
        ->where($userTableName.'.user_id != ?', "0");
      $searchDefault = false;
    }

    // Add displayname
    if( !empty($displayname) ) {
      $select->where("(`{$userTableName}`.`displayname` LIKE ?)", "%{$displayname}%");
      $searchDefault = false;
    }

    // Build search part of query
    $searchParts = Engine_Api::_()->fields()->getSearchQuery('user', $options);
    foreach( $searchParts as $k => $v ) {
      $select->where("`{$searchTableName}`.{$k}", $v);
      
      if(isset($v) && $v != ""){
        $searchDefault = false;
      }
    }
    
    if($searchDefault){
      $select->order("{$userTableName}.lastlogin_date DESC");
    } else {
      $select->order("{$userTableName}.displayname ASC");
    }

    // Build paginator
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($page);
    
    $result = $this->memberResult($paginator);
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Does not exist member.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
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
        if(in_array($member->getIdentity(), $this->_blockedUser)):
          continue;
        endif;
        $result['notification'][$counterLoop]['user_id'] = $member->getIdentity();
        $result['notification'][$counterLoop]['title'] = $member->getTitle();//preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $member->getTitle());
        
        //$age = $this->userAge($member);
        //if($age){
          //$result['notification'][$counterLoop]['age'] =  $age ;
        //}
        //user location
        if(!empty($member->location))
           $result['notification'][$counterLoop]['location'] =   $member->location;
       
       //follow
        if($followActive && $viewer->getIdentity() && $viewer->getIdentity() != $member->getIdentity()){
            if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')) {
                $FollowUser = Engine_Api::_()->sesmember()->getFollowStatus($member->user_id);
                if (!$FollowUser) {
                    $result['notification'][$counterLoop]['follow']['action'] = 'follow';
                    $result['notification'][$counterLoop]['follow']['text'] = $followText;
                } else {
                    $result['notification'][$counterLoop]['follow']['action'] = 'unfollow';
                    $result['notification'][$counterLoop]['follow']['text'] = $unfollowText;
                }
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
  function detailsAction(){
    $user = Engine_Api::_()->user()->getViewer(); 
    if(!$user->getIdentity())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>"User Logged out"));    
      //send new signup user
    //foreach($useArray as $key=>$value){
      $result["user_id"] = $user->user_id;
      $result["email"] = $user->email;
      $result["username"] = $user->username;
      $result["displayname"] = $user->displayname;
      $result["photo_id"] = $user->photo_id;
      $result["status"] = $user->status;
      $result["password"] = $user->password;
      $result["status_date"] = $user->status_date;
      $result["salt"] = $user->salt;
      $result["locale"] = $user->locale;
      $result["language"] = $user->language;
      $result["timezone"] = $user->timezone;
      $result["search"] = $user->search;
      $result["level_id"] = $user->level_id;
    //  }      
      $result['photo_url']= $this->userImage($this->view->viewer(),'thumb.profile');
            
      //Register device token
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$result));  
  }
  public function getUserAction(){
    $id = $this->_getParam('user_name');
    if( null !== $id )
    {
      $subject = Engine_Api::_()->user()->getUser($id);
      if( $subject->getIdentity() )
      {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$subject->toArray()));  
      }
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>""));
  }
}
