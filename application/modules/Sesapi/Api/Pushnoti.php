<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Pushnoti.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
// Server file
class Sesapi_Api_Pushnoti extends Core_Api_Abstract
{
  // Sends Push notification for Android users
	public function android($data, $reg_id,$userInfo = array(),$id = 0) {
      $tokens = array();
      foreach($reg_id as $token){
          $tokens[] = $token->device_uuid;
      }
      $url = 'https://fcm.googleapis.com/fcm/send';
      if(!empty($data['description']))
        $description = $data['description'];
      else
        $description = " ";
      $message = array(
          'title' => $data['title'],
          'body' => $description,
          'msgcnt' => 1,
          'vibrate' => 1,
          'sound'=>'default',
          "content_available"=> true
      );
      if(count($userInfo)>0){
          $message = array_merge($message,array('userInfo'=>$userInfo));
      }else{
          $message = array_merge($message,array('userInfo'=>"{}"));
      }
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $API_ACCESS_KEY =  $settings->getSetting('sesandroidapp_server_key', 0);
      if($id)
       $API_ACCESS_KEY =  $settings->getSetting('sesiosapp_server_key', 0);
      if(!$API_ACCESS_KEY){
        return false;
      }
      $headers = array(
        'Authorization:key=' .$API_ACCESS_KEY,
        'Content-Type: application/json'
      );
      foreach($tokens as $array){
        $fields = array(
            'to' => $array,
            'priority' => 10,
            'data' => $message,
            'notification' => $message
        );
        $this->useCurl($url, $headers,json_encode($fields), $id);
      }
      
      return true;
 	}
  // Sends Push notification for iOS users
	public function iOS($data, $deviceToken,$userInfo = array()) {  
	  return $this->android($data, $deviceToken,$userInfo,1);
	}
	// Curl 
	private function useCurl($url, $headers, $fields = null,$id = false) {
    // Open connection
    $ch = curl_init();
    if ($url) {
      // Set the url, number of POST vars, POST data
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($ch,CURLOPT_TIMEOUT,10);
      // Disabling SSL Certificate support temporarly
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      if ($fields)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      // Execute post
      $result = curl_exec($ch);
      // Close connection
      curl_close($ch);
      return $result;
    }
  }
}
?>
