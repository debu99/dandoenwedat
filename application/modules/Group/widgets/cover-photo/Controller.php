<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Widget_CoverPhotoController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    if (Engine_Api::_()->core()->hasSubject('group')) {
      $this->view->group = $group = Engine_Api::_()->core()->getSubject('group');
    }

    if (empty($group) || !$group->getIdentity()) {
      return $this->setNoRender();
    }

    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $this->view->photo = '';
    if (isset($group->coverphoto)) {
      $this->view->photo = $photo = Engine_Api::_()->getItem('group_photo', $group->coverphoto);
    }
    $this->view->level_id = 0;
    $this->view->uploadDefaultCover = 0;

    if ($viewer->getIdentity() && $viewer->level_id == 1 && $group->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = isset($params['uploadDefaultCover']) ? $params['uploadDefaultCover'] : 0;
      $this->view->level_id = isset($params['level_id']) ? $params['level_id'] : 0;
    }

    if ($this->view->uploadDefaultCover) {
      $levelsAssoc = Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc();
      $this->view->defaultCoverMessage = Zend_Registry::get('Zend_Translate')->_(sprintf(
        'Here, you can upload and set default group cover photo for ' . $levelsAssoc[$this->view->level_id] .
          '. %s to go to group\'s profile page.',
        "<a href='". $group->getHref() . "'>Click here</a>"
      ));
    }

    $can_edit = $group->authorization()->isAllowed($viewer, 'edit');
    if ($can_edit && Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'coverphotoupload')) {
      $this->view->can_edit = 1;
    } else {
      $this->view->can_edit = 0;
    }
  }

}
