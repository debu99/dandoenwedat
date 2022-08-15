<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_tagCloudController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $countItem = $this->_getParam('itemCountPerPage', '25');
    $this->view->height = $this->_getParam('height', '300');
    $this->view->color = $this->_getParam('color', '#00f');
    $this->view->textHeight = $this->_getParam('text_height', '15');

    $paginator = Engine_Api::_()->sesevent()->tagCloudItemCore('', array('type' => 'sesevent_event'));

    $this->view->paginator = $paginator;
    $paginator->setItemCountPerPage($countItem);
    $paginator->setCurrentPageNumber(1);
    // Do not render if nothing to show
    if ($paginator->getTotalItemCount() <= 0) {
      return $this->setNoRender();
    }
  }

}
