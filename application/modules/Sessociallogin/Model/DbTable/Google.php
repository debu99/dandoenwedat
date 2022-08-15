<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Google.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sessociallogin_Model_DbTable_Google extends Engine_Db_Table
{
  protected $_name = 'user_google';
  protected $_api;

  public static function getGoogleInstance()
  {
    return Engine_Api::_()->getDbtable('google', 'sessociallogin')->getApi();
  }
  public function enable(){
    $settings['google_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientid','');
    $settings['google_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientsecret','');
    $enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.enable',0);
    if( empty($settings['google_client']) || 
        empty($settings['google_secret']) || !$enable) {
      return false;
    }  
    return true;
  }
  public function getApi()
  {
    // Already initialized
    if( null !== $this->_api ) {
      return $this->_api;
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    // Need to initialize
    $settings['google_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientid','');
    $settings['google_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientsecret','');
    $settings['google_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.enable',0);
    if( empty($settings['google_client']) || 
        empty($settings['google_secret']) || empty($settings['google_enable'])) {
      $this->_api = null;
      Zend_Registry::set('google_Api', $this->_api);
      return false;
    }
    
    // Try to log viewer in?
    if (!empty($_SESSION['sessociallogin_google'])) {
      $_SESSION['google_lock'] = true;
      $inst_uid = Engine_Api::_()->getDbtable('google', 'sessociallogin')
          ->fetchRow(array('user_id = ?' => $viewer->getIdentity()));
      if($inst_uid){
       $postBody = 'client_id='.urlencode($settings['google_client'])
              .'&client_secret='.urlencode($settings['google_secret'])
              .'&refresh_token='.urlencode($inst_uid->access_token)
              .'&grant_type=refresh_token';
           $siteURL = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']).Zend_Registry::get('StaticBaseUrl').'sessociallogin/auth/google';
        $curl = curl_init();
        curl_setopt_array( $curl,
                         array( CURLOPT_CUSTOMREQUEST => 'POST'
                               , CURLOPT_URL => 'https://www.googleapis.com/oauth2/v3/token'
                               , CURLOPT_HTTPHEADER => array( 'Content-Type: application/x-www-form-urlencoded'
                                                             , 'Content-Length: '.strlen($postBody)
                                                             , 'User-Agent: HoltstromLifeCounter/0.1 +http://holtstrom.com/michael'
                                                             )
                               , CURLOPT_POSTFIELDS => $postBody                              
                               , CURLOPT_REFERER => $siteURL
                               , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
                               , CURLOPT_TIMEOUT => 12 // max seconds to wait
                               , CURLOPT_FOLLOWLOCATION => 0 // don't follow any Location headers, use only the CURLOPT_URL, this is for security
                               , CURLOPT_FAILONERROR => 0 // do not fail verbosely fi the http_code is an error, this is for security
                               , CURLOPT_SSL_VERIFYPEER => 1 // do verify the SSL of CURLOPT_URL, this is for security
                               , CURLOPT_VERBOSE => 0 // don't output verbosely to stderr, this is for security
                         ) );
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);  
    
        if (strlen($response) < 1)
          return false;
    
        $response = json_decode($response, true); // convert returned objects into associative arrays
        $expires = time() - 60 + (int) ($response['expires_in']);
        if ( empty($response['access_token']) || $expires <= time() )
        { return false; }
        return $response['access_token'];
        // store the updated token/expiry in your db
        // pass our the updated token for use
      }
   }else
     $_SESSION['google_lock']  = '';
   return $this->_api;
 }
   public function isConnected(){
     // Need to initialize
    $settings['google_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientid','');
    $settings['google_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.clientsecret','');
    $settings['google_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.google.enable',0);
    if( empty($settings['google_client']) || empty($settings['google_secret']) || empty($settings['google_enable'])) 
      return false;
     return true;
   }
}
