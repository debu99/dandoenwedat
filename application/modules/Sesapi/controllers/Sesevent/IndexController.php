<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesevent_IndexController extends Sesapi_Controller_Action_Standard
{
    public function init()
    {
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $id = $this->_getParam('event_id', $this->_getParam('id', null));
        $host_id = $this->_getParam('host_id', null);
        $review_id = $this->_getParam('review_id', null);

        if ($id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $id);
            if ($event) {
                Engine_Api::_()->core()->setSubject($event);
            }
        } else if ($host_id) {
            $host = Engine_Api::_()->getItem('sesevent_host', $host_id);
            if ($host) {
                Engine_Api::_()->core()->setSubject($host);
            }
        } else if (0 !== ($topic_id = (int)$this->_getParam('topic_id'))) {
            $topic = Engine_Api::_()->getItem('sesevent_topic', $topic_id);
            if ($topic)
                Engine_Api::_()->core()->setSubject($topic);
            else
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        } else if ($review_id) {
            $review = Engine_Api::_()->getItem('seseventreview_review', $review_id);
            Engine_Api::_()->core()->setSubject($review);
        }
    }
    public function browseAction(){
        if (isset($_POST['params']))
            $params = json_decode($_POST['params'], true);
        if (isset($_POST['searchParams']) && $_POST['searchParams'])
            parse_str($_POST['searchParams'], $searchArray);
        $viewer = Engine_Api::_()->user()->getViewer();
        $identity = Engine_Api::_()->sesevent()->getIdentityWidget('sesevent.browse-events', 'widget', 'sesevent_index_browse');
        if ($identity) {
            $cookiedata = Engine_Api::_()->sesbasic()->getUserLocationBasedCookieData();
            if (!empty($cookiedata['location'])) {
                $params['location'] = $cookiedata['location'];
                $params['lat'] = $cookiedata['lat'];
                $params['lng'] = $cookiedata['lng'];
                $params['miles'] = 1000;
            }
        }
        if (!empty($_POST['location'])) {
            $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
            if ($latlng) {
                $_POST['lat'] = $latlng['lat'];
                $_POST['lng'] = $latlng['lng'];
            }
        }
        $friend_type = $this->_getParam('view', null);
        if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0)
            unset($friend_type);
        if (count($friend_type))
            $friendOnly = $this->_getParam('friend_show', 'yes');
        else
            $friendOnly = 'no';
        $search_type = $this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created', 'mostSPviewed' => 'Most Viewed', 'mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented', 'featured' => 'Featured', 'sponsored' => 'Sponsored', 'verified' => 'Verified', 'mostSPrated' => 'Most Rated', 'mostSPfavourite' => 'Most Favourite', 'mostSPjoined' => 'Most Joined', 'starttime' => 'Start Time'));
        if (count($search_type))
            $browseBy = $this->_getParam('browse_by', 'yes');
        else
            $browseBy = 'no';
        $location = $this->_getParam('location');
        $form = new Sesevent_Form_Filter_Browse(array('friendType' => $friend_type, 'searchType' => $search_type, 'locationSearch' => $location, 'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'), 'categoriesSearch' => $this->_getParam('categories', 'yes'), 'browseBy' => 'yes', 'searchTitle' => $this->_getParam('search_title'), 'FriendsSearch' => $friendOnly, 'citySearch' => $this->_getParam('city', 'yes'), 'stateSearch' => $this->_getParam('state', 'yes'), 'zipSearch' => $this->_getParam('zip', 'yes'), 'countrySearch' => $this->_getParam('country', 'yes'), 'venueSearch' => $this->_getParam('venue', 'yes'), 'startDate' => $this->_getParam('start_date', 'yes'), 'endDate' => $this->_getParam('end_date', 'yes'), 'alphabetSearch' => $this->_getParam('alphabet', 'yes'),));
        $form->populate($_POST);
        $params = $form->getValues();
        $value = array();
        $value['status'] = 1;
        $value['search'] = 1;
        $value['draft'] = "1";
        $order = $this->_getParam('order');
        $filterby = $this->_getparam('filter', null);
        if ($filterby) {
            $params['order'] = $filterby;
        }
        //$params['order'] = $filterby;
        if(!empty($order)){
            $params['order'] = $order;
            $params['customSearchCriteria']	= $order;
            if(strtolower($order) == 'most joined'){
                unset($params['customSearchCriteria']);
                $params['most_joined_event'] = true;
            }
            if(strtolower($order) == "ongoing upcomming"){
                unset($params['customSearchCriteria']);
                $params['order'] = $defaultOrder = 'ongoingSPupcomming';
            }
        }
        if (isset($params['search_text']))
            $params['text'] = ($params['search_text']);
        $params['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
        $params['owner'] = $this->_getParam('owner', null);
		$params['alphabet'] = $this->_getParam('alphabet', null);
		$params['end_date'] = $this->_getParam('end_date', null);
        $params = array_merge($params, $value);
        $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $editdelete = 0;
        if($this->getevents($paginator, $editdelete))
          $result['events'] = $this->getevents($paginator, $editdelete);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getevents($paginator, $editdelete){
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        foreach ($paginator as $events) {
            $event = $events->toArray();
            $result[$counter] = $event;
            $result[$counter]['owner_title'] = $events->getOwner()->getTitle();
            if ($editdelete == 1) {
                $optionCounter = 0;
                $editList = false;

                if ($events->authorization()->isAllowed($viewer, 'edit')) {
                    $result[$counter]['options'][$optionCounter]['name'] = 'edit';
                    $result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Edit');
                    $optionCounter++;
                }
                if ($events->authorization()->isAllowed($viewer, 'delete')) {
                    $result[$counter]['options'][$optionCounter]['name'] = 'delete';
                    $result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Delete');
                }
            }
            if ($events->category_id) {
                $category = Engine_Api::_()->getItem('sesevent_category', $events->category_id);
                if ($category) {
                    $result[$counter]['category_title'] = $category->category_name;
                    if ($events->subcat_id) {
                        $subcat = Engine_Api::_()->getItem('sesevent_category', $events->subcat_id);
                        if ($subcat) {
                            $result[$counter]['subcategory_title'] = $subcat->category_name;
                            if ($events->subsubcat_id) {
                                $subsubcat = Engine_Api::_()->getItem('sesevent_category', $events->subsubcat_id);
                                if ($subsubcat) {
                                    $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                                }
                            }
                        }
                    }
                }
            }
            $tags = array();
            foreach ($events->tags()->getTagMaps() as $tagmap) {
               try{
                 $arrayTag = $tagmap->toArray();
                 if(!$tagmap->getTag())
                  continue;
                $tags[] = array_merge($tagmap->toArray(), array(
                    'id' => $tagmap->getIdentity(),
                    'text' => $tagmap->getTitle(),
                    'href' => $tagmap->getHref(),
                    'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                ));
               }catch(Exception $e){ // silence
               }
            }
            if (count($tags)) {
                $result[$counter]['tag'] = $tags;
            }
            $host = Engine_Api::_()->getItem('sesevent_host', $events->host);

            if ($host) {
                $result[$counter]['event_host']['host_title'] = $host->getTitle();
                $href = $host->getHref();
                $imagepath = $host->getPhotoUrl('thumb.profile');
                $result[$counter]['event_host']['host_image'] = $this->getBaseUrl(false, $imagepath);
            }
            $locationActive = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1);
            if (!$locationActive) {
                //unset($event['lat']);
                //unset($event['long']);
                unset($result[$counter]['location']);
            }
            $defaultParams = array();
            $defaultParams['isSesapi'] = 1;
            $defaultParams['starttime'] = true;
            $defaultParams['endtime'] = true;
            $defaultParams['timezone'] = true;
            $strtime = $this->view->eventStartEndDates($events, $defaultParams);
            list($result[$counter]['calanderStartTime'], $result[$counter]['calanderEndTime']) = explode('ENDDATE', strip_tags($strtime));
            $canaddtolist = $viewerId ? Engine_Api::_()->authorization()->getPermission($levelId, 'sesevent_event', 'addlist_event') : 0;
            if ($canaddtolist) {
                $result[$counter]['can_add_to_list'] = $canaddtolist;
            } else {
                $result[$counter]['can_add_to_list'] = $canaddtolist;
            }
            if ($viewerId != 0) {
                $result[$counter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($events);
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
                    $result[$counter]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($events, 'favourites', 'sesevent', 'sesevent_event', 'user_id');
                }
                $result[$counter]['is_content_follow'] = Engine_Api::_()->sesapi()->contentFollow($events, 'follows', 'sesevent', 'sesevent_event', 'poster_id');
            }else{
              unset($result[$counter]['like_count']);
              unset($result[$counter]['favourite_count']);
              unset($result[$counter]['follow_count']);
            }
            $result[$counter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($events, '', "");
            if(!count($result[$counter]['images']))
              $result[$counter]['images']['main'] = $this->getBaseUrl(true,$events->getPhotoUrl());
            $result[$counter]['creationDate'] = date('m M', strtotime($events->creation_date));
            //if ($events->cover_photo) {
                $result[$counter]['cover_images'] = $this->getBaseUrl(true,$events->getCoverPhotoUrl());
            //}
            $counter++;
        }
        return $result;
    }
    public function browsesearchAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $p = $request->getParams();
        $filter = !empty($p['filter']) ? $p['filter'] : 'future';
        if ($filter != 'past' && $filter != 'future')
            $filter = 'future';
        // Create form
        $default_search_type = $this->_getParam('default_search_type', 'like_count DESC');
        $searchArray = array (
            0 => 'recentlySPcreated',
            1 => 'mostSPviewed',
            2 => 'mostSPliked',
            3 => 'mostSPcommented',
            4 => 'mostSPrated',
            6 => 'mostSPjoined',
            7 => 'featured',
            8 => 'sponsored',
            9 => 'verified',
        );
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
            $searchArray[5] = 'mostSPfavourite';
        }
        $search_type = $this->_getParam('search_type',$searchArray);
        if (count($search_type))
            $browseBy = $this->_getParam('browse_by', 'yes');
        else
            $browseBy = 'no';
        $arrayView = array(
            '0' => 'Everyone\'s Events',
            '1' => 'Only My Friend\'s Events',
            'ongoing' => 'Ongoing Events',
            'past' => 'Past Events',
            'week' => 'This Week',
            'weekend' => 'This Weekends',
            'future' => 'Upcomming Events',
            'month' => 'This Month',
            'ongoingSPupcomming' => 'Ongoing & Upcomming'
        );
        $defaultView = array(
            '0',
            '1',
            'ongoing',
            'past',
            'week',
            'weekend',
            'future',
            'month'
        );
        $friend_type = $this->_getParam('view', $defaultView);
        if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0)
            unset($friend_type['1']);
        if (count($friend_type))
            $friendOnly = $this->_getParam('friend_show', 'yes');
        else
            $friendOnly = 'no';
        if ($this->_getParam('location', 'yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)) {
            $location = 'yes';
        } else
            $location = 'no';
        $form = $formFilter = new Sesevent_Form_Filter_Browse(array('friendType' => $friend_type, 'searchType' => $search_type, 'locationSearch' => $location, 'kilometerMiles' => $this->_getParam('kilometer_miles', 'yes'), 'categoriesSearch' => $this->_getParam('categories', 'yes'), 'browseBy' => 'yes', 'searchTitle' => $this->_getParam('search_title'), 'FriendsSearch' => $friendOnly, 'citySearch' => $this->_getParam('city', 'yes'), 'stateSearch' => $this->_getParam('state', 'yes'), 'zipSearch' => $this->_getParam('zip', 'yes'), 'countrySearch' => $this->_getParam('country', 'yes'), 'venueSearch' => $this->_getParam('venue', 'yes'), 'startDate' => $this->_getParam('start_date', 'yes'), 'endDate' => $this->_getParam('end_date', 'yes'), 'alphabetSearch' => $this->_getParam('alphabet', 'yes'),));
        $urlParams = array();
        foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey => $urlParamsVal) {
            if ($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
                continue;
            $urlParams[$urlParamsKey] = $urlParamsVal;
        }
        $formFilter->populate($urlParams);
        if (!count($friend_type))
            $formFilter->removeElement('view');
        else if ($formFilter->view) {
            $viewArray = array();
            foreach ($friend_type as $val) {
                $viewArray[$val] = $arrayView[$val];
            }
            if (count($viewArray))
                $formFilter->view->setMultiOptions($viewArray);
        }
        // Populate options
        if (isset($formFilter->category_id) && count($formFilter->category_id->getMultiOptions()) <= 1)
            $formFilter->removeElement('category_id');

        $formFilter->removeElement('lat');
        $formFilter->removeElement('lng');
        $formFilter->removeElement('loading-img-sesevent'); // this

        if (isset($_GET['order'])) {
            if ($formFilter->order)
                $formFilter->order->setValue($_GET['order']);
        } else {
            if ($formFilter->order)
                $formFilter->order->setValue($default_search_type);
        }
        $advancedSettingBtn = $this->_getParam('show_advanced_search', '1');
        if (!$advancedSettingBtn) {
            $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
            $formFilter->removeElement("advanced_options_search_" . $view->identity);
        }
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
    }
    public function menusAction(){
        $navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('sesevent_main', array());
        $result = array();
        $countMenu = 0;
        $counter = 0;
        $videoenable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seseventvideo');
        $reviewenable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seseventreview') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview_allow_review', 1);;
        foreach ($navigation as $navigationMenu) {
            $class = end(explode(' ', $navigationMenu->class));
            if ($class == "sesevent_main_mytickets" || $class == "sesevent_main_eventlocation" || $class == "seseventvideo_main_eventvideolocation" || $class == "seseventmusic_main_home" || $class == "sesevent_main_browsehome")
                continue;
            if (!$videoenable && $class == 'seseventvideo_main_browsehome')
                continue;
			if(!$reviewenable && $class == 'sesevent_main_browsereview')
				 continue;
            if ($class == 'seseventvideo_main_browsehome') {
                $label = 'Browse Videos';
            } else if ($class == 'sesevent_main_calender') {
                $label = $navigationMenu->label;
                $baseurl = $this->getbaseurl();
                $url = $baseurl . "events/calender";
                $value = $url;
            } else {
                $label = $navigationMenu->label;
            }
            if ($class == 'sesevent_main_calender') {
                $result['menus'][$counter]['name'] = $class;
                $result['menus'][$counter]['label'] = $this->view->translate($label);
                $result['menus'][$counter]['value'] = $value;

                $counter++;
            } else {
                $result['menus'][$counter]['name'] = $class;
                $result['menus'][$counter]['label'] = $this->view->translate($label);
                $counter++;
            }
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function browselistAction(){
        $values['title'] = $this->_getParam('title_name', null);
        $values['popularity'] = $this->_getParam('popularity', null);
        $values['show'] = $this->_getParam('show', null);
        $paginator = Engine_Api::_()->getDbTable('lists', 'sesevent')->getListPaginator($values);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result['lists'] = $this->getlists($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getlists($paginator, $editdelete){
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        foreach ($paginator as $items) {
            $item = $items->toArray();
            $result[$counter] = $item;
            if ($editdelete == 1) {
                $optionCounter = 0;
                $editList = false;
                if ($viewer_id == $items->owner_id || $viewer->level_id == 1) {
                    $result[$counter]['options'][$optionCounter]['name'] = 'edit';
                    $result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Edit');
                    $optionCounter++;

                    $result[$counter]['options'][$optionCounter]['name'] = 'delete';
                    $result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Delete');
                }
            }
            $result[$counter]['owner_title'] = $items->getOwner()->getTitle();
            $owneritem = Engine_Api::_()->getItem('user', $items->owner_id);
            $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owneritem, "", "");
            if ($ownerimage)
                $result[$counter]['owner_image'] = $ownerimage;
            $image = $items->getPhotoUrl();
            if ($image)
                $result[$counter]['image'] = $this->getBaseUrl(false, $image);
            if ($viewer_id != 0) {
                $itemtype = 'sesevent_list';
                $getId = 'list_id';
                $canComment = true;
                if ($canComment)
                    $result[$counter]['can_comment_list'] = $canComment;
                $LikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($items->$getId, $items->getType());
                if ($LikeStatus) {
                    $result[$counter]['is_like_list'] = true;
                }
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
                    $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type' => $itemtype, 'resource_id' => $items->$getId));
                    if ($favStatus) {
                        $result[$counter]['is_follow_list'] = true;
                    }
                }
            }
            $events = $items->getEvents(array('limit' => 3), false);
            $eventcounter = 0;
            foreach ($events as $eventItems) {
                $event = Engine_Api::_()->getItem('event', $eventItems->file_id);
                if (!empty($event)) {
                    $eventimage = $event->getPhotoUrl();
                    $result[$counter]['events'][$eventcounter]['image'] = $this->getBaseUrl(false, $eventimage);
                    if (count($events) > 3) {
                        $moreNumber = count($events) - 3;
                        $result[$counter]['events'][$eventcounter]['more_event'] = $this->translate(array('%s Event', '%s Events', $moreNumber));
                    }
                }
                $eventcounter++;
            }
            $counter++;
        }
        return $result;
    }
    public function browsecategoriesAction(){
        $params['criteria'] = 'most_event';
        $params['countEvents'] = 1;
        //$params['paginator'] = 1;
        $paginator = Engine_Api::_()->getDbTable('categories', 'sesevent')->getCategory($params);
        $result['category'] = $this->getcategories($paginator);
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), array()));
    }
    public function getcategories($paginator){
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        foreach ($paginator as $categories) {
            $category = $categories->toArray();
            $result[$counter] = $category;
            if ($categories->thumbnail != '' && !is_null($categories->thumbnail) && intval($categories->thumbnail)) {
                $image = Engine_Api::_()->storage()->get($categories->thumbnail)->getPhotoUrl('thumb.thumb');
                $result[$counter]['images']['main'] = $this->getBaseUrl(false, $image);
            }
            if ($categories->cat_icon != '' && !is_null($categories->cat_icon) && intval($categories->cat_icon)) {
                $caticon = Engine_Api::_()->storage()->get($categories->cat_icon)->getPhotoUrl('thumb.icon');
                $result[$counter]['images']['icon'] = $this->getBaseUrl(false, $caticon);
            }
            $categoryname = $this->view->translate($categories->category_name);
            if ($categoryname) {
                $result[$counter]['name'] = $categoryname;
            }
            $result[$counter]['count'] = $this->view->translate(array('%s event', '%s events', $categories->total_event_categories), $this->view->locale()->toNumber($categories->total_event_categories));

            $counter++;
        }
        return $result;
    }
    public function edithostAction(){
        $host = Engine_Api::_()->core()->getSubject();
        $viewer = $this->view->viewer();
        if ($viewer->getIdentity() == $host->owner_id || $viewer->level_id == 1)
            Engine_Api::_()->core()->getSubject();
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        $this->view->form = $form = new Sesevent_Form_Host_Edit();
        $form->populate($host->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        if ($this->getRequest()->isPost()) {
            $db = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getAdapter();
            $db->beginTransaction();
            try {
                $host->setFromArray($_POST);
                if (!empty($_FILES['host_photo']['size']) && !$_POST['remove_host_img']) {
                    $photo_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->setPhoto($_FILES['host_photo'], $host->getIdentity());
                    $host->photo_id = $photo_id;
                } else if (!empty($_FILES['photo']['size']) && !$_POST['remove_host_img']) {
                    $photo_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->setPhoto($_FILES['photo'], $host->getIdentity());
                    $host->photo_id = $photo_id;
                } else if ($_POST['remove_host_img']) {
                    $host->photo_id = 0;
                }
                $host->save();
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('host_id' => $host->getIdentity(), 'message' => $this->view->translate("Host updated successfully."))));
        }
    }
    public function hostDeleteAction(){
        $this->view->host = $host = Engine_Api::_()->core()->getSubject();
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($host->type != 'offsite' || !$host)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        // Make form
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $eventTable = Engine_Api::_()->getItemTable('sesevent_event');
        $db = $host->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $select = $eventTable->select()
                ->setIntegrityCheck(false)
                ->from($eventTable->info('name'))
                ->where('host =?', $host->getIdentity())
                ->where('host_type =?', 'offsite');
            $events = $eventTable->fetchAll($select);
            foreach ($events as $event) {
                $getEventHostId = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $event->user_id, 'host_type' => 'site'));
                if ($getEventHostId) {
                    $host_id_in = $getEventHostId;
                    $host_type = 'site';
                } else {
                    $table = Engine_Api::_()->getDbtable('hosts', 'sesevent');
                    $user = Engine_Api::_()->getItem('user', $event->user_id);
                    $hostIn = $table->createRow();
                    $hostIn->host_name = $user->displayname;
                    $hostIn->host_email = $user->email;
                    $hostIn->photo_id = $user->photo_id;
                    $hostIn->user_id = $user->getIdentity();
                    $hostIn->type = 'site';
                    $hostIn->owner_id = $user->getIdentity();
                    $hostIn->ip_address = $_SERVER['REMOTE_ADDR'];
                    $hostIn->creation_date = date('Y-m-d H:i:s');
                    $hostIn->modified_date = date('Y-m-d H:i:s');
                    $hostIn->save();
                    $host_id_in = $hostIn->getIdentity();
                    $host_type = 'site';
                }
                $event->host = $host_id_in;
                $event->host_type = $host_type;
                $event->save();
            }
            $host->delete();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Host deleted successfully.');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->message));
    }
    public function browsehostsAction(){
        $values['name'] = $this->_getParam('title_name', null);
        $getpopularity = $this->_getParam('popularity', null);
        if ($getpopularity) {
            $values['popularity'] = $getpopularity;
        } else {
            $values['popularity'] = 'favourite_count';
        }
        $values['widgteName'] = 'Browse Hosts';
        $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getHostsPaginator($values);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $editdelete = 0;
        $result['hosts'] = $this->gethosts($paginator, $editdelete);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function gethosts($paginator, $editdelete){
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        foreach ($paginator as $hosts) {
            $host = $hosts->toArray();
            $result[$counter] = $host;
            if ($editdelete) {
                $isEditHost = false;
                $isDeleteHost = false;
                if ($viewerId == $hosts->owner_id || $viewer->level_id == 1) {
                    $optioncounter = 0;
                    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1) || $hosts->type == 'offsite') {
                        $isEditHost = true;
                        $result[$counter]['options'][$optioncounter]['name'] = 'edit';
                        $result[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Edit');
                        $optioncounter++;
                    }
                    if ($hosts->type == 'offsite') {
                        $result[$counter]['options'][$optioncounter]['name'] = 'delete';
                        $result[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Delete');
                        $optioncounter++;
                    }
                }
            }
            $followCount = Engine_Api::_()->getDbtable('follows', 'sesevent')->getFollowCount(array('host_id' => $hosts->host_id, 'type' => $hosts->type));
            $hostEventCount = Engine_Api::_()->getDbtable('events', 'sesevent')->getHostEventCounts(array('host_id' => $hosts->host_id, 'type' => $hosts->type));
            if ($followCount) {
                $result[$counter]['follow_count'] = $followCount;
            }
            if ($hostEventCount) {
                $result[$counter]['event_count'] = $hostEventCount;
            }
            $imageURL = $hosts->getPhotoUrl('thumb.main');
            if ($imageURL) {
                $result[$counter]['image'] = $this->getBaseUrl(false, $imageURL);
            }
            if ($hosts->featured) {
                $result[$counter]['featured_label'] = $this->view->translate("FEATURED");
            }
            if ($hosts->sponsored) {
                $result[$counter]['sponsored_label'] = $this->view->translate("SPONSORED");
            }
            if ($hosts->verified) {
                $result[$counter]['varified_label'] = $this->view->translate("Verified Host");
            }
            $itemtype = 'sesevent_host';
            $getId = 'host_id';
            $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type' => 'sesevent_host', 'resource_id' => $hosts->host_id));
            if ($favStatus) {
                $result[$counter]['is_content_favourite'] = true;
            } else {
                $result[$counter]['is_content_favourite'] = false;
            }
            $counter++;
        }

        return $result;
    }
    public function browsevideoAction()
    {
        $defaultOptionsArray = $this->_getParam('search_type', array('recentlySPcreated', 'mostSPviewed', 'mostSPliked', 'mostSPcommented', 'mostSPrated', 'mostSPfavourite', 'hot', 'featured', 'sponsored'));
        if (is_array($defaultOptionsArray)) {
            $defaultOptions = $arrayOptions = array();
            foreach ($defaultOptionsArray as $key => $defaultValue) {
                if ($this->_getParam($defaultValue . '_order'))
                    $order = $this->_getParam($defaultValue . '_order') . '||' . $defaultValue;
                else
                    $order = (1000 + $key) . '||' . $defaultValue;
                if ($this->_getParam($defaultValue . '_label'))
                    $valueLabel = $this->_getParam($defaultValue . '_label');
                else {
                    if ($defaultValue == 'recentlySPcreated')
                        $valueLabel = 'Recently Created';
                    else if ($defaultValue == 'mostSPviewed')
                        $valueLabel = 'Most Viewed';
                    else if ($defaultValue == 'mostSPliked')
                        $valueLabel = 'Most Liked';
                    else if ($defaultValue == 'mostSPcommented')
                        $valueLabel = 'Most Commented';
                    else if ($defaultValue == 'mostSPrated')
                        $valueLabel = 'Most Rated';
                    else if ($defaultValue == 'mostSPfavourite' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1))
                        $valueLabel = 'Most Favourite';
                    else if ($defaultValue == 'hot')
                        $valueLabel = 'Hot';
                    else if ($defaultValue == 'featured')
                        $valueLabel = 'Featured';
                    else if ($defaultValue == 'sponsored')
                        $valueLabel = 'Sponsored';
                }
                $arrayOptions[$order] = $valueLabel;
            }
            ksort($arrayOptions);
            $counter = 0;
            foreach ($arrayOptions as $key => $valueOption) {
                $key = explode('||', $key);
                if ($counter == 0)
                    $defaultOpenTab = $defaultOpenTab = $key[1];
                $defaultOptions[$key[1]] = $valueOption;
                $counter++;
            }
            $defaultOptions = $defaultOptions;
        }
        $defaultOpenTab = (isset($_GET['openTab']) ? str_replace('_', 'SP', $_GET['openTab']) : ($this->_getParam('openTab') != NULL ? $this->_getParam('openTab') : (isset($params['openTab']) ? $params['openTab'] : '')));
        switch (@$defaultOpenTab) {
            case 'recentlySPcreated':
                $popularCol = 'creation_date';
                $type = 'creation';
                break;
            case 'mostSPviewed':
                $popularCol = 'view_count';
                $type = 'view';
                break;
            case 'mostSPliked':
                $popularCol = 'like_count';
                $type = 'like';
                break;
            case 'mostSPcommented':
                $popularCol = 'comment_count';
                $type = 'comment';
                break;
            case 'mostSPrated':
                $popularCol = 'rating';
                $type = 'rating';
                break;
            case 'mostSPfavourite':
                $popularCol = 'favourite_count';
                $type = 'favourite';
                break;
            case 'hot':
                $popularCol = 'is_hot';
                $type = 'is_hot';
                $fixedData = 'is_hot';
                break;
            case 'featured':
                $popularCol = 'is_featured';
                $type = 'is_featured';
                $fixedData = 'is_featured';
                break;
            case 'sponsored':
                $popularCol = 'is_sponsored';
                $type = 'is_sponsored';
                $fixedData = 'is_sponsored';
                break;
        }
        $value['popularCol'] = isset($popularCol) ? $popularCol : '';
        $value['fixedData'] = isset($fixedData) ? $fixedData : '';
        $value['status'] = 1;
        $value['search'] = 1;
        $value['watchLater'] = true;
        $value['parent_id'] = $parentid = $this->_getParam('parent_id', null);
        $value['parent_type'] = $parenttype = $this->_getParam('parent_type', null);
        $allowVideo = Engine_Api::_()->authorization()->isAllowed('sesevent_event', $viewer, 'event_video');
        if ($parentid && $parenttype && $allowVideo) {
            $button['label'] = $this->view->translate('Post New Video');
            $button['name'] = 'postnewvideo';
            $result['post_button'] = $button;
        }
        if ($parentid && $parenttype) {
            $data['parent_id'] = $parentid;
            $data['parent_type'] = $parenttype;
            $paginator = Engine_Api::_()->getDbtable('videos', 'seseventvideo')->getVideo(array('parent_id' => $parentid, 'parent_type' => $parenttype));
        } else {
            $paginator = Engine_Api::_()->getDbtable('videos', 'seseventvideo')->getVideo($value);
        }
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getparam('page', 1));
        $result['videos'] = $this->getvideo($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getvideo($paginator){
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        foreach ($paginator as $videos) {
            $video = $videos->toArray();
            $event = Engine_Api::_()->getItem('sesevent_event', $videos->event_id);
            $eventtitle = $event->title;
            $result[$counter] = $video;
            $result[$counter]['event_title'] = $eventtitle;
            if (isset($videos->video_id)) {
                $newvideoobj = Engine_Api::_()->getItem('seseventvideo_video', $videos->video_id);
            } else if (isset($videos->resource_id)) {
                $newvideoobj = Engine_Api::_()->getItem('seseventvideo_video', $videos->resource_id);
            } else {
                $newvideoobj = Engine_Api::_()->getItem('seseventvideo_video', $videos->file_id);
            }
            if (isset($videos->watchlater_id)) {
                $watchlater_watch_id = $videos->watchlater_id;
                $watchlater_watch_id_exists = 'YES';
            }
            if ($newvideoobj->getType() == 'seseventvideo_video') {
                $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.video.rating', 1);
                $allowShowPreviousRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.ratevideo.show', 1);
                if ($allowRating == 0) {
                    if ($allowShowPreviousRating == 0)
                        $result[$counter]['rating_show'] = false;
                    else
                        $result[$counter]['rating_show'] = true;
                } else
                    $result[$counter]['rating_show'] = true;
            } else
                $result[$counter]['rating_show'] = true;
            $href = $newvideoobj->getHref();
            $imageURL = $newvideoobj->getPhotoUrl();
            $result[$counter]['images']['main'] = $this->getbaseurl(false, $imageURL);
            if ($newvideoobj->is_featured == 1)
                $result[$counter]['featured_label'] = $this->translate('FEATURED');

            if ($newvideoobj->is_sponsored == 1) {
                $result[$counter]['featured_label'] = $this->translate("SPONSORED");
            }
            if ($newvideoobj->is_hot == 1) {
                $result[$counter]['featured_label'] = $this->translate("HOT");
            }
            if ($newvideoobj->duration) {
                if ($newvideoobj->duration >= 3600) {
                    $result[$counter]['duration'] = gmdate("H:i:s", $newvideoobj->duration);
                } else {
                    $result[$counter]['duration'] = gmdate("i:s", $newvideoobj->duration);
                }
            }
            if (isset($newvideoobj->watchlater_id) && $viewerId != 0) {
                $result[$counter]['watch_letter_id'] = $watchLaterId = $newvideoobj->watchlater_id;
                $result[$counter]['watch_letter_text'] = $this->view->translate('Add to Watch Later');
            } else {
                $result[$counter]['watch_letter_text'] = $this->view->translate('Remove from Watch Later');
            }
            $itemtype = 'seseventvideo_video';
            $getId = 'video_id';
            $favStatus = Engine_Api::_()->getDbtable('favourites', 'seseventvideo')->isFavourite(array('resource_type' => $itemtype, 'resource_id' => $newvideoobj->$getId));
            $LikeStatus = Engine_Api::_()->seseventvideo()->getLikeStatusVideo($newvideoobj->$getId, $newvideoobj->getType());
            $canComment = $newvideoobj->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
            if ($canComment) {
                $result[$counter]['can_comment'] = true;
            } else {
                $result[$counter]['can_comment'] = false;
            }
            if ($LikeStatus) {
                $result[$counter]['is_like'] = true;
            } else {
                $result[$counter]['is_like'] = false;
            }
            if ($favStatus && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
                $result[$counter]['is_favourite'] = true;
            } else {
                $result[$counter]['is_favourite'] = false;
            }

            if ($this->view->viewer()->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.enable.watchlater', 1)) {
                if (empty($videos->watchlater_id) && is_null($videos->watchlater_id)) {
                    $result[$counter]["watchlater_id"] = 0;
                } else {
                    $result[$counter]["watchlater_id"] = $videos->watchlater_id;
                }
                $result[$counter]["canWatchlater"] = true;
            } else {
                $result[$counter]["canWatchlater"] = false;
            }
            $owner = $newvideoobj->getOwner();
            if ($owner) {
                $result[$counter]['user_title'] = $owner->getTitle();
            }
            if (isset($newvideoobj->like_count)) {
                $result[$counter]['like_count'] = $newvideoobj->like_count;
            }
            if (isset($newvideoobj->comment_count)) {
                $result[$counter]['comment_count'] = $newvideoobj->comment_count;
            }

            if (isset($newvideoobj->favourite_count) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
                $result[$counter]['favourite_count'] = $newvideoobj->favourite_count;
            }
            if (isset($newvideoobj->view_count)) {
                $result[$counter]['view_count'] = $newvideoobj->view_count;
            }
            if ($ratingShow && isset($newvideoobj->rating) && $newvideoobj->rating > 0) {
                $result[$counter]['rating_count'] = $this->view->translate(array('%s rating', '%s ratings', round($newvideoobj->rating, 1)), $this->view->locale()->toNumber(round($newvideoobj->rating, 1)));
            }
            $counter++;
        }
        return $result;
    }
    public function manageeventsAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (!$viewer_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        }
        $params['user_id'] = $viewer_id;
        $search = $this->_getParam('search_filter', null);
        if ($search) {
            switch ($search) {
                case 'all':
                    $params['manageorder'] = 'creation_date';
                    break;
                case 'joinedEvents':
                    $params['manageorder'] = 'view_count';
                    break;
                case 'hostedEvents':
                    $params['manageorder'] = 'like_count';
                    break;
                case 'like':
                    $params['manageorder'] = 'comment_count';
                    break;
                case 'save':
                    $params['manageorder'] = 'favourite_count';
                    break;
                case 'favourite':
                    $params['manageorder'] = 'follow_count';
                    break;
                case 'featured':
                    $params['manageorder'] = 'featured';
                    break;
                case 'sponsored':
                    $params['manageorder'] = 'sponsored';
                    break;
                case 'verified':
                    $params['sort'] = 'verified';
                    break;
                case 'lists':
                    $params['manageorder'] = 'lists';
                    break;
                case 'hosts':
                    $params['manageorder'] = 'hosts';
                    break;
            }
        }
        $page = $this->getParam('page', 1);
        if ($page == 1) {
            $filterOptionsMenu = array();
            $filterMenucounter = 0;
            $resultmenu[$filterMenucounter]['name'] = 'all';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Owned Events');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'joinedEvents';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Joined Events only');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'hostedEvents';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Hosted Events Only');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'like';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Liked');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'save';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Saved Events');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'sponsored';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Sponsored Events');
            $filterMenucounter++;
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
            $resultmenu[$filterMenucounter]['name'] = 'favourite';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Favourite Events');
            $filterMenucounter++;
            }
            $resultmenu[$filterMenucounter]['name'] = 'featured';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Featured Events');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'verified';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Verified Events');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'lists';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('My Lists');
            $filterMenucounter++;
            $resultmenu[$filterMenucounter]['name'] = 'hosts';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('My Hosts');
            $filterMenucounter++;
            $result['menus'] = $resultmenu;
        }
        $value['widgetManage'] = true;
        if ($params['manageorder'] == 'lists') {
            $type = 'lists';
            $paginator = Engine_Api::_()->getDbTable('lists', 'sesevent')
                ->getListPaginator(array_merge($value, array('action' => 'manage', 'user' => $viewer->getIdentity())));
        } else if ($params['manageorder'] == 'hosts') {
            $type = 'hosts';
            $paginator = Engine_Api::_()->getDbTable('hosts', 'sesevent')
                ->getAllHosts(array_merge($value, array('owner_id' => $viewer->getIdentity())));
        } else {
            $type = 'events';
            $params['user_id'] = $viewer_id;
            $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')
                ->getEventPaginator($params);
        }
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $editdelete = 1;
        if ($type == 'lists') {
            $result['lists'] = $this->getlists($paginator, $editdelete);
        } else if ($type == 'hosts') {
            $result['hosts'] = $this->gethosts($paginator, $editdelete);
        } else if ($type == 'events') {
            $result['events'] = $this->getevents($paginator, $editdelete);
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function categoryviewAction(){
        $categoryId = $this->_getParam('category_id', null);
        $subCategoryId = $this->_getParam('sub_category_id', null);
        $subSubCategoryId = $this->_getParam('Sub_subcategory_id', null);
        if ($categoryId) {
            $category = Engine_Api::_()->getItem('sesevent_category', $categoryId);
        }else if($subCategoryId){
			$category = Engine_Api::_()->getItem('sesevent_category', $subCategoryId);
		}else if($subSubCategoryId){
			$category = Engine_Api::_()->getItem('sesevent_category', $subSubCategoryId);
		} else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => ' parameter_missing', 'result' => array()));
        }

		if($this->_getParam('page',null)==1){
			 $result['event_category'] = $category->toArray();
		  if (isset($category->thumbnail) && !empty($category->thumbnail)) {
            $image = Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl('thumb.main');
            $result['event_category']['images']['main'] = $this->getbaseurl(false, $image);
			}
		  if ($category->subcat_id == 0 && $category->subsubcat_id == 0) {
            $innerCatData = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubcategory(array('category_id' => $category->category_id, 'column_name' => '*', 'countEvents' => true, 'getcategory0' => true));
            $columnCategory = 'category_id';
            $countersubCat = 0;
            foreach ($innerCatData as $item) {
                $result['sub_category'][$countersubCat] = $item->toArray();
                if ($item->thumbnail != '' && !is_null($item->thumbnail) && intval($item->thumbnail)) {
                    $image = Engine_Api::_()->storage()->get($item->thumbnail)->getPhotoUrl('thumb.main');
                    $result['sub_category'][$countersubCat]['images']['main'] = $this->getbaseurl(false, $image);
                }
                if ($item->cat_icon != '' && !is_null($item->cat_icon) && intval($item->cat_icon)) {
                    $image = Engine_Api::_()->storage()->get($item->thumbnail)->getPhotoUrl('thumb.icon');
                    $result['sub_category'][$countersubCat]['images']['icon'] = $this->getbaseurl(false, $image);
                }
                $result['sub_category'][$countersubCat]['count'] = $this->view->translate(array('%s event', '%s events', $item->total_events_categories), $this->view->locale()->toNumber($item->total_events_categories));

                $countersubCat++;
            }
			}
		}
        $data['category_id'] = $categoryId;
		if($subCategoryId)
        $data['subcat_id'] = $subCategoryId;
		if($subSubCategoryId)
        $data['subsubcat_id'] = $subSubCategoryId;
        $data['getcategory0'] = true;
        $paginator = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventPaginator($data);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $counter = 0;
        foreach ($paginator as $eventitems) {
            $eventitem = $eventitems->toArray();
            $result['events'][$counter] = $eventitem;

            $imageURL = $eventitems->getPhotoUrl();
            $result['events'][$counter]['images']['main'] = $this->getbaseurl(false, $imageURL);

            $defaultParams = array();
            $defaultParams['isSesapi'] = 1;
            $defaultParams['starttime'] = true;
            $defaultParams['endtime'] = true;
            $defaultParams['timezone'] = true;
            $strtime = $this->view->eventStartEndDates($eventitems, $defaultParams);
            list($result['events'][$counter]['calanderStartTime'], $result['events'][$counter]['calanderEndTime']) = explode('ENDDATE', strip_tags($strtime));
            if ($eventitems->featured == 1) {
                $result['events'][$counter]['featured_label'] = $this->view->translate("Featured");
            }
            if ($eventitems->sponsored == 1) {
                $result['events'][$counter]['featured_label'] = $this->view->translate("Sponsored");
            }
            $owner = $eventitems->getOwner();
            $result['events'][$counter]['posted_by'] = $this->view->translate('Posted by %1$s', $owner->getTitle());

            $guestCountStats = $eventitems->joinedmember ? $eventitems->joinedmember : 0;
            $result['events'][$counter]['guest_count'] = $guestCount = $this->view->translate(array('%s guest', '%s guests', $eventitems->joinedmember), $this->view->locale()->toNumber($guestCountStats));
            $counter++;
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function eventviewAction(){
        $event_id = $this->_getParam('event_id', null);
        if (!$event_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject()) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
            Engine_Api::_()->core()->setSubject($event);
        } else {
            $event = Engine_Api::_()->core()->getSubject();
        }
        $sesprofilelock_enable_module = (array)Engine_Api::_()->getApi('settings', 'core')->getSetting('sesprofilelock.enable.modules');
        if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesprofilelock')) && in_array('sesevent', $sesprofilelock_enable_module) && $viewerId != $event->owner_id) {
            $cookieData = '';
            if ($event->enable_lock && !in_array($event->page_id, explode(',', $cookieData))) {
                $locked = true;
            } else {
                $locked = false;
            }
            $password = $event->page_password;
        } else {
            $password = true;
        }
        $menus = $this->_getParam('menus', null);
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewerId = $viewer->getIdentity();
        if ($menus) {
            $tabcounter = 0;
            $result['menus'][$tabcounter]['name'] = 'updates';
            $result['menus'][$tabcounter]['label'] = 'Updates';
            $tabcounter++;
            $result['menus'][$tabcounter]['name'] = 'info';
            $result['menus'][$tabcounter]['label'] = 'Info';
            $tabcounter++;
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
                $eventHasTicket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $event->getIdentity()));
                if (!count($eventHasTicket)) {
                    $result['menus'][$tabcounter]['name'] = 'members';
                    $result['menus'][$tabcounter]['label'] = 'Guest';
                    $tabcounter++;
                }
            } else {
                $result['menus'][$tabcounter]['name'] = 'members';
                $result['menus'][$tabcounter]['label'] = 'Guest';
                $tabcounter++;
            }
            $result['menus'][$tabcounter]['name'] = 'album';
            $result['menus'][$tabcounter]['label'] = 'Album';
            $tabcounter++;
            if ($event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)) {
                $result['menus'][$tabcounter]['name'] = 'location';
                $result['menus'][$tabcounter]['label'] = 'Location';
                $tabcounter++;
            }
            $editOverview = $event->authorization()->isAllowed($viewer, 'edit');
            if (($event->overview || !is_null($event->overview)) || ($editOverview && (!$event->overview || is_null($event->overview)))) {
                $result['menus'][$tabcounter]['name'] = 'overview';
                $result['menus'][$tabcounter]['label'] = 'Overview';
                $tabcounter++;
            }
            $result['menus'][$tabcounter]['name'] = 'discussions';
            $result['menus'][$tabcounter]['label'] = 'Discussions';
            $tabcounter++;
            if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seseventvideo')) {
                $result['menus'][$tabcounter]['name'] = 'video';
                $result['menus'][$tabcounter]['label'] = 'Video';
                $tabcounter++;
            }
			if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seseventreview') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview_allow_review', 1) && $this->_helper->requireAuth()->setAuthParams('seseventreview_review', null, 'view')->isValid()) {
            $result['menus'][$tabcounter]['name'] = 'reviews';
            $result['menus'][$tabcounter]['label'] = 'Reviews';
			}
        }
        $category_id = $event->category_id;
        $value['category_id'] = $category_id;
        $pageid[] = $event_id;
        $relatedParams['not_event_id'] = $pageid;
        $relatedParams['category_id'] = $event->category_id;
        $relatedParams['getcategory0'] = true;
        if ($category_id) {
            $related = $this->relatedevents($relatedParams);
            if ($related) {
                $result['related_events'] = $related;
            }
        }
        $result['photo'] = $this->photo($event->event_id);
        $result['event'] = $this->getProfile($event);
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function photo($eventid){
        $params['event_id'] = $eventid;
        $paginator = Engine_Api::_()->getDbTable('photos', 'sesevent')
            ->getPhotoPaginator($params);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber(1);
        $i = 0;
        foreach ($paginator as $photos) {

            $images = $photos->getPhotoUrl();
            if ($images) {
                $imagesA = $this->getBaseUrl(false, $images);
                $result[$i]['images'] = $imagesA;
            } else {
                $result[$i]['images'] = "";
            }
            $result[$i]['photo_id'] = $photos->getIdentity();
            $result[$i]['album_id'] = $photos->album_id;
            $i++;
        }
        return $result;
    }
    public function eventinfoAction(){
        $event_id = $this->_getParam('event_id', null);
        if (!$event_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => $result)));
        }
        if (!Engine_Api::_()->core()->hasSubject()) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
            Engine_Api::_()->core()->setSubject($event);
        } else {
            $event = Engine_Api::_()->core()->getSubject();
        }
        if($this->getInformation($event));
        $result['information'] = $this->getInformation($event);
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function getInformation($event){
        $result = $event->toArray();
        $owner = $event->getOwner();
        $result['basic_information']['label'] = $this->view->translate('Basic Info');
        $counterinfo = 0;
        $info[$counterinfo]['label'] = $this->view->translate("Created by");
        $info[$counterinfo]['name'] = "createdby";
        $info[$counterinfo]['value'] = $owner->getTitle();
        $counterinfo++;
        $host = Engine_Api::_()->getItem('sesevent_host', $event->host);
        $info[$counterinfo]['name'] = "hostedby";
        $info[$counterinfo]['label'] = $this->view->translate("Hosted by");
        $info[$counterinfo]['value'] = $host->getTitle();
        $counterinfo++;
        $info[$counterinfo]['name'] = $this->view->translate("createDate");
        $info[$counterinfo]['label'] = $this->view->translate("Created on");
        $info[$counterinfo]['value'] = $event->creation_date;
        $counterinfo++;
        $info[$counterinfo]['label'] = $this->view->translate("Stats");
        $info[$counterinfo]['name'] = "stats";
        $statslike = $this->view->translate(array('<b>%s</b> Like', '<b>%s</b> Likes', $event->like_count), $this->view->locale()->toNumber($event->like_count));
        $statsComment = $this->view->translate(array('<b>%s</b> Comment', '<b>%s</b> Comments', $event->comment_count), $this->view->locale()->toNumber($event->comment_count));
        $statsView = $this->view->translate(array('<b>%s</b> View', '<b>%s</b> Views', $event->view_count), $this->view->locale()->toNumber($event->view_count));
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
            $statsFovourite = $this->view->translate(array('<b>%s</b> Favourite', '<b>%s</b> Favourites', $event->favourite_count), $this->view->locale()->toNumber($event->favourite_count));
        }
        $info[$counterinfo]['value'] = $statslike . ", " . $statsComment . ", " . $statsView . ", " . $statsFovourite;
        $result['basic_information']['value'] = $info;
        $result['when_and_where']['label'] = $this->view->translate("When & Where");
        $whencounter = 0;
        $whenandwhere[$whencounter]['label'] = $this->view->translate("When");
        $whenandwhere[$whencounter]['value'] = $this->view->eventStartEndDates($event);
        $whencounter++;
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)) {
            $whenandwhere[$whencounter]['label'] = $this->view->translate("Where");
            $whenandwhere[$whencounter]['value'] = $event->location;
        }
        $result['when_and_where']['value'] = $whenandwhere;
        $result['detail']['name'] = 'detail';
        $result['detail']['value'] = $event->description;
        foreach ($event->tags()->getTagMaps() as $tagmap) {
            $tag = $tagmap->getTag();
            if (!isset($tag->text))
                continue;
            $tags[] = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
            ));
        }
        if (count($tags)) {
            $result['tag'] = $tags;
        }
        $result['meta']['label'] = $this->view->translate("Meta Info");
        $metacounter = 0;
        if ($event->category_id) {
            $category = Engine_Api::_()->getItem('sesevent_category', $event->category_id);
            if ($category) {
                $metavalue[$metacounter]['name'] = 'category';
                $metavalue[$metacounter]['label'] = $this->view->translate("Category");
                $metavalue[$metacounter]['value'] = $category->category_name;
                $metacounter++;
            }
            if (count($tags)) {
                $metavalue[$metacounter]['name'] = 'tag';
                $metavalue[$metacounter]['label'] = $this->view->translate("Tag");
                $metacounter++;
            }
        }
        $result['meta']['value'] = $metavalue;
        $profileFieldCounter = 0;
        $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
        $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($event);
        if (count($fieldStructure)) { // @todo figure out right logic
            $content = $this->view->fieldSesapiValueLoop($event, $fieldStructure);
            foreach ($content as $key => $value) {
                $profileFields[$profileFieldCounter]['label'] = $key;
                $profileFields[$profileFieldCounter]['value'] = $value;
                $profileFieldCounter++;
            }
        }
        if ($profileFieldCounter)
            $result['profileDetail'] = $profileFields;
        return $result;
    }
    public function relatedevents($params){
        $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')
            ->getEventPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $editdelete = 0;
        $result = $this->getEvents($paginator, $editdelete);
        return $result;
    }
    public function getProfile($event){
        $pagedata = $event->toArray();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewerId = $viewer->getIdentity();
		    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $result = $event->toArray();
        foreach ($event->tags()->getTagMaps() as $tagmap) {
            $tag = $tagmap->getTag();
            if (!isset($tag->text))
                continue;
            $tags[] = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
            ));
        }
        if (count($tags)) {
            $result['tag'] = $tags;
        }
        $currentTime = time();
        if (strtotime($event->starttime) > $currentTime) {
            $status = 'notStarted';
        } else if (strtotime($event->endtime) < $currentTime) {
            $status = 'expire';
        } else {
            $status = 'onGoing';
        }
        $result['event_status'] = $status;
        if ($event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)) {
            unset($event['location']);
            $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('sesevent_event', $event->getIdentity());
            if ($location) {
                $result['location'] = $location->toArray();
                if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.map.integration', 1)) {
                    $result['location']['showMap'] = true;
                } else {
                    $result['location']['showMap'] = false;
                }
            }
        }
        if ($viewer_id > 0) {
            $type = $event->getType();
            $id = $event->getIdentity();
			$saveEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_event_save', 1);
			if($saveEnable >0){
				$isSave = Engine_Api::_()->getDbTable('saves', 'sesevent')->isSave(array('resource_type' => $type, 'resource_id' => $id));
				if ($isSave) {
					$result['is_content_saved'] = true;
					$isSaved = Engine_Api::_()->getDbtable('saves', 'sesevent')->getSave($event, $viewer);
					if ($isSaved > 0)
						$result['save_id'] = $isSaved->getIdentity();
				} else {
					$result['is_content_saved'] = false;
				}
			}
        }
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allow.share', 1);
        if ($event->is_approved) {
            if ($shareType) {
                $result["share"]["imageUrl"] = $this->getBaseUrl(false, $event->getPhotoUrl());
								$result["share"]["url"] = $this->getBaseUrl(false,$event->getHref());
                $result["share"]["title"] = $event->getTitle();
                $result["share"]["description"] = strip_tags($event->getDescription());
                $result["share"]['urlParams'] = array(
                    "type" => $event->getType(),
                    "id" => $event->getIdentity()
                );
            }
        }
        $row = $event->membership()->getRow($viewer);
        $viewer_id = $viewer->getIdentity();
        //if ($row) {
            $rsvp = !empty($row->rsvp) ? $row->rsvp : 10;
            $counterrsvp = 0;
            $result['RSVP'][$counterrsvp]['name'] = '2';
            $result['RSVP'][$counterrsvp]['label'] = $this->view->translate('Attending');
            $result['RSVP'][$counterrsvp]['value'] = $rsvp == 2;
            $counterrsvp++;
            $result['RSVP'][$counterrsvp]['name'] = '1';
            $result['RSVP'][$counterrsvp]['label'] = $this->view->translate('May be Attending');
            $result['RSVP'][$counterrsvp]['value'] = $rsvp == 1;
            $counterrsvp++;
            $result['RSVP'][$counterrsvp]['name'] = '0';
            $result['RSVP'][$counterrsvp]['label'] = $this->view->translate('Not Attending');
            $result['RSVP'][$counterrsvp]['value'] = $rsvp == 0;
            $counterrsvp++;
        //}
        $navigations = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_profile');
        $navigationCounter = 0;

        foreach ($navigations as $navigation) {
            $params = $navigation->params;
            $class = end(explode(' ', $navigation->class));

            $label = $this->view->translate($navigation->getLabel());
            if ($class == 'sesevent_profile_style') {
                $action = 'editeventstyle';
            } else if ($class == 'sesevent_profile_share') {
                $action = 'share';
            } else if ($class == 'sesevent_profile_invite') {
                $action = 'invite';
            } else if ($class == 'sesevent_profile_message') {
                $action = 'messagemembers';
            } else if ($class == 'sesevent_profile_join' || $params['action'] == 'join') {
                $action = 'join';
            } else if ($class == 'sesevent_profile_leave' || $params['action'] == 'leave') {
                $action = 'leave';
            } else if ($class == 'sesevent_accept_invite_request' || $params['action'] == 'accept') {
                $action = 'accept';
            } else if ($class == 'sesevent_ignore_invite_request' || $params['action'] == 'reject') {
                $action = 'reject';
            } else if ($class == 'sesevent_profile_invite_request' || $params['action'] == 'request') {
                $action = 'request';
            } else if ($class == 'sesevent_cancel_invite_request' || $params['action'] == 'cancel') {
                $action = 'cancel';
            } else if ($class == 'sesevent_profile_dashboard') {
                $action = 'dashboard';
                $baseurl = $this->getBaseUrl();
                $custumurl = $event->custom_url;
                $url = $baseurl . 'sesevent/dashboard/edit/' . $custumurl;
                $value = $url;
            } else if ($class == 'sesevent_profile_delete') {
                $action = 'delete';
            }
            if ($class != 'sesevent_profile_style' && $class != 'sesevent_profile_delete') {
                $result['options'][$navigationCounter]['label'] = $label;
                $result['options'][$navigationCounter]['name'] = $action;
                if ($class == 'sesevent_profile_dashboard') {
                    $result['options'][$navigationCounter]['value'] = $value;
                }
                $navigationCounter++;
            }
            $canEdit = $canEdit = $event->authorization()->isAllowed($viewer, 'edit');
            if ($canEdit) {
                $i = 0;
                if (isset($event->cover_photo) && $event->cover_photo != 0 && $event->cover_photo != '') {
                    $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Change Cover Photo');
                    $result['updateCoverPhoto'][$i]['name'] = 'upload';
                    $i++;
                    $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Remove Cover Photo');
                    $result['updateCoverPhoto'][$i]['name'] = 'removePhoto';
                    $i++;
                    $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('View Cover Photo');
                    $result['updateCoverPhoto'][$i]['name'] = 'view';
                    $i++;
                } else {
                    $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Add Cover Photo');
                    $result['updateCoverPhoto'][$i]['name'] = 'upload';
                    $i++;
                }
                $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Choose From Albums');
                $result['updateCoverPhoto'][$i]['name'] = 'album';
                // photo upload
                $j = 0;
                if (!empty($event->photo_id)) {
                    $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('View Photo');
                    $result['updateProfilePhoto'][$j]['name'] = 'view';
                    $j++;
                    $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Change Photo');
                    $result['updateProfilePhoto'][$j]['name'] = 'upload';
                    $j++;
                    $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Remove Photo');
                    $result['updateProfilePhoto'][$j]['name'] = 'removePhoto';
                    $j++;
                } else {
                    $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Upload Profile Photo');
                    $result['updateProfilePhoto'][$j]['name'] = 'upload';
                    $j++;
                }
                $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Choose From Albums');
                $result['updateProfilePhoto'][$j]['name'] = 'album';
                $j++;
            }
            $i = 0;
            if ($event->event_contact_email || $event->event_contact_phone || $event->event_contact_website) {
                if ($result->event_contact_email) {
                    $result['about'][$i]['name'] = 'mail';
                    $result['about'][$i]['label'] = $this->view->translate('Send Email');
                    $result['about'][$i]['value'] = $event->event_contact_email;
                    $i++;
                }
                if ($event->event_contact_phone) {
                    $result['about'][$i]['name'] = 'phone';
                    $result['about'][$i]['label'] = $this->view->translate('View Phone number');
                    $result['about'][$i]['value'] = $event->event_contact_phone;
                    $i++;
                }
                if ($event->event_contact_website) {
                    $result['about'][$i]['name'] = 'website';
                    $result['about'][$i]['label'] = $this->view->translate('Visit Website');
                    $result['about'][$i]['value'] = $event->event_contact_website;
                    $i++;
                }
                if ($event->creation_date) {
                    $result['about'][$i]['name'] = 'createDate';
                    $result['about'][$i]['label'] = $this->view->translate('Create Date');
                    $result['about'][$i]['value'] = $event->creation_date;
                    $i++;
                }
                if ($event->category_id) {
                    $category = Engine_Api::_()->getItem('sesevent_category', $event->category_id);
                    if ($category) {
                        $result['about'][$i]['name'] = 'category';
                        $result['about'][$i]['label'] = $this->view->translate('Category Title');
                        $result['about'][$i]['value'] = $category->category_name;
                        //                     $pagedata['about'][$i]['id'] = $page->category_id;
                    }
                    $i++;
                }
                if (count($tags)) {
                    $result['about'][$i]['name'] = 'tag';
                    $result['about'][$i]['value'] = $this->view->translate('Tag');
                    $i++;
                }
                $result['about'][$i]['name'] = 'seeall';
                $result['about'][$i]['value'] = $this->view->translate('See All');
            }
        }
        $result['cover_photo'] = (int)$event->cover_photo;
        $result['photo_id'] = (int)$event->photo_id;
        $canaddtolist = $viewerId ? Engine_Api::_()->authorization()->getPermission($levelId, 'sesevent_event', 'addlist_event') : 0;
        if ($canaddtolist) {
            $result['can_add_to_list'] = $canaddtolist;
        } else {
            $result['can_add_to_list'] = $canaddtolist;
        }
        if ($viewerId != 0) {
            $result['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($event);
            $result['like_count'] = $event->like_count;
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1)) {
                $result['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($event, 'favourites', 'sesevent', 'sesevent_event', 'user_id');
                $result['favourite_count'] = $event->favourite_count;
            }
            $result['is_content_follow'] = Engine_Api::_()->sesapi()->contentFollow($event, 'follows', 'sesevent', 'sesevent_event', 'poster_id');
            $result['follow_count'] = (int)Engine_Api::_()->sesapi()->getContentFollowCount($event, 'follows', 'sesevent', 'sesevent_event', 'poster_id');
        }
        $result['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($event, '', "");
        $result['cover_images'] = $this->getBaseUrl(true, $event->getCoverPhotoUrl());
        $owner = $event->getOwner();
        if ($event->authorization()->isAllowed($viewer, 'edit')) {
            $result['options'][$navigationCounter]['label'] = $this->view->translate('Edit');
            $result['options'][$navigationCounter]['name'] = 'edit';
            $navigationCounter++;
        }
        if ($event->authorization()->isAllowed($viewer, 'delete')) {
            $result['options'][$navigationCounter]['label'] = $this->view->translate('Delete');
            $result['options'][$navigationCounter]['name'] = 'delete';
        }
        if ($event->category_id) {
            $category = Engine_Api::_()->getItem('sesevent_category', $event->category_id);
            if ($category) {
                $result['category_title'] = $category->category_name;
                if ($event->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('sesevent_category', $event->subcat_id);
                    if ($subcat) {
                        $result['subcategory_title'] = $subcat->category_name;
                        if ($event->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('sesevent_category', $event->subsubcat_id);
                            if ($subsubcat) {
                                $result['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
    function favouriteAction(){
        if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        if ($this->_getParam('type') == 'sesevent_event') {
            $type = 'sesevent_event';
            $dbTable = 'events';
            $resorces_id = 'event_id';
            $notificationType = 'sesevent_favourite_event';
        } else if ($this->_getParam('type') == 'sesevent_list') {
            $type = 'sesevent_list';
            $dbTable = 'lists';
            $resorces_id = 'list_id';
            $notificationType = 'sesevent_favourite_eventlist';
        } else if ($this->_getParam('type') == 'seseventspeaker_speaker') {
            $type = 'seseventspeaker_speaker';
            $dbTable = 'speakers';
            $resorces_id = 'speaker_id';
            $notificationType = 'sesevent_favourite_eventspeaker';
        } else if ($this->_getParam('type') == 'sesevent_host') {
            $type = 'sesevent_host';
            $dbTable = 'hosts';
            $resorces_id = 'host_id';
            $notificationType = 'sesevent_favourite_eventhost';
        }
        $item_id = $this->_getParam('id');
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $Fav = Engine_Api::_()->getDbTable('favourites', 'sesevent')->getItemfav($type, $item_id);

        if ($this->_getParam('type') == 'seseventspeaker_speaker') {
            $favItem = Engine_Api::_()->getDbtable($dbTable, 'seseventspeaker');
        } else {
            $favItem = Engine_Api::_()->getDbtable($dbTable, 'sesevent');
        }
        if (count($Fav) > 0) {
            //delete
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Fav->delete();
                $db->commit();
                $message['message'] = $this->view->translate('Successfully Unfavourited.');
                $message['favourite_id'] = 0;
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
            $item = Engine_Api::_()->getItem($type, $item_id);
            if (@$notificationType) {
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
                Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
                Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $message));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('favourites', 'sesevent')->getAdapter();
            $db->beginTransaction();
            try {
                $fav = Engine_Api::_()->getDbTable('favourites', 'sesevent')->createRow();
                $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $fav->resource_type = $type;
                $fav->resource_id = $item_id;
                $fav->save();
                $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1'),
                ), array(
                    $resorces_id . '= ?' => $item_id,
                ));
                // Commit
                $db->commit();
                $message['message'] = $this->view->translate('Successfully Favourited.');
                $message['favourite_id'] = 1;
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            //send notification and activity feed work.
            $item = Engine_Api::_()->getItem(@$type, @$item_id);
            if (@$notificationType) {
                $subject = $item;
                $owner = $subject->getOwner();
                if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && @$notificationType) {
                    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
                    $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                    if (!$result) {
                        $action = $activityTable->addActivity($viewer, $subject, $notificationType);
                        if ($action)
                            $activityTable->attachActivity($action, $subject);
                    }
                }
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $message));
        }
    }
    public function createAction(){
        if (!$this->_helper->requireUser->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'create')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $select = Engine_Api::_()->getDbTable('events', 'sesevent')->select()->where('user_id =?', $viewer->getIdentity());
        $paginator = count(Engine_Api::_()->getDbTable('events', 'sesevent')->fetchAll($select));
        $quota = Engine_Api::_()->authorization()->getPermission($levelId, 'sesevent_event', 'addevent_maxevent');
        $current_count = $paginator;
        if (($current_count >= $quota) && !empty($quota)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have already uploaded the maximum number of events allowed.'), 'result' => array('message' => $this->view->translate('If you would like to upload a new event, please delete an old one first.'))));
        }
        $parent_type = $this->_getParam('parent_type');
        $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));
        if ($parent_type == 'group' && Engine_Api::_()->hasItemType('group')) {
            $group = Engine_Api::_()->getItem('group', $parent_id);
            if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'sesevent_event')->isValid())
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        } else {
            $parent_type = 'user';
            $parent_id = $viewer->getIdentity();
        }
        //Event Category and profile fields check
        $event_id = $this->_getParam('event_id', false);
        if ($event_id)
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        $defaultProfileId = 1;

        $form = new Sesevent_Form_Create(array('parent_type' => $parent_type, 'parent_id' => $parent_id, 'defaultProfileId' => $defaultProfileId, 'smoothboxType' => $sessmoothbox, 'fromApi' => "true"));
        $form->removeElement('event_custom_datetimes');
        $form->removeElement('photouploader');
        $form->removeElement('event_main_photo_preview');
        $form->removeElement('removeimage');
        $form->removeElement('removeimage2');
        $form->removeElement('event_timezone_popup');
        $form->removeElement('photo-uploader');
        if ($form->getElement('event_location'))
            $form->getElement('event_location')->setLabel('Location');


        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent'));
        }

        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        //check custom url
        if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
            $custom_url = Engine_Api::_()->getDbtable('events', 'sesevent')->checkCustomUrl($_POST['custom_url']);
            if ($custom_url) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Custom Url not available.Please select other."), 'result' => array()));
            }
        }
        if ($_GET['sesapi_platform'] != 1) {
            // Process
            $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'] . ' ' . $_POST['start_time'])) : '';
            $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'] . ' ' . $_POST['end_time'])) : '';
        } else {
            $starttime = isset($_POST['start_time']) ? date('Y-m-d H:i:s', strtotime($_POST['start_time'])) : '';
            $endtime = isset($_POST['end_time']) ? date('Y-m-d H:i:s', strtotime($_POST['end_time'])) : '';
        }
        $values = $form->getValues();
        if(empty($values['draft'])){
          $values['draft'] = 1;
        }
        $values['user_id'] = $viewer->getIdentity();
        $values['parent_type'] = $parent_type;
        $values['parent_id'] = $parent_id;
        $values['timezone'] = isset($_POST['timezone']) ? $_POST['timezone'] : '';
        $values['location'] = isset($_POST['event_location']) ? $_POST['event_location'] : '';
        $values['show_timezone'] = !empty($_POST['show_timezone']) ? $_POST['show_timezone'] : '0';
        $values['show_endtime'] = !empty($_POST['show_endtime']) ? $_POST['show_endtime'] : '0';
        $values['show_starttime'] = !empty($_POST['show_starttime']) ? $_POST['show_starttime'] : '0';
        $values['venue_name'] = isset($_POST['venue_name']) ? $_POST['venue_name'] : '';
        if (empty($values['timezone'])) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Timezone is a required field."), 'result' => array()));
        }
        if ($parent_type == 'group' && Engine_Api::_()->hasItemType('group') && empty($values['host'])) {
            $values['host'] = $group->getTitle();
        }
        if (strtotime($starttime) >= strtotime($endtime)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Start Time must be less than End Time."), 'result' => array()));
        }
		if (time() >strtotime($starttime)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Event Start Time must be greater than or equal to Current Time."), 'result' => array()));
        }
        $settings = Engine_Api::_()->getApi('settings', 'core');
		//echo '<pre>';print_r($settings->getSetting('sesevent_mainphotomand', 1));die;
        if ($settings->getSetting('sesevent_mainphotomand', 1)) {
            if (empty($_FILES['photo']['size'])) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Main Photo is a required field."), 'result' => array()));
            }
        }

        // Convert times
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($values['timezone']);
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        date_default_timezone_set($oldTz);
        $values['starttime'] = date('Y-m-d H:i:s', $start);
        $values['endtime'] = date('Y-m-d H:i:s', $end);
        $db = Engine_Api::_()->getDbtable('events', 'sesevent')->getAdapter();
        $db->beginTransaction();
        try {
            // Create event
            $table = Engine_Api::_()->getDbtable('events', 'sesevent');
            $event = $table->createRow();
            if (empty($values['is_sponsorship']))
                $values['is_sponsorship'] = 0;
            if (empty($values['is_custom_term_condition']))
                unset($values['custom_term_condition']);
            //set location
            if (empty($_POST['lat'])) {
                unset($values['location']);
                unset($values['lat']);
                unset($values['lng']);
                unset($values['venue_name']);
                $values['is_webinar'] = 1;
            } else
                $values['is_webinar'] = 0;

            if (!empty($_POST['event_location'])) {
                $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['event_location']);
                if ($latlng) {
                    $values['lat'] = $_POST['lat'] = $latlng['lat'];
                    $values['long'] = $_POST['lng'] = $latlng['lng'];
					$values['location'] = $_POST['event_location'];
                }
            }

            $values['is_approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_approve');
            //Host save function
            if ($_POST['host_type'] == 'offsite' && !empty($_POST['event_host']) && $_POST['choose_host'] != "new") {
                $host_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $_POST['event_host'], 'host_type' => 'offsite'));
                $values['host_type'] = 'offsite';
                if (!empty($host_id)) {
                    $values['host'] = $host_id;
                    unset($values['event_host']);
                } else {
                    $values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'offsite', $_POST);
                    unset($values['event_host']);
                }
            } elseif ($_POST['choose_host'] != "new" && ($_POST['host_type'] == 'site' || $_POST['host_type'] == 'myself')) {
                if (!empty($_POST['selectonsitehost']))
                    $_POST['toValues'] = $_POST['selectonsitehost'];
                $values['host_type'] = 'site';
                if ($_POST['host_type'] == 'myself') {
                    $_POST['toValues'] = $viewer->getIdentity();
                    $_POST['host_type'] = 'site';
                }
                $host_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $_POST['toValues'], 'host_type' => 'site'));
                if (!empty($host_id)) {
                    $values['host'] = $host_id;
                    unset($values['toValues']);
                } else {
                    $values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'site', $_POST);
                    unset($values['toValues']);
                }
            } else if ($_POST['choose_host'] == "new") {
                $values['host_type'] = 'offsite';
                $values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'offsite', $_POST);
            } else {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Please select valid host."), 'result' => array()));
            }


            $values['featured'] = (int)Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_sponsored');
            $values['sponsored'] = (int)Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_featured');
            $values['verified'] = (int)Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_verified');
            $values['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $sesevent_draft = $settings->getSetting('sesevent.draft', 1);
            if (empty($sesevent_draft)) {
                $values['draft'] = 1;
            }
            $sesevent_rsvpevent = $settings->getSetting('sesevent.rsvpevent', 1);
            if (empty($sesevent_rsvpevent)) {
                $values['approval'] = $settings->getSetting('sesevent.rsvpdefaultval', 1);
            }
            $sesevent_inviteguest = $settings->getSetting('sesevent.inviteguest', 1);
            if (empty($sesevent_inviteguest)) {
                $values['invite'] = $settings->getSetting('sesevent.guestdefaultval', 1);
            }
            if (empty($values['category_id']))
                $values['category_id'] = 0;
            if (empty($values['subsubcat_id']))
                $values['subsubcat_id'] = 0;
            if (empty($values['subcat_id']))
                $values['subcat_id'] = 0;
            $event->setFromArray($values);
            $event->save();
			//echo '<pre>';print_r($_POST['lat']);echo '<br>';
			//echo '<pre>';print_r($_POST['lng']);echo '<br>';
			//echo '<pre>';print_r($_POST['event_location']);echo '<br>';
			//die;
            if (!$event->is_webinar) {
                if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && !empty($_POST['event_location'])) {
                    $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                    $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $event->event_id . '","' . $_POST['event_location'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "sesevent_event")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
                }
            }
            //$event->seo_title = $event->title;
            $event->save();
            $tags = preg_split('/[,]+/', $values['tags']);
            $event->tags()->addTagMaps($viewer, $tags);
            $event->seo_keywords = implode(',', $tags);
            // Add owner as member
            $event->membership()->addMember($viewer)
                ->setUserApproved($viewer)
                ->setResourceApproved($viewer);
            // Add owner rsvp
            $event->membership()
                ->getMemberInfo($viewer)
                ->setFromArray(array('rsvp' => 2))
                ->save();
            // Add photo

            if (!empty($_FILES['photo']) && !empty($_FILES['photo']['size'])) {
              try{
                  $event->setPhoto($_FILES['photo']);
              }catch(Exception $e){
                 try{
                    $event->setPhoto($form->photo);
                 }catch(Exception $e){//silence
                 }
              }
            }else if (!empty($_FILES['image']) && !empty($_FILES['image']['name'])) {
               try{
                  $event->setPhoto($_FILES['image']);
              }catch(Exception $e){
                 try{
                    $event->setPhoto($form->image);
                 }catch(Exception $e){//silence
                 }
              }
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            if ($values['parent_type'] == 'group') {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }
            if (empty($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            if (empty($values['auth_comment'])) {
                $values['auth_comment'] = 'everyone';
            }
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $photoMax = array_search($values['auth_photo'], $roles);
            $videoMax = array_search($values['auth_video'], $roles);
            $musicMax = array_search($values['auth_music'], $roles);
            $topicMax = array_search($values['auth_topic'], $roles);
            $ratingMax = array_search($values['auth_rating'], $roles);
            foreach ($roles as $i => $role) {
                $auth->setAllowed($event, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($event, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'photo', ($i <= $photoMax));
                $auth->setAllowed($event, $role, 'video', ($i <= $videoMax));
                $auth->setAllowed($event, $role, 'music', ($i <= $musicMax));
                $auth->setAllowed($event, $role, 'topic', ($i <= $topicMax));
                $auth->setAllowed($event, $role, 'rating', ($i <= $ratingMax));
            }
            $auth->setAllowed($event, 'member', 'invite', $values['auth_invite']);
            // Add an entry for member_requested
            $auth->setAllowed($event, 'member_requested', 'view', 1);
            //Add fields
            $customfieldform = $form->getSubForm('fields');
            $customfieldform->setItem($event);
            $customfieldform->saveValues();
            $event->save();
            // Commit
            $db->commit();
            if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
                $event->custom_url = $_POST['custom_url'];
            else
                $event->custom_url = $event->event_id;
            $event->save();
            //Activity Feed Work
            if ($event->draft == 1 && $event->is_approved == 1) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $event, 'sesevent_create');
                if ($action) {
                    $activityApi->attachActivity($action, $event);
                }
                //Tag Work
                if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
                    $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                    foreach ($tags as $tag) {
                        $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("' . $action->getIdentity() . '", "' . $tag . '")');
                    }
                }
                //Event create mail send to event owner
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($event->getOwner(), 'sesevent_event_create', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            // Redirect
            $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.autoopenpopup', 1);
            if ($autoOpenSharePopup && $event->draft && $event->is_approved) {
                $_SESSION['newEvent'] = true;
            }
			$redirection = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.redirect', 1);
			if(!$event->is_approved)
				$redirect = 'manage';
			else if($event->is_approved && $redirection == 1)
				$redirect = 'dashboard';
			else
				$redirect = 'view';
            if (!$event->is_approved) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Event created successfully and send to admin approval.'), 'event_id' => 0,'redirect'=>$redirect)));
            } else {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('event_id' => $event->getIdentity(), 'success_message' => $this->view->translate('Event created successfully.'),'redirect'=>$redirect)));
            }
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The image you selected was too large.'), 'result' => array()));
        } catch (Exception $e) {
            $db->rollBack();
           // throw $e;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function setPhoto($photo,$host_id){
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
			$name = basename($file);
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
			$name = basename($photo['name']);
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
			$name = basename($file);
    } else {
      throw new Sesevent_Model_Exception('invalid argument passed to setPhoto');
    }

    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_id' => $id,
        'parent_type' => 'sesevent_event'
    );

    // Save
    $storage = Engine_Api::_()->storage();
    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($path . '/m_' . $name)
            ->destroy();

    // Resize image (normal)
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(140, 160)
            ->write($path . '/in_' . $name)
            ->destroy();
    // Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);
    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;
    $image->resample($x, $y, $size, $size, 48, 48)
            ->write($path . '/is_' . $name)
            ->destroy();
    // Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    $iIconNormal = $storage->create($path . '/in_' . $name, $params);
    $iSquare = $storage->create($path . '/is_' . $name, $params);
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare, 'thumb.icon');
    // Remove temp files
    @unlink($path . '/m_' . $name);
    @unlink($path . '/in_' . $name);
    @unlink($path . '/is_' . $name);
		return $iMain->file_id;
  }

    public function deleteAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $event_id = $this->_getParam('event_id', null);
        if (!$event_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing.', 'result' => array()));
        $event = Engine_Api::_()->getItem('event', $this->getRequest()->getParam('event_id'));

        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));

        // Make form
        $form = new Sesevent_Form_Delete();
        if (!$event) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Event doesn't exists or not authorized to delete."), 'result' => array()));
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>"invalid_request.", 'result' => array()));
        }
        $db = $event->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $event->is_delete = '1';
            $event->save();
            $db->commit();
            $message = 'Event Successfully Deleted.';
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate($message))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'databse_error', 'result' => array()));
        }
    }
    public function viewhostAction(){
        $host_id = $this->_getParam('host_id', null);
        if (!$host_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => "parameter_missing", 'result' => array()));
        $host = Engine_Api::_()->getItem('sesevent_host', $host_id);
        if (!$host)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Host doesn't exists."), 'result' => array()));
        Engine_Api::_()->getDbtable('hosts', 'sesevent')->update(array('view_count' => new Zend_Db_Expr('view_count + 1'),), array('host_id = ?' => $host->host_id,));

        $type = 'sesevent_host';
        $id = $host->host_id;
        $isFollow = Engine_Api::_()->getDbTable('follows', 'sesevent')->isFollow(array('resource_type' => $type, 'resource_id' => $id));
        $allowFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.followeventowner', 1);
        $select = Engine_Api::_()->getDbtable('follows', 'sesevent')->getFollowSelect($host);
        $results = $select->query()->fetchAll();
        $followCount = count($results);
        $hostEventCount = Engine_Api::_()->getDbtable('events', 'sesevent')->getHostEventCounts(array('host_id' => $host->host_id, 'type' => $host->type));
        $hostArray = array();
        $hostArray['host_name'] = $host->getTitle();
        $hostArray['host_id'] = $host->getIdentity();
        $hostArray['image'] = $this->getBaseUrl(true, $host->getPhotoUrl());
        if ($allowFollow) {
            $hostArray['followCount'] = $followCount . ' ' . $this->view->translate("Followed");
            $hostArray['is_content_follow'] = $isFollow ? true : false;
            if($hostArray['is_content_follow']){
              $hostArray['follow_id'] = $isFollow;
            }
            $hostArray['content_follow_count'] = (int)$followCount;
        }
        $hostArray['hostedEvent'] = $hostEventCount . ' ' . $this->view->translate("Event Hosted");
        $hostArray['viewCount'] = $this->view->translate(array('%s view', '%s views', $host->view_count), $this->view->locale()->toNumber($host->view_count));

        $hostArray['favourite_count'] = $this->view->translate(array('%s favourite', '%s favourites', $host->favourite_count), $this->view->locale()->toNumber($host->favourite_count));

        $hostArray['description'] = $host->host_description;
        if ($host->host_email)
            $hostArray['host_email'] = $host->host_email;
        if ($host->host_phone)
            $hostArray['host_phone'] = (string)$host->host_phone;
        if ($host->website_url)
            $hostArray['website_url'] = (preg_match("#http?://#", $host->website_url) === 0) ? 'http://' . $host->website_url : $host->website_url;
        if ($host->facebook_url)
            $hostArray['facebook_url'] = (preg_match("#https?://#", $host->facebook_url) === 0) ? 'http://' . $host->facebook_url : $host->facebook_url;
        if ($host->twitter_url)
            $hostArray['twitter_url'] = (preg_match("#https?://#", $host->twitter_url) === 0) ? 'http://' . $host->twitter_url : $host->twitter_url;;
        if ($host->linkdin_url)
            $hostArray['linkdin_url'] = (preg_match("#https?://#", $host->linkdin_url) === 0) ? 'http://' . $host->linkdin_url : $host->linkdin_url;;
        if ($host->googleplus_url)
            $hostArray['googleplus_url'] = (preg_match("#https?://#", $host->googleplus_url) === 0) ? 'http://' . $host->googleplus_url : $host->googleplus_url;;
        if ($host->website_url)
            $hostArray['website_url'] = $host->website_url;
        if (($this->view->viewer()->getIdentity() == $host->owner_id || $this->view->viewer()->level_id == 1) && $host->type == "offsite" && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.editeventhost', 1)):
            $hostArray['menus'][0]['label'] = $this->view->translate('Edit');
            $hostArray['menus'][0]['name'] = 'edit';
            $hostArray['menus'][1]['label'] = $this->view->translate('Delete');
            $hostArray['menus'][1]['name'] = 'delete';
        endif;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $hostArray));
    }
    public function albumviewAction(){
        $albumid = $this->_getParam('album_id', 0);
        $eventId = $this->_getParam('event_id', 0);
        $album = Engine_Api::_()->getItem('sesevent_album', $albumid);
        if ($album)
            $event = Engine_Api::_()->getItem('sesevent_event', $album->event_id);
        if (!$albumid) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        if (!$album || !$event) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => $this->view->translate(' There are no results that match your search. Please try again.')));
        }
        $photoTable = Engine_Api::_()->getItemTable('sesevent_photo');
        $mine = true;
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        if (!$viewer) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        $viewer_id = $viewer->getIdentity();
        $result['album'] = $album->toArray();
        $result['album']['user_title'] = $viewer->getOwner()->getTitle();
        $category = Engine_Api::_()->getItem('sesevent_category', $event->category_id);
        if ($category)
            $result['album']['category_title'] = $category->category_name;
        if ($viewer->getIdentity() > 0) {
            $viewPermission = $event->authorization()->isAllowed($viewer, 'edit');
            $canComment = $event->authorization()->isAllowed($viewer, 'comment');
            $canCreateMemberLevelPermission = Engine_Api::_()->authorization()->getPermission($levelId, 'sesevent_event', 'create');
            $canEditMemberLevelPermission = Engine_Api::_()->authorization()->getPermission($levelId, 'sesevent_event', 'edit');
            $canDeleteMemberLevelPermission = Engine_Api::_()->authorization()->getPermission($levelId, 'sesevent_event', 'delete');
			$editItem = true;
            if($canEditMemberLevelPermission == 1){
              if($viewer->getIdentity() == $this->album->owner_id){
                $editItem = true;
              }else{
                $editItem = false;
              }
            }else if($canEditMemberLevelPermission == 2){
               $editItem = true;
            }else{
                $editItem = false;
            }
            $deleteItem = true;
            if($canDeleteMemberLevelPermission == 1){
              if($viewer->getIdentity() == $this->album->owner_id){
                $deleteItem = true;
              }else{
                $deleteItem = false;
              }
            }else if($canDeleteMemberLevelPermission == 2){
               $deleteItem = true;
            }else{
                $deleteItem = false;
            }
             $createItem = true;
            if($canCreateMemberLevelPermission == 1){
              if($viewer->getIdentity() == $this->album->owner_id){
                $createItem = true;
              }else{
                $createItem = false;
              }
            }else{
                $createItem = false;
            }
        }
        $menusCounter = 0;
        if ($editItem) {
                $result['album']['is_edit'] = true;
                $result['menus'][$menusCounter]['name'] = 'edit';
                $result['menus'][$menusCounter]['label'] = $this->view->translate('Edit');
                $menusCounter++;
            } else {
                $result['album']['is_edit'] = false;
            }
        $result['album']['is_delete'] = true;
        if ($deleteItem) {
                $result['album']['is_delete'] = true;
                $result['menus'][$menusCounter]['name'] = 'delete';
                $result['menus'][$menusCounter]['label'] = $this->view->translate('Delete');
                $menusCounter++;
                $result['album']['is_delete'] = false;
        } else {
            $result['album']['is_delete'] = false;
        }
        if ($viewer_id != $album->owner_id) {
            $result['menus'][$menusCounter]['name'] = 'report';
            $result['menus'][$menusCounter]['label'] = $this->view->translate("Report");
            $result['menus'][$menusCounter]["params"]['id'] = $album->getIdentity();
            $result['menus'][$menusCounter]["params"]['type'] = $album->getType();
            $menusCounter++;
        }
        $result['menus'][$menusCounter]['name'] = 'uploadphoto';
        $result['menus'][$menusCounter]['label'] = $this->view->translate("Upload more photos");
        $menusCounter++;
        $owner = $album->getOwner();
        $coverAlbumPhoto = $album->getPhotoUrl('thumb.profile', 'status');
        if ($coverAlbumPhoto)
            $imageURL = $this->getbaseurl(false, $coverAlbumPhoto);
        if ($imageURL)
            $result['album']['image'] = $imageURL;
        if ($canComment)
            $result['album']['is_comment'] = true;
        else
            $result['album']['is_comment'] = false;
        $result['album']['user_title'] = $album->getOwner()->getTitle();
        $result['album']['user_image'] = $this->userImage($album->getOwner()->getIdentity(), "thumb.profile");
        $paginator = $photoTable->getPhotoPaginator($result['album']);
        $paginator->setItemCountPerPage('limit', 10);
        $paginator->setCurrentPageNumber('page', 1);
        $photoCounter = 0;
        foreach ($paginator as $photo) {
            $result['photos'][$photoCounter] = $photo->toArray();
            $photoimageURL = $photo->getPhotoUrl('thumb.normalmain');
            if ($photoimageURL)
                $image = $this->getbaseurl(false, $photoimageURL);
            $result['photos'][$photoCounter]['images'] = $image;
            $result['photos'][$photoCounter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photo);
            $result['photos'][$photoCounter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($photo);

            $photoCounter++;
        }
        $albumArtCover = '';
        if (isset($album->art_cover) && $album->art_cover != 0 && $album->art_cover != '') {
            $albumArtCover = Engine_Api::_()->storage()->get($album->art_cover, '');
            if ($albumArtCover) {
                $result['album']['albumArtCover'] = $albumArtCover->getPhotoUrl();
                $result['album']['cover_pic'] = $albumArtCover;
            }
        }
        $albumImage = Engine_Api::_()->sesevent()->getAlbumPhoto($album->getIdentity(), 0, 1);
        foreach ($albumImage as $photo) {
            $imageURL = $photo->getPhotoUrl('thumb.normalmain');
            break;
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function eventalbumAction(){
        if (!Engine_Api::_()->core()->hasSubject() || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        $event = Engine_Api::_()->core()->getSubject();
		$order = isset($_POST['sort'])?$_POST['sort']:'album_id';
		$search = isset($_POST['search'])?$_POST['search']:'';
		$albumTable = Engine_Api::_()->getDbTable('albums', 'sesevent');
		$albumTableName = $albumTable->info('name');
		$select = $albumTable->select()
		    ->from($albumTableName)
		    ->where('search =?',1)
		    ->where($albumTableName.'.event_id =?',$event->event_id);
			if(count($search)>0)
			$select->where('`'.$albumTableName.'`.`title` LIKE ?', '%'. $search .'%');
		    $select->group($albumTableName.'.album_id');
			switch($order){
        case 'most_commented':
           $select->order($hostsTableName . '.most_commented DESC');
          break;
        case 'view_count':
          $select->order($hostsTableName . '.view_count DESC');
          break;
        case "like_count":
         $select->order($hostsTableName . '.like_count DESC');
          break;
        case "creation_date":
		  $select->order($hostsTableName . '.creation_date DESC');
          break;
		}
        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
		$canUpload = $event->authorization()->isAllowed(null, 'photo');
		if($canUpload){
			//$result['can_create']['name'] = 'true';
			$result['can_create'] = 'true';
		}
		$optioncounter = 0;
		$result['menus'][$optioncounter]['name'] = 'creation_date';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Recently Created');
        $optioncounter++;
        $result['menus'][$optioncounter]['name'] = 'like_count';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Liked');
        $optioncounter++;
        $result['menus'][$optioncounter]['name'] = 'view_count';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Viewed');
        $optioncounter++;
        $result['menus'][$optioncounter]['name'] = 'most_commented';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Commented');
        $optioncounter++;

        $albumCounter = 0;
        foreach ($paginator as $item) {
            $owner = $item->getOwner();
            $ownertitle = $owner->displayname;
            $result['albums'][$albumCounter] = $item->toArray();
            $image = $item->getPhotoUrl('thumb.normalmain');
            if ($image)
                $imageurl = $this->getbaseurl(false, $image);
            else
                $imageurl = '';
            $result['albums'][$albumCounter]['images'] = $imageurl;
            $result['albums'][$albumCounter]['user_title'] = $ownertitle;
            if ($viewer_id != 0) {
                $result['albums'][$albumCounter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
                $result['albums'][$albumCounter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($item);
            }
			$result['albums'][$albumCounter]['photo_count'] = $item->count();
            $albumCounter++;
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function editoverviewAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $subject = Engine_Api::_()->core()->getSubject();
        if ($this->_getParam('getForm')) {
            $formFields = array();
            $formFields[0]['name'] = "overview";
            $formFields[0]['type'] = "Textarea";
            $formFields[0]['multiple'] = "";
            $formFields[0]['label'] = "Event Overview";
            $formFields[0]['description'] = "";
            $formFields[0]['isRequired'] = "1";
            $formFields[0]['value'] = $subject->overview;
            $formFields[1]['name'] = "submit";
            $formFields[1]['type'] = "Button";
            $formFields[1]['multiple'] = "";
            $formFields[1]['label'] = "Save Changes";
            $formFields[1]['description'] = "";
            $formFields[1]['isRequired'] = "0";
            $formFields[1]['value'] = '';
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        $subject->overview = $_POST['overview'];
        $subject->save();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Event overview saved successfully.'))));
    }
    public function eventoverviewAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $subject = Engine_Api::_()->core()->getSubject();
        $editOverview = $subject->authorization()->isAllowed($viewer, 'edit');
        if (!$editOverview && (!$subject->overview || is_null($subject->overview))) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('There are no results that match your search. Please try again.'), 'result' => array()));
        }
        if ($editOverview) {
            if ($subject->overview) {
                $result['button'][0]['name'] = "editoverview";
                $result['button'][0]['lable'] = $this->view->translate("Change Overview");
            } else {
                $result['button'][0]['name'] = "editoverview";
                $result['button'][0]['lable'] = $this->view->translate("Add Overview");
            }
        }
        if ($subject->overview) {
            $result['overview'] = $subject->overview;
        } else {
            $result['overview'] = $this->view->translate("There is currently no overview.");
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }
    public function eventguestAction(){
        // Don't render this if not authorized
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject('sesevent_event')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'user_not_autheticate', 'result' => array()));
        }
        $event = Engine_Api::_()->core()->getSubject();
        $canEdit = $subject->authorization()->isAllowed($viewer, 'edit');
        $membershipTable = Engine_Api::_()->getDbtable('membership', 'sesevent');
        $membershipTableName = $membershipTable->info('name');
        $selectAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS attending');
        $selectAttenting = $selectAttenting->where('active =?', 1)->where('rsvp =?', 2)->where('resource_id =?', $event->getIdentity())->query()->fetchColumn();
        $selectNotAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS notattending');
        $selectNotAttenting = $selectNotAttenting->where('active =?', 1)->where('resource_id =?', $event->getIdentity())->where('rsvp =?', 0)->query()->fetchColumn();
        $selectMaybeAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS maybeattending');
        $selectMaybeAttenting = $selectMaybeAttenting->where('active =?', 1)->where('resource_id =?', $event->getIdentity())->where('rsvp =?', 1)->query()->fetchColumn();
        $selectNewAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS newattending');
        $selectNewAttenting = $selectNewAttenting->where('active =?', 0)->where('resource_id =?', $event->getIdentity())->query()->fetchColumn();
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
            $eventHasTicket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $subject->getIdentity()));
            if (count($eventHasTicket))
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        }
        // Get params
        $page = $this->_getParam('page', 1);
        $params['event_id'] = $event->event_id;
        $params['type'] = $this->_getParam('type', '');
        $params['searchVal'] = $this->_getParam('search', null);
        $paginator = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership($params);

        if ($params['searchVal']) {
            $value['event_id'] = $event->event_id;
            $value['type'] = $this->_getParam('type', '');
            $allpaginator = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership($value);
            $totalcount = $allpaginator->getTotalItemCount();
        } else {
            $totalcount = $paginator->getTotalItemCount();
        }
        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $tabcounter = 0;
        $data[$tabcounter]['name'] = 'all';
        $data[$tabcounter]['label'] = $this->view->translate("All");
        $data[$tabcounter]['value'] = $totalcount;
        $tabcounter++;
        $data[$tabcounter]['name'] = 'attending';
        $data[$tabcounter]['label'] = $this->view->translate("Attending");
        $data[$tabcounter]['value'] = $selectAttenting;
        $tabcounter++;
        $data[$tabcounter]['name'] = 'maybeattending';
        $data[$tabcounter]['label'] = $this->view->translate("Maybe Attending");
        $data[$tabcounter]['value'] = $selectMaybeAttenting;
        $tabcounter++;
        $data[$tabcounter]['name'] = 'notattending';
        $data[$tabcounter]['label'] = $this->view->translate("Not Attending");
        $data[$tabcounter]['value'] = $selectNotAttenting;
        $tabcounter++;
        if ($canEdit) {
            $data[$tabcounter]['name'] = 'approvalorwaitingrequest';
            $data[$tabcounter]['label'] = $this->view->translate("Approval or Waiting Request");
            $data[$tabcounter]['value'] = $selectNewAttenting;
        }
        $counter = 0;
        foreach ($paginator as $member) {
            $result[$counter] = $member->toArray();
            if (!empty($member->resource_id)) {
                $memberInfo = $member;
                $member = $this->view->item('user', $memberInfo->user_id);
            } else {
                $memberInfo = $event->membership()->getMemberInfo($member);
            }
            $imagepath = $member->getPhotoUrl('thumb.profile');
            if ($imagepath)
                $result[$counter]['member_photo'] = $this->getBaseUrl(false, $imagepath);
            $result[$counter]['member_title'] = $member->getTitle();
            if ($event->getParent()->getGuid() == ($member->getGuid()))
                $result[$counter]['member_is_owner'] = $this->view->translate('(%s)', ($memberInfo->title ? $memberInfo->title : $this->view->translate('owner')));

            if ($memberInfo->rsvp == 0)
                $result[$counter]['RSVP'] = $this->view->translate('Not Attending');
            else if ($memberInfo->rsvp == 1)
                $result[$counter]['RSVP'] = $this->view->translate('Maybe Attending');
            else if ($memberInfo->rsvp == 2)
                $result[$counter]['RSVP'] = $this->view->translate('Attending');
            else
                $result[$counter]['RSVP'] = $this->view->translate('Awaiting Reply');

            if ($event->isOwner($viewer) && !$event->isOwner($member)) {
                $optioncounter = 0;
                if ($event->isOwner($viewer)) {
                    if (!$event->isOwner($member) && $memberInfo->active == true) {
                        $result[$counter]['options'][$optioncounter]['name'] = 'remove';
                        $result[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Remove Member');
                    }
                    if ($memberInfo->active == false && $memberInfo->resource_approved == false) {
                        $result[$counter]['options'][$optioncounter]['name'] = 'approve';
                        $result[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Approve Request');
                        $optioncounter++;
                        $result[$counter]['options'][$optioncounter]['name'] = 'reject';
                        $result[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Reject Request');
                    }
                    if ($memberInfo->active == false && $memberInfo->resource_approved == true) {
                        $result[$counter]['options'][$optioncounter]['name'] = 'cancelinvite';
                        $result[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Cancel Invite');
                    }
                }
            }
            $counter++;
        }
        $resultdata['members'] = $result;
        $resultdata['menus'] = $data;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $resultdata), $extraParams));
    }
    public function eventdiscussionAction()
    {
        // Don't render this if not authorized
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        }
        $canTopicCreate = $subject->authorization()->isAllowed(null, 'topic');
        // Get paginator
        $table = Engine_Api::_()->getItemTable('sesevent_topic');
        $select = $table->select()
            ->where('event_id = ?', $subject->getIdentity())
            ->order('sticky DESC')
            ->order('modified_date DESC');
        $paginator = Zend_Paginator::factory($select);
        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        if ($viewer->getIdentity()) {
            if ($canTopicCreate) {
                $result['label'] = $this->view->translate('Post New Topic');
                $result['name'] = 'pastnewtopic';
            }
        }
        $counter = 0;
        foreach ($paginator as $topic) {
            $data[$counter] = $topic->toArray();
            $lastpost = $topic->getLastPost();
            $lastposter = $topic->getLastPoster();
            $data[$counter]['reply_count'] = $this->view->locale()->toNumber($topic->post_count - 1);
            $data[$counter]['reply_label'] = $this->view->translate(array('reply', 'replies', $topic->post_count - 1));
            $lastposterimagepath = $this->userImage($lastposter->user_id, 'thumb.profile');
            $data[$counter]['last_post_date'] = $lastpost->creation_date;
            $data[$counter]['last_post']['image'] = $this->getBaseUrl(false, $lastposterimagepath);
            $data[$counter]['last_post']['label'] = $this->view->translate('Last Post by, %s', $lastposter->getTitle());
            //if($topic->sticky){
            $data[$counter]['post_title'] = $topic->getTitle();
            //}
            $data[$counter]['post_description'] = ($topic->getDescription());
            $counter++;
        }
        $resultdata['discussions'] = $data;
        $resultdata['post_button'] = $result;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $resultdata), $extraParams));
    }
    public function creatediscussionAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('sesevent_event')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $event = $event = Engine_Api::_()->core()->getSubject();
        $viewer = $viewer = Engine_Api::_()->user()->getViewer();
        // Make form
        $form = $form = new Sesevent_Form_Topic_Create();

        $form->getElement('body')->setLabel('Description');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        // Process
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $values['event_id'] = $event->getIdentity();
        $topicTable = Engine_Api::_()->getDbtable('topics', 'sesevent');
        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
        $postTable = Engine_Api::_()->getDbtable('posts', 'sesevent');
        $db = $event->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Create topic
            $topic = $topicTable->createRow();
            $topic->setFromArray($values);
            $topic->save();
            // Create post
            $values['topic_id'] = $topic->topic_id;
            $post = $postTable->createRow();
            $post->setFromArray($values);
            $post->save();
            // Create topic watch
            $topicWatchesTable->insert(array(
                'resource_id' => $event->getIdentity(),
                'topic_id' => $topic->getIdentity(),
                'user_id' => $viewer->getIdentity(),
                'watch' => (bool)$values['watch'],
            ));
            // Add activity
            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
            $action = $activityApi->addActivity($viewer, $topic, 'sesevent_topic_create');
            if ($action) {
                $action->attach($topic);
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succussfully Topic created.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function discussionviewAction(){
        if (!$this->_helper->requireSubject('sesevent_topic')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $topic = $topic = Engine_Api::_()->core()->getSubject();
        $event = $event = $topic->getParentEvent();
        $canEdit = $canEdit = $event->authorization()->isAllowed($viewer, 'edit');
        $canPost = $canPost = $event->authorization()->isAllowed($viewer, 'comment');
        $canAdminEdit = Engine_Api::_()->authorization()->isAllowed($event, null, 'edit');
        if (!$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id) {
            $topic->view_count = new Zend_Db_Expr('view_count + 1');
            $topic->save();
        }
        $isWatching = null;
        if ($viewer->getIdentity()) {
            $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
            $isWatching = $topicWatchesTable
                ->select()
                ->from($topicWatchesTable->info('name'), 'watch')
                ->where('resource_id = ?', $event->getIdentity())
                ->where('topic_id = ?', $topic->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->limit(1)
                ->query()
                ->fetchColumn(0);
            if (false === $isWatching) {
                $isWatching = null;
            } else {
                $isWatching = (bool)$isWatching;
            }
        }
        // @todo implement scan to post
        $post_id = (int)$this->_getParam('post');
        $table = Engine_Api::_()->getDbtable('posts', 'sesevent');
        $select = $table->select()
            ->where('event_id = ?', $event->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->order('creation_date ASC');
        $paginator = Zend_Paginator::factory($select);
        $topicdata['label'] = $topic->getTitle();
        $headeroptionscounter = 0;
        if ($canPost) {
            $data[$headeroptionscounter]['name'] = 'postreply';
            $data[$headeroptionscounter]['label'] = $this->view->translate('Post Reply');
            $headeroptionscounter++;
            if ($viewer->getIdentity()) {
                if (!$isWatching) {
                    $data[$headeroptionscounter]['name'] = 'watchtopic';
                    $data[$headeroptionscounter]['label'] = $this->view->translate('Watch Topic');
                    $headeroptionscounter++;
                } else {
                    $data[$headeroptionscounter]['name'] = 'stopwatching';
                    $data[$headeroptionscounter]['label'] = $this->view->translate('Stop Watching Topic');
                    $headeroptionscounter++;
                }
            }
        }
//         if ($canEdit || $canAdminEdit) {
//             if (!$topic->sticky) {
//                 $data[$headeroptionscounter]['name'] = 'makesticky';
//                 $data[$headeroptionscounter]['label'] = $this->view->translate('Make Sticky');
//                 $headeroptionscounter++;
//             } else {
//                 $data[$headeroptionscounter]['name'] = 'removesticky';
//                 $data[$headeroptionscounter]['label'] = $this->view->translate('Remove Sticky');
//                 $headeroptionscounter++;
//             }
//             if (!$topic->closed) {
//                 $data[$headeroptionscounter]['name'] = 'close';
//                 $data[$headeroptionscounter]['label'] = $this->view->translate('Close');
//                 $headeroptionscounter++;
//             } else {
//                 $data[$headeroptionscounter]['name'] = 'open';
//                 $data[$headeroptionscounter]['label'] = $this->view->translate('Open');
//                 $headeroptionscounter++;
//             }
//             $data[$headeroptionscounter]['name'] = 'rename';
//             $data[$headeroptionscounter]['label'] = $this->view->translate('Rename');
//             $headeroptionscounter++;
//             $data[$headeroptionscounter]['name'] = 'delete';
//             $data[$headeroptionscounter]['label'] = $this->view->translate('Delete');
//             $headeroptionscounter++;
//         } elseif (!$canEdit) {
//             if ($this->topic->closed) {
//                 $data[$headeroptionscounter]['name'] = 'thistopichasbeenclosed';
//                 $data[$headeroptionscounter]['label'] = $this->view->translate('This topic has been closed.');
//                 $headeroptionscounter++;
//             }
//         }
        $topicdata['value'] = $data;
        $counter = 0;
        foreach ($paginator as $post) {
            $posts[$counter] = $post->toArray();
            $user = $this->view->item('user', $post->user_id);
            $isOwner = false;
            $isMember = false;
            if ($event->isOwner($user)) {
                $isOwner = true;
                $isMember = true;
            } else if ($event->membership()->isMember($user)) {
                $isMember = true;
            }
            $posts[$counter]['post_id'] = $post->getIdentity();
            $posts[$counter]['title'] = $user->getTitle();
            $imagepath = $user->getPhotoUrl('thumb.profile');
            if ($imagepath)
                $posts[$counter]['user_photo'] = $this->getBaseUrl(false, $imagepath);

            if ($isOwner) {
                $posts[$counter]['is_owner_label'] = $this->view->translate('Host');
            } else if ($isMember) {
                $posts[$counter]['is_owner_label'] = $this->view->translate('Member');
            }
            $optioncounter = 0;
            if ($post->user_id == $viewer->getIdentity() || $event->getOwner()->getIdentity() == $viewer->getIdentity() || $canAdminEdit) {
                $posts[$counter]['options'][$optioncounter]['name'] = 'edit';
                $posts[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Edit');
                $optioncounter++;
                $posts[$counter]['options'][$optioncounter]['name'] = 'delete';
                $posts[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Delete');
            }

            $posts[$counter]['creation_date'] = $event->creation_date;

            $counter++;
        }
        $result['posts'] = $posts;
        $result['topic'] = $topicdata;
        // Skip to page of specified post
        if (0 !== ($post_id = (int)$this->_getParam('post_id')) &&
            null !== ($post = Engine_Api::_()->getItem('sesevent_post', $post_id))) {
            $icpp = $paginator->getItemCountPerPage();
            $page = ceil(($post->getPostIndex() + 1) / $icpp);
            $paginator->setCurrentPageNumber($page);
        } // Use specified page
        else if (0 !== ($page = (int)$this->_getParam('page'))) {
            $paginator->setCurrentPageNumber($this->_getParam('page'));
        }

        if ($canPost && !$topic->closed) {
            $form = new Sesevent_Form_Post_Create();

            if ($this->_getParam('getForm')) {
                $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
                $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
            }
            $form->populate(array(
                'topic_id' => $topic->getIdentity(),
                'ref' => $topic->getHref(),
                'watch' => (false === $isWatching ? '0' : '1'),
            ));
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function closeAction()
    {
        $topic = Engine_Api::_()->core()->getSubject();
        $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->closed = (null === $this->_getParam('closed') ? !$topic->closed : (bool)$this->_getParam('closed'));
            $topic->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => 'Action performed successfully.')));
    }
    public function commentonpostAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('sesevent_topic')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $topic = $topic = Engine_Api::_()->core()->getSubject();
        $event = $event = $topic->getParentEvent();
        if ($topic->closed) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This has been closed for posting.'), 'result' => array()));
            $status = false;
        }
        // Make form
        $form = $form = new Sesevent_Form_Post_Create();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        // Process
        $viewer = Engine_Api::_()->user()->getViewer();
        $topicOwner = $topic->getOwner();
        $isOwnTopic = $viewer->isSelf($topicOwner);
        $postTable = Engine_Api::_()->getDbtable('posts', 'sesevent');
        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
        $userTable = Engine_Api::_()->getItemTable('user');
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $values['event_id'] = $event->getIdentity();
        $values['topic_id'] = $topic->getIdentity();
        $watch = (bool)$values['watch'];
        $isWatching = $topicWatchesTable
            ->select()
            ->from($topicWatchesTable->info('name'), 'watch')
            ->where('resource_id = ?', $event->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->where('user_id = ?', $viewer->getIdentity())
            ->limit(1)
            ->query()
            ->fetchColumn(0);
        $db = $event->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Create post
            $post = $postTable->createRow();
            $post->setFromArray($values);
            $post->save();
            // Watch
            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $event->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool)$watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool)$watch,
                ), array(
                    'resource_id = ?' => $event->getIdentity(),
                    'topic_id = ?' => $topic->getIdentity(),
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            }
            // Activity
            $action = $activityApi->addActivity($viewer, $topic, 'sesevent_topic_reply');
            if ($action) {
                $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
            }
            // Notifications
            $notifyUserIds = $topicWatchesTable->select()
                ->from($topicWatchesTable->info('name'), 'user_id')
                ->where('resource_id = ?', $event->getIdentity())
                ->where('topic_id = ?', $topic->getIdentity())
                ->where('watch = ?', 1)
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);
            foreach ($userTable->find($notifyUserIds) as $notifyUser) {
                // Don't notify self
                if ($notifyUser->isSelf($viewer)) {
                    continue;
                }
                if ($notifyUser->isSelf($topicOwner)) {
                    $type = 'sesevent_discussion_response';
                } else {
                    $type = 'sesevent_discussion_reply';
                }
                $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
                    'message' => $this->view->BBCode($post->body),
                ));
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have succussfully commented on this topic.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function stickyAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->sticky = (null === $this->_getParam('sticky') ? !$topic->sticky : (bool)$this->_getParam('sticky'));
            $topic->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succussfully maked Sticky to this Topic.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function renametopicAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        $form = $form = new Sesevent_Form_Topic_Rename();
        $form->populate($topic->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $title = $form->getValue('title');
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->title = htmlspecialchars($title);
            $topic->save();

            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully topic renamed.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
    }
    public function deletetopicAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $event = Engine_Api::_()->getItem('event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        $form = $form = new Sesevent_Form_Topic_Delete();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $event = $topic->getParent('sesevent_event');
            $topic->delete();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully deleted.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
        }
    }
    public function editpostAction(){
        $postid = $this->_getParam('post_id', $this->_getParam('topic_id' . null));
        if (!$postid)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'parameter_missing', 'result' => array()));
        $post = Engine_Api::_()->getItem('sesevent_post', $postid);
        $event = $post->getParent('sesevent_event');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$event->isOwner($viewer) && !$post->isOwner($viewer)) {
            if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid()) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
            }
        }
        $form = new Sesevent_Form_Post_Edit();
        $form->body->setValue(html_entity_decode($post->body));
        $form->populate($post->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array());
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        // Process
        $table = $post->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $post->setFromArray($form->getValues());
            $post->modified_date = date('Y-m-d H:i:s');
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $allowHtml = (bool)$settings->getSetting('sesevent_html', 0);
            $allowBbcode = (bool)$settings->getSetting('sesevent_bbcode', 0);
            if (!$allowBbcode && !$allowHtml) {
                $post->body = htmlspecialchars($post->body, ENT_NOQUOTES, 'UTF-8');
            }
            $post->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully Post edited.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function deletepostAction(){
        $postid = $this->_getParam('post_id', null);
        if (!$postid)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        $post = Engine_Api::_()->getItem('sesevent_post', $postid);
        $event = $post->getParent('sesevent_event');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$event->isOwner($viewer) && !$post->isOwner($viewer)) {
            if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid()) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
            }
        }
        // Process
        $table = $post->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $topic_id = $post->topic_id;
            $post->delete();

            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully Post deleted.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        // Try to get topic
        $topic = Engine_Api::_()->getItem('sesevent_topic', $topic_id);
        $href = (null === $topic ? $event->getHref() : $topic->getHref());
        return $this->_forward('success', 'utility', 'core', array(
            'closeSmoothbox' => true,
            'parentRedirect' => $href,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
        ));
    }
    public function watchAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $event = Engine_Api::_()->getItem('sesevent_event', $topic->event_id);
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'view')->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        $watch = $this->_getParam('watch', true);
        $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sesevent');
        $db = $topicWatchesTable->getAdapter();
        $db->beginTransaction();
        try {
            $isWatching = $topicWatchesTable
                ->select()
                ->from($topicWatchesTable->info('name'), 'watch')
                ->where('resource_id = ?', $event->getIdentity())
                ->where('topic_id = ?', $topic->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->limit(1)
                ->query()
                ->fetchColumn(0);

            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $event->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool)$watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool)$watch,
                ), array(
                    'resource_id = ?' => $event->getIdentity(),
                    'topic_id = ?' => $topic->getIdentity(),
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Watched.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
        }
    }
    public function uploadphotoAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $event = Engine_Api::_()->core()->getSubject();
        if (!$event)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This Event does not exist.'), 'result' => array()));
        $photo = $event->photo_id;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        $photo_id = $this->_getParam('photo_id',0);
        if($photo_id){
          $data = Engine_Api::_()->getItem('album_photo',$photo_id);
        }
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $event->setPhoto($data, '', 'profile');
        if ($photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $photo);
            $im->delete();
        }
        $file = array('main' => $event->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => $this->view->translate('Successfully photo uploaded.')));
    }
    public function removephotoAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        } else {
            $event = Engine_Api::_()->core()->getSubject();
        }
        if (!$event)
            $event = Engine_Api::_()->getItem('sesevent_event', $this->_getparam('event_id', null));
        if (!$event)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Event does not exists.'), 'result' => array()));
        if (isset($event->photo_id) && $event->photo_id > 0) {
            $event->photo_id = 0;
            $event->save();
        }
        $file = array('main' => $event->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully photo deleted.'), 'images' => $file));
    }
    public function uploadcoverAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $event = Engine_Api::_()->core()->getSubject();
        if (!$event)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'parameter_missing', 'result' => array()));
        $cover_photo = $event->cover_photo;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];

        $photo_id = $this->_getParam('photo_id',0);
        if($photo_id){
          $data = Engine_Api::_()->getItem('album_photo',$photo_id);
        }
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $event->setCoverPhoto($data);
        if ($cover_photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $cover_photo);
            $im->delete();
        }
        $file['main'] = $event->getCoverPhotoUrl();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Successfully cover photo uploaded.')));
    }
    public function removecoverAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $event = Engine_Api::_()->core()->getSubject();
        if (!$event)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (isset($event->cover_photo) && $event->cover_photo > 0) {
            $im = Engine_Api::_()->getItem('storage_file', $event->cover);
            $event->cover = 0;
            $event->save();
            $im->delete();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully deleted cover photo.'))));
        }else{
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'database_error', 'result' => array()));
        }
    }
    public function addtolistAction(){
        //Check auth
		$viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'addlist_event')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        //Set song
		 $listCount = Engine_Api::_()->getDbtable('lists', 'sesevent')->getListsCount(array('viewer_id' => $viewer->getIdentity(), 'column_name' => array('list_id', 'title')));
		 $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesevent_event', 'addlist_maxevent');
		if (!($quota > count($listCount) || $quota == 0))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error' , 'message' => $this->view->translate('You have already created the maximum number of lists allowed. If you would like to create a new list, please delete an old one first. Currently, you can only add events in your existing lists.')));
        $event = Engine_Api::_()->getItem('sesevent_event', $this->_getParam('event_id'));
        $event_id = $event->event_id;
        //Get form
        $form = new Sesevent_Form_Append();
        if ($form->list_id) {
            $alreadyExistsResults = Engine_Api::_()->getDbtable('listevents', 'sesevent')->getListEvents(array('column_name' => 'list_id', 'file_id' => $event_id));
            $allListIds = array();
            foreach ($alreadyExistsResults as $alreadyExistsResult) {
                $allListIds[] = $alreadyExistsResult['list_id'];
            }
            //Populate form
            $listTable = Engine_Api::_()->getDbtable('lists', 'sesevent');
            $select = $listTable->select()
                ->from($listTable, array('list_id', 'title'));
            if ($allListIds) {
                $select->where($listTable->info('name') . '.list_id NOT IN(?)', $allListIds);
            }
            $select->where('owner_id = ?', $viewer->getIdentity());
            $lists = $listTable->fetchAll($select);

            if ($lists)
                $lists = $lists->toArray();
            foreach ($lists as $list)
                $form->list_id->addMultiOption($list['list_id'], html_entity_decode($list['title']));
        }
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $values = $form->getValues();
        if (empty($values['list_id']) && empty($values['title']))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Please enter a title or select a list.'), 'result' => array()));
        //Process
        $listEventTable = Engine_Api::_()->getDbtable('lists', 'sesevent');
        $db = $listEventTable->getAdapter();
        $db->beginTransaction();
        try {
            //Existing list
            if (!empty($values['list_id'])) {
                $list = Engine_Api::_()->getItem('sesevent_list', $values['list_id']);
                //Already exists in list
                $alreadyExists = Engine_Api::_()->getDbtable('listevents', 'sesevent')->checkEventsAlready(array('column_name' => 'listevent_id', 'list_id' => $list->getIdentity(), 'listevent_id' => $event_id));
                if ($alreadyExists)
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This list already has this event.'), 'result' => array()));
            } //New list
            else {
                $list = $listEventTable->createRow();
                $list->title = trim($values['title']);
                $list->description = $values['description'];
                $list->owner_id = $viewer->getIdentity();
                $list->save();
            }
            $list->event_count++;
            $list->save();
            //Add song
            $list->addEvent($event->photo_id, $event_id);
            $listID = $list->getIdentity();
            //Photo upload for list
            if (!empty($values['mainphoto'])) {
                $previousPhoto = $list->photo_id;
                if ($previousPhoto) {
                    $listPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
                    $listPhoto->delete();
                }
                $list->setPhoto($form->mainphoto, 'mainPhoto');
            }
            if (!empty($_FILES['photo']['name']) && $_FILES['photo']['size'] > 0) {
                $previousPhoto = $list->photo_id;
                if ($previousPhoto) {
                    $listPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
                    $listPhoto->delete();
                }
                $list->setPhoto($_FILES['photo'], 'mainPhoto');
            }
            if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
                $storage = Engine_Api::_()->getItem('storage_file', $list->photo_id);
                $list->photo_id = 0;
                $list->save();
                if ($storage)
                    $storage->delete();
            }
            //Activity Feed work
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $event, "sesevent_list_create", '', array('list' => array($list->getType(), $list->getIdentity()),));
            if ($action) {
                Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $event);
            }
            $db->commit();
            //Response
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Event has been successfully added to your list.'))));
        } catch (Sesevent_Model_Exception $e) {
            $db->rollback();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        } catch (Exception $e) {
            $db->rollback();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function reviewsAction(){
        if (isset($_POST['params']))
            $params = json_decode($_POST['params'], true);
        if (isset($_POST['searchParams']) && $_POST['searchParams'])
            parse_str($_POST['searchParams'], $searchArray);

        $page = $this->_getParam('page', 1);
        $limit = $this->_getParam('limit', 10);
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $value['search_text'] = isset($searchArray['search_text']) ? $searchArray['search_text'] : (isset($_GET['search_text']) ? $_GET['search_text'] : (isset($params['search_text']) ? $params['search_text'] : ''));
        $value['order'] = isset($searchArray['order']) ? $searchArray['order'] : (isset($_GET['order']) ? $_GET['order'] : (isset($params['order']) ? $params['order'] : ''));
        $value['review_stars'] = isset($searchArray['review_stars']) ? $searchArray['review_stars'] : (isset($_GET['review_stars']) ? $_GET['review_stars'] : (isset($params['review_stars']) ? $params['review_stars'] : ''));
        $value['review_recommended'] = isset($searchArray['review_recommended']) ? $searchArray['review_recommended'] : (isset($_GET['review_recommended']) ? $_GET['review_recommended'] : (isset($params['review_recommended']) ? $params['review_recommended'] : ''));
        $stats = isset($params) ? $params : $this->_getParam('stats', array('featured', 'sponsored', 'likeCount', 'commentCount', 'viewCount', 'title', 'postedBy', 'pros', 'cons', 'description', 'creationDate', 'recommended', 'parameter', 'rating', 'likeButton', 'socialSharing'));
        $table = Engine_Api::_()->getDbTable('reviews', 'seseventreview');
        $params = array('search_text' => $value['search_text'], 'info' => str_replace('SP', '_', $value['order']), 'review_stars' => $value['review_stars'], 'review_recommended' => $value['review_recommended']);
        $event_id = $this->_getParam('event_id');
        if ($event_id)
            $params['content_id'] = $event_id;
        $select = $table->getEventReviewSelect($params);
        $paginator = Zend_Paginator::factory($select);
        //Set item count per page and current page number
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $counter = 0;
        foreach ($paginator as $item) {
            $reviewer = Engine_Api::_()->getItem('sesevent_event', $item->content_id);
            if (!$reviewer)
                continue;
            $result[$counter] = $item->toArray();
            $result[$counter]['event_title'] = $reviewer->getTitle();
            $result[$counter]['event_id'] = $item->content_id;
            $imagepath = $this->userImage($item->owner_id, 'thumb.profile');
            $result[$counter]['image'] = $imagepath;
            $result[$counter]['viewer_title'] = $item->getOwner()->getTitle();
            $counter++;
        }
        $resultdata['reviews'] = $result;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $resultdata), $extraParams));
    }
    public function saveAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $id = $this->_getParam('id');
        $type = $this->_getParam('type');
        if (!$id || !$type) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $contentId = $this->_getParam('contentId');
        $item = Engine_Api::_()->getItem($type, $id);
        if (empty($contentId)) {
            $isSave = Engine_Api::_()->getDbTable('saves', 'sesevent')->isSave(array('resource_type' => $type, 'resource_id' => $id));
            if (empty($isSave)) {
                $saveTable = Engine_Api::_()->getDbTable('saves', 'sesevent');
                $db = $saveTable->getAdapter();
                $db->beginTransaction();
                try {
                    if (!empty($item))
                        $contentId = $saveTable->addSave($item, $viewer)->save_id;
                    if ($viewer->getIdentity() != $item->getOwner()->getIdentity()) {
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item->getOwner(), $viewer, $item, 'sesevent_eventsave', array());
                        //Activity Feed Work
                        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                        $action = $activityApi->addActivity($viewer, $item, 'sesevent_event_save');
                        if ($action) {
                            $activityApi->attachActivity($action, $item);
                        }
                    }
                    $db->commit();
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully Saved.'), 'saved_id' => $contentId)));
                } catch (Exception $e) {
                    $db->rollBack();
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
                }
            } else {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have already saved to this event.'))));
            }
        } else {
            Engine_Api::_()->getDbTable('saves', 'sesevent')->delete(array('save_id =?' => $contentId));
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully Unsaved.'))));
        }
    }
    public function profilersvpsaveAction(){
        $eventid = $this->_getParam('event_id', null);
        if (!Engine_Api::_()->core()->hasSubject()) {
            $event = Engine_Api::_()->getItem('sesevent_event', $eventid);
        } else {
            $event = Engine_Api::_()->core()->getSubject();
        }
        if (!$event)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$event->membership()->isMember($viewer, true)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have need to join event first.'), 'result' => array()));
        }
        $row = $event->membership()->getRow($viewer);
        $viewer_id = $viewer->getIdentity();
        if ($row) {
            $rsvp = $row->rsvp;
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        if ($this->getRequest()->isPost()) {
            $option_id = $this->getRequest()->getParam('option_id');
            $row->rsvp = $option_id;
            $row->save();
            //Send mail to event owner when some change rsvp
            if ($row->rsvp == '0') {
                $rsvp_change = 'Not Attending';
            } elseif ($row->rsvp == '1') {
                $rsvp_change = 'Maybe Attending';
            } elseif ($row->rsvp == '2') {
                $rsvp_change = 'Attending';
            }

            if ($row) {
                $rsvp = $row->rsvp;
                $counterrsvp = 0;
                $result['RSVP'][$counterrsvp]['name'] = '2';
                $result['RSVP'][$counterrsvp]['label'] = $this->view->translate('Attending');
                $result['RSVP'][$counterrsvp]['value'] = $rsvp == 2;
                $counterrsvp++;
                $result['RSVP'][$counterrsvp]['name'] = '1';
                $result['RSVP'][$counterrsvp]['label'] = $this->view->translate('May be Attending');
                $result['RSVP'][$counterrsvp]['value'] = $rsvp == 1;
                $counterrsvp++;
                $result['RSVP'][$counterrsvp]['name'] = '0';
                $result['RSVP'][$counterrsvp]['label'] = $this->view->translate('Not Attending');
                $result['RSVP'][$counterrsvp]['value'] = $rsvp == 0;
                $counterrsvp++;
            }
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($event->getOwner(), 'sesevent_rsvp_change', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'viewer_name' => $viewer->getTitle(), 'host' => $_SERVER['HTTP_HOST'], 'rsvp_changetext' => $rsvp_change));
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
    }
    public function editAction(){
        $event_id = $this->_getParam('event_id', null);
        if (!$event_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!Engine_Api::_()->core()->hasSubject()) {
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
            Engine_Api::_()->core()->setSubject($event);
        } else {
            $event = Engine_Api::_()->core()->getSubject();
        }
        $values = $event->toArray();
        if (!$event)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data does not exist.'), 'result' => array()));
        $previous_starttime = $event->starttime;
        $previous_endtime = $event->endtime;
        $previous_venue_name = $event->venue_name;
        $previous_location = $event->location;
        //Event Category and profile fileds
        $defaultProfileId = 1;
        if (isset($event->category_id) && $event->category_id != 0)
            $category_id = $event->category_id;
        else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
            $category_id = $_POST['category_id'];
        else
            $category_id = 0;
        if (isset($event->subsubcat_id) && $event->subsubcat_id != 0)
            $subsubcat_id = $event->subsubcat_id;
        else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
            $subsubcat_id = $_POST['subsubcat_id'];
        else
            $subsubcat_id = 0;
        if (isset($event->subcat_id) && $event->subcat_id != 0)
            $subcat_id = $event->subcat_id;
        else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
            $subcat_id = $_POST['subcat_id'];
        else
            $subcat_id = 0;
        //Event category and profile fields
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data does not exist.'), 'result' => array()));
        // Create form
        $form = new Sesevent_Form_Edit(array('parent_type' => $event->parent_type, 'parent_id' => $event->parent_id, 'defaultProfileId' => $defaultProfileId, 'fromApi' => "true"));
        $tagStr = '';
        foreach ($event->tags()->getTagMaps() as $tagMap) {
            $tag = $tagMap->getTag();
            if (!isset($tag->text))
                continue;
            if ('' !== $tagStr)
                $tagStr .= ', ';
            $tagStr .= $tag->text;
        }
        $values['tags'] = $tagStr;
        $form->populate($values);
        if ($form->getElement('event_location')) {
            $form->event_location->setValue($event->location);
            $form->getElement('event_location')->setLabel('Location');
        }
        $form->removeElement('event_timezone_popup');
        $form->removeElement('event_custom_datetimes');
        $form->removeElement('event_timezone_popup');
        $form->removeElement('host_photo');
        $startDate = date('Y-m-d h:i:s', strtotime($event['starttime']));
        $start_date = date('m/d/y', strtotime($startDate));
        $start_time = date('g:i A', strtotime($startDate));
        $endDate = date('Y-m-d h:i:s', strtotime($event['endtime']));
        $end_date = date('m/d/y', strtotime($endDate));
        $end_time = date('g:i A', strtotime($endDate));
        if ($form->start_date)
            $form->start_date->setValue($start_date);
        if ($form->start_time)
            $form->start_time->setValue($start_time);
        if ($form->end_date)
            $form->end_date->setValue($end_date);
        if ($form->end_time)
            $form->end_time->setValue($end_time);
        $hostobj = Engine_Api::_()->getItem('sesevent_host', $event->host);
        $form->selectonsitehost->setValue($hostobj->user_id);

        if ($_GET['sesapi_platform'] == 1) {
            $form->start_time->setValue($startDate);
            $form->end_time->setValue($endDate);
        }
        if (!$this->getRequest()->isPost()) {
            // Populate auth
            $auth = Engine_Api::_()->authorization()->context;
            if ($event->parent_type == 'group')
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            else
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            foreach ($roles as $role) {
                if (isset($form->auth_view->options[$role]) && $auth->isAllowed($event, $role, 'view'))
                    $form->auth_view->setValue($role);
                if (isset($form->auth_comment->options[$role]) && $auth->isAllowed($event, $role, 'comment'))
                    $form->auth_comment->setValue($role);
                if (isset($form->auth_photo->options[$role]) && $auth->isAllowed($event, $role, 'photo'))
                    $form->auth_photo->setValue($role);
                if (isset($form->auth_topic->options[$role]) && $auth->isAllowed($event, $role, 'topic'))
                    $form->auth_topic->setValue($role);
                if (isset($form->auth_music->options[$role]) && $auth->isAllowed($event, $role, 'music'))
                    $form->auth_music->setValue($role);
            }
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.inviteguest', 1)) {
                $form->auth_invite->setValue($auth->isAllowed($event, 'member', 'invite'));
            }
        }
        if ($form->draft->getValue() == 1)
            $form->removeElement('draft');

        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if ($_GET['sesapi_platform'] != 1) {
            // Process
            $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'] . ' ' . $_POST['start_time'])) : '';
            $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'] . ' ' . $_POST['end_time'])) : '';
        } else {
            $starttime = isset($_POST['start_time']) ? date('Y-m-d H:i:s', strtotime($_POST['start_time'])) : '';
            $endtime = isset($_POST['end_time']) ? date('Y-m-d H:i:s', strtotime($_POST['end_time'])) : '';
        }
        // Process
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        if (strtotime($values['starttime']) > strtotime($values['endtime'])) {
            $form->addError($this->view->translate('Start Time must be less than End Time.'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        $values = $form->getValues();
        $values['timezone'] = $_POST['timezone'] ? $_POST['timezone'] : '';
        $values['location'] = $_POST['event_location'] ? $_POST['event_location'] : '';
        $values['show_timezone'] = !empty($_POST['show_timezone']) ? $_POST['show_timezone'] : '0';
        $values['show_endtime'] = !empty($_POST['show_endtime']) ? $_POST['show_endtime'] : '0';
        $values['show_starttime'] = !empty($_POST['show_starttime']) ? $_POST['show_starttime'] : '0';
        $values['venue_name'] = isset($_POST['venue_name']) ? $_POST['venue_name'] : '';
        if (empty($values['timezone'])) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Timezone is a required field.'), 'result' => array()));
        }
        // Convert times
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($values['timezone']);
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        date_default_timezone_set($oldTz);
        $values['starttime'] = date('Y-m-d H:i:s', $start);
        $values['endtime'] = date('Y-m-d H:i:s', $end);

        if (strtotime($values['starttime']) > strtotime($values['endtime'])) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Start Time must be less than End Time.'), 'result' => array()));
        }
        // Check parent
        if (!isset($values['host']) && $event->parent_type == 'group' && Engine_Api::_()->hasItemType('group')) {
            $group = Engine_Api::_()->getItem('group', $event->parent_id);
            $values['host'] = $group->getTitle();
        }
        // Process
        $db = Engine_Api::_()->getItemTable('sesevent_event')->getAdapter();
        $db->beginTransaction();
        try {
            $current_starttime = $values['starttime'];
            $current_endtime = $values['endtime'];
            $current_venue_name = isset($_POST['venue_name']) ? $_POST['venue_name'] : '';
            $current_location = $values['location'];
            if (!$values['is_custom_term_condition'])
                unset($values['custom_term_condition']);
            if (!($values['is_sponsorship']))
                $values['is_sponsorship'] = 0;
            //set location
            if (empty($_POST['lat'])) {
                unset($values['location']);
                unset($values['lat']);
                unset($values['lng']);
                unset($values['venue_name']);
                $values['is_webinar'] = 1;
            } else
                $values['is_webinar'] = 0;
            //Host save function
            if ($_POST['selectonsitehost'])
                $_POST['toValues'] = $_POST['selectonsitehost'];
            if ($_POST['host_type'] == 'offsite' && isset($_POST['toValues'])) {
                $host_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $_POST['toValues'], 'host_type' => 'offsite'));
                $values['host_type'] = 'offsite';
                if (!empty($host_id)) {
                    $values['host'] = $host_id;
                    unset($values['toValues']);
                } else {
                    $values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'offsite', $_POST);
                    unset($values['toValues']);
                }
            } elseif (($_POST['host_type'] == 'site' || $_POST['host_type'] == 'myself') && isset($_POST['toValues'])) {
                $values['host_type'] = 'site';
                if ($_POST['host_type'] == 'myself') {
                    $_POST['toValues'] = $viewer->getIdentity();
                    $_POST['host_type'] = 'site';
                }
                $host_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $_POST['toValues'], 'host_type' => 'site'));
                if (!empty($host_id)) {
                    $values['host'] = $host_id;
                    unset($values['toValues']);
                } else {
                    $values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'site', $_POST);
                    unset($values['toValues']);
                }
            } elseif ($_POST['host_type'] == 'upload') {

                $values['host_type'] = 'offsite';
                $values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'offsite', $_POST);
                unset($values['toValues']);
            }
            if (!($values['draft']))
                unset($values['draft']);
            $event->setFromArray($values);
            $event->save();
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            if (!$event->is_webinar) {
                //save value to sescore table for future use
                if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && !empty($_POST['event_location'])) {
                    $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                    $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $event->event_id . '","' . $_POST['event_location'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "sesevent_event")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
                }
            } else {
                $event->location = '';
                $event->save();
                //remove sescore entry
                $dbGetInsert->query("DELETE FROM engine4_sesbasic_locations WHERE resource_id = " . $event->event_id . ' AND resource_type = "sesevent_event"');
            }
            $event->save();
            $tags = preg_split('/[,]+/', $values['tags']);
            $event->tags()->setTagMaps($viewer, $tags);
            // Add photo
            if (!empty($values['photo'])) {
                $event->setPhoto($form->photo);
            }
            // Add cover photo
            if (!empty($values['cover'])) {
                $event->setCover($form->cover);
            }
            // Set auth
            $auth = Engine_Api::_()->authorization()->context;
            if ($event->parent_type == 'group')
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            else
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            if (empty($values['auth_view']))
                $values['auth_view'] = 'everyone';
            if (empty($values['auth_comment']))
                $values['auth_comment'] = 'everyone';
            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $photoMax = array_search($values['auth_photo'], $roles);
            $videoMax = array_search(@$values['auth_video'], $roles);
            $musicMax = array_search(@$values['auth_music'], $roles);
            $topicMax = array_search($values['auth_topic'], $roles);
            $ratingMax = array_search(@$values['auth_rating'], $roles);
            foreach ($roles as $i => $role) {
                $auth->setAllowed($event, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($event, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'photo', ($i <= $photoMax));
                $auth->setAllowed($event, $role, 'video', ($i <= $videoMax));
                $auth->setAllowed($event, $role, 'music', ($i <= $musicMax));
                $auth->setAllowed($event, $role, 'topic', ($i <= $topicMax));
                $auth->setAllowed($event, $role, 'rating', ($i <= $ratingMax));
            }
            $auth->setAllowed($event, 'member', 'invite', $values['auth_invite']);
            // Add an entry for member_requested
            $auth->setAllowed($event, 'member_requested', 'view', 1);
            //Add fields
            $customfieldform = $form->getSubForm('fields');
            $customfieldform->setItem($event);
            $customfieldform->saveValues();
            $event->save();
            if ($previous_location != $current_location) {
                //Activity Feed Work
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editlocation', '', array("editlocation" => '<b>' . $current_location . '</b>'));
                if ($action) {
                    $activityApi->attachActivity($action, $event);
                }
            }
            if ($previous_venue_name != $current_venue_name) {
                //Activity Feed Work
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editvenue', '', array("editvenue" => '<b>' . $current_venue_name . '</b>'));
                if ($action) {
                    $activityApi->attachActivity($action, $event);
                }
            }
            if ($previous_starttime != $current_starttime || $previous_endtime != $current_endtime) {
                $final_date = 'From <b>' . Engine_Api::_()->sesevent()->dateFormat($current_starttime) . '</b> To <b>' . Engine_Api::_()->sesevent()->dateFormat($current_endtime) . '</b>' . ' (' . $event->timezone . ')';
                //Activity Feed Work
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editeventdate', '', array("editDateFormat" => $final_date));
                if ($action) {
                    $activityApi->attachActivity($action, $event);
                }
            }
            $db->commit();
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('The image you selected was too large.'), 'result' => array()));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $db->beginTransaction();
        try {
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('event_id' => $event->getIdentity(), 'success_message' => $this->view->translate('Event edited successfully.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function listviewAction(){
        if (isset($_POST['params']))
            $params = $_POST['params'];
        $list_id = $this->getParam('list_id');
        $search = $this->_getParam('search_filter', null);
        if (!$list_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        else if (!$search) {
            $list = Engine_Api::_()->getItem('sesevent_list', $list_id);
            $result['list'] = $list->toArray();
            $ownerid = $list->owner_id;
            $owner = $user = Engine_Api::_()->getItem('user', $ownerid);
            $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
          if ($ownerimage)
            $result['list']['owner_image'] = $ownerimage;
            $LikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($list->list_id, $list->getType());
            $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type' => $list->getType(), 'resource_id' => $list->list_id));
            if ($LikeStatus) {
                $result['list']['is_content_like'] = true;
            } else {
                $result['list']['is_content_like'] = false;
            }
            if ($favStatus) {
                $result['list']['is_content_favourite'] = true;
            } else {
                $result['list']['is_content_favourite'] = false;
            }
            $imageURL = $list->getPhotoUrl();
            $result['list']['images']['main'] = $this->getbaseurl(false, $imageURL);
            $result['list']['created_by'] = $list->getOwner()->getTitle();
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewerId = $viewer->getIdentity();
            $liscounter = 0;
          if ($viewerId > 0) {
            if ($viewerId == $list->owner_id || $viewer->level_id == 1) {
                $result['list']['options'] [$liscounter]['name'] = 'edit';
                $result['list']['options'] [$liscounter]['label'] = $this->view->translate('Edit List');
                $liscounter++;
                $result['list']['options'] [$liscounter]['name'] = 'delete';
                $result['list']['options'] [$liscounter]['label'] = $this->view->translate('Delete List');
                $liscounter++;
            }
                $result['list']['share_list'] = true;
                $result['list']['options'][$liscounter]['label'] = $this->view->translate('Share');
                $result['list']['options'] [$liscounter]['name'] = 'share';
                $liscounter++;
                $result['list']["share"]["imageUrl"] = $this->getBaseUrl(false, $list->getPhotoUrl());
								$result['list']["share"]["url"] = $this->getBaseUrl(false,$list->getHref());
                $result['list']["share"]["title"] = $list->getTitle();
                $result['list']["share"]["description"] = strip_tags($list->getDescription());
                $result['list']["share"]['urlParams'] = array(
                    "type" => $list->getType(),
                    "id" => $list->getIdentity()
                );
            } else {
                $result['list']['share_list'] = false;
            }
            $result['list']['options'] [$liscounter]['label'] = $this->view->translate('Report');
            $result['list']['options'] [$liscounter]['name'] = 'report';
            $liscounter++;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if ($search) {
            switch ($search) {
                case 'all':
                    $params['order'] = 'ongoingSPupcomming';
                    break;
                case 'ongoing':
                    $params['order'] = 'ongoing';
                    break;
                case 'past':
                    $params['order'] = 'past';
                    break;
                case 'week':
                    $params['order'] = 'week';
                    break;
                case 'weekend':
                    $params['manageorder'] = 'weekend';
                    break;
                case 'month':
                    $params['manageorder'] = 'month';
                    break;
                case 'mostjoinevents':
                    $params['info'] = 'most_joined';
                    break;
                case 'latest':
                    $params['info'] = 'creation_date';
                    break;
                default:
                    $params['order'] = 'ongoingSPupcomming';
            }
        }
        $params['resource_id'] = $list_id;
        $params['resource_type'] = 'sesevent_list';
        $paginator = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $editdelete = 0;
        $menus = $this->_getParam('menus', null);
        if ($menus) {
            $countermenu = 0;
            $result['menus'][$countermenu]['name'] = 'latest';
            $result['menus'][$countermenu]['label'] = $this->view->translate('Latest');
            $countermenu++;
            $result['menus'][$countermenu]['name'] = 'ongoingSPupcomming';
            $result['menus'][$countermenu]['label'] = $this->view->translate('Upcoming & Ongoing');
            $countermenu++;
            $result['menus'][$countermenu]['name'] = 'ongoing';
            $result['menus'][$countermenu]['label'] = $this->view->translate('Ongoing');
            $countermenu++;
            $result['menus'][$countermenu]['name'] = 'past';
            $result['menus'][$countermenu]['label'] = $this->view->translate('Past');
            $countermenu++;
            $result['menus'][$countermenu]['name'] = 'week';
            $result['menus'][$countermenu]['label'] = $this->view->translate('This Week');
            $countermenu++;
            $result['menus'][$countermenu]['name'] = 'weekend';
            $result['menus'][$countermenu]['label'] = $this->view->translate('This Weekend');
            $countermenu++;
            $result['menus'][$countermenu]['name'] = 'month';
            $result['menus'][$countermenu]['label'] = $this->view->translate('This Month');
            $countermenu++;
            $result['menus'][$countermenu]['name'] = 'mostjoinevents';
            $result['menus'][$countermenu]['label'] = $this->view->translate('Most Joined Events');
            $countermenu++;
        }
        if ($search) {
            $events = $this->getevents($paginator, $editdelete);
            $result['events'] = $events;
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function lightboxAction(){
        $photo = Engine_Api::_()->getItem('sesevent_photo', $this->_getParam('photo_id'));
        $event_id = $this->_getparam('event_id', null);
        if ($photo && !$this->_getParam('album_id', null)) {
            $album_id = $photo->album_id;
        } else {
            $album_id = $this->_getParam('album_id', null);
        }
        if ($album_id) {
            $album = Engine_Api::_()->getItem('sesevent_album', $album_id);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_request', 'result' => array()));
        }
        if (!$this->_getparam('event_id', null)) {
            $event_id = $album->event_id;
        }
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        $photo_id = $photo->getIdentity();
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $albumData = array();
        if ($viewer->getIdentity() > 0) {
            $menu = array();
            $counterMenu = 0;
            $menu[$counterMenu]["name"] = "save";
            $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");
            $counterMenu++;
            $canEdit = $event->authorization()->isAllowed($viewer, 'edit');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "edit";
                $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");
                $counterMenu++;
            }
            $can_delete = $event->authorization()->isAllowed($viewer, 'delete');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "delete";
                $menu[$counterMenu]["label"] = $this->view->translate("Delete Photo");
                $counterMenu++;
            }
            $menu[$counterMenu]["name"] = "report";
            $menu[$counterMenu]["label"] = $this->view->translate("Report Photo");
            $counterMenu++;
            $menu[$counterMenu]["name"] = "makeprofilephoto";
            $menu[$counterMenu]["label"] = $this->view->translate("Make Profile Photo");
            $albumData['menus'] = $menu;
            $canComment = $event->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $albumData['can_comment'] = $canComment;
            $sharemenu = array();
            if ($viewer->getIdentity() > 0) {
                $sharemenu[0]["name"] = "siteshare";
                $sharemenu[0]["label"] = $this->view->translate("Share");
            }
            $sharemenu[1]["name"] = "share";
            $sharemenu[1]["label"] = $this->view->translate("Share Outside");
            $albumData['share'] = $sharemenu;
        }
        $condition = $this->_getParam('condition');
        if (!$condition) {
            $next = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, ">="), true);
            $previous = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, "<"), true);
            $array_merge = array_merge($previous, $next);
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
                $recArray = array();
                $reactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getPaginator();
                $counterReaction = 0;
                foreach ($reactions as $reac) {
                    if (!$reac->enabled)
                        continue;
                    $albumData['reaction_plugin'][$counterReaction]['reaction_id'] = $reac['reaction_id'];
                    $albumData['reaction_plugin'][$counterReaction]['title'] = $this->view->translate($reac['title']);
                    $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
                    $albumData['reaction_plugin'][$counterReaction]['image'] = $icon['main'];
                    $counterReaction++;
                }
            }
        } else {
            $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, $condition), true);
        }
        $albumData['module_name'] = 'sesevent';
        $albumData['photos'] = $array_merge;
        if (count($albumData['photos']) <= 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('No photo created in this album yet.'), 'result' => array()));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $albumData)));
    }
    public function editDescriptionAction(){
        $status = true;
        $error = false;
        $viewer = Engine_Api::_()->user()->getViewer();
        $photo = Engine_Api::_()->getItem('sesevent_photo', $this->_getParam('photo_id', 0));
        if ($status && !$error) {
            $values['title'] = $_POST['title'];
            $values['description'] = $_POST['description'];
            $values['location'] = $_POST['location'];
            //update location data in sesbasic location table
            if ($_POST['lat'] != '' && $_POST['lng'] != '') {
                $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $_POST['photo_id'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesalbum_photo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
            }
            $db = $photo->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $photo->setFromArray($values);
                $photo->save();
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Photo description edited successfully.')));
    }
    public function deletePhotoAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $photo = Engine_Api::_()->getItem('sesevent_photo', $this->_getParam('photo_id', 0));
        $album = $photo->getParent();
        $this->view->form = $form = new Sesalbum_Form_Photo_Delete();
        if (!$this->getRequest()->isPost())
            return;
        $db = $photo->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // delete files from server
            $filesDB = Engine_Api::_()->getDbtable('files', 'storage');
            $filePath = $filesDB->fetchRow($filesDB->select()->where('file_id = ?', $photo->file_id))->storage_path;
            unlink($filePath);
            $thumbPath = $filesDB->fetchRow($filesDB->select()->where('parent_file_id = ?', $photo->file_id))->storage_path;
            unlink($thumbPath);
            // Delete image and thumbnail
            $filesDB->delete(array('file_id = ?' => $photo->file_id));
            $filesDB->delete(array('parent_file_id = ?' => $photo->file_id));
            // Check activity actions
            $attachDB = Engine_Api::_()->getDbtable('attachments', 'activity');
            $actions = $attachDB->fetchAll($attachDB->select()->where('type = ?', 'sesevent_photo')->where('id = ?', $photo->photo_id));
            $actionsDB = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach ($actions as $action) {
                $action_id = $action->action_id;
                $attachDB->delete(array('type = ?' => 'sesevent_photo', 'id = ?' => $photo->photo_id));
                $action = $actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
                $count = $action->params['count'];
                if (!is_null($count) && ($count > 1)) {
                    $action->params = array('count' => (integer)$count - 1);
                    $action->save();
                } else {
                    $action->delete();
                }
            }
            $photo->delete();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Photo Deleted Successfully.')));
    }
    public function nextPreviousImage($photo_id, $album_id, $condition = "<="){
        $photoTable = Engine_Api::_()->getItemTable('sesevent_photo');
        $select = $photoTable->select()
            ->where('album_id =?', $album_id)
            ->where('event_id !=?', 0)
            ->where('photo_id ' . $condition . ' ?', $photo_id)
            ->order('order ASC')
            ->limit(20);
        return $photoTable->fetchAll($select);
    }
    public function getPhotos($paginator, $updateViewCount = false){
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        foreach ($paginator as $photos) {
            $photo = $photos->toArray();
            $photos->view_count = new Zend_Db_Expr('view_count + 1');
            $photos->save();
            $photo['user_title'] = $photos->getOwner()->getTitle();
            if ($viewer_id != 0) {
                $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $photo['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($photos);
            }
            $attachmentItem = $photos;
            if ($attachmentItem->getPhotoUrl())
                $photo["shareData"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());
            $photo["shareData"]["title"] = $attachmentItem->getTitle();
            $photo["shareData"]["description"] = strip_tags($attachmentItem->getDescription());
            $photo["shareData"]['urlParams'] = array(
                "type" => $photos->getType(),
                "id" => $photos->getIdentity()
            );
            if (is_null($photo["shareData"]["title"]))
                unset($photo["shareData"]["title"]);
            $owner = $photos->getOwner();
            $photo['owner']['title'] = $owner->getTitle();
            $photo['owner']['id'] = $owner->getIdentity();
            $photo["owner"]['href'] = $owner->getHref();
            if ($attachmentItem->getPhotoUrl())
                $album_photo['images']['main'] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());
            $photo['can_comment'] = $photos->getParent()->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $photo['module_name'] = 'album';
            if ($photo['can_comment']) {
                if ($viewer_id) {
                    $itemTable = Engine_Api::_()->getItemTable($photos->getType(), $photos->getIdentity());
                    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
                    $tableMainLike = $tableLike->info('name');
                    $select = $tableLike->select()
                        ->from($tableMainLike)
                        ->where('resource_type = ?', $photos->getType())
                        ->where('poster_id = ?', $viewer_id)
                        ->where('poster_type = ?', 'user')
                        ->where('resource_id = ?', $photos->getIdentity());
                    $resultData = $tableLike->fetchRow($select);
                    if ($resultData) {
                        $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id);
                        $photo['reaction_type'] = $item_activity_like->type;
                    }
                }
                $photo['resource_type'] = $photos->getType();
                $photo['resource_id'] = $photos->getIdentity();
                $table = Engine_Api::_()->getDbTable('likes', 'core');
                $recTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->info('name');
                $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
                $coreliketableName = $coreliketable->info('name');
                $select = $table->select()->from($table->info('name'), array('total' => new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?', $photos->getIdentity())->group('type')->setIntegrityCheck(false);
                $select->where('resource_type =?', $photos->getType());
                $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
                $select->joinLeft($recTable, $recTable . '.reaction_id =' . $coreliketableName . '.type', array('file_id'))->where('enabled =?', 1)->order('total DESC');

                $resultData = $table->fetchAll($select);
                $photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $reactionData = array();
                $reactionCounter = 0;
                if (count($resultData)) {
                    foreach ($resultData as $type) {
                        $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)', $type['total'], Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                        $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                        $reactionCounter++;
                    }
                    $photo['reactionData'] = $reactionData;
                }
                if ($photo['is_like']) {
                    $photo['is_like'] = true;
                    $like = true;
                    $type = $photo['reaction_type'];
                    $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type));
                    $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
                } else {
                    $photo['is_like'] = false;
                    $like = false;
                    $type = '';
                    $imageLike = '';
                    $text = 'Like';
                }
                if (empty($like)) {
                    $photo["like"]["name"] = "like";
                } else {
                    $photo["like"]["name"] = "unlike";
                }
                // Get tags
                $tags = array();
                foreach ($photos->tags()->getTagMaps() as $tagmap) {

                    $tag = $tagmap->getTag();
                    if (!isset($tag->text))
                        continue;
                    $tags[] = array_merge($tagmap->toArray(), array(
                        'id' => $tagmap->getIdentity(),
                        'text' => $tagmap->getTitle(),
                        'href' => $tagmap->getHref(),
                        'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                    ));

                }
                if ($tags)
                    $photo["tags"] = $tags;
                if ($type)
                    $photo["like"]["type"] = $type;
                if ($imageLike)
                    $photo["like"]["image"] = $imageLike;
                $photo["like"]["label"] = $this->view->translate($text);
                $photo['reactionUserData'] = $this->view->FluentListUsers($photos->likes()->getAllLikesUsers(), '', $photos->likes()->getLike($viewer), $viewer);
            }
            if (!count($album_photo['images']))
                $album_photo['images']['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl());
            $result[$counter] = array_merge($photo, $album_photo);
            $counter++;
        }
        return $result;
    }
    public function addmorephotosAction(){
        $album_id = $this->_getParam('album_id', false);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('sesevent_album', $album_id);
            $event_id = $album->event_id;
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $form = new Sesevent_Form_Album();
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        $photoTable = Engine_Api::_()->getDbTable('photos', 'sesevent');
        $uploadSource = $_FILES['attachmentImage'];
        $photoArray = array(
            'event_id' => $event->event_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'sesevent')->getAdapter();
        $db->beginTransaction();
        try {
            foreach ($uploadSource['name'] as $name) {
                $images['name'] = $name;
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
                $photo = $photo->setAlbumPhoto($images, false, false, $album);
                $photo->collection_id = $photo->album_id;
                $photo->save();
                $photosource[] = $photo->getIdentity();
                $counter++;
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $_POST['file'] = implode(' ', $uploadSource);
        $form->album->setValue($album_id);
        $album = $form->saveValues();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('album_id' => $album->album_id, 'message' => $this->view->translate('Photo added successfully.'))));
    }
    public function editalbumAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $album_id = $this->_getParam('album_id', false);
        if ($album_id)
            $album = Engine_Api::_()->getItem('sesevent_album', $album_id);
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));

        $event = Engine_Api::_()->getItem('sesevent_event', $album->event_id);
        if ($event) {
            Engine_Api::_()->core()->setSubject($event);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data does not exist.'), 'result' => array()));
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        // Make form
        $form = new Sesevent_Form_Album_Edit();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        $form->populate($album->toArray());
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        //is post
        if (!$form->isValid($this->getRequest()->getPost())) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'validation_error', 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        // Process
        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $values = $form->getValues();
            $album->setFromArray($values);
            $album->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate("You have successfully edtited this album."))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }// album delete action
    public function deletealbumAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $album_id = $this->_getParam('album_id', false);
        if ($album_id)
            $this->view->album = $album = Engine_Api::_()->getItem('sesevent_album', $album_id);
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        $event = Engine_Api::_()->getItem('event', $album->event_id);
        if ($event) {
            Engine_Api::_()->core()->setSubject($event);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'Data does not exist.', 'result' => array()));
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');
        $form = new Sesevent_Form_Album_Delete();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$album) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Album does not exists or not authorized to delete'), 'result' => array()));
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $album->delete();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('Message' => $this->view->translate('album deleted successfully.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function editlistAction(){
        //Only members can upload event
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        //Get list
        $list = Engine_Api::_()->getItem('sesevent_list', $this->_getParam('list_id'));
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() != $list->owner_id && $viewer->level_id != 1) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        }
        //Make form
        $form = new Sesevent_Form_EditList();
        if ($form->getElement('list_id'))
            $form->removeElement('list_id');
        $form->populate($list->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        $form->populate($list->toarray());
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $values = $form->getValues();
        unset($values['file']);
        $db = Engine_Api::_()->getDbTable('lists', 'sesevent')->getAdapter();
        $db->beginTransaction();
        try {
            $list->title = $values['title'];
            $list->description = $values['description'];
            $list->is_private = $values['is_private'];
            $list->save();
            //Photo upload for list
            if (!empty($values['mainphoto'])) {
                $previousPhoto = $list->photo_id;
                if ($previousPhoto) {
                    $listPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
                    $listPhoto->delete();
                }
                $list->setPhoto($form->mainphoto, 'mainPhoto');
            } else if (!empty($_FILES['photo'])) {
                $previousPhoto = $list->photo_id;
                if ($previousPhoto) {
                    $listPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
                    $listPhoto->delete();
                }
                $list->setPhoto($_FILES["photo"], 'mainPhoto');
            }
            if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
                $storage = Engine_Api::_()->getItem('storage_file', $list->photo_id);
                $list->photo_id = 0;
                $list->save();
                if ($storage)
                    $storage->delete();
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('List edited successfully.'))));
        } catch (Exception $e) {
            $db->rollback();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function deletelistAction(){
        $list = Engine_Api::_()->getItem('sesevent_list', $this->getRequest()->getParam('list_id'));
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() != $list->owner_id && $viewer->level_id != 1) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        if (!$list) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'List does not exists or not authorized to delete', 'result' => array()));
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>  $this->view->translate('Invalid request method'), 'result' => array()));
        }
        $db = $list->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            //Delete all list events which is related to this list
            Engine_Api::_()->getDbtable('listevents', 'sesevent')->delete(array('list_id =?' => $this->_getParam('list_id')));
            $list->delete();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('The selected list has been deleted.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function createreviewsAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $event_id = $this->_getParam('event_id', null);
		if(!$event_id)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        $item = Engine_Api::_()->getItemByGuid($this->getParam('type'));
        if (!Engine_Api::_()->authorization()->getPermission($levelId, 'seseventreview_review', 'create'))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        if (!$item)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid Type'), 'result' => array()));
        //check review exists
        $isReview = Engine_Api::_()->getDbtable('reviews', 'seseventreview')->isReview(array('content_id' => $item->getIdentity(), 'content_type' => $item->getType(), 'module_name' => 'sesevent', 'column_name' => 'review_id'));
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.owner', 1)) {
            $allowedCreate = true;
        } else {
            if ($item->user_id == $viewer->getIdentity())
                $allowedCreate = false;
            else
                $allowedCreate = true;
        }
        if ($isReview || !$allowedCreate)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        if (isset($item->category_id) && $item->category_id != 0)
            $category_id = $item->category_id;
        else
            $category_id = 0;
        if (isset($item->subsubcat_id) && $item->subsubcat_id != 0)
            $subsubcat_id = $item->subsubcat_id;
        else
            $subsubcat_id = 0;
        if (isset($item->subcat_id) && $item->subcat_id != 0)
            $subcat_id = $item->subcat_id;
        else
            $subcat_id = 0;
        $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'seseventreview')->profileFieldId();
        $form = new Seseventreview_Form_Review_Create(array('defaultProfileId' => $defaultProfileId));
        $title = Zend_Registry::get('Zend_Translate')->_('Write a Review for "<b>%s</b>".');
        $form->setTitle(sprintf($title, $item->getTitle()));
        $form->setDescription("Please fill below information.");
        $form->removeElement('submit');
        $form->removeElement('review_star');
        $form->removeElement('review_parameters');
        $form->addElement('dummy', 'rate_value', array(
            'label' => 'Review',
            'required' => 'false',
            'value' => '',
        ));
		$form->addElement('dummy', 'rate_value', array(
            'label' => 'Review',
            'required' => 'false',
            'value' => '',
        ));
        $form->addElement('Button', 'submit', array(
            'label' => 'Submit',
            'type' => 'submit',
            'ignore' => true,
        ));
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $values = $form->getValues();
        $values['rating'] = $_POST['rate_value'];
        $values['owner_id'] = $viewer->getIdentity();
        $values['module_name'] = strtolower($item->getModuleName());
        $values['content_type'] = $item->getType();
        $values['content_id'] = $item->getIdentity();
        $reviews_table = Engine_Api::_()->getDbtable('reviews', 'seseventreview');
        $db = $reviews_table->getAdapter();
        $db->beginTransaction();
        try {
            $review = $reviews_table->createRow();
            $review->setFromArray($values);
            $review->save();
             $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            $viewMax = array_search('everyone', $roles);
            $commentMax = array_search('everyone', $roles);

            foreach( $roles as $i => $role ) {
              $auth->setAllowed($review, $role, 'view', ($i <= $viewMax));
              $auth->setAllowed($review, $role, 'comment', ($i <= $commentMax));
            }
            $dbObject = Engine_Db_Table::getDefaultAdapter();
            //tak review ids from post
            $table = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview');
            $tablename = $table->info('name');
            foreach ($_POST as $key => $reviewC) {
                if (count(explode('_', $key)) != 3 || !$reviewC)
                    continue;
                $key = str_replace('review_parameter_', '', $key);
                if (!is_numeric($key))
                    continue;
                $parameter = Engine_Api::_()->getItem('seseventreview_parameter', $key);
                $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`resources_type`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $viewer->getIdentity() . '","' . $item->getIdentity() . '","sesevent_event","' . $review->getIdentity() . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
                $dbObject->query($query);
                $ratingP = $table->getRating($key, $review->content_type);
                $parameter->rating = $ratingP;
                $parameter->save();
            }
            $db->commit();
            //save rating in parent table if exists
            if (isset($item->rating)) {
                $item->rating = Engine_Api::_()->getDbtable('reviews', 'seseventreview')->getRating($review->content_id, $review->content_type);
                $item->save();
            }
            //Add fields
            $customfieldform = $form->getSubForm('fields');
            $customfieldform->setItem($review);
            $customfieldform->saveValues();
            $review->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have reviewed successfully.'), 'review_id' => $review->review_id)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array('review_id' => 0)));
        }
    }
    public function editreviewsAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $review_id = $this->_getParam('review_id', null);
        if (!$review_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        $subject = Engine_Api::_()->getItem('seseventreview_review', $review_id);
        if (!$subject)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
        if (!Engine_Api::_()->authorization()->getPermission($levelId, 'seseventreview_review', 'edit'))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $item = Engine_Api::_()->getItem($subject->content_type, $subject->content_id);
        if (isset($item->category_id) && $item->category_id != 0)
            $category_id = $item->category_id;
        else
            $category_id = 0;
        if (isset($item->subsubcat_id) && $item->subsubcat_id != 0)
            $subsubcat_id = $item->subsubcat_id;
        else
            $subsubcat_id = 0;
        if (isset($item->subcat_id) && $item->subcat_id != 0)
            $subcat_id = $item->subcat_id;
        else
            $subcat_id = 0;
        if (!$review_id || !$subject)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
        $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'seseventreview')->profileFieldId();
        $form = new Seseventreview_Form_Review_Edit(array('defaultProfileId' => $defaultProfileId));
        $title = Zend_Registry::get('Zend_Translate')->_('Edit a Review for "<b>%s</b>".');
        if ($form->review_parameters)
            $form->removeElement('review_parameters');
        $form->setTitle(sprintf($title, $subject->getTitle()));
        $form->setDescription("Please fill below information.");
        $form->populate($subject->toArray());
        $form->rate_value->setValue($subject->rating);
        $form->review_star->setValue($subject->rating);
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $values = $form->getValues();
        $values['rating'] = $_POST['review_star'];
        $reviews_table = Engine_Api::_()->getDbtable('reviews', 'seseventreview');
        $db = $reviews_table->getAdapter();
        $db->beginTransaction();
        try {
            $subject->setFromArray($values);
            $subject->save();
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            $viewMax = array_search('everyone', $roles);
            $commentMax = array_search('everyone', $roles);

            foreach( $roles as $i => $role ) {
              $auth->setAllowed($subject, $role, 'view', ($i <= $viewMax));
              $auth->setAllowed($subject, $role, 'comment', ($i <= $commentMax));
            }
            //save rating in parent table if exists
            //tak review ids from post
            $table = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview');
            $tablename = $table->info('name');
            $dbObject = Engine_Db_Table::getDefaultAdapter();
            foreach ($_POST as $key => $reviewC) {
                if (count(explode('_', $key)) != 3 || !$reviewC)
                    continue;
                $key = str_replace('review_parameter_', '', $key);
                if (!is_numeric($key))
                    continue;
                $parameter = Engine_Api::_()->getItem('seseventreview_parameter', $key);
                $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`resources_type`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $subject->owner_id . '","' . $item->getIdentity() . '","sesevent_event","' . $subject->getIdentity() . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
                $dbObject->query($query);
                $ratingP = $table->getRating($key, $subject->content_type);
                $parameter->rating = $ratingP;
                $parameter->save();
            }
            if (isset($item->rating)) {
                $item->rating = Engine_Api::_()->getDbtable('reviews', 'seseventreview')->getRating($subject->content_id, $subject->content_type);
                $item->save();
            }
            //Add fields
            $customfieldform = $form->getSubForm('fields');
            $customfieldform->setItem($subject);
            $customfieldform->saveValues();
            $subject->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have edited  to review successfully.'), 'review_id' => $subject->review_id)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array('review_id' => 0)));
        }
    }
    public function deletereviewsAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $review = Engine_Api::_()->getItem('seseventreview_review', $this->getRequest()->getParam('type'));
        $content_item = Engine_Api::_()->getItem($review->content_type, $review->content_id);
        if (!$this->_helper->requireAuth()->setAuthParams($review, $viewer, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        if ($this->getRequest()->isPost()) {
            $db = $review->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $review->delete();
                $db->commit();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('The selected review has been deleted.'))));
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
        }
    }
    public function reviewviewAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if (Engine_Api::_()->core()->hasSubject('seseventreview_review'))
            $review = $subject = Engine_Api::_()->core()->getSubject();
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
        $review_id = $this->_getParam('review_id', null);
        if (!$this->_helper->requireAuth()->setAuthParams('seseventreview_review', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        //Increment view count
        if (!$viewer->isSelf($subject->getOwner())) {
            $subject->view_count++;
            $subject->save();
        }
        $event = Engine_Api::_()->getItem($review->content_type, $review->content_id);
        $currentTime = time();
        //don't render widget if event ends
        if (strtotime($event->starttime) > ($currentTime))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
        $result['review'] = $review->toArray();
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.share', 1) && $viewer->getIdentity()) {
            $result['review']["share"]["imageUrl"] = $this->userImage($review->owner_id, 'thumb.profile');
						$result['review']["share"]["url"] = $this->getBaseUrl(false,$review->getHref());
            $result['review']["share"]["title"] = $review->getTitle();
            $result['review']["share"]["description"] = strip_tags($review->getDescription());
            $result['review']["share"]['urlParams'] = array(
                "type" => $review->getType(),
                "id" => $review->getIdentity()
            );
        }
        $coreMenuApi = Engine_Api::_()->getApi('menus', 'core');
        $navigation = $coreMenuApi->getNavigation('sesevent_reviewprofile');
        $counter = 0;
        foreach ($navigation as $menus) {
            $label = $this->view->translate($menus->getLabel());
            if ($label == 'Edit Review') {
                $action = 'edit';
            } else if ($label == 'Delete Review') {
                $action = 'delete';
            } else if ($label == 'Report') {
                $action = 'report';
            } else if ($label == 'Share') {
                $action = 'share';
            }
            $result['review']['options'][$counter]['label'] = $label;
            $result['review']['options'][$counter]['action'] = $action;
            $counter++;
        }
        $user = Engine_Api::_()->getItem('user', $review->owner_id);
        $imagepath = $user->getPhotoUrl('thumb.profile');
        if ($imagepath)
            $result['review']['owner_image'] = $this->getBaseUrl(false, $imagepath);
        $result['review']['event_title'] = $event->getTitle();
        $result['review']['event_id'] = $event->getIdentity();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function eventreviewAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $event_id = $this->_getParam('event_id', null);
        $subject = Engine_Api::_()->getItem('sesevent_event', $event_id);
        if (!$event_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        $table = Engine_Api::_()->getDbTable('reviews', 'seseventreview');
        $params = array('content_id' => $event_id);
        $select = $table->getEventReviewSelect($params);
        $paginator = Zend_Paginator::factory($select);
        //Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('limit', 20));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $counter = 0;
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.owner', 0)) {
            $cancreate = true;
        } else {
            if ($subject->user_id == $viewer->getIdentity())
                $cancreate = false;
            else
                $cancreate = true;
        }
        if (!$this->_helper->requireAuth()->setAuthParams('seseventreview_review', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $permission = $viewer->getIdentity() ? $viewer : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$allowedCreate= Engine_Api::_()->authorization()->getPermission($permission, 'seseventreview_review', 'create');
		//$allowedCreate = Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating();
		$isReview = Engine_Api::_()->getDbtable('reviews', 'seseventreview')->isReview(array('content_id' => $subject->getIdentity(), 'content_type' => $subject->getType(), 'module_name' => 'sesevent', 'column_name' => 'review_id'));
        if ($allowedCreate && $viewer->getIdentity() && $cancreate && !$isReview) {
            $button['label'] = $this->view->translate('Write a Review');
            $button['name'] = 'writeareview';
            $resultdata['post_button'] = $button;
        }
        $resultdata['event_title'] = $subject->getTitle();
        foreach ($paginator as $item) {
            $reviewer = Engine_Api::_()->getItem('sesevent_event', $item->content_id);
            if (!$reviewer)
                continue;
            $result[$counter] = $item->toArray();
            $result[$counter]['event_title'] = $reviewer->getTitle();
            $result[$counter]['event_id'] = $item->content_id;
            $owner = $item->getOwner();
            $imagepath = $owner->getPhotoUrl('thumb.profile');
            if ($imagepath)
                $result[$counter]['image'] = $this->getBaseUrl(false, $imagepath);
            $result[$counter]['viewer_title'] = $item->getOwner()->getTitle();
            $counter++;
        }
        $resultdata['reviews'] = $result;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $resultdata), $extraParams));
    }
    public function inviteAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('sesevent_event')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'user_not_autheticate', 'result' => array()));
        // @todo auth
        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();
        $event = Engine_Api::_()->core()->getSubject();
        // Prepare friends
        $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
        $friendsIds = $friendsTable->select()
            ->from($friendsTable, 'user_id')
            ->where('resource_id = ?', $viewer->getIdentity())
            ->where('active = ?', true)
            ->limit(100)
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);
        if (!empty($friendsIds)) {
            $friends = Engine_Api::_()->getItemTable('user')->find($friendsIds);
        } else {
            $friends = array();
        }
        // Prepare form
        $form = new Sesevent_Form_Invite();
        $count = 0;
        foreach ($friends as $friend) {
            if ($event->membership()->isMember($friend, null)) {
                continue;
            }
            $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
            $count++;
        }
        if ($count == 1)
            $form->removeElement('all');
        else if (!$count)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have no friends you can invite.'))));
        if ($this->_getParam('getForm')) {
            if ($form->getElement('all'))
                $form->getElement('all')->setName('sesevent_choose_all');

            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        // Not posting
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Process
        $table = $event->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $usersIds = $form->getValue('users');
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            foreach ($friends as $friend) {
                if (!in_array($friend->getIdentity(), $usersIds)) {
                    continue;
                }
                $event->membership()->addMember($friend)->setResourceApproved($friend);
                $notifyApi->addNotification($friend, $viewer, $event, 'sesevent_invite');
            }
            if ($count > 1) {
                $message = $this->view->translate('All members invited.');
            } else {
                $message = $this->view->translate('member invited.');
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $message)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function listbrowsesearchAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $searchOptionsType = $this->_getParam('searchOptionsType', array('searchBox', 'view', 'show'));
        $formFilter = new Sesevent_Form_SearchList();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($formFilter);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
    }
    public function hostbrowsesearchAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $searchOptionsType = $this->_getParam('searchOptionsType', array('searchBox', 'view', 'show'));
        $formFilter = new Sesevent_Form_SearchHost();
        $formFilter->removeElement('loading-img-sesevent-host');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($formFilter);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
    }
    public function followHostAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        $id = $this->_getParam('id');
        $type = $this->_getParam('type');
        $contentId = $this->_getParam('contentId');
        $item = Engine_Api::_()->getItem($type, $id);
        if (empty($contentId)) {
            $isFollow = Engine_Api::_()->getDbTable('follows', 'sesevent')->isFollow(array('resource_type' => $type, 'resource_id' => $id));

            if (empty($isFollow)) {
                $followTable = Engine_Api::_()->getDbTable('follows', 'sesevent');
                $db = $followTable->getAdapter();
                $db->beginTransaction();
                try {
                    if (!empty($item))
                        $contentId = $followTable->addFollow($item, $viewer)->follow_id;
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item->getOwner(), $viewer, $item, 'sesevent_eventfollow', array());
                    //Activity Feed Work
                    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                    $action = $activityApi->addActivity($viewer, $item, 'sesevent_event_follow');
                    if ($action) {
                        $activityApi->attachActivity($action, $item);
                    }
                    $db->commit();
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully followed.'), 'follow_id' => $contentId)));
                } catch (Exception $e) {
                    $db->rollBack();
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
                }
            } else {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Already followed.'), 'follow_id' => $isFollow)));
            }
        } else {
            Engine_Api::_()->getDbTable('follows', 'sesevent')->delete(array('follow_id =?' => $contentId));
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully unfollowed.'), 'follow_id' => 0)));
        }
    }

	 public function createAlbumAction() {
     $event_id = $this->_getParam('event_id',false);
	 $album_id = $this->_getParam('album_id',false);
    if($album_id){
    	$album = Engine_Api::_()->getItem('sesevent_album', $album_id);
			$event_id = $event_id = $album->event_id;
		}else{
			$event_id = $event_id = $event_id;
		}
		$event =  Engine_Api::_()->getItem('sesevent_event', $event_id);
    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $current_count =Engine_Api::_()->getDbtable('albums', 'sesevent')->getUserAlbumCount($values);
    $quota = 0;
    // Get form
    $form = new Sesevent_Form_Album();
	$form->removeElement('fancyuploadfileids');
	$form->removeElement('tabs_form_albumcreate');
	$form->removeElement('drag-drop');
	$form->removeElement('from-url');
	$form->removeElement('file_multi');
	$form->removeElement('uploadFileContainer');
	// Render
	if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesevent_event'));
        }
	if (!$form->isValid($this->getRequest()->getPost())){
	  $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
		if (count($validateFields))
			$this->validateFormFields($validateFields);
	}
    $db = Engine_Api::_()->getItemTable('sesevent_album')->getAdapter();
    $db->beginTransaction();
    try {
		  $photoTable = Engine_Api::_()->getDbTable('photos', 'sesevent');
		$uploadSource = $_FILES['image'];
        $photoArray = array(
            'event_id' => $event->event_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
		//process
		$db = Engine_Api::_()->getDbtable('photos', 'sesevent')->getAdapter();
        $db->beginTransaction();
        try {
                $images['name'] = $name;
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
                $photo = $photo->setAlbumPhoto($uploadSource, false, false, $album);
                $photo->collection_id = $photo->album_id;
                $photo->save();
                $photosource[] = $photo->getIdentity();
                $counter++;
				 $db->commit();
		} catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
           $_POST['event_id'] = $event_id;
        $_POST['file'] = implode(' ', $photosource);
      $album = $form->saveValues();
      // Add tags
      $values = $form->getValues();
      $db->commit();
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Created.'), album_id => $album->getIdentity()))));
    } catch (Exception $e) {
      $db->rollBack();
      $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array())));
    }
  }


}

