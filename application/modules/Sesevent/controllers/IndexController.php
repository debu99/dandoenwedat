<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_IndexController extends Core_Controller_Action_Standard {

    public function init() {
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'view')->isValid())
            return;
        $id = $this->_getParam('event_id', $this->_getParam('id', null));
        $host_id = $this->_getParam('host_id', null);
        if ($id) {
            $event = Engine_Api::_()->getItem('sesevent_event', $id);
            if ($event) {
                Engine_Api::_()->core()->setSubject($event);
            }
        } else if ($host_id) {
            $host = Engine_Api::_()->getItem('sesevent_host', $heost_id);
            if ($host) {
                Engine_Api::_()->core()->setSubject($host);
            }
        }
    }

    public function setDateDataAction() {
        $timeZone = $_POST['timezone'];
        $values = trim($_POST['values'], '&');
        parse_str($values, $parameters);
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($timeZone);
        $start_date = date('m/d/Y', strtotime($parameters["sesevent_start_date"]));
        $start_time = date('g:ia', strtotime($parameters['sesevent_start_time']));
        $end_date = date('m/d/Y', strtotime($parameters['sesevent_end_date']));
        $end_time = date('g:ia', strtotime($parameters['sesevent_end_time']));

        date_default_timezone_set($oldTz);
        $dateData = array(array('key' => 'sesevent_start_date', 'value' => $start_date), array('key' => 'sesevent_end_date', 'value' => $end_date), array('key' => 'sesevent_start_time', 'value' => $start_time), array('key' => 'sesevent_end_time', 'value' => $end_time));
        echo json_encode($dateData);
        die;
    }

    public function welcomeAction() {
        //Render
        $this->_helper->content->setEnabled();
    }

    public function homeAction() {
        //Render
        $this->_helper->content->setEnabled();
    }

    public function termConditionAction() {
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'edit')->isValid()) {
            return;
        }
        $event = Engine_Api::_()->core()->getSubject();
        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');
        $this->view->form = $form = new Sesevent_Form_Termandcondition();
        $form->populate($event->toArray());
        if (!$event) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Event doesn't exists or not authorized");
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }
        $db = $event->getTable()->getAdapter();
        $db->beginTransaction();
        try {

            $event->custom_term_condition = $_POST['custom_term_condition'];
            if (empty($_POST['custom_term_condition']))
                $event->is_custom_term_condition = 0;
            else
                $event->is_custom_term_condition = 1;
            $event->save();
            $this->view->status = true;
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('Event term & condition has been updated successfully.');
            $db->commit();
            return $this->_forward('success', 'utility', 'core', array(
                        'messages' => Array($this->view->message),
                        'layout' => 'default-simple',
                        'parentRefresh' => true,
                        'smoothboxClose' => false,
            ));
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function overviewAction() {
        die;
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'edit')->isValid()) {
            return;
        }
        $event = Engine_Api::_()->core()->getSubject();
        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');
        $this->view->form = $form = new Sesevent_Form_Overview();
        $form->populate($event->toArray());
        if (!$event) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Event doesn't exists or not authorized");
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }
        $db = $event->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $event->overview = $_POST['overview'];
            $event->save();
            $this->view->status = true;
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('Event Overview has been updated successfully.');
            $viewer = Engine_Api::_()->user()->getViewer();
            //Activity Feed Work
            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
            $action = $activityApi->addActivity($viewer, $event, 'sesevent_event_editeventoverview');
            if ($action) {
                $activityApi->attachActivity($action, $event);
            }

            $db->commit();
            return $this->_forward('success', 'utility', 'core', array(
                        'messages' => Array($this->view->message),
                        'layout' => 'default-simple',
                        'parentRefresh' => true,
                        'smoothboxClose' => false,
            ));
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function locationsAction() {
        //Render
        $this->_helper->content->setEnabled();
    }

    public function browseEventsAction() {

        $integrateothermodule_id = $this->_getParam('integrateothermodule_id', null);
        $page = 'sesevent_index_' . $integrateothermodule_id;
        //Render
        $this->_helper->content->setContentName($page)->setEnabled();
    }

    public function browseHostAction() {
        // Render
        $this->view->paginator = array();
        $this->_helper->content->setEnabled();
    }

    public function browseAction() {
        // Render
        $this->_helper->content->setEnabled();
    }

    public function upcomingAction() {
        // Render
        $this->_helper->content->setEnabled();
    }

    public function pastAction() {
        // Render
        $this->_helper->content->setEnabled();
    }

    public function calenderAction() {
        // Render
        $this->_helper->content->setEnabled();
    }

    public function viewhostAction() {
        $host_id = $this->_getParam('host_id', null);
        $host = Engine_Api::_()->getItem('sesevent_host', $host_id);
        if (!$host)
            return $this->_forward('notfound', 'error', 'core');
        Engine_Api::_()->getDbtable('hosts', 'sesevent')->update(array(
            'view_count' => new Zend_Db_Expr('view_count + 1'),
                ), array(
            'host_id = ?' => $host->host_id,
        ));
        //Render
        $this->_helper->content->setEnabled();
    }

    public function savedEventAction() {
        //Create form
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'edit')->isValid())
            return;
        //Get navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_main');
        //Render
        $this->_helper->content->setEnabled();
        $viewer = Engine_Api::_()->user()->getViewer();
        $table = Engine_Api::_()->getDbtable('saves', 'sesbasic');
        $tableName = $table->info('name');

        $eventTable = Engine_Api::_()->getDbtable('events', 'sesevent');
        $eventTableName = $eventTable->info('name');

        $select = $table->select()
                ->setIntegrityCheck(false)
                ->joinLeft($eventTableName, "$eventTableName.event_id = $tableName.resource_id", NULL)
                ->where('resource_type = ?', 'sesevent_event')
                ->where('poster_id = ?', $viewer->getIdentity())
                ->order('save_id DESC');
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        //Check create
        $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('sesevent', null, 'create');
    }

    public function manageAction() {
        // Create form
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'edit')->isValid())
            return;
        // Render
        $this->_helper->content->setEnabled();
    }

    //fetch ticket buyer as per given event id .
    public function buyerDetailsAction() {
        $event_id = $this->_getParam('event_id', '0');
        if ($event_id == 0)
            return;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $this->view->viewmore = isset($_POST['viewmore']) ? $_POST['viewmore'] : '';
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('orders', 'sesevent')->getOrders(array('event_id' => $event->getIdentity(), 'groupBy' => 'owner_id'));
        $this->view->event_id = $event->event_id;
        // Set item count per page and current page number
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($page);
    }

    public function addCalanderAction() {
        $event_id = $this->_getParam('event_id', false);
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        if (!$event_id || !$event)
            return;
        ob_start();

        $oldTz = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $starttime = date('Y-m-d H:i:s', strtotime($event->starttime));
        $endtime = date('Y-m-d H:i:s', strtotime($event->endtime));

        $dateStart = date("Ymd", strtotime($starttime));
        $dateEnd = date("Ymd", strtotime($endtime));
        $dateStartTime = date("His", strtotime($starttime));
        $dateEndTime = date("His", strtotime($endtime));
        date_default_timezone_set($oldTz);

        $filename = 'calendar.ics';
        $filepath = APPLICATION_PATH . '/public';
        $handle = fopen($filepath . DIRECTORY_SEPARATOR . $filename, "w");
        $content = "BEGIN:VCALENDAR\n";
        $content .= "VERSION:2.0\n";
        $content .= "X-WR-CALNAME:" . $event->getTitle() . "\n";
        //$content .=  "PRODID:-//YourSite//NONSGML YourSite//EN\n";
        $content .= "METHOD:PUBLISH\n"; // required by Outlook
        $content .= "BEGIN:VEVENT\n";
        $content .= "UID:" . md5(date('Ymd') . 'T' . date('His') . "-" . rand() . "-" . $_SERVER['HTTP_HOST'] . ".com") . "\n"; // required by Outlook
        $content .= "DTSTAMP:" . date('Ymd') . 'T' . date('His') . "Z\n"; // required by Outlook
        $content .= "DTSTART:" . $dateStart . "T" . $dateStartTime . "Z\n"; //20120824T093200 (Datetime format required)
        $content .= "DTEND:" . $dateEnd . "T" . $dateEndTime . "\n"; //20120824T093200 (Datetime format required)
        $content .= "SUMMARY:" . $event->getTitle() . "\n";
        $content .= "X-ALT-DESC;FMTTYPE=text/html: " . $event->description . "\n";
        $content .= "LOCATION:" . $event->location . "\n";
        $content .= "END:VEVENT\n";
        $content .= "END:VCALENDAR\n";
        fwrite($handle, $content);
        fclose($handle);
        // SET HEADER
        header("Content-Disposition: attachment; filename=" . urlencode(basename($filename)), true);
        header("Content-Transfer-Encoding: Binary");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        header('content-length: ' . filesize($filepath . DIRECTORY_SEPARATOR . $filename));
        readfile($filepath . DIRECTORY_SEPARATOR . $filename);
        exit();
    }

    public function createAction() {

        if (!$this->_helper->requireUser->isValid())
            return;
        if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'create')->isValid())
            return;
        $sessmoothbox = $this->view->typesmoothbox = false;
        if ($this->_getParam('typesmoothbox', false)) {
            // Render
            $sessmoothbox = true;
            $this->view->typesmoothbox = true;
            $this->_helper->layout->setLayout('default-simple');
            $layoutOri = $this->view->layout()->orientation;
            if ($layoutOri == 'right-to-left') {
                $this->view->direction = 'rtl';
            } else {
                $this->view->direction = 'ltr';
            }

            $language = explode('_', $this->view->locale()->getLocale()->__toString());
            $this->view->language = $language[0];
        } else {
            $this->_helper->content->setEnabled();
        }

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $select = Engine_Api::_()->getDbTable('events', 'sesevent')->select()->where('user_id =?', $viewer->getIdentity());
        $paginator = count(Engine_Api::_()->getDbTable('events', 'sesevent')->fetchAll($select));
        $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesevent_event', 'maxevent');
        $this->view->current_count = $paginator;

        $viewer = Engine_Api::_()->user()->getViewer();
        $parent_type = $this->_getParam('parent_type');
        $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));
        if ($parent_type == 'group' && Engine_Api::_()->hasItemType('group')) {
            $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
            if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'sesevent_event')->isValid())
                return;
        } else {
            $parent_type = 'user';
            $parent_id = $viewer->getIdentity();
        }

        //Event Category and profile fields check
        $event_id = $this->_getParam('event_id', false);
        if ($event_id)
            $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
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
        //Create form
        $this->view->parent_type = $parent_type;


        $this->view->form = $form = new Sesevent_Form_Create(array(
            'parent_type' => $parent_type,
            'parent_id' => $parent_id,
            'defaultProfileId' => $defaultProfileId,
            'smoothboxType' => $sessmoothbox,
        ));
        if (isset($event) && ($viewer->getIdentity() == $event->user_id)) {
            $this->view->form->setDefaults($event->getCleanData());
        }
        // Not post/invalid
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        //check custom url
        if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
            $custom_url = Engine_Api::_()->getDbtable('events', 'sesevent')->checkCustomUrl($_POST['custom_url']);
            if ($custom_url) {
                $form->addError($this->view->translate("Custom Url not available.Please select other."));
                return;
            }
        }
        // if enddate is disabled, smaller endtime means the next day
        $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'] . ' ' . $_POST['start_time'])) : '';
        if ($_POST['end_date'] == null) {
            $_POST['end_date'] = $_POST['start_date'];
            $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'] . ' ' . $_POST['end_time'])) : '';
            if (strtotime($starttime) >= strtotime($endtime)) {
                $endtime = date('Y-m-d H:i:s', strtotime($endtime . " +1 day"));
            }
        } else {
            $endtime = isset($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'] . ' ' . $_POST['end_time'])) : '';
        }
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $values['parent_type'] = $parent_type;
        $values['parent_id'] = $parent_id;
        $values['timezone'] = isset($_POST['timezone']) ? $_POST['timezone'] : '';
        $values['location'] = isset($_POST['location']) ? $_POST['location'] : '';
        $values['show_timezone'] = !empty($_POST['show_timezone']) ? $_POST['show_timezone'] : '0';
        $values['show_endtime'] = !empty($_POST['show_endtime']) ? $_POST['show_endtime'] : '0';
        $values['show_starttime'] = !empty($_POST['show_starttime']) ? $_POST['show_starttime'] : '0';
        $values['venue_name'] = isset($_POST['venue_name']) ? $_POST['venue_name'] : '';
        $values['region_id'] = $_POST['region'] ?? '';

        if (isset($values['additional_costs_amount'])) {
            $values['additional_costs_amount'] = floatval(str_replace(",", ".", $values['additional_costs_amount']));
            $values['additional_costs_amount_currency'] = Engine_Api::_()->sesbasic()->getCurrentCurrency();
        }

        if (isset($values['age_categories'])) {
            $age_categories = Sesevent_Model_Event::getAgeCategoriesToInterval($values['age_categories']);
            $values['age_category_from'] = $age_categories['from'];
            $values['age_category_to'] = $age_categories['to'];
        }
        if (empty($values['timezone'])) {
            $form->addError(Zend_Registry::get('Zend_Translate')->_('Timezone is a required field.'));
            return;
        }
        if ($parent_type == 'group' && Engine_Api::_()->hasItemType('group') && empty($values['host'])) {
            $values['host'] = $group->getTitle();
        }
        if (strtotime($starttime) >= strtotime($endtime)) {
            $form->addError(Zend_Registry::get('Zend_Translate')->_('Start Time must be less than End Time.'));
            return;
        }

        $startTime = new DateTime($values['starttime']);
        $endTime = new DateTime($values['endtime']);

        if (!$viewer->isAdmin() && (int) $endTime->diff($startTime)->format("%a") > 1) {
            $form->addError(Zend_Registry::get('Zend_Translate')->_("Regular members can't create events that take more than 1 day."));
            return;
        }

        $settings = Engine_Api::_()->getApi('settings', 'core');
        if ($settings->getSetting('sesevent.eevecremainphoto', 1) && $settings->getSetting('sesevent.eevecremainphoto', 1)) {
            if (empty($values['photo'])) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
                return;
            }
        }

        $resource_id = $this->_getParam('resource_id', null);
        $resource_type = $this->_getParam('resource_type', null);

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

            if (isset($values['levels']))
                $values['levels'] = implode(',', $values['levels']);

            if (isset($values['networks']))
                $values['networks'] = implode(',', $values['networks']);

            if (!($values['is_sponsorship']))
                $values['is_sponsorship'] = 0;
            if (!$values['is_custom_term_condition'])
                unset($values['custom_term_condition']);
            //set location
            if (empty($_POST['location'])) {
                unset($values['location']);
                unset($values['lat']);
                unset($values['lng']);
                unset($values['venue_name']);
                $values['is_webinar'] = 1;
            } else
                $values['is_webinar'] = 0;
            $values['is_approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_approve');
            //Host save function
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
            $values['featured'] = (int) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_sponsored');
            $values['sponsored'] = (int) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_featured');
            $values['verified'] = (int) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'event_verified');
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
            if (!$event->is_webinar) {
                if (!empty($_POST['location'])) {
                    $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
                    $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id,venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $event->event_id . '","' . $_POST['location'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","' . $_POST['city'] . '","' . $_POST['state'] . '","' . $_POST['zip'] . '","' . $_POST['country'] . '","' . $_POST['address'] . '","' . $_POST['address2'] . '",  "sesevent_event")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '",city = "' . $_POST['city'] . '", state = "' . $_POST['state'] . '", country = "' . $_POST['country'] . '", zip = "' . $_POST['zip'] . '", address = "' . $_POST['address'] . '", address2 = "' . $_POST['address2'] . '", venue = "' . $_POST['venue'] . '"');
                }
            }

            $event->seo_keywords = implode(',', $tags);
            //$event->seo_title = $event->title;
            $event->save();
            $tags = preg_split('/[,]+/', $values['tags']);
            $event->tags()->addTagMaps($viewer, $tags);
            // Add owner as member
            $event->membership()->addMember($viewer)
                    ->setUserApproved($viewer)
                    ->setResourceApproved($viewer);
            // Add owner rsvp
            $event->membership()
                    ->getMemberInfo($viewer)
                    ->setFromArray(array('rsvp' => 2))
                    ->save();
            $event->increaseGenderCount($viewer);

            // Add photo
            if (!empty($values['photo'])) {
                $event->setPhoto($form->photo);
                $event->setCoverPhoto($form->photo);
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
            if ($customfieldform) {
                $customfieldform->setItem($event);
                $customfieldform->saveValues();
            }
            $event->save();

            // Other module work
            if (!empty($resource_type) && !empty($resource_id)) {
                $event->resource_id = $resource_id;
                $event->resource_type = $resource_type;
                $event->save();
            }

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
            }

            $userTable = Engine_Api::_()->getItemTable('user');
            $users = $userTable->fetchAll();
            //email to user
            if ($event->is_approved == 1) {
                foreach ($users as $user) {
                    if ($user->getIdentity() != $event->getOwner()->getIdentity()) {
                        if ($event->is_webinar) {
                            if ($event->starttime <= date('Y-m-d h:m:s', time() + 3 * 24 * 60 * 60)) {
                                //notify and email to user register its for last minute event
                                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                    $user,
                                    $viewer,
                                    $event,
                                    'sesevent_last_minute_online_event',
                                    array(
                                        'queue' => true,
                                        'object_date' => $event->getTime('starttime', 'j M'),
                                        'object_time' => $event->getTime('starttime', 'H:i')
                                    )
                                );
                            } else {
                                //notify and email to user register its for online event
                                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                    $user,
                                    $viewer,
                                    $event,
                                    'sesevent_new_online_event',
                                    array(
                                        'queue' => true,
                                        'object_date' => $event->getTime('starttime', 'j M'),
                                        'object_time' => $event->getTime('starttime', 'H:i')
                                    )
                                );
                            }
                        } elseif (isset($event->region_id)) {
                            //notify and email to user register its for new event in their region
                            if ($user->checkInRegion($event->region_id)) {
                                if ($event->starttime <= date('Y-m-d h:m:s', time() + 3 * 24 * 60 * 60)) {
                                    //notify and email to user register its for new event in their region
                                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                        $user,
                                        $viewer,
                                        $event,
                                        'sesevent_last_minute_event',
                                        array(
                                            'queue' => true,
                                            'object_date' => $event->getTime('starttime', 'j M'),
                                            'object_time' => $event->getTime('starttime', 'H:i')
                                        )
                                    );
                                } else {
                                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                                        $user,
                                        $viewer,
                                        $event,
                                        'sesevent_new_event',
                                        array(
                                            'queue' => true,
                                            'object_date' => $event->getTime('starttime', 'j M'),
                                            'object_time' => $event->getTime('starttime', 'H:i')
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
            }
            $field = $userTable->select()
                    ->from($userTable->info('name'))
                    ->where("level_id = ?", 1);
            $admins = $userTable->fetchAll($field);
            
            //Send mail to admin after user create event
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($admins, 'sesevent_event_create', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            
            //Event create mail send to event owner
            if (!$event->getOwner()->isAdmin()) {
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($event->getOwner(), 'sesevent_event_create', array('event_title' => $event->title, 'object_link' => $event->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }

            // Redirect
            $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.autoopenpopup', 1);
            if ($autoOpenSharePopup && $event->draft && $event->is_approved) {
                $_SESSION['newEvent'] = true;
            }
            $redirection = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.redirect', 1);
            if (!$event->is_approved) {
                return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'sesevent_general', true);
            } else if ($redirection == 1) {
                header('location:' . $event->getHref());
            } else {
                return $this->_helper->redirector->gotoRoute(array('event_id' => $event->custom_url), 'sesevent_dashboard', true);
            }
        } catch (Engine_Image_Exception $e) {
            $db->rollBack();
            $form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function uploadPhotoAction() {
        $viewer = Engine_Api::_()->user()->getViewer();

        $this->_helper->layout->disableLayout();

        if (!Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create')) {
            return false;
        }

        if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid())
            return;

        if (!$this->_helper->requireUser()->checkRequire()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }
        if (!isset($_FILES['userfile']) || !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
            return;
        }

        $db = Engine_Api::_()->getDbtable('photos', 'album')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();

            $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
            $photo = $photoTable->createRow();
            $photo->setFromArray(array(
                'owner_type' => 'user',
                'owner_id' => $viewer->getIdentity()
            ));
            $photo->save();

            $photo->setPhoto($_FILES['userfile']);

            $this->view->status = true;
            $this->view->name = $_FILES['userfile']['name'];
            $this->view->photo_id = $photo->photo_id;
            $this->view->photo_url = $photo->getPhotoUrl();

            $table = Engine_Api::_()->getDbtable('albums', 'album');
            $album = $table->getSpecialAlbum($viewer, 'sesevent');

            $photo->album_id = $album->album_id;
            $photo->save();

            if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
            }

            $auth = Engine_Api::_()->authorization()->context;
            $auth->setAllowed($photo, 'everyone', 'view', true);
            $auth->setAllowed($photo, 'everyone', 'comment', true);
            $auth->setAllowed($album, 'everyone', 'view', true);
            $auth->setAllowed($album, 'everyone', 'comment', true);


            $db->commit();
        } catch (Sesevent_Model_Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = $this->view->translate($e->getMessage());
            throw $e;
            return;
        } catch (Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
            throw $e;
            return;
        }
    }

    public function getEventAction() {

        $sesdata = array();
        $eventTable = Engine_Api::_()->getDbtable('events', 'sesevent');
        $selectEventTable = $eventTable->select()->where('title LIKE "%' . $this->_getParam('text', '') . '%"');
        $events = $eventTable->fetchAll($selectEventTable);
        foreach ($events as $event) {
            $event_icon = $this->view->itemPhoto($event, 'thumb.icon');
            $sesdata[] = array(
                'id' => $event->event_id,
                'event_id' => $event->event_id,
                'url' => $event->getHref(),
                'label' => $event->title,
                'photo' => $event_icon
            );
        }
        return $this->_helper->json($sesdata);
    }

    public function subcategoryAction() {

        $category_id = $this->_getParam('category_id', null);
        if ($category_id) {
            $categoryTable = Engine_Api::_()->getDbtable('categories', 'sesevent');
            $category_select = $categoryTable->select()
                    ->from($categoryTable->info('name'))
                    ->where('subcat_id = ?', $category_id);
            $subcategory = $categoryTable->fetchAll($category_select);
            $count_subcat = count($subcategory->toarray());
            if (isset($_POST['selected']))
                $selected = $_POST['selected'];
            else
                $selected = '';
            $data = '';
            if ($subcategory && $count_subcat) {
                $data .= '<option value="0">' . Zend_Registry::get('Zend_Translate')->_("Choose a Sub Category") . '</option>';
                foreach ($subcategory as $category) {
                    $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
                }
            }
        } else
            $data = '';
        echo $data;
        die;
    }

    function likeAction() {

        if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
            echo json_encode(array('status' => 'false', 'error' => 'Login'));
            die;
        }

        $type = 'sesevent_event';
        $dbTable = 'events';
        $resorces_id = 'event_id';
        $notificationType = 'sesevent_like_event';

        if ($this->_getParam('type', false) && $this->_getParam('type') == 'sesevent_album') {
            $type = 'sesevent_album';
            $dbTable = 'albums';
            $resorces_id = 'album_id';
            $notificationType = 'sesevent_like_eventalbum';
        } else if ($this->_getParam('type', false) && $this->_getParam('type') == 'sesevent_photo') {
            $type = 'sesevent_photo';
            $dbTable = 'photos';
            $resorces_id = 'photo_id';
            $notificationType = 'sesevent_like_eventphoto';
        } else if ($this->_getParam('type', false) && $this->_getParam('type') == 'sesevent_list') {
            $type = 'sesevent_list';
            $dbTable = 'lists';
            $resorces_id = 'list_id';
            $notificationType = 'sesevent_like_eventlist';
        } else if ($this->_getParam('type', false) && $this->_getParam('type') == 'seseventspeaker_speaker') {
            $type = 'seseventspeaker_speaker';
            $dbTable = 'speakers';
            $resorces_id = 'speaker_id';
            $notificationType = 'sesevent_like_eventspeaker';
        } else if ($this->_getParam('type', false) && $this->_getParam('type') == 'sesevent_host') {
            $type = 'sesevent_host';
            $dbTable = 'hosts';
            $resorces_id = 'host_id';
            $notificationType = 'sesevent_like_eventhost';
        }

        $item_id = $this->_getParam('id');
        if (intval($item_id) == 0) {
            echo json_encode(array('status' => 'false', 'error' => 'Invalid argument supplied.'));
            die;
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();

        if ($this->_getParam('type', false) && $this->_getParam('type') == 'seseventspeaker_speaker') {
            $itemTable = Engine_Api::_()->getDbtable($dbTable, 'seseventspeaker');
        } else {
            $itemTable = Engine_Api::_()->getDbtable($dbTable, 'sesevent');
        }

        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableMainLike = $tableLike->info('name');

        $select = $tableLike->select()
                ->from($tableMainLike)
                ->where('resource_type = ?', $type)
                ->where('poster_id = ?', $viewer_id)
                ->where('poster_type = ?', 'user')
                ->where('resource_id = ?', $item_id);
        $result = $tableLike->fetchRow($select);

        if (count($result) > 0) {
            //delete
            $db = $result->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $result->delete();
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }


            $itemTable->update(array('like_count' => new Zend_Db_Expr('like_count - 1')), array($resorces_id . ' = ?' => $item_id));

            $item = Engine_Api::_()->getItem($type, $item_id);

            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
            Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
            Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
            echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'reduced', 'count' => $item->like_count));
            die;
        } else {

            //update
            $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
            $db->beginTransaction();
            try {

                $like = $tableLike->createRow();
                $like->poster_id = $viewer_id;
                $like->resource_type = $type;
                $like->resource_id = $item_id;
                $like->poster_type = 'user';
                $like->save();

                $itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array($resorces_id . '= ?' => $item_id));

                //Commit
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }

            //Send notification and activity feed work.
            $item = Engine_Api::_()->getItem($type, $item_id);
            $subject = $item;
            $owner = $subject->getOwner();
            if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');

                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));

                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);

                $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));

                //if (!$result) {
                $action = $activityTable->addActivity($viewer, $subject, $notificationType);
                if ($action)
                    $activityTable->attachActivity($action, $subject);
                //}
            }
            echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'increment', 'count' => $item->like_count));
            die;
        }
    }

    //item favourite as per item tye given
    function favouriteAction() {
        if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
            echo json_encode(array('status' => 'false', 'error' => 'Login'));
            die;
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
            echo json_encode(array('status' => 'false', 'error' => 'Invalid argument supplied.'));
            die;
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
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
            $item = Engine_Api::_()->getItem($type, $item_id);
            if (@$notificationType) {
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
                Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
                Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
            }
            echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'reduced', 'count' => $item->favourite_count));
            $this->view->favourite_id = 0;
            die;
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
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
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
            $this->view->favourite_id = 1;
            echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'increment', 'count' => $item->favourite_count, 'favourite_id' => 1));
            die;
        }
    }

    public function subsubcategoryAction() {
        $category_id = $this->_getParam('subcategory_id', null);
        if ($category_id) {
            $categoryTable = Engine_Api::_()->getDbtable('categories', 'sesevent');
            $category_select = $categoryTable->select()
                    ->from($categoryTable->info('name'))
                    ->where('subsubcat_id = ?', $category_id);
            $subcategory = $categoryTable->fetchAll($category_select);
            $count_subcat = count($subcategory->toarray());
            if (isset($_POST['selected']))
                $selected = $_POST['selected'];
            else
                $selected = '';
            $data = '';
            if ($subcategory && $count_subcat) {
                $data .= '<option value="0">' . Zend_Registry::get('Zend_Translate')->_("Choose a Sub Sub Category") . '</option>';
                foreach ($subcategory as $category) {
                    $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '">' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
                }
            }
        } else
            $data = '';
        echo $data;
        die;
    }

    public function linkBlogAction() {
        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->event_id = $event_id = $this->_getParam('event_id', '0');
        if ($event_id == 0)
            return;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $this->view->viewmore = isset($_POST['viewmore']) ? $_POST['viewmore'] : '';
        $blogTable = Engine_Api::_()->getItemTable('sesblog_blog');
        $blogTableName = $blogTable->info('name');
        $blogMapTable = Engine_Api::_()->getDbTable('mapevents', 'sesblog');
        $blogMapTableName = $blogMapTable->info('name');
        $select = $blogTable->select()
                ->setIntegrityCheck(false)
                ->from($blogTableName)
                ->Joinleft($blogMapTableName, "$blogMapTableName.blog_id = $blogTableName.blog_id", null)
                ->where($blogTableName . '.blog_id !=?', '')
                ->where($blogMapTableName . '.event_id !=? or ' . $blogMapTableName . '.event_id is null', $event_id);
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        // Set item count per page and current page number
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($page);
        if (!$this->getRequest()->isPost())
            return;
        $blogIds = $_POST['blog'];
        $eventObject = Engine_Api::_()->getItem('sesevent_event', $event_id);
        foreach ($blogIds as $blogId) {
            $item = Engine_Api::_()->getItem('sesblog_blog', $blogId);
            $owner = $item->getOwner();
            $table = Engine_Api::_()->getDbtable('mapevents', 'sesblog');
            $db = $table->getAdapter();
            $db->beginTransaction();
            try {
                $seseventblog = $table->createRow();
                $seseventblog->blog_id = $blogId;
                $seseventblog->event_id = $event_id;
                $seseventblog->request_owner_event = 1;
                $seseventblog->approved = 0;
                $seseventblog->save();
                $blogPageLink = '<a href="' . $item->getHref() . '">' . ucfirst($item->getTitle()) . '</a>';
                // Send notifications for subscribers
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $eventObject, 'sesblog_link_blog', array("blogPageLink" => $blogPageLink));
                // Commit
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }
        $this->view->message = Zend_Registry::get('Zend_Translate')->_("Your changes have been saved.");
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefresh' => false,
            'messages' => array($this->view->message)
        ));
    }

    public function saveAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            return;

        $id = $this->_getParam('id');
        $type = $this->_getParam('type');
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
                    $this->view->save_id = $contentId;
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
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            } else {
                $this->view->save_id = $isSave;
            }
        } else {
            Engine_Api::_()->getDbTable('saves', 'sesevent')->delete(array('save_id =?' => $contentId));
        }
    }

    public function followAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            return;

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
                    $this->view->follow_id = $contentId;
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item->getOwner(), $viewer, $item, 'sesevent_eventfollow', array());
                    //Activity Feed Work
                    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                    $action = $activityApi->addActivity($viewer, $item, 'sesevent_event_follow');
                    if ($action) {
                        $activityApi->attachActivity($action, $item);
                    }
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            } else {
                $this->view->follow_id = $isFollow;
            }
        } else {
            Engine_Api::_()->getDbTable('follows', 'sesevent')->delete(array('follow_id =?' => $contentId));
        }
    }

    //update cover photo function
    public function uploadCoverAction() {
        $event_id = $this->_getParam('event_id', '0');
        if ($event_id == 0)
            return;
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        if (!$event)
            return;
        $cover_photo = $event->cover_photo;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        $event->setCoverPhoto($data);
        if ($cover_photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $cover_photo);
            $im->delete();
        }
        echo json_encode(array('file' => Engine_Api::_()->storage()->get($event->cover_photo)->getPhotoUrl('')));
        die;
    }

    //remove cover photo action
    public function removeCoverAction() {
        $event_id = $this->_getParam('event_id', '0');
        if ($event_id == 0)
            return false;
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        if (!$event)
            return false;
        if (isset($event->cover_photo) && $event->cover_photo > 0) {
            $im = Engine_Api::_()->getItem('storage_file', $event->cover_photo);
            $event->cover_photo = 0;
            $event->save();
            $im->delete();
        }
        echo json_encode(array('file' => $event->getCoverPhotoUrl()));
        die;
    }

    //update main photo function
    public function uploadMainAction() {
        $event_id = $this->_getParam('event_id', '0');
        if ($event_id == 0)
            return;
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        if (!$event)
            return;
        $main_photo = $event->photo_id;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        $event->setPhoto($data);
        if ($main_photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $main_photo);
            $im->delete();
        }
        echo json_encode(array('file' => $event->getPhotoUrl()));
        die;
    }

    //remove main photo action
    public function removeMainAction() {
        $event_id = $this->_getParam('event_id', '0');
        if ($event_id == 0)
            return false;
        $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        if (!$event)
            return false;
        if (isset($event->photo_id) && $event->photo_id > 0) {
            $im = Engine_Api::_()->getItem('storage_file', $event->photo_id);
            $event->photo_id = 0;
            $event->save();
            $im->delete();
        }
        echo json_encode(array('file' => $event->getPhotoUrl()));
        die;
    }

    public function tagsAction() {
        $this->_helper->content->setEnabled();
    }

    public function searchAction() {
        $text = $this->_getParam('text', null);
        $table = Engine_Api::_()->getDbtable('events', 'sesevent');
        $select = $table->select()->where('title LIKE ? OR description LIKE ?', '%' . $text . '%')->where("is_delete = 0")->where("is_approved = 1")->where("search = 1");
        $select->limit('10');
        $results = Zend_Paginator::factory($select);
        $data = array();
        foreach ($results as $result) {
            $photo_icon_photo = $this->view->itemPhoto($result, 'thumb.icon');
            $data[] = array(
                'id' => $result->getIdentity(),
                'label' => $result->getTitle(),
                'photo' => $photo_icon_photo,
                'url' => $result->getHref(),
                'resource_type' => '',
            );
        }
        return $this->_helper->json($data);
    }

    public function guestInfoAction() {
        $event_id = $this->_getParam('event_id', '0');
        $this->view->value = $value = $this->_getParam('value', '0');
        if (!$event_id || !$value)
            return;
        if ($value == 'attending')
            $textVal = 'Guest Attending This Event';
        else if ($value == 'notattending')
            $textVal = 'Guest Not Attending This Event';
        else if ($value == 'maybeattending')
            $textVal = 'Guest Maybe Attending This Event';
        else if ($value == "onwaitinglist")
            $textVal = 'Guest on Waiting List';

        $this->view->textVal = $textVal;
        $this->view->textVal = $textVal;
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $this->view->viewmore = isset($_POST['viewmore']) ? $_POST['viewmore'] : '';
        $this->view->event = $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
        $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id' => $event->getIdentity(), 'type' => $value));
        $this->view->event_id = $event->event_id;
        // Set item count per page and current page number
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($page);
    }

}
