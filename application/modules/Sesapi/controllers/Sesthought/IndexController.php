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
class Sesthought_IndexController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    // only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('sesthought_thought', null, 'view')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
  }  
  public function browseAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->form = $form = new Sesthought_Form_Search();
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
    if($manage)
      $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getItemTable('sesthought_thought')->getThoughtsPaginator($values);
    $paginator->setItemCountPerPage($this->view->allParams['limit']);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $count = $paginator->getTotalItemCount();
    $result = $this->getThoughts($paginator,$manage);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    
    if($this->_getParam('page', 1) == 1 && !$manage){
      $categories = $this->categoryAction(true);
      $result['thoughtCategories'] = $categories;
    }
    
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Nobody has written a thought entry with that criteria.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));  
  }
  public function categoryAction($getCategory = false){
    $params['hasThought'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'sesthought')->getCategory($params);
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
      $catgeoryArray[$counter]["count"] = $this->view->translate(array('%s thought', '%s thoughts', $category->total_thought_categories), $this->view->locale()->toNumber($category->total_thought_categories));
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
  public function getThoughts($paginator,$manage = false){
    $result = array();
    $counter = 0;
    foreach($paginator as $thoughts){ 
        $thought = $thoughts->toArray();
        if($this->view->viewer()->getIdentity() != 0){
          try{
          $thought['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($thoughts);
          $thought['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($thoughts);
          }catch(Exception $e){}
        }     
        
        // Get tags
        $counterTags = 0;
        foreach( $thoughts->tags()->getTagMaps() as $tagMap ) {
          $tag = $tagMap->getTag();
          if( !isset($tag->text) ) continue;
          $thought['tags'][$counterTags]['title'] = '#'.$tag->text;
          $thought['tags'][$counterTags]['tag_id']  = $tagMap->tag_id;
          $counterTags++;
        }
        if($thoughts->category_id){
          $category = Engine_Api::_()->getItem('sesthought_category',$thoughts->category_id);  
          if($category)
            $thought['category_title'] = $category->category_name;
        }
        $thought['user_title'] = $thoughts->getOwner()->getTitle();
        $thought['user_image_url'] = $this->userImage($thoughts->getOwner()->getIdentity(),"thumb.profile");
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($thoughts,'',"");
        if(count($images))
        $thought['images'] = $images;        
        if($manage){
          $menuoptions= array();
          $canEdit = $this->_helper->requireAuth()->setAuthParams($thoughts, null, 'edit')->isValid();
          $counterMenu = 0;
          if($canEdit){
            $menuoptions[$counterMenu]['name'] = "edit";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
            $counterMenu++;
          }
          $canDelete = $this->_helper->requireAuth()->setAuthParams($thoughts, null, 'delete')->isValid();
          if($canDelete){
            $menuoptions[$counterMenu]['name'] = "delete";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
          }
          $thought['menus'] = $menuoptions;
        }
        
        $result['thoughts'][$counter] = array_merge($thought,array());
        $counter++;
    }
    return $result;
  }
 public function searchFormAction(){
   $viewer = Engine_Api::_()->user()->getViewer();
	  $searchForm = new Sesthought_Form_Search();
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
    $thought = Engine_Api::_()->getItem('sesthought_thought', $this->_getParam('thought_id'));
    if( $thought ) {
      Engine_Api::_()->core()->setSubject($thought);
    }
    $thoughtResult = array();
    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $canView = $this->_helper->requireAuth()->setAuthParams('sesthought_thought', null, 'view')->checkRequire();
    if(empty($canView)) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $thoughtResult['thought'] = $thought->toArray();
    $thoughtResult['thought']['code'] = str_replace("//cdn",'http://cdn',$thoughtResult['thought']['code']);
     
    preg_match('/src="([^"]+)"/', $thoughtResult['thought']['code'], $match);
    if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
      $iframeUrl = str_replace('//','https://',$match[1]);
    }else{
      $iframeUrl = $match[1];
    }
    $thoughtResult['thought']['iframeUrl'] = $iframeUrl;
    // Prepare data
    $thoughtTable = Engine_Api::_()->getDbtable('thoughts', 'sesthought');
    $owner = $owner = $thought->getOwner();
    $viewer = $viewer;
    $viewer_id = $viewer->getIdentity();
    // Do other stuff
    $mine = true;
    $canEdit = $this->_helper->requireAuth()->setAuthParams($thought, null, 'edit')->checkRequire();
    if( !$thought->getOwner()->isSelf(Engine_Api::_()->user()->getViewer()) ) {
      $thought->getTable()->update(array(
        'view_count' => new Zend_Db_Expr('view_count + 1'),
      ), array(
        'thought_id = ?' => $thought->getIdentity(),
      ));
      $mine = false;
    }
    if ($viewer->getIdentity() != 0 && isset($thought->thought_id)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesthought_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $thought->thought_id . '", "sesthought_thought","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }
    $thoughtResult['thought']['user_title'] = $owner->getTitle();
    if($this->view->viewer()->getIdentity() != 0){
      try{
      $thoughtResult['thought']['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($thought);
      $thoughtResult['thought']['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($thought);
      }catch(Exception $e){}
    }     
    if($thought->category_id){
      $category = Engine_Api::_()->getItem('sesthought_category',$thought->category_id);  
      if($category)
        $thoughtResult['thought']['category_title'] = $category->category_name;
    }
    $images = Engine_Api::_()->sesapi()->getPhotoUrls($thought,'',"");
    $thoughtResult['thought']['images'] = $images;    
    
    $photo = $this->getBaseUrl(false,$thought->getPhotoUrl());
    if($photo)
      $thoughtResult['thought']["share"]["imageUrl"] = $photo;
		$thoughtResult['thought']["share"]["url"] = $this->getBaseUrl(false,$thought->getHref());
    $thoughtResult['thought']["share"]["title"] = $thought->source;
    $thoughtResult['thought']["share"]["description"] = strip_tags($thought->getTitle());
    $thoughtResult['thought']["share"]['urlParams'] = array(
        "type" => $thought->getType(),
        "id" => $thought->getIdentity()
    );
    if(is_null($thoughtResult['thought']["share"]["title"]))
      unset($thoughtResult['thought']["share"]["title"]);
    $viewer = Engine_Api::_()->user()->getViewer();
    $menuoptions= array();
    $canEdit = $this->_helper->requireAuth()->setAuthParams($thought, null, 'edit')->isValid();
    $counterMenu = 0;
    if($canEdit){
      $menuoptions[$counterMenu]['name'] = "edit";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
      $counterMenu++;
    }
    $canDelete = $this->_helper->requireAuth()->setAuthParams($thought, null, 'delete')->isValid();
    if($canDelete){
      $menuoptions[$counterMenu]['name'] = "delete";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
    }
    if($viewer->getIdentity() != 0 &&  !$thought->getOwner()->isSelf($viewer) ){
        $menuoptions[$counterMenu]['name'] = "report";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Thought");
    }
    $thoughtResult['menus'] = $menuoptions;   
    // Get tags
    $counterTags = 0;
    foreach( $thought->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      $thoughtResult['tags'][$counterTags]['title'] = '#'.$tag->text;
      $thoughtResult['tags'][$counterTags]['tag_id']  = $tagMap->tag_id;
      $counterTags++;
    }
    $thoughtResult['thought']['user_image_url'] = $this->userImage($thought->getOwner()->getIdentity(),"thumb.profile");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$thoughtResult));
  }
  
  public function createAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    if( !$this->_helper->requireAuth()->setAuthParams('sesthought_thought', null, 'create')->isValid()) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
      // Prepare form
     $form = new Sesthought_Form_Create();
     $mediaType = !empty($_POST['mediatype']) ? $_POST['mediatype'] : "";
     $form->removeElement('cancel');
     $form->removeElement('token');
     $form->removeElement('cancel');
     if($this->_getParam('getForm')) {
         $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
         //echo "<pre>";var_dump($formFields);die;
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
      $table = Engine_Api::_()->getItemTable('sesthought_thought');
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
        $thought = $table->createRow();
        if($values['video']) {
          $information = $this->handleIframelyInformation($values['video']);
          $values['code'] = $information['code'];
          try{
            $thought->setPhoto($information['thumbnail']);
          }catch(Exception $e){
            //silence  
          }
        }       
        $thought->setFromArray($values);
        $thought->save();
        if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) 
        $thought->setPhoto($_FILES['image']);
        // Auth
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $viewMax = array_search($values['auth_view'], $roles);
        $commentMax = array_search($values['auth_comment'], $roles);
        foreach( $roles as $i => $role ) {
          $auth->setAllowed($thought, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($thought, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $thought->tags()->addTagMaps($viewer, $tags);
        // Add activity only if blog is published
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $thought, 'sesthought_new');
        // make sure action exists before attaching the blog to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $thought);
        }
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          foreach($tags as $tag) {
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');  
          }
        }
        $thought->action_id = $action->getIdentity();
        $thought->save();
        // Commit
        $db->commit();
       $result["message"] = $this->view->translate("Thought created successfully.");
       $result['id'] = $thought->getIdentity();
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
      } catch( Exception $e ) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
  }
  // HELPER FUNCTIONS
  public function handleIframelyInformation($uri) {
    $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesthought_iframely_disallow');
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
    $this->view->thought_id = $this->_getParam('thought_id');
    $thought = Engine_Api::_()->getItem('sesthought_thought', $this->_getParam('thought_id'));    
    if( !$this->_helper->requireAuth()->setAuthParams($thought, $viewer, 'edit')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
      // Prepare form
       $form = new Sesthought_Form_Edit();
      $category_id = $thought->category_id;
      $subcat_id = $thought->subcat_id;
      $subsubcat_id = $thought->subsubcat_id;
      // Populate form
      $form->populate($thought->toArray());
      $tagStr = '';
      foreach( $thought->tags()->getTagMaps() as $tagMap ) {
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
          if( $auth->isAllowed($thought, $role, 'view') ) {
           $form->auth_view->setValue($role);
          }
        }
        if ($form->auth_comment){
          if( $auth->isAllowed($thought, $role, 'comment') ) {
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
        if(count($formFields) && $thought->category_id){
          foreach($formFields as $fields){
            foreach($fields as $field){
                $subcat = array();
                if($fields['name'] == "subcat_id"){ 
                  $subcat = Engine_Api::_()->getItemTable('sesthought_category')->getModuleSubcategory(array('category_id'=>$thought->category_id,'column_name'=>'*'));
                }else if($fields['name'] == "subsubcat_id"){
                  if($thought->subcat_id)
                  $subcat = Engine_Api::_()->getItemTable('sesthought_category')->getModuleSubSubcategory(array('category_id'=>$thought->subcat_id,'column_name'=>'*'));
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
        $thought->setFromArray($values);
        $thought->modified_date = date('Y-m-d H:i:s');
        $thought->save();
        // Add photo
        if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) 
        $thought->setPhoto($_FILES['image']);
        // handle tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $thought->tags()->setTagMaps($viewer, $tags);
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags && $thought->action_id) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query("DELETE FROM engine4_sesadvancedactivity_hashtags WHERE action_id = '".$thought->action_id."'");
          foreach($tags as $tag) {
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$thought->action_id.'", "'.$tag.'")');  
          }
        }
        $db->commit();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Thought edited successfully.")));
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
    $thought = Engine_Api::_()->getItem('sesthought_thought', $this->getRequest()->getParam('thought_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($thought, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $this->view->form = $form = new Sesthought_Form_Delete();
    if( !$thought ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Thought entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }
    $db = $thought->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query("DELETE FROM engine4_sesadvancedactivity_hashtags WHERE action_id = '".$thought->action_id."'");
      }
      $thought->delete();
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->translate('Thought has been deleted.')));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your thought entry has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->message));
  }
}
