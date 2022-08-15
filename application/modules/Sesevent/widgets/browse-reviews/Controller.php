<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_BrowseReviewsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    if (isset($_POST['params']))
      $params = json_decode($_POST['params'], true);
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
      parse_str($_POST['searchParams'], $searchArray);
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $this->view->page = $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->limit = $limit = isset($_POST['limit']) ? $_POST['limit'] : $this->_getParam('limit_data', 10);
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $identity = $view->identity;
    $this->view->widgetId = $widgetId = isset($_POST['widgetId']) ? $_POST['widgetId'] : $identity;

    $this->view->loadOptionData = $loadOptionData = isset($_POST['loadOptionData']) ? $_POST['loadOptionData'] : $this->_getParam('pagging', 'auto_load');

   // $show_criterias = $this->_getParam('show_criteria', array('likemainButton', 'featuredLabel', 'verifiedLabel'));
   // foreach ($show_criterias as $show_criteria)
   // $this->view->{$show_criteria . 'Active'} = $show_criteria;

    $value['search_text'] = isset($searchArray['search_text']) ? $searchArray['search_text'] : (isset($_GET['search_text']) ? $_GET['search_text'] : (isset($params['search_text']) ? $params['search_text'] : ''));
    $value['order'] = isset($searchArray['order']) ? $searchArray['order'] : (isset($_GET['order']) ? $_GET['order'] : (isset($params['order']) ? $params['order'] : ''));
    $value['review_stars'] = isset($searchArray['review_stars']) ? $searchArray['review_stars'] : (isset($_GET['review_stars']) ? $_GET['review_stars'] : (isset($params['review_stars']) ? $params['review_stars'] : ''));
    $value['review_recommended'] = isset($searchArray['review_recommended']) ? $searchArray['review_recommended'] : (isset($_GET['review_recommended']) ? $_GET['review_recommended'] : (isset($params['review_recommended']) ? $params['review_recommended'] : ''));
    $this->view->stats = isset($params) ? $params : $this->_getParam('stats', array('featured', 'sponsored', 'likeCount', 'commentCount', 'viewCount', 'title', 'postedBy', 'pros', 'cons', 'description', 'creationDate', 'recommended', 'parameter', 'rating', 'likeButton', 'socialSharing'));
		
    $this->view->params = array('stats' => $this->view->stats, 'search_text' => $value['search_text'], 'order' => $value['order'], 'review_stars' => $value['review_stars'], 'review_recommended' => $value['review_recommended']);

    $table = Engine_Api::_()->getDbTable('eventreviews','seseventreview');
    $params = array('search_text' => $value['search_text'], 'info' => str_replace('SP', '_', $value['order']), 'review_stars' => $value['review_stars'], 'review_recommended' => $value['review_recommended']);

    $select = $table->getEventReviewSelect($params);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    //Set item count per page and current page number
    $paginator->setItemCountPerPage($limit);
    $paginator->setCurrentPageNumber($page);
    if ($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    }
  }

}
