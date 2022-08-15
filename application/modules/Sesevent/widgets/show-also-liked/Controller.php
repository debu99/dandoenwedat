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
class Sesevent_Widget_ShowAlsoLikedController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //Check subject
    if (!Engine_Api::_()->core()->hasSubject('sesevent_event'))
      return $this->setNoRender();

    if (Engine_Api::_()->core()->hasSubject('sesevent_event'))
      $subject = Engine_Api::_()->core()->getSubject('sesevent_event');

    //Set default title
    if (!$this->getElement()->getTitle())
      $this->getElement()->setTitle('People Also Liked');

    $this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);
    $this->view->order = $order = isset($params['order']) ? $params['order'] : $this->_getParam('order', '');   
    $this->view->height = $this->_getParam('height', '180');
    $this->view->width = $this->_getParam('width', '180');
    $this->view->title_truncation_list = $this->_getParam('list_truncation_list', '45');
    $this->view->title_truncation_grid = $this->_getParam('grid_truncation_grid', '45');
     $this->view->view_type = $this->_getParam('viewType', 'list');
		$this->view->gridInsideOutside = $this->_getParam('gridInsideOutside', 'in');
		$this->view->mouseOver = $this->_getParam('mouseOver', 'over');
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    $show_criterias = isset($values['show_criterias']) ? $values['show_criterias'] : $this->_getParam('show_criteria', array('title', 'by', 'like', 'comment', 'view', 'favourite', 'featuredLabel', 'sponsoredLabel', 'verifiedLabel', 'favouriteButton', 'likeButton', 'socialSharing', 'location', 'startenddate', 'category'));
    foreach ($show_criterias as $show_criteria)
      $this->view->{$show_criteria . 'Active'} = $show_criteria;

    $values['widgetName'] = 'Also Liked';
    $values['poster_id'] = $subject->getIdentity();
    $values['fetchAll'] = true;
    $values['limit'] = $this->_getParam('limit_data', 5);
    $values['order'] = $order;
    $this->view->results = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventSelect($values);
    if (count($this->view->results) <= 0)
      return $this->setNoRender();
  }

}
