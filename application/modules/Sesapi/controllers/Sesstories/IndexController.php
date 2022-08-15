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


class Sesstories_IndexController extends Sesapi_Controller_Action_Standard
{

    const staticUserImage = 'application/modules/User/externals/images/nophoto_user_thumb_profile.png';

    public function storysettingsAction()
    {

        $user_id = $this->_getParam('user_id', null);
        $user = Engine_Api::_()->getItem('user', $user_id);

        $settings = Engine_Api::_()->getApi('settings', 'core');
        $auth = Engine_Api::_()->authorization()->context;

        $this->view->form = $form = new Sesstories_Form_Settings_Settings(array(
            'item' => $user,
        ));

        // Hides options from the form if there are less then one option.
        if (count($form->story_privacy->options) <= 1) {
            $form->removeElement('story_privacy');
        }
        if (count($form->story_comment->options) <= 1) {
            $form->removeElement('story_comment');
        }

        // Populate form
        $form->populate($user->toArray());

        // Check if post and populate
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $newFormFieldsArray = array();
            foreach ($formFields as $fields) { //echo "<pre>";var_dump($fields);die;
                foreach ($fields as $key => $field) { }
                $newFormFieldsArray[] = $fields;
            }
            $this->generateFormFields($newFormFieldsArray);
        }

