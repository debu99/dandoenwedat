<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: TopicController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_TopicController extends Core_Controller_Action_Standard {

  public function init() {
    
    if (Engine_Api::_()->core()->hasSubject())
      return;

    /*
      if( 0 !== ($post_id = (int) $this->_getParam('post_id')) &&
      null !== ($post = Engine_Api::_()->getItem('event_post', $post_id)) )
      {
      Engine_Api::_()->core()->setSubject($post);
      }

      else */if (0 !== ($topic_id = (int) $this->_getParam('topic_id')) &&
            null !== ($topic = Engine_Api::_()->getItem('sesevent_topic', $topic_id))) {
      Engine_Api::_()->core()->setSubject($topic);
    } else if (0 !== ($event_id = (int) $this->_getParam('event_id')) &&
            null !== ($event = Engine_Api::_()->getItem('sesevent_event', $event_id))) {
      Engine_Api::_()->core()->setSubject($event);
    }

    $this->_helper->requireUser->addActionRequires(array(
        'close', 'create', 'delete', 'post', 'rename', 'reply', 'sticky', 'watch',
    ));

    $this->_helper->requireSubject->setActionRequireTypes(array(
        'close' => 'sesevent_topic',
        'create' => 'sesevent_event',
        'delete' => 'sesevent_topic',
        'index' => 'sesevent_event',
        'post' => 'sesevent_topic',
        'rename' => 'sesevent_topic',
        'reply' => 'sesevent_topic',
        'sticky' => 'sesevent_topic',
        'view' => 'sesevent_topic',
        'watch' => 'sesevent_topic',
    ));
  }

  public function indexAction() {
    if (!$this->_helper->requireSubject('sesevent_event')->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid())
      return;

    $this->view->event = $event = Engine_Api::_()->core()->getSubject();

    $table = Engine_Api::_()->getDbtable('topics', 'sesevent');
    $select = $table->select()
            ->where('event_id = ?', $event->getIdentity())
            ->order('sticky DESC')
            ->order('modified_date DESC');

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->can_post = $can_post = $this->_helper->requireAuth->setAuthParams(null, null, 'comment')->checkRequire();
    $paginator->setCurrentPageNumber($this->_getParam('page'));
  }

  public function viewAction() {
    if (!$this->_helper->requireSubject('sesevent_topic')->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid())
      return;

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject();
    $this->view->event = $event = $topic->getParentEvent();

    $this->view->canEdit = $canEdit = $event->authorization()->isAllowed($viewer, 'edit');
    $this->view->canPost = $canPost = $event->authorization()->isAllowed($viewer, 'comment');
    $this->view->canAdminEdit = Engine_Api::_()->authorization()->isAllowed($event, null, 'edit');

    if (!$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id) {
      $topic->view_count = new Zend_Db_Expr('view_count + 1');
      $topic->save();
    }

    $isWatching = null;
    if ($viewer->getIdentity()) {
      $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
      $isWatching = $topicWatchesTable
              ->select()
              ->from($topicWatchesTable->info('name'), 'watch')
              ->where('resource_id = ?', $event->getIdentity())
              ->where('topic_id = ?', $topic->getIdentity())
              ->where('user_id = ?', $viewer->getIdentity())
              ->limit(1)
              ->query()
              ->fetchColumn(0)
      ;
      if (false === $isWatching) {
        $isWatching = null;
      } else {
        $isWatching = (bool) $isWatching;
      }
    }
    $this->view->isWatching = $isWatching;

    // @todo implement scan to post
    $this->view->post_id = $post_id = (int) $this->_getParam('post');

    $table = Engine_Api::_()->getDbtable('posts', 'sesevent');
    $select = $table->select()
            ->where('event_id = ?', $event->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->order('creation_date ASC');

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    // Skip to page of specified post
    if (0 !== ($post_id = (int) $this->_getParam('post_id')) &&
            null !== ($post = Engine_Api::_()->getItem('sesevent_post', $post_id))) {
      $icpp = $paginator->getItemCountPerPage();
      $page = ceil(($post->getPostIndex() + 1) / $icpp);
      $paginator->setCurrentPageNumber($page);
    }

    // Use specified page
    else if (0 !== ($page = (int) $this->_getParam('page'))) {
      $paginator->setCurrentPageNumber($this->_getParam('page'));
    }

    if ($canPost && !$topic->closed) {
      $this->view->form = $form = new Sesevent_Form_Post_Create();
      $form->populate(array(
          'topic_id' => $topic->getIdentity(),
          'ref' => $topic->getHref(),
          'watch' => ( false === $isWatching ? '0' : '1' ),
      ));
    }
  }

  public function createAction() {
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject('sesevent_event')->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
      return;

    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

    // Make form
    $this->view->form = $form = new Sesevent_Form_Topic_Create();

    // Check method/data
    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $values = $form->getValues();
    $values['user_id'] = $viewer->getIdentity();
    $values['event_id'] = $event->getIdentity();

    $topicTable = Engine_Api::_()->getDbtable('topics', 'sesevent');
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
    $postTable = Engine_Api::_()->getDbtable('posts', 'sesevent');

    $db = $event->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      // Create topic
      $topic = $topicTable->createRow();
      $topic->setFromArray($values);
      $topic->save();

      // Create post
      $values['topic_id'] = $topic->topic_id;

      $post = $postTable->createRow();
      $post->setFromArray($values);
      $post->save();

      // Create topic watch
      $topicWatchesTable->insert(array(
          'resource_id' => $event->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $values['watch'],
      ));

      // Add activity
      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $topic, 'sesevent_topic_create');
      if ($action) {
        $action->attach($topic);
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    // Redirect to the post
    $this->_redirectCustom($post);
  }

  public function postAction() {
      
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (!$this->_helper->requireSubject('sesevent_topic')->isValid())
      return;
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
      return;

    $this->view->topic = $topic = Engine_Api::_()->core()->getSubject();
    $this->view->event = $event = $topic->getParentEvent();

    if ($topic->closed) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('This has been closed for posting.');
      return;
    }

    // Make form
    $this->view->form = $form = new Sesevent_Form_Post_Create();

    // Check method/data
    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $viewer = Engine_Api::_()->user()->getViewer();
    $topicOwner = $topic->getOwner();
    $isOwnTopic = $viewer->isSelf($topicOwner);

    $postTable = Engine_Api::_()->getDbtable('posts', 'sesevent');
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
    $userTable = Engine_Api::_()->getItemTable('user');
    $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

    $values = $form->getValues();
    $values['user_id'] = $viewer->getIdentity();
    $values['event_id'] = $event->getIdentity();
    $values['topic_id'] = $topic->getIdentity();

    $watch = (bool) $values['watch'];
    $isWatching = $topicWatchesTable
            ->select()
            ->from($topicWatchesTable->info('name'), 'watch')
            ->where('resource_id = ?', $event->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->where('user_id = ?', $viewer->getIdentity())
            ->limit(1)
            ->query()
            ->fetchColumn(0)
    ;

    $db = $event->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      // Create post
      $post = $postTable->createRow();
      $post->setFromArray($values);
      $post->save();

      // Watch
      if (false === $isWatching) {
        $topicWatchesTable->insert(array(
            'resource_id' => $event->getIdentity(),
            'topic_id' => $topic->getIdentity(),
            'user_id' => $viewer->getIdentity(),
            'watch' => (bool) $watch,
        ));
      } else if ($watch != $isWatching) {
        $topicWatchesTable->update(array(
            'watch' => (bool) $watch,
                ), array(
            'resource_id = ?' => $event->getIdentity(),
            'topic_id = ?' => $topic->getIdentity(),
            'user_id = ?' => $viewer->getIdentity(),
        ));
      }

      // Activity
      $action = $activityApi->addActivity($viewer, $topic, 'sesevent_topic_reply');
      if ($action) {
        $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
      }

      // Notifications
      $notifyUserIds = $topicWatchesTable->select()
              ->from($topicWatchesTable->info('name'), 'user_id')
              ->where('resource_id = ?', $event->getIdentity())
              ->where('topic_id = ?', $topic->getIdentity())
              ->where('watch = ?', 1)
              ->query()
              ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;

      foreach ($userTable->find($notifyUserIds) as $notifyUser) {
        // Don't notify self
        if ($notifyUser->isSelf($viewer)) {
          continue;
        }
        if ($notifyUser->isSelf($topicOwner)) {
          $type = 'sesevent_discussion_response';
        } else {
          $type = 'sesevent_discussion_reply';
        }
        $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
            'message' => $this->view->BBCode($post->body),
        ));
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    // Redirect to the post
    $this->_redirectCustom($post);
  }

  public function stickyAction() {
    $topic = Engine_Api::_()->core()->getSubject();
    $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->sticky = ( null === $this->_getParam('sticky') ? !$topic->sticky : (bool) $this->_getParam('sticky') );
      $topic->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->_redirectCustom($topic);
  }

  public function closeAction() {
    $topic = Engine_Api::_()->core()->getSubject();
    $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;


    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $topic = Engine_Api::_()->core()->getSubject();
      $topic->closed = ( null === $this->_getParam('closed') ? !$topic->closed : (bool) $this->_getParam('closed') );
      $topic->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->_redirectCustom($topic);
  }

  public function renameAction() {

    $topic = Engine_Api::_()->core()->getSubject();
    $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;

    $this->view->form = $form = new Sesevent_Form_Topic_Rename();

    if (!$this->getRequest()->isPost()) {
      $form->title->setValue(htmlspecialchars_decode($topic->title));
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $title = $form->getValue('title');

      $topic = Engine_Api::_()->core()->getSubject();
      $topic->title = htmlspecialchars($title);
      $topic->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic renamed.')),
                'layout' => 'default-simple',
                'parentRefresh' => true,
    ));
  }

  public function deleteAction() {


    $topic = Engine_Api::_()->core()->getSubject();
    $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;

    $this->view->form = $form = new Sesevent_Form_Topic_Delete();

    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $topic = Engine_Api::_()->core()->getSubject();
      $event = $topic->getParent('sesevent_event');
      $topic->delete();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic deleted.')),
                'layout' => 'default-simple',
                'parentRedirect' => $event->getHref(),
    ));
  }

  public function watchAction() {
    $topic = Engine_Api::_()->core()->getSubject();
    $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'view')->isValid()) {
      return;
    }

    $watch = $this->_getParam('watch', true);

    $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
    $db = $topicWatchesTable->getAdapter();
    $db->beginTransaction();

    try {
      $isWatching = $topicWatchesTable
              ->select()
              ->from($topicWatchesTable->info('name'), 'watch')
              ->where('resource_id = ?', $event->getIdentity())
              ->where('topic_id = ?', $topic->getIdentity())
              ->where('user_id = ?', $viewer->getIdentity())
              ->limit(1)
              ->query()
              ->fetchColumn(0)
      ;

      if (false === $isWatching) {
        $topicWatchesTable->insert(array(
            'resource_id' => $event->getIdentity(),
            'topic_id' => $topic->getIdentity(),
            'user_id' => $viewer->getIdentity(),
            'watch' => (bool) $watch,
        ));
      } else if ($watch != $isWatching) {
        $topicWatchesTable->update(array(
            'watch' => (bool) $watch,
                ), array(
            'resource_id = ?' => $event->getIdentity(),
            'topic_id = ?' => $topic->getIdentity(),
            'user_id = ?' => $viewer->getIdentity(),
        ));
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->_redirectCustom($topic);
  }

}
