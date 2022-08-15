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
class User_AuthController extends Sesapi_Controller_Action_Standard
{
  public function checkVersion($android, $ios)
  {
    if (is_numeric(_SESAPI_VERSION_ANDROID) && _SESAPI_VERSION_ANDROID >= $android)
      return  true;
    if (is_numeric(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS >= $ios)
      return true;
    return false;
  }
    function timeDiff($seconds){
        // extract hours

        $hours = floor($seconds / (60 * 60));
        // extract minutes
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
        // extract the remaining seconds
        $divisor_for_seconds = $divisor_for_minutes % 60;
        $seconds = ceil($divisor_for_seconds);
        // return the final array
        $string = "";
        if($hours > 0)
            $string .= $hours.($hours != 1 ? " hours " : " hour ");
        if($minutes > 0)
            $string .= $minutes.($minutes != 1 ? " minutes " : " minute ");
        if($seconds > 0)
            $string .= $seconds.($seconds != 1 ? " seconds " : " second ");
        return trim($string," ");
    }
  public function loginAction()
  {
    $user = Engine_Api::_()->user()->getViewer();
    // Check login creds
    extract($_POST); // $email, $password, $remember

    if (!empty($user_id)) {
      $demoUser = Engine_Api::_()->getItem('user', $user_id);
      $getDemoUserId = Engine_Api::_()->getDbtable('demousers', 'sesdemouser')->getDemoUserId($user_id);
      if (!empty($getDemoUserId) && $getDemoUserId && $demoUser->level_id != 1) {
        $this->successLogin($demoUser);
      } else {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'validation_error'));
      }
    }
    if (empty($email))
      extract($_GET);
    if (empty($email) || empty($password)) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'validation_error'));
    }
    $user_table = Engine_Api::_()->getDbtable('users', 'user');
    $user_select = $user_table->select()
      ->where('email = ?', $email); // If post exists
    $user = $user_table->fetchRow($user_select);

    // Get ip address
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $is_error = false;
    $message = '';

    // Check if user exists
    if (empty($user)) {
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('otpsms') && $this->checkVersion(2.7, 1.7)) {
        $user_table = Engine_Api::_()->getDbtable('users', 'user');
        $user_select = $user_table->select()
          ->where('phone_number = ?', $email);
        // If post exists
        $user = $user_table->fetchRow($user_select);
        if (empty($user)) {
          $this->view->status = false;
          $is_error = true;
          $this->view->error = $error = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
          //  $form->addError(Zend_Registry::get('Zend_Translate')->_('No record of a member with that email was found.'));
          $message = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
          // Register login
          Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
            'email' => $email,
            'ip' => $ipExpr,
            'timestamp' => new Zend_Db_Expr('NOW()'),
            'state' => 'no-member',
          ));
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $_POST));
        }
      } else {
        $this->view->status = false;
        $is_error = true;
        $this->view->error = $error = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
        $message = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'no-member',
        ));

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $_POST));
      }
    }
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $lockAccount = ($settings
          ->getSetting('core.spam.lockaccount', 0));
      $lockAttempts = ($settings
          ->getSetting('core.spam.lockattempts', 3));
      $lockDuration = ($settings
          ->getSetting('core.spam.lockduration', 120));


      if($lockAccount && $user->login_attempt_count && $user->login_attempt_count >= $lockAttempts){
          if(strtotime($user->last_login_attempt) + $lockDuration > time()){
              $this->view->status = false;
              $timeDiff = $this->timeDiff(strtotime($user->last_login_attempt) + $lockDuration - time());
              $this->view->error = $this->view->translate('You have reached maximum login attempts. Please try after %s.',$timeDiff);
              $user->login_attempt_count = $user->login_attempt_count + 1;
              $user->save();
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have reached maximum login attempts. Please try after %s.',$timeDiff)));
              return;
          }else{
              $user->last_login_attempt = NULL;
              $user->login_attempt_count = 0;
              $user->save();
          }
      }
    $isValidPassword = Engine_Api::_()->user()->checkCredential($user->getIdentity(), $password,$user);
    if (!$isValidPassword) {
        if($lockAccount){
            $user->last_login_attempt = date('Y-m-d H:i:s');
            $user->login_attempt_count = $user->login_attempt_count + 1;
            $user->save();
        }
      $this->view->status = false;
      $is_error = true;
      $this->view->error = $error = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
      //$form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
      $message = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
      // Register bad password login
      Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
        'user_id' => $user->getIdentity(),
        'email' => $email,
        'ip' => $ipExpr,
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'state' => 'bad-password',
      ));

      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error));
    }

    // Check if user is verified and enabled
    if (!$user->enabled) {
      if (!$user->verified) {
        $this->view->status = false;
        $is_error = true;
        $resend_url = $this->_helper->url->url(array('action' => 'resend', 'email' => $email), 'user_signup', true);
        $translate = Zend_Registry::get('Zend_Translate');
        $error = $translate->translate('This account still requires either email verification.');
        //$error .= ' ';
        //$error .= sprintf($translate->translate('Click <a href="%s">here</a> to resend the email.'), $resend_url);
        //$form->getDecorator('errors')->setOption('escape', false);
        $message = $error;
        //$form->addError($error);

        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'disabled',
        ));

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error));
      } else if (!$user->approved) {
        $this->view->status = false;
        $is_error = true;
        $translate = Zend_Registry::get('Zend_Translate');
        $error = $translate->translate('This account still requires admin approval.');
        //  $form->getDecorator('errors')->setOption('escape', false);
        //   $form->addError($error);
        $message = $error;

        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'disabled',
        ));

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error));
      }
      // Should be handled by hooks or payment
      //return;
      $translate = Zend_Registry::get('Zend_Translate');
      $error = $translate->translate('That user account has not yet been verified or disabled by an admin.');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error));
    }
    // $dovarify = false;
    //OTP CODE HERE
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('otpsms') && $this->checkVersion(2.7, 1.7)) {
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $otpAllow = Engine_Api::_()->authorization()->getPermission($user->level_id, 'otpsms', 'verification');
      if ($settings->getSetting('otpsms.login.options', 0) != 1 && !empty($otpAllow) && !empty($user->phone_number) && !empty($user->enable_verification)) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'result' => 'Otpsms_Form_Signup_Otpsms'));
      }
    }

    // Handle subscriptions
    if (Engine_Api::_()->hasModuleBootstrap('payment')) {
      // Check for the user's plan
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      if (!$subscriptionsTable->check($user)) {
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'unpaid',
        ));
        // Redirect to subscription page
        $subscriptionSession = new Zend_Session_Namespace('Payment_Subscription');
        $subscriptionSession->unsetAll();
        $subscriptionSession->user_id = $user->getIdentity();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => 'Sesapi_Form_Signup_Subscription'), $user->getIdentity());
      }
    }

    // Run pre login hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginBefore', $user);
    foreach ((array) $event->getResponses() as $response) {
      if (is_array($response)) {
        if (!empty($response['error']) && !empty($response['message'])) {
          // $form->addError($response['message']);
          $error = $response['message'];
        } else if (!empty($response['redirect'])) {
          // $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
        } else {
          continue;
        }

        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'third-party',
        ));
      }
    }

    // Version 3 Import compatibility
    if (empty($user->password)) {
      $compat = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.compatibility.password');
      $migration = null;
      try {
        $migration = Engine_Db_Table::getDefaultAdapter()->select()
          ->from('engine4_user_migration')
          ->where('user_id = ?', $user->getIdentity())
          ->limit(1)
          ->query()
          ->fetch();
      } catch (Exception $e) {
        $migration = null;
        $compat = null;
      }
      if (!$migration) {
        $compat = null;
      }

      if ($compat == 'import-version-3') {

        // Version 3 authentication
        $cryptedPassword = self::_version3PasswordCrypt($migration['user_password_method'], $migration['user_code'], $password);
        if ($cryptedPassword === $migration['user_password']) {
          // Regenerate the user password using the given password
          $user->salt = (string) rand(1000000, 9999999);
          $user->password = $password;
          $user->save();
          Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
          // @todo should we delete the old migration row?
        } else {
          $this->view->status = false;
          $this->view->error = $error = $this->view->translate('Invalid credentials');
          //$form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
          $message = $this->view->translate('Invalid credentials supplied');
        }
        // End Version 3 authentication

      } else {
        //   $form->addError('There appears to be a problem logging in. Please reset your password with the Forgot Password link.');
        $message = $this->view->translate('There appears to be a problem logging in. Please reset your password with the Forgot Password link.');
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'v3-migration',
        ));
      }
    }

    // Normal authentication
    else {
        $authResult = Engine_Api::_()->user()->authenticate($email, $password,$user);
        $authCode = $authResult->getCode();
        Engine_Api::_()->user()->setViewer();

      if ($authCode != Zend_Auth_Result::SUCCESS) {
        $this->view->status = false;
        $this->view->error = $error = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
        //    $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
        $message = $this->view->translate('The credentials you have supplied are invalid. Please check your email and password, and try again.');
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'bad-password',
        ));

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $message));
      }
    }

    // -- Success! --

    // Register login
    $loginTable = Engine_Api::_()->getDbtable('logins', 'user');
    $loginTable->insert(array(
      'user_id' => $user->getIdentity(),
      'email' => $email,
      'ip' => $ipExpr,
      'timestamp' => new Zend_Db_Expr('NOW()'),
      'state' => 'success',
      'active' => true,
    ));
    $_SESSION['login_id'] = $login_id = $loginTable->getAdapter()->lastInsertId();

    // Remember
    if ($remember) {
      $lifetime = 1209600; // Two weeks
      Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
      Zend_Session::rememberMe($lifetime);
    }

    // Increment sign-in count
    Engine_Api::_()->getDbtable('statistics', 'core')
      ->increment('user.logins');

    // Test activity @todo remove
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($viewer->getIdentity()) {
      $viewer->lastlogin_date = date("Y-m-d H:i:s");
      if ('cli' !== PHP_SAPI) {
        $viewer->lastlogin_ip = $ipExpr;
      }
      $viewer->save();
      Engine_Api::_()->getDbtable('actions', 'activity')
        ->addActivity($viewer, $viewer, 'login');
    }

    // Assign sid to view for json context
    $this->view->status = true;
    $this->view->message = $message = $this->view->translate('Login successful');
    $this->view->sid = Zend_Session::getId();
    $this->view->sname = Zend_Session::getOptions('name');

    // Run post login hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginAfter', $viewer);

    // Do redirection only if normal context
    if (null === $this->_helper->contextSwitch->getCurrentContext()) {
      // Redirect by session
      $session = new Zend_Session_Namespace('Redirect');
      if (isset($session->uri)) {
        $uri  = $session->uri;
        $opts = $session->options;
        $session->unsetAll();
      } else if (isset($session->route)) {
        $session->unsetAll();
      }

      // Redirect by hook
      foreach ((array) $event->getResponses() as $response) {
        if (is_array($response)) {
          if (!empty($response['error']) && !empty($response['message'])) {
            $message = $response['message'];
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $message));
          } else if (!empty($response['redirect'])) {
            //return $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
          }
        }
      }

      if ($is_error) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $message));
      } else {
        $this->successLogin($user);
      }
    }
  }
  public function loginOtpFormAction()
  {
    $email = $this->_getParam('email', null);
    $password = $this->_getParam('password', null);
    $user_table = Engine_Api::_()->getDbTable('users', 'user');
    $user_select = $user_table->select()
      ->where('email = ?', $email); // If post exists
    $user = $user_table->fetchRow($user_select);
    if (empty($user)) {
      $user_select = $user_table->select()
        ->where('phone_number = ?', $email);
      $user = $user_table->fetchRow($user_select);
    }

    if ($this->_getParam('formType') == 'login') {
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $otpAllow = Engine_Api::_()->authorization()->getPermission($user->level_id, 'otpsms', 'verification');
      $db = Engine_Db_Table::getDefaultAdapter();
      $ipObj = new Engine_IP();
      $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

      if ($settings->getSetting('otpsms.login.options', 0) != 1 && !empty($otpAllow) && !empty($user->phone_number) && !empty($user->enable_verification)) {
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'OtpVerificationSend',
        ));


        $otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
        $otpverification->unsetAll();
        //validate opt limit set by admin
        $codes = Engine_Api::_()->getDbTable('codes', 'otpsms');
        $response = $codes->generateCode($user, $type = "login");
        if (!empty($response['error'])) {
          $error = $response['message'];
          $otpverification->step = 1;
          $_POST['email'] = null;
          $_POST['password'] = null;
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error));
        }

        $otpverification->unsetAll();
        $otpverification->user_id = $user->getIdentity();
        $otpverification->step = 2;
        $otpverification->email = $email;
        $otpverification->password = $password;
        //$otpverification->return_url = $this->_getparam('return_url');
        //$otpverification->remember = $this->_getparam('remember',0);
        $code = $response['code'];
        $_POST['email'] = null;
        $_POST['password'] = null;
        //send code to mobile
        Engine_Api::_()->otpsms()->sendMessage("+" . $user->country_code . $user->phone_number, $code, "login_template");

        //redirect to outh login page

        //return $this->_helper->redirector->gotoRoute(array('action' => 'verify'), 'optsms_verify', true);
        $expire = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.duration', 600);
        $otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
        $user_id = $otpverification->user_id;
        $otpform = new Otpsms_Form_Signup_Otpsms();
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($otpform);
        $this->generateFormFields($formFields, array('user_id' => $user_id, 'action' => 'verify-login', 'otpsms_duration' => $expire, ''));
      }
    } elseif ($this->_getParam('formType') == 'forgot') {
      $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
      $result = $forgotTable->delete(array(
        'user_id = ?' => $user->getIdentity(),
      ));
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('otpsms') && $this->checkVersion(2.7, 1.7)) {
        if (!empty($user->phone_number)) {
          $code = Engine_Api::_()->otpsms()->generateCode();
          //send code to mobile
          $number = "+" . $user->country_code . $user->phone_number;
          Engine_Api::_()->otpsms()->sendMessage("+" . $user->country_code . $user->phone_number, $code, "forgot_template");
          $codesend = true;

          $expire = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.duration', 600);
          $otpform = new Otpsms_Form_Signup_Otpsms();
          $reul = $forgotTable->insert(array(
            'user_id' => $user->getIdentity(),
            'code' => $code,
            'creation_date' => date('Y-m-d H:i:s'),
          ));
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($otpform);
          $this->generateFormFields($formFields, array('user_id' => $user->getIdentity(), 'action' => 'reset', 'otpsms_duration' => $expire));
        }
      }
    }
  }
  public function resendLoginCodeAction()
  {
    //$type = $this->_getParam('type');
    $user = Engine_Api::_()->getItem('user', $this->_getParam('user_id', 0));
    //validate opt limit set by admin
    $codes = Engine_Api::_()->getDbTable('codes', 'otpsms');
    $response = $codes->generateCode($user, $type = "login");
    if (!empty($response['error'])) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $response['message'], 'result' => array()));
    }
    $code = $response['code'];
    //send code to mobile
    Engine_Api::_()->otpsms()->sendMessage("+" . $user->country_code . $user->phone_number, $code, "login_template");
    //send for to reponse
    //$form = new Otpsms_Form_Signup_Otpsms();
    //$description = $form->getDescription();
    //echo json_encode(array('error'=>0,'description'=>$description));die;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('success' => true)));
  }
  public function verifyLoginAction()
  {
    $code = $this->_getParam('code', '');
    $user_id = $this->_getParam('user_id', '');
    //fetch from table
    $codes = Engine_Api::_()->getDbTable('codes', 'otpsms');
    $select = $codes->select()->where('user_id =?', $user_id)->where('code =?', $code)->where('type =?', 'login');
    $codeData = $codes->fetchRow($select);
    if (!$codeData) {
      $message = $this->view->translate("OTP entered is not valid.");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'result' => array('error_message' => $message)));
    }
    $otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
    $otpverification->code = $code;
    $expire = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.duration', 600);

    $time = time() - $expire;
    if (strtotime($codeData->modified_date) < $time) {
      $message = $this->view->translate("The OTP code you entered has expired. Please click on'RESEND' button to get new OTP code.");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'result' => array('error_message' => $message)));
    }
    /*------------login code-------------*/
    $user = Engine_Api::_()->getItem('user', $user_id);
    $email = $user->email;
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    $form = new User_Form_Login();
    // Handle subscriptions
    if (Engine_Api::_()->hasModuleBootstrap('payment')) {
      // Check for the user's plan
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      if (!$subscriptionsTable->check($user)) {
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'unpaid',
        ));
        // Redirect to subscription page
        $subscriptionSession = new Zend_Session_Namespace('Payment_Subscription');
        $subscriptionSession->unsetAll();
        $subscriptionSession->user_id = $user->getIdentity();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => 'Sesapi_Form_Signup_Subscription'), $user->getIdentity());
      }
    }

    // Run pre login hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginBefore', $user);
    foreach ((array) $event->getResponses() as $response) {
      if (is_array($response)) {
        if (!empty($response['error']) && !empty($response['message'])) {
          $form->addError($response['message']);
        } else if (!empty($response['redirect'])) {
          $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
        } else {
          continue;
        }

        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'third-party',
        ));

        // Return
        return;
      }
    }

    // Version 3 Import compatibility
    if (empty($user->password)) {
      $compat = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.compatibility.password');
      $migration = null;
      try {
        $migration = Engine_Db_Table::getDefaultAdapter()->select()
          ->from('engine4_user_migration')
          ->where('user_id = ?', $user->getIdentity())
          ->limit(1)
          ->query()
          ->fetch();
      } catch (Exception $e) {
        $migration = null;
        $compat = null;
      }
      if (!$migration) {
        $compat = null;
      }

      if ($compat == 'import-version-3') {

        // Version 3 authentication
        $cryptedPassword = self::_version3PasswordCrypt($migration['user_password_method'], $migration['user_code'], $password);
        if ($cryptedPassword === $migration['user_password']) {
          // Regenerate the user password using the given password
          $user->salt = (string) rand(1000000, 9999999);
          $user->password = $password;
          $user->save();
          Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
          // @todo should we delete the old migration row?
        } else {
          $message = $this->view->translate('Invalid credentials supplied');
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'result' => array('error_message' => $message)));
        }
        // End Version 3 authentication

      } else {
        $message = 'There appears to be a problem logging in. Please reset your password with the Forgot Password link.';

        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'v3-migration',
        ));

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $message));
      }
    } else {
      //Engine_Api::_()->user()->setViewer();
      Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
      /*$authResult = Engine_Api::_()->user()->authenticate($email, $password);
      $authCode = $authResult->getCode();
      Engine_Api::_()->user()->setViewer();

      if( $authCode != Zend_Auth_Result::SUCCESS ) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid credentials');
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
        
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'bad-password',
        ));

        return;
      }*/
    }
    // -- Success! --
    // Register login
    $loginTable = Engine_Api::_()->getDbtable('logins', 'user');
    $loginTable->insert(array(
      'user_id' => $user->getIdentity(),
      'email' => $email,
      'ip' => $ipExpr,
      'timestamp' => new Zend_Db_Expr('NOW()'),
      'state' => 'success',
      'active' => true,
    ));
    $_SESSION['login_id'] = $login_id = $loginTable->getAdapter()->lastInsertId();

    // Remember
    if ($remember) {
      $lifetime = 1209600; // Two weeks
      Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
      Zend_Session::rememberMe($lifetime);
    }

    // Increment sign-in count
    Engine_Api::_()->getDbtable('statistics', 'core')
      ->increment('user.logins');

    // Test activity @todo remove
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($viewer->getIdentity()) {
      $viewer->lastlogin_date = date("Y-m-d H:i:s");
      if ('cli' !== PHP_SAPI) {
        $viewer->lastlogin_ip = $ipExpr;
      }
      $viewer->save();
      Engine_Api::_()->getDbtable('actions', 'activity')
        ->addActivity($viewer, $viewer, 'login');
    }

    // Assign sid to view for json context
    $status = true;
    $message = Zend_Registry::get('Zend_Translate')->_('Login successful');
    $sid = Zend_Session::getId();
    $sname = Zend_Session::getOptions('name');

    // Run post login hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginAfter', $viewer);

    // Do redirection only if normal context
    if (null === $this->_helper->contextSwitch->getCurrentContext()) {
      // Redirect by session
      $session = new Zend_Session_Namespace('Redirect');
      if (isset($session->uri)) {
        $uri  = $session->uri;
        $opts = $session->options;
        $session->unsetAll();
      } else if (isset($session->route)) {
        $session->unsetAll();
      }
      // Redirect by hook
      foreach ((array) $event->getResponses() as $response) {
        if (is_array($response)) {
          if (!empty($response['error']) && !empty($response['message'])) {
            return $form->addError($response['message']);
          } else if (!empty($response['redirect'])) {
            //return $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
          }
        }
      }

      // Redirect to edit profile if user has no profile type
      /*$aliasedFields = $viewer->fields()->getFieldsObjectsByAlias();
      $profileType = isset($aliasedFields['profile_type']) ?
        (is_object($aliasedFields['profile_type']->getValue($user)) ?
          $aliasedFields['profile_type']->getValue($viewer)->value : null
      ) : null;
      */
      if (empty($profileType)) {
        //return $this->_helper->redirector->gotoRoute(array(
        //'action' => 'profile',
        //'controller' => 'edit',
        //), 'user_extended', false);
      }
      // Just redirect to home

      $this->successLogin($user);
    }
  }
  protected function successLogin($user)
  {
    $result = $user->toArray();
    if (!empty($result['photo_id'])) {
      $photo = $this->getBaseUrl(true, $user->getPhotoUrl());
      $result['photo_url']  = $photo;
    } else
      $result['photo_url'] = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
    //Auth token
    $token = Engine_Api::_()->getApi('oauth', 'sesapi')->generateOauthToken();
    $token->user_id = $result['user_id'];
    $token->save();
    //Register device token
    Engine_Api::_()->getDbTable('users', 'sesapi')->register(array('user_id' => $result['user_id'], 'device_uuid' => $_REQUEST['device_uuid']));
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => $result, 'token' => $token->token));
  }
  public function logoutAction()
  {

    // Check if already logged out
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer->getIdentity()) {
      $error = $this->view->translate('You are already logged out.');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    // Run logout hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLogoutBefore', $viewer);

    // Test activity @todo remove
    Engine_Api::_()->getDbtable('actions', 'activity')
      ->addActivity($viewer, $viewer, 'logout');

    // Update online status
    $onlineTable = Engine_Api::_()->getDbtable('online', 'user')
      ->delete(array(
        'user_id = ?' => $viewer->getIdentity(),
      ));

    // Logout
    Engine_Api::_()->user()->getAuth()->clearIdentity();

    if (!empty($_SESSION['login_id'])) {
      Engine_Api::_()->getDbtable('logins', 'user')->update(array(
        'active' => false,
      ), array(
        'login_id = ?' => $_SESSION['login_id'],
      ));
      unset($_SESSION['login_id']);
    }


    // Run logout hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLogoutAfter', $viewer);

    $doRedirect = true;

    // Clear twitter/facebook session info

    // facebook api
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
    $facebook = $facebookTable->getApi();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');
    if ($facebook && 'none' != $settings->core_facebook_enable) {
      /*
      $logoutUrl = $facebook->getLogoutUrl(array(
        'next' => 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array(), 'default', true),
      ));
      */
      if (
        method_exists($facebook, 'getAccessToken') && ($access_token = $facebook->getAccessToken())
      ) {
        $doRedirect = false; // javascript will run to log them out of fb
        $this->view->appId = $facebook->getAppId();
        $access_array = explode("|", $access_token);
        if (($session_key = $access_array[1])) {
          $this->view->fbSession = $session_key;
        }
      }
      try {
        $facebook->clearAllPersistentData();
      } catch (Exception $e) {
        // Silence
      }
    }

    unset($_SESSION['facebook_lock']);
    unset($_SESSION['facebook_uid']);

    // Response
    $this->view->status = true;
    $this->view->message = $this->view->translate('You are now logged out.');
    //revoke token
    $token = $_REQUEST['auth_token'];
    $token = Engine_Api::_()->getApi('oauth', 'sesapi')->revokeToken($token);
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => 1)));
  }

  public function forgotAction()
  {
    // Check request
    if (!$this->getRequest()->isPost() || !$_POST['email']) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'validation_error'));
    }
    $email = $_POST['email'];
    $valid = true;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $valid = false;
    }
    if (!$valid) {
      if (is_numeric($email)) {
        $valid = true;
      }
    }
    if (!$valid) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'Email Address / Phone number is not valid, Please provide a valid Email or phone number.'));
    }
    // Check for existing user
    $user = Engine_Api::_()->getDbtable('users', 'user')
      ->fetchRow(array('email = ?' => $email));
    if (!$user || !$user->getIdentity()) {

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('otpsms') && $this->checkVersion(2.7, 1.7) && !empty($user->phone_number)) {
        $user = Engine_Api::_()->getDbtable('users', 'user')
          ->fetchRow(array('phone_number = ?' => $email));
      }
      if (!$user || !$user->getIdentity()) {
        $message = ('A user account with that email was not found.');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $message));
      }
    }

    // Check to make sure they're enabled
    if (!$user->enabled) {
      $message = ('That user account has not yet been verified or disabled by an admin.');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $message));
    }

    // Ok now we can do the fun stuff
    $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
    $db = $forgotTable->getAdapter();
    $db->beginTransaction();

    try {
      // Delete any existing reset password codes

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('otpsms') && $this->checkVersion(2.7, 1.7) && !empty($user->phone_number)) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => 'Otpsms_Form_Signup_Otpsms'));
      }
      // Delete any existing reset password codes
      $forgotTable->delete(array(
        'user_id = ?' => $user->getIdentity(),
      ));

      $code = base_convert(md5($user->salt . $user->email . $user->user_id . uniqid(time(), true)), 16, 36);
      $forgotTable->insert(array(
        'user_id' => $user->getIdentity(),
        'code' => $code,
        'creation_date' => date('Y-m-d H:i:s'),
      ));
      // Send user an email
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'core_lostpassword', array(
        'host' => $_SERVER['HTTP_HOST'],
        'email' => $user->email,
        'date' => time(),
        'recipient_title' => $user->getTitle(),
        'recipient_link' => $user->getHref(),
        'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
        'object_link' => $this->_helper->url->url(array('module' => 'user', 'action' => 'reset', 'code' => $code, 'uid' => $user->getIdentity())),
        'queue' => false,
      ));
      // Show success
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success' => 'Email sent successfully, please check your email.')));
    } catch (Exception $e) {
      $db->rollBack();
      $message =  $e->__toString();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $message));
    }
  }

  public function resetAction()
  {
    // no logged in users
    if (Engine_Api::_()->user()->getViewer()->getIdentity()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('you have already loged in.')));
      //return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }

    // Check for empty params
    $user_id = $this->_getParam('uid');
    $code = $this->_getParam('code');

    if (empty($user_id) || empty($code)) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing')));
      //return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Check user
    $user = Engine_Api::_()->getItem('user', $user_id);
    if (!$user || !$user->getIdentity()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user does not exist')));
      //return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Check code
    $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
    $forgotSelect = $forgotTable->select()
      ->where('user_id = ?', $user->getIdentity())
      ->where('code = ?', $code);
    $forgotRow = $forgotTable->fetchRow($forgotSelect);
    if (!$forgotRow || (int) $forgotRow->user_id !== (int) $user->getIdentity()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Something went wrong try again.')));
      // return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Code expired
    // Note: Let's set the current timeout for 6 hours for now
    $min_creation_date = time() - (3600 * 1);
    if (strtotime($forgotRow->creation_date) < $min_creation_date) { // @todo The strtotime might not work exactly right
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('code has been expired.')));
      // return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Make form
    $form = $form = new User_Form_Auth_Reset();
    //$form->setAction($this->_helper->url->url(array()));
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    // Check request
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }

    // Check data
    if (!$form->isValid($this->getRequest()->getPost())) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    }

    // Process
    $values = $form->getValues();

    // Check same password
    if ($values['password'] !== $values['password_confirm']) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The passwords you entered did not match.'), 'result' => array()));
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
        $this->validateFormFields($validateFields);
    }
    // Db
    $db = $user->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      // Delete the lost password code now
      $forgotTable->delete(array(
        'user_id = ?' => $user->getIdentity(),
      ));
      // This gets handled by the post-update hook
      $user->password = $values['password'];
      $user->save();
      $db->commit();
      $reset = true;
      //return $this->_helper->redirector->gotoRoute(array(), 'user_login', true);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('success' => 'true')));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function phoneNumberAction()
  {
    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer || !$viewer->getIdentity())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

    $otpsmsverification = new Zend_Session_Namespace('Otpsms_Verification');
    $user = $subject = Engine_Api::_()->user()->getViewer();
    Engine_Api::_()->core()->setSubject($subject);
    if (!count($_POST) || $this->_getParam('getForm')) {
      $otpsmsverification->unsetAll();
      $otpsmsverification->step = 1;
    } else {
      $otpsmsverification->step = $this->_getParam('step', 0);
    }
    $expiretime = Engine_Api::_()->getApi('settings', 'core')->getSetting("otpsms.duration", 600);
    if ($otpsmsverification->step == 1) {
      $form = $form = new Otpsms_Form_Phonenumber();
      $form->phone_number->setValue($subject->phone_number);
      if ($subject->country_code)
        $form->country_code->setValue($subject->country_code);
      else
        $form->removeElement('remove');
      $form->enable->setValue($subject->enable_verification);
      // render form of phone
      if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
      }
    }
    if ($otpsmsverification->step == 2) {
      $form = $form = new Otpsms_Form_Phonenumber();
      if (!$this->getRequest()->isPost())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
      // Check data
      if (!$form->isValid($this->getRequest()->getPost()))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
      if (!empty($_POST['remove'])) {
        $subject->phone_number = "";
        $subject->country_code = "";
        $subject->enable_verification = 0;
        $subject->save();
        $form->reset();
        $form->removeElement('remove');
        $form->addNotice("Phone Number removed successfully.");
        $otpsmsverification->unsetAll();
        $otpsmsverification->step = 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('success' => 'Phone Number removed successfully.')));
      }
      $values = $form->getValues();
      //check phone number already exists
      $table = Engine_Api::_()->getDbTable('users', 'user');
      $select = $table->select()->where('phone_number =?', $values['phone_number'])->where('user_id !=?', $this->view->viewer()->getIdentity());
      $res = $table->fetchAll($select);
      if (count($res)) {
        //$form->addError('Phone Number already exists.');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Phone Number already exists.'), 'result' => array()));
      }
      //save values in session

      $otpsmsverification->phone_number = "+" . $values['country_code'] . $values['phone_number'];
      $otpsmsverification->phone_number_code = $values['phone_number'];
      $otpsmsverification->country_code = $values['country_code'];
      $otpsmsverification->enable_verification = $values['enable'];
      $otpsmsverification->step = 3;
    }
    if ($otpsmsverification->step == 3) {
      $form = $form = new Otpsms_Form_Signup_Otpsms();
      // render form of Otp
      $inputcode = $this->_getParam("code");
      if ($this->_getParam('resend')) {
        unset($otpsmsverification->code);
      }
      if (empty($otpsmsverification->code)) {
        //generate code
        $code = Engine_Api::_()->otpsms()->generateCode();
        $otpsmsverification->code = $code;
        $otpsmsverification->creation_time = time();
        //send code to mobile
        if ($subject->phone_number)
          $type = "edit_number_template";
        else
          $type = "add_number_template";
        Engine_Api::_()->otpsms()->sendMessage("+" . $otpsmsverification->country_code . $otpsmsverification->phone_number_code, $code, $type);
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('otpsms_duration' => $expiretime));
      }

      if (empty($_POST['code']))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Please submit OTP.'), 'result' => array()));
      if ($form->isValid($this->getRequest()->getPost())) {
        $code = $otpsmsverification->code;
        $expiretime = Engine_Api::_()->getApi('settings', 'core')->getSetting("otpsms.duration", 600);
        $codeexpirytime = time() - $expiretime;
        if ($code != $inputcode) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The OTP code you entered is invalid. Please enter the correct OTP code.'), 'result' => array()));
          //$form->addError("The OTP code you entered is invalid. Please enter the correct OTP code.");
        } else if ($otpsmsverification->creation_time < $codeexpirytime) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The OTP code you entered has expired. Please click on\'RESEND\' button to get new OTP code.'), 'result' => array()));
          //$form->addError("The OTP code you entered has expired. Please click on'RESEND' button to get new OTP code.");
        } else {
          //save phone number
          $subject->phone_number = $otpsmsverification->phone_number_code;
          $subject->country_code = $otpsmsverification->country_code;
          $subject->enable_verification = $otpsmsverification->enable_verification;
          $subject->save();
          $otpsmsverification->unsetAll();
          //header("Location:".$_SERVER['REQUEST_URI']);
        }
      }
    }
    // Set up require's
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
    $this->_helper->requireAuth()->setAuthParams(
      $subject,
      null,
      'edit'
    );
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('success' => 'Phone Number added successfully.')));
  }

  public function verifyForgotAction()
  {
    $code = $this->_getParam('value', '');
    $user_id = $this->_getParam('user_id', '');
    if (!$code || $user_id) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->translate->view('parameter_missing')));
    }
    $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
    $forgotSelect = $forgotTable->select()
      ->where('user_id = ?', $user_id)
      ->where('code = ?', $code);

    $forgotRow = $forgotTable->fetchRow($forgotSelect);
    if (!$forgotRow || (int) $forgotRow->user_id !== (int) $user_id) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->translate->view('Invalid code')));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('action' => 'reset', 'code' => $code, 'uid' => $user_id)));
  }

  public function facebookAction()
  {
    $_SESSION['fbFirstName'] = $_REQUEST['fbFirstName'];
    $_SESSION['device_uuid'] = $_REQUEST['device_uuid'];
    $_SESSION['fbLastName'] = $_REQUEST['fbLastName'];
    $_SESSION['fbEmail'] = $_REQUEST['fbEmail'];
    $_SESSION['fbPictureURL'] = $_REQUEST['fbPictureURL'];
    $_SESSION['fbToken'] = $fbToken = $_REQUEST['fbToken'];
    $_SESSION['facebook_uid'] = $facebook_uid = !empty($_REQUEST['fbUserId']) ? $_REQUEST['fbUserId'] : $_REQUEST['fbUserId'];
    $code = "";
    $_SESSION["device_token"] = $device_token = $_REQUEST["deviceToken"];
    if (empty($fbToken) || empty($facebook_uid))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
    $facebook = $facebookTable->getApi();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

    // Enabled?
    if (!$facebook || 'none' == $settings->core_facebook_enable)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'facebook_login_not_enabled', 'result' => array()));

    // Attempt to login
    if (!$viewer->getIdentity()) {
      if ($facebook_uid) {
        $user_id = $facebookTable->select()
          ->from($facebookTable, 'user_id')
          ->where('facebook_uid = ?', $facebook_uid)
          ->query()
          ->fetchColumn();
      }
      if (
        $user_id &&
        $viewer = Engine_Api::_()->getItem('user', $user_id)
      ) {
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
        // They do not have an account
        $_SESSION['facebook_signup'] = true;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => "Sesapi_Form_Signup_Account"));
      }
    } else {
      // Check for facebook user
      $facebookInfo = $facebookTable->select()
        ->from($facebookTable)
        ->where('facebook_uid = ?', $facebook_uid)
        ->limit(1)
        ->query()
        ->fetch();

      if (!empty($facebookInfo) && $facebookInfo['user_id'] != $viewer->getIdentity()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
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
          'facebook_uid' => $facebook_uid,
          'access_token' => $fbToken,
          'code' => $code,
          'expires' => 0,
        ));
      } else {
        // Save info to db
        $facebookTable->update(array(
          'facebook_uid' => $facebook_uid,
          'access_token' => $fbToken,
          'code' => $code,
          'expires' => 0,
        ), array(
          'user_id = ?' => $viewer->getIdentity(),
        ));
      }
    }

    if ($viewer->getIdentity() && $viewer->enabled) {
      $result["user_id"] = $viewer->user_id;
      $result["email"] = $viewer->email;
      $result["username"] = $viewer->username;
      $result["displayname"] = $viewer->displayname;
      $result["photo_id"] = $viewer->photo_id;
      $result["status"] = $viewer->status;
      $result["password"] = $viewer->password;
      $result["status_date"] = $viewer->status_date;
      $result["salt"] = $viewer->salt;
      $result["locale"] = $viewer->locale;
      $result["language"] = $viewer->language;
      $result["timezone"] = $viewer->timezone;
      $result["search"] = $viewer->search;
      $result["level_id"] = $viewer->level_id;
      if (!empty($result['photo_id'])) {
        $photo = $this->getBaseUrl(true, $viewer->getPhotoUrl());
        $result['photo_url']  = $photo; //substr($photo, 0, strpos($photo, '?'));
      } else
        $result['photo_url'] = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
      $token = Engine_Api::_()->getApi('oauth', 'sesapi')->generateOauthToken();
      $token->user_id = $result['user_id'];
      $token->save();
      //Register device token
      Engine_Api::_()->getDbTable('users', 'sesapi')->register(array('user_id' => $result['user_id'], 'device_uuid' => $_REQUEST['device_uuid']));
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => $result, 'token' => $token->token));
    } else {
      Engine_Api::_()->user()->setViewer(null);
      Engine_Api::_()->user()->getAuth()->getStorage()->clear();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => "require_confirmation"));
    }
  }

  public function twitterAction()
  {
    // Clear
    if (null !== $this->_getParam('clear')) {
      unset($_SESSION['twitter_lock']);
      unset($_SESSION['twitter_token']);
      unset($_SESSION['twitter_secret']);
      unset($_SESSION['twitter_token2']);
      unset($_SESSION['twitter_secret2']);
    }

    if ($this->_getParam('denied')) {
      $this->view->error = 'Access Denied!';
      return;
    }

    // Setup
    $viewer = Engine_Api::_()->user()->getViewer();
    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
    $twitter = $twitterTable->getApi();
    $twitterOauth = $twitterTable->getOauth();

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

    // Check
    if (!$twitter || !$twitterOauth) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Connect
    try {

      $accountInfo = null;
      if (isset($_SESSION['twitter_token2'], $_SESSION['twitter_secret2'])) {
        // Try to login?
        if (!$viewer->getIdentity()) {
          // Get account info
          try {
            $accountInfo = $twitter->account->verify_credentials();
          } catch (Exception $e) {
            // This usually happens when the application is modified after connecting
            unset($_SESSION['twitter_token']);
            unset($_SESSION['twitter_secret']);
            unset($_SESSION['twitter_token2']);
            unset($_SESSION['twitter_secret2']);
            $twitterTable->clearApi();
            $twitter = $twitterTable->getApi();
            $twitterOauth = $twitterTable->getOauth();
          }
        }
      }

      if (isset($_SESSION['twitter_token2'], $_SESSION['twitter_secret2'])) {
        // Try to login?
        if (!$viewer->getIdentity()) {

          $info = $twitterTable->select()
            ->from($twitterTable)
            ->where('twitter_uid = ?', $accountInfo->id)
            ->query()
            ->fetch();

          if (empty($info)) {
            // They do not have an account
            $_SESSION['twitter_signup'] = true;
            return $this->_helper->redirector->gotoRoute(array(
              //'action' => 'twitter',
            ), 'user_signup', true);
          } else {
            Zend_Auth::getInstance()->getStorage()->write($info['user_id']);
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
          }
        }
        // Success
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      } else if (isset($_SESSION['twitter_token'], $_SESSION['twitter_secret'],
      $_GET['oauth_verifier'])) {
        $twitterOauth->getAccessToken('https://twitter.com/oauth/access_token', $_GET['oauth_verifier']);

        $_SESSION['twitter_token2'] = $twitter_token = $twitterOauth->getToken();
        $_SESSION['twitter_secret2'] = $twitter_secret = $twitterOauth->getTokenSecret();

        // Reload api?
        $twitterTable->clearApi();
        $twitter = $twitterTable->getApi();

        // Get account info
        $accountInfo = $twitter->account->verify_credentials();

        // Save to settings table (if logged in)
        if ($viewer->getIdentity()) {
          $info = $twitterTable->select()
            ->from($twitterTable)
            ->where('user_id = ?', $viewer->getIdentity())
            ->query()
            ->fetch();

          if (!empty($info)) {
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
          return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else { // Otherwise try to login?
          $info = $twitterTable->select()
            ->from($twitterTable)
            ->where('twitter_uid = ?', $accountInfo->id)
            ->query()
            ->fetch();

          if (empty($info)) {
            // They do not have an account
            $_SESSION['twitter_signup'] = true;
            return $this->_helper->redirector->gotoRoute(array(
              //'action' => 'twitter',
            ), 'user_signup', true);
          } else {
            Zend_Auth::getInstance()->getStorage()->write($info['user_id']);

            // Register login
            $viewer = Engine_Api::_()->getItem('user', $info['user_id']);
            $viewer->lastlogin_date = date("Y-m-d H:i:s");

            if ('cli' !== PHP_SAPI) {
              $viewer->lastlogin_ip = $ipExpr;

              Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $info['user_id'],
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'twitter',
              ));
            }

            $viewer->save();

            // Redirect to referer page
            $url = $_SESSION['redirectURL'];
            return $this->_redirect($url, array('prependBase' => false));
          }
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
        $twitterOauth->getRequestToken(
          'https://twitter.com/oauth/request_token',
          'http://' . $_SERVER['HTTP_HOST'] . $this->view->url()
        );

        $_SESSION['twitter_token']  = $twitterOauth->getToken();
        $_SESSION['twitter_secret'] = $twitterOauth->getTokenSecret();

        $url = $twitterOauth->getAuthorizeUrl('http://twitter.com/oauth/authenticate');

        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    } catch (Services_Twitter_Exception $e) {
      if (in_array($e->getCode(), array(500, 502, 503))) {
        $this->view->error = 'Twitter is currently experiencing technical issues, please try again later.';
        return;
      } else {
        throw $e;
      }
    } catch (Exception $e) {
      throw $e;
    }
  }

  static protected function _version3PasswordCrypt($method, $salt, $password)
  {

    // For new methods
    if ($method > 0) {
      if (!empty($salt)) {
        list($salt1, $salt2) = str_split($salt, ceil(strlen($salt) / 2));
        $salty_password = $salt1 . $password . $salt2;
      } else {
        $salty_password = $password;
      }
    }

    // Hash it
    switch ($method) {
        // crypt()
      default:
      case 0:
        $user_password_crypt = crypt($password, '$1$' . str_pad(substr($salt, 0, 8), 8, '0', STR_PAD_LEFT) . '$');
        break;

        // md5()
      case 1:
        $user_password_crypt = md5($salty_password);
        break;

        // sha1()
      case 2:
        $user_password_crypt = sha1($salty_password);
        break;

        // crc32()
      case 3:
        $user_password_crypt = sprintf("%u", crc32($salty_password));
        break;
    }

    return $user_password_crypt;
  }
}
