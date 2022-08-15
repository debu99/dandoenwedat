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

class Sescredit_Widget_MyPointsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $viewerId = $this->view->viewer()->getIdentity();
    if (!$viewerId)
      return $this->setNoRender();
    $creditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $this->view->firstActivityDate = $creditDetailTable->select()
            ->from($creditDetailTable->info('name'), 'first_activity_date')
            ->where('owner_id =?', $viewerId)
            ->query()
            ->fetchColumn();
  }

}
