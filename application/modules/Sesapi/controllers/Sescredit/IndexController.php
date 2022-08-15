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

class Sescredit_IndexController extends Sesapi_Controller_Action_Standard {

	public function init(){
		
	}
	
	public function menusAction(){
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_main', array());
		$menu_counter = 0;
		$result_menu[$menu_counter]['label'] = $this->view->translate('Send Point To Friend');
		$result_menu[$menu_counter]['action'] = 'sescredit_main_sendpoint';
		$result_menu[$menu_counter]['isActive'] = false;
		$menu_counter++;
		$result_menu[$menu_counter]['label'] = $this->view->translate('Purchase Points');
		$result_menu[$menu_counter]['action'] = 'sescredit_main_purchagepoint';
		$result_menu[$menu_counter]['isActive'] = false;
		$menu_counter++;
		$result_menu[$menu_counter]['label'] = $this->view->translate('How To Earn Point');
		$result_menu[$menu_counter]['action'] = 'sescredit_main_howearnpoint';
		$result_menu[$menu_counter]['isActive'] = false;
		$menu_counter++;
		$result_menu[$menu_counter]['label'] = $this->view->translate('Terms & Conditions');
		$result_menu[$menu_counter]['action'] = 'sescredit_main_termcondition';
		$result_menu[$menu_counter]['isActive'] = false;
		$menu_counter++;
		$setting = Engine_Api::_()->getApi('settings', 'core');
		$urls = $setting->getSetting('sescredit.manifest', 'credits');
		foreach ($menus as $menu) {
			$class = end(explode(' ', $menu->class));
			if ('sescredit_main_help' == $class){
				//	continue;
				$result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
				$result_menu[$menu_counter]['action'] = $class;
                $result_menu[$menu_counter]['value'] = $this->getBaseUrl(false,$urls.'/help');
				$result_menu[$menu_counter]['isActive'] = $menu->active;
				$menu_counter++;
			}else{
				$result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
				$result_menu[$menu_counter]['action'] = $class;
				$result_menu[$menu_counter]['isActive'] = $menu->active;
				$menu_counter++;
			}
		}
		$result['menus'] = $result_menu;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result)));
  }
	
	public function leaderboardAction(){
		$creditDetailsTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $creditDetailsTableName = $creditDetailsTable->info('name');
    $userBadgeTable = Engine_Api::_()->getDbTable('userbadges', 'sescredit');
    $userBadgeTableName = $userBadgeTable->info('name');
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $select = $userTable->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('displayname', 'user_id', 'photo_id', 'badgeCount' => new Zend_Db_Expr('(SELECT COUNT(' . $userBadgeTableName . '.user_id) from ' . $userBadgeTableName . ' where user_id =' . $userTableName . '.user_id group by user_id)')))
            ->joinRight($creditDetailsTableName, $creditDetailsTableName . '.owner_id =' . $userTableName . '.user_id', array('total_credit'))
            ->order("CAST(total_credit as SIGNED INTEGER) DESC");
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
		
		$counter = 0;
		$result = array();
		$memberResult = $this->memberResult($paginator);
		foreach($paginator as $member){
			$result[$counter] = $member->toArray();
			$result[$counter]['images']['main'] = $this->getBaseUrl(false, $member->getPhotoUrl('thumb.icon'));
			$result[$counter]['user_detail'] = $memberResult['notification'][$counter];
			$counter++;
		}
		$results['items'] = $result;   
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $results), $extraParams));
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
	
	public function badgesAction(){
		$viewerId = $this->view->viewer()->getIdentity();
    $badgeType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.badge.type', 1);
    $userBadgeTable = Engine_Api::_()->getDbTable('userbadges', 'sescredit');
    $userBadgeTableName = $userBadgeTable->info('name');
    $badgeTable = Engine_Api::_()->getDbTable('badges', 'sescredit');
    $badgeTableName = $badgeTable->info('name');
    $select = $badgeTable->select()
            ->setIntegrityCheck(false)
            ->from($badgeTableName, array('badge_id', 'title', 'description', 'photo_id'))
            ->join($userBadgeTableName, $userBadgeTableName . '.badge_id =' . $badgeTableName . '.badge_id', array('user_id', 'active'))
            ->where($userBadgeTableName . '.user_id =?', $viewerId);
    if(!$badgeType) {
      $select->where('engine4_sescredit_badges.credit_value <= ?',new Zend_Db_Expr('(SELECT credit_value from '.$userBadgeTableName.' Left Join engine4_sescredit_badges on engine4_sescredit_badges.badge_id = '.$userBadgeTableName.'.badge_id  where '.$userBadgeTableName.'.active = 1)'))->order('credit_value DESC');
    }
    $badge = $badgeTable->fetchAll($select);
		$badgeCounter = 0;
		$badgeResult = array();
		foreach($badge as $bd){
			$badgeResult[$badgeCounter] = $bd->toArray();
			if($badge->active){
				$badgeResult[$badgeCounter]['label'] = $this->view->translate("Current Badge");
			}
			if(Engine_Api::_()->getItem('storage_file',$bd->photo_id))
				$badgeResult[$badgeCounter]['images']['main'] = $this->getBaseUrl(false,Engine_Api::_()->getItem('storage_file',$bd->photo_id)->getPhotoUrl());
			$badgeCounter++;
		}
    $select = $badgeTable->select()
            ->from($badgeTableName, array('badge_id', 'title', 'description', 'photo_id', 'countMember' => new Zend_Db_Expr('(SELECT COUNT(*) from '.$userBadgeTableName.' where badge_id = '.$badgeTableName.'.badge_id and active = 1)')))
            ->where('enabled =?', 1);
    $allBadges = $badgeTable->fetchAll($select);
		$counter = 0; 
		
		
		foreach($allBadges as $badges){
			$result[$counter] = $badges->toArray();
			if(Engine_Api::_()->getItem('storage_file',$badges->photo_id))
				$result[$counter]['images']['main'] = $this->getBaseUrl(false, Engine_Api::_()->getItem('storage_file',$badges->photo_id)->getPhotoUrl());
			$result[$counter]['count_label'] = $this->view->translate(array('%s Member', '%s Members', $badges->countMember), $this->view->locale()->toNumber($badges->countMember));
			$counter++;
		}
		$data = array();
		$data['current_badge'] = $badgeResult;
		$data['all_badges'] = $result;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data)));
	}
	
	public function myCreditAction() {
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $creditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $firstActivityDate = $creditDetailTable->select()
            ->from($creditDetailTable->info('name'), 'first_activity_date')
            ->where('owner_id =?', $viewerId)
            ->query()
            ->fetchColumn();
		$counter = 0;		
		if(!empty($firstActivityDate)){
			$result['header'][$counter]['label'] = $this->view->translate('Credit Points');
			$credit = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'total_credit'));
			$result['header'][$counter]['value'] = $credit;
			$counter++;
			$result['header'][$counter]['label'] = $this->view->translate('Debit Points');
			$debit =  Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'total_debit'));
			$result['header'][$counter]['value'] = empty($debit) ? 0 : $debit;
			$counter++;
			$result['header'][$counter]['label'] = $this->view->translate('Remaining Points');
			$result['header'][$counter]['value'] = $credit - $debit;;
			$counter++;
			
			$month = Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.month',0);
			$year = Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.year',0);
			$date1 = strtotime('+'.$month.' months',strtotime($firstActivityDate));
			$date1 = strtotime('+'.$year.' years',($date1));
			$validityFinalDate = date("Y-m-d H:i:s", $date1);
			$result['header'][$counter]['label'] = $this->view->translate('Validity Date');
			$result['header'][$counter]['value'] = date('jS M', strtotime($validityFinalDate)).' '.date('Y', strtotime($validityFinalDate));
			$counter++;
		}
		$bodyCounter = 0;
		$result['body'][$bodyCounter]['label'] = $this->view->translate('For New Activity');
		$result['body'][$bodyCounter]['value'] = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'credit'));
		$result['body'][$bodyCounter]['action'] = '-';
		$bodyCounter++;
		$result['body'][$bodyCounter]['label'] = $this->view->translate('On Activity Deletion');
		$result['body'][$bodyCounter]['value'] = '-';
		$result['body'][$bodyCounter]['action'] = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'deduction'))?Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'deduction')):'-';
		$bodyCounter++;
		
		$signupPoint = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'affiliate'));
		if(!empty($signupPoint)){
			$result['body'][$bodyCounter]['label'] = $this->view->translate('Inviter Affiliation');
			$result['body'][$bodyCounter]['value'] = $signupPoint;
			$result['body'][$bodyCounter]['action'] = '-';
			$bodyCounter++;
		}
		$result['body'][$bodyCounter]['label'] = $this->view->translate('Transferred to Friends');
		$result['body'][$bodyCounter]['value'] = '-';
		$result['body'][$bodyCounter]['action'] = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'transfer_friend')) ?Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'transfer_friend')) : '-';
		$bodyCounter++;
		
		$result['body'][$bodyCounter]['label'] = $this->view->translate('Received from Friends');
		$result['body'][$bodyCounter]['value'] = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'receive_friend')) ? Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'receive_friend')) : '-';
		$result['body'][$bodyCounter]['action'] = '-';
		$bodyCounter++;
		
		$result['body'][$bodyCounter]['label'] = $this->view->translate('Buy from site');
		$result['body'][$bodyCounter]['value'] = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'purchase')) ? Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'purchase')) : '-';
		$result['body'][$bodyCounter]['action'] = '-';
		$bodyCounter++;
		$upgradePoint = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'upgrade_level'));
		if(!empty($upgradePoint)){
			$result['body'][$bodyCounter]['label'] = $this->view->translate('On Membership Upgrade');
			$result['body'][$bodyCounter]['value'] = $upgradePoint;
			$result['body'][$bodyCounter]['action'] = '-';
			$bodyCounter++;
		}
		
		
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result)));
  }
	
	public function termsAction(){
		
		$coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
		$coreContentTableName = $coreContentTable->info('name');
		$corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
		$corePagesTableName = $corePagesTable->info('name');
		$select = $corePagesTable->select()
			->setIntegrityCheck(false)
			->from($corePagesTable, null)
			->where($coreContentTableName . '.name =?', 'sescredit.terms')
			->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id')
			->where($corePagesTableName . '.name = ?', 'sescredit_index_manage');
			
		$result = $corePagesTable->fetchAll($select);
		if(COUNT($result) >0 ){
		        $html = json_decode($result[0]['params'],true);
				$data['terms'] = $html['terms'];
		}else{
			$data = array();
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data)));
	}

	public function sendPointAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $form = new Sescredit_Form_SendPoint();
    if($this->_getParam('getForm')){
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields);
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
    $message = '';
    if (isset($_POST['friend_user_id']) && empty($_POST['friend_user_id'])) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Please enter your friend name.'), 'result' => array()));
    }
    $point = $_POST['send_credit_value'];
    $userCreditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $totalCredit = $userCreditDetailTable->select()
            ->from($userCreditDetailTable->info('name'), 'total_credit')
            ->where('owner_id =?', $viewerId)
            ->query()
            ->fetchColumn();
    if ($totalCredit) {
      if ($totalCredit < $point) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You don\'t have sufficient point to transfer.'), 'result' => array()));
      }
      if (empty($message)) {
        $creditRoute = $this->view->url(array("action" => "transaction"), "sescredit_general", true);
        $receiver = Engine_Api::_()->getItem('user', $_POST['friend_user_id']);
        $creditPageLink = '<a href="' . $creditRoute . '">' . ucfirst($manageActions->name) . " Check Your Point" . '</a>';
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'receive_from_friend', 'owner_id' => $_POST['friend_user_id'], 'action_id' => 0, 'object_id' => 0, 'point' => $point, 'point_type' => 'receive_friend'));
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'transfer_to_friend', 'owner_id' => $viewerId, 'action_id' => 0, 'object_id' => 0, 'point' => $point, 'point_type' => 'transfer_friend'));
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($receiver, $viewer, $viewer, 'notify_sescredit_send_point', array("point" => $point, "creditPageLink" => $creditPageLink));
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiver, 'sescredit_send_point', array('sender_title' => $viewer->displayname, 'point' => $point));
      }
    } else {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You don\'t have point to transfer.'), 'result' => array()));
 
    }
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('success_message'=>$this->view->translate('Successfully Transferred'))));
	}
	
	public function browsesearchAction(){
		$coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
		$coreContentTableName = $coreContentTable->info('name');
		$corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
		$corePagesTableName = $corePagesTable->info('name');
		$select = $corePagesTable->select()
				->setIntegrityCheck(false)
				->from($corePagesTable, null)
				->where($coreContentTableName . '.name=?', 'sescredit.browse-search')
				->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
				->where($corePagesTableName . '.name = ?', 'sescredit_index_transaction');
		$id = $select->query()->fetchColumn();
		$searchForm = $form = new Sescredit_Form_Transaction_Search(array('contentId' => $id));
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $searchForm->setMethod('get')->populate($request->getParams());
		if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
			$this->generateFormFields($formFields);
		} else {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
	}
	
	public function myTransactionsAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
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
				->where($coreContentTableName . '.name=?', 'sescredit.my-transactions')
				->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
				->where($corePagesTableName . '.name = ?', 'sescredit_index_transaction');
		$id = $widgetId = $select->query()->fetchColumn();
    $params = Engine_Api::_()->sescredit()->getWidgetParams($widgetId);

    if (isset($_POST['show']) && !empty($_POST['show']))
      $params['show'] = $_POST['show'];
		if (isset($_POST['point_type']) && !empty($_POST['point_type']))
      $params['point_type'] = $_POST['point_type'];
    if (!empty($searchArray)) {
      foreach ($searchArray as $key => $search) {
        $params[$key] = $search;
      }
    }
    $currentTime = date('Y-m-d H:i:s');
    $creditValueTable = Engine_Api::_()->getDbTable('values', 'sescredit');
    $creditValueTableName = $creditValueTable->info('name');
    $creditTable = Engine_Api::_()->getDbTable('credits', 'sescredit');
    $creditTableName = $creditTable->info('name');
    $language = !empty($_COOKIE['en4_language']) ? $_COOKIE['en4_language'] : 'en';
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $languageColumn = $db->query('SHOW COLUMNS FROM engine4_sescredit_values LIKE "' . $language . '"')->fetch();
    if (empty($languageColumn)) {
      $language = 'en';
    }
    $select = $creditTable->select()
            ->setIntegrityCheck(false)
            ->from($creditTableName, array('*'))
            ->joinLeft($creditValueTableName, $creditValueTableName . '.type = ' . $creditTableName . '.type', array('type', 'language' => new Zend_Db_Expr("Case when $language IS NULL or $language = '' then en else $language end")))
            ->where($creditTableName . '.owner_id =?', $viewer->getIdentity());
            //->where($creditValueTableName . '.member_level =?', $viewer->level_id);
    if (isset($params['show']) && $params['show'] == 'week') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 week"));
      $select->where("DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')");
    } elseif (isset($params['show']) && $params['show'] == 'today') {
      $select->where("$creditTableName.creation_date LIKE ?", date('Y-m-d') . "%");
    } elseif (isset($params['show']) && $params['show'] == 'month') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 month"));
      $select->where("DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')");
    }
    if (isset($params['point_type']) && $params['point_type']) {
      if ($params['point_type'] == 1)
        $select->where("$creditTableName.point_type =?", "credit");
      elseif ($params['point_type'] == 2)
        $select->where("$creditTableName.point_type =?", "deduction");
      elseif ($params['point_type'] == 3)
        $select->where("$creditTableName.point_type =?", "affiliate");
      elseif ($params['point_type'] == 4)
        $select->where("$creditTableName.point_type =?", "transfer_friend");
      elseif ($params['point_type'] == 5)
        $select->where("$creditTableName.point_type =?", "receive_friend");
      elseif ($params['point_type'] == 6)
        $select->where("$creditTableName.point_type =?", "upgrade_level");
      elseif ($params['point_type'] == 7)
        $select->where("$creditTableName.point_type =?", "buy");
    }
    if (isset($params['show_date_field']) && !empty($params['show_date_field'])) {
      $explodeTime = explode('-', $params['show_date_field']);
      $startTime = $explodeTime[0];
      $endTime = $explodeTime[1];
      $select->where("DATE_FORMAT(" . $creditTableName . ".creation_date, '%Y-%m-%d') between ('" . date('Y-m-d', strtotime($startTime)) . "') and ('" . date('Y-m-d', strtotime($endTime)) . "')");
    }
    $select->order('credit_id DESC');
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('lmit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
		
		$data['transactions'] = $this->getTransaction($paginator);
		
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data), $extraParams));
    
	}
	
	public function getTransaction($paginator){
		$result = array();
		$counter = 0;
		$localeLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
		foreach($paginator as $transaction){
			
			$result[$counter] = $transaction->toArray();
			
      if($transaction->point_type == 'affiliate'):
      $result[$counter]['language'] = $this->view->translate("Inviter Affiliation");
      elseif($transaction->point_type == 'transfer_friend'):
      $result[$counter]['language'] = $this->view->translate("Transferred to Friends");
      elseif($transaction->point_type == 'sesproduct_order'):
      $result[$counter]['language'] = $this->view->translate("Product Purchased");
      elseif($transaction->point_type == 'receive_friend'):
      $result[$counter]['language'] = $this->view->translate("Received from Friends");
      elseif($transaction->point_type == 'purchase'):
      $result[$counter]['language'] = $this->view->translate("Buy from site");
      elseif($transaction->point_type == 'upgrade_level'):
      $result[$counter]['language'] = $this->view->translate("On Membership Upgrade");
      else:
      $result[$counter]['language'] = $transaction->language;
      endif;
      
      if(in_array($transaction->point_type, array('credit', 'affiliate', 'receive_friend', 'purchase'))) {
        $result[$counter]['point_type'] = $this->view->translate("credit");;
      } else if(in_array($transaction->point_type, array('deduction', 'transfer_friend', 'sesproduct_order', 'upgrade_level')))  {
        $result[$counter]['point_type'] = $this->view->translate("deduction");;
      }
			
			if( 1 !== count($languageNameList))
				$localeLanguage = $_COOKIE['en4_language'];
			$locale = new Zend_Locale($localeLanguage);
			$date = new Zend_Date(strtotime($transaction->creation_date), false, $locale);
			$result[$counter]['date_title'] = date('jS M', strtotime($transaction->creation_date)).' '.date('Y', strtotime($transaction->creation_date));
			$counter++;
		}
		return $result;
	}
	
	public function earnCreditAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId)
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $form = new Sescredit_Form_ActivityFilter();
		
		//$this->generateFormFields($formFields);
    $actionTypesTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
    $actionTypesTableName = $actionTypesTable->info('name');
    $moduleSettingTable = Engine_Api::_()->getDbTable('modulesettings', 'sescredit');
    $moduleSettingTableName = $moduleSettingTable->info('name');
    $select = $actionTypesTable->select()
            ->from($actionTypesTableName, array('module', 'type'))
            ->setIntegrityCheck(false)
            ->joinLeft($moduleSettingTableName, $moduleSettingTableName . '.module = ' . $actionTypesTableName . '.module', array('modulesetting_id', 'order_id', 'title', 'parent_id', 'status'))
            ->where($moduleSettingTableName . '.parent_id IS NULL or parent_id=""')
            ->where($moduleSettingTableName.'.modulesetting_id IS NOT NULL')
            ->order($moduleSettingTableName . '.order_id ASC');
    $actionTypes = $actionTypesTable->fetchAll($select);
    $selectedModule = !empty($_GET['module']) ? $_GET['module'] : $this->_getParam('moduleName');
    $moduleOptions = array();
    $moduleTable = Engine_Api::_()->getDbTable('modules', 'core');
    foreach ($actionTypes as $actionType) {
      $moduleBaseActionTypes[$actionType->module][$actionType->type] = 'ADMIN_ACTIVITY_TYPE_' . strtoupper($actionType->type);
      if (isset($moduleOptions[$actionType->module])) {
        continue;
      }
      if (!empty($actionType->modulesetting_id) && !$actionType->status) {
        continue;
      }
      if ($moduleTable->getModule($actionType->module)->enabled) {
        $moduleOptions[$actionType->module] = !empty($actionType->title) ? $actionType->title : $moduleTable->getModule($actionType->module)->title;
      }
    }
    if (!$selectedModule || !isset($moduleBaseActionTypes[$selectedModule])) {
      $selectedModule = '';
    }
    $form->module->setMultiOptions(array_merge(array('' => 'All Modules'), $moduleOptions));
    $form->populate(array('module' => $selectedModule));
		$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
		$data['form'] = $formFields[0];
    //$widgetId = (isset($_POST['widget_id']) ? $_POST['widget_id'] : $this->view->identity);
    $widgetName = 'activity-points-info';
    //$this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $creditValueTable = Engine_Api::_()->getDbTable('values', 'sescredit');
    $creditValueTableName = $creditValueTable->info('name');
    $select = $creditValueTable->select()
            ->setIntegrityCheck(false)
            ->from($creditValueTable->info('name'), array('*', "custom_module" => new Zend_Db_Expr("case when parent_id IS Not NULL then parent_id else engine4_sescredit_values.module end"), "custom_orderid" => new Zend_Db_Expr("case when modulesetting_id IS Not NULL then order_id else 99999 end")))
            ->joinLeft($moduleSettingTableName, $moduleSettingTableName . '.module = ' . $creditValueTableName . '.module', array('module_title'=>'title'))
            ->where('member_level =?', $viewer->level_id);
    if ($selectedModule != '') {
      $select->where($creditValueTableName . '.module IN (SELECT  engine4_activity_actiontypes.module from engine4_activity_actiontypes left join engine4_sescredit_modulesettings on engine4_activity_actiontypes.module = engine4_sescredit_modulesettings.module where (engine4_sescredit_modulesettings.status IS NULL or engine4_sescredit_modulesettings.status = 1) and (engine4_activity_actiontypes.module = "' . $selectedModule . '" or parent_id = "' . $selectedModule . '"))');
    }
    else {
      $select->where('engine4_sescredit_modulesettings.status IS NULL or engine4_sescredit_modulesettings.status = 1');
    }
    $select->order('custom_module ASC');
    $select->order('custom_orderid ASC');
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('limit',4));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    
		$data['earn_credit'] = $this->getEarnCredit($paginator);
		
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data), $extraParams));
		
	}
	
	public function getEarnCredit($paginator){
		$counter = -1 ;
		$result = array();
		$languageColumn = 'en';
		$count = 0;
		if( 1 !== count($languageNameList))
				$languageColumn = $_COOKIE['en4_language'];
			$oldHeading = '';
			$acitivityType = '';
			$oldActivityType = '';
		foreach($paginator as $activity){
			//$result[$counter] = $activity->toArray();
			if($activity->custom_module != $oldHeading){
				$counter++;
				$result[$counter]['label'] = !empty($activity->module_title) ? $activity->module_title : $activity->custom_module;
				$count =0;
			}else{
				$count++;
			}
			$oldHeading = $activity->custom_module;
			$result[$counter]['value'][$count]['activity_type'] = empty($activity->$languageColumn) ? (str_replace(array('(subject)','(object)'),'',$this->view->translate($this->view->translate("ADMIN_ACTIVITY_TYPE_".strtoupper($activity->type))))) : $activity->$languageColumn;
			$result[$counter]['value'][$count]['first_activity'] = $activity->firstactivity ? $activity->firstactivity : '-';
			$result[$counter]['value'][$count]['next_activity'] = $activity->nextactivity ? $activity->nextactivity : '-';
			$result[$counter]['value'][$count]['max_perday'] = $activity->maxperday ? $activity->maxperday : '-';
			$result[$counter]['value'][$count]['deduction'] = $activity->deduction ? $activity->deduction : '-';
            
		}
		return $result;
	}
	
	public function howEarnPointAction(){
		$coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
		$coreContentTableName = $coreContentTable->info('name');
		$corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
		$corePagesTableName = $corePagesTable->info('name');
		$select = $corePagesTable->select()
			->setIntegrityCheck(false)
			->from($corePagesTable, null)
			->where($coreContentTableName . '.name =?', 'sescredit.how-to-earn-points')
			->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id')
			->where($corePagesTableName . '.name = ?', 'sescredit_index_earn-credit');
			
		$result = $corePagesTable->fetchAll($select);
		if(COUNT($result) >0 ){
			$params =  $result[0]['params'];
				$data['terms'] = json_decode($params);
		}else{
			$data = array();
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data)));
	}
	
	public function purchasePointsAction(){
		$form = new Sescredit_Form_Purchasepoint();
		
    $priceSymbol = Engine_Api::_()->sescredit()->getCurrencySymbol();
    $options = Engine_Api::_()->getDbTable('offers', 'sescredit')->getOffer();
    $multiOptions = $optionArray = array();
    foreach ($options as $option) {
      $multiOptions[$option->offer_id] = $option->point . " Point in " . Engine_Api::_()->sescredit()->getCurrencyPrice($option->point_value,'','',true);
      $optionArray[$option->offer_id]['point'] = $option->point;
      $optionArray[$option->offer_id]['value'] = Engine_Api::_()->sescredit()->getCurrencyPrice($option->point_value,'','',true);
    }
    if (count($options) < 1)
      $form->sescredit_site_offers->setDescription("No Offers Available.");
    $form->sescredit_site_offers->setMultiOptions($multiOptions);
    $this->view->optionArray = json_encode($optionArray);
    $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $gatewaySelect = $gatewayTable->select()->where('enabled = ?', 1);
    $gateways = $gatewayTable->fetchAll($gatewaySelect);

    $gatewayPlugins = array();
        $gateway_id = "";
    foreach ($gateways as $gateway) {
        if($gateway->plugin == "Payment_Plugin_Gateway_PayPal"){
            $gateway_id = $gateway->gateway_id;
        }
      $gatewayPlugins[] = array(
          'gateway' => $gateway,
          'plugin' => $gateway->getGateway(),
      );
    }
    $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
    $currencySymbol = Engine_Api::_()->sescredit()->getCurrencySymbol(Engine_Api::_()->sescredit()->getCurrentCurrency());
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmultiplecurrency'))
         $currencyValue =  round(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmultiplecurrency.' . Engine_Api::_()->sescredit()->getCurrentCurrency(),1),2);
     else
        $currencyValue = 1;
    $creditvalue = Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.creditvalue', '100');

    $this->generateFormFields($formFields,array('gateway_id'=>$gateway_id,'currencySymbol'=>$currencySymbol,'currencyValue'=>$currencyValue,'creditvalue'=>$creditvalue,'action'=>$this->getBaseUrl(false,'sescredit/payment/process')));

	}
}
