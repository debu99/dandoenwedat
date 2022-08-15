<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Linkedin.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

require_once(APPLICATION_PATH.'/application/modules/Sesadvancedactivity/Api/Linkedin/LinkedIn.php');
class Sesadvancedactivity_Model_DbTable_Linkedin extends Engine_Db_Table
{
  protected $_name = 'user_linkedin';
  protected $_api;

  public static function getLIInstance()
  {
    return Engine_Api::_()->getDbtable('likedin', 'sesadvancedactivity')->getApi();
  }

  public function getApi()
  {
    // Already initialized
    if( null !== $this->_api ) {
      return $this->_api;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    // Need to initialize
    $settings['linkedin_access'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.access','');
    $settings['linkedin_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.secret','');
    $settings['linkedin_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.enable',false);
    if( empty($settings['linkedin_access']) ||
        empty($settings['linkedin_secret']) ||
        empty($settings['linkedin_enable']) ) {
      $this->_api = null;
      Zend_Registry::set('Linkedin_Api', $this->_api);
      return false;
    }
    
    $this->_api = new LinkedIn(array('appKey'=>$settings['linkedin_access'],'appSecret'=>$settings['linkedin_secret']));
    Zend_Registry::set('Linkedin_Api', $this->_api);

    // Try to log viewer in?
    if (!empty($_SESSION[linkedin_uid])) {
      $_SESSION['linkedin_lock'] = true;
      $lin_uid = Engine_Api::_()->getDbtable('linkedin', 'sesadvancedactivity')
          ->fetchRow(array('user_id = ?' => $viewer->getIdentity()));
      $_SESSION['linkedin_uid'] = $lin_uid['linkedin_uid'];
      $_SESSION['linkedin_secret'] = $lin_uid['code'];
      $_SESSION['linkedin_token'] = $lin_uid['access_token'];
      $this->_api->setTokenAccess($_SESSION['linkedin_access']);
      
   }else
     $_SESSION['linkedin_lock']  = '';
   
   return $this->_api;
 }
   public function isConnected(){
     // Need to initialize
    $settings['linkedin_access'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.access','');
    $settings['linkedin_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.secret','');
    $settings['linkedin_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.linkedin.enable','0');
    if(!$settings['linkedin_access'] || !$settings['linkedin_secret'] || !$settings['linkedin_enable']  )
      return false;
     return true;
   }
  /**
   * Generates the button used for Linkedin Connect
   *
   * @param mixed $fb_params A string or array of Linkedin parameters for login
   * @param string $connect_with_Linkedin The string to display inside the button
   * @return String Generates HTML code for Linkedin login button
   */
  public static function loginButton($connect_text = 'Connect with Linkedin')
  {
     return Zend_Controller_Front::getInstance()->getRouter()
        ->assemble(array('module' => 'sesadvancedactivity', 'controller' => 'auth',
          'action' => 'linkedin'), 'default', true); 
  }
}
