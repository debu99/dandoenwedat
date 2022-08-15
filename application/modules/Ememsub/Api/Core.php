<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Core.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Api_Core extends Core_Api_Abstract {
  public function isFileIdExist($fileId = null, $columnId = null) {
    $column = $fileId . '_file_id';
    $featureTable = Engine_Api::_()->getDbtable('features', 'ememsub');
    $db = Engine_Db_Table::getDefaultAdapter();
    $columnName = $db->query("SHOW COLUMNS FROM engine4_ememsub_features LIKE '$column'")->fetch();
    if(empty($columnName))
      return '0';
    
    $fileId = $featureTable->select()->from($featureTable->info('name'), $column)->where('feature_id =?', $columnId)->query()->fetchColumn();
    return ($fileId) ? '1' : '0';
  }
  public function textTruncation($text, $textLength = null) {
    $text = strip_tags($text);
    return ( Engine_String::strlen($text) > $textLength ? Engine_String::substr($text, 0, $textLength) . '...' : $text);
  }
}
