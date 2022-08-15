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
class Sesevent_Widget_BrowseSearchController extends Engine_Content_Widget_Abstract {
  public function indexAction() {  
    $viewer = Engine_Api::_()->user()->getViewer();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $p = $request->getParams();
    $filter = !empty($p['filter']) ? $p['filter'] : 'future';
    if ($filter != 'past' && $filter != 'future')
      $filter = 'future';
    // Create form
    $default_search_type = $this-> _getParam('default_search_type', 'like_count DESC');
		$search_type = $this-> _getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','featured' => 'Featured','sponsored' => 'Sponsored','verified'=>'Verified', 'mostSPrated'=>'Most Rated','mostSPfavourite'=>'Most Favourite','mostSPjoined'=>'Most Joined','starttime'=>'Start Time'));
		if(count($search_type))
			$browseBy =	$this->_getParam('browse_by', 'yes');
		else
			$browseBy='no';
		$arrayView = array('0' => 'Everyone\'s Events','1' => 'Only My Friend\'s Events','ongoing' => 'Ongoing Events','past' => 'Past Events','week' => 'This Week','weekend' => 'This Weekends','future' => 'Upcomming Events','month' => 'This Month','ongoingSPupcomming'=>'Ongoing & Upcomming');
		$defaultView = array('0','1','ongoing' ,'past','week','weekend','future','month');
		$friend_type = $this-> _getParam('view', $defaultView);
		if(Engine_Api::_()->user()->getViewer()->getIdentity() == 0)
			unset($friend_type['1']);
		if(count($friend_type))
			$friendOnly =	$this->_getParam('friend_show', 'yes');
		else
			$friendOnly='no';
    $this->view->view_type = $this->_getParam('view_type', 'horizontal');
		if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)){
			$location = 'yes';	
		}else
			$location = 'no';
    $this->view->form = $formFilter = new Sesevent_Form_Filter_Browse(array('friendType' =>$friend_type,'searchType' =>$search_type,'locationSearch' => $location,'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'), 'categoriesSearch' => $this->_getParam('categories', 'yes'),'browseBy' => $browseBy, 'searchTitle' => $this->_getParam('search_title'),'FriendsSearch'=>$friendOnly,'citySearch' => $this->_getParam('city', 'yes'),'stateSearch' => $this->_getParam('state', 'yes'),'zipSearch' => $this->_getParam('zip', 'yes'),'countrySearch' => $this->_getParam('country', 'yes'),'venueSearch' => $this->_getParam('venue', 'yes'),'startDate' => $this->_getParam('start_date', 'yes'),'endDate' => $this->_getParam('end_date', 'yes'),'alphabetSearch' => $this->_getParam('alphabet', 'yes'),));
		$urlParams = array();
		foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey=>$urlParamsVal){
			if($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
				continue;
			$urlParams[$urlParamsKey] = $urlParamsVal;
		}
		$formFilter->populate($urlParams);
		if(!count($friend_type))
			$formFilter->removeElement('view');
    else if($formFilter->view){
			$viewArray = array();
			foreach($friend_type as $val)	{
				$viewArray[$val] = $arrayView[$val];
			}
			if(count($viewArray))
			$formFilter->view->setMultiOptions($viewArray);
		}
    // Populate options
    if (isset($formFilter->category_id) && count($formFilter->category_id->getMultiOptions()) <= 1)
      $formFilter->removeElement('category_id');
    
   	if(isset($_GET['order'])){
			 if($formFilter->order)
    		 $formFilter->order->setValue($_GET['order']);
		}else{
			if($formFilter->order)
    		 $formFilter->order->setValue($default_search_type);
		}
		$advancedSettingBtn = $this->_getParam('show_advanced_search','1');
    if(!$advancedSettingBtn){
			$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
			$formFilter->removeElement("advanced_options_search_".$view->identity);
		}
  }
}