<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Widget_ActivityPointsInfoController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer->getIdentity())
      return $this->setNoRender();
    $this->view->form = $form = new Sescredit_Form_ActivityFilter();
    $actionTypesTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
    $actionTypesTableName = $actionTypesTable->info('name');
    $moduleSettingTable = Engine_Api::_()->getDbTable('modulesettings', 'sescredit');
    $moduleSettingTableName = $moduleSettingTable->info('name');
    $select = $actionTypesTable->select()
            ->from($actionTypesTableName, array('module', 'type'))
            ->setIntegrityCheck(false)
            ->joinLeft($moduleSettingTableName, $moduleSettingTableName . '.module = ' . $actionTypesTableName . '.module', array('modulesetting_id', 'order_id', 'title', 'parent_id', 'status'))
            ->where($moduleSettingTableName . '.parent_id IS NULL or parent_id=""')
            ->where($moduleSettingTableName.'.modulesetting_id IS NOT NULL')
            ->order($moduleSettingTableName . '.order_id ASC');
    $actionTypes = $actionTypesTable->fetchAll($select);
    $this->view->module = $selectedModule = !empty($_GET['module']) ? $_GET['module'] : $this->_getParam('moduleName');
    $moduleOptions = array();
    $moduleTable = Engine_Api::_()->getDbTable('modules', 'core');
    foreach ($actionTypes as $actionType) {
      $moduleBaseActionTypes[$actionType->module][$actionType->type] = 'ADMIN_ACTIVITY_TYPE_' . strtoupper($actionType->type);
      if (isset($moduleOptions[$actionType->module])) {
        continue;
      }
      if (!empty($actionType->modulesetting_id) && !$actionType->status) {
        continue;
      }
      if ($moduleTable->getModule($actionType->module)->enabled) {
        $moduleOptions[$actionType->module] = !empty($actionType->title) ? $actionType->title : $moduleTable->getModule($actionType->module)->title;
      }
    }
    if (!$selectedModule || !isset($moduleBaseActionTypes[$selectedModule])) {
      $selectedModule = '';
    }
    $form->module->setMultiOptions(array_merge(array('' => 'All Modules'), $moduleOptions));
    $form->populate(array('module' => $selectedModule));
    $this->view->widgetId = $widgetId = (isset($_POST['widget_id']) ? $_POST['widget_id'] : $this->view->identity);
    $this->view->widgetName = 'activity-points-info';
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $creditValueTable = Engine_Api::_()->getDbTable('values', 'sescredit');
    $creditValueTableName = $creditValueTable->info('name');
    $select = $creditValueTable->select()
            ->setIntegrityCheck(false)
            ->from($creditValueTable->info('name'), array('*', "custom_module" => new Zend_Db_Expr("case when parent_id IS Not NULL then parent_id else engine4_sescredit_values.module end"), "custom_orderid" => new Zend_Db_Expr("case when modulesetting_id IS Not NULL then order_id else 99999 end")))
            ->joinLeft($moduleSettingTableName, $moduleSettingTableName . '.module = ' . $creditValueTableName . '.module', array('module_title'=>'title'))
            ->where('member_level =?', $viewer->level_id);
    if ($selectedModule != '') {
      $select->where($creditValueTableName . '.module IN (SELECT  engine4_activity_actiontypes.module from engine4_activity_actiontypes left join engine4_sescredit_modulesettings on engine4_activity_actiontypes.module = engine4_sescredit_modulesettings.module where (engine4_sescredit_modulesettings.status IS NULL or engine4_sescredit_modulesettings.status = 1) and (engine4_activity_actiontypes.module = "' . $selectedModule . '" or parent_id = "' . $selectedModule . '"))');
    }
    else {
      $select->where('engine4_sescredit_modulesettings.status IS NULL or engine4_sescredit_modulesettings.status = 1');
    }
    $select->order('custom_module ASC');
    $select->order('custom_orderid ASC');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(4);
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->page = $page;
    $paginator->setCurrentPageNumber($page);
    if ($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    }
  }

}
