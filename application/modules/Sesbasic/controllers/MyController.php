<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_MyController extends Core_Controller_Action_Standard {
		//send message to site user function.
  public function messageAction() {
  
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('item_id');
    $type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type');
    if (!$id || !$type)
      return;
    // Make form
    $this->view->form = $form = new Sesbasic_Form_Compose();
    // Get params
    $multi = $this->_getParam('multi');
    $to = $this->_getParam('to');
    $viewer = Engine_Api::_()->user()->getViewer();
    $toObject = null;
    // Build
    $isPopulated = false;
    if (!empty($to) && (empty($multi) || $multi == 'user')) {
      $multi = null;
      // Prepopulate user
      $toUser = Engine_Api::_()->getItem('user', $to);
      $isMsgable = ( 'friends' != Engine_Api::_()->authorization()->getPermission($viewer, 'messages', 'auth') ||
              $viewer->membership()->isMember($toUser) );
      if ($toUser instanceof User_Model_User &&
              (!$viewer->isBlockedBy($toUser) && !$toUser->isBlockedBy($viewer)) &&
              isset($toUser->user_id) &&
              $isMsgable) {
        $this->view->toObject = $toObject = $toUser;
        $form->toValues->setValue($toUser->getGuid());
        $isPopulated = true;
      } else {
        $multi = null;
        $to = null;
      }
    }
    $this->view->isPopulated = $isPopulated;
    // Assign the composing stuff
    $composePartials = array();
    // Get config
    $this->view->maxRecipients = $maxRecipients = 10;
    // Check method/data
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      // Try attachment getting stuff
      $attachment = null;

      if ($id) {
        $attachment = Engine_Api::_()->getItem($type, $id);
      }
      $viewer = Engine_Api::_()->user()->getViewer();
      $values = $form->getValues();

      // Prepopulated
      if ($toObject instanceof User_Model_User) {
        $recipientsUsers = array($toObject);
        $recipients = $toObject;
        // Validate friends
        if ('friends' == Engine_Api::_()->authorization()->getPermission($viewer, 'messages', 'auth')) {
          if (!$viewer->membership()->isMember($recipients)) {
            return $form->addError('One of the members specified is not in your friends list.');
          }
        }
      } else if ($toObject instanceof Core_Model_Item_Abstract &&
              method_exists($toObject, 'membership')) {
        $recipientsUsers = $toObject->membership()->getMembers();
        $recipients = $toObject;
      }
      // Normal
      else {
        $recipients = preg_split('/[,. ]+/', $values['toValues']);
        // clean the recipients for repeating ids
        // this can happen if recipient is selected and then a friend list is selected
        $recipients = array_unique($recipients);
        // Slice down to 10
        $recipients = array_slice($recipients, 0, $maxRecipients);
        // Get user objects
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $recipients);
        // Validate friends
        if ('friends' == Engine_Api::_()->authorization()->getPermission($viewer, 'messages', 'auth')) {
          foreach ($recipientsUsers as &$recipientUser) {
            if (!$viewer->membership()->isMember($recipientUser)) {
              return $form->addError('One of the members specified is not in your friends list.');
            }
          }
        }
      }

      // Create conversation
      $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
              $viewer, $recipients, $values['title'], $values['body'], $attachment
      );

      // Send notifications
      foreach ($recipientsUsers as $user) {
        if ($user->getIdentity() == $viewer->getIdentity()) {
          continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                $user, $viewer, $conversation, 'message_new'
        );
      }
      // Increment messages counter
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');
      // Commit
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    if ($this->getRequest()->getParam('format') == 'smoothbox') {
      return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.')),
                  'smoothboxClose' => true,
      ));
    }
  
	}
}