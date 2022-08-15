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
class Sesarticle_IndexController extends Sesapi_Controller_Action_Standard
{
    protected $_sesarticleEnabled;
    public function init() {
        // only show to member_level if authorized
        if( !$this->_helper->requireAuth()->setAuthParams('sesarticle', null, 'view')->isValid() )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        $id = $this->_getParam('article_id', $this->_getParam('id', null));
        $this->issesarticleEnable();
        if($this->_sesarticleEnabled){
            $article_id = Engine_Api::_()->getDbtable('sesarticles', 'sesarticle')->getArticleId($id);
            if ($article_id) {
                $article = Engine_Api::_()->getItem('sesarticle', $article_id);
                if ($article) {
                    Engine_Api::_()->core()->setSubject($article);
                }
            }
        }else{
            if ($id) {
                $article = Engine_Api::_()->getItem('sesarticle', $id);
                if ($article) {
                    Engine_Api::_()->core()->setSubject($article);
                }
            }
        }
    }
    protected function issesarticleEnable(){
        $this->_sesarticleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesarticle');
    }
    public function searchFormAction(){
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle_enable_location', 1))
            $location = 'yes';
        else
            $location = 'no';

        $form = new Sesarticle_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'article','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => $location,'kilometerMiles' => 'yes','hasPhoto' => 'yes'));
        $form->removeElement('lat');
        $form->removeElement('lng');

        $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));

        if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.favourite', 1))
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
        if($this->_sesarticleEnabled){
            $form = new Sesarticle_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'article','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => 'yes','kilometerMiles' => 'yes','hasPhoto' => 'yes'));

            $filterOptions = (array)$this->_getParam('sortss', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));
            if(!empty($_POST['location'])){
                $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
                if($latlng){
                    $_POST['lat'] = $latlng['lat'];
                    $_POST['lng'] = $latlng['lng'];
                }
            }
            $form->populate($_POST);
            if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.favourite', 1))
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

            if(!empty($_POST['category_id']) && empty($options['category_id']))
                $options['category_id'] = $_POST['category_id'];

            if(!empty($_POST['subcategory_id']))
                $options['subcat_id'] = $_POST['subcategory_id'];
            if(!empty($_POST['subsubcategory_id']))
                $options['subsubcat_id'] = $_POST['subsubcategory_id'];

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
            $paginator = Engine_Api::_()->getDbtable('sesarticles', 'sesarticle')->getSesarticlesPaginator(array_merge($options,$condition), $options);
            $page = (int)  $this->_getParam('page', 1);
            // Build paginator
            $paginator->setItemCountPerPage($this->_getParam('limit',10));
            $paginator->setCurrentPageNumber($page);

            $result = $this->articleResult($paginator);

            if(!empty($_POST['user_id'])){
                $viewer = Engine_Api::_()->user()->getViewer();
                $menuoptions= array();
                $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'sesarticle', 'edit');
                $counter = 0;
                if($canEdit){
                    $menuoptions[$counter]['name'] = "edit";
                    $menuoptions[$counter]['label'] = $this->view->translate("Edit");
                    $counter++;
                }
                $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'sesarticle', 'delete');
                if($canDelete){
                    $menuoptions[$counter]['name'] = "delete";
                    $menuoptions[$counter]['label'] = $this->view->translate("Delete");
                }
                $result['menus'] = $menuoptions;
            }

            if($this->_getParam('catArticle',1)){
                //articleCategories
                $category_id = $this->_getParam('category_id');
                $subcategory_id = $this->_getParam('subcategory_id');
                $table = Engine_Api::_()->getDbTable('categories','sesarticle');
                $tableName = $table->info('name');
                $category_select = $table->select()
                    ->from($tableName, '*');
                if($category_id)
                    $category_select->where($tableName . '.subcat_id = ?', $category_id);
                if($subcategory_id)
                    $category_select->where($tableName . '.subsubcat_id = ?', $subcategory_id);
                $articleTable = Engine_Api::_()->getDbTable('sesarticles', 'sesarticle')->info('name');
                $category_select = $category_select->setIntegrityCheck(false);
                //
                $category_select = $category_select->having("COUNT($articleTable.category_id) > 0")
                    ->group("$articleTable.category_id");
                $category_select = $category_select->joinLeft($articleTable, "$articleTable.category_id=$tableName.category_id", null);
                $paginatore = $table->fetchAll($category_select);
                $counter = 0;
                $catgeoryArray = array();
                if(count($paginatore) > 0){
                    foreach($paginatore as $category){
                        $catgeoryArray[$counter]["category_id"] = $category->getIdentity();
                        $catgeoryArray[$counter]["label"] = $category->category_name;
                        if($category->thumbnail != '' && !is_null($category->thumbnail) && intval($category->thumbnail)):
                            $catgeoryArray[$counter]["thumbnail"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->thumbnail) ? Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl('') : "");
                        endif;
                        if($category->cat_icon != '' && !is_null($category->cat_icon) && intval($category->cat_icon)):
                            $catgeoryArray[$counter]["cat_icon"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->cat_icon) ? Engine_Api::_()->storage()->get($category->cat_icon)->getPhotoUrl('thumb.icon') : "");
                        endif;
                        $counter++;
                    }
                    $result["articleCategories"] = $catgeoryArray;
                }
            }

            $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
            $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
            $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
            $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
            if($result <= 0)
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist articles.'), 'result' => array()));
            else
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
        }else{

        }
    }
    function articleResult($paginator){
        $result = array();
        $counterLoop = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesarticle')){
            $sesarticle = true;
        }
        foreach($paginator as $articles){
            $article = $articles->toArray();
            $description = strip_tags($articles['body']);
            $description = preg_replace('/\s+/', ' ', $description);
            unset($article['body']);
            $article["comment_count"] = Engine_Api::_()->sesadvancedcomment()->commentCount($articles,'subject');
            $article['owner_title'] = Engine_Api::_()->getItem('user',$article['owner_id'])->getTitle();
            $article['body'] = $description;
            $article['resource_type'] = $articles->getType();

            if($this->_sesarticleEnabled){
                if($viewer->getIdentity() != 0){
                    $article['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($articles);
                    $article['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($articles);
                    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.favourite', 1)){
                        $article['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($articles,'favourites','sesarticle');
                        $article['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($articles,'favourites','sesarticle');
                    }
                }
            }

            $result['articles'][$counterLoop] = $article;
            $images = Engine_Api::_()->sesapi()->getPhotoUrls($articles,'','');
            if(!count($images))
                $images['main'] = $this->getBaseUrl(true,$articles->getPhotoUrl());
            $result['articles'][$counterLoop]['images'] = $images;
            $counterLoop++;
        }
        return $result;
    }
    public function categoryAction(){
        $params['countArticles'] = true;
        $paginator = Engine_Api::_()->getDbTable('categories', 'sesarticle')->getCategory($params);
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
            $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s article', '%s articles', $category->total_articles_categories), $this->view->locale()->toNumber($category->total_articles_categories));

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

        if(!Engine_Api::_()->authorization()->getPermission($viewer, 'sesarticle_claim', 'create') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.claim', 1))
            return $this->_forward('requireauth', 'error', 'core');

        // Render
        $this->_helper->content->setEnabled();
    }

    public function claimRequestsAction() {

        $checkClaimRequest = Engine_Api::_()->getDbTable('claims', 'sesarticle')->claimCount();
        if(!$checkClaimRequest)
            return $this->_forward('notfound', 'error', 'core');
        // Render
        $this->_helper->content->setEnabled();
    }


    public function viewAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $id = $this->_getParam('article_id', null);
        $this->view->article_id = $article_id = Engine_Api::_()->getDbtable('sesarticles', 'sesarticle')->getArticleId($id);
        if(!Engine_Api::_()->core()->hasSubject())
            $sesarticle = Engine_Api::_()->getItem('sesarticle', $article_id);
        else
            $sesarticle = Engine_Api::_()->core()->getSubject();

        if( !$this->_helper->requireSubject()->isValid() )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        if( !$this->_helper->requireAuth()->setAuthParams($sesarticle, $viewer, 'view')->isValid() )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        if( !$sesarticle || !$sesarticle->getIdentity() || ($sesarticle->draft && !$sesarticle->isOwner($viewer)) )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        $article = $sesarticle->toArray();
        //$body = @str_replace('src="/', 'src="' . $this->getBaseUrl() . '/', $article['body']);
        //$body = preg_replace('/<\/?a[^>]*>/','',$body);
        $body = $this->replaceSrc($article['body']);
        $article['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
        $article['owner_title'] = Engine_Api::_()->getItem('user',$article['owner_id'])->getTitle();
        $article['resource_type'] = $sesarticle->getType();


        // Get tags
        $articleTags = $sesarticle->tags()->getTagMaps();
        if (!empty($articleTags)) {
            foreach ($articleTags as $tag) {
                $article['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
            }
        }

        if($this->_sesarticleEnabled){
            if($viewer->getIdentity() != 0){
                $article['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($sesarticle);
                $article['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($sesarticle);
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.favourite', 1)){
                    $article['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($sesarticle,'favourites','sesarticle');
                    $article['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($sesarticle,'favourites','sesarticle');
                }
            }
        }
        if (!$sesarticle->isOwner($viewer)) {
            $sesarticle->view_count = $sesarticle->view_count + 1;
            $sesarticle->save();
        }

        $category = Engine_Api::_()->getItem('sesarticle_category', $sesarticle->category_id);
        if (!empty($category))
            $article['category_title'] = $category->getTitle();

        $subcategory = Engine_Api::_()->getItem('sesarticle_category', $sesarticle->subcat_id);
        if (!empty($subcategory))
            $article['subcategory_title'] = $subcategory->getTitle();

        $subsubcat = Engine_Api::_()->getItem('sesarticle_category', $sesarticle->subsubcat_id);
        if (!empty($subsubcat))
            $article['subsubcategory_title'] = $subsubcat->getTitle();

        $article['content_url'] = $this->getBaseUrl(false,$sesarticle->getHref());
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.favourite', 1)){
            $article['can_favorite'] = true;
        }else{
            $article['can_favorite'] = false;
        }
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.share', 1)){
            $article['can_share'] = true;
        }else{
            $article['can_share'] = false;
        }
        $result['article'] = $article;
        if($viewer->getIdentity() > 0){
            $result['article']['permission']['canEdit'] = $canEdit = $viewPermission = $sesarticle->authorization()->isAllowed($viewer, 'edit') ? true : false;
            $result['article']['permission']['canComment'] =  $sesarticle->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $result['article']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesarticle', 'create') ? true : false;
            $result['article']['permission']['can_delete'] = $canDelete  = $sesarticle->authorization()->isAllowed($viewer,'delete') ? true : false;

            $menuoptions= array();
            $counter = 0;
            if($canEdit){
                $menuoptions[$counter]['name'] = "changephoto";
                $menuoptions[$counter]['label'] = $this->view->translate("Change Main Photo");
                $counter++;
                $menuoptions[$counter]['name'] = "edit";
                $menuoptions[$counter]['label'] = $this->view->translate("Edit Article");
                $counter++;
            }
            if($canDelete){
                $menuoptions[$counter]['name'] = "delete";
                $menuoptions[$counter]['label'] = $this->view->translate("Delete Article");
                $counter++;
            }
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.enable.report', 1)){
                $menuoptions[$counter]['name'] = "report";
                $menuoptions[$counter]['label'] = $this->view->translate("Report Article");
            }
            $result['menus'] = $menuoptions;
        }

        $result['article']["share"]["name"] = "share";
        $result['article']["share"]["label"] = $this->view->translate("Share");
        $photo = $this->getBaseUrl(false,$sesarticle->getPhotoUrl());
        if($photo)
            $result['article']["share"]["imageUrl"] = $photo;
				$result['article']["share"]["url"] = $this->getBaseUrl(false,$sesarticle->getHref());
        $result['article']["share"]["title"] = $sesarticle->getTitle();
        $result['article']["share"]["description"] = strip_tags($sesarticle->getDescription());
        $result['article']["share"]['urlParams'] = array(
            "type" => $sesarticle->getType(),
            "id" => $sesarticle->getIdentity()
        );
        if(is_null($result['article']["share"]["title"]))
            unset($result['article']["share"]["title"]);

        $images = Engine_Api::_()->sesapi()->getPhotoUrls($sesarticle,'',"");
        if(!count($images))
            $images['main'] = $this->getBaseUrl(true,$sesarticle->getPhotoUrl());
        $result['article']['article_images'] = $images;

        $result['article']['user_images'] = $this->userImage($sesarticle->owner_id,"thumb.profile");
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));

    }
    function replaceSrc($html = ""){
        preg_match_all( '@src="([^"]+)"@' , $html, $match );
        foreach(array_pop($match) as $src){
            if(strpos($src,'http://') === false && strpos($src,'https://') === false && strpos($src,'//') === false){
                $html = str_replace($src,$this->getBaseUrl().'/'.$src,$html);
            }else if(strpos($src,'http://') === false && strpos($src,'https://') === false){
                $html = str_replace($src,'http://'.$src,$html);
            }
        }
        return $html;
    }
    public function createAction() {
        if( !$this->_helper->requireUser()->isValid() )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        if( !$this->_helper->requireAuth()->setAuthParams('sesarticle', null, 'create')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        /*if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesarticlepackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticlepackage.enable.package', 1)){
            $package = Engine_Api::_()->getItem('sesarticlepackage_package',$this->_getParam('package_id',0));
            $existingpackage = Engine_Api::_()->getItem('sesarticlepackage_orderspackage',$this->_getParam('existing_package_id',0));
            if($existingpackage){
                $package = Engine_Api::_()->getItem('sesarticlepackage_package',$existingpackage->package_id);
            }
            if (!$package && !$existingpackage){
                //check package exists for this member level
                $packageMemberLevel = Engine_Api::_()->getDbTable('packages','sesarticlepackage')->getPackage(array('member_level'=>$viewer->level_id));
                if(count($packageMemberLevel)){
                    //redirect to package page
                    return $this->_helper->redirector->gotoRoute(array('action'=>'article'), 'sesarticlepackage_general', true);
                }
            }
        }*/
        $session = new Zend_Session_Namespace();
        if(empty($_POST))
            unset($session->album_id);
        $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesarticle')->profileFieldId();

        // set up data needed to check quota
        $parentType = $this->_getParam('parent_type', null);
        if($parentType)
            $event_id = $this->_getParam('event_id', null);

        $parentId = $this->_getParam('parent_id', 0);
        $values['user_id'] = $viewer->getIdentity();
        $paginator = Engine_Api::_()->getDbtable('sesarticles', 'sesarticle')->getSesarticlesPaginator($values);

        $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesarticle', 'max');
        $this->view->current_count = $paginator->getTotalItemCount();
        if (($this->view->current_count >= $quota) && !empty($quota)) {
            // return error message
            $message = $this->view->translate('You have already uploaded the maximum number of articles allowed.');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
        }
        $this->view->categories = Engine_Api::_()->getDbtable('categories', 'sesarticle')->getCategoriesAssoc();

        // Prepare form
        $this->view->form = $form = new Sesarticle_Form_Create(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));
        $form->removeElement('lat');
        $form->removeElement('map-canvas');
        $form->removeElement('ses_location');
        $form->removeElement('lng');
        $form->removeElement('fancyuploadfileids');
        $form->removeElement('tabs_form_articlecreate');
        $form->removeElement('file_multi');
        $form->removeElement('from-url');
        $form->removeElement('drag-drop');
        $form->removeElement('uploadFileContainer');
        $form->removeElement('articlestyle');
        $form->removeElement('submit_check');
        $form->removeElement('article_custom_datetimes');
        // Check if post and populate
        if($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if(!empty($_POST['custom_url_article']))
            $_POST['custom_url'] = $_POST['custom_url_article'];
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
            $custom_url = Engine_Api::_()->getDbtable('sesarticles', 'sesarticle')->checkCustomUrl($_POST['custom_url']);
            if ($custom_url) {
                $form->addError($this->view->translate("Custom Url is not available. Please select another URL."));
                $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
                if(count($validateFields))
                    $this->validateFormFields($validateFields);
            }
        }

        $mainPhotoEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesarticle.photo.mandatory', '1');
        if ($mainPhotoEnable == 1 && empty($_FILES['image']['size'])) {
            $form->addError($this->view->translate("Please upload main photo"));
            $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
            if(count($validateFields))
                $this->validateFormFields($validateFields);
        }

        // Process
        $table = Engine_Api::_()->getDbtable('sesarticles', 'sesarticle');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {

            // Create sesarticle
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
            $sesarticle = $table->createRow();
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
                if(isset($sesarticle->package_id) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesarticlepackage') ){
                    $values['package_id'] = Engine_Api::_()->getDbTable('packages','sesarticlepackage')->getDefaultPackage();
                }
            }

            if($_POST['articlestyle'])
                $values['style'] = $_POST['articlestyle'];

            //SEO By Default Work
            $values['seo_title'] = $values['title'];
            if($values['tags'])
                $values['seo_keywords'] = $values['tags'];

            $sesarticle->setFromArray($values);

            if(!empty($_FILES['image']['size'])){
                $sesarticle->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesarticle','sesarticle','',$sesarticle,true);
            }

            if(isset($starttime) && $starttime != ''){
                $starttime = isset($starttime) ? date('Y-m-d H:i:s',strtotime($starttime)) : '';
                $sesarticle->publish_date =$starttime;
            }

            if(isset($starttime) && $viewer->timezone && $starttime != ""){
                //Convert Time Zone
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($viewer->timezone);
                $start = strtotime($starttime);
                date_default_timezone_set($oldTz);
                $sesarticle->publish_date = date('Y-m-d H:i:s', $start);
            }else{
                $sesarticle->publish_date = date('Y-m-d H:i:s',strtotime("-2 minutes", time()));
            }
            $sesarticle->parent_id = $parentId;
            $sesarticle->save();
            $article_id = $sesarticle->article_id;

            if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
                $sesarticle->custom_url = $_POST['custom_url'];
            else
                $sesarticle->custom_url = $sesarticle->article_id;
            $sesarticle->save();
            $article_id = $sesarticle->article_id;

            $roleTable = Engine_Api::_()->getDbtable('roles', 'sesarticle');
            $row = $roleTable->createRow();
            $row->article_id = $article_id;
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
                Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $article_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesarticle")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
            }

            if($parentType == 'sesevent_article') {
                $sesarticle->parent_type = $parentType;
                $sesarticle->event_id = $event_id;
                $sesarticle->save();
                $seseventarticle = Engine_Api::_()->getDbtable('mapevents', 'sesarticle')->createRow();
                $seseventarticle->event_id = $event_id;
                $seseventarticle->article_id = $article_id;
                $seseventarticle->save();
            }

            if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
                $sesarticle->photo_id = $_POST['cover'];
                $sesarticle->save();
            }

            $customfieldform = $form->getSubForm('fields');
            if (!is_null($customfieldform)) {
                $customfieldform->setItem($sesarticle);
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
                $auth->setAllowed($sesarticle, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($sesarticle, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($sesarticle, $role, 'video', ($i <= $videoMax));
                $auth->setAllowed($sesarticle, $role, 'music', ($i <= $musicMax));
            }

            // Add tags
            $tags = preg_split('/[,]+/', $values['tags']);
            // $sesarticle->seo_keywords = implode(',',$tags);
            //$sesarticle->seo_title = $sesarticle->title;
            $sesarticle->save();
            $sesarticle->tags()->addTagMaps($viewer, $tags);

            $session = new Zend_Session_Namespace();
            if(!empty($session->album_id)){
                $album_id = $session->album_id;
                if(isset($article_id) && isset($sesarticle->title)){
                    Engine_Api::_()->getDbTable('albums', 'sesarticle')->update(array('article_id' => $article_id,'owner_id' => $viewer->getIdentity(),'title' => $sesarticle->title), array('album_id = ?' => $album_id));
                    if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
                        Engine_Api::_()->getDbTable('albums', 'sesarticle')->update(array('photo_id' => $_POST['cover']), array('album_id = ?' => $album_id));
                    }
                    Engine_Api::_()->getDbTable('photos', 'sesarticle')->update(array('article_id' => $article_id), array('album_id = ?' => $album_id));
                    unset($session->album_id);
                }
            }

            // Add activity only if sesarticle is published
            if( $values['draft'] == 0 && $values['is_approved'] == 1 && (!$sesarticle->publish_date || strtotime($sesarticle->publish_date) <= time())) {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesarticle, 'sesarticle_new');
                // make sure action exists before attaching the sesarticle to the activity
                if( $action ) {
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesarticle);
                }
                //Send notifications for subscribers
                Engine_Api::_()->getDbtable('subscriptions', 'sesarticle')->sendNotifications($sesarticle);
                $sesarticle->is_publish = 1;
                $sesarticle->save();
            }
            // Commit
            $db->commit();
        }

        catch( Exception $e ) {
            $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }

        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('article_id'=>$sesarticle->getIdentity(),'message'=>$this->view->translate('Blog created successfully.'))));
    }

    public function deleteAction() {
        $sesarticle = Engine_Api::_()->getItem('sesarticle', $this->getRequest()->getParam('article_id'));
        if( !$this->_helper->requireAuth()->setAuthParams($sesarticle, null, 'delete')->isValid())
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        if( !$sesarticle ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Article entry doesn't exist or not authorized to delete");
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
        }

        if( !$this->getRequest()->isPost() ) {
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
        }
        $db = $sesarticle->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            Engine_Api::_()->sesarticle()->deleteArticle($sesarticle);;
            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
        }
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your Article entry has been deleted.');
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
            'parent_type' => 'sesarticle',
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
                throw new sesarticle_Model_Exception($e->getMessage(), $e->getCode());
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

        $this->view->article = $sesarticle = Engine_Api::_()->core()->getSubject();


        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesarticle')->profileFieldId();

        if( !$this->_helper->requireSubject()->isValid() )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        if( !$this->_helper->requireAuth()->setAuthParams('sesarticle', $viewer, 'edit')->isValid() )
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


        // Prepare form
        $this->view->form = $form = new Sesarticle_Form_Edit(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));

        // Populate form
        $form->populate($sesarticle->toArray());

        $tagStr = '';
        foreach( $sesarticle->tags()->getTagMaps() as $tagMap ) {
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
                if( $auth->isAllowed($sesarticle, $role, 'view') ) {
                    $form->auth_view->setValue($role);
                }
            }

            if ($form->auth_comment){
                if( $auth->isAllowed($sesarticle, $role, 'comment') ) {
                    $form->auth_comment->setValue($role);
                }
            }

            if ($form->auth_video){
                if( $auth->isAllowed($sesarticle, $role, 'video') ) {
                    $form->auth_video->setValue($role);
                }
            }

            if ($form->auth_music){
                if( $auth->isAllowed($sesarticle, $role, 'music') ) {
                    $form->auth_music->setValue($role);
                }
            }
        }

        // hide status change if it has been already published
        if( $sesarticle->draft == "0" )
            $form->removeElement('draft');

        $form->removeElement('lat');
        $form->removeElement('map-canvas');
        $form->removeElement('ses_location');
        $form->removeElement('lng');
        $form->removeElement('fancyuploadfileids');
        $form->removeElement('tabs_form_articlecreate');
        $form->removeElement('file_multi');
        $form->removeElement('from-url');
        $form->removeElement('drag-drop');
        $form->removeElement('uploadFileContainer');
        $form->removeElement('articlestyle');
        $form->removeElement('submit_check');
        $form->removeElement('article_custom_datetimes');
        // Check if post and populate
        if($this->_getParam('getForm')) {
            if(isset($sesarticle) && $form->starttime){
                $start = strtotime($sesarticle->publish_date);
                $start_date = date('m/d/Y',($start));
                $start_time = date('g:ia',($start));
                $viewer = Engine_Api::_()->user()->getViewer();
                $publishDate = $start_date.' '.$start_time;
                $start_date_y = date('Y',strtotime($start_date));
                $start_date_m = date('m',strtotime($start_date));
                $start_date_d = date('d',strtotime($start_date));
                if($viewer->timezone){
                    $start = strtotime($sesarticle->publish_date);
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

            if(count($formFields) && $sesarticle->category_id){
                foreach($formFields as $fields){
                    foreach($fields as $field){
                        $subcat = array();
                        if($fields['name'] == "subcat_id"){
                            $subcat = Engine_Api::_()->getItemTable('sesarticle_category')->getModuleSubcategory(array('category_id'=>$sesarticle->category_id,'column_name'=>'*'));
                        }else if($fields['name'] == "subsubcat_id"){
                            if($sesarticle->subcat_id)
                                $subcat = Engine_Api::_()->getItemTable('sesarticle_category')->getModuleSubSubcategory(array('category_id'=>$sesarticle->subcat_id,'column_name'=>'*'));
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
        if(empty($_FILES['image']))
            $_FILES['image'] = array();
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
            $sesarticle->setFromArray($values);
            $sesarticle->modified_date = date('Y-m-d H:i:s');
            if(isset($starttime) && $starttime != ''){
                $starttime = isset($starttime) ? date('Y-m-d H:i:s',strtotime($starttime)) : '';
                $sesarticle->publish_date =$starttime;
            }
            //else{
            //	$sesarticle->publish_date = '';
            //}
            $sesarticle->save();
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
                Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('article_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesarticle") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
            }

            if(isset($values['draft']) && !$values['draft']) {
                $currentDate = date('Y-m-d H:i:s');
                if($sesarticle->publish_date < $currentDate) {
                    $sesarticle->publish_date = $currentDate;
                    $sesarticle->save();
                }
            }
            if(!empty($_FILES['image']['size'])){
                $sesarticle->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesarticle','sesarticle','',$sesarticle,true);
                $sesarticle->save();
            }
            // Add fields
            $customfieldform = $form->getSubForm('fields');
            if (!is_null($customfieldform)) {
                $customfieldform->setItem($sesarticle);
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
                $auth->setAllowed($sesarticle, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($sesarticle, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($sesarticle, $role, 'video', ($i <= $videoMax));
                $auth->setAllowed($sesarticle, $role, 'music', ($i <= $musicMax));
            }

            // handle tags
            $tags = preg_split('/[,]+/', $values['tags']);
            $sesarticle->tags()->setTagMaps($viewer, $tags);

            // insert new activity if sesarticle is just getting published
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($sesarticle);
            if( count($action->toArray()) <= 0 && $values['draft'] == '0' && (!$sesarticle->publish_date || strtotime($sesarticle->publish_date) <= time())) {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesarticle, 'sesarticle_new');
                // make sure action exists before attaching the sesarticle to the activity
                if( $action != null ) {
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesarticle);
                }
                $sesarticle->is_publish = 1;
                $sesarticle->save();
            }

            // Rebuild privacy
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach( $actionTable->getActionsByObject($sesarticle) as $action ) {
                $actionTable->resetActivityBindings($action);
            }

            // Send notifications for subscribers
            Engine_Api::_()->getDbtable('subscriptions', 'sesarticle')
                ->sendNotifications($sesarticle);
            $db->commit();

        }
        catch( Exception $e )
        {
            $db->rollBack();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

        }
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('article_id'=>$sesarticle->getIdentity(),'message'=>$this->view->translate('Article Edit successfully.'))));


    }
    public function editPhotoAction() {
        $article_id = $this->_getParam('article_id',0);
        $sesarticle = Engine_Api::_()->core()->getSubject();
        if(!$sesarticle){
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
        }
        if(!empty($_FILES['image']['size'])){
            $sesarticle->photo_id =  Engine_Api::_()->sesapi()->setPhoto($_FILES['image'], false,false,'sesarticle','sesarticle','',$sesarticle,true);
            $sesarticle->save();

            $images = Engine_Api::_()->sesapi()->getPhotoUrls($sesarticle,'','');
            if(!count($images))
                $images['main'] = $this->getBaseUrl(true,$sesarticle->getPhotoUrl());
            $result['images'] = $images;
            $result['message'] = $this->view->translate('Article photo updated successfully.');
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
        $article_id = $this->_getParam('article_id',null);
        $custom_url = Engine_Api::_()->getDbtable('sesarticles', 'sesarticle')->checkCustomUrl($value,$article_id);
        if($custom_url){
            echo json_encode(array('error'=>true,'value'=>$value));die;
        }else{
            echo json_encode(array('error'=>false,'value'=>$value));die;
        }
    }

    function sanitize($string, $force_lowercase = true, $anal = false) {}
}
