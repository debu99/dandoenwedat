<?php

require_once(APPLICATION_PATH.'/application/modules/Sesbasic/Api/Instagram/Instagram.php');

class Sesbasic_Model_DbTable_Instagram extends Engine_Db_Table {

  protected $_name = 'sesbasic_instagram';
  protected $_api;
  
  public function enable($moduleName) {
  
    $settings['instagram_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.instagram.clientid','');
    $settings['instagram_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.instagram.clientsecret','');
    $enable = Engine_Api::_()->getApi('settings', 'core')->getSetting($moduleName.'.instagram.enable', 0);
    if(empty($settings['instagram_client']) || empty($settings['instagram_secret']) || !$enable) {
      return false;
    }
    return true;
  }
  
  public static function getInInstance() {
  
    return Engine_Api::_()->getDbtable('likedin', 'sesbasic')->getApi();
  }

  public function getApi($auth = false) {
  
    // Already initialized
    if( null !== $this->_api ) {
      return $this->_api;
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Need to initialize
    $settings['instagram_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.instagram.clientid','');
    $settings['instagram_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.instagram.clientsecret','');
    
    if( empty($settings['instagram_client']) || empty($settings['instagram_secret']) ) {
      $this->_api = null;
      Zend_Registry::set('Instagram_Api', $this->_api);
      return false;
    }
    
    $this->_api = new Instagram(array(
      'apiKey'      => $settings['instagram_client'],
      'apiSecret'   => $settings['instagram_secret'],
      'apiCallback' => (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']).Zend_Registry::get('StaticBaseUrl').'sesbasic/auth/instagram'
    ));
    Zend_Registry::set('Instagram_Api', $this->_api);
    
    if($auth)
      return $this->_api;
      
    // Try to log viewer in?
    if (!empty($_SESSION['sesbasic_instagram'])) {
      $_SESSION['instagram_lock'] = true;
      $inst_uid = Engine_Api::_()->getDbtable('instagram', 'sesbasic')->fetchRow(array('user_id = ?' => $viewer->getIdentity()));
      if($inst_uid) {
        $this->_api->setAccessToken($inst_uid->access_token); 
        $user = $this->_api->getUser();
        if(empty($user->data->username))
          return false;
      }
    } else
     $_SESSION['instagram_lock']  = '';
   
   return $this->_api;
  }
 
  public function isConnected() {

    //Need to initialize
    $settings['instagram_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.instagram.clientid','');
    $settings['instagram_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.instagram.clientsecret','');
    
    if(empty($settings['instagram_client']) || empty($settings['instagram_secret'])) 
      return false;

    return true;
  }
  
  public static function loginButton() {
    return Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesbasic', 'controller' => 'auth', 'action' => 'instagram'), 'default', true); 
  }
}
