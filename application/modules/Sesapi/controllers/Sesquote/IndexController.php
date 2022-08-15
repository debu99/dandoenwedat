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
class Sesquote_IndexController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    // only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('sesquote_quote', null, 'view')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
  }  
  public function browseAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->form = $form = new Sesquote_Form_Search();
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
    $paginator = Engine_Api::_()->getItemTable('sesquote_quote')->getQuotesPaginator($values);
    $paginator->setItemCountPerPage($this->view->allParams['limit']);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $count = $paginator->getTotalItemCount();
    $result = $this->getQuotes($paginator,$manage);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    
    if($this->_getParam('page', 1) == 1 && !$manage){
      $categories = $this->categoryAction(true);
      $result['quoteCategories'] = $categories;
    }
    
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'Nobody has written a quote entry with that criteria.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));  
  }
  public function categoryAction($getCategory = false){
    $params['hasQuote'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'sesquote')->getCategory($params);
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
      $catgeoryArray[$counter]["count"] = $this->view->translate(array('%s quote', '%s quotes', $category->total_quote_categories), $this->view->locale()->toNumber($category->total_quote_categories));
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
  public function getQuotes($paginator,$manage = false){
    $result = array();
    $counter = 0;
    foreach($paginator as $quotes){ 
        $quote = $quotes->toArray();
        if($this->view->viewer()->getIdentity() != 0){
          try{
          $quote['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($quotes);
          $quote['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($quotes);
          }catch(Exception $e){}
        }     
        
        // Get tags
        $counterTags = 0;
        foreach( $quotes->tags()->getTagMaps() as $tagMap ) {
          $tag = $tagMap->getTag();
          if( !isset($tag->text) ) continue;
          $quote['tags'][$counterTags]['title'] = '#'.$tag->text;
          $quote['tags'][$counterTags]['tag_id']  = $tagMap->tag_id;
          $counterTags++;
        }
        if($quotes->category_id){
          $category = Engine_Api::_()->getItem('sesquote_category',$quotes->category_id);  
          if($category)
            $quote['category_title'] = $category->category_name;
        }
        $quote['user_title'] = $quotes->getOwner()->getTitle();
        $quote['user_image_url'] = $this->userImage($quotes->getOwner()->getIdentity(),"thumb.profile");
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($quotes,'',"");
        if(count($images))
        $quote['images'] = $images;        
        if($manage){
          $menuoptions= array();
          $canEdit = $this->_helper->requireAuth()->setAuthParams($quotes, null, 'edit')->isValid();
          $counterMenu = 0;
          if($canEdit){
            $menuoptions[$counterMenu]['name'] = "edit";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
            $counterMenu++;
          }
          $canDelete = $this->_helper->requireAuth()->setAuthParams($quotes, null, 'delete')->isValid();
          if($canDelete){
            $menuoptions[$counterMenu]['name'] = "delete";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
          }
          $quote['menus'] = $menuoptions;
        }
        
        $result['quotes'][$counter] = array_merge($quote,array());
        $counter++;
    }
    return $result;
  }
 public function searchFormAction(){
   $viewer = Engine_Api::_()->user()->getViewer();
	  $searchForm = new Sesquote_Form_Search();
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
    $quote = Engine_Api::_()->getItem('sesquote_quote', $this->_getParam('quote_id'));
    if( $quote ) {
      Engine_Api::_()->core()->setSubject($quote);
    }
    $quoteResult = array();
    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $canView = $this->_helper->requireAuth()->setAuthParams('sesquote_quote', null, 'view')->checkRequire();
    if(empty($canView)) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $quoteResult['quote'] = $quote->toArray();
    unset($quoteResult['quote']['title']);
    $quoteResult['quote']['title'] = strip_tags($quote->title);
    $quoteResult['quote']['code'] = str_replace("//cdn",'http://cdn',$quoteResult['quote']['code']);
     
    preg_match('/src="([^"]+)"/', $quoteResult['quote']['code'], $match);
    if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
      $iframeUrl = str_replace('//','https://',$match[1]);
    }else{
      $iframeUrl = $match[1];
    }
    $quoteResult['quote']['iframeUrl'] = $iframeUrl;
    // Prepare data
    $quoteTable = Engine_Api::_()->getDbtable('quotes', 'sesquote');
    $owner = $owner = $quote->getOwner();
    $viewer = $viewer;
    $viewer_id = $viewer->getIdentity();
    // Do other stuff
    $mine = true;
    $canEdit = $this->_helper->requireAuth()->setAuthParams($quote, null, 'edit')->checkRequire();
    if( !$quote->getOwner()->isSelf(Engine_Api::_()->user()->getViewer()) ) {
      $quote->getTable()->update(array(
        'view_count' => new Zend_Db_Expr('view_count + 1'),
      ), array(
        'quote_id = ?' => $quote->getIdentity(),
      ));
      $mine = false;
    }
    if ($viewer->getIdentity() != 0 && isset($quote->quote_id)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesquote_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $quote->quote_id . '", "sesquote_quote","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }
    $quoteResult['quote']['user_title'] = $owner->getTitle();
    if($this->view->viewer()->getIdentity() != 0){
      try{
      $quoteResult['quote']['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($quote);
      $quoteResult['quote']['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($quote);
      }catch(Exception $e){}
    }     
    if($quote->category_id){
      $category = Engine_Api::_()->getItem('sesquote_category',$quote->category_id);  
      if($category)
        $quoteResult['quote']['category_title'] = $category->category_name;
    }
    $images = Engine_Api::_()->sesapi()->getPhotoUrls($quote,'',"");
    $quoteResult['quote']['images'] = $images;    
    
    $photo = $this->getBaseUrl(false,$quote->getPhotoUrl());
    if($photo)
      $quoteResult['quote']["share"]["imageUrl"] = $photo;
		$quoteResult['quote']["share"]["url"] = $this->getBaseUrl(false,$quote->getHref());
    $quoteResult['quote']["share"]["title"] = $quote->source;
    $quoteResult['quote']["share"]["description"] = strip_tags($quote->getTitle());
    $quoteResult['quote']["share"]['urlParams'] = array(
        "type" => $quote->getType(),
        "id" => $quote->getIdentity()
    );
    if(is_null($quoteResult['quote']["share"]["title"]))
      unset($quoteResult['quote']["share"]["title"]);
    $viewer = Engine_Api::_()->user()->getViewer();
    $menuoptions= array();
    $canEdit = $this->_helper->requireAuth()->setAuthParams($quote, null, 'edit')->isValid();
    $counterMenu = 0;
    if($canEdit){
      $menuoptions[$counterMenu]['name'] = "edit";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
      $counterMenu++;
    }
    $canDelete = $this->_helper->requireAuth()->setAuthParams($quote, null, 'delete')->isValid();
    if($canDelete){
      $menuoptions[$counterMenu]['name'] = "delete";
      $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
    }
    if($viewer->getIdentity() != 0 &&  !$quote->getOwner()->isSelf($viewer) ){
        $menuoptions[$counterMenu]['name'] = "report";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Quote");
    }
    $quoteResult['menus'] = $menuoptions;   
    // Get tags
    $counterTags = 0;
    foreach( $quote->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      $quoteResult['tags'][$counterTags]['title'] = '#'.$tag->text;
      $quoteResult['tags'][$counterTags]['tag_id']  = $tagMap->tag_id;
      $counterTags++;
    }
    $quoteResult['quote']['user_image_url'] = $this->userImage($quote->getOwner()->getIdentity(),"thumb.profile");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$quoteResult));
  }
  
  public function createAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    if( !$this->_helper->requireAuth()->setAuthParams('sesquote_quote', null, 'create')->isValid()) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
      // Prepare form
      if(empty($_FILES['image']['name']))
        $_FILES['image'] = array();
     $form = new Sesquote_Form_Create();
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
      $table = Engine_Api::_()->getItemTable('sesquote_quote');
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
        $quote = $table->createRow();
        if($values['video']) {
          $information = $this->handleIframelyInformation($values['video']);
          $values['code'] = $information['code'];
          try{
            $quote->setPhoto($information['thumbnail']);
          }catch(Exception $e){
            //silence  
          }
        }       
        $quote->setFromArray($values);
        $quote->save();
        if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) 
        $quote->setPhoto($_FILES['image']);
        // Auth
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $viewMax = array_search($values['auth_view'], $roles);
        $commentMax = array_search($values['auth_comment'], $roles);
        foreach( $roles as $i => $role ) {
          $auth->setAllowed($quote, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($quote, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $quote->tags()->addTagMaps($viewer, $tags);
        // Add activity only if blog is published
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $quote, 'sesquote_new');
        // make sure action exists before attaching the blog to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $quote);
          if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            foreach($tags as $tag) {
              $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');  
            }
          }
          $quote->action_id = $action->getIdentity();
        }
        
        $quote->save();
        // Commit
        $db->commit();
       $result["message"] = $this->view->translate("Quote created successfully.");
       $result['id'] = $quote->getIdentity();
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
      } catch( Exception $e ) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
  }
  // HELPER FUNCTIONS
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

  public function editAction()
  {
    
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->quote_id = $this->_getParam('quote_id');
    $quote = Engine_Api::_()->getItem('sesquote_quote', $this->_getParam('quote_id'));    
    if( !$this->_helper->requireAuth()->setAuthParams($quote, $viewer, 'edit')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
      // Prepare form
       $form = new Sesquote_Form_Edit();
      $category_id = $quote->category_id;
      $subcat_id = $quote->subcat_id;
      $subsubcat_id = $quote->subsubcat_id;
      // Populate form
      $form->populate($quote->toArray());
      $tagStr = '';
      foreach( $quote->tags()->getTagMaps() as $tagMap ) {
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
          if( $auth->isAllowed($quote, $role, 'view') ) {
           $form->auth_view->setValue($role);
          }
        }
        if ($form->auth_comment){
          if( $auth->isAllowed($quote, $role, 'comment') ) {
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
        if(count($formFields) && $quote->category_id){
          foreach($formFields as $fields){
            foreach($fields as $field){
                $subcat = array();
                if($fields['name'] == "subcat_id"){ 
                  $subcat = Engine_Api::_()->getItemTable('sesquote_category')->getModuleSubcategory(array('category_id'=>$quote->category_id,'column_name'=>'*'));
                }else if($fields['name'] == "subsubcat_id"){
                  if($quote->subcat_id)
                  $subcat = Engine_Api::_()->getItemTable('sesquote_category')->getModuleSubSubcategory(array('category_id'=>$quote->subcat_id,'column_name'=>'*'));
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
        $quote->setFromArray($values);
        $quote->modified_date = date('Y-m-d H:i:s');
        $quote->save();
        // Add photo
        if( !empty($_FILES['image']['name']) && !empty($_FILES['image']['size'])) 
          $quote->setPhoto($_FILES['image']);
        // handle tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $quote->tags()->setTagMaps($viewer, $tags);
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags && $quote->action_id) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query("DELETE FROM engine4_sesadvancedactivity_hashtags WHERE action_id = '".$quote->action_id."'");
          foreach($tags as $tag) {
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$quote->action_id.'", "'.$tag.'")');  
          }
        }
        $db->commit();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Quote edited successfully.")));
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
    $quote = Engine_Api::_()->getItem('sesquote_quote', $this->getRequest()->getParam('quote_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($quote, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    $this->view->form = $form = new Sesquote_Form_Delete();
    if( !$quote ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Quote entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error,'result'=>''));
    }
    $db = $quote->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query("DELETE FROM engine4_sesadvancedactivity_hashtags WHERE action_id = '".$quote->action_id."'");
      }
      $quote->delete();
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->translate('Quote has been deleted.')));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>''));
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your quote entry has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$this->view->message));
  }
}
