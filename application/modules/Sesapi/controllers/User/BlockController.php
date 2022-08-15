<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: BlockController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class User_BlockController extends Sesapi_Controller_Action_User
{
  public function init()
  {
    $this->_helper->requireUser();
  }
  
  public function addAction()
  {
    // Get id of friend to add
    $user_id = $this->_getParam('user_id', "");
    if( !$user_id ) {
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
    }

    
    if( !$this->getRequest()->isPost() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Invalid data', 'result' => array()));
    }

   
    // Process
    $db = Engine_Api::_()->getDbtable('block', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $user = Engine_Api::_()->getItem('user', $user_id);
      
      $viewer->addBlock($user);
      if( $user->membership()->isMember($viewer, null) ) {
        $user->membership()->removeMember($viewer);
      }
      
      try {
        // Set the requests as handled
        $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($viewer, $user, 'friend_request');
        if( $notification ) {
          $notification->mitigated = true;
          $notification->read = 1;
          $notification->save();
        }
        $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
            ->getNotificationBySubjectAndType($viewer, $user, 'friend_follow_request');
        if( $notification ) {
          $notification->mitigated = true;
          $notification->read = 1;
          $notification->save();
        }
      } catch( Exception $e ) {}

      $db->commit();

      $message = Zend_Registry::get('Zend_Translate')->_('Member blocked');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
      
    } catch( Exception $e ) {
      $db->rollBack();

        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }

  public function removeAction()
  {
    // Get id of friend to add
    $user_id = $this->_getParam('user_id', null);
    if( !$user_id ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
    }

    // Make form

    if( !$this->getRequest()->isPost() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'No action taken', 'result' => array()));
    }

   

    // Process
    $db = Engine_Api::_()->getDbtable('block', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $user = Engine_Api::_()->getItem('user', $user_id);

      $viewer->removeBlock($user);

      $db->commit();

      $this->view->status = true;
      $message = Zend_Registry::get('Zend_Translate')->_('Member unblocked');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));

     
    } catch( Exception $e ) {
      $db->rollBack();

     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
}