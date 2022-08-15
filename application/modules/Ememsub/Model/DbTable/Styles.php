<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Styles.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Ememsub_Model_DbTable_Styles extends Core_Model_Item_DbTable_Abstract {
  protected $_rowClass = "Ememsub_Model_Style";
  public function getStyleId($packageId,$templateId) {
    $select = $this->select()->from($this->info('name'),array('*'))->where('package_id = ?', $packageId)->where('template_id = ?', $templateId);
    return $this->fetchRow($select);//->query()->fetchColumn();
  }
}
