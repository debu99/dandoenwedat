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
class Activity_IndexController extends Sesapi_Controller_Action_Standard
{
	function composerOptionsAction(){
		if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
			$this->sesadvancedactivity();
		}else{
            if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
                $this->coreactivity();
            }
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => ''));
	}
	function coreactivity(){
		$contentResponse = array();
		$request = Zend_Controller_Front::getInstance()->getRequest();
		// Don't render this if not authorized
		$viewer = Engine_Api::_()->user()->getViewer();
		$subject = $this->_getParam('resource_type', '');
		$resource_id = $this->_getParam('resource_id', '');
		// Get permission setting
		if ($viewer->getIdentity() != 0) {
			$permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
			if (Authorization_Api_Core::LEVEL_DISALLOW === $permission) {
				$messageText = "message_denied";
			}
		}
		if ($subject) {
			// Get subject
			$subject = Engine_Api::_()->getItem($subject, $resource_id);
			if ($subject)
				Engine_Api::_()->core()->setSubject($subject);
			if (!$subject->authorization()->isAllowed($viewer, 'view')) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => !empty($messageText) ? $messageText : "Invalid Request"));
			}
		}
		if ($viewer->getIdentity()) {
			$contentResponse['user_image'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
			$contentResponse['user_id'] = $viewer->getIdentity();
			$contentResponse['user_title'] = $viewer->getTitle();
		}
    $contentResponse['reaction_plugin'][0]['reaction_id'] = 1;
    $contentResponse['reaction_plugin'][0]['title'] = 'Like';
    $contentResponse['reaction_plugin'][0]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/b6c60430c0c81b44aac34d34239e44b0.png');
    
		$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
		$usersettings =  'everyone';
		$explodedSettings = explode(',', $usersettings);
		$contentResponse['userSelectedSettings'] = $explodedSettings;
		$this->view->allowprivacysetting = 1;
		if (!$this->view->allowprivacysetting)
			$contentResponse['privacySetting'] =  false;
		else
			$contentResponse['privacySetting'] =  true;
		$contentResponse['privacyOptions'] = Engine_Api::_()->sesapi()->privacyOptions();
		if (!$subject) {
			$allownetworkprivacy = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy', 0);
			$allownetworkprivacytype = 1;
			$allowlistprivacy = 1;
			if ($allownetworkprivacytype == 1) {
				$select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
			} else {
				$select = Engine_Api::_()->getDbtable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
			}
			$usernetworks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);
			if ($allownetworkprivacy && count($usernetworks) && (_SESAPI_VERSION_ANDROID >= 2.3 || _SESAPI_VERSION_IOS >= 1.3)) {
				$networkOptions = array();
				$counterVal =  0;
				foreach ($usernetworks as $networkfilter) {
					if ($counterVal == 0)
						$networkOptions[$counterVal]['first'] = 1;
					$networkOptions[$counterVal]['name'] = "network_list_" . $networkfilter->getIdentity();
					$networkOptions[$counterVal]['value'] = $this->view->translate($networkfilter["title"]);
					$counterVal++;
				}
				$contentResponse['privacyOptions'] = array_merge($contentResponse['privacyOptions'], $networkOptions);
			}
			//network based filtering
			$networkbasedfiltering = 1;
			if ($networkbasedfiltering != 2) {
				if ($networkbasedfiltering == 1) {
					$select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
				} else {
					$select = Engine_Api::_()->getDbtable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
				}
				$networkbasedfilter = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);
			}
			//user list
			$userlists = Engine_Api::_()->getDbtable('lists', 'user')->fetchAll(Engine_Api::_()->getDbtable('lists', 'user')->select()->order('engine4_user_lists.title ASC')->where('owner_id =?', $viewer->getIdentity()));
			if (count($userlists) && $allowlistprivacy  && (_SESAPI_VERSION_ANDROID >= 2.3 || _SESAPI_VERSION_IOS >= 1.3)) {
				$listsOptions = array();
				$counterVal =  0;
				foreach ($userlists as $listsOption) {
					if ($counterVal == 0)
						$listsOptions[$counterVal]['first'] = 1;
            $listsOptions[$counterVal]['name'] = "members_list_" . $listsOption->getIdentity();
            $listsOptions[$counterVal]['value'] = $this->view->translate($listsOption["title"]);
            $counterVal++;
				}
				$contentResponse['privacyOptions'] = array_merge($contentResponse['privacyOptions'], $listsOptions);
			}
			if ($networkbasedfilter) {
				foreach ($networkbasedfilter as $filterBased) {
					$listsArray[] = $filterBased;
				}
			}
			if (count($userlists)) {
				foreach ($userlists as $filterbased) {
					$listsArray[] = $filterbased;
				}
			}
			$filterFeed = !empty($listsArray[0]['filtertype']) ? $listsArray[0]['filtertype'] : "";
			$feedSearchOptions = array();
			$counter = 0;
			foreach ($listsArray as $searchOptions) {
				if (isset($searchOptions['network_id'])) {
					$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/networks.png');
					$feedSearchOptions[$counter]['key'] = 'network_filter_' . $searchOptions['network_id'];
					$feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
					$counter++;
					continue;
				} else if (isset($searchOptions['list_id'])) {
					$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/list.png');
					$feedSearchOptions[$counter]['key'] = 'member_list_' . $searchOptions['list_id'];
					$feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
					$counter++;
					continue;
				}
				if (!empty($searchOptions['file_id'])) {
					$storage = Engine_Api::_()->storage()->get($searchOptions['file_id'], '');
					if ($storage) {
						$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', $storage->getPhotoUrl());
					}
				}
				$feedSearchOptions[$counter]['key'] = $searchOptions['filtertype'];
				$feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
				$counter++;
			}
		} else if ($subject && $subject->getType() == "user") {
			$counter = 0;
			$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/all.png');
			$feedSearchOptions[$counter]['key'] = 'all';
			$feedSearchOptions[$counter]['value'] =  $this->view->translate("All Updates");
			$counter++;

			$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/post_self_buysell.png');
			$feedSearchOptions[$counter]['key'] = 'post_self_buysell';
			$feedSearchOptions[$counter]['value'] =  $this->view->translate("Sell Something");
			$counter++;

			$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/list.png');
			$feedSearchOptions[$counter]['key'] = 'post_self_file';
			$feedSearchOptions[$counter]['value'] =  $this->view->translate("Files");
			$counter++;
			if ($subject->getOwner()->getIdentity() == $this->view->viewer()->getIdentity()) {
				$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/hiddenpost.png');
				$feedSearchOptions[$counter]['key'] = 'hiddenpost';
				$feedSearchOptions[$counter]['value'] =  $this->view->translate("Posts You've Hidden");
				$counter++;
				$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/taggedinpost.png');
				$feedSearchOptions[$counter]['key'] = 'taggedinpost';
				$feedSearchOptions[$counter]['value'] =  $this->view->translate("Posts You're Tagged In");
				$counter++;
			}
		}
		$contentResponse['feedSearchOptions'] = $feedSearchOptions;
		unset($contentResponse['feedSearchOptions']);
		
		// Get some other info
		if (!empty($subject)) {
			$contentResponse['subjectGuid'] = $subject->getGuid(false);
		}
		//composer enable options
		$contentResponse['enableComposer'] = false;
		if ($viewer->getIdentity() && !$this->_getParam('action_id')) {
			if (!$subject || ($subject instanceof Core_Model_Item_Abstract && $subject->isSelf($viewer))) {
				if (Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'user', 'status')) {
					$contentResponse['enableComposer'] = true;
				}
			} else if ($subject) {
				if (Engine_Api::_()->authorization()->isAllowed($subject, $viewer, 'comment')) {
					$contentResponse['enableComposer'] = true;
				}
			}
		}
		//for live stream enable.
		$contentResponse['enableLivestream'] = false;
		if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming')) {
			$contentResponse['enableLivestream'] = true;
		}
		if ($contentResponse['enableComposer']) {
      $composerOptions = array();
 			//$composerOptions = array('addPhoto' => "Photo", 'addVideo' => 'Video', 'addLink' => "Link",'tagPeople' => "Tag People");
			//$getEnableComposers = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options', array());
      $composePartials = array();
      foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
        if( empty($data['composer']) ) {
          continue;
        }
        foreach( $data['composer'] as $type => $config ) {
          if( !empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1]) ) {
            continue;
          }
          $composePartials[] = $config['script'];
        }
      }
      $activitycomposerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options');
      foreach($composePartials as $partial ):
        if(false !== strpos($partial[0], '_composeTag') && !in_array('userTags', $activitycomposerOptions)) {
          continue;
        }
        // For core modules
        if(false !== strpos($partial[0], '_composeLink')) {
          $composerOptions['addLink'] = $this->view->translate('Add Link');
        } 
        // For core  album
        if(false !== strpos($partial[0], '_composePhoto')) {
          $composerOptions['addPhoto'] = $this->view->translate('Add Photo');
        }
         // For core  music
        if(false !== strpos($partial[0], '_composeMusic')) {
          $composerOptions['addMusic'] = $this->view->translate('Add Music');
        }
        // For core  video
        if(false !== strpos($partial[0], '_composeVideo')) {
          $composerOptions['addVideo'] = $this->view->translate('Add Video');
        }
        // For sesmusic
        if(false !== strpos($partial[0], '_composeMusic') && Engine_Api::_()->sesapi()->isModuleEnable('sesmusic')) {
          $composerOptions['addMusic'] = $this->view->translate('Add Music');
        }
        // For sesvideo
        if(false !== strpos($partial[0], '_composeVideo') && Engine_Api::_()->sesapi()->isModuleEnable('sesvideo')) {
          $composerOptions['addVideo'] = $this->view->translate('Add Video');
        }

        // For sespagepoll
        if ($subject && $subject->getType() == 'sespage_page' && (false !== strpos($partial[0], '_composeSespagepoll')) && Engine_Api::_()->sesapi()->isModuleEnable('sespagepoll') && ( !defined(_SESAPI_VERSION_IOS) || _SESAPI_VERSION_IOS >= 2.3)) {
          $composerOptions['addPoll'] = $this->view->translate("Add Poll");
        }
        // For sesgrouppoll
        if ($subject && $subject->getType() == 'sesgroup_group' && (false !== strpos($partial[0], '_composeSesgrouppoll')) && Engine_Api::_()->sesapi()->isModuleEnable('sesgrouppoll')  && ( !defined(_SESAPI_VERSION_IOS) || _SESAPI_VERSION_IOS >= 2.3)) {
          $composerOptions['addPoll'] = $this->view->translate("Add Poll");
        }
        if ($subject && $subject->getType() == 'businesses' && (false !== strpos($partial[0], '_composeSesbusinesspoll')) && Engine_Api::_()->sesapi()->isModuleEnable('sesbusinesspoll')  && ( !defined(_SESAPI_VERSION_IOS) || _SESAPI_VERSION_IOS >= 2.3)) {
          $composerOptions['addPoll'] = $this->view->translate("Add Poll");
        }
      endforeach;
			// for live streaming.
      if ((_SESAPI_VERSION_IOS < 1.8 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID < 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
				$key = array_search('elivestreaming', $getEnableComposers);
				unset($getEnableComposers[$key]);
			}
			$composerOptionsCounter = 0;
			$counter = 0;
			foreach ($composerOptions as $key => $option) {
				$contentResponse['composerOptions'][$counter]['value'] = $this->view->translate($option);
				$contentResponse['composerOptions'][$counter]['name'] = $key;
				$counter++;
			}
		}
		if (!empty($messageText) && empty($subject))
			$contentResponse['message_permission'] = $messageText;

		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $contentResponse));
	}
  function sesadvancedactivity()
  {
    $contentResponse = array();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = $this->_getParam('resource_type', '');
    $resource_id = $this->_getParam('resource_id', '');
    // Get permission setting
    if ($viewer->getIdentity() != 0) {
      $permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
      if (Authorization_Api_Core::LEVEL_DISALLOW === $permission) {
      $messageText = "message_denied";
      }
    }
    if ($subject) {
      // Get subject
      $subject = Engine_Api::_()->getItem($subject, $resource_id);
      if ($subject)
      Engine_Api::_()->core()->setSubject($subject);
      if (!$subject->authorization()->isAllowed($viewer, 'view')) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => !empty($messageText) ? $messageText : "Invalid Request"));
      }
    }
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
      $recArray = array();
      $reactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getPaginator();
      $counterReaction = 0;
      foreach ($reactions as $reac) {
      if (!$reac->enabled)
        continue;
      $contentResponse['reaction_plugin'][$counterReaction]['reaction_id']  = $reac['reaction_id'];
      $contentResponse['reaction_plugin'][$counterReaction]['title']  = $this->view->translate($reac['title']);
      $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
      $contentResponse['reaction_plugin'][$counterReaction]['image']  = $icon['main'];
      $counterReaction++;
      }
    }
    $emojiText = "";
    $contentResponse['defaultCurrency'] = Engine_Api::_()->sesadvancedactivity()->getCurrencySymbol();
    $contentResponse['sesfeelingactivity'] = 0;
    $emojiPluginEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesfeelingactivity');
    if ($emojiPluginEnable && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeelingactivity.enablefeeling', 1) && Engine_Api::_()->authorization()->isAllowed('sesfelngactvity', null, 'enablefeeling')) {
      $enableFeelingsCategories = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesfelngactvity', $viewer, 'felingscategorie');
      if (in_array('1', $enableFeelingsCategories)) {
      $feelingEnable = true;
      $emojiText = $this->view->translate("Feeling/Activity/Sticker");
      } else {
      $emojiText = $this->view->translate("Activity/Sticker");
      }
      $contentResponse['sesfeelingactivity'] = 1;
    } else {
      $contentResponse['sesfeelingactivity'] = 1;
      $emojiText = $this->view->translate("Sticker");
    }
    if ($viewer->getIdentity()) {
      $contentResponse['user_image'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
      $contentResponse['user_id'] = $viewer->getIdentity();
      $contentResponse['user_title'] = $viewer->getTitle();
    }

    $actionTable = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity');
    $usersettings =  rtrim(Engine_Api::_()->getApi('settings', 'core')->getSetting($viewer->getIdentity() . '.activity.user.setting', 'everyone'), ',');
    $explodedSettings = explode(',', $usersettings);
    $contentResponse['userSelectedSettings'] = $explodedSettings;

    $this->view->allowprivacysetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.allowprivacysetting', 1);
    if (!$this->view->allowprivacysetting)
      $contentResponse['privacySetting'] =  false;
    else
      $contentResponse['privacySetting'] =  true;
    $contentResponse['privacyOptions'] = Engine_Api::_()->sesapi()->privacyOptions();

	if (!$subject) {
	  $allownetworkprivacy = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy',0);
	  $allowlistprivacy = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.allowlistprivacy', 1);
	  
    if($allownetworkprivacy == 1){
        $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
    }
    else if($allownetworkprivacy == 2){
      $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
    }else{
      $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->where(0);
    }
	  $usernetworks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);

	  if (_SESAPI_VERSION_ANDROID >= 1.2) {
      $enableVideo = 1;
	  }
	  if (_SESAPI_VERSION_IOS >= 1.2) {
      $enableVideo = 1;
	  }

	  if ($allownetworkprivacy && count($usernetworks) && (_SESAPI_VERSION_ANDROID >= 2.3 || _SESAPI_VERSION_IOS >= 1.3)) {
      $networkOptions = array();
      $counterVal =  0;
      foreach ($usernetworks as $networkfilter) {
        if ($counterVal == 0)
        $networkOptions[$counterVal]['first'] = 1;
        $networkOptions[$counterVal]['name'] = "network_list_" . $networkfilter->getIdentity();
        $networkOptions[$counterVal]['value'] = $this->view->translate($networkfilter["title"]);
        $counterVal++;
      }
      $contentResponse['privacyOptions'] = array_merge($contentResponse['privacyOptions'], $networkOptions);
	  }

	  //network based filtering
	  $networkbasedfiltering = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.networkbasedfiltering', 1);
	  if ($networkbasedfiltering != 2) {
      if ($networkbasedfiltering == 1) {
        $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
      } else {
        $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
      }
      $networkbasedfilter = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);
	  }

	  //user list
	  $userlists = Engine_Api::_()->getDbtable('lists', 'user')->fetchAll(Engine_Api::_()->getDbtable('lists', 'user')->select()->order('engine4_user_lists.title ASC')->where('owner_id =?', $viewer->getIdentity()));

	  if (count($userlists) && $allowlistprivacy  && (_SESAPI_VERSION_ANDROID >= 2.3 || _SESAPI_VERSION_IOS >= 1.3)) {
      $listsOptions = array();
      $counterVal =  0;
      foreach ($userlists as $listsOption) {
        if ($counterVal == 0)
        $listsOptions[$counterVal]['first'] = 1;
        $listsOptions[$counterVal]['name'] = "members_list_" . $listsOption->getIdentity();
        $listsOptions[$counterVal]['value'] = $this->view->translate($listsOption["title"]);
        $counterVal++;
      }
      $contentResponse['privacyOptions'] = array_merge($contentResponse['privacyOptions'], $listsOptions);
	  }
	  $activeLists = Engine_Api::_()->getDbTable('filterlists', 'sesadvancedactivity')->getLists(array());
	  $lists = $activeLists->toArray();

	  //check module enable
	  $listsArray = array();
	  foreach ($lists as $list) {
		if ($viewer->getIdentity() == 0 && ($list['filtertype'] == "my_friends" || $list['filtertype'] == "scheduled_post"  || $list['filtertype'] == "saved_feeds" || $list['filtertype'] == "my_networks")) {
		  continue;
		}
		if ($list['filtertype'] != 'all' && $list['filtertype'] != 'scheduled_post' && $list['filtertype'] != 'my_networks' && $list['filtertype'] != 'my_friends' && $list['filtertype'] != 'posts' && $list['filtertype'] != 'saved_feeds' && $list['filtertype'] != 'post_self_buysell'  && $list['filtertype'] != 'post_self_file' && !Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($list['filtertype']))
		  continue;
		$listsArray[] = $list;
	  }
	  if ($networkbasedfilter) {
		foreach ($networkbasedfilter as $filterBased) {
		  $listsArray[] = $filterBased;
		}
	  }
	  if (count($userlists)) {
		foreach ($userlists as $filterbased) {
		  $listsArray[] = $filterbased;
		}
	  }
	  $filterFeed = $listsArray[0]['filtertype'];
	  $feedSearchOptions = array();
	  $counter = 0;
	  foreach ($listsArray as $searchOptions) {
		if (isset($searchOptions['network_id'])) {
		  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/networks.png');
		  $feedSearchOptions[$counter]['key'] = 'network_filter_' . $searchOptions['network_id'];
		  $feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
		  $counter++;
		  continue;
		} else if (isset($searchOptions['list_id'])) {
		  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/list.png');
		  $feedSearchOptions[$counter]['key'] = 'member_list_' . $searchOptions['list_id'];
		  $feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
		  $counter++;
		  continue;
		}
		if (!empty($searchOptions['file_id'])) {
		  $storage = Engine_Api::_()->storage()->get($searchOptions['file_id'], '');
		  if ($storage) {
			$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', $storage->getPhotoUrl());
		  }
		}
		$feedSearchOptions[$counter]['key'] = $searchOptions['filtertype'];
		$feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
		$counter++;
	  }
	} else if ($subject && $subject->getType() == "user") {
	  $counter = 0;
	  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/all.png');
	  $feedSearchOptions[$counter]['key'] = 'all';
	  $feedSearchOptions[$counter]['value'] =  $this->view->translate("All Updates");
	  $counter++;

	  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/post_self_buysell.png');
	  $feedSearchOptions[$counter]['key'] = 'post_self_buysell';
	  $feedSearchOptions[$counter]['value'] =  $this->view->translate("Sell Something");
	  $counter++;

	  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/list.png');
	  $feedSearchOptions[$counter]['key'] = 'post_self_file';
	  $feedSearchOptions[$counter]['value'] =  $this->view->translate("Files");
	  $counter++;
	  if ($subject->getOwner()->getIdentity() == $this->view->viewer()->getIdentity()) {
		$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/hiddenpost.png');
		$feedSearchOptions[$counter]['key'] = 'hiddenpost';
		$feedSearchOptions[$counter]['value'] =  $this->view->translate("Posts You've Hidden");
		$counter++;
		$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/taggedinpost.png');
		$feedSearchOptions[$counter]['key'] = 'taggedinpost';
		$feedSearchOptions[$counter]['value'] =  $this->view->translate("Posts You're Tagged In");
		$counter++;
	  }
	}
	$contentResponse['feedSearchOptions'] = $feedSearchOptions;
	// Get some other info
	if (!empty($subject)) {
	  $contentResponse['subjectGuid'] = $subject->getGuid(false);
	}
	//composer enable options
	$contentResponse['enableComposer'] = false;
	if ($viewer->getIdentity() && !$this->_getParam('action_id')) {
	  if (!$subject || ($subject instanceof Core_Model_Item_Abstract && $subject->isSelf($viewer))) {
		if (Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'user', 'status')) {
		  $contentResponse['enableComposer'] = true;
		}
	  } else if ($subject) {
		if (Engine_Api::_()->authorization()->isAllowed($subject, $viewer, 'comment')) {
		  $contentResponse['enableComposer'] = true;
		}
	  }
	}
	// for live stream enable.
	$contentResponse['enableLivestream'] = false;
	if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming')) {
		$contentResponse['enableLivestream'] = true;
	}
	if (!empty($feelingEnable))
	  $contentResponse['activityStikersMenu'][] = array('label' => $this->view->translate('Fellings'), 'name' => 'feelings', 'title' => $this->view->translate('How Are You Feeling?'));
	$contentResponse['activityStikersMenu'][] = array('label' => $this->view->translate('Stickers'), 'name' => 'stickers', 'title' => $this->view->translate('Add a Sticker?'));

	if (($emojiPluginEnable))
	  $contentResponse['activityStikersMenu'][] = array('label' => $this->view->translate('Activities'), 'name' => 'activities', 'title' => $this->view->translate('What Are You Doing?'));

	if ($contentResponse['enableComposer']) {
	  $composerOptions = array('addPhoto' => "Photo", 'addVideo' => 'Video', 'checkIn' => "Check In"/*,'addQuote'=>"Quote"*/, 'addLink' => "Link", 'sellSomething' => "Sell Something", 'scheduledPost' => "Scheduled Post", 'tagPeople' => "Tag People", 'emotions' => $emojiText);
	  $getEnableComposers = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.composeroptions', array());
	  // for live streaming.
    if ((_SESAPI_VERSION_IOS < 1.8 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID < 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
		$key = array_search('elivestreaming', $getEnableComposers);
		unset($getEnableComposers[$key]);
	  }
	  $composerOptions = array();
	  //foreach($getEnableComposers as $compose){

	  if ($subject && method_exists($subject, 'activityComposerOptions')) {
		$allowedExtentions =  $subject->activityComposerOptions($subject);
		if (in_array('photo', $getEnableComposers)) {
		  unset($getEnableComposers['photo']);
		}
		if (in_array('sesmusic', $getEnableComposers)) {
		  unset($getEnableComposers['sesmusic']);
		}
		if (in_array('video', $getEnableComposers)) {
		  unset($getEnableComposers['video']);
		}
		$composePartialsArrayDiff = array();

		foreach ($getEnableComposers as $key => $partials) {
		  if (array_key_exists($partials, $allowedExtentions)) {
			$composePartialsArrayDiff[$key] = $partials;
		  }
		}
		if (in_array('sespage_photo', $composePartialsArrayDiff) || in_array('sesgroup_photo', $composePartialsArrayDiff) || in_array('sesbusiness_photo', $composePartialsArrayDiff)) {
		  $composePartialsArrayDiff['photo'] = "photo";
		} else if (in_array('photo', $composePartialsArrayDiff)) {
		  unset($composePartialsArrayDiff['photo']);
		}
		if (in_array('sespagevideo', $composePartialsArrayDiff) || in_array('sesgroupvideo', $composePartialsArrayDiff) || in_array('sesbusiness', $composePartialsArrayDiff)) {
		  $composePartialsArrayDiff['video'] = "video";
		} else if (in_array('video', $composePartialsArrayDiff)) {
		  unset($composePartialsArrayDiff['video']);
		}
		if (in_array('sespagemusic', $composePartialsArrayDiff) || in_array('sesgroupmusic', $composePartialsArrayDiff) || in_array('sesbusinessmusic', $composePartialsArrayDiff)) {
		  $composePartialsArrayDiff['sesmusic'] = "sesmusic";
		} else if (in_array('sesmusic', $composePartialsArrayDiff)) {
		  unset($composePartialsArrayDiff['sesmusic']);
		}
		if (in_array('sespagepoll', $composePartialsArrayDiff) || in_array('sesbusinesspoll', $composePartialsArrayDiff) || in_array('sesgrouppoll', $composePartialsArrayDiff)   && ( !defined(_SESAPI_VERSION_IOS) || _SESAPI_VERSION_IOS >= 2.3)) {
		  $composePartialsArrayDiff['poll'] = "poll";
		}

		$getEnableComposers = $composePartialsArrayDiff;
	  } else {
		if ($subject && $this->view->viewer()->getIdentity() && $subject->getType() != "user" &&  $subject->getIdentity() != $this->view->viewer()->getIdentity()) {
		  unset($composerOptions['sellSomething']);
		}
	  }
	  // for live streaming.
	  if (in_array('elivestreaming', $getEnableComposers)) {
		$composerOptions['elivestreaming'] = $this->view->translate("elive_sesapi_controllers_activity_index");
	  }
	  if ($subject && $subject->getType() == 'sespage_page' && in_array('sespagepoll', $getEnableComposers) && Engine_Api::_()->sesapi()->isModuleEnable('sespagepoll')   && ( !defined(_SESAPI_VERSION_IOS) || _SESAPI_VERSION_IOS >= 2.3)) {
      $composerOptions['addPoll'] = $this->view->translate("Add Poll");
	  }
	  if ($subject && $subject->getType() == 'sesgroup_group' && in_array('sesgrouppoll', $getEnableComposers) && Engine_Api::_()->sesapi()->isModuleEnable('sesgrouppoll')   && ( !defined(_SESAPI_VERSION_IOS) || _SESAPI_VERSION_IOS >= 2.3)) {
		$composerOptions['addPoll'] = $this->view->translate("Add Poll");
	  }
	  if ($subject && $subject->getType() == 'businesses' && in_array('sesbusinesspoll', $getEnableComposers) && Engine_Api::_()->sesapi()->isModuleEnable('sesbusinesspoll')   && ( !defined(_SESAPI_VERSION_IOS) || _SESAPI_VERSION_IOS >= 2.3)) {
		$composerOptions['addPoll'] = $this->view->translate("Add Poll");
	  }
	  if (in_array('photo', $getEnableComposers) && (Engine_Api::_()->sesapi()->isModuleEnable('sesalbum') || Engine_Api::_()->sesapi()->isModuleEnable('album'))) {
		$composerOptions['addPhoto'] = $this->view->translate("Photo");
	  }
	  if ((in_array('sesmusic', $getEnableComposers) || in_array('sesmusic', $getEnableComposers)) && Engine_Api::_()->sesapi()->isModuleEnable('sesmusic') && _SESAPI_PLATFORM_SERVICE != 1) {
		$composerOptions['addMusic'] = $this->view->translate("Music");
	  }
	  if (in_array('video', $getEnableComposers) && (Engine_Api::_()->sesapi()->isModuleEnable('sesvideo') || Engine_Api::_()->sesapi()->isModuleEnable('video'))) {
		$composerOptions['addVideo'] = $this->view->translate("Video");
	  }
	  if (in_array('locationses', $getEnableComposers)) {
		$composerOptions['checkIn'] = $this->view->translate("Check In");
	  }
	  if (in_array('sesadvancedactivitylink', $getEnableComposers)) {
		$composerOptions['addLink'] = $this->view->translate("Link");
	  }
	  if (in_array('buysell', $getEnableComposers)) {
		$composerOptions['sellSomething'] = $this->view->translate("Sell Something");
	  }
	  if (in_array('shedulepost', $getEnableComposers)) {
		$composerOptions['scheduledPost'] = $this->view->translate("Scheduled Post");
	  }
	  if (in_array('tagUseses', $getEnableComposers)) {
		$composerOptions['tagPeople'] = $this->view->translate("Tag People");
	  }
	  if (in_array('emojisses', $getEnableComposers) || in_array('feelingssctivity', $getEnableComposers)) {
		$composerOptions['emotions'] = $emojiText;
	  }
	  //  }

	  if ($contentResponse['sesfeelingactivity'] == 0) {
		unset($composerOptions['emotions']);
	  }

	  $composerOptionsCounter = 0;
	  $counter = 0;
	  foreach ($composerOptions as $key => $option) {
		$contentResponse['composerOptions'][$counter]['value'] = $this->view->translate($option);
		$contentResponse['composerOptions'][$counter]['name'] = $key;
		$counter++;
	  }
	}

	$textColorChange = Engine_Api::_()->getDbTable('textcolors', 'sesadvancedactivity')->getAllTextColors();
	$type = _SESAPI_PLATFORM_SERVICE;
	if (count($textColorChange)) {
	  $textStringColor = array();
	  $counterColor = 0;
	  foreach ($textColorChange as $val) {
		if ($type == 1) {
		  $textStringColor[$val['string']] = '#' . $val['color'];
		} else {
		  $textStringColor[$counterColor]['name'] = $val['string'];
		  $textStringColor[$counterColor]['color'] = '#' . $val['color'];
		  $counterColor++;
		}
	  }
	  $contentResponse['textStringColor'] = $textStringColor;
	}

	if (Engine_Api::_()->sesbasic()->isModuleEnable('Sesfeedbg') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeedbg.enablefeedbg', 1) && $viewer->getIdentity()) {
    $sesfeedbg_enablefeedbg = false;
    $enablefeedbg = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions');
    if(in_array('enablefeedbg', $enablefeedbg)) {
      $sesfeedbg_enablefeedbg = true;
    }
	  $sesfeedbg_limit_show = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesfeedbg', 'max');
	  if ($sesfeedbg_enablefeedbg) {
		$getFeaturedBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'sesfeedbg')->getBackgrounds(array('admin' => 1, 'fetchAll' => 1, 'sesfeedbg_limit_show' => 5, 'featured' => 1));
		$featured = $backgrounds = array();
		foreach ($getFeaturedBackgrounds as $getFeaturedBackground) {
		  $featured[] = $getFeaturedBackground->background_id;
		}
		if (count($featured) > 0) {
		  $sesfeedbg_limit_show = $sesfeedbg_limit_show - 5;
		}
		$getBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'sesfeedbg')->getBackgrounds(array('admin' => 1, 'fetchAll' => 1, 'sesfeedbg_limit_show' => $sesfeedbg_limit_show, 'feedbgorder' => $this->feedbgorder, 'featuredbgIds' => $featured));
		foreach ($getBackgrounds as $getBackground) {
		  $backgrounds[] = $getBackground->background_id;
		}
		if (count($featured) > 0) {
		  $backgrounds = array_merge($featured, $backgrounds);
		}

		if (count($backgrounds) > 0) {
		  $counter = 0;
		  $contentResponse['feedBgStatusPost'][$counter]['photo'] = $this->getBaseUrl('', "application/modules/Sesfeedbg/externals/images/white.png");
		  $contentResponse['feedBgStatusPost'][$counter]['background_id'] = 0;
		  $counter++;
		  foreach ($backgrounds as $getBackground) {
			$getBackground = Engine_Api::_()->getItem('sesfeedbg_background', $getBackground);
			if ($getBackground->file_id) {
			  $photo = Engine_Api::_()->storage()->get($getBackground->file_id, '');
			  if ($photo) {
				$photo = $this->getBaseUrl('', $photo->getPhotoUrl());
				$contentResponse['feedBgStatusPost'][$counter]['photo'] = $photo;
				$contentResponse['feedBgStatusPost'][$counter]['background_id'] = $getBackground->getIdentity();
				$counter++;
			  }
			}
		  }
		}
	  }
	}
	if (!empty($messageText) && empty($subject))
	  $contentResponse['message_permission'] = $messageText;
	if (empty($subject)) {
	  //intelligent notification
	  $contentResponse['intelligent_notifications'] = $this->intelligentNotification();
	}
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $contentResponse));
  }

  public function indexAction()
  {
	$contentResponse = array();
	$request = Zend_Controller_Front::getInstance()->getRequest();
	// Don't render this if not authorized
	$viewer = Engine_Api::_()->user()->getViewer();
	$subject = $this->_getParam('resource_type', '');
	$resource_id = $this->_getParam('resource_id', '');
	if ($subject) {
		// Get subject
		$subject = Engine_Api::_()->getItem($subject, $resource_id);
		if ($subject)
		Engine_Api::_()->core()->setSubject($subject);
	}
	if ($viewer->getIdentity()) {
		$contentResponse['user_image'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
		$contentResponse['user_id'] = $viewer->getIdentity();
		$contentResponse['user_title'] = $viewer->getTitle();
	}
	$contentResponse['feedOnly'] = $feedOnly = $request->getParam('feedOnly', false);
	$getUpdate = $request->getParam('getUpdate');
	$checkUpdate = $request->getParam('checkUpdate');
	$contentResponse['filterFeed'] = $filterFeed = $this->_getParam('filterFeed', 'all');
	$contentResponse['length'] = $length = $request->getParam('limit', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
	$contentResponse['itemActionLimit']  = $itemActionLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.userlength', 5);
	$this->view->action_id = (int) $request->getParam('action_id');
	if ($length > 50) {
      $contentResponse['length'] = $length = 50;
	}
	// Get all activity feed types for custom view?
	$actionTypesTable = Engine_Api::_()->getDbtable('actionTypes', 'activity');
	$groupedActionTypes = $actionTypesTable->getEnabledGroupedActionTypes();
	$actionTypeGroup = $filterFeed;
	$actionTypeFilters = array();
	//SES advanced member plugin followig work
	$isSesmember = $actionTypeGroup == 'sesmember' && Engine_Api::_()->sesbasic()->isModuleEnable('sesmember') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active', 1);
	if (!$isSesmember) {
		if ($actionTypeGroup && isset($groupedActionTypes[$actionTypeGroup])) {
		$actionTypeFilters = $groupedActionTypes[$actionTypeGroup];
		if ($actionTypeGroup == 'sesalbum' || $actionTypeGroup == 'album')
			$actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes['photo']);
		else if ($actionTypeGroup == 'sesvideo')
			$actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes['video']);
		}
	}
	if ($actionTypeGroup == 'post_self_buysell')
		$actionTypeFilters = array('post_self_buysell');
	else if ($actionTypeGroup == 'post_self_file')
		$actionTypeFilters = array('post_self_file');
	//else if(strpos($actionTypeGroup , 'network_filter_' ) !== false)
	// $actionTypeFilters = array('network');
	$isOnThisDayPage = false;
	if (!empty($_POST['isOnThisDayPage'])) {
		$isOnThisDayPage = true;
	}
	if ((int) $_POST['maxid'] == 0 && Engine_Api::_()->sesbasic()->isModuleEnable('sescommunityads')) {
		$front = Zend_Controller_Front::getInstance();
		$key = Engine_Api::_()->sescommunityads()->getKey($front);
		if (!empty($_SESSION[$key]))
		unset($_SESSION[$key]);
		$_SESSION[$key] = array();
		$_SESSION[$key . "_stop"] = false;
	}
	$this->view->composerOptions = $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.composeroptions', array());
	// Get config options for activity
	$hashTag = isset($_POST['hashtag']) ? str_replace('#', '', $_POST['hashtag']) : '';
	$config = array(
		'action_id' => (int) $_POST['action_id'],
		'max_id'    => (int) $_POST['maxid'],
		'min_id'    => (int) $_POST['minid'],
		'limit'     => (int) $length,
		'showTypes' => $actionTypeFilters,
		'filterFeed' => $filterFeed,
		'hashTag' => $hashTag,
		'action_video_id' => $this->_getParam('action_video_id'),
		'targetPost' => in_array('sesadvancedactivitytargetpost', $composerOptions),
		'isOnThisDayPage' => $isOnThisDayPage,
		'allvideos' => $this->_getParam('allvideos')
	);
		if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
				$sesAdv = true;
		}else{
				if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
						$sesAdv = false;
				}
		}
		if($sesAdv)
			$actionTable = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity');
		else
			$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
			// Pre-process feed items
	$selectCount = 0;
	$nextid = null;
	$firstid = null;
	$tmpConfig = $config;
	$activity = array();
	$endOfFeed = false;
	$friendRequests = array();
	$itemActionCounts = array();
	$enabledModules = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
	$counter = 0;
	$backGroundEnable =  Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeedbg');
	$contentprofilecoverphotoenable =  Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesusercoverphoto');
	$sesAdvancedactivitytextlimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.textlimit', 120);
	$enableFeedBg =  Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeedbg.enablefeedbg', 1);
	$sesadvancedactivitybigtext = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.bigtext', 1);
	$sesAdvancedactivityfonttextsize = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.fonttextsize', 24);
	do {
		// Get current batch
		$actions = null;
		// Where the Activity Feed is Fetched
		if (!empty($subject) && $sesAdv) {
			$actions = $actionTable->getActivityAbout($subject, $viewer, $tmpConfig);
		} elseif(!empty($subject) && !$sesAdv) {
			$actions = $actionTable->getActivity($viewer, $tmpConfig,$subject);
		} else {
			$actions = $actionTable->getActivity($viewer, $tmpConfig);
		}
		$selectCount++;
		// Are we at the end?
		if (count($actions) < $length || count($actions) <= 0) {
		$endOfFeed = true;
		}
		$allowDelete = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete');
		if ($viewer->getIdentity()) {
		$activity_moderate =  Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
		} else {
		$activity_moderate = 0;
		}
		$activityTypeTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
		// Pre-process
		if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity'))
		$feeling = true;
		else
		$feeling = false;
		if (count($actions) > 0) {
			foreach ($actions as $action) {
				$return = false;
				try {
				include('activity.php');
				if (!empty($break))
					break;
				} catch (Exception $e) { throw $e;
				continue;
				}
				if (!$return)
				$counter++;
			}
		}
		// Set next tmp max_id
		if ($nextid) {
		$tmpConfig['max_id'] = $nextid;
		}
		if (!empty($tmpConfig['action_id'])) {
		$actions = array();
		}
	} while (count($activity) < $length && $selectCount <= 3 && !$endOfFeed);
	if ($checkUpdate) {
		$count = count($actions);
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $count));
	}
	$communityAdsEnable = false;
	//community ads integration
	if (_SESAPI_VERSION_ANDROID >= 2.6)
		$communityAdsEnable = true;
	if (_SESAPI_VERSION_IOS >= 1.6)
		$communityAdsEnable = true;

	$contentCounter = $this->_getParam('contentCounter', 0);
	$activityArrayContent = array();
	$communityadsExecuted = false;
	if ($communityAdsEnable && Engine_Api::_()->sesbasic()->isModuleEnable('sescommunityads') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescommunityads_advertisement_enable', '1')) {
		$counterActivity = 0;
		$communityadsExecuted = true;
		foreach ($activity as $acti) {
		$content = $this->sescommunityAds($subject, $contentCounter);
		if (count($content)) {
			$activityArrayContent[$counterActivity] = $content;
			if ($activityArrayContent[$counterActivity]['ad_type'] != "boost_post_cnt")
			$activityArrayContent[$counterActivity]['content_type'] = 'communityads';
			else
			$activityArrayContent[$counterActivity]['content_type'] = 'feed';
			$counterActivity++;
		}
		$activityArrayContent[$counterActivity] = $acti;
		$activityArrayContent[$counterActivity]['content_type'] = 'feed';
		$contentCounter++;
		$counterActivity++;
		}
	}
	$enable = false;
	if (_SESAPI_VERSION_ANDROID >= 2.2)
		$enable = true;
	if (_SESAPI_VERSION_IOS >= 2.2)
		$enable = true;

	if (!$subject && $enable && !$this->_getParam('allvideos', 0)) {
		//get pymk and se default ads
		$counterActivity = 0;
		if ($communityadsExecuted) {
		$activityArrayResult = $activityArrayContent;
		} else {
		$activityArrayResult = $activity;
		}
		foreach ($activityArrayResult as $acti) {
		$content = $this->canShowAddsAndPeopleYoumayKnow($counterActivity);
		if (count($content['pymk'])) {
			if (count($content['pymk']['users'])) {
			$activityArrayContent[$counterActivity]['result'] = $content['pymk']['users'];
			$activityArrayContent[$counterActivity]['seeall'] = $content['pymk']['sellall'];
			$activityArrayContent[$counterActivity]['content_type'] = 'peopleyoumayknow';
			$counterActivity++;
			}
		}
		if (count($content['ads'])) {
			$activityArrayContent[$counterActivity] = $content['ads'];
			$activityArrayContent[$counterActivity]['content_type'] = 'ads';
			$counterActivity++;
		}
		$activityArrayContent[$counterActivity] = $acti;
		if (empty($activityArrayContent[$counterActivity]['content_type']))
			$activityArrayContent[$counterActivity]['content_type'] = 'feed';
		$contentCounter++;
		$counterActivity++;
		}
	} else if (!$communityadsExecuted)
		$activityArrayContent = $activity;
	$contentResponse['activity'] = $activityArrayContent;
	$contentResponse['activityCount'] = count($activity);
	$contentResponse['nextid'] = $nextid;
	$contentResponse['contentCounter'] = $contentCounter + count($activity);
	$contentResponse['firstid'] = (int) $firstid;
	$contentResponse['endOfFeed'] = $endOfFeed;
	//send user pages in response
	if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sespage')) {
		$pageAttr = $this->getPages();
		if (count($pageAttr))
		$contentResponse['sespage_page'] = $pageAttr;
	}
	if ($subject && $subject->getType() == "sespage_page") {
		$pageAttr = $this->postAttributionSespage($subject);
		if ($pageAttr)
		$contentResponse['activity_attribution'] = $pageAttr;
	}
	//send business in response
	if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesbusiness')) {
		$pageAttr = $this->getBusinesses();
		if (count($pageAttr))
		$contentResponse['businesses'] = $pageAttr;
	}
	if ($subject && $subject->getType() == "businesses") {
		$pageAttr = $this->postAttributionSesbusiness($subject);
		if ($pageAttr)
		$contentResponse['activity_attribution'] = $pageAttr;
	}
	if($config['allvideos'] == "1" && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')){
		$contentResponse['activity'] = $this->loadOnlyVideo($contentResponse['activity']);
	}      
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $contentResponse));
  }

  function getBusinesses()
  {
	$user_id = $this->_getParam('user_id', false);
	$table = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness');
	$selelct = $table->select($table->info('name'), 'business_id')->where('user_id =?', $this->view->viewer()->getIdentity());
	$res = $table->fetchAll($selelct);
	$pageIds = array();
	foreach ($res as $page) {
	  $pageIds[] = $page->business_id;
	}
	if (!$user_id)
	  $user_id = $this->view->viewer()->getIdentity();
	$value['user_id'] = $user_id;
	$value['businessIds'] = $pageIds;
	$value['fetchAll'] = true;
	$pages = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')->getBusinessSelect($value);
	$counter = 0;
	$userPages = array();
	$viewer = $this->view->viewer();
	$userPages[$counter]['guid'] = $viewer->getGuid();
	$userPages[$counter]['photo'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
	$userPages[$counter]['title'] = $viewer->getTitle();
	$counter++;
	foreach ($pages as $page) {
	  $userPages[$counter]['guid'] = $page->getGuid();
	  $userPages[$counter]['photo'] = $this->getBaseUrl(true, $page->getPhotoUrl('thumb.profile'));
	  $userPages[$counter]['title'] = $page->getTitle();
	  $counter++;
	}
	return $userPages;
  }
  function postAttributionSesbusiness($subject)
  {
	$viewer = $this->view->viewer();
	$user_id = $viewer->getIdentity();
	$attributionType = Engine_Api::_()->getDbTable('postattributions', 'sesbusiness')->getBusinessPostAttribution(array('business_id' => $subject->getIdentity()));
	$pageAttributionType = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'seb_attribution');
	$allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'auth_defattribut');
	$enablePostAttribution = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'auth_contSwitch');
	if (!$pageAttributionType || $attributionType == 0) {
	  $pageAttribution = "";
	}
	if ($pageAttributionType && !$allowUserChoosePageAttribution || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if ($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1 || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if (!$enablePostAttribution || !$pageAttributionType || !$user_id) { } else {

	  $isAdmin = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness')->isAdmin(array('business_id' => $subject->getIdentity(), 'user_id' => $this->view->viewer()->getIdentity()));
	  if (!$isAdmin) {
		$pageAttribution = $this->view->viewer();
	  }

	  if (!empty($pageAttribution)) {
		return array('guid' => $pageAttribution->getGuid(), 'photo' => $this->getBaseUrl(true, $pageAttribution->getPhotoUrl('thumb.profile')));
	  } else {
		return array('guid' => $viewer->getGuid(), 'photo' => $this->userImage($viewer, "thumb.profile"));
	  }
	}
	return false;
  }
  function friendBirthdayHTML($birthdayuser)
  {
	$replace = array($birthdayuser->getTitle(), '<a href="' . $this->getBaseUrl(true, $birthdayuser->getHref()) . '?module=profile"><img src="' . $this->userImage($birthdayuser->getIdentity(), "thumb.profile") . '" alt="' . ($birthdayuser->getTitle()) . '">' . $birthdayuser->getTitle() . '</a>');
	$token = array('BIRTHDAY_USER_NAME', 'BIRTHDAY_USER_IMAGE');
	$message = "";
	if (Engine_Api::_()->sesapi()->hasCheckMessage($birthdayuser)) {
	  $message = '<a href="' . $this->getBaseUrl(true, "messages/compose/to/" . $birthdayuser->user_id) . '?module=composeMessage" class="smoothbox"><i class="far fa-comments"></i><span>' . $this->view->translate('Send Message') . '</span></a>';
	}
	return array('html' => "<link href=\"" . $this->getBaseUrl(true, 'application/modules/Sesapi/externals/styles/activity_messages.css') . "\" type=\"text/css\" rel=\"stylesheet\">" . '<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_sendwish sesbasic_bxs parent_notification_sesadv">' . str_replace($token, $replace, Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.friendnotificationbirthdaytext', '')) . '</div><div class="sesact_sendwish_btns"><a href="' . $this->getBaseUrl(true, $birthdayuser->getHref()) . '?module=postonwall"><i class="far fa-edit"></i><span>' . $this->view->translate("Post on Wall") . '</span></a>' . $message . '</div>', 'message' => Engine_Api::_()->sesapi()->hasCheckMessage($birthdayuser) ? true : false, 'user_id' => $birthdayuser->user_id, 'user_title' => $birthdayuser->getTitle());
  }
  function viewerBirthdayHTML()
  {
	$viewer = $this->view->viewer();
	$token = array('BIRTHDAY_USER_NAME');
	$replace = array(ucwords($viewer->getTitle()));
	$html = "<link href=\"" . $this->getBaseUrl(true, 'application/modules/Sesapi/externals/styles/activity_messages.css') . "\" type=\"text/css\" rel=\"stylesheet\">" . '<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_bdaywish centerT sesbasic_bxs parent_notification_sesadv" style="padding:0;background-image:none;">' . str_replace($token, $replace, Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationbirthdaytext', '')) . '</div>';
	return array('html' => $html);
  }
  function welcomeMessage($isWelcomeMessage)
  {
	$viewer = $this->view->viewer();
	$token = array('NOTIFICATION_TIME', 'NOTIFICATION_USER', 'NOTIFICATION_IMAGE');
	$replace = array($isWelcomeMessage['message'], ucwords($viewer->getTitle()), $this->getBaseUrl(true, "application/modules/Sesadvancedactivity/externals/images/" . $isWelcomeMessage['image']));
	$html = "<link href=\"" . $this->getBaseUrl(true, 'application/modules/Sesapi/externals/styles/activity_messages.css') . "\" type=\"text/css\" rel=\"stylesheet\">" . '<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_wishbox parent_notification_sesadv" style="padding:0;">' . str_replace($token, $replace, Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationdaytext', '')) . ' </div>';
	return array('html' => $html);
  }
  function intelligentNotification()
  {
	$viewer = $this->view->viewer();
	if (!$viewer->getIdentity())
	  return;
	//user bithday
	if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.friendnotificationbirthday', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.friendnotificationbirthdaytext', '') && $birthdayFriend = Engine_Api::_()->sesadvancedactivity()->loggedinFriendBirthday(array('single' => true), $viewer)) {
	  $birthdayuser = Engine_Api::_()->getItem('user', $birthdayFriend->item_id);
	  $response["birthdayUser"] = $this->friendBirthdayHTML($birthdayuser);
	}
	//viewer birthday
	if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationbirthday', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationbirthdaytext', '')) {
	  $fields = Engine_Api::_()->fields()->getFieldsValuesByAlias($viewer);
	  $isBirthday = Engine_Api::_()->sesadvancedactivity()->getBirthdayViewer($viewer, $fields);
	  if ($isBirthday)
		$response["viewerBirthday"] = $this->viewerBirthdayHTML();
	}
	//welcome message
	if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationday', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationdaytext', '')) {
	  $isWelcomeMessage = Engine_Api::_()->sesadvancedactivity()->getWelcomeMessage($viewer);
	  if ($isWelcomeMessage['status'])
		$response["welcome_message"] = $this->welcomeMessage($isWelcomeMessage);
	}
	//add friends
	if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationfriends', 1)) {
	  $friendCountTotal = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationfriendsdays', 30);
	  if ($friendCountTotal)
		$friendCount = $viewer->membership()->getMemberCount($viewer);
	  if (!$friendCountTotal || $friendCount < $friendCountTotal)
		$response['add_friend'] = $this->addFriendHTML();
	}
	//add date of birth
	if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.dobadd', 1)) {
	  if (empty($fields))
		$fields = Engine_Api::_()->fields()->getFieldsValuesByAlias($viewer);
	  if (empty($fields['birthdate']))
		$response['add_dateofbirth'] = $this->addDobHTML();
	}
	return $response;
  }
  function addDobHTML()
  {
	$html = "<link href=\"" . $this->getBaseUrl(true, 'application/modules/Sesapi/externals/styles/activity_messages.css') . "\" type=\"text/css\" rel=\"stylesheet\">" . '<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_addbday centerT parent_notification_sesadv"><span class="sesact_addbday_title">' . $this->view->translate("Add your birthday to your profile") . '</span><span class="sesact_addbday_des">' . $this->view->translate("Let people know when the big day arrives.") . '</span><span class="sesact_addbday_btn"><a href="' . $this->getBaseUrl(true, 'members/edit/profile?module=edit_profile') . '" class="sesbasic_link_btn">' . $this->view->translate("Go now") . '</a></span></div>';
	return array('html' => $html);
  }
  function addFriendHTML()
  {
	$html = "<link href=\"" . $this->getBaseUrl(true, 'application/modules/Sesapi/externals/styles/activity_messages.css') . "\" type=\"text/css\" rel=\"stylesheet\">" . '<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_find_frind parent_notification_sesadv"><div class="sesact_find_frind_head sesbm">' . $this->view->translate("Add Friends to See More Feeds") . '</div><div class="sesact_find_frind_cont sesbasic_clearfix"><i class="floatL"><img src="' . $this->getBaseUrl(true, 'application/modules/Sesadvancedactivity/externals/images/feed64.png') . '" alt="" /></i><span class="floatR more_btn"><a href="' . $this->getBaseUrl(true, 'members?module=members') . '" class="sesbasic_button">' . $this->view->translate("Find Friends") . '</a></span><span class="des">' . $this->view->translate("You’ll have more feeds in your Activity Feed wall, once you add more friends here.") . '</span></div></div>';
	return array('html' => $html);
  }
  function postAttributionSespage($subject)
  {
	$viewer = $this->view->viewer();
	$user_id = $viewer->getIdentity();
	$attributionType = Engine_Api::_()->getDbTable('postattributions', 'sespage')->getPagePostAttribution(array('page_id' => $subject->getIdentity()));
	$pageAttributionType = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'page_attribution');
	$allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'auth_defattribut');
	$enablePostAttribution = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'auth_contSwitch');
	if (!$pageAttributionType || $attributionType == 0) {
	  $pageAttribution = "";
	}
	if ($pageAttributionType && !$allowUserChoosePageAttribution || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if ($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1 || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if (!$enablePostAttribution || !$pageAttributionType || !$user_id) { } else {

	  $isAdmin = Engine_Api::_()->getDbTable('pageroles', 'sespage')->isAdmin(array('page_id' => $subject->getIdentity(), 'user_id' => $this->view->viewer()->getIdentity()));
	  if (!$isAdmin) {
		$pageAttribution = $this->view->viewer();
	  }

	  if (!empty($pageAttribution)) {
		return array('guid' => $pageAttribution->getGuid(), 'photo' => $this->getBaseUrl(true, $pageAttribution->getPhotoUrl('thumb.profile')));
	  } else {
		return array('guid' => $viewer->getGuid(), 'photo' => $this->userImage($viewer, "thumb.profile"));
	  }
	}
	return false;
  }
  function getPages()
  {
	$user_id = $this->_getParam('user_id', false);
	$table = Engine_Api::_()->getDbTable('pageroles', 'sespage');
	$selelct = $table->select($table->info('name'), 'page_id')->where('user_id =?', $this->view->viewer()->getIdentity());
	$res = $table->fetchAll($selelct);
	$pageIds = array();
	foreach ($res as $page) {
	  $pageIds[] = $page->page_id;
	}
	if (!$user_id)
	  $user_id = $this->view->viewer()->getIdentity();
	$value['user_id'] = $user_id;
	$value['pageIds'] = $pageIds;
	$value['fetchAll'] = true;
	$pages = Engine_Api::_()->getDbTable('pages', 'sespage')->getPageSelect($value);
	$counter = 0;
	$userPages = array();
	$viewer = $this->view->viewer();
	$userPages[$counter]['guid'] = $viewer->getGuid();
	$userPages[$counter]['photo'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
	$userPages[$counter]['title'] = $viewer->getTitle();
	$counter++;
	foreach ($pages as $page) {
	  $userPages[$counter]['guid'] = $page->getGuid();
	  $userPages[$counter]['photo'] = $this->getBaseUrl(true, $page->getPhotoUrl('thumb.profile'));
	  $userPages[$counter]['title'] = $page->getTitle();
	  $counter++;
	}
	return $userPages;
  }
  function sescommunityAds($subject, $contentCount = 0)
  {
	$settings = Engine_Api::_()->getApi('settings', 'core');
	$communityAdsEnable = $settings->getSetting('sescommunityads_advertisement_enable', 1);
	$communityAdsDisplay = $settings->getSetting('sescommunityads_advertisement_display', 3);
	$communityAdsDisplayFeed = $settings->getSetting('sescommunityads_advertisement_displayfeed', 1);
	if (!$subject && !$communityAdsDisplayFeed)
	  return array();
	$communityAdsDisplayAds = $settings->getSetting('sescommunityads_advertisement_displayads', 5);
	$communityads = array();
	if ($contentCount && $contentCount % $communityAdsDisplayAds == 0) {
	  $valueAds['communityAdsDisplay'] = $communityAdsDisplay;
	  $view = Engine_Api::_()->authorization()->isAllowed('sescommunityads', null, 'view');
	  if (!$view)
		return array();
	  $valueAds['fetchAll'] = true;
	  $valueAds['limit'] = 1; //Engine_Api::_()->getApi('settings', 'core')->getSetting('sescommunityads.ads.count', 1);
	  $valueAds["fromActivityFeed"] = true;
	  $select = Engine_Api::_()->getDbTable('sescommunityads', 'sescommunityads')->getAds($valueAds);
	  $paginator =  $select;

	  if (count($paginator) > 0) {
		foreach ($paginator as $ad) {
		  $communityads['ad_id'] = $ad->getIdentity();
		  $communityads['user_id'] = $ad->user_id;
		  $communityads['ad_type'] = $ad->type;
		  if ($ad->user_id != $this->view->viewer()->getIdentity()) {
			$adsItem = Engine_Api::_()->getItem('sescommunityads', $ad->getIdentity());
			$adsItem->views_count++;
			$adsItem->save();

			$campaign = Engine_Api::_()->getItem('sescommunityads_campaign', $adsItem->campaign_id);
			$campaign->views_count++;
			$campaign->save();

			//insert in view table
			Engine_Api::_()->getDbTable('viewstats', 'sescommunityads')->insertrow($adsItem, $this->view->viewer());
			//insert campaign stats
			Engine_Api::_()->getDbTable('campaignstats', 'sescommunityads')->insertrow($adsItem, $this->view->viewer(), 'view');
		  }
		  if ($ad->type == "promote_content_cnt" || $ad->type == "promote_website_cnt") {
			//header data
			$image = Engine_Api::_()->getItem('storage_file', $ad->website_image);
			$imageSrc = "";
			if ($image)
			  $imageSrc = $image->map();
			$communityads['url'] = $this->getBaseUrl(false, $ad->getHref(array('subject' => true)));
			if ($ad->type != "promote_website_cnt" || $imageSrc) {
			  $communityads['header_image'] = $this->getBaseUrl(false, !empty($ad) && $ad->resources_type ? $ad->description : ($imageSrc ? $imageSrc : "application/modules/Sescommunityads/externals/images/transprant-bg.png"));
			}
			$communityads['title'] = $ad->title;

			$dot = "";
			if ($ad->sponsored) {
			  $communityads['sponsored']  =  $this->view->translate('Sponsored');
			}
			if ($ad->featured && !$ad->sponsored) {
			  $communityads['sponsored'] = $this->view->translate('Featured');
			}
			if ($ad->user_id != $this->view->viewer()->getIdentity()) {
			  $menuOptionsCounter = 0;
			  $menuOption[$menuOptionsCounter]['label'] = $menuOption[$menuOptionsCounter]['value'] = $this->view->translate('hide ad');
			  $menuOption[$menuOptionsCounter]['name'] = $this->view->translate('hide_ad');
			  $menuOptionsCounter = 1;
			  $useful = $ad->isUseful();
			  $menuOption[$menuOptionsCounter]['label'] = $menuOption[$menuOptionsCounter]['value'] = !$useful ? $this->view->translate('This ad is useful') : $this->view->translate('Remove from useful');
			  $menuOption[$menuOptionsCounter]['is_useful'] = $useful ? 1 : 0;
			  $menuOption[$menuOptionsCounter]['name'] = $this->view->translate('ad_useful');
			  $communityads['menus'] = $menuOption;
			}
			$communityads['hidden_data'] = array(
			  'heading' => $this->view->translate('Ad hidden'),
			  'description' => $this->view->translate('You Won\'t See this ad and ads like this.') . ' ' . $this->view->translate('Why did you hide it?'),
			  'options' => array(
				'Offensive' => $this->view->translate('Offensive'),
				'Misleading' => $this->view->translate('Misleading'),
				'Inappropriate' => $this->view->translate('Inappropriate'),
				'Licensed Material' => $this->view->translate('Licensed Material'),
				'Other' => $this->view->translate('Other'),
			  ),
			  'other_text' => $this->view->translate('Specify your reason here..'),
			  'submit_button_text' => $this->view->translate('Report'),
			  'success_text' => $this->view->translate('Thanks for your feedback. Your report has been submitted.')
			);

			//get attachments
			$table = Engine_Api::_()->getDbTable('attachments', 'sescommunityads');
			$select = $table->select()->where('sescommunityad_id =?', $ad->getIdentity());
			$attachment = $table->fetchAll($select);

			if ($ad->subtype == "image") {
			  if (count($attachment)) {
				$attach = $attachment[0];
				$image = Engine_Api::_()->getItem('storage_file', $attach->file_id);
				$imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				if ($image)
				  $imageSrc = $image->map();
				$communityads['ad_type'] = 'image';
				$communityads['attachment']['href'] = $this->getBaseUrl(false, $attach->getHref());;
				$communityads['attachment']['src'] = $this->getBaseUrl(false, $imageSrc);
				if ($ad->type == "promote_website_cnt") {
				  $description = $ad->description;
				  $description = str_replace('http://', '', $description);
				  $description = str_replace('https://', '', $description);
				  $description = explode('/', $description);
				  $communityads['attachment']['url_description'] = $description[0];
				}
				$communityads['attachment']['title'] = $attach->title;
				$communityads['attachment']['description'] = $attach->description;
				if ($ad->calltoaction) {
				  $communityads['attachment']['calltoaction']['href'] = $this->getBaseUrl(false, $attach->getHref());
				  $communityads['attachment']['calltoaction']['label'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->calltoaction ? $ad->calltoaction : "")));;
				}
			  }
			} else if ($ad->subtype == "video") {
			  if (count($attachment)) {
				$attach = $attachment[0];
				$image = Engine_Api::_()->getItem('storage_file', $attach->file_id);
				$imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				if ($image)
				  $imageSrc = $image->map();

				$video = Engine_Api::_()->getItem('storage_file', $ad->video_src);
				$videosrc = "";
				if ($videosrc)
				  $videosrc = $video->map();
				$communityads['attachment']['image_src'] = $this->getBaseUrl(false, $videosrc);

				$communityads['ad_type'] = 'video';
				$communityads['attachment']['href'] = $this->getBaseUrl(false, $attach->getHref());
				$communityads['attachment']['src'] = $this->getBaseUrl(false, $imageSrc);
				if ($ad->type == "promote_website_cnt") {
				  $description = $ad->description;
				  $description = str_replace('http://', '', $description);
				  $description = str_replace('https://', '', $description);
				  $description = explode('/', $description);
				  $communityads['attachment']['url_description'] = $description[0];
				}
				$communityads['attachment']['title'] = $attach->title;
				$communityads['attachment']['description'] = $attach->description;
				if ($ad->calltoaction) {
				  $communityads['attachment']['calltoaction']['href'] = $this->getBaseUrl(false, $attach->getHref());
				  $communityads['attachment']['calltoaction']['label'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->calltoaction ? $ad->calltoaction : "")));;
				}
			  }
			} else {
			  if (count($attachment)) {
				$counter = 0;
				$communityads['ad_type'] = "carousel";
				foreach ($attachment as $attach) {
				  $image = Engine_Api::_()->getItem('storage_file', $attach->file_id);
				  $imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				  if ($image)
					$imageSrc = $image->map();
				  $communityads['carousel_attachment'][$counter]['href'] = $this->getBaseUrl(false, $attach->getHref());
				  $communityads['carousel_attachment'][$counter]['src'] = $this->getBaseUrl(false, $imageSrc);
				  $communityads['carousel_attachment'][$counter]['title'] = $attach->title;
				  $communityads['carousel_attachment'][$counter]['description'] = $attach->description;
				  if ($ad->calltoaction) {
					$communityads['carousel_attachment'][$counter]['calltoaction']['href'] = $this->getBaseUrl(false, $attach->getHref());
					$communityads['carousel_attachment'][$counter]['calltoaction']['label'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->calltoaction ? $ad->calltoaction : "")));;
				  }
				  if ($ad->call_to_action_overlay) {
					$communityads['carousel_attachment'][$counter]['call_to_action_overlay'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->call_to_action_overlay ? $ad->call_to_action_overlay : "")));
				  }
				  $counter++;
				}
				if ($ad->more_image) {
				  $image = Engine_Api::_()->getItem('storage_file', $ad->more_image);
				  $imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				  if ($image)
					$imageSrc = $image->map();
				  $communityads['seemore']['href'] = $this->getBaseUrl(false, $ad->getHref());
				  $communityads['seemore']['src'] = $this->getBaseUrl(false, $imageSrc);
				  $communityads['seemore']['title'] = $this->view->translate('See more at');
				  $communityads['seemore']['description'] = $ad->see_more_display_link;
				}
			  }
			}
		  } else {
			$action = Engine_Api::_()->getItem('sesadvancedactivity_action', $ad->resources_id);
			if (!$action)
			  return array();
			$allowDelete = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete');
			$viewer = $this->view->viewer();
			if ($viewer->getIdentity()) {
			  $activity_moderate =  Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
			} else {
			  $activity_moderate = 0;
			}
			$activityTypeTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
			// Pre-process
			if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity'))
			  $feeling = true;
			else
			  $feeling = false;
			try {
			  $counter = 0;
			  $activity = array();
			  $length = 1;
			  $sescommunityads = true;
			  $fromActivityFeed = $_SESSION['fromActivityFeed'] = true;
			  include('activity.php');
			  $communityads = $activity[0];
			  $communityads['ad_id'] = $ad->getIdentity();
			  $communityads['hidden_data'] = array(
				'heading' => $this->view->translate('Ad hidden'),
				'description' => $this->view->translate('You Won\'t See this ad and ads like this.') . ' ' . $this->view->translate('Why did you hide it?'),
				'options' => array(
				  'Offensive' => $this->view->translate('Offensive'),
				  'Misleading' => $this->view->translate('Misleading'),
				  'Inappropriate' => $this->view->translate('Inappropriate'),
				  'Licensed Material' => $this->view->translate('Licensed Material'),
				  'Other' => $this->view->translate('Other'),
				),
				'other_text' => $this->view->translate('Specify your reason here..'),
				'submit_button_text' => $this->view->translate('Report'),
				'success_text' => $this->view->translate('Thanks for your feedback. Your report has been submitted.')
			  );
			} catch (Exception $e) { //throw $e;
			  $_SESSION['fromActivityFeed'] = false;
			  return array();
			}
			$_SESSION['fromActivityFeed'] = false;
		  }
		}
	  }
	}
	return $communityads;
  }
  function canShowAddsAndPeopleYoumayKnow($contentCount = 0)
  {
	$ads = array();
	$pymk = array();
	$settings = Engine_Api::_()->getApi('settings', 'core');
	$adsEnable = $settings->getSetting('sesadvancedactivity.adsenable', 0);
	$adsRepeat = $settings->getSetting('sesadvancedactivity.adsrepeatenable', 0);
	$adsRepeatTime = $settings->getSetting('sesadvancedactivity.adsrepeattimes', 15);
	//show campaign ads

	if ($adsEnable && ($contentCount && $contentCount % $adsRepeatTime == 0) && ($adsRepeat || (!$adsRepeat && $contentCount / $adsRepeatTime == 1))) {
	  $ads =  $this->addSEAds();
	}
	//PYMY
	$peopleymkEnable = $settings->getSetting('sesadvancedactivity.peopleymk', 1);
	$peopleymkrepeattimes = $settings->getSetting('sesadvancedactivity.peopleymkrepeattimes', 5);
	$pymkrepeatenable = $settings->getSetting('sesadvancedactivity.pymkrepeatenable', 0);

	if (Engine_Api::_()->sesapi()->isModuleEnable('sespymk') && $peopleymkEnable && ($contentCount && $contentCount % $peopleymkrepeattimes == 0) && ($pymkrepeatenable || (!$pymkrepeatenable && $contentCount / $peopleymkrepeattimes == 1))) {
	  $pymk = $this->pymk();
	}

	return array('pymk' => $pymk, 'ads' => $ads);
  }
  function pymk()
  {

	$viewer = Engine_Api::_()->user()->getViewer();
	if (!$viewer->getIdentity())
	  return array();
	$userIDS = $viewer->membership()->getMembershipsOfIds();
	$userMembershipTable = Engine_Api::_()->getDbtable('membership', 'user');
	$userMembershipTableName = $userMembershipTable->info('name');
	$select_membership = $userMembershipTable->select()
	  ->where('resource_id = ?', $viewer->getIdentity());
	$member_results = $userMembershipTable->fetchAll($select_membership);
	foreach ($member_results as $member_result) {
	  $membershipIDS[] = $member_result->user_id;
	}

	$userTable = Engine_Api::_()->getDbtable('users', 'user');
	$userTableName = $userTable->info('name');
	$select = $userTable->select()
	  ->where('user_id <> ?', $viewer->getIdentity());
	$select->where('photo_id <> ?', 0);
	if ($membershipIDS) {
	  $select->where('user_id NOT IN (?)', $membershipIDS);
	}

	$select->order('rand()');

	$peopleyoumayknow = Zend_Paginator::factory($select);
	$peopleyoumayknow->setItemCountPerPage(15);
	$peopleyoumayknow->setCurrentPageNumber(1);
	if ($peopleyoumayknow->getTotalItemCount() == 0)
	  return array();
	if ($peopleyoumayknow->getTotalItemCount() < 4)
	  return array();
	$counterLoop = 0;
	$users = array();
	if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember'))
	  $memberEnable = true;
	foreach ($peopleyoumayknow as $member) {
	  if (!empty($memberEnable)) {
		//mutual friends
		$mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($member, $viewer);
		if (!$member->isSelf($viewer)) {
		  $users[$counterLoop]['mutualFriends'] = $mfriend == 1 ? $mfriend . $this->view->translate(" mutual friend") : $mfriend . $this->view->translate(" mutual friends");
		}
	  }
	  $users[$counterLoop]['user_id'] = $member->getIdentity();
	  $users[$counterLoop]['title'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $member->getTitle());
	  $users[$counterLoop]['user_image'] = $this->userImage($member->getIdentity(), "thumb.profile");
    if($this->friendRequest($member)) {
      $users[$counterLoop]['membership'] = $this->friendRequest($member);
    }
	  $counterLoop++;
	}
	$result["users"] = $users;
	$result["sellall"] = $peopleyoumayknow->getTotalItemCount() > 15 ? true : false;
	return $result;
  }
  function friendRequest($subject)
  {

	$viewer = Engine_Api::_()->user()->getViewer();

	// Not logged in
	if (!$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false)) {
	  return 0;
	}

	// No blocked
	if ($viewer->isBlockedBy($subject)) {
	  return 0;
	}

	// Check if friendship is allowed in the network
	$eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
	if (!$eligible) {
	  return 0;
	}

	// check admin level setting if you can befriend people in your network
	else if ($eligible == 1) {

	  $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
	  $networkMembershipName = $networkMembershipTable->info('name');

	  $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
	  $select
		->from($networkMembershipName, 'user_id')
		->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
		->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
		->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity());

	  $data = $select->query()->fetch();

	  if (empty($data)) {
		return 0;
	  }
	}

	// One-way mode
	$direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
	if (!$direction) {
	  $viewerRow = $viewer->membership()->getRow($subject);
	  $subjectRow = $subject->membership()->getRow($viewer);
	  $params = array();

	  // Viewer?
	  if (null === $subjectRow) {
		// Follow
		return array(
		  'label' => $this->view->translate('Follow'),
		  'action' => 'add',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',
		);
	  } else if ($subjectRow->resource_approved == 0) {
		// Cancel follow request
		return array(
		  'label' => $this->view->translate('Cancel Request'),
		  'action' => 'cancel',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',
		);
	  } else {
		// Unfollow
		return array(
		  'label' => $this->view->translate('Unfollow'),
		  'action' => 'remove',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',
		);
	  }
	  // Subject?
	  if (null === $viewerRow) {
		// Do nothing
	  } else if ($viewerRow->resource_approved == 0) {
		// Approve follow request
		return array(
		  'label' => $this->view->translate('Approve Request'),
		  'action' => 'confirm',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',

		);
	  } else {
		// Remove as follower?
		return array(
		  'label' => $this->view->translate('Unfollow'),
		  'action' => 'remove',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',

		);
	  }
	  if (count($params) == 1) {
		return $params[0];
	  } else if (count($params) == 0) {
		return 0;
	  } else {
		return $params;
	  }
	}

	// Two-way mode
	else {

	  $table =  Engine_Api::_()->getDbTable('membership', 'user');
	  $select = $table->select()
		->where('resource_id = ?', $viewer->getIdentity())
		->where('user_id = ?', $subject->getIdentity());
	  $select = $select->limit(1);
	  $row = $table->fetchRow($select);

	  if (null === $row) {
		// Add
		return array(
		  'label' => $this->view->translate('Add Friend'),
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',
		  'action' => 'add',
		);
	  } else if ($row->user_approved == 0) {
		// Cancel request
		return array(
		  'label' => $this->view->translate('Cancel Friend'),
		  'action' => 'cancel',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',

		);
	  } else if ($row->resource_approved == 0) {
		// Approve request
		return array(
		  'label' => $this->view->translate('Approve Request'),
		  'action' => 'confirm',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',

		);
	  } else {
		// Remove friend
		return array(
		  'label' => $this->view->translate('Remove Friend'),
		  'action' => 'remove',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',

		);
	  }
	}
  }
  function addSEAds()
  {
	// Get campaign
	if (
	  !($id = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.adcampaignid', '0')) ||
	  !($campaign = Engine_Api::_()->getItem('core_adcampaign', $id))
	) {
	  return array();
	}

	// Check limits, start, and expire
	if (!$campaign->isActive()) {
	  return array();
	}

	// Get viewer
	$viewer = Engine_Api::_()->user()->getViewer();
	if (!$campaign->isAllowedToView($viewer)) {
	  return array();
	}

	// Get ad
	$table = Engine_Api::_()->getDbtable('ads', 'core');
	$select = $table->select()->where('ad_campaign = ?', $id)->order('RAND()');
	$ad =  $table->fetchRow($select);
	if (!($ad)) {
	  return array();
	}
	// Okay
	$campaign->views++;
	$campaign->save();

	$ad->views++;
	$ad->save();
	return array('campaign_id' => $campaign->getIdentity(), 'ad_id' => $ad->getIdentity(), 'ad_content' => $ad->html_code, 'content_type' => 'ads');
  }
  function getMentionTags($content)
  {
	$contentMention = $content;
	$mentions = array();
	preg_match_all('/(^|\s)(@\w+)/', $contentMention, $result);
	$counter = 0;
	foreach ($result[2] as $value) {
	  $user_id = str_replace('@_user_', '', $value);
	  if (intval($user_id) > 0) {
		$user = Engine_Api::_()->getItem('user', $user_id);
		if (!$user)
		  continue;
	  } else {
		$itemArray = explode('_', $user_id);
		$resource_id = $itemArray[count($itemArray) - 1];
		unset($itemArray[count($itemArray) - 1]);
		$resource_type = implode('_', $itemArray);
		try {
		  $user = Engine_Api::_()->getItem($resource_type, $resource_id);
		} catch (Exception $e) {
		  continue;
		}
		if (!$user || !$user->getIdentity())
		  continue;
	  }
	  $mentions[$counter]['word'] = $value;
	  $mentions[$counter]['title'] = $user->getTitle();
	  $mentions[$counter]['module'] = 'user';
	  $mentions[$counter]['href'] = $this->getBaseUrl(false, $user->getHref());
	  $mentions[$counter]['user_id'] = $user->getIdentity();
	  $counter++;
	}
	return $mentions;
  }
  function gethashtags($content)
  {
	$hashTagWords = array();
	preg_match_all("/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u", $content, $matches);
	$searchword = $replaceWord = array();
	foreach ($matches[0] as $value) {
	  $hashTagWords[] = $value;
	}
	return $hashTagWords;
  }

  function loadOnlyVideo($contentResponse)
  {
    $selectedVideos = array();
    foreach ($contentResponse as $key => $value) 
      if($value['object_type'] == "video")
        $selectedVideos[] = $value;
      return $selectedVideos;
  }
  
}
