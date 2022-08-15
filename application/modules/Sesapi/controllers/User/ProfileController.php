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
class User_ProfileController extends Sesapi_Controller_Action_Standard
{
  protected $_friends_count = 0;
  public function init()
  {
    // @todo this may not work with some of the content stuff in here, double-check
    $subject = null;
    if( !Engine_Api::_()->core()->hasSubject() )
    {
      $id = $this->_getParam('id');

      // use viewer ID if not specified
      //if( is_null($id) )
      //  $id = Engine_Api::_()->user()->getViewer()->getIdentity();

      if( null !== $id )
      {
        $subject = Engine_Api::_()->user()->getUser($id);
        if( $subject->getIdentity() )
        {
          Engine_Api::_()->core()->setSubject($subject);
        }
      }
    }
    $this->_helper->requireSubject('user');
    $this->_helper->requireAuth()->setNoForward()->setAuthParams(
      $subject,
      Engine_Api::_()->user()->getViewer(),
      'view'
    );
  }
  public function indexAction()
  {
    $subject = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->core_general_profile;
    if( !$require_check && !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error'));
    }
    if(!$subject->authorization()->isAllowed(null, 'view'))
			  Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    // Check enabled
    if( !$subject->enabled && !$viewer->isAdmin() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error'));
    }

    // Check block
    if( $viewer->isBlockedBy($subject) && !$viewer->isAdmin() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error'));
    }

    $isSelf = true;
    if(!$subject->isSelf($viewer)){
      $isSelf = false;
    }

    // Increment view count
    if(!$isSelf){
      $subject->view_count++;
      $subject->save();
    }
    $result = array();
    // user is valid to view profile
    $result["is_self"] = $isSelf;

    //option upload photo
    if($isSelf){
      $profilePhotoOptions[] = array('label'=>$this->view->translate('Upload Photo'),'name'=>'upload_photo');
      $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");;
      if($isAlbumEnable)
       $profilePhotoOptions[] = array('label'=>$this->view->translate('Choose From Albums'),'name'=>'choose_from_albums');
      if($subject->photo_id){
        // $profilePhotoOptions[] = array('label'=>$this->view->translate('View Profile Photo'),'name'=>'view_profile_photo');
        $profilePhotoOptions[] = array('label'=>$this->view->translate('Remove Profile Photo'),'name'=>'remove_profile_photo');
      }
      $result['profile_image_options'] = $profilePhotoOptions;
    }
    $profile_tabbed_menus = array();
    $profileCounter = 0;
    if($isSelf){
      $profile_tabbed_menus[$profileCounter]['name'] = "post";
      $profile_tabbed_menus[$profileCounter]['label'] = $this->view->translate("Post");
      $profileCounter++;
      $profile_tabbed_menus[$profileCounter]['name'] = "edit_post";
      $profile_tabbed_menus[$profileCounter]['label'] = $this->view->translate("Edit Profile");
      $profileCounter++;
      $profile_tabbed_menus[$profileCounter]['name'] = "privacy_settings";
      $profile_tabbed_menus[$profileCounter]['label'] = $this->view->translate("Privacy");
      $profileCounter++;
      $profile_tabbed_menus[$profileCounter]['name'] = "notification";
      $profile_tabbed_menus[$profileCounter]['label'] = $this->view->translate("Notifications");
      $profileCounter++;
      $result['profile_tabbed_menus'] = $profile_tabbed_menus;
    }else{
      $result['profile_tabbed_menus'] = $this->gutterMenuAction();
    }
    //gutter menu
    $result['gutterMenu'] = $this->gutterMenuAction(false);
    $result['profile_tabs'] = $this->profiletabs();
    $isCoverPhoto = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesusercoverphoto");
    if($isCoverPhoto){
      $defaultCoverPhoto = Engine_Api::_()->authorization()->getPermission($subject, 'sesusercoverphoto', 'defaultcoverphoto');
      if($defaultCoverPhoto)
        $defaultCoverPhoto = $this->getBaseUrl(false,$defaultCoverPhoto);
      else
        $defaultCoverPhoto = $this->getBaseUrl().'application/modules/Sesusercoverphoto/externals/images/default_cover.jpg';

      if(isset($subject->coverphoto) && $subject->coverphoto != 0 && $subject->coverphoto != ''){
  			 $memberCover =	Engine_Api::_()->storage()->get($subject->coverphoto, '');
         if($memberCover)
         	$memberCover = $this->getBaseUrl(false,$memberCover->map());
     }else
        $memberCover = $defaultCoverPhoto;
    } else {
        if (!empty($subject->coverphoto)) {
          $memberCover = $this->getBaseUrl() . Engine_Api::_()->getItem('storage_file', $subject->coverphoto)->storage_path;
        }
    }
    if($memberCover)
      $result['cover_photo'] = $memberCover;

       //option upload cover photo
      if($subject->isSelf($viewer)){
        $coverPhotoOptions[] = array('label'=>$this->view->translate('Upload Cover Photo'),'name'=>'upload_cover');
        $isAlbumEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum") ||  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("album");
        if($isAlbumEnable)
        //  $coverPhotoOptions[] = array('label'=>$this->view->translate('Choose From Albums'),'name'=>'choose_from_albums');
         if(!empty($subject->coverphoto)){
          // $coverPhotoOptions[] = array('label'=>$this->view->translate('View Cover Photo'),'name'=>'view_cover_photo');
          $coverPhotoOptions[] = array('label'=>$this->view->translate('Remove Cover Photo'),'name'=>'remove_cover_photo');
         }
        $result['cover_image_options'] = $coverPhotoOptions;
      }

    
    $result["profile"] = $subject->toArray();
    unset($result['profile']['lastlogin_ip']);
    unset($result['profile']['creation_ip']);

    $result['profile']['user_photo'] = $this->userImage($subject,'thumb.profile');


    $profileFields = $this->getProfileFieldValueAndPrivacy($subject);

    $profileFieldsResults = array();
    $customFieldCounter = 0;

    if(isset($subject->location) && !empty($subject->location)){
      $location = Engine_Api::_()->getDbTable('locations','sesbasic')->getLocationData('user',$subject->getIdentity());
      if($location){
        $profileFieldsResults[$customFieldCounter]['key'] =  'location';
        $profileFieldsResults[$customFieldCounter]['value'] = $subject->location;
        $profileFieldsResults[$customFieldCounter]['lat'] = $location->lat;
        $profileFieldsResults[$customFieldCounter]['lng'] = $location->lng;
        $profileFieldsResults[$customFieldCounter]['label'] = $this->view->translate('Location');
        $customFieldCounter++;
      }
    }

    $sesmember = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesmember");
    if($sesmember && !$subject->isSelf($viewer)){
      $result['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($subject);
      $result['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($subject);
      $result['is_content_follow'] = Engine_Api::_()->sesapi()->contentFollow($subject,'follows','sesmember');
      $result['content_follow_count'] = (int) Engine_Api::_()->sesapi()->getContentFollowCount($subject,'follows','sesmember');
      $mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($subject, $viewer);
      if($mfriend){
        $profileFieldsResults[$customFieldCounter]['key'] =  'mutual';
        $profileFieldsResults[$customFieldCounter]['value'] = $mfriend == 1 ? $mfriend.$this->view->translate(" mutual friend") : $mfriend.$this->view->translate(" mutual friends");
        $profileFieldsResults[$customFieldCounter]['label'] = $this->view->translate($mfriend == 1 ? $mfriend.$this->view->translate("mutual friend") : $mfriend.$this->view->translate("mutual friends"));
        $customFieldCounter++;
      }
    }

    // Calculate viewer-subject relationship
    $viewer = $this->view->viewer();
    $usePrivacy = ($subject instanceof User_Model_User);
    if( $usePrivacy ) {
      $relationship = 'everyone';
      if( $viewer && $viewer->getIdentity() ) {
        if( $viewer->getIdentity() == $subject->getIdentity() ) {
          $relationship = 'self';
        } elseif( $viewer->membership()->isMember($subject, true) ) {
          $relationship = 'friends';
        } else {
          $relationship = 'registered';
        }
      }
    }
    // Get first value object for reference
    $firstValue = $privacy;
     $db = Engine_Db_Table::getDefaultAdapter();
    foreach($profileFields as $fields){
      $privacy = $fields['privacy'];
      $type = $fields['type'];
      $value = $fields['value'];
      $label = $fields['label'];
      //Evaluate privacy
      if( $usePrivacy && !empty($privacy) && $relationship != 'self' ) {
        if( $privacy == 'self' && $relationship != 'self' ) {
          continue;
        } elseif( $privacy == 'friends' && ($relationship != 'friends' && $relationship != 'self') ) {
          continue;
        } elseif( $privacy == 'registered' && $relationship == 'everyone' ) {
          continue;
        }
      }

      if($type == "gender"){
          $genderRes = $db->query("SELECT label FROM engine4_user_fields_options WHERE field_id = ".$fields['field_id']." AND option_id = ".$value."")->fetchAll();
          if(count($genderRes)){
            $value = $genderRes[0]['label'];
          }else
            continue;
      }else if($type == "birthdate"){
        $value = date('F d, Y',strtotime($value));
      }else if($type == "partner_gender"){
        $value = ucfirst($value);
      }else if($type == "relationship_status"){
        if($value == "single"){
          $value = $this->view->translate("Single");
        }else if($value == "relationship"){
          $value = $this->view->translate("In a Relationship");
        }else if($value == "engaged"){
          $value = $this->view->translate("Engaged");
        }else if($value == "married"){
          $value = $this->view->translate("Married");
        }else if($value == "complicated"){
          $value = $this->view->translate("It's Complicated");
        }else if($value == "open"){
          $value = $this->view->translate("In an Open Relationship");
        }else if($value == "widow"){
          $value = $this->view->translate("Widowed");
        }
      }else if($type == "income"){
        if($value == "0"){
          $value =   $this->view->translate("Less than $25,000");
        }else if($value == "25_35"){
          $value =   $this->view->translate("$25,001 to $35,000");
        }else if($value == "35_50"){
          $value =   $this->view->translate("$35,001 to $50,000");
        }else if($value == "50_75"){
          $value =   $this->view->translate("$50,001 to $75,000");
        }else if($value == "75_100"){
          $value =   $this->view->translate("$75,001 to $100,000");
        }else if($value == "100_150"){
          $value =   $this->view->translate("$100,001 to $150,000");
        }else if($value == "1"){
          $value =   $this->view->translate("$150,001+");
        }
      }else if($type == "education_level"){
         if($value == "high_school"){
           $value = $this->view->translate("High School");
         }else if($value == "some_college"){
           $value = $this->view->translate("Some College");
         }else if($value == "associates"){
           $value = $this->view->translate("Associates Degree");
         }else if($value == "bachelors"){
           $value = $this->view->translate("Bachelors Degree");
         }else if($value == "graduate"){
           $value = $this->view->translate("Graduate Degree");
         }else if($value == "phd"){
           $value = $this->view->translate("PhD / Post Doctoral");
         }
      }else if($type == "country"){
          $locale = Zend_Registry::get('Zend_Translate')->getLocale();
          $territories = Zend_Locale::getTranslationList('territory', $locale, 2);
          if(!empty($territories[$value])){
              $value = $territories[$value];
          }else
            continue;
      }

        $profileFieldsResults[$customFieldCounter]['key'] =  $type;
        $profileFieldsResults[$customFieldCounter]['value'] = $value;
        $profileFieldsResults[$customFieldCounter]['label'] = $label;
        $customFieldCounter++;
    }

    if(count($profileFieldsResults) > 0)
      $result['profile_info'] = $profileFieldsResults;

    //featured photos
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum') && $sesmember){
      $user_photo = array();
      $fPhotos = Engine_Api::_()->getDbTable('members', 'sesmember')->getFeaturedPhotos($subject->getIdentity());
      if($fPhotos){
         $counterPhoto = 0;
         foreach($fPhotos as $photo){
            $photo = Engine_Api::_()->getItem('album_photo',$photo['photo_id']);
            if($photo){
              $user_photo[$counterPhoto] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id,'','',true);
              $user_photo[$counterPhoto]['photo_id'] = $photo['photo_id'];
              $counterPhoto++;
            }
         }
         if($counterPhoto>0)
          $result['user_photo'] = $user_photo;
      }
    }

    //profile_friends
    if($isSelf){
      $friendsFriends = $this->getProfileFriends($subject);
      if(count($friendsFriends)){
        $result['profile']["total_friend_count"] = $this->_friends_count;
        $result['profile_friends'] = $friendsFriends;
      }
    }else{
      //mutual_friends
      $mutualFriends = $this->getMutualFriends($subject);
      if(count($mutualFriends)){
        $result['profile']["total_friend_count"] = $this->_friends_count;
        $result['mutual_friends'] = $mutualFriends;
      }
    }
    $result['profile']['displayname'] = $result['profile']['displayname'];//preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$result['profile']['displayname']);
    //echo "<pre>";var_dump($result);die;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
  }
  protected function getProfileFriends($subject){
    $select = $subject->membership()->getMembersOfSelect();
    $paginator = Zend_Paginator::factory($select);
    // Set item count per page and current page number
    $paginator->setItemCountPerPage(6);
    $paginator->setCurrentPageNumber(1);
     $this->_friends_count = $paginator->getTotalItemCount();
     // Now get all common friends
    $uids = array();

    foreach ($paginator as $data) {
      $uids[] = $data['resource_id'];
    }
    // Do not render if nothing to show
    if (count($uids) <= 0) {
      return array();
    }
    // Get paginator
    $usersTable = Engine_Api::_()->getItemTable('user');
    $select = $usersTable->select()->from($usersTable->info('name'))->where('user_id IN(?)', $uids);
    $item = Zend_Paginator::factory($select);
    $item->setItemCountPerPage(6);
    $item->setCurrentPageNumber(1);
    return $this->getFriendsArray($item);

  }
  protected function getMutualFriends($subject){
    $user_id = $subject->getIdentity();
    $viewer = Engine_Api::_()->user()->getViewer();
    $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
    $friendsName = $friendsTable->info('name');
    $col1 = 'resource_id';
    $col2 = 'user_id';
    $select = new Zend_Db_Select($friendsTable->getAdapter());
    $select
            ->from($friendsName, $col1)
            ->join($friendsName, "`{$friendsName}`.`{$col1}`=`{$friendsName}_2`.{$col1}", null)
            ->where("`{$friendsName}`.{$col2} = ?", $viewer->getIdentity())
            ->where("`{$friendsName}_2`.{$col2} = ?", $user_id)
            ->where("`{$friendsName}`.active = ?", 1)
            ->where("`{$friendsName}_2`.active = ?", 1)
    ;
    // Now get all common friends
    $uids = array();
    $item = Zend_Paginator::factory($select);
    // Set item count per page and current page number
    $item->setItemCountPerPage(6);
    $item->setCurrentPageNumber(1);
     $this->_friends_count = $item->getTotalItemCount();
    foreach ($item as $data) {
      $uids[] = $data[$col1];
    }
    // Do not render if nothing to show
    if (count($uids) <= 0) {
      return array();
    }
    // Get paginator
    $usersTable = Engine_Api::_()->getItemTable('user');
    $select = $usersTable->select()->from($usersTable->info('name'))->where('user_id IN(?)', $uids);
    $item = Zend_Paginator::factory($select);
    $item->setItemCountPerPage(6);
    $item->setCurrentPageNumber(1);
    return $this->getFriendsArray($item);
  }
  protected function getFriendsArray($result){
    $response = array();
    $counter = 0;
    foreach($result as $member){
      $response[$counter]['title'] = $member->getTitle();//preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $member->getTitle());
      $response[$counter]['user_id'] = $member->getIdentity();
      $response[$counter]['user_image'] = $this->userImage($member->getIdentity(),"thumb.profile");
      $counter++;
    }
    return $response;
  }
  protected function getProfileFieldValueAndPrivacy($subject){
     $db = Engine_Db_Table::getDefaultAdapter();
     return $db->query("SELECT type,privacy,value,label,engine4_user_fields_meta.field_id FROM engine4_user_fields_meta LEFT JOIN engine4_user_fields_values ON engine4_user_fields_values.field_id = engine4_user_fields_meta.field_id WHERE (item_id = ".$subject->getIdentity()." && value != '' && value IS NOT NULL) && (type = 'gender' || type = 'birthdate' || type = 'website' || type = 'twitter' || type = 'facebook' || type = 'partner_gender' || type = 'city' || type = 'country' || type = 'relationship_status' || type = 'education_level' || type = 'income')")->fetchAll();

  }

