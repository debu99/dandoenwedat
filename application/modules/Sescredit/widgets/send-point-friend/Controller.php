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

class Sescredit_Widget_SendPointFriendController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewer = $this->view->viewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId)
      return $this->setNoRender();
    $this->view->form = $form = new Sescredit_Form_SendPoint();
    if (empty($_POST))
      return;
    $message = '';
    if (isset($_POST['friend_user_id']) && empty($_POST['friend_user_id'])) {
      $message = $this->view->translate("Please enter your friend name.");
    }
    $point = $_POST['send_credit_value'];
    $userCreditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $totalCredit = $userCreditDetailTable->select()
            ->from($userCreditDetailTable->info('name'), 'total_credit')
            ->where('owner_id =?', $viewerId)
            ->query()
            ->fetchColumn();
    if ($totalCredit) {
      if ($totalCredit < $point) {
        $message = $this->view->translate("You don't have sufficient point to transfer.");
      }
      if (empty($message)) {
        $creditRoute = $this->view->url(array("action" => "transaction"), "sescredit_general", true);
        $receiver = Engine_Api::_()->getItem('user', $_POST['friend_user_id']);
        $creditPageLink = '<a href="' . $creditRoute . '">' . ucfirst($manageActions->name) . " Check Your Point" . '</a>';
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'receive_from_friend', 'owner_id' => $_POST['friend_user_id'], 'action_id' => 0, 'object_id' => 0, 'point' => $point, 'point_type' => 'receive_friend'));
        Engine_Api::_()->getDbTable('credits', 'sescredit')->insertUpdatePoint(array('type' => 'transfer_to_friend', 'owner_id' => $viewerId, 'action_id' => 0, 'object_id' => 0, 'point' => $point, 'point_type' => 'transfer_friend'));
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($receiver, $viewer, $viewer, 'notify_sescredit_send_point', array("point" => $point, "creditPageLink" => $creditPageLink));
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiver, 'sescredit_send_point', array('sender_title' => $viewer->displayname, 'point' => $point));
      }
    } else {
      $message = $this->view->translate("You don't have point to transfer.");
    }
    echo json_encode(array('message' => $message, 'status' => !empty($message) ? 0 : 1));
    die;
  }

}
