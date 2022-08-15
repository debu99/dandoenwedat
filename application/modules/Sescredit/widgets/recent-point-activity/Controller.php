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

class Sescredit_Widget_RecentPointActivityController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewer = $this->view->viewer();
    $viewerId = $this->view->viewer()->getIdentity();
    if (!$viewerId)
      return $this->setNoRender();
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
            ->joinLeft($creditValueTableName, $creditValueTableName . '.type = ' . $creditTableName . '.type', array('type', 'language' => new Zend_Db_Expr("Case when $language IS NULL or $language = '' then en else $language end")))
            ->where($creditTableName . '.owner_id =?', $viewerId)
            ->where($creditValueTableName . '.member_level =?', $viewer->level_id)
            ->limit($this->_getParam('limit', 5))
            ->order($creditTableName . '.creation_date DESC');
    $this->view->activities = $activites = $creditTable->fetchAll($select);
    if (count($activites) < 1)
      return $this->setNoRender();
  }

}
