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
class Sesevent_Widget_EventCategoryIconsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->height = $this->_getParam('height', '200');
    $this->view->width = $this->_getParam('width', '200');
    $this->view->alignContent = $this->_getParam('alignContent', 'center');
		$this->view->titleC = $this->_getParam('titleC','What are you in the mood for?');
    $params['criteria'] = $this->_getParam('criteria', '');
    $this->view->order = $order = isset($params['order']) ? $params['order'] : $this->_getParam('order', '');  
    
    $show_criterias = $this->_getParam('show_criteria', array('title', 'countEvents', 'icon'));
    
    if (in_array('countEvents', $show_criterias) || $params['criteria'] == 'most_event')
      $params['countEvents'] = true;

    foreach ($show_criterias as $show_criteria)
      $this->view->$show_criteria = $show_criteria;
		$params['limit'] = $this->_getParam('limit_data',10);
    // Get events category
    $params['order'] = $order;
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('categories', 'sesevent')->getCategory($params);

    if (count($paginator) == 0)
      return;
  }

}
