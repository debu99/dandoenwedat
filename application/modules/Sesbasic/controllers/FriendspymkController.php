<?php

class Sesbasic_FriendspymkController extends Core_Controller_Action_User {

  public function init() {
  
    // Try to set subject
    $user_id = $this->_getParam('user_id', null);
    if( $user_id && !Engine_Api::_()->core()->hasSubject() )
    {
      $user = Engine_Api::_()->getItem('user', $user_id);
      if( $user )
      {
        Engine_Api::_()->core()->setSubject($user);
      }
    }

    // Check if friendships are enabled
    if( $this->getRequest()->getActionName() !== 'suggest' &&
        !Engine_Api::_()->getApi('settings', 'core')->user_friends_eligible ) {
      $this->_helper->requireAuth()->forward();
    }
  }

  public function addAction() {
  
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Get viewer and other user
    $viewer = Engine_Api::_()->user()->getViewer();
    if( null == ($user_id = $this->_getParam('user_id')) ||
        null == ($user = Engine_Api::_()->getItem('user', $user_id)) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No member specified');
      return;
    }

    // check that user is not trying to befriend 'self'
    if( $viewer->isSelf($user) ) {
      return $this->_forwardSuccess('You cannot befriend yourself.');
    }

    // check that user is already friends with the member
    if( $user->membership()->isMember($viewer) ) {
      return $this->_forwardSuccess('You are already friends with this member.');
    }

    // check that user has not blocked the member
    if( $viewer->isBlocked($user) ) {
      return $this->_forwardSuccess('Friendship request was not sent because you blocked this member.');
    }
    
    // Make form
    $this->view->form = $form = new Sesbasic_Form_Friends_Add(array('user' => $user));

    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No action taken');
      return;
    }

//     if( !$form->isValid($this->getRequest()->getPost()) ) {
//       $this->view->status = false;
//       $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
//       return;
//     }

    // Process
    $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      
      // send request
      $user->membership()
        ->addMember($viewer)
        ->setUserApproved($viewer);
      
      if( !$viewer->membership()->isUserApprovalRequired() && !$viewer->membership()->isReciprocal() ) {
        // if one way friendship and verification not required

        // Add activity
        Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($viewer, $user, 'friends_follow', '{item:$subject} is now following {item:$object}.');

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $viewer, 'friend_follow');

        $message = Zend_Registry::get('Zend_Translate')->_("You are now following this member.");
        
      } else if( !$viewer->membership()->isUserApprovalRequired() && $viewer->membership()->isReciprocal() ){
        // if two way friendship and verification not required

        // Add activity
        Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($user, $viewer, 'friends', '{item:$object} is now friends with {item:$subject}.');
        Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($viewer, $user, 'friends', '{item:$object} is now friends with {item:$subject}.');

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $user, 'friend_accepted');
        
        $message = Zend_Registry::get('Zend_Translate')->_("You are now friends with this member.");

      } else if( !$user->membership()->isReciprocal() ) {
        // if one way friendship and verification required

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $user, 'friend_follow_request');
        
        $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
        
      } else if( $user->membership()->isReciprocal() ) {
        // if two way friendship and verification required

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $user, 'friend_request');
        
        $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
      }

      $db->commit();


      $this->view->status = true;
      return $this->_forwardSuccess('<div class="sespymk_request_success_message">Your friend request has been sent.</div>');
    } catch( Exception $e ) {
      $db->rollBack();

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      $this->view->exception = $e->__toString();
      return;
    }
  }
  
  public function cancelAction() {
  
    if( !$this->_helper->requireUser()->isValid() ) {
			//echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Please login to continue.'))));exit();;
			return $this->_forwardSuccess('Please login to continue.');
		}
    $parambutton = $this->_getParam('parambutton', null);
    // Get viewer and other user
    $viewer = Engine_Api::_()->user()->getViewer();
    if( null == ($user_id = $this->_getParam('user_id')) ||
        null == ($user = Engine_Api::_()->getItem('user', $user_id)) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No member specified');
      //echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('No member specified.'))));exit();;
      return $this->_forwardSuccess('No member specified.');
    }
    
    // Make form
    $this->view->form = $form = new User_Form_Friends_Cancel(array('user'=>$user));
    if($form->{'token_'.$user->getGuid()})
      $form->removeElement('token_'.$user->getGuid());
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      //echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('No action taken.'))));exit();;
      return $this->_forwardSuccess('No action taken.');
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $this->view->status = false;
      //echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Invalid data.'))));exit();;
      return $this->_forwardSuccess('Invalid data.');
    }
    
    // Process
    $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      $user->membership()->removeMember($viewer);

      // Set the requests as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($user, $viewer, 'friend_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($user, $viewer, 'friend_follow_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }

      $db->commit();
      $user = Engine_Api::_()->getItem('user', $user_id);
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your friend request has been cancelled.');
      if($parambutton == 'button') {
        $showData =  $this->view->partial('_addfriend_button.tpl','sesbasic',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      } else {
        $showData =  $this->view->partial('_addfriend.tpl','sesbasic',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      }
      return $this->_forwardSuccess('<div class="sespymk_request_success_message">Your friend request has been cancelled.</div>');
			//echo Zend_Json::encode(array('status' =>1, 'message' => $showData,'tip'=>$this->view->message));exit();
    } catch( Exception $e ) {
      $db->rollBack();

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      echo Zend_Json::encode(array('status' => 0, 'message' => $this->view->error));die;
      $this->view->exception = $e->__toString();
    }
  }
  
  private function setTokenData() {
  
    $this->view->tokenName = $tokenName = 'token_' . $this->view->notification->getSubject()->getGuid();
    $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret');
    $this->view->tokenValue = $this->view->token(null, $tokenName, $salt);
  }

  private function _forwardSuccess($message = '', $user = null) {
  
    $message = Zend_Registry::get('Zend_Translate')->_($message);
    if ($user instanceof User_Model_User) {
      $message = sprintf($message, $user->__toString());
    }

    $this->view->message = $message;
    $params = array('messages' => array($message));
    if ($this->_helper->contextSwitch->getCurrentContext() === 'smoothbox') {
      $params = array_merge($params, array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
      ));
    }
    return $this->_forward('success', 'utility', 'core', $params);
  }
}
