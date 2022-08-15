<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AjaxController.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_AjaxController extends Core_Controller_Action_Standard {

  public function getFriendAction() {
    $sesdata = array();
    $viewerId = $this->view->viewer()->getIdentity();
    $search = $this->_getParam('text', null);
    $subject = Engine_Api::_()->getItem('user', $viewerId);
    $userTable = Engine_Api::_()->getItemTable('user');
    $select = $subject->membership()->getMembersObjectSelect();
    $select->where('displayname LIKE ?', '%' . $search . '%');
    $friends = $userTable->fetchAll($select);
    foreach ($friends as $friend) {
      $userIcon = $this->view->itemPhoto($friend, 'thumb.icon');
      $sesdata[] = array(
          'id' => $friend->user_id,
          'label' => $friend->displayname,
          'photo' => $userIcon
      );
    }
    return $this->_helper->json($sesdata);
  }

}
