<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesblog
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_BrowseReviewSearchController extends Engine_Content_Widget_Abstract {
  public function indexAction(){
    // Create form
    $isWidget = $this->_getParam('isWidget', 0);
    $filterOptions = (array)$this->_getParam('view', array('likeSPcount' => 'Most Liked', 'viewSPcount' => 'Most Viewed', 'commentSPcount' => 'Most Commented', 'mostSPrated' => 'Most Rated'));
    $this->view->view_type = $view_type = isset($_POST['view_type']) ? $_POST['view_type'] : $this->_getParam('view_type', 'vertical');
    $this->view->subject_id = $this->_getParam('blog_id', 0);
    $this->view->widgetIdentity = $this->_getParam('widgetIdentity', 0);
    $reviewSearch = $this->_getParam('review_search', 1);
    $this->view->form = $formFilter = new Sesevent_Form_Review_Browse(array('reviewTitle' => $this->_getParam('review_title', 1), 'reviewSearch' => $reviewSearch, 'reviewStars' => $this->_getParam('review_stars', 1), 'reviewRecommended' => $this->_getParam('review_recommendation', 1)));
   if($reviewSearch){
		$arrayOptions = $filterOptions;
		$filterOptions = array();
		foreach ($arrayOptions as $key=>$filterOption) {
      $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
      $filterOptions[$filterOption] = ucwords($value);
    }
		 $filterOptions = array(''=>'')+$filterOptions;
		 $formFilter->order->setMultiOptions($filterOptions);
	 }	
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
      if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
      $urlParams[$urlParamsKey] = $urlParamsVal;
    }
    $formFilter->populate($urlParams);
    if ($isWidget) {
      $this->getElement()->removeDecorator('Container');
      $formFilter->setAttrib('class', '');
    }
  }
}