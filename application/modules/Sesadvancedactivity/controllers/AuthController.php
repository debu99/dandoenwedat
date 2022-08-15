<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AuthController.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_AuthController extends Core_Controller_Action_Standard {

  public function facebookAction()
  {
    
    // Clear
    if( null !== $this->_getParam('clear') ) {
      unset($_SESSION['facebook_lock']);
      unset($_SESSION['facebook_uid']);
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
    $facebook = $facebookTable->getApi();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $this->view->error = true;
    $this->view->success = false;
    // Enabled?
    if( !$facebook || 'none' == $settings->core_facebook_enable ) {
      $this->view->error = true;
    }
    
    // Already connected
    if( $facebook->getUser() ) {
       $code = $facebook->getPersistentData('code');
        $this->view->success = true;
        // Attempt to connect account
        $info = $facebookTable->select()
            ->from($facebookTable)
            ->where('user_id = ?', $viewer->getIdentity())
            ->limit(1)
            ->query()
            ->fetch();
        if( empty($info) ) {
          $facebookTable->insert(array(
            'user_id' => $viewer->getIdentity(),
            'facebook_uid' => $facebook->getUser(),
            'access_token' => $facebook->getAccessToken(),
            'code' => $code,
            'expires' => 0,
          ));
        } else {
          // Save info to db
          $facebookTable->update(array(
            'facebook_uid' => $facebook->getUser(),
            'access_token' => $facebook->getAccessToken(),
            'code' => $code,
            'expires' => 0,
          ), array(
            'user_id = ?' => $viewer->getIdentity(),
          ));
        }
      
    }

    // Not connected
    else {
      
      // Okay
      if( !empty($_GET['code']) ) {
       $this->view->error = true;
      }
      
      // Error
      else if( !empty($_GET['error']) ) {
       $this->view->error = true;;
      }

      // Redirect to auth page
      else {
        $url = $facebook->getLoginUrl(array(
          'redirect_uri' => (_ENGINE_SSL ? 'https://' : 'http://') 
              . $_SERVER['HTTP_HOST'] . $this->view->url(),
          'scope' => join(',', array(
            'email',
            'user_birthday',
            'publish_actions',
          )),
        ));
        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    }
  }
 
  public function twitterAction()
  {
    $this->view->error = true;
    $this->view->success = false;
    // Clear
    if( null !== $this->_getParam('clear') ) {
      unset($_SESSION['twitter_lock']);
      unset($_SESSION['twitter_token']);
      unset($_SESSION['twitter_secret']);
      unset($_SESSION['twitter_token2']);
      unset($_SESSION['twitter_secret2']);
    }
    if( $this->_getParam('denied') ) {
      $this->view->error = 'Access Denied!';
      return;
    }
    // Setup
    $viewer = Engine_Api::_()->user()->getViewer();
    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'sesadvancedactivity');
    $twitter = $twitterTable->getApi();
    $twitterOauth = $twitterTable->getOauth();
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    // Check
    if( !$twitter || !$twitterOauth ) {
      $this->error = true;
    }
    
    // Connect
    try {
      
      $accountInfo = null;
     
      if( isset($_SESSION['twitter_token'], $_SESSION['twitter_secret'],
          $_GET['oauth_verifier']) ) {
        $twitterOauth->getAccessToken('https://twitter.com/oauth/access_token', $_GET['oauth_verifier']);

        $_SESSION['twitter_token2'] = $twitter_token = $twitterOauth->getToken();
        $_SESSION['twitter_secret2'] = $twitter_secret = $twitterOauth->getTokenSecret();

        // Reload api?
        $twitterTable->clearApi();
        $twitter = $twitterTable->getApi();

        // Get account info
        $accountInfo = $twitter->account->verify_credentials();

        // Save to settings table (if logged in)
        if( $viewer->getIdentity() ) {
          $info = $twitterTable->select()
              ->from($twitterTable)
              ->where('user_id = ?', $viewer->getIdentity())
              ->query()
              ->fetch();

          if( !empty($info) ) {
            $twitterTable->update(array(
              'twitter_uid' => $accountInfo->id,
              'twitter_token' => $twitter_token,
              'twitter_secret' => $twitter_secret,
            ), array(
              'user_id = ?' => $viewer->getIdentity(),
            ));
          } else {
            $twitterTable->insert(array(
              'user_id' => $viewer->getIdentity(),
              'twitter_uid' => $accountInfo->id,
              'twitter_token' => $twitter_token,
              'twitter_secret' => $twitter_secret,
            ));
          }

	  // Redirect
         $this->view->success = true;

        }

      } else {
        
        unset($_SESSION['twitter_token']);
        unset($_SESSION['twitter_secret']);
        unset($_SESSION['twitter_token2']);
        unset($_SESSION['twitter_secret2']);
        
        // Reload api?
        $twitterTable->clearApi();
        $twitter = $twitterTable->getApi();
        $twitterOauth = $twitterTable->getOauth();
        
        // Connect account
        $twitterOauth->getRequestToken('https://twitter.com/oauth/request_token',
            'http://' . $_SERVER['HTTP_HOST'] . $this->view->url());

        $_SESSION['twitter_token']  = $twitterOauth->getToken();
        $_SESSION['twitter_secret'] = $twitterOauth->getTokenSecret();

        $url = $twitterOauth->getAuthorizeUrl('http://twitter.com/oauth/authenticate');

        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    } catch( Services_Twitter_Exception $e ) {
      if( in_array($e->getCode(), array(500, 502, 503)) ) {
        $this->view->error = true;
        return;
      } else {
        throw $e;
      }
    } catch( Exception $e ) {
      $this->view->error =  true;
    }
    
  }
  public function linkedinAction(){
    $this->view->error = true;
    $this->view->success = false;
    
   if( null !== $this->_getParam('clear') && empty($_GET['oauth_verifier'])) {
     unset($_SESSION['linkedin_lock']);
     unset($_SESSION['linkedin_uid']);
     unset($_SESSION['linkedin_secret']);
     unset($_SESSION['linkedin_token']);
     unset($_SESSION['linkedin_token']);
     unset($_SESSION['linkedin_access']);
   }
   if( $this->_getParam('denied') ) {
      $this->view->error = 'Access Denied!';
      return;
   }
    // Setup
    $viewer = Engine_Api::_()->user()->getViewer();
    $likedinTable = Engine_Api::_()->getDbtable('linkedin', 'sesadvancedactivity');
    $likedin = $likedinTable->getApi();
    $access = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.access','');
    $secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.secret','');
    $db = Engine_Db_Table::getDefaultAdapter();
    
    // Check
    if(empty($likedin)) {
      $this->error = true;
    } 
    try{
      if(empty($_GET['oauth_verifier'])){
        $likedin->setCallbackUrl((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url());
        $likedin->setTokenAccess(NULL);
        $result = $likedin->retrieveTokenRequest();
        if ($result['success'] === TRUE) {
          $_SESSION['linkedin_token'] = $result['linkedin']['oauth_token'];
          $_SESSION['oauth_token_secret']  = $result['linkedin']['oauth_token_secret'];
          header('Location: ' . Linkedin::_URL_AUTH . $result['linkedin']['oauth_token']);
      }
      }else if(!empty($_GET['oauth_verifier'])){
         $likedin->setCallbackUrl((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url());
         $result = $likedin->retrieveTokenAccess($_SESSION['linkedin_token'], $_SESSION['oauth_token_secret'], $_GET['oauth_verifier']);
      if ($result['success'] == TRUE) {
        $_SESSION['linkedin_token'] = $token = $result['linkedin']['oauth_token'];
        $_SESSION['linkedin_secret'] = $secret = $result['linkedin']['oauth_token_secret'];        
        $_SESSION['linkedin_access'] = $result['linkedin'];

        // Get account info
        $user = $likedin->profile('~:(id)');

        $user = json_decode(json_encode((array) simplexml_load_string($user['linkedin'])), 1);
        $userid = $user['id'];
        if(!$userid)
          return;
        $_SESSION['linkedin_lock'] = true;
        $_SESSION['linkedin_uid'] = $userid;
       }
       // Save to settings table (if logged in)
        if( $viewer->getIdentity() ) {
          $info = $likedinTable->select()
              ->from($likedinTable)
              ->where('user_id = ?', $viewer->getIdentity())
              ->query()
              ->fetch();

          if( !empty($info) ) {
            $likedinTable->update(array(
              'linkedin_uid' => $userid,
              'access_token' => $_SESSION['linkedin_token'],
              'code' => $_SESSION['linkedin_secret'],
            ), array(
              'user_id = ?' => $viewer->getIdentity(),
            ));
          } else {
            $likedinTable->insert(array(
              'user_id' => $viewer->getIdentity(),
              'linkedin_uid' => $userid,
              'access_token' => $_SESSION['linkedin_token'],
              'code' => $_SESSION['linkedin_secret'],
            ));
          }

	  // Redirect
         $this->view->success = true;

        }
      }
      
    }catch(Exception $e){
      throw $e;
      $this->view->error = true;  
    }
  }
}
