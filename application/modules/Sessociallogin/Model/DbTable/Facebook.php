<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Facebook.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sessociallogin_Model_DbTable_Facebook extends User_Model_DbTable_Facebook
{
  protected $_api;
  protected $_name = "user_facebook";
  public static function getFBInstance()
  {
    return Engine_Api::_()->getDbtable('facebook', 'sessociallogin')->getApi();
  }

  public function getApi()
  {
    // Already initialized
    if( null !== $this->_api ) {
      return $this->_api;
    }
    $settings['appid'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.clientid', '');
    $settings['secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.clientsecret', '');
    $settings['enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.enable', '0');
    
    // Need to initialize
    if( empty($settings['secret']) ||
        empty($settings['appid']) ||
        empty($settings['enable']) ||
        $settings['enable'] == 'none' ) {
      $this->_api = null;
      Zend_Registry::set('Facebook_Api', $this->_api);
      return false;
    }

    $this->_api = new Facebook_Api(array(
      'appId'  => $settings['appid'],
      'secret' => $settings['secret'],
      'cookie' => false, // @todo make sure this works
      'baseDomain' => $_SERVER['HTTP_HOST'],
    ));
    Zend_Registry::set('Facebook_Api', $this->_api);

    // Try to log viewer in?
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !isset($_SESSION['facebook_uid']) ||
        @$_SESSION['facebook_lock'] !== $viewer->getIdentity() ) {
      $_SESSION['facebook_lock'] = $viewer->getIdentity();
      if( $this->_api->getUser() ) {
        $_SESSION['facebook_uid'] = $this->_api->getUser();
      } else if( $viewer && $viewer->getIdentity() ) {
        // Try to get from db
        $info = $this->select()
            ->from($this)
            ->where('user_id = ?', $viewer->getIdentity())
            ->query()
            ->fetch();
        if( is_array($info) && !empty($info['facebook_uid']) &&
            !empty($info['access_token']) && !empty($info['code']) ) {
          $_SESSION['facebook_uid'] = $info['facebook_uid'];
          $this->_api->setPersistentData('code', $info['code']);
          $this->_api->setPersistentData('access_token', $info['access_token']);
        } else {
          // Could not get
          $_SESSION['facebook_uid'] = false;
        }
      } else {
        // Could not get
        //$_SESSION['facebook_uid'] = false;
      }
    }
    
    return $this->_api;
  }

  public function isConnected()
  {
    if( ($api = $this->getApi()) ) {
      return (bool) $api->getUser();
    } else {
      return false;
    }
  }

  public function checkConnection(User_Model_User $user = null)
  {
    if( null === $user ) {
      $user = Engine_Api::_()->user()->getViewer();
    }
    try {
      $this->getApi()->api('/me');
      $fb_uid = Engine_Api::_()->getDbtable('facebook', 'user')
          ->fetchRow(array('user_id = ?' => $user->getIdentity()));
    } catch( Exception $e ) {
      return false;
    }
    
    if( !$fb_uid || !$fb_uid->facebook_uid || $fb_uid->facebook_uid != $this->getApi()->getUser() ) {
      return false;
    } else {
      return true;
    }
  }
}
