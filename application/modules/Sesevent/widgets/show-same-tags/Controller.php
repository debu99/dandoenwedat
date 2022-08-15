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
class Sesevent_Widget_ShowSameTagsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    if (!Engine_Api::_()->core()->hasSubject('sesevent_event'))
      return $this->setNoRender();

    if (Engine_Api::_()->core()->hasSubject('sesevent_event'))
      $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('sesevent_event');

    //Set default title
    if (!$this->getElement()->getTitle())
      $this->getElement()->setTitle('Similar ' . ucwords(str_replace('sesevent_', '', $subject->getType())));

    $this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);
    
    $values['limit'] = $this->_getParam('limit_data', 5);
    $this->view->order = $order = isset($params['order']) ? $params['order'] : $this->_getParam('order', '');  
    $this->view->height = $this->_getParam('height', '180');
    $this->view->width = $this->_getParam('width', '180');
    $this->view->title_truncation_list = $this->_getParam('list_title_truncation', '45');
    $this->view->title_truncation_grid = $this->_getParam('grid_title_truncation', '45');
    $this->view->view_type = $this->_getParam('viewType', 'list');
		$this->view->gridInsideOutside = $this->_getParam('gridInsideOutside', 'in');
		$this->view->mouseOver = $this->_getParam('mouseOver', 'over');
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    $show_criterias = isset($values['show_criterias']) ? $values['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'by', 'title', 'socialSharing', 'view', 'featuredLabel', 'sponsoredLabel', 'verifiedLabel', 'likeButton','rating'));
    foreach ($show_criterias as $show_criteria)
      $this->view->{$show_criteria . 'Active'} = $show_criteria;

    //Get tags for this event
    $tagMapsTable = Engine_Api::_()->getDbtable('tagMaps', 'core');

    //Get tags
    $tags = $tagMapsTable->select()
            ->from($tagMapsTable, 'tag_id')
            ->where('resource_type = ?', $subject->getType())
            ->where('resource_id = ?', $subject->getIdentity())
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);
    //No tags
    if (empty($tags))
      return $this->setNoRender();

    $values['sameTagresource_id'] = $subject->getIdentity();
    $values['sameTagTag_id'] = $tags;
    $values['sameTag'] = 'sameTag';
    $values['fetchAll'] = true;
    $values['order'] = $order;
    $this->view->results = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventSelect($values);
    if (count($this->view->results) <= 0)
      return $this->setNoRender();
  }

}
