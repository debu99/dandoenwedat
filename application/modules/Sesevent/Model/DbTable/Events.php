<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Events.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Events extends Engine_Db_Table
{
    protected $_rowClass = "Sesevent_Model_Event";

    public function countEvents($params = array())
    {
        $select = $this->select()->from($this->info('name'), array('*'));
        if (isset($params['host'])) {
            $select->where('host_type =?', $params['host']);
        }
        if (isset($params['host_id'])) {
            $select->where('host =?', $params['host_id']);
        }
        if (isset($params['fetchAll'])) {
            return $this->fetchAll($select);
        }
        return Zend_Paginator::factory($select);
    }

    public function getEventPaginator($params = array())
    {
        return Zend_Paginator::factory($this->getEventSelect($params));
    }

    public function getEventSelect($params = array())
    {

        if (!empty($params['start_date']))
            $params['start_date'] = date('Y-m-d', strtotime($params['start_date']));

        if (!empty($params['end_date']))
            $params['end_date'] = date('Y-m-d', strtotime($params['end_date']));

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $tableLocation = Engine_Api::_()->getDbtable('locations', 'sesbasic');
        $tableLocationName = $tableLocation->info('name');
        $tableTagmap = Engine_Api::_()->getDbtable('tagMaps', 'core');
        $tableTagName = $tableTagmap->info('name');
        $tableTag = Engine_Api::_()->getDbtable('tags', 'core');
        $tableMainTagName = $tableTag->info('name');
        $likesTable = Engine_Api::_()->getDbtable('likes', 'core');
        $likesTableName = $likesTable->info('name');
        $table = Engine_Api::_()->getItemTable('event');
        $currentTime = date('Y-m-d H:i:s');
        $eventTableName = $table->info('name');
        if (isset($params['lat']))
            $origLat = $params['lat'];
        if (isset($params['lng']))
            $origLon = $params['lng'];
        $searchType = 6371;
        //This is the maximum distance (in miles) away from $origLat, $origLon in which to search
        if (isset($params['miles']))
            $dist = $params['miles'];
        $select = $table->select()
            ->from($eventTableName)
            ->setIntegrityCheck(false);
        $membershipTableName = Engine_Api::_()->getDbtable('membership', 'sesevent')->info('name');
        $select->joinLeft($membershipTableName, $membershipTableName . '.resource_id = ' . $this->info('name') . ' .event_id AND ' . $membershipTableName . '.active = 1  AND ' . $membershipTableName . '.resource_approved = 1  AND ' . $membershipTableName . '.user_approved = 1  AND ' . $membershipTableName . '.rsvp = 2', 'COUNT(' . $membershipTableName . '.resource_id) as joinedmember')->group($membershipTableName . '.resource_id');
        if (isset($params['most_joined_event'])) {
            $select->order('joinedmember DESC');
        }
        if (isset($params['tag_id']) && !empty($params['tag_id'])) {
            $select->joinLeft($tableTagName, $tableTagName . '.resource_id=' . $eventTableName . '.event_id', null)
                ->joinLeft($tableMainTagName, $tableMainTagName . '.tag_id = ' . $tableTagName . '.tag_id', null);
            $select->where("$tableTagName.tag_id  = ?", $params['tag_id']);
        }
        if (isset($params['widgetName']) && $params['widgetName'] == 'Also Liked') {
            $select->distinct(true)
                ->joinLeft($likesTableName, $likesTableName . '.resource_id=event_id', null)
                ->joinLeft($likesTableName . ' as l2', $likesTableName . '.poster_id=l2.poster_id', null)
                ->where($likesTableName . '.poster_type = ?', 'user')
                ->where('l2.poster_type = ?', 'user')
                ->where($likesTableName . '.resource_type = ?', 'sesevent_event')
                ->where('l2.resource_type = ?', 'sesevent_event')
                ->where($likesTableName . '.resource_id != ?', $params['poster_id'])
                ->where('l2.resource_id = ?', $params['poster_id'])
                ->where('search = ?', true)
                ->where('event_id != ?', $params['poster_id']);
        }
        if (!empty($params['city'])) {
            $select->where('`' . $tableLocationName . '`.`city` LIKE ?', '%' . $params['city'] . '%');
        }
        if (!empty($params['state'])) {
            $select->where('`' . $tableLocationName . '`.`state` LIKE ?', '%' . $params['state'] . '%');
        }
        if (!empty($params['country'])) {
            $select->where('`' . $tableLocationName . '`.`country` LIKE ?', '%' . $params['country'] . '%');
        }
        if (!empty($params['zip'])) {
            $select->where('`' . $tableLocationName . '`.`zip` LIKE ?', '%' . $params['zip'] . '%');
        }
        if (!empty($params['venue'])) {
            $select->where('`' . $tableLocationName . '`.`venue` LIKE ?', '%' . $params['venue'] . '%');
        }
        if (isset($params['lat']) && isset($params['miles']) && $params['miles'] != 0 && isset($params['lng']) && $params['lat'] != '' && $params['lng'] != '' && ((isset($params['location']) && $params['location'] != '' && strtolower($params['location']) != 'world'))) {
            $origLat = $lat = $params['lat'];
            $origLon = $long = $params['lng'];
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.search.type', 1) == 1) {
                $searchType = 3956;
            } else
                $searchType = 6371;
            //This is the maximum distance (in miles) away from $origLat, $origLon in which to search
            $dist = $params['miles'];

            $select->joinLeft($tableLocationName, $tableLocationName . '.resource_id = ' . $eventTableName . '.event_id AND ' . $tableLocationName . '.resource_type = "sesevent_event" ', array('lat', 'lng', 'distance' => new Zend_Db_Expr($searchType . " * 2 * ASIN(SQRT( POWER(SIN(($lat - lat) *  pi()/180 / 2), 2) +COS($lat * pi()/180) * COS(lat * pi()/180) * POWER(SIN(($long - lng) * pi()/180 / 2), 2) ))")));

            $rectLong1 = $long - $dist / abs(cos(deg2rad($lat)) * 69);
            $rectLong2 = $long + $dist / abs(cos(deg2rad($lat)) * 69);
            $rectLat1 = $lat - ($dist / 69);
            $rectLat2 = $lat + ($dist / 69);

            $select->where($tableLocationName . ".lng between $rectLong1 AND $rectLong2  and " . $tableLocationName . ".lat between $rectLat1 AND $rectLat2");


            $select->order('distance');
            $select->having("distance < $dist");
        } else {
            $select->joinLeft($tableLocationName, $tableLocationName . '.resource_id = ' . $eventTableName . '.event_id AND ' . $tableLocationName . '.resource_type = "sesevent_event" ', array('lat', 'lng'));
        }
        if (empty($params['widgetManage'])) {
            $select->where($eventTableName . '.draft = ?', (bool)1);
            $select->where($eventTableName . '.is_approved = ?', (bool)1);
            $select->where($eventTableName . '.search = ?', (bool)1);
        }
        $select->where($eventTableName . '.is_delete = ?', 0);
        if (isset($params['owner']) && $params['owner'] instanceof Core_Model_Item_Abstract) {
            $select->where($eventTableName . '.user_id = ?', $params['owner']->getIdentity());
        } else if (isset($params['user_id']) && !empty($params['user_id'])) {
            $select->where($eventTableName . '.user_id = ?', $params['user_id']);
        } else if (isset($params['users']) && is_array($params['users'])) {
            $users = array();
            foreach ($params['users'] as $user_id) {
                if (is_int($user_id) && $user_id > 0) {
                    $users[] = $user_id;
                }
            }
            // if users is set yet there are none, $select will always return an empty rowset
            if (empty($users))
                return $select->where('1 != 1');
            else
                $select->where($eventTableName . ".user_id IN (?)", $users);
        }
        //get event between from-to
        if (isset($params['to']) && isset($params['from']) && isset($params['viewCal'])) {
            //year month day week
            if ($params['viewCal'] == 'month') {
                $select->where("DATE_FORMAT(" . $eventTableName . ".endtime, '%Y-%m') between ('" . date('Y-m', strtotime($params["from"])) . "') and ('" . date('Y-m', strtotime($params["to"])) . "')");
            } else if ($params['viewCal'] == 'week' || $params['viewCal'] == 'day') {
                $select->where("DATE_FORMAT(" . $eventTableName . ".endtime, '%Y-%m-%d') between ('" . date('Y-m-d', strtotime($params["from"])) . "') and ('" . date('Y-m-d', strtotime($params["to"])) . "') OR DATE_FORMAT(" . $eventTableName . ".starttime, '%Y-%m-%d') between ('" . date('Y-m-d', strtotime($params["from"])) . "') and ('" . date('Y-m-d', strtotime($params["to"])) . "')");
            } else if ($params['viewCal'] == 'year') {
                $select->where("DATE_FORMAT(" . $eventTableName . ".endtime, '%Y') between ('" . date('Y', strtotime($params["from"])) . "') and ('" . date('Y', strtotime($params["to"])) . "') OR DATE_FORMAT(" . $eventTableName . ".starttime, '%Y') between ('" . date('Y', strtotime($params["from"])) . "') and ('" . date('Y', strtotime($params["to"])) . "')");
            }
        }
        if (isset($params['starttimeWidget'])) {
            $startTime = $params['starttimeWidget'];
            $endTime = $params['endtimeWidget'];
            $select->where("DATE(starttime) between ('$startTime') and ('$endTime')");
            $select->where("DATE(endtime) <= '$endTime'");
            $select->order('starttime');
            $select->group('starttime');
            $select->joinLeft($eventTableName, $eventTableName . '.event_id = ' . $eventTableName . '.event_id', null);
        }

        if (isset($params['date_range_from']) && isset($params['date_range_to'])) {
            $date_range_from = $params['date_range_from'];
            $date_range_to = $params['date_range_to'];
            $select->where("('$date_range_from' <= endtime)");
            $select->where("('$date_range_to' >= starttime)");
        }
        if (isset($params['view'])) {
            if ($params['view'] == 'week') {
                $select->where("((YEARWEEK(starttime) = YEARWEEK('$currentTime')) || YEARWEEK(endtime) = YEARWEEK('$currentTime'))  || (DATE(starttime) <= DATE('$currentTime') AND DATE(endtime) >= DATE('$currentTime'))");
            } elseif ($params['view'] == 'weekend') {
                $select->where("DATE_ADD('$currentTime', INTERVAL (7+5 - weekday('$currentTime'))%7 DAY) between starttime and endtime OR  DATE_ADD('$currentTime', INTERVAL 6 - weekday('$currentTime') DAY) between starttime and endtime");
            } elseif ($params['view'] == 'month') {
                $select->where("((YEAR(starttime) > YEAR('$currentTime')) || YEAR(starttime) <= YEAR('$currentTime') AND (MONTH(starttime) <= MONTH('$currentTime'))) AND ((YEAR(endtime) > YEAR('$currentTime') || MONTH(endtime) >= MONTH('$currentTime')) AND YEAR(endtime) >= YEAR('$currentTime'))");
            } elseif ($params['view'] == 'ongoing') {
                $select->where($eventTableName . ".endtime >= ?", $currentTime)->where($eventTableName . ".starttime <= ?", $currentTime);
            } else if ($params['view'] == "ongoingSPupcomming") {
                $select->where("(endtime >= '" . $currentTime . "') || (endtime > '" . $currentTime . "' && starttime > '" . $currentTime . "')");
            } else if ($params['view'] == 1) {
                if ($viewer->getIdentity()) {
                    $users = $viewer->membership()->getMembershipsOfIds();
                    if ($users)
                        $select->where($eventTableName . '.user_id IN (?)', $users);
                    else
                        $select->where($eventTableName . '.user_id IN (?)', 0);
                }
            }
        }
        //Category
        if (isset($params['category_id']) && ($params['category_id'] != '' || $params['category_id'] === 0))
            $select->where($eventTableName . '.category_id = ?', $params['category_id']);
        else if (isset($params['getcategory0']) && !empty($params['category_id']))
            $select->where($eventTableName . '.category_id = ?', $params['category_id']);

        if (isset($params['subcat_id']) && !empty($params['subcat_id']))
            $select->where($eventTableName . '.subcat_id = ?', $params['subcat_id']);
        if (isset($params['subsubcat_id']) && !empty($params['subsubcat_id']))
            $select->where($eventTableName . '.subsubcat_id = ?', $params['subsubcat_id']);
        if (!empty($params['event_id']))
            $select->where($eventTableName . '.event_id =?', $params['event_id']);
        if (!empty($params['hosted_id'])) {
            $select->where($eventTableName . '.host =?', $params['hosted_id']);
            $select->where($eventTableName . '.host_type =?', 'site');
        }
        if (isset($params['sponsorship_owner_id'])) {
            $spTable = Engine_Api::_()->getDbTable('sponsorshipmembers', 'sesevent');
            $spTableName = $spTable->info('name');
            $select->where($spTableName . '.owner_id =?', $params['sponsorship_owner_id'])
                ->where($spTableName . '.status =?', 'complete')
                ->where($spTableName . '.sponsorshipmemeber_id !=?', '');
            $select = $select->setIntegrityCheck(false);
            $select = $select->joinLeft($spTableName, "$spTableName.event_id=$eventTableName.event_id", NULL);
        } else if (isset($params['spoked_id'])) {
            $seakerTableName = Engine_Api::_()->getDbTable('speakers', 'seseventspeaker')->info('name');
            $seakerContentTableName = Engine_Api::_()->getDbTable('eventspeakers', 'seseventspeaker')->info('name');
            $sponkedIn = $params['spoked_id'];
            $select->where($seakerContentTableName . '.type =?', 'sitemember')
                ->where($seakerContentTableName . '.enabled =?', 1)
                ->where($seakerTableName . '.user_id =?', $sponkedIn);
            $select = $select->setIntegrityCheck(false);
            $select = $select->joinLeft($seakerContentTableName, "$eventTableName.event_id=$seakerContentTableName.event_id", NULL);
            $select = $select->joinLeft($seakerTableName, "$seakerTableName.speaker_id=$seakerContentTableName.speaker_id", NULL);
            $select = $select->where($seakerTableName . '.speaker_id != ?', '');
        }
        //Full Text
        if (isset($params['text']) && $params['text']) {
            $search_text = $params['text'];
            $select->where($eventTableName . ".description LIKE '%$search_text%' or " . $eventTableName . ".title LIKE '%$search_text%'");
        }
        if (isset($params['manageorder']) && !empty($params['manageorder'])) {
            if (isset($params['searchCtr']) && $params['searchCtr'] == 'past')
                $select->where("endtime <= FROM_UNIXTIME(?)", time());
            else if (isset($params['searchCtr']) && $params['searchCtr'] == 'ongoing')
                $select->where($eventTableName . ".endtime >= ?", $currentTime)->where($eventTableName . ".starttime <= ?", $currentTime);
            else if (isset($params['searchCtr']) && $params['searchCtr'] == 'week')
                $select->where("((YEARWEEK(starttime) = YEARWEEK('$currentTime')) || YEARWEEK(endtime) = YEARWEEK('$currentTime'))  || (DATE(starttime) <= DATE('$currentTime') AND DATE(endtime) >= DATE('$currentTime'))");
            else if (isset($params['searchCtr']) && $params['searchCtr'] == 'weekend')
                $select->where("DATE_ADD('$currentTime', INTERVAL (7+5 - weekday('$currentTime'))%7 DAY) between starttime and endtime OR  DATE_ADD('$currentTime', INTERVAL 6 - weekday('$currentTime') DAY) between starttime and endtime");
            else if (isset($params['searchCtr']) && $params['searchCtr'] == 'month')
                $select->where("((YEAR(starttime) > YEAR('$currentTime')) || YEAR(starttime) <= YEAR('$currentTime') AND (MONTH(starttime) <= MONTH('$currentTime'))) AND ((YEAR(endtime) > YEAR('$currentTime') || MONTH(endtime) >= MONTH('$currentTime')) AND YEAR(endtime) >= YEAR('$currentTime'))");
            else if (isset($params['searchCtr']) && $params['searchCtr'] == 'upcoming')
                $select->where("endtime > FROM_UNIXTIME('" . time() . "') && starttime > FROM_UNIXTIME('" . time() . "')");
            else
                $select->where("(endtime >= '" . $currentTime . "') || (endtime > '" . $currentTime . "' && starttime > '" . $currentTime . "')");
            if ($params['manageorder'] == 'like') {
                $likeTable = Engine_Api::_()->getDbTable('likes', 'core');
                $likeTableName = $likeTable->info('name');
                $select->where($likeTableName . '.resource_type =?', 'sesevent_event')
                    ->where($likeTableName . '.poster_id =?', $viewer_id)
                    ->order($likeTableName . '.like_id DESC');
                $select = $select->setIntegrityCheck(false);
                $select = $select->joinLeft($likeTableName, "$likeTableName.resource_id=$eventTableName.event_id", NULL);
                $select = $select->where($eventTableName . '.event_id != ?', '');
                $select = $select->where($likeTableName . '.like_id != ?', '');
            } else if ($params['manageorder'] == 'joinedEvents') {
                $select->where($membershipTableName . '.user_id =?', $viewer_id);
            } else if ($params['manageorder'] == 'verified') {
                $select->where($eventTableName . '.verified =?', 1);
                $select->where($eventTableName . '.user_id =?', $viewer_id);
            } else if ($params['manageorder'] == 'sponsored') {
                $select->where($eventTableName . '.sponsored =?', 1);
                $select->where($eventTableName . '.user_id =?', $viewer_id);
            } else if ($params['manageorder'] == 'featured') {
                $select->where($eventTableName . '.featured =?', 1);
                $select->where($eventTableName . '.user_id =?', $viewer_id);
            } else if ($params['manageorder'] == 'hostedEvents') {
                $getHost_id = Engine_Api::_()->getDbTable('hosts', 'sesevent')->getHostId(array('host_type' => 'site', 'toValues' => $viewer_id));
                $select->where($eventTableName . '.host =?', $getHost_id);
                $select->where($eventTableName . '.host_type =?', 'site');
            } else if ($params['manageorder'] == 'favourite') {
                $favTable = Engine_Api::_()->getDbTable('favourites', 'sesevent');
                $favTableName = $favTable->info('name');
                $select->where($favTableName . '.resource_type =?', 'sesevent_event')
                    ->where($favTableName . '.user_id =?', $viewer_id)
                    ->order($favTableName . '.favourite_id DESC');
                $select = $select->setIntegrityCheck(false);
                $select = $select->joinLeft($favTableName, "$favTableName.resource_id=$eventTableName.event_id", NULL);
                $select = $select->where($eventTableName . '.event_id != ?', '');
                $select = $select->where($favTableName . '.favourite_id != ?', '');
            } else if ($params['manageorder'] == 'save') {
                $saveTable = Engine_Api::_()->getDbTable('saves', 'sesevent');
                $saveTableName = $saveTable->info('name');
                $select->where($saveTableName . '.resource_type =?', 'sesevent_event')
                    ->where($saveTableName . '.poster_id =?', $viewer_id)
                    ->order($saveTableName . '.save_id DESC');
                $select = $select->setIntegrityCheck(false);
                $select = $select->joinLeft($saveTableName, "$saveTableName.resource_id=$eventTableName.event_id", NULL);
                $select = $select->where($eventTableName . '.event_id != ?', '');
                $select = $select->where($saveTableName . '.save_id != ?', '');
            } else
                $select->where($eventTableName . '.user_id =?', $viewer_id);
        }
        if (isset($params['order']) && !empty($params['order'])) {
            if ($params['order'] == 'featured')
                $select->where('featured = ?', '1');
            elseif ($params['order'] == 'sponsored')
                $select->where('sponsored = ?', '1');
            elseif ($params['order'] == 'verified')
                $select->where('verified = ?', '1');
            else if ($params['order'] == 'upcoming')
                $select->where("endtime > FROM_UNIXTIME('" . time() . "') && starttime > FROM_UNIXTIME('" . time() . "')");
            elseif ($params['order'] == 'past')
                $select->where("endtime <= FROM_UNIXTIME(?)", time());

            if ($params['order'] == 'week') {
                $monday = date("Y-m-d", strtotime('monday this week'));
                $sunday = date("Y-m-d", strtotime('sunday this week'));
                $select->where("DATE(" . $eventTableName . ".starttime) between ('$monday') and ('$sunday')");
            } elseif ($params['order'] == 'weekend') {
                $saturday = date("Y-m-d", strtotime('saturday this week'));
                $sunday = date("Y-m-d", strtotime('sunday this week'));
                $select->where("DATE(" . $eventTableName . ".starttime) between ('$saturday') and ('$sunday')");
            } elseif ($params['order'] == 'month') {
                $firstDay = date('Y-m-01');
                $lastDay = date('Y-m-t');
                $select->where("DATE(" . $eventTableName . ".starttime) between ('$firstDay') and ('$lastDay')");
            } elseif ($params['order'] == 'recentlySPcreated') {
                $select->order('creation_date DESC');
            } elseif ($params['order'] == 'ongoing') {
                $select->where($eventTableName . ".endtime >= ?", $currentTime)->where($eventTableName . ".starttime <= ?", $currentTime);
            } elseif ($params['order'] == 'ongoingSPupcomming') {
                $select->where("(endtime >= '" . $currentTime . "') || (endtime > '" . $currentTime . "' && starttime > '" . $currentTime . "')");
            } else if ($params['order'] == 1) {
                if ($viewer->getIdentity()) {
                    $users = $viewer->membership()->getMembershipsOfIds();
                    if ($users)
                        $select->where($eventTableName . '.user_id IN (?)', $users);
                    else
                        $select->where($eventTableName . '.user_id IN (?)', 0);
                }
            } else if (isset($params['most_joined']) && $params['most_joined']) {
                $select->order('joinedmember DESC');
            }
        }

        //don't show other module events
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.other.moduleevents', 1) && empty($params['resource_type'])) {
            $select->where($eventTableName . '.resource_type IS NULL')
                ->where($eventTableName . '.resource_id =?', 0);
        } else if (!empty($params['resource_type']) && !empty($params['resource_id'])) {
            $select->where($eventTableName . '.resource_type =?', $params['resource_type'])
                ->where($eventTableName . '.resource_id =?', $params['resource_id']);
        } else if (!empty($params['resource_type'])) {
            $select->where($eventTableName . '.resource_type =?', $params['resource_type']);
        }
        //don't show other module events

        if (isset($params['customSearchCriteria']) && isset($params['order']) && $params['order'] != '') {
            $select->order($eventTableName . '.' . $params['order']);
        }
        // Endtime
        if ((isset($params['past']) && !empty($params['past'])) || (isset($params['view']) && $params['view'] == 'past'))
            $select->where("endtime <= FROM_UNIXTIME(?)", time());
        elseif (isset($params['future']) && !empty($params['future']) || (isset($params['view']) && $params['view'] == 'future'))
            $select->where("endtime > FROM_UNIXTIME('" . time() . "') && starttime > FROM_UNIXTIME('" . time() . "')");

        if (!empty($params['tag'])) {
            $select->joinLeft($tableTagName, "$tableTagName.resource_id = $eventTableName.event_id", NULL)
                ->where($tableTagName . '.resource_type = ?', 'sesevent_event')
                ->where($tableTagName . '.tag_id = ?', $params['tag']);
        }
        if (!empty($params['sameTag'])) {
            $select->joinLeft($tableTagName, "$tableTagName.resource_id = $eventTableName.event_id", NULL)
                ->where($tableTagName . '.resource_type = ?', 'sesevent_event')
                ->distinct(true)
                ->where($tableTagName . '.resource_id != ?', $params['sameTagresource_id'])
                ->where($tableTagName . '.tag_id IN(?)', $params['sameTagTag_id']);
        }
        if (isset($params['widgteName']) && $params['widgteName'] == "Recommanded Event") {
            $select->where($eventTableName . ".user_id <> ?", $viewer_id);
        }
        if (isset($params['widgetName']) && $params['widgetName'] == "hostEvents") {
            $select->where($eventTableName . ".host = ?", $params['host_id'])
                ->where($eventTableName . ".host_type = ?", $params['host_type']);
        }
        if (isset($params['resource_type']) && $params['resource_type'] == 'sesevent_host')
            $select = $select->where('host =?', $params['resource_id']);
        if (isset($params['resource_type']) && $params['resource_type'] == 'sesevent_speakers') {
            $speakerTable = Engine_Api::_()->getDbtable('eventspeakers', 'seseventspeaker');
            $speakerTableName = $speakerTable->info('name');
            $select->joinLeft($speakerTableName, $speakerTableName . '.event_id= ' . $eventTableName . '.event_id', null);
            $select->where($speakerTableName . '.eventspeaker_id !=?', '');
        }

        if (isset($params['resource_type']) && $params['resource_type'] == 'sesevent_list') {
            $listeventsTable = Engine_Api::_()->getDbtable('listevents', 'sesevent');
            $listeventsTableName = $listeventsTable->info('name');
            $select->joinLeft($listeventsTableName, $listeventsTableName . '.file_id= ' . $eventTableName . '.event_id', null);
            $select->where($listeventsTableName . '.list_id =?', $params['resource_id']);
        }

        if (isset($params['widgteName']) && $params['widgteName'] == "Other Event") {
            $select->where($eventTableName . ".event_id <> ?", $params['event_id'])
                ->where($eventTableName . ".user_id = ?", $params['user_id']);
        }
        if (!empty($params['not_event_id']))
            $select->where($eventTableName . '.event_id != ?', $params['not_event_id']);
        if (!empty($params['user_id']) && $params['user_id'] != '')
            $select->where($eventTableName . '.user_id = ?', $params['user_id']);
        if (isset($params['criteria'])) {
            if ($params['criteria'] == 1)
                $select->where($eventTableName . '.featured =?', '1');
            else if ($params['criteria'] == 2)
                $select->where($eventTableName . '.sponsored =?', '1');
            else if ($params['criteria'] == 6)
                $select->where($eventTableName . '.verified =?', '1');
            else if ($params['criteria'] == 3)
                $select->where($eventTableName . '.featured = 1 OR ' . $eventTableName . '.sponsored = 1');
            else if ($params['criteria'] == 4)
                $select->where($eventTableName . '.featured = 0 AND ' . $eventTableName . '.sponsored = 0');
        }
        if (isset($params['info'])) {
            switch ($params['info']) {
                case 'most_viewed':
                    $select->order('view_count DESC');
                    break;
                case 'most_liked':
                    $select->order('like_count DESC');
                    break;
                case 'most_commented':
                    $select->order('comment_count DESC');
                    break;
                case "view_count":
                    $select->order($eventTableName . '.view_count DESC');
                    break;
                case "favourite_count":
                    $select->order($eventTableName . '.favourite_count DESC');
                    break;
                case "most_favourite":
                    $select->order($eventTableName . '.favourite_count DESC');
                    break;
                case "most_rated":
                    $select->order($eventTableName . '.rating DESC');
                    break;
                case 'random':
                    $select->order('Rand()');
                    break;
                case "sponsored" :
                    $select->where($eventTableName . '.sponsored' . ' = 1')
                        ->order($eventTableName . '.event_id DESC');
                    break;
                case "verified" :
                    $select->where($eventTableName . '.verified' . ' = 1')
                        ->order($eventTableName . '.event_id DESC');
                    break;
                case "featured" :
                    $select->where($eventTableName . '.featured' . ' = 1')
                        ->order($eventTableName . '.event_id DESC');
                    break;
                case "creation_date":
                    $select->order($eventTableName . '.creation_date DESC');
                    break;
                case "modified_date":
                    $select->order($eventTableName . '.modified_date DESC');
                    break;
                case "most_joined":
                    $select->order('joinedmember DESC');
                    break;
            }
        }
        if (isset($params['widgetName']) && $params['widgetName'] == 'oftheday') {
            $select->where($eventTableName . '.offtheday =?', 1)
                ->where($eventTableName . '.startdate <= DATE(NOW())')
                ->where($eventTableName . '.enddate >= DATE(NOW())')
                ->order('RAND()');
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $select->where("DATE(starttime) between ('" . $params['start_date'] . "') and ('" . $params['end_date'] . "')");
        } else {
            if (!empty($params['start_date'])) {
                $select->where("starttime LIKE ?", "{$params['start_date']}%");
            }
            if (!empty($params['end_date'])) {
                $select->where("endtime LIKE ?", "{$params['end_date']}%");
            }
        }
        if (isset($params['calanderWidget'])) {
            $likeD = $params['year'] . '-' . $params['month'];
            $select->where("starttime LIKE ?", "{$likeD}%");
        }
        if (isset($params['getEventLike'])) {
            $likeD = $params['year'] . '-' . $params['month'] . '-' . $params['day'];
            $select->where("starttime LIKE ?", "{$likeD}%");
        }
        if (isset($params['orderby'])) {
            $select->order($eventTableName . '.' . $params['orderby'] . ' DESC');
        }
        $select->order('starttime');
        if (isset($params['limit']) && !empty($params['limit']))
            $select->limit($params['limit']);
        if (!empty($params['limit_data']))
            $select->limit($params['limit_data']);
        if (!empty($params['alphabet']))
            $select->where($eventTableName . '.title LIKE ?', "%{$params['alphabet']}%");
        if (isset($params['fetchAll'])) {
            return $this->fetchAll($select);
        } else {
            return $select;
        }
    }

    public function getEventId($slug = null)
    {
        if ($slug) {
            $tableName = $this->info('name');
            $select = $this->select()
                ->from($tableName)
                ->where($tableName . '.custom_url = ?', $slug);
            $row = $this->fetchRow($select);
            if (empty($row)) {
                $event_id = $slug;
            } else
                $event_id = $row->event_id;
            return $event_id;
        }
        return '';
    }

    public function checkCustomUrl($value = '', $event_id = '')
    {
        $select = $this->select('event_id')->where('custom_url = ?', $value);
        if ($event_id)
            $select->where('event_id !=?', $event_id);
        return $select->query()->fetchColumn();
    }

    public function getHostEventCounts($params = array())
    {

        return $this->select()
            ->from($this->info('name'), array('count(*) as hostEventCount'))
            ->where('host_type =?', $params['type'])
            ->where('host =?', $params['host_id'])
            ->query()
            ->fetchColumn();
    }

    public function getHostsPaginator($params = array())
    {

        $tableName = $this->info('name');
        $hostsTable = Engine_Api::_()->getDbTable('hosts', 'sesevent');
        $hostsTableName = $hostsTable->info('name');

        if (isset($params['widgteName']) && $params['widgteName'] != 'Browse Hosts') {
            $select = $this->select()
                ->from($this)
                ->setIntegrityCheck(false)
                ->joinLeft($hostsTableName, "$tableName.host = $hostsTableName.host_id", '');
            if (isset($params['event_id']) && !empty($params['event_id'])) {
                $select->where("$tableName.event_id =?", $params['event_id']);
            }
            if (isset($params['popularity']) && $params['popularity'] == 'most_event') {
                $select->setIntegrityCheck(false)
                    ->joinLeft($hostsTableName, "$tableName.host = $hostsTableName.host_id", 'COUNT(' . $tableName . '.event_id) as totalEventHosted')
                    ->group($tableName . '.host')
                    ->order('totalEventHosted DESC');
            }
        } else {
            $select = $hostsTable->select()->from($hostsTableName);
            if (isset($params['popularity']) && $params['popularity'] == 'most_event') {
                $select->setIntegrityCheck(false)
                    ->joinLeft($tableName, "$tableName.host = $hostsTableName.host_id", 'COUNT(' . $tableName . '.event_id) as totalEventHosted')
                    ->group($tableName . '.host')
                    ->order('totalEventHosted DESC');
            }
        }
        //String Search
        if (isset($params['name']) && !empty($params['name'])) {
            $select->where("$hostsTableName.host_name LIKE ?", "%{$params['name']}%");
        }
        if (isset($params['criteria'])) {
            if ($params['criteria'] == 1)
                $select->where($hostsTableName . '.featured =?', '1');
            else if ($params['criteria'] == 2)
                $select->where($hostsTableName . '.sponsored =?', '1');
            else if ($params['criteria'] == 6)
                $select->where($hostsTableName . '.verified =?', '1');
            else if ($params['criteria'] == 3)
                $select->where($hostsTableName . '.featured = 1 OR ' . $hostsTableName . '.sponsored = 1');
            else if ($params['criteria'] == 4)
                $select->where($hostsTableName . '.featured = 0 AND ' . $hostsTableName . '.sponsored = 0');
        }
        if (isset($params['popularity']) && $params['popularity'] == 'featured') {
            $select->where($hostsTableName . ".featured = ?", 1);
        }
        if (isset($params['popularity']) && $params['popularity'] == 'sponsored') {
            $select->where($hostsTableName . ".sponsored = ?", 1);
        }
        if (isset($params['popularity']) && $params['popularity'] == 'verified') {
            $select->where($hostsTableName . ".verified = ?", 1);
        }
        if (isset($params['popularity'])) {
            switch ($params['popularity']) {
                case "favourite_count":
                    $select->order($hostsTableName . '.favourite_count DESC')
                        ->order($hostsTableName . '.host_id DESC');
                    break;
                case "view_count":
                    $select->order($hostsTableName . '.view_count DESC')
                        ->order($hostsTableName . '.host_id DESC');
                    break;
                case "creation_date":
                    $select->order($hostsTableName . '.creation_date DESC');
                    break;
            }
        }
        if (empty($params['popularity'])) {
            $select->order($hostsTableName . '.creation_date DESC');
        }

        $paginator = Zend_Paginator::factory($select);
        if (!empty($params['page']))
            $paginator->setCurrentPageNumber($params['page']);

        if (!empty($params['limit']))
            $paginator->setItemCountPerPage($params['limit']);

        return $paginator;
    }
}
