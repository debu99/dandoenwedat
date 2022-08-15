<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Style.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Model_Style extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'user';
  protected $_owner_type = 'user';
  protected $_parent_is_owner = true;
  protected $_type = 'style';
  public function setPhoto($photo, $column_id) {
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
    if (!$fileName) {
      $fileName = $file;
    }

    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_price.' . ltrim(strrchr($fileName, '.'), '.');
    $photo_params = array(
        'parent_id' => $column_id,
        'parent_type' => "ememsub_feature",
        'name' => $fileName,
    );
    //Resize accordion icon
    $image = Engine_Image::factory();
    $image->open($file)
            ->resample(0, 0, $image->width, $image->height, $image->width, $image->height)
            ->write($mainPath)
            ->destroy();
    try {
      $photoFile = Engine_Api::_()->getDbtable('files', 'storage')->createFile($mainPath, $photo_params);
    } catch (Exception $e) {
      if ($e->getCode() == Storage_Api_Storage::SPACE_LIMIT_REACHED_CODE) {
        echo $e->getMessage();
        exit();
      }
    }
    //Delete temp file.
    @unlink($mainPath);
    return $photoFile;
  }
}
