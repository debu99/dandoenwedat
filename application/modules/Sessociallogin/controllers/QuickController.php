<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: QuickController.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sessociallogin_QuickController extends Core_Controller_Action_Standard {

    protected $_redirectUrl;

    public function signupAction() {
        if (!empty($_SESSION['google_signup'])) {
            $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.quick.signup', '');
            if (!$quickSignupEnable) {
                return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
            }
            $this->_redirectUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.redirect.user', '3');
            $profileType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.profile.type', '1');
            $memberLebel = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.quick.signup', '');
        } else if (!empty($_SESSION['linkedin_signup'])) {
            $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.quick.signup', '');
            if (!$quickSignupEnable) {
                return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
            }
            $this->_redirectUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.redirect.user', '3');
            $profileType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.profile.type', '1');
            $memberLebel = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.quick.signup', '');
        } else if (!empty($_SESSION['hotmail_signup'])) {
            $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.quick.signup', '');
            if (!$quickSignupEnable) {
                return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
            }
            $this->_redirectUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.redirect.user', '3');
            $profileType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.profile.type', '1');
            $memberLebel = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.quick.signup', '');
        } else if (!empty($_SESSION['facebook_signup'])) {
            $quickSignupEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.quick.signup', '');
            if (!$quickSignupEnable) {
                return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
            }
            $this->_redirectUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.redirect.user', '3');
            $profileType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.profile.type', '1');
            $memberLebel = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.quick.signup', '');
        }
        if (!$memberLebel) {
            $public_level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel();
            $memberLebel = $public_level->level_id;
        }

        $email = $_SESSION['signup_fields']["email"];
        if (!$email)
            return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
        //check email already exists
        $dbQuery = "SELECT user_id FROM engine4_users WHERE email = '" . $email . "'";
        $db = Engine_Db_Table::getDefaultAdapter();
        $user_id = $db->query($dbQuery)->fetchAll();
        //email already registerd
        if (count($user_id)) {
            $user_id = $user_id[0]["user_id"];
            if ($viewer = Engine_Api::_()->getItem('user', $user_id)) {
                $this->loginSuccess($user_id, true);
            }
        }
        $signup = $this->signup($profileType, $memberLebel);
    }

    protected function loginSuccess($user_id, $isLogin = false) {

        $ipObj = new Engine_IP();
        $db = Engine_Db_Table::getDefaultAdapter();
        $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
        Engine_Api::_()->user()->getAuth()->getStorage()->write($user_id);
        $viewer = Engine_Api::_()->getItem('user', $user_id);
        $viewer->approved = 1;
        $viewer->verified = 1;
        $viewer->enabled = 1;
        $viewer->save();
        if (!$viewer->enabled) {
            Engine_Api::_()->user()->setViewer(null);
            Engine_Api::_()->user()->getAuth()->getStorage()->clear();
            $confirmSession = new Zend_Session_Namespace('Signup_Confirm');
            $confirmSession->approved = $viewer->approved;
            $confirmSession->verified = $viewer->verified;
            $confirmSession->enabled = $viewer->enabled;
            return $this->_helper->_redirector->gotoRoute(array('action' => 'confirm'), 'user_signup', true);
        }
        $viewer->save();
        $url = $_SESSION['redirectURL'];
        // Register login
        $viewer->lastlogin_date = date("Y-m-d H:i:s");
        if ('cli' !== PHP_SAPI) {
            $viewer->lastlogin_ip = $ipExpr;
            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $user_id,
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'login',
            ));
        }


        if ($this->_redirectUrl != 3 && !$isLogin) {
            $redirect[0] = "Redirect to Member Home Page";
            $redirect[1] = "Redirect to Member Profile Page";
            $redirect[2] = "Redirect to Member Profile Edit Page";

            if ($this->_redirectUrl == 0) {
                return $this->_helper->redirector->gotoRoute(array(), 'default', true);
                exit();
            } else if ($this->_redirectUrl == 1) {
                $url = $this->view->absoluteUrl($viewer->getHref());
                header("Location:" . $url);
                exit();
            } else if ($this->_redirectUrl == 2) {
                return $this->_helper->redirector->gotoRoute(array('controller' => 'edit','action' => 'profile'), 'user_extended', true);
                //$url = $this->view->absoluteUrl('/members/edit/profile');
                //header("Location:" . $url);
                //exit();
            }
        }
        return $this->_redirect($url, array('prependBase' => false));
    }

    protected function getFieldId($type, $profileType) {
        $dbQuery = "SELECT engine4_user_fields_meta.field_id FROM engine4_user_fields_maps LEFT JOIN   engine4_user_fields_meta ON engine4_user_fields_maps.child_id = engine4_user_fields_meta.field_id  WHERE engine4_user_fields_meta.alias = '" . $type . "' && option_id = " . $profileType;
        $db = Engine_Db_Table::getDefaultAdapter();
        return $db->query($dbQuery)->fetchAll();
    }

    protected function signup($profileType, $memberLebel) {

        $first_name_field_id = $this->getFieldId('first_name', $profileType);
        if (count($first_name_field_id)) {
            $first_name_field_id = $first_name_field_id[0]["field_id"];
        } else {
            $first_name_field_id = "";
        }
        $last_name_field_id = $this->getFieldId('last_name', $profileType);
        if (count($last_name_field_id)) {
            $last_name_field_id = $last_name_field_id[0]["field_id"];
        } else {
            $last_name_field_id = "";
        }

        $signupfields = $_SESSION['signup_fields'];
        $first_name = $signupfields["first_name"];
        $last_name = $signupfields["last_name"];
        $data["displayname"] = $first_name . ' ' . $last_name;
        $data['email'] = $signupfields["email"];
        $photoUrl = $signupfields["photo"];


        $settings = Engine_Api::_()->getApi('settings', 'core');
        $random = ($settings->getSetting('user.signup.random', 0) == 1);
        $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);
        if ($emailadmin) {
            // the signup notification is emailed to the first SuperAdmin by default
            $users_table = Engine_Api::_()->getDbtable('users', 'user');
            $users_select = $users_table->select()
                ->where('level_id = ?', 1)
                ->where('enabled >= ?', 1);
            $super_admin = $users_table->fetchRow($users_select);
        }

        // Add email and code to invite session if available
        $inviteSession = new Zend_Session_Namespace('invite');
        if (isset($data['email'])) {
            $inviteSession->signup_email = $data['email'];
        }
        if (isset($data['code'])) {
            $inviteSession->signup_code = $data['code'];
        }

        //if ($random) {
        $data['password'] = Engine_Api::_()->user()->randomPass(10);
        // }

        if (!empty($data['language'])) {
            $data['locale'] = $data['language'];
        }

        // Create user
        // Note: you must assign this to the registry before calling save or it
        // will not be available to the plugin in the hook
        $this->_registry->user  = $user = Engine_Api::_()->getDbtable('users', 'user')->createRow();
        $data['enabled'] = 1;
        $data['verified'] = 1;
        $user->setFromArray($data);
        $user->save();
        $db = Engine_Db_Table::getDefaultAdapter();
        $user = $viewer = Engine_Api::_()->getItem('user', $user->getIdentity());
        Engine_Api::_()->user()->setViewer($user);
        // Increment signup counter
        Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.creations');

        if ($user->verified && $user->enabled) {
            // Create activity for them
            Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'signup');
            // Set user as logged in if not have to verify email
            Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
        }

        $mailType = null;
        $mailParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $user->email,
            'date' => time(),
            'recipient_title' => $user->getTitle(),
            'recipient_link' => $user->getHref(),
            'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
            'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        );

        // Add password to email if necessary
        if ($random) {
            $mailParams['password'] = $data['password'];
        }

        // Mail stuff
        switch ($settings->getSetting('user.signup.verifyemail', 0)) {
            case 0:
                // only override admin setting if random passwords are being created
                if ($random) {
                    $mailType = 'core_welcome_password';
                }
                if ($emailadmin) {
                    $mailAdminType = 'notify_admin_user_signup';
                    $siteTimezone = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.timezone', 'America/Los_Angeles');
                    $date = new DateTime("now", new DateTimeZone($siteTimezone));
                    $mailAdminParams = array(
                        'host' => $_SERVER['HTTP_HOST'],
                        'email' => $user->email,
                        'date' => $date->format('F j, Y, g:i a'),
                        'recipient_title' => $super_admin->displayname,
                        'object_title' => $user->displayname,
                        'object_link' => $user->getHref(),
                    );
                }
                break;

            case 1:
                // send welcome email
                $mailType = ($random ? 'core_welcome_password' : 'core_welcome');
                if ($emailadmin) {
                    $mailAdminType = 'notify_admin_user_signup';
                    $siteTimezone = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.timezone', 'America/Los_Angeles');
                    $date = new DateTime("now", new DateTimeZone($siteTimezone));
                    $mailAdminParams = array(
                        'host' => $_SERVER['HTTP_HOST'],
                        'email' => $user->email,
                        'date' => $date->format('F j, Y, g:i a'),
                        'recipient_title' => $super_admin->displayname,
                        'object_title' => $user->getTitle(),
                        'object_link' => $user->getHref(),
                    );
                }
                break;

            case 2:
                // verify email before enabling account
                $verify_table = Engine_Api::_()->getDbtable('verify', 'user');
                $verify_row = $verify_table->createRow();
                $verify_row->user_id = $user->getIdentity();
                $verify_row->code = md5($user->email
                    . $user->creation_date
                    . $settings->getSetting('core.secret', 'staticSalt')
                    . (string) rand(1000000, 9999999));
                $verify_row->date = $user->creation_date;
                $verify_row->save();

                $mailType = ($random ? 'core_verification_password' : 'core_verification');

                $mailParams['object_link'] = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
                    'action' => 'verify',
                    'email' => $user->email,
                    'token' =>  base64_encode(time() . ":" . $verify_row->user_id),
                    'verify' => $verify_row->code
                ), 'user_signup', true);

                if ($emailadmin) {
                    $mailAdminType = 'notify_admin_user_signup';

                    $mailAdminParams = array(
                        'host' => $_SERVER['HTTP_HOST'],
                        'email' => $user->email,
                        'date' => date("F j, Y, g:i a"),
                        'recipient_title' => $super_admin->displayname,
                        'object_title' => $user->getTitle(),
                        'object_link' => $user->getHref(),
                    );
                }
                break;

            default:
                // do nothing
                break;
        }

        if (!empty($mailType)) {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem(
                $user, $mailType, $mailParams
            );
        }

        if (!empty($mailAdminType)) {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem(
                $user, $mailAdminType, $mailAdminParams
            );
        }

        // Attempt to connect google
        if (!empty($_SESSION['google_signup'])) {
            try {
                $googleTable = Engine_Api::_()->getDbtable('google', 'sessociallogin');
                if ($googleTable->isConnected()) {
                    $googleTable->insert(array(
                        'user_id' => $user->getIdentity(),
                        'google_uid' => $_SESSION['google_uid'],
                        'access_token' => $_SESSION['access_token'],
                        'code' => $_SESSION['refresh_token'],
                        'expires' => 0,
                    ));
                }
            } catch (Exception $e) {
                // Silence
                if ('development' == APPLICATION_ENV) {
                    echo $e;
                }
            }
            unset($_SESSION['google_signup']);
        }

        // Attempt to connect linkedin
        if (!empty($_SESSION['linkedin_signup'])) {
            try {
                $linkedinTable = Engine_Api::_()->getDbtable('linkedin', 'sessociallogin');
                if ($linkedinTable->isConnected()) {
                    $linkedinTable->insert(array(
                        'user_id' => $user->getIdentity(),
                        'linkedin_uid' => $_SESSION['linkedin_uid'],
                        'access_token' => $_SESSION['linkedin_token'],
                        'code' => $_SESSION['linkedin_secret'],
                        'expires' => 0,
                    ));
                }
            } catch (Exception $e) {
                throw $e;
                // Silence
                if ('development' == APPLICATION_ENV) {
                    echo $e;
                }
            }
            unset($_SESSION['linkedin_signup']);
        }

        // Attempt to connect facebook
        if (!empty($_SESSION['facebook_signup'])) {
            try {
                $facebookTable = Engine_Api::_()->getDbtable('facebook', 'sessociallogin');
                $facebook = $facebookTable->getApi();
                $settings = Engine_Api::_()->getDbtable('settings', 'core');
                if ($facebook) {
                    $facebookTable->insert(array(
                        'user_id' => $user->getIdentity(),
                        'facebook_uid' => $facebook->getUser(),
                        'access_token' => $facebook->getAccessToken(),
                        //'code' => $code,
                        'expires' => 0, // @todo make sure this is correct
                    ));
                }
            } catch (Exception $e) {
                // Silence
                throw $e;
                if ('development' == APPLICATION_ENV) {
                    echo $e;
                }
            }
            unset($_SESSION['facebook_signup']);
        }

        //insert profile fields

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->query("INSERT INTO `engine4_user_fields_search`(`item_id`, `profile_type`, `first_name`, `last_name`) VALUES (" . $user->getIdentity() . "," . $profileType . ",'" . $first_name . "','" . $last_name . "' )");
        $db->query("INSERT INTO `engine4_user_fields_values`(`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES (" . $user->getIdentity() . ",1,0," . $profileType . ",'')");
        if (!empty($first_name_field_id))
            $db->query("INSERT INTO `engine4_user_fields_values`(`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES (" . $user->getIdentity() . "," . $first_name_field_id . ",0,'" . $first_name . "','')");
        if (!empty($last_name_field_id))
            $db->query("INSERT INTO `engine4_user_fields_values`(`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES (" . $user->getIdentity() . "," . $last_name_field_id . ",0,'" . $last_name . "','')");
        $this->_fetchImage($photoUrl, $user);


        // Run post signup hook
        $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserSignupAfter', $viewer);
        $responses = $event->getResponses();
        if( $responses ){
            foreach( $event->getResponses() as $response ) {
                if( is_array($response) ) {
                    // Clear login status
                    if( !empty($response['error']) ) {
                        Engine_Api::_()->user()->setViewer(null);
                        Engine_Api::_()->user()->getAuth()->getStorage()->clear();
                    }
                    // Redirect
                    if( !empty($response['redirect']) ) {
                        return $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
                    }
                }
            }
        }
        // Handle subscriptions
        if( Engine_Api::_()->hasModuleBootstrap('payment') ) {
            // Check for the user's plan
            $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
            if (!$subscriptionsTable->check($user) && $this->allowDefaultPlan($user)) {
                // Handle default payment plan
                $this->activateDefaultPlan($user);
            }
        }

        $this->loginSuccess($user->getIdentity());
    }
    function activateDefaultPlan($user){
        $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
        $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
        $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');

        // Have any gateways or packages been added yet?
        if( $gatewaysTable->getEnabledGatewayCount() <= 0 ||
            $packagesTable->getEnabledNonFreePackageCount() <= 0 ) {
            return false;
        }

        // See if they've had a plan before
        $hasSubscription = (bool) $subscriptionTable->select()
            ->from($subscriptionTable->info("name"), new Zend_Db_Expr('TRUE'))
            ->where('user_id = ?', $user->getIdentity())
            ->limit(1)
            ->query()
            ->fetchColumn();
        if( $hasSubscription ) {
            return false;
        }

        // Get the default package
        $package = $packagesTable->fetchRow(array(
            '`default` = ?' => true,
            'enabled = ?' => true,
            'price <= ?' => 0,
        ));

        if( !$package ) {
            return false;
        }
        // Create the default subscription
        $subscription = $subscriptionTable->createRow();
        $subscription->setFromArray(array(
            'package_id' => $package->package_id,
            'user_id' => $user->getIdentity(),
            'status' => 'active',
            'active' => 1,
            'creation_date' => new Zend_Db_Expr('NOW()'),
        ));
        $subscription->save();
        return $subscription;
    }
    private function allowDefaultPlan($user)
    {
        $mappedProfileTypes = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')
            ->getMappedProfileTypeIds($user->level_id);
        $mappedPackages = Engine_Api::_()->getDbtable('packages', 'payment')->fetchRow(array(
            'level_id = ?' => $user->level_id,
            'enabled = ?' => true,
            'signup = ?' => true,
        ));

        return count($mappedProfileTypes) == 0  || $mappedPackages ||
            !Engine_Api::_()->getDbtable('subscriptions', 'payment')->isSignupSubscriptionEnable();
    }
    protected function _fetchImage($photo_url, $user) {
        if (empty($photo_url))
            return;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $photo_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        $tmpfile = APPLICATION_PATH_TMP . DS . md5($photo_url) . '.jpg';
        @file_put_contents($tmpfile, $data);
        $this->_resizeImages($tmpfile, $user);
    }

    protected function _resizeImages($file, $user) {
        $name = basename($file);
        $path = dirname($file);

        // Resize image (main)
        $iMainPath = $path . '/m_' . $name;
        $image = Engine_Image::factory();
        $image->open($file)
            ->autoRotate()
            ->resize(720, 720)
            ->write($iMainPath)
            ->destroy();

        // Resize image (profile)
        $iProfilePath = $path . '/p_' . $name;
        $image = Engine_Image::factory();
        $image->open($file)
            ->autoRotate()
            ->resize(320, 640)
            ->write($iProfilePath)
            ->destroy();

        // Resize image (icon.normal)
        $iNormalPath = $path . '/n_' . $name;
        $image = Engine_Image::factory();
        $image->open($file)
            ->autoRotate()
            ->resize(140, 160)
            ->write($iNormalPath)
            ->destroy();

        // Resize image (icon.square)
        $iSquarePath = $path . '/s_' . $name;
        $image = Engine_Image::factory();
        $image->open($file)
            ->autoRotate();
        $size = min($image->height, $image->width);
        $x = ($image->width - $size) / 2;
        $y = ($image->height - $size) / 2;
        $image->resample($x, $y, $size, $size, 48, 48)
            ->write($iSquarePath)
            ->destroy();

        // Cloud compatibility, put into storage system as temporary files
        $storage = Engine_Api::_()->getItemTable('storage_file');

        // Save/load from session
        // Save
        $iMain = $storage->createTemporaryFile($iMainPath);
        $iProfile = $storage->createTemporaryFile($iProfilePath);
        $iNormal = $storage->createTemporaryFile($iNormalPath);
        $iSquare = $storage->createTemporaryFile($iSquarePath);

        $iMain->bridge($iProfile, 'thumb.profile');
        $iMain->bridge($iNormal, 'thumb.normal');
        $iMain->bridge($iSquare, 'thumb.icon');

        $user->photo_id = $iMain->file_id;
        $user->save();

        // Remove temp files
        @unlink($path . '/p_' . $name);
        @unlink($path . '/m_' . $name);
        @unlink($path . '/n_' . $name);
        @unlink($path . '/s_' . $name);
        return true;
    }

}
