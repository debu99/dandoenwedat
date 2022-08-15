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
class Blog_IndexController extends Sesapi_Controller_Action_Standard {

  protected $_blogEnabled;

  public function init() {

		//Only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $this->isBlogEnable();

//     $id = $this->_getParam('blog_id', $this->_getParam('id', null));
//     $this->isBlogEnable();
//     if($this->_blogEnabled){
//       $blog_id = Engine_Api::_()->getDbtable('blogs', 'sesblog')->getBlogId($id);
//       if ($blog_id) {
//         $blog = Engine_Api::_()->getItem('sesblog_blog', $blog_id);
//         if ($blog) {
//             Engine_Api::_()->core()->setSubject($blog);
//         }
//       }
//     }else{
//       if ($id) {
//         $blog = Engine_Api::_()->getItem('blog', $id);
//         if ($blog) {
//             Engine_Api::_()->core()->setSubject($blog);
//         }
//       }
//     }
  }

  protected function isBlogEnable() {
    $this->_blogEnabled = true;
  }

  public function searchFormAction() {

    $filterOptions = (array)$this->_getParam('orderby', array('creation_date DESC' => 'Recently Created',
      'member_count DESC' => 'Most Popular',));
    $search_for = $this-> _getParam('search_for', 'blog');

    $default_search_type = $this->_getParam('default_search_type', 'recentlySPcreated');

    $form = new Blog_Form_Search();
    if($form->draft)
      $form->removeElement('draft');
    if(count($filterOptions)) {
      $arrayOptions = $filterOptions;
      $filterOptions = array();
      foreach ($arrayOptions as $key=>$filterOption) {
        $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
        $filterOptions[$key] = ucwords($value);
      }
      $filterOptions = array(''=>'')+$filterOptions;
      $form->orderby->setMultiOptions($filterOptions);
      $form->orderby->setValue($default_search_type);
    }

    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
    $this->generateFormFields($formFields);
  }


  public function browseAction() {

    // Prepare data
    $viewer = Engine_Api::_()->user()->getViewer();

    // Permissions
    $canCreate = $this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->checkRequire();

    // Make form
    // Note: this code is duplicated in the blog.browse-search widget
    $form = new Blog_Form_Search();

    $form->removeElement('draft');
    if( !$viewer->getIdentity() ) {
      $form->removeElement('show');
    }

    // Process form
    $defaultValues = $form->getValues();
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    } else {
      $values = $defaultValues;
    }
    //$this->view->formValues = array_filter($values);
    $values['draft'] = "0";
    $values['visible'] = "1";

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
      //unset($values['show']);
      $values['users'] = $ids;
    }

    //$this->view->assign($values);

    if(!empty($_POST['user_id']))
      $values["user_id"] = $_POST['user_id'];
    if(!empty($_POST['category_id']))
      $values['category'] = $_POST['category_id'];
    // Get blogs
    $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator($values);
    $items_per_page = Engine_Api::_()->getApi('settings', 'core')->blog_page;
    $paginator->setItemCountPerPage($items_per_page);
    $paginator->setCurrentPageNumber( $values['page'] );
    $result = $this->blogResult($paginator);

    if(!empty($_POST['user_id'])) {

      $viewer = Engine_Api::_()->user()->getViewer();
      $menuoptions= array();
      $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'blog', 'edit');
      $counter = 0;
      if($canEdit) {
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit");
        $counter++;
      }

      $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'blog', 'delete');
      if($canDelete) {
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete");
      }
      $result['menus'] = $menuoptions;
    }

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist blogs.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }

  public function categoryAction() {

    $params['countBlogs'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'blog')->getCategoriesAssoc();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {

      if($key == '') continue;

      $category = Engine_Api::_()->getItem('blog_category', $key);

      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;

      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');

      //Blogs Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $category->getIdentity(), 'table_name' => 'blogs', 'module_name' => 'blog'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s blog', '%s blogs', $Itemcount), $this->view->locale()->toNumber($Itemcount));

      $counter++;
    }

    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array()));
  }




