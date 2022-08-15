<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: DashboardController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_DashboardController extends Core_Controller_Action_Standard {
  public function init() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $level = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
    if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'view')->isValid())
      return;
    if (!$this->_helper->requireUser->isValid())
      return;
    $id = $this->_getParam('event_id', null);
    $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
    if ($event_id) {
      $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
      // if ($event && $event->is_approved)
        Engine_Api::_()->core()->setSubject($event);
      // else
      //   return $this->_forward('requireauth', 'error', 'core');
    } else
      return $this->_forward('requireauth', 'error', 'core');
		if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;
  }

  public function editAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $previous_starttime  = $event->starttime;
    $previous_endtime = $event->endtime;
    $previous_venue_name = $event->venue_name;
    $previous_location = $event->location;
    //Event Category and profile fileds
    $this->view->defaultProfileId = $defaultProfileId = 1; //Engine_Api::_()->getDbTable('metas', 'sesevent')->profileFieldId();
    if (isset($event->category_id) && $event->category_id != 0)
      $this->view->category_id = $event->category_id;
    else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
      $this->view->category_id = $_POST['category_id'];
    else
      $this->view->category_id = 0;
    if (isset($event->subsubcat_id) && $event->subsubcat_id != 0)
      $this->view->subsubcat_id = $event->subsubcat_id;
    else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
      $this->view->subsubcat_id = $_POST['subsubcat_id'];
    else
      $this->view->subsubcat_id = 0;
    if (isset($event->subcat_id) && $event->subcat_id != 0)
      $this->view->subcat_id = $event->subcat_id;
    else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
      $this->view->subcat_id = $_POST['subcat_id'];
    else
      $this->view->subcat_id = 0;
    
    //Event category and profile fields
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
     return $this->_forward('notfound', 'error', 'core');
      $timesChangesTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.limit.change.title', 2);
      $timesChangesTitleRemain = $timesChangesTitle - $event->change_title_count;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Edit(array('parent_type' => $event->parent_type, 'parent_id' => $event->parent_id, 'defaultProfileId' => $defaultProfileId, 'timeChangeTitleRemain'=>$timesChangesTitleRemain));

    $this->view->category_id = $event->category_id;
    $this->view->subcat_id = $event->subcat_id;
    $this->view->subsubcat_id = $event->subsubcat_id;
    $tagStr = '';
    foreach ($event->tags()->getTagMaps() as $tagMap) {
      $tag = $tagMap->getTag();
      if (!isset($tag->text))
        continue;
      if ('' !== $tagStr)
        $tagStr .= ', ';
      $tagStr .= $tag->text;
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
        if (isset($form->auth_video->options[$role]) && $auth->isAllowed($event, $role, 'video'))
          $form->auth_video->setValue($role);
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.inviteguest', 1)) {
	      $form->auth_invite->setValue($auth->isAllowed($event, 'member', 'invite'));
      }
      $locale = Zend_Registry::get('Zend_Translate')->getLocale();
      $event['additional_costs_amount'] = Zend_Locale_Format::toNumber($event['additional_costs_amount'],
        array('locale' =>  $locale,
              'precision' => 2)
      );

      $form->populate($event->toArray());
      $form->populate(array('tags' => $tagStr));
      $form->populate(array(
          'networks' => explode(",",$event->networks),
          'levels' => explode(",",$event->levels)
      ));
      if ($form->draft->getValue() == 1)
        $form->removeElement('draft');
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    //check custom url
    /*if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
      $custom_url = Engine_Api::_()->getDbtable('events', 'sesevent')->checkCustomUrl($_POST['custom_url'], $event->event_id);
      if ($custom_url) {
        $form->addError($this->view->translate("Custom Url not available.Please select other."));
        return;
      }
    }*/

        // if enddate is disabled, smaller endtime means the next day
    $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
    if($_POST['end_date'] == null) {
      $_POST['end_date'] = $_POST['start_date'];
      $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['end_date'].' '.$_POST['end_time'])) : '';
      if(strtotime($starttime) >= strtotime($endtime)) {
        $endtime = date('Y-m-d H:i:s', strtotime($endtime . " +1 day"));
      }
    } else {
      $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['end_date'].' '.$_POST['end_time'])) : '';
    }
    // Process
      $values = $form->getValues();
      $values['timezone'] = $_POST['timezone'] ? $_POST['timezone'] : '';
      $values['location'] = $_POST['location'] ? $_POST['location'] : '';
      $values['show_timezone'] = !empty($_POST['show_timezone']) ? $_POST['show_timezone'] : '0';
      $values['show_endtime'] = !empty($_POST['show_endtime']) ? $_POST['show_endtime'] : '0';
      $values['show_starttime'] = !empty($_POST['show_starttime']) ? $_POST['show_starttime'] : '0';
      $values['venue_name'] = isset($_POST['venue_name']) ? $_POST['venue_name'] : '';
      $values['region_id'] = $_POST['region'] ?? '';
      if (empty($values['timezone'])) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Timezone is a required field.'));
          return;
      }

    if($values['is_additional_costs'] == ''){
      $values['is_additional_costs'] = 0;
      $values['additional_costs_amount'] = 0;
      $values['additional_costs_description'] = null;
      $values['additional_costs_amount_currency'] = null;
    }
    else if(isset($values['additional_costs_amount'])){
      $values['additional_costs_amount'] = floatval(str_replace(",", ".", $values['additional_costs_amount']));
      $values['additional_costs_amount_currency'] = Engine_Api::_()->sesbasic()->getCurrentCurrency();
    }
    if(isset($values['age_categories'])) {
      $age_categories = $event->getAgeCategoriesToInterval($values['age_categories']);
      $values['age_category_from'] = $age_categories['from'];
      $values['age_category_to'] = $age_categories['to'];
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
      $form->addError(Zend_Registry::get('Zend_Translate')->_('Start Time must be less than End Time.'));
      return;
    }
    // Check parent
    if (!isset($values['host']) && $event->parent_type == 'group' && Engine_Api::_()->hasItemType('group')) {
      $group = Engine_Api::_()->getItem('group', $event->parent_id);
      $values['host'] = $group->getTitle();
    }
    // Process
    $db = Engine_Api::_()->getItemTable('sesevent_event')->getAdapter();
    $db->beginTransaction();
      $timesChangesTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.limit.change.title', 2);
      $isTitleChange = (strcmp($event->title, $values['title']) != 0);
      if ($event->change_title_count >= $timesChangesTitle) {
          if (!$viewer->isAdmin() && $isTitleChange) {
              $form->addError(Zend_Registry::get('Zend_Translate')->_('The event has exceeded the number of title changes.'));
              return;
          }
      } else if (!$viewer->isAdmin() && $isTitleChange) {
          $event->change_title_count++;
      }
    try {
	    $current_starttime = $values['starttime'];
	    $current_endtime = $values['endtime'];
	    $current_venue_name = isset($_POST['venue_name']) ? $_POST['venue_name'] : '';
	    $current_location = $values['location'];
      if (!$values['is_custom_term_condition'])
        unset($values['custom_term_condition']);
		  if(!($values['is_sponsorship']))
			  $values['is_sponsorship'] = 0;
      //set location
      if (empty($_POST['location'])) {
        unset($values['location']);
        unset($values['lat']);
        unset($values['lng']);
        unset($values['venue_name']);
        $values['is_webinar'] = 1;
      } else
        $values['is_webinar'] = 0;

      if(isset($values['levels']))
          $values['levels'] = implode(',',$values['levels']);

      if(isset($values['networks']))
          $values['networks'] = implode(',',$values['networks']);

      //Host save function
      if($_POST['host_type'] == 'offsite' && isset($_POST['toValues'])) {
				$host_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $_POST['toValues'], 'host_type' => 'offsite'));
				$values['host_type'] = 'offsite';
				if(!empty($host_id)) {
					$values['host'] = $host_id;
					unset($values['toValues']);
				}else {
					$values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'offsite',$_POST);
					unset($values['toValues']);
				}
			} elseif(($_POST['host_type'] == 'site' || $_POST['host_type'] == 'myself') && isset($_POST['toValues'])) {
				$values['host_type'] = 'site';
				if($_POST['host_type'] == 'myself'){
					$_POST['toValues'] = $viewer->getIdentity();
					$_POST['host_type'] = 'site';
				}
				$host_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $_POST['toValues'], 'host_type' => 'site'));
				if(!empty($host_id)) {
					$values['host'] = $host_id;
					unset($values['toValues']);
				} else {
					$values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form,'site',$_POST);
					unset($values['toValues']);
				}
			} elseif($_POST['host_type'] == 'upload') {
				$values['host_type'] = 'offsite';
				$values['host'] = Engine_Api::_()->getDbtable('hosts', 'sesevent')->hostInsert($values, $form, 'offsite',$_POST);
				unset($values['toValues']);
			}
      if (!($values['draft']))
        unset($values['draft']);
      $event->setFromArray($values);
      $event->save();
			$dbGetInsert = Engine_Db_Table::getDefaultAdapter();
       if (!$event->is_webinar) {
        //save value to sescore table for future use
        if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && !empty($_POST['location'])) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $event->event_id . '","'.$_POST['location'].'", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "sesevent_event")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
        }
      }else{
				$event->location = '';
           $event->region_id = null;
           $event->venue_name = '';
				$event->save();
				//remove sescore entry
				$dbGetInsert->query("DELETE FROM engine4_sesbasic_locations WHERE resource_id = ".$event->event_id .' AND resource_type = "sesevent_event"');
			}

      /*if (isset($_POST['custom_url']))
        $event->custom_url = $_POST['custom_url'];
      else
        $event->custom_url = $event->event_id;*/
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
      if($customfieldform) {
          $customfieldform->setItem($event);
          $customfieldform->saveValues();
      }
			$event->save();
      if($previous_location != $current_location) {
        //Activity Feed Work
	      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
	      $action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editlocation', '',  array("editlocation" => '<b>' . $current_location . '</b>'));
		    if ($action) {
			    $activityApi->attachActivity($action, $event);
		    }
      }
      if($previous_venue_name != $current_venue_name) {
        //Activity Feed Work
	      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
	      $action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editvenue', '',  array("editvenue" => '<b>' . $current_venue_name . '</b>'));
		    if ($action) {
			    $activityApi->attachActivity($action, $event);
		    }
      }
      if($previous_starttime != $current_starttime || $previous_endtime != $current_endtime) {
        $final_date = 'From <b>' . Engine_Api::_()->sesevent()->dateFormat($current_starttime) . '</b> To <b>' . Engine_Api::_()->sesevent()->dateFormat($current_endtime) . '</b>' . ' ('.$event->timezone.')';
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
      $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $db->beginTransaction();
    try {
      // Rebuild privacy
      //$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      //foreach( $actionTable->getActionsByObject($event) as $action ) {
      //$actionTable->resetActivityBindings($action);
      //}
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    // Redirect
    $this->_redirectCustom(array('route' => 'sesevent_dashboard', 'action' => 'edit', 'event_id' => $event->custom_url));
  }
	 public function removeMainphotoAction() {
      //GET Event ID AND ITEM
	    $event = Engine_Api::_()->core()->getSubject();
			$db = Engine_Api::_()->getDbTable('events', 'sesevent')->getAdapter();
      $db->beginTransaction();
      try {
        $event->photo_id = '';
				$event->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
			return $this->_helper->redirector->gotoRoute(array('action' => 'mainphoto', 'event_id' => $event->custom_url), "sesevent_dashboard", true);
  }

	public function backgroundphotoAction(){
		$is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Dashboard_Backgroundphoto();
    $form->populate($event->toArray());
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    $db = Engine_Api::_()->getDbtable('events', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $event->setTicketLogo($_FILES['background'],'background');
      $event->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
    }
		 return $this->_helper->redirector->gotoRoute(array('action' => 'backgroundphoto', 'event_id' => $event->custom_url), "sesevent_dashboard", true);
	}

	public function mainphotoAction(){
		$is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Dashboard_Mainphoto();
    $form->populate($event->toArray());
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    $db = Engine_Api::_()->getDbtable('events', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $event->setPhoto($_FILES['background']);
      $event->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
    }
		 return $this->_helper->redirector->gotoRoute(array('action' => 'mainphoto', 'event_id' => $event->custom_url), "sesevent_dashboard", true);
	}
	//get seo detail
  public function overviewAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Overview();
    $form->populate($event->toArray());
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    $db = Engine_Api::_()->getDbtable('events', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $event->setFromArray($_POST);
      $event->save();
      $db->commit();
      //Activity Feed Work
			$activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
			$action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editeventoverview');
			if ($action) {
				$activityApi->attachActivity($action, $event);
			}

    } catch (Exception $e) {
      $db->rollBack();
    }
  }
  //get seo detail
  public function seoAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Dashboard_Seo();

    $form->populate($event->toArray());
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    $db = Engine_Api::_()->getDbtable('events', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $event->setFromArray($_POST);
      $event->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
    }
  }
	 //get style detail
  public function styleAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer) || $this->_helper->requireAuth()->setAuthParams(null, null, 'style')->isValid()))
      return;
		// Get current row
    $table = Engine_Api::_()->getDbtable('styles', 'core');
    $select = $table->select()
            ->where('type = ?', 'sesevent')
            ->where('id = ?', $event->getIdentity())
            ->limit(1);
    $row = $table->fetchRow($select);
    // Create form
    $this->view->form = $form = new Sesevent_Form_Style();
    // Check post
    if (!$this->getRequest()->isPost()) {
      $form->populate(array(
          'style' => ( null === $row ? '' : $row->style )
      ));
    }
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
		// Cool! Process
    $style = $form->getValue('style');
    // Save
    if (null == $row) {
      $row = $table->createRow();
      $row->type = 'sesevent';
      $row->id = $event->getIdentity();
    }
    $row->style = $style;
    $row->save();
  }
  //get user account details
  public function accountDetailsAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $gateway_type = $this->view->gateway_type = $this->_getParam('gateway_type', "paypal");
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;

    $this->forwardToErrorPageIfTicketsNotAvailable();

    $userGateway = Engine_Api::_()->getDbtable('usergateways', 'sesevent')->getUserGateway(array('event_id' => $event->event_id,'gateway_type'=>$gateway_type,'enabled'=>true));
		$settings = Engine_Api::_()->getApi('settings', 'core');
    $userGatewayEnable = $settings->getSetting('sesevent.userGateway', 'paypal');
    if($gateway_type == "paypal") {
       	$this->view->form = $form = new Sesevent_Form_PayPal();
        $gatewayTitle = 'Paypal';
        $sponsorshipClass= 'Sesevent_Plugin_Gateway_Sponsorship_Owner';
        $gatewayClass= 'Sesevent_Plugin_Gateway_PayPal';
    } else if($gateway_type == "stripe") {
        $userGatewayEnable = 'stripe';
        $this->view->form = $form = new Sesadvpmnt_Form_Admin_Settings_Stripe();
        $gatewayTitle = 'Stripe';
        $gatewayClass= 'Sesadvpmnt_Plugin_Gateway_Stripe';
        $sponsorshipClass= 'Sesadvpmnt_Plugin_Gateway_User_Stripe';
    } else if($gateway_type == "paytm") {
        $userGatewayEnable = 'paytm';
        $this->view->form = $form = new Epaytm_Form_Admin_Settings_Paytm();
        $gatewayTitle = 'Paytm';
        $gatewayClass= 'Epaytm_Plugin_Gateway_Paytm';
        $sponsorshipClass= 'Epaytm_Plugin_Gateway_User_Paytm';
    }
    if (count($userGateway)) {
      $form->populate($userGateway->toArray());
      if (is_array($userGateway['config'])) {
        $form->populate($userGateway['config']);
      }
    }
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    // Process
    $values = $form->getValues();
    $enabled = (bool) $values['enabled'];
    unset($values['enabled']);
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    $userGatewayTable = Engine_Api::_()->getDbtable('usergateways', 'sesevent');
    // insert data to table if not exists
    try {
      if (!count($userGateway)) {
        $gatewayObject = $userGatewayTable->createRow();
        $gatewayObject->event_id = $event->event_id;
        $gatewayObject->user_id = $viewer->getIdentity();
        $gatewayObject->title = $gatewayTitle;
        $gatewayObject->plugin = $gatewayClass;
				$gatewayObject->sponsorship = $sponsorshipClass;
				$gatewayObject->gateway_type = $gateway_type;
        $gatewayObject->save();
      } else {
        $gatewayObject = Engine_Api::_()->getItem("sesevent_usergateway", $userGateway['usergateway_id']);
      }
      $db->commit();
    } catch (Exception $e) {
      echo $e->getMessage();
    }
    // Validate gateway config
    if ($enabled) {
      $gatewayObjectObj = $gatewayObject->getGateway();
      try {
        $gatewayObjectObj->setConfig($values);
        $response = $gatewayObjectObj->test();
      } catch (Exception $e) {
        $enabled = false;
        $form->populate(array('enabled' => false));
        $form->addError(sprintf('Gateway login failed. Please double check ' .
                        'your connection information. The gateway has been disabled. ' .
                        'The message was: [%2$d] %1$s', $e->getMessage(), $e->getCode()));
      }
    } else {
      $form->addError('Gateway is currently disabled.');
    }
    // Process
    $message = null;
    try {
      $values = $gatewayObject->getPlugin()->processAdminGatewayForm($values);
    } catch (Exception $e) {
      $message = $e->getMessage();
      $values = null;
    }
    if (null !== $values) {
      $gatewayObject->setFromArray(array(
          'enabled' => $enabled,
          'config' => $values,
      ));
			//echo "asdf<pre>";var_dump($gatewayObject);die;
      $gatewayObject->save();
      $form->addNotice('Changes saved.');
    } else {
      $form->addError($message);
    }
  }
	//download report in csv and excel
	public function downloadReportsAction(){
		$value = array();
    if (isset($_GET['eventTicketId']))
      $value['eventTicketId'] = $_GET['eventTicketId'];
    if (isset($_GET['start']['date']))
      $value['start'] = date('Y-m-d', strtotime($_GET['start']['date']));
    if (isset($_GET['end']['date']))
      $value['end'] = date('Y-m-d', strtotime($_GET['end']['date']));
    if (isset($_GET['type']))
      $value['type'] = $_GET['type'];
    if (!count($value)) {
      $value['end'] = date('Y-m-d', strtotime(date('Y-m-d')));
      $value['start'] = date('Y-m-d', strtotime('-30 days'));
      $value['type'] = $form->type->getValue();
    }

   $data =  Engine_Api::_()->getDbtable('orders', 'sesevent')->getReportData($value);

	}
  //get sales report
  public function salesReportsAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
      
    $this->forwardToErrorPageIfTicketsNotAvailable();

    $eventTicketDetails = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $event->event_id));
    $this->view->form = $form = new Sesevent_Form_Dashboard_Searchsalereport();
    $value = array();
    if (isset($_GET['eventTicketId']))
      $value['eventTicketId'] = $_GET['eventTicketId'];
    if (isset($_GET['startdate']))
      $value['startdate'] = $value['start'] = date('Y-m-d', strtotime($_GET['startdate']));
    if (isset($_GET['enddate']))
     $value['enddate'] = $value['end'] = date('Y-m-d', strtotime($_GET['enddate']));
    if (isset($_GET['type']))
      $value['type'] = $_GET['type'];
    if (!count($value)) {
      $value['enddate'] = date('Y-m-d', strtotime(date('Y-m-d')));
      $value['startdate'] = date('Y-m-d', strtotime('-30 days'));
      $value['type'] = $form->type->getValue();
    }
		if(isset($_GET['excel']) && $_GET['excel'] != '')
			$value['download'] = 'excel';
		if(isset($_GET['csv']) && $_GET['csv'] != '')
			$value['download'] = 'csv';
    $form->populate($value);
		$value['event_id'] = $event->getIdentity();
    $this->view->eventSaleData = $data = Engine_Api::_()->getDbtable('orders', 'sesevent')->getReportData($value);

		if(isset($value['download'])){
			$name = str_replace(' ','_',$event->getTitle()).'_'.time();
			switch($value["download"])
    {
			case "excel" :
			// Submission from
			$filename = $name . ".xls";
			header("Content-Type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=\"$filename\"");
			$this->exportFile($data);
			exit();
			case "csv" :
				// Submission from
			$filename = $name . ".csv";
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Expires: 0");
			$this->exportCSVFile($data);
				exit();
			default :
				//silence
			break;
			}
		}

  }
	protected function exportCSVFile($records) {
	// create a file pointer connected to the output stream
	$fh = fopen( 'php://output', 'w' );
	$heading = false;
	$counter = 1;
		if(!empty($records))
		  foreach($records as $row) {
			$valueVal['S.No'] = $counter;
			$valueVal['Ticket Name'] = $row['title'];
			$valueVal['Date of Purchase'] = Engine_Api::_()->sesevent()->dateFormat($row['creation_date']);
			$valueVal['Quatity'] = $row['total_tickets'];
			$valueVal['Service Tax'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['total_service_tax'],$defaultCurrency);
			$valueVal['Entertainment Tax'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['total_entertainment_tax'],$defaultCurrency);
			$valueVal['Total Tax'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['totalTaxAmount'],$defaultCurrency);
			//$valueVal['Commission Amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['commission_amount'],$defaultCurrency);
			$valueVal['Total Amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['totalAmountSale'],$defaultCurrency);
			$counter++;
			if(!$heading) {
			  // output the column headings
			  fputcsv($fh, array_keys($valueVal));
			  $heading = true;
			}
			// loop over the rows, outputting them
			 fputcsv($fh, array_values($valueVal));

		  }
		  fclose($fh);
}

protected function exportFile($records) {
	$heading = false;
	$counter = 1;
	$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
	if(!empty($records))
	  foreach($records as $row) {
			$valueVal['S.No'] = $counter;
			$valueVal['Ticket Name'] = $row['title'];
			$valueVal['Date'] = Engine_Api::_()->sesevent()->dateFormat($row['creation_date']);
			$valueVal['Quatity'] = $row['total_tickets'];
			$valueVal['Service Tax Amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['total_service_tax'],$defaultCurrency);
			$valueVal['Entertainment Tax Amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['total_entertainment_tax'],$defaultCurrency);
			$valueVal['Total Tax Amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['totalTaxAmount'],$defaultCurrency);
			//$valueVal['Commission Amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['commission_amount'],$defaultCurrency);
			$valueVal['Total Amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($row['totalAmountSale'],$defaultCurrency);
			$counter++;
		if(!$heading) {
		  // display field/column names as a first row
		  echo implode("\t", array_keys($valueVal)) . "\n";
		  $heading = true;
		}
		echo implode("\t", array_values($valueVal)) . "\n";
	  }
	exit;
}
  public function paymentTransactionAction() {

    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;

    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;

    $this->view->event = $event = Engine_Api::_()->core()->getSubject();

    $viewer = Engine_Api::_()->user()->getViewer();

    $this->forwardToErrorPageIfTicketsNotAvailable();

    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;

    $this->view->paymentRequests = Engine_Api::_()->getDbtable('userpayrequests', 'sesevent')->getPaymentRequests(array('event_id' => $event->event_id, 'state' => 'complete'));
  }

  //get payment to admin information
  public function paymentRequestsAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (
      !($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->thresholdAmount = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_threshold');

    $this->forwardToErrorPageIfTicketsNotAvailable();
    
    //get total amount of ticket sold in given event
		$this->view->userGateway = Engine_Api::_()->getDbtable('usergateways', 'sesevent')->getUserGateway(array('event_id' => $event->event_id, 'user_id' => $viewer->user_id));
    $this->view->orderDetails = Engine_Api::_()->getDbtable('orders', 'sesevent')->getEventStats(array('event_id' => $event->event_id));
    //get ramaining amount
    $remainingAmount = Engine_Api::_()->getDbtable('remainingpayments', 'sesevent')->getEventRemainingAmount(array('event_id' => $event->event_id));
    if (!$remainingAmount) {
      $this->view->remainingAmount = 0;
    } else
      $this->view->remainingAmount = $remainingAmount->remaining_payment;
		$this->view->isAlreadyRequests = Engine_Api::_()->getDbtable('userpayrequests', 'sesevent')->getPaymentRequests(array('event_id' => $event->event_id,'isPending'=>true));
    $this->view->paymentRequests = Engine_Api::_()->getDbtable('userpayrequests', 'sesevent')->getPaymentRequests(array('event_id' => $event->event_id,'isPending'=>true));
  }

  public function paymentRequestAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->thresholdAmount = $thresholdAmount = Engine_Api::_()->authorization()->getPermission($viewer, 'sesevent_event', 'event_threshold');
    //get remaining amount
    $remainingAmount = Engine_Api::_()->getDbtable('remainingpayments', 'sesevent')->getEventRemainingAmount(array('event_id' => $event->event_id));
    if (!$remainingAmount) {
      $this->view->remainingAmount = 0;
    } else {
      $this->view->remainingAmount = $remainingAmount->remaining_payment;
    }
    $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
    $orderDetails = Engine_Api::_()->getDbtable('orders', 'sesevent')->getEventStats(array('event_id' => $event->event_id));
    $this->view->form = $form = new Sesevent_Form_Dashboard_Paymentrequest();
    $value = array();
    $value['total_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['totalAmountSale'], $defaultCurrency);
    $value['total_tax_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['totalTaxAmount'], $defaultCurrency);
    $value['total_commission_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['commission_amount'], $defaultCurrency);
    $value['remaining_amount'] = Engine_Api::_()->sesevent()->getCurrencyPrice($remainingAmount->remaining_payment, $defaultCurrency);
    $value['requested_amount'] = round($remainingAmount->remaining_payment,2);
    //set value to form
    if ($this->_getParam('id', false)) {
      $item = Engine_Api::_()->getItem('sesevent_userpayrequest', $this->_getParam('id'));
      if ($item) {
        $itemValue = $item->toArray();
        //unset($value['requested_amount']);
        $value = array_merge($itemValue, $value);
      } else {
        return $this->_forward('requireauth', 'error', 'core');
      }
    }
    if (empty($_POST))
      $form->populate($value);

    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    if (@round($thresholdAmount,2) > @round($remainingAmount->remaining_payment,2) && empty($_POST)) {
      $this->view->message = 'Remaining amount is less than Threshold amount.';
      $this->view->errorMessage = true;
      return;
    } else if (isset($_POST['requested_amount']) && @round($_POST['requested_amount'],2) > @round($remainingAmount->remaining_payment,2)) {
      $form->addError('Requested amount must be less than or equal to remaining amount.');
      return;
    } else if (isset($_POST['requested_amount']) && @round($thresholdAmount) > @round($_POST['requested_amount'],2)) {
      $form->addError('Requested amount must be greater than or equal to threshold amount.');
      return;
    }

    $db = Engine_Api::_()->getDbtable('userpayrequests', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $tableOrder = Engine_Api::_()->getDbtable('userpayrequests', 'sesevent');
      if (isset($itemValue))
        $order = $item;
      else
        $order = $tableOrder->createRow();
      $order->requested_amount = round($_POST['requested_amount'],2);
      $order->user_message = $_POST['user_message'];
      $order->event_id = $event->event_id;
      $order->owner_id = $viewer->getIdentity();
      $order->user_message = $_POST['user_message'];
      $order->creation_date = date('Y-m-d h:i:s');
      $order->currency_symbol = $defaultCurrency;
			$settings = Engine_Api::_()->getApi('settings', 'core');
   	  $userGatewayEnable = $settings->getSetting('sesevent.userGateway', 'paypal');
      $order->save();
      $db->commit();

      //Notification work
			$owner_admin = Engine_Api::_()->getItem('user', 1);
			Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner_admin, $viewer, $event, 'sesevent_event_paymentrequest', array('requestAmount' => round($_POST['requested_amount'],2)));

			//Payment request mail send to admin
			$event_owner = Engine_Api::_()->getItem('user', $event->user_id);
			Engine_Api::_()->getApi('mail', 'core')->sendSystem($owner_admin, 'sesevent_ticketpayment_requestadmin', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'event_owner' => $event_owner->getTitle(), 'host' => $_SERVER['HTTP_HOST']));

      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Payment request send successfully.');
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => 10,
                  'parentRefresh' => 10,
                  'messages' => array($this->view->message)
      ));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  //delete payment request
  public function deletePaymentAction() {

    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $paymnetReq = Engine_Api::_()->getItem('sesevent_userpayrequest', $this->getRequest()->getParam('id'));

    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid())
      return;

    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Make form
    $this->view->form = $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete Payment Request?');
    $form->setDescription('Are you sure that you want to delete this payment request? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    if (!$paymnetReq) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Paymnet request doesn't exists or not authorized to delete");
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $paymnetReq->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $paymnetReq->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Payment Request has been deleted.');
    return $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => 10,
                'parentRefresh' => 10,
                'messages' => array($this->view->message)
    ));
  }

  //get paymnet detail
  public function detailPaymentAction() {
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $this->view->item = $paymnetReq = Engine_Api::_()->getItem('sesevent_userpayrequest', $this->getRequest()->getParam('id'));
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
      return;

    if (!$paymnetReq) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Paymnet request doesn't exists or not authorized to delete");
      return;
    }
  }

  //get event contact information
  public function contactInformationAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $sesevent_contactevent = Zend_Registry::isRegistered('sesevent_contactevent') ? Zend_Registry::get('sesevent_contactevent') : null;
    if(empty($sesevent_contactevent)) {
	    return $this->_forward('notfound', 'error', 'core');
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Dashboard_Contactinformation();

    $form->populate($event->toArray());
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    $db = Engine_Api::_()->getDbtable('events', 'sesevent')->getAdapter();
    $db->beginTransaction();
    try {
      $event->event_contact_name = isset($_POST['event_contact_name']) ? $_POST['event_contact_name'] : '';
      $event->event_contact_email = isset($_POST['event_contact_email']) ? $_POST['event_contact_email'] : '';
      $event->event_contact_phone = isset($_POST['event_contact_phone']) ? $_POST['event_contact_phone'] : '';
      $event->event_contact_website = isset($_POST['event_contact_website']) ? $_POST['event_contact_website'] : '';
      $event->event_contact_facebook = isset($_POST['event_contact_facebook']) ? $_POST['event_contact_facebook'] : '';
			$event->event_contact_twitter = isset($_POST['event_contact_twitter']) ? $_POST['event_contact_twitter'] : '';
			$event->event_contact_linkedin = isset($_POST['event_contact_linkedin']) ? $_POST['event_contact_linkedin'] : '';
      $event->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      echo false; die;
    }
  }

  public function manageTicketAction() {

    $value = array();

    $this->view->event = $event = Engine_Api::_()->core()->getSubject();

    $this->view->event_id = $value['event_id'] = $event->getIdentity();

    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;

    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;

    $this->view->is_search_ajax = $is_search_ajax = isset($_POST['is_search_ajax']) ? $_POST['is_search_ajax'] : false;
    if (!$is_search_ajax) {
      $this->view->searchForm = $searchForm = new Sesevent_Form_ManageTickets();
    }

    $this->forwardToErrorPageIfTicketsNotAvailable();

    if (isset($_POST['searchParams']) && $_POST['searchParams'])
      parse_str($_POST['searchParams'], $searchArray);

    $value['name'] = isset($searchArray['name']) ? $searchArray['name'] : '';
    $value['type'] = isset($searchArray['type']) ? $searchArray['type'] : '';

    $this->view->eventTickets = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($value);
  }

  public function deleteTicketAction() {
    $ticket_id = $this->_getParam('ticket_id');
    $ticket = Engine_Api::_()->getItem('sesevent_ticket', $ticket_id);
    if (!$ticket)
      return $this->_forward('requireauth', 'error', 'core');
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (strtotime($event->endtime) < time()){
      echo 'expire';die;
		}
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid() || $event->isOwner($viewer)))
      return;
    $db = $ticket->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $ticket->is_delete = '1';
      $ticket->save();
      $db->commit();
      echo true;
      die;
    } catch (Exception $e) {
      $db->rollBack();
      echo false;
      die;
    }
  }

  public function editTicketAction() {
    $ticket_id = $this->_getParam('ticket_id');
    $ticket = Engine_Api::_()->getItem('sesevent_ticket', $ticket_id);
    $this->view->event_timezone = array(
            'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
            'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
            'US/Central' => '(UTC-6) Central Time (US & Canada)',
            'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
            'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
            'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
            'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
            'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
            'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
            'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
            'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
            'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
            'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
            'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
            'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
            'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
            'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
            'Iran' => '(UTC+3:30) Tehran',
            'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
            'Asia/Kabul' => '(UTC+4:30) Kabul',
            'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
            'Asia/Calcutta' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
            'Asia/Katmandu' => '(UTC+5:45) Nepal',
            'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
            'Indian/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
            'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
            'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
            'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
            'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
            'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
            'Asia/Magadan' => '(UTC+11) Magadan, Solomon Is., New Caledonia',
            'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
        );
    $previous_starttime = $ticket->starttime;
    $previous_endtime = $ticket->endtime;

    if (!$ticket)
      return $this->_forward('requireauth', 'error', 'core');
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();


    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
    // Create form
    $this->view->form = $form = new Sesevent_Form_Dashboard_Ticket();

      $form->submit->setLabel('Save');
    $form->setTitle('Edit Ticket');
    if ($ticket->service_tax || $ticket->entertainment_tax) {
			if($form->tax)
      $form->tax->setChecked(true);
      if ($ticket->service_tax && $form->service_tax_checkbox)
        $form->service_tax_checkbox->setChecked(true);
      if ($ticket->entertainment_tax && $form->entertainment_tax_checkbox)
        $form->entertainment_tax_checkbox->setChecked(true);
    }
    $form->populate($ticket->toArray());
    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
		// Convert times
		$_POST['starttime'] = $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
		$_POST['endtime'] = $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['end_date'].' '.$_POST['end_time'])) : '';

    if (strtotime($starttime . " " . $event->timezone) > strtotime($event->endtime) || strtotime($endtime . " " . $event->timezone) > strtotime($event->endtime)) {
      $form->addError($this->view->translate("Event ticket end date must be less than event end date."));
      return;
    }

    $startTime = new DateTime($values['starttime']);
    $endTime = new DateTime($values['endtime']);
    
    if(!$viewer->isAdmin() && (int)$endTime->diff($startTime)->format("%a") > 1) {
      $form->addError(Zend_Registry::get('Zend_Translate')->_("Regular members can't create events that take more than 1 day."));
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    $db = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getAdapter();
    $db->beginTransaction();
    $values = $form->getValues();
    // Convert times
    $oldTz = date_default_timezone_get();
		date_default_timezone_set($event->timezone);
		$start = strtotime($starttime);
		$end = strtotime($endtime);
		date_default_timezone_set($oldTz);
		$_POST['starttime'] = date('Y-m-d H:i:s', $start);
		$_POST['endtime'] = date('Y-m-d H:i:s', $end);
    try {
      $current_starttime = $_POST['starttime'];
      $current_endtime = $_POST['endtime'];
      $_POST['event_id'] = $event->event_id;
      if (empty($_POST['tax'])) {
        $_POST['service_tax'] = '';
        $_POST['entertainment_tax'] = '';;
      } else if (empty($_POST['service_tax_checkbox']))
        $_POST['service_tax'] = '';
      else if (empty($_POST['entertainment_tax_checkbox']))
        $_POST['entertainment_tax'] = '';
      $ticket->setFromArray($_POST);
      $ticket->save();
      $db->commit();

      if(strtotime($previous_starttime) != strtotime($current_starttime) || strtotime($previous_endtime) != strtotime($current_endtime)) {
			  //Activity Feed Work
			  $final_date = 'From <b>' . Engine_Api::_()->sesevent()->dateFormat($current_starttime) . '</b> To <b>' . Engine_Api::_()->sesevent()->dateFormat($current_endtime) . '</b>' . ' ('.$event->timezone.')';
				$activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
				$action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editticketdate', '', array("editDateFormat" => $final_date, 'ticketName' => '<b>' .$ticket->name . '</b>'));
      }
      $this->_redirectCustom(array('route' => 'sesevent_dashboard', 'action' => 'manage-ticket', 'event_id' => $event->custom_url));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  public function salesStatsAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;

    $this->forwardToErrorPageIfTicketsNotAvailable();

    $this->view->todaySale = Engine_Api::_()->getDbtable('orders', 'sesevent')->getSaleStats(array('stats' => 'today', 'event_id' => $event->event_id));
    $this->view->weekSale = Engine_Api::_()->getDbtable('orders', 'sesevent')->getSaleStats(array('stats' => 'week', 'event_id' => $event->event_id));
    $this->view->monthSale = Engine_Api::_()->getDbtable('orders', 'sesevent')->getSaleStats(array('stats' => 'month', 'event_id' => $event->event_id));

    //get getEventStats
    $this->view->eventStatsSale = Engine_Api::_()->getDbtable('orders', 'sesevent')->getEventStats(array('event_id' => $event->event_id));
  }
	public function searchTicketAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    
    $this->forwardToErrorPageIfTicketsNotAvailable();

    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
		$this->view->order_id = $this->_getParam('dataAjax','');
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() || $event->isOwner($viewer)))
      return;
  }
  public function manageOrdersAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    
    $this->forwardToErrorPageIfTicketsNotAvailable();
    
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
  }

  public function eventTermconditionAction() {
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $this->_helper->layout->setLayout('default-simple');
  }

  public function currencyConverterAction() {
    //default currency
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    if ($is_ajax) {
      $curr = $this->_getParam('curr', Engine_Api::_()->sesevent()->defaultCurrency());
      $val = $this->_getParam('val', '1');
      $currencyVal = $settings->getSetting('sesmultiplecurrency.' . $curr);
      echo round($currencyVal*$val,2);die;
    }
    //currecy Array
    $fullySupportedCurrenciesExists = array();
    $fullySupportedCurrencies = Engine_Api::_()->sesevent()->getSupportedCurrency();
    foreach ($fullySupportedCurrencies as $key => $values) {
      if ($settings->getSetting('sesmultiplecurrency.' . $key))
        $fullySupportedCurrenciesExists[$key] = $values;
    }
    $this->view->form = $form = new Sesevent_Form_Dashboard_Conversion();
    $form->currency->setMultioptions($fullySupportedCurrenciesExists);
    $form->currency->setValue($defaultCurrency);
  }

  private function currentUserIsAllowedToMakeTickets(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $level = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
    $member_level_current_user = $level->flag;
    $allowed_member_levels = array("superadmin", "admin");
    $allowedToMakeTickets = in_array($member_level_current_user, $allowed_member_levels);
    return $allowedToMakeTickets;
  }

  private function ticketsAreAvailable(){
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			return false;
    }
    return $this->currentUserIsAllowedToMakeTickets();
  }
  
  private function forwardToErrorPageIfTicketsNotAvailable(){
    if(!$this->ticketsAreAvailable()) $this->_forward('notfound', 'error', 'core');
  }
  public function createTicketAction() {
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->forwardToErrorPageIfTicketsNotAvailable();

    $this->view->event_timezone = array(
            'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
            'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
            'US/Central' => '(UTC-6) Central Time (US & Canada)',
            'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
            'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
            'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
            'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
            'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
            'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
            'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
            'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
            'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
            'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
            'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
            'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
            'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
            'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
            'Iran' => '(UTC+3:30) Tehran',
            'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
            'Asia/Kabul' => '(UTC+4:30) Kabul',
            'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
            'Asia/Calcutta' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
            'Asia/Katmandu' => '(UTC+5:45) Nepal',
            'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
            'Indian/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
            'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
            'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
            'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
            'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
            'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
            'Asia/Magadan' => '(UTC+11) Magadan, Solomon Is., New Caledonia',
            'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
        );
    // Create form
    $this->view->form = $form = new Sesevent_Form_Dashboard_Ticket();

    if (!empty($_POST))
      $form->populate($_POST);
    $this->forwardToErrorPageIfTicketsNotAvailable();


    if (!$this->getRequest()->isPost())
      return;
    $values = $form->getValues();
    // Convert times
		$_POST['starttime'] = $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
		$_POST['endtime'] = $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['end_date'].' '.$_POST['end_time'])) : '';


    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    if ($this->getRequest()->isPost() && $_POST['type'] == 'paid'){
        // Check for enabled payment gateways
         if( Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0 ) {
          $form->addError('There are currently no ' .
              'enabled payment gateways. Please contact Admin of the site.');
          return;
        }
     }
    if (strtotime($starttime . " " . $event->timezone) > strtotime($event->endtime) || strtotime($endtime . " " . $event->timezone) > strtotime($event->endtime)) {
      $form->addError($this->view->translate("Event ticket end date must be less than event end date."));
      return;
    }
    
    $db = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getAdapter();
    $db->beginTransaction();

    try {
      $table = Engine_Api::_()->getDbtable('tickets', 'sesevent');
      $ticket = $table->createRow();
      $_POST['event_id'] = $event->event_id;
			$oldTz = date_default_timezone_get();
			date_default_timezone_set($event->timezone);
			$start = strtotime($starttime);
			$end = strtotime($endtime);
			date_default_timezone_set($oldTz);
			$_POST['starttime'] = date('Y-m-d H:i:s', $start);
			$_POST['endtime'] = date('Y-m-d H:i:s', $end);
      if (empty($_POST['tax'])) {
        $_POST['service_tax'] = '';
        $_POST['entertainment_tax'] = '';;
      } else if (empty($_POST['service_tax_checkbox']))
        $_POST['service_tax'] = '';
      else if (empty($_POST['entertainment_tax_checkbox']))
        $_POST['entertainment_tax'] = '';
      $ticket->setFromArray($_POST);
      $ticket->save();

      //Activity Feed Work
			$activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
			$action = $activityApi->addActivity($viewer, $event, 'sesevent_event_createticket', '' , array('ticketName' => '<b>' . $ticket->name . '</b>'));

      $db->commit();
      $this->_redirectCustom(array('route' => 'sesevent_dashboard', 'action' => 'manage-ticket', 'event_id' => $event->custom_url));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }
  public function showBlogRequestAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;
  }
  public function approvedAction() {
    $blog_id = $this->_getParam('blog_id');
    $event_id = $this->_getParam('event_id');
    if (!empty($event_id)) {
      $customUrl = Engine_Api::_()->getItem('sesevent_event', $event_id)->custom_url;
      $blog = Engine_Api::_()->getItem('sesblog_blog', $event_id);
      if(!$blog->event_id)
	$approved = 1;
      else {
	$approved = 0;
	$event_id = 0;
      }
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->update('engine4_sesblog_mapevents', array(
      'approved' => $approved,
      ), array(
	'event_id = ?' => $event_id,
	'blog_id = ?' => $blog_id,
      ));
      $db->update('engine4_sesblog_blogs', array(
      'event_id' => $event_id,
      ), array(
	'blog_id = ?' => $blog_id,
      ));
    }
    $this->_redirect('events/dashboard/show-blog-request/'.$customUrl);
  }
  public function ticketInformationAction() {
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
		$is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)))
      return;

    $this->forwardToErrorPageIfTicketsNotAvailable();
    
    // Create form
    $this->view->form = $form = new Sesevent_Form_Dashboard_Printticketinfo();
		 $form->populate($event->toArray());
    if (!$this->getRequest()->isPost() || $is_ajax_content) {
			 return;
		}
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    // Process
    $values = $form->getValues();
    $db = Engine_Api::_()->getItemTable('sesevent_event')->getAdapter();
    $db->beginTransaction();
    try {
			$event->setFromArray($_POST);
      $event->save();
      $db->commit();
			// Add/Remove logo
      if($values['remove']){
				$event->ticket_logo = 0;
				$event->save();
			} else if (!empty($values['logo'])) {
        $event->setTicketLogo($form->logo);
      }
			// Create form
    	$this->view->form = $form = new Sesevent_Form_Dashboard_Printticketinfo();
		 	$form->populate($event->toArray());
    } catch (Engine_Image_Exception $e) {
      $db->rollBack();
      $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
    } catch (Exception $e) {
      $db->rollBack();
			$form->addError(Zend_Registry::get('Zend_Translate')->_('Something went wrong,please try again later.'));
    }
  }
  public function rejectRequestAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $blog_id = $this->_getParam('blog_id');
    $blogObject = Engine_Api::_()->getItem('sesblog_blog', $blog_id);
    $owner = $blogObject->getOwner();
    $event_id = $this->_getParam('event_id');
    $eventObject = Engine_Api::_()->getItem('sesevent_event', $event_id);
    $customUrl = Engine_Api::_()->getItem('sesevent_event', $event_id)->custom_url;
    $mapBlogTable = Engine_Api::_()->getDbtable('mapevents', 'sesblog');
    $selectMapTable = $mapBlogTable->select()->where('event_id =?', $event_id)->where('blog_id =?', $blog_id)->where('request_owner_blog =?', 1);
    $mapResult = $mapBlogTable->fetchRow($selectMapTable);
    $db = $mapResult->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $mapResult->delete();
      $blogPageLink = '<a href="' . $blogObject->getHref() . '">' . ucfirst($blogObject->getTitle()) . '</a>';
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $eventObject, 'sesblog_reject_blog_request', array("blogPageLink" => $blogPageLink));
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirect('events/dashboard/show-blog-request/'.$customUrl);
  }
  public function removeBackgroundphotoAction() {
    $event = Engine_Api::_()->core()->getSubject();
    $event->background_photo_id = 0;
    $event->save();
    return $this->_helper->redirector->gotoRoute(array('action' => 'backgroundphoto', 'event_id' => $event->custom_url), "sesevent_dashboard", true);
  }
  // TO DO => REMOVE DUPLICATION , method also in indexcontroller


}
