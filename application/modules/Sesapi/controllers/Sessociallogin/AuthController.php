<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AuthController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sessociallogin_AuthController extends Sesapi_Controller_Action_Standard {
  public function init(){
    
  }
  public function hotmailAction() {
    unset($_SESSION['signup_fields']);
    unset($_SESSION['hotmail_signup']);
    if (isset($_GET['return_url']))
      $_SESSION['redirectURL'] = $_GET['return_url'];
    $viewer = Engine_Api::_()->user()->getViewer();
    $FieldArray = array();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $client_id = $settings->getSetting('sessociallogin.hotmailclientid', false);
    $client_secret = $settings->getSetting('sessociallogin.hotmailclientsecret', false);
    $baseURL = Zend_Registry::get('StaticBaseUrl');
    if ($baseURL)
      $baseurl = $baseURL;
    else
      $baseurl = '/';
    $redirect_uri = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $baseurl . 'sessociallogin/auth/hotmail';
    if (!isset($_GET['code'])) {
      $urls_ = 'https://login.live.com/oauth20_authorize.srf?client_id=' . $client_id . '&scope=wl.signin%20wl.basic%20wl.emails%20wl.contacts_emails&response_type=code&redirect_uri=' . $redirect_uri;
      header('location:' . $urls_);
    }
    if (isset($_GET['code'])) {
      $auth_code = $_GET["code"];
      $fields = array(
          'code' => urlencode($auth_code),
          'client_id' => urlencode($client_id),
          'client_secret' => urlencode($client_secret),
          'redirect_uri' => urlencode($redirect_uri),
          'grant_type' => urlencode('authorization_code')
      );
      $post = '';
      foreach ($fields as $key => $value) {
        $post .= $key . '=' . $value . '&';
      }
      $post = rtrim($post, '&');
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, 'https://login.live.com/oauth20_token.srf');
      curl_setopt($curl, CURLOPT_POST, 5);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      $result = curl_exec($curl);
      curl_close($curl);
      $response = json_decode($result);

      $response = json_decode($result);
      $accesstoken = $response->access_token;


      $my_url = "https://apis.live.net/v5.0/me?access_token=" . $accesstoken;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_URL, $my_url);
      $myres = curl_exec($ch);
      curl_close($ch);

      $profile = json_decode($myres, true);

      if (!empty($profile['id'])) {
        $hotmailId = $profile['id'];
        $FieldArray['id'] = $hotmailId;
        $FieldArray['first_name'] = $profile["first_name"];
        $FieldArray['last_name'] = $profile["last_name"];
        $FieldArray['email'] = $profile["emails"]["account"];
        $FieldArray['photo'] = "https://apis.live.net/v5.0/" . $hotmailId . "/picture?type=large";
      }
      // Attempt to login
      if (!$viewer->getIdentity()) {
        $table = Engine_Api::_()->getDbTable('hotmail', 'sessociallogin');
        if ($hotmailId) {
          $user_id = $table->select()
                  ->from($table, 'user_id')
                  ->where('hotmail_uid = ?', $hotmailId)
                  ->query()
                  ->fetchColumn();
        }
        $viewer = Engine_Api::_()->getItem('user', $user_id);
        if ($user_id  && $viewer->getIdentity()) {
          Zend_Auth::getInstance()->getStorage()->write($user_id);
          // Register login
          $viewer->lastlogin_date = date("Y-m-d H:i:s");
          if ('cli' !== PHP_SAPI) {
            $viewer->lastlogin_ip = $ipExpr;
            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $user_id,
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'hotmail',
            ));
          }
          $viewer->save();
          $url = $_SESSION['redirectURL'];
          return $this->_redirect($url, array('prependBase' => false));
        } else if ($hotmailId) {
          if (!empty($user_id))
            Engine_Api::_()->getDbtable('hotmail', 'sessociallogin')->delete(array('user_id =?' => $user_id));

          // They do not have an account
          $_SESSION['hotmail_signup'] = true;
          $_SESSION['hotmail_uid'] = $hotmailId;
          $_SESSION['signup_fields'] = $FieldArray;
          $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.quick.signup', '');
          if ($quickSignupEnable) {
            return $this->_helper->redirector->gotoRoute(array(), 'sessocial_quick_login', true);
          }
          return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
        }
      }
    }
  }

  public function facebookAction() {
    
    // Clear
    if (null !== $this->_getParam('clear')) {
      unset($_SESSION['facebook_lock']);
      unset($_SESSION['facebook_uid']);
    }
    unset($_SESSION['signup_fields']);
    if (isset($_GET['return_url']))
      $_SESSION['redirectURL'] = $_GET['return_url'];
    $viewer = Engine_Api::_()->user()->getViewer();
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'sessociallogin');
    $facebook = $facebookTable->getApi();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

    // Enabled?
    if (!$facebook) {
      $url = $this->view->url(array('module'=>'sessociallogin','action'=>'success','controller'=>'auth'),"default", true).'?restApi=Sesapi&error=1&error_message=Vaidation Error&sessocialloginstate=failure';
      return $this->_helper->redirector->gotoUrl($url);
    }

    // Already connected
    if ($facebook->getUser()) {
      $code = $facebook->getPersistentData('code');

      // Attempt to login
      if (!$viewer->getIdentity()) {
        $facebook_uid = $facebook->getUser();
        if ($facebook_uid) {
          $user_id = $facebookTable->select()
                  ->from($facebookTable, 'user_id')
                  ->where('facebook_uid = ?', $facebook_uid)
                  ->query()
                  ->fetchColumn();
        }
        $viewer = Engine_Api::_()->getItem('user', $user_id);
        if ($user_id && $viewer->getIdentity()) {
          Zend_Auth::getInstance()->getStorage()->write($user_id);

          // Register login
          $viewer->lastlogin_date = date("Y-m-d H:i:s");

          if ('cli' !== PHP_SAPI) {
            $viewer->lastlogin_ip = $ipExpr;

            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $user_id,
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'facebook',
            ));
          }

          $viewer->save();
        } else if ($facebook_uid) {
          if (!empty($user_id))
            Engine_Api::_()->getDbtable('facebook', 'sessociallogin')->delete(array('user_id =?' => $user_id));
          // They do not have an account
          $_SESSION['facebook_signup'] = true;

          $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.quick.signup', '');
          $apiInfo = $facebook->api('/me?fields=name,gender,email,first_name,last_name,birthday,picture');
          $FieldArray['id'] = $apiInfo["id"];
          $FieldArray['email'] = $apiInfo['email'];
          $FieldArray['first_name'] = $apiInfo['first_name'];
          $FieldArray['last_name'] = $apiInfo['last_name'];
          $user_id = $apiInfo["id"];
          $photo_url = "https://graph.facebook.com/"
                  . $user_id
                  . "/picture?type=large";
          $FieldArray['photo'] = $photo_url;
          $_SESSION['facebook_uid'] = $user_id;
          $_SESSION['signup_fields'] = $FieldArray;
          if ($quickSignupEnable) {
            return $this->_helper->redirector->gotoRoute(array(), 'sessocial_quick_login', true);
          }
          $url = $this->view->url(array( 'module'=>'sessociallogin','action'=>'success','controller'=>'auth'), "default", true).'?restApi=Sesapi&error=0&error_message=&sessocialloginstate=success&result=Sesapi_User_Signup';
          return $this->_helper->redirector->gotoUrl($url);
        }
        
      } else {
        // Check for facebook user
        $facebookInfo = $facebookTable->select()
                ->from($facebookTable)
                ->where('facebook_uid = ?', $facebook->getUser())
                ->limit(1)
                ->query()
                ->fetch();

        if (!empty($facebookInfo) && $facebookInfo['user_id'] != $viewer->getIdentity()) {
          // Redirect to referer page
          $url = $_SESSION['redirectURL'];
          $parsedUrl = parse_url($url);
          $separator = ($parsedUrl['query'] == NULL) ? '?' : '&';
          $url .= $separator . 'already_integrated_fb_account=1';
          $facebook->clearAllPersistentData();
          $url = $this->view->url(array('module'=>'sessociallogin','action'=>'success','controller'=>'auth'),"default", true).'?restApi=Sesapi&error=1&error_message=Validation Error&sessocialloginstate=failure&result=';
          return $this->_helper->redirector->gotoUrl($url);
        }
        // Attempt to connect account
        $info = $facebookTable->select()
                ->from($facebookTable)
                ->where('user_id = ?', $viewer->getIdentity())
                ->limit(1)
                ->query()
                ->fetch();
        if (empty($info)) {
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

      // Redirect to referer page
      $url = $_SESSION['redirectURL'];
      $url = $this->view->url(array( 'module'=>'sessociallogin','action'=>'success','controller'=>'auth'),"default", true).'?restApi=Sesapi&error=0&error_message=&result=Sesapi_User_Login&user_id='.$viewer->getIdentity().'&sessocialloginstate=success';
      return $this->_helper->redirector->gotoUrl($url);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' =>'Sesapi_User_Login')));  
    }

    // Not connected
    else {
      // Okay
      if (!empty($_GET['code'])) {
        // This doesn't seem to be necessary anymore, it's probably
        // being handled in the api initialization
        $url = $this->view->url(array('module'=>'sessociallogin','action'=>'success','controller'=>'auth', 'module'=>'sessociallogin','action'=>'success','controller'=>'auth'),'default',true).'?restApi=Sesapi&error=1&error_message=Validation Error&sessocialloginstate=failure';
        return $this->_helper->redirector->gotoUrl($url);
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'1','error_message'=>'', 'result' =>'')));  
      }

      // Error
      else if (!empty($_GET['error'])) {
        // @todo maybe display a message?
        $url = $this->view->url(array('module'=>'sessociallogin','action'=>'success','controller'=>'auth'),"default", true).'?restApi=Sesapi&error=1&error_message=Validation Error&sessocialloginstate=failure';
        return $this->_helper->redirector->gotoUrl($url);
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'1','error_message'=>'validation_error', 'result' =>'')));  
      }

      // Redirect to auth page
      else {
        $_SESSION['sesapi'] = true;  
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
    echo "YES";die;
  }

  public function pinterestAction() {

    unset($_SESSION['signup_fields']);
    unset($_SESSION['pinterest_signup']);
    if (isset($_GET['return_url']))
      $_SESSION['redirectURL'] = $_GET['return_url'];
    $viewer = Engine_Api::_()->user()->getViewer();
    $FieldArray = array();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    require_once(APPLICATION_PATH . '/application/modules/Sessociallogin/Api/PinterestApi.php');
    $api_key = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.pinterest.appid', '');
    $api_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.pinterest.secret', '');
    $redirectURL = ("https://" . $_SERVER['HTTP_HOST']) . Zend_Registry::get('StaticBaseUrl') . 'sessociallogin/auth/pinterest';
    // Pinterest passes a parameter 'code' in the Redirect Url
    if (isset($_GET['code'])) {
      try {
        $pinterest_ob = new PinterestApi();

        // Get the access token 
        $access_token = $pinterest_ob->GetAccessToken($api_key, $redirectURL, $api_secret, $_GET['code']);

        // Get user informationPINTEREST_REDIRECT_URI
        $user_info = $pinterest_ob->GetUserProfileInfo($access_token);

        if (!empty($user_info['id'])) {
          $pinterestId = $user_info['id'];
          $FieldArray['id'] = $pinterestId;
          $FieldArray['username'] = $user_info['username'];
          $FieldArray['first_name'] = $user_info['first_name'];
          $FieldArray['last_name'] = $user_info['last_name'];
          $FieldArray['photo'] = $user_info['image']['60x60']['url'];
        }
        // Attempt to login
        if (!$viewer->getIdentity()) {
          $table = Engine_Api::_()->getDbTable('pinterest', 'sessociallogin');
          if ($pinterestId) {
            $user_id = $table->select()
                    ->from($table, 'user_id')
                    ->where('pinterest_uid = ?', $pinterestId)
                    ->query()
                    ->fetchColumn();
          }
          $viewer = Engine_Api::_()->getItem('user', $user_id);
          if ($user_id && $viewer->getIdentity()) {
            Zend_Auth::getInstance()->getStorage()->write($user_id);
            // Register login
            $viewer->lastlogin_date = date("Y-m-d H:i:s");
            if ('cli' !== PHP_SAPI) {
              $viewer->lastlogin_ip = $ipExpr;
              Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                  'user_id' => $user_id,
                  'ip' => $ipExpr,
                  'timestamp' => new Zend_Db_Expr('NOW()'),
                  'state' => 'success',
                  'source' => 'pinterest',
              ));
            }
            $viewer->save();
          } else if ($pinterestId) {
            if (!empty($user_id))
              Engine_Api::_()->getDbtable('pinterest', 'sessociallogin')->delete(array('user_id =?' => $user_id));
            // They do not have an account
            $_SESSION['pinterest_signup'] = true;
            $_SESSION['pinterest_uid'] = $pinterestId;
            $_SESSION['signup_fields'] = $FieldArray;
            return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
          }
        }
        $url = $_SESSION['redirectURL'];
        return $this->_redirect($url, array('prependBase' => false));

        // Now that the user is logged in you may want to start some session variables
        // $_SESSION['logged_in'] = 1;
        // You may now want to redirect the user to the home page of your website
        // header('Location: home.php');
      } catch (Exception $e) {
        echo $e->getMessage();
        exit;
      }
    }
    // Not connected
    else {
      // Okay
      if (!empty($_GET['code']))
        $this->view->error = true;
      else {
        $url = "https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile&response_type=code&access_type=offline&redirect_uri=" . $siteURL . "&approval_prompt=force&client_id=" . $api_key;
        $url = "https://api.pinterest.com/oauth/?client_id=" . $api_key . "&redirect_uri=" . urlencode($redirectURL) . "&response_type=code&scope=read_public";
        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    }
  }

  public function yahooAction() {

    unset($_SESSION['signup_fields']);
    unset($_SESSION['yahoo_signup']);

    $viewer = Engine_Api::_()->user()->getViewer();
    $FieldArray = array();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    define('OAUTH_CONSUMER_KEY', $settings->getSetting('sessociallogin.yahooconsumerkey', false));
    define('OAUTH_CONSUMER_SECRET', $settings->getSetting('sessociallogin.yahooconsumersecret', false));
    define('OAUTH_DOMAIN', ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']);
    define('OAUTH_APP_ID', $settings->getSetting('sessociallogin.yahooappid', false));
    // Include the YOS library.
    require realpath(dirname(__FILE__) . '/..') . DIRECTORY_SEPARATOR . 'Api' . DIRECTORY_SEPARATOR . 'Yahoo' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Yahoo.inc';
    if (isset($_GET['return_url'])) {
      $_SESSION['redirectURL'] = $_GET['return_url'];
      // YahooSession::clearSession();
    }
    $hasSession = YahooSession::hasSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);
    if ($hasSession == FALSE) {
      // create the callback url,
      $callback = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) . Zend_Registry::get('StaticBaseUrl') . 'sessociallogin/auth/yahoo';
      $sessionStore = new NativeSessionStore();
      // pass the credentials to get an auth url.
      // this URL will be used for the pop-up.
      header("location:" . YahooSession::createAuthorizationUrl(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, $callback, $sessionStore));
      exit();
    } else {
      // pass the credentials to initiate a session
      $session = YahooSession::requireSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);
      // if a session is initialized, fetch the user's profile information
      if ($session) {
        // Get the currently sessioned user.
        $user = $session->getSessionedUser();
        // Load the profile for the current user.
        $profile = (array) $user->getProfile();
        if (!empty($profile['guid'])) {
          $yahooId = $profile['guid'];
          $FieldArray['id'] = $yahooId;
          $FieldArray['photo'] = $profile['image']->imageUrl;
        }
        // Attempt to login
        if (!$viewer->getIdentity()) {
          $table = Engine_Api::_()->getDbTable('yahoo', 'sessociallogin');
          if ($yahooId) {
            $user_id = $table->select()
                    ->from($table, 'user_id')
                    ->where('yahoo_uid = ?', $yahooId)
                    ->query()
                    ->fetchColumn();
          }
          $viewer = Engine_Api::_()->getItem('user', $user_id);
          if ($user_id  && $viewer->getIdentity()) {
            Zend_Auth::getInstance()->getStorage()->write($user_id);
            // Register login
            $viewer->lastlogin_date = date("Y-m-d H:i:s");
            if ('cli' !== PHP_SAPI) {
              $viewer->lastlogin_ip = $ipExpr;
              Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                  'user_id' => $user_id,
                  'ip' => $ipExpr,
                  'timestamp' => new Zend_Db_Expr('NOW()'),
                  'state' => 'success',
                  'source' => 'yahoo',
              ));
            }
            $viewer->save();
          } else if ($yahooId) {
            if (!empty($user_id))
              Engine_Api::_()->getDbtable('yahoo', 'sessociallogin')->delete(array('user_id =?' => $user_id));
            // They do not have an account
            $_SESSION['yahoo_signup'] = true;
            $_SESSION['yahoo_uid'] = $yahooId;
            $_SESSION['signup_fields'] = $FieldArray;
            return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
          }
        }
        $url = $_SESSION['redirectURL'];
        return $this->_redirect($url, array('prependBase' => false));
      }
    }
  }

  public function googleAction() {
    // Clear
    unset($_SESSION['google_lock']);
    unset($_SESSION['signup_fields']);
    unset($_SESSION['google_signup']);
    if (isset($_GET['return_url']))
      $_SESSION['redirectURL'] = $_GET['return_url'];
    $viewer = Engine_Api::_()->user()->getViewer();
    $FieldArray = array();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');
    $table = Engine_Api::_()->getDbTable('google', 'sessociallogin');
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $this->view->error = true;
    $this->view->success = false;
    $api_key = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientid', '');
    $api_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientsecret', '');
    $siteURL = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) . Zend_Registry::get('StaticBaseUrl') . 'sessociallogin/auth/google';
    // Already connected
    if (!empty($_GET['code'])) {
      $code = $_GET['code'];
      $clientId = $api_key;
      $clientSecret = $api_secret;
      $referer = $siteURL;

      $postBody = 'code=' . urlencode($_GET['code'])
              . '&grant_type=authorization_code'
              . '&redirect_uri=' . urlencode($referer)
              . '&client_id=' . urlencode($clientId)
              . '&client_secret=' . urlencode($clientSecret);

      $curl = curl_init();
      curl_setopt_array($curl, array(CURLOPT_CUSTOMREQUEST => 'POST'
          , CURLOPT_URL => 'https://accounts.google.com/o/oauth2/token'
          , CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'
              , 'Content-Length: ' . strlen($postBody)
              , 'User-Agent: YourApp/0.1 +http://yoursite.com/yourapp'
          )
          , CURLOPT_POSTFIELDS => $postBody
          , CURLOPT_REFERER => $referer
          , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
          , CURLOPT_TIMEOUT => 12 // max seconds to wait
          , CURLOPT_FOLLOWLOCATION => 0 // don't follow any Location headers, use only the CURLOPT_URL, this is for security
          , CURLOPT_FAILONERROR => 0 // do not fail verbosely fi the http_code is an error, this is for security
          , CURLOPT_SSL_VERIFYPEER => 0 // do verify the SSL of CURLOPT_URL, this is for security
          , CURLOPT_VERBOSE => 0 // don't output verbosely to stderr, this is for security
      ));
      $response = curl_exec($curl);
      $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      $response = json_decode($response, true);

      if (empty($response['access_token'])) {
        $this->view->error = true;
        return;
      }
      $accessToken = $response['access_token'];
      $refreshToken = $response['refresh_token'];

      // get user info
      $q = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $accessToken;
      $json = file_get_contents($q);
      $userInfoArray = json_decode($json, true);
      if (!empty($userInfoArray['id'])) {
        $googleid = $userInfoArray['id'];
        $FieldArray['id'] = $googleid;
        $FieldArray['photo'] = $userInfoArray['picture'];
        $FieldArray['email'] = $userInfoArray['email'];
        $FieldArray['first_name'] = $userInfoArray['given_name'];
        $FieldArray['last_name'] = $userInfoArray['family_name'];
      } else {
        $this->view->error = true;
        return;
      }

      // Attempt to login
      if (!$viewer->getIdentity()) {
        if ($googleid) {
          $user_id = $table->select()
                  ->from($table, 'user_id')
                  ->where('google_uid = ?', $googleid)
                  ->query()
                  ->fetchColumn();
        }
        $viewer = Engine_Api::_()->getItem('user', $user_id);
        if ($user_id && $viewer->getIdentity()) {
          Zend_Auth::getInstance()->getStorage()->write($user_id);
          // Register login
          $viewer->lastlogin_date = date("Y-m-d H:i:s");
          if ('cli' !== PHP_SAPI) {
            $viewer->lastlogin_ip = $ipExpr;
            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $user_id,
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'google',
            ));
          }
          $viewer->save();
        } else if ($googleid) {
          if (!empty($user_id))
            Engine_Api::_()->getDbtable('google', 'sessociallogin')->delete(array('user_id =?' => $user_id));
          // They do not have an account
          $_SESSION['google_signup'] = true;
          $_SESSION['access_token'] = $accessToken;
          $_SESSION['refresh_token'] = $refreshToken;
          $_SESSION['google_uid'] = $googleid;
          $_SESSION['signup_fields'] = $FieldArray;

          $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.quick.signup', '');
          if ($quickSignupEnable) {
            return $this->_helper->redirector->gotoRoute(array(), 'sessocial_quick_login', true);
          }

          return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
        }
      }
      $url = $_SESSION['redirectURL'];
      return $this->_redirect($url, array('prependBase' => false));
    }
    // Not connected
    else {
      // Okay
      if (!empty($_GET['code']))
        $this->view->error = true;
      // Error
      else if (!empty($_GET['code']))
        $this->view->error = true;
      // Redirect to auth page
      else {
        $url = "https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile&response_type=code&access_type=offline&redirect_uri=" . $siteURL . "&approval_prompt=force&client_id=" . $api_key;
        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    }
  }

  public function vkAction() {

    // Clear
    unset($_SESSION['vk_lock']);

    $viewer = Engine_Api::_()->user()->getViewer();
    $vkTable = Engine_Api::_()->getDbtable('vk', 'sessociallogin');
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $vkTable->isConnected();
    $api_key = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.vkkey', '');
    $api_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.vksecret', '');
    $siteURL = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) . Zend_Registry::get('StaticBaseUrl') . 'sessociallogin/auth/vk';
    $vk = new Vkontakte([
        'client_id' => $api_key,
        'client_secret' => $api_secret,
        'redirect_uri' => $siteURL,
    ]);

    // Already connected
    if (!empty($_GET['code'])) {
      $vk->authenticate($_GET['code']);
      $_SESSION['vk_access_token'] = $vk->getAccessToken();

      header('Location: ' . $siteURL . '?execute=true');
      exit;
    } else if (!empty($_GET['execute'])) {
      $accessToken = isset($_SESSION['vk_access_token']) ? $_SESSION['vk_access_token'] : null;
      $vk->setAccessToken($accessToken);
      $userNsid = $vk->getUserId();

      $accessToken = $accessToken["access_token"];

      $users = $vk->api('users.get', [
          'user_id' => $userNsid,
          'fields' => [
              'photo_max_orig',
              'bdate',
              'first_name',
              'last_name',
              'screen_name',
          ],
      ]);
      $users = $users[0];
      $photo = $users['photo_max_orig'];
      $FieldArray['id'] = $users['id'];
      $FieldArray['photo'] = $photo;


      $FieldArray['first_name'] = $users['first_name'];

      $FieldArray['last_name'] = $users['last_name'];

      // Attempt to login
      if (!$viewer->getIdentity()) {
        if ($userNsid) {
          $user_id = $vkTable->select()
                  ->from($vkTable, 'user_id')
                  ->where('vk_uid = ?', $userNsid)
                  ->query()
                  ->fetchColumn();
        }
        $viewer = Engine_Api::_()->getItem('user', $user_id);
        if ($user_id && $viewer->getIdentity()) {
          Zend_Auth::getInstance()->getStorage()->write($user_id);
          // Register login
          $viewer->lastlogin_date = date("Y-m-d H:i:s");
          if ('cli' !== PHP_SAPI) {
            $viewer->lastlogin_ip = $ipExpr;
            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $user_id,
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'flickr',
            ));
          }
          $viewer->save();
        } else if ($userNsid) {
          if (!empty($user_id))
            Engine_Api::_()->getDbtable('vk', 'sessociallogin')->delete(array('user_id =?' => $user_id));
          // They do not have an account
          $_SESSION['vk_signup'] = true;
          $_SESSION['access_token'] = $accessToken;
          $_SESSION['vk_uid'] = $userNsid;
          $_SESSION['signup_fields'] = $FieldArray;
          return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
        }
      }
      $url = $_SESSION['redirectURL'];
      return $this->_redirect($url, array('prependBase' => false));
    } else {
      header("Location:" . $vk->getLoginUrl());
      exit();
    }
  }

  public function flickrAction() {
    // Clear
    unset($_SESSION['flickr_lock']);
    unset($_SESSION['flickr_lock']);
    unset($_SESSION['phpFlickr_auth_token']);
    unset($_SESSION['phpFlickr_auth_token']);

    $viewer = Engine_Api::_()->user()->getViewer();
    $flickrTable = Engine_Api::_()->getDbtable('flickr', 'sessociallogin');
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

    $api_key = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.flickrkey', '');
    $api_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.flickrsecret', '');
    $permissions = "read";
    $siteURL = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) . Zend_Registry::get('StaticBaseUrl') . 'sessociallogin/auth/flickr';
    $flickr = new Flickr($api_key, $api_secret, $siteURL);

    // Already connected
    if ($flickr->authenticate('read')) {
      $userNsid = $flickr->getOauthData(Flickr::USER_NSID);
      $fullname = $flickr->getOauthData(Flickr::USER_FULL_NAME);
      $accessToken = $flickr->getOauthData(Flickr::OAUTH_REQUEST_TOKEN);
      $reqtokensecret = $flickr->getOauthData(Flickr::OAUTH_REQUEST_TOKEN_SECRET);

      $getInfo = $flickr->call('flickr.people.getInfo', array('user_id' => $userNsid));
      $photo = "";
      if (isset($getInfo["person"]['iconfarm']) && $getInfo["person"]['iconfarm'] > 0)
        $photo = sprintf('http://farm%s.staticflickr.com/%s/buddyicons/%s_m.jpg', $getInfo["person"]["iconfarm"], $getInfo["person"]["iconserver"], $userNsid);

      $FieldArray['id'] = $userNsid;
      $FieldArray['photo'] = $photo;

      $name = explode(' ', $fullname);

      if (!empty($name[0])) {
        $FieldArray['first_name'] = $name[0];
      }
      if (!empty($name[1]))
        $FieldArray['last_name'] = $name[1];

      // Attempt to login
      if (!$viewer->getIdentity()) {
        if ($userNsid) {
          $user_id = $flickrTable->select()
                  ->from($flickrTable, 'user_id')
                  ->where('flickr_uid = ?', $userNsid)
                  ->query()
                  ->fetchColumn();
        }
        $viewer = Engine_Api::_()->getItem('user', $user_id);
        if ($user_id && $viewer->getIdentity()) {
          Zend_Auth::getInstance()->getStorage()->write($user_id);
          // Register login
          $viewer->lastlogin_date = date("Y-m-d H:i:s");
          if ('cli' !== PHP_SAPI) {
            $viewer->lastlogin_ip = $ipExpr;
            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $user_id,
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'flickr',
            ));
          }
          $viewer->save();
        } else if ($userNsid) {
          if (!empty($user_id))
            Engine_Api::_()->getDbtable('flickr', 'sessociallogin')->delete(array('user_id =?' => $user_id));
          // They do not have an account
          $_SESSION['flickr_signup'] = true;
          $_SESSION['access_token'] = $accessToken;
          $_SESSION['code'] = $reqtokensecret;
          $_SESSION['flickr_uid'] = $userNsid;
          $_SESSION['signup_fields'] = $FieldArray;
          return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
        }
      }
      $url = $_SESSION['redirectURL'];
      return $this->_redirect($url, array('prependBase' => false));
    } else {
      die("Hmm, something went wrong...\n");
    }
  }

  public function instagramAction() {
    // Clear
    if (null !== $this->_getParam('clear')) {
      unset($_SESSION['instagram_lock']);
      unset($_SESSION['instagram_token']);
      unset($_SESSION['instagram_code']);
      unset($_SESSION['instagram_signup']);
    }
    if (isset($_GET['return_url']))
      $_SESSION['redirectURL'] = $_GET['return_url'];
    $viewer = Engine_Api::_()->user()->getViewer();
    $instagramTable = Engine_Api::_()->getDbtable('instagram', 'sessociallogin');
    $instagram = $instagramTable->getApi('auth');
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $this->view->error = true;
    $this->view->success = false;
    // Enabled?
    if (!$instagram) {
      $this->view->error = true;
    }

    // Already connected
    if (!empty($_GET['code'])) {
      $code = $_GET['code'];
      $data = $instagram->getOAuthToken($code);
      $this->view->success = true;
      $infoId = $data->user->id;

      // Attempt to login
      if (!$viewer->getIdentity()) {
        if ($infoId) {
          $user_id = $instagramTable->select()
                  ->from($instagramTable, 'user_id')
                  ->where('instagram_uid = ?', $infoId)
                  ->query()
                  ->fetchColumn();
        }
        $viewer = Engine_Api::_()->getItem('user', $user_id);
        if ($user_id && $viewer->getIdentity()) {
          Zend_Auth::getInstance()->getStorage()->write($user_id);
          // Register login
          $viewer->lastlogin_date = date("Y-m-d H:i:s");
          if ('cli' !== PHP_SAPI) {
            $viewer->lastlogin_ip = $ipExpr;

            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $user_id,
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'instagram',
            ));
          }
          $viewer->save();
        } else if ($infoId) {
          if (!empty($user_id))
            Engine_Api::_()->getDbtable('instagram', 'sessociallogin')->delete(array('user_id =?' => $user_id));
          // They do not have an account
          $_SESSION['instagram_signup'] = true;
          $_SESSION['instagram_code'] = $code;
          $_SESSION['instagram_uid'] = $infoId;
          $_SESSION['instagram_token'] = $data->access_token;
          $_SESSION['sessociallogin_instagram']['inphoto_url'] = $data->user->profile_picture;
          $_SESSION['sessociallogin_instagram']['in_id'] = $data->user->id;
          $flName = explode(' ', $data->user->full_name);
          $_SESSION['sessociallogin_instagram']['first_name'] = $flName[0];
          $_SESSION['sessociallogin_instagram']['last_name'] = $flName[1];
          $_SESSION['sessociallogin_instagram']['in_username'] = $data->user->username;
          return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
        }
      }
      $url = $_SESSION['redirectURL'];
      return $this->_redirect($url, array('prependBase' => false));
    }
    // Not connected
    else {
      // Okay
      if (!empty($_GET['code']))
        $this->view->error = true;
      // Error
      else if (!empty($_GET['error']))
        $this->view->error = true;
      // Redirect to auth page
      else {
        $url = $instagram->getLoginUrl();
        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    }
  }

  public function linkedinAction() {
    $this->view->error = true;
    $this->view->success = false;
    if (isset($_GET['return_url']))
      $_SESSION['redirectURL'] = $_GET['return_url'];
    if (null !== $this->_getParam('clear') && empty($_GET['oauth_verifier'])) {
      unset($_SESSION['linkedin_lock']);
      unset($_SESSION['linkedin_uid']);
      unset($_SESSION['linkedin_secret']);
      unset($_SESSION['linkedin_token']);
      unset($_SESSION['oauth_token_secret']);
      unset($_SESSION['linkedin_token']);
      unset($_SESSION['linkedin_access']);
      unset($_SESSION['signup_fields']);
      unset($_SESSION['linkedin_signup']);
    }
    if ($this->_getParam('denied')) {
      $this->view->error = 'Access Denied!';
      return;
    }
    // Setup
    $viewer = Engine_Api::_()->user()->getViewer();
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $FieldArray = array();
    $likedinTable = Engine_Api::_()->getDbtable('linkedin', 'sessociallogin');
    $likedin = $likedinTable->getApi();
    $access = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.access', '');
    $secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.secret', '');

    // Check
    if (empty($likedin)) {
      $this->error = true;
    }
    try {
      if (empty($_GET['oauth_verifier'])) {
        $likedin->setCallbackUrl((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url());
        $likedin->setTokenAccess(NULL);
        $result = $likedin->retrieveTokenRequest();
        if ($result['success'] === TRUE) {
          $_SESSION['linkedin_token'] = $result['linkedin']['oauth_token'];
          $_SESSION['oauth_token_secret'] = $result['linkedin']['oauth_token_secret'];
          header('Location: ' . Linkedin::_URL_AUTH . $result['linkedin']['oauth_token']);
          exit();
        }
      } else if (!empty($_GET['oauth_verifier'])) {
        $likedin->setCallbackUrl((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url());
        $result = $likedin->retrieveTokenAccess($_SESSION['linkedin_token'], $_SESSION['oauth_token_secret'], $_GET['oauth_verifier']);
        if ($result['success'] == TRUE) {
          $_SESSION['linkedin_token'] = $token = $result['linkedin']['oauth_token'];
          $_SESSION['linkedin_secret'] = $secret = $result['linkedin']['oauth_token_secret'];
          $_SESSION['linkedin_access'] = $result['linkedin'];

          // Get account info
          $user = $likedin->profile('~:(id,picture-urls::(original),email-address,first-name,last-name)');

          $user = json_decode(json_encode((array) simplexml_load_string($user['linkedin'])), 1);

          $FieldArray['id'] = $user['id'];
          $FieldArray['photo'] = $user['picture-urls']['picture-url'];
          $FieldArray['email'] = $user['email-address'];
          $FieldArray['first_name'] = $user['first-name'];
          $FieldArray['last_name'] = $user['last-name'];
          $infoId = $user['id'];
          if (!$infoId)
            return;
          $_SESSION['linkedin_lock'] = true;
          $_SESSION['linkedin_uid'] = $infoId;
          $_SESSION['signup_fields'] = $FieldArray;
        }
        // Attempt to login
        if (!$viewer->getIdentity()) {

          if ($infoId) {
            $user_id = $likedinTable->select()
                    ->from($likedinTable, 'user_id')
                    ->where('linkedin_uid = ?', $infoId)
                    ->query()
                    ->fetchColumn();
          }
          $viewer = Engine_Api::_()->getItem('user', $user_id);
          if ($user_id && $viewer->getIdentity()) {
            Zend_Auth::getInstance()->getStorage()->write($user_id);
            // Register login
            $viewer->lastlogin_date = date("Y-m-d H:i:s");
            if ('cli' !== PHP_SAPI) {
              $viewer->lastlogin_ip = $ipExpr;

              Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                  'user_id' => $user_id,
                  'ip' => $ipExpr,
                  'timestamp' => new Zend_Db_Expr('NOW()'),
                  'state' => 'success',
                  'source' => 'linkedin',
              ));
            }
            $viewer->save();
          } else if ($infoId) {
            if (!empty($user_id))
              Engine_Api::_()->getDbtable('linkedin', 'sessociallogin')->delete(array('user_id =?' => $user_id));
            // They do not have an account
            $_SESSION['linkedin_signup'] = true;
            $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.quick.signup', '');
            if ($quickSignupEnable) {
              return $this->_helper->redirector->gotoRoute(array(), 'sessocial_quick_login', true);
            }
            return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
          }
        }
        $url = $_SESSION['redirectURL'];
        return $this->_redirect($url, array('prependBase' => false));
      }
    } catch (Exception $e) {
      throw $e;
      $this->view->error = true;
    }
  }
  function successAction(){
    echo "";die;  
  }
}
