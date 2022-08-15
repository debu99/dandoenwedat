<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AuthController.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_AuthController extends Core_Controller_Action_Standard {

  public function instagramAction() {

    // Clear
    if( null !== $this->_getParam('clear') ) {
      unset($_SESSION['instagram_lock']);
      unset($_SESSION['instagram_token']);
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $instagramTable = Engine_Api::_()->getDbtable('instagram', 'sesbasic');
    $instagram = $instagramTable->getApi('auth');
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $this->view->error = true;
    $this->view->success = false;
    // Enabled?
    if( !$instagram) {
      $this->view->error = true;
    }
    
    // Already connected
    if(!empty($_GET['code'])) {
        $code = $_GET['code'];
        $data = $instagram->getOAuthToken($code);
        $this->view->success = true;
        // Attempt to connect account
        $info = $instagramTable->select()
            ->from($instagramTable)
            ->where('user_id = ?', $viewer->getIdentity())
            ->limit(1)
            ->query()
            ->fetch();
        if( empty($info) ) {
          $instagramTable->insert(array(
            'user_id' => $viewer->getIdentity(),
            'instagram_uid' => $data->user->id,
            'access_token' => $data->access_token,
            'code' => $code,
            'expires' => 0,
          ));
        } else {
          // Save info to db
          $instagramTable->update(array(
            'instagram_uid' => $data->user->id,
            'access_token' => $data->access_token,
            'code' => $code,
            'expires' => 0,
          ), array(
            'user_id = ?' => $viewer->getIdentity(),
          ));
        }        
        $_SESSION['sesbasic_instagram']['inphoto_url'] = $data->user->profile_picture;
        $_SESSION['sesbasic_instagram']['in_id'] = $data->user->id;
        $_SESSION['sesbasic_instagram']['in_name'] = $data->user->full_name;
        $_SESSION['sesbasic_instagram']['in_username'] = $data->user->username;
    }
    // Not connected
    else {
      // Okay
      if( !empty($_GET['code']) )
       $this->view->error = true;
      // Error
      else if( !empty($_GET['error']) ) 
       $this->view->error = true;
      // Redirect to auth page
      else {
        $url = $instagram->getLoginUrl();
        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    }
  }
}