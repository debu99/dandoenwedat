<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Modulesettings.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Modulesettings extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Modulesetting";

  public function getModuleChild($params = array()) {
    $moduleTable = Engine_Api::_()->getDbTable('modules', 'core');
    $moduleTableName = $moduleTable->info('name');
    $actionTypeTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
    $actionTypeTableName = $actionTypeTable->info('name');
    $moduleSettingTable = Engine_Api::_()->getDbTable('modulesettings', 'sescredit');
    $moduleSettingTableName = $moduleSettingTable->info('name');
    $select = $moduleTable->select()
            ->from($moduleTableName, array('*'))
            ->setIntegrityCheck(false)
            ->joinLeft($moduleSettingTableName, $moduleSettingTableName . '.module = ' . $moduleTableName . '.name', array('*'))
            ->join($actionTypeTableName, $actionTypeTableName . '.module = ' . $moduleTableName . '.name', null)
            ->where($moduleTableName . '.enabled =?', 1);
    if (isset($params['parent_id']) && $params['parent_id'])
      $select->where($moduleSettingTableName . '.parent_id =?', $params['parent_id']);
    else
      $select->where($moduleSettingTableName . '.parent_id IS NULL or ' . $moduleSettingTableName . '.parent_id = ""');
    $select->group($actionTypeTableName . '.module')->order('order_id DESC');
    return $moduleTable->fetchAll($select);
  }

  public function order($categoryType = 'modulesetting_id', $categoryTypeId) {
    // Get a list of all corresponding category, by order
    $currentOrder = $this->select()
            ->from($this->info('name'), 'modulesetting_id')
            ->order('order_id DESC');
    if ($categoryType != 'modulesetting_id')
      $currentOrder = $currentOrder->where($categoryType . ' = ?', $categoryTypeId);
    else
      $currentOrder = $currentOrder->where('parent_id  IS NULL or parent_id = "" ');
    return $currentOrder->query()->fetchAll(Zend_Db::FETCH_COLUMN);
  }

}
