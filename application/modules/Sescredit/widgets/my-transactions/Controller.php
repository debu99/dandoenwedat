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

class Sescredit_Widget_MyTransactionsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $viewer = $this->view->viewer();
    if (!$viewer->getIdentity()) {
      return $this->setNoRender();
    }
    $searchArray = array();
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
      parse_str($_POST['searchParams'], $searchArray);

    $this->view->widgetId = $widgetId = (isset($_POST['widget_id']) ? $_POST['widget_id'] : $this->view->identity);
    $this->view->widgetName = 'my-transactions';
    $this->view->is_search = $is_search = !empty($_POST['is_search']) ? true : false;
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $this->view->params = $params = Engine_Api::_()->sescredit()->getWidgetParams($widgetId);

    if (isset($_GET['show']) && !empty($_GET['show']))
      $params['show'] = $_GET['show'];
    if (!empty($searchArray)) {
      foreach ($searchArray as $key => $search) {
        $params[$key] = $search;
      }
    }
    $currentTime = date('Y-m-d H:i:s');
    $creditValueTable = Engine_Api::_()->getDbTable('values', 'sescredit');
    $creditValueTableName = $creditValueTable->info('name');
    $creditTable = Engine_Api::_()->getDbTable('credits', 'sescredit');
    $creditTableName = $creditTable->info('name');
    $language = !empty($_COOKIE['en4_language']) ? $_COOKIE['en4_language'] : 'en';
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $languageColumn = $db->query('SHOW COLUMNS FROM engine4_sescredit_values LIKE "' . $language . '"')->fetch();
    if (empty($languageColumn)) {
      $language = 'en';
    }
    $select = $creditTable->select()
            ->setIntegrityCheck(false)
            ->from($creditTableName, array('*'))
            ->joinLeft($creditValueTableName, $creditValueTableName . '.type = ' . $creditTableName . '.type AND '.$creditValueTableName . '.member_level ='. $viewer->level_id, array('type', 'language' => new Zend_Db_Expr("Case when $language IS NULL or $language = '' then en else $language end")))
            ->where($creditTableName . '.owner_id =?', $viewer->getIdentity());
            //->where($creditValueTableName . '.member_level =?', $viewer->level_id);
    if (isset($params['show']) && $params['show'] == 'week') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 week"));
      $select->where("DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')");
    } elseif (isset($params['show']) && $params['show'] == 'today') {
      $select->where("$creditTableName.creation_date LIKE ?", date('Y-m-d') . "%");
    } elseif (isset($params['show']) && $params['show'] == 'month') {
      $endTime = date('Y-m-d H:i:s', strtotime("-1 month"));
      $select->where("DATE(" . $creditTableName . ".creation_date) between ('$endTime') and ('$currentTime')");
    }
    if (isset($params['point_type']) && $params['point_type']) {
      if ($params['point_type'] == 1)
        $select->where("$creditTableName.point_type =?", "credit");
      elseif ($params['point_type'] == 2)
        $select->where("$creditTableName.point_type =?", "deduction");
      elseif ($params['point_type'] == 3)
        $select->where("$creditTableName.point_type =?", "affiliate");
      elseif ($params['point_type'] == 4)
        $select->where("$creditTableName.point_type =?", "transfer_friend");
      elseif ($params['point_type'] == 8)
          $select->where("$creditTableName.point_type =?", "sesproduct_order");
      elseif ($params['point_type'] == 5)
        $select->where("$creditTableName.point_type =?", "receive_friend");
      elseif ($params['point_type'] == 6)
        $select->where("$creditTableName.point_type =?", "upgrade_level");
      elseif ($params['point_type'] == 7)
        $select->where("$creditTableName.point_type =?", "buy");
    }
    if (isset($params['show_date_field']) && !empty($params['show_date_field'])) {
      $explodeTime = explode('-', $params['show_date_field']);
      $startTime = $explodeTime[0];
      $endTime = $explodeTime[1];
      $select->where("DATE_FORMAT(" . $creditTableName . ".creation_date, '%Y-%m-%d') between ('" . date('Y-m-d', strtotime($startTime)) . "') and ('" . date('Y-m-d', strtotime($endTime)) . "')");
    }
    $select->order('credit_id DESC');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->page = $page;
    $paginator->setCurrentPageNumber($page);
    if ($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    }
  }

}
