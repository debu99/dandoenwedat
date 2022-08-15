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

class Sescredit_Widget_MyBadgeController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewerId = $this->view->viewer()->getIdentity();
    $isBadgeEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.enablebadge', 1);
    if (!$viewerId || !$isBadgeEnable)
      return $this->setNoRender();
    $userBadgeTable = Engine_Api::_()->getDbTable('userbadges', 'sescredit');
    $userBadgeTableName = $userBadgeTable->info('name');
    $badgeTable = Engine_Api::_()->getDbTable('badges', 'sescredit');
    $badgeTableName = $badgeTable->info('name');
    $select = $badgeTable->select()
            ->setIntegrityCheck(false)
            ->from($badgeTableName, array('badge_id', 'title', 'description', 'photo_id'))
            ->join($userBadgeTableName, $userBadgeTableName . '.badge_id =' . $badgeTableName . '.badge_id', 'user_id')
            ->where($userBadgeTableName . '.user_id =?', $viewerId)
            ->where($userBadgeTableName . '.active =?', 1);
    $this->view->badge = $badge = $badgeTable->fetchRow($select);
    if (empty($badge))
      return $this->setNoRender();
  }

}
