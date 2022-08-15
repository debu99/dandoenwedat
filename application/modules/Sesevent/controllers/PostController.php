<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: PostController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_PostController extends Core_Controller_Action_Standard {

  public function init() {
    if (Engine_Api::_()->core()->hasSubject())
      return;

    if (0 !== ($post_id = (int) $this->_getParam('post_id')) &&
      null !== ($post = Engine_Api::_()->getItem('sesevent_post', $post_id))) {
      Engine_Api::_()->core()->setSubject($post);
    }

    $this->_helper->requireUser->addActionRequires(array(
        'edit',
        'delete',
    ));

    $this->_helper->requireSubject->setActionRequireTypes(array(
        'edit' => 'sesevent_post',
        'delete' => 'sesevent_post',
    ));
  }

  public function editAction() {
    $post = Engine_Api::_()->core()->getSubject('sesevent_post');
    $event = $post->getParent('sesevent_event');
    $viewer = Engine_Api::_()->user()->getViewer();

    if (!$event->isOwner($viewer) && !$post->isOwner($viewer)) {
      if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid()) {
        return;
      }
    }

    $this->view->form = $form = new Sesevent_Form_Post_Edit();

    if (!$this->getRequest()->isPost()) {
      $form->populate($post->toArray());
      $form->body->setValue(html_entity_decode($post->body));
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $table = $post->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $post->setFromArray($form->getValues());
      $post->modified_date = date('Y-m-d H:i:s');
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $allowHtml = (bool) $settings->getSetting('sesevent_html', 0);
      $allowBbcode = (bool) $settings->getSetting('sesevent_bbcode', 0);
      if (!$allowBbcode && !$allowHtml) {
        $post->body = htmlspecialchars($post->body, ENT_NOQUOTES, 'UTF-8');
      }
      $post->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    // Try to get topic
    return $this->_forward('success', 'utility', 'core', array(
                'closeSmoothbox' => true,
                'parentRefresh' => true,
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
    ));
  }

  public function deleteAction() {
    $post = Engine_Api::_()->core()->getSubject('sesevent_post');
    $event = $post->getParent('sesevent_event');
    $viewer = Engine_Api::_()->user()->getViewer();

    if (!$event->isOwner($viewer) && !$post->isOwner($viewer)) {
      if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid()) {
        return;
      }
    }

    $this->view->form = $form = new Sesevent_Form_Post_Delete();

    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $table = $post->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {

      $topic_id = $post->topic_id;
      $post->delete();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    // Try to get topic
    $topic = Engine_Api::_()->getItem('sesevent_topic', $topic_id);
    $href = ( null === $topic ? $event->getHref() : $topic->getHref() );
    return $this->_forward('success', 'utility', 'core', array(
                'closeSmoothbox' => true,
                'parentRedirect' => $href,
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
    ));
  }

}
