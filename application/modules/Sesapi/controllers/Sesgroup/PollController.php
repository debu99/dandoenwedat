<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: PollController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesgroup_PollController extends Sesapi_Controller_Action_Standard
{
    public function init(){
        $poll_id = $this->_getParam('poll_id');
        $poll = null;
        $poll = Engine_Api::_()->getItem('sesgrouppoll_poll', $poll_id);
        if ($poll) {
            Engine_Api::_()->core()->setSubject($poll);
        }
    }
    public function browseAction()
    {
        $form = new Sesgrouppoll_Form_Search();
        $form->populate($_POST);
        $params = $form->getValues();
        if ($params['closed'] == '')
            $params['closed'] = 0;
        $paginator = Engine_Api::_()->getDbTable('polls', 'sesgrouppoll')->getPollsPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result['polls'] = $this->getPolls($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function gifsAction(){
        $paginator = Engine_Api::_()->getDbTable('images', 'sesfeedgif')->getPaginator(array('fetchAll' => 1, 'limit' => 10));
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $data =array();
        if(count($paginator)){
            $counter = 0;
            foreach($paginator as $gif){
                $data[$counter] = $gif->toArray();
                if($gif->file_id && Engine_Api::_()->storage()->get($gif->file_id,'')){
					$data[$counter]['image'] = $this->getBaseUrl(true,Engine_Api::_()->storage()->get($gif->file_id,'')->map());
					$counter++;
				}
            }
        }
        $result['gifs'] = $data;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));

    }
    public function moreAction(){
        if($optionId = $this->_getParam('option_id'))
            $option = Engine_Api::_()->getItem('sesgrouppoll_option', $this->_getParam('option_id'));
        if(!$option)
            return;
        $paginator = Engine_Api::_()->getDbtable('votes', 'sesgrouppoll')->getVotesPaginator($option->poll_option_id);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $data =array();
        if(count($paginator)){
            $counter = 0;
            foreach($paginator as $user) {
                $userItem = Engine_Api::_()->getItem('user', $user->user_id);
                $data[$counter] = $userItem->toArray();
                unset($data[$counter]['creation_ip']);
                unset($data[$counter]['lastlogin_ip']);
                $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($userItem, "", "");
                if($userImage)
                    $data[$counter]['owner_photo'] = $userImage;
                $counter++;
            }
        }
        $result['members'] = $data;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        //echo '<prE>';print_r(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));die;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getPolls($paginator){
        $counter = 0;
        $result = array();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        foreach ($paginator as $poll){
            $result[$counter] = $poll->toArray();
            $group_id = $poll->group_id;
            $result[$counter]['content_id'] = $group_id;
            $user_id = $poll->user_id;
            $user = Engine_Api::_()->getItem('user', $user_id);
            $result[$counter]['owner_title'] = $poll->getOwner()->getTitle();
            $result[$counter]['owner_title'] = $poll->getOwner()->getTitle();
            $likeStatus = Engine_Api::_()->sesgroup()->getLikeStatus($poll->poll_id,'sesgrouppoll_poll');
            $can_fav = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.allow.favourite', 1);
            $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sesgrouppoll')->isFavourite(array('resource_id' => $poll->poll_id,'resource_type' => 'sesgrouppoll_poll'));
            if($user){
                $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                $result[$counter]['owner_image'] = $ownerimage;
            }
            $group = Engine_Api::_()->getItem('sesgroup_group', $group_id);
            if($group)
                $result[$counter]['content_title'] = $group->title;
            if($viewer_id)
            $result[$counter]['is_content_like'] = $likeStatus>0 ? true : false;
            if($can_fav)
                $result[$counter]['is_content_favourite'] = $favouriteStatus>0 ? true : false;
            $counter++;
        }
        return $result;
    }
    public function searchAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $p = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        // Get form
        $form = new Sesgrouppoll_Form_Search(array('searchTitle'=>$this->_getParam('search_title')));
        if( !$viewer->getIdentity() ) {
            $form->removeElement('show');
        }
        // Process form
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesgrouppoll_poll'));
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

    }
    public function groupPollAction(){
        $params = array();
        $result = array();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $params['group_id']= $group_id = $this->_getParam('group_id',null);
        $params['sort'] = $this->_getParam('sort','creation_date');
        if(!$group_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $params['closed'] = 0;
        $paginator = Engine_Api::_()->getDbTable('polls', 'sesgrouppoll')->getPollsPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $allowPoll  = Engine_Api::_()->authorization()->isAllowed('sesgroup_group', $viewer, 'poll');
        $canUpload =  Engine_Api::_()->authorization()->isAllowed('sesgrouppoll_poll',$viewer, 'create');
        if($allowPoll && $canUpload){
            $result['button']['label'] = $this->view->translate('Post New Poll');
            $result['button']['name'] = 'create';
        }
        $sortCounter = 0;
        $result['sort'][$sortCounter]['name'] = 'creation_date';
        $result['sort'][$sortCounter]['label'] = $this->view->translate('Recently Created');
        $sortCounter++;
        $result['sort'][$sortCounter]['name'] = 'most_liked';
        $result['sort'][$sortCounter]['label'] = $this->view->translate('Most Liked');
        $sortCounter++;
        $result['sort'][$sortCounter]['name'] = 'most_viewed';
        $result['sort'][$sortCounter]['label'] = $this->view->translate('Most Viewed');
        $sortCounter++;
        $result['sort'][$sortCounter]['name'] = 'most_commented';
        $result['sort'][$sortCounter]['label'] = $this->view->translate('Most Commented');
        $sortCounter++;
        $result['polls'] = $this->getPolls($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function likeAction(){
        if (!Engine_Api::_()->core()->hasSubject())
            $subject = Engine_Api::_()->getItem('sesgrouppoll_poll', $this->_getParam('id', null));
        else
            $subject = Engine_Api::_()->core()->getSubject();
        if(!$subject)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $type = 'sesgrouppoll_poll';
        $dbTable = 'polls';
        $resorces_id = 'poll_id';
        $notificationType = 'sesgrouppoll_like_poll';
        $item_id = $subject->poll_id;
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableMainLike = $tableLike->info('name');
        $itemTable = Engine_Api::_()->getDbtable($dbTable, 'sesgrouppoll');
        $select = $tableLike->select()->from($tableMainLike)->where('resource_type =?', $type)->where('poster_id =?', Engine_Api::_()->user()->getViewer()->getIdentity())->where('poster_type =?', 'user')->where('resource_id =?', $item_id);
        $Like = $tableLike->fetchRow($select);
				$item = Engine_Api::_()->getItem('sesgrouppoll_poll', $item_id);
				$group = Engine_Api::_()->getItem('sesgroup_group', $item->group_id);
        if (count($Like) > 0) {
            // delete
            $db = $Like->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Like->delete();
                $db->commit();
                $temp['data']['message'] = $this->view->translate('Poll Successfully Unliked.');
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            $item = Engine_Api::_()->getItem($type, $item_id);
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $group->getType(), "object_id = ?" => $group->getIdentity()));
            Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => 'like_sesgrouppoll_poll', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $group->getType(), "object_id = ?" => $group->getIdentity()));
            
            $temp['data']['like_count'] = $item->like_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
            $db->beginTransaction();
            try {
                $like = $tableLike->createRow();
                $like->poster_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $like->resource_type = $type;
                $like->resource_id = $item_id;
                $like->poster_type = 'user';
                $like->save();
                $itemTable->update(array(
                    'like_count' => new Zend_Db_Expr('like_count + 1'),
                ), array(
                    $resorces_id . '= ?' => $item_id,
                ));
                // Commit
                $db->commit();
                $temp['data']['message'] = $this->view->translate('Poll Successfully liked.');
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            //send notification and activity feed work.
            $item = Engine_Api::_()->getItem($type, $item_id);
            $subject = $item;
            $owner = $subject->getOwner();
            if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $group->getType(), "object_id = ?" => $group->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $group, $notificationType);
                   	$action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $group, 'like_sesgrouppoll_poll');
							if( $action != null ) {
								Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
							
                }
            }
            $temp['data']['like_count'] = $item->like_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
        }
    }
    public function favouriteAction(){
        if (!Engine_Api::_()->core()->hasSubject())
            $subject = Engine_Api::_()->getItem('sesgrouppoll_poll', $this->_getParam('id', null));
        else
            $subject = Engine_Api::_()->core()->getSubject();
        if(!$subject)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $type = 'sesgrouppoll_poll';
        $dbTable = 'polls';
        $resorces_id = 'poll_id';
        $notificationType = 'sesgrouppoll_favourite_poll';
        $item_id = $subject->poll_id;
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $Fav = Engine_Api::_()->getDbTable('favourites', 'sesgrouppoll')->getItemfav($type, $item_id);
        $favItem = Engine_Api::_()->getDbtable($dbTable, 'sesgrouppoll');
	$item = Engine_Api::_()->getItem('sesgrouppoll_poll', $item_id);
	$group = Engine_Api::_()->getItem('sesgroup_group', $item->group_id);
        if (count($Fav) > 0) {
            //delete
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Fav->delete();
                $db->commit();
                $temp['data']['message'] = 'Poll Successfully Unfavourited.';
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
            $item = Engine_Api::_()->getItem($type, $item_id);
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $group->getType(), "object_id = ?" => $group->getIdentity()));
            Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => 'favourite_sesgrouppoll_poll', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $group->getType(), "object_id = ?" => $group->getIdentity()));
            Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
            $temp['data']['favourite_count'] = $item->favourite_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('favourites', 'sesgrouppoll')->getAdapter();
            $db->beginTransaction();
            try {
                $fav = Engine_Api::_()->getDbTable('favourites', 'sesgrouppoll')->createRow();
                $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $fav->resource_type = $type;
                $fav->resource_id = $item_id;
                $fav->save();
                $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1'),
                ), array(
                    $resorces_id . '= ?' => $item_id,
                ));
                // Commit
                $db->commit();
                $temp['data']['message'] = 'Poll Successfully favourited.';
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            //send notification and activity feed work.
            $item = Engine_Api::_()->getItem(@$type, @$item_id);
            if ($this->_getParam('type') != 'sesgrouppoll_artist') {
                $subject = $item;
                $owner = $subject->getOwner();
                if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $group->getType(), "object_id = ?" => $group->getIdentity()));
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $group, $notificationType);
                   
                       $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $group, 'favourite_sesgrouppoll_poll');
								if( $action != null ) {
									Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
								}
                }
            }
            $temp['data']['favourite_count'] = $item->favourite_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
        }
    }
    public function deleteAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $data = array();
        //if (!$this->_helper->requireUser()->isValid())
            //Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject())
            $poll = Engine_Api::_()->getItem('sesgrouppoll_poll', $this->_getParam('id', null));
        else
            $poll = Engine_Api::_()->core()->getSubject();
        if(!$poll)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $can_delete = $poll->authorization()->isAllowed($viewer, 'delete');
        if(!$can_delete)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->getRequest()->isPost()) {
            $data['status'] = false;
            $data['message'] = $this->view->translate('Invalid request method');
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$data['status'], 'result' => $data));
        }
        $db = $poll->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $poll->delete();
            $db->commit();
            $data['status'] = true;
            $data['message'] = $this->view->translate('Poll has been deleted succuessfully.');
        } catch( Exception $e ) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' =>array()));
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
    }
    public function closeAction(){
        $data = array();
        if( !$this->_helper->requireUser()->isValid() )
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject())
            $poll = Engine_Api::_()->getItem('sesgrouppoll_poll', $this->_getParam('id', null));
        else
            $poll = Engine_Api::_()->core()->getSubject();
        if(!$poll)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        // Check auth
        if( !$this->_helper->requireAuth()->setAuthParams($poll, $viewer, 'edit')->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        if (!$this->getRequest()->isPost()) {
            $data['status'] = false;
            $data['message'] = $this->view->translate('Invalid request method');
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $data['message'], 'result' => $data));
        }
        // @todo convert this to post only
        $table = $poll->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $poll->closed = $poll->closed==1 ? 0 : 1 ;
            $poll->save();
            $db->commit();
            $data['status'] = true;
            $data['message'] = $poll->closed == 1 ? $this->view->translate('Successfully Closed') : $this->view->translate('Successfully Unclosed') ;
        } catch( Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$this->getMessage(), 'result' => array()));
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
    }
    public function voteAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if( !$this->_helper->requireUser()->isValid() || !$this->_helper->requireAuth()->setAuthParams('sesgrouppoll_poll', $viewer, 'view')->isValid() ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        if( !$this->_helper->requireAuth()->setAuthParams('sesgrouppoll_poll', $viewer, 'vote')->isValid() ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        // Check method
        if( !$this->getRequest()->isPost() ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $this->view->translate('Invalid request method'), 'result' => array()));
        }
        if (!Engine_Api::_()->core()->hasSubject())
            $poll = Engine_Api::_()->getItem('sesgrouppoll_poll', $this->_getParam('id', null));
        else
            $poll = Engine_Api::_()->core()->getSubject();
        if(!$poll)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This poll does not seem to exist anymore.'), 'result' => array()));
				$group = Engine_Api::_()->getItem('sesgroup_group', $poll->group_id);
        $option_id = $this->_getParam('option_id');
        $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.canchangevote', false);
        $hashElement = $this->view->sesGroupVoteHash($poll)->getElement();
        $data = array();
        $data['token'] = $this->view->sesGroupVoteHash($poll)->generateHash();
        $data['status'] = true;
       // if (!$hashElement->isValid($this->_getParam('token'))) {
         //   $data['status'] = false;
          //  $error = join(';', $hashElement->getMessages());
         //   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $error, 'result' => array()));
       // }
        if( $poll->closed ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $this->view->translate('This poll is closed.'), 'result' => array()));
        }
        if( $poll->hasVoted($viewer) && !$canChangeVote ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $this->view->translate('You have already voted on this poll, and are not permitted to change your vote.'), 'result' => array()));
        }
        $db = Engine_Api::_()->getDbtable('polls', 'sesgrouppoll')->getAdapter();
        $db->beginTransaction();
        try {
            $poll->vote($viewer,$option_id,$group,$poll->getOwner(),$poll);
            $db->commit();
            $data['token'] = $this->view->sesGroupVoteHash($poll)->generateHash();
            $data['status'] = true;
            $data['votes_total'] = $poll->vote_count;
        } catch( Exception $e ) {
            $db->rollback();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $e->getMessage(), 'result' => array()));
        }

        $pollOptions = array();
        $counter = 0 ;
        foreach( $poll->getOptions()->toArray() as $option ) {
            $data['vote_detail'][$counter] = $this->view->translate(array('%s vote', '%s votes', $option['votes']), $this->view->locale()->toNumber($option['votes']));
            $counter++;
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => $data));
    }
    public function createAction(){
		
        if( !$this->_helper->requireUser()->isValid() ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        // check member level  authentication
        if( !$this->_helper->requireAuth()->setAuthParams('sesgrouppoll_poll', null, 'create')->isValid() ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $group_id = $this->_getParam('group_id',null);
        if(!$group_id){
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $options = array();
        $maxOptions = $max_options = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.maxoptions', 15);
        $form = new Sesgrouppoll_Form_Create();
        $viewer = Engine_Api::_()->user()->getViewer();
        $gifCount =0;
        $module = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeedgif');
        if($module){
            $gifCount = count(Engine_Api::_()->getDbTable('images', 'sesfeedgif')->getImages(array('fetchAll' => 1, 'limit' => 10)));
        }
        $isGifModuleEnable=$gifCount > 0 ? true : false;
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('max_options' => $maxOptions,'is_gif_enable'=>$isGifModuleEnable));
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }
        //is post
        if (!$form->isValid($this->getRequest()->getPost())) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
        }
        $values = $form->getValues();
        $optionImages = $_FILES['optionsImage'];
        $optionGifs = $_FILES['optionsGif'];
        $options = (array) $this->_getParam('optionsArray');
        $options = array_filter(array_map('trim', $options));
        $options = array_slice($options, 0, $max_options);
        $message['status'] = false;
        $message['message'] = $this->view->translate('Something went wrong.');
        $message['poll_id'] = 0;
        if( empty($options) || !is_array($options) || count($options) < 2 ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You must provide at least two possible answers.'), 'result' => array()));
        //      return $form->addError('You must provide at least two possible answers.');
        }
        foreach( $options as $index => $option ) {
            if( strlen($option) > 300 ) {
                $options[$index] = Engine_String::substr($option, 0, 300);
            }
        }
        // Process
        $pollTable = Engine_Api::_()->getItemTable('sesgrouppoll_poll');
        $pollOptionsTable = Engine_Api::_()->getDbtable('options', 'sesgrouppoll');
        $db = $pollTable->getAdapter();
        $db->beginTransaction();
        try {
            $values = $form->getValues();
            $values['group_id'] =$group_id;
            $values['user_id'] = $viewer->getIdentity();
            if( empty($values['auth_view']) ) {
                $values['auth_view'] = 'everyone';
            }
            if( empty($values['auth_comment']) ) {
                $values['auth_comment'] = 'everyone';
            }
            $values['view_privacy'] = $values['auth_view'];
            // Create poll
            $poll = $pollTable->createRow();
            $poll->setFromArray($values);
            $poll->save();
            // Create options
            $censor = new Engine_Filter_Censor();
            $html = new Engine_Filter_Html(array('AllowedTags'=> array('a')));
            $counter = 0;
            $storage = Engine_Api::_()->getItemTable('storage_file');
            foreach( $options as $option ) {
                $option = $censor->filter($html->filter($option));
                $file_id = 0;
                $image_type= 0;
                if(!empty($_FILES['optionsImage']['name'][$counter])){
                    $file['tmp_name'] = $_FILES['optionsImage']['tmp_name'][$counter];
                    $file['name'] = $_FILES['optionsImage']['name'][$counter];
                    $file['size'] = $_FILES['optionsImage']['size'][$counter];
                    $file['error'] = $_FILES['optionsImage']['error'][$counter];
                    $file['type'] = $_FILES['optionsImage']['type'][$counter];
                    $image_type = 1;
                }elseif(!empty($_POST['optionsGif'][$counter])){
                    $file_id  = $_POST['optionsGif'][$counter];
                    $image_type = 2;
                }
                if($file && $image_type == 1){
                    $thumbname = $storage->createFile($file, array(
                        'parent_id' => $poll->getIdentity(),
                        'parent_type' => 'sesgrouppoll_poll',
                        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                    ));
                    $file_id = $thumbname->file_id;
                }
                $pollOptionsTable->insert(array(
                    'poll_id' => $poll->getIdentity(),
                    'poll_option' => $option,
                    'file_id'=>$file_id,
                    'image_type'=>$image_type
                ));
                $image_type = 0;
                $counter ++;
            }
            // Privacy
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            foreach( $roles as $i => $role ) {
                $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
            }
            $auth->setAllowed($poll, 'registered', 'vote', true);
            $db->commit();
            $message['status'] = true;
            $message['message'] = $this->view->translate('Poll created successfully.');
            $message['poll_id'] = $poll->poll_id;
        } catch( Exception $e ) {
            $db->rollback();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        // Process activity
        $db = Engine_Api::_()->getDbTable('polls', 'sesgrouppoll')->getAdapter();
        $db->beginTransaction();
        try {
            $group = Engine_Api::_()->getItem('sesgroup_group',$group_id);
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity(Engine_Api::_()->user()->getViewer(), $group, 'sesgroup_group_createpoll');
            if( $action ) {
                Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $poll);
            }
            $db->commit();
            $message['status'] = true;
            $message['message'] = $this->view->translate('Poll created successfully.');
            $message['poll_id'] = $poll->poll_id;
        } catch( Exception $e ) {
            $db->rollback();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => $message));
    }
    public function editAction(){
        if( !$this->_helper->requireUser()->isValid() ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        if( !$this->_helper->requireAuth()->setAuthParams('sesgrouppoll_poll', null, 'edit')->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        if (!Engine_Api::_()->core()->hasSubject())
            $subject = Engine_Api::_()->getItem('sesgrouppoll_poll', $this->_getParam('id', null));
        else
            $subject = Engine_Api::_()->core()->getSubject();
        if(!$subject)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $pollData = array();
        $poll = $subject;
        $pollData['poll'] = $poll->toArray();
        $poll_options = $poll->optionsFetchAll();
        $pollOptionCount = 0;
        foreach($poll_options as $polloptn){
            $pollData['poll_options'][$pollOptionCount] = $polloptn;
            if($polloptn['file_id']>0 && $polloptn['image_type']>0){
                $imageUrl =Engine_Api::_()->storage()->get($polloptn['file_id'],'')->map();
                $pollData['poll_options'][$pollOptionCount]['option_image'] = $this->getBaseUrl(true,$imageUrl);
            }
            $pollOptionCount++;
        }
        $pollData['max_options'] = $max_options = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.maxoptions', 15);
        $form = new Sesgrouppoll_Form_Edit();
        $form->getElement('title')->setValue($poll->title);
        $form->getElement('description')->setValue($poll->description);
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        // Populate form with current settings
        $form->search->setValue($poll->search);
        foreach( $roles as $role ) {
            if( 1 === $auth->isAllowed('sesgrouppoll_poll', $role, 'view') ) {
                $form->auth_view->setValue($role);
            }
            if( 1 === $auth->isAllowed('sesgrouppoll_poll', $role, 'comment') ) {
                $form->auth_comment->setValue($role);
            }
        }
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, $pollData);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }
        //is post
        if (!$form->isValid($this->getRequest()->getPost())) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
        }
        $options = (array) $this->_getParam('optionsArray');
        $optionsCount = count($options);
        $ids = (array) $this->_getParam('optionIds');
        $options = array_filter(array_map('trim', $options));
        $options = array_slice($options, 0, $max_options);
        if( empty($options) || !is_array($options) || count($options) < 2 ) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You must provide at least two possible answers.'), 'result' => array()));
        }
        $message['status'] = false;
        $message['message'] = $this->view->translate('Something went wrong.');
        $message['poll_id'] = 0;
        foreach( $options as $index => $option ) {
            if( strlen($option) > 300 ) {
                $options[$index] = Engine_String::substr($option, 0, 300);
            }
        }
        $pollTable = Engine_Api::_()->getItemTable('sesgrouppoll_poll');
        $pollOptionsTable = Engine_Api::_()->getDbtable('options', 'sesgrouppoll');
        $getoptionIds = $pollOptionsTable->select()
            ->from($pollOptionsTable, '*')
            ->where('poll_id = ?', $poll->getIdentity())
            ->query()
            ->fetchAll()
        ;
        $getoptionIdsCounter = 0;

        foreach($getoptionIds as $index=>$value){
            $getoptionIdsArray[$getoptionIdsCounter] = $value['poll_option_id'];
            $getoptionTextArray[$getoptionIdsCounter] = $value['poll_option'];
            $getoptionIdsCounter ++;
        }
        $IdsDiffrence=array_diff($getoptionIdsArray,$ids);

        if(!empty($IdsDiffrence)){
            foreach($IdsDiffrence as $index=>$value){
                $diffItem = $optionItem = Engine_Api::_()->getItem('sesgrouppoll_option', $value);
                if(!empty($diffItem)){
                    $option_file_id = $diffItem->file_id;
                    if($option_file_id && $diffItem->image_type != 2){
                        $fileobj = Engine_Api::_()->getItem('storage_file', $option_file_id);
                        $fileobj->remove();
                    }
                    $diffItem->delete();
                }
            }
        }
        if($this->getParam('is_image_delete',0)==1){
            foreach($ids as $k=>$value){
                $Item = Engine_Api::_()->getItem('sesgrouppoll_option', $value);
                if($Item){
                    $fileobj = Engine_Api::_()->getItem('storage_file', $Item->file_id);
                    if ($fileobj) {
                        if($Item->image_type == 1)
                            $fileobj->remove();
                        $pollOptionsTable->update(
                            array('poll_option' => $options[$k], 'file_id' => 0, 'image_type' => 0),
                            array('`poll_option_id` = ?' => $value));
                        $fileobj = null;
                    }
                }
            }
        }
        $dbOptn = $pollTable->getAdapter();
        $dbOptn->beginTransaction();
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $censor = new Engine_Filter_Censor();
        $html = new Engine_Filter_Html(array('AllowedTags'=> array('a')));
        $counter = 0;
        try{
            foreach($options as $optionKey=>$optionValue) {
                $optionItem = Engine_Api::_()->getItem('sesgrouppoll_option', $ids[$optionKey]);
                $pollOptn = $censor->filter($html->filter($optionValue));
                if(!empty($optionItem)){
                    $optionItemArray = $optionItem->toArray();
                    $fileobj = Engine_Api::_()->getItem('storage_file', $optionItemArray['file_id']);
                    $image_type = 0;
                    if (!empty($_FILES['optionsImage']['name'][$optionKey])) {
                        if($optionItemArray['file_id'] && $optionItemArray['image_type'] != 2 ){
                            if ($fileobj) {
                                $fileobj->remove();
                            }
                        }
                        $file['tmp_name'] = $_FILES['optionsImage']['tmp_name'][$optionKey];
                        $file['name'] = $_FILES['optionsImage']['name'][$optionKey];
                        $file['size'] = $_FILES['optionsImage']['size'][$optionKey];
                        $file['error'] = $_FILES['optionsImage']['error'][$optionKey];
                        $file['type'] = $_FILES['optionsImage']['type'][$optionKey];
                        $image_type = 1;
                    } elseif(!empty($_POST['optionsGif'][$optionKey])){
                        $file =$_POST['optionsGif'][$optionKey];
                        $image_type = 2;
                    }
                    if ($file && $image_type == 1) {
                        $thumbname = $storage->createFile($file, array(
                            'parent_id' => $poll->getIdentity(),
                            'parent_type' => 'sesgrouppoll_poll',
                            'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                        ));
                        $file_id = $thumbname->file_id;
                        $pollOptionsTable->update(
                            array('poll_option' => $pollOptn, 'file_id' => $file_id, 'image_type' => $image_type),
                            array('`poll_option_id` = ?' => $ids[$optionKey]));
                        $file = null;
                    }else if ($file && $image_type ==2) {
                        $file_id = count($file)>0 ? $file :0;
                        $pollOptionsTable->update(
                            array('poll_option' => $pollOptn, 'file_id' => $file_id, 'image_type' => $image_type),
                            array('`poll_option_id` = ?' => $ids[$optionKey]));
                        $file = null;
                    } else {
                        $pollOptionsTable->update(
                            array('poll_option' => $optionValue),
                            array('`poll_option_id` = ?' => $ids[$optionKey]));
                        $file = null;
                    }
                }else{
                    $file_id = 0;
                    $image_type= 0;
                    if(!empty($_FILES['optionsImage']['name'][$optionKey])){
                        $file['tmp_name'] = $_FILES['optionsImage']['tmp_name'][$optionKey];
                        $file['name'] = $_FILES['optionsImage']['name'][$optionKey];
                        $file['size'] = $_FILES['optionsImage']['size'][$optionKey];
                        $file['error'] = $_FILES['optionsImage']['error'][$optionKey];
                        $file['type'] = $_FILES['optionsImage']['type'][$optionKey];
                        $image_type = 1;
                    }elseif(!empty($_POST['optionsGif'][$optionKey])){
                        $file = $_POST['optionsGif'][$optionKey];
                        $image_type = 2;
                    }
                    if($file && $image_type == 1 ){
                        $thumbname = $storage->createFile($file, array(
                            'parent_id' => $poll->getIdentity(),
                            'parent_type' => 'sesgrouppoll_poll',
                            'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
                        ));
                        $file_id = $thumbname->file_id;
                    }
                    if($image_type == 2){
                        $file_id = count($file)>0 ?  $file : 0;
                    }
                    $pollOptionsTable->insert(array(
                        'poll_id' => $poll->getIdentity(),
                        'poll_option' => $pollOptn,
                        'file_id'=>$file_id,
                        'image_type'=>$image_type
                    ));
                    $file = null;
                }
            }
            $message['status'] = true;
            $message['message'] = $this->view->translate('Poll successfully edited.');
            $message['poll_id'] = $poll->getIdentity();
        }catch( Exception $e ) {
            $dbOptn->rollback();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage() , 'result' => array()));
        }
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
            $values = $form->getValues();
            // CREATE AUTH STUFF HERE
            if( empty($values['auth_view']) ) {
                $values['auth_view'] = 'everyone';
            }
            if( empty($values['auth_comment']) ) {
                $values['auth_comment'] = 'everyone';
            }
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            foreach( $roles as $i => $role ) {
                $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
            }
            $poll->title = $values['title'];
            $poll->description = $values['description'];
            $poll->search = (bool) $values['search'];
            $poll->view_privacy = $values['auth_view'];
            $poll->save();
            $db->commit();
            $message['status'] = true;
            $message['message'] = $this->view->translate('Poll successfully edited.');
            $message['poll_id'] = $poll->getIdentity();
        } catch( Exception $e ) {
            $dbOptn->rollBack();
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage() , 'result' => array()));
        }
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
            // Rebuild privacy
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach( $actionTable->getActionsByObject($poll) as $action ) {
                $actionTable->resetActivityBindings($action);
            }
            $db->commit();
            $message['status'] = true;
            $message['message'] = $this->view->translate('Poll successfully edited.');
            $message['poll_id'] = $poll->getIdentity();
        } catch( Exception $e ) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage() , 'result' => array()));
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'', 'result' => $message));
    }
    public function viewAction()
    {
        // Check auth
        if (!Engine_Api::_()->core()->hasSubject())
            $poll = Engine_Api::_()->getItem('sesgrouppoll_poll', $this->_getParam('id', null));
        else
            $poll = Engine_Api::_()->core()->getSubject();
        if (!$poll)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This poll does not seem to exist anymore.'), 'result' => array()));

        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $result = array();
        $poll = Engine_Api::_()->core()->getSubject('sesgrouppoll_poll');
        $owner = $poll->getOwner();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $pollOptions = $poll->getOptions();
        $hasVoted = $poll->viewerVoted();
        $showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.showpiechart', false);
        $canVote = $poll->authorization()->isAllowed(null, 'vote');
        $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.canchangevote', false);
        $canDelete = $poll->authorization()->isAllowed($viewer, 'delete');
        $canEdit = $poll->authorization()->isAllowed($viewer, 'edit');
        $poll_is_voted = $poll->vote_count>0 ? true : false ;
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.allow.share', 1);
        $likeStatus = Engine_Api::_()->sesgroup()->getLikeStatus($poll->poll_id, 'sesgrouppoll_poll');
        $can_fav = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.allow.favourite', 1);
        $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sesgrouppoll')->isFavourite(array('resource_id' => $poll->poll_id, 'resource_type' => 'sesgrouppoll_poll'));

        if (!$owner->isSelf($viewer)) {
            $poll->view_count++;
            $poll->save();
        }
        $counterOpt =0;
        $optionData = array();
        if($viewer_id >0 && !$owner->isSelf($viewer)){
            $optionData[$counterOpt]['name'] = 'report';
            $optionData[$counterOpt]['label'] = $this->view->translate('Report');
            $counterOpt++;
        }
        if($canEdit && !$poll_is_voted){
            $optionData[$counterOpt]['name'] = 'edit';
            $optionData[$counterOpt]['label'] = $this->view->translate('Edit');
            $counterOpt++;
        }
        if($canDelete){
            $optionData[$counterOpt]['name'] = 'delete';
            $optionData[$counterOpt]['label'] = $this->view->translate('Delete');
            $counterOpt++;
        }
        if($shareType){
            $optionData[$counterOpt]['name'] = 'share';
            $optionData[$counterOpt]['label'] = $this->view->translate('Share');
            $counterOpt++;
        }
        if($owner->isSelf($viewer) && !$poll->closed){
            $optionData[$counterOpt]['name'] = 'close';
            $optionData[$counterOpt]['label'] = $this->view->translate('Close');
            $counterOpt++;
        }elseif ($owner->isSelf($viewer) && $poll->closed){
            $optionData[$counterOpt]['name'] = 'open';
            $optionData[$counterOpt]['label'] = $this->view->translate('Open');
            $counterOpt++;
        }


        $result = $poll->toArray();
        $group_id = $poll->group_id;
        $result['content_id'] = $group_id;
        $user_id = $owner->getIdentity();
        $user = Engine_Api::_()->getItem('user', $user_id);
        $result['owner_title'] = $poll->getOwner()->getTitle();
        $result['can_edit'] = $canEdit >0 ? true : false;
        $result['can_delete'] = $canDelete >0 ? true : false;
        $result['has_voted'] = $hasVoted >0 ? true : false;
        $result['has_voted_id'] = ($hasVoted == false) ? 0 : $hasVoted;
        $result['token'] = $this->view->sesGroupVoteHash($poll)->generateHash();
		$result['can_change_vote'] = $canChangeVote;
        if($hasVoted){
            if($canChangeVote){
                $result['can_vote'] = true;
            }else{
                $result['can_vote'] = false;
            }
        }else{
            $result['can_vote'] = $canVote >0 ? true : false;
        }
        if ($user) {
            $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
            $result['owner_image'] = $ownerimage;
        }
        $group = Engine_Api::_()->getItem('sesgroup_group', $group_id);
        if ($group)
            $result['content_title'] = $group->title;
        if ($viewer_id)
            $result['is_content_like'] = $likeStatus > 0 ? true : false;
        if ($can_fav)
            $result['is_content_favourite'] = $favouriteStatus > 0 ? true : false;
        $counter = 0;
        
        foreach ($pollOptions as $option) {
			$voteUserCounter = 0 ;
            $result['options'][$counter] = $option->toArray();
            if ($option->file_id > 0 && $option->image_type > 0) {
                $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                if (!$pct)
                    $pct = 1;
                $result['options'][$counter]['vote_percent'] =$result['options'][$counter]['vote_percent'] = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)).'('.$this->view->
                    translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)).')';
                $result['options'][$counter]['option_image'] =  ($storage = Engine_Api::_()->storage()->get($option->file_id, '')) ? $this->getBaseUrl(true,$storage->map()) : "";
                $tables = Engine_Api::_()->getDbtable('votes', 'sesgrouppoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(5)->setCurrentPageNumber(1);
                $pagecount = $tables->getPages()->pageCount;
                foreach($tables as $table) {
                    $user = Engine_Api::_()->getItem('user', $table->user_id);
                    $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                    $result['options'][$counter]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                    $result['options'][$counter]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                    if ($userImage) {
                        $result['options'][$counter]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                    }
                    $voteUserCounter++;
                }
                $result['options'][$counter]['more_user_link'] = $pagecount >1 ? true : false ;
            }else{
                $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                if (!$pct)
                    $pct = 1;
                $result['options'][$counter]['vote_percent'] = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)).'('.$this->view->
                translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)).')';
                $tables = Engine_Api::_()->getDbtable('votes', 'sesgrouppoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(4)->setCurrentPageNumber(1);
                $pagecount = $tables->getPages()->pageCount;
                foreach($tables as $table) {
                    $user = Engine_Api::_()->getItem('user', $table->user_id);
                    $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                    $result['options'][$counter]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                    $result['options'][$counter]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                    if ($userImage) {
                        $result['options'][$counter]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                    }
                    $voteUserCounter++;
                }
                $result['options'][$counter]['more_user_link'] = $pagecount >1 ? true : false ;
            }
            $counter++;
        }
        $data = array();
        if(count($optionData)>0)
            $data['options'] = $optionData;
        $data['poll'] = $result;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data)));

    }
}