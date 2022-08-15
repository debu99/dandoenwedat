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
class Sesprayer_IndexController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    // only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('sesprayer_prayer', null, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
  }
  public function browseAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->form = $form = new Sesprayer_Form_Search();
    $form->removeElement('draft');
    if( !$viewer->getIdentity() ) {
      $form->removeElement('show');
    }
    $defaultValues = $form->getValues();
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    } else {
      $values = $defaultValues;
    }
    $values['draft'] = "0";
    $values['visible'] = "1";
    $values = array_merge($values, $_GET);
    if(isset($_POST['tag_id']))
      $values['tag'] = $_POST['tag_id'];
    // Do the show thingy
    if( @$values['show'] == 2 ) {
      // Get an array of friend ids
      $table = Engine_Api::_()->getItemTable('user');
      $select = $viewer->membership()->getMembersSelect('user_id');
      $friends = $table->fetchAll($select);
      // Get stuff
      $ids = array();
      foreach( $friends as $friend )
      {
        $ids[] = $friend->user_id;
      }
      $values['users'] = $ids;
    }
    if(@$params) {
      $this->view->allParams = $values = @$params;
    } else {
      $this->view->allParams = $values = array_merge($this->_getAllParams(), $values);
    }
    $manage = $this->_getParam('manage',false);
    if($manage) {
      $values['user_id'] = $viewer->getIdentity();
    } else {
        $values['actionname'] = 'browseprayer';
    }

    if(isset($values['search_networks']) && !empty($values['search_networks'])) {
      $networksTable = Engine_Api::_()->getDbtable('membership', 'network');
      $select = $networksTable->select()->from($networksTable->info('name'), array('user_id'))->where('resource_id = ?', $values['search_networks']);
      $users = $networksTable->fetchAll($select);
      $usersIDSNetworks = array();
      foreach($users as $user) {
        if($viewer_id == $user->user_id) continue;
        $usersIDSNetworks[] = $user->user_id;
      }
      $values['userNetworksSearch'] = $usersIDSNetworks;
    }

    //When Search by lists
    if(isset($values['search_lists']) && !empty($values['search_lists'])) {
      $listitemsTable = Engine_Api::_()->getItemTable('user_list_item');
      $select = $listitemsTable->select()->from($listitemsTable->info('name'), array('child_id'))->where('list_id = ?', $values['search_lists'])->group('child_id');
      $users = $listitemsTable->fetchAll($select);
      $usersIDS = array();
      foreach($users as $user) {
        $usersIDS[] = $user->child_id;
      }
      $values['userlistsSearch'] = $usersIDS;
    }

    $paginator = Engine_Api::_()->getItemTable('sesprayer_prayer')->getPrayersPaginator($values);
    $paginator->setItemCountPerPage($this->view->allParams['limit']);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $count = $paginator->getTotalItemCount();
    $result = $this->getPrayers($paginator,$manage);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;

    if($this->_getParam('page', 1) == 1 && !$manage){
      $categories = $this->categoryAction(true);
      $result['prayerCategories'] = $categories;
    }

    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Nobody has written a prayer entry with that criteria.', 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  public function categoryAction($getCategory = false){
    $params['hasPrayer'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'sesprayer')->getCategory($params);
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $category){
      $catgeoryArray[$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray[$counter]["label"] = $category->category_name;
      if($category->thumbnail != '' && !is_null($category->thumbnail) && intval($category->thumbnail)):
        $catgeoryArray[$counter]["thumbnail"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl(''));
      endif;
      if($category->cat_icon != '' && !is_null($category->cat_icon) && intval($category->cat_icon)):
        $catgeoryArray[$counter]["cat_icon"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->cat_icon)->getPhotoUrl('thumb.icon'));
      endif;
      $catgeoryArray[$counter]["count"] = $this->view->translate(array('%s prayer', '%s prayers', $category->total_prayer_categories), $this->view->locale()->toNumber($category->total_prayer_categories));
      $counter++;
    }
    if($getCategory)
      return $catgeoryArray;
    $res["category"] = $catgeoryArray;
    if($catgeoryArray <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No Category exists.', 'result' => array()));
    else
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $res),array()));
  }
  public function getPrayers($paginator,$manage = false){
    $result = array();
    $counter = 0;
    foreach($paginator as $prayers){
        $prayer = $prayers->toArray();
        if($this->view->viewer()->getIdentity() != 0){
          try{
          $prayer['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($prayers);
          $prayer['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($prayers);
          }catch(Exception $e){}
        }

        // Get tags
        $counterTags = 0;
        foreach( $prayers->tags()->getTagMaps() as $tagMap ) {
          $tag = $tagMap->getTag();
          if( !isset($tag->text) ) continue;
          $prayer['tags'][$counterTags]['title'] = '#'.$tag->text;
          $prayer['tags'][$counterTags]['tag_id']  = $tagMap->tag_id;
          $counterTags++;
        }
        if($prayers->category_id){
          $category = Engine_Api::_()->getItem('sesprayer_category',$prayers->category_id);
          if($category)
            $prayer['category_title'] = $category->category_name;
        }
        $prayer['user_title'] = $prayers->getOwner()->getTitle();
        $prayer['user_image_url'] = $this->userImage($prayers->getOwner()->getIdentity(),"thumb.profile");
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($prayers,'',"");
        if(count($images))
        $prayer['images'] = $images;
        if($manage){
          $menuoptions= array();
          $canEdit = $this->_helper->requireAuth()->setAuthParams($prayers, null, 'edit')->isValid();
          $counterMenu = 0;
          if($canEdit){
            $menuoptions[$counterMenu]['name'] = "edit";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
            $counterMenu++;
          }
          $canDelete = $this->_helper->requireAuth()->setAuthParams($prayers, null, 'delete')->isValid();
          if($canDelete){
            $menuoptions[$counterMenu]['name'] = "delete";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
          }
          $prayer['menus'] = $menuoptions;
        }

        $result['prayers'][$counter] = array_merge($prayer,array());
        $counter++;
    }
    return $result;
  }
 public function searchFormAction(){
   $viewer = Engine_Api::_()->user()->getViewer();
	  $searchForm = new Sesprayer_Form_Search();
     if( !$viewer->getIdentity() ) {
      $searchForm->removeElement('show');
    }
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($searchForm,true);
    $this->generateFormFields($formFields);
  }
  public function getIframelyInformationAction($return = false) {
    $url = trim(strip_tags($this->_getParam('video')));
    $information = $this->handleIframelyInformation($url);
    $valid = !empty($information['code']);
    $message = "";
    if(!$valid){
      $message  = $this->view->translate("Invalid video URL");
    }
    if($return)
      return $valid;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>$valid,'error_message'=>$message,'result'=>!$valid ? $message : ""));
  }
  public function viewAction()
  {
    // Check permission
    $viewer = Engine_Api::_()->user()->getViewer();
    $prayer = Engine_Api::_()->getItem('sesprayer_prayer', $this->_getParam('prayer_id'));
    if( $prayer ) {
      Engine_Api::_()->core()->setSubject($prayer);
    }
    $prayerResult = array();
    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $canView = $this->_helper->requireAuth()->setAuthParams('sesprayer_prayer', null, 'view')->checkRequire();
    if(empty($canView))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $prayerResult['prayer'] = $prayer->toArray();
    $prayerResult['prayer']['code'] = str_replace("//cdn",'http://cdn',$prayerResult['prayer']['code']);

    preg_match('/src="([^"]+)"/', $prayerResult['prayer']['code'], $match);
    if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
      $iframeUrl = str_replace('//','https://',$match[1]);
    }else{
      $iframeUrl = $match[1];
    }
    $prayerResult['prayer']['iframeUrl'] = $iframeUrl;
    // Prepare data
    $prayerTable = Engine_Api::_()->getDbtable('prayers', 'sesprayer');
    $owner = $owner = $prayer->getOwner();
    $viewer = $viewer;
    $viewer_id = $viewer->getIdentity();
    // Do other stuff
    $mine = true;
    $canEdit = $this->_helper->requireAuth()->setAuthParams($prayer, null, 'edit')->checkRequire();
    if( !$prayer->getOwner()->isSelf(Engine_Api::_()->user()->getViewer()) ) {
      $prayer->getTable()->update(array(
        'view_count' => new Zend_Db_Expr('view_count + 1'),
      ), array(
        'prayer_id = ?' => $prayer->getIdentity(),
      ));
      $mine = false;
    }
    if ($viewer->getIdentity() != 0 && isset($prayer->prayer_id)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesprayer_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $prayer->prayer_id . '", "sesprayer_prayer","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }
    $prayerResult['prayer']['user_title'] = $owner->getTitle();
    if($this->view->viewer()->getIdentity() != 0){
      try{
      $prayerResult['prayer']['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($prayer);
      $prayerResult['prayer']['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($prayer);
      }catch(Exception $e){}
    }
    if($prayer->category_id){
      $category = Engine_Api::_()->getItem('sesprayer_category',$prayer->category_id);
      if($category)
        $prayerResult['prayer']['category_title'] = $category->category_name;
    }
    $images = Engine_Api::_()->sesapi()->getPhotoUrls($prayer,'',"");
    $prayerResult['prayer']['images'] = $images;

    $photo = $this->getBaseUrl(false,$prayer->getPhotoUrl());
    if($photo)
      $prayerResult['prayer']["share"]["imageUrl"] = $photo;
		$prayerResult['prayer']["share"]["url"] = $this->getBaseUrl(false,$prayer->getHref());
    $prayerResult['prayer']["share"]["title"] = $prayer->source;
    $prayerResult['prayer']["share"]["description"] = strip_tags($prayer->getTitle());
    $prayerResult['prayer']["share"]['urlParams'] = array(
        "type" => $prayer->getType(),
        "id" => $prayer->getIdentity()
    );
    if(is_null($prayerResult['prayer']["share"]["title"]))
      unset($prayerResult['prayer']["share"]["title"]);
    $viewer = Engine_Api::_()->user()->getViewer();
    $menuoptions= array();
    $canEdit = $this->_helper->requireAuth()->setAuthParams($prayer, null, 'edit')->isValid();
    $counterMenu = 0;
    if($canEdit){
      $menuoptions[$counterMenu]['name'] = "edit";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
      $counterMenu++;
    }
    $canDelete = $this->_helper->requireAuth()->setAuthParams($prayer, null, 'delete')->isValid();
    if($canDelete){
      $menuoptions[$counterMenu]['name'] = "delete";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
    }
    if($viewer->getIdentity() != 0 &&  !$prayer->getOwner()->isSelf($viewer) ){
        $menuoptions[$counterMenu]['name'] = "report";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Prayer");
    }
    $prayerResult['menus'] = $menuoptions;
    // Get tags
    $counterTags = 0;
    foreach( $prayer->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      $prayerResult['tags'][$counterTags]['title'] = '#'.$tag->text;
      $prayerResult['tags'][$counterTags]['tag_id']  = $tagMap->tag_id;
      $counterTags++;
    }
    $prayerResult['prayer']['user_image_url'] = $this->userImage($prayer->getOwner()->getIdentity(),"thumb.profile");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$prayerResult));
  }

  public function createAction()
  {
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    if( !$this->_helper->requireAuth()->setAuthParams('sesprayer_prayer', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
      // Prepare form
     $form = new Sesprayer_Form_Create();
     $mediaType = !empty($_POST['mediatype']) ? $_POST['mediatype'] : "";
     $form->removeElement('cancel');
     $form->removeElement('token');
     $form->removeElement('cancel');
     if($this->_getParam('getForm')) {
       if($form->posttype){
         $form->posttype->setValue('');
         $form->posttype->setLabel($this->view->translate('Posting Type'));
       }
       if($form->user_id)
         $form->user_id->setLabel($this->view->translate('Friends'));
       if($form->lists)
         $form->lists->setDescription('');
       if($form->posttype)
         $form->posttype->setValue('1');
         $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
         $this->generateFormFields($formFields);
     }
     if( !$form->isValid($_POST) ) {
       $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
       if($mediaType == 2){
          $valid = $this->getIframelyInformationAction(true);
          if(!$valid){
            $count = count($validateFields) - 1;
            $video[$count]["type"] = "";
            $video[$count]["name"] = "video";
            $video[$count]["label"] = "Paste web address of the video";
            $video[$count]["errorMessage"] = $this->view->translate("Invalid Video Url");
            $video[$count]["isRequired"] = false;
            $video[$count]["value"] = "";
            $validateFields = array_merge($video,$validateFields);
          }
       }
       if(count($validateFields))
         $this->validateFormFields($validateFields);
     }else{
        if($mediaType == 2){
            $valid = $this->getIframelyInformationAction(true);
            if(!$valid){
              $video[0]["type"] = "";
              $video[0]["name"] = "video";
              $video[0]["label"] = "Paste web address of the video";
              $video[0]["errorMessage"] = $this->view->translate("Invalid Video Url");
              $video[0]["isRequired"] = false;
              $video[0]["value"] = "";
              $this->validateFormFields($video);
            }
        }
     }
      // Process
      $receiverTable = Engine_Api::_()->getDbTable('receivers', 'sesprayer');
      $table = Engine_Api::_()->getItemTable('sesprayer_prayer');
      $db = $table->getAdapter();
      $db->beginTransaction();
      $values = $_POST; //$form->getValues();
      try {
        // Create blog
        $viewer = Engine_Api::_()->user()->getViewer();
        $formValues = $_POST;
        if( empty($values['auth_view']) ) {
          $formValues['auth_view'] = 'everyone';
        }
        if( empty($values['auth_comment']) ) {
          $formValues['auth_comment'] = 'everyone';
        }
        $values = array_merge($formValues, array(
          'owner_type' => $viewer->getType(),
          'owner_id' => $viewer->getIdentity(),
        ));
        $prayer = $table->createRow();
        if($values['video']) {
          $information = $this->handleIframelyInformation($values['video']);
          $values['code'] = $information['code'];
          try{
            $prayer->setPhoto($information['thumbnail']);
          }catch(Exception $e){
            //silence
          }
        }
        if($_POST['posttype'] == 1) {
        $networkValues = array();
        foreach (Engine_Api::_()->getDbtable('networks', 'network')->fetchAll() as $network) {
          $networkValues[] = $network->network_id;
        }
        if (@$values['networks'])
          $values['networks'] = json_encode($values['networks']);
        else
          $values['networks'] = json_encode($networkValues);
        }
        if($_POST['posttype'] == 2) {
        if (@$values['lists'])
          $values['lists'] = json_encode($values['lists']);
        else
          $values['lists'] = '';
        }

        $prayer->setFromArray($values);
        $prayer->save();
        if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) )
          $prayer->setPhoto($_FILES['image']);
        // Auth
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $viewMax = array_search($values['auth_view'], $roles);
        $commentMax = array_search($values['auth_comment'], $roles);
        foreach( $roles as $i => $role ) {
          $auth->setAllowed($prayer, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($prayer, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $prayer->tags()->addTagMaps($viewer, $tags);
        // Add activity only if blog is published
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $prayer, 'sesprayer_new');
        // make sure action exists before attaching the blog to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $prayer);
        }
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          foreach($tags as $tag) {
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
          }
        }
        $prayer->action_id = $action->getIdentity();
        $prayer->save();

        if($prayer->posttype == 2) {
          $networksArray = Zend_Json::decode($values['networks']);
          $networksTable = Engine_Api::_()->getDbtable('membership', 'network');
          $select = $networksTable->select()->from($networksTable->info('name'), array('user_id'))->where('resource_id IN (?)', $networksArray)->group('user_id');
          foreach($networksArray as $networks){
            $privacy .= 'network_list_'.$networks.',';
          }
          $users = $networksTable->fetchAll($select);
          foreach($users as $user) {
            $user = Engine_Api::_()->getItem('user', $user->user_id);
            if($user->getIdentity() != $prayer->owner_id) {
                //Receiver Table Entry
                $receiverRow = $receiverTable->createRow();
                $receiverRow->prayer_id = $prayer->getIdentity();
                $receiverRow->sender_id = $prayer->owner_id;
                $receiverRow->resource_id = $user->getIdentity();
                $receiverRow->save();

                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $prayer, 'sesprayer_sendprayer');
            }
          }
        } else if($prayer->posttype == 3) {
          $listsArray = Zend_Json::decode($values['lists']);
          foreach($listsArray as $list){
            $privacy .= 'members_list_'.$list.',';
          }
          $listitemsTable = Engine_Api::_()->getItemTable('user_list_item');
          $select = $listitemsTable->select()->from($listitemsTable->info('name'), array('child_id'))->where('list_id IN (?)', $listsArray)->group('child_id');
          $users = $listitemsTable->fetchAll($select);
          foreach($users as $user) {
            $user = Engine_Api::_()->getItem('user', $user->child_id);
            if($user->getIdentity() != $prayer->owner_id) {
                //Receiver Table Entry
                $receiverRow = $receiverTable->createRow();
                $receiverRow->prayer_id = $prayer->getIdentity();
                $receiverRow->sender_id = $prayer->owner_id;
                $receiverRow->resource_id = $user->getIdentity();
                $receiverRow->save();

                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $prayer, 'sesprayer_sendprayer');
            }

          }
        } else if($prayer->posttype == 4) {
            $user = Engine_Api::_()->getItem('user', $values['user_id']);

            //Receiver Table Entry
            $receiverRow = $receiverTable->createRow();
            $receiverRow->prayer_id = $prayer->getIdentity();
            $receiverRow->sender_id = $prayer->owner_id;
            $receiverRow->resource_id = $user->getIdentity();
            $receiverRow->save();

            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $prayer, 'sesprayer_sendprayer');
            $privacy .= 'friends_list_'.$values['user_id'];
        } else {
            $privacy = 'everyone';
        }

        // make sure action exists before attaching the blog to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $prayer);
        }

        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags && $action) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          foreach($tags as $tag) {
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
          }
          $prayer->action_id = $action->getIdentity();
          $prayer->save();
        }

        //Case of everyone
        if($prayer->posttype == 0) {
          $prayer->lists = '';
          $prayer->networks = '[]';
          $prayer->save();
        }

        // Commit
        $db->commit();
       $result["message"] = $this->view->translate("Prayer created successfully.");
       $result['id'] = $prayer->getIdentity();
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
      } catch( Exception $e ) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
  }
  // HELPER FUNCTIONS
  public function handleIframelyInformation($uri) {
    $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesprayer_iframely_disallow');
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

  public function editAction()
  {

    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->prayer_id = $this->_getParam('prayer_id');
    $prayer = Engine_Api::_()->getItem('sesprayer_prayer', $this->_getParam('prayer_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($prayer, $viewer, 'edit')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
      // Prepare form
       $form = new Sesprayer_Form_Edit();
      $category_id = $prayer->category_id;
      $subcat_id = $prayer->subcat_id;
      $subsubcat_id = $prayer->subsubcat_id;
      // Populate form
      $form->populate($prayer->toArray());
      $tagStr = '';
      foreach( $prayer->tags()->getTagMaps() as $tagMap ) {
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
          if( $auth->isAllowed($prayer, $role, 'view') ) {
           $form->auth_view->setValue($role);
          }
        }
        if ($form->auth_comment){
          if( $auth->isAllowed($prayer, $role, 'comment') ) {
            $form->auth_comment->setValue($role);
          }
        }
      }
        $form->removeElement('cancel');
        $form->removeElement('token');
      if($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
        //set subcategory
        $newFormFieldsArray = array();
        if(count($formFields) && $prayer->category_id){
          foreach($formFields as $fields){
            foreach($fields as $field){
                $subcat = array();
                if($fields['name'] == "subcat_id"){
                  $subcat = Engine_Api::_()->getItemTable('sesprayer_category')->getModuleSubcategory(array('category_id'=>$prayer->category_id,'column_name'=>'*'));
                }else if($fields['name'] == "subsubcat_id"){
                  if($prayer->subcat_id)
                  $subcat = Engine_Api::_()->getItemTable('sesprayer_category')->getModuleSubSubcategory(array('category_id'=>$prayer->subcat_id,'column_name'=>'*'));
                }
                  if(count($subcat)){
                    $arrayCat = array();
                    foreach($subcat as $cat){
                      $arrayCat[$cat->getIdentity()] = $cat->category_name;
                    }
                    $fields["multiOptions"] = $arrayCat;
                  }
            }
            $newFormFieldsArray[] = $fields;
          }
          if(!count($newFormFieldsArray))
            $newFormFieldsArray = $formFields;
          $this->generateFormFields($newFormFieldsArray);
        }
        $this->generateFormFields($formFields);
      }
      // Check if valid
      if( !$form->isValid($_POST) ) {
        $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
        if(count($validateFields))
          $this->validateFormFields($validateFields);
      }
      // Process
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $values = $_POST; //$form->getValues();
        if( empty($values['auth_view']) ) {
          $values['auth_view'] = 'everyone';
        }
        if( empty($values['auth_comment']) ) {
          $values['auth_comment'] = 'everyone';
        }
        $prayer->setFromArray($values);
        $prayer->modified_date = date('Y-m-d H:i:s');
        $prayer->save();
        // Add photo
        if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) )
          $prayer->setPhoto($_FILES['image']);
        // handle tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $prayer->tags()->setTagMaps($viewer, $tags);
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags && $prayer->action_id) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query("DELETE FROM engine4_sesadvancedactivity_hashtags WHERE action_id = '".$prayer->action_id."'");
          foreach($tags as $tag) {
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$prayer->action_id.'", "'.$tag.'")');
          }
        }
        $db->commit();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Prayer edited successfully.")));
      }
      catch( Exception $e ) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
      }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
  }
  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $prayer = Engine_Api::_()->getItem('sesprayer_prayer', $this->getRequest()->getParam('prayer_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($prayer, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $this->view->form = $form = new Sesprayer_Form_Delete();
    if( !$prayer ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Prayer entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }
    $db = $prayer->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query("DELETE FROM engine4_sesadvancedactivity_hashtags WHERE action_id = '".$prayer->action_id."'");
      }
      $prayer->delete();
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->translate('Prayer has been deleted.')));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your prayer entry has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->message));
  }
}
