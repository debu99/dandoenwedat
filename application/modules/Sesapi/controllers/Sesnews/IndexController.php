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
class Sesnews_IndexController extends Sesapi_Controller_Action_Standard
{
  protected $_sesnewsEnabled;
  public function init() {
		// only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('sesnews_news', null, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $id = $this->_getParam('news_id', $this->_getParam('id', null));
    $this->isSesnewsEnable();
    if($this->_sesnewsEnabled){
      $news_id = Engine_Api::_()->getDbtable('news', 'sesnews')->getNewsId($id);
      if ($news_id) {
        $news = Engine_Api::_()->getItem('sesnews_news', $news_id);
        if ($news) {
            Engine_Api::_()->core()->setSubject($news);
        }
      }
    }else{
      if ($id) {
        $news = Engine_Api::_()->getItem('news', $id);
        if ($news) {
            Engine_Api::_()->core()->setSubject($news);
        }
      }
    }
  }
 protected function isSesnewsEnable(){
   $this->_sesnewsEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesnews');
 }
  public function searchFormAction(){
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews_enable_location', 1))
        $location = 'yes';
        else
        $location = 'no';

       $form = new Sesnews_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'news','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => $location,'kilometerMiles' => 'yes','hasPhoto' => 'yes'));
       $form->removeElement('lat');
       $form->removeElement('lng');

       $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));

		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.favourite', 1))
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
  
  public function rssviewAction() {
      
    $rss_id = $this->_getParam('rss_id', null);
    $rss = Engine_Api::_()->getItem('sesnews_rss', $rss_id);

    $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('news', 'sesnews')->getSesnewsPaginator(array('rss_id' => $rss->rss_id));
    $page = (int)  $this->_getParam('page', 1);
    
    // Build paginator
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($page);
    $result = $this->newsResult($paginator);
    
    $rss_title = strip_tags($rss->title);
    $rss_title = preg_replace('/\s+/', ' ', $rss_title);
    $result['rss_title'] = $rss_title;

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist any news in this Rss.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  
  public function browserssAction() {
 
    $form = new Sesnews_Form_SearchRss(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'news','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked'));

    $filterOptions = (array)$this->_getParam('sortss', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed'));
    
    $form->populate($_POST);

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
        $condition = array('browse-widget'=>true);
      }else{
        $condition = array('status'=>1,'draft'=>0,'visible'=>1);
      }
      
      $owner_id = $this->_getParam('owner_id');
      if(!empty($owner_id))
        $options["user_id"] = $owner_id;
        
      $paginator = Engine_Api::_()->getDbtable('rss', 'sesnews')->getRssPaginator(array_merge($options,$condition));
      
       $page = (int)  $this->_getParam('page', 1);
      // Build paginator
      $paginator->setItemCountPerPage($this->_getParam('limit',10));
      $paginator->setCurrentPageNumber($page);

      $result = $this->rssResult($paginator);

      if(!empty($owner_id)){
        $viewer = Engine_Api::_()->user()->getViewer();
        $menuoptions= array();
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'sesnews_rss', 'edit');
        $counter = 0;
        if($canEdit){
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit");
          $counter++;
        }
        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'sesnews_rss', 'delete');
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
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist rss.'), 'result' => array()));
      else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  function rssResult($paginator){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      foreach($paginator as $rss_item){
     
        $rss = $rss_item->toArray();
        $description = strip_tags($rss['body']);
        $description = preg_replace('/\s+/', ' ', $description);
        unset($rss['body']);
        $rss['owner_title'] = Engine_Api::_()->getItem('user',$rss['owner_id'])->getTitle();
        $rss['body'] = $description;
        $rss['resource_type'] = $rss_item->getType();

        $result['rss_list'][$counterLoop] = $rss;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($rss_item,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$rss_item->getPhotoUrl());
        $result['rss_list'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }
      return $result;
  }
  public function browseAction() {
 
    if($this->_sesnewsEnabled){
        $form = new Sesnews_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'news','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => 'yes','kilometerMiles' => 'yes','hasPhoto' => 'yes'));

        $filterOptions = (array)$this->_getParam('sortss', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));
    if(!empty($_POST['location'])){
      $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
      if($latlng){
        $_POST['lat'] = $latlng['lat'];
        $_POST['lng'] = $latlng['lng'];
      }
    }
    $form->populate($_POST);
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.favourite', 1))
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
      $paginator = Engine_Api::_()->getDbtable('news', 'sesnews')->getSesnewsPaginator(array_merge($options,$condition), $options);
      
       $page = (int)  $this->_getParam('page', 1);
      // Build paginator
      $paginator->setItemCountPerPage($this->_getParam('limit',10));
      $paginator->setCurrentPageNumber($page);

      $result = $this->newsResult($paginator);

      if(!empty($_POST['user_id'])){
        $viewer = Engine_Api::_()->user()->getViewer();
        $menuoptions= array();
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'sesnews_news', 'edit');
        $counter = 0;
        if($canEdit){
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit");
          $counter++;
        }
        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'sesnews_news', 'delete');
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
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist news.'), 'result' => array()));
      else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    }else{

    }
  }
  function newsResult($paginator){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesnews')){
        $sesnews = true;
      }
      foreach($paginator as $news_item){
     
        $news = $news_item->toArray();
        $description = strip_tags($news['body']);
        $description = preg_replace('/\s+/', ' ', $description);
        unset($news['body']);
        $news["comment_count"] = Engine_Api::_()->sesadvancedcomment()->commentCount($news_item,'subject');
        $news['owner_title'] = Engine_Api::_()->getItem('user',$news['owner_id'])->getTitle();
        $news['body'] = str_replace('"', ' ', $description);
        $news['resource_type'] = $news_item->getType();

        if($this->_sesnewsEnabled){
          if($viewer->getIdentity() != 0){
            $news['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($news_item);
            $news['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($news_item);
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.favourite', 1)){
              $news['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($news_item,'favourites','sesnews');
              $news['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($news_item,'favourites','sesnews');
            }
          }
        }
        $result['news_list'][$counterLoop] = $news;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($news_item,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$news_item->getPhotoUrl());
        $result['news_list'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }
      return $result;
  }
  public function categoryAction(){
    $params['countNews'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'sesnews')->getCategory($params);
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
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s news', '%s news', $category->total_news_categories), $this->view->locale()->toNumber($category->total_news_categories));

      $counter++;
    }
    if($catgeoryArray <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array()));
    else
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array()));
  }
  //Browse News Contributors
  public function contributorsAction() {
    // Render
    $this->_helper->content->setEnabled();
  }



  public function claimAction() {

		$viewer = Engine_Api::_()->user()->getViewer();
		if( !$viewer || !$viewer->getIdentity() )
		if( !$this->_helper->requireUser()->isValid() ) return;

    if(!Engine_Api::_()->authorization()->getPermission($viewer, 'sesnews_claim', 'create') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.claim', 1))
    return $this->_forward('requireauth', 'error', 'core');

    // Render
    $this->_helper->content->setEnabled();
  }

  public function claimRequestsAction() {

    $checkClaimRequest = Engine_Api::_()->getDbTable('claims', 'sesnews')->claimCount();
    if(!$checkClaimRequest)
    return $this->_forward('notfound', 'error', 'core');
    // Render
    $this->_helper->content->setEnabled();
  }


  public function viewAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $id = $this->_getParam('news_id', null);
    $this->view->news_id = $news_id = Engine_Api::_()->getDbtable('news', 'sesnews')->getNewsId($id);
    if(!Engine_Api::_()->core()->hasSubject())
      $sesnews = Engine_Api::_()->getItem('sesnews_news', $news_id);
    else
      $sesnews = Engine_Api::_()->core()->getSubject();

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams($sesnews, $viewer, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$sesnews || !$sesnews->getIdentity() || ($sesnews->draft && !$sesnews->isOwner($viewer)) )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $news = $sesnews->toArray();
      $body = $this->replaceSrc($sesnews['body']);
    $news['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
    $news['owner_title'] = Engine_Api::_()->getItem('user',$news['owner_id'])->getTitle();
    $news['resource_type'] = $sesnews->getType();


     // Get tags
    $newsTags = $sesnews->tags()->getTagMaps();
    if (!empty($newsTags)) {
        foreach ($newsTags as $tag) {
            $news['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
        }
    }

    if($this->_sesnewsEnabled){
      if($viewer->getIdentity() != 0){
        $news['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($sesnews);
        $news['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($sesnews);
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.favourite', 1)){
          $news['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($sesnews,'favourites','sesnews');
          $news['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($sesnews,'favourites','sesnews');
        }
      }
    }
    if (!$sesnews->isOwner($viewer)) {
        $sesnews->view_count = $sesnews->view_count + 1;
        $sesnews->save();
    }

    $category = Engine_Api::_()->getItem('sesnews_category', $sesnews->category_id);
    if (!empty($category))
        $news['category_title'] = $category->getTitle();

    $subcategory = Engine_Api::_()->getItem('sesnews_category', $sesnews->subcat_id);
    if (!empty($subcategory))
        $news['subcategory_title'] = $subcategory->getTitle();

    $subsubcat = Engine_Api::_()->getItem('sesnews_category', $sesnews->subsubcat_id);
    if (!empty($subsubcat))
        $news['subsubcategory_title'] = $subsubcat->getTitle();

    $news['content_url'] = $this->getBaseUrl(false,$sesnews->getHref());
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.favourite', 1)){
      $news['can_favorite'] = true;
    }else{
      $news['can_favorite'] = false;
    }
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.share', 1)){
      $news['can_share'] = true;
    }else{
      $news['can_share'] = false;
    }
    $result['news'] = $news;
    if($viewer->getIdentity() > 0){
			$result['news']['permission']['canEdit'] = $canEdit = $viewPermission = $sesnews->authorization()->isAllowed($viewer, 'edit') ? true : false;
			$result['news']['permission']['canComment'] =  $sesnews->authorization()->isAllowed($viewer, 'comment') ? true : false;
			$result['news']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesnews_news', 'create') ? true : false;
			$result['news']['permission']['can_delete'] = $canDelete  = $sesnews->authorization()->isAllowed($viewer,'delete') ? true : false;

      $menuoptions= array();
      $counter = 0;
      if($canEdit){
        $menuoptions[$counter]['name'] = "changephoto";
        $menuoptions[$counter]['label'] = $this->view->translate("Change Main Photo");
        $counter++;
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit News");
        $counter++;
      }
      if($canDelete){
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete News");
        $counter++;
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.enable.report', 1)){
        $menuoptions[$counter]['name'] = "report";
        $menuoptions[$counter]['label'] = $this->view->translate("Report News");
      }
      $result['menus'] = $menuoptions;
		}

    $result['news']["share"]["name"] = "share";
    $result['news']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$sesnews->getPhotoUrl());
    if($photo)
    $result['news']["share"]["imageUrl"] = $photo;
		$result['news']["share"]["url"] = $this->getBaseUrl(false,$sesnews->getHref());
    $result['news']["share"]["title"] = $sesnews->getTitle();
    $result['news']["share"]["description"] = strip_tags($sesnews->getDescription());
    $result['news']["share"]['urlParams'] = array(
        "type" => $sesnews->getType(),
        "id" => $sesnews->getIdentity()
    );
    if(is_null($result['news']["share"]["title"]))
      unset($result['news']["share"]["title"]);

    $images = Engine_Api::_()->sesapi()->getPhotoUrls($sesnews,'',"");
    if(!count($images))
      $images['main'] = $this->getBaseUrl(true,$sesnews->getPhotoUrl());
    $result['news']['news_images'] = $images;

    $result['news']['user_images'] = $this->userImage($sesnews->owner_id,"thumb.profile");
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
    if( !$this->_helper->requireAuth()->setAuthParams('sesnews_news', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
		$viewer = Engine_Api::_()->user()->getViewer();
		/*if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesnewspackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnewspackage.enable.package', 1)){
			$package = Engine_Api::_()->getItem('sesnewspackage_package',$this->_getParam('package_id',0));
			$existingpackage = Engine_Api::_()->getItem('sesnewspackage_orderspackage',$this->_getParam('existing_package_id',0));
			if($existingpackage){
				$package = Engine_Api::_()->getItem('sesnewspackage_package',$existingpackage->package_id);
			}
			if (!$package && !$existingpackage){
				//check package exists for this member level
				$packageMemberLevel = Engine_Api::_()->getDbTable('packages','sesnewspackage')->getPackage(array('member_level'=>$viewer->level_id));
				if(count($packageMemberLevel)){
					//redirect to package page
					return $this->_helper->redirector->gotoRoute(array('action'=>'news'), 'sesnewspackage_general', true);
				}
			}
		}*/
    $session = new Zend_Session_Namespace();
		if(empty($_POST))
		unset($session->album_id);
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesnews')->profileFieldId();

    // set up data needed to check quota
    $parentType = $this->_getParam('parent_type', null);
    if($parentType)
    $event_id = $this->_getParam('event_id', null);

    $parentId = $this->_getParam('parent_id', 0);
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getDbtable('news', 'sesnews')->getSesnewsPaginator($values);

    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesnews_news', 'max');
    $this->view->current_count = $paginator->getTotalItemCount();
    if (($this->view->current_count >= $quota) && !empty($quota)) {
        // return error message
        $message = $this->view->translate('You have already uploaded the maximum number of news allowed.');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
      }
    $this->view->categories = Engine_Api::_()->getDbtable('categories', 'sesnews')->getCategoriesAssoc();

    // Prepare form
    $this->view->form = $form = new Sesnews_Form_Create(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));
    $form->removeElement('lat');
    $form->removeElement('map-canvas');
    $form->removeElement('ses_location');
    $form->removeElement('lng');
    $form->removeElement('fancyuploadfileids');
    $form->removeElement('tabs_form_newscreate');
    $form->removeElement('file_multi');
    $form->removeElement('from-url');
    $form->removeElement('drag-drop');
    $form->removeElement('uploadFileContainer');
    $form->removeElement('newstyle');
    $form->removeElement('submit_check');
    $form->removeElement('news_custom_datetimes');
      // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    if(!empty($_POST['custom_url_news']))
      $_POST['custom_url'] = $_POST['custom_url_news'];
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
      $custom_url = Engine_Api::_()->getDbtable('news', 'sesnews')->checkCustomUrl($_POST['custom_url']);
      if ($custom_url) {
				$form->addError($this->view->translate("Custom Url is not available. Please select another URL."));
        $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
        if(count($validateFields))
          $this->validateFormFields($validateFields);
      }
    }

    $mainPhotoEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesnews.photo.mandatory', '1');
		if ($mainPhotoEnable == 1 && empty($_FILES['image']['size'])) {
			$form->addError($this->view->translate("Please upload main photo"));
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
		}

    // Process
    $table = Engine_Api::_()->getDbtable('news', 'sesnews');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {

      // Create sesnews
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
      $sesnews = $table->createRow();
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
				if(isset($sesnews->package_id) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesnewspackage') ){
					$values['package_id'] = Engine_Api::_()->getDbTable('packages','sesnewspackage')->getDefaultPackage();
				}
			}
			if(empty($_POST['draft'])){
        $values['draft'] = 0;
      }
			if($_POST['newstyle'])
        $values['style'] = $_POST['newstyle'];

      //SEO By Default Work
      $values['seo_title'] = $values['title'];
			if($values['tags'])
			  $values['seo_keywords'] = $values['tags'];

      $sesnews->setFromArray($values);

      if(!empty($_FILES['image']['size'])){
        $sesnews->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesnews','sesnews_news','',$sesnews,true);
      }

			if(isset($starttime) && $starttime != ''){
				$starttime = isset($starttime) ? date('Y-m-d H:i:s',strtotime($starttime)) : '';
      	$sesnews->publish_date =$starttime;
			}

			if(isset($starttime) && $viewer->timezone && $starttime != ""){
				//Convert Time Zone
				$oldTz = date_default_timezone_get();
				date_default_timezone_set($viewer->timezone);
				$start = strtotime($starttime);
				date_default_timezone_set($oldTz);
				$sesnews->publish_date = date('Y-m-d H:i:s', $start);
			}else{
				$sesnews->publish_date = date('Y-m-d H:i:s',strtotime("-2 minutes", time()));
			}
			$sesnews->parent_id = $parentId;
      $sesnews->save();
      $news_id = $sesnews->news_id;

      if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
      $sesnews->custom_url = $_POST['custom_url'];
      else
      $sesnews->custom_url = $sesnews->news_id;
      $sesnews->save();
      $news_id = $sesnews->news_id;

      $roleTable = Engine_Api::_()->getDbtable('roles', 'sesnews');
			$row = $roleTable->createRow();
			$row->news_id = $news_id;
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
					Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $news_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesnews_news")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }

      if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
				$sesnews->photo_id = $_POST['cover'];
				$sesnews->save();
      }

      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
				$customfieldform->setItem($sesnews);
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
        $auth->setAllowed($sesnews, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($sesnews, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($sesnews, $role, 'video', ($i <= $videoMax));
        $auth->setAllowed($sesnews, $role, 'music', ($i <= $musicMax));
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
     // $sesnews->seo_keywords = implode(',',$tags);
      //$sesnews->seo_title = $sesnews->title;
      $sesnews->save();
      $sesnews->tags()->addTagMaps($viewer, $tags);

      $session = new Zend_Session_Namespace();
      if(!empty($session->album_id)){
				$album_id = $session->album_id;
				if(isset($news_id) && isset($sesnews->title)){
					Engine_Api::_()->getDbTable('albums', 'sesnews')->update(array('news_id' => $news_id,'owner_id' => $viewer->getIdentity(),'title' => $sesnews->title), array('album_id = ?' => $album_id));
					if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
						Engine_Api::_()->getDbTable('albums', 'sesnews')->update(array('photo_id' => $_POST['cover']), array('album_id = ?' => $album_id));
					}
					Engine_Api::_()->getDbTable('photos', 'sesnews')->update(array('news_id' => $news_id), array('album_id = ?' => $album_id));
					unset($session->album_id);
				}
      }

      // Add activity only if sesnews is published
      if( $values['draft'] == 0 && $values['is_approved'] == 1 && (!$sesnews->publish_date || strtotime($sesnews->publish_date) <= time())) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesnews, 'sesnews_new');
        // make sure action exists before attaching the sesnews to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesnews);
        }
//         //Send notifications for subscribers
//       	Engine_Api::_()->getDbtable('subscriptions', 'sesnews')->sendNotifications($sesnews);
      	$sesnews->is_publish = 1;
      	$sesnews->save();
			}
      // Commit
      $db->commit();
    }

    catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('news_id'=>$sesnews->getIdentity(),'message'=>$this->view->translate('News created successfully.'))));
  }
  
  public function createrssAction()
  {
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireAuth()->setAuthParams('sesnews_rss', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Render
    //$this->_helper->content->setEnabled();

    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();

    $paginator = Engine_Api::_()->getItemTable('sesnews_rss')->getRssPaginator($values);

    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesnews_rss', 'max');

    $this->view->current_count = $paginator->getTotalItemCount();

    if (isset($sesnews->category_id) && $sesnews->category_id != 0) {
      $this->view->category_id = $sesnews->category_id;
    } else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
      $this->view->category_id = $_POST['category_id'];
    else
      $this->view->category_id = 0;
    if (isset($sesnews->subsubcat_id) && $sesnews->subsubcat_id != 0) {
      $this->view->subsubcat_id = $sesnews->subsubcat_id;
    } else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
      $this->view->subsubcat_id = $_POST['subsubcat_id'];
    else
      $this->view->subsubcat_id = 0;
    if (isset($sesnews->subcat_id) && $sesnews->subcat_id != 0) {
      $this->view->subcat_id = $sesnews->subcat_id;
    } else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
      $this->view->subcat_id = $_POST['subcat_id'];
    else
      $this->view->subcat_id = 0;

    // Prepare form
    $this->view->form = $form = new Sesnews_Form_CreateRss(array('fromApi'=>true));
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    
    // If not post or form not valid, return
//     if( !$this->getRequest()->isPost() ) {
//       return;
//     }

    // Check if valid
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }


    // Process
    $table = Engine_Api::_()->getItemTable('sesnews_rss');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      // Create rss
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
        'comment_privacy' => $formValues['auth_comment'],
      ));

      $rss = $table->createRow();
      $rss->setFromArray($values);
      $rss->save();
	  
	  if(!empty($_FILES['image']['size'])){
        $rss->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesnews','sesnews_rss','',$rss,true);
		$rss->save();
      }
 
      //if( !empty($values['photo']) ) {
       // $rss->setPhoto($form->photo);
      //}

      $rss->is_approved = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesnews_rss', $viewer, 'rss_approve');
      $rss->save();

    if(isset($_POST['start_date']) && $_POST['start_date'] != ''){
        $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
        $rss->publish_date =$starttime;
    }

    if(isset($_POST['start_date']) && $viewer->timezone && $_POST['start_date'] != ''){
        //Convert Time Zone
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($viewer->timezone);
        $start = strtotime($_POST['start_date'].' '.$_POST['start_time']);
        date_default_timezone_set($oldTz);
        $rss->publish_date = date('Y-m-d H:i:s', $start);
    }

      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($rss, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($rss, $role, 'comment', ($i <= $commentMax));
      }

      // Add activity only if blog is published
