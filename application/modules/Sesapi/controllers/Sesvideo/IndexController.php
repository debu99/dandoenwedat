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
    class Sesvideo_IndexController extends Sesapi_Controller_Action_Standard {
        protected $_permission = array();
        protected $_leftvideo ;
        protected $_counterVideoUploaded;
        public function init() {
            // only show videos if authorized
            if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'view')->isValid())
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
            $id = $this->_getParam('video_id', $this->_getParam('id', null));
            if ($id && intval($id)) {
                $video = Engine_Api::_()->getItem('video', $id);
                if ($video) {
                    Engine_Api::_()->core()->setSubject($video);
                }
            }
            $this->_permission = array('canCreateVideo'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'create'),'watchLater'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.watchlater', 1),'canCreatePlaylist'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'addplaylist_video'),'canCreateChannel'=>Engine_Api::_()->authorization()->isAllowed('sesvideo_chanel', null, 'create'),'canChannelEnable'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video_enable_chanel', 1));
        }
        public function videosAction(){
            $resource_id = $this->_getParam('resource_id',0);
            $resource_type = $this->_getParam('resource_type',0);

            $item = Engine_Api::_()->getItem($resource_type,$resource_id);
            if(!$item)
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
            if($resource_type == "sesvideo_chanel"){
                $paginator = Engine_Api::_()->getDbTable('chanelvideos', 'sesvideo')->getChanelAssociateVideos($item);
            }

            $paginator->setItemCountPerPage($this->_getParam('limit',10));
            $paginator->setCurrentPageNumber($this->_getParam('page',1));
            $result["permission"] =  $this->_permission;
            $result['videos'] = $this->getVideos($paginator,"");

            $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
            $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
            $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
            $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
            if($result <= 0)
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No video found.', 'result' => array()));
            else
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

        }
        public function categoryAction(){
            $params['countVideos'] = true;
            $paginator = Engine_Api::_()->getDbTable('categories', 'sesvideo')->getCategory($params);
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
                $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s video', '%s videos', $category->total_videos_categories), $this->view->locale()->toNumber($category->total_videos_categories));

                $counter++;
            }
            $catgeoryArray["permission"] =  $this->_permission;
            if($catgeoryArray <= 0)
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No Category exists.', 'result' => array()));
            else
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array()));
        }

        public function editAction() {
            $viewer = Engine_Api::_()->user()->getViewer();
            $video = Engine_Api::_()->getItem('video', $this->_getParam('video_id'));
            if (!$this->_helper->requireUser()->isValid())
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));

            $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesvideo')->profileFieldId();
            if (!$this->_helper->requireSubject()->isValid())
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));

            if($video->parent_type == 'sesblog_blog') {
                $blog = Engine_Api::_()->getItem('sesblog_blog', $video->parent_id);
                if (!Engine_Api::_()->sesblog()->checkBlogAdmin($blog) && !$this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid()) {
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
                }
            }
            else {
                if ($viewer->getIdentity() != $video->owner_id && !$this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid()) {
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
                }
            }
            $this->view->video = $video;
            $this->view->form = $form = new Sesvideo_Form_Edit(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));
            $form->populate($video->toArray());
            if (isset($video->artists)) {
                $artists_array = json_decode($video->artists);
                if (count($artists_array) > 0 && in_array($artists_array))
                    $form->artists->setValue(json_decode($video->artists));
            }

            if($form->getElement('location'))
                $form->getElement('location')->setValue($video->location);
            $form->getElement('search')->setValue($video->search);
            $form->getElement('title')->setValue($video->title);
            $form->getElement('description')->setValue($video->description);
            $form->getElement('category_id')->setValue($video->category_id);
            $allowAdultContent = Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.allow.adult.filtering');
            if($allowAdultContent){
                if ($form->getElement('adult'))
                    $form->getElement('adult')->setValue($video->adult);
            }
            if ($form->getElement('is_locked'))
                $form->getElement('is_locked')->setValue($video->is_locked);
            if ($form->getElement('password'))
                $form->getElement('password')->setValue($video->password);
            if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesvideosell') && $video->payment_type == 'paid') {
                if ($form->getElement('price'))
                    $form->getElement('price')->setValue($video->price);
            }
            // authorization
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            foreach ($roles as $role) {
                if (1 === $auth->isAllowed($video, $role, 'view')) {
                    $form->auth_view->setValue($role);
                }
                if (1 === $auth->isAllowed($video, $role, 'comment')) {
                    $form->auth_comment->setValue($role);
                }
            }
            // prepare tags
            $videoTags = $video->tags()->getTagMaps();
            $tagString = '';
            foreach ($videoTags as $tagmap) {
                if ($tagString !== '')
                    $tagString .= ', ';
                $tagString .= $tagmap->getTag()->getTitle();
            }
            $this->view->tagNamePrepared = $tagString;
            $form->tags->setValue($tagString);

            $form->removeElement('lat');
            $form->removeElement('lng');
            $form->removeElement('map-canvas');
            $form->removeElement('ses_location');
            $form->removeElement('embedUrl');
            $form->removeElement('code');
            $form->removeElement('id');
            $form->removeElement('ignore');

            if($this->_getParam('getForm')) {

                $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
                //set subcategory
                $newFormFieldsArray = array();
                if(count($formFields) && $video->category_id){
                    foreach($formFields as $fields){
                        foreach($fields as $field){
                            $subcat = array();
                            if($fields['name'] == "subcat_id"){
                                $subcat = Engine_Api::_()->getItemTable('sesvideo_category')->getModuleSubcategory(array('category_id'=>$video->category_id,'column_name'=>'*'));
                            }else if($fields['name'] == "subsubcat_id"){
                                if($sesblog->subcat_id)
                                    $subcat = Engine_Api::_()->getItemTable('sesvideo_category')->getModuleSubSubcategory(array('category_id'=>$video->subcat_id,'column_name'=>'*'));
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

            // Check if valid
            if( !$form->isValid($_POST) ) {
                $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
                if(count($validateFields))
                    $this->validateFormFields($validateFields);
            }

            // Process
            $db = Engine_Api::_()->getDbtable('videos', 'sesvideo')->getAdapter();
            $db->beginTransaction();
            try {
                $values = $form->getValues();
                if (isset($values['artists']))
                    $values['artists'] = json_encode($values['artists']);
                else
                    $values['artists'] = json_encode(array());

                if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '' && $_FILES['image']['size'] > 0) {
                    $values['photo_id'] = $this->setPhoto($_FILES['image'], $video->video_id, true);
                } else {
                    if (empty($values['photo_id'])){
                        unset($values['photo_id']);
                    }
                }
                $sesprofilelock_enable_module = (array)Engine_Api::_()->getApi('settings', 'core')->getSetting('sesprofilelock.enable.modules');
                //check dependent module sesprofile install or not
                if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesprofilelock'))  && in_array('sesvideo',$sesprofilelock_enable_module)) {
                    //disable lock if password not set.
                    if (!$values['is_locked'] || !$values['password']) {
                        $values['is_locked'] = '0';
                        $values['password'] = '';
                    }
                }else{
                    $values['is_locked']    = '';
                    $values['password']    = '';
                }
                if(!empty($_POST['location'])){
                    $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
                    if($latlng){
                        $_POST['lat'] = $latlng['lat'];
                        $_POST['lng'] = $latlng['lng'];
                    }
                }

                if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
                    $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                    $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('video_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesvideo_video")    ON DUPLICATE KEY UPDATE    lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
                }
                $video->setFromArray($values);
                $video->save();
                // Add fields
                $customfieldform = $form->getSubForm('fields');
                if (!is_null($customfieldform)) {
                    $customfieldform->setItem($video);
                    $customfieldform->saveValues();
                }
                // CREATE AUTH STUFF HERE
                $auth = Engine_Api::_()->authorization()->context;
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                if ($values['auth_view'])
                    $auth_view = $values['auth_view'];
                else
                    $auth_view = "everyone";
                $viewMax = array_search($auth_view, $roles);
                foreach ($roles as $i => $role) {
                    $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
                }
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                if ($values['auth_comment'])
                    $auth_comment = $values['auth_comment'];
                else
                    $auth_comment = "everyone";
                $commentMax = array_search($auth_comment, $roles);
                foreach ($roles as $i => $role) {
                    $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
                }
                // Add tags
                $tags = preg_split('/[,]+/', $values['tags']);
                $video->tags()->setTagMaps($viewer, $tags);
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

            }
            $db->beginTransaction();
            try {
                // Rebuild privacy
                $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
                foreach ($actionTable->getActionsByObject($video) as $action) {
                    $actionTable->resetActivityBindings($action);
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
            }
            if($video->parent_type == 'sesblog_blog') {
                //$tab_id = Engine_Api::_()->sesbasic()->getWidgetTabId(array('name' => 'sesblog.profile-videos'));
                //return $this->_helper->redirector->gotoRoute(array('action' => 'view','blog_id'=>$blog->custom_url, 'tab' => $tab_id),'sesblog_entry_view',true);
            }else {
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Video edited successfully.")));

            }
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
                            'parent_type' => 'video',
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
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('upload_limit_reach'), 'result' => array()));
                } else {
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('uploading_error'), 'result' => array()));

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

        public function createAction() {
            // Upload video
            if (!$this->_helper->requireUser->isValid())
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
            if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'create')->isValid())
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
            if (isset($_POST['c_type']))
                return $this->_forward('compose-upload', null, null, array('format' => 'json'));

            $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesvideo')->profileFieldId();
            //check ses modules integration
            $values['parent_id'] = $parent_id = $this->_getParam('parent_id', null);
            $values['parent_type'] = $parent_type = $this->_getParam('parent_type', null);

            // set up data needed to check quota
            $viewer = Engine_Api::_()->user()->getViewer();
            $values['user_id'] = $viewer->getIdentity();
            $paginator = Engine_Api::_()->getApi('core', 'sesvideo')->getVideosPaginator($values);
            $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
            $this->view->current_count = $currentCount = $paginator->getTotalItemCount();
            if ($quota)
                $leftVideos = $quota - $currentCount;
            else
                $leftVideos = 0; //o means unlimited

            if (($this->current_count >= $this->quota) && !empty($this->quota)){
                // return error message
                $message = $this->view->translate('You have already uploaded the maximum number of videos allowed.');
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
            }
            //Create form
            $this->view->form = $form = new Sesvideo_Form_Video(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));
            $form->removeElement('lat');
            $form->removeElement('lng');
            $form->removeElement('map-canvas');
            $form->removeElement('ses_location');
            $form->removeElement('embedUrl');
            $form->removeElement('code');
            $form->removeElement('id');
            $form->removeElement('ignore');
            if($form->getElement('photo_id'))
                $form->removeElement('photo_id');
            $youtubeVID = $this->_getParam('vid');
            $data = array();
            if ($youtubeVID)
            {
                $data = $this->handleInformation(1, $youtubeVID);
                $data['code'] = $youtubeVID;
                $data['type'] = 1;
                $data['url'] = 'https://www.youtube.com/watch?v='.$youtubeVID;
                $form->populate($data);
            }
            if(empty($_FILES['upload_video']['name']) && $_FILES['upload_video']['size'] < 1){
                $_FILES['upload_video'] = array();
            }
            $this->view->data = $data;
            if ($this->_getParam('type', false))
                $form->getElement('type')->setValue($this->_getParam('type'));

            if($this->_getParam('getForm')) {
                $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
                $this->generateFormFields($formFields);
            }

            // Check if valid
            if( !$form->isValid($this->getRequest()->getPost()) ) {
                $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
                if(count($validateFields))
                    $this->validateFormFields($validateFields);
            }

            // Process
            $values = $form->getValues();
            if(empty($values['rotation']))
                $values['rotation'] = 0;
            $video_type = $_POST['type'] = $_POST['resource_video_type'];
            parse_str( parse_url( $_POST['url'], PHP_URL_QUERY ), $my_array_of_vars );


            if($_POST['resource_video_type'] != 3)
                $validateVideo = $this->handleInformation($_POST['url']);
            else{
                $validateVideo = empty($_FILES['video']['name']) ?  0 : 1;
            }

            if(!$validateVideo){
                if($_POST['resource_video_type'] != 3)
                    $error = ('Please select valid upload url for video.');
                else
                    $error = ('Please select video to upload.');
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array()));
            }

            $values['type'] = $_POST['resource_video_type'];
            $values['parent_id'] = $parent_id = $this->_getParam('parent_id', null);
            $values['parent_type'] = $parent_type = $this->_getParam('parent_type', null);
            if( $values['parent_id'] &&  $values['parent_type'])
                $parentItem = Engine_Api::_()->getItem($parent_type, $parent_id);

            $values['owner_id'] = $viewer->getIdentity();
            $insert_action = false;
            $db = Engine_Api::_()->getDbtable('videos', 'sesvideo')->getAdapter();
            $db->beginTransaction();
            if(!empty($_POST['rotation'])){
                $rotation = $_POST['rotation'];
                if($rotation == 1){
                    $_POST['rotation'] = 90;
                }else if($rotation == 2){
                    $_POST['rotation'] = 180;
                }else if($rotation == 3){
                    $_POST['rotation'] = 270;
                }
            }
            try {
                $viewer = Engine_Api::_()->user()->getViewer();
                $isApproveUploadOption = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $viewer, 'video_approve');
                $approveUploadOption = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $viewer, 'video_approve_type');
                $approve = 1;
                if($isApproveUploadOption){
                    foreach($approveUploadOption as $valuesIs){
                        if ($values['type'] == 3 && $valuesIs == 'myComputer') {
                            //my computer
                            $approve = 0;
                            break;
                        }elseif($valuesIs == "iframely"){
                            $approve = 0;
                            break;
                        }
                    }
                }


                //Create video
                $table = Engine_Api::_()->getDbtable('videos', 'sesvideo');
                if($values['type'] == 'iframely') {

                    $information = $this->handleInformation($values['url']);
                    if (empty($information)) {
                        $message = $this->view->translate('We could not find a video there - please check the URL and try again.');
                        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
                    }
                    $values['code'] = $information['code'];
                    $values['thumbnail'] = $information['thumbnail'];
                    $values['duration'] = $information['duration'];
                    $video = $table->createRow();

                }
                else if ($values['type'] == 3) {
                    $viewer = Engine_Api::_()->user()->getViewer();
                    $values['owner_id'] = $viewer->getIdentity();
                    $params = array(
                                    'owner_type' => 'user',
                                    'owner_id' => $viewer->getIdentity()
                                    );
                    $video = Engine_Api::_()->sesvideo()->createVideo($params, $_FILES['video'], $values);
                    if(empty($values['title'])){
                        $video->title = $this->view->translate('Untitled Video');
                        $video->save();
                    }
                    //Sample Video Work
                    $samplevideo_id = $this->_getParam('samplevideo_id', null);
                    if(!empty($samplevideo_id)) {
                        $samplevideo = Engine_Api::_()->getItem('sesvideo_samplevideo', $this->_getParam('samplevideo_id'));
                        $samplevideo->video_id = $video->getIdentity();
                        $samplevideo->save();
                    }
                }else if($values['type'] == 16){
                    $video = Engine_Api::_()->sesvideo()->createVideo(array(), $values['code'], $values,false);
                } else
                    $video = $table->createRow();


                if ($values['type'] == 3 && isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {
                    $values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
                }


                if (isset($values['artists']))
                    $values['artists'] = json_encode(array_keys($values['artists']));
                else
                    $values['artists'] = json_encode(array());

                if (is_null($values['subsubcat_id']))
                    $values['subsubcat_id'] = 0;
                if (is_null($values['subcat_id']))
                    $values['subcat_id'] = 0;
                //disable lock if password not set.
                if (isset($values['is_locked']) && $values['is_locked'] && $values['password'] == '')
                    $values['is_locked'] = '0';
                if(empty($_FILES['photo_id']['name'])){
                    unset($values['photo_id']);
                }
                $values['approve'] = $approve;

                if(empty($values['category_id']) || is_null($values['category_id'])){
                    $values['category_id'] = "";
                    $values['subcat_id'] = "";
                    $values['subsubcat_id'] = "";
                }

                $video->setFromArray($values);
                $video->save();
                // Add fields

                $customfieldform = $form->getSubForm('fields');
                if (!is_null($customfieldform)) {
                    $customfieldform->setItem($video);
                    $customfieldform->saveValues();
                }
                $thumbnail = $values['thumbnail'];
                $ext = ltrim(strrchr($thumbnail, '.'), '.');
                $thumbnail_parsed = @parse_url($thumbnail);

                if (@GetImageSize($thumbnail)) {
                    $valid_thumb = true;
                } else {
                    $valid_thumb = false;
                }


                if(isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '' && $values['type'] != 3 ) {
                    $video->photo_id = $this->setPhoto($form->photo_id, $video->video_id, true);
                    $video->save();
                } else if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {

                    $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                    $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;

                    $src_fh = fopen($thumbnail, 'r');
                    $tmp_fh = fopen($tmp_file, 'w');
                    stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                    //resize video thumbnails
                    $image = Engine_Image::factory();
                    $image->open($tmp_file)
                    ->resize(500, 500)
                    ->write($thumb_file)
                    ->destroy();
                    try {
                        $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                                                                                              'parent_type' => $video->getType(),
                                                                                              'parent_id' => $video->getIdentity()
                                                                                              ));
                        // Remove temp file
                        @unlink($thumb_file);
                        @unlink($tmp_file);
                        $video->status = 1;
                        $video->photo_id = $thumbFileRow->file_id;
                        $video->save();

                    } catch (Exception $e){
                        @unlink($thumb_file);
                        @unlink($tmp_file);
                    }
                }
                if($values['type'] == 'iframely') {
                    $video->status = 1;
                    $video->save();
                    $insert_action = true;
                }


                if(!empty($_POST['location'])){
                    $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
                    if($latlng){
                        $_POST['lat'] = $latlng['lat'];
                        $_POST['lng'] = $latlng['lng'];
                    }
                }

                if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
                    $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                    $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $video->video_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesvideo_video")    ON DUPLICATE KEY UPDATE    lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
                }
                if ($values['ignore'] == true) {
                    $video->status = 1;
                    $video->save();
                    $insert_action = true;
                }
                // CREATE AUTH STUFF HERE
                $auth = Engine_Api::_()->authorization()->context;
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                if (isset($values['auth_view']))
                    $auth_view = $values['auth_view'];
                else
                    $auth_view = "everyone";
                $viewMax = array_search($auth_view, $roles);
                foreach ($roles as $i => $role) {
                    $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
                }
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                if (isset($values['auth_comment']))
                    $auth_comment = $values['auth_comment'];
                else
                    $auth_comment = "everyone";
                $commentMax = array_search($auth_comment, $roles);
                foreach ($roles as $i => $role) {
                    $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
                }
                // Add tags
                $tags = preg_split('/[,]+/', $values['tags']);
                $video->tags()->addTagMaps($viewer, $tags);
                $db->commit();

            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

            }
            $db->beginTransaction();
            try {
                if ($approve && $video->status == 1) {
                    $owner = $video->getOwner();
                    //Create Activity Feed

                    if($parent_id && $parent_type) {
                        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $parentItem, 'sesevent_event_editeventvideo');
                        if ($action != null) {
                            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
                        }
                    } else {
                        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'sesvideo_video_create');
                        if ($action != null) {
                            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
                        }
                    }
                    // Rebuild privacy
                    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
                    foreach ($actionTable->getActionsByObject($video) as $action) {
                        $actionTable->resetActivityBindings($action);
                    }
                }

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
            }
            $result["video"]["message"] = $this->view->translate("Video created successfully.");
            $result['video']['id'] = $video->getIdentity();
            if (($video->type == 3 && $video->status != 1) || !$approve) {
                $result['video']['redirect'] = "manage";
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
            }
            if ($parent_id && $parent_type == 'sesevent_event') {
                $result['video']['redirect'] = "profile_event";
                $result['video']['id'] = $parent_id;
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
            } elseif ($parent_id && $parent_type == 'sesblog_blog') {
                $result['video']['redirect'] = "profile_blog";
                $result['video']['id'] = $parent_id;
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
            } else {
                $result['video']['redirect'] = "video_view";
                $result['video']['id'] = $video->getIdentity();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
            }
        }
        public function importVideoFromYoutubePlaylist($playlistId = null, $leftvideos, $values, $form, $limitYoutubePlaylist, $googleApiKey,$approve) {
            if (!$playlistId)
                return;
            require_once 'application/modules/Sesvideo/controllers/Google/autoload.php';
            require_once 'application/modules/Sesvideo/controllers/Google/Client.php';
            require_once 'application/modules/Sesvideo/controllers/Google/Service/YouTube.php';
            $client = new Google_Client();
            $client->setDeveloperKey($googleApiKey);
            $youtube = new Google_Service_YouTube($client);
            $nextPageToken = '';
            $this->_counterVideoUploaded = 0;
            $this->_leftvideo = $leftvideos;
            $playlistItemsResponse = array();
            $videoIds = array();
            $counter = 1;
            $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
            do {
                if (($leftvideos && $counter > 1 && $counter * 50 >= $leftvideos ) || ($counter > 1 && $counter * 50 > $limitYoutubePlaylist && $limitYoutubePlaylist > 0))
                    break;
                $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
                                                                                                     'playlistId' => $values['code'],
                                                                                                     'maxResults' => 50,
                                                                                                     'pageToken' => $nextPageToken));

                foreach ($playlistItemsResponse['items'] as $playlistItem) {
                    $videoIds[] = $playlistItem['snippet']['resourceId']['videoId'];
                }
                $ids = implode(',', $videoIds);
                if (function_exists('curl_init')){
                    $data =  $this->url_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$ids&key=$key");
                }else
                    $data = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$ids&key=$key");
                if (!$data) {
                    $result['video']['redirect'] = "manage";
                    $result['video']['message'] = $this->view->translate("Youtube Playlist imported successfully.");
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$result));
                }
                $data = Zend_Json::decode($data);
                $returnError = $this->uploadYoutubePlaylistVideos($values, $form, $leftvideos, $data, $limitYoutubePlaylist,$approve);
                if (!$returnError)
                {
                    $result['video']['redirect'] = "manage";
                    $result['video']['message'] = $this->view->translate("Youtube Playlist imported successfully.");
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$result));
                }

                $nextPageToken = $playlistItemsResponse['nextPageToken'];
                $counter++;
                sleep(2);
            } while ($nextPageToken <> '');
            $result['video']['redirect'] = "manage";
            $result['video']['message'] = $this->view->translate("Youtube Playlist imported successfully.");
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$result));
        }
        public function uploadYoutubePlaylistVideos($values, $form, $leftVideos, $data, $limitYoutubePlaylist,$approve) {
            $viewer = Engine_Api::_()->user()->getViewer();
            $values['user_id'] = $viewer->getIdentity();
            // Process
            $values['owner_id'] = $viewer->getIdentity();
            $insert_action = false;
            $db = Engine_Api::_()->getDbtable('videos', 'sesvideo')->getAdapter();
            $db->beginTransaction();
            try {
                //Create video
                $table = Engine_Api::_()->getDbtable('videos', 'sesvideo');
                $values['type'] = 1;
                $changeCode = true;
                $counterVideoUploadLeft = 0;
                $values['approve'] = $approve;
                if(isset($values['artists']))
                    $artists = $values['artists'];
                foreach ($data['items'] as $videoId) {
                    if (($this->_leftvideo && $this->_counterVideoUploaded == $this->_leftvideo) || ($limitYoutubePlaylist == $this->_counterVideoUploaded + 1 && $limitYoutubePlaylist > 0))
                        return false;
                    $video = $table->createRow();
                    $values['title'] = $videoId['snippet']['title'];
                    $values['description'] = $videoId['snippet']['description'];
                    $values['duration'] = Engine_Date::convertISO8601IntoSeconds($videoId['contentDetails']['duration']);
                    $values['code'] = $videoId['id'];
                    $values['type'] = 1;
                    if (isset($artists))
                        $values['artists'] = json_encode($artists);
                    else
                        $values['artists'] = json_encode(array());
                    if (is_null($values['subsubcat_id']))
                        $values['subsubcat_id'] = 0;
                    if (is_null($values['subcat_id']))
                        $values['subcat_id'] = 0;
                    //disable lock if password not set.
                    if (isset($values['is_locked']) && $values['is_locked'] && $values['password'] == '') {
                        $values['is_locked'] = '0';
                    }
                    $video->setFromArray($values);
                    $video->save();
                    // Add fields
                    $customfieldform = $form->getSubForm('fields');
                    if (!is_null($customfieldform)) {
                        $customfieldform->setItem($video);
                        $customfieldform->saveValues();
                    }
                    // Now try to create thumbnail
                    $thumbnail = $this->handleThumbnail($values['type'], $values['code']);
                    $ext = ltrim(strrchr($thumbnail, '.'), '.');
                    $thumbnail_parsed = @parse_url($thumbnail);
                    $imageUploadSize = @getimagesize($thumbnail);
                    $width = isset($imageUploadSize[0]) ? $imageUploadSize[0] : '';
                    $height = isset($imageUploadSize[1]) ? $imageUploadSize[1] : '';
                    if (@$imageUploadSize && $width > 120 && $height > 90) {
                        $valid_thumb = true;
                    } else {
                        if($values['type'] == 1) {
                            $thumbnail = "http://img.youtube.com/vi/".$values['code']."/hqdefault.jpg";
                            if (@getimagesize($thumbnail)) {
                                $valid_thumb = true;
                                $thumbnail_parsed = @parse_url($thumbnail);
                            } else {
                                $valid_thumb = false;
                            }
                        } else {
                            $valid_thumb = false;
                        }
                    }
                    if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
                        $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
                        $src_fh = fopen($thumbnail, 'r');
                        $tmp_fh = fopen($tmp_file, 'w');
                        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                        //resize video thumbnails
                        $image = Engine_Image::factory();
                        $image->open($tmp_file)
                        ->resize(500, 500)
                        ->write($thumb_file)
                        ->destroy();
                        try {
                            $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                                                                                                  'parent_type' => $video->getType(),
                                                                                                  'parent_id' => $video->getIdentity()
                                                                                                  ));
                            // Remove temp file
                            @unlink($thumb_file);
                            @unlink($tmp_file);
                        } catch (Exception $e) {
                            //silence
                        }
                        $video->photo_id = $thumbFileRow->file_id;
                        $video->status = 1;
                        $video->save();
                    }
                    if (isset($values['lat']) && isset($values['lng']) && $values['lat'] != '' && $values['lng'] != '') {
                        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $video->video_id . '", "' . $values['lat'] . '","' . $values['lng'] . '","sesvideo_video")    ON DUPLICATE KEY UPDATE    lat = "' . $values['lat'] . '" , lng = "' . $values['lng'] . '"');
                    }
                    // CREATE AUTH STUFF HERE
                    $auth = Engine_Api::_()->authorization()->context;
                    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                    if (isset($values['auth_view']))
                        $auth_view = $values['auth_view'];
                    else
                        $auth_view = "everyone";
                    $viewMax = array_search($auth_view, $roles);
                    foreach ($roles as $i => $role) {
                        $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
                    }
                    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                    if (isset($values['auth_comment']))
                        $auth_comment = $values['auth_comment'];
                    else
                        $auth_comment = "everyone";
                    $commentMax = array_search($auth_comment, $roles);
                    foreach ($roles as $i => $role) {
                        $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
                    }
                    // Add tags
                    $tags = preg_split('/[,]+/', $values['tags']);
                    $video->tags()->addTagMaps($viewer, $tags);
                    $owner = $video->getOwner();
                    //Create Activity Feed
                    $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'sesvideo_video_create');
                    if ($action != null) {
                        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
                    }
                    // Rebuild privacy
                    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
                    foreach ($actionTable->getActionsByObject($video) as $action) {
                        $actionTable->resetActivityBindings($action);
                    }
                    $db->commit();
                    $this->_counterVideoUploaded++;
                }
            } catch (Exception $e) {
                return false;
            }
            return true;
        }
        public function searchFormAction(){
            $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','hot' => 'Hot','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));

            $search_for = $this-> _getParam('search_for', 'video');
            $setting = Engine_Api::_()->getApi('settings', 'core');
            if ($search_for == 'chanel' && !$setting->getSetting('video_enable_chanel', 1)) {
                //return
            }
            $default_search_type = $this-> _getParam('default_search_type', 'recentlySPcreated');
            if( Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo_enable_location', 1)){
                $location = 'yes';
            }else
                $location = 'no';
            $searchForm = new Sesvideo_Form_Browsesearch(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'price' => $this->_getParam('price', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));
            if($this->_getParam('search_type','video') !== null && $this->_getParam('browse_by', 'yes') == 'yes'){
                $arrayOptions = $filterOptions;
                if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavv', 1)) {
                    unset($arrayOptions['mostSPfavourite']);
                }
                $filterOptions = array();
                foreach ($arrayOptions as $key=>$filterOption) {
                    $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
                    $filterOptions[$key] = ucwords($value);
                }
                $filterOptions = array(''=>'')+$filterOptions;
                $searchForm->sort->setMultiOptions($filterOptions);
                $searchForm->sort->setValue($default_search_type);
            }
            $searchForm->removeElement('loading-img-sesvideo');
            $searchForm->removeElement('lat');
            $searchForm->removeElement('lng');
            $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($searchForm,true);
            $this->generateFormFields($formFields);

        }
        public function browseAction() {
            $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','hot' => 'Hot','mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite'));

            $search_for = $this-> _getParam('search_for', 'video');
            $setting = Engine_Api::_()->getApi('settings', 'core');
            if ($search_for == 'chanel' && !$setting->getSetting('video_enable_chanel', 1)) {
                //return
            }
            $default_search_type = $this-> _getParam('default_search_type', 'recentlySPcreated');
            if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo_enable_location', 1)){
                $location = 'yes';
            }else
                $location = 'no';
            $searchForm = new Sesvideo_Form_Browsesearch(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'price' => $this->_getParam('price', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type));
            if($this->_getParam('search_type','video') !== null && $this->_getParam('browse_by', 'yes') == 'yes'){
                $arrayOptions = $filterOptions;
                if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavv', 1)) {
                    unset($arrayOptions['mostSPfavourite']);
                }
                $filterOptions = array();
                foreach ($arrayOptions as $key=>$filterOption) {
                    $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
                    $filterOptions[$key] = ucwords($value);
                }
                $filterOptions = array(''=>'')+$filterOptions;
                $searchForm->sort->setMultiOptions($filterOptions);
                $searchForm->sort->setValue($default_search_type);
            }

            if(!empty($_POST['location'])){
                $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
                if($latlng){
                    $_POST['lat'] = $latlng['lat'];
                    $_POST['lng'] = $latlng['lng'];
                }
            }
            $manage = $this->_getParam('type','');
            $searchForm->populate($_POST);
            $value = $searchForm->getValues();
            if(!empty($_POST['search']))
                $value['text'] = $_POST['search'];
            if($manage == "manage"){
                if($this->view->viewer()->getIdentity()){
                    $value['user_id'] = $this->view->viewer()->getIdentity();
                    $value["manageVideo"] = "manageVideo";
                }
                else
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'','result'=>'invalid_request'));
            }
            if (!empty($_POST['user_id']))
                $value["user_id"] = $_POST['user_id'];
            if (isset($value['sort']) && $value['sort'] != '') {
                $value['getParamSort'] = str_replace('SP', '_', $value['sort']);
            } else
                $value['getParamSort'] = 'creation_date';

            if (isset($value['getParamSort'])) {
                switch ($value['getParamSort']) {
                    case 'most_viewed':
                        $value['popularCol'] = 'view_count';
                        break;
                    case 'most_liked':
                        $value['popularCol'] = 'like_count';
                        break;
                    case 'most_commented':
                        $value['popularCol'] = 'comment_count';
                        break;
                    case 'most_favourite':
                        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavv', 1)) {
                            $value['popularCol'] = 'favourite_count';
                        }
                        break;
                    case 'hot':
                        $value['popularCol'] = 'is_hot';
                        $value['fixedData'] = 'is_hot';
                        break;
                    case 'sponsored':
                        $value['popularCol'] = 'is_sponsored';
                        $value['fixedData'] = 'is_sponsored';
                        break;
                    case 'featured':
                        $value['popularCol'] = 'is_featured';
                        $value['fixedData'] = 'is_featured';
                        break;
                    case 'most_rated':
                        $value['popularCol'] = 'rating';
                        break;
                    case 'recently_created':
                    default:
                        $value['popularCol'] = 'creation_date';
                        break;
                }
            }
            if(!$manage){
                $value['status'] = 1;
                $value['search'] = 1;
            }
            $value['watchLater'] = true;
            if(!empty($_POST['category_id'])){
                $value['category_id']   = $_POST['category_id'];
            }
            if(!empty($_POST['artist_id'])){
                $value['artist']   = $_POST['artist_id'];
                $value['widgetName'] = "artistViewPage";
            }
            $paginator = Engine_Api::_()->getDbTable('videos', 'sesvideo')->getVideo(array_merge($value,array()));
            $paginator->setItemCountPerPage($this->_getParam('limit',10));
            $paginator->setCurrentPageNumber($this->_getParam('page',1));
            $result["permission"] =  $this->_permission;
            $result['videos'] = $this->getVideos($paginator,$manage);

            $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
            $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
            $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
            $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
            if($result <= 0)
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No video uploaded yet.', 'result' => array()));
            else
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
        }
        protected function getVideos($paginator,$manage = ""){
            $result = array();
            $counter = 0;
            $allowShowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratevideo.show', 1);
            $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.video.rating', 1);
            if ($allowRating == 0) {
                if ($allowShowRating == 0)
                    $showRating = false;
                else
                    $showRating = true;
            } else
                $showRating = true;
            foreach($paginator as $videos){
                $video = $videos->toArray();
                if(!$showRating)
                    unset($video["rating"]);
                $video["description"] = preg_replace('/\s+/', ' ', $video["description"]);
                $video['user_title'] = $videos->getOwner()->getTitle();
                if($this->view->viewer()->getIdentity() != 0){
                    try{
                        $video['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($videos);
                        $video['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($videos);
                        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo.allowfavv', 1)) {
                        $video['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($videos,'favourites','sesvideo','sesvideo_video');
                        $video['content_favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($videos,'favourites','sesvideo','sesvideo_video');
                        }
                    }catch(Exception $e){}
                }
                if($manage){
                    $viewer = Engine_Api::_()->user()->getViewer();
                    $menuoptions= array();
                    $canEdit = $this->_helper->requireAuth()->setAuthParams($videos, null, 'edit')->isValid();
                    $counterMenu = 0;
                    if($canEdit){
                        $menuoptions[$counterMenu]['name'] = "edit";
                        $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
                        $counterMenu++;
                    }
                    $canDelete = $this->_helper->requireAuth()->setAuthParams($videos, null, 'delete')->isValid();
                    if($canDelete){
                        $menuoptions[$counterMenu]['name'] = "delete";
                        $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
                    }
                    $video['menus'] = $menuoptions;
                }
                if( $videos->duration >= 3600 ) {
                    $duration = gmdate("H:i:s", $videos->duration);
                } else {
                    $duration = gmdate("i:s", $videos->duration);
                }
                $video['duration'] = $duration;
                if($this->_permission["watchLater"] && $this->view->viewer()->getIdentity()){
                    if(empty($video["watchlater_id"]) && is_null($video["watchlater_id"])){
                        $video["watchlater_id"] = 0;
                    }
                    $video["canWatchlater"] = true;
                }else{
                    $video["canWatchlater"] = false;
                }
                $video['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($videos,'',"");
                if(!count($video['images']))
                    $video['images']['main'] = $this->getBaseUrl(false,$videos->getPhotoUrl());

                if ($videos instanceof Sesvideo_Model_Chanelvideo){
                    $videoV = Engine_Api::_()->getItem('video',$videos->video_id);
                    if ($videoV->type == 3) {
                        if (!empty($videoV->file_id)) {
                            $storage_file = Engine_Api::_()->getItem('storage_file', $videoV->file_id);
                            $video['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
                            $video['video_extension'] = $storage_file->extension;
                        }
                    }else{
                        $embedded = $videoV->getRichContent(true,array(),'',true);

                        preg_match('/src="([^"]+)"/', $embedded, $match);
                        if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
                            $video['iframeURL'] = str_replace('//','https://',$match[1]);
                        }else{
                            $video['iframeURL'] = $match[1];
                        }
                    }
                }
                $result[$counter] = array_merge($video,array());
                $counter++;
            }
            return $result;
        }
        public function viewAction() {

            if (!$this->_helper->requireSubject()->isValid())
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

            if (!$this->_helper->requireAuth()->setAuthParams($video, null, 'view')->isValid()) {
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
            }
            $video = Engine_Api::_()->core()->getSubject('video');
            $viewer = Engine_Api::_()->user()->getViewer();
            /* Insert data for recently viewed widget */
            if ($viewer->getIdentity() != 0 && isset($video->video_id)) {
                $dbObject = Engine_Db_Table::getDefaultAdapter();
                $dbObject->query('INSERT INTO engine4_sesvideo_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $video->video_id . '", "sesvideo_video","' . $viewer->getIdentity() . '",NOW())    ON DUPLICATE KEY UPDATE    creation_date = NOW()');
            }
            if($video->status != 1)
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("The video you are looking for does not exist or has not been processed yet."), 'result' => array()));
            $response = array();
            $response['video'] = $video->toArray();
            $response['video']['description'] = strip_tags($video->getDescription());
            if($video->location != ""){
                $location = $video->location;
                $latLng = Engine_Api::_()->getDbtable('locations', 'sesbasic')->getLocationData('sesvideo_video',$video->getIdentity());
                if($latLng){
                    $response['video']['location'] = $latLng->toArray();
                    $response['video']['location']['name'] = $location;
                }
            }
            $response['video']['tags'] = $video->tags()->getTagMaps()->toArray();
            if($viewer->getIdentity()){
                $menuoptions= array();
                $canEdit = $this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid();
                $counterMenu = 0;
                if($canEdit){
                    $menuoptions[$counterMenu]['name'] = "edit";
                    $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit");
                    $counterMenu++;
                }
                $canDelete = $this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->isValid();
                if($canDelete){
                    $menuoptions[$counterMenu]['name'] = "delete";
                    $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
                    $counterMenu++;
                }
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.report', 1)){
                    $menuoptions[$counterMenu]['name'] = "report";
                    $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Video");
                }
                $response['menus'] = $menuoptions;
            }
            $photo = $this->getBaseUrl(false,$video->getPhotoUrl());
            if($photo)
                $response['video']["share"]["imageUrl"] = $photo;
						$response['video']["share"]["url"] = $this->getBaseUrl(false,$video->getHref());
            $response['video']["share"]["title"] = $video->getTitle();
            $response['video']["share"]["description"] = strip_tags($video->getDescription());
            $response['video']["share"]['urlParams'] = array(
                                                             "type" => $video->getType(),
                                                             "id" => $video->getIdentity()
                                                             );
            if(is_null($response['video']["share"]["title"]))
                unset($response['video']["share"]["title"]);

            if ($video->type == 3 || $video->type == "upload") {
                if (!empty($video->file_id)) {
                    $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
                    $response['video']['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
                    $$response['video']['video_extension'] = $storage_file->extension;
                }
            }else{
                $embedded = $video->getRichContent(true,array(),'',true);
                preg_match('/src="([^"]+)"/', $embedded, $match);
                if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
                    $response['video']['iframeURL'] = str_replace('//','https://',$match[1]);
                }else{
                    $response['video']['iframeURL'] = $match[1];
                }
            }

            // rating code
            $allowShowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratevideo.show', 1);
            $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.video.rating', 1);
            if ($allowRating == 0) {
                if ($allowShowRating == 0)
                    $showRating = false;
                else
                    $showRating = true;
            } else
                $showRating = true;
            if ($showRating) {
                $canRate = Engine_Api::_()->authorization()->isAllowed('video', $viewer, 'rating');
                $allowRateAgain = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratevideo.again', 1);
                $allowRateOwn = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratevideo.own', 1);
                if ($canRate == 0 || $allowRating == 0)
                    $allowRating = false;
                else
                    $allowRating = true;
                if ($allowRateOwn == 0 && $video->owner_id == $viewer->getIdentity())
                    $allowMine = false;
                else
                    $allowMine = true;
                $rating_type = "video";
                $rating_count = Engine_Api::_()->getDbTable('ratings', 'sesvideo')->ratingCount($video->getIdentity(), $rating_type);
                $rated = Engine_Api::_()->getDbTable('ratings', 'sesvideo')->checkRated($video->getIdentity(), $viewer->getIdentity(), $rating_type);
                $rating_sum = Engine_Api::_()->getDbTable('ratings', 'sesvideo')->getSumRating($video->getIdentity(), $rating_type);
                if ($rating_count != 0) {
                    $total_rating_average = $rating_sum / $rating_count;
                } else {
                    $total_rating_average = 0;
                }

                if (!$allowRateAgain && $rated) {
                    $allowMine = false;
                } else {
                    $allowMine = true;
                }
                if($viewer->getIdentity() == 0){
                    $rate = 0;
                    $message = $this->view->translate('please login to rate');
                }else if($allowShowRating == 1 && $allowRating == 0){
                    $rate = 3;
                    $message = $this->view->translate('rating is disabled');
                }else if($allowRateAgain == 0 && $rated){
                    $rate = 1;
                    $message = $this->view->translate("you already rated");
                }else if($canRate == 0 && $viewer_id != 0){
                    $rate = 4;
                    $message = $this->view->translate('rating is not allowed for your member level');
                }else if(!$allowMine){
                    $rate = 2;
                    $message = $this->view->translate('rating on own video not allowed');;
                }else {
                    $rate = 100;
                    $message = "";
                }
                unset($response['video']['rating']);
                $condition['code'] = $rate;
                $condition['message'] = $message;
                $response['video']['rating'] = $condition;
                $response['video']['rating']['total_rating_average'] = $total_rating_average;
            }else{
                unset($response['video']["rating"]);
            }
            if($viewer->getIdentity()){
                $response['video']['canEdit'] = $video->authorization()->isAllowed($viewer, 'edit');
                $response['video']['canDelete'] = $video->authorization()->isAllowed($viewer, 'delete');
            }
            if (!$viewer->isSelf($video->getOwner())){
                $video->view_count++;
                $video->save();
            }
            $response['video']['user_image'] = $this->userImage($video->getOwner()->getIdentity(),"thumb.profile");
            $response['video']['user_id'] = $video->getOwner()->getIdentity();
            $response['video']['user_title'] = $video->getOwner()->getTitle();
            $response['video']['resource_type'] = 'video';

            if($this->_permission["watchLater"] && $this->view->viewer()->getIdentity()){
                $response['video']["hasWatchlater"] = true;

                if (Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.watchlater', 1)) {
                    $table = Engine_Api::_()->getDbTable('watchlaters', 'sesvideo');
                    $watchLaterTable = $table->info('name');
                    $select = $table->select()->from($watchLaterTable, array('watchlater_id'));
                    $select->where($watchLaterTable . '.owner_id = ' . $this->view->viewer()->getIdentity() .' AND video_id = '.$video->getIdentity());
                    $result = $table->fetchRow($select);
                    if(!$result)
                        $response['video']["hasWatchlater"] = false;
                }

            }else{
                $response['video']["canWatchlater"] = false;
            }

            $artists = json_decode($video->artists,true);
            $artistsArray = array();
            $counter = 0;
            if (count($artists) && $artists != ''){
                foreach( $artists as $item ):
                $artistItem = Engine_Api::_()->getItem('sesvideo_artist',$item);
                if(!$artistItem) continue;
                $artistsArray[$counter]['name'] = $artistItem->getTitle();
                $artistsArray[$counter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($artistItem->artist_photo,'',"");
                $counter++;
                endforeach;
            }
            $response['video']['artists'] = $artistsArray;


            //similar videos

            $similarVideos = $this->getVideos($this->getSimilarVideos($video));
            if(count($similarVideos) > 0){
                $response['similar_videos'] = $similarVideos;
            }
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$response));
        }
        protected function getSimilarVideos($video){
            $table = Engine_Api::_()->getDbTable('videos','sesvideo');
            $tableName = $table->info('name');
            $select = $table->select()->where('video_id != ?',$video->getIdentity())->where('category_id =?',$video->category_id)->limit(10);
            $result = $table->fetchAll($select);
            return $result;
        }
        public function rateAction() {
            $viewer = Engine_Api::_()->user()->getViewer();
            $user_id = $viewer->getIdentity();
            $rating = $this->_getParam('rating');
            $resource_id = $this->_getParam('resource_id');
            $resource_type = $this->_getParam('resource_type');
            $table = Engine_Api::_()->getDbtable('ratings', 'sesvideo');
            $db = $table->getAdapter();
            $db->beginTransaction();
            try {
                Engine_Api::_()->getDbtable('ratings', 'sesvideo')->setRating($resource_id, $user_id, $rating, $resource_type);
                if ($resource_type && $resource_type == 'video')
                    $item = Engine_Api::_()->getItem('sesvideo_video', $resource_id);
                else if ($resource_type && $resource_type == 'sesvideo_artists')
                    $item = Engine_Api::_()->getItem('sesvideo_artists', $resource_id);
                else if($resource_type && $resource_type == 'sesvideo_chanel')
                    $item = Engine_Api::_()->getItem('sesvideo_chanel', $resource_id);
                $item->rating = Engine_Api::_()->getDbtable('ratings', 'sesvideo')->getRating($item->getIdentity(), $resource_type);
                $item->save();
                if ($resource_type == 'video') {
                    $type = 'sesvideo_video_rating';
                } elseif ($resource_type == 'sesvideo_chanel') {
                    $type = 'sesvideo_chanel_rating';
                } elseif ($resource_type == 'sesvideo_artists') {
                    $type = 'sesvideo_artist_rating';
                }
                //Activity Feed / Notification
                if ($resource_type != 'sesvideo_artists') {
                    $owner = $item->getOwner();
                    if ($viewer->getIdentity() != $item->owner_id) {
                        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $type, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));

                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, $type);
                    }
                }
                $result = Engine_Api::_()->getDbtable('actions', 'activity')->fetchRow(array('type =?' => $type, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
                if (!$result) {
                    $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $item, $type);
                    if ($action)
                        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
                }

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));

            }
            //$total = Engine_Api::_()->getDbtable('ratings', 'sesvideo')->ratingCount($item->getIdentity(), $resource_type);
            //    $rating_sum = Engine_Api::_()->getDbtable('ratings', 'sesvideo')->getSumRating($item->getIdentity(), $resource_type);
            //    $data = array();
            //    $totalTxt = $this->view->translate(array('%s rating', '%s ratings', $total), $total);
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Item Rated Successfully.")));

        }
        public function watchAction() {
            $video_id = $this->_getParam('video_id', false);
            $error = true;
            $status = false;
            if ($video_id) {
                $params['video_id'] = $video_id;
                $insertVideo = Engine_Api::_()->sesvideo()->deleteWatchlaterVideo($params);
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>'Action perform successfully.'));
                die;
            }
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'','result'=>'invalid_request'));
            die;
        }
        public function composeUploadAction() {
            $viewer = Engine_Api::_()->user()->getViewer();
            if (!$viewer->getIdentity()) {
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate'));
            }
            if (!$this->getRequest()->isPost()) {
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request'));
            }
            $video_title = $this->_getParam('title');
            $video_url = $this->_getParam('uri');
            $video_type = $this->_getParam('type');
            if (strpos($video_url,'youtube') !== false || strpos($video_url,'youtu.be') !== false)
                $video_type = 1;
            else if(strpos($video_url,'vimeo') !== false)
                $video_type = 2;
            $composer_type = $this->_getParam('c_type', 'wall');
            // extract code
            //$code = $this->extractCode($video_url, $video_type);
            // check if code is valid
            // check which API should be used
            /*if (strpos($video_url,'youtube') !== false || strpos($video_url,'youtu.be') !== false) {
             $valid = $this->checkYouTube($code);
             }
             else if (strpos($video_url,'vimeo') !== false) {
             $valid = $this->checkVimeo($code);
             }else{
             Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Invalid upload video Url.'));
             }*/



            $information = $this->handleInformation($video_url);

            if (empty($information)) {
                $this->view->message = Zend_Registry::get('Zend_Translate')->_('We could not find a video there - please check the URL and try again.');
                return;
            }
            $valid = true;
            $video_type = 'iframely';

            // check to make sure the user has not met their quota of # of allowed video uploads
            // set up data needed to check quota
            $values['user_id'] = $viewer->getIdentity();
            $paginator = Engine_Api::_()->getApi('core', 'sesvideo')->getVideosPaginator($values);
            $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
            $current_count = $paginator->getTotalItemCount();

            if (($current_count >= $quota) && !empty($quota)) {
                // return error message
                $message = $this->view->translate('You have already uploaded the maximum number of videos allowed.');
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
            } else if ($valid) {
                $db = Engine_Api::_()->getItemTable('video')->getAdapter();
                $db->beginTransaction();
                try {


                    //$information = $this->handleInformation($video_type, $code);
                    // create video
                    $table = Engine_Api::_()->getItemTable('video');
                    $video = $table->createRow();
                    $video->title = $information['title'] ? $information['title']  : 'Untitled Video';
                    $video->description = $information['description'] ? $information['description'] : '';
                    $video->duration = $information['duration'] ? $information['duration'] : '';
                    $video->owner_id = $viewer->getIdentity();
                    $video->code = $information['code'];
                    //$video->thumbnail = $information['thumbnail'];
                    $video->duration = $information['duration'];
                    $video->type = $video_type;
                    $video->save();
                    // Now try to create thumbnail
                    //$thumbnail = $this->handleThumbnail($video->type, $video->code);
                    $thumbnail = $information['thumbnail'];
                    $ext = ltrim(strrchr($thumbnail, '.'), '.');
                    $thumbnail_parsed = @parse_url($thumbnail);

                    if (@GetImageSize($thumbnail)) {
                        $valid_thumb = true;
                    } else {
                        $valid_thumb = false;
                    }


                    if(isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '' && $values['type'] != 3 ) {
                        $video->photo_id = $this->setPhoto($form->photo_id, $video->video_id, true);
                        $video->save();
                    } else if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {

                        $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;

                        $src_fh = fopen($thumbnail, 'r');
                        $tmp_fh = fopen($tmp_file, 'w');
                        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                        //resize video thumbnails
                        $image = Engine_Image::factory();
                        $image->open($tmp_file)
                        ->resize(500, 500)
                        ->write($thumb_file)
                        ->destroy();
                        try {
                            $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                                                                                                  'parent_type' => $video->getType(),
                                                                                                  'parent_id' => $video->getIdentity()
                                                                                                  ));
                            // Remove temp file
                            @unlink($thumb_file);
                            @unlink($tmp_file);
                            $video->status = 1;
                            $video->photo_id = $thumbFileRow->file_id;
                            $video->save();

                        } catch (Exception $e){
                            @unlink($thumb_file);
                            @unlink($tmp_file);
                        }
                    }
                    /* $ext = ltrim(strrchr($thumbnail, '.'), '.');
                     $thumbnail_parsed = @parse_url($thumbnail);
                     $imageUploadSize = @getimagesize($thumbnail);
                     $width = isset($imageUploadSize[0]) ? $imageUploadSize[0] : '';
                     $height = isset($imageUploadSize[1]) ? $imageUploadSize[1] : '';
                     if (@$imageUploadSize && $width > 120 && $height > 90) {$valid_thumb = true;}else{
                     if($video_type == 1) {
                     $thumbnail = "http://img.youtube.com/vi/".$video->code."/hqdefault.jpg";
                     if (@getimagesize($thumbnail)) {
                     $valid_thumb = true;
                     $thumbnail_parsed = @parse_url($thumbnail);
                     } else {    $valid_thumb = false;}
                     }else
                     $valid_thumb = false;
                     }
                     if($valid_thumb){
                     $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                     $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
                     $src_fh = fopen($thumbnail, 'r');
                     $tmp_fh = fopen($tmp_file, 'w');
                     stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                     $image = Engine_Image::factory();
                     $image->open($tmp_file)
                     ->resize(500, 500)
                     ->write($thumb_file)
                     ->destroy();
                     $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                     'parent_type' => $video->getType(),
                     'parent_id' => $video->getIdentity()
                     ));
                     @unlink($tmp_file);
                     @unlink($thumb_file);
                     $video->photo_id = $thumbFileRow->file_id;
                     }*/
                    // If video is from the composer, keep it hidden until the post is complete

                    $video->status = 1;
                    $video->save();
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    @unlink($tmp_file);
                    @unlink($thumb_file);
                    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $e->getMessage()));
                }
                // make the video public
                if ($composer_type === 'wall') {
                    // CREATE AUTH STUFF HERE
                    $auth = Engine_Api::_()->authorization()->context;
                    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
                    foreach ($roles as $i => $role) {
                        $auth->setAllowed($video, $role, 'view', ($i <= $roles));
                        $auth->setAllowed($video, $role, 'comment', ($i <= $roles));
                    }
                }
                $result['video']['status'] = true;
                $result['video']['video_id'] = (string) $video->video_id;
                $result['video']['photo_id'] = $video->photo_id;
                $result['video']['title'] = $video->title;
                $result['video']['description'] = $video->description;
                $result['video']['src'] = $this->getBaseUrl(false,$video->getPhotoUrl());
                $result['video']['message'] = $this->view->translate('Video posted successfully');
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
            } else {
                $message = $this->view->translate('We could not find a video there - please check the URL and try again.');
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
            }
        }
        public function deleteAction() {
            $viewer = Engine_Api::_()->user()->getViewer();
            $video = Engine_Api::_()->getItem('video', $this->getRequest()->getParam('video_id'));
            if (!$this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->isValid())
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"user_not_autheticate", 'result' => array()));
            // In smoothbox
            if (!$video) {
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"invalid_request", 'result' => array()));
            }
            if (!$this->getRequest()->isPost()) {
                $this->view->status = false;
                Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"invalid_request", 'result' => array()));
            }


            $db = $video->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                Engine_Api::_()->getApi('core', 'sesvideo')->deleteVideo($video);
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            $this->view->status = true;
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('Video has been deleted.');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
        }
        public function validationAction($params = array()) {
            $video_type = $this->_getParam('type',$params['type']);
            if(!empty($params['type']))
                $video_type = $params['type'];
            $code = $this->_getParam('code',$params['code']);
            $ajax = $this->_getParam('ajax', false);
            $mURL = $this->_getParam('url',$params['url']);
            $valid = false;
            // check which API should be used
            if ($video_type == "youtube") {
                $valid = $this->checkYouTube($code);
            } else if ($video_type == "vimeo") {
                $valid = $this->checkVimeo($code);
            } else if ($video_type == 'dailymotion') {
                $valid = $this->checkdailymotion($code);
            } else if ($video_type == 'youtubePlaylist') {
                $valid = $this->checkYoutubePlaylist($code);
            } else if ($video_type == 'embedCode') {
                $valid = $this->checkembedCode($code);
            }else if ($video_type == 'fromurl') {
                $valid = $this->checkFromUrl($code);
            }
            if(count($params))
                return $valid;
            $this->view->code = $code;
            $this->view->ajax = $ajax;
            $this->view->valid = $valid;
        }
        public function checkembedCode($url){
            if(!$url)
                return false;
            $url = str_replace('embed','iframe',$url);
            $regex = '/(<iframe.*? src=(\"|\'))(.*?)((\"|\').*)/';
            preg_match($regex, $url, $matches);
            if(count($matches) > 2)
            {
                return true;
            }else
                return false;
        }
        public function checkFromUrl($url){
            if(!$url)
                return false;
            $ch = curl_init(trim($url));
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            # get the content type
            $output = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
            if(strpos($output,'video') === FALSE){
                return false;
            }else
                return true;
        }
        // HELPER FUNCTIONS
        public function extractCode($url, $type) {
            switch ($type) {
                    //youtube
                case "1":
                    // change new youtube URL to old one
                    $new_code = @pathinfo($url);
                    $url = preg_replace("/#!/", "?", $url);

                    // get v variable from the url
                    $arr = array();
                    $arr = @parse_url($url);
                    if ($arr['host'] === 'youtu.be') {
                        $data = explode("?", $new_code['basename']);
                        $code = $data[0];
                    } else {
                        $parameters = $arr["query"];
                        parse_str($parameters, $data);
                        $code = $data['v'];
                        if ($code == "") {
                            $code = $new_code['basename'];
                        }
                    }
                    return $code;
                    //vimeo
                case "2":
                    // get the first variable after slash
                    $code = @pathinfo($url);
                    return $code['basename'];
                    //dailymotion
                case "4":
                    // get the first variable after slash
                    $code = @pathinfo($url);
                    $code = explode('_', $code['basename']);
                    if (isset($code[0]))
                        return $code[0];
                    else
                        return '';
            }
        }
        // YouTube Functions
        public function checkYouTubePlaylist($code) {
            require_once 'application/modules/Sesvideo/controllers/Google/autoload.php';
            require_once 'application/modules/Sesvideo/controllers/Google/Client.php';
            require_once 'application/modules/Sesvideo/controllers/Google/Service/YouTube.php';
            $client = new Google_Client();
            $client->setDeveloperKey(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey', 0));
            $youtube = new Google_Service_YouTube($client);
            $nextPageToken = '';
            $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
                                                                                                 'playlistId' => $code,
                                                                                                 'maxResults' => 50,
                                                                                                 'pageToken' => $nextPageToken));
            if (isset($playlistItemsResponse['items'][0]['snippet']['resourceId']['videoId']))
                return true;
            else
                return false;
        }
        // YouTube Functions
        public function checkYouTube($code) {

            $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
            if (function_exists('curl_init')){ echo 'https://www.googleapis.com/youtube/v3/videos?part=id&id=' . $code . '&key=' . $key;die;
                $data =  $this->url_get_contents('https://www.googleapis.com/youtube/v3/videos?part=id&id=' . $code . '&key=' . $key);
            }else{
                if (!$data = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=id&id=' . $code . '&key=' . $key))
                    return false;
            }
            $data = Zend_Json::decode($data);
            if (empty($data['items']))
                return false;
            return true;
        }
        function url_get_contents ($Url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $Url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        }
        // Vimeo Functions
        public function checkVimeo($code) {
            //http://www.vimeo.com/api/docs/simple-api
            //http://vimeo.com/api/v2/video
            $data = @simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
            $id = count($data->video->id);
            if ($id == 0)
                return false;
            return true;
        }
        public function checkdailymotion($code) {
            //https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title
            if (function_exists('curl_init')){
                $data =  $this->url_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed");
            }else
                $data = file_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed");
            if ($data != '') {
                $data = json_decode($data, true);
                if (isset($data['allow_embed']) && $data['allow_embed'])
                    return true;
            }
            return false;
        }
        // handles thumbnails
        public function handleThumbnail($type, $code = null) {
            switch ($type) {
                    //youtube
                case "1":
                    return "http://img.youtube.com/vi/$code/maxresdefault.jpg";
                    //vimeo
                case "2":
                    //thumbnail_medium
                    if (function_exists('curl_init')){
                        $data =  unserialize($this->url_get_contents("http://vimeo.com/api/v2/video/$code.php"));
                    }else
                        $data = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$code.php"));
                    $thumbnail = $data[0]['thumbnail_large'];
                    return $thumbnail;
                case "4":
                    if (function_exists('curl_init')){
                        $data =  ($this->url_get_contents("https://api.dailymotion.com/video/$code?fields=thumbnail_url"));
                    }else
                        $data = file_get_contents("https://api.dailymotion.com/video/$code?fields=thumbnail_url");
                    if ($data != '') {
                        $data = json_decode($data, true);
                        $thumbnail_url = (isset($data['thumbnail_url']) && $data['thumbnail_url']) ? $data['thumbnail_url'] : '';
                        return $thumbnail_url;
                    }
            }
        }
        // retrieves infromation and returns title + desc
        public function handleInformation($uri) {
            $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
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
            /*switch ($type) {
             //youtube
             case "1":
             $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
             if (function_exists('curl_init')){
             $data =  ($this->url_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$code&key=$key"));
             }else
             $data = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$code&key=$key");
             if (empty($data)) {
             return;
             }
             $data = Zend_Json::decode($data);
             $information = array();
             $youtube_video = $data['items'][0];
             $information['title'] = $youtube_video['snippet']['title'];
             $information['description'] = $youtube_video['snippet']['description'];
             $information['duration'] = Engine_Date::convertISO8601IntoSeconds($youtube_video['contentDetails']['duration']);
             return $information;
             //vimeo
             case "2":
             //thumbnail_medium
             $data = simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
             $thumbnail = $data->video->thumbnail_medium;
             $information = array();
             $information['title'] = $data->video->title;
             $information['description'] = $data->video->description;
             $information['duration'] = $data->video->duration;
             return $information;
             case "4":
             if (function_exists('curl_init')){
             $data =  ($this->url_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title"));
             }else
             $data = file_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title");
             $data = json_decode($data, true);
             $information['title'] = $data['title'];
             $information['description'] = $data['description'];
             $information['duration'] = $data['duration'];
             return $information;
             }*/
        }
        protected function buildBaseString($baseURI, $method, $params) {
            $r = array();
            ksort($params);
            foreach($params as $key=>$value){
                $r[] = "$key=" . rawurlencode($value);
            }
            return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
        }
        protected    function getMetaTags($str)
        {
            $pattern = '
            ~<\s*meta\s

            # using lookahead to capture type to $1
            (?=[^>]*?
             \b(?:name|property|http-equiv)\s*=\s*
             (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
             ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
              )

             # capture content to $2
             [^>]*?\bcontent\s*=\s*
             (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
             ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
              [^>]*>

              ~ix';

              if(preg_match_all($pattern, $str, $out))
              return array_combine($out[1], $out[2]);
              return array();
              }
              protected  function buildAuthorizationHeader($oauth) {
              $r = 'Authorization: OAuth ';
              $values = array();
              foreach($oauth as $key=>$value)
              $values[] = "$key=\"" . rawurlencode($value) . "\"";
              $r .= implode(', ', $values);
              return $r;
              }
              protected function removeEmoji($text) {

              $clean_text = "";

              // Match Emoticons
              $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
              $clean_text = preg_replace($regexEmoticons, '', $text);

              // Match Miscellaneous Symbols and Pictographs
              $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
              $clean_text = preg_replace($regexSymbols, '', $clean_text);

              // Match Transport And Map Symbols
              $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
              $clean_text = preg_replace($regexTransport, '', $clean_text);

              // Match Miscellaneous Symbols
              $regexMisc = '/[\x{2600}-\x{26FF}]/u';
              $clean_text = preg_replace($regexMisc, '', $clean_text);

              // Match Dingbats
              $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
              $clean_text = preg_replace($regexDingbats, '', $clean_text);

              return $clean_text;
              }
              function ranger($url){
              $headers = array(
                               "Range: bytes=0-32768"
                               );

              $curl = curl_init($url);
              curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
              $raw = curl_exec($curl);
              curl_close($curl);
              $start = microtime(true);

              $im = imagecreatefromstring($raw);

              $width = imagesx($im);
              $height = imagesy($im);

              $stop = round(microtime(true) - $start, 5);
              return array('width'=>$width,'height'=>$height);
              }
              }
