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

class Sescredit_Widget_InviteFriendsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewer = $this->view->viewer();
    $viewerId = $viewer->getIdentity();
    $enableSignupReferral = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.affiliateforsingup', 1);
    if (!$viewerId || !$enableSignupReferral)
      return $this->setNoRender();
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $affiliateTable = Engine_Api::_()->getDbTable('affiliates', 'sescredit');
    $select = $affiliateTable->select()
            ->from($affiliateTable->info('name'), array('*'))
            ->where('user_id =?', $viewerId);
    $affiliate = $affiliateTable->fetchRow($select);
    if (!$affiliate) {
      $email = $viewer->email;
      do {
        $affiliateCode = substr(md5(rand(0, 999) . $email), 10, 7);
      } while (null !== $affiliateTable->fetchRow(array('affiliate = ?' => $affiliateCode)));
      $row = $affiliateTable->createRow();
      $row->user_id = $viewerId;
      $row->affiliate = $affiliateCode;
      $row->save();
      $this->view->affiliate = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
                  'action' => 'signup',
                      ), 'sescredit_general', true)
              . '?affiliate=' . $row->affiliate;
    } else {
      $this->view->affiliate = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
                  'action' => 'signup',
                      ), 'sescredit_general', true)
              . '?affiliate=' . $affiliate->affiliate;
    }
  }

}