//   public function browseAction() {
//     if($this->_blogEnabled){
//         $form = new Sesblog_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'blog','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => 'yes','kilometerMiles' => 'yes','hasPhoto' => 'yes'));
//
//         $filterOptions = (array)$this->_getParam('sortss', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));
//     if(!empty($_POST['location'])){
//       $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
//       if($latlng){
//         $_POST['lat'] = $latlng['lat'];
//         $_POST['lng'] = $latlng['lng'];
//       }
//     }
//     $form->populate($_POST);
// 		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1))
// 			unset($filterOptions['mostSPfavourite']);
//
//     $arrayOptions = $filterOptions;
//     $filterOptions = array();
//     foreach ($arrayOptions as $key=>$filterOption) {
//       if(is_numeric($key))
//         $columnValue = $filterOption;
//       else
//         $columnValue = $key;
//       $value = str_replace(array('SP',''), array(' ',' '), $columnValue);
//       $filterOptions[$columnValue] = ucwords($value);
//     }
//     $filterOptions = array(''=>'')+$filterOptions;
//     $form->sort->setMultiOptions($filterOptions);
//     $sort = $this->_getParam('sort','recentlySPcreated');
//     $form->sort->setValue($sort);
//     $options = $form->getValues();
//
//     if (isset($options['sort']) && $options['sort'] != '') {
//       $getParamSort = str_replace('SP', '_', $options['sort']);
//     } else
//       $getParamSort = 'creation_date';
//
//       if (isset($getParamSort)) {
//         switch ($getParamSort) {
//           case 'most_viewed':
//             $options['popularCol'] = 'view_count';
//             break;
//           case 'most_liked':
//             $options['popularCol'] = 'like_count';
//             break;
//           case 'most_commented':
//             $options['popularCol'] = 'comment_count';
//             break;
//           case 'most_favourite':
//             $options['popularCol'] = 'favourite_count';
//             break;
//           case 'sponsored':
//             $options['popularCol'] = 'sponsored';
//             $options['fixedData'] = 'sponsored';
//             break;
//           case 'verified':
//             $options['popularCol'] = 'verified';
//             $options['fixedData'] = 'verified';
//           break;
//           case 'featured':
//             $options['popularCol'] = 'featured';
//             $options['fixedData'] = 'featured';
//             break;
//           case 'most_rated':
//             $options['popularCol'] = 'rating';
//             break;
//           case 'recently_created':
//             default:
//             $options['popularCol'] = 'creation_date';
//             break;
//         }
//       }
//       // Get search params
//       $page = (int)  $this->_getParam('page', 1);
//
//       if(!empty($_POST['search']))
//         $options['text'] = $_POST['search'];
//       if(!empty($_POST['user_id'])){
//         $options['user_id'] = $_POST['user_id'];
//         $condition = array('manage-widget'=>true);
//       }else{
//         $condition = array('status'=>1,'draft'=>0,'visible'=>1);
//       }
//       if(!empty($_POST['owner_id']))
//         $options["user_id"] = $_POST['owner_id'];
//       $paginator = Engine_Api::_()->getDbtable('blogs', 'sesblog')->getSesblogsPaginator(array_merge($options,$condition), $options);
//        $page = (int)  $this->_getParam('page', 1);
//       // Build paginator
//       $paginator->setItemCountPerPage($this->_getParam('limit',10));
//       $paginator->setCurrentPageNumber($page);
//
//       $result = $this->blogResult($paginator);
//
//       if(!empty($_POST['user_id'])){
//         $viewer = Engine_Api::_()->user()->getViewer();
//         $menuoptions= array();
//         $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'edit');
//         $counter = 0;
//         if($canEdit){
//           $menuoptions[$counter]['name'] = "edit";
//           $menuoptions[$counter]['label'] = $this->view->translate("Edit");
//           $counter++;
//         }
//         $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'delete');
//         if($canDelete){
//           $menuoptions[$counter]['name'] = "delete";
//           $menuoptions[$counter]['label'] = $this->view->translate("Delete");
//         }
//         $result['menus'] = $menuoptions;
//       }
//
//       $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
//       $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
//       $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
//       $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
//       if($result <= 0)
//         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist blogs.'), 'result' => array()));
//       else
//         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
//     } else {
//     }
//   }

  function blogResult($paginator) {

    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();

    foreach($paginator as $item) {

      $resource = $item->toArray();
      $description = strip_tags($item['body']);
      $description = preg_replace('/\s+/', ' ', $description);
      unset($resource['body']);
      $resource['owner_title'] = Engine_Api::_()->getItem('user', $resource['owner_id'])->getTitle();
      $resource['body'] = $description;
      $resource['resource_type'] = $item->getType();
      $resource['resource_id'] = $item->getIdentity();

      //Category name
      if(!empty($resource['category_id'])) {
        $category = Engine_Api::_()->getItem('blog_category', $resource['category_id']);
        $resource['category_name'] = $category->category_name;
      }

      // Check content like or not and get like count
      if($this->_blogEnabled) {
        if($viewer->getIdentity() != 0) {
          $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
          $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
        }
      }

      $result['blogs'][$counterLoop] = $resource;
      $images = array();

      if ($item->photo_id)
        $images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl());
      else
        $images['main'] = $this->getBaseUrl(true, "/application/modules/Blog/externals/images/nophoto_blog_thumb_normal.png");
      
      $result['blogs'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }
    return $result;
  }

  public function viewAction() {

    // Check permission
    $viewer = Engine_Api::_()->user()->getViewer();

    $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
    if( $blog ) {
      Engine_Api::_()->core()->setSubject($blog);
    }

    if( !$this->_helper->requireSubject()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    if( !$blog || !$blog->getIdentity() || ($blog->draft && !$blog->isOwner($viewer)) ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Prepare data
    $blogTable = Engine_Api::_()->getDbtable('blogs', 'blog');


    $blog_content = $blog->toArray();

    $body = $this->replaceSrc($blog_content['body']);
    $blog_content['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
    $blog_content['owner_title'] = Engine_Api::_()->getItem('user', $blog_content['owner_id'])->getTitle();
    $blog_content['resource_type'] = $blog->getType();
    $blog_content['resource_id'] = $blog->getType();
    $blog_content['category_id'] = $blog->category_id;

    if( !$blog->isOwner($viewer) ) {
      $blogTable->update(array(
        'view_count' => new Zend_Db_Expr('view_count + 1'),
      ), array(
        'blog_id = ?' => $blog->getIdentity(),
      ));
    }

    // Get tags
    $blogTags = $blog->tags()->getTagMaps();
    if (!empty($blogTags)) {
      foreach ($blogTags as $tag) {
        $blog_content['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
      }
    }

    // Get category
    if( !empty($blog->category_id) ) {
      $category = Engine_Api::_()->getItem('blog_category', $blog->category_id);
      $blog_content['category_title'] = $category->category_name;
    }

    if($this->_blogEnabled) {
      if($viewer->getIdentity() != 0) {
        $blog_content['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($blog);
        $blog_content['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($blog);
      }
    }

    $blog_content['content_url'] = $this->getBaseUrl(false,$blog->getHref());
    $blog_content['can_favorite'] = false;
    $blog_content['can_share'] = false;

    $result['blog'] = $blog_content;

    if($viewer->getIdentity() > 0) {
			$result['blog']['permission']['canEdit'] = $canEdit = $viewPermission = $blog->authorization()->isAllowed($viewer, 'edit') ? true : false;
			$result['blog']['permission']['canComment'] =  $blog->authorization()->isAllowed($viewer, 'comment') ? true : false;
			$result['blog']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'create') ? true : false;
			$result['blog']['permission']['can_delete'] = $canDelete  = $blog->authorization()->isAllowed($viewer,'delete') ? true : false;

      $menuoptions= array();
      $counter = 0;
    
      if($canEdit) {
//         $menuoptions[$counter]['name'] = "changephoto";
//         $menuoptions[$counter]['label'] = $this->view->translate("Change Main Photo");
//         $counter++;
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit Blog");
        $counter++;
      }
      if($canDelete){
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete Blog");
        $counter++;
      }
      if (!$blog->isOwner($viewer)) {
        $menuoptions[$counter]['name'] = "report";
        $menuoptions[$counter]['label'] = $this->view->translate("Report Blog");
      }
      $result['menus'] = $menuoptions;
		}
    
    $result['blog']["share"]["name"] = "share";
    $result['blog']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$blog->getPhotoUrl());
    if($photo)
      $result['blog']["share"]["imageUrl"] = $photo;
    $url = "blogs/" . $blog->getOwner()->getIdentity() . "/" . $blog->getIdentity() . "/" . strtolower(str_replace(" ", "-", $blog->getTitle()));
    $result['blog']["share"]["url"] = $this->getBaseUrl(false,$url);
    $result['blog']["share"]["title"] = $blog->getTitle();
    $result['blog']["share"]["description"] = strip_tags($blog->getDescription());
    $result['blog']["share"]['urlParams'] = array(
        "type" => $blog->getType(),
        "id" => $blog->getIdentity()
    );

    if(is_null($result['blog']["share"]["title"]))
      unset($result['blog']["share"]["title"]);

    $images = Engine_Api::_()->sesapi()->getPhotoUrls($blog,'',"");
    if(!count($images))
      $images['main'] = $this->getBaseUrl(true, $blog->getPhotoUrl());

    $result['blog']['blog_images'] = $images;

    $result['blog']['user_images'] = $this->userImage($blog->owner_id,"thumb.profile");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
  }
    function replaceSrc($html = ""){
        preg_match_all( '@src="([^"]+)"@' , $html, $match );
        foreach(array_pop($match) as $src){
            if(strpos($src,'http://') === false && strpos($src,'https://') === false && strpos($src,'//') === false){
                $baseUrl = str_replace(Zend_Registry::get('StaticBaseUrl'),'',$this->getBaseUrl());
                if(end(explode("",$baseUrl)) != '/')
                  $baseUrl .= '/';
                $html = str_replace($src,$baseUrl.$src,$html);
            }else if(strpos($src,'http://') === false && strpos($src,'https://') === false){
                $html = str_replace($src,'https://'.$src,$html);
            }
        }
        return $html;
    }

//   public function viewAction() {
//
//     $viewer = Engine_Api::_()->user()->getViewer();
//     $id = $this->_getParam('blog_id', null);
//     $this->view->blog_id = $blog_id = Engine_Api::_()->getDbtable('blogs', 'sesblog')->getBlogId($id);
//     if(!Engine_Api::_()->core()->hasSubject())
//       $sesblog = Engine_Api::_()->getItem('sesblog_blog', $blog_id);
//     else
//       $sesblog = Engine_Api::_()->core()->getSubject();
//
//     if( !$this->_helper->requireSubject()->isValid() )
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//
//     if( !$this->_helper->requireAuth()->setAuthParams($sesblog, $viewer, 'view')->isValid() )
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//
//     if( !$sesblog || !$sesblog->getIdentity() || ($sesblog->draft && !$sesblog->isOwner($viewer)) )
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//
//     $blog = $sesblog->toArray();
//     $body = @str_replace('src="/', 'src="' . $this->getBaseUrl() . '/', $blog['body']);
//     $body = preg_replace('/<\/?a[^>]*>/','',$body);
//     $blog['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
//     $blog['owner_title'] = Engine_Api::_()->getItem('user',$blog['owner_id'])->getTitle();
//     $blog['resource_type'] = $sesblog->getType();
//
//
//      // Get tags
//     $blogTags = $sesblog->tags()->getTagMaps();
//     if (!empty($blogTags)) {
//         foreach ($blogTags as $tag) {
//             $blog['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
//         }
//     }
//
//     if($this->_blogEnabled){
//       if($viewer->getIdentity() != 0){
//         $blog['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($sesblog);
//         $blog['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($sesblog);
//         if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1)){
//           $blog['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($sesblog,'favourites','sesblog');
//           $blog['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($sesblog,'favourites','sesblog');
//         }
//       }
//     }
//     if (!$sesblog->isOwner($viewer)) {
//         $sesblog->view_count = $sesblog->view_count + 1;
//         $sesblog->save();
//     }
//
//     $category = Engine_Api::_()->getItem('sesblog_category', $sesblog->category_id);
//     if (!empty($category))
//         $blog['category_title'] = $category->getTitle();
//
//     $subcategory = Engine_Api::_()->getItem('sesblog_category', $sesblog->subcat_id);
//     if (!empty($subcategory))
//         $blog['subcategory_title'] = $subcategory->getTitle();
//
//     $subsubcat = Engine_Api::_()->getItem('sesblog_category', $sesblog->subsubcat_id);
//     if (!empty($subsubcat))
//         $blog['subsubcategory_title'] = $subsubcat->getTitle();
//
//     $blog['content_url'] = $this->getBaseUrl(false,$sesblog->getHref());
//     if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.favourite', 1)){
//       $blog['can_favorite'] = true;
//     }else{
//       $blog['can_favorite'] = false;
//     }
//     if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.share', 1)){
//       $blog['can_share'] = true;
//     }else{
//       $blog['can_share'] = false;
//     }
//     $result['blog'] = $blog;
//     if($viewer->getIdentity() > 0){
// 			$result['blog']['permission']['canEdit'] = $canEdit = $viewPermission = $sesblog->authorization()->isAllowed($viewer, 'edit') ? true : false;
// 			$result['blog']['permission']['canComment'] =  $sesblog->authorization()->isAllowed($viewer, 'comment') ? true : false;
// 			$result['blog']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'create') ? true : false;
// 			$result['blog']['permission']['can_delete'] = $canDelete  = $sesblog->authorization()->isAllowed($viewer,'delete') ? true : false;
//
//       $menuoptions= array();
//       $counter = 0;
//       if($canEdit){
//         $menuoptions[$counter]['name'] = "changephoto";
//         $menuoptions[$counter]['label'] = $this->view->translate("Change Main Photo");
//         $counter++;
//         $menuoptions[$counter]['name'] = "edit";
//         $menuoptions[$counter]['label'] = $this->view->translate("Edit Blog");
//         $counter++;
//       }
//       if($canDelete){
//         $menuoptions[$counter]['name'] = "delete";
//         $menuoptions[$counter]['label'] = $this->view->translate("Delete Blog");
//         $counter++;
//       }
//       if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesblog.enable.report', 1)){
//         $menuoptions[$counter]['name'] = "report";
//         $menuoptions[$counter]['label'] = $this->view->translate("Report Blog");
//       }
//       $result['menus'] = $menuoptions;
// 		}
//
//     $result['blog']["share"]["name"] = "share";
//     $result['blog']["share"]["label"] = $this->view->translate("Share");
//     $photo = $this->getBaseUrl(false,$sesblog->getPhotoUrl());
//     if($photo)
//     $result['blog']["share"]["imageUrl"] = $photo;
//     $result['blog']["share"]["title"] = $sesblog->getTitle();
//     $result['blog']["share"]["description"] = strip_tags($sesblog->getDescription());
//     $result['blog']["share"]['urlParams'] = array(
//         "type" => $sesblog->getType(),
//         "id" => $sesblog->getIdentity()
//     );
//     if(is_null($result['blog']["share"]["title"]))
//       unset($result['blog']["share"]["title"]);
//
//     $images = Engine_Api::_()->sesapi()->getPhotoUrls($sesblog,'',"");
//     if(!count($images))
//       $images['main'] = $this->getBaseUrl(true,$sesblog->getPhotoUrl());
//     $result['blog']['blog_images'] = $images;
//
//     $result['blog']['user_images'] = $this->userImage($sesblog->owner_id,"thumb.profile");
//     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
//   }

  public function createAction() {

    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator($values);
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'blog', 'max');
    $paginator->getTotalItemCount();
    $current_count = $paginator->getTotalItemCount();
    if (($current_count >= $quota) && !empty($quota)) {
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of blogs allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error' => '1', 'error_message' => $message, 'result' => array()));
    }

    // Prepare form
    $form = new Blog_Form_Create();
    $form->removeElement('token');

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'blog'));
    }

    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $formFields[4]['name'] = "file";
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }


    // Process
    $table = Engine_Api::_()->getItemTable('blog');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      // Create blog
      $viewer = Engine_Api::_()->user()->getViewer();
      $formValues = $form->getValues();

      if( empty($values['auth_view']) ) {
        $formValues['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $formValues['auth_comment'] = 'everyone';
      }

      $values = array_merge($formValues, array(
        'owner_type' => $viewer->getType(),
        'owner_id' => $viewer->getIdentity(),
        'view_privacy' => $formValues['auth_view'],
      ));

      $blog = $table->createRow();
      $blog->setFromArray($values);
      $blog->save();

      if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) {
        $this->setPhoto($_FILES['image'],$blog);
      }

      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $blog->tags()->addTagMaps($viewer, $tags);

      // Add activity only if blog is published
      if( $values['draft'] == 0 ) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new');
        // make sure action exists before attaching the blog to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $blog);
        }
      }

      // Send notifications for subscribers
      Engine_Api::_()->getDbtable('subscriptions', 'blog')->sendNotifications($blog);

      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('blog_id' => $blog->getIdentity(),'message' => $this->view->translate('Blog created successfully.'))));
  }

  public function setPhoto($photo,$blog)
  {
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
    } elseif( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
    } elseif( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
    } else {
      throw new Blog_Model_Exception('Invalid argument passed to setPhoto: ' . print_r($photo, 1));
    }

    $name = basename($photo['name']);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => 'blog',
      'parent_id' => $blog->getIdentity()
    );

    // Save
    $storage = Engine_Api::_()->storage();

    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(720, 720)
      ->write($path . '/m_' . $name)
      ->destroy();

    // Resize image (profile)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(200, 400)
      ->write($path . '/p_' . $name)
      ->destroy();

    // Resize image (normal)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(140, 160)
      ->write($path . '/in_' . $name)
      ->destroy();

    // Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
      ->write($path . '/is_' . $name)
      ->destroy();

    // Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    $iProfile = $storage->create($path . '/p_' . $name, $params);
    $iIconNormal = $storage->create($path . '/in_' . $name, $params);
    $iSquare = $storage->create($path . '/is_' . $name, $params);

    $iMain->bridge($iProfile, 'thumb.profile');
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare, 'thumb.icon');

    // Remove temp files
    @unlink($path . '/p_' . $name);
    @unlink($path . '/m_' . $name);
    @unlink($path . '/in_' . $name);
    @unlink($path . '/is_' . $name);

    // Update row
    $blog->modified_date = date('Y-m-d H:i:s');
    $blog->photo_id = $iMain->getIdentity();
    $blog->save();

    return $blog;
  }
  public function editAction() {

    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();
    $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
    if(!Engine_Api::_()->core()->hasSubject('blog') ) {
      Engine_Api::_()->core()->setSubject($blog);
    }

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'edit')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Prepare form
    $form = new Blog_Form_Edit();
    $form->removeElement('token');

    // Populate form
    $form->populate($blog->toArray());

    $tagStr = '';
    foreach( $blog->tags()->getTagMaps() as $tagMap ) {
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
        if( $auth->isAllowed($blog, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($blog, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }
    }

    // hide status change if it has been already published
    if( $blog->draft == "0" ) {
      $form->removeElement('draft');
    }
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $formFields[4]['name'] = "file";
      $this->generateFormFields($formFields,array('resources_type'=>'blog'));
    }
    // Check post/form
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }

    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }
      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $values['view_privacy'] = $values['auth_view'];

      $blog->setFromArray($values);
      $blog->modified_date = date('Y-m-d H:i:s');
      $blog->save();

      // Add photo
      if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) {
        $this->setPhoto($_FILES['image'],$blog);
      }

      // Auth
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
      }

      // handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $blog->tags()->setTagMaps($viewer, $tags);

      // insert new activity if blog is just getting published
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($blog);
      if( count($action->toArray()) <= 0 && $values['draft'] == '0' ) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new');
          // make sure action exists before attaching the blog to the activity
        if( $action != null ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $blog);
        }
      }

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($blog) as $action ) {
        $actionTable->resetActivityBindings($action);
      }

      // Send notifications for subscribers
      Engine_Api::_()->getDbtable('subscriptions', 'blog')->sendNotifications($blog);
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('blog_id' => $blog->getIdentity(),'message' => $this->view->translate('Blog edited successfully.'))));
  }


  public function deleteAction() {

    $blog = Engine_Api::_()->getItem('blog', $this->getRequest()->getParam('blog_id'));

    if( !$this->_helper->requireAuth()->setAuthParams($blog, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $form = new Blog_Form_Delete();
    if( !$blog ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_("Blog entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    if( !$this->getRequest()->isPost() ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    $db = $blog->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $blog->delete();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('Your sesblog entry has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }
}