  protected function profiletabs(){
      $subject = Engine_Api::_()->core()->getSubject('user');
      $tabs = array();
      $tabs[] = array(
          'label' => $this->view->translate('Updates'),
          'name' => 'updates'
      );
      $content = $this->getInfoContent();
      if($content){
          $tabs[] = array(
              'label' => $this->view->translate('Info'),
              'name' => 'info'
          );
      }
      $select = $subject->membership()->getMembersOfSelect();
      $friends = Zend_Paginator::factory($select)->getTotalItemCount();
      if($friends > 0){
          $tabs[] = array(
              'label' => $friends == 1 ? $this->view->translate("Friends") : $this->view->translate("Friends"),
              'name' => 'profile_friend',
              'totalCount' => $friends
          );
      }

      //Album and Sesalbum
      $sesalbumEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesalbum");
      if($sesalbumEnabled){
          $paginator = (Engine_Api::_()->getItemTable('album')->profileAlbums(array('userId'=>$subject->getIdentity())));
          $total = $paginator->getTotalItemCount();
          $tabs[] = array(
              'label' => $total == 1 ? $this->view->translate("Album") : $this->view->translate("Albums"),
              'name' => 'album',
              'totalCount' => $total
          );
      }


      //Blog and Sesblog
      $sesblogEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesblog");
      if($sesblogEnabled){
          $paginator = (Engine_Api::_()->getItemTable('sesblog_blog')->getSesblogsPaginator(array('user'=>$subject)));
          $total = $paginator->getTotalItemCount();
          $tabs[] = array(
              'label' => $total == 1 ? $this->view->translate("Blog") : $this->view->translate("Blogs"),
              'name' => 'blog',
              'totalCount' => $total
          );
      }

      //Music and Sesmusic
      $sesmusicEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesmusic");
      if($sesmusicEnabled){
          $table = Engine_Api::_()->getItemTable('sesmusic_album');
          $tableName = $table->info('name');
          $select = $table->select()
              ->from($tableName)
              ->where($tableName . '.search = ?', true)
              ->where($tableName . '.owner_id = ?', $subject->getIdentity());
          $paginator = Zend_Paginator::factory($select);
          $total = $paginator->getTotalItemCount();
          $tabs[] = array(
              'label' => $total == 1 ? $this->view->translate("Music") : $this->view->translate("Musics"),
              'name' => 'music',
              'totalCount' => $total
          );
      }
      // Sesvideo
      $sesvideoEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesvideo");
      if($sesvideoEnabled){
          $paginator = Engine_Api::_()->getDbTable('videos', 'sesvideo')->getVideo(array('user_id' => $subject->getIdentity()));
          $total = $paginator->getTotalItemCount();
          $tabs[] = array(
              'label' => $total == 1 ? $this->view->translate("Video") : $this->view->translate("Videos"),
              'name' => 'video',
              'totalCount' => $total
          );
      }
      if(_SESAPI_VERSION_IOS >= 2.9 || _SESAPI_VERSION_IOS >= 1.9) {
          if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active', '1')) {
              $paginator = Engine_Api::_()->getDbTable('members', 'sesmember')->followers(array('user_id' => $subject->getIdentity(), 'paginator' => true));
              $total = $paginator->getTotalItemCount();
              $tabs[] = array(
                  'label' => $total == 1 ? $this->view->translate("Follower") : $this->view->translate("Followers"),
                  'name' => 'sesmember_followers',
                  'totalCount' => $total
              );
          }
          if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active', '1')) {
              $paginator = Engine_Api::_()->getDbTable('members', 'sesmember')->following(array('user_id' => $subject->getIdentity(), 'paginator' => true));
              $total = $paginator->getTotalItemCount();
              $tabs[] = array(
                  'label' => $total == 1 ? $this->view->translate("Following") : $this->view->translate("Following"),
                  'name' => 'sesmember_following',
                  'totalCount' => $total
              );
          }

