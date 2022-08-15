<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedbg
 * @package    Sesfeedbg
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedbg_Api_Core extends Core_Api_Abstract {

  protected function returnBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) 
    {
      case 'g':
      $val *= 1024;
      case 'm':
      $val *= 1024;
      case 'k':
      $val *= 1024;
    }
    return $val;
  }

  public function max_file_upload_in_bytes() {
    //select maximum upload size
    $max_upload = $this->returnBytes(ini_get('upload_max_filesize'));
    //select post limit
    $max_post = $this->returnBytes(ini_get('post_max_size'));
    //select memory limit
    $memory_limit = $this->returnBytes(ini_get('memory_limit'));
    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit);
  }

}