//       if( $values['draft'] == 0 ) {
//         $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $rss, 'sesnews_rss_new');
//
//         // make sure action exists before attaching the blog to the activity
//         if( $action ) {
//           Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $rss);
//         }
//       }
//
//       // Send notifications for subscribers
//       Engine_Api::_()->getDbtable('subscriptions', 'blog')
//           ->sendNotifications($rss);

      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('rss_id'=>$rss->getIdentity(),'message'=>$this->view->translate('Rss created successfully.'))));
  }

  public function deleterssAction() {

    $rss = Engine_Api::_()->getItem('sesnews_rss', $this->getRequest()->getParam('rss_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($rss, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$rss ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("RSS entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    if( !$this->getRequest()->isPost() ) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }
    $db = $rss->getTable()->getAdapter();
    $db->beginTransaction();

    try {
        $allNews = Engine_Api::_()->getDbTable('news', 'sesnews')->getAllNews($rss->rss_id);
        if(count($allNews) > 0) {
          foreach($allNews as $news) {
              Engine_Api::_()->sesnews()->deleteNews($news);
          }
        }
        $rss->delete();
        $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your rss entry has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
  }
  
  public function deleteAction() {
    $sesnews = Engine_Api::_()->getItem('sesnews_news', $this->getRequest()->getParam('news_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($sesnews, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$sesnews ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Sesnews entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

    if( !$this->getRequest()->isPost() ) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }
    $db = $sesnews->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      Engine_Api::_()->sesnews()->deleteNews($sesnews);;
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your sesnews entry has been deleted.');
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
        'parent_type' => 'sesnews_news',
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
        throw new Sesnews_Model_Exception($e->getMessage(), $e->getCode());
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
  

  public function editrssAction() {

    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    //$is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    
    $rss_id = $this->_getParam('rss_id', null);
    $rss = Engine_Api::_()->getItem('sesnews_rss', $rss_id);
    
    //$this->view->news = $rss = Engine_Api::_()->core()->getSubject();
    if (isset($rss->category_id) && $rss->category_id != 0)
    $this->view->category_id = $rss->category_id;
    else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
    $this->view->category_id = $_POST['category_id'];
    else
    $this->view->category_id = 0;
    if (isset($rss->subsubcat_id) && $rss->subsubcat_id != 0)
    $this->view->subsubcat_id = $rss->subsubcat_id;
    else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
    $this->view->subsubcat_id = $_POST['subsubcat_id'];
    else
    $this->view->subsubcat_id = 0;
    if (isset($rss->subcat_id) && $rss->subcat_id != 0)
    $this->view->subcat_id = $rss->subcat_id;
    else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
    $this->view->subcat_id = $_POST['subcat_id'];
    else
    $this->view->subcat_id = 0;

    $viewer = Engine_Api::_()->user()->getViewer();
    
    
//     if( !Engine_Api::_()->core()->hasSubject('sesnews_rss') )
//         Engine_Api::_()->core()->setSubject($rss);
      
//     if( !$this->_helper->requireAuth()->setAuthParams('sesnews_rss', $viewer, 'edit')->isValid() )
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $this->view->categories = Engine_Api::_()->getDbtable('categories', 'sesnews')->getCategoriesAssoc();

    // Prepare form
    $this->view->form = $form = new Sesnews_Form_EditRss();

    // Populate form
    $form->populate($rss->toArray());


    if($form->getElement('category_id'))
        $form->getElement('category_id')->setValue($rss->category_id);

    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    foreach( $roles as $role ) {
      if ($form->auth_view){
        if( $auth->isAllowed($rss, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($rss, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }
    }

    // hide status change if it has been already published
    if( $rss->draft == "0" )
        $form->removeElement('draft');


    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
       //set subcategory
      $newFormFieldsArray = array();

      if(count($formFields) && $rss->category_id){
          foreach($formFields as $fields){
            foreach($fields as $field){
                $subcat = array();
                if($fields['name'] == "subcat_id"){
                  $subcat = Engine_Api::_()->getItemTable('sesnews_category')->getModuleSubcategory(array('category_id'=>$rss->category_id,'column_name'=>'*'));
                }else if($fields['name'] == "subsubcat_id"){
                  if($rss->subcat_id)
                  $subcat = Engine_Api::_()->getItemTable('sesnews_category')->getModuleSubSubcategory(array('category_id'=>$rss->subcat_id,'column_name'=>'*'));
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

    if( !$form->isValid($this->getRequest()->getPost()) ) {
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
        unset($values['rss_link']);
        $rss->setFromArray($values);
        $rss->modified_date = date('Y-m-d H:i:s');
        if(isset($_POST['start_date']) && $_POST['start_date'] != ''){
            $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
            $rss->publish_date =$starttime;
        }

        $rss->save();

        if(isset($values['draft']) && !$values['draft']) {
            $currentDate = date('Y-m-d H:i:s');
            if($rss->publish_date < $currentDate) {
                $rss->publish_date = $currentDate;
                $rss->save();
            }
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
      foreach( $roles as $i => $role ) {
        $auth->setAllowed($rss, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($rss, $role, 'comment', ($i <= $commentMax));
      }

      if( !empty($values['photo']) ) {
        $rss->setPhoto($form->photo);
      }

      // insert new activity if sesnews is just getting published
//       $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($rss);
//       if( count($action->toArray()) <= 0 && $values['draft'] == '0' && (!$rss->publish_date || strtotime($rss->publish_date) <= time())) {
//         $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $rss, 'sesnews_new');
//           // make sure action exists before attaching the sesnews to the activity
//         if( $action != null ) {
//           Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $rss);
//         }
//         $rss->is_publish = 1;
//       	$rss->save();
//       }
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message' => $e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('rss_id' => $rss->getIdentity(),'message'=>$this->view->translate('Rss Edit successfully.'))));
  }
  
  
  public function editAction(){
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $this->view->news = $sesnews = Engine_Api::_()->core()->getSubject();


    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesnews')->profileFieldId();

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('sesnews_news', $viewer, 'edit')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


    // Prepare form
    $this->view->form = $form = new Sesnews_Form_Edit(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));

    // Populate form
    $form->populate($sesnews->toArray());

    $tagStr = '';
    foreach( $sesnews->tags()->getTagMaps() as $tagMap ) {
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
        if( $auth->isAllowed($sesnews, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($sesnews, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }

      if ($form->auth_video){
        if( $auth->isAllowed($sesnews, $role, 'video') ) {
          $form->auth_video->setValue($role);
        }
      }

      if ($form->auth_music){
        if( $auth->isAllowed($sesnews, $role, 'music') ) {
          $form->auth_music->setValue($role);
        }
      }
    }

    // hide status change if it has been already published
    if( $sesnews->draft == "0" )
      $form->removeElement('draft');

    $form->removeElement('lat');
    $form->removeElement('map-canvas');
    $form->removeElement('ses_location');
    $form->removeElement('lng');
    $form->removeElement('fancyuploadfileids');
    $form->removeElement('tabs_form_newscreate');
    $form->removeElement('file_multi');
    $form->removeElement('from-url');
    $form->removeElement('drag-drop');
    $form->removeElement('uploadFileContainer');
    $form->removeElement('newstyle');
    $form->removeElement('submit_check');
    $form->removeElement('news_custom_datetimes');
      // Check if post and populate
    if($this->_getParam('getForm')) {
      if(isset($sesnews) && $form->starttime){
				$start = strtotime($sesnews->publish_date);
				$start_date = date('m/d/Y',($start));
				$start_time = date('g:ia',($start));
				$viewer = Engine_Api::_()->user()->getViewer();
				$publishDate = $start_date.' '.$start_time;
        $start_date_y = date('Y',strtotime($start_date));
        $start_date_m = date('m',strtotime($start_date));
        $start_date_d = date('d',strtotime($start_date));
				if($viewer->timezone){
					$start = strtotime($sesnews->publish_date);
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

          if(count($formFields) && $sesnews->category_id){
              foreach($formFields as $fields){
                foreach($fields as $field){
                    $subcat = array();
                    if($fields['name'] == "subcat_id"){
                      $subcat = Engine_Api::_()->getItemTable('sesnews_category')->getModuleSubcategory(array('category_id'=>$sesnews->category_id,'column_name'=>'*'));
                    }else if($fields['name'] == "subsubcat_id"){
                      if($sesnews->subcat_id)
                      $subcat = Engine_Api::_()->getItemTable('sesnews_category')->getModuleSubSubcategory(array('category_id'=>$sesnews->subcat_id,'column_name'=>'*'));
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
      $sesnews->setFromArray($values);
      $sesnews->modified_date = date('Y-m-d H:i:s');
			if(isset($starttime) && $starttime != ''){
				$starttime = isset($starttime) ? date('Y-m-d H:i:s',strtotime($starttime)) : '';
      	$sesnews->publish_date =$starttime;
			}
			//else{
			//	$sesnews->publish_date = '';
			//}
      $sesnews->save();
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
	Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('news_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesnews_news") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }

      if(isset($values['draft']) && !$values['draft']) {
        $currentDate = date('Y-m-d H:i:s');
        if($sesnews->publish_date < $currentDate) {
          $sesnews->publish_date = $currentDate;
          $sesnews->save();
        }
      }
      if(!empty($_FILES['image']['size'])){
        $sesnews->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesnews','sesnews_news','',$sesnews,true);
        $sesnews->save();
      }
      // Add fields
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
        $customfieldform->setItem($sesnews);
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
        $auth->setAllowed($sesnews, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($sesnews, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($sesnews, $role, 'video', ($i <= $videoMax));
        $auth->setAllowed($sesnews, $role, 'music', ($i <= $musicMax));
      }

      // handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $sesnews->tags()->setTagMaps($viewer, $tags);

      // insert new activity if sesnews is just getting published
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($sesnews);
      if( count($action->toArray()) <= 0 && $values['draft'] == '0' && (!$sesnews->publish_date || strtotime($sesnews->publish_date) <= time())) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesnews, 'sesnews_new');
          // make sure action exists before attaching the sesnews to the activity
        if( $action != null ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesnews);
        }
        $sesnews->is_publish = 1;
      	$sesnews->save();
      }

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($sesnews) as $action ) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();

    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('news_id'=>$sesnews->getIdentity(),'message'=>$this->view->translate('News Edit successfully.'))));
  }
  
  public function editPhotoAction() {
    $news_id = $this->_getParam('news_id',0);
    $sesnews = Engine_Api::_()->core()->getSubject();
    if(!$sesnews){
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }
    if(!empty($_FILES['image']['size'])){
      $sesnews->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesnews','sesnews_news','',$sesnews,true);
      $sesnews->save();

      $images = Engine_Api::_()->sesapi()->getPhotoUrls($sesnews,'','');
      if(!count($images))
        $images['main'] = $this->getBaseUrl(true,$sesnews->getPhotoUrl());
      $result['images'] = $images;
      $result['message'] = $this->view->translate('News photo updated successfully.');
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
    $news_id = $this->_getParam('news_id',null);
    $custom_url = Engine_Api::_()->getDbtable('news', 'sesnews')->checkCustomUrl($value,$news_id);
    if($custom_url){
      echo json_encode(array('error'=>true,'value'=>$value));die;
    }else{
      echo json_encode(array('error'=>false,'value'=>$value));die;
    }
  }

  function sanitize($string, $force_lowercase = true, $anal = false) {}
}