          $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
          if ($viewer_id == $subject->getIdentity()) {
              $paginator = Engine_Api::_()->getDbTable('userviews', 'sesmember')->whoViewedMe(array('resources_id' => $viewer_id, 'paginator' => true, 'view_by_me' => true));
              $total = $paginator->getTotalItemCount();
              $tabs[] = array(
                  'label' => $total == 1 ? $this->view->translate("Recently Viewed By Me") : $this->view->translate("Recently Viewed By Me"),
                  'name' => 'sesmember_recently_viewed_by_me',
                  'totalCount' => $total
              );
          }

          if ($viewer_id == $subject->getIdentity()) {
              $paginator = Engine_Api::_()->getDbTable('userviews', 'sesmember')->whoViewedMe(array('resources_id' => $viewer_id, 'paginator' => true));
              $total = $paginator->getTotalItemCount();
              $tabs[] = array(
                  'label' => $total == 1 ? $this->view->translate("Recently Viewed Me") : $this->view->translate("Recently Viewed Me"),
                  'name' => 'sesmember_recently_viewed_me',
                  'totalCount' => $total
              );
          }

          $seseventEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sesevent");
          if ($seseventEnabled) {
              $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventPaginator(array('user_id' => $subject->getIdentity()));
              $total = $paginator->getTotalItemCount();
              $tabs[] = array(
                  'label' => $total == 1 ? $this->view->translate("Event") : $this->view->translate("Events"),
                  'name' => 'sesevent',
                  'totalCount' => $total
              );
          }
          if (_SESAPI_VERSION_IOS > 33) {
              $sescontestEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled("sescontest");
              if ($sescontestEnabled) {
                  $paginator = Engine_Api::_()->getDbTable('contests', 'sescontest')->getContestPaginator(array('user_id' => $subject->getIdentity()));
                  $total = $paginator->getTotalItemCount();
                  $tabs[] = array(
                      'label' => $total == 1 ? $this->view->translate("Contest") : $this->view->translate("Contests"),
                      'name' => 'sescontest',
                      'totalCount' => $total
                  );
              }
          }
      }
      return $tabs;
  }

  public function addlocationAction() {
    
    $user = Engine_Api::_()->getItem('user', $this->_getParam('id', null));
    $this->view->form = $form = new Sesmember_Form_Location();
    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'user'));
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }
    $userInfoItem = Engine_Api::_()->sesmember()->getUserInfoItem($user->getIdentity());
    if ($this->getRequest()->getPost()) {
      $userInfoItem->location = $_POST['ses_location'];
      $userInfoItem->save();
      if (isset($_POST['ses_lat']) && isset($_POST['ses_lng']) && $_POST['ses_lat'] != '' && $_POST['ses_lng'] != '' && !empty($_POST['ses_location'])) {
        Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng ,city,state,zip,country, resource_type) VALUES ("' . $user->user_id . '", "' . $_POST['ses_lat'] . '","' . $_POST['ses_lng'] . '","' . $_POST['ses_city'] . '","' . $_POST['ses_state'] . '","' . $_POST['ses_zip'] . '","' . $_POST['ses_country'] . '",  "user")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['ses_lat'] . '" , lng = "' . $_POST['ses_lng'] . '",city = "' . $_POST['ses_city'] . '", state = "' . $_POST['ses_state'] . '", country = "' . $_POST['ses_country'] . '", zip = "' . $_POST['ses_zip'] . '"');
      }
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('id' => $user->getIdentity(),'message' => $this->view->translate('Location has been added successfully.'))));
  }
  
  public function gutterMenuAction($data = true){

    $fromOut = $this->_getParam('out',false);
    $messageSuccess = $this->_getParam('message','');
    $viewer = $this->view->viewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if(!($viewer->getIdentity()))
      return;

    $menu = array();
    if($viewer->getIdentity() == $subject->getIdentity()){
      //show edit
      if($fromOut)
        $menu[] = array('label'=>$this->view->translate('Edit Profile'),'name'=>'edit_profile');
        $menu[] = array('label'=>$this->view->translate('Edit Profile Photo'),'name'=>'edit_profile_photo');
        
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.enable.location', 1))  {
        $getLoggedInuserLocation = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData($subject->getType(), $subject->getIdentity());
          if(!empty($getLoggedInuserLocation)) {
            $menu[] = array(
              'label' => $this->view->translate('Edit Location'),
              'name' => 'edit_profile_location',
              'params' => array(
                'module' => 'user',
                'controller' => 'index',
                'action' => 'editlocation',
              ),
            );
          } else {
            $menu[] = array(
              'label' => $this->view->translate('Add Location'),
              'name' => 'edit_profile_location',
              'params' => array(
                'module' => 'user',
                'controller' => 'profile',
                'action' => 'addlocation',
              ),
            );
          }
        }
    }else if($data){
      //show friendship
      $menu[] = Engine_Api::_()->sesapi()->friendship($subject);
      $message = Engine_Api::_()->sesapi()->hasCheckMessage($subject);
    if($message){
      $menu[]  = array(
          'label' => $this->view->translate('Send Message'),
          'name' => 'send_message',
          'params' => array(
             'to' => $subject->getIdentity(),
           ),
        );
    }
    if( $viewer->isAllowed('user', 'block') ) {
        if( !$subject->isBlockedBy($viewer) ) {
          $menu[]= array(
            'label' => $this->view->translate('Block Member'),
            'name' => 'block_member',
            'params' => array(
              'user_id' => $subject->getIdentity()
            ),
          );
        } else {
          $menu[] = array(
            'label' => $this->view->translate('Unblock Member'),
            'name' => 'remove_block_member',
            'params' => array(
              'user_id' => $subject->getIdentity()
            ),
          );
        }
      }
      //follow
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')){
        $followActive = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active',1);
        if($followActive){
          $unfollowText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.unfollowtext','Unfollow'));
          $followText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.followtext','Follow'));
          //follow
          if($followActive && $viewer->getIdentity() && $viewer->getIdentity() != $subject->getIdentity()){
            $FollowUser = Engine_Api::_()->sesmember()->getFollowStatus($subject->user_id);
            if(!$FollowUser){
                $menu[] = array(
                            'name'=>'follow',
                            'label' => $followText,
                            'params' => array(
                              'user_id' => $subject->getIdentity()
                            ),
                          );
            }else{
                $menu[] = array(
                            'name'=>'unfollow',
                            'label' => $unfollowText,
                            'params' => array(
                              'user_id' => $subject->getIdentity()
                            ),
                          );
            }
          }
        }
      }
    }

    if( !$viewer->isSelf($subject) && $this->view->viewer()->getIdentity() > 0){
      $menu[]  = array(
        'label' => $this->view->translate('Report'),
        'name' => 'report',
        'params' => array(
           'subject' => $subject->getGuid(),
         ),
      );
    }


    if(!$fromOut)
      return $menu;
    $menuGutter['gutterMenu'] = $menu;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $menuGutter));
  }
  function userInfoAction(){
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('user');
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
       return false;
    }
    $content = $this->getInfoContent(true);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $content));
  }
  function getInfoContent($recreate = false){
    // Load fields view helpers
    $subject = Engine_Api::_()->core()->getSubject('user');
    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Sesapi/View/Helper', 'Sesapi_View_Helper');

    // Values
    $this->view->fieldStructure = $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($subject);
    if( count($fieldStructure) <= 1 ) { // @todo figure out right logic
      return array();
    }
    $content = $this->view->fieldSesapiValueLoop($subject, $fieldStructure);;
    $counter = 0;
    $array = array();
    if($recreate){
       foreach($content as $key=>$value){
          $array['info'][$counter]['name'] = $key;
          $array['info'][$counter]['value'] = $value;
          $counter++;
       }
    }else
      $array = $content;
    return $array;
  }
}
