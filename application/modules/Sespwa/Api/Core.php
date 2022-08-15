<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Api_Core extends Core_Api_Abstract {

  public function getFileUrl($image) {
    
    $table = Engine_Api::_()->getDbTable('files', 'core');
    $result = $table->select()
                ->from($table->info('name'), 'storage_file_id')
                ->where('storage_path =?', $image)
                ->query()
                ->fetchColumn();
    if(!empty($result)) {
      $storage = Engine_Api::_()->getItem('storage_file', $result);
      return $storage->map();
    } else {
      return $image;
    }
  }
  
  public function setPhoto($photo, $menuId = null) {

    //GET PHOTO DETAILS
    $mainName = dirname($photo['tmp_name']) . '/' . $photo['name'];

    //GET VIEWER ID
    $photo_params = array(
        'parent_id' => $menuId,
        'parent_type' => "sespwa_pwa",
    );
    copy($photo['tmp_name'], $mainName);
    try {
      $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
    } catch (Exception $e) {
      if ($e->getCode() == Storage_Api_Storage::SPACE_LIMIT_REACHED_CODE) {
        echo $e->getMessage();
        exit();
      }
    }

    return $photoFile;
  }

  public function getMenuIcon($menuName) {

    $table = Engine_Api::_()->getDbTable('menuitems', 'core');
    $menuId =  $table->select()
                    ->from($table, 'id')
                    ->where('name =?', $menuName)
                    ->query()
                    ->fetchColumn();
    if($menuId){
      $row = Engine_Api::_()->getDbTable('menusicons','sesbasic')->getRow($menuId);
    if($row)
      return $row->sespwa_icon_id;
    }
   return false;
  }

  public function isMobile() {

    // No UA defined?
    if( !isset($_SERVER['HTTP_USER_AGENT']) ) {
      return false;
    }

    // Windows is (generally) not a mobile OS
    if( false !== stripos($_SERVER['HTTP_USER_AGENT'], 'windows') &&
        false === stripos($_SERVER['HTTP_USER_AGENT'], 'windows phone os')) {
      return false;
    }

    // Sends a WAP profile header
    if( isset($_SERVER['HTTP_PROFILE']) ||
        isset($_SERVER['HTTP_X_WAP_PROFILE']) ) {
      return true;
    }

    // Accepts WAP as a valid type
    if( isset($_SERVER['HTTP_ACCEPT']) &&
        false !== stripos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') ) {
      return true;
    }

    // Is Opera Mini
    if( isset($_SERVER['ALL_HTTP']) &&
        false !== stripos($_SERVER['ALL_HTTP'], 'OperaMini') ) {
      return true;
    }

    if( preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', $_SERVER['HTTP_USER_AGENT']) ) {
      return true;
    }

    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
    $mobile_agents = array(
      'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird',
      'blac', 'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric',
      'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c',
      'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi',
      'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki', 'oper', 'palm', 'pana',
      'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams', 'sany',
      'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal',
      'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'tsm-',
      'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr',
      'webc', 'winw', 'winw', 'xda ', 'xda-'
    );

    if( in_array($mobile_ua, $mobile_agents) ) {
      return true;
    }

    return false;
  }
}