        // Check if valid
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            $this->validateFormFields($validateFields);
        }

        $form->save();
        $values = $form->getValues();
        $user->setFromArray($values)->save();

        $message = $this->view->translate('Your changes have been saved.');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => $message));
    }

    public function getallmutedmembersAction()
    {

        $user_id = $this->_getParam('user_id', null);

        $table = Engine_Api::_()->getDbTable('mutes', 'sesstories');
        $tableName = $table->info('name');

        $select = $table->select()
            ->from($tableName)
            ->where('user_id =?', $user_id);

        $results = $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_getParam('page'));

        $mutedusers = $result = array();
        $counterLoop =  0;

        foreach ($results as $viewers) {
            $user = Engine_Api::_()->getItem('user', $viewers->resource_id);
            $userImage = $user->getPhotoUrl() ? $user->getPhotoUrl() : self::staticUserImage;

            $mutedusers[$counterLoop]['user_image'] = $this->getBaseUrl(true, $userImage);
            $mutedusers[$counterLoop]['user_title'] = $user->getTitle();
            $mutedusers[$counterLoop]['user_id'] = $user->getIdentity();

            $menuoptions['name'] = "unmute";
            $menuoptions['label'] = $this->view->translate("Unmute");
            $menuoptions['mute_id'] = $viewers->getIdentity();
            $mutedusers[$counterLoop]['options'] = $menuoptions;

            $counterLoop++;
        }
        $result['viewers'] = $mutedusers;

        if (count($results) > 0) {
            $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
            $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
            $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
            $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        }

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function getstoryviewersAction()
    {
        $story_id = $this->_getParam('story_id', null);

        $table = Engine_Api::_()->getDbTable('recentlyviewitems', 'sesstories');
        $tableName = $table->info('name');

        $select = $table->select()
            ->from($tableName)
            ->where('resource_id =?', $story_id);

        $results = $table->fetchAll($select);

        $story_viewerdata = $result = array();
        $counterLoop =  0;

        foreach ($results as $viewers) {
            $user = Engine_Api::_()->getItem('user', $viewers->owner_id);
            $staticUserImage = 'application/modules/User/externals/images/nophoto_user_thumb_profile.png';
            $userImage = $user->getPhotoUrl() ? $user->getPhotoUrl() : $staticUserImage;
            $story_viewerdata[$counterLoop]['user_image'] = $this->getBaseUrl(true, $userImage);
            $story_viewerdata[$counterLoop]['user_title'] = $user->getTitle();
            $story_viewerdata[$counterLoop]['user_id'] = $user->getIdentity();
            $story_viewerdata[$counterLoop]['creation_date'] = $viewers->creation_date;
            $counterLoop++;
        }
        $result['viewers'] = $story_viewerdata;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }

    public function allstoriesAction()
    {

        $user_id = $this->_getParam('user_id', null);
        $userarchivedstories = $this->_getParam('userarchivedstories', null);

        $highlight = $this->_getParam('highlight', null);

        if (empty($userarchivedstories) && empty($highlight)) {
            $user = Engine_Api::_()->getItem('user', $user_id);

            $staticUserImage = 'application/modules/User/externals/images/nophoto_user_thumb_profile.png';
            $userImage = $user->getPhotoUrl() ? $user->getPhotoUrl() : $staticUserImage;

            //$getAllUserHaveStories = Engine_Api::_()->getDbTable('stories', 'sesstories')->getAllUserHaveStories(array('user_id' => $user_id));

            $getAllUserHaveStories = Engine_Api::_()->getDbTable('userinfos', 'sesstories')->getAllUserHaveStories(array('user_id' => $user_id));
            $friendArra = array();
            if (count($getAllUserHaveStories) > 0) {
                foreach ($getAllUserHaveStories as $getAllUserHaveStorie) {
                    $friendArra[] = $getAllUserHaveStorie->owner_id;
                }
            }

            //$friendArra = $user->membership()->getMembershipsOfIds();
        } else {
            $friendArra = array($user_id);
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $settings = Engine_Api::_()->getApi('settings', 'core');

        $finalArray = $images = $menuoptions = array();
        $counterLoop = $counter = $menucounter = 0;

        if (empty($userarchivedstories) && empty($highlight)) {
            $viewerresults = Engine_Api::_()->getDbTable('stories', 'sesstories')->getAllStories($user_id);
            if (count($viewerresults) > 0) {
                foreach ($viewerresults as $item) {
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming')) {
                        $elivehost = Engine_Api::_()->getDbtable('elivehosts', 'elivestreaming')->getHostId(array('story_id' => $item->story_id));
                        if ($elivehost && ((_SESAPI_VERSION_IOS < 1.8 && _SESAPI_PLATFORM_SERVICE == 1))) {
                            continue;
                        }
                    }
                    // for live streaming.
                    $liveStreamImage = $staticUserImage;
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming'))
                        if ($settings->getSetting('elivestreaming.showliveimage'))
                            $liveStreamImage = $settings->getSetting('elivestreaming.storieslivedefaultimage');
                    $storageObject = Engine_Api::_()->getItemTable('storage_file')->getFile($item->file_id, '');
                    $images['story_content'][$counter]['story_id'] = $item->story_id;
                    $images['story_content'][$counter]['media_url'] = $this->getBaseUrl(true, $storageObject ? $storageObject->map() : $liveStreamImage);
                    $images['story_content'][$counter]['comment'] = $item->title;
                    if (!empty($item->type)) {
                        $images['story_content'][$counter]['is_video'] = true;
                    } else {
                        $images['story_content'][$counter]['is_video'] = false;
                    }
                    $images['story_content'][$counter]['highlight'] = $item->highlight;
                    $images['story_content'][$counter]['view_count'] = $item->view_count;
                    $images['story_content'][$counter]['like_count'] = $item->like_count;
                    $images['story_content'][$counter]['comment_count'] = $item->comment_count;
                    $images['story_content'][$counter]['creation_date'] = $item->creation_date;

                    $menucounter = 0;
                    $menuoptions[$menucounter]['name'] = "delete";
                    $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
                    $menucounter++;
                    $images['story_content'][$counter]['options'] = $menuoptions;


                    $viewer_id = $this->view->viewer()->getIdentity();
                    if ($viewer_id) {
                        $itemTable = Engine_Api::_()->getItemTable($item->getType(), $item->getIdentity());
                        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
                        $tableMainLike = $tableLike->info('name');
                        $select = $tableLike->select()
                            ->from($tableMainLike)
                            ->where('resource_type = ?', $item->getType())
                            ->where('poster_id = ?', $viewer_id)
                            ->where('poster_type = ?', 'user')
                            ->where('resource_id = ?', $item->getIdentity());
                        $resultData = $tableLike->fetchRow($select);
                        if ($resultData) {
                            $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id);
                            $photo['reaction_type'] = $item_activity_like->type;
                        }
                    }

                    $table = Engine_Api::_()->getDbTable('likes', 'core');
                    $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
                    $coreliketableName = $coreliketable->info('name');

                    $recTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->info('name');
                    $select = $table->select()->from($table->info('name'), array('total' => new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?', $item->getIdentity())->group('type')->setIntegrityCheck(false);
                    $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
                    $select->where('resource_type =?', $item->getType());
                    $select->joinLeft($recTable, $recTable . '.reaction_id =' . $coreliketableName . '.type', array('file_id'))->where('enabled =?', 1)->order('total DESC');
                    $resultData =  $table->fetchAll($select);

                    $is_like = Engine_Api::_()->sesapi()->contentLike($item);
                    $reactionData = array();
                    $reactionCounter = 0;
                    if (count($resultData)) {
                        foreach ($resultData as $type) {
                            $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)', $type['total'], Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                            $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                            $reactionCounter++;
                        }
                        $images['story_content'][$counter]['reactionData'] = $reactionData;
                    }
                    if ($is_like) {
                        $images['story_content'][$counter]['is_like'] = true;
                        $like = true;
                        $type = $is_like['reaction_type'];
                        $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type));
                        if ($type)
                            $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
                        else
                            $text = 'Like';
                    } else {
                        $images['story_content'][$counter]['is_like'] = false;
                        $like = false;
                        $type = '';
                        $imageLike = '';
                        $text = 'Like';
                    }
                    if (empty($like)) {
                        $images['story_content'][$counter]["like"]["name"] = "like";
                    } else {
                        $images['story_content'][$counter]["like"]["name"] = "unlike";
                    }

                    $images['story_content'][$counter]['reactionUserData'] = $this->view->FluentListUsers($item->likes()->getAllLikesUsers(), '', $item->likes()->getLike($this->view->viewer()), $this->view->viewer());

                    $counter++;
                }
                if (count($viewerresults) > 0) {
                    $result['my_story'] = $images;
                    $result['my_story']['user_id'] = $user->getIdentity();
                    $result['my_story']['username'] = $user->getTitle();
                    $result['my_story']['user_image'] = $this->getBaseUrl(true, $userImage);
                }
            } else {
                $result['my_story'] = array();
                $result['my_story']['user_id'] = $user->getIdentity();
                $result['my_story']['username'] = $user->getTitle();
                $result['my_story']['user_image'] = $this->getBaseUrl(true, $userImage);
            }
        }

        if (count($friendArra) > 0) {
            foreach ($friendArra as $key => $friend_id) {

                if (empty($userarchivedstories) && empty($highlight)) {
                    if ($friend_id == $viewer_id) continue;
                }

                $getAllMutesMembers = Engine_Api::_()->getDbTable('mutes', 'sesstories')->getAllMutesMembers(array('user_id' => $viewer_id));
                if (count($getAllMutesMembers) > 0) {
                    if (in_array($friend_id, $getAllMutesMembers)) continue;
                }

                if (empty($userarchivedstories)) {
                    $results = Engine_Api::_()->getDbTable('stories', 'sesstories')->getAllStories($friend_id, $userarchivedstories, $highlight);
                } else {
                    $select = Engine_Api::_()->getDbTable('stories', 'sesstories')->getAllStories($friend_id, $userarchivedstories, $highlight);

                    $results = $paginator = Zend_Paginator::factory($select);
                    $paginator->setItemCountPerPage(10);
                    $paginator->setCurrentPageNumber($this->_getParam('page'));
                }

                $user = Engine_Api::_()->getItem('user', $friend_id);
                $userImage = $user->getPhotoUrl() ? $user->getPhotoUrl() : $staticUserImage;

                //$canComment =   $album->authorization()->isAllowed($viewer, 'comment') ? true : false; $user->authorization()->isAllowed($viewer, 'story_comment') ? true : false;

                $images = array();
                $counter = 0;
                $existsItem = false;
                foreach ($results as $item) {

                    // for live streaming.
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming')) {
                        $elivehost = Engine_Api::_()->getDbtable('elivehosts', 'elivestreaming')->getHostId(array('story_id' => $item->story_id));
                        if($elivehost && ((_SESAPI_VERSION_IOS > 1.8 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.0 && _SESAPI_PLATFORM_SERVICE == 2))) {
                            if ($elivehost && $elivehost['status'] == 'started') {
                                if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming'))
                                    if ($settings->getSetting('elivestreaming.showliveimage'))
                                        $images['story_content'][$counter]['media_url'] = $this->getBaseUrl(true, $settings->getSetting('elivestreaming.storieslivedefaultimage'));
                                    else
                                        $images['story_content'][$counter]['media_url'] = $this->getBaseUrl(true, $userImage);
                            }
                        }else if($elivehost){
                            continue;
                        }
                    }
                    $existsItem = true;
                    $storageObject = Engine_Api::_()->getItemTable('storage_file')->getFile($item->file_id, '');
                    $images['story_content'][$counter]['story_id'] = $item->story_id;
                    $images['story_content'][$counter]['media_url'] = $this->getBaseUrl(true, $storageObject ? $storageObject->map() : $staticUserImage);
                    $images['story_content'][$counter]['comment'] = $item->title;
                    if (!empty($item->type)) {
                        $images['story_content'][$counter]['is_video'] = true;
                    } else {
                        $images['story_content'][$counter]['is_video'] = false;
                    }
                    $images['story_content'][$counter]['highlight'] = $item->highlight;
                    $images['story_content'][$counter]['view_count'] = $item->view_count;
                    $images['story_content'][$counter]['like_count'] = $item->like_count;
                    $images['story_content'][$counter]['comment_count'] = $item->comment_count;
                    $images['story_content'][$counter]['creation_date'] = $item->creation_date;

                    $images['story_content'][$counter]['can_comment'] = $item->authorization()->isAllowed($viewer, 'comment') ? true : false;


                    $menucounter = 0;
                    if ($viewer_id != $item->owner_id) {
                        $menuoptions[$menucounter]['name'] = "mute";
                        $menuoptions[$menucounter]['label'] = $this->view->translate("Mute");
                        $menucounter++;

                        $menuoptions[$menucounter]['name'] = "report";
                        $menuoptions[$menucounter]['label'] = $this->view->translate("Report");
                        $menucounter++;

                        $images['story_content'][$counter]['options'] = $menuoptions;
                    }

                    //Reaction work
                    $viewer_id = $this->view->viewer()->getIdentity();
                    if ($viewer_id) {
                        $itemTable = Engine_Api::_()->getItemTable($item->getType(), $item->getIdentity());
                        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
                        $tableMainLike = $tableLike->info('name');
                        $select = $tableLike->select()
                            ->from($tableMainLike)
                            ->where('resource_type = ?', $item->getType())
                            ->where('poster_id = ?', $viewer_id)
                            ->where('poster_type = ?', 'user')
                            ->where('resource_id = ?', $item->getIdentity());
                        $resultData = $tableLike->fetchRow($select);
                        if ($resultData) {
                            $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id);
                            $reaction_type = $item_activity_like->type;
                        }
                    }

                    $table = Engine_Api::_()->getDbTable('likes', 'core');
                    $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
                    $coreliketableName = $coreliketable->info('name');

                    $recTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->info('name');
                    $select = $table->select()->from($table->info('name'), array('total' => new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?', $item->getIdentity())->group('type')->setIntegrityCheck(false);
                    $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
                    $select->where('resource_type =?', $item->getType());
                    $select->joinLeft($recTable, $recTable . '.reaction_id =' . $coreliketableName . '.type', array('file_id'))->where('enabled =?', 1)->order('total DESC');
                    $resultData =  $table->fetchAll($select);

                    $is_like = Engine_Api::_()->sesapi()->contentLike($item);
                    $reactionData = array();
                    $reactionCounter = 0;
                    if (count($resultData)) {
                        foreach ($resultData as $type) {
                            $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)', $type['total'], Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                            $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                            $reactionCounter++;
                        }
                        $images['story_content'][$counter]['reactionData'] = $reactionData;
                    }
                    if ($is_like) {
                        $images['story_content'][$counter]['is_like'] = true;
                        $like = true;
                        $type = $reaction_type; //$is_like['reaction_type'];
                        $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type));
                        if ($type)
                            $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
                        else
                            $text = 'Like';
                    } else {
                        $images['story_content'][$counter]['is_like'] = false;
                        $like = false;
                        $type = '';
                        $imageLike = '';
                        $text = 'Like';
                    }
                    if (empty($like)) {
                        $images['story_content'][$counter]["like"]["name"] = "like";
                    } else {
                        $images['story_content'][$counter]["like"]["name"] = "unlike";
                    }

                    $images['story_content'][$counter]['reactionUserData'] = $this->view->FluentListUsers($item->likes()->getAllLikesUsers(), '', $item->likes()->getLike($this->view->viewer()), $this->view->viewer());
                    //Reaction work end
                    // for live streaming.
                    // for live streaming.


                    $counter++;
                }
                if (count($results) > 0 && $existsItem) {
                    $result['stories'][$counterLoop] = $images;
                    $result['stories'][$counterLoop]['user_id'] = $user->getIdentity();
                    $result['stories'][$counterLoop]['username'] = $user->getTitle();
                    $result['stories'][$counterLoop]['user_image'] = $this->getBaseUrl(true, $userImage);
                    // for live streaming.
                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming')) {
                        foreach ($images['story_content'] as $key => $item) {
                            if ($key == 0) {
                                $elivehost = Engine_Api::_()->getDbtable('elivehosts', 'elivestreaming')->getHostId(array('story_id' => $item['story_id']));
                                if ($elivehost && $elivehost['status'] == 'started') {
                                    $result['stories'][$counterLoop]['is_live'] = true;
                                    $result['stories'][$counterLoop]['activity_id'] = $elivehost['action_id'];
                                } else {
                                    $result['stories'][$counterLoop]['is_live'] = false;
                                }
                            }
                        }
                    }
                    $counterLoop++;
                    if (!empty($userarchivedstories)) {
                        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
                        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
                        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
                        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
                    }
                }
            }
        }

        if (!empty($userarchivedstories)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
        }
    }


    public function createAction()
    {

        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();


        //Current User Privacy
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_network', 'registered');

        foreach ($roles as $role) {
            if ($auth->isAllowed($viewer, $role, 'story_view')) {
                $auth_view = $role;
            } else {
                $auth_view = 'owner_member';
            }

            if ($auth->isAllowed($viewer, $role, 'story_comment')) {
                $auth_comment = $role;
            } else {
                $auth_comment = 'owner_member';
            }
        }

        Engine_Api::_()->sesstories()->isExist($viewer->getIdentity(), $auth_view);

        // Process
        $table = Engine_Api::_()->getDbtable('stories', 'sesstories');
        //$db = $table->getAdapter();
        //$db->beginTransaction();


        $values['owner_id'] = $viewer->getIdentity();
        $values['type'] = '0';

        $images = $menuoptions = array();
        $counter = $menucounter = 0;

        if (isset($_FILES['attachmentVideo'])) {
            foreach ($_FILES['attachmentVideo']['name'] as $key => $files) {

                if (!empty($_FILES['attachmentVideo']['name'][$key])) {
                    try {

                        $type = explode('/', $_FILES['attachmentVideo']['type'][$key]);

                        $item = $table->createRow();
                        $values['type'] = '1';
                        $item->setFromArray($values);
                        $item->title = $_POST['comment'][$key] ? $_POST['comment'][$key] : "";
                        $item->save();

                        // Auth
                        $viewMax = array_search($auth_view, $roles);
                        $commentMax = array_search($auth_comment, $roles);

                        foreach ($roles as $i => $role) {
                            $auth->setAllowed($item, $role, 'view', ($i <= $viewMax));
                            $auth->setAllowed($item, $role, 'comment', ($i <= $commentMax));
                        }

                        $image = array('name' => $_FILES['attachmentVideo']['name'][$key], 'type' => $_FILES['attachmentVideo']['type'][$key], 'tmp_name' => $_FILES['attachmentVideo']['tmp_name'][$key], 'error' => $_FILES['attachmentVideo']['error'][$key], 'size' => $_FILES['attachmentVideo']['size'][$key]);


                        if ($type[1] == 'mp4') {
                            $storage = Engine_Api::_()->getItemTable('storage_file');
                            $storageObject = $storage->createFile($image, array(
                                'parent_id' => $item->getIdentity(),
                                'parent_type' => $item->getType(),
                                'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                            ));
                            // Remove temporary file
                            @unlink($_FILES['attachmentVideo']['tmp_name'][$key]);
                            $item->file_id = $storageObject->file_id;
                            $item->save();
                            if ($item->getIdentity()) {
                                $result['stories_id'] = $item->getIdentity();
                                $result['message'] = "Your stories video is currently being processed";
                                // for live streaming.
                                $postData = $this->getRequest()->getPost();
                                if (!empty($postData['elivehost_id'])) {
                                    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming')) {
                                        if (!empty($postData['elivehost_id'])) {
                                            $elivehostItem = Engine_Api::_()->getItem('elivehost', $postData['elivehost_id']);
                                            if (!empty($elivehostItem)) {
                                                if ($postData['canPost'] == "true")
                                                    $elivehostItem->status = 'processing';
                                                if ($postData['canPost'] == "false")
                                                    $elivehostItem->status = 'completed';
                                                $elivehostItem->story_id = $item->getIdentity();
                                                $elivehostItem->save();
                                            }
                                        }
                                    }
                                }
                            }
                        } else {

                            $params = array(
                                'parent_id' => $item->getIdentity(),
                                'parent_type' => $item->getType(),
                                'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                            );

                            $video = Engine_Api::_()->sesstories()->createVideo($params, $image, $item);
                        }
                        // Commit
                        //$db->commit();
                    } catch (Exception $e) {
                        continue;
                    }
                    //$story_id = $item->getIdentity();

                    // 									$images['story_content'][$counter]['media_url'] = $this->getBaseUrl(true,$storageObject->map());
                    // 									$images['story_content'][$counter]['comment'] = $item->title;
                    // 									$images['story_content'][$counter]['is_video'] = false;
                    // 									$images['story_content'][$counter]['like_count'] = $item->like_count;
                    // 									$images['story_content'][$counter]['comment_count'] = $item->comment_count;
                    // 									$images['story_content'][$counter]['creation_date'] = $item->creation_date;
                    // 									$images['story_content'][$counter]['story_id'] = $story_id;
                    //
                    // 									$menucounter = 0;
                    // 									if($viewer_id != $item->user_id) {
                    // 										$menuoptions[$menucounter]['name'] = "mute";
                    // 										$menuoptions[$menucounter]['label'] = $this->view->translate("Mute");
                    // 										$menucounter++;
                    //
                    // 										$menuoptions[$menucounter]['name'] = "report";
                    // 										$menuoptions[$menucounter]['label'] = $this->view->translate("Report");
                    // 										$menucounter++;
                    //
                    // 										$images['story_content'][$counter]['options'] = $menuoptions;
                    // 									}
                    // 									$counter++;

                }
            }
        }

        if (isset($_FILES['attachmentImage'])) {
            foreach ($_FILES['attachmentImage']['name'] as $key => $files) {

                if (!empty($_FILES['attachmentImage']['name'][$key])) {
                    try {
                        $item = $table->createRow();
                        $item->setFromArray($values);
                        if (isset($_POST['comment'][$key]))
                            $item->title = $_POST['comment'][$key];
                        else
                            $item->title = "Untitled Story";
                        $item->view_privacy = $auth_view;
                        $item->save();

                        $image = array('name' => $_FILES['attachmentImage']['name'][$key], 'type' => $_FILES['attachmentImage']['type'][$key], 'tmp_name' => $_FILES['attachmentImage']['tmp_name'][$key], 'error' => $_FILES['attachmentImage']['error'][$key], 'size' => $_FILES['attachmentImage']['size'][$key]);
                        $storage = Engine_Api::_()->getItemTable('storage_file');
                        $storageObject = $storage->createFile($image, array(
                            'parent_id' => $item->getIdentity(),
                            'parent_type' => $item->getType(),
                            'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                        ));
                        // Remove temporary file
                        @unlink($_FILES['attachmentImage']['tmp_name'][$key]);
                        $item->file_id = $storageObject->file_id;

                        $item->save();

                        // Auth
                        $viewMax = array_search($auth_view, $roles);
                        $commentMax = array_search($auth_comment, $roles);

                        foreach ($roles as $i => $role) {
                            $auth->setAllowed($item, $role, 'view', ($i <= $viewMax));
                            $auth->setAllowed($item, $role, 'comment', ($i <= $commentMax));
                        }
                        // Commit
                        //$db->commit();
                    } catch (Exception $e) {
                        continue;
                    }
                    $story_id = $item->getIdentity();

                    $images['story_content'][$counter]['media_url'] = $this->getBaseUrl(true, $storageObject->map());
                    $images['story_content'][$counter]['comment'] = $item->title;
                    $images['story_content'][$counter]['is_video'] = false;
                    $images['story_content'][$counter]['highlight'] = $item->highlight;
                    $images['story_content'][$counter]['like_count'] = $item->like_count;
                    $images['story_content'][$counter]['comment_count'] = $item->comment_count;
                    $images['story_content'][$counter]['creation_date'] = $item->creation_date;
                    $images['story_content'][$counter]['story_id'] = $story_id;

                    $menucounter = 0;
                    if ($viewer_id != $item->owner_id) {
                        $menuoptions[$menucounter]['name'] = "mute";
                        $menuoptions[$menucounter]['label'] = $this->view->translate("Mute");
                        $menucounter++;

                        $menuoptions[$menucounter]['name'] = "report";
                        $menuoptions[$menucounter]['label'] = $this->view->translate("Report");
                        $menucounter++;

                        $images['story_content'][$counter]['options'] = $menuoptions;
                    }
                    $counter++;
                }
            }
            $result['story'] = $images;
            $result['story']['user_id'] = $viewer->getIdentity();
            $result['story']['username'] = $viewer->getTitle();
            $result['story']['user_image'] = $this->getBaseUrl(true, $viewer->getPhotoUrl());

            // 						$friendArra = $viewer->membership()->getMembershipsOfIds();
            // 						foreach($friendArra as $friend_id) {
            // 							$userFriend = Engine_Api::_()->getItem('user', $friend_id);
            // 							Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($userFriend, $viewer, $viewer, 'sesstories_storycreate');
            // 						}
        }

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }

    public function viewAction()
    {

        $story_id = $this->_getParam('story_id', null);
        $user_id = $this->_getParam('user_id', null);

        $story = Engine_Api::_()->getItem('sesstories_story', $this->getRequest()->getParam('story_id'));

        $isAlreadyView = Engine_Api::_()->getDbTable('recentlyviewitems', 'sesstories')->isAlreadyView(array('owner_id' => $user_id, 'resource_id' => $story_id));

        if (empty($isAlreadyView)) {
            Engine_Api::_()->getDbtable('stories', 'sesstories')->update(array('view_count' => new Zend_Db_Expr('view_count + 1')), array('story_id = ?' => $story_id));
        }

        if ($user_id != 0 && isset($story->story_id)) {
            $dbObject = Engine_Db_Table::getDefaultAdapter();

            $dbObject->query('INSERT INTO engine4_sesstories_recentlyviewitems (resource_id,owner_id,creation_date ) VALUES ("' . $story->story_id . '","' . $user_id . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array()));
    }

    public function highlightAction()
    {

        $counter = 0;
        $result = array();
        foreach ($_POST['story_id'] as $story_id) {
            //$story_id = $this->_getParam('story_id', null);
            $story = Engine_Api::_()->getItem('sesstories_story', $story_id);
            $story->highlight = !$story->highlight;
            $story->save();
            $result['story_content'][$counter]['highlight'] = $story->highlight;
            $result['story_content'][$counter]['story_id'] = $story->story_id;
            $counter++;
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }

    public function muteAction()
    {
        $user_id = $this->_getParam('user_id', null);
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $table = Engine_Api::_()->getDbtable('mutes', 'sesstories');
        $resource_id = $user_id;
        $isStoryAlreadyMuted = Engine_Api::_()->getDbTable('mutes', 'sesstories')->getMuteStory($resource_id);
        $mute_id = null;
        if (!count($isStoryAlreadyMuted)) {
            $values = array('user_id' => $viewer_id, 'resource_id' => $user_id, 'mute' => '1');
            $item = $table->createRow();
            $item->setFromArray($values);
            $item->save();
            $mute_id = $item->mute_id;
        } else {
            $mute_id = $isStoryAlreadyMuted->mute_id;
        }
        $result['option']['label'] = $this->view->translate('Unmute');
        $result['option']['name'] = 'unmute';
        $result['option']['mute_id'] = $mute_id;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }

    public function unmuteAction()
    {
        $mute_id = $this->_getParam('mute_id', null);
        $mute = Engine_Api::_()->getItem('sesstories_mute', $mute_id);
        if ($mute)
            $mute->delete();
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Mute id not found!'), 'result' => array()));
        $result['option']['label'] = $this->view->translate('Mute');
        $result['option']['name'] = 'mute';
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }

    public function deleteAction()
    {

        $story = Engine_Api::_()->getItem('sesstories_story', $this->getRequest()->getParam('story_id'));
        if (!$story) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Story entry doesn't exist to delete.");
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->error, 'result' => array()));
        }

        $db = $story->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $story->delete();
            $db->commit();
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your Story entry has been deleted.');
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->message));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'databse_error', 'result' => array()));
        }
    }
}
