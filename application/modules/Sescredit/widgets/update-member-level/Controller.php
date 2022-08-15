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

class Sescredit_Widget_UpdateMemberlevelController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if (!$viewerId || ($viewer->level_id == 1))
      return $this->setNoRender();
    $this->view->label = Engine_Api::_()->getItem('authorization_level', $viewer->level_id)->title;
    $this->view->status = Engine_Api::_()->getDbTable('upgradeusers', 'sescredit')->isSentUpgradeRequest($viewerId);
  }

}
