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
class Sesblog_IndexController extends Sesapi_Controller_Action_Standard
{
  protected $_sesblogEnabled;
  public function init() {
		// only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('sesblog_blog', null, 'view')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    $id = $this->_getParam('blog_id', $this->_getParam('id', null));
    $this->isSesblogEnable();
    if($this->_sesblogEnabled){
      $blog_id = Engine_Api::_()->getDbtable('blogs', 'sesblog')->getBlogId($id);
      if ($blog_id) {
        $blog = Engine_Api::_()->getItem('sesblog_blog', $blog_id);
        if ($blog) {
            Engine_Api::_()->core()->setSubject($blog);
        }
      }
    }else{
      if ($id) {
        $blog = Engine_Api::_()->getItem('blog', $id);
        if ($blog) {
            Engine_Api::_()->core()->setSubject($blog);
        }
      }
    }
  }
 protected function isSesblogEnable(){
   $this->_sesblogEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesblog');
 }
  public function searchFormAction(){
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog_enable_location', 1))
        $location = 'yes';	
        else
        $location = 'no';
    
       $form = new Sesblog_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'blog','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => $location,'kilometerMiles' => 'yes','hasPhoto' => 'yes'));
       $form->removeElement('lat');
       $form->removeElement('lng');
       
       $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));
    
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1))
			unset($filterOptions['mostSPfavourite']);
        $arrayOptions = $filterOptions;
        $filterOptions = array();
        foreach ($arrayOptions as $key=>$filterOption) {
          if(is_numeric($key))
            $columnValue = $filterOption;
          else
            $columnValue = $key;
          $value = str_replace(array('SP',''), array(' ',' '), $columnValue);
          $filterOptions[$columnValue] = ucwords($value);
        }
        $filterOptions = array(''=>'')+$filterOptions;
        $form->sort->setMultiOptions($filterOptions);
        $form->sort->setValue('recentlySPcreated');       
       
       $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
       $this->generateFormFields($formFields); 
  }
  public function browseAction() {
    if($this->_sesblogEnabled){
        $form = new Sesblog_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'blog','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => 'yes','kilometerMiles' => 'yes','hasPhoto' => 'yes'));
        
        $filterOptions = (array)$this->_getParam('sortss', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));
    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];  
      }  
    }
    $form->populate($_POST);
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1))
			unset($filterOptions['mostSPfavourite']);
    
    $arrayOptions = $filterOptions;
    $filterOptions = array();
    foreach ($arrayOptions as $key=>$filterOption) {
      if(is_numeric($key))
        $columnValue = $filterOption;
      else
        $columnValue = $key;
      $value = str_replace(array('SP',''), array(' ',' '), $columnValue);
      $filterOptions[$columnValue] = ucwords($value);
    }
    $filterOptions = array(''=>'')+$filterOptions;
    $form->sort->setMultiOptions($filterOptions);
    $sort = $this->_getParam('sort','recentlySPcreated');
    $form->sort->setValue($sort);
    $options = $form->getValues();
    
    if (isset($options['sort']) && $options['sort'] != '') {
      $getParamSort = str_replace('SP', '_', $options['sort']);
    } else
      $getParamSort = 'creation_date';
      
      if (isset($getParamSort)) {
        switch ($getParamSort) {
          case 'most_viewed':
            $options['popularCol'] = 'view_count';
            break;
          case 'most_liked':
            $options['popularCol'] = 'like_count';
            break;
          case 'most_commented':
            $options['popularCol'] = 'comment_count';
            break;
          case 'most_favourite':
            $options['popularCol'] = 'favourite_count';
            break;
          case 'sponsored':
            $options['popularCol'] = 'sponsored';
            $options['fixedData'] = 'sponsored';
            break;
          case 'verified':
            $options['popularCol'] = 'verified';
            $options['fixedData'] = 'verified';
          break;
          case 'featured':
            $options['popularCol'] = 'featured';
            $options['fixedData'] = 'featured';
            break;
          case 'most_rated':
            $options['popularCol'] = 'rating';
            break;
          case 'recently_created':
            default:
            $options['popularCol'] = 'creation_date';
            break;
        }
      }
      // Get search params
      $page = (int)  $this->_getParam('page', 1);
      
      if(!empty($_POST['search']))
        $options['text'] = $_POST['search'];
      if(!empty($_POST['user_id'])){
        $options['user_id'] = $_POST['user_id'];
        $condition = array('manage-widget'=>true);
      }else{
        $condition = array('status'=>1,'draft'=>0,'visible'=>1);  
      }
      if(!empty($_POST['owner_id']))
        $options["user_id"] = $_POST['owner_id'];
      $paginator = Engine_Api::_()->getDbtable('blogs', 'sesblog')->getSesblogsPaginator(array_merge($options,$condition), $options);
       $page = (int)  $this->_getParam('page', 1);
      // Build paginator
      $paginator->setItemCountPerPage($this->_getParam('limit',10));
      $paginator->setCurrentPageNumber($page);
      
      $result = $this->blogResult($paginator);
      
      if(!empty($_POST['user_id'])){
        $viewer = Engine_Api::_()->user()->getViewer();
        $menuoptions= array();
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'edit');
        $counter = 0;
        if($canEdit){
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit"); 
          $counter++;
        }
        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'delete');
        if($canDelete){
          $menuoptions[$counter]['name'] = "delete";
          $menuoptions[$counter]['label'] = $this->view->translate("Delete");
        }
        $result['menus'] = $menuoptions;  
      }
      
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
      if($result <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist blogs.'), 'result' => array())); 
      else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams)); 
    }else{
        
    }
  }
  function blogResult($paginator){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesblog')){
        $sesblog = true; 
      }
      foreach($paginator as $blogs){
        $blog = $blogs->toArray();
        $description = strip_tags($blogs['body']);
        $description = preg_replace('/\s+/', ' ', $description);
        unset($blog['body']);
        $blog["comment_count"] = Engine_Api::_()->sesadvancedcomment()->commentCount($blogs,'subject');
        $blog['owner_title'] = Engine_Api::_()->getItem('user',$blog['owner_id'])->getTitle();
        $blog['body'] = $description;   
        $blog['resource_type'] = $blogs->getType();
                
        if($this->_sesblogEnabled){
          if($viewer->getIdentity() != 0){
            $blog['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($blogs);
            $blog['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($blogs);
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1)){
              $blog['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($blogs,'favourites','sesblog');
              $blog['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($blogs,'favourites','sesblog');
            }
          }  
        }
        
        $result['blogs'][$counterLoop] = $blog;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($blogs,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$blogs->getPhotoUrl());
        $result['blogs'][$counterLoop]['images'] = $images;
        $counterLoop++;  
      }
      return $result;
  }
  public function categoryAction(){
    $params['countBlogs'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'sesblog')->getCategory($params);
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $category){
      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;
      if($category->thumbnail != '' && !is_null($category->thumbnail) && intval($category->thumbnail)):
        $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl(''));
      endif;
      if($category->cat_icon != '' && !is_null($category->cat_icon) && intval($category->cat_icon)):
        $catgeoryArray["category"][$counter]["cat_icon"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->cat_icon)->getPhotoUrl('thumb.icon'));
      endif;
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s blog', '%s blogs', $category->total_blogs_categories), $this->view->locale()->toNumber($category->total_blogs_categories));
      
      $counter++;
    }
    if($catgeoryArray <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array())); 
  }
  //Browse Blog Contributors
  public function contributorsAction() {
    // Render
    $this->_helper->content->setEnabled();
  }

  
  
  public function claimAction() {
  
		$viewer = Engine_Api::_()->user()->getViewer();
		if( !$viewer || !$viewer->getIdentity() ) 
		if( !$this->_helper->requireUser()->isValid() ) return;
		
    if(!Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_claim', 'create') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.claim', 1)) 
    return $this->_forward('requireauth', 'error', 'core');
  
    // Render
    $this->_helper->content->setEnabled();
  }
  
  public function claimRequestsAction() {
  
    $checkClaimRequest = Engine_Api::_()->getDbTable('claims', 'sesblog')->claimCount();
    if(!$checkClaimRequest)
    return $this->_forward('notfound', 'error', 'core');
    // Render
    $this->_helper->content->setEnabled();
  }
  
  
  public function viewAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $id = $this->_getParam('blog_id', null);
    $this->view->blog_id = $blog_id = Engine_Api::_()->getDbtable('blogs', 'sesblog')->getBlogId($id);
    if(!Engine_Api::_()->core()->hasSubject())
      $sesblog = Engine_Api::_()->getItem('sesblog_blog', $blog_id);
    else
      $sesblog = Engine_Api::_()->core()->getSubject();

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
      
    if( !$this->_helper->requireAuth()->setAuthParams($sesblog, $viewer, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
      
    if( !$sesblog || !$sesblog->getIdentity() || ($sesblog->draft && !$sesblog->isOwner($viewer)) )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    
    $blog = $sesblog->toArray();
      $body = $this->replaceSrc($sesblog['body']);
    $blog['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
    $blog['owner_title'] = Engine_Api::_()->getItem('user',$blog['owner_id'])->getTitle();
    $blog['resource_type'] = $sesblog->getType();
    
    
     // Get tags
    $blogTags = $sesblog->tags()->getTagMaps();
    if (!empty($blogTags)) {
        foreach ($blogTags as $tag) {
            $blog['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
        }
    }
    
    if($this->_sesblogEnabled){
      if($viewer->getIdentity() != 0){
        $blog['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($sesblog);
        $blog['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($sesblog);
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1)){
          $blog['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($sesblog,'favourites','sesblog');
          $blog['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($sesblog,'favourites','sesblog');
        }
      }  
    }
    if (!$sesblog->isOwner($viewer)) {
        $sesblog->view_count = $sesblog->view_count + 1;
        $sesblog->save();
    }
    
    $category = Engine_Api::_()->getItem('sesblog_category', $sesblog->category_id);
    if (!empty($category))
        $blog['category_title'] = $category->getTitle();
    
    $subcategory = Engine_Api::_()->getItem('sesblog_category', $sesblog->subcat_id);
    if (!empty($subcategory))
        $blog['subcategory_title'] = $subcategory->getTitle();
    
    $subsubcat = Engine_Api::_()->getItem('sesblog_category', $sesblog->subsubcat_id);
    if (!empty($subsubcat))
        $blog['subsubcategory_title'] = $subsubcat->getTitle();
    
    $blog['content_url'] = $this->getBaseUrl(false,$sesblog->getHref());
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1)){
      $blog['can_favorite'] = true;
    }else{
      $blog['can_favorite'] = false;
    }
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.share', 1)){
      $blog['can_share'] = true;
    }else{
      $blog['can_share'] = false;
    }
    $result['blog'] = $blog;
    if($viewer->getIdentity() > 0){
			$result['blog']['permission']['canEdit'] = $canEdit = $viewPermission = $sesblog->authorization()->isAllowed($viewer, 'edit') ? true : false;
			$result['blog']['permission']['canComment'] =  $sesblog->authorization()->isAllowed($viewer, 'comment') ? true : false;
			$result['blog']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'create') ? true : false;
			$result['blog']['permission']['can_delete'] = $canDelete  = $sesblog->authorization()->isAllowed($viewer,'delete') ? true : false;
      
      $menuoptions= array();
      $counter = 0;
      if($canEdit){
        $menuoptions[$counter]['name'] = "changephoto";
        $menuoptions[$counter]['label'] = $this->view->translate("Change Main Photo");
        $counter++;
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit Blog"); 
        $counter++;
      }
      if($canDelete){
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete Blog");
        $counter++;
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.report', 1)){
        $menuoptions[$counter]['name'] = "report";
        $menuoptions[$counter]['label'] = $this->view->translate("Report Blog");
      }
      $result['menus'] = $menuoptions;
		}
    
    $result['blog']["share"]["name"] = "share";
    $result['blog']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$sesblog->getPhotoUrl());
    if($photo)
    $result['blog']["share"]["imageUrl"] = $photo;
		$result['blog']["share"]["url"] = $this->getBaseUrl(false,$sesblog->getHref());
    $result['blog']["share"]["title"] = $sesblog->getTitle();
    $result['blog']["share"]["description"] = strip_tags($sesblog->getDescription());
    $result['blog']["share"]['urlParams'] = array(
        "type" => $sesblog->getType(),
        "id" => $sesblog->getIdentity()
    );
    if(is_null($result['blog']["share"]["title"]))
      unset($result['blog']["share"]["title"]);
    
    $images = Engine_Api::_()->sesapi()->getPhotoUrls($sesblog,'',"");
    if(!count($images))
      $images['main'] = $this->getBaseUrl(true,$sesblog->getPhotoUrl());
    $result['blog']['blog_images'] = $images;
    
    $result['blog']['user_images'] = $this->userImage($sesblog->owner_id,"thumb.profile");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array())); 
    
  }
    function replaceSrc($html = ""){
        preg_match_all( '@src="([^"]+)"@' , $html, $match );
        foreach(array_pop($match) as $src){
            if(strpos($src,'http://') === false && strpos($src,'https://') === false && strpos($src,'//') === false){
                $html = str_replace($src,$this->getBaseUrl().$src,$html);
            }else if(strpos($src,'http://') === false && strpos($src,'https://') === false){
                $html = str_replace($src,'https://'.$src,$html);
            }
        }
        return $html;
    }
  public function createAction() {     
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('sesblog_blog', null, 'create')->isValid()) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
		$viewer = Engine_Api::_()->user()->getViewer();
		/*if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesblogpackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblogpackage.enable.package', 1)){
			$package = Engine_Api::_()->getItem('sesblogpackage_package',$this->_getParam('package_id',0));
			$existingpackage = Engine_Api::_()->getItem('sesblogpackage_orderspackage',$this->_getParam('existing_package_id',0));
			if($existingpackage){
				$package = Engine_Api::_()->getItem('sesblogpackage_package',$existingpackage->package_id);
			}
			if (!$package && !$existingpackage){
				//check package exists for this member level
				$packageMemberLevel = Engine_Api::_()->getDbTable('packages','sesblogpackage')->getPackage(array('member_level'=>$viewer->level_id));
				if(count($packageMemberLevel)){
					//redirect to package page
					return $this->_helper->redirector->gotoRoute(array('action'=>'blog'), 'sesblogpackage_general', true);
				}
			}
		}*/
    $session = new Zend_Session_Namespace();
		if(empty($_POST))
		unset($session->album_id);
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesblog')->profileFieldId();

    // set up data needed to check quota
    $parentType = $this->_getParam('parent_type', null);
    if($parentType)
    $event_id = $this->_getParam('event_id', null);
    
    $parentId = $this->_getParam('parent_id', 0);
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getDbtable('blogs', 'sesblog')->getSesblogsPaginator($values);

    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesblog_blog', 'max');
    $this->view->current_count = $paginator->getTotalItemCount();
    if (($this->view->current_count >= $quota) && !empty($quota)) {
        // return error message
        $message = $this->view->translate('You have already uploaded the maximum number of blogs allowed.');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
      }
    $this->view->categories = Engine_Api::_()->getDbtable('categories', 'sesblog')->getCategoriesAssoc();
        
    // Prepare form
    $this->view->form = $form = new Sesblog_Form_Create(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));
    $form->removeElement('lat');
    $form->removeElement('map-canvas');
    $form->removeElement('ses_location');
    $form->removeElement('lng');
    $form->removeElement('fancyuploadfileids');
    $form->removeElement('tabs_form_blogcreate');
    $form->removeElement('file_multi');
    $form->removeElement('from-url');
    $form->removeElement('drag-drop');
    $form->removeElement('uploadFileContainer');
    $form->removeElement('blogstyle');
    $form->removeElement('submit_check');
    $form->removeElement('blog_custom_datetimes');
      // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    if(!empty($_POST['custom_url_blog']))
      $_POST['custom_url'] = $_POST['custom_url_blog'];
    if(!empty($_POST["starttime"])){
      $date = $_POST["starttime"];  
      unset($_POST['starttime']);
      if(!empty($date) && !is_null($date)){
        $_POST['starttime']['month'] = date('m',strtotime($date));
        $_POST['starttime']['year'] = date('Y',strtotime($date));
        $_POST['starttime']['day'] = date('d',strtotime($date));
      }
    }else{
      $_POST['starttime'] = "";  
    }  
    
    // Check if valid
    if( !$form->isValid($this->getRequest()->getPost()) ) { 
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }
    
    //check custom url
    if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
      $custom_url = Engine_Api::_()->getDbtable('blogs', 'sesblog')->checkCustomUrl($_POST['custom_url']);
      if ($custom_url) {
				$form->addError($this->view->translate("Custom Url is not available. Please select another URL."));
        $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
        if(count($validateFields))
          $this->validateFormFields($validateFields);
      }
    }
    
    $mainPhotoEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.photo.mandatory', '1');
		if ($mainPhotoEnable == 1 && empty($_FILES['image']['size'])) {
			$form->addError($this->view->translate("Please upload main photo"));
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
		}
    
    // Process
    $table = Engine_Api::_()->getDbtable('blogs', 'sesblog');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      
      // Create sesblog
      $viewer = Engine_Api::_()->user()->getViewer();
      $values = array_merge($form->getValues(), array(
        'owner_type' => $viewer->getType(),
        'owner_id' => $viewer->getIdentity(),
      ));
      if(!empty($values['starttime'])){
        $starttime = $values['starttime'];
        unset($_POST['starttime']);
      }
      $values['ip_address'] = $_SERVER['REMOTE_ADDR'];
      $sesblog = $table->createRow();
      if (is_null($values['subsubcat_id']))
      $values['subsubcat_id'] = 0;
      if (is_null($values['subcat_id']))
      $values['subcat_id'] = 0;
			if(isset($package)){
				$values['package_id'] = $package->getIdentity();
				$values['is_approved'] = 0;
				if($existingpackage){
					$values['existing_package_order'] = $existingpackage->getIdentity();
					$values['orderspackage_id'] = $existingpackage->getIdentity();
					$existingpackage->item_count = $existingpackage->item_count - 1;
					$existingpackage->save();
					$values['is_approved'] = 1;
				}
			}else{
				$values['is_approved'] = 1;
				if(isset($sesblog->package_id) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesblogpackage') ){
					$values['package_id'] = Engine_Api::_()->getDbTable('packages','sesblogpackage')->getDefaultPackage();
				}
			}
			if(empty($_POST['draft'])){
        $values['draft'] = 0;  
      }
			if($_POST['blogstyle'])
        $values['style'] = $_POST['blogstyle'];
      
      //SEO By Default Work
      $values['seo_title'] = $values['title'];
			if($values['tags'])
			  $values['seo_keywords'] = $values['tags'];
      
      $sesblog->setFromArray($values);
						
      if(!empty($_FILES['image']['size'])){
        $sesblog->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesblog','sesblog_blog','',$sesblog,true);
      }
      
			if(isset($starttime) && $starttime != ''){
				$starttime = isset($starttime) ? date('Y-m-d H:i:s',strtotime($starttime)) : '';
      	$sesblog->publish_date =$starttime;
			}
			
			if(isset($starttime) && $viewer->timezone && $starttime != ""){
				//Convert Time Zone
				$oldTz = date_default_timezone_get();
				date_default_timezone_set($viewer->timezone);
				$start = strtotime($starttime);
				date_default_timezone_set($oldTz);
				$sesblog->publish_date = date('Y-m-d H:i:s', $start);
			}else{
				$sesblog->publish_date = date('Y-m-d H:i:s',strtotime("-2 minutes", time()));
			}
			$sesblog->parent_id = $parentId;
      $sesblog->save();
      $blog_id = $sesblog->blog_id;

      if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
      $sesblog->custom_url = $_POST['custom_url'];
      else
      $sesblog->custom_url = $sesblog->blog_id;
      $sesblog->save();
      $blog_id = $sesblog->blog_id;
      
      $roleTable = Engine_Api::_()->getDbtable('roles', 'sesblog');
			$row = $roleTable->createRow();
			$row->blog_id = $blog_id;
			$row->user_id = $viewer->getIdentity();
			$row->save();
      if(!empty($_POST['location'])){
        $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
        if($latlng){
          $_POST['lat'] = $latlng['lat'];
          $_POST['lng'] = $latlng['lng'];  
        }  
      }
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
					Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $blog_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesblog_blog")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      
      if($parentType == 'sesevent_blog') {
        $sesblog->parent_type = $parentType;
        $sesblog->event_id = $event_id;
        $sesblog->save();
        $seseventblog = Engine_Api::_()->getDbtable('mapevents', 'sesblog')->createRow();
        $seseventblog->event_id = $event_id;
        $seseventblog->blog_id = $blog_id;
        $seseventblog->save();
      }

      if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
				$sesblog->photo_id = $_POST['cover'];
				$sesblog->save();
      }
      
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
				$customfieldform->setItem($sesblog);
				$customfieldform->saveValues();
      }
	
      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $videoMax = array_search(isset($values['auth_video']) ? $values['auth_video']: '', $roles);
      $musicMax = array_search(isset($values['auth_music']) ? $values['auth_music']: '', $roles);
			
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($sesblog, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($sesblog, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($sesblog, $role, 'video', ($i <= $videoMax));
        $auth->setAllowed($sesblog, $role, 'music', ($i <= $musicMax));
      }
      
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
     // $sesblog->seo_keywords = implode(',',$tags);
      //$sesblog->seo_title = $sesblog->title;
      $sesblog->save();
      $sesblog->tags()->addTagMaps($viewer, $tags);
      
      $session = new Zend_Session_Namespace();
      if(!empty($session->album_id)){
				$album_id = $session->album_id;
				if(isset($blog_id) && isset($sesblog->title)){
					Engine_Api::_()->getDbTable('albums', 'sesblog')->update(array('blog_id' => $blog_id,'owner_id' => $viewer->getIdentity(),'title' => $sesblog->title), array('album_id = ?' => $album_id));
					if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
						Engine_Api::_()->getDbTable('albums', 'sesblog')->update(array('photo_id' => $_POST['cover']), array('album_id = ?' => $album_id));
					}
					Engine_Api::_()->getDbTable('photos', 'sesblog')->update(array('blog_id' => $blog_id), array('album_id = ?' => $album_id));
					unset($session->album_id);
				}
      }

      // Add activity only if sesblog is published
      if( $values['draft'] == 0 && $values['is_approved'] == 1 && (!$sesblog->publish_date || strtotime($sesblog->publish_date) <= time())) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesblog, 'sesblog_new');
        // make sure action exists before attaching the sesblog to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesblog);
        }
        //Send notifications for subscribers
      	Engine_Api::_()->getDbtable('subscriptions', 'sesblog')->sendNotifications($sesblog);
      	$sesblog->is_publish = 1;
      	$sesblog->save();
			}
      // Commit
      $db->commit();
    }

    catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
		
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('blog_id'=>$sesblog->getIdentity(),'message'=>$this->view->translate('Blog created successfully.'))));    
  }
    
  public function deleteAction() {
    $sesblog = Engine_Api::_()->getItem('sesblog_blog', $this->getRequest()->getParam('blog_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($sesblog, null, 'delete')->isValid()) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
   
    if( !$sesblog ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Sesblog entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array())); 
    }

    if( !$this->getRequest()->isPost() ) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }
    $db = $sesblog->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      Engine_Api::_()->sesblog()->deleteBlog($sesblog);;
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your sesblog entry has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
  }

  protected function setPhoto($photo, $id) {
  
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    if (!$fileName) {
      $fileName = $file;
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'sesblog_blog',
        'parent_id' => $id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $fileName,
    );
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_main.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($mainPath)
            ->destroy();
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
    } catch (Exception $e) {
      // Remove temp files
      @unlink($mainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Sesblog_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
    @unlink($mainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }
  public function editAction(){
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    
    $this->view->blog = $sesblog = Engine_Api::_()->core()->getSubject(); 
    
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesblog')->profileFieldId();
   
    if( !$this->_helper->requireSubject()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('sesblog_blog', $viewer, 'edit')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


    // Prepare form
    $this->view->form = $form = new Sesblog_Form_Edit(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));

    // Populate form
    $form->populate($sesblog->toArray());
    
    $tagStr = '';
    foreach( $sesblog->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      if( '' !== $tagStr ) $tagStr .= ', ';
      $tagStr .= $tag->text;
    }
    $form->populate(array(
      'tags' => $tagStr,
    ));
    $this->view->tagNamePrepared = $tagStr;

    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    foreach( $roles as $role ) {
      if ($form->auth_view){
        if( $auth->isAllowed($sesblog, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($sesblog, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }
      
      if ($form->auth_video){
        if( $auth->isAllowed($sesblog, $role, 'video') ) {
          $form->auth_video->setValue($role);
        }
      }
      
      if ($form->auth_music){
        if( $auth->isAllowed($sesblog, $role, 'music') ) {
          $form->auth_music->setValue($role);
        }
      }
    }
    
    // hide status change if it has been already published
    if( $sesblog->draft == "0" )
      $form->removeElement('draft');
    
    $form->removeElement('lat');
    $form->removeElement('map-canvas');
    $form->removeElement('ses_location');
    $form->removeElement('lng');
    $form->removeElement('fancyuploadfileids');
    $form->removeElement('tabs_form_blogcreate');
    $form->removeElement('file_multi');
    $form->removeElement('from-url');
    $form->removeElement('drag-drop');
    $form->removeElement('uploadFileContainer');
    $form->removeElement('blogstyle');
    $form->removeElement('submit_check');
    $form->removeElement('blog_custom_datetimes');
      // Check if post and populate
    if($this->_getParam('getForm')) {
      if(isset($sesblog) && $form->starttime){
				$start = strtotime($sesblog->publish_date);
				$start_date = date('m/d/Y',($start));
				$start_time = date('g:ia',($start));
				$viewer = Engine_Api::_()->user()->getViewer();
				$publishDate = $start_date.' '.$start_time;
        $start_date_y = date('Y',strtotime($start_date));
        $start_date_m = date('m',strtotime($start_date));
        $start_date_d = date('d',strtotime($start_date));
				if($viewer->timezone){
					$start = strtotime($sesblog->publish_date);
					$oldTz = date_default_timezone_get();
					date_default_timezone_set($viewer->timezone);
					$start_date = date('m/d/Y',($start));
          $start_date_y = date('Y',strtotime($start_date));
          $start_date_m = date('m',strtotime($start_date));
          $start_date_d = date('d',strtotime($start_date));
					$start_time = date('g:ia',($start));
					date_default_timezone_set($oldTz);
				}
        if(!empty($start_date_y)){
            $start_date_cal = array('year'=>$start_date_y,'month'=>$start_date_m,'day'=>$start_date_d);
            $form->starttime->setValue($start_date_cal);
          }
			}
      
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory
      $newFormFieldsArray = array();
      
          if(count($formFields) && $sesblog->category_id){
              foreach($formFields as $fields){
                foreach($fields as $field){
                    $subcat = array();
                    if($fields['name'] == "subcat_id"){ 
                      $subcat = Engine_Api::_()->getItemTable('sesblog_category')->getModuleSubcategory(array('category_id'=>$sesblog->category_id,'column_name'=>'*'));
                    }else if($fields['name'] == "subsubcat_id"){
                      if($sesblog->subcat_id)
                      $subcat = Engine_Api::_()->getItemTable('sesblog_category')->getModuleSubSubcategory(array('category_id'=>$sesblog->subcat_id,'column_name'=>'*'));
                    }
                      if(count($subcat)){
                        $arrayCat = array();
                        foreach($subcat as $cat){
                          $arrayCat[$cat->getIdentity()] = $cat->getTitle(); 
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
    if(!empty($_POST["starttime"])){
        $date = $_POST["starttime"];  
        unset($_POST['starttime']);
        if(!empty($date) && !is_null($date)){
          $_POST['starttime']['month'] = date('m',strtotime($date));
          $_POST['starttime']['year'] = date('Y',strtotime($date));
          $_POST['starttime']['day'] = date('d',strtotime($date));
        }
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
    
    try
    { 
      $values = $form->getValues();
      if(!empty($values['starttime'])){
        $starttime = $values['starttime'];
        unset($_POST['starttime']);
      }
      $sesblog->setFromArray($values);
      $sesblog->modified_date = date('Y-m-d H:i:s');
			if(isset($starttime) && $starttime != ''){
				$starttime = isset($starttime) ? date('Y-m-d H:i:s',strtotime($starttime)) : '';
      	$sesblog->publish_date =$starttime;
			}
			//else{
			//	$sesblog->publish_date = '';	
			//}
      $sesblog->save();
      unset($_POST['title']);
      unset($_POST['tags']);
      unset($_POST['category_id']);
      unset($_POST['subcat_id']);
      unset($_POST['MAX_FILE_SIZE']);
      unset($_POST['body']);
      unset($_POST['search']);
      unset($_POST['execute']);
      unset($_POST['token']);
      unset($_POST['submit']);
      $values['fields'] = $_POST;
      $values['fields']['0_0_1'] = '2';
      if(!empty($_POST['location'])){
        $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
        if($latlng){
          $_POST['lat'] = $latlng['lat'];
          $_POST['lng'] = $latlng['lng'];  
        }  
      }
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
	Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('blog_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesblog_blog") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      
      if(isset($values['draft']) && !$values['draft']) {
        $currentDate = date('Y-m-d H:i:s');
        if($sesblog->publish_date < $currentDate) {
          $sesblog->publish_date = $currentDate;
          $sesblog->save();
        }
      }
      if(!empty($_FILES['image']['size'])){
        $sesblog->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesblog','sesblog_blog','',$sesblog,true);
        $sesblog->save();
      }
      // Add fields
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
        $customfieldform->setItem($sesblog);
        $customfieldform->saveValues($values['fields']);
      }
      
      // Auth
      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $videoMax = array_search($values['auth_video'], $roles);
      $musicMax = array_search($values['auth_music'], $roles);
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($sesblog, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($sesblog, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($sesblog, $role, 'video', ($i <= $videoMax));
        $auth->setAllowed($sesblog, $role, 'music', ($i <= $musicMax));
      }

      // handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $sesblog->tags()->setTagMaps($viewer, $tags);
						
      // insert new activity if sesblog is just getting published
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($sesblog);
      if( count($action->toArray()) <= 0 && $values['draft'] == '0' && (!$sesblog->publish_date || strtotime($sesblog->publish_date) <= time())) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesblog, 'sesblog_new');
          // make sure action exists before attaching the sesblog to the activity
        if( $action != null ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesblog);
        }
        $sesblog->is_publish = 1;
      	$sesblog->save();
      }
      
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($sesblog) as $action ) {
        $actionTable->resetActivityBindings($action);
      }

      // Send notifications for subscribers
      Engine_Api::_()->getDbtable('subscriptions', 'sesblog')
          ->sendNotifications($sesblog);
      $db->commit();
      
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    
    }
Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('blog_id'=>$sesblog->getIdentity(),'message'=>$this->view->translate('Blog Edit successfully.'))));
    
    
  }
  public function editPhotoAction() {
    $blog_id = $this->_getParam('blog_id',0);
    $sesblog = Engine_Api::_()->core()->getSubject();
    if(!$sesblog){
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }
    if(!empty($_FILES['image']['size'])){
      $sesblog->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesblog','sesblog_blog','',$sesblog,true);
      $sesblog->save();
      
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($sesblog,'','');
      if(!count($images))
        $images['main'] = $this->getBaseUrl(true,$sesblog->getPhotoUrl());
      $result['images'] = $images;
      $result['message'] = $this->view->translate('Blog photo updated successfully.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
    }else{ 
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }
  }
  
  public function customUrlCheckAction(){
    $value = $this->sanitize($this->_getParam('value', null));
    if(!$value) {
      echo json_encode(array('error'=>true));die;
    }
    $blog_id = $this->_getParam('blog_id',null);
    $custom_url = Engine_Api::_()->getDbtable('blogs', 'sesblog')->checkCustomUrl($value,$blog_id);
    if($custom_url){
      echo json_encode(array('error'=>true,'value'=>$value));die;
    }else{
      echo json_encode(array('error'=>false,'value'=>$value));die;	
    }
  }
  
  function sanitize($string, $force_lowercase = true, $anal = false) {}
}
