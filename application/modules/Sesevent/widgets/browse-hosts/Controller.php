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
class Sesevent_Widget_BrowseHostsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    if (isset($_POST['params']))
      $params = json_decode($_POST['params'],true);
		if (isset($_POST['searchparams']) && $_POST['searchparams'])
    parse_str($_POST['searchparams'], $searchArray);

    $values = array();
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->view->viewmore = $this->_getParam('viewmore', 0);
    $this->view->paginationType = $values['paginationType'] = isset($params['paginationType']) ? $params['paginationType'] : $this->_getParam('paginationType', 1);
		$this->view->list_count = $values['list_count'] =  isset($params['list_count']) ? $params['list_count'] : $this->_getParam('list_count', '1'); 
    $this->view->contentInsideOutside = $values['contentInsideOutside'] =  isset($params['contentInsideOutside']) ? $params['contentInsideOutside'] : $this->_getParam('contentInsideOutside', 'in');
    $this->view->title_truncation_grid = $values['title_truncation'] = isset($params['title_truncation']) ? $params['title_truncation'] : $this->_getParam('title_truncation', '45'); 
    $this->view->mouseOver = $values['mouseOver'] =  isset($params['mouseOver']) ? $params['mouseOver'] : $this->_getParam('mouseOver', 'in'); 
    $this->view->width = $values['width'] = isset($params['width']) ? $params['width'] : $this->_getParam('width', 200);
    $this->view->height = $values['height'] = isset($params['height']) ? $params['height'] : $this->_getParam('height', 200);
    
    $this->view->socialshare_enable_plusicon = $values['socialshare_enable_plusicon'] = isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $values['socialshare_icon_limit'] = isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);
    
    $this->view->heightblock = $values['heightblock'] = isset($params['heightblock']) ? $params['heightblock'] : $this->_getParam('heightblock', 200);
    $show_criterias = $values['information'] = isset($params['information']) ? $params['information'] : $this->_getParam('information', array('featured', 'sponsored', 'displayname', 'email', 'phone', 'location', 'website', 'facebook', 'linkdin', 'twitter', 'googleplus'));
    if($show_criterias) {
	    foreach ($show_criterias as $show_criteria)
	      $this->view->{$show_criteria . 'Active'} = $show_criteria;
    }
      
    $values['itemCount'] = $itemCount = isset($params['itemCount']) ? $params['itemCount'] : $this->_getParam('itemCount', 20);
    $values['popularity'] = isset($_GET['popularity']) ? $_GET['popularity'] : (isset($searchArray['popularity']) ? $searchArray['popularity'] : (isset($params['popularity']) ? $params['popularity'] : $this->_getParam('popularity', 'most_event')));
    $values['name'] = isset($_GET['title_name']) ? $_GET['title_name'] : (isset($searchArray['title_name']) ? $searchArray['title_name'] : (isset($params['title_name']) ? $params['title_name'] : ''));
    $values['alphabet'] = isset($_GET['alphabet']) ? $_GET['alphabet'] : (isset($params['alphabet']) ? $params['alphabet'] : '');
    $values['widgteName'] = isset($_GET['widgteName']) ? $_GET['widgteName'] : (isset($params['widgteName']) ? $params['widgteName'] : 'Browse Hosts');

    $this->view->all_params = $values;
    if ($this->view->viewmore)
      $this->getElement()->removeDecorator('Container');

    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getHostsPaginator($values);
    $paginator->setItemCountPerPage($itemCount);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $this->view->count = $paginator->getTotalItemCount();
  }

}
