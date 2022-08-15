<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sescredit_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_settings');
    $this->view->form = $form = new Sescredit_Form_Admin_Settings_Global();
    $setting = Engine_Api::_()->getApi('settings', 'core');

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {

      $values = $form->getValues();

      if (isset($_POST['sescredit_endtime']) && empty($_POST['sescredit_endtime']) && empty($_POST['sescredit_year']) && empty($_POST['sescredit_month'])) {
        $form->addError("Please select atleast month or year. It is required.");
        return;
      }

      include_once APPLICATION_PATH . "/application/modules/Sescredit/controllers/License.php";
      if (!empty($_POST['end_date'])) {
        $values['sescredit_end_time'] = date('Y-m-d H:i:s', strtotime($_POST['end_date'] . ' ' . $_POST['end_time']));
      }
      if ($setting->getSetting('sescredit.pluginactivated', 1)) {
        //START TEXT CHNAGE WORK IN CSV FILE
        $oldSigularWord = $setting->getSetting('sescredit.text.singular', 'credit');
        $oldPluralWord = $setting->getSetting('sescredit.text.plural', 'credits');
        $newSigularWord = (isset($values['sescredit_text_singular']) && $values['sescredit_text_singular']) ? $values['sescredit_text_singular'] : 'credit';
        $newPluralWord = (isset($values['sescredit_text_plural']) && $values['sescredit_text_plural']) ? $values['sescredit_text_plural'] : 'credits';
        $newSigularWordUpper = ucfirst($newSigularWord);
        $newPluralWordUpper = ucfirst($newPluralWord);
        if ($newSigularWord != $oldSigularWord && $newPluralWord != $oldPluralWord) {
          $tmp = Engine_Translate_Parser_Csv::parse(APPLICATION_PATH . '/application/languages/en/sescredit.csv', 'null', array('delimiter' => ';', 'enclosure' => '"'));
          if (!empty($tmp['null']) && is_array($tmp['null']))
            $inputData = $tmp['null'];
          else
            $inputData = array();
          $OutputData = array();
          $chnagedData = array();
          foreach ($inputData as $key => $input) {
            $chnagedData = str_replace(array($oldPluralWord, $oldSigularWord, ucfirst($oldPluralWord), ucfirst($oldSigularWord), strtoupper($oldPluralWord), strtoupper($oldSigularWord)), array($newPluralWord, $newSigularWord, ucfirst($newPluralWord), ucfirst($newSigularWord), strtoupper($newPluralWord), strtoupper($newSigularWord)), $input);
            $OutputData[$key] = $chnagedData;
          }
          $targetFile = APPLICATION_PATH . '/application/languages/en/sescredit.csv';
          if (file_exists($targetFile))
            @unlink($targetFile);
          touch($targetFile);
          chmod($targetFile, 0777);
          $writer = new Engine_Translate_Writer_Csv($targetFile);
          $writer->setTranslations($OutputData);
          $writer->write();
          //END CSV FILE WORK
        }
        if (!empty($_POST['sescredit_endtime'])) {
          $_POST['sescredit_year'] = '16';
          $_POST['sescredit_month'] = 0;
        }
        foreach ($_POST as $key => $value) {
          if (Engine_Api::_()->getApi('settings', 'core')->hasSetting($key, $value))
            $setting->removeSetting($key);
          if (!$value && strlen($value) == 0)
            continue;
          $setting->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        if ($error)
          $this->_helper->redirector->gotoRoute(array());
      }
    }
  }
  public function moduleEnableAction(){
      $value_id = $this->_getParam('id');
      if (!empty($value_id)) {
          $creditValue = Engine_Api::_()->getItem('sescredit_managemodule', $value_id);
          $creditValue->enabled = !$creditValue->enabled;
          $creditValue->save();
      }
      $creditValue = Engine_Api::_()->getItem('sescredit_managemodule', $value_id);
      $value = 0;
      if ($creditValue->enabled)
          $value = 1;
      echo json_encode(array('error' => false, 'value' => $value));
      die;
  }
  public function manageModuleAction(){
      $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_manageModule');

      if(count($_POST)){
          foreach ($_POST['module'] as $key=>$module){
              $item = Engine_Api::_()->getItem('sescredit_managemodule', $module);
              if(!$item)
                  continue;
              $item->min_credit = $_POST['min_credit'][$key];
              $item->min_checkout_price = $_POST['min_checkout_price'][$key];
              $item->limit_use = $_POST['limit_use'][$key];
              $item->save();

          }
      }


      $table = Engine_Api::_()->getDbTable('managemodules','sescredit');
      $select = $table->select()->from($table->info('name'),'*');

      $this->view->paginator = $table->fetchAll($select);

  }
  public function manageWidgetizePageAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_managewidgetizepage');
    $pagesArray = array(
        'sescredit_index_manage',
        'sescredit_index_transaction',
        'sescredit_index_earn-credit',
        'sescredit_index_help',
        'sescredit_index_badges',
        'sescredit_index_leaderboard'
    );
    $this->view->pagesArray = $pagesArray;
  }

  public function statsticsAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sescredit_admin_main', array(), 'sescredit_admin_main_statstics');
    $this->view->formFilter = $formFilter = new Sescredit_Form_Admin_Transaction_Filter();
    $currentTime = date('Y-m-d H:i:s');
    $creditTable = Engine_Api::_()->getDbTable('credits', 'sescredit');
    $creditTableName = $creditTable->info('name');
    $subQueryCondition = '';
    $subQueryDateCondition = '';
    if (isset($_GET['show_date_field']) && !empty($_GET['show_date_field'])) {
      $explodeTime = explode('-', $_GET['show_date_field']);
      $startTime = $explodeTime[0];
      $endTime = $explodeTime[1];
      $subQuery = "DATE_FORMAT(" . $creditTableName . ".creation_date, '%Y-%m-%d') between ('" . date('Y-m-d', strtotime($startTime)) . "') and ('" . date('Y-m-d', strtotime($endTime)) . "')";
      $subQueryCondition = ' and ' . $subQuery;
    }

    if (isset($_GET['show']) && $_GET['show'] == '2') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 week"));
      $subQueryDateCondition = "DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')";
      $subQueryCondition .= ' and ' . $subQueryDateCondition;
    } elseif (isset($_GET['show']) && $_GET['show'] == '1') {
      $subQueryDateCondition = $creditTableName . "creation_date LIKE '" . date('Y-m-d') . "%'";
      $subQueryCondition .= ' and ' . $subQueryDateCondition;
    } elseif (isset($_GET['show']) && $_GET['show'] == '3') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 month"));
      $subQueryDateCondition = "DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')";
      $subQueryCondition .= ' and ' . $subQueryDateCondition;
    }
    $select = $creditTable->select()
            ->from($creditTableName, array("totalCredit" => new Zend_Db_Expr("(SELECT SUM(credit) from " . $creditTableName . " where point_type = 'credit' $subQueryCondition)"), 'totalDeduct' => new Zend_Db_Expr('SUM(credit)'), "totalPurchase" => new Zend_Db_Expr("(SELECT SUM(credit) from " . $creditTableName . " where point_type = 'buy' $subQueryCondition)"), "totalReferral" => new Zend_Db_Expr("(SELECT SUM(credit) from " . $creditTableName . " where point_type = 'affiliate' $subQueryCondition)"), "totalLevelUpgrade" => new Zend_Db_Expr("(SELECT SUM(credit) from " . $creditTableName . " where point_type = 'upgrade_level' $subQueryCondition)"), "totalReceiveFriend" => new Zend_Db_Expr("(SELECT SUM(credit) from " . $creditTableName . " where point_type = 'receive_friend' $subQueryCondition)"), "totalTransferFriend" => new Zend_Db_Expr("(SELECT SUM(credit) from " . $creditTableName . " where point_type = 'transfer_friend' $subQueryCondition)"), "totalProductPurchased" =>  new Zend_Db_Expr("(SELECT SUM(credit) from " . $creditTableName . " where point_type = 'sesproduct_order' $subQueryCondition)")));
    $select->where('point_type =?', 'deduction');

    if (!empty($subQueryDateCondition)) {
      $select->where($subQueryDateCondition);
    }
    if (isset($_GET['show_date_field']) && !empty($_GET['show_date_field'])) {
      $select->where($subQuery);
    }
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $this->view->assign($values);
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
      if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
      $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $this->view->stats = $creditTable->fetchRow($select);
  }

}
