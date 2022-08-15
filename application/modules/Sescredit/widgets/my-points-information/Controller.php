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

class Sescredit_Widget_MyPointsInformationController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
    $badges = Engine_Api::_()->getDbTable('badges', 'sescredit')->badges();
    if (!$viewerId || (count($badges) < 1))
      return $this->setNoRender();
    $this->view->currentBadge = $curentBadge = Engine_Api::_()->getDbTable('userbadges', 'sescredit')->getBadge(array('user_id' => $viewerId));
    $this->view->currentPoint = $currentPoint = Engine_Api::_()->getDbTable('details', 'sescredit')->getCurrentUserPoint(array('owner_id' => $viewerId));
    $this->view->nextBadge = $nextBadge = Engine_Api::_()->getDbTable('badges', 'sescredit')->nextBadge(array('point' => $currentPoint));
    if ((empty($curentBadge) && empty($nextBadge) || (!$currentPoint))) {
      return $this->setNoRender();
    }
  }

}
