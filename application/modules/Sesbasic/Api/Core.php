<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Api_Core extends Core_Api_Abstract {

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
  
  public function number_format_short( $n, $precision = 1 ) {
    if ($n < 900) {
      // 0 - 900
      $n_format = number_format($n, $precision);
      $suffix = '';
    } else if ($n < 900000) {
      // 0.9k-850k
      $n_format = number_format($n / 1000, $precision);
      $suffix = 'K';
    } else if ($n < 900000000) {
      // 0.9m-850m
      $n_format = number_format($n / 1000000, $precision);
      $suffix = 'M';
    } else if ($n < 900000000000) {
      // 0.9b-850b
      $n_format = number_format($n / 1000000000, $precision);
      $suffix = 'B';
    } else {
      // 0.9t+
      $n_format = number_format($n / 1000000000000, $precision);
      $suffix = 'T';
    }
    // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ( $precision > 0 ) {
      $dotzero = '.' . str_repeat( '0', $precision );
      $n_format = str_replace( $dotzero, '', $n_format );
    }
    return $n_format . $suffix;
  }

  public function isSkuExists($modulename){
    $valid = false;
    foreach( Zend_Registry::get('Engine_Manifest') as $key=>$data ) {
       if($key == $modulename){
         $package = $data['package'];
         if(!empty($package['sku'])){
            $valid = "aSKDJHaksjdhkjhaD128973WHQDK";
         }
       }
    }
    return $valid;
  }
    function get_string_between($string){
        $regex = "/\[(.*?)\]/";
        preg_match_all($regex, $string, $matches);
        return $matches[1];
    }
  public function isModuleEnable($name = '') {
    $moduleTable = Engine_Api::_()->getDbtable('modules', 'core');
    return $moduleTable->select()->from($moduleTable->info('name'), new Zend_Db_Expr('COUNT(*)'))->where('name In (?)', $name)->where('enabled =?', 1)->query()->fetchColumn();
  }

  public function checkSesPaymentExtentionsEnable() {
    $moduleTable = Engine_Api::_()->getDbtable('modules', 'core');
    return $moduleTable->select()->from($moduleTable->info('name'), new Zend_Db_Expr('COUNT(*)'))->where('name In ("seseventticket","sesadvancedactivity", "sesvideosell", "sescrowdfunding")')->where('enabled =?', 1)->query()->fetchColumn();
  }

  public function dateFormat($date = null, $changetimezone = '', $object = '', $formate = 'M d, Y h:m A') {

    if ($changetimezone != '' && $date) {
      $date = strtotime($date);
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($object->timezone);
      if ($formate == '')
        $dateChange = date('Y-m-d h:i:s', $date);
      else {
        $dateChange = date('M d, Y h:i A', $date);
      }
      date_default_timezone_set($oldTz);
      return $dateChange . ' (' . $object->timezone . ')';
    }
    if ($date) {
      return date('M d, Y h:i A', strtotime($date));
    }
  }
  public function checkAdultContent($params = array()) {
    $viewer = Engine_Api::_()->user()->getViewer();

    $enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.allow.adult.filtering', 0);
    if (!$enable)
      return 1;
    $viewer_id = $viewer->getIdentity();
    if ($viewer_id == 0) {
      return isset($_COOKIE['ses_adult_filter']) ? $_COOKIE['ses_adult_filter'] : 1;
    } else {
      return Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.allow.adult.content.' . $viewer_id, 1);
    }
  }

  public function pluginVersion($name = null) {
    $moduleTable = Engine_Api::_()->getDbtable('modules', 'core');
    return $moduleTable->select()->from($moduleTable->info('name'), array('version'))->where('name =?', $name)->where('enabled =?', 1)->query()->fetchColumn();
  }

  //get location based cookie data
  function getUserLocationBasedCookieData() {
    $locationVal = $lat = $lng = '';
    if (isset($_COOKIE['sesbasic_location_data']) && isset($_COOKIE['sesbasic_location_lat']) && isset($_COOKIE['sesbasic_location_lng'])) {
      $locationVal = $_COOKIE['sesbasic_location_data'];
      $lat = $_COOKIE['sesbasic_location_lat'];
      $lng = $_COOKIE['sesbasic_location_lng'];
    }
    return array('location' => $locationVal, 'lat' => $lat, 'lng' => $lng);
  }

  //get next previous item for other module.
  public function SesNextPreviousPhoto($photo_item, $condition, $resourcePhoto, $child_id, $parent_id, $allPhoto = false) {
    $GetTableNamePhotoMain = Engine_Api::_()->getItemTable($resourcePhoto);
    $tableNamePhotoMain = $GetTableNamePhotoMain->info('name');
    $select = $GetTableNamePhotoMain->select()
            ->from($tableNamePhotoMain);
    if (!$allPhoto) {
      $select->where("$tableNamePhotoMain.$child_id $condition  ?", $photo_item->$child_id)->limit(1);
      ;
    }
    $select->where("$tableNamePhotoMain.$parent_id =  ?", $photo_item->$parent_id);
    if ($allPhoto) {
      $select->order("$tableNamePhotoMain.$child_id ASC");
      return Zend_Paginator::factory($select);
    }
    if ($condition == '<')
      $select->order($tableNamePhotoMain . ".$child_id DESC");
    return $GetTableNamePhotoMain->fetchRow($select);
  }

  public function pluginInstalled($name = null) {
    $moduleTable = Engine_Api::_()->getDbtable('modules', 'core');
    return $moduleTable->select()->from($moduleTable->info('name'), array('name'))->where('name =?', $name)->query()->fetchColumn();
  }

  public function textTruncation($text, $textLength = null) {
    $text = strip_tags($text);
    return ( Engine_String::strlen($text) > $textLength ? Engine_String::substr($text, 0, $textLength) . '...' : $text);
  }

  public function pageTabIdOnPage($widgetName, $pageName, $type = 'widget') {
    $contentTable = Engine_Api::_()->getDbtable('content', 'core');
    $contentTableName = $contentTable->info('name');
    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
    $pageTableName = $pageTable->info('name');
    $select = $contentTable->select()
            ->setIntegrityCheck(false)
            ->from($contentTableName)
            ->join($pageTableName, $pageTableName . ".page_id = ." . $contentTableName . ".page_id  ", null)
            ->where($pageTableName . '.name = ?', $pageName)
            ->where($contentTableName . '.name = ?', $widgetName)
            ->where($contentTableName . '.type = ?', $type);
    return $contentTable->fetchRow($select);
  }

  public function isWidgetEnable($type = 'widget', $name = '') {
    $widgetTable = Engine_Api::_()->getDbTable('content', 'core');
    return $widgetTable->select()
                    ->from($widgetTable, 'content_id')
                    ->where($widgetTable->info('name') . '.type = ?', $type)
                    ->where($widgetTable->info('name') . '.name = ?', $name)
                    ->query()
                    ->fetchColumn();
  }

  public function getUserFnameLname($user_id = null) {
    //if no user id given take logged in user details
    $returnRes = array();
    if (!$user_id) {
      $user = Engine_Api::_()->user()->getViewer();
      if ($user->getIdentity() != 0)
        $user_id = $user->getIdentity();
      else
        return $returnRes;
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $result = $db->query("SELECT  mv.value,mf.type FROM engine4_user_fields_values as mv LEFT JOIN engine4_user_fields_meta as mf ON (mf.field_id = mv.field_id) WHERE mv.item_id = " . $user_id . " && (mf.type = 'first_name' || mf.type = 'last_name')")->fetchAll();
    if (count($result)) {
      foreach ($result as $val) {
        if (isset($val['type']) && $val['type'] == 'first_name')
          $returnRes['first_name'] = $val['value'];
        else
          $returnRes['last_name'] = $val['value'];
      }
    }
    return $returnRes;
  }

  public function getWidgetTabId($params = array()) {
    $table = Engine_Api::_()->getDbTable('content', 'core');
    return $table->select()
                    ->from($table, 'content_id')
                    ->where('name =?', $params['name'])
                    ->query()
                    ->fetchColumn();
  }

  // get photo like status
  public function getLikeStatus($resource_id = '', $resource_type = '') {
    if ($resource_id != '') {
      $userId = Engine_Api::_()->user()->getViewer()->getIdentity();
      if ($userId == 0)
        return false;
      $coreLikeTable = Engine_Api::_()->getDbtable('likes', 'core');
      $total_likes = $coreLikeTable->select()->from($coreLikeTable->info('name'), new Zend_Db_Expr('COUNT(like_id) as like_count'))->where('resource_type =?', $resource_type)->where('poster_id =?', $userId)->where('poster_type =?', 'user')->where('	resource_id =?', $resource_id)->limit(1)->query()->fetchColumn();
      if ($total_likes > 0) {
        return true;
      } else {
        return false;
      }
    }
    return false;
  }

  public function getwidgetizePage($params = array()) {

    $corePages = Engine_Api::_()->getDbtable('pages', 'core');
    $corePagesName = $corePages->info('name');
    $select = $corePages->select()
            ->from($corePagesName, array('*'))
            ->where('name = ?', $params['name'])
            ->limit(1);
    return $corePages->fetchRow($select);
  }

  public function getwidgetizePageName($params = array()) {

    $corePages = Engine_Api::_()->getDbtable('pages', 'core');
    $corePagesName = $corePages->info('name');
    return $corePages->select()
                    ->from($corePagesName, array('title'))
                    ->where('page_id = ?', $params['page_id'])
                    ->limit(1)->query()->fetchColumn();
  }

  public function totalSiteMembersCount() {

    $table = Engine_Api::_()->getDbtable('users', 'user');
    $info = $table->select()
            ->from($table, array('COUNT(*) AS count'))
            ->where('enabled = ?', true)
            ->query()
            ->fetch();
    return $info['count'];
  }

  //Change System Mode of Site
  public function changeEnvironmentMode($system_mode) {

    if ($system_mode == 1) {
      $global_settings_file = APPLICATION_PATH . '/application/settings/general.php';
      if (file_exists($global_settings_file)) {
        $g = include $global_settings_file;
        if (!is_array($g)) {
          $g = (array) $g;
        }
      } else {
        $g = array();
      }
      if (!is_writable($global_settings_file)) {
        if (!is_writable($global_settings_file)) {
          $this->view->success = false;
          $this->view->error = 'Unable to write to settings file; please CHMOD 666 the file /application/settings/general.php, then try again.';
          return;
        } else {
          // it worked; continue.
        }
      }

      if ($system_mode == 1) {
        $g['environment_mode'] = 'development';
        $file_contents = "<?php defined('_ENGINE') or die('Access Denied'); return ";
        $file_contents .= var_export($g, true);
        $file_contents .= "; ?>";
        $this->view->success = @file_put_contents($global_settings_file, $file_contents);
        // clear scaffold cache
        Core_Model_DbTable_Themes::clearScaffoldCache();
        // Increment site counter
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $settings->core_site_counter = $settings->core_site_counter + 1;
        return;
      }
    }
  }

  public function isModuleExist($key) {
    $db = Engine_Db_Table::getDefaultAdapter();
    return $db->query("SELECT * FROM  `engine4_core_modules` WHERE  `name` LIKE  '".$key."'")->fetch();
  }

  public function checkPluginVersion($moduleName, $sesbasic_currentversion) {
    $db = Engine_Db_Table::getDefaultAdapter();
    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_modules')
            ->where('name = ?', $moduleName);
    $results = $select->query()->fetchObject();
    $sesbasic_enabled = $results->version;
    $sesbasicSiteversion = @explode('p', $sesbasic_currentversion);
    $sesbasiCurrentversionE = @explode('p', $sesbasic_enabled);
    if (isset($sesbasiCurrentversionE[0]))
      $sesbasiCurrentVersion = @explode('.', $sesbasiCurrentversionE[0]);
    if (isset($sesbasiCurrentversionE[1]))
      $sesbasiCurrentVersionP = $sesbasiCurrentversionE[1];
    $finalVersion = 1;
    $versionB = false;
    foreach ($sesbasicSiteversion as $versionSite) {
      $sesVersion = explode('.', $versionSite);
      if (count($sesVersion) > 1) {
        $counterV = 0;
        foreach ($sesVersion as $key => $version) {
          if (isset($sesbasiCurrentVersion[$key]) && $version < $sesbasiCurrentVersion[$key]) {
            $versionB = true;
            $finalVersion = 1;
            break;
          }
          if (isset($sesbasiCurrentVersion[$key]) && $version > $sesbasiCurrentVersion[$key] && $version != $sesbasiCurrentVersion[$key]) {
            $finalVersion = 0;
            break;
          }
          $counterV++;
        }
      } else {
        //string after p
        if (isset($sesbasiCurrentVersionP)) {
          if ($versionSite > $sesbasiCurrentVersionP && $versionSite != $sesbasiCurrentVersionP) {
            $finalVersion = 0;
            break;
          }
        } else {
          $finalVersion = 0;
          break;
        }
      }
      //check if final result is false exit
      if (!$finalVersion || $versionB)
        break;
    }
    return $finalVersion;
  }

  //upload photo with watermark
  //watermark on photo
  function watermark_image($oldimage_name, $new_image_name, $type, $image_path, $modulename) {
    ini_set('memory_limit', '1024M');
    list($owidth, $oheight) = getimagesize($oldimage_name);
    $width = $sourcefile_width = $owidth;
    $height = $sourcefile_height = $oheight;
    $im = imagecreatetruecolor($width, $height);
    if (strpos(strtolower($type), 'png') !== FALSE)
      $image_type = 'png';
    else if (strpos(strtolower($type), 'jpg') !== FALSE || strpos(strtolower($type), 'jpeg') !== FALSE)
      $image_type = 'jpeg';
    else if (strpos(strtolower($type), 'gif') !== FALSE)
      $image_type = 'gif';
    switch ($image_type) {
      case 'gif': $img_src = imagecreatefromgif($oldimage_name);
        break;
      case 'jpeg': $img_src = imagecreatefromjpeg($oldimage_name);
        break;
      case 'png': $img_src = imagecreatefrompng($oldimage_name);
        break;
      default: return false;
        break;
    }
    imagecopyresampled($im, $img_src, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
    $watermark = imagecreatefrompng($image_path);
    list($insertfile_width, $insertfile_height) = getimagesize($image_path);

    $pos = Engine_Api::_()->getApi('settings', 'core')->getSetting($modulename . '.position.watermark', 0);
    //middle
    if ($pos == 0) {
      $dest_x = ( $sourcefile_width / 2 ) - ( $insertfile_width / 2 );
      $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
    }
    //top left
    else if ($pos == 1) {
      $dest_x = 0;
      $dest_y = 0;
    }
//top right
    else if ($pos == 2) {
      $dest_x = $sourcefile_width - $insertfile_width;
      $dest_y = 0;
    }

//bottom right
    else if ($pos == 3) {
      $dest_x = $sourcefile_width - $insertfile_width;
      $dest_y = $sourcefile_height - $insertfile_height;
    }

//bottom left
    else if ($pos == 4) {
      $dest_x = 0;
      $dest_y = $sourcefile_height - $insertfile_height;
    }

//top middle
    else if ($pos == 5) {
      $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
      $dest_y = 0;
    }

//middle right
    else if ($pos == 6) {
      $dest_x = $sourcefile_width - $insertfile_width;
      $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
    }

//bottom middle
    else if ($pos == 7) {
      $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
      $dest_y = $sourcefile_height - $insertfile_height;
    }

//middle left
    else if ($pos == 8) {
      $dest_x = 0;
      $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
    }


    imagecopy($im, $watermark, $dest_x, $dest_y, 0, 0, $insertfile_width, $insertfile_height);
    imagejpeg($im, $new_image_name, 100);
    imagedestroy($im);
    @unlink($oldimage_name);
    return true;
  }

  //upload photo
  public function setPhoto($photo, $isURL = false, $isUploadDirect = false, $modulename, $memberlevelType, $photoParams = array(), $item, $package = false, $sameThumbWatermark = false,$watermarkLabel = 'watermark') {
    if (!$isURL) {
      if ($photo instanceof Zend_Form_Element_File) {
        $file = $photo->getFileName();
        $fileName = $file;
      } else if ($photo instanceof Storage_Model_File) {
        $file = $photo->temporary();
        $fileName = $photo->name;
      } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
        $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        $file = $tmpRow->temporary();
        $fileName = $tmpRow->name;
      } else if (is_array($photo) && !empty($photo['tmp_name'])) {
        $file = $photo['tmp_name'];
        $fileName = $photo['name'];
      } else if (is_string($photo) && file_exists($photo)) {
        $file = $photo;
        $fileName = $photo;
      } else {
        throw new User_Model_Exception('invalid argument passed to setPhoto');
      }
      $name = basename($file);
      $extension = ltrim(strrchr($fileName, '.'), '.');
      $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    } else {
      $fileName = time() . '_' . $modulename;
      $PhotoExtension = '.' . pathinfo($photo, PATHINFO_EXTENSION);
      $filenameInsert = $fileName . $PhotoExtension;
      $copySuccess = @copy($photo, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/' . $filenameInsert);
      if ($copySuccess)
        $file = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . $filenameInsert;
      else
        return false;
      $name = basename($photo);
      $extension = ltrim(strrchr($name, '.'), '.');
      $base = rtrim(substr(basename($name), 0, strrpos(basename($name), '.')), '.');
    }
    if (!$fileName) {
      $fileName = $file;
    }
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => $item->getType(),
        'parent_id' => $item->getIdentity(),
        'name' => $fileName,
    );
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    /* setting of image dimentions from core settings */
    $core_settings = Engine_Api::_()->getApi('settings', 'core');
    $main_height = $core_settings->getSetting($modulename . '.mainheight', 1600);
    $main_width = $core_settings->getSetting($modulename . '.mainwidth', 1600);
    $normal_height = $core_settings->getSetting($modulename . '.normalheight', 500);
    $normal_width = $core_settings->getSetting($modulename . '.normalwidth', 500);
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize($main_width, $main_height)
            ->write($mainPath)
            ->destroy();
    // Resize image (normal) make same image for activity feed so it open in pop up with out jump effect.
    $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize($normal_width, $normal_height)
            ->write($normalPath)
            ->destroy();
    //watermark on main photo
    if (!$isUploadDirect) {
      $enableWatermark = $core_settings->getSetting($modulename . '.watermark.enable', 0);
      if ($enableWatermark == 1) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $watermarkImage = Engine_Api::_()->authorization()->getPermission($viewer->level_id, $memberlevelType, $watermarkLabel);
        if (is_file($watermarkImage)) {
          if (isset($extension))
            $type = $extension;
          else
            $type = $PhotoExtension;
          $mainFileUploaded = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . $name;
          $fileName = current(explode('/', $name));
          $fileName = explode('.', $fileName);
          if (isset($fileName[0]))
            $name = $fileName[0];
          else
            $name = time();
          $fileNew = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . time() . '_' . $name . ".jpg";
          $watemarkImageResult = $this->watermark_image($mainPath, $fileNew, $type, $watermarkImage, $modulename);
          if ($watemarkImageResult) {
            @unlink($mainPath);
            $image->open($fileNew)
                    ->resize($main_width, $main_height)
                    ->write($mainPath)
                    ->destroy();
            @unlink($fileNew);
          }
          $watermarkImageNew = Engine_Api::_()->authorization()->getPermission($viewer->level_id, $memberlevelType, 'watermarkthumb');
          if($sameThumbWatermark)
            $watermarkImageNew = $watermarkImage;
          if (!is_file($watermarkImageNew)) {
            $fileNew = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . time() . '_' . $fileName . ".jpg";
            $watemarkImageResult = $this->watermark_image($normalPath, $fileNew, $type, $watermarkImage, $modulename);
            if ($watemarkImageResult) {
              @unlink($normalPath);
              $image->open($fileNew)
                      ->resize($main_width, $main_height)
                      ->write($normalPath)
                      ->destroy();
              @unlink($fileNew);
            }
          }
        }
      }
    }

    //thumb photo watermark
    if ($enableWatermark == 1) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $watermarkImage = Engine_Api::_()->authorization()->getPermission($viewer->level_id, $memberlevelType, 'watermarkthumb');
      if($sameThumbWatermark)
            $watermarkImageNew = $watermarkImage;
      if (is_file($watermarkImage)) {
        if (isset($extension))
          $type = $extension;
        else
          $type = $PhotoExtension;
        $fileNew = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . time() . '_' . $fileName . ".jpg";
        $watemarkImageThumbResult = $this->watermark_image($normalPath, $fileNew, $type, $watermarkImage, $modulename);
        if ($watemarkImageThumbResult) {
          @unlink($normalPath);
          $image->open($fileNew)
                  ->resize($normal_width, $normal_height)
                  ->write($normalPath)
                  ->destroy();
          @unlink($fileNew);
        }
      }
    }
    // normal main  image resize
    $normalMainPath = $path . DIRECTORY_SEPARATOR . $base . '_nm.' . $extension;
    $image = Engine_Image::factory();
    $image->open($normalPath)
            ->resize($normal_width, $normal_height)
            ->write($normalMainPath)
            ->destroy();
    // Resize image (icon)
    $squarePath = $path . DIRECTORY_SEPARATOR . $base . '_is.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file);
    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;
    $image->resample($x, $y, $size, $size, 150, 150)
            ->write($squarePath)
            ->destroy();
    // Store
    try {
      $iSquare = $filesTable->createFile($squarePath, $params);
      $iMain = $filesTable->createFile($mainPath, $params);
      $iIconNormal = $filesTable->createFile($normalPath, $params);
      $iNormalMain = $filesTable->createFile($normalMainPath, $params);
      $iMain->bridge($iNormalMain, 'thumb.normalmain');
      $iMain->bridge($iIconNormal, 'thumb.normal');
      $iMain->bridge($iSquare, 'thumb.icon');
    } catch (Exception $e) {
      @unlink($file);
      // Remove temp files
      @unlink($mainPath);
      @unlink($normalPath);
      @unlink($squarePath);
      @unlink($normalMainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    @unlink($file);
    // Remove temp files
    @unlink($mainPath);
    @unlink($normalPath);
    @unlink($squarePath);
    @unlink($normalMainPath);
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    if ($package)
      return $iMain->file_id;;
    $photoParams['file_id'] = $iMain->file_id; // This might be wrong
    $photoParams['photo_id'] = $iMain->file_id;
    $row = Engine_Api::_()->getDbtable('photos', $modulename)->createRow();

    $row->setFromArray($photoParams);
    $row->save();
    return $row;
  }

  public function getRow(Core_Model_Item_Abstract $resource, User_Model_User $user) {

    $id = $resource->getIdentity() . '_' . $user->getIdentity();
    $table = Engine_Api::_()->getDbTable('membership', 'user');
    $select = $table->select()
            ->where('resource_id = ?', $resource->getIdentity())
            ->where('user_id = ?', $user->getIdentity());
    $select = $select->limit(1);
    $row = $table->fetchRow($select);
    return $row;
  }

  public function getColumnName($value) {

    switch ($value) {
      case 'recently created':
        $optionKey = 'creation_date DESC';
        break;
      case 'most viewed':
        $optionKey = 'view_count DESC';
        break;
      case 'most liked':
        $optionKey = 'like_count DESC';
        break;
      case 'most rated':
        $optionKey = 'rating DESC';
        break;
      default:
        $optionKey = $value;
    };
    return $optionKey;
  }

  public function hasCheckMessage($user) {

    // Not logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer->getIdentity() || $viewer->getGuid(false) === $user->getGuid(false)) {
      return false;
    }

    // Get setting?
    $permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
    if (Authorization_Api_Core::LEVEL_DISALLOW === $permission) {
      return false;
    }
    $messageAuth = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'auth');
    if ($messageAuth == 'none') {
      return false;
    } else if ($messageAuth == 'friends') {
      // Get data
      $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
      if (!$direction) {
        //one way
        $friendship_status = $viewer->membership()->getRow($user);
      } else
        $friendship_status = $user->membership()->getRow($viewer);

      if (!$friendship_status || $friendship_status->active == 0) {
        return false;
      }
    }
    return true;
  }

  public function getIdentityWidget($name, $type, $corePages) {
    $widgetTable = Engine_Api::_()->getDbTable('content', 'core');
    $widgetPages = Engine_Api::_()->getDbTable('pages', 'core')->info('name');
    $identity = $widgetTable->select()
            ->setIntegrityCheck(false)
            ->from($widgetTable, 'content_id')
            ->where($widgetTable->info('name') . '.type = ?', $type)
            ->where($widgetTable->info('name') . '.name = ?', $name)
            ->where($widgetPages . '.name = ?', $corePages)
            ->joinLeft($widgetPages, $widgetPages . '.page_id = ' . $widgetTable->info('name') . '.page_id')
            ->query()
            ->fetchColumn();
    return $identity;
  }

  public function getViewerPrivacy($resourceType = null, $privacy = null) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $select = new Zend_Db_Select($db);
      $select->from('engine4_authorization_levels', 'level_id')->where('type = ?', 'public');
      $levelId = $select->query()->fetchColumn();
      return Engine_Api::_()->authorization()->getPermission($levelId, $resourceType, $privacy);
    } else {
      return Engine_Api::_()->authorization()->getPermission($viewer, $resourceType, $privacy);
    }
  }

  public function advShareUrl($href = '', $subject = '', $type = '') {
    if (!$href)
      return 'javascript:;';
    $href = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $href;

    if ($type == 'gmail') {
      return 'https://mail.google.com/mail/u/0/?view=cm&fs=1&to&body=' . urlencode($href) . '&ui=2&tf=1';
    } else if ($type == 'tumblr') {
      return 'http://www.tumblr.com/share/link?url' . urlencode($href);
    } else if ($type == 'digg') {
      return 'http://digg.com/submit?phase=2&amp;url=' . urlencode($href);
    } else if ($type == 'stumbleupon') {
      return 'http://www.stumbleupon.com/submit?url=' . urlencode($href);
    } else if ($type == 'myspace') {
      return 'http://www.myspace.com/Modules/PostTo/Pages/?u=' . urlencode($href) . '&l=3';
    } else if ($type == '') {
      return 'https://www.facebook.com/dialog/send?link=' . urlencode($href) . '&redirect_uri=' . urlencode($href) . '&app_id=' . Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook.appid', '');
    } else if ($type == 'rediff') {
      return 'http://share.rediff.com/bookmark/addbookmark?bookmarkurl=' . urlencode($href);
    } else if ($type == 'googlebookmark') {
      return 'https://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk=' . urlencode($href);
    } else if ($type == 'flipboard') {
      return 'https://share.flipboard.com/bookmarklet/popout?v=2&url=' . urlencode($href);
    } else if ($type == 'skype') {
      return 'https://web.skype.com/share?url=' . urlencode($href) . '&lang=en';
    } else if ($type == 'pinterest') {

      if ($subject) {
        $urlencode = urlencode($href); //urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $subject->getHref());

        return 'http://pinterest.com/pin/create/button/?url=' . $urlencode . '&media=' . urlencode((strpos($subject->getPhotoUrl(), 'http') === FALSE ? (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $subject->getPhotoUrl() ) : $subject->getPhotoUrl())) . '&description=' . $subject->getTitle();
      } else {
        $urlencode = urlencode($href); //urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

        $nonmetaTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessocialshare.nonmeta.title', '');
        $nonmetaPhoto = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessocialshare.nonmeta.photo', '');
        if (!empty($nonmetaPhoto)) {
          $image = $this->baseUrl() . '/' . $nonmetaPhoto;
          $image = 'http://' . $_SERVER['HTTP_HOST'] . $image;
        } else {
          $image = '';
        }

        return 'http://pinterest.com/pin/create/button/?url=' . $urlencode . '&media=' . urlencode((strpos($image, 'http') === FALSE ? (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $image ) : $image)) . '&description=' . strip_tags($nonmetaTitle);
      }
    }
  }

  public function facebookShareUrl($href = '', $subject = '') {
    if (!$href)
      return 'javascript:;';
    $href = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $href;
    return 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($href) . '&t=' . $subject->getTitle();
  }

  public function twitterShareUrl($href = '', $subject = '') {
    if (!$href)
      return 'javascript:;';
    $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $href);
    return 'https://twitter.com/share?url=' . $urlencode . '&text=' . htmlspecialchars(urlencode(html_entity_decode($subject->getTitle('encode'), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8') . "%0a";
  }

  public function googlePlusShareUrl($href = '', $subject = '') {
    if (!$href)
      return 'javascript:;';
    $href = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $href;
    return 'https://plus.google.com/share?url=' . urlencode($href) . '&t=' . $subject->getTitle();
  }

  public function LinkedinShareUrl($href = '', $subject = '') {
    if (!$href)
      return 'javascript:;';
    $href = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $href;
    return 'https://www.linkedin.com/shareArticle?mini=true&url=' . $href;
  }

  public function getFieldsStructurePartial($spec, $parent_field_id = null) {
    // Spec must be a item for this one
    if (!($spec instanceof Core_Model_Item_Abstract)) {
      throw new Fields_Model_Exception("First argument of getFieldsValues must be an instance of Core_Model_Item_Abstract");
    }

    $type = Engine_Api::_()->fields()->getFieldType($spec);
    $parentMeta = null;
    $parentValue = null;

    // Get current field values
    if ($parent_field_id) {
      $parentMeta = Engine_Api::_()->fields()->getFieldsMeta($type)->getRowMatching('field_id', $parent_field_id);
      $parentValueObject = $parentMeta->getValue($spec);
      if (is_array($parentValueObject)) {
        $parentValue = array();
        foreach ($parentValueObject as $parentValueObjectSingle) {
          $parentValue[] = $parentValueObjectSingle->value;
        }
      } else if (is_object($parentValueObject)) {
        $parentValue = $parentValueObject->value;
      }
    }

    // Build structure
    $structure = array();
    foreach (Engine_Api::_()->fields()->getFieldsMaps($spec)->getRowsMatching('field_id', (int) $parent_field_id) as $map) {
      // Get child field
      $field = Engine_Api::_()->fields()->getFieldsMeta($type)->getRowMatching('field_id', $map->child_id);
      if (empty($field)) {
        continue;
      }
      // Add to structure
      $structure[$map->getKey()] = $map;
      // Get dependents
      if ($field->canHaveDependents()) {
        $structure += $this->getFieldsStructurePartial($spec, $field->field_id);
      }
    }

    return $structure;
  }

  function deleteFeed($params = array()) {
    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
    $select = $actionTable->select()
            ->where('type =?', $params['type'])
            ->where('subject_id =?', $params['subject_id'])
            ->where('object_type =?', $params['object_type'])
            ->where('object_id =?', $params['object_id']);
    $actionObject = $actionTable->fetchRow($select);
    if($actionObject)
    $actionObject->delete();
  }

  // common lightbox work: get lightbox image URL
  function getImageViewerHref($getImageViewerData, $paramsExtra = array()) {

    if (is_object($getImageViewerData)) {
      if (isset($getImageViewerData->album_id))
        $album_id = $getImageViewerData->album_id;
      else if (isset($getImageViewerData['album_id']))
        $album_id = $getImageViewerData['album_id'];

      if (isset($getImageViewerData->photo_id))
        $photo_id = $getImageViewerData->photo_id;
      else if (isset($getImageViewerData['photo_id']))
        $photo_id = $getImageViewerData['photo_id'];

      $params = array_merge(array(
          'route' => 'default',
          'module' => 'sesbasic',
          'controller' => 'lightbox',
          'action' => 'image-viewer-detail',
          'reset' => true,
          'album_id' => $album_id,
          'photo_id' => $photo_id,
              ), $paramsExtra);
      $route = $params['route'];
      $reset = $params['reset'];
      unset($params['route']);
      unset($params['reset']);
      return Zend_Controller_Front::getInstance()->getRouter()
                      ->assemble($params, $route, $reset);
    }
    return '';
  }

  function checkBannedWord($newText = "",$oldText = "", $routeType = 0) {
    $isBanned = false;
    if($newText != $oldText) {
      $bannedTable = Engine_Api::_()->getDbTable('bannedwords','sesbasic');
      $select =  $bannedTable->select()
              ->from($bannedTable->info('name'))
              ->where('word =?',$newText);
      if($routeType)
      return $bannedTable->fetchRow($select);
      else
      $isExist =  $bannedTable->fetchRow($select);
      if($isExist)
         $isBanned = true;
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sespagebuilder') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sespagebuilder.pluginactivated')) {
      $bannedURLTable = Engine_Api::_()->getDbTable('bannedwords','sespagebuilder');
      $isExist = $bannedURLTable->select()
            ->from($bannedURLTable->info('name'), 'bannedword_id')
            ->where('word =?', $newText)
            ->query()
            ->fetchColumn();
      if($isExist)
        $isBanned = true;
      }
      $bannedUsernamesTable = Engine_Api::_()->getDbtable('BannedUsernames', 'core');
      if($bannedUsernamesTable->isUsernameBanned($newText))
        $isBanned = true;
      $userTable = Engine_Api::_()->getItemTable('user');
      $isUsernameExist =  $userTable->select()
              ->from($userTable->info('name'),'username')
              ->where('username =?',$newText)->query()->fetchColumn();
      if($isUsernameExist)
        $isBanned = true;
    }
    return $isBanned;
  }

  public function isWordExist($resourceType = '',$resourceId = '', $word = '') {
    $bannedWordTable = Engine_Api::_()->getDbTable('bannedwords','sesbasic');
    return $bannedWordTable->select()
            ->from($bannedWordTable
            ->info('name'),'bannedword_id')
            ->where('resource_type =?',$resourceType)
            ->where('resource_id =?',$resourceId)
            ->where('word =?',$word)
            ->query()
            ->fetchColumn();
  }
  public function getSupportedCurrency(){
    if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getSupportedCurrency();
    }else{
      return array();
    }
  }

  public function getLanguages() {

    // Languages
    $translate = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();

    //$currentLocale = Zend_Registry::get('Locale')->__toString();
    // Prepare default langauge
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if (!in_array($defaultLanguage, $languageList)) {
      if ($defaultLanguage == 'auto' && isset($languageList['en'])) {
        $defaultLanguage = 'en';
      } else {
        $defaultLanguage = null;
      }
    }

    // Prepare language name list
    $languageNameList = array();
    $languageDataList = Zend_Locale_Data::getList(null, 'language');
    $territoryDataList = Zend_Locale_Data::getList(null, 'territory');

    foreach ($languageList as $localeCode) {
      $languageNameList[$localeCode] = Engine_String::ucfirst(Zend_Locale::getTranslation($localeCode, 'language', $localeCode));
      if (empty($languageNameList[$localeCode])) {
        if (false !== strpos($localeCode, '_')) {
          list($locale, $territory) = explode('_', $localeCode);
        } else {
          $locale = $localeCode;
          $territory = null;
        }
        if (isset($territoryDataList[$territory]) && isset($languageDataList[$locale])) {
          $languageNameList[$localeCode] = $territoryDataList[$territory] . ' ' . $languageDataList[$locale];
        } else if (isset($territoryDataList[$territory])) {
          $languageNameList[$localeCode] = $territoryDataList[$territory];
        } else if (isset($languageDataList[$locale])) {
          $languageNameList[$localeCode] = $languageDataList[$locale];
        } else {
          continue;
        }
      }
    }
    return array_merge(array($defaultLanguage => $defaultLanguage), $languageNameList);
  }
  public function updateCart($singlecart) { 
    $db = Engine_Db_Table::getDefaultAdapter();
    if($singlecart) {
      $db->update('engine4_core_menuitems', array('enabled' => 0), array("name = ?" =>"sesproduct_add_cart_dropdown"));
      $db->update('engine4_core_menuitems', array('enabled' => 0), array("name = ?" =>"courses_add_cart_dropdown"));
      $menu = $db->select()
        ->from('engine4_core_menuitems', 'id')
        ->where('name = ?', 'site_add_cart_dropdown')
        ->limit(1)
        ->query()
        ->fetchColumn();
      if(!$menu) {
        $db->insert('engine4_core_menuitems', array(
            'name' => 'site_add_cart_dropdown',
            'module' => 'sesbasic',
            'label' => "Cart",
            'plugin' => "",
            'enabled'=> '1',
            'params' => '{"uri":"javascript:void(0);this.blur();"}',
            'menu'=> 'core_mini',
            'order'=> '6',
        ));
      }
    } else {
      $db->update('engine4_core_menuitems', array('enabled' => 1), array("name = ?" =>"sesproduct_add_cart_dropdown"));
      $db->update('engine4_core_menuitems', array('enabled' => 1), array("name = ?" =>"courses_add_cart_dropdown"));
      $db->query("DELETE FROM engine4_core_menuitems WHERE name = 'site_add_cart_dropdown'");
    }
  $db->commit();
  }
  public function getCurrentCurrency(){
      $settings = Engine_Api::_()->getApi('settings', 'core');
      if(!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])){
      return Engine_Api::_()->sesmultiplecurrency()->getCurrentCurrency();
      }else{
      return $settings->getSetting('payment.currency', 'USD');
      }
  }
  function getCurrencyPrice($price = 0, $givenSymbol = '', $change_rate = '') {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $precisionValue = $settings->getSetting('sesmultiplecurrency.precision', 2);
    $defaultParams['precision'] = $precisionValue;
    if (!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])) {
      return Engine_Api::_()->sesmultiplecurrency()->getCurrencyPrice($price, $givenSymbol, $change_rate);
    } else {
	  $givenSymbol = $settings->getSetting('payment.currency', 'USD');
      return Zend_Registry::get('Zend_View')->locale()->toCurrency($price, $givenSymbol, $defaultParams);
    }
  }
}
