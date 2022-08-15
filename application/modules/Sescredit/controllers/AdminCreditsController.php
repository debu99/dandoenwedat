<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminCreditsController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_AdminCreditsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_managecredits');
    $this->view->form = $form = new Sescredit_Form_Admin_Settings_Filter();
    $selectedModule = $this->_getParam('plugin');

    $translate = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if (!in_array($defaultLanguage, $languageList)) {
      if ($defaultLanguage == 'auto' && isset($languageList['en'])) {
        $defaultLanguage = 'en';
      } else {
        $defaultLanguage = null;
      }
    }
    // Start language work
    $languageNameList = array();
    $languageDataList = Zend_Locale_Data::getList(null, 'language');
    $territoryDataList = Zend_Locale_Data::getList(null, 'territory');
    foreach ($languageList as $localeCode) {
      $column = $db->query("SHOW COLUMNS FROM engine4_sescredit_values LIKE '$localeCode'")->fetch();
      if (empty($column)) {
        $db->query("ALTER TABLE `engine4_sescredit_values` ADD $localeCode TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL AFTER `deduction`");
      }
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
    $languageNameList = array_merge(array(
        $defaultLanguage => $defaultLanguage
            ), $languageNameList);
    $this->view->languageNameList = $languageNameList;
    //End language work

    $actionTypesTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
    $actionTypes = $actionTypesTable->fetchAll();

    $moduleOptions = $moduleBaseActionTypes = array();
    $moduleTable = Engine_Api::_()->getDbTable('modules', 'core');
    foreach ($actionTypes as $actionType) {
      $moduleBaseActionTypes[$actionType->module][$actionType->type] = 'ADMIN_ACTIVITY_TYPE_' . strtoupper($actionType->type);

      if (isset($moduleOptions[$actionType->module])) {
        continue;
      }
      if ($moduleTable->getModule($actionType->module)->enabled) {
        $moduleOptions[$actionType->module] = $moduleTable->getModule($actionType->module)->title;
      }
    }
    asort($moduleOptions);
    if (!$selectedModule || !isset($moduleBaseActionTypes[$selectedModule])) {
      $selectedModule = key($moduleOptions);
    }
// Get level id
    if (null !== ($id = $this->_getParam('member_level'))) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }
    $this->view->level_id = $id = $level->level_id;
    $form->member_level->setValue($id);
    $form->plugin->setMultiOptions($moduleOptions);
    $form->populate(array('plugin' => $selectedModule));
    if ($selectedModule) {
      $moduleBaseActionTypesOld = $moduleBaseActionTypes;
      $moduleBaseActionTypes = array();
      $moduleBaseActionTypes[$selectedModule] = ($moduleBaseActionTypesOld[$selectedModule]);
    }

    $this->view->moduleBaseActionTypes = $moduleBaseActionTypes;
    $this->view->moduleOptions = $moduleOptions;

    if (!$this->getRequest()->isPost()) {
      return;
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    foreach ($_POST['type'] as $key => $value) {
      $type = strtolower(str_replace('ADMIN_ACTIVITY_TYPE_', '', $value));
      $levelId = isset($_GET['member_level']) ? $_GET['member_level'] : $id;
      $creditValueTable = Engine_Api::_()->getDbTable('values', 'sescredit');
      $select = $creditValueTable->select()
              ->from($creditValueTable->info('name'), array('*'))
              ->where('type =?', $type)
              ->where('member_level =?', $levelId);
      $creditValue = $creditValueTable->fetchRow($select);
      if (empty($creditValue)) {
        if (empty($_POST['firstactivity'][$key]) && empty($_POST['nextactivity'][$key]))
          continue;
        $db = $creditValueTable->getAdapter();
        $db->beginTransaction();
        try {
          $creditValue = $creditValueTable->createRow();
          $creditValue->type = $type;
          $creditValue->module = $selectedModule;
          $creditValue->firstactivity = $_POST['firstactivity'][$key];
          $creditValue->nextactivity = $_POST['nextactivity'][$key];
          $creditValue->maxperday = $_POST['maxperday'][$key];
          $creditValue->deduction = $_POST['deduction'][$key];
          $creditValue->member_level = $levelId;
          if (isset($_POST['status'][$value]))
            $creditValue->status = $_POST['status'][$value];
          foreach ($languageList as $language) {
            $creditValue->{$language} = $_POST[$language][$key];
          }
          $creditValue->save();
          // Commit
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }
      } else {
        $creditValue->firstactivity = $_POST['firstactivity'][$key];
        $creditValue->nextactivity = $_POST['nextactivity'][$key];
        $creditValue->maxperday = $_POST['maxperday'][$key];
        $creditValue->deduction = $_POST['deduction'][$key];
        foreach ($languageList as $language) {
          $creditValue->{$language} = $_POST[$language][$key];
        }
        $creditValue->save();
      }
    }
    if (isset($_SERVER['HTTP_REFERER']))
      return $_SERVER['HTTP_REFERER'];
    else
      return $this->_helper->redirector->gotoRoute(array());
  }

  public function settingsAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_modulesettings');
    $this->view->modules = Engine_Api::_()->getDbTable('modulesettings', 'sescredit')->getModuleChild();
  }

  public function transactionsAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_transaction');
    $this->view->formFilter = $formFilter = new Sescredit_Form_Admin_Transaction_Filter();
    $currentTime = date('Y-m-d H:i:s');
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $creditTable = Engine_Api::_()->getDbTable('credits', 'sescredit');
    $creditTableName = $creditTable->info('name');
    $select = $userTable->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('displayname', 'user_id'))
            ->joinLeft($creditTableName, $creditTableName . '.owner_id = ' . $userTableName . '.user_id', array('*'))
            ->where($creditTableName . '.credit_id IS NOT NULL')
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'credit_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));
    if (!empty($_GET['owner_name']))
      $select->where($userTableName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');

    if (isset($_GET['show']) && $_GET['show'] == '2') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 week"));
      $select->where("DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')");
    } elseif (isset($_GET['show']) && $_GET['show'] == '1') {
      $select->where("$creditTableName.creation_date LIKE ?", date('Y-m-d') . "%");
    } elseif (isset($_GET['show']) && $_GET['show'] == '3') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 month"));
      $select->where("DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')");
    }
    if (isset($_GET['point_type']) && $_GET['point_type']) {
      if ($_GET['point_type'] == 1)
        $select->where("$creditTableName.point_type =?", "credit");
      elseif ($_GET['point_type'] == 2)
        $select->where("$creditTableName.point_type =?", "deduction");
      elseif ($_GET['point_type'] == 3)
        $select->where("$creditTableName.point_type =?", "affiliate");
      elseif ($_GET['point_type'] == 4)
        $select->where("$creditTableName.point_type =?", "transfer_friend");
      elseif ($_GET['point_type'] == 8)
          $select->where("$creditTableName.point_type =?", "sesproduct_order");
      elseif ($_GET['point_type'] == 5)
        $select->where("$creditTableName.point_type =?", "receive_friend");
      elseif ($_GET['point_type'] == 6)
        $select->where("$creditTableName.point_type =?", "upgrade_level");
      elseif ($_GET['point_type'] == 7)
        $select->where("$creditTableName.point_type =?", "buy");
    }
    if (isset($_GET['show_date_field']) && !empty($_GET['show_date_field'])) {
      $explodeTime = explode('-', $_GET['show_date_field']);
      $startTime = $explodeTime[0];
      $endTime = $explodeTime[1];
      $select->where("DATE_FORMAT(" . $creditTableName . ".creation_date, '%Y-%m-%d') between ('" . date('Y-m-d', strtotime($startTime)) . "') and ('" . date('Y-m-d', strtotime($endTime)) . "')");
    }
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $values = array_merge(array(
        'order' => isset($_GET['order']) ? $_GET['order'] : '',
        'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
            ), $values);
    $this->view->assign($values);
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
      if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
      $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function memberPointsAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_membercredit');
    $this->view->formFilter = $formFilter = new Sescredit_Form_Admin_Member_Filter();
    $month = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.month', 0);
    $year = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.year', 0);
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $minPoint = isset($_GET['min_point']) ? ($_GET['min_point'] == '' ? 0 : $_GET['min_point']) : 0;
    $maxPoint = isset($_GET['max_point']) ? ($_GET['max_point'] == '' ? 10000 : $_GET['max_point']) : 10000;
    $creditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $creditDetailTableName = $creditDetailTable->info('name');
    $endDate = "date_add(date_add(first_activity_date,interval $month month),interval $year year)";
    $select = $userTable->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('displayname', 'user_id'))
            ->joinLeft($creditDetailTableName, $creditDetailTableName . '.owner_id = ' . $userTableName . '.user_id', array('*', 'expiry_date' => new Zend_Db_Expr($endDate)))
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'user_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));

    $select->where($creditDetailTableName . ".total_credit between $minPoint and $maxPoint");

    if (!empty($_GET['owner_name']))
      $select->where($userTableName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $values = array_merge(array(
        'order' => isset($_GET['order']) ? $_GET['order'] : '',
        'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
            ), $values);
    $this->view->assign($values);
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
      if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
      $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function showParentTypeAction() {
    $this->view->form = $form = new Sescredit_Form_Admin_Settings_ParentType();
    $selectedModule = $this->_getParam('plugin');
    $actionTypesTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
    $actionTypes = $actionTypesTable->fetchAll();
    $moduleOptions = array();
    $moduleTable = Engine_Api::_()->getDbTable('modules', 'core');
    foreach ($actionTypes as $actionType) {
      $moduleBaseActionTypes[$actionType->module][$actionType->type] = 'ADMIN_ACTIVITY_TYPE_' . strtoupper($actionType->type);
      if (isset($moduleOptions[$actionType->module])) {
        continue;
      }
      if ($moduleTable->getModule($actionType->module)->enabled) {
        $moduleOptions[$actionType->module] = $moduleTable->getModule($actionType->module)->title;
      }
    }
    asort($moduleOptions);
    $moduleSettingTable = Engine_Api::_()->getDbTable('modulesettings', 'sescredit');
    $select = $moduleSettingTable->select()
            ->from($moduleSettingTable->info('name'), array('*'))
            ->where('module =?', $selectedModule);
    $isModuleExist = $moduleSettingTable->fetchRow($select);
    $form->plugin->setMultiOptions($moduleOptions);
    $rowExist = 0;
    if (!empty($isModuleExist)) {
      $form->title->setValue(!empty($isModuleExist->title) ? $isModuleExist->title : $selectedModule);
      $form->populate(array('plugin' => (!empty($isModuleExist->parent_id) ? $isModuleExist->parent_id : $selectedModule)));
      $rowExist = 1;
    } else {
      $form->title->setValue($selectedModule);
      $form->populate(array('plugin' => $selectedModule));
    }
    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    $db = $moduleSettingTable->getAdapter();
    $db->beginTransaction();
    try {
      if (!$rowExist) {
        $moduleSetting = $moduleSettingTable->createRow();
        $moduleSetting->parent_id = ($_POST['plugin'] != $selectedModule) ? $_POST['plugin'] : NULL;
        $moduleSetting->module = $selectedModule;
        $moduleSetting->title = $_POST['title'];
        $moduleSetting->save();
        $order = $moduleSetting->modulesetting_id;
        $moduleSetting->order_id = $order;
        $moduleSetting->save();
      } else {
        $isModuleExist->parent_id = ($_POST['plugin'] != $selectedModule) ? $_POST['plugin'] : NULL;
        $isModuleExist->module = $selectedModule;
        $isModuleExist->title = $_POST['title'];
        $isModuleExist->save();
      }
// Commit
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    return $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => 10,
                'parentRefresh' => 10,
                'messages' => array('You have successfully.')
    ));
  }

  public function changeOrderAction() {
    if ($this->_getParam('id', false) || $this->_getParam('nextid', false)) {
      $id = $this->_getParam('id', false);
      $order = $this->_getParam('articleorder', false);
      $order = explode(',', $order);
      $nextid = $this->_getParam('nextid', false);
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $categoryTable = Engine_Api::_()->getDbTable('modulesettings', 'sescredit');
      //for ($i = count($order) - 1; $i>0; $i--) {
      for ($i = 0; $i < count($order); $i++) {
        $module = $order[$i];
        if ($module == 'undefined')
          continue;
        $dbObject->query('INSERT INTO engine4_sescredit_modulesettings (module, order_id) VALUES ("' . $module . '","' . $i . '")	ON DUPLICATE KEY UPDATE	order_id =' . $i);
      }
      $checkCategoryChildrenCondition = $dbObject->query("SELECT * FROM engine4_sescredit_modulesettings WHERE parent_id = '" . $id . "' || parent_id = '" . $nextid . "'")->fetchAll();
      if (empty($checkCategoryChildrenCondition)) {
        echo 'done';
        die;
      }
      echo "children";
      die;
    }
  }

  public function showDetailAction() {
    $this->view->creditDetail = Engine_Api::_()->getItem('sescredit_credit', $this->_getParam('id'));
  }

  public function showMemberPointDetailAction() {
    $this->view->memberPointDetail = Engine_Api::_()->getItem('sescredit_detail', $this->_getParam('id'));
  }

  public function enableAction() {
    $value_id = $this->_getParam('id');
    if (!empty($value_id)) {
      $creditValue = Engine_Api::_()->getItem('sescredit_value', $value_id);
      $creditValue->status = !$creditValue->status;
      $creditValue->save();
    }
    $creditValue = Engine_Api::_()->getItem('sescredit_value', $value_id);
    $value = 0;
    if ($creditValue->status)
      $value = 1;
    echo json_encode(array('error' => false, 'value' => $value));
    die;
  }

  public function enablePluginAction() {
    $value_id = $this->_getParam('id');
    $moduleName = $this->_getParam('plugin');
    if (empty($value_id) && !empty($moduleName)) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sescredit_modulesettings (module, order_id, `status`) VALUES ("' . $moduleName . '",0, 1) ON DUPLICATE KEY UPDATE	order_id = order_id');
    } else {
      if (!empty($value_id)) {
        $pluginSetting = Engine_Api::_()->getItem('sescredit_modulesetting', $value_id);
        $pluginSetting->status = !$pluginSetting->status;
        $pluginSetting->save();
      }
    }
    if (isset($_SERVER['HTTP_REFERER']))
      $url = $_SERVER['HTTP_REFERER'];
    else
      $url = 'admin/sescredit/credits/settings';
    $this->_redirect($url);
  }

  public function sendPointsAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_sendpoint');
    $this->view->formFilter = $formFilter = new Sescredit_Form_Admin_Reward_Filter();
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $levelTableName = Engine_Api::_()->getDbTable('levels', 'authorization')->info('name');
    $rewardPointTable = Engine_Api::_()->getDbTable('rewardpoints', 'sescredit');
    $rewardPointTableName = $rewardPointTable->info('name');
    $select = $userTable->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('level_id', 'displayname', 'user_id'))
            ->join($rewardPointTableName, $rewardPointTableName . ".user_id =" . $userTableName . ".user_id", array('*'))
            ->join($levelTableName, $levelTableName . ".level_id =" . $userTableName . ".level_id", array('title'))
            ->order((!empty($_GET['order']) ? $_GET['order'] : 'rewardpoint_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));
    if (!empty($_GET['owner_name']))
      $select->where($userTableName . '.displayname LIKE ?', '%' . $_GET['owner_name'] . '%');

    if (isset($_GET['show_date_field']) && !empty($_GET['show_date_field'])) {
      $explodeTime = explode('-', $_GET['show_date_field']);
      $startTime = $explodeTime[0];
      $endTime = $explodeTime[1];
      $select->where("DATE_FORMAT(" . $rewardPointTableName . ".creation_date, '%Y-%m-%d') between ('" . date('Y-m-d', strtotime($startTime)) . "') and ('" . date('Y-m-d', strtotime($endTime)) . "')");
    }
    $minPoint = isset($_GET['min_point']) ? ($_GET['min_point'] == '' ? 0 : $_GET['min_point']) : 0;
    $maxPoint = isset($_GET['max_point']) ? ($_GET['max_point'] == '' ? 10000 : $_GET['max_point']) : 10000;

    $select->where($rewardPointTableName . ".point between $minPoint and $maxPoint");

    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $values = array_merge(array(
        'order' => isset($_GET['order']) ? $_GET['order'] : '',
        'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
            ), $values);
    $this->view->assign($values);
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
      if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
      $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function sendPointAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_sendpoint');
    $this->view->form = $form = new Sescredit_Form_Admin_Reward_SendPoint();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->getRequest()->isPost())
      return;

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    $values = $form->getValues();
    $point = $_POST['point'];
    $memberType = $_POST['member_type'];
    $userId = (isset($_POST['sescredit_user_id']) && !empty($_POST['sescredit_user_id'])) ? $_POST['sescredit_user_id'] : 0;
    $memberLevel = (isset($_POST['member_level']) && !empty($_POST['member_level'])) ? $_POST['member_level'] : 0;
    if ($memberType == 1 && empty($_POST['sescredit_user_id'])) {
      $form->addError("Please select the member to whom you want to send point.");
      return;
    }
    if ($_POST['send_email'] == 1 && empty($_POST['email_message'])) {
      $form->addError("Please enter the message will be attach in email.");
      return;
    }
    $rewardPointTable = Engine_Api::_()->getDbTable('rewardpoints', 'sescredit');
    $db = $rewardPointTable->getAdapter();
    $userTable = Engine_Api::_()->getItemTable('user');
    $select = $userTable->select()
            ->from($userTable->info('name'), array('user_id', 'level_id', 'email', 'photo_id'))
            ->where('level_id != ?', 1);
    switch ($memberType) {
      case '0' :
        $members = $userTable->fetchAll($select);
        break;
      case '1':
        $select->where('user_id =?', $userId);
        $members = $userTable->fetchAll($select);
        break;
      case '2':
        $select->where('level_id =?', $memberLevel);
        $members = $userTable->fetchAll($select);
        break;
    }
    foreach ($members as $member) {
      $db->beginTransaction();
      try {
        $rewardPoint = $rewardPointTable->createRow();
        $rewardPoint->member_type = $memberType;
        $rewardPoint->level_id = $member->level_id;
        $rewardPoint->user_id = $member->user_id;
        $rewardPoint->point = $point;
        $rewardPoint->reason = $_POST['send_reason'];
        $rewardPoint->save();
        $db->commit();
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'reward', 'owner_id' => $member->user_id, 'action_id' => 0, 'object_id' => 0, 'point' => $point, 'point_type' => 'reward'));
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($member, $viewer, $viewer, 'notif_sescredit_send_by_site');
        if ($_POST['send_email']) {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($member->email, 'sescredit_send_by_site', array('object_link' => $view->baseUrl(), 'host' => $_SERVER['HTTP_HOST'], 'point' => $_POST['point'], 'message' => $_POST['email_message']));
        }
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'send-points'));
  }

  public function getAllMembersAction() {
    $sesdata = array();
    $search = $this->_getParam('text', null);
    $userTable = Engine_Api::_()->getItemTable('user');
    $select = $userTable->select()
            ->from($userTable->info('name'), array('user_id', 'displayname', 'photo_id'))
            ->where('level_id != ?', 1)
            ->where('displayname LIKE ?', '%' . $search . '%');
    $members = $userTable->fetchAll($select);
    foreach ($members as $member) {
      $userIcon = $this->view->itemPhoto($member, 'thumb.icon');
      $sesdata[] = array(
          'id' => $member->user_id,
          'label' => $member->displayname,
          'photo' => $userIcon
      );
    }
    return $this->_helper->json($sesdata);
  }

  public function showSendPointDetailAction() {
    $this->view->detail = Engine_Api::_()->getItem('sescredit_rewardpoint', $this->_getParam('id'));
  }

}
