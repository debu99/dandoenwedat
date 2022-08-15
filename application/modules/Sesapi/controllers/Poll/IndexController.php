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
class Poll_IndexController extends Sesapi_Controller_Action_Standard
{

  public function init()
  {

    // Get subject
    $poll = null;
    if (null !== ($pollIdentity = $this->_getParam('poll_id'))) {
      $poll = Engine_Api::_()->getItem('poll', $pollIdentity);
      if (null !== $poll) {
        Engine_Api::_()->core()->setSubject($poll);
      }
    }

    // Get viewer
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    // only show polls if authorized
    $resource = ($poll ? $poll : 'poll');
    $viewer = ($viewer && $viewer->getIdentity() ? $viewer : null);
    if (!$this->_helper->requireAuth()->setAuthParams($resource, $viewer, 'view')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
  }

  public function browseAction()
  {

    // Prepare
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('poll', null, 'create');

    // Get form
    $this->view->form = $form = new Poll_Form_Search();

    // Process form
    $values = array();
    if ($form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
    }

    if (empty($this->_getParam('user_id'))) 
      $values['browse'] = 1;

    $this->view->formValues = array_filter($values);

    if (@$values['show'] == 2 && $viewer->getIdentity()) {
      // Get an array of friend ids
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    }
    unset($values['show']);

    // Make paginator
    $currentPageNumber = $this->_getParam('page', 1);
    $itemCountPerPage = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.perPage', 10);

    // check to see if request is for specific user's listings
    if (($user_id = $this->_getParam('user_id'))) {
      $values['user_id'] = $user_id;
    }

    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('poll')->getPollsPaginator($values);
    $paginator->setItemCountPerPage($itemCountPerPage)->setCurrentPageNumber($currentPageNumber);

    $result = $this->pollsResult($paginator);
    foreach ($result['polls'] as $key => $value) {
      $user = Engine_Api::_()->getItem('user', $value['user_id']);
      if ($user) {
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
        if ($ownerimage) {
          $result['polls'][$key]['owner_image'] = $ownerimage;
        } else {
          $userMainTempProfile = array(
            "main" => $value['owner_photo'],
            "icon" => $value['owner_photo'],
            "normal" => $value['owner_photo'],
            "profile" => $value['owner_photo'],
          );
          $result['polls'][$key]['owner_image'] = $userMainTempProfile;
        }
      }
      if (!empty($this->_getParam('user_id'))) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $menuoptions = array();
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'poll', 'edit');
        $counter = 0;
        if ($canEdit) {
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit Privacy");
          $counter++;
        }

        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'poll', 'delete');
        if ($canDelete) {
          $menuoptions[$counter]['name'] = "delete";
          $menuoptions[$counter]['label'] = $this->view->translate("Delete Poll");
          $counter++;
        }

        $menuoptions[$counter]['name'] = "close";
        $menuoptions[$counter]['label'] = $this->view->translate("Open Poll");
        $menuoptions[$counter]['cl'] = $value['closed'];
        if ($value['closed'] == "0") {
          $menuoptions[$counter]['label'] = $this->view->translate("Close Poll");
        }

        $result['polls'][$key]['menus'] = $menuoptions;
      }
    }

    $canCreate = false;
    if (!empty($this->_getParam('user_id'))) {
      $canCreate = Engine_Api::_()->authorization()->getPermission($viewer, 'poll', 'create');
    }
    $result['can_create'] =$canCreate;

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if ($result <= 0)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('Does not exist polls.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }

  function pollsResult($paginator)
  {

    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();

    foreach ($paginator as $item) {

      $resource = $item->toArray();
      $resource['owner_title'] = Engine_Api::_()->getItem('user', $resource['owner_id'])->getTitle();
      $resource['resource_type'] = $item->getType();
      $resource['resource_id'] = $item->getIdentity();

      // Check content like or not and get like count
      if ($viewer->getIdentity() != 0) {
        $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
        $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
      }

      $owner = $item->getOwner();
      if ($owner && $owner->photo_id) {
        $photo = $this->getBaseUrl(false, $owner->getPhotoUrl('thumb.profile'));
        $resource['owner_photo']  = $photo;
      } else {
        $resource['owner_photo'] = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
      }
      $resource['owner_title'] = $this->view->translate("Posted by ") . $item->getOwner()->getTitle();

      $result['polls'][$counterLoop] = $resource;
      $result['polls'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }
    return $result;
  }

  public function createAction()
  {
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
    if (!$this->_helper->requireAuth()->setAuthParams('poll', null, 'create')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }

    $this->view->options = array();
    $this->view->maxOptions = $max_options = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.maxoptions', 15);
    $this->view->form = $form = new Poll_Form_Create();

    $viewer = Engine_Api::_()->user()->getViewer();

    // Check if post and populate
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      // $formFields['maxOptions'] = $max_options;
      $this->generateFormFields($formFields, array('resources_type' => 'poll', 'maxOptions' => $max_options));
    }

    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'post data error', 'result' => array()));
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
        $this->validateFormFields($validateFields);
    }

    // Check options
    $options = (array) $this->_getParam('optionsArray');
    $options = array_filter(array_map('trim', $options));
    $options = array_slice($options, 0, $max_options);
    $this->view->options = $options;
    if (empty($options) || !is_array($options) || count($options) < 2) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'Polls options error.', 'result' => array()));
    }
    foreach ($options as $index => $option) {
      if (strlen($option) > 300) {
        $options[$index] = Engine_String::substr($option, 0, 300);
      }
    }

    // Process
    $pollTable = Engine_Api::_()->getItemTable('poll');
    $pollOptionsTable = Engine_Api::_()->getDbtable('options', 'poll');
    $db = $pollTable->getAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();
      $values['user_id'] = $viewer->getIdentity();

      if (empty($values['auth_view'])) {
        $values['auth_view'] = 'everyone';
      }
      if (empty($values['auth_comment'])) {
        $values['auth_comment'] = 'everyone';
      }

      $values['view_privacy'] = $values['auth_view'];

      // Create poll
      $poll = $pollTable->createRow();
      $poll->setFromArray($values);
      $poll->save();

      // Create options
      $censor = new Engine_Filter_Censor();
      $html = new Engine_Filter_Html(array('AllowedTags' => array('a')));
      foreach ($options as $option) {
        $option = $censor->filter($html->filter($option));
        $pollOptionsTable->insert(array(
          'poll_id' => $poll->getIdentity(),
          'poll_option' => $option,
        ));
      }

      // Privacy
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach ($roles as $i => $role) {
        $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
      }

      $auth->setAllowed($poll, 'registered', 'vote', true);

      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

    // Process activity
    $db = Engine_Api::_()->getDbTable('polls', 'poll')->getAdapter();
    $db->beginTransaction();
    try {
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity(Engine_Api::_()->user()->getViewer(), $poll, 'poll_new');
      if ($action) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $poll);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('poll_id' => $poll->getIdentity(), 'message' => $this->view->translate('Poll created successfully.'))));
    // Redirect
    //return $this->_helper->redirector->gotoUrl($poll->getHref(), array('prependBase' => false));
  }

  public function editAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject())
      $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));
    else
      $poll = Engine_Api::_()->core()->getSubject();
    if (!$poll) {
      $error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    // Check auth
    if (!$this->_helper->requireAuth()->setAuthParams($poll, $viewer, 'edit')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

    if (!$this->_helper->requireSubject()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }

    // Setup
    $poll = Engine_Api::_()->core()->getSubject('poll');

    // Get form
    $this->view->form = $form = new Poll_Form_Edit();
    $form->removeElement('title');
    $form->removeElement('description');
    $form->removeElement('options');

    // Prepare privacy
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    // Populate form with current settings
    $form->search->setValue($poll->search);
    foreach ($roles as $role) {
      if (1 === $auth->isAllowed($poll, $role, 'view')) {
        $form->auth_view->setValue($role);
      }
      if (1 === $auth->isAllowed($poll, $role, 'comment')) {
        $form->auth_comment->setValue($role);
      }
    }

    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'poll'));
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
        $this->validateFormFields($validateFields);
    }

    $values = $form->getValues();

    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {

      // CREATE AUTH STUFF HERE
      if (empty($values['auth_view'])) {
        $values['auth_view'] = 'everyone';
      }
      if (empty($values['auth_comment'])) {
        $values['auth_comment'] = 'everyone';
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach ($roles as $i => $role) {
        $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
      }

      $poll->search = (bool) $values['search'];
      $poll->view_privacy = $values['auth_view'];
      $poll->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($poll) as $action) {
        $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('poll_id' => $poll->getIdentity(), 'message' => $this->view->translate('Poll edited successfully.'))));
    //return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'poll_general', true);
  }

  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));

    if (!$this->_helper->requireAuth()->setAuthParams($poll, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));

    if (!$poll) {
      $this->view->status = false;
      $error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    $db = $poll->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $poll->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'databse_error', 'result' => array()));
    }

    $this->view->status = true;
    $message = Zend_Registry::get('Zend_Translate')->_('Your poll has been deleted.');
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $message));
  }

  public function closeAction()
  {
    $data = array();
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject())
      $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));
    else
      $poll = Engine_Api::_()->core()->getSubject();
    if (!$poll) {
      $error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    // Check auth
    if (!$this->_helper->requireAuth()->setAuthParams($poll, $viewer, 'edit')->isValid()) {
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
      $poll->closed = $poll->closed == 1 ? 0 : 1;
      $poll->save();
      $db->commit();
      $data['status'] = true;
      $data['message'] = $poll->closed == 1 ? $this->view->translate('Successfully Closed') : $this->view->translate('Successfully Unclosed');
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
  }

  public function viewAction()
  {
    // Check auth
    if (!Engine_Api::_()->core()->hasSubject())
      $poll = Engine_Api::_()->getItem('poll', $this->_getParam('poll_id', null));
    else
      $poll = Engine_Api::_()->core()->getSubject('poll');
    if (!$poll)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This poll does not seem to exist anymore.'), 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }

    $result = array();
    $owner = $poll->getOwner();
    $keyPoll = 'poll';
    $viewer = Engine_Api::_()->user()->getViewer();
    $pollOptions = $poll->getOptions();
    $data['owner_title'] = $owner->getTitle();
    if ($owner && $owner->photo_id) {
      $photo = $this->getBaseUrl(false, $owner->getPhotoUrl('thumb.profile'));
      $data['owner_photo']  = $photo;
    } else {
      $owner_photo = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
      // $userMainTempProfile = array(
      //   "main" => $owner_photo,
      //   "icon" => $owner_photo,
      //   "normal" => $owner_photo,
      //   "profile" => $owner_photo,
      // );
      $data['owner_photo'] = $owner_photo;
    }
    $data['has_voted'] = $poll->viewerVoted() ? 'true' : 'false';
    $data['can_vote'] = $poll->authorization()->isAllowed(null, 'vote') ? 'true' : 'false';
    $data['can_delete'] = Engine_Api::_()->authorization()->isAllowed(null, null, 'delete') ? 'true' : 'false';
    $data['can_edit'] = Engine_Api::_()->authorization()->isAllowed(null, null, 'edit') ? 'true' : 'false';
    $data['can_change_votes'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false) ? 'true' : 'false';
    // $data["vote_count"] = $this->view->translate(array('%s vote', '%s votes', $poll->vote_count), $this->view->locale()->toNumber($poll->vote_count));
    $data["vote_count"] = $poll->vote_count;
    // $data["view_count"]  =   $this->view->translate(array('%s view', '%s views', $poll->view_count), $this->view->locale()->toNumber($poll->view_count));
    $data["view_count"]  = $poll->view_count;
    $data['options'] = $pollOptions->toArray();
    foreach ($pollOptions as $key => $option) {
      $pct = $poll->vote_count
        ? floor(100 * ($option['votes'] / $poll->vote_count))
        : 0;
      if (!$pct)
        $pct = 1;
      $data['options'][$key]['vote_percent'] = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
    }
    $data["share"]["imageUrl"] = $data['owner_photo'];
    $data["share"]["url"] = $this->getBaseUrl(false, $poll->getHref());
    $data["share"]["title"] = $poll->title;
    $data["share"]["description"] = strip_tags($poll->getTitle());
    $data["share"]['urlParams'] = array(
      "type" => $poll->getType(),
      "id" => $poll->getIdentity()
    );
    $result[$keyPoll] = array_merge($poll->toArray(), $data);

    $counterOpt = 0;
    $optionData = array();
    if(!$poll->isOwner($viewer)){
      $optionData[$counterOpt]['name'] = 'report';
      $optionData[$counterOpt]['label'] = $this->view->translate('Report');
      $counterOpt++;
    }
    $optionData[$counterOpt]['name'] = 'share';
    $optionData[$counterOpt]['label'] = $this->view->translate('Share');
    if (filter_var($data['can_edit'], FILTER_VALIDATE_BOOLEAN)) {
      $counterOpt++;
      $optionData[$counterOpt]['name'] = 'edit_privacy';
      $optionData[$counterOpt]['label'] = $this->view->translate('Edit Privacy');
    }
    if (filter_var($data['can_delete'], FILTER_VALIDATE_BOOLEAN)) {
      $counterOpt++;
      $optionData[$counterOpt]['name'] = 'delete';
      $optionData[$counterOpt]['label'] = $this->view->translate('Delete');
    }

    $result['options'] = $optionData;
    if (!$owner->isSelf($viewer)) {
      $poll->view_count++;
      $poll->save();
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result)));
  }

  public function searchAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $p = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    // Get form
    $form = new Poll_Form_Search();
    if (!$viewer->getIdentity()) {
      $form->removeElement('show');
    }
    // Process form
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'poll'));
    } else {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
  }

  public function voteAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'You do not have permission to view this private page.', 'result' => array()));
    }
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'vote')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'You do not have permission to vote on poll.', 'result' => array()));
    }

    // Check method
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'post data error', 'result' => array()));
    }

    $option_id = $this->_getParam('option_id');
    $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false);

    $poll = Engine_Api::_()->core()->getSubject('poll');
    $viewer = Engine_Api::_()->user()->getViewer();

    if (!$poll) {
      $this->view->success = false;
      $error = Zend_Registry::get('Zend_Translate')->_("This poll does not seem to exist anymore.");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    if ($poll->closed) {
      $this->view->success = false;
      $error = Zend_Registry::get('Zend_Translate')->_('This poll is closed.');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    if ($poll->hasVoted($viewer) && !$canChangeVote) {
      $this->view->success = false;
      $error = Zend_Registry::get('Zend_Translate')->_('You have already voted on this poll, and are not permitted to change your vote.');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    $data = array();
    $db = Engine_Api::_()->getDbtable('polls', 'poll')->getAdapter();
    $db->beginTransaction();
    try {
      $poll->vote($viewer, $option_id);

      $db->commit();
    } catch (Exception $error) {
      $db->rollback();
      $this->view->success = false;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    $data['success'] = true;
    $pollOptions = array();
    foreach ($poll->getOptions()->toArray() as $option) {
      $option['votesTranslated'] = $this->view->translate(array('%s vote', '%s votes', $option['votes']), $this->view->locale()->toNumber($option['votes']));
      $pollOptions[] = $option;
    }
    $data['options'] = $pollOptions;
    $data['votes_total'] = $poll->vote_count;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
  }
}
