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

class Sescredit_Widget_BadgesController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewerId = $this->view->viewer()->getIdentity();
    $badgeType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.badge.type', 1);
    $userBadgeTable = Engine_Api::_()->getDbTable('userbadges', 'sescredit');
    $userBadgeTableName = $userBadgeTable->info('name');
    $badgeTable = Engine_Api::_()->getDbTable('badges', 'sescredit');
    $badgeTableName = $badgeTable->info('name');
    $select = $badgeTable->select()
            ->setIntegrityCheck(false)
            ->from($badgeTableName, array('badge_id', 'title', 'description', 'photo_id'))
            ->join($userBadgeTableName, $userBadgeTableName . '.badge_id =' . $badgeTableName . '.badge_id', array('user_id', 'active'))
            ->where($userBadgeTableName . '.user_id =?', $viewerId);
    if(!$badgeType) {
      $select->where('engine4_sescredit_badges.credit_value <= ?',new Zend_Db_Expr('(SELECT credit_value from '.$userBadgeTableName.' Left Join engine4_sescredit_badges on engine4_sescredit_badges.badge_id = '.$userBadgeTableName.'.badge_id  where '.$userBadgeTableName.'.active = 1)'))->order('credit_value DESC');
    }
    $this->view->badges = $badge = $badgeTable->fetchAll($select);

    $select = $badgeTable->select()
            ->from($badgeTableName, array('badge_id', 'title', 'description', 'photo_id', 'countMember' => new Zend_Db_Expr('(SELECT COUNT(*) from '.$userBadgeTableName.' where badge_id = '.$badgeTableName.'.badge_id and active = 1)')))
            ->where('enabled =?', 1);
    $this->view->allBadges = $badgeTable->fetchAll($select);
  }

}
