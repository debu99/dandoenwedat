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

class Sescredit_Widget_TopPointReceiverMembersController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $creditDetailsTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $creditDetailsTableName = $creditDetailsTable->info('name');
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $select = $userTable->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('displayname', 'user_id', 'photo_id'))
            ->join($creditDetailsTableName, $creditDetailsTableName . '.owner_id =' . $userTableName . '.user_id', array('total_credit'))
            ->order("CAST(total_credit as SIGNED INTEGER) DESC")
            ->order('Rand()')
            ->limit($this->_getParam('limit',5));
    $this->view->topMembers = $members = $userTable->fetchAll($select);
    if (count($members) < 1)
      return $this->setNoRender();
  }

}
