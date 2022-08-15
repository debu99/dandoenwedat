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

class Sescredit_Widget_BrowseTopMembersController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->widgetId = $widgetId = (isset($_POST['widget_id']) ? $_POST['widget_id'] : $this->view->identity);
    $this->view->widgetName = 'browse-top-members';
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $this->view->limit = $limit = isset($_POST['limit']) ? $_POST['limit'] : $this->_getParam('limit');
    $this->view->rank = isset($_POST['rank']) ? $_POST['rank'] : 1;
    $this->view->followButton = isset($_POST['followButton']) ? $_POST['followButton'] : $this->_getParam('followButton');
    $this->view->friendButton = isset($_POST['friendButton']) ? $_POST['friendButton'] : $this->_getParam('friendButton');

    $creditDetailsTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $creditDetailsTableName = $creditDetailsTable->info('name');
    $userBadgeTable = Engine_Api::_()->getDbTable('userbadges', 'sescredit');
    $userBadgeTableName = $userBadgeTable->info('name');
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $select = $userTable->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('displayname', 'user_id', 'photo_id', 'badgeCount' => new Zend_Db_Expr('(SELECT COUNT(' . $userBadgeTableName . '.user_id) from ' . $userBadgeTableName . ' where user_id =' . $userTableName . '.user_id group by user_id)')))
            ->joinRight($creditDetailsTableName, $creditDetailsTableName . '.owner_id =' . $userTableName . '.user_id', array('total_credit'))
            ->order("CAST(total_credit as SIGNED INTEGER) DESC");
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($limit);
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->page = $page;
    $paginator->setCurrentPageNumber($page);
    if ($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    }
    if ($paginator->getTotalItemCount() < 1)
      return $this->setNoRender();
  }

}
